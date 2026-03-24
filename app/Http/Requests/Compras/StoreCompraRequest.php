<?php

namespace App\Http\Requests\Compras;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreCompraRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'id_proveedor' => 'required|exists:proveedores,id',
            'estado' => 'nullable|string|in:pendiente,completado,cancelado',
            'detalles' => 'required|array|min:1',
            'detalles.*.id_producto' => 'required|exists:productos,id',
            'detalles.*.cantidad' => 'required|integer|min:1',
            'detalles.*.costo_unitario' => 'required|numeric|min:0',
        ];
    }

    public function messages(): array
    {
        return [
            'id_proveedor.required' => 'El proveedor es obligatorio.',
            'id_proveedor.exists' => 'El proveedor seleccionado no existe.',
            'estado.string' => 'El estado debe ser una cadena de texto.',
            'estado.in' => 'El estado debe ser: pendiente, completado o cancelado.',
            'detalles.required' => 'Los detalles de la compra son obligatorios.',
            'detalles.array' => 'Los detalles deben ser un arreglo.',
            'detalles.min' => 'Debe incluir al menos un producto.',
            'detalles.*.id_producto.required' => 'El producto es obligatorio.',
            'detalles.*.id_producto.exists' => 'El producto seleccionado no existe.',
            'detalles.*.cantidad.required' => 'La cantidad es obligatoria.',
            'detalles.*.cantidad.integer' => 'La cantidad debe ser un número entero.',
            'detalles.*.cantidad.min' => 'La cantidad debe ser al menos 1.',
            'detalles.*.costo_unitario.required' => 'El costo unitario es obligatorio.',
            'detalles.*.costo_unitario.numeric' => 'El costo unitario debe ser un número.',
            'detalles.*.costo_unitario.min' => 'El costo unitario no puede ser negativo.',
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
