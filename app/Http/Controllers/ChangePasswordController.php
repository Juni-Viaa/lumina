<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class ChangePasswordController extends Controller
{
    /**
     * Show change password page
     */
    public function editPassword()
    {
        return view('change-password.index');
    }

    /**
     * Update user password
     */
    public function updatePassword(Request $request)
    {
        $request->validate([
            'old_password'     => ['required'],
            'new_password'     => ['required', 'min:8'],
            'confirm_password' => ['required', 'same:new_password'],
        ]);

        $user = auth()->user();

        /*
        |--------------------------------------------------------------------------
        | Check old password
        |--------------------------------------------------------------------------
        */

        if (!Hash::check($request->old_password, $user->password)) {

            return response()->json([
                'message' => 'Password lama salah.',
            ], 422);

        }

        /*
        |--------------------------------------------------------------------------
        | Update password
        |--------------------------------------------------------------------------
        */

        $user->password = Hash::make($request->new_password);

        $user->save();

        return response()->json([
            'message' => 'Password berhasil diubah.',
        ]);
    }
}