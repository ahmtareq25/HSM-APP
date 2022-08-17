<?php

namespace App\Http\Base\Requests\Users;

use App\Http\Base\Requests\BaseRequest;
use App\Models\User;
use Illuminate\Validation\Rule;

class UserUpdateRequest extends BaseRequest
{
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', Rule::unique(User::class)->ignore($this->user_id, 'id')],
            'language' => ['nullable', 'string', Rule::in(config('app.languages'))]
        ];
    }
}
