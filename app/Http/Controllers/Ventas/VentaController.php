<?php

namespace App\Http\Controllers\Ventas;

use App\Http\Controllers\Controller;
use App\Http\Requests\Ventas\StoreVentaRequest;
use App\Http\Requests\Ventas\UpdateVentaRequest;
use App\Services\Ventas\VentaService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Throwable;

class VentaController extends Controller
{
    public function __construct(
        private VentaService $ventaService
    ) {}

    public function index(Request $request): JsonResponse
    {
        try {
            $estado = $request->query('estado');
            $ventas = $this->ventaService->getAll($estado);

            return response()->json($ventas);
        } catch (Throwable $e) {
            return response()->json([
                'error' => 'Error al obtener las ventas.',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function store(StoreVentaRequest $request): JsonResponse
    {
        try {
            $venta = $this->ventaService->create($request->validated(), Auth::id());
            $venta->load(['cliente', 'usuario', 'detalleVentas.producto']);

            return response()->json($venta, 201);
        } catch (Throwable $e) {
            return response()->json([
                'error' => 'Error al crear la venta.',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function show(int $id): JsonResponse
    {
        try {
            $venta = $this->ventaService->getById($id);

            return response()->json($venta);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'error' => 'Venta no encontrada.',
                'message' => 'La venta con el ID especificado no existe.',
            ], 404);
        } catch (Throwable $e) {
            return response()->json([
                'error' => 'Error al obtener la venta.',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function update(UpdateVentaRequest $request, int $id): JsonResponse
    {
        try {
            $venta = $this->ventaService->update($id, $request->validated(), Auth::id());
            $venta->load(['cliente', 'usuario', 'detalleVentas.producto']);

            return response()->json($venta);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'error' => 'Venta no encontrada.',
                'message' => 'La venta con el ID especificado no existe.',
            ], 404);
        } catch (Throwable $e) {
            return response()->json([
                'error' => 'Error al actualizar la venta.',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function destroy(int $id): JsonResponse
    {
        try {
            $this->ventaService->delete($id, Auth::id());

            return response()->json(['message' => 'Venta eliminada correctamente']);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'error' => 'Venta no encontrada.',
                'message' => 'La venta con el ID especificado no existe.',
            ], 404);
        } catch (Throwable $e) {
            return response()->json([
                'error' => 'Error al eliminar la venta.',
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
