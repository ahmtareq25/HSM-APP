<?php

namespace App\Http\CardManagement;

use App\Models\AppSetting;
use App\Models\CardInformation;
use App\Services\ApplicationCode;

class CardManager
{
    const CVV_DEFAULT = '000';
    const IS_ALLOW_REQUEST_ENCRYPTION = false;
    public $status = false;
    public $application_code = ApplicationCode::APP_PROCESS_FAILED;
    public $response_data = [];
    private $encryptDecryptObj = null;
    private $hsmObj = null;
    private $secret_key;
    private $appSettingObj;



    public function __construct($secret_key, $connect_hsm = true)
    {
        if ($connect_hsm){
            $this->hsmObj = new HSM();
        }

        $this->encryptDecryptObj =  new EncryptDecrypt($secret_key);
        $this->secret_key = $secret_key;
        $this->appSettingObj = getResource(AppSetting::APP_SETTING_OBJ_NAME);
    }

    public function generateTokenByCardInfo($inputData){

        $logData = [
            'action' => 'TOKEN_GENERATION_SERVICE',
            'request_unique_key' => getResource(AppSetting::APP_REQUEST_UNIQUE_KEY_NAME)
        ];
        $result = [];
        try {
            $pan = $inputData['pan'];
            $expiry = $inputData['expiry'];
            $cvv = empty($inputData['cvv']) ? self::CVV_DEFAULT : $inputData['cvv'];
            $hsm_header = "ABCD";

            $encryptDecryptObj = $this->encryptDecryptObj;


            if(self::IS_ALLOW_REQUEST_ENCRYPTION) {
                // decrypted
                $decrypted_pan = $encryptDecryptObj->decrypt($pan);
                $decrypted_expiry = $encryptDecryptObj->decrypt($expiry);
                $decrypted_cvv = $encryptDecryptObj->decrypt($cvv);

                // remove padding
                $decrypted_pan = $encryptDecryptObj->removePadding($decrypted_pan, 'F');
                $decrypted_expiry = $encryptDecryptObj->removePadding($decrypted_expiry, 'F');
                $decrypted_cvv = $encryptDecryptObj->removePadding($decrypted_cvv, 'F');

                // concate pan, expiry cvv and convert to ASCII
                $decrypted_pan_expiry_cvv = $decrypted_pan . $decrypted_expiry . $decrypted_cvv;

                $ascii_string = $encryptDecryptObj->convertToAscii($decrypted_pan_expiry_cvv);
            }else{
                $ascii_string = $pan.$expiry.$cvv;
            }


            // replace "/" with "DECIMAL"
            $replaced_string = $encryptDecryptObj->replaceValueWithType("/", "DEC", $ascii_string);

            // Padding with 0s at the start to make it 36

            $padding_length = 36 - strlen($replaced_string);
            $padding_zeros = $padding_length - strlen($padding_length);
            $padded_string = $padding_length . $encryptDecryptObj->makePadding("", $padding_zeros, '0', "LEFT") . $replaced_string;


            // split equal 3 parts
            $split_part = str_split($padded_string, 12);


            $pin1 = $split_part[0].'F';
            $pin2 = $split_part[1].'F';
            $pin3 = $split_part[2].'F';

            $right_most_12_without_check_digit = substr($replaced_string, 0, 12);

            $response_pin1_from_hsm = $this->getCommandResponse(
                $pin1,
                $hsm_header,
                HSM::COMMAND_BA,
                $right_most_12_without_check_digit
            );

            $response_pin2_from_hsm = $this->getCommandResponse(
                $pin2,
                $hsm_header,
                HSM::COMMAND_BA,
                $right_most_12_without_check_digit
            );

            $response_pin3_from_hsm = $this->getCommandResponse(
                $pin3,
                $hsm_header,
                HSM::COMMAND_BA,
                $right_most_12_without_check_digit
            );

            $final_encrypted_string = $response_pin1_from_hsm["encrypted"];
            $final_encrypted_string .= $response_pin2_from_hsm["encrypted"];
            $final_encrypted_string .= $response_pin3_from_hsm["encrypted"];

            // left 4 F padding for right most 12 string
            $four_f_padding = $encryptDecryptObj->makePadding($right_most_12_without_check_digit, strlen($right_most_12_without_check_digit) + 4, 'F', "LEFT");

            // $four_f_padding encrypt by pre shared key
            $four_f_padding_encrypted_string = $encryptDecryptObj->encrypt($four_f_padding);

            // Creating Token
            $right_most_12_without_check_digit_padding = $this->encryptDecryptObj->makePadding($right_most_12_without_check_digit,13,'F',"RIGHT");
            $response_token_from_hsm = $this->getCommandResponse(
                $right_most_12_without_check_digit_padding,
                $hsm_header,
                HSM::COMMAND_BA,
                $right_most_12_without_check_digit
            );

            // HSM Encrypted + Pre-Shared Key Encrypted of 4F Padding Encrypted
            $full_token = $response_token_from_hsm["encrypted"] . strtoupper($four_f_padding_encrypted_string);

            // encrypt by pre shared key full token
            $final_encrypted_token = strtoupper($encryptDecryptObj->encrypt($full_token));

            $result = [
                "data" => $final_encrypted_string,
                "token" => $final_encrypted_token
            ];
        }catch (\Exception $ex){
            $result['error'] = $ex->getMessage();
            $result['line'] = $ex->getLine();
            $result['trace'] = $ex->getTrace();

            $logData['exception'] = $ex;

        }

        appLog()->createLog($logData);

        return $result;
    }


