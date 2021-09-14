<?php

namespace App\Http\Controllers\API\v1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
Use App\TimeTable;
use Validator;
use App\User;
use Auth;

class UserTimeTableController extends BaseController
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
       
        $data = TimeTable::where('user_id',$request->user()->id)->orderBy('order_on','ASC')->get();

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
            'file' => 'required',
        ]);

        if($validator->fails()){
            return $this->sendError('Validation Error.', $validator->errors(),422);      
        }

        if($request->hasFile('file'))
        {
            $uploaded_file = rand().'_'.$request->user()->id.'.'.$request->file('file')->guessExtension();
            $request->file = $request->file('file')->storeAs('file/time_table',$uploaded_file,'public');
        }

        $data = new TimeTable([
            'user_id' => $request->user()->id,
            'title'   => $request->get('title'),
            'file'    => $request->file,
            'status'  => $request->has('status') ? $request->status : '0' ,           
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
       
        $data = TimeTable::select('id','user_id','title','file','status','updated_at')->where('user_id',$request->user()->id)->FindorFail($id);

        if (is_null($data)){                        
            return $this->sendResponse($data, trans('en_lang.DATA_NOT_FOUND'),404);
        }
        return $this->sendResponse($data, trans('en_lang.DATA_FOUND'),200);
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
            'title' => 'required',
            'file' => 'required',
        ]);

        if($validator->fails()){
            return $this->sendError('Validation Error.', $validator->errors(),422);      
        }



        $data = TimeTable::where('user_id',$request->user()->id)->find($id);
        
        if($request->hasFile('file'))
        {
            DeleteOldPicture($data->file);
            $uploaded_file = rand().'_'.$request->user()->id.'.'.$request->file('file')->guessExtension();
            $request->file = $request->file('file')->storeAs('file/time_table',$uploaded_file,'public');
        }

        $data->user_id    = $request->user()->id;
        $data->title      = $request->title;
        $data->file       = $request->file;
        $data->status     = $request->status;

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
       
        $delete = TimeTable::where('user_id',$request->user()->id)->find($id);  
        $delete->delete();

        return $this->sendResponse('Time Table successfully deleted', trans('en_lang.DATA_DELETED'),204);
    }

    public function orderTimeTable(Request $request)
    {
        $input = $request->all();
        $validator = Validator::make($input, [
            'orders' => 'required',
        ]);

        if($validator->fails()){
            return $this->sendError('Validation Error.', $validator->errors(),422);      
        }
       
        foreach ($request->orders as $key => $order) {
            TimeTable::where('id', $order['id'])->update(['order_on' => $key + 1]);
        }
        return $this->sendResponse('Updated Successfully.', 'DATA FOUND', 200);
              
    }
}
