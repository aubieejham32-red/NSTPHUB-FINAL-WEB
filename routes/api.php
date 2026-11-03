<?php

use App\Http\Controllers\Api\Mobile\DigitalStudentIdController;
use App\Http\Controllers\Api\Mobile\GeneralRegistrationController;
use App\Http\Controllers\Api\Mobile\HomepageController;
use App\Http\Controllers\Api\Mobile\MobileAccountSettingsController;
use App\Http\Controllers\Api\Mobile\MobileAnnouncementController;
use App\Http\Controllers\Api\Mobile\MobileAttendanceController;
use App\Http\Controllers\Api\Mobile\MobileAuthController;
use App\Http\Controllers\Api\Mobile\MobileExcuseLetterController;
use App\Http\Controllers\Api\Mobile\MobileForgotPasswordController;
use App\Http\Controllers\Api\Mobile\MobileLogoutController;
use App\Http\Controllers\Api\Mobile\MobileProfileController;
use App\Http\Controllers\Api\Mobile\MobileReportController;
use App\Http\Controllers\Api\Mobile\MobileScheduleController;
use App\Http\Controllers\Api\Mobile\RegistrationConfirmationController;
use App\Http\Controllers\Api\Mobile\RegistrationWaitEditController;
use App\Http\Controllers\Api\Mobile\RotcConfirmEnrollmentController;
use App\Http\Controllers\Api\Mobile\RotcEnrollmentController;
use App\Http\Controllers\Api\Mobile\UniversityController;
use App\Http\Middleware\EnsureMobileStudent;


use App\Http\Controllers\Api\AdminMobile\AdminMobileAuthController;
use App\Http\Controllers\Api\AdminMobile\AdminMobileDashboardController;
use App\Http\Controllers\Api\AdminMobile\AdminMobileUniversityController;
use App\Http\Middleware\EnsureMobileUniversityAdmin;

use App\Http\Controllers\InstructorCoordinators\AttendanceController as SharedAttendanceController;
use App\Http\Controllers\InstructorCoordinators\ExcuseLetterController as SharedExcuseLetterController;
use App\Http\Controllers\InstructorCoordinators\InstructorCoordinatorProfileController as SharedProfileController;
use App\Http\Controllers\Auth\InstructorCoordinatorForgotPasswordController as SharedForgotPasswordController;




use Illuminate\Support\Facades\Route;


/*
|--------------------------------------------------------------------------
| NSTP HUB MOBILE API
|--------------------------------------------------------------------------
|
| Base URL:
|
| /api/mobile
|
*/