    private function getCommandResponse($pin,$hsm_header,$command,$right_most_12_without_check_digit){
        $breakdown = [];
        // 12 right-most without the Check Digit
        $pin_command = $hsm_header.$command.$pin.$right_most_12_without_check_digit;
        $response = $this->hsmObj->sendHSMCommand($pin_command);
        $breakdown['header'] = $hsm_header;
        $replaced_header = str_replace($hsm_header,"",$response);
        $breakdown['command'] = substr($replaced_header,0,2);

        $after_replaced_header = substr($replaced_header, strlen($breakdown['command']));

        $breakdown['response_code'] = substr($after_replaced_header,0,2);

        $breakdown['encrypted'] = substr($after_replaced_header, strlen($breakdown['response_code']));

        if($breakdown['response_code'] != "00"){
            throw new \Exception("HSM error response {$breakdown['response_code']} for {$pin}");
        }

        return $breakdown;
    }

    private function getDecryptedBlockResult($token,$hsm_header,$pin){
        // pin1 hsm response using validated token (2nd decrypted block)
        $pin1_hsm_response = $this->getCommandResponse(
            $token,
            $hsm_header,
            HSM::COMMAND_NG,
            $pin
        );

        if(!$position = strpos($pin1_hsm_response['encrypted'],"F")){
            throw new \Exception("Data is Invalid");
        }
        // decrypt block 1
        return substr($pin1_hsm_response['encrypted'],0,$position+1);
    }


