<?php

namespace App\Http\Controllers\Categorias;

use App\Http\Controllers\Controller;
use App\Http\Requests\Categorias\StoreCategoriaRequest;
use App\Http\Requests\Categorias\UpdateCategoriaRequest;
use App\Services\Categorias\CategoriaService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;

class CategoriaController extends Controller
{
    public function __construct(
        private CategoriaService $categoriaService
    ) {}

    public function index(Request $request): JsonResponse
    {
        try {
            $estado = $request->query('estado');
            $categorias = $this->categoriaService->getAll($estado);

            return response()->json($categorias);
        } catch (Throwable $e) {
            return response()->json([
                'error' => 'Error al obtener las categorías.',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function store(StoreCategoriaRequest $request): JsonResponse
    {
        try {
            $categoria = $this->categoriaService->create($request->validated());

            return response()->json($categoria, 201);
        } catch (Throwable $e) {
            return response()->json([
                'error' => 'Error al crear la categoría.',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function show(int $id): JsonResponse
    {
        try {
            $categoria = $this->categoriaService->getById($id);

            return response()->json($categoria);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'error' => 'Categoría no encontrada.',
                'message' => 'La categoría con el ID especificado no existe.',
            ], 404);
        } catch (Throwable $e) {
            return response()->json([
                'error' => 'Error al obtener la categoría.',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function update(UpdateCategoriaRequest $request, int $id): JsonResponse
    {
        try {
            $categoria = $this->categoriaService->update($id, $request->validated());

            return response()->json($categoria);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'error' => 'Categoría no encontrada.',
                'message' => 'La categoría con el ID especificado no existe.',
            ], 404);
        } catch (Throwable $e) {
            return response()->json([
                'error' => 'Error al actualizar la categoría.',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function destroy(int $id): JsonResponse
    {
        try {
            $this->categoriaService->delete($id);

            return response()->json(['message' => 'Categoría eliminada correctamente']);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'error' => 'Categoría no encontrada.',
                'message' => 'La categoría con el ID especificado no existe.',
            ], 404);
        } catch (Throwable $e) {
            return response()->json([
                'error' => 'Error al eliminar la categoría.',
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
