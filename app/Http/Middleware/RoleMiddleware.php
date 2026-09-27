<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    private const ROLE_ALIASES = [
        'admin' => ['admin', 'kepala_puskesmas'],
        'petugas' => ['petugas', 'sanitarian', 'staf_backup_kluster4'],
    ];

    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if ($user === null) {
            return redirect()->guest(route('login'));
        }

        $allowedRoles = [];
        foreach ($roles as $role) {
            $allowedRoles = array_merge($allowedRoles, self::ROLE_ALIASES[$role] ?? [$role]);
        }

        if (! in_array($user->role, $allowedRoles, true)) {
            abort(403, 'Anda tidak memiliki akses ke halaman ini.');
        }

        return $next($request);
    }
}