Route::prefix('mobile')
    ->group(function () {

        /*
        |--------------------------------------------------------------------------
        | PUBLIC ROUTES
        |--------------------------------------------------------------------------
        */

        Route::post(
            '/university/verify-access-code',
            [
                UniversityController::class,
                'verifyAccessCode',
            ]
        )
            ->middleware('throttle:5,1')
            ->name(
                'api.mobile.university.verify-access-code'
            );


        Route::get(
            '/universities/{university}/logo',
            [
                UniversityController::class,
                'logo',
            ]
        )
            ->whereNumber('university')
            ->middleware('throttle:60,1')
            ->name(
                'api.mobile.university.logo'
            );


        /*
        |--------------------------------------------------------------------------
        | LOGIN
        |--------------------------------------------------------------------------
        */

        Route::post(
            '/login',
            [
                MobileAuthController::class,
                'login',
            ]
        )
            ->middleware('throttle:10,1')
            ->name(
                'api.mobile.login'
            );


        Route::post(
            '/google-login',
            [
                MobileAuthController::class,
                'googleLogin',
            ]
        )
            ->middleware('throttle:10,1')
            ->name(
                'api.mobile.google-login'
            );


        Route::post(
            '/google-set-password',
            [
                MobileAuthController::class,
                'setGooglePassword',
            ]
        )
            ->middleware('throttle:10,1')
            ->name(
                'api.mobile.google-set-password'
            );


        /*
        |--------------------------------------------------------------------------
        | FORGOT PASSWORD
        |--------------------------------------------------------------------------
        |
        | Public student account recovery flow:
        |
        | 1. Send 6-digit verification code
        | 2. Verify verification code
        | 3. Receive temporary reset token
        | 4. Reset password
        |
        */


        /*
        |--------------------------------------------------------------------------
        | Send Verification Code
        |--------------------------------------------------------------------------
        |
        | POST
        |
        | /api/mobile/forgot-password/send-code
        |
        */

        Route::post(
            '/forgot-password/send-code',
            [
                MobileForgotPasswordController::class,
                'sendCode',
            ]
        )
            ->middleware('throttle:3,1')
            ->name(
                'api.mobile.forgot-password.send-code'
            );


        /*
        |--------------------------------------------------------------------------
        | Verify Verification Code
        |--------------------------------------------------------------------------
        |
        | POST
        |
        | /api/mobile/forgot-password/verify-code
        |
        */

        Route::post(
            '/forgot-password/verify-code',
            [
                MobileForgotPasswordController::class,
                'verifyCode',
            ]
        )
            ->middleware('throttle:10,1')
            ->name(
                'api.mobile.forgot-password.verify-code'
            );


        /*
        |--------------------------------------------------------------------------
        | Resend Verification Code
        |--------------------------------------------------------------------------
        |
        | POST
        |
        | /api/mobile/forgot-password/resend-code
        |
        */

        Route::post(
            '/forgot-password/resend-code',
            [
                MobileForgotPasswordController::class,
                'resendCode',
            ]
        )
            ->middleware('throttle:3,1')
            ->name(
                'api.mobile.forgot-password.resend-code'
            );


        /*
        |--------------------------------------------------------------------------
        | Reset Password
        |--------------------------------------------------------------------------
        |
        | POST
        |
        | /api/mobile/forgot-password/reset-password
        |
        */

        Route::post(
            '/forgot-password/reset-password',
            [
                MobileForgotPasswordController::class,
                'resetPassword',
            ]
        )
            ->middleware('throttle:5,1')
            ->name(
                'api.mobile.forgot-password.reset-password'
            );


        /*
        |--------------------------------------------------------------------------
        | SAVED STUDENT ACCOUNT LOGIN
        |--------------------------------------------------------------------------
        |
        | POST
        |
        | /api/mobile/logout-page/login
        |
        | Public because the saved account is already signed out.
        |
        */

        Route::post(
            '/logout-page/login',
            [
                MobileLogoutController::class,
                'loginSavedAccount',
            ]
        )
            ->middleware('throttle:5,1')
            ->name(
                'api.mobile.logout-page.login'
            );


        /*
        |--------------------------------------------------------------------------
        | PUBLIC EXCUSE LETTER EVIDENCE
        |--------------------------------------------------------------------------
        */

        Route::get(
            '/excuse-letters/{excuseLetter}/evidence',
            [
                MobileExcuseLetterController::class,
                'evidence',
            ]
        )
            ->whereNumber('excuseLetter')
            ->middleware('throttle:60,1')
            ->name(
                'api.mobile.excuse-letters.evidence'
            );


        /*
        |--------------------------------------------------------------------------
        | PROTECTED STUDENT ROUTES
        |--------------------------------------------------------------------------
        */

        Route::middleware([
            'auth:sanctum',
            EnsureMobileStudent::class,
        ])
            ->group(function () {

                /*
                |--------------------------------------------------------------------------
                | CURRENT STUDENT
                |--------------------------------------------------------------------------
                */

                Route::get(
                    '/me',
                    [
                        MobileAuthController::class,
                        'me',
                    ]
                )
                    ->middleware('throttle:60,1')
                    ->name(
                        'api.mobile.me'
                    );


                /*
                |--------------------------------------------------------------------------
                | ACCOUNT SETTINGS
                |--------------------------------------------------------------------------
                |
                | Change Password
                |
                | PUT
                |
                | /api/mobile/account/password
                |
                | Body:
                |
                | {
                |     "current_password": "...",
                |     "password": "...",
                |     "password_confirmation": "..."
                | }
                |
                */

                Route::put(
                    '/account/password',
                    [
                        MobileAccountSettingsController::class,
                        'changePassword',
                    ]
                )
                    ->middleware('throttle:5,1')
                    ->name(
                        'api.mobile.account.password'
                    );


                /*
                |--------------------------------------------------------------------------
                | CURRENT UNIVERSITY
                |--------------------------------------------------------------------------
                */

                Route::get(
                    '/university',
                    [
                        UniversityController::class,
                        'current',
                    ]
                )
                    ->middleware('throttle:60,1')
                    ->name(
                        'api.mobile.university'
                    );


                /*
                |--------------------------------------------------------------------------
                | HOMEPAGE
                |--------------------------------------------------------------------------
                */

                Route::get(
                    '/homepage',
                    [
                        HomepageController::class,
                        'show',
                    ]
                )
                    ->middleware('throttle:30,1')
                    ->name(
                        'api.mobile.homepage.show'
                    );


                /*
                |--------------------------------------------------------------------------
                | SCHEDULES
                |--------------------------------------------------------------------------
                */

                Route::get(
                    '/schedules',
                    [
                        MobileScheduleController::class,
                        'index',
                    ]
                )
                    ->middleware('throttle:30,1')
                    ->name(
                        'api.mobile.schedules.index'
                    );


                /*
                |--------------------------------------------------------------------------
                | ANNOUNCEMENTS
                |--------------------------------------------------------------------------
                */

                Route::get(
                    '/announcements',
                    [
                        MobileAnnouncementController::class,
                        'index',
                    ]
                )
                    ->middleware('throttle:30,1')
                    ->name(
                        'api.mobile.announcements.index'
                    );


                /*
                |--------------------------------------------------------------------------
                | ATTENDANCE RECORDS
                |--------------------------------------------------------------------------
                */

                Route::get(
                    '/attendance-records',
                    [
                        MobileAttendanceController::class,
                        'index',
                    ]
                )
                    ->middleware('throttle:30,1')
                    ->name(
                        'api.mobile.attendance-records.index'
                    );


                /*
                |--------------------------------------------------------------------------
                | REPORTS
                |--------------------------------------------------------------------------
                */

                Route::get(
                    '/reports',
                    [
                        MobileReportController::class,
                        'index',
                    ]
                )
                    ->middleware('throttle:30,1')
                    ->name(
                        'api.mobile.reports.index'
                    );


                Route::post(
                    '/reports',
                    [
                        MobileReportController::class,
                        'store',
                    ]
                )
                    ->middleware('throttle:10,1')
                    ->name(
                        'api.mobile.reports.store'
                    );


                Route::post(
                    '/reports/{report}',
                    [
                        MobileReportController::class,
                        'update',
                    ]
                )
                    ->whereNumber('report')
                    ->middleware('throttle:10,1')
                    ->name(
                        'api.mobile.reports.update'
                    );


                Route::delete(
                    '/reports/{report}',
                    [
                        MobileReportController::class,
                        'destroy',
                    ]
                )
                    ->whereNumber('report')
                    ->middleware('throttle:10,1')
                    ->name(
                        'api.mobile.reports.destroy'
                    );


                /*
                |--------------------------------------------------------------------------
                | EXCUSE LETTERS
                |--------------------------------------------------------------------------
                */

                Route::get(
                    '/excuse-letters',
                    [
                        MobileExcuseLetterController::class,
                        'index',
                    ]
                )
                    ->middleware('throttle:30,1')
                    ->name(
                        'api.mobile.excuse-letters.index'
                    );


                Route::post(
                    '/excuse-letters',
                    [
                        MobileExcuseLetterController::class,
                        'store',
                    ]
                )
                    ->middleware('throttle:10,1')
                    ->name(
                        'api.mobile.excuse-letters.store'
                    );


                Route::post(
                    '/excuse-letters/{excuseLetter}',
                    [
                        MobileExcuseLetterController::class,
                        'update',
                    ]
                )
                    ->whereNumber('excuseLetter')
                    ->middleware('throttle:10,1')
                    ->name(
                        'api.mobile.excuse-letters.update'
                    );


                Route::delete(
                    '/excuse-letters/{excuseLetter}',
                    [
                        MobileExcuseLetterController::class,
                        'destroy',
                    ]
                )
                    ->whereNumber('excuseLetter')
                    ->middleware('throttle:10,1')
                    ->name(
                        'api.mobile.excuse-letters.destroy'
                    );


                /*
                |--------------------------------------------------------------------------
                | DIGITAL STUDENT ID
                |--------------------------------------------------------------------------
                */

                Route::get(
                    '/digital-student-id',
                    [
                        DigitalStudentIdController::class,
                        'show',
                    ]
                )
                    ->middleware('throttle:30,1')
                    ->name(
                        'api.mobile.digital-student-id.show'
                    );


                Route::get(
                    '/digital-student-id/qr-code',
                    [
                        DigitalStudentIdController::class,
                        'qrCode',
                    ]
                )
                    ->middleware('throttle:60,1')
                    ->name(
                        'api.mobile.digital-student-id.qr-code'
                    );


                /*
                |--------------------------------------------------------------------------
                | SHARED STUDENT PROFILE
                |--------------------------------------------------------------------------
                |
                | Available to:
                |
                | - CWTS
                | - LTS
                | - ROTC
                |
                */

                Route::get(
                    '/profile',
                    [
                        MobileProfileController::class,
                        'show',
                    ]
                )
                    ->middleware('throttle:30,1')
                    ->name(
                        'api.mobile.profile.show'
                    );


                Route::put(
                    '/profile',
                    [
                        MobileProfileController::class,
                        'update',
                    ]
                )
                    ->middleware('throttle:10,1')
                    ->name(
                        'api.mobile.profile.update'
                    );


                Route::post(
                    '/profile/signature',
                    [
                        MobileProfileController::class,
                        'updateSignature',
                    ]
                )
                    ->middleware('throttle:10,1')
                    ->name(
                        'api.mobile.profile.signature'
                    );


                Route::post(
                    '/profile/photo',
                    [
                        MobileProfileController::class,
                        'updateProfilePhoto',
                    ]
                )
                    ->middleware('throttle:10,1')
                    ->name(
                        'api.mobile.profile.photo'
                    );


                /*
                |--------------------------------------------------------------------------
                | LEGACY ROTC PROFILE ROUTES
                |--------------------------------------------------------------------------
                |
                | Keep these while older mobile code is still being migrated.
                |
                */

                Route::get(
                    '/rotc-profile',
                    [
                        MobileProfileController::class,
                        'show',
                    ]
                )
                    ->middleware('throttle:30,1')
                    ->name(
                        'api.mobile.rotc-profile.show'
                    );


                Route::put(
                    '/rotc-profile',
                    [
                        MobileProfileController::class,
                        'update',
                    ]
                )
                    ->middleware('throttle:10,1')
                    ->name(
                        'api.mobile.rotc-profile.update'
                    );


                Route::post(
                    '/rotc-profile/signature',
                    [
                        MobileProfileController::class,
                        'updateSignature',
                    ]
                )
                    ->middleware('throttle:10,1')
                    ->name(
                        'api.mobile.rotc-profile.signature'
                    );


                Route::post(
                    '/rotc-profile/photo',
                    [
                        MobileProfileController::class,
                        'updateProfilePhoto',
                    ]
                )
                    ->middleware('throttle:10,1')
                    ->name(
                        'api.mobile.rotc-profile.photo'
                    );


                /*
                |--------------------------------------------------------------------------
                | GENERAL NSTP REGISTRATION
                |--------------------------------------------------------------------------
                */

                Route::get(
                    '/general-registration',
                    [
                        GeneralRegistrationController::class,
                        'show',
                    ]
                )
                    ->middleware('throttle:30,1')
                    ->name(
                        'api.mobile.general-registration.show'
                    );


                Route::match(
                    [
                        'post',
                        'put',
                    ],
                    '/general-registration',
                    [
                        GeneralRegistrationController::class,
                        'save',
                    ]
                )
                    ->middleware('throttle:15,1')
                    ->name(
                        'api.mobile.general-registration.save'
                    );


                Route::post(
                    '/general-registration/signature',
                    [
                        GeneralRegistrationController::class,
                        'signature',
                    ]
                )
                    ->middleware('throttle:10,1')
                    ->name(
                        'api.mobile.general-registration.signature'
                    );


                /*
                |--------------------------------------------------------------------------
                | REGISTRATION WAIT / EDIT
                |--------------------------------------------------------------------------
                */

                Route::get(
                    '/registration-wait-edit',
                    [
                        RegistrationWaitEditController::class,
                        'show',
                    ]
                )
                    ->middleware('throttle:30,1')
                    ->name(
                        'api.mobile.registration-wait-edit.show'
                    );


                Route::patch(
                    '/registration-wait-edit/component',
                    [
                        RegistrationWaitEditController::class,
                        'updateComponent',
                    ]
                )
                    ->middleware('throttle:10,1')
                    ->name(
                        'api.mobile.registration-wait-edit.component'
                    );


                /*
                |--------------------------------------------------------------------------
                | REGISTRATION CONFIRMATION
                |--------------------------------------------------------------------------
                */

                Route::get(
                    '/registration-confirmation',
                    [
                        RegistrationConfirmationController::class,
                        'show',
                    ]
                )
                    ->middleware('throttle:30,1')
                    ->name(
                        'api.mobile.registration-confirmation.show'
                    );


                /*
                |--------------------------------------------------------------------------
                | ROTC ENROLLMENT
                |--------------------------------------------------------------------------
                */

                Route::get(
                    '/rotc-enrollment',
                    [
                        RotcEnrollmentController::class,
                        'show',
                    ]
                )
                    ->middleware('throttle:30,1')
                    ->name(
                        'api.mobile.rotc-enrollment.show'
                    );


                Route::match(
                    [
                        'post',
                        'put',
                    ],
                    '/rotc-enrollment/step-1',
                    [
                        RotcEnrollmentController::class,
                        'saveStepOne',
                    ]
                )
                    ->middleware('throttle:15,1')
                    ->name(
                        'api.mobile.rotc-enrollment.step-one'
                    );


                Route::match(
                    [
                        'post',
                        'put',
                    ],
                    '/rotc-enrollment/step-2',
                    [
                        RotcEnrollmentController::class,
                        'saveStepTwo',
                    ]
                )
                    ->middleware('throttle:15,1')
                    ->name(
                        'api.mobile.rotc-enrollment.step-two'
                    );


                Route::match(
                    [
                        'post',
                        'put',
                    ],
                    '/rotc-enrollment/step-3',
                    [
                        RotcEnrollmentController::class,
                        'saveStepThree',
                    ]
                )
                    ->middleware('throttle:15,1')
                    ->name(
                        'api.mobile.rotc-enrollment.step-three'
                    );


                Route::match(
                    [
                        'post',
                        'put',
                    ],
                    '/rotc-enrollment/step-4',
                    [
                        RotcEnrollmentController::class,
                        'saveStepFour',
                    ]
                )
                    ->middleware('throttle:10,1')
                    ->name(
                        'api.mobile.rotc-enrollment.step-four'
                    );


                Route::get(
                    '/rotc-enrollment/confirmation',
                    [
                        RotcConfirmEnrollmentController::class,
                        'show',
                    ]
                )
                    ->middleware('throttle:30,1')
                    ->name(
                        'api.mobile.rotc-enrollment.confirmation'
                    );


                /*
                |--------------------------------------------------------------------------
                | ACCOUNT CHOOSER LOGOUT
                |--------------------------------------------------------------------------
                */

                Route::post(
                    '/logout-page/logout',
                    [
                        MobileLogoutController::class,
                        'logout',
                    ]
                )
                    ->middleware('throttle:10,1')
                    ->name(
                        'api.mobile.logout-page.logout'
                    );


                /*
                |--------------------------------------------------------------------------
                | LEGACY / DIRECT LOGOUT
                |--------------------------------------------------------------------------
                */

                Route::post(
                    '/logout',
                    [
                        MobileAuthController::class,
                        'logout',
                    ]
                )
                    ->middleware('throttle:10,1')
                    ->name(
                        'api.mobile.logout'
                    );
            });
    });









