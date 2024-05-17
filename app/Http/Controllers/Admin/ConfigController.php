<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Cms\UpdateConfigRequest;
use App\Models\ReportConfig;
use Carbon\Carbon;
use Illuminate\Contracts\Session\Session;

class ConfigController extends Controller
{
    public function index()
    {
        if (!$this->userCan('admin')) {
            abort('403', __('messages.permission_access_denied'));
        }
        $page_title  = __('layouts.config');
        $config = ReportConfig::first();
        $dayOfWeek = [
            1 => __('layouts.mon'),
            2 => __('layouts.tue'),
            3 => __('layouts.wed'),
            4 => __('layouts.thu'),
            5 => __('layouts.fri'),
            6 => __('layouts.sat'),
            0 => __('layouts.sun')
        ];
        return view('admin.config.index', compact('page_title', 'config', 'dayOfWeek'));
    }

    public function edit(ReportConfig $config)
    {
        if (!$this->userCan('admin')) {
            abort('403', __('messages.permission_access_denied'));
        }
        $page_title  = __('layouts.edit_config');
        $optionUsers = $config->getUsers(false);
        $selectedUsers = $config->getUsers();
        $dayOfWeek = [
            1 => __('layouts.mon'),
            2 => __('layouts.tue'),
            3 => __('layouts.wed'),
            4 => __('layouts.thu'),
            5 => __('layouts.fri'),
            6 => __('layouts.sat'),
            0 => __('layouts.sun')
        ];
        return view('admin.config.edit', compact('page_title', 'config', 'optionUsers', 'selectedUsers', 'dayOfWeek'));
    }

    public function update(UpdateConfigRequest $request, ReportConfig $config)
    {
        if (!$this->userCan('admin')) {
            abort('403', __('messages.permission_access_denied'));
        }
        $data = $request->transferredData();
        $oldData = $config->toArray();

        try {
            $config->update($data);
            $success = true;
            $message = __('messages.edit.success');
        } catch (\Exception $e) {
            $success = false;
            $message = __('messages.edit.error');
            logger()->error('Admin Update Config: ' . $e->getMessage());
        }
        if ($config->start != Carbon::make($oldData['start'])->format('H:i') ||
            $config->start_morning_late != Carbon::make($oldData['start_morning_late'])->format('H:i') ||
            $config->end_morning != Carbon::make($oldData['end_morning'])->format('H:i') ||
            $config->start_afternoon != Carbon::make($oldData['start_afternoon'])->format('H:i') ||
            $config->start_afternoon_late != Carbon::make($oldData['start_afternoon_late'])->format('H:i') ||
            $config->end != Carbon::make($oldData['end'])->format('H:i') ||
            $config->offset_time != Carbon::make($oldData['offset_time'])->format('H:i') ||
            json_encode($config->work_days) != $oldData['work_days'] ||
            $config->start_normal_OT != Carbon::make($oldData['start_normal_OT'])->format('H:i') ||
            $config->start_night_OT != Carbon::make($oldData['start_night_OT'])->format('H:i') ||
            $config->end_night_OT != Carbon::make($oldData['end_night_OT'])->format('H:i')){
            $message = __('messages.warning_change_work_days');
            return redirect()->route('config.index')
                ->with(['warning' => $success, 'message' => $message]);
        }
        return redirect()->route('config.index')
            ->with(['success' => $success, 'message' => $message]);
    }
}
