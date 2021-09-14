<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\API\v1\BaseController as BaseController;
use App\Http\Controllers\Controller;
use App\AcademicAndNonAcademic;
use Illuminate\Http\Request;
use App\SubType;
use App\Job;
use Validator;

class JobController extends BaseController
{

   public function index(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'lang' => 'required|in:en,hi,ur',
        ]);
        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors(),422);
        }

        $data = array();
        $Job = Job::/*where('approval_status','approved')->*/orderBy('created_at','desc')->orderBy('title','asc')
                ->when(request()->ac_non_ac_id, function ($query) {
                    $query->where('ac_non_ac_id', '=' , request()->ac_non_ac_id);
                })->when(request()->search_keyword, function ($query) {
                    $query->where('title', 'LIKE', '%'.request()->search_keyword.'%');
                })->paginate(env('ITEM_PER_PAGE'));


        if (empty($Job)){                        
            return $this->sendResponse($Job, trans('en_lang.DATA_NOT_FOUND'),404);
        }
        
        $data['data'] = $Job;

        if(request()->ac_non_ac_id){
            $data['ac_non_ac_id'] = request()->ac_non_ac_id;
        }else{
            $data['ac_non_ac_id'] = '';
        }
        if(request()->search_keyword){
            $data['search_keyword'] = request()->search_keyword;
        }else{
            $data['search_keyword'] = '';
        }
        if ($data){                        
            return $this->sendResponse($data, trans('en_lang.DATA_FOUND'),200);
        } else {
            return $this->sendResponse($data, trans('en_lang.DATA_NOT_FOUND'),404);
        }        
    }

    /*
    Get Academic Types
    */
    public function getAcademicType(Request $request)
    {
        $types = SubType::select('id','title')->orderby('title', 'ASC')->get();

        if (is_null($types)){                        
            return $this->sendResponse($types, trans('en_lang.DATA_NOT_FOUND'),404);
        }
        return $this->sendResponse($types, trans('en_lang.DATA_FOUND'),200);
    }

    /*
    Get Academic and Non Academic List
    */
    public function getAcNonAcList(Request $request)
    {
        $validator = Validator::make($request->all(), [
                'lang' => 'required|in:en,hi,ur',
                'type_id' => 'required|integer',
            ]);


            
        if($validator->fails()){
            return $this->sendError('Validation Error.', $validator->errors(),422);      
        }
        if (request()->type_id == 0) {
           
           $data = AcademicAndNonAcademic::select('id','title_en As title','sub_type')->where('status','1')->orderby('title_en', 'ASC')->get();
        }else{
            $data = AcademicAndNonAcademic::select('id','title_en As title','sub_type')->where('sub_type','=',request()->type_id)->where('status','1')->orderby('title_en', 'ASC')->get();
        }
        

        if (is_null($data)){                        
            return $this->sendResponse($data, trans('en_lang.DATA_NOT_FOUND'),404);
        }
        return $this->sendResponse($data, trans('en_lang.DATA_FOUND'),200);
    } 
    
}
