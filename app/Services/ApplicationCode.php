<?php

namespace App\Services;

class ApplicationCode
{
    const APP_PROCESS_FAILED = 99;
    const APP_PROCESS_SUCCESSFUL = 100;
    const APP_INVALID_CREDENTIALS = 101;
    const APP_INVALID_CARD_TOKEN = 102;
    const APP_IP_ADDRESS_IS_NOT_ALLOWED = 103;



    public  function getMessageByCode($application_code){
        $message = 'UNKNOWN';
        $messageArr = [
            self::APP_PROCESS_FAILED => 'Process Failed',
            self::APP_PROCESS_SUCCESSFUL => 'Process Successful',
            self::APP_INVALID_CREDENTIALS => 'Invalid credentials',
            self::APP_INVALID_CARD_TOKEN => 'Invalid card token',
            self::APP_IP_ADDRESS_IS_NOT_ALLOWED => 'IP address is not allowed'
        ];
        if (isset($messageArr[$application_code])){
            $message = $messageArr[$application_code];
        }
        return $message;
    }

}
