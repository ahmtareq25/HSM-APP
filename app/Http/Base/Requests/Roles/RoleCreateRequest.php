<?php

namespace App\Http\Base\Requests\Roles;

use App\Http\Base\Requests\BaseRequest;
use App\Http\Base\Rules\IsExistsOnDB;
use App\Models\Role;
use Illuminate\Validation\Rule;

class RoleCreateRequest extends BaseRequest
{
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:100', Rule::unique(Role::class)],
            'status' => ['required', 'boolean']
        ];
    }
}
