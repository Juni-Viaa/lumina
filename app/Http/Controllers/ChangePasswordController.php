<?php

namespace App\Http\Controllers;

use App\Models\ChangePassword;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ChangePasswordController extends Controller
{
    public function index(): View
    {
        return view('change-password.index');
    }

    public function editPassword(): View
    {
        return view('change-password.index');
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'new_password' => 'required|min:8|confirmed',
        ]);

        $user = auth()->user();

        if (!\Hash::check($request->current_password, $user->password)) {
            return back()->withErrors(['current_password' => 'Current password is incorrect']);
        }

        $user->password = \Hash::make($request->new_password);
        $user->save();

        return redirect()->route('change-password.index')->with('success', 'Password updated successfully');
    }
}