    public function getCardInfoByToken($inputData){
        $logData = [
            'action' => 'CARD_EXTRACTION_SERVICE',
            'request_unique_key' => getResource(AppSetting::APP_REQUEST_UNIQUE_KEY_NAME)
        ];
        $result = [];
        try {
            $encryptDecryptObj = $this->encryptDecryptObj;

            $hsm_header = "ABCD";
            $token = $inputData['token'];
            $data = $inputData['data'];

            // decrypt token pre-shared key
            $token = $encryptDecryptObj->decrypt($token);

            // get first 13 chars
            $left_13_chars = substr($token, 0, 13);
            // remove left-most 13 chars
            $token = str_replace($left_13_chars, "", $token);

            // decrypt pre-shared key
            $token = $encryptDecryptObj->decrypt($token);

            // remove 4 letf padding F
            $token = str_replace("FFFF", "", $token);

            // sending HSM command
            $response_from_hsm = $this->getCommandResponse(
                $token,
                $hsm_header,
                HSM::COMMAND_NG,
                $left_13_chars
            );

            if(empty($token)){
                throw new \Exception("Token is empty");
            }

            // check 2nd decrypted block exist
            if(!str_contains($response_from_hsm['encrypted'],$token)){
                throw new \Exception("Incorrect User Token");
            }

            // Data divided into 3 equal parts
            $divided_array = str_split($data,strlen($data)/3);

            $pin1 = $divided_array[0];
            $pin2 = $divided_array[1];
            $pin3 = $divided_array[2];

            $decrypted_block_1 = $this->getDecryptedBlockResult($token,$hsm_header,$pin1);

            // remove first char and remove all leading zero also remove last F
            $block_1 = rtrim($decrypted_block_1,"F");

            $decrypted_block_2 = $this->getDecryptedBlockResult($token,$hsm_header,$pin2);
            $block_2 = rtrim($decrypted_block_2,"F"); // removed last F

            $decrypted_block_3 = $this->getDecryptedBlockResult($token,$hsm_header,$pin3);
            $block_3 = rtrim($decrypted_block_3,"F"); // removed last F

            // 3 block together
            $block = $block_1.$block_2.$block_3;

            $padding_length = $this->getPaddingLength($block);

            $removePadding = ltrim(ltrim($block, $padding_length), '0');

            // less possibility to be true
            if (strlen($removePadding) == strlen($block)){
                $removePadding = $padding_length.$removePadding;
            }



            $result = $this->extractCardInfo($removePadding);


        }catch (\Exception $ex){
            $result['error'] = $ex->getMessage();
            $result['line'] = $ex->getLine();
            $logData['exception'] = $ex;
        }

        appLog()->createLog($logData);

        return $result;
    }

    private function getPaddingLength($block){
        $blockArr = str_split($block);
        $padding_length = '';
        foreach ($blockArr as $key => $value){
            if ($key <= 1 && $value > 0){
                $padding_length .=$value;
            }else{
                break;
            }
        }
        return $padding_length;
    }

    private function extractCardInfo($full_string){
        $cvv_length = 3;
        $max_cvv_length = 10;

        for ($i = $cvv_length; $i <= $max_cvv_length; $i++){
            $rightMost10 = substr($full_string, -($i + 6));
            $rightMost10Arr = str_split($rightMost10, 2);
            if (strlen($rightMost10Arr[count($rightMost10Arr) -1]) == 1){
                $rightMost10 = substr($full_string, -($i + 7));
                $rightMost10Arr = str_split($rightMost10, 2);
            }


            if ($rightMost10Arr[1] == 47 && $rightMost10Arr[0] <=12){
                $cvv_length = $i;
            }
        }
//
//
//
//
        $cvv = substr($full_string, -$cvv_length);
        $sting_without_cvv = substr($full_string, 0, strlen($full_string) - $cvv_length);
        $sting_without_cvvArr = str_split(substr($sting_without_cvv, -6), 2);
        $month = $sting_without_cvvArr[0];
        $year = $sting_without_cvvArr[2];
        $pan = substr($full_string, 0, strlen($full_string) - ($cvv_length +6));
        $expiry = $month.'/'.$year;

//        $string_length = strlen($full_string);
//
//        for($cvv_length = 3; $cvv_length <=4;$cvv_length++){
//            $separator_7_index = $string_length - $cvv_length-3;
//            $separator_4_index = $string_length - $cvv_length-4;
//            $check = $full_string[$separator_7_index] == "7"
//                && $full_string[$separator_4_index] == "4"
//                && $full_string[$separator_4_index-2].$full_string[$separator_4_index-1] < 13;
//
//            if($check){
//                 break;
//            }
//        }



//        $pan = substr($full_string,0,$separator_4_index-2);
//        $expiry = $full_string[$separator_4_index-2];
//        $expiry.=$full_string[$separator_4_index-1];
//        $expiry.="/";
//        $expiry.=$full_string[$separator_4_index+2];
//        $expiry.=$full_string[$separator_4_index+3];
//        $cvv = substr($full_string,$separator_4_index+4);

        if(self::IS_ALLOW_REQUEST_ENCRYPTION) {
            $pan = strtoupper($this->encryptDecryptObj->encrypt($this->encryptDecryptObj->makePadding(bin2hex($pan),48,"F","LEFT")));
            $expiry = strtoupper($this->encryptDecryptObj->encrypt($this->encryptDecryptObj->makePadding(bin2hex($expiry),16,"F","LEFT")));
            $cvv = strtoupper($this->encryptDecryptObj->encrypt($this->encryptDecryptObj->makePadding(bin2hex($cvv),16,"F","LEFT")));
        }

        return [
            'pan' => $pan,
            'expiry' => $expiry,
            'cvv' => $cvv
        ];
    }




