<?php

namespace App\Http\Base\Requests;

class LoginRequest extends BaseRequest
{
    public function rules(): array
    {
        return [
            'email' => ['required', 'email'],
            'password' => ['required']
        ];
    }


}
