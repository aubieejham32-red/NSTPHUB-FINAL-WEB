<?php

namespace App\Http\Middleware;

use App\Models\Coordinator;
use App\Models\Instructor;
use App\Models\UniversityAdministrator;
use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureMobileUniversityAdmin
{
    public function handle(Request $request, Closure $next): Response|JsonResponse
    {
        $account = $request->user();

        $allowed =
            $account instanceof UniversityAdministrator
            || ($account instanceof Instructor && $account->isInstructor())
            || ($account instanceof Coordinator && $account->isAttendanceCoordinator());

        if (!$allowed) {
            return response()->json([
                'success' => false,
                'message' => 'This endpoint is available only to University Administrators, Instructors, and Attendance Coordinators.',
            ], 403);
        }

        if ($account instanceof Instructor || $account instanceof Coordinator) {
            if (strtoupper(trim((string) $account->status)) !== 'ACTIVE') {
                return response()->json([
                    'success' => false,
                    'message' => 'Your staff account is currently inactive.',
                ], 403);
            }
        }

        $account->loadMissing('university');

        if (
            !$account->university ||
            strtoupper(trim((string) $account->university->status)) !== 'ACTIVE'
        ) {
            return response()->json([
                'success' => false,
                'message' => 'Your institution is currently inactive in NSTP HUB.',
            ], 403);
        }

        $token = $account->currentAccessToken();

        if (
            $token &&
            method_exists($token, 'can') &&
            !$token->can('admin-mobile')
        ) {
            return response()->json([
                'success' => false,
                'message' => 'This authentication token cannot access the admin mobile API.',
            ], 403);
        }

        return $next($request);
    }
}
