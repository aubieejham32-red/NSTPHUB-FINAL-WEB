<?php

namespace App\Http\Controllers\Api\Mobile;

use App\Http\Controllers\Controller;
use App\Models\User;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\RoundBlockSizeMode;
use Endroid\QrCode\Writer\PngWriter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Throwable;

class DigitalStudentIdController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Digital Student ID Information
    |--------------------------------------------------------------------------
    |
    | GET
    |
    | /api/mobile/digital-student-id
    |
    */

    public function show(
        Request $request
    ): JsonResponse {
        try {

            /*
            |--------------------------------------------------------------------------
            | Authenticated Student
            |--------------------------------------------------------------------------
            */

            $student =
                $request->user();


            if (
                !(
                    $student instanceof User
                )
            ) {
                return response()->json([
                    'success' =>
                        false,

                    'message' =>
                        'Authenticated student account could not be found.',
                ], 401);
            }


            /*
            |--------------------------------------------------------------------------
            | Student Authorization
            |--------------------------------------------------------------------------
            */

            if (
                !$student->isStudent()
            ) {
                return response()->json([
                    'success' =>
                        false,

                    'message' =>
                        'This account is not authorized as an NSTP student.',
                ], 403);
            }


            /*
            |--------------------------------------------------------------------------
            | Load University
            |--------------------------------------------------------------------------
            */

            $student->load(
                'university'
            );


            $university =
                $student->university;


            if (
                !$university
            ) {
                return response()->json([
                    'success' =>
                        false,

                    'message' =>
                        'Your student account is not connected to a university.',
                ], 409);
            }


            /*
            |--------------------------------------------------------------------------
            | Student ID Number
            |--------------------------------------------------------------------------
            */

            $studentIdNumber =
                trim(
                    (string)
                    (
                        $student->student_id_number
                        ??
                        ''
                    )
                );


            /*
            |--------------------------------------------------------------------------
            | QR Token Status
            |--------------------------------------------------------------------------
            |
            | IMPORTANT:
            |
            | qr_token stays on Laravel.
            |
            | It is NEVER returned directly to React Native.
            |
            */

            $hasQrToken =
                filled(
                    $student->qr_token
                );


            $credentialsReady =
                filled(
                    $studentIdNumber
                )
                &&
                $hasQrToken;


            /*
            |--------------------------------------------------------------------------
            | Student Photo URL
            |--------------------------------------------------------------------------
            */

            $profilePhotoUrl =
                $this->publicStorageUrl(
                    request:
                        $request,

                    path:
                        $student->profile_photo
                );


            /*
            |--------------------------------------------------------------------------
            | University Logo URL
            |--------------------------------------------------------------------------
            */

            $universityLogoUrl =
                $this->absoluteRouteUrl(
                    request:
                        $request,

                    routeName:
                        'api.mobile.university.logo',

                    parameters: [
                        'university' =>
                            $university->id,
                    ]
                );


            /*
            |--------------------------------------------------------------------------
            | Protected QR URL
            |--------------------------------------------------------------------------
            */

            $qrCodeUrl =
                $credentialsReady
                    ? $this->absoluteRouteUrl(
                        request:
                            $request,

                        routeName:
                            'api.mobile.digital-student-id.qr-code'
                    )
                    : null;


            /*
            |--------------------------------------------------------------------------
            | Successful JSON Response
            |--------------------------------------------------------------------------
            */

            return response()->json([
                'success' =>
                    true,

                'message' =>
                    'Digital Student ID loaded successfully.',


                /*
                |--------------------------------------------------------------------------
                | Credential Status
                |--------------------------------------------------------------------------
                */

                'credentials_ready' =>
                    $credentialsReady,

                'qr_available' =>
                    $credentialsReady,

                'qr_code_url' =>
                    $qrCodeUrl,


                /*
                |--------------------------------------------------------------------------
                | Student
                |--------------------------------------------------------------------------
                */

                'student' => [

                    'id' =>
                        $student->id,


                    /*
                    |--------------------------------------------------------------------------
                    | Student ID Number
                    |--------------------------------------------------------------------------
                    */

                    'student_id_number' =>
                        $studentIdNumber !== ''
                            ? $studentIdNumber
                            : null,


                    /*
                    |--------------------------------------------------------------------------
                    | Name
                    |--------------------------------------------------------------------------
                    */

                    'name' =>
                        $student->name,

                    'full_name' =>
                        $student->full_name,

                    'surname' =>
                        $student->surname,

                    'first_name' =>
                        $student->first_name,

                    'middle_name' =>
                        $student->middle_name,


                    /*
                    |--------------------------------------------------------------------------
                    | Account
                    |--------------------------------------------------------------------------
                    */

                    'email' =>
                        $student->email,


                    /*
                    |--------------------------------------------------------------------------
                    | NSTP
                    |--------------------------------------------------------------------------
                    */

                    'component' =>
                        strtoupper(
                            trim(
                                (string)
                                (
                                    $student->component
                                    ??
                                    ''
                                )
                            )
                        ),

                    'component_name' =>
                        $this->componentName(
                            $student->component
                        ),

                    'subject' =>
                        $student->subject,

                    'term' =>
                        $student->term,


                    /*
                    |--------------------------------------------------------------------------
                    | Academic Information
                    |--------------------------------------------------------------------------
                    */

                    'course' =>
                        $student->course,

                    'year_level' =>
                        $student->year_level,

                    'section' =>
                        $student->section,


                    /*
                    |--------------------------------------------------------------------------
                    | Personal Information
                    |--------------------------------------------------------------------------
                    */

                    'gender' =>
                        $student->gender,


                    /*
                    |--------------------------------------------------------------------------
                    | Profile Photo
                    |--------------------------------------------------------------------------
                    */

                    'profile_photo_url' =>
                        $profilePhotoUrl,
                ],


                /*
                |--------------------------------------------------------------------------
                | University
                |--------------------------------------------------------------------------
                */

                'university' => [

                    'id' =>
                        $university->id,

                    'name' =>
                        $university->name,

                    'acronym' =>
                        $university->acronym,

                    'academic_year' =>
                        $university->academic_year,

                    'semester' =>
                        $university->semester,

                    'logo_url' =>
                        $universityLogoUrl,
                ],
            ]);

        } catch (
            Throwable $exception
        ) {

            /*
            |--------------------------------------------------------------------------
            | Log Actual Laravel Error
            |--------------------------------------------------------------------------
            */

            Log::error(
                'Digital Student ID API failed.',
                [
                    'message' =>
                        $exception->getMessage(),

                    'file' =>
                        $exception->getFile(),

                    'line' =>
                        $exception->getLine(),

                    'user_id' =>
                        optional(
                            $request->user()
                        )->id,
                ]
            );


            /*
            |--------------------------------------------------------------------------
            | IMPORTANT
            |--------------------------------------------------------------------------
            |
            | Always return JSON.
            |
            | This prevents React Native from receiving a Laravel HTML error page
            | and then failing with:
            |
            | "The NSTP HUB server returned an invalid response."
            |
            */

            return response()->json([
                'success' =>
                    false,

                'message' =>
                    config(
                        'app.debug'
                    )
                        ? 'Digital Student ID error: '
                            .
                            $exception->getMessage()
                        : 'Unable to load your Digital Student ID.',
            ], 500);
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Protected Student QR Code
    |--------------------------------------------------------------------------
    |
    | GET
    |
    | /api/mobile/digital-student-id/qr-code
    |
    */

    public function qrCode(
        Request $request
    ): Response {
        /*
        |--------------------------------------------------------------------------
        | Student
        |--------------------------------------------------------------------------
        */

        $student =
            $request->user();


        if (
            !(
                $student instanceof User
            )
        ) {
            abort(
                401,
                'Authenticated student account could not be found.'
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Student Authorization
        |--------------------------------------------------------------------------
        */

        if (
            !$student->isStudent()
        ) {
            abort(
                403,
                'This account is not authorized as an NSTP student.'
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Student ID Number Required
        |--------------------------------------------------------------------------
        */

        if (
            blank(
                $student->student_id_number
            )
        ) {
            abort(
                404,
                'Your Student ID number has not been generated yet.'
            );
        }


        /*
        |--------------------------------------------------------------------------
        | QR Token Required
        |--------------------------------------------------------------------------
        */

        if (
            blank(
                $student->qr_token
            )
        ) {
            abort(
                404,
                'Your QR credentials have not been generated yet.'
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Secure Attendance Payload
        |--------------------------------------------------------------------------
        |
        | Never send qr_token separately to the mobile app.
        |
        */

        $payload =
            'NSTPHUB:ATTENDANCE:'
            .
            $student->qr_token;


        /*
        |--------------------------------------------------------------------------
        | Build QR
        |--------------------------------------------------------------------------
        */

        $qrCode =
            new QrCode(
                data:
                    $payload,

                encoding:
                    new Encoding(
                        'UTF-8'
                    ),

                errorCorrectionLevel:
                    ErrorCorrectionLevel::High,

                size:
                    420,

                margin:
                    12,

                roundBlockSizeMode:
                    RoundBlockSizeMode::Margin
            );


        /*
        |--------------------------------------------------------------------------
        | PNG Writer
        |--------------------------------------------------------------------------
        */

        $writer =
            new PngWriter();


        $result =
            $writer->write(
                $qrCode
            );


        /*
        |--------------------------------------------------------------------------
        | Return PNG
        |--------------------------------------------------------------------------
        */

        return response(
            $result->getString(),

            200,

            [
                'Content-Type' =>
                    $result->getMimeType(),

                'Cache-Control' =>
                    'private, no-store, no-cache, must-revalidate',

                'Pragma' =>
                    'no-cache',

                'Expires' =>
                    '0',
            ]
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Component Display Name
    |--------------------------------------------------------------------------
    */

    private function componentName(
        ?string $component
    ): string {
        $component =
            strtoupper(
                trim(
                    (string)
                    $component
                )
            );


        return match (
            $component
        ) {
            'ROTC' =>
                "ROTC - Reserve Officers' Training Corps",

            'CWTS' =>
                'CWTS - Civic Welfare Training Service',

            'LTS' =>
                'LTS - Literacy Training Service',

            default =>
                'National Service Training Program',
        };
    }


    /*
    |--------------------------------------------------------------------------
    | Public Storage URL
    |--------------------------------------------------------------------------
    */

    private function publicStorageUrl(
        Request $request,
        ?string $path
    ): ?string {
        $path =
            trim(
                (string)
                $path
            );


        if (
            $path ===
            ''
        ) {
            return null;
        }


        /*
        |--------------------------------------------------------------------------
        | Already Absolute URL
        |--------------------------------------------------------------------------
        */

        if (
            str_starts_with(
                $path,
                'http://'
            )
            ||
            str_starts_with(
                $path,
                'https://'
            )
        ) {
            return $path;
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
        | Already /storage/... Path
        |--------------------------------------------------------------------------
        */

        if (
            str_starts_with(
                $path,
                'storage/'
            )
        ) {
            return
                $request
                    ->getSchemeAndHttpHost()
                .
                '/'
                .
                $path;
        }


        /*
        |--------------------------------------------------------------------------
        | Laravel Public Disk URL
        |--------------------------------------------------------------------------
        */

        $relativeUrl =
            Storage::disk(
                'public'
            )
                ->url(
                    $path
                );


        /*
        |--------------------------------------------------------------------------
        | Make URL Reachable From Phone
        |--------------------------------------------------------------------------
        */

        return
            $request
                ->getSchemeAndHttpHost()
            .
            '/'
            .
            ltrim(
                $relativeUrl,
                '/'
            );
    }


    /*
    |--------------------------------------------------------------------------
    | Absolute Route URL
    |--------------------------------------------------------------------------
    |
    | IMPORTANT:
    |
    | We deliberately use the host from the incoming phone request instead of
    | APP_URL.
    |
    | If the phone requested:
    |
    | http://192.168.254.117:8000
    |
    | Laravel returns URLs using:
    |
    | http://192.168.254.117:8000
    |
    | instead of localhost.
    |
    */

    private function absoluteRouteUrl(
        Request $request,
        string $routeName,
        array $parameters = []
    ): string {
        $relativeRoute =
            route(
                $routeName,
                $parameters,
                false
            );


        return
            $request
                ->getSchemeAndHttpHost()
            .
            $relativeRoute;
    }
}