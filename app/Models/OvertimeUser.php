<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OvertimeUser extends Model
{
    protected $table = 'overtime_user';

    protected $fillable = [
        'overtime_id',
        'user_id',
    ];

    public function overtime()
    {
        return $this->belongsTo(Overtime::class, 'overtime_id', 'id');
    }

    public function getListOvertimeUser($condition = [])
    {
        $query = OvertimeUser::query()->with('overtime');
        if (isset($condition['user_id']) && !empty($condition['user_id'])){
            $query->where('overtime_user.user_id', '=', $condition['user_id']);
        }
        if (isset($condition['status']) && !empty($condition['status'])){
            $query->join('overtime', 'overtime_user.overtime_id', '=', 'overtime.id');
            $query->where('overtime.status', '=', $condition['status']);
        }

        return $query->get();
    }
}
