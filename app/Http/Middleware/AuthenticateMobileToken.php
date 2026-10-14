<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class AuthenticateMobileToken
{
    public function handle(Request $request, Closure $next): Response
    {
        $plainToken = $request->bearerToken();

        if ($plainToken === null || $plainToken === '') {
            return $this->unauthenticatedResponse();
        }

        $token = DB::table('mobile_access_tokens')
            ->where('token_hash', hash('sha256', $plainToken))
            ->first(['id_user']);

        $user = $token === null ? null : User::query()->find($token->id_user);

        if ($user === null) {
            return $this->unauthenticatedResponse();
        }

        if (! $user->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'Akun ini sedang dinonaktifkan.',
            ], 403);
        }

        $request->setUserResolver(static fn (): User => $user);

        return $next($request);
    }

    private function unauthenticatedResponse(): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => 'Token tidak valid atau tidak tersedia.',
        ], 401);
    }
}
