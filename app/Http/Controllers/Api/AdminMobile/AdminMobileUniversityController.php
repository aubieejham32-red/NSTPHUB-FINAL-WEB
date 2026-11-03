<?php

namespace App\Http\Controllers\Api\AdminMobile;

use App\Http\Controllers\Controller;
use App\Models\University;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class AdminMobileUniversityController extends Controller
{
    public function verifyAccessCode(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'access_code' => [
                'required',
                'string',
                'min:3',
                'max:100',
            ],
        ]);

        $accessCode = strtoupper(
            trim($validated['access_code'])
        );

        $university = University::query()
            ->whereRaw(
                'UPPER(access_code) = ?',
                [$accessCode]
            )
            ->first();

        if (!$university) {
            return response()->json([
                'success' => false,
                'message' => 'The institutional authorization code is invalid.',
            ], 422);
        }

        if (strtoupper(trim((string) $university->status)) !== 'ACTIVE') {
            return response()->json([
                'success' => false,
                'message' => 'This institution is currently inactive in NSTP HUB.',
            ], 403);
        }

        /*
        |--------------------------------------------------------------------------
        | Temporary University Session
        |--------------------------------------------------------------------------
        |
        | The same university token can now be used by any supported admin-mobile
        | account type: University Administrator, Instructor, or Attendance
        | Coordinator. The exact role is resolved only during login.
        |
        */
        $accessToken = Str::random(96);

        Cache::store('file')->put(
            $this->cacheKey($accessToken),
            [
                'university_id' => (int) $university->id,
            ],
            now()->addMinutes(15)
        );

        return response()->json([
            'success' => true,
            'message' => 'Institution verified successfully.',
            'access_token' => $accessToken,
            'expires_in_seconds' => 900,
            'university' => [
                'id' => (int) $university->id,
                'name' => $university->name,
                'acronym' => $university->acronym,
                'academic_year' => $university->academic_year,
                'semester' => $university->semester,
                'status' => $university->status,
            ],
        ]);
    }

    private function cacheKey(string $token): string
    {
        return 'admin_mobile_university_access:' . hash('sha256', $token);
    }
}
