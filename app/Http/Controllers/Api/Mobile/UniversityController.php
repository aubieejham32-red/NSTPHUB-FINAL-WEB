<?php

namespace App\Http\Controllers\Api\Mobile;

use App\Http\Controllers\Controller;
use App\Models\University;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

class UniversityController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Verify University Access Code
    |--------------------------------------------------------------------------
    |
    | PUBLIC
    |
    | POST /api/mobile/university/verify-access-code
    |
    */

    public function verifyAccessCode(
        Request $request
    ): JsonResponse {
        /*
        |--------------------------------------------------------------------------
        | Validate
        |--------------------------------------------------------------------------
        */

        $validated =
            $request->validate([
                'access_code' => [
                    'required',
                    'string',
                    'min:3',
                    'max:100',
                ],
            ]);


        /*
        |--------------------------------------------------------------------------
        | Normalize
        |--------------------------------------------------------------------------
        */

        $accessCode =
            strtoupper(
                trim(
                    $validated[
                        'access_code'
                    ]
                )
            );


        /*
        |--------------------------------------------------------------------------
        | Find University
        |--------------------------------------------------------------------------
        */

        $university =
            University::query()
                ->whereRaw(
                    'UPPER(access_code) = ?',
                    [
                        $accessCode,
                    ]
                )
                ->first();


        /*
        |--------------------------------------------------------------------------
        | Invalid Code
        |--------------------------------------------------------------------------
        */

        if (!$university) {
            return response()->json([
                'success' =>
                    false,

                'message' =>
                    'The university access code is invalid.',
            ], 422);
        }


        /*
        |--------------------------------------------------------------------------
        | Active University Required
        |--------------------------------------------------------------------------
        */

        if (
            strtoupper(
                trim(
                    (string)
                    $university->status
                )
            ) !==
            'ACTIVE'
        ) {
            return response()->json([
                'success' =>
                    false,

                'message' =>
                    'This university is currently inactive in NSTP HUB.',
            ], 403);
        }


        /*
        |--------------------------------------------------------------------------
        | Create Temporary University Access Session
        |--------------------------------------------------------------------------
        */

        $accessToken =
            Str::random(
                96
            );


        Cache::store('file')
            ->put(
                $this->universityAccessCacheKey(
                    $accessToken
                ),

                [
                    'university_id' =>
                        $university->id,
                ],

                now()->addMinutes(
                    15
                )
            );


        /*
        |--------------------------------------------------------------------------
        | Security Log
        |--------------------------------------------------------------------------
        |
        | Never log the access code itself.
        |
        */

        Log::info(
            'Mobile university access code verified.',
            [
                'university_id' =>
                    $university->id,
            ]
        );


        /*
        |--------------------------------------------------------------------------
        | Response
        |--------------------------------------------------------------------------
        */

        return response()->json([
            'success' =>
                true,

            'message' =>
                'University access code verified.',

            'university_access_token' =>
                $accessToken,

            'expires_in' =>
                900,

            'next_screen' =>
                'login',

            'university' =>
                $this->universityData(
                    request: $request,
                    university: $university
                ),
        ]);
    }


    /*
    |--------------------------------------------------------------------------
    | Current Student University
    |--------------------------------------------------------------------------
    |
    | GET /api/mobile/university
    |
    */

    public function current(
        Request $request
    ): JsonResponse {
        /*
        |--------------------------------------------------------------------------
        | Authenticated Student
        |--------------------------------------------------------------------------
        */

        $user =
            $request->user();


        if (!$user) {
            return response()->json([
                'success' =>
                    false,

                'message' =>
                    'Authentication is required.',
            ], 401);
        }


        /*
        |--------------------------------------------------------------------------
        | Mobile Student Account Required
        |--------------------------------------------------------------------------
        */

        if (
            !(
                $user instanceof User
            )
        ) {
            return response()->json([
                'success' =>
                    false,

                'message' =>
                    'This account is not authorized to use the NSTP HUB mobile application.',
            ], 403);
        }


        /*
        |--------------------------------------------------------------------------
        | Ensure Student Role
        |--------------------------------------------------------------------------
        */

        $this->ensureStudentRole(
            $user
        );


        /*
        |--------------------------------------------------------------------------
        | Student University
        |--------------------------------------------------------------------------
        */

        $university =
            $user
                ->university()
                ->first();


        /*
        |--------------------------------------------------------------------------
        | University Missing
        |--------------------------------------------------------------------------
        */

        if (!$university) {
            return response()->json([
                'success' =>
                    false,

                'message' =>
                    'Your student account is not connected to a university.',
            ], 404);
        }


        /*
        |--------------------------------------------------------------------------
        | University Must Be Active
        |--------------------------------------------------------------------------
        */

        if (
            strtoupper(
                trim(
                    (string)
                    $university->status
                )
            ) !==
            'ACTIVE'
        ) {
            return response()->json([
                'success' =>
                    false,

                'message' =>
                    'Your university is currently inactive in NSTP HUB.',
            ], 403);
        }


        /*
        |--------------------------------------------------------------------------
        | Response
        |--------------------------------------------------------------------------
        */

        return response()->json([
            'success' =>
                true,

            'university' =>
                $this->universityData(
                    request: $request,
                    university: $university
                ),
        ]);
    }


    /*
    |--------------------------------------------------------------------------
    | University Logo
    |--------------------------------------------------------------------------
    |
    | GET:
    |
    | /api/mobile/universities/{university}/logo
    |
    | This endpoint reads the real file from Laravel's public disk.
    |
    | It means React Native does NOT need to directly understand the
    | internal storage path saved in universities.logo.
    |
    */

    public function logo(
        University $university
    ): Response {
        /*
        |--------------------------------------------------------------------------
        | Database Logo
        |--------------------------------------------------------------------------
        */

        $logo =
            $university
                ->logo;


        if (!$logo) {
            abort(
                404,
                'University logo is not configured.'
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Normalize Storage Path
        |--------------------------------------------------------------------------
        */

        $path =
            $this->normalizeLogoPath(
                $logo
            );


        if (!$path) {
            abort(
                404,
                'University logo path is invalid.'
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Check Public Disk
        |--------------------------------------------------------------------------
        */

        if (
            !Storage::disk(
                'public'
            )
                ->exists(
                    $path
                )
        ) {
            Log::warning(
                'University logo file does not exist.',
                [
                    'university_id' =>
                        $university->id,

                    'database_logo' =>
                        $university->logo,

                    'normalized_path' =>
                        $path,
                ]
            );


            abort(
                404,
                'University logo file was not found.'
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Read File
        |--------------------------------------------------------------------------
        */

        $contents =
            Storage::disk(
                'public'
            )
                ->get(
                    $path
                );


        /*
        |--------------------------------------------------------------------------
        | Detect MIME Type
        |--------------------------------------------------------------------------
        */

        $mimeType =
            Storage::disk(
                'public'
            )
                ->mimeType(
                    $path
                )
            ??
            'image/png';


        /*
        |--------------------------------------------------------------------------
        | Return Image
        |--------------------------------------------------------------------------
        */

        return response(
            $contents,
            200,
            [
                'Content-Type' =>
                    $mimeType,

                'Cache-Control' =>
                    'public, max-age=3600',

                'Content-Disposition' =>
                    'inline',
            ]
        );
    }


    /*
    |--------------------------------------------------------------------------
    | University API Data
    |--------------------------------------------------------------------------
    |
    | access_code is NEVER returned.
    |
    */

    private function universityData(
        Request $request,
        University $university
    ): array {
        return [
            /*
            |--------------------------------------------------------------------------
            | Identity
            |--------------------------------------------------------------------------
            */

            'id' =>
                $university->id,

            'name' =>
                $university->name,

            'acronym' =>
                $university->acronym,

            'type' =>
                $university->type,

            'campus_type' =>
                $university->campus_type,


            /*
            |--------------------------------------------------------------------------
            | Contact
            |--------------------------------------------------------------------------
            */

            'email' =>
                $university->email,

            'contact_number' =>
                $university->contact_number,

            'website' =>
                $university->website,


            /*
            |--------------------------------------------------------------------------
            | Database Logo
            |--------------------------------------------------------------------------
            */

            'logo' =>
                $university->logo,


            /*
            |--------------------------------------------------------------------------
            | React Native Logo URL
            |--------------------------------------------------------------------------
            |
            | Example:
            |
            | http://192.168.254.117:8000
            | /api/mobile/universities/1/logo
            |
            | Laravel serves the actual image.
            |
            */

            'logo_url' =>
                $this->logoApiUrl(
                    request: $request,
                    university: $university
                ),


            /*
            |--------------------------------------------------------------------------
            | Address
            |--------------------------------------------------------------------------
            */

            'region' =>
                $university->region,

            'province' =>
                $university->province,

            'city' =>
                $university->city,

            'barangay' =>
                $university->barangay,

            'zip_code' =>
                $university->zip_code,

            'complete_address' =>
                $university->complete_address,


            /*
            |--------------------------------------------------------------------------
            | NSTP Configuration
            |--------------------------------------------------------------------------
            */

            'academic_year' =>
                $university->academic_year,

            'semester' =>
                $university->semester,

            'components' =>
                $university->components
                ??
                [],

            'max_students' =>
                $university->max_students,

            'status' =>
                $university->status,
        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Build Logo API URL
    |--------------------------------------------------------------------------
    */

    private function logoApiUrl(
        Request $request,
        University $university
    ): ?string {
        /*
        |--------------------------------------------------------------------------
        | No Logo
        |--------------------------------------------------------------------------
        */

        if (!$university->logo) {
            return null;
        }


        /*
        |--------------------------------------------------------------------------
        | Already External URL
        |--------------------------------------------------------------------------
        |
        | If universities.logo contains a complete HTTP/HTTPS URL,
        | return that URL directly.
        |
        */

        if (
            Str::startsWith(
                $university->logo,
                [
                    'http://',
                    'https://',
                ]
            )
        ) {
            return
                $university
                    ->logo;
        }


        /*
        |--------------------------------------------------------------------------
        | Laravel Logo API
        |--------------------------------------------------------------------------
        */

        return
            rtrim(
                $request
                    ->getSchemeAndHttpHost(),
                '/'
            )
            .
            '/api/mobile/universities/'
            .
            $university->id
            .
            '/logo';
    }


    /*
    |--------------------------------------------------------------------------
    | Normalize University Logo Path
    |--------------------------------------------------------------------------
    |
    | Handles common database values such as:
    |
    | universities/logo.png
    |
    | storage/universities/logo.png
    |
    | public/universities/logo.png
    |
    | public/storage/universities/logo.png
    |
    | storage/app/public/universities/logo.png
    |
    */

    private function normalizeLogoPath(
        string $logo
    ): ?string {
        /*
        |--------------------------------------------------------------------------
        | Normalize Windows Slashes
        |--------------------------------------------------------------------------
        */

        $path =
            str_replace(
                '\\',
                '/',
                trim(
                    $logo
                )
            );


        /*
        |--------------------------------------------------------------------------
        | Empty Path
        |--------------------------------------------------------------------------
        */

        if ($path === '') {
            return null;
        }


        /*
        |--------------------------------------------------------------------------
        | External URL
        |--------------------------------------------------------------------------
        |
        | The logo() endpoint is not needed for external files.
        |
        */

        if (
            Str::startsWith(
                $path,
                [
                    'http://',
                    'https://',
                ]
            )
        ) {
            return null;
        }


        /*
        |--------------------------------------------------------------------------
        | Remove Leading Slash
        |--------------------------------------------------------------------------
        */

        $path =
            ltrim(
                $path,
                '/'
            );


        /*
        |--------------------------------------------------------------------------
        | Remove Laravel Storage Prefixes
        |--------------------------------------------------------------------------
        */

        $prefixes = [
            'storage/app/public/',
            'public/storage/',
            'app/public/',
            'storage/',
            'public/',
        ];


        foreach (
            $prefixes
            as $prefix
        ) {
            if (
                Str::startsWith(
                    $path,
                    $prefix
                )
            ) {
                $path =
                    Str::after(
                        $path,
                        $prefix
                    );


                break;
            }
        }


        /*
        |--------------------------------------------------------------------------
        | Final Path
        |--------------------------------------------------------------------------
        */

        return
            ltrim(
                $path,
                '/'
            );
    }


    /*
    |--------------------------------------------------------------------------
    | University Access Cache Key
    |--------------------------------------------------------------------------
    */

    private function universityAccessCacheKey(
        string $token
    ): string {
        return
            'nstphub:mobile:university-access:'
            .
            hash(
                'sha256',
                $token
            );
    }


    /*
    |--------------------------------------------------------------------------
    | Ensure Student Role
    |--------------------------------------------------------------------------
    */

    private function ensureStudentRole(
        User $user
    ): void {
        $studentRole =
            Role::firstOrCreate([
                'name' =>
                    'student',

                'guard_name' =>
                    'web',
            ]);


        if (
            !$user->hasRole(
                'student'
            )
        ) {
            $user->assignRole(
                $studentRole
            );
        }
    }
}