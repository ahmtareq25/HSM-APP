<?php

namespace App\Http\Base\Requests;

class UpdateUserGroupRolesRequest extends BaseRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            "usergroup_id" => "required|numeric",
            "selected_roles" => "required|array",
        ];
    }
}
