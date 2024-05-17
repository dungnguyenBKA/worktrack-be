<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Overtime extends Model
{
    protected $table = 'overtime';

    const STATUS_PENDING = 1;
    const STATUS_APPROVE = 2;
    const STATUS_REJECT = 3;

    protected $fillable = [
        'project',
        'from_time',
        'to_time',
        'reason',
        'status',
        'reason2',
        'created_by',
    ];

    public function overtimeUsers()
    {
        return $this->hasMany(OvertimeUser::class, 'overtime_id', 'id');
    }

    public function scopeMyRequest($query, $userId, $id = '')
    {
       $query->select('overtime.*');
       $query->join('overtime_user', 'overtime.id', '=', 'overtime_user.overtime_id');
        if (!empty($id))
            $query->where('overtime.id', $id);

       $query->where(function ($query) use ($userId) {
           $query->orWhere('overtime.created_by', $userId);
           $query->orWhere('overtime_user.user_id', $userId);
       });
        return $query->groupBy('overtime.id');
    }
}
