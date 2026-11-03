<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\SuperAdmin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Inertia\Inertia;

class SuperAdminAuthController extends Controller
{
    /**
     * Display the Super Admin Login page.
     */
    public function create()
    {
        return Inertia::render('SuperAdmin/SuperAdminLogin');
    }

    /**
     * Display the Super Admin Create Account page.
     */
    public function createAccount()
    {
        return Inertia::render('SuperAdmin/SuperAdminCreateAcc');
    }

    /**
     * Authenticate Super Admin.
     */
    /**
 * Authenticate Super Admin.
 */
public function store(Request $request)
{
    $request->validate([
        'email' => ['required', 'email'],
        'password' => ['required'],
    ]);

    $superAdmin = SuperAdmin::where('email', $request->email)->first();

    // Email does not exist
    if (!$superAdmin) {

        if ($request->expectsJson()) {
            return response()->json([
                'errors' => [
                    'email' => [
                        'The email does not exist.'
                    ]
                ]
            ], 422);
        }

        return back()->withErrors([
            'email' => 'The email does not exist.',
        ]);
    }

    // Wrong password
    if (!Hash::check($request->password, $superAdmin->password)) {

        if ($request->expectsJson()) {
            return response()->json([
                'errors' => [
                    'password' => [
                        'Incorrect password.'
                    ]
                ]
            ], 422);
        }

        return back()->withErrors([
            'password' => 'Incorrect password.',
        ]);
    }

    Auth::guard('superadmin')->login(
        $superAdmin,
        $request->boolean('remember')
    );

    $request->session()->regenerate();

    if ($request->expectsJson()) {
        return response()->json([
            'success' => true,
            'redirect' => route('superadmin.dashboard'),
            'user' => [
                'id' => $superAdmin->id,
                'name' => $superAdmin->name,
                'email' => $superAdmin->email,
                'photo' => $superAdmin->photo,
            ]
        ]);
    }

    return redirect()->route('superadmin.dashboard');
}
    /**
     * Register a new Super Admin.
     */
    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:255', 'unique:super_admins,username'],
            'email' => ['required', 'email', 'max:255', 'unique:super_admins,email'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $superAdmin = SuperAdmin::create([
            'name' => $validated['name'],
            'username' => $validated['username'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        return redirect()
            ->route('superadmin.login')
            ->with('success', 'Super Admin account created successfully. Please log in.');
    }

    /**
 * Update Super Admin Password.
 */
public function updatePassword(Request $request)
{
    $request->validate([
        'current_password' => ['required'],
        'password' => ['required', 'min:8', 'confirmed'],
    ]);

    $superAdmin = Auth::guard('superadmin')->user();

    if (!$superAdmin) {
        return response()->json([
            'message' => 'Unauthorized.'
        ], 401);
    }

    // Check current password
    if (!Hash::check($request->current_password, $superAdmin->password)) {

        return response()->json([
            'message' => 'Current password is incorrect.'
        ], 422);

    }

    // Update password
    $superAdmin->password = Hash::make($request->password);
    $superAdmin->save();

    return response()->json([
        'message' => 'Password updated successfully.'
    ]);
}
    /**
     * Logout Super Admin.
     *
     * Return a safe snapshot of the account that was just signed out so the
     * frontend can always add it to the account chooser, even when localStorage
     * was empty or the login happened through Google OAuth.
     */
    public function destroy(Request $request)
    {
        /** @var SuperAdmin|null $superAdmin */
        $superAdmin = Auth::guard('superadmin')->user();

        $account = null;

        if ($superAdmin) {
            $account = [
                'id' => $superAdmin->id,
                'name' => $superAdmin->name,
                'username' => $superAdmin->username,
                'email' => $superAdmin->email,
                'photo' => $superAdmin->photo ?? null,
                'profile_photo' => $superAdmin->profile_photo ?? null,
                'account_type' => 'superadmin',
            ];
        }

        Auth::guard('superadmin')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'account' => $account,
                'redirect' => route('superadmin.logout'),
            ]);
        }

        return redirect()->route('superadmin.logout');
    }
}
