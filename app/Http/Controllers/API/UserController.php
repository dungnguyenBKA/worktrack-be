<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\API\BaseController;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Validator;
use Carbon\Carbon;
use DB;
use Exception;
use App\Models\User;

class UserController extends BaseController
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Get User Profile
     * 
     * @return \Illuminate\Http\Response
     */
    public function getUserProfile(Request $request)
    {
        return $this->sendSuccess($this->user);
    }

    /**
     * Update User Profile
     * 
     * @param string name
     * @param string birth_date
     * @param integer gender
     * @param string icon
     * @return \Illuminate\Http\Response
     */
    public function updateUserProfile(Request $request)
    {
        // Check validator for param
        $validator = Validator::make($request->all(), [
            'name' => 'required|max:255|string',
            'birth_date' => 'required|date',
            'gender' => 'required|boolean',
            'icon' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($validator->fails()) {
            return $this->sendError('E0003', $validator->errors());
        }

        DB::beginTransaction();
        try {
            $updateData = [
                'name' => $request->name,
                'birth_date' => $request->birth_date,
                'gender' => $request->gender,
                'updated_at' => Carbon::now(),
            ];

            $iconUrl = $this->uploadFile($request, 'icon', config('constants.upload_file_path.user_info_icon'), $this->user->icon);
            if ($iconUrl) {
                $updateData['icon'] = $iconUrl;
            }

            $this->user->update($updateData);

            DB::commit();
            return $this->sendSuccess();
        } catch (Exception $e) {
            DB::rollBack();
            Log::error("API updateUserProfile: " . $e->getMessage());
            return $this->sendError('E0301');
        }
    }

    /**
     * Update User Profile
     * 
     * @param string old_password
     * @param string password
     * @param string password_confirmation
     * @return \Illuminate\Http\Response
     */
    public function passwordChange(Request $request)
    {
        // Check validator for param
        $validator = Validator::make($request->all(), [
            'old_password' => 'required|string',
            'password' => [
                'required',
                'string',
                'confirmed',
                'min:8',
                'different:old_password',
                'regex:/^[A-Za-z\d!"#$%&\'()=~\-|^\\@\[;:\],.\/`{}+*>?_]{8,}$/'
            ],
        ]);
        if ($validator->fails()) {
            return $this->sendError('E0003', $validator->errors());
        }

        $oldPassword = $this->user->password;
        if (!Hash::check($request->old_password, $oldPassword)) {
            return $this->sendError('E0003');
        }

        DB::beginTransaction();
        try {
            $updateData = [
                'password' => Hash::make($request->password),
                'updated_at' => Carbon::now(),
            ];

            $this->user->update($updateData);

            DB::commit();
            return $this->sendSuccess();
        } catch (Exception $e) {
            DB::rollBack();
            Log::error("API passwordChange: " . $e->getMessage());
            return $this->sendError('E0301');
        }
    }
}
