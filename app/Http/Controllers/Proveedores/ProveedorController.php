<?php

namespace App\Http\Controllers\Proveedores;

use App\Http\Controllers\Controller;
use App\Http\Requests\Proveedores\StoreProveedorRequest;
use App\Http\Requests\Proveedores\UpdateProveedorRequest;
use App\Services\Proveedores\ProveedorService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;

class ProveedorController extends Controller
{
    public function __construct(
        private ProveedorService $proveedorService
    ) {}

    public function index(Request $request): JsonResponse
    {
        try {
            $estado = $request->query('estado');
            $proveedores = $this->proveedorService->getAll($estado);

            return response()->json($proveedores);
        } catch (Throwable $e) {
            return response()->json([
                'error' => 'Error al obtener los proveedores.',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function store(StoreProveedorRequest $request): JsonResponse
    {
        try {
            $proveedor = $this->proveedorService->create($request->validated());

            return response()->json($proveedor, 201);
        } catch (Throwable $e) {
            return response()->json([
                'error' => 'Error al crear el proveedor.',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function show(int $id): JsonResponse
    {
        try {
            $proveedor = $this->proveedorService->getById($id);

            return response()->json($proveedor);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'error' => 'Proveedor no encontrado.',
                'message' => 'El proveedor con el ID especificado no existe.',
            ], 404);
        } catch (Throwable $e) {
            return response()->json([
                'error' => 'Error al obtener el proveedor.',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function update(UpdateProveedorRequest $request, int $id): JsonResponse
    {
        try {
            $proveedor = $this->proveedorService->update($id, $request->validated());

            return response()->json($proveedor);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'error' => 'Proveedor no encontrado.',
                'message' => 'El proveedor con el ID especificado no existe.',
            ], 404);
        } catch (Throwable $e) {
            return response()->json([
                'error' => 'Error al actualizar el proveedor.',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function destroy(int $id): JsonResponse
    {
        try {
            $this->proveedorService->delete($id);

            return response()->json(['message' => 'Proveedor eliminado correctamente']);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'error' => 'Proveedor no encontrado.',
                'message' => 'El proveedor con el ID especificado no existe.',
            ], 404);
        } catch (Throwable $e) {
            return response()->json([
                'error' => 'Error al eliminar el proveedor.',
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
