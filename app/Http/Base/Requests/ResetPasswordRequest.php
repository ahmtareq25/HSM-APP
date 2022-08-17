<?php

namespace App\Http\Base\Requests;

use App\Http\Base\Rules\IsExistsOnDB;
use Illuminate\Foundation\Http\FormRequest;

class ResetPasswordRequest extends BaseRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'email' => ['required','email',new IsExistsOnDB('users','email')],
            'password' => 'required',
            'password_confirmation' => 'required_with:password|same:password',
            'token' => 'required'
        ];
    }
}
