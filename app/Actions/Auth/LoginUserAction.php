<?php

namespace App\Actions\Auth;

use Illuminate\Support\Facades\Auth;

class LoginUserAction
{
    public function handle(array $data): ?string
    {
        if (Auth::attempt(['email' => $data['email'], 'password' => $data['password']])) {
            return Auth::user()->createToken('auth-token')->plainTextToken;
        }

        return null;
    }
}
