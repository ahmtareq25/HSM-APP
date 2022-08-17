<?php

namespace App\Http\Base\Requests;

class ResendOTPRequest extends BaseRequest
{
    public function rules(): array
    {
        return [
            'user_id' => ['required']
        ];
    }
}
