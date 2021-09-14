<?php

namespace App\Http\Controllers\API\v1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
Use App\Qualification;
use Validator;
use App\User;
use Auth;

class UserQualificationController extends BaseController
{
    
    public function __construct()
    {
        $this->middleware('auth:api');
    }    

    /**
     * Display a listing of the User Qalification.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
       
        $qualification = Qualification::select('id','user_id','qualification','from_year','from_where','order_on','updated_at')->where('user_id',$request->user()->id)->orderBy('order_on','ASC')->get();

        if (is_null($qualification)){                        
            return $this->sendResponse($qualification, trans('en_lang.DATA_NOT_FOUND'),404);
        }
        return $this->sendResponse($qualification, trans('en_lang.DATA_FOUND'),200);
    }

    
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $input = $request->all();

        $validator = Validator::make($input, [
            'course' => 'required'
            // 'fromeWhere' => 'required',
            // 'year' => 'required'
        ]);

        if($validator->fails()){
            return $this->sendError('Validation Error.', $validator->errors(),422);      
        }
        
        $qualification = new Qualification([
            'user_id' => $request->user()->id,
            'qualification' => $request->get('course'),
            'from_year' => $request->get('year'),
            'from_where' => $request->get('fromeWhere'),           
        ]);
        $qualification->save();

        return $this->sendResponse($qualification,trans('en_lang.DATA_CREATED'),201);

    }

    /**
     * Edit Single User Qalification.
     *
     * @return \Illuminate\Http\Response
     */
    public function edit(Request $request, $id)
    {
       
        $qualification = Qualification::select('id','user_id','qualification','from_year','from_where','updated_at')->where('user_id',$request->user()->id)->FindOrFail($id);

        if (is_null($qualification)){                        
            return $this->sendResponse($qualification, trans('en_lang.DATA_NOT_FOUND'),404);
        }
        return $this->sendResponse($qualification, trans('en_lang.DATA_FOUND'),200);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    /*public function show(Request $request, $id)
    {
       // $id = $request->id;
        $qualification = Qualification::select('id','user_id','qualification','from_year','from_where','updated_at')->where('user_id',$request->user()->id)->find($id);

        if (is_null($qualification)) {
            return $this->sendResponse($qualification, trans('en_lang.DATA_NOT_FOUND'),404);
        }

        return $this->sendResponse($qualification, trans('en_lang.DATA_FOUND'),200);
    }*/

    
    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $input = $request->all();
        $validator = Validator::make($input, [
            'qualification' => 'required'
            // 'from_where' => 'required',
            // 'from_year' => 'required'
        ]);


        if($validator->fails()){
            return $this->sendError('Validation Error.', $validator->errors(),422);      
        }

        $qualification = Qualification::where('user_id',$request->user()->id)->find($id);

        $qualification->user_id         = $request->user()->id;
        $qualification->qualification   = $request->qualification;
        $qualification->from_year       =  $request->from_year;
        $qualification->from_where      = $request->from_where;

        $qualification->save();

        return $this->sendResponse($qualification,trans('en_lang.DATA_UPDATED'),200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, $id)
    {
        //$id = $request->id;
        $delete = Qualification::where('user_id',$request->user()->id)->find($id);  
        $delete->delete();

        return $this->sendResponse('Qualification successfully deleted', trans('en_lang.DATA_DELETED'),204);
    }

    public function orderQualification(Request $request)
    {
        $input = $request->all();
        $validator = Validator::make($input, [
            'orders' => 'required',
        ]);

        if($validator->fails()){
            return $this->sendError('Validation Error.', $validator->errors(),422);      
        }
       
        foreach ($request->orders as $key => $order) {
            Qualification::where('id', $order['id'])->update(['order_on' => $key + 1]);
        }
        return $this->sendResponse('Updated Successfully.', 'DATA FOUND', 200);
              
    }
}
