<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use App\Models\Position;
use Illuminate\Support\Carbon;
use App\Models\ReportConfig;

class PositionLimit implements Rule
{

    public function __construct($userId)
    {
        $this->userId = $userId;
    }

    public function passes($attribute, $value)
    {
        $config = ReportConfig::query()->first();
        $postionCount = Position::query()->where('user_id', '=', $this->userId)
                ->where('status', 1)->count();
        if ($config && $config->position_limit > $postionCount) {
            return true;
        }

        return false;
    }

    public function message()
    {
        return __('messages.position_exceed_limit');
    }
}