<?php

namespace App\Actions;

use App\Http\Remotes\CrmRemote;
use App\Models\User;
use App\Models\UserApp;
use Illuminate\Support\Facades\DB;

class AuthAction
{
    public function register($userData)
    {
        DB::beginTransaction();
        try {
            // Create user
            User::create($userData);
            DB::commit();
            return send_success();
        } catch (\Exception $e) {
            DB::rollBack();
            return send_error('E0301', "API register: " . $e->getMessage());
        }
    }

    public function faceRegister($user)
    {
        DB::beginTransaction();
        try {
            $identity = "$user->email|$user->pw|";
            $updateData = ['face_id' => generate_face_id($identity)];
            $user->update($updateData);
            DB::commit();
            return send_success($updateData);
        } catch (\Exception $e) {
            DB::rollBack();
            return send_error('E0301', "API face register: " . $e->getMessage());
        }
    }
    public function login($user)
    {
        DB::beginTransaction();
        try {
            // Create token by ID
            if (!$token = $token = auth('api')->tokenById($user->id)) {
                return send_error('E0101');
            }
            $refreshToken = generate_refresh_token();

            $userAppData = [
                'user_id' => $user->id,
                'access_token' => $token,
                'refresh_token' => $refreshToken,
            ];
            UserApp::create($userAppData);

            $staffData = [
                'id' => $user->id,
                'staff_id' => $user->staff_id,
                'role' => $user->role,
                'status' => $user->status,
                'face_id' => $user->face_id,
                'first_name' => $user->first_name,
                'last_name' => $user->last_name,
                'full_name' => $user->getFullName(),
                'id_timesheet_machine' => $user->timesheet_machine_id
            ];

            // response data
            $response = [
                'token_type' => 'Bearer',
                'expires_in' => auth('api')->setToken($token)->payload()['exp'],
                'access_token' => $token,
                'refresh_token' => $refreshToken,
                'staff' => $staffData,
            ];

            DB::commit();
            return send_success($response);
        } catch (\Exception $e) {
            DB::rollBack();
            return send_error('E0301', "API login: " . $e->getMessage());
        }
    }

    public function logout($userId, $deviceToken)
    {

        DB::beginTransaction();
        try {
            // Check user
            $token = auth('api')->getToken();
            $userApp = UserApp::where([
                ['user_id', $userId],
                ['access_token', $token]
            ])->first();

            // Logout with access token
            auth('api')->logout(true);

            // Remove token in DB
            $userApp->delete();
            $crmRemote = new CrmRemote();
            $response = $crmRemote->updateDeviceToken($deviceToken);
            DB::commit();
            return send_success();
        } catch (\Exception $e) {
            DB::rollBack();
            return send_error('E0301', "API logout: " . $e->getMessage());
        }
    }
}
