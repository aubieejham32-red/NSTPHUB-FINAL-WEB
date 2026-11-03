<?php

namespace App\Http\Controllers\UniversityAdmin;

use App\Http\Controllers\Controller;
use App\Models\University;
use App\Models\UniversityAdministrator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;
use Throwable;

class ProfileController extends Controller
{
    /**
     * Display the logged-in University Administrator profile.
     */
    public function index(
        Request $request
    ): Response|RedirectResponse {
        /*
        |--------------------------------------------------------------------------
        | Get Authenticated University Administrator
        |--------------------------------------------------------------------------
        */

        $authenticatedAdmin = Auth::guard(
            'university_admin'
        )->user();

        if (!$authenticatedAdmin) {
            return redirect()
                ->route('university-admin.login')
                ->with(
                    'error',
                    'Please log in first before accessing your profile.'
                );
        }

        /*
        |--------------------------------------------------------------------------
        | Get Administrator Directly From Database
        |--------------------------------------------------------------------------
        */

        $admin = UniversityAdministrator::query()
            ->find(
                $authenticatedAdmin->getAuthIdentifier()
            );

        if (!$admin) {
            Auth::guard(
                'university_admin'
            )->logout();

            $request->session()->invalidate();

            $request->session()->regenerateToken();

            return redirect()
                ->route('university-admin.login')
                ->with(
                    'error',
                    'University Administrator account was not found.'
                );
        }

        /*
        |--------------------------------------------------------------------------
        | Get University Directly From University.php Model
        |--------------------------------------------------------------------------
        |
        | UniversityAdministrator.university_id
        |
        | connects to:
        |
        | universities.id
        |
        */

        $university = University::query()
            ->find($admin->university_id);

        if (!$university) {
            abort(
                404,
                'This University Administrator is not connected to a University.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Administrator Profile Photo URL
        |--------------------------------------------------------------------------
        */

        $photoUrl = null;

        if (!empty($admin->photo)) {
            $storedPhoto = trim((string) $admin->photo);

            if (
                str_starts_with($storedPhoto, 'http://') ||
                str_starts_with($storedPhoto, 'https://') ||
                str_starts_with($storedPhoto, '/')
            ) {
                $photoUrl = $storedPhoto;
            } else {
                $photoUrl = '/storage/'.ltrim($storedPhoto, '/');
            }
        }

        /*
        |--------------------------------------------------------------------------
        | University Logo URL
        |--------------------------------------------------------------------------
        */

        $logoUrl = $university->logo_url;

        /*
        |--------------------------------------------------------------------------
        | Return Profile Page
        |--------------------------------------------------------------------------
        */

        return Inertia::render(
            'UniversityAdmin/Profile/UniversityAdminProfile',
            [
                /*
                |--------------------------------------------------------------------------
                | Administrator
                |--------------------------------------------------------------------------
                */

                'admin' => [
                    'id' =>
                        $admin->id,

                    'university_id' =>
                        $admin->university_id,

                    'first_name' =>
                        $admin->first_name,

                    'middle_name' =>
                        $admin->middle_name,

                    'last_name' =>
                        $admin->last_name,

                    'email' =>
                        $admin->email,

                    'phone' =>
                        $admin->phone,

                    'username' =>
                        $admin->username,

                    'photo' =>
                        $admin->photo,

                    'photo_url' =>
                        $photoUrl,
                ],

                /*
                |--------------------------------------------------------------------------
                | University
                |--------------------------------------------------------------------------
                |
                | These values come directly from University.php / universities table.
                |
                */

                'university' => [
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

                    'email' =>
                        $university->email,

                    'contact_number' =>
                        $university->contact_number,

                    'website' =>
                        $university->website,

                    'logo' =>
                        $university->logo,

                    'logo_url' =>
                        $logoUrl,

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
                        $university->components,

                    'max_students' =>
                        $university->max_students,

                    /*
                    |--------------------------------------------------------------------------
                    | Protected Information
                    |--------------------------------------------------------------------------
                    */

                    'access_code' =>
                        $university->access_code,

                    'status' =>
                        $university->status,
                ],
            ]
        );
    }

    /**
     * Upload or replace administrator profile photo.
     */
    public function updatePhoto(
        Request $request
    ): RedirectResponse {
        /*
        |--------------------------------------------------------------------------
        | Authentication
        |--------------------------------------------------------------------------
        */

        $authenticatedAdmin = Auth::guard(
            'university_admin'
        )->user();

        if (!$authenticatedAdmin) {
            return redirect()
                ->route('university-admin.login')
                ->with(
                    'error',
                    'Please log in first.'
                );
        }

        /*
        |--------------------------------------------------------------------------
        | Validate Profile Photo
        |--------------------------------------------------------------------------
        */

        $request->validate(
            [
                'photo' => [
                    'required',
                    'file',
                    'image',
                    'mimes:jpg,jpeg,png,webp',
                    'max:2048',
                ],
            ],
            [
                'photo.required' =>
                    'Please select a profile photo.',

                'photo.file' =>
                    'The selected profile photo is invalid.',

                'photo.image' =>
                    'The selected file must be an image.',

                'photo.mimes' =>
                    'Only JPG, JPEG, PNG and WEBP images are allowed.',

                'photo.max' =>
                    'The profile photo must not be larger than 2MB.',
            ]
        );

        /*
        |--------------------------------------------------------------------------
        | Get Administrator
        |--------------------------------------------------------------------------
        */

        $admin = UniversityAdministrator::query()
            ->find(
                $authenticatedAdmin->getAuthIdentifier()
            );

        if (!$admin) {
            return redirect()
                ->route('university-admin.login')
                ->with(
                    'error',
                    'University Administrator account was not found.'
                );
        }

        /*
        |--------------------------------------------------------------------------
        | Get Uploaded Photo
        |--------------------------------------------------------------------------
        */

        $uploadedPhoto = $request->file('photo');

        if (
            !$uploadedPhoto ||
            !$uploadedPhoto->isValid()
        ) {
            return back()
                ->withErrors([
                    'photo' =>
                        'The uploaded profile photo is invalid.',
                ]);
        }

        /*
        |--------------------------------------------------------------------------
        | Remember Old Photo
        |--------------------------------------------------------------------------
        */

        $oldPhotoPath = $admin->photo;

        /*
        |--------------------------------------------------------------------------
        | Store New Photo
        |--------------------------------------------------------------------------
        */

        $newPhotoPath = $uploadedPhoto->store(
            'university-admin/profile-photos',
            'public'
        );

        if (!$newPhotoPath) {
            return back()
                ->withErrors([
                    'photo' =>
                        'Unable to save the profile photo.',
                ]);
        }

        /*
        |--------------------------------------------------------------------------
        | Save Database Record
        |--------------------------------------------------------------------------
        */

        try {
            $admin->photo =
                $newPhotoPath;

            $admin->save();

        } catch (Throwable $exception) {
            /*
            |--------------------------------------------------------------------------
            | Delete New File If Database Save Failed
            |--------------------------------------------------------------------------
            */

            if (
                Storage::disk('public')
                    ->exists($newPhotoPath)
            ) {
                Storage::disk('public')
                    ->delete($newPhotoPath);
            }

            report($exception);

            return back()
                ->withErrors([
                    'photo' =>
                        'Unable to update the profile photo.',
                ]);
        }

        /*
        |--------------------------------------------------------------------------
        | Delete Previous Photo
        |--------------------------------------------------------------------------
        */

        if (
            !empty($oldPhotoPath) &&
            $oldPhotoPath !== $newPhotoPath &&
            str_starts_with(
                $oldPhotoPath,
                'university-admin/profile-photos/'
            )
        ) {
            if (
                Storage::disk('public')
                    ->exists($oldPhotoPath)
            ) {
                Storage::disk('public')
                    ->delete($oldPhotoPath);
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Return To Profile
        |--------------------------------------------------------------------------
        */

        return redirect()
            ->route('university-admin.profile')
            ->with(
                'success',
                'Profile photo updated successfully.'
            );
    }

    /**
     * Update University information.
     *
     * Connected directly to App\Models\University.
     */
    public function updateUniversityInformation(
        Request $request
    ): RedirectResponse {
        /*
        |--------------------------------------------------------------------------
        | Authentication
        |--------------------------------------------------------------------------
        */

        $authenticatedAdmin = Auth::guard(
            'university_admin'
        )->user();

        if (!$authenticatedAdmin) {
            return redirect()
                ->route('university-admin.login')
                ->with(
                    'error',
                    'Please log in first.'
                );
        }

        /*
        |--------------------------------------------------------------------------
        | Get Administrator
        |--------------------------------------------------------------------------
        */

        $admin = UniversityAdministrator::query()
            ->find(
                $authenticatedAdmin->getAuthIdentifier()
            );

        if (!$admin) {
            return redirect()
                ->route('university-admin.login')
                ->with(
                    'error',
                    'University Administrator account was not found.'
                );
        }

        /*
        |--------------------------------------------------------------------------
        | Get University Using University.php
        |--------------------------------------------------------------------------
        |
        | IMPORTANT:
        |
        | We only retrieve the university using the authenticated
        | administrator's university_id.
        |
        | The administrator therefore cannot edit another university
        | by changing an ID in the browser.
        |
        */

        $university = University::query()
            ->where(
                'id',
                $admin->university_id
            )
            ->first();

        if (!$university) {
            abort(
                404,
                'University record was not found.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Validate University Information
        |--------------------------------------------------------------------------
        */

        $validated = $request->validate(
            [
                /*
                |--------------------------------------------------------------------------
                | Institution Information
                |--------------------------------------------------------------------------
                */

                'name' => [
                    'required',
                    'string',
                    'max:255',
                ],

                'acronym' => [
                    'required',
                    'string',
                    'max:50',
                ],

                'type' => [
                    'required',
                    'string',
                    'max:100',
                ],

                'campus_type' => [
                    'required',
                    'string',
                    'max:100',
                ],

                'email' => [
                    'required',
                    'email',
                    'max:255',
                ],

                'contact_number' => [
                    'required',
                    'string',
                    'max:50',
                ],

                'website' => [
                    'nullable',
                    'string',
                    'max:255',
                ],

                /*
                |--------------------------------------------------------------------------
                | Address Information
                |--------------------------------------------------------------------------
                */

                'region' => [
                    'required',
                    'string',
                    'max:150',
                ],

                'province' => [
                    'required',
                    'string',
                    'max:150',
                ],

                'city' => [
                    'required',
                    'string',
                    'max:150',
                ],

                'barangay' => [
                    'required',
                    'string',
                    'max:150',
                ],

                'zip_code' => [
                    'required',
                    'string',
                    'max:20',
                ],

                'complete_address' => [
                    'required',
                    'string',
                    'max:1000',
                ],

                /*
                |--------------------------------------------------------------------------
                | NSTP Configuration
                |--------------------------------------------------------------------------
                */

                'academic_year' => [
                    'required',
                    'string',
                    'max:50',
                ],

                'semester' => [
                    'required',
                    'string',
                    'max:100',
                ],

                'components' => [
                    'nullable',
                    'string',
                    'max:255',
                ],

                'max_students' => [
                    'required',
                    'integer',
                    'min:1',
                ],

                /*
                |--------------------------------------------------------------------------
                | University Logo
                |--------------------------------------------------------------------------
                */

                'logo' => [
                    'nullable',
                    'file',
                    'image',
                    'mimes:jpg,jpeg,png,webp',
                    'max:2048',
                ],
            ],
            [
                /*
                |--------------------------------------------------------------------------
                | Institution
                |--------------------------------------------------------------------------
                */

                'name.required' =>
                    'University name is required.',

                'acronym.required' =>
                    'University acronym is required.',

                'type.required' =>
                    'University type is required.',

                'campus_type.required' =>
                    'Campus type is required.',

                'email.required' =>
                    'University email is required.',

                'email.email' =>
                    'Please enter a valid university email address.',

                'contact_number.required' =>
                    'Contact number is required.',

                /*
                |--------------------------------------------------------------------------
                | Address
                |--------------------------------------------------------------------------
                */

                'region.required' =>
                    'Region is required.',

                'province.required' =>
                    'Province is required.',

                'city.required' =>
                    'City is required.',

                'barangay.required' =>
                    'Barangay is required.',

                'zip_code.required' =>
                    'Zip code is required.',

                'complete_address.required' =>
                    'Complete address is required.',

                /*
                |--------------------------------------------------------------------------
                | NSTP
                |--------------------------------------------------------------------------
                */

                'academic_year.required' =>
                    'Academic year is required.',

                'semester.required' =>
                    'Semester is required.',

                'max_students.required' =>
                    'Maximum students is required.',

                'max_students.integer' =>
                    'Maximum students must be a whole number.',

                'max_students.min' =>
                    'Maximum students must be at least 1.',

                /*
                |--------------------------------------------------------------------------
                | Logo
                |--------------------------------------------------------------------------
                */

                'logo.file' =>
                    'The selected university logo is invalid.',

                'logo.image' =>
                    'The university logo must be an image.',

                'logo.mimes' =>
                    'Only JPG, JPEG, PNG and WEBP images are allowed.',

                'logo.max' =>
                    'The university logo must not be larger than 2MB.',
            ]
        );

        /*
        |--------------------------------------------------------------------------
        | Convert NSTP Components To Array
        |--------------------------------------------------------------------------
        |
        | Vue sends:
        |
        | CWTS, ROTC, LTS
        |
        | University.php stores:
        |
        | [
        |     "CWTS",
        |     "ROTC",
        |     "LTS"
        | ]
        |
        */

        $components = [];

        if (
            !empty(
                $validated['components'] ?? null
            )
        ) {
            $components = collect(
                explode(
                    ',',
                    $validated['components']
                )
            )
                ->map(
                    fn ($component) =>
                        trim($component)
                )
                ->filter()
                ->unique()
                ->values()
                ->all();
        }

        /*
        |--------------------------------------------------------------------------
        | Logo Variables
        |--------------------------------------------------------------------------
        */

        $oldLogoPath =
            $university->logo;

        $newLogoPath = null;

        /*
        |--------------------------------------------------------------------------
        | Store New University Logo
        |--------------------------------------------------------------------------
        */

        if (
            $request->hasFile('logo')
        ) {
            $uploadedLogo =
                $request->file('logo');

            if (
                !$uploadedLogo ||
                !$uploadedLogo->isValid()
            ) {
                return back()
                    ->withErrors([
                        'logo' =>
                            'The uploaded university logo is invalid.',
                    ]);
            }

            $newLogoPath =
                $uploadedLogo->store(
                    'universities/logos',
                    'public'
                );

            if (!$newLogoPath) {
                return back()
                    ->withErrors([
                        'logo' =>
                            'Unable to upload the university logo.',
                    ]);
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Update Database
        |--------------------------------------------------------------------------
        */

        try {
            DB::transaction(
                function () use (
                    $university,
                    $validated,
                    $components,
                    $newLogoPath
                ) {
                    /*
                    |--------------------------------------------------------------------------
                    | Institution Information
                    |--------------------------------------------------------------------------
                    */

                    $university->name =
                        trim(
                            $validated['name']
                        );

                    $university->acronym =
                        trim(
                            $validated['acronym']
                        );

                    $university->type =
                        trim(
                            $validated['type']
                        );

                    $university->campus_type =
                        trim(
                            $validated['campus_type']
                        );

                    $university->email =
                        trim(
                            $validated['email']
                        );

                    $university->contact_number =
                        trim(
                            $validated['contact_number']
                        );

                    $university->website =
                        !empty(
                            $validated['website'] ?? null
                        )
                            ? trim(
                                $validated['website']
                            )
                            : null;

                    /*
                    |--------------------------------------------------------------------------
                    | Address Information
                    |--------------------------------------------------------------------------
                    */

                    $university->region =
                        trim(
                            $validated['region']
                        );

                    $university->province =
                        trim(
                            $validated['province']
                        );

                    $university->city =
                        trim(
                            $validated['city']
                        );

                    $university->barangay =
                        trim(
                            $validated['barangay']
                        );

                    $university->zip_code =
                        trim(
                            $validated['zip_code']
                        );

                    $university->complete_address =
                        trim(
                            $validated[
                                'complete_address'
                            ]
                        );

                    /*
                    |--------------------------------------------------------------------------
                    | NSTP Configuration
                    |--------------------------------------------------------------------------
                    */

                    $university->academic_year =
                        trim(
                            $validated[
                                'academic_year'
                            ]
                        );

                    $university->semester =
                        trim(
                            $validated['semester']
                        );

                    $university->components =
                        $components;

                    $university->max_students =
                        (int) $validated[
                            'max_students'
                        ];

                    /*
                    |--------------------------------------------------------------------------
                    | University Logo
                    |--------------------------------------------------------------------------
                    */

                    if ($newLogoPath) {
                        $university->logo =
                            $newLogoPath;
                    }

                    /*
                    |--------------------------------------------------------------------------
                    | Protected Fields
                    |--------------------------------------------------------------------------
                    |
                    | We deliberately DO NOT update:
                    |
                    | access_code
                    | status
                    |
                    */

                    /*
                    |--------------------------------------------------------------------------
                    | Save Through University.php Model
                    |--------------------------------------------------------------------------
                    */

                    $university->save();
                }
            );

        } catch (Throwable $exception) {
            /*
            |--------------------------------------------------------------------------
            | Delete Uploaded Logo If Database Update Failed
            |--------------------------------------------------------------------------
            */

            if (
                $newLogoPath &&
                Storage::disk('public')
                    ->exists($newLogoPath)
            ) {
                Storage::disk('public')
                    ->delete($newLogoPath);
            }

            report($exception);

            return back()
                ->with(
                    'error',
                    'Unable to update the university information. Please try again.'
                );
        }

        /*
        |--------------------------------------------------------------------------
        | Delete Old Logo After Successful Database Update
        |--------------------------------------------------------------------------
        */

        if (
            $newLogoPath &&
            !empty($oldLogoPath) &&
            $oldLogoPath !== $newLogoPath &&
            str_starts_with(
                $oldLogoPath,
                'universities/logos/'
            )
        ) {
            if (
                Storage::disk('public')
                    ->exists($oldLogoPath)
            ) {
                Storage::disk('public')
                    ->delete($oldLogoPath);
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Redirect Back
        |--------------------------------------------------------------------------
        */

        return redirect()
            ->route(
                'university-admin.profile'
            )
            ->with(
                'success',
                'University information updated successfully.'
            );
    }
}