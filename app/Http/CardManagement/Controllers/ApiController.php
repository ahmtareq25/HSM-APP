<?php

namespace App\Http\CardManagement\Controllers;

use App\Http\CardManagement\CardManager;
use App\Http\CardManagement\Requests\VerifyTokenRequest;
use App\Http\Controllers\Controller;
use App\Http\CardManagement\Requests\TokenRequest;
use App\Models\AppSetting;
use App\Services\ApplicationCode;
use App\Services\AppLog;

class ApiController extends Controller
{
    const API_NAME_GENERATE_TOKEN = 1;
    const API_NAME_GET_CARD_INFO = 2;
    const API_NAME_UPDATE_CARD_INFO = 3;
    const API_NAME_DELETE_TOKEN = 4;
    private $cardManager;
    private $pre_shared_key;


    public function generateToken(TokenRequest $request){
        $this->commonSetter();
        $this->commonLandingRequestLog(self::API_NAME_GENERATE_TOKEN,$request->all());
        $this->cardManager->generateToken($request->all());
        return $this->commonResponse(self::API_NAME_GENERATE_TOKEN);

    }

    public function getCardInfo(VerifyTokenRequest $request){
        $this->commonSetter();
        $this->commonLandingRequestLog(self::API_NAME_GET_CARD_INFO,$request->all());
        $this->cardManager->getCardInfo($request->all());
        return $this->commonResponse(self::API_NAME_GET_CARD_INFO);
    }

    public function updateCardInfo(TokenRequest $request){
        $this->commonSetter();
        $this->commonLandingRequestLog(self::API_NAME_UPDATE_CARD_INFO,$request->all());
        $this->cardManager->processUpdate($request->all());
        return $this->commonResponse(self::API_NAME_UPDATE_CARD_INFO);
    }

    public function deleteCardInfo(VerifyTokenRequest $request){
        $this->commonSetter(false);
        $this->commonLandingRequestLog(self::API_NAME_DELETE_TOKEN,$request->all());
        $this->cardManager->processDelete($request->all());
        return $this->commonResponse(self::API_NAME_DELETE_TOKEN);
    }

    private function commonResponse($type){

        $logData = [
            'action' => $this->getApiName($type).'_LANDING_RESPONSE',
            'request_unique_key' => getResource(AppSetting::APP_REQUEST_UNIQUE_KEY_NAME),
            'repose_data' => $this->cardManager->response_data,
            'status' => $this->cardManager->status
        ];
        $this->commonLog($logData);

        if ( $this->cardManager->application_code == ApplicationCode::APP_PROCESS_SUCCESSFUL){
            return appResponse()->success($this->cardManager->application_code, __($this->getMessageByType($type)), $this->cardManager->response_data);
        }
        return appResponse()->failed($this->cardManager->application_code, __($this->getMessageByType($type)));
    }

    private function getMessageByType($type){

        $messageArr = [
            self::API_NAME_GENERATE_TOKEN => [
                ApplicationCode::APP_PROCESS_SUCCESSFUL => 'Token generated successfully',
                ApplicationCode::APP_PROCESS_FAILED => 'Token generation failed',

            ],
            self::API_NAME_GET_CARD_INFO => [
                ApplicationCode::APP_PROCESS_SUCCESSFUL => 'Card info fetched successfully',
                ApplicationCode::APP_PROCESS_FAILED => 'No Card info found',

            ],
            self::API_NAME_UPDATE_CARD_INFO => [
                ApplicationCode::APP_PROCESS_SUCCESSFUL => 'Card info updated successfully',
                ApplicationCode::APP_PROCESS_FAILED => 'Card info update failed',

            ],
            self::API_NAME_DELETE_TOKEN => [
                ApplicationCode::APP_PROCESS_SUCCESSFUL => 'Card info deleted successfully',
                ApplicationCode::APP_PROCESS_FAILED => 'Card info deletion failed',

            ],

        ];

        return $messageArr[$type][$this->cardManager->application_code] ?? (new ApplicationCode())->getMessageByCode($this->cardManager->application_code);
    }

    private function getApiName($type){
        $name = "API";
        $nameArr = [
            self::API_NAME_GENERATE_TOKEN => 'GENERATE_TOKEN',
            self::API_NAME_GET_CARD_INFO => 'GET_CARD_INFO',
            self::API_NAME_UPDATE_CARD_INFO => 'UPDATE_CARD_INFO',
            self::API_NAME_DELETE_TOKEN => 'DELETE_CARD_TOKEN'];

        if (isset($nameArr[$type])){
            $name = $nameArr[$type];
        }
        return $name;
    }

    private function commonLog($logdata){

        appLog()->createLog(AppLog::unsetKeys($logdata));
    }

    private function commonLandingRequestLog($type, $requestData){
        $logData = [
            'action' => $this->getApiName($type).'_LANDING_REQUEST',
            'request_unique_key' => getResource(AppSetting::APP_REQUEST_UNIQUE_KEY_NAME),
            'request_data' => $requestData
        ];
        $this->commonLog($logData);
    }

    private function commonSetter($connect_hsm = true){
        $this->pre_shared_key = '50262C46168A96BAB8D6A2567244E048';
        $this->cardManager = new CardManager($this->pre_shared_key, $connect_hsm);
    }



}
