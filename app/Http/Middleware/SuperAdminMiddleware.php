<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Response;
use App\UserRole;
use Auth;
use Redirect;
use Request;

class SuperAdminMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (Auth::id()) {
          $userrole = UserRole::where('user_id','=',$request->user()->id)->whereIn('role_id',[1,19,49])->first();
            if ($request->user() && !$userrole)
            {
                return new Response(view('unauthorized')->with('role', 'SUPER ADMIN OR PRO Or MIC Telephone'));
            }
        return $next($request);
        }else{
           if (strpos(Request::getPathInfo(), "storage") !== false){
              return Redirect::to(env('FRONT_URL_404'));
           }else{
              return Redirect::to('/');
           }
           return Redirect::to('/');
        }
        
    }
}
