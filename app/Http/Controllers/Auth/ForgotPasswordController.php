<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\SendsPasswordResetEmails;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Password;
use Symfony\Component\HttpFoundation\Request;

class ForgotPasswordController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Password Reset Controller
    |--------------------------------------------------------------------------
    |
    | This controller is responsible for handling password reset emails and
    | includes a trait which assists in sending these notifications from
    | your application to your users. Feel free to explore this trait.
    |
    */

    use SendsPasswordResetEmails;

    public function sendResetLinkEmail(Request $request)
    {
        $request->validate(['email' => 'required|email']);
        try {
            $response = $this->broker()->sendResetLink(
                $this->credentials($request)
            );
            if ($response == Password::INVALID_USER){
                return $this->sendResetLinkFailedResponse($request, $response);
            }

            return $this->sendResetLinkResponse($request, $response);
        }catch (\Exception $e){
            Log::error('Forgot password error: '.$e->getMessage());
        }

        return back()->with('status', trans('passwords.sent'));
    }
}
