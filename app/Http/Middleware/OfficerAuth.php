<?php

namespace App\Http\Middleware;

use App\Models\MobileAccessToken;
use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class OfficerAuth
{
    /**
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $plainToken = $request->bearerToken();

        if ($plainToken === null) {
            return response()->json(['message' => 'Token autentikasi diperlukan.'], 401);
        }

        $token = MobileAccessToken::query()
            ->where('token_hash', hash('sha256', $plainToken))
            ->first(['id_user']);

        if ($token === null) {
            return response()->json(['message' => 'Token autentikasi tidak valid.'], 401);
        }

        $user = User::query()->find($token->id_user);

        if ($user === null || $user->is_active === false) {
            return response()->json(['message' => 'Akun tidak aktif atau tidak ditemukan.'], 401);
        }

        if (! $user->isSanitarian()) {
            return response()->json(['message' => 'Akses hanya tersedia untuk petugas sanitarian.'], 403);
        }

        $request->setUserResolver(static fn (): User => $user);

        return $next($request);
    }
}
