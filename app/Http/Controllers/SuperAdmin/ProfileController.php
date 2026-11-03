<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    /**
     * Return authenticated Super Admin.
     */
    public function show()
{
    $administrator = Auth::guard('superadmin')->user();

    return response()->json([
        'id' => $administrator->id,
        'name' => $administrator->name,
        'username' => $administrator->username,
        'email' => $administrator->email,
        'photo' => $administrator->photo,
    ]);
}

    /**
     * Upload profile photo.
     */
    public function updatePhoto(Request $request)
{
    $request->validate([
        'photo' => 'required|image|mimes:jpg,jpeg,png|max:2048',
    ]);

    $administrator = Auth::guard('superadmin')->user();

    if ($administrator->photo) {
        Storage::disk('public')->delete($administrator->photo);
    }

    $path = $request->file('photo')->store('superadmins', 'public');

    $administrator->update([
        'photo' => $path,
    ]);

    return response()->json([
        'photo' => $path,
    ]);
}
}