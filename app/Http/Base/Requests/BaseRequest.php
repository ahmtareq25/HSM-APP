<?php

namespace App\Http\Base\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class BaseRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            //
        ];
    }

    protected function passedValidation()
    {
        $request = $this->all();
        $rules = array_keys($this->rules());
        $final_data = [];
        if (!empty($rules)) {
            foreach ($rules as $rule) {
                if (isset($request[$rule])) {
                    $final_data[$rule] = $request[$rule];
                }
            }
        }

        $this->replace($final_data);
    }

    protected function failedValidation(Validator $validator)
    {
        $error = (new ValidationException($validator))->errors();
        throw new HttpResponseException(appResponse()->failed(config('status_code.validation_fail'), __('Validation Error'),  $error));
    }
}
