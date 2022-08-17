<?php

namespace App\Http\Base\Requests\Users;

use App\Http\Base\Requests\BaseRequest;
use App\Models\User;
use Illuminate\Validation\Rule;

class UserCreateRequest extends BaseRequest
{
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', Rule::unique(User::class)],
            'password' => ['required', 'string', 'min:6', 'max:30', 'confirmed'],
            'language' => ['nullable', 'string', Rule::in(config('app.languages'))]
        ];
    }
}
