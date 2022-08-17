<?php

namespace App\Http\CardManagement;

class EncryptDecrypt
{

    const cipher ="AES-256-ECB";
    private $secret_key;
    const padding_lenght = 48;
    const padding_char = 'F';

    private $replace_value_map =[
        "/"=>[
            "HEX"=>"2f",
            "DEC"=>"47"
        ]
    ];

    public function __construct($secret_key)
    {
        $this->secret_key = $secret_key;
    }

    // Encryption
    public function encrypt($value){

        $cipher = self::cipher;
        $plaintext = $value;

        $key = $this->secret_key;
        //$plaintext_to_hex = bin2hex($plaintext);
        //$padding = $this->makePadding($plaintext_to_hex,self::padding_lenght,self::padding_char,"LEFT");
        //$output = openssl_encrypt($padding, $cipher, $key, OPENSSL_RAW_DATA);
        $output = openssl_encrypt($value, $cipher, $key, OPENSSL_RAW_DATA);
        return bin2hex($output);
    }

    public function makePadding($value,$length,$char,$direction){
        $directionArray = [
            "LEFT"=>STR_PAD_LEFT,
            "RIGHT"=>STR_PAD_RIGHT
        ];
        $padding = str_pad($value,$length,$char,$directionArray[$direction]);
        return $padding;
    }

    // Decryption
    public function decrypt($encrypt_value)
    {
        $result = "";
        try {
            $cipher = self::cipher;
            $key = $this->secret_key;
            $chiperRaw = hex2bin($encrypt_value);
            $chiperRaw = trim(base64_encode($chiperRaw));
            $chiperRaw = base64_decode($chiperRaw);

            $result =  openssl_decrypt($chiperRaw, $cipher, $key, OPENSSL_RAW_DATA);
        }catch (\Exception $exception){

        }
        return $result;

    }


    public function removePadding($value,$padding_char){
        return ltrim($value,$padding_char);
    }

    public function convertToAscii($value){
        $str = '';
        for($i=0;$i<strlen($value);$i+=2) $str .= chr(hexdec(substr($value,$i,2)));
        return $str;
    }

    public function replaceValueWithType($replace_value,$type,$string){
        return str_replace($replace_value,$this->replace_value_map[$replace_value][$type],$string);
    }


}
