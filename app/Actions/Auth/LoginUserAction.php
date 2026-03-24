<?php

namespace App\Actions\Auth;

use App\Models\User;
use Illuminate\Support\Facades\Hash;

class LoginUserAction
{
    public function handle(array $data): ?string
    {
        $user = User::where('email', $data['email'])->first();

        if (! $user || ! Hash::check($data['password'], $user->password)) {
            return null;
        }

        return $user->createToken('auth-token')->plainTextToken;
    }
}
