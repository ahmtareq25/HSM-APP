<?php

namespace App\Http\CardManagement\Requests;


use Illuminate\Support\Facades\Route;

class TokenRequest extends AppRequest
{


    public function rules(): array
    {


        $routeName = Route::currentRouteName();
        if ($routeName == 'updateCardInfoByToken'){
            $result =  [
                'token' => ['required'],
                'pan' => ['sometimes', 'regex:'.self::RULE_PAN_REGEX],
                'expiry' => ['sometimes', 'regex:'.self::RULE_EXPIRY_REGEX],
                'cvv' => ['sometimes', 'regex:'.self::RULE_CVV_REGEX],
            ];
        }else{
            $result =  [
                'pan' => ['required', 'regex:'.self::RULE_PAN_REGEX],
                'expiry' => ['required', 'regex:'.self::RULE_EXPIRY_REGEX],
                'cvv' => ['sometimes', 'regex:'.self::RULE_CVV_REGEX],
            ];
        }


        return $this->getAppRules() + $result;

    }


}