    //started from Tareq
    public function getCardInfo($requestData){


        $token = $this->encryptDecryptObj->decrypt($requestData['token']);
        $logData = [
            'action' => 'GET_CARD_TOKEN',
            'request_unique_key' => getResource(AppSetting::APP_REQUEST_UNIQUE_KEY_NAME),
            'decrypted_token' => $token
        ];

        try {


            $cardInformationObj = null;
            if (!empty($token)){
                $cardInformation = new CardInformation();
                $cardInformationObj = $cardInformation->findByBrandToken($this->appSettingObj->id, $token);
            }

            if (!empty($cardInformationObj)){
                $inputData = [
                    'data' => $cardInformationObj->hsm_data,
                    'token' => $cardInformationObj->hsm_token
                ];
                $result = $this->getCardInfoByToken($inputData);
                if (isset($result['pan'])){
                    $this->response_data = $result;
                    $this->application_code = ApplicationCode::APP_PROCESS_SUCCESSFUL;
                }
            }else{
                $this->application_code = ApplicationCode::APP_INVALID_CARD_TOKEN;
            }
        }catch (\Throwable $exception){
            $logData['exception'] = $exception;
        }

        $logData['application_code'] = $this->application_code;
        appLog()->createLog($logData);

    }

    public function generateToken($requestData){
        $logData = [
            'action' => 'GENERATE_CARD_TOKEN',
            'request_unique_key' => getResource(AppSetting::APP_REQUEST_UNIQUE_KEY_NAME)
        ];
        try {
            $newCardData = [
                'pan' => $requestData['pan'],
                'expiry' => $requestData['expiry'],
                'cvv' => $requestData['cvv'] ?? '000'
            ];
            $tokenResponse = $this->generateTokenByCardInfo($newCardData);
            if (isset($tokenResponse['token'])) {
                $cardInformation = new CardInformation();
                $insertData = [
                    'hsm_token' => $tokenResponse['token'],
                    'hsm_data' => $tokenResponse['data'],
                    'application_id' => $this->appSettingObj->id
                ];
                $cardInformationObj = $cardInformation->insertEntry($insertData);
                if (!empty($cardInformationObj)){
                    $brand_token = $this->generateBrandToken($cardInformationObj->id);
                    $updateData = [
                        'brand_token' => $brand_token,
                    ];
                    if ($cardInformation->updateDataById($cardInformationObj->id, $updateData)) {
                        $responseData = [
                            'token' => strtoupper($this->encryptDecryptObj->encrypt($brand_token))
                        ];
                        $this->response_data = $responseData;
                        $this->application_code = ApplicationCode::APP_PROCESS_SUCCESSFUL;
                    }
                }

            }
        }catch (\Throwable $exception){
            $logData['exception'] = $exception;
        }

        $logData['application_code'] = $this->application_code;

        appLog()->createLog($logData);

    }

