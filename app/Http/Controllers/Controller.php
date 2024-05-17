<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Session;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    public function userCan($action, $user = null, $option = null)
    {
        $user = $user ?? auth()->user();
        return Gate::forUser($user)->allows($action, $option);
    }


    protected function keepBackUrl()
    {
        if (Session::has('backUrl')) {
            Session::keep('backUrl');
        }
    }
}
