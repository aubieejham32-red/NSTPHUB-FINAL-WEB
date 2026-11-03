<?php

namespace App\Http\Middleware;

use App\Models\Coordinator;
use App\Models\Instructor;
use App\Models\University;
use App\Models\UniversityAdministrator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        return array_merge(
            parent::share($request),
            [
                'name' =>
                    config('app.name'),

                'sidebarOpen' =>
                    !$request->hasCookie('sidebar_state')
                    || $request->cookie('sidebar_state') === 'true',

                /*
                |--------------------------------------------------------------------------
                | Authentication / University Context
                |--------------------------------------------------------------------------
                |
                | UAHeader.vue is shared by:
                |
                | - University Administrator
                | - Instructor
                | - Coordinator
                |
                | Some pages do not explicitly send a `university` prop. The old
                | middleware only shared the Super Admin account and therefore the
                | header had no university logo to read on those pages.
                |
                | The authenticated university is now shared globally and safely.
                | Sensitive fields such as the university access code are NOT sent.
                |
                */

                'auth' => [
                    'user' =>
                        $request->user(),

                    'administrator' =>
                        fn () => $this->sharedSuperAdministrator(),

                    'university' =>
                        fn () => $this->sharedUniversity(),
                ],
            ]
        );
    }

    /**
     * Shared Super Administrator payload.
     *
     * @return array<string, mixed>|null
     */
    private function sharedSuperAdministrator(): ?array
    {
        $user =
            Auth::guard('superadmin')->user();

        if (!$user) {
            return null;
        }

        return [
            'id' =>
                $user->id,

            'name' =>
                $user->name,

            'username' =>
                $user->username,

            'email' =>
                $user->email,

            'photo' =>
                $user->photo,
        ];
    }

    /**
     * Resolve the university belonging to the currently authenticated portal
     * account and return only fields needed by shared UI such as UAHeader.vue.
     *
     * @return array<string, mixed>|null
     */
    private function sharedUniversity(): ?array
    {
        $account =
            Auth::guard('university_admin')->user()
            ?? Auth::guard('instructor')->user()
            ?? Auth::guard('coordinator')->user();

        if (
            !$account instanceof UniversityAdministrator
            && !$account instanceof Instructor
            && !$account instanceof Coordinator
        ) {
            return null;
        }

        $account->loadMissing('university');

        /** @var University|null $university */
        $university =
            $account->university;

        if (!$university) {
            return null;
        }

        return [
            'id' =>
                (int) $university->id,

            'name' =>
                $university->name,

            'acronym' =>
                $university->acronym,

            'type' =>
                $university->type,

            'campus_type' =>
                $university->campus_type,

            'logo' =>
                $university->logo,

            'logo_url' =>
                $university->logo_url,

            'academic_year' =>
                $university->academic_year,

            'semester' =>
                $university->semester,

            'status' =>
                $university->status,

            'updated_at' =>
                $university->updated_at?->toISOString(),
        ];
    }
}
