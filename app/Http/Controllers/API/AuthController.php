<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\ForgotPasswordRequest;
use App\Http\Requests\ResetPasswordRequest;
use App\Http\Requests\UserLoginRequest;
use App\Http\Requests\UserRegisterRequest;
use App\Http\Resources\UserResource;
use App\Models\PasswordReset;
use App\Models\User;
use App\Notifications\PasswordResetNotification;
use Carbon\Carbon;
use Illuminate\Contracts\Hashing\Hasher;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

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

        $attributes['password'] = Hash::make($attributes['password']);

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
    public function login(UserLoginRequest $request)
    {
        // Validate User Request
        $request->validated();


        // If User email doesn't exist in the system or credentials doesn't match ( Can be Seperated Checks )
        $user = User::where('email', $request->get('email'))->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->error('No Record Found','Invalid Credentials', 401);
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

    /**
     * Request a password reset email.
     *
     * @param Hasher $hasher
     * @param ForgotPasswordRequest $request
     * @return JsonResponse
     * @throws ValidationException
     */
    public function forgot(ForgotPasswordRequest $request): JsonResponse
    {
        /** @var User $user */
        $user = ($query = User::query());

        $user = $user->where($query->qualifyColumn('email'), $request->input('email'))->first();

        // If no such user exists then throw an error
        if (!$user || !$user->email) {
            return response()->error('No Record Found','Incorrect Email Address Provided', 404);
        }

        // Generate a 4 digit random Token
        $resetPasswordToken = str_pad(random_int(1, 9999), 4, '0', STR_PAD_LEFT);

        // In Case User has already requested for forgot password don't create another record
        // Instead Update the existing token with the new token
        if (!$userPassReset = PasswordReset::where('email', $user->email)->first()) {
            // Store Token in DB with Token Expiration TIme i.e: 1 hour
            PasswordReset::create([
                'email' => $user->email,
                'token' => $resetPasswordToken,
            ]);
        } else {
            // Store Token in DB with Token Expiration TIme i.e: 1 hour
            $userPassReset->update([
                'email' => $user->email,
                'token' => $resetPasswordToken,
            ]);
        }

        // Send Notification to the user about the reset token
        $user->notify(
            new PasswordResetNotification(
                $user,
                $resetPasswordToken
            )
        );

        return new JsonResponse(['message' => 'A Code has been Sent to your Email Address.']);
    }

    /**
     * Perform a password reset.
     *
     * @param ResetPasswordRequest $request
     * @return JsonResponse
     * @throws ValidationException
     */
    public function reset(ResetPasswordRequest $request): JsonResponse
    {
        // Validate the request
        $attributes = $request->validated();

        $user = User::where('email', $attributes['email'])
                ->first();

        // Throw Exception if user is not found
        if (!$user) {
            return response()->error('No Record Found','Incorrect Email Address Provided', 404);
        }

        $resetRequest = PasswordReset::where('email', $user->email)->first();

        if (!$resetRequest || $resetRequest->token != $request->token) {
            return response()->error('An Error Occured. Please Try again.','Token mismatch.', 400);
        }

        // Update User's Password
        $user->fill([
            'password' => Hash::make($attributes['password']),
        ]);
        $user->save();

        // Delete previous all Tokens
        $user->tokens()->delete();

        $resetRequest->delete();

        // Get Token for Authenticated User
        $token = $user->createToken('authToken')->plainTextToken;

        // Create a Response
        $loginResponse = [
            'user' => UserResource::make($user),
            'token' => $token
        ];

        return response()->success(
            $loginResponse,
            'Password Reset Success',
            201
        );
    }
}