/*
|--------------------------------------------------------------------------
| NSTP HUB ADMIN MOBILE API
|--------------------------------------------------------------------------
|
| Base URL: /api/admin-mobile
|
| This is separate from the student /api/mobile API so administrator tokens,
| authorization-code sessions, and permissions remain isolated.
|
*/
Route::prefix('admin-mobile')
    ->group(function () {
        Route::post(
            '/university/verify-access-code',
            [
                AdminMobileUniversityController::class,
                'verifyAccessCode',
            ]
        )
            ->middleware('throttle:5,1')
            ->name('api.admin-mobile.university.verify-access-code');

        Route::post(
            '/login',
            [
                AdminMobileAuthController::class,
                'login',
            ]
        )
            ->middleware('throttle:10,1')
            ->name('api.admin-mobile.login');

        Route::post(
            '/google-login',
            [
                AdminMobileAuthController::class,
                'googleLogin',
            ]
        )
            ->middleware('throttle:10,1')
            ->name('api.admin-mobile.google-login');

        Route::post(
            '/forgot-password',
            [SharedForgotPasswordController::class, 'mobileSendVerificationCode']
        )->middleware('throttle:5,1');

        Route::post(
            '/forgot-password/verify-code',
            [SharedForgotPasswordController::class, 'mobileVerifyCode']
        )->middleware('throttle:10,1');

        Route::post(
            '/forgot-password/resend-code',
            [SharedForgotPasswordController::class, 'mobileResendCode']
        )->middleware('throttle:5,1');

        Route::post(
            '/forgot-password/reset',
            [SharedForgotPasswordController::class, 'mobileResetPassword']
        )->middleware('throttle:5,1');

        Route::middleware([
            'auth:sanctum',
            EnsureMobileUniversityAdmin::class,
        ])
            ->group(function () {
                Route::get(
                    '/me',
                    [
                        AdminMobileAuthController::class,
                        'me',
                    ]
                )
                    ->middleware('throttle:60,1')
                    ->name('api.admin-mobile.me');

                Route::get(
                    '/dashboard',
                    [
                        AdminMobileDashboardController::class,
                        'index',
                    ]
                )
                    ->middleware('throttle:60,1')
                    ->name('api.admin-mobile.dashboard');

                Route::get(
                    '/attendance',
                    [SharedAttendanceController::class, 'index']
                )->name('api.admin-mobile.attendance.index');

                Route::put(
                    '/attendance/schedule',
                    [SharedAttendanceController::class, 'updateSchedule']
                )->name('api.admin-mobile.attendance.schedule.update');

                Route::delete(
                    '/attendance/schedule/{component}',
                    [SharedAttendanceController::class, 'destroySchedule']
                )->name('api.admin-mobile.attendance.schedule.destroy');

                Route::post(
                    '/attendance/scan',
                    [SharedAttendanceController::class, 'scan']
                )->name('api.admin-mobile.attendance.scan');

                Route::get(
                    '/attendance/students/{student}',
                    [SharedAttendanceController::class, 'show']
                )->whereNumber('student')
                    ->name('api.admin-mobile.attendance.students.show');

                Route::patch(
                    '/attendance/students/{student}/status',
                    [SharedAttendanceController::class, 'updateStatus']
                )->whereNumber('student')
                    ->name('api.admin-mobile.attendance.students.status.update');

                Route::get(
                    '/excuse-letters',
                    [SharedExcuseLetterController::class, 'index']
                )->name('api.admin-mobile.excuse-letters.index');

                Route::patch(
                    '/excuse-letters/{excuseLetter}/approve',
                    [SharedExcuseLetterController::class, 'approve']
                )->whereNumber('excuseLetter')
                    ->name('api.admin-mobile.excuse-letters.approve');

                Route::patch(
                    '/excuse-letters/{excuseLetter}/reject',
                    [SharedExcuseLetterController::class, 'reject']
                )->whereNumber('excuseLetter')
                    ->name('api.admin-mobile.excuse-letters.reject');

                Route::get(
                    '/profile',
                    [SharedProfileController::class, 'index']
                )->name('api.admin-mobile.profile');

                Route::post(
                    '/profile/photo',
                    [SharedProfileController::class, 'updatePhoto']
                )->name('api.admin-mobile.profile.photo');

                Route::post(
                    '/change-password',
                    [SharedProfileController::class, 'updatePassword']
                )->name('api.admin-mobile.change-password');

                Route::post(
                    '/logout',
                    [
                        AdminMobileAuthController::class,
                        'logout',
                    ]
                )
                    ->middleware('throttle:10,1')
                    ->name('api.admin-mobile.logout');
            });
    });

