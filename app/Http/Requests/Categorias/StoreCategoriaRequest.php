<?php

namespace App\Http\Requests\Categorias;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreCategoriaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string|max:255',
            'estado' => 'nullable|string|in:activo,inactivo',
        ];
    }

    public function messages(): array
    {
        return [
            'nombre.required' => 'El nombre de la categoría es obligatorio.',
            'nombre.string' => 'El nombre debe ser una cadena de texto.',
            'nombre.max' => 'El nombre no puede exceder los 255 caracteres.',
            'descripcion.string' => 'La descripción debe ser una cadena de texto.',
            'descripcion.max' => 'El descripción no puede exceder los 255 caracteres.',
            'estado.string' => 'El estado debe ser una cadena de texto.',
            'estado.in' => 'El estado debe ser: activo o inactivo.',
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
