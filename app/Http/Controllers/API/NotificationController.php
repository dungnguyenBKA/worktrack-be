<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Notifications;
use App\Models\NotificationUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class NotificationController extends BaseController
{
    /**
     * List notification
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $notificationModel = new Notifications();
        $notificationUserModel = new NotificationUser();
        $notifications = $notificationModel->getList();
        $notificationUsers = $notificationUserModel->getList(['user_id' => $this->userId]);
        $notificationIds = array_column($notificationUsers->toArray(), 'notification_id');
        foreach ($notifications as $notification){
            $notification->readed = in_array($notification->id, $notificationIds);
        }

        return send_success($notifications);
    }

    /**
     * Detail notification
     * @param $id
     */
    public function detail($id)
    {
        $notificationModel = new Notifications();
        $notification = $notificationModel->find($id);
        if ($notification){
            try {
                NotificationUser::updateOrCreate(
                    [
                        'notification_id' => $id,
                        'user_id' => $this->userId
                    ],
                    [
                        'notification_id' => $id,
                        'user_id' => $this->userId
                    ]
                );
                $notification->readed = true;
                return send_success($notification);
            } catch (\Exception $exception){
                Log::error('Update notification user: '.$exception->getMessage());
                return send_error('E0301');
            }
        }

        return send_error('E0400');
    }
}
