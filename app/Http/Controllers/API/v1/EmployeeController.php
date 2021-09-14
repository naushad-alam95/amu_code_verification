<?php

namespace App\Http\Controllers\API\v1;

use App\Http\Controllers\API\v1\BaseController as BaseController;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
Use App\AcademicAndNonAcademic;
use App\MappingDepartments;
Use App\UserVisibility;
Use App\RelatedLink;
Use App\RelatedLinkData;
Use App\GroupLink;
use App\RevokeMessage;
use App\User;
use Validator;
use Auth;
use DB;

class EmployeeController extends BaseController
{

    /********Teaching staff Functions Start************/
    public function getTeachingStaffDetails(Request $request) {
        $validator = Validator::make($request->all(), [
            'lang' => 'required|in:en,hi,ur',
        ]);
        $input = $request->all();
        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors(),422);
        } else {
            $locale='';
            if(isset($request->lang) && $request->lang=='ur' ){
                $locale='ur';
            }else if(isset($request->lang) && $request->lang=='hi' ){
                $locale='hi';
            }else{
                $locale='en';
            }
            app()->setLocale($locale); 
            try {
                switch ($locale) {
                    case "ur": //GET widget IN Urdu
                        $dataUR = array();                             
                        $employee = User::select('id','url','eid','title','first_name','middle_name','last_name','dob','profile','for_id','slug','image','cv')->where('slug','=',$request->slug)->with('getDepartment.getDesignation','getContact','getStudyMaterialFile','getStudyMaterialVideo','getThrustArea','getQualification', 'getJournal','getKeyJournal','getTimeTable')->first();                       
                        if($employee->image !=NULL){
                           $employee['image'] = asset('storage').$employee->image;
                        }else{
                           $employee['image'] = asset('storage').'/images/default-img.png';
                        }
                        $core = explode('/',$employee->url);
                        $academic =  AcademicAndNonAcademic::where('slug',$core[1])->with('getSubType:id,title')->first();
                        $id = $academic->id;
                        if ($academic->sub_type == '2' || $academic->sub_type == '22') {
                            $id = academicRelatedLink($academic->id, $locale);
                            $results = json_decode($id->content(), true);
                            if ($results['code'] == 1000) {
                                $employee['section_type']= 'academic';
                                $employee['sub_type']= strtolower($academic->getSubType->title);
                                $employee['sub_type_slug']= str_slug($academic->getSubType->title, '-');
                                $employee['section_name']= ucfirst($academic->title_en);
                                $employee['head']=$results['head'];
                                $employee['links']=$results['links'];

                            }                            
                        }else{
                            $type = str_slug($academic->getSubType->title, '-');
                            $id = nonacRelatedLink($academic->id, $academic->head, $locale, $type);
                            $results = json_decode($id->content(), true);
                            if ($results['code'] == 1000) {
                                $employee['section_type']= 'non-academic';
                                $employee['sub_type']= strtolower($academic->getSubType->title);
                                $employee['sub_type_slug']= str_slug($academic->getSubType->title, '-');
                                $employee['section_name']= ucfirst($academic->title_en);
                                $employee['head']=$results['head'] ;
                                $employee['links']=$results['links'];
                                if (!empty($results['customlinks'])) {
                                    $employee['customlinks']=$results['customlinks'];
                                }
                                if (!empty($results['section'])) {
                                    $employee['section']=$results['section'];
                                }
                            }
                        }

                        if (empty($employee)){                        
                            return $this->sendResponse($employee, trans($locale.'_lang.DATA_NOT_FOUND'),404);
                        }
                        $dataUR = $employee;
                        if ($dataUR){                        
                            return $this->sendResponse($dataUR, trans($locale.'_lang.DATA_FOUND'),200);
                        } else {
                            return $this->sendResponse($dataUR, trans($locale.'_lang.DATA_NOT_FOUND'),404);
                        }
                        break;
                    case "hi": //GET widget IN Hindi
                        $dataHI = array();
                        $employee = User::select('id','url','eid','title','first_name','middle_name','last_name','dob','profile','for_id','slug','image','cv')->where('slug','=',$request->slug)->with('getDepartment.getDesignation','getContact','getStudyMaterialFile','getStudyMaterialVideo','getThrustArea','getQualification', 'getJournal','getKeyJournal','getTimeTable')->first();

                        if($employee->image !=NULL){
                           $employee['image'] = asset('storage').$employee->image;
                        }else{
                           $employee['image'] = asset('storage').'/images/default-img.png';
                        }
                        $core = explode('/',$employee->url);
                        $academic =  AcademicAndNonAcademic::where('slug',$core[1])->with('getSubType:id,title')->first();
                        $id = $academic->id;
                        if ($academic->sub_type == '2' || $academic->sub_type == '22') {
                            $id = academicRelatedLink($academic->id, $locale);
                            $results = json_decode($id->content(), true);
                            if ($results['code'] == 1000) {
                                $employee['section_type']= 'academic';
                                $employee['sub_type']= strtolower($academic->getSubType->title);
                                $employee['sub_type_slug']= str_slug($academic->getSubType->title, '-');
                                $employee['section_name']= ucfirst($academic->title_en);
                                $employee['head']=$results['head'] ;
                                $employee['links']=$results['links'];
                            }                            
                        }else{
                            $type = str_slug($academic->getSubType->title, '-');;
                            $id = nonacRelatedLink($academic->id, $academic->head, $locale, $type);
                            $results = json_decode($id->content(), true);
                            if ($results['code'] == 1000) {
                                $employee['section_type']= 'non-academic';
                                $employee['sub_type']= strtolower($academic->getSubType->title);
                                $employee['sub_type_slug']= str_slug($academic->getSubType->title, '-');
                                $employee['section_name']= ucfirst($academic->title_en);
                                $employee['head']=$results['head'] ;
                                $employee['links']=$results['links'];
                                if (!empty($results['customlinks'])) {
                                    $employee['customlinks']=$results['customlinks'];
                                }
                                if (!empty($results['section'])) {
                                    $employee['section']=$results['section'];
                                }
                            }
                        }


                        if (empty($employee)){                        
                            return $this->sendResponse($employee, trans($locale.'_lang.DATA_NOT_FOUND'),404);
                        }
                        $dataHI = $employee;
                        if ($dataHI){                        
                            return $this->sendResponse($dataHI, trans($locale.'_lang.DATA_FOUND'),200);
                        } else {
                            return $this->sendResponse($dataHI, trans($locale.'_lang.DATA_NOT_FOUND'),404);
                        }
                        break;
                    default: //GET widget IN English
                        $data = array();
                        $employee = User::select('id','url','eid','title','first_name','middle_name','last_name','dob','profile','for_id','slug','image','cv')->where('slug','=',$request->slug)->with('getDepartment.getDesignation','getContact','getStudyMaterialFile','getStudyMaterialVideo','getThrustArea','getQualification', 'getJournal','getKeyJournal','getTimeTable')->first();
                        if($employee->image !=NULL){
                           $employee['image'] = asset('storage').$employee->image;
                        }else{
                           $employee['image'] = asset('storage').'/images/default-img.png';
                        }
                        $core = explode('/',$employee->url);
                        $academic =  AcademicAndNonAcademic::where('slug',$core[1])->with('getSubType:id,title')->first();
                        if ($academic->sub_type == '2' || $academic->sub_type == '22') {
                            $id = academicRelatedLink($academic->id, $locale);
                            $results = json_decode($id->content(), true);
                            if ($results['code'] == 1000) {
                                $employee['section_type']= 'academic';
                                $employee['sub_type']= strtolower($academic->getSubType->title);
                                $employee['sub_type_slug']= str_slug($academic->getSubType->title, '-');
                                $employee['section_name']= ucfirst($academic->title_en);
                                $employee['head']=$results['head'] ;
                                $employee['links']=$results['links'];
                            }                            
                        }else{
                            $type = str_slug($academic->getSubType->title, '-');
                            $id = nonacRelatedLink($academic->id, $academic->head, $locale, $type);
                            $results = json_decode($id->content(), true);
                            if ($results['code'] == 1000) {
                                $employee['section_type']= 'non-academic';
                                $employee['sub_type']= strtolower($academic->getSubType->title);
                                $employee['sub_type_slug']= str_slug($academic->getSubType->title, '-');
                                $employee['section_name']= ucfirst($academic->title_en);
                                $employee['head']=$results['head'] ;
                                $employee['links']=$results['links'];
                                if (!empty($results['customlinks'])) {
                                    $employee['customlinks']=$results['customlinks'];
                                }
                                if (!empty($results['section'])) {
                                    $employee['section']=$results['section'];
                                }
                            }
                        }
                       
                        if (empty($employee)){                        
                            return $this->sendResponse($employee, trans($locale.'_lang.DATA_NOT_FOUND'),404);
                        }
                        $data = $employee;
                         
                        if ($data){                        
                            return $this->sendResponse($data, trans($locale.'_lang.DATA_FOUND'),200);
                        } else {
                            return $this->sendResponse($data, trans($locale.'_lang.DATA_NOT_FOUND'),404);
                        }
                        break;
                }
            }catch (Exception $e) {
               return $this->sendError(trans($locale.'_lang.MODEL_NOT_FOUND'), $e->getMessage(), 404);
            }
        }
    }

    /*
    * Public user data search api
    */
    public function publicUserSearch(Request $request){
        if ($request->isMethod('get')){
            $validator = Validator::make($request->all(), [                
                'keyword' => 'required',
            ]);
            
            if($validator->fails()){
                return $this->sendError('Validation Error.', $validator->errors(),422);      
            }

            $input = $request->all();
            $name = $request->keyword;
            $users = User::select('id','title','first_name','middle_name','last_name','url','status','image','created_at')->where('status','=','1')->where('eid','!=','')
            ->where(function($query) use ($name){
                $query->orWhere('eid', 'LIKE', '%'.$name.'%');
                $query->orWhere('title', 'LIKE', '%'.$name.'%');
                $query->orWhere('first_name', 'LIKE', '%'.$name.'%');
                $query->orWhere('last_name', 'LIKE', '%'.$name.'%');
                $query->orWhere('middle_name', 'LIKE', '%'.$name.'%');
                $query->orWhereRaw("CONCAT(`first_name`, ' ', `last_name`) LIKE ?", ['%'.$name.'%']);
                $query->orWhereRaw("CONCAT(`first_name`, ' ', `middle_name`, ' ', `last_name`) LIKE ?", ['%'.$name.'%']);
            })
            ->with('getDepartment.getDesignation:id,name','getDepartment.getAcAndNonAc:id,title_en')->orderBy('created_at','desc')->get();

            $data = array();
            foreach ($users as $key => $value) { 

                
                $data[$key]['id'] = $value->id;
                $data[$key]['title'] = $value->title;
                $data[$key]['first_name'] = $value->first_name;
                $data[$key]['middle_name'] = $value->middle_name;
                $data[$key]['last_name'] = $value->last_name;
                $data[$key]['url'] = $value->url;
                $data[$key]['status'] = $value->status;
                $data[$key]['image'] = $value->image;
                if($value->getDepartment != null){
                    if ($value->getDepartment->getDesignation) {
                        $data[$key]['designations'] = $value->getDepartment->getDesignation->name;
                    }
                    if ($value->getDepartment->getAcAndNonAc) {
                       $data[$key]['department'] = $value->getDepartment->getAcAndNonAc->title_en;
                    }
                }else{
                   $data[$key]['designations']  = '';
                   $data[$key]['department']    = '';
                }            
                
                $data[$key]['created_at'] = $value->created_at;
            }
            
            if (is_null($data)){                        
                return $this->sendResponse($data, trans('en_lang.DATA_NOT_FOUND'),404);
            }
                return $this->sendResponse($data, trans('en_lang.DATA_FOUND'),200); 

        }    
        
    }

    /**Get Department Members By Faculty List**/
    public function getFacultyDepMembers(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'lang' => 'required|in:en,hi,ur',
        ]);
            
        if($validator->fails()){
            return $this->sendError('Validation Error.', $validator->errors(),422);      
        }

        $faculty  = AcademicAndNonAcademic::select('id','title_en As title')->where('sub_type',1)->where('status',1)
            ->with(['getMapId' => function($q){
                $q->with(['mapDepartments' => function($q){
                $q->with(['getLink' => function($q){  
                $q->where('link_name_en', 'LIKE', 'Faculty Members');
                
                }]);
                
                }]);
            }])->orderBy('title','ASC')->orderBy('id','ASC')->get();

        if (is_null($faculty)){                        
            return $this->sendResponse($faculty, trans('en_lang.DATA_NOT_FOUND'),404);
        }
        return $this->sendResponse($faculty, trans('en_lang.DATA_FOUND'),200);
    }


    public function getRevokeMessage(Request $request)
    {
       
        $message = RevokeMessage::first();
        if (is_null($message)){                        
            return $this->sendResponse($message, trans('en_lang.DATA_NOT_FOUND'),404);
        }
        return $this->sendResponse($message, trans('en_lang.DATA_FOUND'),200);
    }
}