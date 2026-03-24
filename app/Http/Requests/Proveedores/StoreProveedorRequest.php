<?php

namespace App\Http\Requests\Proveedores;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreProveedorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nombre' => 'required|string|max:255',
            'contacto' => 'nullable|string|max:255',
            'telefono' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'estado' => 'nullable|string|in:activo,inactivo',
        ];
    }

    public function messages(): array
    {
        return [
            'nombre.required' => 'El nombre del proveedor es obligatorio.',
            'nombre.string' => 'El nombre debe ser una cadena de texto.',
            'nombre.max' => 'El nombre no puede exceder los 255 caracteres.',
            'contacto.string' => 'El contacto debe ser una cadena de texto.',
            'contacto.max' => 'El contacto no puede exceder los 255 caracteres.',
            'telefono.string' => 'El teléfono debe ser una cadena de texto.',
            'telefono.max' => 'El teléfono no puede exceder los 20 caracteres.',
            'email.email' => 'El correo electrónico debe ser válido.',
            'email.max' => 'El correo no puede exceder los 255 caracteres.',
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
