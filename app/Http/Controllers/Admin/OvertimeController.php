<?php

namespace App\Http\Controllers\Admin;

use App\Models\User;
use App\Models\Overtime;
use App\Models\OvertimeUser;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Http\Requests\Cms\StoreOvertimeRequest;
use App\Http\Requests\Cms\UpdateOvertimeRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use App\Exports\OvertimeExport;
use Maatwebsite\Excel\Facades\Excel;

class OvertimeController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(Overtime::class);
    }

    public function index(Request $request)
    {
        $page_title = __('layouts.overtime_request');
        $user = $request->user();
        $isSearch = isset($request->isSearch);
        try {
            $date = isset($request->date) ? Carbon::createFromFormat('Y/m', $request->date)->format('Y-m') : ( $isSearch ? null : Carbon::now()->format('Y-m') );
        } catch (\Throwable $th) {
            $date = Carbon::now()->format('Y-m');
        }
        $userIdSearch = $request->user_id;
        $isAdmin = $this->userCan('admin');
        $users = [];

        $query = Overtime::select('overtime.*', 'users.staff_id', 'users.first_name', 'users.last_name', 'users.email')
            ->join('users', 'overtime.created_by', '=', 'users.id')
            ->join('overtime_user', 'overtime.id', '=', 'overtime_user.overtime_id')
            ->whereNull('users.deleted_at')
            ->with('overtimeUsers', function ($q) {
                $q->join('users', 'users.id', '=', 'overtime_user.user_id')
                    ->whereNull('users.deleted_at')
                    ->select('overtime_user.*', 'users.staff_id', 'users.first_name', 'users.last_name', 'users.email');
            });

        if($date) {
            $startDate = Carbon::parse($date)->format('Y-m-d');
            $endDate = Carbon::parse($date)->addMonth()->format('Y-m-d');
            $query->where('from_time', '>=', $startDate ?? DATE(NOW()))
                    ->where('from_time', '<', $endDate ?? DATE(NOW()));
        }

        if ($user && $user->role == config('common.user.role.user') || isset($userIdSearch)) {
            $query->where(function ($query) use ($user, $userIdSearch) {
                $query->orWhere('overtime.created_by', isset($userIdSearch) ? $userIdSearch : $user->id);
                $query->orWhere('overtime_user.user_id', isset($userIdSearch) ? $userIdSearch : $user->id);
            });

        }

        $query->groupBy('overtime.id');
        $overtimes  = $query->orderBy('from_time', 'desc')->orderBy('created_at', 'desc')->paginate(30);
        if($isAdmin) {
            $userIds = $date ? $this->getUserIdsOvertime($date, $startDate, $endDate) : $this->getUserIdsOvertime($date);
            $users = User::whereIn('id', $userIds)->get();
        }
        Session::flash('backUrl', $request->fullUrl());

        return view('admin.overtime.index', compact('page_title', 'overtimes', 'users', 'date', 'request'));
    }

    function getUserIdsOvertime($date, $startDate = null, $endDate = null) {
        $userIds = [];
        if($date) {
            $queryWithMonth = Overtime::where('from_time', '>=', $startDate ?? DATE(NOW()))
                        ->where('from_time', '<', $endDate ?? DATE(NOW()))
                        ->groupBy('created_by')->get();
            foreach ($queryWithMonth as $key => $value) {
                $userIds[] = $value->created_by;
            }
        } else {
            $overtimes = Overtime::all()->groupBy('created_by')->toArray();
            if(count($overtimes) > 0) {
                foreach ($overtimes as $key => $value) {
                    $userIds[] = $key;
                }
            }
        }
        return $userIds;
    }

    public function create()
    {
        $page_title = __('layouts.create_request_overtime') ;
        $members = User::getUsers();
        $this->keepBackUrl();
        return view('admin.overtime.create', compact('page_title', 'members'));
    }

    public function store(StoreOvertimeRequest $request)
    {
        $overtimeData = $request->transferredData();
        $url = Session::has('backUrl') ? Session::get('backUrl') : route('overtimes.index');
        DB::beginTransaction();
        try {
            $success = true;
            $message = __('messages.create.success');
            $overtime = Overtime::create($overtimeData);
            if (key_exists('members', $overtimeData)) {
                $overtimeUserData = [];
                foreach ($overtimeData['members'] as $m) {
                    $overtimeUserData[] = ['overtime_id' => $overtime->id, 'user_id' => $m];
                }
                OvertimeUser::insert($overtimeUserData);
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            $success = false;
            $message = __('messages.create.error');
            logger()->error('Admin overtime update: ' . $e->getMessage());
        }
        return redirect()->to($url)->with(['success' => $success, 'message' => $message]);
    }

    public function approve(Overtime $overtime)
    {
        $this->keepBackUrl();
        if (!$this->userCan('admin')) {
            abort('403', __('messages.permission_access_denied'));
        }
        $page_title = __('layouts.approve_overtime_request');
        $overtime = Overtime::where('overtime.id', $overtime->id)
            ->select('overtime.*', 'users.staff_id', 'users.first_name', 'users.last_name', 'users.email')
            ->join('users', 'overtime.created_by', '=', 'users.id')
            ->whereNull('users.deleted_at')
            ->with('overtimeUsers', function ($q) {
                $q->join('users', 'users.id', '=', 'overtime_user.user_id')
                    ->whereNull('users.deleted_at')
                    ->select('overtime_user.*', 'users.staff_id', 'users.first_name', 'users.last_name', 'users.email');
            })
            ->first();
        return view('admin.overtime.show', compact('page_title', 'overtime'));
    }

    public function approveOrRejectAll(Request $request) {
        $this->keepBackUrl();
        try {
            if (!$this->userCan('admin')) {
                abort('403', __('messages.permission_access_denied'));
            }
            $dataRequest = $request->session()->all();
            $splitUrl = explode('?', $dataRequest['_previous']['url']);
            $isApprove = $request->type == '1';
            $statusUpdate = $isApprove ? config('common.overtime.approve') : config('common.overtime.reject');
            $messageSuccess = $isApprove ? __('messages.approve-all-success') : __('messages.reject-all-success');
            $overtimesPending = Overtime::whereIn('id', $request->ids)->get();
            foreach ($overtimesPending as $key => $overtime) {
                $overtime->status = $statusUpdate;
                $overtime->save();
            }
            Session::flash('message',$messageSuccess);
            Session::flash('success', true);
            return response()->json(['success' => true, 'url' => $splitUrl]);
        } catch (\Exception $e) {
            logger()->error('Approve or reject all error: ' . $e->getMessage());
        }
    }

    public function edit(Overtime $overtime)
    {
        $page_title = __('layouts.edit_overtime_request');
        $members = DB::select('SELECT u.id, staff_id, first_name , last_name , email , overtime_id FROM users u
            left join overtime_user ou on u.id = ou.user_id and overtime_id = ' . $overtime->id.' where u.deleted_at is null');
        $memberIds = [];
        foreach ($members as $member){
            if (!is_null($member->overtime_id)){
                $memberIds[] = $member->id;
            }
        }
        $this->keepBackUrl();
        return view('admin.overtime.edit', compact('page_title', 'overtime', 'members', 'memberIds'));
    }

    public function update(UpdateOvertimeRequest $request, Overtime $overtime)
    {
        $overtimeData = $request->transferredData();
        $url = Session::has('backUrl') ? Session::get('backUrl') : route('overtimes.index');
        DB::beginTransaction();
        try {
            $success = true;
            $message = __('messages.edit.success');
            $overtime->update($overtimeData);
            if (key_exists('members', $overtimeData)) {
                $overtimeUserData = [];
                foreach ($overtimeData['members'] as $m) {
                    $overtimeUserData[] = ['overtime_id' => $overtime->id, 'user_id' => $m];
                }
                $overtime->overtimeUsers()->delete();
                OvertimeUser::insert($overtimeUserData);
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            $success = false;
            $message = __('messages.edit.error');
            logger()->error('Admin overtime update: ' . $e->getMessage());
        }
        return redirect()->to($url)
            ->with(['success' => $success, 'message' => $message]);
    }

    public function destroy(Overtime $overtime)
    {
        $user = User::find($overtime->created_by);
        if (!$this->userCan('self', $user) && !$this->userCan('admin')) {
            abort('403', __('messages.permission_access_denied'));
        }
        $success = true;
        $message = __('messages.delete.success');
        $url = Session::has('backUrl') ? Session::get('backUrl') : route('overtimes.index');
        DB::beginTransaction();
        try {
            $overtime->overtimeUsers()->delete();
            $overtime->delete();
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            logger()->error('Delete overtime request: ' . $e->getMessage());
            $success = false;
            $message = __('messages.delete.error');
        }
        return redirect()->to($url)
            ->with(['success' => $success, 'message' => $message]);
    }

    function exportOvertime(Request $request) {
        $this->keepBackUrl();
        try {
            $userId = $this->userCan('admin') ? $request->user_id : Auth::id();
            $date = isset($request->date) ? Carbon::createFromFormat('Y/m', $request->date)->format('Y-m') : null;
            if($date) {
                $startDate = Carbon::parse($date)->format('Y-m-d');
                $endDate = Carbon::parse($date)->addMonth()->format('Y-m-d');
                $requestExport = new OvertimeExport($userId, $startDate, $endDate);
            } else {
                $requestExport = new OvertimeExport($userId);
            }
            $dateExport = $date ? $date : Carbon::now()->format('Y-m');
            $dateExport = date("Ymd_his");
            if ($userId != null) {
                $user = User::getUsers(2, $userId);
                $userName = $user[0]->first_name . ' ' . $user[0]->last_name;
                return Excel::download($requestExport, __('layouts.prefix_overtime') . $userName . '_' . $dateExport . '.xlsx');
            } else {
                return Excel::download($requestExport, 'WorkTrack_' . __('layouts.prefix_overtime') . $dateExport . '.xlsx');
            }
        } catch (\Exception $e) {
            logger()->error('export overtime: ' . $e->getMessage());
        }
    }
}
