<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Facades\DB;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Database\Eloquent\SoftDeletes;

class Staff extends Authenticatable implements JWTSubject
{
    use HasFactory, SoftDeletes;

    const CREATED_AT = 'create_date';
    const UPDATED_AT = 'last_update';

    protected $dateFormat = 'U';

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'users';
    protected $casts = ['id' => 'string'];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'staff_id',
        'timesheet_machine_id',
        'first_name',
        'last_name',
        'email',
        'birth',
        'address',
        'position_id',
        'role',
        'status',
        'date_start_work',
        'password',
        'face_id',
        'created_by',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'status',
        'remember_token',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    public function timesheets()
    {
        return $this->hasMany(Timesheet::class, 'user_id', 'id');
    }

    public function scopeLateIn($query, $startDate, $endDate)
    {
        if (empty($startDate) || empty($endDate)) return $query->where('id', '<', 0);
        $nationalDays = NationalDay::getListNationalDay(['start_date'=>$startDate, 'end_date'=>$endDate]);
        $config = ReportConfig::first();
        return $query->with(['timesheets' => function ($q) use ($startDate, $endDate, $nationalDays, $config) {
            $q->where(function ($q) use ($startDate, $endDate, $config){
                $q->where(function ($q) use ($startDate, $endDate, $config){
                    $q->whereTime('check_in', '>=', $config->start_morning_late);
                    $q->whereTime('check_in', '<', $config->end_morning);
                });
                $q->orWhereTime('check_in', '>=', $config->start_afternoon_late);
            });
            $q->where(function ($q) use ($startDate, $endDate){
                $q->where(function ($q) use ($startDate, $endDate){
                    $q->where('check_in', '>=', $startDate);
                    $q->where('check_in', '<=', $endDate);
                    $q->where(function ($q) use ($startDate, $endDate) {
                        $q->where(function ($q) use ($startDate, $endDate) {
                            $q->where('check_out', '>=', $startDate);
                            $q->where('check_out', '<=', $endDate);
                        });
                        $q->orWhereNull('check_out');
                    });
                });
                $q->orWhere(function ($q) use ($startDate, $endDate){
                    $q->where('check_out', '>=', $startDate);
                    $q->where('check_out', '<=', $endDate);
                    $q->where(function ($q) use ($startDate, $endDate) {
                        $q->where(function ($q) use ($startDate, $endDate) {
                            $q->where('check_in', '>=', $startDate);
                            $q->where('check_in', '<=', $endDate);
                        });
                        $q->orWhereNull('check_in');
                    });
                });
            })->where([
                [DB::raw('DAYOFWEEK(check_in)'), '<>', 1],
                [DB::raw('DAYOFWEEK(check_in)'), '<>', 7]
            ])->where(function ($query) use ($nationalDays) {
                foreach ($nationalDays as $nationalDay){
                    $query->where(function ($query) use ($nationalDay){
                        $query->orWhere('check_in', '<', $nationalDay->from_date);
                        $query->orWhere('check_in', '>', $nationalDay->to_date.' 23:59:59');
                    });
                }
            })->orderBy('check_in');

        }]);
    }
    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [];
    }

    public static function getStaffsLateIn($startDate, $endDate, $addTimesheet = true, $overTimes = 1)
    {
        $staffs = Staff::select('id', 'first_name', 'last_name', 'email', 'staff_id')
            ->lateIn($startDate, $endDate)->get();

        $retData = [];
        foreach ($staffs as $staff) {
            $lateTimes = count($staff->timesheets);

            if ($lateTimes < $overTimes) continue;
            if (!$addTimesheet) unset($staff['timesheets']);

            $staff['late_times'] = $lateTimes;
            $retData[] = $staff;
        }
        $lateColumn = array_column($retData, 'late_times');
        array_multisort($lateColumn, SORT_DESC, $retData);

        return $retData;
    }
}
