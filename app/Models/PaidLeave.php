<?php

namespace App\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Timesheet;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;
use App\Models\NationalDay;

class PaidLeave extends Model
{
    use HasFactory;

    protected $table='paid_leave';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id', 'month_year', 'day_left', 'day_add_in_month', 'day_use_in_month', 'day_edit', 'comment', 'leave_hour_in_work_hour', 'not_use_leave_hour'
    ];

    /**
     * Get paid leave with condition
     * @param array $condition
     */
    protected function getPaidLeaveWithCondition($condition = [])
    {
        $query = PaidLeave::query();
        if (isset($condition['month_year']) && !empty($condition['month_year']))
        {
            $query->where('month_year', '=', $condition['month_year']);
        }
        if (isset($condition['user_id']) && !empty($condition['user_id']))
        {
            $query->where('user_id', '=', $condition['user_id']);
        }

        return $query;
    }

    /**
     * Get one paid leave with condition
     * @param array $condition
     */
    public function getOnePaidLeaveWithCondition($condition = [])
    {
        return $this->getPaidLeaveWithCondition($condition)->first();
    }

    /**
     * salary deduction day
     * @param int $timeInFuture
     * @return int
     */
    public function salaryDeductionDay($timeInFuture = 0) {
        return $this->day_use_in_month - $timeInFuture + $this->leave_hour_in_work_hour - $this->not_use_leave_hour < ($this->day_left + $this->day_add_in_month + $this->day_edit) ?
            $this->not_use_leave_hour :
            ($this->day_use_in_month - $timeInFuture + $this->leave_hour_in_work_hour) - ($this->day_left + $this->day_add_in_month + $this->day_edit);
    }

    /**
     * number of leave days left
     * @param int $timeInFuture
     * @return int
     */
    public function leaveDaysLeft($timeInFuture = 0) {
        $days = $this->day_use_in_month + $this->leave_hour_in_work_hour - $this->not_use_leave_hour - $timeInFuture < ($this->day_left + $this->day_add_in_month + $this->day_edit) ?
            ($this->day_left + $this->day_add_in_month + $this->day_edit) - ($this->day_use_in_month + $this->leave_hour_in_work_hour - $this->not_use_leave_hour - $timeInFuture) : 0;

        return $days;
    }

    public static function daysUserInMonth($userId, $mothYear) {
        $month = date('m', strtotime($mothYear));
        $year = date('Y', strtotime($mothYear));
        $condition = [
            'user_id' => $userId,
            'month' => $month,
            'year' => $year
        ];
        $config = ReportConfig::first();
        $timeSheets = (new Timesheet)->getListTimeSheet($condition);
        $nationalDays = (new NationalDay)->getListNationalDay($condition);
        $whTotal = 0;
        $totalHoliday = (new NationalDay)->countHolidayByMonth($month, $year);
        foreach ($timeSheets as $timeSheet){
            $nationaDay = NationalDay::holiday($timeSheet->check_in, $nationalDays);
            if(in_array(\Carbon\Carbon::make($timeSheet->check_in)->dayOfWeek, json_decode($config->work_days)) && !$nationaDay) {
                $whTotal += Timesheet::work_house($timeSheet->check_in, $timeSheet->check_out);
            }
        }
        $workingDays = daysWorkingInMonth($month, $year);
        $workHour = ReportConfig::getWorkHour();
        return ($workingDays - $totalHoliday) * $workHour  - $whTotal;
    }

    public static function daysLeftFirstMonth($userId, $monthYear) {
        $monthLast = date('Y-m', strtotime(date("Y-m-d", strtotime($monthYear)) . " -01 month"));
        $paidLeaveLast = self::query()->where('month_year', '=', $monthLast)
                ->where('user_id', '=', $userId)->first();

        return $paidLeaveLast ? $paidLeaveLast->leaveDaysLeft() : 0;
    }

    public static function createOrUpdate($userId, $monthYear)
    {
        $paidLeave = self::query()->where('user_id', '=', $userId)
                        ->where('month_year', '=', $monthYear)->first();
        try {
            if($paidLeave) {
                $paidLeave->update([
                    'day_use_in_month' => self::daysUserInMonth($userId, $monthYear),
                    'day_left' => self::daysLeftFirstMonth($userId, $monthYear)
                ]);
            } else {
                $user = User::find($userId);
                self::create([
                    'user_id' => $userId,
                    'month_year' => $monthYear,
                    'day_left' => self::daysLeftFirstMonth($userId, $monthYear),
                    'day_add_in_month' => $user->paid_leave_start_date && $monthYear < $user->paid_leave_start_date ? 0 : ReportConfig::getWorkHour(),
                    'day_use_in_month' => self::daysUserInMonth($userId, $monthYear),
                ]);
            }
            self::updateFollowingMonths($userId, $monthYear);
        } catch (\Exception $exception) {
            Log::error('Create Or Update Paid Leave Error: '.$exception->getMessage());
        }
    }

    /**
     * update the following months
     * @param type $userId
     * @param type $monthYear
     * return void
     */
    public static function updateFollowingMonths($userId, $monthYear)
    {
        $paidLeaves = self::query()->where('user_id', '=', $userId)
                        ->where('month_year', '>', $monthYear)
                        ->orderBy('month_year')->get();
        $user = User::find($userId);
        try {
            foreach ($paidLeaves as $paidLeave) {
                $paidLeave->update([
                    'day_left' => self::daysLeftFirstMonth($userId, $paidLeave->month_year),
                    'day_add_in_month' => $user->paid_leave_start_date && $paidLeave->month_year < $user->paid_leave_start_date ? 0 : ReportConfig::getWorkHour(),
                    'day_use_in_month' => self::daysUserInMonth($userId, $paidLeave->month_year),
                ]);
            }
        } catch (\Exception $exception) {
            Log::error('Update the following months error: '.$exception->getMessage());
        }
    }

    public static function createOrUpdateAllUserByMonth($monthYear)
    {
        $users = User::getUsers();
        foreach ($users as $user) {
            PaidLeave::createOrUpdate($user->id, $monthYear);
        }
    }
}
