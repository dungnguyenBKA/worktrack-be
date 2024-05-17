<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FaceTimesheetLog extends Model
{
    protected $table = 'face_timesheet_logs';
    
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';

    protected $fillable = [
        'user_id',
        'check_time',
    ];
}