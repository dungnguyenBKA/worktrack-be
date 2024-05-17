<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Cms\ReqAbsentRequest;
use App\Http\Requests\UpdateAbsentRequest;
use App\Models\ReportConfig;
use App\Models\RequestAbsent;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\RequestAbsentExport;
use Carbon\Carbon;

class RequestAbsentController extends Controller
{
    public function index(Request $request)
    {
        $page_title = __('layouts.absence_request');
        $user = $request->user();
        $isHaveRequest = count($request->all()) > 0;
        $isSearch = isset($request->isSearch);
        $users = [];
        try {
            $date = isset($request->date) ? Carbon::createFromFormat('Y/m', $request->date)->format('Y-m') : ( $isSearch ? null : Carbon::now()->format('Y-m') );
        } catch (\Throwable $th) {
            $date = Carbon::now()->format('Y-m');
        }
        $userIdSearch = $request->user_id;

        $query = RequestAbsent::select('request_absent.*', 'email', 'staff_id', 'first_name', 'last_name')
            ->join('users', 'users.id', '=', 'request_absent.user_id')
            ->whereNull('users.deleted_at');
        if($date) {
            $startDate = Carbon::parse($date)->format('Y-m-d');
            $endDate = Carbon::parse($date)->addMonth()->format('Y-m-d');
            $query->where('from_time', '>=', $startDate ?? DATE(NOW()))
                    ->where('from_time', '<', $endDate ?? DATE(NOW()));
        }

        if ($user && $user->role == config('common.user.role.user')) {
            $query->where('request_absent.created_by', $user->id);
        }

        if ($userIdSearch) {
            $query->where(function ($query) use ($userIdSearch) {
                if(isset($userIdSearch)) {
                    $query->where('request_absent.created_by', $userIdSearch);
                }
            });

        }
        $query->orderBy('from_time', 'DESC')->orderBy('created_at', 'DESC');
        $isAdmin = $this->userCan('admin');

        $requestAbsents = $query->paginate(30);
        if($isAdmin) {
            $userIds = $date ? $this->getUserIdsRequestAbsent($date, $startDate, $endDate) :  $this->getUserIdsRequestAbsent(false);
            $users = User::whereIn('id', $userIds)->get();
        }
        Session::flash('backUrl', $request->fullUrl());

        return view('admin.request_absent.index', compact('page_title', 'requestAbsents', 'isAdmin', 'users', 'date', 'request'));
    }

    function getUserIdsRequestAbsent($date, $startDate = null, $endDate = null) {
        $userIds = [];
        if($date) {
            $queryWithMonth = RequestAbsent::where('from_time', '>=', $startDate ?? DATE(NOW()))
                        ->where('from_time', '<', $endDate ?? DATE(NOW()))
                        ->groupBy('user_id')->get();
            foreach ($queryWithMonth as $key => $value) {
                $userIds[] = $value->user_id;
            }
        } else {
            $requestAbsents = RequestAbsent::all()->groupBy('user_id')->toArray();
            if(count($requestAbsents) > 0) {
                foreach ($requestAbsents as $key => $value) {
                    $userIds[] = $key;
                }
            }
        }
        return $userIds;
    }

    public function approve(RequestAbsent $requestAbsent){
        if (!$this->userCan('admin')) {
            abort('403', __('messages.permission_access_denied'));
        }
        $page_title = __('layouts.approve_request_absent');
        $this->keepBackUrl();
        return view('admin.request_absent.show', compact('page_title', 'requestAbsent'));
    }

    public function approveOrRejectAll(Request $request) {
        try {
            if (!$this->userCan('admin')) {
                abort('403', __('messages.permission_access_denied'));
            }
            $dataRequest = $request->session()->all();
            $splitUrl = explode('?', $dataRequest['_previous']['url']);
            $isApprove = $request->type == '1';
            $statusUpdate = $isApprove ? config('common.request_absent.approve') : config('common.request_absent.reject');
            $messageSuccess = $isApprove ? __('messages.approve-all-success') : __('messages.reject-all-success');
            $requestAbsentsPending = RequestAbsent::whereIn('id', $request->ids)->get();
            foreach ($requestAbsentsPending as $key => $requestAbsent) {
                $requestAbsent->status = $statusUpdate;
                $requestAbsent->save();
            }
            Session::flash('message',$messageSuccess);
            Session::flash('success', true);
            return response()->json(['success' => true, 'url' => $splitUrl]);
        } catch (\Exception $e) {
            logger()->error('Approve all error: ' . $e->getMessage());
        }
    }

