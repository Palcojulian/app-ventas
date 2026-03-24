<?php

namespace App\Http\Controllers\Productos;

use App\Http\Controllers\Controller;
use App\Http\Requests\Productos\ImportExcelRequest;
use App\Http\Requests\Productos\StoreProductoRequest;
use App\Http\Requests\Productos\UpdateProductoRequest;
use App\Services\Productos\ProductoService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;

class ProductoController extends Controller
{
    public function __construct(
        private ProductoService $productoService
    ) {}

    public function index(Request $request): JsonResponse
    {
        try {
            $estado = $request->query('estado');
            $productos = $this->productoService->getAll($estado);

            return response()->json($productos);
        } catch (Throwable $e) {
            return response()->json([
                'error' => 'Error al obtener los productos.',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function store(StoreProductoRequest $request): JsonResponse
    {
        try {
            $producto = $this->productoService->create($request->validated());

            return response()->json($producto, 201);
        } catch (Throwable $e) {
            return response()->json([
                'error' => 'Error al crear el producto.',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function show(int $id): JsonResponse
    {
        try {
            $producto = $this->productoService->getById($id);

            return response()->json($producto);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'error' => 'Producto no encontrado.',
                'message' => 'El producto con el ID especificado no existe.',
            ], 404);
        } catch (Throwable $e) {
            return response()->json([
                'error' => 'Error al obtener el producto.',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function update(UpdateProductoRequest $request, int $id): JsonResponse
    {
        try {
            $producto = $this->productoService->update($id, $request->validated());

            return response()->json($producto);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'error' => 'Producto no encontrado.',
                'message' => 'El producto con el ID especificado no existe.',
            ], 404);
        } catch (Throwable $e) {
            return response()->json([
                'error' => 'Error al actualizar el producto.',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function destroy(int $id): JsonResponse
    {
        try {
            $this->productoService->delete($id);

            return response()->json(['message' => 'Producto eliminado correctamente']);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'error' => 'Producto no encontrado.',
                'message' => 'El producto con el ID especificado no existe.',
            ], 404);
        } catch (Throwable $e) {
            return response()->json([
                'error' => 'Error al eliminar el producto.',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function importarProductos(ImportExcelRequest $request)
    {
        try {
            $this->productoService->importar($request->file('archivo'));
            return response()->json(['message' => 'Acción realizada con exito'], 200);
        } catch (Throwable $e) {
            return response()->json([
                'error' => 'Error al eliminar el producto.',
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
