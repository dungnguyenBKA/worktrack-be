<?php

namespace App\Http\Controllers\API;

use App\Actions\AuthAction;
use App\Http\Controllers\API\BaseController;
use App\Http\Requests\LoginFaceIdRequest;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Models\Staff;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Tymon\JWTAuth\Contracts\Providers\JWT;
use Validator;
use Carbon\Carbon;
use Exception;
use Tymon\JWTAuth\Exceptions\JWTException;
// use Illuminate\Support\Facades\Mail;
// use App\Mail\SendUserRegister;
// use App\Services\SNSPushService;
use App\Models\User;
use App\Models\UserApp;
use App\Models\UserForgotPassword;
use Illuminate\Support\Facades\Hash;

class AuthController extends BaseController
{
    // protected $snsService;

    public function __construct()
    {
        parent::__construct();
        // $this->snsService = new SNSPushService();
    }

    /**
     * Register User Information
     *
     * @param string name
     * @param string birth_date
     * @param integer gender
     * @param string email
     * @param string icon
     * @param string password
     * @param string password_confirmation
     * @param string phone_number
     * @return \Illuminate\Http\Response
     */
    public function register(RegisterRequest $request, AuthAction $action)
    {
        return $action->register($request->transferredData());
    }

    /**
     * Register User Face Information
     *
     * @return \Illuminate\Http\Response
     */
    public function faceRegister(AuthAction $action)
    {
        return $action->faceRegister($this->user);
    }

    /**
     * Login User
     *
     * @param string email
     * @param string password
     * @return \Illuminate\Http\Response
     */
    public function login(LoginRequest $request, AuthAction $action)
    {
        $user = User::whereEmail($request->email)->first();
        if (!$user) {
            return $this->sendError('G00050002');
        }
        if (!Hash::check($request->password, $user->password)) {
            return $this->sendError('G00050001');
        }
        return $action->login($user);
    }

    // public function updateDeviceToken($deviceToken, $platform, $userId, $accessToken)
    // {
    //     $userApp = UserApp::where([
    //             ['user_id', $userId],
    //             ['access_token', $accessToken]
    //         ])
    //         ->first();

    //     // update to SNS
    //     $newEndpoint = $this->snsService->createEndpoint($deviceToken, $platform);
    //     $newSubscription = $this->snsService->subscribeDeviceTokenToTopic($newEndpoint, USER_TYPE, $platform);

    //     // update to DB
    //     $userDataUpdate = [
    //         'platform' => $platform,
    //         'device_token' => $deviceToken,
    //         'endpoint_arn' => $newEndpoint,
    //         'subscription_arn' => $newSubscription,
    //     ];
    //     $userApp->update($userDataUpdate);
    // }

    public function resetAccessToken(Request $request)
    {
        $refreshToken = $request->header('REFRESH_TOKEN');
        $deviceToken = $request->header('DEVICE_TOKEN');
        if (!$refreshToken || !$deviceToken || !isset(explode('.', $refreshToken)[1]) ||
            !is_numeric(base64_decode(explode('.', $refreshToken)[1]))) {
            return $this->sendError('E0003');
        }elseif (now()->timestamp - base64_decode(explode('.', $refreshToken)[1]) > config('jwt.refresh_ttl')*60) {
            return $this->sendError('E0101', ['refresh_token_expired' => true]);
        }

        $userApp = UserApp::where('refresh_token', $refreshToken)->first();
        if (!$userApp) {
            return $this->sendError('E0003');
        }

        DB::beginTransaction();
        try {
            $user = $userApp->user;
            // New access token
            $newToken = auth('api')->tokenById($user->id);
            $expiresIn = auth('api')->setToken($newToken)->payload()['exp'];
            // New refresh token
            $newRefreshToken = generate_refresh_token();
            // update login token
            $dataUpdate = [
                'access_token' => $newToken,
                'refresh_token' => $newRefreshToken,
            ];
            $oldLoginToken = $userApp ? $userApp->access_token : null;
            $userApp->update($dataUpdate);

            // delete old token
            if ($oldLoginToken) {
                try {
                    auth('api')->manager()->invalidate(new \Tymon\JWTAuth\Token($oldLoginToken), true);
                } catch (JWTException $e) {
                }
            }

            $response = [
                'token_type' => 'Bearer',
                'expires_in' => $expiresIn,
                'access_token' => $newToken,
                'refresh_token' => $newRefreshToken,
            ];

            DB::commit();
            return $this->sendSuccess($response);
        } catch (Exception $e) {
            DB::rollBack();
            Log::error("API reset-access-token: " . $e->getMessage());
            return $this->sendError('E0301');
        }
    }

    /**
     * Logout
     *
     * @param Request $request
     * @param AuthAction $action
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout(Request $request, AuthAction $action)
    {
        return $action->logout($this->userId, $request->device_token);
    }

    /**
     * Forgot Password
     *
     * @param string email
     * @return \Illuminate\Http\Response
     */
    public function passwordForgot(Request $request)
    {
        // Check validator for param
        $validator = Validator::make($request->all(), [
            'email' => 'required|max:255|email',
        ]);
        if ($validator->fails()) {
            return $this->sendError('E0003', $validator->errors());
        }

        $user = User::whereEmail($request->email)->first();
        if (!$user) {
            return $this->sendError('G00050001');
        }

        DB::beginTransaction();
        try {
            $token = bin2hex(openssl_random_pseudo_bytes(config('constants.password_lenght')));
            $data = [
                'user_id' => $user->id,
                'token' => $token,
                'expired' => Carbon::now()->addMinutes(config('constants.forgot_password_token_expired'))
            ];
            UserForgotPassword::create($data);

            $mailInfo = [
                'name' => $user->name,
                'token' => $token,
            ];
            Mail::to($user->email)->send(new ForgotPasswordMail($mailInfo));

            DB::commit();
            return $this->sendSuccess();
        } catch (Exception $e) {
            DB::rollBack();
            Log::error("API password-forgot: " . $e->getMessage());
            return $this->sendError('E0301');
        }
    }
}
