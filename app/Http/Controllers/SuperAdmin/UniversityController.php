<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\University;
use App\Models\UniversityAdministrator;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class UniversityController extends Controller
{
    /**
     * Display all universities.
     */
    /**
 * Display all universities.
 */
public function index(Request $request)
{
    $query = University::with('administrator');

    if ($request->filled('search')) {

        $search = trim($request->search);

        $query->where(function ($q) use ($search) {

            $q->where('name', 'LIKE', "%{$search}%")
              ->orWhere('acronym', 'LIKE', "%{$search}%")
              ->orWhere('region', 'LIKE', "%{$search}%")
              ->orWhere('province', 'LIKE', "%{$search}%")
              ->orWhere('access_code', 'LIKE', "%{$search}%")
              ->orWhereHas('administrator', function ($admin) use ($search) {

                    $admin->where('first_name', 'LIKE', "%{$search}%")
                          ->orWhere('last_name', 'LIKE', "%{$search}%")
                          ->orWhereRaw(
                              "CONCAT(first_name,' ',last_name) LIKE ?",
                              ["%{$search}%"]
                          );

              });

        });

    }

    $universities = $query
        ->latest()
        ->paginate(10);

    return response()->json($universities);
}

    /**
     * Store a newly created university.
     */
    public function store(Request $request)
    {
        $request->validate([

            // University
            'name'              => 'required|string|max:255',
            'acronym'           => 'required|string|max:30|unique:universities,acronym',
            'type'              => 'required',
            'campus_type'       => 'required',
            'email'             => 'required|email|unique:universities,email',
            'contact_number'    => 'required',
            'website'           => 'nullable|string',
            'logo'              => 'nullable|image|max:2048',

            'region'            => 'required',
            'province'          => 'required',
            'city'              => 'required',
            'barangay'          => 'required',
            'zip_code'          => 'required',
            'complete_address'  => 'required',

            'academic_year'     => 'required',
            'semester'          => 'required',
            'components'        => 'required|array',
            'max_students'      => 'required|integer',

            // Administrator
            'first_name'        => 'required',
            'middle_name'       => 'nullable',
            'last_name'         => 'required',

            'admin_email'       => 'required|email|unique:users,email',
            'phone'             => 'required',

            'username'          => 'required|unique:users,username',
            'password'          => 'required|min:8',
        ]);

        DB::beginTransaction();

        try {

            $logo = null;

            if ($request->hasFile('logo')) {
                $logo = $request->file('logo')->store('universities', 'public');
            }

            $accessCode = $this->generateAccessCode();

            $university = University::create([

                'name'              => $request->name,
                'acronym'           => strtoupper($request->acronym),
                'type'              => $request->type,
                'campus_type'       => $request->campus_type,
                'email'             => $request->email,
                'contact_number'    => $request->contact_number,
                'website'           => $request->website,
                'logo'              => $logo,

                'region'            => $request->region,
                'province'          => $request->province,
                'city'              => $request->city,
                'barangay'          => $request->barangay,
                'zip_code'          => $request->zip_code,
                'complete_address'  => $request->complete_address,

                'academic_year'     => $request->academic_year,
                'semester'          => $request->semester,
                'components'        => $request->components,
                'max_students'      => $request->max_students,

                'access_code'       => $accessCode,
                'status'            => 'ACTIVE',
            ]);

            $user = User::create([
                'name'      => $request->first_name . ' ' . $request->last_name,
                'email'     => $request->admin_email,
                'username'  => $request->username,
                'password'  => Hash::make($request->password),
            ]);

            $user->assignRole('University Admin');

            UniversityAdministrator::create([

                'university_id' => $university->id,

                'first_name' => $request->first_name,
                'middle_name' => $request->middle_name,
                'last_name' => $request->last_name,

                'email' => $request->admin_email,
                'phone' => $request->phone,

                'username' => $request->username,
                'password' => Hash::make($request->password),
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'University created successfully.',
                'accessCode' => $accessCode,
                'portalUrl' => url('/superadmin/login'),
                'data' => $university->load('administrator')
            ], 201);

        } catch (\Exception $e) {

            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display one university.
     */
    public function show(University $university)
    {
        return response()->json(
            $university->load('administrator')
        );
    }

    /**
     * Update a university.
     */
    public function update(Request $request, University $university)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'acronym' => 'required|string|max:30|unique:universities,acronym,'.$university->id,
            'type' => 'required|in:Public,Private',
            'campus_type' => 'required|in:Main Campus,Extension',

            'email' => 'required|email|unique:universities,email,'.$university->id,
            'contact_number' => 'required|string|max:30',
            'website' => 'nullable|string|max:255',

            'logo' => 'nullable|file|image|mimes:jpg,jpeg,png,webp|max:2048',

            'region' => 'required|string|max:255',
            'province' => 'required|string|max:255',
            'city' => 'required|string|max:255',
            'barangay' => 'required|string|max:255',
            'zip_code' => 'required|string|max:10',
            'complete_address' => 'required|string',

            'academic_year' => 'required|string|max:50',
            'semester' => 'required|string|max:50',
            'components' => 'required|array|min:1',
            'components.*' => 'string|in:CWTS,LTS,ROTC',
            'max_students' => 'required|integer|min:1',
            'status' => 'required|in:ACTIVE,INACTIVE',

            'administrator.first_name' => 'required|string|max:255',
            'administrator.middle_name' => 'nullable|string|max:255',
            'administrator.last_name' => 'required|string|max:255',
            'administrator.email' => 'required|email|max:255',
            'administrator.phone' => 'required|string|max:30',
            'administrator.username' => 'required|string|max:100',
        ]);

        $oldLogoPath = $university->logo;
        $newLogoPath = null;

        try {
            /*
            |------------------------------------------------------------------
            | Store the replacement logo first
            |------------------------------------------------------------------
            |
            | The old logo is kept until the database update succeeds. This
            | prevents a failed update from leaving the university without a
            | logo.
            |
            */
            if ($request->hasFile('logo')) {
                $uploadedLogo = $request->file('logo');

                if (! $uploadedLogo || ! $uploadedLogo->isValid()) {
                    throw new \RuntimeException('The uploaded university logo is invalid.');
                }

                $newLogoPath = $uploadedLogo->store(
                    'universities/logos',
                    'public'
                );

                if (
                    ! $newLogoPath ||
                    ! Storage::disk('public')->exists($newLogoPath)
                ) {
                    throw new \RuntimeException('Unable to save the university logo.');
                }
            }

            DB::beginTransaction();

            $university->update([
                'name' => trim($validated['name']),
                'acronym' => strtoupper(trim($validated['acronym'])),
                'type' => $validated['type'],
                'campus_type' => $validated['campus_type'],
                'email' => strtolower(trim($validated['email'])),
                'contact_number' => trim($validated['contact_number']),
                'website' => ! empty($validated['website'])
                    ? trim($validated['website'])
                    : null,
                'logo' => $newLogoPath ?: $oldLogoPath,
                'region' => trim($validated['region']),
                'province' => trim($validated['province']),
                'city' => trim($validated['city']),
                'barangay' => trim($validated['barangay']),
                'zip_code' => trim($validated['zip_code']),
                'complete_address' => trim($validated['complete_address']),
                'academic_year' => trim($validated['academic_year']),
                'semester' => trim($validated['semester']),
                'components' => array_values($validated['components']),
                'max_students' => (int) $validated['max_students'],
                'status' => $validated['status'],
            ]);

            $administrator = $university->administrator;

            if ($administrator) {
                $oldAdminUsername = $administrator->username;
                $adminData = $validated['administrator'];

                $administrator->update([
                    'first_name' => trim($adminData['first_name']),
                    'middle_name' => ! empty($adminData['middle_name'])
                        ? trim($adminData['middle_name'])
                        : null,
                    'last_name' => trim($adminData['last_name']),
                    'email' => strtolower(trim($adminData['email'])),
                    'phone' => trim($adminData['phone']),
                    'username' => trim($adminData['username']),
                ]);

                // Older installations may also have a mirrored users record.
                User::where('username', $oldAdminUsername)->update([
                    'name' => trim($adminData['first_name']).' '.trim($adminData['last_name']),
                    'email' => strtolower(trim($adminData['email'])),
                    'username' => trim($adminData['username']),
                ]);
            }

            DB::commit();

            if (
                $newLogoPath &&
                $oldLogoPath &&
                $oldLogoPath !== $newLogoPath &&
                Storage::disk('public')->exists($oldLogoPath)
            ) {
                Storage::disk('public')->delete($oldLogoPath);
            }

            return response()->json([
                'success' => true,
                'message' => 'University updated successfully.',
                'data' => $university->fresh()->load('administrator'),
            ]);
        } catch (\Throwable $e) {
            if (DB::transactionLevel() > 0) {
                DB::rollBack();
            }

            if (
                $newLogoPath &&
                Storage::disk('public')->exists($newLogoPath)
            ) {
                Storage::disk('public')->delete($newLogoPath);
            }

            report($e);

            return response()->json([
                'success' => false,
                'message' => app()->environment('local')
                    ? $e->getMessage()
                    : 'Unable to update university.',
            ], 500);
        }
    }

    /**
     * Delete a university.
     */
    public function destroy(University $university)
    {
        DB::beginTransaction();

        try {

            if ($university->logo && Storage::disk('public')->exists($university->logo)) {
                Storage::disk('public')->delete($university->logo);
            }

            $administrator = $university->administrator;

            if ($administrator) {

                User::where('username', $administrator->username)->delete();

                $administrator->delete();
            }

            $university->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'University deleted successfully.',
            ]);

        } catch (\Exception $e) {

            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function toggleStatus(University $university)
{
    $university->status =
        $university->status === 'ACTIVE'
            ? 'INACTIVE'
            : 'ACTIVE';

    $university->save();

    return response()->json([
        'status' => $university->status
    ]);
}

    public function universityAdministrators()
{
    $administrators = UniversityAdministrator::with('university')
        ->orderBy('first_name')
        ->get();

    return response()->json([
        'success' => true,
        'data' => $administrators,
    ]);
}
}