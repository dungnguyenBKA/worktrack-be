<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TimesheetLog extends Model
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
    protected $table = 'timesheet_log';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id', 'date_time', 'gps', 'location_id'
    ];

    public function getListTimeSheetLog($condition = [])
    {
        $query = TimesheetLog::query();
        $query->selectRaw('*, timesheet_log.user_id as user_id, timesheet_log.id as id');
        if (isset($condition['user_id']) && !empty($condition['user_id'])) {
            $query->where('timesheet_log.user_id', '=', $condition['user_id']);
        }
        if (isset($condition['start_date']) && !empty($condition['start_date'])) {
            $query->where('date_time', '>=', $condition['start_date']);
        }
        if (isset($condition['end_date']) && !empty($condition['end_date'])) {
            $query->where('date_time', '<', $condition['end_date']);
        }
        $query->leftJoin('positions', 'positions.id', '=', 'timesheet_log.location_id');
        $query->orderBy('timesheet_log.user_id', 'ASC');
        $query->orderBy('date_time', 'ASC');

        return $query->get();
    }
}
