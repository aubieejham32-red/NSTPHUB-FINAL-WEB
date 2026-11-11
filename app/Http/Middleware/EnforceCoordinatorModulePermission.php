<?php

namespace App\Http\Middleware;

use App\Models\Coordinator;
use App\Support\CoordinatorModulePermissions;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class EnforceCoordinatorModulePermission
{
    public function handle(Request $request, Closure $next, string $module): Response
    {
        // The route's auth middleware has already selected the actual guard.
        // Do not change instructor or university administrator permissions.
        $actor = $request->user();

        if (
            $actor instanceof Coordinator
            && !CoordinatorModulePermissions::canManage($actor, $module)
        ) {
            abort(403, 'This module is view-only for your assigned coordinator role.');
        }

        return $next($request);
    }
}
