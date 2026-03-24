<?php

namespace App\Http\Requests\Productos;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdateProductoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $productoId = $this->route('id');

        return [
            'codigo' => 'sometimes|string|max:50|unique:productos,codigo,'.$productoId,
            'nombre' => 'sometimes|string|max:255',
            'descripcion' => 'nullable|string',
            'id_categoria' => 'sometimes|exists:categorias,id',
            'precio_venta' => 'sometimes|numeric|min:0',
            'costo' => 'nullable|numeric|min:0',
            'stock_actual' => 'nullable|integer|min:0',
            'stock_minimo' => 'nullable|integer|min:0',
            'unidad_medida' => 'nullable|string|max:50',
            'estado' => 'nullable|string|in:activo,inactivo',
        ];
    }

    public function messages(): array
    {
        return [
            'codigo.string' => 'El código debe ser una cadena de texto.',
            'codigo.max' => 'El código no puede exceder los 50 caracteres.',
            'codigo.unique' => 'El código ya está en uso.',
            'nombre.string' => 'El nombre debe ser una cadena de texto.',
            'nombre.max' => 'El nombre no puede exceder los 255 caracteres.',
            'descripcion.string' => 'La descripción debe ser una cadena de texto.',
            'id_categoria.exists' => 'La categoría seleccionada no existe.',
            'precio_venta.numeric' => 'El precio de venta debe ser un número.',
            'precio_venta.min' => 'El precio de venta no puede ser negativo.',
            'costo.numeric' => 'El costo debe ser un número.',
            'costo.min' => 'El costo no puede ser negativo.',
            'stock_actual.integer' => 'El stock actual debe ser un número entero.',
            'stock_actual.min' => 'El stock actual no puede ser negativo.',
            'stock_minimo.integer' => 'El stock mínimo debe ser un número entero.',
            'stock_minimo.min' => 'El stock mínimo no puede ser negativo.',
            'unidad_medida.string' => 'La unidad de medida debe ser una cadena de texto.',
            'unidad_medida.max' => 'La unidad de medida no puede exceder los 50 caracteres.',
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
