<?php

namespace App\Http\Controllers\Admin;

use App\Exports\AllExport;
use App\Http\Controllers\Controller;
use App\Models\Comments;
use App\Models\NationalDay;
use App\Models\OvertimeUser;
use App\Models\PaidLeave;
use App\Models\Timesheet;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Requests\PaidLeaveRequest;
use App\Http\Requests\UploadTimeSheetRequest;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\TimeSheetImport;
use App\Http\Requests\Cms\TimeSheetRequest;
use App\Exports\TimeSheetExport;
use Illuminate\Support\Facades\Session;
use App\Models\Position;
use App\Models\ReportConfig;

class TimeSheetController extends Controller
{
    protected $timeSheetModel;
    protected $commentModel;
    protected $overtimeUserModel;
    protected $nationalDayModel;
    protected $paidLeaveModel;

    public function __construct(
        Timesheet $timeSheetModel,
        Comments $commentModel,
        OvertimeUser $overtimeUserModel,
        NationalDay $nationalDayModel,
        PaidLeave $paidLeaveModel
    ){
        $this->timeSheetModel = $timeSheetModel;
        $this->commentModel = $commentModel;
        $this->overtimeUserModel = $overtimeUserModel;
        $this->nationalDayModel = $nationalDayModel;
        $this->paidLeaveModel = $paidLeaveModel;
    }

    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
     */
    public function index(Request $request)
    {
        $isAdmin = $this->userCan('admin');
        $input = $request->only(['date', 'user_id']);
        $date = isset($input['date']) ? Carbon::createFromFormat('Y/m/d', $input['date'].'/01')->format('Y-m')
            : Carbon::now()->format('Y-m');
        $start_date = Carbon::parse($date)->format('Y-m-d');
        $end_date = Carbon::parse($date)->addMonth()->format('Y-m-d');
        $userPaginate = $isAdmin ? User::getUsers(2, $request->user_id, false)->paginate(1) : false;
        $user_id = Auth::id();
        if ($isAdmin && isset($input['user_id'])){
            $user_id = $input['user_id'];
        }
        if($userPaginate) {
            $user_id = $userPaginate->first()->id;
        }
        $timeSheetArr = (new Timesheet())->getTimesheetCalendar($start_date, $end_date, $user_id);
        $paidLeave = $this->paidLeaveModel->getOnePaidLeaveWithCondition([
                    'month_year' => Carbon::make($start_date)->format('Y-m'),
                    'user_id' => $user_id
                ]);
        if ($paidLeave){
            $paidLeave->fill([
                'leave_hour_in_work_hour' => isset($timeSheetArr['total']['leaveHourInWorkHour']) ? $timeSheetArr['total']['leaveHourInWorkHour'] : 0,
                'not_use_leave_hour' => isset($timeSheetArr['total']['notUseLeaveHour']) ? $timeSheetArr['total']['notUseLeaveHour'] : 0
            ]);
            $paidLeave->save();
        }

        $totalDate = Carbon::parse($end_date)->diffInDays(Carbon::parse($start_date));
        $start = Carbon::parse($start_date);
        $page_title = __('layouts.timesheet');
        $users = User::getUsers();
        $userSelected = User::getOneUser($user_id);
        $allowPositions = Position::getAllowPositions($user_id);
        $distanceLimit = ReportConfig::getDistanceLimit();
        Session::flash('backUrl', $request->fullUrl());

        return view('timesheet.index', compact('page_title', 'timeSheetArr', 'totalDate', 'start', 'date',
                'paidLeave', 'users', 'userSelected', 'isAdmin', 'userPaginate', 'allowPositions', 'distanceLimit'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
     */
    public function create(Request $request)
    {
        $timesheet = new Timesheet();
        $config = ReportConfig::first();
        $timesheet->user_id =  $request->has('user_id') ? $request->user_id : false;
        $timesheet->check_in = $request->has('date') ? $request->date . ' ' . $config->start : false;
        $timesheet->check_out = $request->has('date') ? $request->date . ' ' . $config->end : false;

        $users = User::getUsers();
        $page_title = __('layouts.add_working_time');
        $this->keepBackUrl();
        return view('timesheet.create', compact('page_title', 'users', 'timesheet', 'config'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(TimeSheetRequest $request)
    {
        $this->keepBackUrl();
        $input = $request->validated();
        DB::beginTransaction();
        try {
            $input['check_in'] = Carbon::parse($input['check_in'])->format('Y-m-d H:i:s');
            $input['check_out'] = $input['check_out']
                    ? Carbon::parse($input['check_out'])->format('Y-m-d H:i:s') : null;
            Timesheet::create($input);
            PaidLeave::createOrUpdate($input['user_id'], Carbon::parse($input['check_in'])->format('Y-m'));
            DB::commit();

            return redirect()->to(Session::get('backUrl') ?? route('timesheet.index'))
                    ->with('success', __('messages.create.success'));
        } catch (\Exception $exception){
            DB::rollBack();
            Log::error('Working time add error: '.$exception->getMessage());

            return redirect()->back()
                    ->with('error', __('messages.create.error'));
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $timesheet
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
     */
    public function edit(Timesheet $timesheet)
    {
        $users = User::getUsers();
        $page_title = __('layouts.update_working_time');
        $this->keepBackUrl();

        return view('timesheet.edit', compact('page_title', 'users', 'timesheet'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $timesheet
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(TimeSheetRequest $request, Timesheet $timesheet)
    {
        $this->keepBackUrl();
        $input = $request->validated();
        DB::beginTransaction();
        try {
            $input['check_in'] = Carbon::parse($input['check_in'])->format('Y-m-d H:i:s');
            $input['check_out'] = $input['check_out']
                    ? Carbon::parse($input['check_out'])->format('Y-m-d H:i:s') : null;
            $timesheet->update($input);
            PaidLeave::createOrUpdate($input['user_id'], Carbon::parse($input['check_in'])->format('Y-m'));
            DB::commit();

            return redirect()->to(Session::get('backUrl') ?? route('timesheet.index'))
                    ->with('success', __('messages.edit.success'));
        } catch (\Exception $exception){
            DB::rollBack();
            Log::error('Working time update error: '.$exception->getMessage());

            return redirect()->back()
                    ->with('error', __('messages.edit.error'));
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $timesheet
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Timesheet $timesheet)
    {
        $userId = $timesheet->user_id;
        $checkIn = $timesheet->check_in;

        try {
            $timesheet->delete();
            PaidLeave::createOrUpdate($userId, Carbon::parse($checkIn)->format('Y-m'));

            return redirect()->to(Session::get('backUrl') ?? route('timesheet.index'))
                            ->with('success', __('messages.delete.success'));
        } catch (\Exception $exception){
            return redirect()->back()
                    ->with('error', __('messages.delete.error'));
        }
    }

    public function updatePaidLeave(PaidLeaveRequest $request, $id)
    {
        $this->keepBackUrl();
        $paidLeave = PaidLeave::Where('id' , '=', $id)->first();
        if(!$paidLeave) {
            return redirect()->back()
                    ->with('error', __('messages.update_paid_leave_fail'));
        }
        $input = $request->validated();
        DB::beginTransaction();
        try {
            $paidLeave->update($input);
            DB::commit();
            PaidLeave::updateFollowingMonths($paidLeave->user_id, $paidLeave->month_year);

            return redirect()->back()
                    ->with('success', __('messages.edit.success'));
        } catch (\Exception $exception) {
            DB::rollBack();
            Log::error('Update Paid Leave: '.$exception->getMessage());

            return redirect()->back()
                    ->with('error', __('messages.edit.error'));
        }
    }

    public function uploadTimesheet(UploadTimeSheetRequest $request)
    {
        $this->keepBackUrl();
        $fieldName = 'file-timesheet';
        if ($request->hasFile($fieldName)) {
            $file = $request->file($fieldName);
            $pathTemp = $file->store('temp');
            $path=storage_path('app').'/'.$pathTemp;
            try {
                Excel::import(new TimeSheetImport($request->type), $path);

                return redirect()->back()
                        ->with('success', __('messages.import.success'));
            } catch (\Exception $exception) {
                Log::error('Upload timesheet error: '.$exception->getMessage());

                return redirect()->back()
                        ->with('error',  __('messages.import.error'));
            }
        }
    }

    public function exportTimesheet(Request $request)
    {
        $this->keepBackUrl();
        $userId = $this->userCan('admin') ? $request->user_id : Auth::id();
        $input = $request->only(['date']);
        $date = isset($input['date']) ? Carbon::createFromFormat('Y/m', $input['date'])->format('Y-m')
            : Carbon::now()->format('Y-m');
        $startDate = Carbon::parse($date)->format('Y-m-d');
        $endDate = Carbon::parse($date)->addMonth()->format('Y-m-d');

        $date = date("Ymd_his");
        $dataInDay = $request->dataInDay;
        if ($userId != null) {
            $user = User::getUsers(2, $userId);
            $userName = $user[0]->first_name . ' ' . $user[0]->last_name;
            return Excel::download(new AllExport($startDate, $endDate, $userId, $dataInDay), __('layouts.prefix_timesheet') . $userName . '_' . $date . '.xlsx');
        } else {
            return Excel::download(new AllExport($startDate, $endDate, $userId, $dataInDay), 'WorkTrack_'. __('layouts.prefix_timesheet') . $date . '.xlsx');
        }
    }

    public function updateMonthAll(Request $request)
    {
        $this->keepBackUrl();
        $input = $request->all();
        $date = $input['date-search'] ?? '';
        $start_date = isset(explode(' ', $date)[0]) && !empty(explode(' ', $date)[0]) ?
            explode(' ', $date)[0] : Carbon::make('first day of this month')->format('Y-m-d');

        try {
            PaidLeave::createOrUpdateAllUserByMonth(Carbon::make($start_date)->format('Y-m'));

            return redirect()->back()
                    ->with('success', __('messages.import.success'));
        } catch (\Exception $exception) {
            Log::error('Upload timesheet error: '.$exception->getMessage());

            return redirect()->back()
                    ->with('error', __('messages.import.error'));
        }
    }
}
