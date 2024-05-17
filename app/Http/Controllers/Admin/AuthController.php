<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Cms\ChangePasswordRequest;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function logout()
    {
        auth()->logout();
        return redirect('login');
    }

    public function changePasswordEdit()
    {
        $page_title = __('layouts.change_password');
        return view('auth.passwords.change', compact('page_title'));
    }

    public function changePasswordUpdate(ChangePasswordRequest $request)
    {
        $user = $request->user();
        $changeData = $request->validated();

        if (!Hash::check($changeData['password_current'], $user->password)) {
            return redirect()->route('change-password.edit')
                ->with(['success' => false, 'message' => __('messages.password_incorrect')]);
        }

        if ($changeData['password_new'] != $changeData['password_confirmation']) {
            return redirect()->route('change-password.edit')
                ->with(['success' => false, 'message' => __('messages.confirm_password_not_match')]);
        }

        $user->update(['password' => bcrypt($changeData['password_new'])]);

        //auth()->setUser($user);

        return redirect()->route('dashboard')
             ->with(['success' => true, 'message' => __('messages.create.success')]);
        //return redirect()->route('logout');
    }
}
