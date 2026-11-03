<?php

namespace App\Support;

use App\Models\Attendance;
use App\Models\Coordinator;
use App\Models\Instructor;
use App\Models\UniversityAdministrator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class NstpComponentAccess
{
    /*
    |--------------------------------------------------------------------------
    | Session Key
    |--------------------------------------------------------------------------
    */

    private const SESSION_KEY =
        'nstp_selected_component';


    /*
    |--------------------------------------------------------------------------
    | Available NSTP Components
    |--------------------------------------------------------------------------
    */

    public static function components(): array
    {
        /*
        |--------------------------------------------------------------------------
        | Use Attendance Model As Primary Source
        |--------------------------------------------------------------------------
        |
        | Attendance::components() should return:
        |
        | [
        |     'LTS',
        |     'CWTS',
        |     'ROTC',
        | ]
        |
        */

        $components =
            Attendance::components();


        return collect(
            $components
        )
            ->map(
                fn (
                    mixed $component
                ): string =>
                    strtoupper(
                        trim(
                            (string)
                            $component
                        )
                    )
            )
            ->filter(
                fn (
                    string $component
                ): bool =>
                    $component !==
                    ''
            )
            ->unique()
            ->values()
            ->all();
    }


    /*
    |--------------------------------------------------------------------------
    | Normalize Component
    |--------------------------------------------------------------------------
    */

    public static function normalize(
        mixed $component
    ): string {

        $component =
            strtoupper(
                trim(
                    (string)
                    (
                        $component
                        ??
                        ''
                    )
                )
            );


        if (
            !in_array(
                $component,
                self::components(),
                true
            )
        ) {

            return '';
        }


        return $component;
    }


    /*
    |--------------------------------------------------------------------------
    | Resolve Current Authenticated Account
    |--------------------------------------------------------------------------
    |
    | Authentication priority:
    |
    | 1. University Administrator
    | 2. Instructor
    | 3. Coordinator
    |
    | University Admin is intentionally checked first because an administrator
    | may enter the Instructor/Coordinator dashboard while remaining logged in
    | through the university_admin guard.
    |
    */

    public static function actor(): array
    {
        /*
        |--------------------------------------------------------------------------
        | SANCTUM / API AUTHENTICATION
        |--------------------------------------------------------------------------
        |
        | Native Admin Mobile requests are authenticated with auth:sanctum and do
        | not have the browser session middleware. Resolve request()->user() first
        | so the same component-access helper works for both web and mobile.
        |
        */

        $apiActor =
            request()->user();

        if (
            $apiActor instanceof
            UniversityAdministrator
        ) {
            return [
                'actor' =>
                    $apiActor,

                'role' =>
                    'university-admin',

                'guard' =>
                    'sanctum',

                'account_type' =>
                    'university_admin',

                'university_id' =>
                    (int)
                    $apiActor->university_id,

                'components' =>
                    self::universityComponents(
                        $apiActor
                    ),

                'is_university_admin' =>
                    true,
            ];
        }

        if (
            $apiActor instanceof
            Instructor
        ) {
            $components =
                method_exists(
                    $apiActor,
                    'componentCodes'
                )
                    ? $apiActor
                        ->componentCodes()
                    : [];

            if (
                empty($components)
            ) {
                $legacyComponent =
                    self::normalize(
                        $apiActor->component
                        ??
                        ''
                    );

                if (
                    $legacyComponent !==
                    ''
                ) {
                    $components = [
                        $legacyComponent,
                    ];
                }
            }

            return [
                'actor' =>
                    $apiActor,

                'role' =>
                    'instructor',

                'guard' =>
                    'sanctum',

                'account_type' =>
                    'instructor',

                'university_id' =>
                    (int)
                    $apiActor->university_id,

                'components' =>
                    self::normalizeComponents(
                        $components
                    ),

                'is_university_admin' =>
                    false,
            ];
        }

        if (
            $apiActor instanceof
            Coordinator
        ) {
            $component =
                self::normalize(
                    $apiActor->component
                    ??
                    ''
                );

            return [
                'actor' =>
                    $apiActor,

                'role' =>
                    $apiActor
                        ->coordinatorRole()
                    ??
                    'coordinator',

                'guard' =>
                    'sanctum',

                'account_type' =>
                    'coordinator',

                'university_id' =>
                    (int)
                    $apiActor->university_id,

                'components' =>
                    $component !==
                    ''
                        ? [
                            $component,
                        ]
                        : [],

                'is_university_admin' =>
                    false,
            ];
        }

        /*
        |--------------------------------------------------------------------------
        | UNIVERSITY ADMINISTRATOR
        |--------------------------------------------------------------------------
        */

        if (
            Auth::guard(
                'university_admin'
            )->check()
        ) {

            /** @var UniversityAdministrator|null $admin */

            $admin =
                Auth::guard(
                    'university_admin'
                )->user();


            if (
                !$admin
            ) {

                abort(
                    401,
                    'University Administrator authentication could not be resolved.'
                );
            }


            return [
                /*
                |--------------------------------------------------------------------------
                | Account
                |--------------------------------------------------------------------------
                */

                'actor' =>
                    $admin,


                /*
                |--------------------------------------------------------------------------
                | Display Role
                |--------------------------------------------------------------------------
                |
                | The frontend should display:
                |
                | UNIVERSITY ADMIN
                |
                */

                'role' =>
                    'university-admin',


                /*
                |--------------------------------------------------------------------------
                | Authentication Guard
                |--------------------------------------------------------------------------
                */

                'guard' =>
                    'university_admin',


                /*
                |--------------------------------------------------------------------------
                | Account Type
                |--------------------------------------------------------------------------
                */

                'account_type' =>
                    'university_admin',


                /*
                |--------------------------------------------------------------------------
                | University
                |--------------------------------------------------------------------------
                */

                'university_id' =>
                    (int)
                    $admin->university_id,


                /*
                |--------------------------------------------------------------------------
                | Accessible Components
                |--------------------------------------------------------------------------
                |
                | University administrators can access all NSTP components
                | belonging to their university.
                |
                */

                'components' =>
                    self::universityComponents(
                        $admin
                    ),


                /*
                |--------------------------------------------------------------------------
                | University Admin Indicator
                |--------------------------------------------------------------------------
                */

                'is_university_admin' =>
                    true,
            ];
        }


        /*
        |--------------------------------------------------------------------------
        | INSTRUCTOR
        |--------------------------------------------------------------------------
        */

        if (
            Auth::guard(
                'instructor'
            )->check()
        ) {

            /** @var Instructor|null $instructor */

            $instructor =
                Auth::guard(
                    'instructor'
                )->user();


            if (
                !$instructor
            ) {

                abort(
                    401,
                    'Instructor authentication could not be resolved.'
                );
            }


            /*
            |--------------------------------------------------------------------------
            | Multiple Component Assignments
            |--------------------------------------------------------------------------
            |
            | The updated Instructor model provides:
            |
            | $instructor->componentCodes()
            |
            | Example:
            |
            | [
            |     'LTS',
            |     'CWTS',
            | ]
            |
            */

            $components =
                method_exists(
                    $instructor,
                    'componentCodes'
                )
                    ? $instructor
                        ->componentCodes()
                    : [];


            /*
            |--------------------------------------------------------------------------
            | Backward Compatibility
            |--------------------------------------------------------------------------
            |
            | Existing instructors may still only have instructors.component.
            |
            */

            if (
                empty(
                    $components
                )
            ) {

                $legacyComponent =
                    self::normalize(
                        $instructor->component
                        ??
                        ''
                    );


                if (
                    $legacyComponent !==
                    ''
                ) {

                    $components = [
                        $legacyComponent,
                    ];
                }
            }


            $components =
                self::normalizeComponents(
                    $components
                );


            return [
                'actor' =>
                    $instructor,

                'role' =>
                    'instructor',

                'guard' =>
                    'instructor',

                'account_type' =>
                    'instructor',

                'university_id' =>
                    (int)
                    $instructor->university_id,

                'components' =>
                    $components,

                'is_university_admin' =>
                    false,
            ];
        }


        /*
        |--------------------------------------------------------------------------
        | COORDINATOR
        |--------------------------------------------------------------------------
        */

        if (
            Auth::guard(
                'coordinator'
            )->check()
        ) {

            /** @var Coordinator|null $coordinator */

            $coordinator =
                Auth::guard(
                    'coordinator'
                )->user();


            if (
                !$coordinator
            ) {

                abort(
                    401,
                    'Coordinator authentication could not be resolved.'
                );
            }


            /*
            |--------------------------------------------------------------------------
            | Coordinator Role
            |--------------------------------------------------------------------------
            */

            $coordinatorRole =
                $coordinator
                    ->coordinatorRole()
                ??
                'coordinator';


            /*
            |--------------------------------------------------------------------------
            | Coordinator Component
            |--------------------------------------------------------------------------
            |
            | Coordinators currently remain single-component accounts.
            |
            */

            $component =
                self::normalize(
                    $coordinator->component
                    ??
                    ''
                );


            return [
                'actor' =>
                    $coordinator,

                'role' =>
                    $coordinatorRole,

                'guard' =>
                    'coordinator',

                'account_type' =>
                    'coordinator',

                'university_id' =>
                    (int)
                    $coordinator->university_id,

                'components' =>
                    $component !==
                    ''
                        ? [
                            $component,
                        ]
                        : [],

                'is_university_admin' =>
                    false,
            ];
        }


        /*
        |--------------------------------------------------------------------------
        | No Valid Authentication
        |--------------------------------------------------------------------------
        */

        abort(
            401,
            'You must be logged in to access this NSTP area.'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Resolve Selected Component
    |--------------------------------------------------------------------------
    |
    | Selection priority:
    |
    | 1. Route component
    |
    |    /university-admin/components/rotc
    |
    | 2. Query component
    |
    |    /instructor-coordinator/dashboard?component=CWTS
    |
    | 3. Saved session component
    |
    | 4. First component the account can access
    |
    */

    public static function selected(
        Request $request,
        array $context
    ): string {

        /*
        |--------------------------------------------------------------------------
        | Normalize Available Components
        |--------------------------------------------------------------------------
        */

        $allowedComponents =
            self::normalizeComponents(
                $context[
                    'components'
                ]
                ??
                []
            );


        /*
        |--------------------------------------------------------------------------
        | No Components Assigned
        |--------------------------------------------------------------------------
        */

        if (
            empty(
                $allowedComponents
            )
        ) {

            self::clearSelection(
                $request
            );


            return '';
        }


        /*
        |--------------------------------------------------------------------------
        | Route Component
        |--------------------------------------------------------------------------
        |
        | Route has priority because University Admin URLs use:
        |
        | /university-admin/components/{component}
        |
        */

        $routeValue =
            $request->route(
                'component'
            );


        if (
            self::hasValue(
                $routeValue
            )
        ) {

            return self::selectRequestedComponent(
                request:
                    $request,

                rawComponent:
                    $routeValue,

                allowedComponents:
                    $allowedComponents
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Query Component
        |--------------------------------------------------------------------------
        |
        | Example:
        |
        | ?component=ROTC
        |
        */

        $queryValue =
            $request->query(
                'component'
            );


        if (
            self::hasValue(
                $queryValue
            )
        ) {

            return self::selectRequestedComponent(
                request:
                    $request,

                rawComponent:
                    $queryValue,

                allowedComponents:
                    $allowedComponents
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Previously Selected Component
        |--------------------------------------------------------------------------
        */

        $sessionComponent =
            self::selectedFromSession(
                $request
            );


        if (
            $sessionComponent !==
            ''
            &&
            in_array(
                $sessionComponent,
                $allowedComponents,
                true
            )
        ) {

            return $sessionComponent;
        }


        /*
        |--------------------------------------------------------------------------
        | Default Component
        |--------------------------------------------------------------------------
        |
        | Use the first available component for the account.
        |
        */

        $component =
            $allowedComponents[0];


        self::remember(
            $request,
            $component
        );


        return $component;
    }


    /*
    |--------------------------------------------------------------------------
    | Current Context
    |--------------------------------------------------------------------------
    |
    | Convenience method for controllers.
    |
    | Instead of:
    |
    | $context = NstpComponentAccess::actor();
    | $component = NstpComponentAccess::selected($request, $context);
    |
    | controllers may use:
    |
    | $context = NstpComponentAccess::context($request);
    |
    */

    public static function context(
        Request $request
    ): array {

        $context =
            self::actor();


        $component =
            self::selected(
                $request,
                $context
            );


        $context[
            'selected_component'
        ] =
            $component;


        return $context;
    }


    /*
    |--------------------------------------------------------------------------
    | Can Access Component
    |--------------------------------------------------------------------------
    */

    public static function canAccess(
        array $context,
        mixed $component
    ): bool {

        $component =
            self::normalize(
                $component
            );


        if (
            $component ===
            ''
        ) {

            return false;
        }


        $allowedComponents =
            self::normalizeComponents(
                $context[
                    'components'
                ]
                ??
                []
            );


        return in_array(
            $component,
            $allowedComponents,
            true
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Require Component Access
    |--------------------------------------------------------------------------
    |
    | Useful for controller actions that receive an explicit component.
    |
    */

    public static function authorize(
        array $context,
        mixed $component
    ): string {

        $component =
            self::normalize(
                $component
            );


        if (
            $component ===
            ''
        ) {

            throw ValidationException::withMessages([
                'component' => [
                    'The selected NSTP component is invalid.',
                ],
            ]);
        }


        if (
            !self::canAccess(
                $context,
                $component
            )
        ) {

            abort(
                403,
                'You do not have access to this NSTP component.'
            );
        }


        return $component;
    }


    /*
    |--------------------------------------------------------------------------
    | Remember Component
    |--------------------------------------------------------------------------
    */

    public static function remember(
        Request $request,
        mixed $component
    ): void {

        $component =
            self::normalize(
                $component
            );


        if (
            $component ===
            ''
        ) {

            return;
        }


        if (
            !$request->hasSession()
        ) {
            return;
        }

        $request
            ->session()
            ->put(
                self::SESSION_KEY,
                $component
            );
    }


    /*
    |--------------------------------------------------------------------------
    | Clear Selected Component
    |--------------------------------------------------------------------------
    */

    public static function clearSelection(
        Request $request
    ): void {

        if (
            !$request->hasSession()
        ) {
            return;
        }

        $request
            ->session()
            ->forget(
                self::SESSION_KEY
            );
    }


    /*
    |--------------------------------------------------------------------------
    | Selected Component From Session
    |--------------------------------------------------------------------------
    */

    public static function selectedFromSession(
        Request $request
    ): string {

        if (
            !$request->hasSession()
        ) {
            return '';
        }

        return self::normalize(
            $request
                ->session()
                ->get(
                    self::SESSION_KEY
                )
        );
    }


    /*
    |--------------------------------------------------------------------------
    | University Enabled Components
    |--------------------------------------------------------------------------
    |
    | A University Administrator may only enter components enabled for
    | their own university. This prevents access to disabled NSTP programs.
    |
    */

    private static function universityComponents(
        UniversityAdministrator $admin
    ): array {

        $admin->loadMissing(
            'university'
        );


        $components =
            $admin->university?->components;


        if (
            !is_array(
                $components
            )
        ) {

            return [];
        }


        return self::normalizeComponents(
            $components
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Normalize Component Collection
    |--------------------------------------------------------------------------
    */

    private static function normalizeComponents(
        array $components
    ): array {

        return collect(
            $components
        )
            ->map(
                fn (
                    mixed $component
                ): string =>
                    self::normalize(
                        $component
                    )
            )
            ->filter(
                fn (
                    string $component
                ): bool =>
                    $component !==
                    ''
            )
            ->unique()
            ->values()
            ->all();
    }


    /*
    |--------------------------------------------------------------------------
    | Select Requested Component
    |--------------------------------------------------------------------------
    */

    private static function selectRequestedComponent(
        Request $request,
        mixed $rawComponent,
        array $allowedComponents
    ): string {

        /*
        |--------------------------------------------------------------------------
        | Normalize
        |--------------------------------------------------------------------------
        */

        $component =
            self::normalize(
                $rawComponent
            );


        /*
        |--------------------------------------------------------------------------
        | Invalid Component
        |--------------------------------------------------------------------------
        |
        | Do not silently fall back to another component if someone requests:
        |
        | ?component=ABC
        |
        */

        if (
            $component ===
            ''
        ) {

            throw ValidationException::withMessages([
                'component' => [
                    'The selected NSTP component is invalid.',
                ],
            ]);
        }


        /*
        |--------------------------------------------------------------------------
        | Unauthorized Component
        |--------------------------------------------------------------------------
        |
        | Example:
        |
        | Instructor has:
        |
        | LTS + CWTS
        |
        | but manually requests:
        |
        | ?component=ROTC
        |
        */

        if (
            !in_array(
                $component,
                $allowedComponents,
                true
            )
        ) {

            abort(
                403,
                'You do not have access to the selected NSTP component.'
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Save Selection
        |--------------------------------------------------------------------------
        */

        self::remember(
            $request,
            $component
        );


        return $component;
    }


    /*
    |--------------------------------------------------------------------------
    | Value Exists
    |--------------------------------------------------------------------------
    */

    private static function hasValue(
        mixed $value
    ): bool {

        if (
            $value ===
            null
        ) {

            return false;
        }


        return trim(
            (string)
            $value
        ) !==
        '';
    }
}