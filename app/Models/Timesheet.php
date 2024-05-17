<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\NationalDay;
use Illuminate\Support\Carbon;
use App\Models\User;
use App\Models\OvertimeUser;
use Illuminate\Support\Facades\DB;
use App\Models\Position;

class Timesheet extends Model
{
    use HasFactory;

    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';

    //protected $dateFormat = 'U';

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'timesheet';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id', 'check_in', 'check_out', 'comment', 'updated_by', 'gps'
    ];

    private $config;
    private $workHour;
    private $workHourMorning;
    private $workHourAfternoon;
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->config = ReportConfig::first();
        $this->workHourMorning = (strtotime(Carbon::make($this->config->end_morning)) - strtotime(Carbon::make($this->config->start)))/3600;
        $this->workHourAfternoon = (strtotime(Carbon::make($this->config->end)) - strtotime(Carbon::make($this->config->start_afternoon)))/3600;
        $this->workHour = $this->workHourMorning + $this->workHourAfternoon;
    }

    public function users()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function scopeToday($query)
    {
        return $query->whereDate('check_in', today())->orWhereDate('check_out', today());
    }

    public function scopeStaff($query, $staffId)
    {
        return $query->where('user_id', $staffId);
    }

    public function getListTimeSheet($condition = [])
    {
        $query = Timesheet::query();
        //$query->selectRaw('*, min(check_in) as check_in, max(check_out) as check_out');
        $query->selectRaw('*');
        if (isset($condition['user_id']) && !empty($condition['user_id'])) {
            $query->where('user_id', '=', $condition['user_id']);
        }
        if (isset($condition['start_date']) && !empty($condition['start_date'])) {
            $query->where('check_in', '>=', $condition['start_date']);
        }
        if (isset($condition['end_date']) && !empty($condition['end_date'])) {
            $query->where('check_in', '<', $condition['end_date']);
        }
        if (isset($condition['month']) && !empty($condition['month'])) {
            $query->whereMonth('check_in', '=', $condition['month']);
        }
        if (isset($condition['year']) && !empty($condition['year'])) {
            $query->whereYear('check_in', '=', $condition['year']);
        }
        $query->orderBy('user_id', 'ASC');
        $query->orderBy('check_in', 'ASC');
        //$query->groupByRaw('date(`check_in`) ASC');

        return $query->get();
    }

    public static function getByDate($userId, $date)
    {
        return Timesheet::query()
                ->where('user_id', '=', $userId)
                ->whereDate('check_in', '=', $date)
                ->first();
    }

    /**
     * Tinh gio lam viec
     * @param $start_time
     * @param $end_time
     */
    public static function work_house($start_time, $end_time, $holiday = false)
    {
        $config = ReportConfig::first();
        if (strtotime($start_time)<strtotime(date("Y/m/d ".$config->start_morning_late,strtotime($start_time)))){
            $start_time=date("Y/m/d ".$config->start,strtotime($start_time));
        }elseif (strtotime($start_time)<strtotime(date("Y/m/d ".$config->start_afternoon,strtotime($start_time))) &&
            strtotime($start_time)>=strtotime(date("Y/m/d ".$config->end_morning,strtotime($start_time)))){
            $start_time=date("Y/m/d ".$config->start_afternoon,strtotime($start_time));
        }

        if (strtotime($end_time)>strtotime(date("Y/m/d ".($config->offset_time ?? $config->end),strtotime($end_time))) && !$holiday){
            $end_time=date("Y/m/d ".($config->offset_time ?? $config->end),strtotime($end_time));
        }

        $wh_temp=strtotime(Carbon::create($end_time)->format('Y-m-d H:i:00'))-strtotime(Carbon::create($start_time)->format('Y-m-d H:i:00'));

        //loai gio nghi trua
        if (strtotime($start_time)<strtotime(date("Y/m/d ".$config->end_morning,strtotime($start_time)))
            && strtotime($end_time)>=strtotime(date("Y/m/d ".$config->start_afternoon,strtotime($start_time)))){
            $wh_temp=$wh_temp-(strtotime(Carbon::make($config->start_afternoon)) - strtotime(Carbon::make($config->end_morning)));
        }

        $work_hours=$wh_temp/3600;
        $workHourMorning = (strtotime(Carbon::make($config->end_morning)) - strtotime(Carbon::make($config->start)))/3600;
        $workHourAfternoon = (strtotime(Carbon::make($config->end)) - strtotime(Carbon::make($config->start_afternoon)))/3600;

        if (!$holiday){
            if (strtotime($start_time)>strtotime(date("Y/m/d ".$config->end_morning,strtotime($start_time))) && $work_hours>$workHourAfternoon){
                $work_hours=$workHourAfternoon;
            }elseif(strtotime($end_time) < strtotime(date("Y/m/d ".$config->start_afternoon,strtotime($end_time)))  && $work_hours>$workHourMorning){
                $work_hours=$workHourMorning;
            }elseif ($work_hours>$workHourMorning + $workHourAfternoon) {
                $work_hours=$workHourMorning + $workHourAfternoon;
            } else {
                $work_hours= self::block_time($work_hours);
            }
        }

        return $work_hours;
    }

    /**
     * từ 0-14 sẽ tính là 0; 15 -29 tính là 0,25; từ 30 đến 44 tính là 0,5 từ 45 đến 59 tính là 0,75
     * @param $work_hours
     * @return false|float
     */
    public static function block_time($work_hours){
        $fraction = $work_hours - floor($work_hours);

        if ($fraction*60<15){
            $work_hours=floor($work_hours);
        }elseif ($fraction*60<30){
            $work_hours=floor($work_hours)+0.25;
        }elseif ($fraction*60<45){
            $work_hours=floor($work_hours)+0.5;
        }
        else{
            $work_hours=floor($work_hours)+0.75;
        }

        return $work_hours > 0 ? $work_hours : 0;
    }


    // /**
    //  * Tinh Overtime
    //  * 1.trước 10h tối  và ngày thường thì là loại 1: 150%
    //  * 2. sau 10 tối và ngày thường thì là loại 2: 200%
    //  * 3. thứ 7,cn thì là loại 3: x200%
    //  * @param $from_time
    //  * @param $to_time
    //  */
    // public static function overtime($from_time, $to_time)
    // {
    //     $config = ReportConfig::first();
    //     $nationalDays = (new NationalDay)->getListNationalDay();
    //     $ot = [
    //         'ot1' => 0,
    //         'ot2' => 0,
    //         'ot3' => 0,
    //         'ot4' => 0,
    //     ];
    //     if (NationalDay::holiday(Carbon::make($from_time)->format('Y-m-d'), $nationalDays)){
    //         $ot['ot4'] = self::block_time(Timesheet::work_house($from_time, $to_time, true));
    //     }elseif (!in_array(Carbon::make($from_time)->dayOfWeek, json_decode($config->work_days))){
    //         $ot['ot3'] = self::block_time(Timesheet::work_house($from_time, $to_time, true));
    //     }else{            
    //         if (strtotime($to_time)>strtotime(date("Y/m/d $config->start_night_OT",strtotime($to_time)))){
    //             $ot['ot2'] = Timesheet::block_time((strtotime($to_time) - strtotime(date("Y/m/d $config->start_night_OT",strtotime($to_time))))/3600);
    //         }
    //         if (strtotime($to_time)>strtotime(date("Y/m/d $config->start_normal_OT",strtotime($to_time)))){
    //             $ot['ot1'] = Timesheet::block_time((strtotime($to_time) - strtotime(date("Y/m/d $config->start_normal_OT",strtotime($to_time))))/3600) - $ot['ot2'];                
    //         }
    //     }

    //     return $ot;
    // }


    /**
     * Tinh Overtime
     * 1.trước 10h tối  và ngày thường thì là loại 1: 150%
     * 2. sau 10 tối và ngày thường thì là loại 2: 200%
     * 3. thứ 7,cn thì là loại 3: x200%
     * @param $from_time
     * @param $to_time
     */
    public static function overtimeRequestBase($from_time, $to_time)
    {
        $config = ReportConfig::first();
        $nationalDays = (new NationalDay)->getListNationalDay();
        $ot = [
            'ot1' => 0,
            'ot2' => 0,
            'ot3' => 0,
            'ot4' => 0,
        ];
        if (NationalDay::holiday(Carbon::make($from_time)->format('Y-m-d'), $nationalDays)){        // If holiday            
            //$ot['ot4'] = self::block_time((strtotime($to_time) - strtotime($from_time))/3600);     
            $ot['ot4'] =  self::block_time(Timesheet::work_house($from_time, $to_time, true));
        } elseif (!in_array(Carbon::make($from_time)->dayOfWeek, json_decode($config->work_days))){  // If Weekend
            //$ot['ot3'] = self::block_time((strtotime($to_time) - strtotime($from_time))/3600);
            $ot['ot3'] =  self::block_time(Timesheet::work_house($from_time, $to_time, true));
        } else {                                                                                     // If Normal day    
            if (strtotime($to_time)>strtotime(date("Y/m/d $config->start_night_OT",strtotime($from_time)))){
                //$ot['ot2'] = self::block_time((strtotime($to_time) - strtotime(date("Y/m/d $config->start_night_OT",strtotime($from_time))))/3600);
                $ot['ot2'] = self::block_time((strtotime($to_time) - max(strtotime($from_time), strtotime(date("Y/m/d $config->start_night_OT",strtotime($from_time)))))/3600);                
            }
            if (strtotime($to_time)>strtotime(date("Y/m/d $config->start_normal_OT",strtotime($from_time)))){
                $ot['ot1'] = self::block_time((strtotime($to_time) - max(strtotime($from_time), strtotime(date("Y/m/d $config->start_normal_OT",strtotime($from_time)))))/3600) - $ot['ot2'];                
            }

            // If early moring overtime (similar to overnight so count it as OT2)
            if ($from_time < date("Y-m-d $config->end_night_OT",strtotime($from_time))) {
                if (strtotime($to_time) <= strtotime(date("Y-m-d $config->end_night_OT",strtotime($to_time)))){
                    $ot['ot2'] += Timesheet::block_time((strtotime($to_time) - strtotime($from_time))/3600);
                } else {
                    $ot['ot2'] += Timesheet::block_time((strtotime(date("Y-m-d $config->end_night_OT",strtotime($from_time))) - strtotime($from_time))/3600);
                }
            }
        }

        return $ot;
    }


    /**
     * Tinh Overtime
     * 1.trước 10h tối  và ngày thường thì là loại 1: 150%
     * 2. sau 10 tối và ngày thường thì là loại 2: 200%
     * 3. thứ 7,cn thì là loại 3: x200%
     * @param $ck_in_time
     * @param $to_time
    **/ 
    public static function overtime($ck_in_time, $ck_out_time, $ck_out_overnight, $ot_from_time, $ot_to_time)
    {
        $config = ReportConfig::first();
        $nationalDays = (new NationalDay)->getListNationalDay();
        $ot = [
            'ot1' => 0,
            'ot2' => 0,
            'ot3' => 0,
            'ot4' => 0,
        ];
        if (NationalDay::holiday(Carbon::make($ck_in_time)->format('Y-m-d'), $nationalDays)){
            // If NOT OVERNIGHT overtime
            if (date("Y-m-d", strtotime($ot_to_time)) == date("Y-m-d", strtotime($ot_from_time))) {
                $ot['ot4'] = self::block_time(Timesheet::work_house($ck_in_time, $ck_out_time, true));
            } else {
                // If OVERNIGHT overtime
                if (isset($ck_out_overnight)) {                
                    $ck_out_time_adjusted = date("Y-m-d 23:45:00", strtotime($ck_in_time)); // use 23:45:00 then plus 0.25hour later in OT2 because we could not use 24:00:00 as checkout time.                
                    $ot['ot4'] = Timesheet::block_time((strtotime($ck_out_time_adjusted) - strtotime($ck_in_time))/3600) +0.25;
                    $ot['ot4'] += Timesheet::block_time((strtotime($ck_out_overnight) - strtotime(date("Y-m-d 00:00:00",strtotime($ck_out_overnight))))/3600);
                } else {
                    $ot['ot4'] = Timesheet::block_time((strtotime($ck_out_time) - strtotime($ck_in_time))/3600);
                }
                
            }
        }elseif (!in_array(Carbon::make($ck_in_time)->dayOfWeek, json_decode($config->work_days))){
            // If NOT OVERNIGHT overtime
            if (date("Y-m-d", strtotime($ot_to_time)) == date("Y-m-d", strtotime($ot_from_time))) {
                $ot['ot3'] = self::block_time(Timesheet::work_house($ck_in_time, $ck_out_time, true));
            } else {
                // If OVERNIGHT overtime
                if (isset($ck_out_overnight)) {
                    $ck_out_time_adjusted = date("Y-m-d 23:45:00", strtotime($ck_in_time)); // use 23:45:00 then plus 0.25hour later in OT2 because we could not use 24:00:00 as checkout time.                
                    $ot['ot3'] = Timesheet::block_time((strtotime($ck_out_time_adjusted) - strtotime($ck_in_time))/3600) +0.25;
                    $ot['ot3'] += Timesheet::block_time((strtotime($ck_out_overnight) - strtotime(date("Y-m-d 00:00:00",strtotime($ck_out_overnight))))/3600);
                } else {
                    $ot['ot3'] = Timesheet::block_time((strtotime($ck_out_time) - strtotime($ck_in_time))/3600);
                }
            }
        }else{
            // If NOT OVERNIGHT overtime
            if (date("Y-m-d", strtotime($ot_to_time)) == date("Y-m-d", strtotime($ot_from_time))) {
             
                
                // If normal 
                if (strtotime($ck_out_time)>strtotime(date("Y-m-d $config->start_night_OT",strtotime($ck_out_time)))){
                    $ot['ot2'] = Timesheet::block_time((strtotime($ck_out_time) - strtotime(date("Y-m-d $config->start_night_OT",strtotime($ck_out_time))))/3600);
                }
                if (strtotime($ck_out_time)>strtotime(date("Y-m-d $config->start_normal_OT",strtotime($ck_out_time)))){
                    $ot['ot1'] = Timesheet::block_time((strtotime($ck_out_time) - strtotime(date("Y-m-d $config->start_normal_OT",strtotime($ck_out_time))))/3600) - $ot['ot2'];                
                }


                // If early moring overtime (similar to overnight so count it as OT2)
                if ($ot_from_time < date("Y-m-d $config->end_night_OT",strtotime($ck_in_time))) {
                    if (strtotime($ck_out_time) <= strtotime(date("Y-m-d $config->end_night_OT",strtotime($ck_out_time)))){
                        $ot['ot2'] += Timesheet::block_time((strtotime($ck_out_time) - strtotime($ck_in_time))/3600);
                    } else {
                        $ot['ot2'] += Timesheet::block_time((strtotime(date("Y-m-d $config->end_night_OT",strtotime($ck_in_time))) - strtotime($ck_in_time))/3600);
                    }
                }                      
            } else { // IF OVERNIGHT overtime
                    if (isset($ck_out_overnight)) {
                        $ck_out_time_adjusted = date("Y-m-d 23:45:00", strtotime($ck_in_time)); // use 23:45:00 then plus 0.25hour later in OT2 because we could not use 24:00:00 as checkout time.
                        $ot['ot2'] = Timesheet::block_time((strtotime($ck_out_time_adjusted) - strtotime(date("Y-m-d $config->start_night_OT",strtotime($ck_out_time_adjusted))))/3600) + 0.25;
                        $ot['ot1'] = Timesheet::block_time((strtotime($ck_out_time_adjusted) - strtotime(date("Y-m-d $config->start_normal_OT",strtotime($ck_out_time_adjusted))))/3600) +0.25 - $ot['ot2'];
                        $ot['ot2'] += Timesheet::block_time((strtotime($ck_out_overnight) - strtotime(date("Y-m-d 00:00:00",strtotime($ck_out_overnight))))/3600);
                    } else {                        
                        $ot['ot2'] = Timesheet::block_time((strtotime($ck_out_time) - strtotime(date("Y-m-d $config->start_night_OT",strtotime($ck_out_time))))/3600);
                        $ot['ot1'] = Timesheet::block_time((strtotime($ck_out_time) - strtotime(date("Y-m-d $config->start_normal_OT",strtotime($ck_out_time))))/3600) - $ot['ot2'];    
                    }
            }
        }

        return $ot;
    }

    /**
     * Time sheet by search period
     * @param type $startDate
     * @param type $endDate
     * @param type $userId
     * @return array
     */
    public function getTimesheetCalendar($startDate, $endDate, $userId = false, $dataInDay = 0) {
        $users = User::getUsers(2, $userId);
        $condition = [
            'start_date' => $startDate,
            'end_date' => $endDate,
            'user_id' => $userId
        ];
        $config = ReportConfig::first();
        $timesheets = (new Timesheet)->getListTimeSheet($condition);
        $timesheetLogs = (new TimesheetLog)->getListTimeSheetLog($condition);
        $overtimes = (new OvertimeUser)->getListOvertimeUser(['user_id' => $userId, 'status' => config('common.overtime.approve')]);
        $nationalDays = (new NationalDay)->getListNationalDay($condition);
        $comments = (new Comments())->getListComment($condition);
        $timeSheetArr = [
            'timesheets' => [],
            'total' => []
        ];
        $stt = 0; $totalWh = 0; $totalMh = 0; $totalOt1 = 0; $totalOt2 = 0; $totalOt3 = 0; $totalOt4 = 0; $timeInFuture = 0;
        $totalNotUseLeaveHour = 0;
        $leaveHourInWorkHour = 0;
        foreach ($users as $user) {
            $totalDate = Carbon::parse($endDate)->diffInDays(Carbon::parse($startDate));
            $start = Carbon::parse($startDate);
            $timesheetUser = $timesheets->where('user_id', '=', $user->id);
            $timesheetLogUser = $timesheetLogs->where('user_id', '=', $user->id);
            $overtimeUse = $overtimes->where('user_id', '=', $user->id);
            $commentUse = $comments->where('user_id', '=', $user->id);
            $workLocationUse = Position::getAllowPositions($user->id, true);

            //dd($commentUse);
            for($i = 0; $i < $totalDate; $i++) {
                $timesheet = $timesheetUser->filter(function($item) use($start){
                    return Carbon::parse($item->check_in)->format('Y-m-d') == $start->format('Y-m-d');
                })->first();
                $timesheetLogArr = $timesheetLogUser->filter(function ($item) use ($start) {
                //$timesheetLogArr = $timesheetLogs->filter(function ($item) use ($start) {
                    return Carbon::parse($item->date_time)->format('Y-m-d') == $start->format('Y-m-d');
                });
                $checkoutOverNightFromLog = $timesheetLogUser->filter(function ($item) use ($start, $config) {
                    $nextDay = date('Y-m-d', strtotime('+1 day', strtotime($start)));
                    return Carbon::parse($item->date_time)->format('Y-m-d') == $nextDay
                        && date("H:i", strtotime($item->date_time)) <= $config->end_night_OT;
                })->sortBy('date_time')->first();

                
                // There is a case that admin add/edit checkin/out time from CMS so we have to get data from Timesheet table also (not only timesheetlog)
                $checkoutOverNightTimesheet = $timesheetUser->filter(function ($item) use ($start, $config) {
                    $nextDay = date('Y-m-d', strtotime('+1 day', strtotime($start)));
                    return Carbon::parse($item->check_in)->format('Y-m-d') == $nextDay
                        && date("H:i", strtotime($item->check_in)) <= $config->end_night_OT;
                })->sortBy('check_in')->first();
                
                
                if (isset($checkoutOverNightFromLog) && isset($checkoutOverNightTimesheet)) {

                    $checkoutOverNight = $checkoutOverNightFromLog->date_time < $checkoutOverNightTimesheet->check_in ? $checkoutOverNightFromLog->date_time : $checkoutOverNightTimesheet->check_in;

                    // if ($checkoutOverNightTimesheet->check_out == null) {
                    //     $checkoutOverNight = $checkoutOverNightFromLog->date_time > $checkoutOverNightTimesheet->check_in ? $checkoutOverNightFromLog->date_time : $checkoutOverNightTimesheet->check_in;
                    // } else if (date("H:i", strtotime($checkoutOverNightTimesheet->check_out)) < $config->end_night_OT) {
                    //     $checkoutOverNight = $checkoutOverNightFromLog->date_time > $checkoutOverNightTimesheet->check_out ? $checkoutOverNightFromLog->date_time : $checkoutOverNightTimesheet->check_out;
                    // }
                } else if (!isset($checkoutOverNightTimesheet) && (isset($checkoutOverNightFromLog) )) {                    
                        $checkoutOverNight = $checkoutOverNightFromLog->date_time;                    
                } else if (isset($checkoutOverNightTimesheet) && (!isset($checkoutOverNightFromLog) )) {

                    $checkoutOverNight = $checkoutOverNightTimesheet->check_in;


                    // if ($checkoutOverNightTimesheet->check_out == null) { 
                    //     $checkoutOverNight = $checkoutOverNightTimesheet->check_in;
                    // } else if (date("H:i", strtotime($checkoutOverNightTimesheet->check_out)) < $config->end_night_OT) {
                    //     $checkoutOverNight = $checkoutOverNightTimesheet->check_out;
                    // } else {
                    //     $checkoutOverNight = date("Y-m-d $config->end_night_OT", strtotime($start));
                    // }


                } else {
                    $checkoutOverNight = null;
                }
               
                $overtime = $overtimeUse->filter(function($item) use($start){
                    return $item->overtime &&
                        Carbon::parse($item->overtime->from_time)->format('Y-m-d') == $start->format('Y-m-d') &&
                        Carbon::parse($item->overtime->to_time)->format('Y-m-d') >= $start->format('Y-m-d');
                })->sortByDesc('from_time')->last();

                $comment = $commentUse->filter(function($item) use($start){
                    return Carbon::parse($item->date)->format('Y-m-d') == $start->format('Y-m-d');
                })->first();
                $nationaDay = NationalDay::holiday($start->format('Y-m-d'), $nationalDays);
                $dayOfWeek = !in_array(\Carbon\Carbon::make($start)->dayOfWeek, json_decode($this->config->work_days));
                $ot = isset($overtime) && isset($timesheet) ? Timesheet::overtime($timesheet->check_in, $timesheet->check_out, $checkoutOverNight, $overtime->from_time, $overtime->to_time) : false;
                
                
                
                // OLD CODE
                // $wh = 0;
                // $mh = $dayOfWeek || $nationaDay ? 0 : $this->workHour;
                // $leaveHour = RequestAbsent::getUseLeaveHour($user->id, $start, $timesheet);
                // $leaveHourInWorkHour += $start->format('Y/m/d') >= Carbon::now()->format('Y/m/d') ? 0 : $leaveHour['leaveHourInWorkHour'];
                // if ($timesheet && !$dayOfWeek && !$nationaDay) {
                //     $wh = Timesheet::work_house($timesheet->check_in, $timesheet->check_out);
                //     $wh -= $leaveHour['leaveHourInWorkHour'];
                //     $mh = $this->workHour - $wh;
                //     if ($leaveHour['totalNotUseLeaveHour'] > 0 &&abs($mh - $leaveHour['totalNotUseLeaveHour']) == 0.25){
                //         $leaveHour['totalNotUseLeaveHour'] = $mh;
                //     }
                // }
                // $totalNotUseLeaveHour += $start->format('Y/m/d') >= Carbon::now()->format('Y/m/d') ? 0 : $leaveHour['totalNotUseLeaveHour'];

                $wh = 0;
                $mh = $dayOfWeek || $nationaDay ? 0 : $this->workHour;
                $leaveHour = RequestAbsent::getUseLeaveHour($user->id, $start, $timesheet);
                $leaveHourInWorkHour += $start->format('Y/m/d') >= Carbon::now()->format('Y/m/d') ? 0 : $leaveHour['leaveHourInWorkHour'];
                
                if (!isset($overtime) || !isset($timesheet) || !isset($checkoutOverNight)) { // If not OVERNIGHT overtime
                    if ($timesheet && !$dayOfWeek && !$nationaDay) { // If not weekend or holiday
                        $wh = Timesheet::work_house($timesheet->check_in, $timesheet->check_out);
                        $wh -= $leaveHour['leaveHourInWorkHour'];
                        $mh = $this->workHour - $wh;
                        if ($leaveHour['totalNotUseLeaveHour'] > 0 &&abs($mh - $leaveHour['totalNotUseLeaveHour']) == 0.25){
                            $leaveHour['totalNotUseLeaveHour'] = $mh;
                        }
                    }                    
                } else { // If OVERNIGHT overtime
                    if ($timesheet && !$dayOfWeek && !$nationaDay) {
                    //if ($timesheet) {
                        $ck_out_time_adjusted = date("Y-m-d 23:59:00", strtotime($timesheet->check_in));
                        // $isHoliday = $dayOfWeek || $nationaDay ? true : false;
                        // $wh = Timesheet::work_house($timesheet->check_in, $ck_out_time_adjusted, $isHoliday);
                        
                        $wh = Timesheet::work_house($timesheet->check_in, $ck_out_time_adjusted);
                        $wh -= $leaveHour['leaveHourInWorkHour'];
                        $mh = $this->workHour - $wh;
                        if ($leaveHour['totalNotUseLeaveHour'] > 0 &&abs($mh - $leaveHour['totalNotUseLeaveHour']) == 0.25){
                            $leaveHour['totalNotUseLeaveHour'] = $mh;
                        }
                    }
                }

                $totalNotUseLeaveHour += $start->format('Y/m/d') >= Carbon::now()->format('Y/m/d') ? 0 : $leaveHour['totalNotUseLeaveHour'];



                $totalWh += (float) $wh;
                $totalMh += (float) $mh;
                $timeInFuture += $start->format('Y/m/d') >= Carbon::now()->format('Y/m/d') ? $mh : 0;
                $totalOt1 += (float) ($ot['ot1'] ?? 0);
                $totalOt2 += (float) ($ot['ot2'] ?? 0);
                $totalOt3 += (float) ($ot['ot3'] ?? 0);
                $totalOt4 += (float) ($ot['ot4'] ?? 0);

                $timeSheetArr['timesheets'][$stt]['stt'] = $stt+1;
                $timeSheetArr['timesheets'][$stt]['date'] = $start->format('Y/m/d');
                $timeSheetArr['timesheets'][$stt]['first_name'] = $user->first_name;
                $timeSheetArr['timesheets'][$stt]['last_name'] = $user->last_name;
                $timeSheetArr['timesheets'][$stt]['checkin'] = $timesheet && $timesheet->check_in
                    ? Carbon::parse($timesheet->check_in)->format('H:i') : "";                
                $timeSheetArr['timesheets'][$stt]['checkout'] = $timesheet && $timesheet->check_out
                    ? Carbon::parse($timesheet->check_out)->format('H:i') : "";
                if (isset($overtime) && isset($timesheet) && isset($checkoutOverNight) && 
                    date("Y-m-d", strtotime($overtime->from_time)) < date("Y-m-d", strtotime($overtime->to_time)) ) {
                    //$timeSheetArr['timesheets'][$stt]['checkout'] = Carbon::parse($checkoutOverNight->date_time)->format('H:i');
                    $timeSheetArr['timesheets'][$stt]['checkout'] = Carbon::parse($checkoutOverNight)->format('H:i');
                }
                $timeSheetArr['timesheets'][$stt]['wh'] = $wh;
                $timeSheetArr['timesheets'][$stt]['mh'] = $start->format('Y/m/d') >= Carbon::now()->format('Y/m/d') ? 0 : $mh;
                $timeSheetArr['timesheets'][$stt]['ot1'] = $ot['ot1'] ?? 0;
                $timeSheetArr['timesheets'][$stt]['ot2'] = $ot['ot2'] ?? 0;
                $timeSheetArr['timesheets'][$stt]['ot3'] = $ot['ot3'] ?? 0;
                $timeSheetArr['timesheets'][$stt]['ot4'] = $ot['ot4'] ?? 0;
                $timeSheetArr['timesheets'][$stt]['national_day'] = $nationaDay;
                $timeSheetArr['timesheets'][$stt]['user_id'] = $user->id;
                $timeSheetArr['timesheets'][$stt]['staff_id'] = $user->staff_id;
                $timeSheetArr['timesheets'][$stt]['comment'] = $comment->comment ?? "";
                $timeSheetArr['timesheets'][$stt]['day_of_week'] = $dayOfWeek;
                $timeSheetArr['timesheets'][$stt]['id'] = $timesheet->id ?? "";
                $timeSheetArr['timesheets'][$stt]['is_late'] = !$nationaDay ? ($timesheet && $this->isCheckLate($timesheet)) : false;
                $t = 0;
                foreach ($timesheetLogArr as $key=>$timesheetLog){
                    $gps = $timesheetLog ? explode(';', $timesheetLog->gps) : false;
                    $latituteArr = $gps ? explode(':', $gps[0]) : false;
                    $latitute = $latituteArr[1] ?? null ;
                    $longituteArr = isset($gps[1]) ? explode(':', $gps[1]) : [];
                    $longitute = $longituteArr[1] ?? null ;
                    $timeSheetArr['timesheets'][$stt]['location'][$t]['lat'] = trim($latitute);
                    $timeSheetArr['timesheets'][$stt]['location'][$t]['long'] = trim($longitute);
                    $timeSheetArr['timesheets'][$stt]['location'][$t]['date_time'] = \Carbon\Carbon::make($timesheetLog->date_time)->format('Y/m/d H:i:s');
                    $timeSheetArr['timesheets'][$stt]['location'][$t]['location_id'] = $timesheetLog->location_id;
                    $timeSheetArr['timesheets'][$stt]['location'][$t]['location_name'] = $timesheetLog->position;
                    $timeSheetArr['timesheets'][$stt]['location'][$t]['checkLocation'] = $timesheetLog->location_id == 0 ? false:true;
                    $t++;
                }
                if($dataInDay == '1') {
                    $arrData = [];
                    if(count($timesheetLogArr) > 0) {
                        foreach ($timesheetLogArr as $key => $value){
                            array_push($arrData, ['time' => Carbon::parse($value->date_time)->format('H:i'), 'location' => $value->position]);
                        }
                    }
                    $timeSheetArr['timesheets'][$stt]['checktime'] = $arrData;
                }
                $start->addDay();
                $stt ++;
            }
        }

        $timeSheetArr['total']['wh'] = $totalWh;
        $timeSheetArr['total']['mh'] = $totalMh - $timeInFuture;
        $timeSheetArr['total']['timeInFuture'] = $timeInFuture;
        $timeSheetArr['total']['ot1'] = $totalOt1;
        $timeSheetArr['total']['ot2'] = $totalOt2;
        $timeSheetArr['total']['ot3'] = $totalOt3;
        $timeSheetArr['total']['ot4'] = $totalOt4;
        $timeSheetArr['total']['notUseLeaveHour'] = $totalNotUseLeaveHour;
        $timeSheetArr['total']['leaveHourInWorkHour'] = $leaveHourInWorkHour;
        if($userId) {
            $timeSheetArr['workLocationsUser'] = Position::getAllowPositions($userId);
        }

        if($dataInDay == '1') {
            $arr = [];
            foreach ($timeSheetArr['timesheets'] as $key => $value) {
                $index = 0;
                if(count($value['checktime']) > 0) {
                    foreach ($value['checktime'] as $keyCheck => $valueCheckTime) {
                        if($index > 0) {
                            $value = array_map(function($v){
                                return "";
                            }, $value);
                        }
                        $value['checktime'] = $valueCheckTime['time'];
                        $value['location'] = $valueCheckTime['location'];
                        array_push($arr, $value);
                        $index++;
                    }
                } else {
                    array_push($arr, $value);
                }
            }
            $timeSheetArr['timesheets'] = $arr;
        }
        return $timeSheetArr;
    }

    /**
     * Number of times each staff is late
     * @param type $startDate
     * @param type $endDate
     * @return type
     */
    public function getTimesStaffLate($startDate, $endDate)
    {
        $users = User::getUsers();
        $timesheets = $this->countTimeSheetLate($startDate, $endDate)->toArray();
        $usersLate = [];
        $i=0;
        foreach ($users as $user) {
            $key = array_search($user->id, array_column($timesheets, 'user_id'));
            if($key !== false && $timesheets[$key]->times > 0) {
                $usersLate[$i]['user_id'] = $user->id;
                $usersLate[$i]['staff_id'] = $user->staff_id;
                $usersLate[$i]['full_name'] = $user->getFullName();
                $usersLate[$i]['times'] = $timesheets[$key]->times;
                $i++;
            }
        }
        usort($usersLate, function($a, $b) {
            return [$b['times'],$a['staff_id']] <=> [$a['times'],$b['staff_id']];
        });

        return $usersLate;
    }

    /**
     * Calculate the number of times you are late according to the optimal time sheet
     * @param type $startDate
     * @param type $endDate
     * @return type
     */
    protected function countTimeSheetLate($startDate, $endDate)
    {
        $workDays = [];
        foreach (json_decode($this->config->work_days) as $value){
            $workDays[] = $value + 1;
        }
        $nationalDays = NationalDay::getListNationalDay(['start_date'=>$startDate, 'end_date'=>$endDate]);
        $timesheets = DB::table(function ($query) use ($startDate, $endDate) {
            $query->selectRaw('user_id, staff_id, concat(first_name, " ", last_name) as full_name'
                    . ', min(check_in) as check_in, max(check_out) as check_out'
                    . ', CONCAT(date(check_in),"__", user_id) as user_id_date')
            ->from($this->table)
            ->rightJoin('users', 'users.id', '=', 'timesheet.user_id')
            ->where('check_in', '>=', $startDate)
            ->where('check_in', '<', $endDate)
            ->groupBy('user_id_date');
        }, 't')
        ->whereIn(DB::raw('DAYOFWEEK(check_in)'), $workDays)
        ->where(function ($query) use ($nationalDays) {
            foreach ($nationalDays as $nationalDay){
                $query->where(function ($query) use ($nationalDay){
                    $query->orWhere('check_in', '<', $nationalDay->from_date);
                    $query->orWhere('check_in', '>', $nationalDay->to_date.' 23:59:59');
                });
            }
        })
        ->where(function ($query) {
           $query->where([
                    ['check_in', '>=', DB::raw('DATE_FORMAT(check_in,"%Y-%m-%d '.$this->config->start_morning_late.'")')],
                    ['check_in', '<', DB::raw('DATE_FORMAT(check_in,"%Y-%m-%d '.$this->config->end_morning.'")')],
                ])
               ->orWhere([
                    ['check_in', '>=', DB::raw('DATE_FORMAT(check_in,"%Y-%m-%d '.$this->config->start_afternoon_late.'")')]
               ]);
        })
        ->selectRaw('*, count(*) as times')
        ->orderBy('times', 'DESC')
        ->groupBy('user_id')
        ->get();

        return $timesheets;
    }

    public static function isDayOff($date){
        $nationalDays = (new NationalDay)->getListNationalDay();
        $isNationalDay = NationalDay::holiday($date, $nationalDays);
        if(!in_array(Carbon::make($date)->dayOfWeek, [0, 6]) && $isNationalDay) {
            return true;
        }

        return false;
    }

    public function update(array $attributes = array(), array $options = array()) {
        $updated = parent::update($attributes, $options);
        if($updated) {
            $checkIn = Carbon::parse($this->check_in)->format('Y-m-d');
            Timesheet::query()->where('user_id', $this->user_id)
                    ->whereDate('check_in', "$checkIn")
                    ->where('id', '!=', $this->id)
                    ->delete();
        }

        return $updated;
    }

    /**
     * checkLocation
     *
     * @param  mixed $lat1
     * @param  mixed $lon1
     * @param  mixed $lat2
     * @param  mixed $lon2
     * @return void
     */
    public static function checkLocation($lat1, $lon1, $workLocations)
    {
        $distanceLimit = ReportConfig::getDistanceLimit();
        $a = [];
        foreach ($workLocations as $item) {
            $lat2 = (float) $item->latitude;
            $lon2 = (float) $item->longitude;

            if (($lat1 == $lat2) && ($lon1 == $lon2)) {
                return 0;
              }
            else {
            $theta = $lon1 - $lon2;
            $dist = sin(deg2rad($lat1)) * sin(deg2rad($lat2)) +  cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($theta));
            $dist = acos($dist);
            $dist = rad2deg($dist);
            $miles = $dist * 60 * 1.1515;
            //$unit = strtoupper($unit);
            $unit = "K";

            if ($unit == "K") {
                $distance = ($miles * 1.609344);
            } else if ($unit == "N") {
                $distance = ($miles * 0.8684);
            } else {
                $distance = $miles;
            }
            }

            if($distance < ($distanceLimit / 1000)) {
                return $item->id;
            }
        }

        return 0;
    }

    public static function isCheckLate($timesheet) {
        $config = ReportConfig::first();
        $checkIn = Carbon::parse($timesheet->check_in)->format('H:i:s');
        if((($checkIn >= $config->start_morning_late
                && $checkIn < $config->end_morning)
                || $checkIn > $config->start_afternoon_late)
                && in_array(Carbon::parse($timesheet->check_in)->format('w'), json_decode($config->work_days))) {
            return true;
        }
        return false;
    }
}