    public function create()
    {
        $page_title = __('layouts.create_request_absent');
        $config = ReportConfig::first();
        $this->keepBackUrl();
        return view('admin.request_absent.create', compact('page_title', 'config'));
    }

    public function store(ReqAbsentRequest $request)
    {
        $this->keepBackUrl();
        $url = Session::has('backUrl') ? Session::get('backUrl') : route('request-absent.index');
        $data = $request->transferredData();

        $data['user_id'] = auth()->id();
        $data['created_by'] = $data['user_id'];

        try {
            RequestAbsent::create($data);
            return redirect()->to($url)
                ->with(['success' => true, 'message' => __('messages.create.success')]);
        } catch (\Exception $e) {
            logger()->error('Admin create user: ' . $e->getMessage());
            return redirect()->back()
                ->with(['success' => false, 'message' => __('messages.create.error')]);
        }
    }

    public function edit(RequestAbsent $requestAbsent)
    {
        $user = User::find($requestAbsent->user_id);
        if (!($this->userCan('self', $user) && $requestAbsent->status == config('common.request_absent.waiting'))
            && !$this->userCan('admin')) {
            abort('403', __('messages.permission_access_denied'));
        }
        $page_title = __('layouts.edit_request_absent');
        $this->keepBackUrl();
        return view('admin.request_absent.edit', compact('page_title', 'requestAbsent'));
    }

    public function update(UpdateAbsentRequest $request, RequestAbsent $requestAbsent)
    {
        $url = Session::has('backUrl') ? Session::get('backUrl') : route('request-absent.index');
        $user = User::find($requestAbsent->user_id);
        if (!($this->userCan('self', $user) && $requestAbsent->status == config('common.request_absent.waiting'))
            && !$this->userCan('admin')) {
            abort('403', __('messages.permission_access_denied'));
        }
        $success = true;
        $message = __('messages.edit.success');
        $RequestAbsentData = $request->validated();
        if (!preg_match('/approve/', url()->previous(), $match)){
            $RequestAbsentData['use_leave_hour'] = isset($RequestAbsentData['use_leave_hour']) ? 1 : 0;
        }
        try {
            $requestAbsent->update($RequestAbsentData);
        } catch (\Exception $e) {
            logger()->error('Admin update user: ' . $e->getMessage());
            $success = false;
            $message = __('messages.edit.error');;
        }

        return redirect()->to($url)
            ->with(['success' => $success, 'message' => $message]);
    }

    public function destroy(RequestAbsent $requestAbsent)
    {
        $user = User::find($requestAbsent->user_id);
        if (!(($this->userCan('self', $user) && $requestAbsent->status == config('common.request_absent.waiting')) ||
            $this->userCan('admin'))) {
            abort('403', __('messages.permission_access_denied'));
        }
        $success = true;
        $message = __('messages.delete.success');
        $url = Session::has('backUrl') ? Session::get('backUrl') : route('request-absent.index');
        try {
            $requestAbsent->delete();
        } catch (\Exception $e) {
            logger()->error('Delete request absent: ' . $e->getMessage());
            $success = false;
            $message = __('messages.delete.error');
        }
        return redirect()->to($url)
            ->with(['success' => $success, 'message' => $message]);
    }

    function exportRequestAbsent(Request $request) {
        $this->keepBackUrl();
        try {
            $userId = $this->userCan('admin') ? $request->user_id : Auth::id();
            $date = isset($request->date) ? Carbon::createFromFormat('Y/m', $request->date)->format('Y-m') : null;
            if($date) {
                $startDate = Carbon::parse($date)->format('Y-m-d');
                $endDate = Carbon::parse($date)->addMonth()->format('Y-m-d');
                $requestExport = new RequestAbsentExport($userId, $startDate, $endDate);
            } else {
                $requestExport = new RequestAbsentExport($userId);
            }
            $dateExport = $date ? $date : Carbon::now()->format('Y-m');
            $dateExport = date("Ymd_his");
            if ($userId != null) {
                $user = User::getUsers(2, $userId);
                $userName = $user[0]->first_name . ' ' . $user[0]->last_name;
                return Excel::download($requestExport, __('layouts.prefix_request_absent') . $userName . '_' . $dateExport . '.xlsx');
            } else {
                return Excel::download($requestExport, 'WorkTrack_' . __('layouts.prefix_request_absent') . $dateExport . '.xlsx');
            }
        } catch (\Exception $e) {
            logger()->error('export request absent: ' . $e->getMessage());
        }
    }
}
