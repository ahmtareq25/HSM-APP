<?php

namespace App\Http\Base\Requests;

class RenewAuthTokenRequest extends BaseRequest
{
    public function rules(): array
    {
        return [
            'refresh_token' => ['required']
        ];
    }
}
