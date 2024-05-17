<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Cms\WorkTitleRequest;
use App\Models\WorkTitle;
use Illuminate\Http\Request;

class WorkTitleController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
     */
    public function index()
    {
        $page_title = __('layouts.work_title');
        $query = WorkTitle::query();
        $workTitles = $query->paginate(10);
        return view('admin.work_title.index', compact('workTitles', 'page_title'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
     */
    public function create()
    {
        $page_title = __('layouts.add_work_title');
        return view('admin.work_title.create', compact('page_title'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(WorkTitleRequest $request)
    {
        $workTitle = new WorkTitle();
        $data = $request->validated();
        $sort_num = WorkTitle::max('sort_num');
        $data['sort_num'] = ++$sort_num;
        $workTitle->fill($data);
        $success = false;
        $message = __('messages.create.error');
        if ($workTitle->save()){
            $success = true;
            $message = __('messages.create.success');
        }
        return redirect()->route('work-titles.index')
            ->with(['success' => $success, 'message' =>$message]);
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
     * @param WorkTitle $workTitle
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
     */
    public function edit(WorkTitle $workTitle)
    {
        $page_title = __('layouts.edit_work_title');
        return view('admin.work_title.edit', compact('page_title', 'workTitle'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param WorkTitleRequest $request
     * @param WorkTitle $workTitle
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(WorkTitleRequest $request, WorkTitle $workTitle)
    {
        $data = $request->validated();
        $success = false;
        $message = __('messages.edit.error');
        if ($workTitle->update($data)){
            $success = true;
            $message = __('messages.edit.success');
        }
        return redirect()->route('work-titles.index')
            ->with(['success' => $success, 'message' => $message]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
