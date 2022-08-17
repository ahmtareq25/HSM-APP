<?php

namespace App\Http\CardManagement\Requests;


class VerifyTokenRequest extends AppRequest
{


    public function rules(): array
    {

        $result =  [
            'token' => ['required']
        ];

        return $this->getAppRules() + $result;

    }


}
