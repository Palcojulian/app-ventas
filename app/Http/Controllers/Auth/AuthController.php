<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Services\Auth\AuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;

class AuthController extends Controller
{
    public function __construct(
        private AuthService $authService
    ) {}

    public function register(RegisterRequest $request): JsonResponse
    {
        try {
            $user = $this->authService->register($request->validated());
            $token = $user->createToken('auth-token')->plainTextToken;

            return response()->json([
                'success' => true,
                'message' => 'Usuario registrado exitosamente.',
                'token' => $token,
                'user' => $user,
            ], 201);
        } catch (Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al registrar el usuario.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function login(LoginRequest $request): JsonResponse
    {
        try {
            $result = $this->authService->login($request->validated());

            if (!$result) {
                return response()->json([
                    'success' => false,
                    'message' => 'Credenciales incorrectas.',
                ], 401);
            }

            return response()->json([
                'success' => true,
                'message' => 'Inicio de sesión exitoso.',
                'token' => $result['token'],
                'user' => $result['user'],
                
            ]);
        } catch (Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al iniciar sesión.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function logout(Request $request): JsonResponse
    {
        try {
            $request->user()->currentAccessToken()->delete();

            return response()->json([
                'success' => true,
                'message' => 'Sesión cerrada correctamente.',
            ]);
        } catch (Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al cerrar sesión.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function me(Request $request): JsonResponse
    {
        try {
            return response()->json([
                'success' => true,
                'data' => $request->user(),
            ]);
        } catch (Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener el usuario.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
