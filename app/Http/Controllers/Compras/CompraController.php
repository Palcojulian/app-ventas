<?php

namespace App\Http\Controllers\Compras;

use App\Http\Controllers\Controller;
use App\Http\Requests\Compras\StoreCompraRequest;
use App\Http\Requests\Compras\UpdateCompraRequest;
use App\Services\Compras\CompraService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Throwable;

class CompraController extends Controller
{
    public function __construct(
        private CompraService $compraService
    ) {}

    public function index(Request $request): JsonResponse
    {
        try {
            $estado = $request->query('estado');
            $compras = $this->compraService->getAll($estado);

            return response()->json($compras);
        } catch (Throwable $e) {
            return response()->json([
                'error' => 'Error al obtener las compras.',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function store(StoreCompraRequest $request): JsonResponse
    {
        try {
            $compra = $this->compraService->create($request->validated(), Auth::id());
            $compra->load(['proveedor', 'detalleCompras.producto']);

            return response()->json($compra, 201);
        } catch (Throwable $e) {
            return response()->json([
                'error' => 'Error al crear la compra.',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function show(int $id): JsonResponse
    {
        try {
            $compra = $this->compraService->getById($id);

            return response()->json($compra);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'error' => 'Compra no encontrada.',
                'message' => 'La compra con el ID especificado no existe.',
            ], 404);
        } catch (Throwable $e) {
            return response()->json([
                'error' => 'Error al obtener la compra.',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function update(UpdateCompraRequest $request, int $id): JsonResponse
    {
        try {
            $compra = $this->compraService->update($id, $request->validated(), Auth::id());
            $compra->load(['proveedor', 'detalleCompras.producto']);

            return response()->json($compra);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'error' => 'Compra no encontrada.',
                'message' => 'La compra con el ID especificado no existe.',
            ], 404);
        } catch (Throwable $e) {
            return response()->json([
                'error' => 'Error al actualizar la compra.',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function destroy(int $id): JsonResponse
    {
        try {
            $this->compraService->delete($id, Auth::id());

            return response()->json(['message' => 'Compra eliminada correctamente']);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'error' => 'Compra no encontrada.',
                'message' => 'La compra con el ID especificado no existe.',
            ], 404);
        } catch (Throwable $e) {
            return response()->json([
                'error' => 'Error al eliminar la compra.',
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
