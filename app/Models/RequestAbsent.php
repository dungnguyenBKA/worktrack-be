<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class RequestAbsent extends Model
{
    protected $table = 'request_absent';

    protected $fillable = [
        'user_id',
        'from_time',
        'to_time',
        'reason',
        'created_by',
        'status',
        'reason2',
        'use_leave_hour',
    ];

    // protected $hidden = [
    //     'user_id',
    //     'created_by',
    // ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function scopeMyRequest($query, $userId, $id = '')
    {
        if (!empty($id))
            $query->whereId($id);
        return $query->where('user_id', $userId);
        //return $query->where('created_by', $userId);
        return $query;
    }

    public static function getRequestAbsentByMonth($startDate = false, $endDate = false){
        $query = RequestAbsent::query()
                ->has('user')
                ->where(DB::raw('DATE(from_time)'), '>=', $startDate ?? DATE(NOW()))
                ->where(DB::raw('DATE(from_time)'), '<', $endDate ?? DATE(NOW()))
                ->orderBy('from_time', 'DESC')->orderBy('created_at', 'DESC');

        if(Auth::user()->role == config('common.user.role.user')) {
            $query->where('user_id', Auth::id());
        }

        return $query->get();
    }

    public static function getUseLeaveHour($userId, $start, $timesheet=null)
    {
        $config = ReportConfig::first();
        $start = Carbon::make($start)->format('Y-m-d '.$config->start);
        $end = Carbon::make($start)->format('Y-m-d '.$config->end);
        $query = RequestAbsent::query()
            ->where([
                'user_id' => $userId,
            ])
            ->where('status', '<>', config('common.request_absent.reject'))
            ->where(function ($query) use ($start, $end) {
                $query->orWhere(function ($query) use ($start, $end) {
                    $query->where('from_time', '>=', $start)
                        ->where('to_time', '<=', $end);
                });
                $query->orWhere(function ($query) use ($start, $end) {
                    $query->where('from_time', '<=', $start)
                        ->where('to_time', '>=', $end);
                });
                $query->orWhere(function ($query) use ($start, $end) {
                    $query->where('from_time', '>=', $start)
                        ->where('from_time', '<=', $end);
                });
                $query->orWhere(function ($query) use ($start, $end) {
                    $query->where('to_time', '>=', $start)
                        ->where('to_time', '<=', $end);
                });
            });

        $requestAbsents = $query->get();
        $totalNotUseLeaveHour = 0;
        $leaveHourInWorkHour = 0;
        $offsetHour = 0;
        if ($timesheet && $timesheet->check_in > $start && $timesheet->check_in < Carbon::make($start)->format('Y-m-d '.$config->end_morning)){
            $offsetHour = Timesheet::work_house(Carbon::make($start)->format('Y-m-d '.$config->end), $timesheet->check_out);
        }

        foreach ($requestAbsents as $requestAbsent){
            $fromTime = strtotime($requestAbsent->from_time)<strtotime($start)
                    ? $start : $requestAbsent->from_time;
            $toTime = strtotime($requestAbsent->to_time)>strtotime($end)
                        ? $end : $requestAbsent->to_time;

            $leaveTime = Timesheet::work_house($fromTime, $toTime);

            if ($timesheet && $fromTime >= $timesheet->check_in && $toTime <= $timesheet->check_out){
                $leaveHourInWorkHour += $leaveTime;
            }

            if ($timesheet && $requestAbsent->use_leave_hour == 0 && $requestAbsent->status == config('common.request_absent.approve')
                && !($fromTime >= $timesheet->check_out || $toTime <= $timesheet->check_in)){
                if ($fromTime <= $timesheet->check_in && $toTime >= $timesheet->check_in){
                    $totalNotUseLeaveHour += Timesheet::work_house($fromTime, $timesheet->check_in);
                } elseif ($fromTime <= $timesheet->check_out && $toTime >= $timesheet->check_out){
                    $totalNotUseLeaveHour += Timesheet::work_house($timesheet->check_out, $toTime);
                }else{
                    $totalNotUseLeaveHour += $leaveTime;
                }
            }elseif ($requestAbsent->use_leave_hour == 0 && $requestAbsent->status == config('common.request_absent.approve')){
                $totalNotUseLeaveHour += $leaveTime;
            }

            if ($timesheet && $fromTime < $timesheet->check_in){
                $totalNotUseLeaveHour = $totalNotUseLeaveHour >= $offsetHour ? $totalNotUseLeaveHour - $offsetHour : 0;
            }
        }

        return [
            'totalNotUseLeaveHour' => $totalNotUseLeaveHour,
            'leaveHourInWorkHour' => $leaveHourInWorkHour
        ];
    }
}
