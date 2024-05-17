<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notifications extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'notifications';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'title', 'content', 'start_time', 'image', 'status'
    ];

    public function getList($condition = [])
    {
        return $this->getNotificationWithCondition($condition)->get();
    }

    protected function getNotificationWithCondition($condition = [])
    {
        $query = Notifications::query();
        $query->where('status', '=', 1);

        return $query;
    }
}
