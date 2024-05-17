<?php

namespace App\Policies;

use App\Models\RequestAbsent;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Auth\Access\Response;
use Illuminate\Support\Facades\Gate;

class RequestAbsentPolicy
{
    use HandlesAuthorization;

    /**
     * Create a new policy instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param User $user
     * @param RequestAbsent $requestAbsent
     * @return Response|bool
     */
    public function update(User $user, RequestAbsent $requestAbsent)
    {
        $userRequestAbsent = User::find($requestAbsent->created_by);
        return Gate::forUser($userRequestAbsent)->allows('self') || Gate::forUser($user)->allows('admin');
    }

    public function approve(User $user, RequestAbsent $requestAbsent)
    {
        return Gate::forUser($user)->allows('admin');
    }

    public function delete(User $user, RequestAbsent $requestAbsent)
    {
        $userRequestAbsent = User::find($requestAbsent->created_by);
        return (Gate::forUser($userRequestAbsent)->allows('self')
                && $requestAbsent->status == config('common.request_absent.waiting'))
            || Gate::forUser($user)->allows('admin');
    }
}
