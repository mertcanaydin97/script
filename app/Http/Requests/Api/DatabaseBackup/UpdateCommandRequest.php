<?php

namespace App\Http\Requests\Api\DatabaseBackup;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCommandRequest extends FormRequest
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
     * @return array
     */
    public function rules()
    {

        $rules = [
            'mysqldump_command'    => 'required',
        ];

        return $rules;
    }
}
