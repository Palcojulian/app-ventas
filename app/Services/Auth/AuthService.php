<?php

namespace App\Services\Auth;

use App\Actions\Auth\LoginUserAction;
use App\Actions\Auth\RegisterUserAction;
use App\Models\User;

class AuthService
{
    public function __construct(
        private RegisterUserAction $registerUserAction,
        private LoginUserAction $loginUserAction,
    ) {}

    public function register(array $data): User
    {
        return $this->registerUserAction->handle($data);
    }

    public function login(array $data): ?string
    {
        return $this->loginUserAction->handle($data);
    }

    public function logout(User $user): void
    {
        $user->currentAccessToken()->delete();
    }
}
