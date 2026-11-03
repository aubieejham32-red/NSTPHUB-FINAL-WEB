<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

use App\Http\Controllers\Auth\GoogleController;
use App\Http\Controllers\Auth\SuperAdminAuthController;
use App\Http\Controllers\Auth\SuperAdminForgotPasswordController;
use App\Http\Controllers\Auth\UniversityAccessController;
use App\Http\Controllers\Auth\UniversityAdminAuthController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\InstructorCoordinatorAuthController;
use App\Http\Controllers\Auth\InstructorCoordinatorForgotPasswordController;

use App\Http\Controllers\SuperAdmin\DashboardController;
use App\Http\Controllers\SuperAdmin\ProfileController;
use App\Http\Controllers\SuperAdmin\UniversityController;
use App\Http\Controllers\SuperAdmin\AddUniversityController;

use App\Http\Controllers\UniversityAdmin\DashboardController as UniversityAdminDashboardController;
use App\Http\Controllers\UniversityAdmin\ProfileController as UniversityAdminProfileController;
use App\Http\Controllers\UniversityAdmin\Users\InstructorController;
use App\Http\Controllers\UniversityAdmin\Users\CoordinatorController;
use App\Http\Controllers\UniversityAdmin\Users\StudentController;
use App\Http\Controllers\UniversityAdmin\Registration\StudentRegistrationController;
use App\Http\Controllers\UniversityAdmin\ReportController;

use App\Http\Controllers\InstructorCoordinators\InstructorCoordinatorProfileController;
use App\Http\Controllers\InstructorCoordinators\AnnouncementController;
use App\Http\Controllers\InstructorCoordinators\ScheduleController;
use App\Http\Controllers\InstructorCoordinators\StudentInformationController;
use App\Http\Controllers\InstructorCoordinators\AttendanceController;
use App\Http\Controllers\InstructorCoordinators\ExcuseLetterController;
use App\Http\Controllers\InstructorCoordinators\PdfExportController;
use App\Http\Controllers\InstructorCoordinators\DashboardController as InstructorCoordinatorDashboardController;


/*
|--------------------------------------------------------------------------
| Welcome
|--------------------------------------------------------------------------
*/

Route::get('/', function () {

    return Inertia::render(
        'WelcomePage'
    );

})->name(
    'Welcome'
);


/*
|--------------------------------------------------------------------------
| Role Selection
|--------------------------------------------------------------------------
*/

Route::get(
    '/role',
    function () {

        return Inertia::render(
            'RolePage'
        );

    }
)->name(
    'role'
);


/*
|--------------------------------------------------------------------------
| Google Authentication
|--------------------------------------------------------------------------
*/

