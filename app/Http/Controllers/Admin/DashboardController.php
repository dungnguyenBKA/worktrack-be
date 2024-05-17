<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Timesheet;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use App\Models\User;
use App\Models\RequestAbsent;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $page_title  = __('layouts.dashboard');
        
        $isAdmin = $this->userCan('admin');
        $input = $request->only(['date']);
        $date = isset($input['date']) ? Carbon::createFromFormat('Y/m', $input['date'])->format('Y-m')
            : Carbon::now()->format('Y-m');
        $startDate = Carbon::parse($date)->format('Y-m-d');
        $endDate = Carbon::parse($date)->addMonth()->format('Y-m-d');
        //$startMonth = Carbon::make('first day of this month')->format('m');
        $startMonth = Carbon::parse($date)->firstOfMonth()->format('m');
        //$startYear = Carbon::make('first day of january this year')->format('Y');
        $startYear = Carbon::parse($date)->firstOfYear()->format('Y');
        $timesheets = (new Timesheet)->getTimesStaffLate($startDate, $endDate);
        $usersBirthday = User::getBirthdayByMonth($startMonth);
        $requestAbsents = RequestAbsent::getRequestAbsentByMonth($startDate, $endDate);
        $newUsers = User::getUserNewByMonth($startMonth, $startYear);
        $endDate = Carbon::make($endDate)->subDay();

        return view('admin.dashboard', compact('page_title', 'timesheets', 'usersBirthday', 'date',
            'startMonth', 'startDate', 'endDate', 'requestAbsents', 'newUsers', 'isAdmin'));
    }
}
