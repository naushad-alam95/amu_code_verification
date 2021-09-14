<?php

namespace App\Http\Controllers\API\v1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
Use App\ThrustArea;
use Validator;
use App\User;
use Auth;

class UserThrustAreaController extends BaseController
{
    
    public function __construct()
    {
        $this->middleware('auth:api');
    }    

    /**
     * Display a listing of the User Study Matrial.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
       
        $data = ThrustArea::where('user_id',$request->user()->id)->orderBy('order_on','ASC')->get();

        if (is_null($data)){                        
            return $this->sendResponse($data, trans('en_lang.DATA_NOT_FOUND'),404);
        }
        return $this->sendResponse($data, trans('en_lang.DATA_FOUND'),200);
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
            'title' => 'required',
        ]);

        if($validator->fails()){
            return $this->sendError('Validation Error.', $validator->errors(),422);      
        }

        

        $data = new ThrustArea([
            'user_id' => $request->user()->id,
            'title'   => $request->title,         
        ]);
        $data->save();

        return $this->sendResponse($data,trans('en_lang.DATA_CREATED'),201);

    }

    /**
     * Edit  User Study Matrial.
     *
     * @return \Illuminate\Http\Response
     */
    public function edit(Request $request, $id)
    {
       
        $data = ThrustArea::find($id);

        if (is_null($data)){                        
            return $this->sendResponse($data, trans('en_lang.DATA_NOT_FOUND'),404);
        }
        return $this->sendResponse($data, trans('en_lang.DATA_FOUND'),200);
    }


    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request, $id)
    {
       
    }
    
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
            'title' => 'required'
        ]);

        if($validator->fails()){
            return $this->sendError('Validation Error.', $validator->errors(),422);      
        }


        $data = ThrustArea::where('user_id',$request->user()->id)->find($id);
        $data->user_id    = $request->user()->id;
        $data->title      = $request->title;          

        $data->save();

        return $this->sendResponse($data,trans('en_lang.DATA_UPDATED'),200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, $id)
    {
       
        $delete = ThrustArea::find($id);  
        $delete->delete();

        return $this->sendResponse('Thrust area successfully deleted', trans('en_lang.DATA_DELETED'),204);
    }

    public function updateOrder(Request $request)
    {
        $input = $request->all();
        $validator = Validator::make($input, [
            'orders' => 'required',
        ]);

        if($validator->fails()){
            return $this->sendError('Validation Error.', $validator->errors(),422);      
        }
       
        foreach ($request->orders as $key => $order) {
            ThrustArea::where('id', $order['id'])->update(['order_on' => $key + 1]);
        }
        return $this->sendResponse('Updated Successfully.', 'DATA FOUND', 200);
              
    }
}