Route::controller(
    GoogleController::class
)->group(function () {

    Route::get(
        '/auth/google',
        'redirect'
    )->name(
        'google.redirect'
    );


    Route::get(
        '/auth/google/callback',
        'callback'
    )->name(
        'google.callback'
    );


    Route::get(
        '/auth/google/instructor-coordinator',
        'redirectInstructorCoordinator'
    )->name(
        'google.instructor-coordinator.redirect'
    );


    Route::get(
        '/auth/google/instructor-coordinator/callback',
        'callbackInstructorCoordinator'
    )->name(
        'google.instructor-coordinator.callback'
    );


    /*
    |--------------------------------------------------------------------------
    | Super Admin Google Authentication
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/auth/google/superadmin',
        'redirectSuperAdmin'
    )->name(
        'google.superadmin.redirect'
    );


    Route::get(
        '/auth/google/superadmin/callback',
        'callbackSuperAdmin'
    )->name(
        'google.superadmin.callback'
    );

});


/*
|--------------------------------------------------------------------------
| Super Admin Authentication
|--------------------------------------------------------------------------
*/

Route::middleware([
    'web',
])
    ->prefix(
        'superadmin'
    )
    ->controller(
        SuperAdminAuthController::class
    )
    ->group(function () {

        Route::get(
            '/login',
            'create'
        )->name(
            'superadmin.login'
        );


        Route::post(
            '/login',
            'store'
        )->name(
            'superadmin.login.store'
        );


        Route::get(
            '/register',
            'createAccount'
        )->name(
            'superadmin.register'
        );


        Route::post(
            '/register',
            'register'
        )->name(
            'superadmin.register.store'
        );

    });


/*
|--------------------------------------------------------------------------
| Super Admin Forgot Password
|--------------------------------------------------------------------------
*/

Route::middleware([
    'web',
])
    ->prefix(
        'superadmin'
    )
    ->name(
        'superadmin.'
    )
    ->controller(
        SuperAdminForgotPasswordController::class
    )
    ->group(function () {

        Route::get(
            '/forgot-password',
            'showForgotPassword'
        )->name(
            'forgot-password'
        );

        Route::post(
            '/forgot-password',
            'sendCode'
        )->name(
            'forgot-password.send'
        );

        Route::get(
            '/forgot-password/verify-code',
            'showVerifyCode'
        )->name(
            'forgot-password.verify-code'
        );

        Route::post(
            '/verify-code',
            'verifyCode'
        )->name(
            'verify-code.submit'
        );

        Route::post(
            '/resend-code',
            'resendCode'
        )->name(
            'resend-code'
        );

        Route::get(
            '/reset-password',
            'showResetPassword'
        )->name(
            'reset-password'
        );

        Route::post(
            '/reset-password',
            'resetPassword'
        )->name(
            'reset-password.submit'
        );

        Route::get(
            '/password-reset-success',
            'showSuccess'
        )->name(
            'password-reset-success'
        );

    });


/*
|--------------------------------------------------------------------------
| Super Admin Routes
|--------------------------------------------------------------------------
*/

Route::middleware([
    'web',
])
    ->prefix(
        'superadmin'
    )
    ->name(
        'superadmin.'
    )
    ->group(function () {

        /*
        |--------------------------------------------------------------------------
        | Logout
        |--------------------------------------------------------------------------
        */

        Route::get(
            '/logout',
            function () {

                return Inertia::render(
                    'SuperAdmin/Logout',
                    [
                        'accounts' => [],
                    ]
                );

            }
        )->name(
            'logout'
        );


        Route::post(
            '/logout',
            [
                SuperAdminAuthController::class,
                'destroy',
            ]
        )->name(
            'logout.destroy'
        );


        /*
        |--------------------------------------------------------------------------
        | Dashboard
        |--------------------------------------------------------------------------
        */

        Route::get(
            '/dashboard',
            [
                DashboardController::class,
                'index',
            ]
        )->name(
            'dashboard'
        );


        /*
        |--------------------------------------------------------------------------
        | Universities
        |--------------------------------------------------------------------------
        */

        Route::get(
            '/universities',
            function () {

                return Inertia::render(
                    'SuperAdmin/SuperAdminUniversities'
                );

            }
        )->name(
            'universities'
        );


        Route::get(
            '/universities/create',
            function () {

                return Inertia::render(
                    'SuperAdmin/AddUniversity'
                );

            }
        )->name(
            'universities.create'
        );


        Route::get(
            '/universities/{university}',
            function ($university) {

                return Inertia::render(
                    'SuperAdmin/ViewUni',
                    [
                        'id' => $university,
                    ]
                );

            }
        )->name(
            'universities.show'
        );


        Route::get(
            '/universities/{university}/edit',
            function ($university) {

                return Inertia::render(
                    'SuperAdmin/EditUni',
                    [
                        'id' => $university,
                        'isEdit' => true,
                    ]
                );

            }
        )->name(
            'universities.edit'
        );


        /*
        |--------------------------------------------------------------------------
        | University Administrators
        |--------------------------------------------------------------------------
        */

        Route::get(
            '/university-administrators',
            function () {

                return Inertia::render(
                    'SuperAdmin/listUniAdmin'
                );

            }
        )->name(
            'university-administrators'
        );


        Route::get(
            '/university-administrators/list',
            [
                UniversityController::class,
                'universityAdministrators',
            ]
        )->name(
            'university-administrators.list'
        );


        /*
        |--------------------------------------------------------------------------
        | Profile
        |--------------------------------------------------------------------------
        */

        Route::get(
            '/profile',
            function () {

                return Inertia::render(
                    'SuperAdmin/Profile'
                );

            }
        )->name(
            'profile'
        );


        Route::get(
            '/profile/data',
            [
                ProfileController::class,
                'show',
            ]
        )->name(
            'profile.data'
        );


        Route::post(
            '/profile/photo',
            [
                ProfileController::class,
                'updatePhoto',
            ]
        )->name(
            'profile.photo'
        );


        /*
        |--------------------------------------------------------------------------
        | Change Password
        |--------------------------------------------------------------------------
        */

        Route::get(
            '/profile/change-password',
            function () {

                return Inertia::render(
                    'SuperAdmin/ChangePassword'
                );

            }
        )->name(
            'profile.change-password'
        );


        Route::put(
            '/profile/password',
            [
                SuperAdminAuthController::class,
                'updatePassword',
            ]
        )->name(
            'profile.password'
        );


        /*
        |--------------------------------------------------------------------------
        | University CRUD
        |--------------------------------------------------------------------------
        */

        Route::prefix(
            'university'
        )
            ->name(
                'university.'
            )
            ->group(function () {

                Route::get(
                    '/',
                    [
                        UniversityController::class,
                        'index',
                    ]
                )->name(
                    'index'
                );


                Route::post(
                    '/',
                    [
                        AddUniversityController::class,
                        'store',
                    ]
                )->name(
                    'store'
                );


                Route::get(
                    '/{university}',
                    [
                        UniversityController::class,
                        'show',
                    ]
                )->name(
                    'show'
                );


                Route::put(
                    '/{university}',
                    [
                        UniversityController::class,
                        'update',
                    ]
                )->name(
                    'update'
                );


                Route::delete(
                    '/{university}',
                    [
                        UniversityController::class,
                        'destroy',
                    ]
                )->name(
                    'destroy'
                );


                Route::patch(
                    '/{university}/status',
                    [
                        UniversityController::class,
                        'toggleStatus',
                    ]
                )->name(
                    'status'
                );


                Route::get(
                    '/pdf/{university}',
                    [
                        AddUniversityController::class,
                        'downloadPdf',
                    ]
                )
                    ->middleware(
                        'auth:superadmin'
                    )
                    ->name(
                        'pdf'
                    );


                Route::post(
                    '/email/{university}',
                    [
                        AddUniversityController::class,
                        'sendEmail',
                    ]
                )->name(
                    'email'
                );

            });

    });


/*
|--------------------------------------------------------------------------
| University Admin Routes
|--------------------------------------------------------------------------
*/

Route::prefix(
    'university-admin'
)
    ->name(
        'university-admin.'
    )
    ->group(function () {

        /*
        |--------------------------------------------------------------------------
        | Access Code
        |--------------------------------------------------------------------------
        */

        Route::get(
            '/access-code',
            function () {

                return Inertia::render(
                    'UniversityAdmin/AccessCode'
                );

            }
        )->name(
            'access-code'
        );


        Route::post(
            '/access-code/verify',
            [
                UniversityAccessController::class,
                'verify',
            ]
        )->name(
            'verify'
        );


        /*
        |--------------------------------------------------------------------------
        | Login
        |--------------------------------------------------------------------------
        */

        Route::get(
            '/login',
            [
                UniversityAdminAuthController::class,
                'create',
            ]
        )->name(
            'login'
        );


        Route::post(
            '/login',
            [
                UniversityAdminAuthController::class,
                'store',
            ]
        )->name(
            'login.store'
        );


        /*
        |--------------------------------------------------------------------------
        | Forgot Password
        |--------------------------------------------------------------------------
        */

        Route::get(
            '/forgot-password',
            function () {

                return Inertia::render(
                    'UniversityAdmin/ForgotPasswordPages/ForgotPassword'
                );

            }
        )->name(
            'forgot-password'
        );


        Route::get(
            '/forgot-password/verify-code',
            function () {

                return Inertia::render(
                    'UniversityAdmin/ForgotPasswordPages/VerifyCode',
                    [
                        'email' =>
                            request(
                                'email'
                            ),
                    ]
                );

            }
        )->name(
            'verify-code'
        );


        Route::get(
            '/reset-password',
            function () {

                return Inertia::render(
                    'UniversityAdmin/ForgotPasswordPages/ResetPassword',
                    [
                        'email' =>
                            request(
                                'email'
                            ),
                    ]
                );

            }
        )->name(
            'reset-password'
        );


        Route::get(
            '/password-reset-success',
            function () {

                return Inertia::render(
                    'UniversityAdmin/ForgotPasswordPages/PasswordResetSuccess'
                );

            }
        )->name(
            'password-reset-success'
        );


        Route::post(
            '/forgot-password',
            [
                ForgotPasswordController::class,
                'sendCode',
            ]
        )->name(
            'forgot-password.send'
        );


        Route::post(
            '/verify-code',
            [
                ForgotPasswordController::class,
                'verifyCode',
            ]
        )->name(
            'verify-code.submit'
        );


        Route::post(
            '/resend-code',
            [
                ForgotPasswordController::class,
                'resendCode',
            ]
        )->name(
            'resend-code'
        );


        Route::post(
            '/reset-password',
            [
                ForgotPasswordController::class,
                'resetPassword',
            ]
        )->name(
            'reset-password.submit'
        );

        /*
            |--------------------------------------------------------------------------
            | Logout
            |--------------------------------------------------------------------------
            */

            Route::get(
                '/logout',
                [
                    UniversityAdminAuthController::class,
                    'logoutPage',
                ]
            )->name(
                'logout.page'
            );


            Route::post(
                '/logout',
                [
                    UniversityAdminAuthController::class,
                    'destroy',
                ]
            )->name(
                'logout'
            );

        /*
        |--------------------------------------------------------------------------
        | Authenticated University Admin
        |--------------------------------------------------------------------------
        */

        Route::middleware(
            'auth:university_admin'
        )->group(function () {

            /*
            |--------------------------------------------------------------------------
            | Dashboard
            |--------------------------------------------------------------------------
            */

            Route::get(
                '/dashboard',
                [
                    UniversityAdminDashboardController::class,
                    'index',
                ]
            )->name(
                'dashboard'
            );


            /*
            |--------------------------------------------------------------------------
            | Component Dashboards
            |--------------------------------------------------------------------------
            |
            | University administrators stay authenticated with the
            | university_admin guard and may open the Instructor/Coordinator
            | dashboard directly for LTS, CWTS, or ROTC.
            |
            | Examples:
            |
            | /university-admin/components/lts
            | /university-admin/components/cwts
            | /university-admin/components/rotc
            |
            */

            Route::get(
                '/components/{component}',
                [
                    InstructorCoordinatorDashboardController::class,
                    'index',
                ]
            )
                ->where(
                    'component',
                    'lts|cwts|rotc|LTS|CWTS|ROTC'
                )
                ->name(
                    'components.dashboard'
                );


            /*
            |--------------------------------------------------------------------------
            | Profile
            |--------------------------------------------------------------------------
            */

            Route::get(
                '/uniadminprofile',
                [
                    UniversityAdminProfileController::class,
                    'index',
                ]
            )->name(
                'profile'
            );


            Route::post(
                '/uniadminprofile/photo',
                [
                    UniversityAdminProfileController::class,
                    'updatePhoto',
                ]
            )->name(
                'profile.photo'
            );


            Route::post(
                '/uniadminprofile/university',
                [
                    UniversityAdminProfileController::class,
                    'updateUniversityInformation',
                ]
            )->name(
                'profile.university.update'
            );


            /*
            |--------------------------------------------------------------------------
            | Change Password
            |--------------------------------------------------------------------------
            */

            Route::get(
                '/change-password',
                [
                    UniversityAdminAuthController::class,
                    'showChangePassword',
                ]
            )->name(
                'change-password'
            );


            Route::post(
                '/change-password',
                [
                    UniversityAdminAuthController::class,
                    'updatePassword',
                ]
            )->name(
                'change-password.update'
            );

            /*
            |--------------------------------------------------------------------------
            | Student Reports
            |--------------------------------------------------------------------------
            */

            Route::prefix(
                'reports'
            )
                ->name(
                    'reports.'
                )
                ->controller(
                    ReportController::class
                )
                ->group(function () {

                    Route::get(
                        '/',
                        function () {

                            return redirect()
                                ->route(
                                    'university-admin.reports.index',
                                    [
                                        'component' =>
                                            'lts',
                                    ]
                                );

                        }
                    )->name(
                        'home'
                    );

                    Route::get(
                        '/{component}',
                        'index'
                    )
                        ->where(
                            'component',
                            'lts|cwts|rotc'
                        )
                        ->name(
                            'index'
                        );

                    Route::patch(
                        '/{report}/status',
                        'updateStatus'
                    )
                        ->whereNumber(
                            'report'
                        )
                        ->name(
                            'status.update'
                        );

                    Route::post(
                        '/{report}/feedback',
                        'storeFeedback'
                    )
                        ->whereNumber(
                            'report'
                        )
                        ->name(
                            'feedback.store'
                        );

                    Route::patch(
                        '/{report}/feedback',
                        'updateFeedback'
                    )
                        ->whereNumber(
                            'report'
                        )
                        ->name(
                            'feedback.update'
                        );

                    Route::delete(
                        '/{report}/feedback',
                        'destroyFeedback'
                    )
                        ->whereNumber(
                            'report'
                        )
                        ->name(
                            'feedback.destroy'
                        );

                });




            /*
            |--------------------------------------------------------------------------
            | Student Registration
            |--------------------------------------------------------------------------
            */

            Route::prefix(
                'student-registration'
            )
                ->name(
                    'student-registration.'
                )
                ->controller(
                    StudentRegistrationController::class
                )
                ->group(function () {

                    Route::get(
                        '/',
                        'index'
                    )
                        ->name(
                            'index'
                        );


                    Route::post(
                        '/deadline',
                        'storeDeadline'
                    )
                        ->name(
                            'deadline.store'
                        );


                    Route::delete(
                        '/deadline',
                        'destroyDeadline'
                    )
                        ->name(
                            'deadline.destroy'
                        );


                    Route::get(
                        '/{student}',
                        'show'
                    )
                        ->whereNumber(
                            'student'
                        )
                        ->name(
                            'show'
                        );


                    Route::patch(
                        '/{student}/approve',
                        'approve'
                    )
                        ->whereNumber(
                            'student'
                        )
                        ->name(
                            'approve'
                        );


                    Route::get(
                        '/{student}/registration',
                        'registrationPage'
                    )
                        ->whereNumber(
                            'student'
                        )
                        ->name(
                            'registration'
                        );


                    Route::post(
                        '/{student}/generate-student-id',
                        'generateStudentId'
                    )
                        ->whereNumber(
                            'student'
                        )
                        ->name(
                            'generate-student-id'
                        );


                    Route::post(
                        '/{student}/generate-credentials',
                        'generateCredentials'
                    )
                        ->whereNumber(
                            'student'
                        )
                        ->name(
                            'generate-credentials'
                        );


                    Route::get(
                        '/{student}/id-qr-management',
                        'idQrManagement'
                    )
                        ->whereNumber(
                            'student'
                        )
                        ->name(
                            'id-qr-management'
                        );


                    Route::get(
                        '/{student}/qr-code',
                        'qrCode'
                    )
                        ->whereNumber(
                            'student'
                        )
                        ->name(
                            'qr-code'
                        );


                    Route::get(
                        '/{student}/signature',
                        'signature'
                    )
                        ->whereNumber(
                            'student'
                        )
                        ->name(
                            'signature'
                        );

                });


            /*
            |--------------------------------------------------------------------------
            | Instructors
            |--------------------------------------------------------------------------
            */

            Route::get(
                '/instructors',
                [
                    InstructorController::class,
                    'index',
                ]
            )->name(
                'instructors'
            );


            Route::get(
                '/instructors/create',
                [
                    InstructorController::class,
                    'create',
                ]
            )->name(
                'instructors.create'
            );


            Route::post(
                '/instructors',
                [
                    InstructorController::class,
                    'store',
                ]
            )->name(
                'instructors.store'
            );


            Route::get(
                '/instructors/{instructor}/new',
                [
                    InstructorController::class,
                    'showNewInstructor',
                ]
            )->name(
                'instructors.new'
            );


            Route::post(
                '/instructors/{instructor}/send-email',
                [
                    InstructorController::class,
                    'sendEmail',
                ]
            )->name(
                'instructors.send-email'
            );


            Route::get(
                '/instructors/{instructor}/edit',
                [
                    InstructorController::class,
                    'edit',
                ]
            )->name(
                'instructors.edit'
            );


            Route::put(
                '/instructors/{instructor}',
                [
                    InstructorController::class,
                    'update',
                ]
            )->name(
                'instructors.update'
            );


            Route::delete(
                '/instructors/{instructor}',
                [
                    InstructorController::class,
                    'destroy',
                ]
            )->name(
                'instructors.destroy'
            );


            /*
            |--------------------------------------------------------------------------
            | Coordinators
            |--------------------------------------------------------------------------
            */

            Route::get(
                '/coordinators',
                [
                    CoordinatorController::class,
                    'index',
                ]
            )->name(
                'coordinators'
            );


            Route::get(
                '/coordinators/create',
                [
                    CoordinatorController::class,
                    'create',
                ]
            )->name(
                'coordinators.create'
            );


            Route::post(
                '/coordinators',
                [
                    CoordinatorController::class,
                    'store',
                ]
            )->name(
                'coordinators.store'
            );


            Route::get(
                '/coordinators/{coordinator}/new',
                [
                    CoordinatorController::class,
                    'showNewCoordinator',
                ]
            )->name(
                'coordinators.new'
            );


            Route::post(
                '/coordinators/{coordinator}/send-email',
                [
                    CoordinatorController::class,
                    'sendEmail',
                ]
            )->name(
                'coordinators.send-email'
            );


            Route::get(
                '/coordinators/{coordinator}/edit',
                [
                    CoordinatorController::class,
                    'edit',
                ]
            )->name(
                'coordinators.edit'
            );


            Route::put(
                '/coordinators/{coordinator}',
                [
                    CoordinatorController::class,
                    'update',
                ]
            )->name(
                'coordinators.update'
            );


            Route::delete(
                '/coordinators/{coordinator}',
                [
                    CoordinatorController::class,
                    'destroy',
                ]
            )->name(
                'coordinators.destroy'
            );


            /*
            |--------------------------------------------------------------------------
            | Students
            |--------------------------------------------------------------------------
            */

            Route::prefix(
                'users/students'
            )
                ->name(
                    'students.'
                )
                ->controller(
                    StudentController::class
                )
                ->group(function () {

                    Route::get(
                        '/',
                        'index'
                    )
                        ->name(
                            'index'
                        );


                    Route::get(
                        '/{student}/edit',
                        'edit'
                    )
                        ->whereNumber(
                            'student'
                        )
                        ->name(
                            'edit'
                        );


                    Route::put(
                        '/{student}',
                        'update'
                    )
                        ->whereNumber(
                            'student'
                        )
                        ->name(
                            'update'
                        );


                    Route::get(
                        '/{student}',
                        'show'
                    )
                        ->whereNumber(
                            'student'
                        )
                        ->name(
                            'show'
                        );

                });

        });

    });




/*
|--------------------------------------------------------------------------
| Instructor / Coordinator Routes
|--------------------------------------------------------------------------
*/

Route::prefix(
    'instructor-coordinator'
)
    ->name(
        'instructor-coordinator.'
    )
    ->group(function () {

        /*
        |--------------------------------------------------------------------------
        | Access Code
        |--------------------------------------------------------------------------
        */

        Route::get(
            '/access-code',
            [
                InstructorCoordinatorAuthController::class,
                'accessCode',
            ]
        )->name(
            'access-code'
        );


        Route::post(
            '/access-code/verify',
            [
                InstructorCoordinatorAuthController::class,
                'verifyAccessCode',
            ]
        )->name(
            'access-code.verify'
        );


        /*
        |--------------------------------------------------------------------------
        | Login
        |--------------------------------------------------------------------------
        */

        Route::get(
            '/login',
            [
                InstructorCoordinatorAuthController::class,
                'loginPage',
            ]
        )->name(
            'login'
        );


        Route::post(
            '/login',
            [
                InstructorCoordinatorAuthController::class,
                'login',
            ]
        )->name(
            'login.store'
        );


        /*
        |--------------------------------------------------------------------------
        | Forgot Password
        |--------------------------------------------------------------------------
        */

        Route::get(
            '/forgot-password',
            [
                InstructorCoordinatorForgotPasswordController::class,
                'showForgotPassword',
            ]
        )->name(
            'forgot-password'
        );


        Route::post(
            '/forgot-password',
            [
                InstructorCoordinatorForgotPasswordController::class,
                'sendVerificationCode',
            ]
        )->name(
            'forgot-password.send'
        );


        Route::get(
            '/forgot-password/verify-code',
            [
                InstructorCoordinatorForgotPasswordController::class,
                'showVerifyCode',
            ]
        )->name(
            'forgot-password.verify-code'
        );


        Route::post(
            '/verify-code',
            [
                InstructorCoordinatorForgotPasswordController::class,
                'verifyCode',
            ]
        )->name(
            'verify-code'
        );


        Route::post(
            '/resend-code',
            [
                InstructorCoordinatorForgotPasswordController::class,
                'resendCode',
            ]
        )->name(
            'resend-code'
        );


        Route::get(
            '/reset-password',
            [
                InstructorCoordinatorForgotPasswordController::class,
                'showResetPassword',
            ]
        )->name(
            'reset-password'
        );


        Route::post(
            '/reset-password',
            [
                InstructorCoordinatorForgotPasswordController::class,
                'resetPassword',
            ]
        )->name(
            'reset-password.update'
        );


        Route::get(
            '/password-reset-success',
            [
                InstructorCoordinatorForgotPasswordController::class,
                'showPasswordResetSuccess',
            ]
        )->name(
            'password-reset-success'
        );


        /*
        |--------------------------------------------------------------------------
        | Dashboard
        |--------------------------------------------------------------------------
        */

        Route::get(
            '/dashboard',
            [
                InstructorCoordinatorDashboardController::class,
                'index',
            ]
        )
            ->middleware(
                'auth:university_admin,instructor,coordinator'
            )
            ->name(
                'dashboard'
            );


        /*
        |--------------------------------------------------------------------------
        | Component Dashboard Shortcut
        |--------------------------------------------------------------------------
        |
        | Allows an authenticated instructor/coordinator with multiple
        | components to switch context without logging out.
        |
        | Examples:
        |
        | /instructor-coordinator/components/LTS
        | /instructor-coordinator/components/CWTS
        | /instructor-coordinator/components/ROTC
        |
        */

        Route::get(
            '/components/{component}',
            [
                InstructorCoordinatorDashboardController::class,
                'index',
            ]
        )
            ->where(
                'component',
                'lts|cwts|rotc|LTS|CWTS|ROTC'
            )
            ->middleware(
                'auth:university_admin,instructor,coordinator'
            )
            ->name(
                'components.dashboard'
            );


        /*
        |--------------------------------------------------------------------------
        | ATTENDANCE
        |--------------------------------------------------------------------------
        |
        | Controller:
        |
        | App\Http\Controllers\InstructorCoordinators\AttendanceController
        |
        | Handles:
        |
        | - Attendance main page
        | - Save / update daily attendance schedule
        | - Remove daily attendance schedule
        | - QR Time In / Time Out scanning
        | - Student attendance history
        | - Student NSTP standing
        |
        */

        Route::prefix(
            'attendance'
        )
            ->name(
                'attendance.'
            )
            ->middleware(
                'auth:university_admin,instructor,coordinator'
            )
            ->controller(
                AttendanceController::class
            )
            ->group(function () {

                /*
                |--------------------------------------------------------------------------
                | Attendance Main Page
                |--------------------------------------------------------------------------
                |
                | GET /instructor-coordinator/attendance
                |
                | Route:
                | instructor-coordinator.attendance.index
                |
                */

                Route::get(
                    '/',
                    'index'
                )
                    ->name(
                        'index'
                    );


                /*
                |--------------------------------------------------------------------------
                | Save / Update Attendance Time
                |--------------------------------------------------------------------------
                |
                | PUT /instructor-coordinator/attendance/schedule
                |
                | Controller:
                | AttendanceController@updateSchedule
                |
                | Route:
                | instructor-coordinator.attendance.schedule.update
                |
                */

                Route::put(
                    '/schedule',
                    'updateSchedule'
                )
                    ->middleware(
                        'throttle:30,1'
                    )
                    ->name(
                        'schedule.update'
                    );


                /*
                |--------------------------------------------------------------------------
                | Delete / Remove Attendance Time
                |--------------------------------------------------------------------------
                |
                | DELETE /instructor-coordinator/attendance/schedule/{component}
                |
                | Examples:
                |
                | /attendance/schedule/ROTC
                | /attendance/schedule/LTS
                | /attendance/schedule/CWTS
                |
                | Controller:
                | AttendanceController@destroySchedule
                |
                | Route:
                | instructor-coordinator.attendance.schedule.destroy
                |
                */

                Route::delete(
                    '/schedule/{component}',
                    'destroySchedule'
                )
                    ->whereIn(
                        'component',
                        [
                            'ROTC',
                            'LTS',
                            'CWTS',
                        ]
                    )
                    ->middleware(
                        'throttle:30,1'
                    )
                    ->name(
                        'schedule.destroy'
                    );


                /*
                |--------------------------------------------------------------------------
                | QR Attendance Scanner
                |--------------------------------------------------------------------------
                |
                | POST /instructor-coordinator/attendance/scan
                |
                | First valid scan:
                | TIME IN
                |
                | Second valid scan:
                | TIME OUT
                |
                | Controller:
                | AttendanceController@scan
                |
                | Route:
                | instructor-coordinator.attendance.scan
                |
                */

                Route::post(
                    '/scan',
                    'scan'
                )
                    ->middleware(
                        'throttle:30,1'
                    )
                    ->name(
                        'scan'
                    );


                /*
                |--------------------------------------------------------------------------
                | Edit Student Attendance Standing
                |--------------------------------------------------------------------------
                |
                | GET
                | /instructor-coordinator/attendance/students/{student}/edit
                |
                */

                Route::get(
                    '/students/{student}/edit',
                    'edit'
                )
                    ->whereNumber(
                        'student'
                    )
                    ->name(
                        'students.edit'
                    );


                /*
                |--------------------------------------------------------------------------
                | Update Student NSTP Standing
                |--------------------------------------------------------------------------
                |
                | PATCH
                | /instructor-coordinator/attendance/students/{student}/status
                |
                | Allowed values:
                |
                | ACTIVE
                | WARNING FOR DROPOUT
                | DROPOUT
                |
                */

                Route::patch(
                    '/students/{student}/status',
                    'updateStatus'
                )
                    ->whereNumber(
                        'student'
                    )
                    ->name(
                        'students.status.update'
                    );


                /*
                |--------------------------------------------------------------------------
                | View Student Attendance History
                |--------------------------------------------------------------------------
                |
                | GET
                | /instructor-coordinator/attendance/students/{student}
                |
                */

                Route::get(
                    '/students/{student}',
                    'show'
                )
                    ->whereNumber(
                        'student'
                    )
                    ->name(
                        'students.show'
                    );

            });


                    /*
                    |--------------------------------------------------------------------------
                    | Excuse Letters
                    |--------------------------------------------------------------------------
                    */

                    Route::prefix(
                        'excuse-letters'
                    )
                        ->name(
                            'excuse-letters.'
                        )
                        ->middleware(
                            'auth:university_admin,instructor,coordinator'
                        )
                        ->controller(
                            ExcuseLetterController::class
                        )
                        ->group(function () {

                            Route::get(
                                '/',
                                'index'
                            )
                                ->name(
                                    'index'
                                );

                            Route::patch(
                                '/{excuseLetter}/approve',
                                'approve'
                            )
                                ->whereNumber(
                                    'excuseLetter'
                                )
                                ->name(
                                    'approve'
                                );

                            Route::patch(
                                '/{excuseLetter}/reject',
                                'reject'
                            )
                                ->whereNumber(
                                    'excuseLetter'
                                )
                                ->name(
                                    'reject'
                                );

                        });


        /*
        |--------------------------------------------------------------------------
        | Announcements
        |--------------------------------------------------------------------------
        */

        Route::get(
            '/announcements',
            [
                AnnouncementController::class,
                'index',
            ]
        )
            ->middleware(
                'auth:university_admin,instructor,coordinator'
            )
            ->name(
                'announcements.index'
            );


        Route::post(
            '/announcements',
            [
                AnnouncementController::class,
                'store',
            ]
        )
            ->middleware(
                'auth:university_admin,instructor,coordinator'
            )
            ->name(
                'announcements.store'
            );


        Route::put(
            '/announcements/{announcement}',
            [
                AnnouncementController::class,
                'update',
            ]
        )
            ->middleware(
                'auth:university_admin,instructor,coordinator'
            )
            ->name(
                'announcements.update'
            );


        Route::delete(
            '/announcements/{announcement}',
            [
                AnnouncementController::class,
                'destroy',
            ]
        )
            ->middleware(
                'auth:university_admin,instructor,coordinator'
            )
            ->name(
                'announcements.destroy'
            );


        /*
        |--------------------------------------------------------------------------
        | Schedules
        |--------------------------------------------------------------------------
        */

        Route::get(
            '/schedules',
            [
                ScheduleController::class,
                'index',
            ]
        )
            ->middleware(
                'auth:university_admin,instructor,coordinator'
            )
            ->name(
                'schedules.index'
            );


        Route::post(
            '/schedules',
            [
                ScheduleController::class,
                'store',
            ]
        )
            ->middleware(
                'auth:university_admin,instructor,coordinator'
            )
            ->name(
                'schedules.store'
            );


        Route::put(
            '/schedules/{schedule}',
            [
                ScheduleController::class,
                'update',
            ]
        )
            ->middleware(
                'auth:university_admin,instructor,coordinator'
            )
            ->name(
                'schedules.update'
            );


        Route::delete(
            '/schedules/{schedule}',
            [
                ScheduleController::class,
                'destroy',
            ]
        )
            ->middleware(
                'auth:university_admin,instructor,coordinator'
            )
            ->name(
                'schedules.destroy'
            );


        /*
        |--------------------------------------------------------------------------
        | Profile
        |--------------------------------------------------------------------------
        */

        Route::get(
            '/profile',
            [
                InstructorCoordinatorProfileController::class,
                'index',
            ]
        )->name(
            'profile'
        );


        Route::post(
            '/profile/photo',
            [
                InstructorCoordinatorProfileController::class,
                'updatePhoto',
            ]
        )->name(
            'profile.photo'
        );


        /*
        |--------------------------------------------------------------------------
        | Change Password
        |--------------------------------------------------------------------------
        */

        Route::get(
            '/change-password',
            [
                InstructorCoordinatorProfileController::class,
                'editPassword',
            ]
        )->name(
            'change-password'
        );


        Route::post(
            '/change-password',
            [
                InstructorCoordinatorProfileController::class,
                'updatePassword',
            ]
        )->name(
            'change-password.update'
        );


        /*
        |--------------------------------------------------------------------------
        | Logout Page
        |--------------------------------------------------------------------------
        */

        Route::get(
            '/logout-page',
            [
                InstructorCoordinatorAuthController::class,
                'logoutPage',
            ]
        )->name(
            'logout-page'
        );


        Route::post(
            '/logout-page/login',
            [
                InstructorCoordinatorAuthController::class,
                'loginSavedAccount',
            ]
        )->name(
            'logout-page.login'
        );


        Route::post(
            '/logout',
            [
                InstructorCoordinatorAuthController::class,
                'logout',
            ]
        )->name(
            'logout'
        );




        /*
        |--------------------------------------------------------------------------
        | PDF Exports
        |--------------------------------------------------------------------------
        |
        | Formal legal-size NSTP PDF reports for instructors/coordinators.
        |
        */

        Route::prefix('exports')
            ->name('exports.')
            ->middleware('auth:university_admin,instructor,coordinator')
            ->controller(PdfExportController::class)
            ->group(function () {

                Route::get(
                    '/attendance/daily.pdf',
                    'dailyAttendance'
                )->name('attendance.daily');

                Route::get(
                    '/attendance/students/{student}.pdf',
                    'studentAttendance'
                )
                    ->whereNumber('student')
                    ->name('attendance.student');

                Route::get(
                    '/students.pdf',
                    'studentList'
                )->name('students.list');

                Route::get(
                    '/students/{student}.pdf',
                    'studentProfile'
                )
                    ->whereNumber('student')
                    ->name('students.profile');
            });

        /*
        |--------------------------------------------------------------------------
        | Student Information
        |--------------------------------------------------------------------------
        */

        Route::prefix(
            'students'
        )
            ->name(
                'students.'
            )
            ->middleware(
                'auth:university_admin,instructor,coordinator'
            )
            ->controller(
                StudentInformationController::class
            )
            ->group(function () {

                /*
                |--------------------------------------------------------------------------
                | Student List
                |--------------------------------------------------------------------------
                */

                Route::get(
                    '/',
                    'index'
                )
                    ->name(
                        'index'
                    );


                /*
                |--------------------------------------------------------------------------
                | Edit Student
                |--------------------------------------------------------------------------
                */

                Route::get(
                    '/{student}/edit',
                    'edit'
                )
                    ->whereNumber(
                        'student'
                    )
                    ->name(
                        'edit'
                    );


                /*
                |--------------------------------------------------------------------------
                | Update Student
                |--------------------------------------------------------------------------
                */

                Route::put(
                    '/{student}',
                    'update'
                )
                    ->whereNumber(
                        'student'
                    )
                    ->name(
                        'update'
                    );


                /*
                |--------------------------------------------------------------------------
                | Protected QR Code
                |--------------------------------------------------------------------------
                */

                Route::get(
                    '/{student}/qr-code',
                    'qrCode'
                )
                    ->whereNumber(
                        'student'
                    )
                    ->name(
                        'qr-code'
                    );


                /*
                |--------------------------------------------------------------------------
                | Protected Digital Signature
                |--------------------------------------------------------------------------
                */

                Route::get(
                    '/{student}/signature',
                    'signature'
                )
                    ->whereNumber(
                        'student'
                    )
                    ->name(
                        'signature'
                    );


                /*
                |--------------------------------------------------------------------------
                | Student Profile
                |--------------------------------------------------------------------------
                */

                Route::get(
                    '/{student}',
                    'show'
                )
                    ->whereNumber(
                        'student'
                    )
                    ->name(
                        'show'
                    );

            });

    });


/*
|--------------------------------------------------------------------------
| Settings Routes
|--------------------------------------------------------------------------
*/

require __DIR__.'/settings.php';