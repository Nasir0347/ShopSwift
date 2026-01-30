<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\AuthService;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    use ApiResponse;

    protected $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    public function register(Request $request)
    {
        $validated = $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ]);

        try {
            $result = $this->authService->register($validated);
            return $this->success($result, 'Registration successful', 201);
        } catch (\Exception $e) {
            return $this->error('Registration failed: ' . $e->getMessage(), 500);
        }
    }

    public function login(Request $request)
    {
        $validated = $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        try {
            $result = $this->authService->login($validated);
            return $this->success($result, 'Login successful');
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), 401);
        }
    }

    public function logout(Request $request)
    {
        $this->authService->logout($request->user());
        return $this->success([], 'Logged out successfully');
    }

    public function me(Request $request)
    {
        return $this->success($request->user(), 'User profile');
    }
}
