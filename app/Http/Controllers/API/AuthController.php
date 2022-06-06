<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\UserLoginRequest;
use App\Http\Requests\UserRegisterRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    /**
     * @param UserRegisterRequest $request
     * @return JsonResponse
     */
    public function register(UserRegisterRequest $request) : JsonResponse
    {
        // Validate Register Request
        $request->validated();

        // Get Only Fill-able Attributes from the request
        $attributes = $request->only(app(User::class)->getFillable());

        // Create a New User
        $user = User::create($attributes);

        // Get Token for Authenticated User
        $token = $user->createToken('authToken')->plainTextToken;

        // Create a Response for Registered
        $loginResponse = [
            'user' => UserResource::make($user),
            'token' => $token
        ];

        return response()->success(
            $loginResponse,
            'User Registered',
        201);
    }

    /**
     * @param UserLoginRequest $request
     * @return JsonResponse
     */
    public function login(UserLoginRequest $request) : JsonResponse
    {
        // Validate User Request
        $request->validated();

        // If User email doesn't exist in the system or credentials doesn't match ( Can be Seperated Checks )
        $user = User::where('email', $request->get('email'))->first();

        if (!$user || Hash::check($request->password, $user->password)) {
            return response()->error('Bad Credentials', 401);
        }

        // Get Token for Authenticated User
        $token = $user->createToken('authToken')->plainTextToken;

        // Create a Response for Registered
        $loginResponse = [
            'user' => UserResource::make($user),
            'token' => $token
        ];

        return response()->success($loginResponse, 201);
    }

    /**
     * @return JsonResponse
     */
    public function logout() : JsonResponse
    {
        // Delete Current user Token
        auth()->user()->tokens()->delete();

        return response()->success([], 'Logged out', 200);
    }
}
