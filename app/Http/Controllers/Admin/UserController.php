<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Cms\StoreUserRequest;
use App\Http\Requests\Cms\UpdateUserRequest;
use App\Models\PaidLeave;
use App\Models\Role;
use App\Models\User;
use App\Models\WorkTitle;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Remotes\CrmRemote;
use Validator;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\UserImport;
use Illuminate\Support\Facades\Session;

class UserController extends Controller
{
    public function profile()
    {
        $page_title = __('layouts.user_profile');
        $user = auth()->user();
        $redirect = 'dashboard';
        return view('admin.users.show', compact('user', 'page_title', 'redirect'));
    }

    public function index(Request $request)
    {
        if (!$this->userCan('admin')) {
            abort('403', __('messages.permission_access_denied'));
        }

        $page_title = __('layouts.users_management');
        $search = $request->input('search');

        $query = User::select('id', 'staff_id', 'first_name', 'last_name', 'email', 'role', 'status');
        if ($search) {
            $query->where('staff_id', 'like', "%{$search}%")
                ->orWhere('first_name', 'like', "%{$search}%")
                ->orWhere('last_name', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%")
                ->orWhere(DB::raw('CONCAT(first_name," ",last_name)'), 'like', "%{$search}%");
        }
        $query->orderBy('staff_id', 'ASC');
        $users = $query->paginate(30);

        return view('admin.users.index', compact('users', 'search', 'page_title'));
    }

    public function create()
    {
        if (!$this->userCan('admin')) {
            abort('403', __('messages.permission_access_denied'));
        }
        $page_title = __('layouts.create_new_user');
        $maxStaffId = User::max('staff_id');
        $roles = Role::all();
        $workTitles = WorkTitle::all();
        return view('admin.users.create', compact( 'page_title', 'roles', 'workTitles'));
    }

    public function store(StoreUserRequest $request)
    {
        $userData = $request->transferredData();
        if (!$this->userCan('admin')) {
            abort('403', __('messages.permission_access_denied'));
        }
        try {
            // Check error create user master in CRM
            if(hasSubdomain()) {
                $userCount = User::count();
                $crmRemote = new CrmRemote();
                $userMaster = $crmRemote->getUserMasterByEmail($userData['email']);
                $company = $crmRemote->getDetailCompany();
                if(isset($userMaster['error']) || isset($company['error'])) {
                    return redirect()->back()
                        ->with(['success' => false, 'message' => __('messages.create.error')]);
                }
                if(!isset($company['data']) || (isset($company['data']) && empty($company['data']))) {
                    return redirect()->back()
                        ->with(['success' => false, 'message' => __('messages.subdomain_does_not_exist')]);
                } else if(isset($company['data']['limit_users']) && !empty ($company['data']['limit_users']) && $company['data']['limit_users'] <= $userCount) {
                    return redirect()->back()
                        ->with(['success' => false, 'message' => __('messages.limit_users')]);
                }
                if(isset($userMaster['data'])) {
                    return redirect()->back()
                        ->with(['success' => false, 'message' => __('messages.email_already_exists_at_another_company')]);
                }
            }

            $user = User::create($userData);
            return redirect()->route('users.show', $user->id)
                ->with(['success' => true, 'message' => __('messages.create.success')]);
        } catch (\Exception $e) {
            logger()->error('Admin create user: ' . $e->getMessage());
            return redirect()->back()
                ->with(['success' => false, 'message' => __('messages.create.error')]);
        }
    }

    public function show(User $user)
    {
        if (!$this->userCan('self', $user) && !$this->userCan('admin')) {
            abort('403', __('messages.permission_access_denied'));
        }
        $page_title = __('layouts.user_profile');
        $roles = Role::all();
        $workTitles = WorkTitle::all();
        $redirect = !$this->userCan('admin') ? 'dashboard' : 'users.index';
        return view('admin.users.show', compact('user', 'page_title', 'redirect', 'roles', 'workTitles'));
    }

    public function edit(Request $request, User $user)
    {
        $url = url()->previous();
        $route = app('router')->getRoutes($url)->match(app('request')->create($url))->getName();
        if (!$this->userCan('self', $user) && !$this->userCan('admin')) {
            abort('403', __('messages.permission_access_denied'));
        }
        $page_title = __('layouts.update_user');
        $roles = Role::all();
        $workTitles = WorkTitle::all();
        $redirect = !$this->userCan('admin') ? 'dashboard' : 'users.index';
        return view('admin.users.edit', compact('user', 'page_title', 'redirect', 'roles', 'workTitles'));
    }

    public function update(UpdateUserRequest $request, User $user)
    {
        if (!$this->userCan('self', $user) && !$this->userCan('admin')) {
            abort('403', __('messages.permission_access_denied'));
        }
        $success = true;
        $message = __('messages.edit.success');
        $userData = $request->transferredData();

        DB::beginTransaction();
        try {
            // Check error create user master in CRM
            if(hasSubdomain() && $userData['email'] !== $user->email) {
                $crmRemote = new CrmRemote();
                $userMaster = $crmRemote->getUserMasterByEmail($userData['email']);
                if(isset($userMaster['error'])) {
                    return redirect()->back()
                        ->with(['success' => false, 'message' => __('messages.create.error')]);
                }
                if(isset($userMaster['data'])) {
                    return redirect()->back()
                        ->with(['success' => false, 'message' => __('messages.email_already_exists_at_another_company')]);
                }
            }

            $user->update($userData);
            PaidLeave::where('user_id', '=', $user->id)->where('month_year', '<', Carbon::make($userData['paid_leave_start_date'])->format('Y-m'))
                    ->update(['day_add_in_month' => 0]);
            PaidLeave::where('user_id', '=', $user->id)->where('month_year', '>=', Carbon::make($userData['paid_leave_start_date'])->format('Y-m'))
                ->update(['day_add_in_month' => 8]);
            if ($user->status == config('common.user.status.resign')){
                DB::table('sessions')->where('user_id', $user->id)->delete();
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            logger()->error('Admin update user: ' . $e->getMessage());
            $success = false;
            $message = __('messages.edit.error');
        }

        if ($this->userCan('admin')){
            return redirect()->route('users.index')
                ->with(['success' => $success, 'message' => $message]);
        }
        return redirect()->route('users.profile')
            ->with(['success' => $success, 'message' => $message]);
    }

    public function destroy(User $user)
    {
        if (!$this->userCan('admin')) {
            abort('403', __('messages.permission_access_denied'));
        }
        $success = true;
        $message = __('messages.delete.success');
        try {
            $user->delete();
        } catch (\Exception $e) {
            logger()->error('Admin delete user: ' . $e->getMessage());
            $success = false;
            $message = __('messages.delete.error');
        }
        return redirect()->route('users.index')
            ->with(['success' => $success, 'message' => $message]);
    }

    public function uploadUsers(Request $request) {
        Validator::make($request->all(), [
            'file-user' => ['required', 'file', 'max:10000', 'mimes:csv,txt'],
            'type' => ['required', 'integer', 'in:0,1']
        ], [], [])->validate();

        $fieldName = 'file-user';
        if ($request->hasFile($fieldName)) {
            $file = $request->file($fieldName);
            $pathTemp = $file->store('temp');
            $path = storage_path('app').'/'.$pathTemp;
            $err = false;
            try {
                $import = new UserImport($request->type);
                Excel::import($import, $path);
                $errLimit = $import->getErrLimituser();
                $errRows = $import->getErrRows();
                $errDuplicate = $import->getErrDuplicate();
                $errMessageRows = $import->getErrMessageRows();
                $errMessageDuplicate = $import->getErrMessageDuplicate();
                if($errLimit) {
                    return redirect()->back()->with(['success' => false, 'message' => __('messages.limit_users')]);
                }
                if($errRows) {
                    return redirect()->back()->with(['success' => false, 'message' => $errMessageRows ]);
                }
                if($errDuplicate) {
                    return redirect()->back()->with(['success' => false, 'message' => $errMessageDuplicate ]);
                }

                return redirect()->back()
                        ->with(['success' => true, 'message' => __('messages.import.success')]);
            } catch (\Exception $exception) {
                logger()->error('Upload user error: '.$exception->getMessage());

                return redirect()->back()
                        ->with('error',  __('messages.import.error'));
            }
        }
    }
}
