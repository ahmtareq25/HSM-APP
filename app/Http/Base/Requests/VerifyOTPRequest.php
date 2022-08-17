<?php

namespace App\Http\Base\Requests;

class VerifyOTPRequest extends BaseRequest
{
    public function rules(): array
    {
        return [
            'user_id' => ['required'],
            'otp' => ['required'],
        ];
    }
}
