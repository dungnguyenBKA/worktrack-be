<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\NotificationRequest;
use App\Models\Notifications;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class NotificationController extends Controller
{
    protected $notificationModel;
    public function __construct()
    {
        $this->notificationModel = new Notifications();
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $page_title = __('layouts.notification');
        $notifications = $this->notificationModel->query()->orderBy('id', 'desc')->paginate(30);
        return view('admin.notification.index', compact('page_title', 'notifications'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $page_title = __('layouts.create_notification');;
        return view('admin.notification.create', compact('page_title'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param NotificationRequest $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(NotificationRequest $request)
    {
        $input = $request->validated();
        try {
            $notification = $this->notificationModel->fill($input);
            $notification->save();

            return redirect()->route('notification.index')
                ->with([
                    'success' => true,
                    'message' => __('messages.create.success')
                ]);
        }catch (\Exception $exception) {
            Log::error('Create Notification: '. $exception->getMessage());
            return redirect()->route('notification.index')
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
     * @param Notifications $notification
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
     */
    public function edit(Notifications $notification)
    {
        $page_title = __('layouts.edit_notification');
        return view('admin.notification.edit', compact('page_title', 'notification'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param NotificationRequest $request
     * @param Notifications $notification
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(NotificationRequest $request, Notifications $notification)
    {
        $input = $request->validated();
        try {
            $notification->update($input);

            return redirect()->route('notification.index')
                ->with([
                    'success' => true,
                    'message' => __('messages.create.success')
                ]);
        }catch (\Exception $exception) {
            Log::error('Update Notification: '. $exception->getMessage());
            return redirect()->route('notification.index')
                ->with([
                    'success' => false,
                    'message' => __('messages.create.error')
                ]);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Notifications $notification
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Notifications $notification)
    {
        try {
            $notification->delete();
            $success = true;
            $message = __('messages.delete.success');
            return redirect()->route('notification.index')
                ->with(['success' => $success, 'message' => $message]);
        }catch (\Exception $e){
            logger()->error('Delete notification error: ' . $e->getMessage());
            $success = false;
            $message = __('messages.delete.error');
            return redirect()->route('notification.index')
                ->with(['success' => $success, 'message' => $message]);
        }
    }
}
