<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NotificationUser extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'notification_user';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'notification_id', 'user_id'
    ];

    public function getList($condition = [])
    {
        return $this->getNotificationUserWithCondition($condition)->get();
    }

    protected function getNotificationUserWithCondition($condition = [])
    {
        $query = NotificationUser::query();
        $query->where($condition);

        return $query;
    }
}
