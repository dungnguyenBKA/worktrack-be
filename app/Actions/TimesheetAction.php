<?php

namespace App\Actions;

use App\Models\Staff;
use App\Models\Timesheet;
use App\Mail\LateInMonthMailManager;
use App\Mail\LateInMonthMailStaff;
use App\Models\ReportConfig;
use App\Models\TimesheetLog;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Models\FaceTimesheetLog;
use App\Models\PaidLeave;
use Illuminate\Support\Carbon;

class TimesheetAction
{
    public function record($staffId, $gps, $locationID)
    {
        DB::beginTransaction();
        try {
            $timesheet = Timesheet::today()->staff($staffId)->whereNotNull('check_in')->first();
            $currentTime = now();
            if ($timesheet) {
                $timesheet->update([
                    'check_out' => $currentTime,
                    'gps' => $gps,
                ]);
            } else {
                $timesheet = Timesheet::create([
                    'user_id' => $staffId,
                    'check_in' => $currentTime,
                    'check_out' => $currentTime,
                    'gps' => $gps,
                ]);
            }
            TimesheetLog::create([
                'user_id' => $staffId,
                'date_time' => $currentTime,
                'gps' => $gps,
                'location_id' => $locationID
            ]);
            DB::commit();
            PaidLeave::createOrUpdate($staffId, Carbon::parse($currentTime)->format('Y-m'));
            $timesheets = (new TimesheetLog)->getListTimeSheetLog([
                'start_date' => Carbon::now()->format('Y-m-d 00:00:00'),
                'end_date' => Carbon::now()->format('Y-m-d 23:59:59'),
            ]);
            return send_success($timesheets);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("API check in out: " . $e->getMessage());
            return send_error('E0301');
        }
    }

    public function sendLateReportMail($startTime = '', $endTime = '')
    {
        if (empty($startTime) || empty($endTime))
            return;
        $config = ReportConfig::first();
        $lateStaffs = Staff::getStaffsLateIn($startTime, $endTime, true, $config->over_times);

        $sendIDs = $config->selected_ids ? json_decode($config->selected_ids) : [];
        $sendStaffs = Staff::select('id', 'email')->whereIn('id', $sendIDs)->get();

        foreach ($lateStaffs as $staff) {
            $object = new \stdClass();
            $object->staff = $staff;
            $object->startTime = $startTime;
            $object->endTime = $endTime;
            try {
                Mail::to($staff->email)->send(new LateInMonthMailStaff($object));
            }catch (\Exception $e){
                Log::error("Send mail to ".$staff->email." error: " . $e->getMessage());
            }

        }

        $object = new \stdClass();
        $object->staffs = $lateStaffs;
        $object->month = \Carbon\Carbon::parse($startTime)->month;
        $object->startTime = $startTime;
        $object->endTime = $endTime;
        foreach ($sendStaffs as $staff) {
            try {
            Mail::to($staff->email)->send(new LateInMonthMailManager($object));
            }catch (\Exception $e){
                Log::error("Send mail to ".$staff->email." error: " . $e->getMessage());
            }
        }
    }

    public function syncTimeData($timesheets)
    {
        DB::beginTransaction();
        try {
            // save logs
            foreach ($timesheets as $t) {
                if (!is_null($t['staff_id']) && !is_null($t['check_time'])) {
                    $staff = Staff::where('staff_id', $t['staff_id'])->first();
                    if($staff) {
                        $data['user_id'] = $staff->id;
                        $data['check_time'] = date('Y-m-d H:i:s', strtotime($t['check_time']));
                        FaceTimesheetLog::firstOrCreate($data);
                    }
                }
            }

            // sort logs
            $timesheetLogs = FaceTimesheetLog::selectRaw('user_id, min(check_time) as check_in, max(check_time) as check_out')
                ->where(DB::raw('DATE_FORMAT(check_time, "%Y-%m")'), date('Y-m'))
                ->groupByRaw('user_id, DATE_FORMAT(check_time, "%Y-%m-%d")')
                ->get();

            // insert logs to timesheet
            foreach ($timesheetLogs as $t) {
                $userId = $t['user_id'];
                $checkIn = $t['check_in'];
                $checkOut = $t['check_out'];

                $timesheet = Timesheet::where('user_id', $userId)
                    ->where(DB::raw('DATE_FORMAT(check_in, "%Y-%m-%d")'), date('Y-m-d', strtotime($checkIn)))
                    ->first();

                if ($timesheet) {
                    if($checkIn > $timesheet->check_in) {
                        $checkIn =  $timesheet->check_in;
                    }
                    if($checkOut < $timesheet->check_out) {
                        $checkOut =  $timesheet->check_out;
                    }
                    $timesheet->update([
                        'check_in' => $checkIn,
                        'check_out' => $checkOut,
                        'last_update_staff_id' => '005',
                    ]);
                } else {
                    Timesheet::create([
                        'user_id' => $userId,
                        'check_in' => $checkIn,
                        'check_out' => $checkOut,
                        'last_update_staff_id' => '005',
                    ]);
                }

                PaidLeave::createOrUpdate($userId, Carbon::parse($checkIn)->format('Y-m'));
            }

            DB::commit();
            return send_success();
        } catch (\Exception $e) {
            DB::rollBack();
            logger()->error('API local sync: ' . $e->getMessage());
            return send_error('E0301', $e->getMessage());
        }
    }
}
