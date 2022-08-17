<?php

namespace App\Models;

use App\Services\ApplicationCode;
use App\Services\StringManagement;

class AppSetting extends BaseModel
{
    const APP_SETTING_OBJ_NAME = 'appSettingObj';
    const APP_REQUEST_UNIQUE_KEY_NAME = 'requestUniqueKey';
    public $application_code = ApplicationCode::APP_PROCESS_FAILED;

    public function getAppSettingByClientIdAndClientSecret($client_id,$client_secret)
    {
        return $this->where('client_id',$client_id)
//            ->select('id')
            ->where('client_secret',$client_secret)
            ->first();
    }

    public function validateRequestAuthentication($client_id,$client_secret){
        $appSettingsObj = (new AppSetting())->getAppSettingByClientIdAndClientSecret($client_id,$client_secret);
        if (!empty($appSettingsObj)){
            if (!empty($appSettingsObj->whitelist_ips) && in_array(getRemoteServerIp(), explode(',',$appSettingsObj->whitelist_ips ))){
                $this->application_code = ApplicationCode::APP_PROCESS_SUCCESSFUL;
                $this->setApplicationResource($appSettingsObj);
            }else {
                $this->application_code = ApplicationCode::APP_IP_ADDRESS_IS_NOT_ALLOWED;
            }
        }else{
            $this->application_code = ApplicationCode::APP_INVALID_CREDENTIALS;
        }

//        $this->setApplicationResource($appSettingsObj);
//        $this->application_code = ApplicationCode::APP_PROCESS_SUCCESSFUL;

        $logData = [
            'action' => 'API_AUTHENTICATION_CHECKING',
            'request_unique_key' => getResource(self::APP_REQUEST_UNIQUE_KEY_NAME),
            'application_code' => $this->application_code,
        ];


        appLog()->createLog($logData);
    }

    private function setApplicationResource($appSettingsObj){
        setResource(self::APP_SETTING_OBJ_NAME,$appSettingsObj);
        setResource(self::APP_REQUEST_UNIQUE_KEY_NAME,StringManagement::generateUniqueKey($appSettingsObj->id));
    }

}
