<?php

namespace Diviky\Bright\Rules;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class FileValidationRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'file' => 'mimetypes:' . $this->input('accept'),
        ];
    }

    public function messages(){
        return [
            'file.mimetypes' => 'The file is not of the expected type.'
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'status' => 'ERROR',
            'code' => 422,
            'message' => $validator->errors()->first(),
            'errors' => $validator->errors(),
        ], 422));
    }
}
