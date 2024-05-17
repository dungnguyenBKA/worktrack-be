<?php

namespace App\Http\Controllers\API;

use App\Http\Requests\UpdateOvertimeRequest;
use App\Models\Overtime;
use App\Models\OvertimeUser;
use App\Rules\ExistArrayUser;
use App\Rules\ExistOvertime;
use App\Rules\OvertimeRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class OvertimeController extends BaseController
{
    // public function __construct()
    // {
    //     //$this->authorizeResource(Overtime::class);
    // }
    public function index()
    {
        $overtimes = Overtime::myRequest($this->userId)
        ->orderBy('overtime.from_time', 'desc')
        ->orderBy('overtime.created_at', 'desc')
        ->get();
        //->paginate(10);
        return send_success($overtimes);
    }

    public function show($id)
    {
        $overtime = Overtime::myRequest($this->userId, $id)->first();
        if (!$overtime) {
            return send_error('E0401');
        }
        return send_success($overtime);
    }

    public function store(Request $request)
    {
        $validator = $this->validation($request);
        if ($validator->fails()) {
            return $this->sendError('E0003', $validator->errors());
        }
        $overtimeData = $validator->validate();
        $overtimeData['created_by'] = $this->userId;
        DB::beginTransaction();
        try {
            $overtime = Overtime::create($overtimeData);
            if (key_exists('members', $overtimeData)) {
                $overtimeUserData = [];
                foreach ($overtimeData['members'] as $m) {
                    $overtimeUserData[] = ['overtime_id' => $overtime->id, 'user_id' => $m];
                }
                OvertimeUser::insert($overtimeUserData);
            }
            DB::commit();
            return send_success($overtime);
        } catch (\Exception $e) {
            DB::rollBack();
            return send_error('E0005', 'Store request overtime api: ' . $e->getMessage());
        }
    }

    public function update(Request $request,  Overtime $overtime)
    {
        if (!($overtime->created_by == $this->userId && $overtime->status == config('common.overtime.waiting') ||
            $this->userCan('admin', $this->user))) {
            return send_error('E0403');
        }
        $validator = $this->validation($request, $overtime->id);
        if ($validator->fails()) {
            return $this->sendError('E0003', $validator->errors());
        }
        $data = $validator->validate();
        try {
            $overtime->update($data);
            if (key_exists('members', $data)) {
                $overtimeUserData = [];
                foreach ($data['members'] as $m) {
                    $overtimeUserData[] = ['overtime_id' => $overtime->id, 'user_id' => $m];
                }
                $overtime->overtimeUsers()->delete();
                OvertimeUser::insert($overtimeUserData);
            }
            return send_success($overtime);
        } catch (\Exception $e) {
            return send_error('E0005', 'Update request overtime api: ' . $e->getMessage());
        }
    }

    public function destroy(Overtime $overtime)
    {
        if (!($overtime->created_by == $this->userId && $overtime->status == config('common.overtime.waiting') ||
            $this->userCan('admin', $this->user))) {
            return send_error('E0403');
        }
        try {
            $overtime->overtimeUsers()->delete();
            $overtime->delete();
            return send_success();
        } catch (\Exception $e) {
            logger()->error('Delete overtime: ' . $e->getMessage());
            return send_error('E0301');
        }
    }

    public function validation($request, $overtimeId = false)
    {
        // Check validator for param
        $validator = Validator::make($request->all(), [
            'from_time' => ['bail', 'required', 'date',
                new ExistOvertime(\Request::instance()->members, \Request::instance()->to_time, $overtimeId)],
            //'to_time' => 'required|date',
            //'to_time' => 'required|date|after_or_equal:from_time',
            'to_time' => ['required','date', 'after_or_equal:from_time', new OvertimeRequest(\Request::instance()->from_time, \Request::instance()->to_time)],
            'project' => 'required|string',
            'reason' => 'required|string',
            //'members' => 'nullable|array',
            'members' => ['bail', 'required', 'array',
                new ExistArrayUser(\Request::instance()->members)],
        ]);
        return $validator;
    }
}
