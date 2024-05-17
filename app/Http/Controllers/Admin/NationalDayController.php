<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\NationalDayRequest;
use App\Models\NationalDay;
use App\Models\PaidLeave;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Yajra\DataTables\DataTables;

class NationalDayController extends Controller
{
    protected $nationalDayModel;
    public function __construct(NationalDay $nationalDay)
    {
        $this->nationalDayModel = $nationalDay;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
     */
    public function index(Request $request)
    {
        $params = $request->only('name');
        $page_title = __('layouts.national_holiday');
        $nationalDays = $this->nationalDayModel->getListNationalDay($params, 10);
        return view('national_day.index', compact('page_title', 'nationalDays'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
     */
    public function create()
    {
        $page_title = __('layouts.create_national_holiday');
        return view('national_day.create', compact('page_title'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(NationalDayRequest $request)
    {
        $input = $request->only(['name', 'from_date', 'to_date']);
        try {
            $nationalDay = $this->nationalDayModel->fill($input);
            $nationalDay->save();
            $this->updatePaidLeaveAllUser($input['from_date'], $input['to_date']);
            return redirect()->route('national-day.index')
                ->with([
                    'success' => true,
                    'message' => __('messages.create.success')
                ]);
        } catch (\Exception $exception){
            Log::error('Create national day: '. $exception->getMessage());
            return redirect()->route('national-day.index')
                ->with([
                    'success' => false,
                    'message' => __('messages.create.error')
                ]);
        }

    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param NationalDay $nationalDay
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
     */
    public function edit(NationalDay $nationalDay)
    {
        $page_title = __('layouts.edit_national_holiday');
        return view('national_day.edit', compact('page_title', 'nationalDay'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param NationalDayRequest $request
     * @param NationalDay $nationalDay
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(NationalDayRequest $request, NationalDay $nationalDay)
    {
        $input = $request->only(['name', 'from_date', 'to_date']);
        try {
            $nationalDay->fill($input);
            $nationalDay->save();
            $this->updatePaidLeaveAllUser($input['from_date'], $input['to_date']);
            return redirect()->route('national-day.index')->with([
                'success' => true,
                'message' => __('messages.edit.success')
            ]);
        }catch (\Exception $exception){
            Log::error('Edit national day: '. $exception->getMessage());
            return redirect()->route('national-day.index')->with([
                'success' => false,
                'message' => __('messages.edit.error')
            ]);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(NationalDay $nationalDay)
    {
        try {
            $from = $nationalDay->from_date;
            $to = $nationalDay->to_date;
            $nationalDay->delete();
            $this->updatePaidLeaveAllUser($from, $to);
            return redirect()->route('national-day.index')->with([
                'success' => true,
                'message' => __('messages.delete.success')
            ]);
        } catch (\Exception $exception){
            Log::error('Delete national day: '. $exception->getMessage());
            return redirect()->route('national-day.index')->with([
                'success' => false,
                'message' => __('messages.delete.error')
            ]);
        }

    }

    private function updatePaidLeaveAllUser($from, $to)
    {
        PaidLeave::createOrUpdateAllUserByMonth(Carbon::make($from)->format('Y-m'));
        if (Carbon::make($from)->format('Y-m') != Carbon::make($to)->format('Y-m')){
            PaidLeave::createOrUpdateAllUserByMonth(Carbon::make($to)->format('Y-m'));
        }

        return 1;
    }
}
