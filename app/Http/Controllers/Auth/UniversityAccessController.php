<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\University;
use Illuminate\Http\Request;

class UniversityAccessController extends Controller
{
    public function verify(Request $request)
    {
        $request->validate([
            'access_code' => 'required|string',
        ]);

        $code = trim($request->access_code);

        $university = University::where('access_code', $code)->first();

        if (!$university) {
            return back()->withErrors([
                'access_code' => 'Invalid access code.',
            ]);
        }

        if ($university->status !== 'ACTIVE') {
            return back()->withErrors([
                'access_code' => 'University is inactive.',
            ]);
        }

        session([
            'university_id' => $university->id,
            'university_name' => $university->name,
            'access_code' => $university->access_code,
        ]);

        return to_route('university-admin.login');
    }
}