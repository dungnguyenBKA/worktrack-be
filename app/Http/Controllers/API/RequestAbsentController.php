<?php

namespace App\Http\Controllers\API;

use App\Models\RequestAbsent;
use App\Rules\ExistRequestAbsent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class RequestAbsentController extends BaseController
{
    public function index()
    {
        $requestAbsents = RequestAbsent::myRequest($this->userId)
        ->orderBy('from_time', 'desc')
        ->orderBy('created_at', 'desc')
        ->get();
        //->paginate(10);
        return send_success($requestAbsents);
    }

    public function show($id)
    {
        $requestAbsent = RequestAbsent::myRequest($this->userId, $id)->first();
        if (!$requestAbsent) {
            return send_error('E0401');
        }
        return send_success($requestAbsent);
    }

    public function store(Request $request)
    {
        $validator = $this->validation($request);
        if ($validator->fails()) {
            return $this->sendError('E0003', $validator->errors());
        }
        try {
            $data = $validator->validate();
            $data['use_leave_hour'] = isset($data['use_leave_hour']) ? 1 : 0;
            $data['user_id'] = $this->userId;
            $data['created_by'] = $this->userId;
            RequestAbsent::create($data);
            return send_success($data);
        } catch (\Exception $e) {
            return send_error('E0005', 'Create request absent api: ' . $e->getMessage());
        }
    }

    public function update(Request $request, RequestAbsent $requestAbsent)
    {
        $user = $requestAbsent->user;
        if (!($user->id == $this->userId && $requestAbsent->status == config('common.request_absent.waiting') ||
            $this->userCan('admin', $this->user))) {
            return send_error('E0403');
        }
        $validator = $this->validation($request, $requestAbsent);
        if ($validator->fails()) {
            return $this->sendError('E0003', $validator->errors());
        }
        try {
            $data = $validator->validate();
            $data['use_leave_hour'] = isset($data['use_leave_hour']) ? 1 : 0;
            $requestAbsent->update($data);
            return send_success($requestAbsent);
        } catch (\Exception $e) {
            return send_error('E0005', 'Update request absent api: ' . $e->getMessage());
        }
    }

    public function destroy(RequestAbsent $requestAbsent)
    {
        $user = $requestAbsent->user;
        if (!($user->id == $this->userId && $requestAbsent->status == config('common.request_absent.waiting') ||
            $this->userCan('admin', $this->user))) {
            return send_error('E0403');
        }
        try {
            $requestAbsent->delete();
            return send_success();
        } catch (\Exception $e) {
            logger()->error('Delete request absent: ' . $e->getMessage());
            return send_error('E0301');
        }
    }

    public function validation($request, $requestAbsent = false)
    {
        // Check validator for param
        $validator = Validator::make($request->all(), [
            'from_time' => ['bail', 'required','date',
                new ExistRequestAbsent(\Request::instance()->to_time, $this->userId
                    , $requestAbsent)],
            'to_time' => 'required|date|after_or_equal:from_time',
            'reason' => 'required|string',
            'use_leave_hour' => 'nullable'
        ]);
        return $validator;
    }
}
