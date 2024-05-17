<?php

namespace App\Models;


use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Support\Facades\DB;
use App\Notifications\ResetPasswordNotification;
use App\Http\Remotes\CrmRemote;

class User extends Authenticatable implements JWTSubject
{
    use HasFactory, Notifiable, SoftDeletes;

    const STATUSC_WORKING = 2;
    const STATUSC_RESIGN = 1;

    const TYPE_OFFICE = 1;
    const TYPE_HOME = 2;
    public $IS_UPDATE_MASTER = true;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'users';

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
        'phone_number',
        'birth',
        'address',
        'position_id',
        'role',
        'status',
        'date_start_work',
        'paid_leave_start_date',
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

    public function workTitle()
    {
        return $this->belongsTo(WorkTitle::class, 'position_id', 'id');
    }

    public function roles()
    {
        return $this->belongsTo(Role::class, 'role', 'id');
    }

    public static function boot()
    {
        parent::boot();

        self::created(function($model){
            // Add user master to CRM
            try {
                if(hasSubdomain()) {
                    $crmRemote = new CrmRemote();
                    $crmRemote->createOrUpdateUserMaster($model->id, $model->email, $model->password);
                }
            } catch (\Exception $e) {
                logger()->error('CRM Remote createUserMaster: ' . $e->getMessage());
            }
        });

        self::updated(function($model){
            // Update user master to CRM
            try {
                if(hasSubdomain()) {
                    if($model->IS_UPDATE_MASTER) {
                        $crmRemote = new CrmRemote();
                        $crmRemote->createOrUpdateUserMaster($model->id, $model->email, $model->password);
                    }
                }
            } catch (\Exception $e) {
                logger()->error('CRM Remote updateUserMaster: ' . $e->getMessage());
            }
        });

        self::deleted(function($model){
            // Update user master to CRM
            try {
                if(hasSubdomain()) {
                    $crmRemote = new CrmRemote();
                    $crmRemote->deleteMaster($model->id);
                }
            } catch (\Exception $e) {
                logger()->error('CRM Remote deleteMaster: ' . $e->getMessage());
            }
        });
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

    public function userApps()
    {
        return $this->hasMany(UserApp::class, 'user_id', 'id');
    }
    /**
     * get list user. Default user working
     * @param type $status
     * @return array
     */
    public static function getUsers($status = 2, $id = false, $returnCollection = true) {
        $query = User::select('id', 'staff_id', 'first_name', 'last_name', 'email', 'phone_number', 'role', 'status',
                'timesheet_machine_id');
        $query->where('status', '=', $status);
        if($id) {
            $query->where('id', '=', $id);
        }
        $query->orderBy('staff_id', 'ASC');

        if($returnCollection)
            return $query->get();

        return $query;
    }

    /**
     * get one user. Default user working
     * @param type $status
     * @return array
     */
    public static function getOneUser($user_id, $status = 2)
    {
        $query = User::select('id', 'staff_id', 'first_name', 'last_name', 'email', 'phone_number', 'role', 'status');
        $query->where('id', '=', $user_id);
        $query->where('status', '=', $status);

        return $query->first();
    }

    /**
     * get one user. Default user working
     * @param type $timesheetMachineID
     * @return array
     */
    public static function getByTimesheetMachineID($timesheetMachineID)
    {
        $query = User::select('id', 'staff_id', 'first_name', 'last_name', 'email', 'phone_number', 'role', 'status');
        $query->where('timesheet_machine_id', '=', $timesheetMachineID);
        $query->where('status', '=', 2);

        return $query->first();
    }

    public function getFullName()
    {
        return $this->first_name . ' ' . $this->last_name;
    }

    public static function getBirthdayByMonth($month = false)
    {
        $users = User::query()
                ->where('status', '=', 2)
                ->where(DB::raw('MONTH(birth)'), '=', $month ?? MONTH(NOW()))
                ->orderBy(DB::raw('DAY(birth)'), 'ASC')
                ->orderBy(DB::raw('MONTH(birth)'), 'ASC')
                ->orderBy(DB::raw('YEAR(birth)'), 'ASC')
                ->get();

        return $users;
    }

    public static function getUserNewByMonth($month = false, $year = false){
        $users = User::query()
            ->where('status', '=', 2)
            ->where(DB::raw('MONTH(date_start_work)'), '=', $month ?? MONTH(NOW()))
            ->where(DB::raw('YEAR(date_start_work)'), '=', $year ?? YEAR(NOW()))
            ->orderBy('date_start_work', 'ASC')
            ->get();

        return $users;
    }

    public function sendPasswordResetNotification($token)
    {
        $this->notify(new ResetPasswordNotification($token));
    }
}
