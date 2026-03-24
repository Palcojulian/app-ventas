<?php

namespace App\Http\Requests\Compras;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdateCompraRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'estado' => 'required|string|in:pendiente,completado,cancelado',
        ];
    }

    public function messages(): array
    {
        return [
            'estado.required' => 'El estado es obligatorio.',
            'estado.string' => 'El estado debe ser una cadena de texto.',
            'estado.in' => 'El estado debe ser: pendiente, completado o cancelado.',
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        $mensajes = implode(' | ', $validator->errors()->all());
        throw new HttpResponseException(response()->json([
            'success' => false,
            'message' => 'Campos requeridos pendientes',
            'errores' => $mensajes,
        ], 422));
    }
}
