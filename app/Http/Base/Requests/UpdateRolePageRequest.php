<?php

namespace App\Http\Base\Requests;

class UpdateRolePageRequest extends BaseRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            "selected_page_ids" => "required|array",
            "role_id" => "required|numeric",
        ];
    }
}