    public function processUpdate($requestData){

        $token = $this->encryptDecryptObj->decrypt($requestData['token']);

        $logData = [
            'action' => 'UPDATE_CARD_TOKEN',
            'request_unique_key' => getResource(AppSetting::APP_REQUEST_UNIQUE_KEY_NAME),
            'decrypted_token' => $token
        ];
        try {

            $cardInformationObj = null;
            if (!empty($token)){
                $cardInformation = new CardInformation();
                $cardInformationObj = $cardInformation->findByBrandToken($this->appSettingObj->id, $token);
            }

            if (!empty($cardInformationObj)){
                $inputData = [
                    'data' => $cardInformationObj->hsm_data,
                    'token' => $cardInformationObj->hsm_token
                ];
                $result = $this->getCardInfoByToken($inputData);
                if (isset($result['pan'])){
                    $newCardData = [
                        'pan' => $requestData['pan'] ?? $result['pan'],
                        'expiry' => $requestData['expiry'] ?? $result['pan'],
                        'cvv' => $requestData['cvv'] ?? $result['cvv']
                    ];
                    $tokenResponse = $this->generateTokenByCardInfo($newCardData);
                    if (isset($tokenResponse['token'])){
                        $brand_token = $this->generateBrandToken($cardInformationObj->id);
                        $updateData = [
                            'brand_token' => $brand_token,
                            'hsm_token' => $tokenResponse['token'],
                            'hsm_data' => $tokenResponse['data']
                        ];
                        if ($cardInformation->updateDataById($cardInformationObj->id, $updateData)){
                            $this->application_code = ApplicationCode::APP_PROCESS_SUCCESSFUL;
                            $responseData = [
                                'token' => strtoupper($this->encryptDecryptObj->encrypt($brand_token))
                            ];
                            $this->response_data = $responseData;
                        }


                    }

                }
            }else{
                $this->application_code = ApplicationCode::APP_INVALID_CARD_TOKEN;
            }
        }catch (\Throwable $exception){
            $logData['exception'] = $exception;
        }

        $logData['application_code'] = $this->application_code;
        appLog()->createLog($logData);
    }

    public function processDelete($requestData){
        $token = $this->encryptDecryptObj->decrypt($requestData['token']);
        $logData = [
            'action' => 'DELETE_CARD_TOKEN',
            'request_unique_key' => getResource(AppSetting::APP_REQUEST_UNIQUE_KEY_NAME),
            'decrypted_token' => $token
        ];
        try {
            if (!empty($token)){
                if ((new CardInformation())->deleteByBrandToken($this->appSettingObj->id, $token)){
                    $this->application_code = ApplicationCode::APP_PROCESS_SUCCESSFUL;
                }
            }else{
                $this->application_code = ApplicationCode::APP_INVALID_CARD_TOKEN;
            }

        }catch (\Throwable $exception){
            $logData['exception'] = $exception;
        }

        $logData['application_code'] = $this->application_code;
        appLog()->createLog($logData);

    }


    private function generateBrandToken($id){
        $length = 13 - strlen($id);
        $uniqueValue = time().rand(100000,9999999);
        if($length > 0){
            $id = $this->encryptDecryptObj->makePadding($id, $length, 0, 'LEFT');
        }
       return $id.'-'.$uniqueValue;
    }

    private function getIdFromBrandToken($bandToken){
        $id = 0;
        $decrypted_token = $this->encryptDecryptObj->decrypt($bandToken);
        if (!empty($decrypted_token)){
            $dataArr = explode('-', $decrypted_token);
            if (is_array($dataArr)){
                $id = $this->encryptDecryptObj->removePadding($dataArr[0], '0');
            }
        }
        return $id;

    }






}
