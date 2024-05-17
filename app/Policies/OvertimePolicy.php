<?php

namespace App\Policies;

use App\Models\Overtime;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Support\Facades\Gate;

class OvertimePolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function viewAny(User $user)
    {
        return true;
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Overtime  $overtime
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function view(User $user, Overtime $overtime)
    {
        $userOvertime = User::find($overtime->created_by);
        return Gate::forUser($userOvertime)->allows('self') || Gate::forUser($user)->allows('admin');
    }

    /**
     * Determine whether the user can create models.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function create(User $user)
    {
        return true;
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Overtime  $overtime
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function update(User $user, Overtime $overtime)
    {
        $userOvertime = User::find($overtime->created_by);
        return (Gate::forUser($userOvertime)->allows('self') && $overtime->status == config('common.overtime.waiting'))
            || Gate::forUser($user)->allows('admin');
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Overtime  $overtime
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function delete(User $user, Overtime $overtime)
    {
        $userOvertime = User::find($overtime->created_by);
        return (Gate::forUser($userOvertime)->allows('self') && $overtime->status == config('common.overtime.waiting'))
            || Gate::forUser($user)->allows('admin');
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Overtime  $overtime
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function restore(User $user, Overtime $overtime)
    {
        $userOvertime = User::find($overtime->created_by);
        return Gate::forUser($userOvertime)->allows('self') || Gate::forUser($user)->allows('admin');
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Overtime  $overtime
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function forceDelete(User $user, Overtime $overtime)
    {
        $userOvertime = User::find($overtime->created_by);
        return Gate::forUser($userOvertime)->allows('self') || Gate::forUser($user)->allows('admin');
    }

    public function approve(User $user, Overtime $overtime)
    {
        return Gate::forUser($user)->allows('admin');
    }
}
