<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class NationalDay extends Model
{
    protected $table = 'national_day';

    protected $fillable = [
        'name', 'from_date', 'to_date'
    ];

    /**
     * Get list national day
     * @param array $condition
     * @param null $perPage
     * @return \Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection
     */
    public static function getListNationalDay($condition = [], $perPage = null)
    {
        $query = NationalDay::query();
        if (isset($condition['name'])){
            $query->where('name', 'like', '%'.$condition['name'].'%');
        }
        if (isset($condition['start_date']) && !empty($condition['start_date'])){
            $query->where('from_date', '>=', $condition['start_date']);
        }
        if (isset($condition['end_date']) && !empty($condition['end_date'])){
            $query->where('to_date', '<=', $condition['end_date']);
        }
        if (isset($condition['month']) && !empty($condition['month'])) {
            $query->where(function ($query) use ($condition) {
                $query->whereMonth('from_date', '=', $condition['month'])
                      ->orWhereMonth('to_date', '=', $condition['month']);
            });
        }
        if (isset($condition['year']) && !empty($condition['year'])) {
            $query->where(function ($query) use ($condition) {
                $query->whereYear('from_date', '=', $condition['year'])
                      ->orwhereYear('to_date', '=', $condition['year']);
            });
        }
        $query->orderBy('from_date', 'desc');
        if (!is_null($perPage)){
            return $query->paginate($perPage);
        }

        return $query->get();
    }

    /**
     * Check holiday
     * @param $day
     * @param $holiday
     */
    public static function holiday($day, $holiday)
    {
        $check = $holiday->where('from_date', '<=', Carbon::make($day)->format('Y-m-d'))
                ->where('to_date', '>=', Carbon::make($day)->format('Y-m-d'));
        return !$check->isEmpty();
    }

    /**
     * Check holiday
     * @param $day
     * @param $holiday
     */
    public static function countHolidayByMonth($month, $year)
    {
        $condition = [
            'month' => $month,
            'year' => $year
        ];
        $holidays = (new NationalDay())->getListNationalDay($condition);

        $total = 0;
        foreach ($holidays as $holiday) {
            $fromDate = Carbon::parse($holiday->from_date);
            $toDate = Carbon::parse($holiday->to_date);
            if($fromDate <= $toDate) {
                $total ++ ;
            }

            $total += $fromDate->diffInDays($toDate);
        }

        return $total;
    }

}
