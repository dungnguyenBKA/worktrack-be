<?php

namespace App\Http\Middleware;

use App\Models\ReportConfig;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;

class CheckMaintenanceMode
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        if ($request->url() != route('maintenance')){
            $config = ReportConfig::first();
            if ($config->maintenance){
                throw new HttpException(503);
            }
        }

        return $next($request);
    }
}
