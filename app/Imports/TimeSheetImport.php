<?php

namespace App\Imports;

use App\Models\Timesheet;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Support\Carbon;
use App\Models\TimesheetLog;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use Illuminate\Support\Facades\Log;
use App\Models\PaidLeave;

class TimeSheetImport implements ToCollection
{
    public function __construct($type) {
        $this->type = $type;
    }
    /**
     * Import time sheet check in/out from excel
     * @param Collection $rows
     */
    public function collection(Collection $rows)
    {
        $users = User::getUsers();
        $sortRows = $rows->sortBy(1);
        $groupRows = $sortRows->groupBy(function ($item) {
            return $item["0"] . "----" . substr($item['2'], 0, 5);
        });
        $this->createTimeSheet($users, $groupRows, $this->type);
        $this->createTimeSheetLog($users, $rows);
    }
    
    /**
     * Import time sheet log from excel
     * @param type $rows
     */
    private function createTimeSheetLog($users, $rows)
    {
        try {
            foreach ($rows as $row) {
                $user = isset($row[0]) ? $users->where('timesheet_machine_id', $row[0])->first() : false;
                if ($user) {
                    $dateTime = isset($row[1])
                                    ? Carbon::instance(Date::excelToDateTimeObject($row[1])) : null;
                    TimesheetLog::create([
                        'user_id' => $user->id,
                        'date_time' => $dateTime
                    ]);
                }
            }
        } catch (\Exception $exception) {
            Log::error('Impport TimeSheet Log Error: '.$exception->getMessage());
        }
    }
    
    /**
     * Import time sheet from excel
     * @param type $users
     * @param type $rows
     */
    private function createTimeSheet($users, $rows, $override = true)
    {
        try {
            foreach ($rows as $key => $row) {
                $timesheet = explode('----', $key);
                $user = isset($timesheet[0]) && !empty($timesheet[0]) 
                    ? $users->where('staff_id', $timesheet[0])->first() : false;
                if ($user) {
                    $rowSort = $row->sortBy(2);
                    $checkIn = $rowSort->first();
                    $checkOut = $rowSort->count() > 1 ? $rowSort->last() : false;
                    $date = $checkIn && isset($checkIn[2])
                            ? Carbon::instance(Date::excelToDateTimeObject($checkIn[2]))->format('Y-m-d') : false;
                    $monthYear = $checkIn && isset($checkIn[2])
                            ? Carbon::instance(Date::excelToDateTimeObject($checkIn[2]))->format('Y-m') : false;
                    $timeSheet = Timesheet::getByDate($user->id, $date);
                    $checkInFinal = $checkIn && isset($checkIn[2])
                                ? Carbon::instance(Date::excelToDateTimeObject($checkIn[2]))->format('Y-m-d H:i:s') : null;
                    $checkOutFinal = $checkOut && isset($checkOut[2])
                                ? Carbon::instance(Date::excelToDateTimeObject($checkOut[2]))->format('Y-m-d H:i:s') : null;
                    
                    if($override && $timeSheet && $checkInFinal > $timeSheet->check_in && !empty($timeSheet->check_in)) {
                        $checkInFinal =  $timeSheet->check_in;
                    }
                    
                    if($override && $timeSheet && $checkOutFinal < $timeSheet->check_out && !empty($timeSheet->check_out)) {
                        $checkOutFinal =  $timeSheet->check_out;
                    }
                    
                    $timeSheetData = [
                            'user_id' => $user->id,
                            'check_in' => $checkInFinal,
                            'check_out' => $checkOutFinal,
                            'updated_by' => Auth::id()
                        ];
                    if (!$timeSheet) {
                        Timesheet::insert($timeSheetData);
                    } elseif($override) {
                        $timeSheet->update($timeSheetData);
                    }
                    PaidLeave::createOrUpdate($user->id, $monthYear);
                }
            }
            
        } catch (\Exception $exception) {
            Log::error('Impport TimeSheet Error: '.$exception->getMessage());
        }
    }
}
