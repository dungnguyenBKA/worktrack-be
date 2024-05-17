<?php

namespace App\Http\Controllers\API;

use App\Actions\TimesheetAction;
use App\Http\Requests\CheckInOutRequest;
use App\Http\Requests\BaseRequest;
use App\Http\Requests\GetTimesheetRequest;
use App\Http\Requests\FaceChecktimeRequest;
use App\Models\PaidLeave;
use App\Models\Position;
use App\Models\Timesheet;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class TimesheetController extends BaseController
{
    public function checkInOut(CheckInOutRequest $request, TimesheetAction $action)
    {
        if (!$this->user) {
            return $this->sendError('E0103');
        }
        if ($request->face_id != $this->user->face_id) {
            return $this->sendError('E0005');
        }
        $workLocationUse = Position::getAllowPositions($this->userId, true);
        $gps = $request->gps ? explode(';', $request->gps) : false;
        $latituteArr = $gps ? explode(':', $gps[0]) : false;
        $latitute = $latituteArr[1] ? trim($latituteArr[1]) : null;
        $longituteArr = isset($gps[1]) ? explode(':', $gps[1]) : [];
        $longitute = $longituteArr[1] ? trim($longituteArr[1]) : null;

        $locationID = Timesheet::checkLocation($latitute, $longitute, $workLocationUse);
        if ($locationID == 0) {
            return $this->sendError('E0006');
        }
        return $action->record($this->userId, $request->gps, $locationID);
    }

    public function getTimesheet(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'from_time' => 'required|date',
            'to_time' => 'required|date',
        ]);
//        if ($validator->fails() ||
////            Carbon::make($request->from_time)->format('Y/m') != Carbon::make($request->to_time)->format('Y/m') ||
//            Carbon::make($request->from_time) != Carbon::make($request->from_time)->firstOfMonth() ||
//            Carbon::make($request->to_time) != Carbon::make($request->to_time)->lastOfMonth()
//        ) {
//
//            dd(
//                [
//                    Carbon::make($request->from_time) != Carbon::make($request->from_time)->firstOfMonth(),
//                    Carbon::make($request->from_time)->format('Y/m') != Carbon::make($request->to_time)->format('Y/m'),
//                    Carbon::make($request->to_time) != Carbon::make($request->to_time)->lastOfMonth(),
//                    Carbon::make($request->from_time)->format('Y/m/d'),
//                    Carbon::make($request->to_time)->format('Y/m/d'),
//                ]
//            );
//
//
//            return $this->sendError('E0001');
//        }

        $start_date = Carbon::parse($request->from_time)->format('Y-m-d');
        $end_date = Carbon::parse($request->to_time)->format('Y-m-d');

        $end_date = date_create($end_date);
        date_add($end_date, date_interval_create_from_date_string("1 days"));

        $timesheets = (new Timesheet)->getTimesheetCalendar($start_date, $end_date, $this->userId);
        $paidLeaveModel = new PaidLeave();
        $paidLeave = $paidLeaveModel->getOnePaidLeaveWithCondition([
            'month_year' => Carbon::make($start_date)->format('Y-m'),
            'user_id' => $this->userId
        ]);
        $total = $timesheets['total'];
        $total['day_left'] = $paidLeave ? (float)$paidLeave->day_left : 0;
        $total['day_use_in_month'] = $paidLeave ? $paidLeave->day_use_in_month + $paidLeave->leave_hour_in_work_hour - $total['timeInFuture'] : 0;
        $total['day_add_in_month'] = $paidLeave ? (float)$paidLeave->day_add_in_month : 0;
        $total['salary_deduction_hour'] = $paidLeave && $paidLeave->salaryDeductionDay($total['timeInFuture']) ? (float)$paidLeave->salaryDeductionDay($total['timeInFuture']) : 0;
        $total['day_edit'] = $paidLeave && $paidLeave->day_edit ? (float)$paidLeave->day_edit : 0;
        $total['leave_day_left'] = $paidLeave ? $paidLeave->leaveDaysLeft($total['timeInFuture']) : 0;
        $timesheets['total'] = $total;

        return $this->sendSuccess($timesheets);
    }

    public function syncTimeData(FaceChecktimeRequest $request, TimesheetAction $action)
    {
        $timesheets = $request->input('timesheets');
        return $action->syncTimeData($timesheets);
    }
}
