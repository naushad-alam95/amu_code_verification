<?php

namespace App\Http\Controllers\API\v1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
Use App\StudyMatrial;
use Validator;
use App\User;
use Auth;

class UserStudyMaterialController extends BaseController
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
       
        $studyMatrial = StudyMatrial::where('user_id',$request->user()->id)->orderBy('order_on','ASC')->get();

        if (is_null($studyMatrial)){                        
            return $this->sendResponse($studyMatrial, trans('en_lang.DATA_NOT_FOUND'),404);
        }
        return $this->sendResponse($studyMatrial, trans('en_lang.DATA_FOUND'),200);
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
        //\Log::info($request->all());

        $validator  = Validator::make($input, [
            'title' => 'required',
            'type'  => 'required|in:file,video',
            'file'  => 'required_if:type,file',
   	    'video' => 'required_if:type,video',
        ]);

        if($validator->fails()){
            return $this->sendError('Validation Error.', $validator->errors(),422);      
        }

        if($request->hasFile('file'))
        {
            $uploaded_file = rand().'_'.$request->user()->id.'.'.$request->file('file')->guessExtension();
            $request->file = $request->file('file')->storeAs('file/study_material',$uploaded_file,'public');
        }
        $data =array();
        $data['user_id'] = $request->user()->id;
        $data['title']   = $request->get('title');
        $data['type']   = $request->get('type');
        if ($request->hasFile('file')) {
        	$data['file'] = $request->file;
        }
        if ($request->video) {
        	$data['video'] = $request->video;
        }
        $studyMatrial = StudyMatrial::create($data);

        return $this->sendResponse($studyMatrial,trans('en_lang.DATA_CREATED'),201);

    }

    /**
     * Edit  User Study Matrial.
     *
     * @return \Illuminate\Http\Response
     */
    public function edit(Request $request, $id)
    {
       
        $studyMatrial = StudyMatrial::where('user_id',$request->user()->id)->FindorFail($id);

        if (is_null($studyMatrial)){                        
            return $this->sendResponse($studyMatrial, trans('en_lang.DATA_NOT_FOUND'),404);
        }
        return $this->sendResponse($studyMatrial, trans('en_lang.DATA_FOUND'),200);
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
        $studyMatrial = StudyMatrial::select('id','user_id','StudyMatrial','from_year','from_where','updated_at')->where('user_id',$request->user()->id)->find($id);

        if (is_null($studyMatrial)) {
            return $this->sendResponse($studyMatrial, trans('en_lang.DATA_NOT_FOUND'),404);
        }

        return $this->sendResponse($studyMatrial, trans('en_lang.DATA_FOUND'),200);
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
        $validator  = Validator::make($input, [
            'title' => 'required',
            'type'  => 'required|in:file,video',
            'file'  => 'required_if:type,file',
   	    'video' => 'required_if:type,video',
        ]);

        if($validator->fails()){
            return $this->sendError('Validation Error.', $validator->errors(),422);      
        }



        $studyMatrial = StudyMatrial::where('user_id',$request->user()->id)->find($id);
        
        if($request->hasFile('file'))
        {
            DeleteOldPicture($studyMatrial->file);
            $uploaded_file = rand().'_'.$request->user()->id.'.'.$request->file('file')->guessExtension();
            $request->file = $request->file('file')->storeAs('file/study_material',$uploaded_file,'public');
        }

        $data =array();
        $data['title']   = $request->get('title');
        $data['type']   = $request->get('type');
        if ($request->hasFile('file')) {
        	$data['file']= $request->file;
        }
        if ($request->video) {
        	$data['video']= $request->video;
        }
        $update = StudyMatrial::where('id',$id)->update($data);

        return $this->sendResponse($update,trans('en_lang.DATA_UPDATED'),200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, $id)
    {
       
        $delete = StudyMatrial::where('user_id',$request->user()->id)->find($id);  
        $delete->delete();

        return $this->sendResponse('StudyMatrial successfully deleted', trans('en_lang.DATA_DELETED'),204);
    }

    public function orderStudyMaterial(Request $request)
    {
        $input = $request->all();
        $validator = Validator::make($input, [
            'orders' => 'required',
        ]);

        if($validator->fails()){
            return $this->sendError('Validation Error.', $validator->errors(),422);      
        }
       
        foreach ($request->orders as $key => $order) {
            StudyMatrial::where('id', $order['id'])->update(['order_on' => $key + 1]);
        }
        return $this->sendResponse('Updated Successfully.', 'DATA FOUND', 200);
              
    }
}
