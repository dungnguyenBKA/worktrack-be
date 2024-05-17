<?php

namespace App\Http\Controllers\Admin;

use App\Models\Position;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Http\Requests\Cms\PostionRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;

class PositionController extends Controller
{

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $page_title = __('layouts.work_location');
        $types = config('constants.postion_type');
        $status = config('constants.status');
        $positions = Position::getAll($request->all(), ['positions.id', 'user_id', 'position', 'positions.status', 'type']);
        $isAdmin = $this->userCan('admin');
        $users = User::getUsers();
        Session::flash('backUrl', $request->fullUrl());

        return view('position.index', compact('request', 'positions', 'types', 'page_title',
               'status', 'isAdmin', 'users'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $page_title = __('layouts.register_work_location');
        $users = User::getUsers();
        $this->keepBackUrl();

        return view('position.create', compact('users', 'page_title'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(PostionRequest $request)
    {
        $this->keepBackUrl();
        $input = $request->validated();
        DB::beginTransaction();
        try {
            $input['created_by'] = Auth::id();
            if($input['type'] == 1) {
                $input['user_id'] = null;
            }
            Position::create($input);
            DB::commit();

            return redirect()->to(Session::get('backUrl') ?? route('position.index'))
                    ->with('success', 'Created successfully');
        } catch (\Exception $exception){
            DB::rollBack();
            Log::error('Posistion add error: '.$exception->getMessage());

            return redirect()->back()
                    ->with('error', 'Create error');
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Position  $position
     * @return \Illuminate\Http\Response
     */
    public function show(Position $position)
    {
        $page_title = __('layouts.work_location');
        $this->keepBackUrl();
        return view('position.show', compact('position', 'page_title'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Position  $position
     * @return \Illuminate\Http\Response
     */
    public function edit(Position $position)
    {
        $page_title = __('layouts.update_work_location');
        $users = User::getUsers();
        $this->keepBackUrl();

        return view('position.edit', compact('users', 'page_title', 'position'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Position  $position
     * @return \Illuminate\Http\Response
     */
    public function update(PostionRequest $request, Position $position)
    {
        $this->keepBackUrl();
        $input = $request->validated();
        DB::beginTransaction();
        try {
            if($input['type'] == 1) {
                $input['user_id'] = null;
            }
            $position->update($input);
            DB::commit();

            return redirect()->to(Session::get('backUrl') ?? route('position.index'))
                    ->with('success', 'Updated successfully');
        } catch (\Exception $exception){
            DB::rollBack();
            Log::error('Posistion add error: '.$exception->getMessage());

            return redirect()->back()
                    ->with('error', 'Update error');
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Position  $position
     * @return \Illuminate\Http\Response
     */
    public function destroy(Position $position)
    {
        $success = true;
        $message = __('messages.delete.success');
        $url = Session::has('backUrl') ? Session::get('backUrl') : route('position.index');
        try {
            $position->delete();
        } catch (\Exception $e) {
            logger()->error('Delete position: ' . $e->getMessage());
            $success = false;
            $message = __('messages.delete.error');
        }
        return redirect()->to(Session::get('backUrl') ?? route('position.index'))
            ->with(['success' => $success, 'message' => $message]);
    }

    /**
     * Get data passed into datatable
     *
     * @param type $request
     * @return json
     */
    public function datatables($request)
    {
        $userPostions = Position::getAll($request, ['id', 'user_id', 'position', 'status']);

        return Datatables::of($userPostions)
            ->addColumn('action', function ($userPostion) {
                return '<a class="btn btn-info btn-xs" href="' . route('game.edit', $userPostion->id) . '">
                <i class="fa fa-edit"></i>' . __('messages.Edit') . '</a>';
            })
            ->addColumn('id', function ($userPostion) {
                return '<a href="' . route('user-position.edit', $userPostion->id) . '">' . $userPostion->id . '</a>';
            })
//            ->addColumn('type', function ($userPostion) {
//                $type =  config('constants.game_types')[$userPostion->type] ?? "";
//
//                return __('messages.' . $type);
//            })
            ->rawColumns(['id', 'action'])
            ->make(true);
    }
}
