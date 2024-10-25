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
            'file' => ['sometimes', 'required', 'mimetypes:' . $this->input('accept', 'image/*')],
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
