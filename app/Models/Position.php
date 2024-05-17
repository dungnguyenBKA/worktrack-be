<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Position extends Model
{
    use HasFactory, SoftDeletes;
    
    protected $table='positions';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id', 'position', 'latitude', 'longitude', 'type', 'status', 'created_by'
    ];
    
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
    
    public function createBy()
    {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }
    
    /**
     * Get list
     *
     * @param array $condition
     * @param array $select
     * @return array || null
     */
    public static function getAll(array $condition = [], array $select = [], $paginate = true)
    {
        $query = Position::query();
        if($select) {
            $query->select($select);
        }
        $query->leftJoin('users', 'positions.user_id', '=', 'users.id');
        $query->where(function ($query) {
           $query->where('users.status', '=', User::STATUSC_WORKING)
                ->orWhere('type', '=', User::TYPE_OFFICE);
        });

        $query->orderBy('type', 'asc');
        $query->orderBy('staff_id', 'asc');
        $query->orderBy('positions.created_by', 'asc');

        if (!empty($condition['position'])) {
            $query->where('position', 'LIKE', '%' . $condition["position"] . '%');
        }
        if (!empty($condition['user_id'])) {
            $query->where('user_id', $condition['user_id']);
        }
        if (!empty($condition['status'])) {
            $query->where('positions.status', $condition['status']);
        }
        if (!empty($condition['type'])) {
            $query->where('type', $condition['type']);
        }
        if($paginate) {
            $resdult = $query->paginate(10);
        } else {
            $resdult = $query->get();
        }

        return $resdult;
    }
    
    /**
     * getByUser
     *
     * @param  int $userId
     * @return void
     */
    public static function getByUser($userId)
    {
        return Position::getAll(['user_id' => $userId]);
    }

    public static function getAllowPositions ($userId, $returnConlection = false)
    {
        $query = Position::query();
        $query->leftJoin('users', 'positions.user_id', '=', 'users.id');
        $query->where(function ($query) use ($userId) {
            $query->where('user_id', '=', $userId);
            $query->orWhere('type', '=', User::TYPE_OFFICE);
        });
        $query->where('positions.status', '=', 1);
        $workLocations = $query->get('positions.*');
        if($returnConlection) {
            return $workLocations;
        }
        $positions = [];
        foreach($workLocations as $item) {
            $positions[] = ['coord' => [
                                'lat' => $item->latitude,
                                'lon' => $item->longitude
                            ],
                            'address' => $item->position
                        ];
        }
        return json_encode($positions);
    }
    
}
