<?php

namespace App\Http\CardManagement\Requests;

use App\Http\Base\Requests\BaseRequest;

class AppRequest extends BaseRequest
{

    const RULE_PAN_REGEX = '/^\d{15,20}$/';
    const RULE_EXPIRY_REGEX = '/^(0[1-9]|1[0-2]|[1-9])\/(1[4-9]|[0-9][0-9]|20[1-9][1-9])$/';
    const RULE_CVV_REGEX = '/^[0-9]{3,4}$/';

    public function getAppRules(): array
    {

        $result['client_id'] = 'required';

        $result['client_secret'] = 'required';

        return $result;
    }



}
