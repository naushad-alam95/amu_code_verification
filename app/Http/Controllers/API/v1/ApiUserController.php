<?php

namespace App\Http\Controllers\API\v1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\User;
use Auth;

class ApiUserController extends Controller
{
    public function index(){
       $users = User::where('id','!=',Auth::id())->where('for_id','=',1)->where('deleted_at',null)->orderBy('created_at','desc')->paginate(5);
       return response()->json($users);
    }
}
