<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Comments extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'comments';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id', 'date', 'comment'
    ];

    public function getListComment($condition = [])
    {
        $query = Comments::query();
        if (isset($condition['user_id']) && !empty($condition['user_id'])){
            $query->where('user_id', '=', $condition['user_id']);
        }
        if (isset($condition['start_date']) && !empty($condition['start_date'])){
            $query->where('date', '>=', $condition['start_date']);
        }
        if (isset($condition['end_date']) && !empty($condition['end_date'])){
            $query->where('date', '<', $condition['end_date']);
        }

        return $query->get();
    }
}
