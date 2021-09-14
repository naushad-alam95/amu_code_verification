<?php

namespace App\Http\Controllers\API\v1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
Use App\Journal;
use Validator;
use App\User;
use Auth;

class UserKeyPublicationController extends BaseController
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
       
        $journal = Journal::select('id','user_id','title','pdf_url','description','status','publish_date','featured')->where('user_id',$request->user()->id)->orderBy('created_at','DESC')->get();

        if (is_null($journal)){                        
            return $this->sendResponse($journal, trans('en_lang.DATA_NOT_FOUND'),404);
        }
        return $this->sendResponse($journal, trans('en_lang.DATA_FOUND'),200);
    }

    public function getFeatured(Request $request)
    {
       
        $journal = Journal::select('id','user_id','title','pdf_url','description','status','publish_date','featured')->where('featured','1')->where('user_id',$request->user()->id)->orderBy('publish_date','ASC')->get();

        if (is_null($journal)){                        
            return $this->sendResponse($journal, trans('en_lang.DATA_NOT_FOUND'),404);
        }
        return $this->sendResponse($journal, trans('en_lang.DATA_FOUND'),200);
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
        //\Log::info($input);
        $validator = Validator::make($input, [
            'title' => 'required'
        ]);

        if($validator->fails()){
            return $this->sendError('Validation Error.', $validator->errors(),422);      
        }

        if($request->hasFile('pdf_url'))
        {
            
            $request->validate([
             'pdf_url'  => 'required|mimes:doc,docx,pdf,txt',
            ]);

            $uploaded_file = rand().'_'.$request->user()->id.'.'.$request->file('pdf_url')->guessExtension();
            $request->pdf_url = $request->file('pdf_url')->storeAs('file/journal',$uploaded_file,'public');
        }

        $data = array();
        $data['user_id']       = $request->user()->id;
        $data['title']         = $request->title;
        $data['description']   = $request->description;
        $data['publish_date']  = date("Y-m-d", strtotime($request->publish_date));
        $data['featured']      = $request->featured;
        $data['status']        = $request->has('status') ? $request->status : '0';
        if ($request->hasFile('pdf_url')) {
            $data['pdf_url']      = $request->pdf_url;
        }
        $journal = Journal::create($data);

        

    }

    /**
     * Edit the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(Request $request, $id)
    {
       
        $journal = Journal::select('id','user_id','title','pdf_url','description','status','featured','publish_date')->where('user_id',$request->user()->id)->orderBy('publish_date','ASC')->FindOrFail($id);

        if (is_null($journal)){                        
            return $this->sendResponse($journal, trans('en_lang.DATA_NOT_FOUND'),404);
        }
        return $this->sendResponse($journal, trans('en_lang.DATA_FOUND'),200);
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
        $journal = Journal::select('id','user_id','Journal','from_year','from_where','updated_at')->where('user_id',$request->user()->id)->find($id);

        if (is_null($journal)) {
            return $this->sendResponse($journal, trans('en_lang.DATA_NOT_FOUND'),404);
        }

        return $this->sendResponse($journal, trans('en_lang.DATA_FOUND'),200);
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
            'title' => 'required',
        ]);

        if($validator->fails()){
            return $this->sendError('Validation Error.', $validator->errors(),422);      
        }

        $journal = Journal::where('user_id',$request->user()->id)->find($id);
        
        if($request->hasFile('pdf_url'))
        {
            $request->validate([
             'pdf_url'  => 'required|mimes:doc,docx,pdf,txt',
            ]);

            DeleteOldPicture($journal->pdf_url);
            $uploaded_file = rand().'_'.$request->user()->id.'.'.$request->file('pdf_url')->guessExtension();
            $request->pdf_url = $request->file('pdf_url')->storeAs('file/journal',$uploaded_file,'public');
        }

        $journal->user_id        = $request->user()->id;
        $journal->title          = $request->title;
        $journal->pdf_url        = $request->pdf_url;
        $journal->status         = $request->has('status') ? $request->status : '0' ;
        $journal->description    = $request->description;
        $journal->featured       = $request->featured;
        $journal->publish_date   = date("Y-m-d", strtotime($request->publish_date));

        $journal->save();

        return $this->sendResponse($journal,trans('en_lang.DATA_UPDATED'),200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, $id)
    {
       
        $delete = Journal::where('user_id',$request->user()->id)->find($id);  
        $delete->delete();

        return $this->sendResponse('Journal successfully deleted', trans('en_lang.DATA_DELETED'),204);
    }
}
