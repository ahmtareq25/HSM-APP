<?php

namespace App\Http\Base\Requests;


use Illuminate\Validation\Validator;

class UserGroupRequest extends BaseRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */

     public function rules()
     {

          return [
               'name' => ['required', 'string', 'max:50'],
          ];

     }


}
