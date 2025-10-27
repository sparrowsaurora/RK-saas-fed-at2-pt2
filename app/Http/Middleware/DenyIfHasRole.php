<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class DenyIfHasRole
{
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        $user = $request->user();

        // Only act if user is authenticated
        if ($user) {

            // If no roles were passed → deny anyone who has ANY role
            if (empty($roles)) {
                if ($user->roles()->exists()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Access denied — users with roles cannot access this route.',
                    ], 403);
                }
            } else {
                // If specific roles were passed → deny if user has ANY of them
                if ($user->hasAnyRole($roles)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Access denied — your role is not permitted here.',
                    ], 403);
                }
            }
        }

        return $next($request);
    }
}
