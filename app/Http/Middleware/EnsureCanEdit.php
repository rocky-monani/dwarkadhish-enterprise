<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureCanEdit
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! Auth::check() || ! Auth::user()->canEdit()) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Unauthorized action. Viewer role has read-only access.'], 403);
            }

            return redirect()->route('complaints.index')->with('error', 'Unauthorized action. Viewer role has read-only access.');
        }

        return $next($request);
    }
}
