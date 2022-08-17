<?php

namespace App\Models;

class OauthClient extends BaseModel
{

    public function getClientSecretByClientId($client_id)
    {
        $client_secret = self::where('id', $client_id)->value('secret');
        if (!empty($client_secret)) {
            return $client_secret;
        }
        return null;
    }
}
