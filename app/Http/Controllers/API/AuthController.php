<?php

namespace App\Http\Controllers\API;

use App\Models\User;
use App\Services\UserService;
use Illuminate\Http\Response;
use App\Services\AuthService;
use App\Http\Controllers\Controller;
use App\Http\Requests\API\User\LoginRequest;
use App\Http\Requests\API\User\RegisterRequest;
use App\Http\Requests\API\User\AuthenticatedRequest;
use App\Http\Requests\API\User\ResendVerifyEmailRequest;

class AuthController extends Controller
{
    public function login(LoginRequest $request, AuthService $authService)
    {
        if ( ! $authService->attemptLogin($request->input('email'), $request->input('password'))) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Invalid login details'
            ], Response::HTTP_UNAUTHORIZED);
        }

        /** @var User $user */
        $user = auth()->user();

        if ( ! $user->hasVerifiedEmail()) {
            return response()->json([
                'status'  => 'error',
                'message' => 'User has not verified email'
            ], Response::HTTP_UNAUTHORIZED);
        }

        $token = $authService->createToken($user);

        return response()->json([
            'status' => 'success',
            'data'   => [
                'user'  => $user,
                'token' => $token->accessToken
            ]
        ]);
    }

    public function register(RegisterRequest $request, UserService $userService)
    {
        if ($userService->exists($request->input('email'))) {
            return response()->json([
                'status'  => 'error',
                'message' => 'User already exists with this email'
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $user = $userService->create($request->all());

        return response()->json([
            'status' => 'success',
            'data'   => [
                'user' => $user
            ]
        ]);
    }

    public function logout(AuthenticatedRequest $request, AuthService $authService)
    {
        /** @var User $user */
        $user = auth()->user();

        $authService->revokeAllTokens($user);

        return response()->json([
            'status' => 'success'
        ]);
    }

    public function resendVerifyEmail(ResendVerifyEmailRequest $request, UserService $userService)
    {
        /** @var User $user */
        $user = $userService->exists($request->input('email'));

        if ($user && ! $user->hasVerifiedEmail()) {
            $user->sendEmailVerificationNotification();
        }

        return response()->json([
            'status' => 'success',
            'data'   => [
                'message' => 'Verification email sent if user exists'
            ]
        ]);
    }
}
