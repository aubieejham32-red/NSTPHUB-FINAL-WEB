<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Mail\UniversityCredentialsMail;
use App\Models\University;
use App\Models\UniversityAdministrator;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

class AddUniversityController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Store New University
    |--------------------------------------------------------------------------
    */

    public function store(Request $request)
    {
        /*
        |--------------------------------------------------------------------------
        | Normalize Components
        |--------------------------------------------------------------------------
        |
        | The Vue page sends components as JSON inside FormData.
        | Convert it into an array before validation.
        |
        */

        $components = $request->input('components');

        if (is_string($components)) {
            $decodedComponents = json_decode($components, true);

            if (json_last_error() === JSON_ERROR_NONE) {
                $components = $decodedComponents;
            }
        }

        if (!is_array($components)) {
            $components = [];
        }

        $components = array_values(
            array_unique(
                array_filter(
                    array_map(
                        static fn ($component) =>
                            strtoupper(trim((string) $component)),
                        $components
                    )
                )
            )
        );

        $request->merge([
            'components' => $components,
        ]);


        /*
        |--------------------------------------------------------------------------
        | Recover A Previously Completed Request
        |--------------------------------------------------------------------------
        |
        | This handles cases where the university was already successfully
        | created but the frontend did not receive the success response.
        |
        | Instead of returning 422 because the email/username already exists,
        | the existing university is returned so the wizard can continue to
        | the Generated step.
        |
        */

        $normalizedUniversityEmail =
            strtolower(
                trim(
                    (string) $request->input('email')
                )
            );

        $normalizedAdminEmail =
            strtolower(
                trim(
                    (string) $request->input('admin_email')
                )
            );

        $normalizedAdminUsername =
            trim(
                (string) $request->input('admin_username')
            );


        if (
            $normalizedUniversityEmail !== '' &&
            $normalizedAdminEmail !== '' &&
            $normalizedAdminUsername !== ''
        ) {
            $existingUniversity =
                University::query()
                    ->whereRaw(
                        'LOWER(email) = ?',
                        [
                            $normalizedUniversityEmail,
                        ]
                    )
                    ->first();


            $existingAdministrator =
                UniversityAdministrator::query()
                    ->whereRaw(
                        'LOWER(email) = ?',
                        [
                            $normalizedAdminEmail,
                        ]
                    )
                    ->where(
                        'username',
                        $normalizedAdminUsername
                    )
                    ->first();


            $sameUniversity =
                $existingUniversity &&
                $existingAdministrator &&
                (int) $existingAdministrator->university_id ===
                (int) $existingUniversity->id;


            $sameIdentity =
                $sameUniversity &&

                strcasecmp(
                    trim(
                        (string) $existingUniversity->name
                    ),
                    trim(
                        (string) $request->input('name')
                    )
                ) === 0 &&

                strcasecmp(
                    trim(
                        (string) $existingUniversity->acronym
                    ),
                    trim(
                        (string) $request->input('acronym')
                    )
                ) === 0 &&

                strcasecmp(
                    trim(
                        (string) $existingAdministrator->first_name
                    ),
                    trim(
                        (string) $request->input('admin_first_name')
                    )
                ) === 0 &&

                strcasecmp(
                    trim(
                        (string) $existingAdministrator->last_name
                    ),
                    trim(
                        (string) $request->input('admin_last_name')
                    )
                ) === 0;


            if ($sameIdentity) {
                return response()->json(
                    [
                        'success' =>
                            true,

                        'already_exists' =>
                            true,

                        'email_sent' =>
                            false,

                        'message' =>
                            'This university was already created. Existing generated information was loaded.',

                        'warning' =>
                            'No duplicate record was created.',


                        /*
                        |--------------------------------------------------------------------------
                        | University Information
                        |--------------------------------------------------------------------------
                        */

                        'university_id' =>
                            $existingUniversity->id,

                        'access_code' =>
                            $existingUniversity->access_code,

                        'portal_url' =>
                            route(
                                'university-admin.login'
                            ),

                        'pdf_url' =>
                            route(
                                'superadmin.university.pdf',
                                [
                                    'university' =>
                                        $existingUniversity->id,
                                ]
                            ),


                        'university' =>
                            [
                                'id' =>
                                    $existingUniversity->id,

                                'name' =>
                                    $existingUniversity->name,

                                'acronym' =>
                                    $existingUniversity->acronym,

                                'email' =>
                                    $existingUniversity->email,

                                'logo_url' =>
                                    $existingUniversity->logo_url,
                            ],


                        /*
                        |--------------------------------------------------------------------------
                        | Administrator Information
                        |--------------------------------------------------------------------------
                        */

                        'administrator' =>
                            [
                                'id' =>
                                    $existingAdministrator->id,

                                'email' =>
                                    $existingAdministrator->email,

                                'username' =>
                                    $existingAdministrator->username,

                                'full_name' =>
                                    $existingAdministrator->full_name,
                            ],
                    ],
                    200
                );
            }
        }


        /*
        |--------------------------------------------------------------------------
        | Validation
        |--------------------------------------------------------------------------
        */

        $validator =
            Validator::make(
                $request->all(),
                [
                    /*
                    |--------------------------------------------------------------------------
                    | Institution
                    |--------------------------------------------------------------------------
                    */

                    'name' =>
                        [
                            'required',
                            'string',
                            'max:255',
                        ],

                    'acronym' =>
                        [
                            'required',
                            'string',
                            'max:30',
                        ],

                    'type' =>
                        [
                            'required',
                            'in:Public,Private',
                        ],

                    'campus_type' =>
                        [
                            'required',
                            'in:Main Campus,Extension',
                        ],

                    'email' =>
                        [
                            'required',
                            'email',
                            'unique:universities,email',
                        ],

                    'contact_number' =>
                        [
                            'nullable',
                            'string',
                            'max:20',
                        ],

                    'website' =>
                        [
                            'nullable',
                            'string',
                            'max:255',
                        ],

                    'logo' =>
                        [
                            'nullable',
                            'file',
                            'image',
                            'mimes:jpg,jpeg,png,webp',
                            'max:2048',
                        ],


                    /*
                    |--------------------------------------------------------------------------
                    | Address
                    |--------------------------------------------------------------------------
                    */

                    'region' =>
                        [
                            'required',
                            'string',
                            'max:255',
                        ],

                    'province' =>
                        [
                            'required',
                            'string',
                            'max:255',
                        ],

                    'city' =>
                        [
                            'required',
                            'string',
                            'max:255',
                        ],

                    'barangay' =>
                        [
                            'required',
                            'string',
                            'max:255',
                        ],

                    'zip_code' =>
                        [
                            'required',
                            'string',
                            'max:10',
                        ],

                    'complete_address' =>
                        [
                            'required',
                            'string',
                        ],


                    /*
                    |--------------------------------------------------------------------------
                    | NSTP Configuration
                    |--------------------------------------------------------------------------
                    */

                    'academic_year' =>
                        [
                            'required',
                            'string',
                            'max:50',
                        ],

                    'semester' =>
                        [
                            'required',
                            'string',
                            'max:50',
                        ],

                    'components' =>
                        [
                            'required',
                            'array',
                            'min:1',
                        ],

                    'components.*' =>
                        [
                            'required',
                            'string',
                            'in:CWTS,ROTC,LTS',
                        ],

                    'max_students' =>
                        [
                            'required',
                            'integer',
                            'min:1',
                        ],


                    /*
                    |--------------------------------------------------------------------------
                    | University Administrator
                    |--------------------------------------------------------------------------
                    */

                    'admin_first_name' =>
                        [
                            'required',
                            'string',
                            'max:255',
                        ],

                    'admin_middle_name' =>
                        [
                            'nullable',
                            'string',
                            'max:255',
                        ],

                    'admin_last_name' =>
                        [
                            'required',
                            'string',
                            'max:255',
                        ],

                    'admin_email' =>
                        [
                            'required',
                            'email',
                            'unique:university_administrators,email',
                        ],

                    'admin_phone' =>
                        [
                            'required',
                            'string',
                            'max:30',
                        ],

                    'admin_username' =>
                        [
                            'required',
                            'string',
                            'max:100',
                            'unique:university_administrators,username',
                        ],
                ]
            );


        /*
        |--------------------------------------------------------------------------
        | Validation Failed
        |--------------------------------------------------------------------------
        */

        if ($validator->fails()) {
            return response()->json(
                [
                    'success' =>
                        false,

                    'message' =>
                        'Please correct the highlighted information before creating the university.',

                    'errors' =>
                        $validator->errors(),
                ],
                422
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Logo Path
        |--------------------------------------------------------------------------
        */

        $logoPath = null;


        try {
            /*
            |--------------------------------------------------------------------------
            | Upload University Logo
            |--------------------------------------------------------------------------
            */

            if (
                $request->hasFile(
                    'logo'
                )
            ) {
                $uploadedLogo =
                    $request->file(
                        'logo'
                    );


                if (
                    !$uploadedLogo ||
                    !$uploadedLogo->isValid()
                ) {
                    throw new \RuntimeException(
                        'The uploaded university logo is invalid.'
                    );
                }


                $logoPath =
                    $uploadedLogo->store(
                        'universities/logos',
                        'public'
                    );


                if (
                    !$logoPath ||
                    !Storage::disk(
                        'public'
                    )->exists(
                        $logoPath
                    )
                ) {
                    throw new \RuntimeException(
                        'Unable to save the university logo.'
                    );
                }
            }


            /*
            |--------------------------------------------------------------------------
            | Create University + Administrator Atomically
            |--------------------------------------------------------------------------
            |
            | Only database operations happen inside the transaction.
            |
            | Email sending happens AFTER this transaction successfully commits.
            |
            */

            [
                $university,
                $administrator,
                $generatedPassword,
                $accessCode,
            ] =
                DB::transaction(
                    function () use (
                        $request,
                        $components,
                        $logoPath
                    ) {
                        /*
                        |--------------------------------------------------------------------------
                        | Generate Access Code
                        |--------------------------------------------------------------------------
                        */

                        $accessCode =
                            $this->generateAccessCode();


                        /*
                        |--------------------------------------------------------------------------
                        | Generate Administrator Temporary Password
                        |--------------------------------------------------------------------------
                        */

                        $generatedPassword =
                            Str::password(
                                length: 12
                            );


                        /*
                        |--------------------------------------------------------------------------
                        | Create University
                        |--------------------------------------------------------------------------
                        */

                        $university =
                            University::create(
                                [
                                    'name' =>
                                        trim(
                                            (string) $request->name
                                        ),

                                    'acronym' =>
                                        trim(
                                            (string) $request->acronym
                                        ),

                                    'type' =>
                                        $request->type,

                                    'campus_type' =>
                                        $request->campus_type,

                                    'email' =>
                                        strtolower(
                                            trim(
                                                (string) $request->email
                                            )
                                        ),

                                    'contact_number' =>
                                        $request->contact_number,

                                    'website' =>
                                        $request->website,

                                    'logo' =>
                                        $logoPath,


                                    /*
                                    |--------------------------------------------------------------------------
                                    | Address
                                    |--------------------------------------------------------------------------
                                    */

                                    'region' =>
                                        $request->region,

                                    'province' =>
                                        $request->province,

                                    'city' =>
                                        $request->city,

                                    'barangay' =>
                                        $request->barangay,

                                    'zip_code' =>
                                        $request->zip_code,

                                    'complete_address' =>
                                        $request->complete_address,


                                    /*
                                    |--------------------------------------------------------------------------
                                    | NSTP Configuration
                                    |--------------------------------------------------------------------------
                                    */

                                    'academic_year' =>
                                        $request->academic_year,

                                    'semester' =>
                                        $request->semester,

                                    'components' =>
                                        $components,

                                    'max_students' =>
                                        (int) $request->max_students,


                                    /*
                                    |--------------------------------------------------------------------------
                                    | University Access
                                    |--------------------------------------------------------------------------
                                    */

                                    'access_code' =>
                                        $accessCode,

                                    'status' =>
                                        'ACTIVE',
                                ]
                            );


                        /*
                        |--------------------------------------------------------------------------
                        | Create University Administrator
                        |--------------------------------------------------------------------------
                        */

                        $administrator =
                            UniversityAdministrator::create(
                                [
                                    'university_id' =>
                                        $university->id,

                                    'first_name' =>
                                        trim(
                                            (string) $request
                                                ->admin_first_name
                                        ),

                                    'middle_name' =>
                                        filled(
                                            $request
                                                ->admin_middle_name
                                        )
                                            ? trim(
                                                (string) $request
                                                    ->admin_middle_name
                                            )
                                            : null,

                                    'last_name' =>
                                        trim(
                                            (string) $request
                                                ->admin_last_name
                                        ),

                                    'email' =>
                                        strtolower(
                                            trim(
                                                (string) $request
                                                    ->admin_email
                                            )
                                        ),

                                    'phone' =>
                                        trim(
                                            (string) $request
                                                ->admin_phone
                                        ),

                                    'username' =>
                                        trim(
                                            (string) $request
                                                ->admin_username
                                        ),

                                    /*
                                    |--------------------------------------------------------------------------
                                    | Login Password
                                    |--------------------------------------------------------------------------
                                    |
                                    | UniversityAdministrator model should have:
                                    |
                                    | 'password' => 'hashed'
                                    |
                                    */

                                    'password' =>
                                        $generatedPassword,


                                    /*
                                    |--------------------------------------------------------------------------
                                    | Temporary Password
                                    |--------------------------------------------------------------------------
                                    |
                                    | UniversityAdministrator model should have:
                                    |
                                    | 'temporary_password' => 'encrypted'
                                    |
                                    */

                                    'temporary_password' =>
                                        $generatedPassword,

                                    'must_change_password' =>
                                        true,
                                ]
                            );


                        /*
                        |--------------------------------------------------------------------------
                        | Ensure University Administrator Role Exists
                        |--------------------------------------------------------------------------
                        |
                        | This prevents errors on new laptops/databases where
                        | RoleSeeder may not have been run yet.
                        |
                        */

                        $universityAdminRole =
                            Role::firstOrCreate(
                                [
                                    'name' =>
                                        'university-admin',

                                    'guard_name' =>
                                        'university_admin',
                                ]
                            );


                        /*
                        |--------------------------------------------------------------------------
                        | Assign University Administrator Role
                        |--------------------------------------------------------------------------
                        */

                        if (
                            !$administrator->hasRole(
                                $universityAdminRole
                            )
                        ) {
                            $administrator->assignRole(
                                $universityAdminRole
                            );
                        }


                        /*
                        |--------------------------------------------------------------------------
                        | Return Created Models
                        |--------------------------------------------------------------------------
                        */

                        return [
                            $university,
                            $administrator,
                            $generatedPassword,
                            $accessCode,
                        ];
                    }
                );


            /*
            |--------------------------------------------------------------------------
            | University Administrator Login Portal
            |--------------------------------------------------------------------------
            */

            $portalUrl =
                route(
                    'university-admin.login'
                );


            /*
            |--------------------------------------------------------------------------
            | Credentials PDF URL
            |--------------------------------------------------------------------------
            */

            $pdfUrl =
                route(
                    'superadmin.university.pdf',
                    [
                        'university' =>
                            $university->id,
                    ]
                );


            /*
            |--------------------------------------------------------------------------
            | Generated Credentials
            |--------------------------------------------------------------------------
            */

            $generatedCredentials =
                [
                    'university' =>
                        $university->name,

                    'access_code' =>
                        $accessCode,

                    'portal_url' =>
                        $portalUrl,

                    'email' =>
                        $administrator->email,

                    'username' =>
                        $administrator->username,

                    'password' =>
                        $generatedPassword,

                    'administrator' =>
                        $administrator->full_name,
                ];


            /*
            |--------------------------------------------------------------------------
            | Send Credentials Email AFTER Database Commit
            |--------------------------------------------------------------------------
            |
            | IMPORTANT:
            |
            | Email failure must NOT undo university creation.
            |
            | If SMTP fails on the new laptop, the university remains created,
            | the frontend can proceed to Generated, and the email can be retried
            | using the Send Email button.
            |
            */

            $emailSent = true;

            $emailWarning = null;


            try {
                $this->sendInvitationEmail(
                    $administrator,
                    $generatedCredentials
                );
            } catch (
                \Throwable $mailException
            ) {
                $emailSent =
                    false;

                $emailWarning =
                    'University created successfully, but the credentials email could not be sent. '
                    . 'Use the Send Email button on the Generated step after checking the mail configuration.';


                Log::warning(
                    'University created but credentials email failed.',
                    [
                        'university_id' =>
                            $university->id,

                        'administrator_id' =>
                            $administrator->id,

                        'exception' =>
                            get_class(
                                $mailException
                            ),

                        'message' =>
                            $mailException
                                ->getMessage(),
                    ]
                );
            }


            /*
            |--------------------------------------------------------------------------
            | Successful Response
            |--------------------------------------------------------------------------
            */

            return response()->json(
                [
                    'success' =>
                        true,

                    'already_exists' =>
                        false,

                    'email_sent' =>
                        $emailSent,

                    'warning' =>
                        $emailWarning,

                    'message' =>
                        'University created successfully.',


                    /*
                    |--------------------------------------------------------------------------
                    | University
                    |--------------------------------------------------------------------------
                    */

                    'university_id' =>
                        $university->id,

                    'access_code' =>
                        $accessCode,

                    'portal_url' =>
                        $portalUrl,

                    'pdf_url' =>
                        $pdfUrl,


                    'university' =>
                        [
                            'id' =>
                                $university->id,

                            'name' =>
                                $university->name,

                            'acronym' =>
                                $university->acronym,

                            'email' =>
                                $university->email,

                            'logo_url' =>
                                $university->logo_url,
                        ],


                    /*
                    |--------------------------------------------------------------------------
                    | Administrator
                    |--------------------------------------------------------------------------
                    */

                    'administrator' =>
                        [
                            'id' =>
                                $administrator->id,

                            'email' =>
                                $administrator->email,

                            'username' =>
                                $administrator->username,

                            'full_name' =>
                                $administrator->full_name,
                        ],
                ],
                201
            );

        } catch (
            \Throwable $e
        ) {
            /*
            |--------------------------------------------------------------------------
            | Remove Uploaded Logo If Database Creation Failed
            |--------------------------------------------------------------------------
            */

            if (
                !empty(
                    $logoPath
                )
            ) {
                Storage::disk(
                    'public'
                )->delete(
                    $logoPath
                );
            }


            /*
            |--------------------------------------------------------------------------
            | Log Detailed Error
            |--------------------------------------------------------------------------
            */

            Log::error(
                'University creation failed.',
                [
                    'exception' =>
                        get_class(
                            $e
                        ),

                    'message' =>
                        $e->getMessage(),

                    'file' =>
                        $e->getFile(),

                    'line' =>
                        $e->getLine(),
                ]
            );


            report(
                $e
            );


            /*
            |--------------------------------------------------------------------------
            | Error Response
            |--------------------------------------------------------------------------
            */

            return response()->json(
                [
                    'success' =>
                        false,

                    'message' =>
                        'Unable to create university.',

                    'error' =>
                        app()->environment(
                            'local'
                        )
                            ? $e->getMessage()
                            : 'An unexpected error occurred.',
                ],
                500
            );
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Download University Credentials PDF
    |--------------------------------------------------------------------------
    |
    | LEGAL SIZE:
    |
    | 8.5 x 14 inches
    |
    */

    public function downloadPdf(University $university)
{
    // Find the university administrator.
    $administrator = UniversityAdministrator::query()
        ->where('university_id', $university->id)
        ->first();

    if (!$administrator) {
        abort(404, 'University administrator not found.');
    }

    // Retrieve the temporary password.
    $temporaryPassword = $administrator->temporary_password;

    if (empty($temporaryPassword)) {
        return response()->json([
            'success' => false,
            'message' => 'The temporary password is no longer available. '
                . 'The administrator may already have changed the password. '
                . 'A credentials PDF containing the original temporary '
                . 'password can no longer be generated.',
        ], 422);
    }

    // Prepare credentials for the PDF.
    $credentials = [
        'university' => $university->name,
        'access_code' => $university->access_code,
        'portal_url' => route('university-admin.login'),
        'email' => $administrator->email,
        'username' => $administrator->username,
        'password' => $temporaryPassword,
        'administrator' => $administrator->full_name,
    ];

    // Generate the PDF on legal-size portrait paper.
    $pdf = Pdf::loadView(
        'pdf.university_credentials',
        ['credentials' => $credentials]
    )->setPaper('legal', 'portrait');

    // Generate a safe filename.
    $safeUniversity = Str::slug(
        (string) ($university->acronym ?: $university->name)
    );

    if ($safeUniversity === '') {
        $safeUniversity = 'university';
    }

    $filename = 'nstphub_credentials_'
        . $safeUniversity
        . '_'
        . now()->format('Ymd_His')
        . '.pdf';

    // Generate the complete PDF before sending response headers.
    $pdfContent = $pdf->output();

    // Validate the generated PDF.
    if (!str_starts_with($pdfContent, '%PDF-')) {
        Log::error('Invalid university credentials PDF generated.', [
            'university_id' => $university->id,
        ]);

        abort(500, 'Unable to generate a valid PDF document.');
    }

    // Stream the PDF without specifying Content-Length.
    return response()->streamDownload(
        static function () use ($pdfContent): void {
            echo $pdfContent;
        },
        $filename,
        [
            'Content-Type' => 'application/pdf',
            'Cache-Control' => 'private, no-store, no-cache, must-revalidate',
            'Pragma' => 'no-cache',
            'X-Content-Type-Options' => 'nosniff',
        ]
    );
}

    /*
    |--------------------------------------------------------------------------
    | Resend Credentials Email
    |--------------------------------------------------------------------------
    */

    public function sendEmail(
        University $university
    ) {
        try {
            /*
            |--------------------------------------------------------------------------
            | Find University Administrator
            |--------------------------------------------------------------------------
            */

            $administrator =
                UniversityAdministrator::query()
                    ->where(
                        'university_id',
                        $university->id
                    )
                    ->first();


            /*
            |--------------------------------------------------------------------------
            | Administrator Not Found
            |--------------------------------------------------------------------------
            */

            if (
                !$administrator
            ) {
                return response()->json(
                    [
                        'success' =>
                            false,

                        'message' =>
                            'University administrator not found.',
                    ],
                    404
                );
            }


            /*
            |--------------------------------------------------------------------------
            | Administrator Email
            |--------------------------------------------------------------------------
            */

            if (
                empty(
                    $administrator->email
                )
            ) {
                return response()->json(
                    [
                        'success' =>
                            false,

                        'message' =>
                            'The University Administrator does not have an email address.',
                    ],
                    422
                );
            }


            /*
            |--------------------------------------------------------------------------
            | Temporary Password
            |--------------------------------------------------------------------------
            */

            $temporaryPassword =
                $administrator
                    ->temporary_password;


            /*
            |--------------------------------------------------------------------------
            | Temporary Password Not Available
            |--------------------------------------------------------------------------
            */

            if (
                empty(
                    $temporaryPassword
                )
            ) {
                return response()->json(
                    [
                        'success' =>
                            false,

                        'message' =>
                            'The temporary password is no longer available. '
                            . 'The University Administrator may have already '
                            . 'changed the password. Use Forgot Password if '
                            . 'account recovery is required.',
                    ],
                    422
                );
            }


            /*
            |--------------------------------------------------------------------------
            | Build Credentials
            |--------------------------------------------------------------------------
            */

            $credentials =
                [
                    'university' =>
                        $university->name,

                    'access_code' =>
                        $university->access_code,

                    'portal_url' =>
                        route(
                            'university-admin.login'
                        ),

                    'email' =>
                        $administrator->email,

                    'username' =>
                        $administrator->username,

                    'password' =>
                        $temporaryPassword,

                    'administrator' =>
                        $administrator->full_name,
                ];


            /*
            |--------------------------------------------------------------------------
            | Send Credentials Email
            |--------------------------------------------------------------------------
            */

            Mail::to(
                $administrator->email
            )->send(
                new UniversityCredentialsMail(
                    $credentials
                )
            );


            /*
            |--------------------------------------------------------------------------
            | Successful Response
            |--------------------------------------------------------------------------
            */

            return response()->json(
                [
                    'success' =>
                        true,

                    'message' =>
                        'Credentials email sent successfully.',
                ]
            );

        } catch (
            \Throwable $e
        ) {
            /*
            |--------------------------------------------------------------------------
            | Report Error
            |--------------------------------------------------------------------------
            */

            report(
                $e
            );


            /*
            |--------------------------------------------------------------------------
            | Error Response
            |--------------------------------------------------------------------------
            */

            return response()->json(
                [
                    'success' =>
                        false,

                    'message' =>
                        'Email sending failed.',

                    'error' =>
                        app()->environment(
                            'local'
                        )
                            ? $e->getMessage()
                            : 'Unable to send credentials email.',
                ],
                500
            );
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Generate Unique Access Code
    |--------------------------------------------------------------------------
    |
    | Format:
    |
    | 2026-123456AB
    |
    */

    private function generateAccessCode(): string
    {
        do {
            /*
            |--------------------------------------------------------------------------
            | Current Year
            |--------------------------------------------------------------------------
            */

            $year =
                now()->format(
                    'Y'
                );


            /*
            |--------------------------------------------------------------------------
            | Random 6-Digit Number
            |--------------------------------------------------------------------------
            */

            $randomNumber =
                random_int(
                    100000,
                    999999
                );


            /*
            |--------------------------------------------------------------------------
            | Random Two Letters
            |--------------------------------------------------------------------------
            */

            $randomLetters =
                strtoupper(
                    Str::random(
                        2
                    )
                );


            /*
            |--------------------------------------------------------------------------
            | Final Access Code
            |--------------------------------------------------------------------------
            */

            $code =
                "{$year}-{$randomNumber}{$randomLetters}";

        } while (
            University::where(
                'access_code',
                $code
            )->exists()
        );


        return $code;
    }


    /*
    |--------------------------------------------------------------------------
    | Send Invitation Email
    |--------------------------------------------------------------------------
    */

    private function sendInvitationEmail(
        UniversityAdministrator $administrator,
        array $credentials
    ): void {
        Mail::to(
            $administrator->email
        )->send(
            new UniversityCredentialsMail(
                $credentials
            )
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Generate Credential PDF
    |--------------------------------------------------------------------------
    |
    | LEGAL SIZE:
    |
    | 8.5 x 14 inches
    |
    */

    private function generateCredentialPDF(
        array $credentials
    ): ?string {
        /*
        |--------------------------------------------------------------------------
        | Generate LEGAL Portrait PDF
        |--------------------------------------------------------------------------
        */

        $pdf =
            Pdf::loadView(
                'pdf.university_credentials',
                [
                    'credentials' =>
                        $credentials,
                ]
            )
                ->setPaper(
                    'legal',
                    'portrait'
                );


        /*
        |--------------------------------------------------------------------------
        | Filename
        |--------------------------------------------------------------------------
        */

        $filename =
            'credentials_'
            . now()->format(
                'Ymd_His'
            )
            . '_'
            . Str::random(
                6
            )
            . '.pdf';


        /*
        |--------------------------------------------------------------------------
        | Store PDF
        |--------------------------------------------------------------------------
        */

        Storage::disk(
            'public'
        )->put(
            'credentials/'
            . $filename,

            $pdf->output()
        );


        /*
        |--------------------------------------------------------------------------
        | Return Public URL
        |--------------------------------------------------------------------------
        */

        return Storage::url(
            'credentials/'
            . $filename
        );
    }
}