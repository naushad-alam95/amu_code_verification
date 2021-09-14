<?php

namespace App\Http\Controllers\API\v1;

use App\Http\Controllers\API\v1\BaseController as BaseController;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
Use App\AcademicAndNonAcademic;
use App\CustomRelatedLink;
Use App\UserVisibility;
Use App\RelatedLink;
Use App\RelatedLinkData;
use App\AcNonAcGallery;
use App\MappingDepartments;
Use App\GroupLink;
Use App\UserLog;
Use App\Course;
Use App\OnGoingProject;
Use App\CompletedProject;
Use App\ResearchScholars;
Use App\FormerChairPerson;
Use App\NoticeCircular;
use App\Notification;
use App\Alumni;
use Validator;
use Auth;
use Log;

class AcademicController extends BaseController
{

    // Get department's related name.
    public function getRelatedlinkTitle(Request $request)
    {

        $input = $request->all();
        $validator = Validator::make($request->all(), [
            'lang' => 'required|in:en,hi,ur',
            'slug' => 'required',
            'type' => 'required',
            'path' => 'required',

        ]);
        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors(), 422, false);
        }else {
            $locale='';
            if(isset($request->lang) && $request->lang=='ur' ){
                $locale='ur';
            }else if(isset($request->lang) && $request->lang=='hi' ){
                $locale='hi';
            }else{
                $locale='en';
            }
            app()->setLocale($locale); 

            $academic =  AcademicAndNonAcademic::where('slug', request()->slug)->first();
            $ac_non_ac_id = $academic->id;
            if ($academic->type == '1') {
                $slug = request()->type.'/'.request()->slug.'/'.request()->path;
            }else{
                 $slug = request()->slug.'/'.request()->path;
            }
            $rlink = RelatedLink::select('id','ac_non_ac_id', 'link_name_en', 'link_name_hi', 'link_name_ur')->where('slug', $slug)->with('getAcAndNonAc')->first();
            if (is_null($rlink)) {
                return $this->sendResponse($rlink, 'Path Not Found', 404, false);
            }
            try {
                switch ($locale) {
                    case "ur": //GET widget IN Urdu
                        $dataUR = array(); 
                        if($rlink->link_name_ur == NULL){
                            $dataUR['title'] = ucfirst($rlink->link_name_en);  
                        }else{
                            $dataUR['title'] = ucfirst($rlink->link_name_ur);
                        }
                        $seo_title = $rlink->link_name_en.' - '.$rlink->getAcAndNonAc->title_en;
                        $dataUR['seo_title'] = $seo_title.' | AMU';                         
                        if ($dataUR){                        
                            return $this->sendResponse($dataUR, trans($locale.'_lang.DATA_FOUND'),200);
                        } else {
                            return $this->sendResponse($dataUR, trans($locale.'_lang.DATA_NOT_FOUND'),404);
                        }
                        break;
                    case "hi": //GET widget IN Hindi
                        $dataHI = array();
                        if($rlink->link_name_hi == NULL){
                            $dataHI['title'] = ucfirst($rlink->link_name_en);  
                        }else{
                            $dataHI['title'] = ucfirst($rlink->link_name_hi);
                        }
                        $seo_title = $rlink->link_name_en.' - '.$rlink->getAcAndNonAc->title_en;
                        $dataHI['seo_title'] = $seo_title.' | AMU';  
                        if ($dataHI){                        
                            return $this->sendResponse($dataHI, trans($locale.'_lang.DATA_FOUND'),200);
                        } else {
                            return $this->sendResponse($dataHI, trans($locale.'_lang.DATA_NOT_FOUND'),404);
                        }
                        break;
                    default: //GET widget IN English
                        $data = array();
                        $data['title'] = ucfirst($rlink->link_name_en);  
                        $seo_title = $rlink->link_name_en.' - '.$rlink->getAcAndNonAc->title_en;
                        $data['seo_title'] = $seo_title.' | AMU'; 
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

    /********Department Functions Start************/
    public function getAcademicList(Request $request) {
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
                        $departments = AcademicAndNonAcademic::orderBy('title_en','asc')->where('sub_type', '2')->where('status',1)->get();
                        if (empty($departments)){                        
                            return $this->sendResponse($departments, trans($locale.'_lang.DATA_NOT_FOUND'),404);
                        }
                        foreach ($departments as $key => $department) {
                            $dataUR[$key]['id'] = $department->id;
                            if($department->title_ur == NULL){
                                $dataUR[$key]['title'] = $department->title_en;  
                            }else{
                                $dataUR[$key]['title'] = $department->title_ur; 
                            }                            
                            $dataUR[$key]['slug'] = $department->slug;
                        }
                        if ($dataUR){                        
                            return $this->sendResponse($dataUR, trans($locale.'_lang.DATA_FOUND'),200);
                        } else {
                            return $this->sendResponse($dataUR, trans($locale.'_lang.DATA_NOT_FOUND'),404);
                        }
                        break;
                    case "hi": //GET widget IN Hindi
                        $dataHI = array();
                        $departments = AcademicAndNonAcademic::orderBy('title_en','asc')->where('sub_type', '2')->where('status',1)->get();
                        if (empty($departments)){                        
                            return $this->sendResponse($departments, trans($locale.'_lang.DATA_NOT_FOUND'),404);
                        }
                        foreach ($departments as $key => $department) {
                            $dataHI[$key]['id'] = $department->id;
                            if($department->title_hi == NULL){
                                $dataHI[$key]['title'] = $department->title_en;  
                            }else{
                                $dataHI[$key]['title'] = $department->title_hi; 
                            }                            
                            $dataHI[$key]['slug'] = $department->slug;
                        }
                        if ($dataHI){                        
                            return $this->sendResponse($dataHI, trans($locale.'_lang.DATA_FOUND'),200);
                        } else {
                            return $this->sendResponse($dataHI, trans($locale.'_lang.DATA_NOT_FOUND'),404);
                        }
                        break;
                    default: //GET widget IN English
                        $data = array();
                        $departments = AcademicAndNonAcademic::orderBy('title_en','asc')->where('sub_type', '2')->where('status',1)->get();

                        if (empty($departments)){                        
                            return $this->sendResponse($departments, trans($locale.'_lang.DATA_NOT_FOUND'),404);
                        }
                        foreach ($departments as $key => $department) {
                            $data[$key]['id'] = $department->id;
                            $data[$key]['title'] = $department->title_en;                            
                            $data[$key]['slug'] = $department->slug;
                        }
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

    public function getDepartmentDetail(Request $request){
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
                        $academic =  AcademicAndNonAcademic::where('slug',request()->slug)->with('getSubType:id,title,slug','getHeadType:id,title,role_type')->first();
                        $id = $academic->id;
                        $chairman = array();
                        $userData = UserVisibility::where('ac_non_ac_id',$id)->where('role_id',$academic->head)->with('getUser.getContact','getDesignation','getRole:id,title')->first();
                        if (!empty($userData)) {
                            $chairman['url'] = $userData->getUser->url;
                            $chairman['slug'] = $userData->getUser->slug;
                            $chairman['title'] = $userData->getUser->title;
                            $chairman['first_name'] = $userData->getUser->first_name;
                            $chairman['last_name'] = $userData->getUser->last_name;
                            $chairman['middle_name'] = $userData->getUser->middle_name;
                            if($userData->getUser->image !=NULL){
                               $userImage = asset('storage').$userData->getUser->image;
                            }else{
                               $userImage = asset('storage').'/images/default-img.png';
                            }
                            $chairman['image'] = $userImage;
                            
                            $chairman['designation'] = $userData->getRole->title . ' and ' . $userData->getDesignation->name;
                        }else{
                            $chairman['image'] = asset('storage').'/images/default-img.png';
                        }
                        
                        
                        $aboutSlug = $academic->getSubType->slug.'/'.request()->slug.'/about-the-'.$academic->getSubType->slug;
                        $aboutId = RelatedLink::where('slug',$aboutSlug)->pluck('id');
                        $about = array();
                        if ($aboutId) {
                            $aboutData = RelatedLinkData::where('related_link_id',$aboutId)->with('getImages')->first();
                            if ($aboutData) {
                                $about['id'] = $aboutData->id;
                                if($aboutData->link_ur == NULL){
                                    $about['link'] = $aboutData->link_en; 
                                }else{ 
                                    $about['link'] = $aboutData->link_ur; 
                                }
                                if($aboutData->link_description_ur == NULL){
                                    $about['link_description'] = $aboutData->link_description_en;
                                }else{
                                    $about['link_description'] = $aboutData->link_description_ur;
                                }
                                $latest_update = UserLog::select('updated_at')->where('ac_non_ac_id',$id)->orderBy('updated_at','DESC')->first();
                                if ($latest_update) {
                                    $about['updated_at'] = $latest_update->updated_at;
                                }else{
                                    $about['updated_at'] = '';
                                }
                                if ($aboutData->getImages->count()) {
                                    $about['slider'] = $aboutData->getImages;
                                }else{
                                    $about['slider'][0] = array('image' => '/images/amu-aligarh2.jpg');
                                }
                            }
                        }
                        $grouplinkcount = RelatedLink::select('group_type_id')->groupBy('group_type_id')->where('ac_non_ac_id',$id)->get()->toArray();
                        $grouplink = array_column($grouplinkcount, 'group_type_id');
                        $links = GroupLink::select('title_ur as title','id')->whereIn('id', $grouplink)
                                ->with(['getRelatedLink' => function($q)  use($id){ 
                                  $q->where('ac_non_ac_id', '=',$id);
                                  $q->orderBy('link_order', 'ASC');}])
                                ->get();
                        if ($academic->getSubType->slug == 'department') {
                            if($id == 47 || $id == 96 || $id == 100 ||  $id == 406 || $id == 407  ) {
                                if($academic->title_ur == NULL){
                                    $dataUR['title'] = $academic->title_en;
                                }else{ 
                                    $dataUR['title']= $academic->title_ur;
                                }
                                
                            }else{
                                if($academic->title_ur == NULL){
                                    $dataUR['title'] =  trans('en_lang.DEPARTMENT_OF') . $academic->title_en;
                                }else{ 
                                    $dataUR['title']= $academic->title_ur . trans('ur_lang.DEPARTMENT_OF');
                                }
                            }

                              
                        }else{
                            $dataUR['title'] = $academic->title_en;

                            //Get other section data
                            $mapdata =  MappingDepartments::where('dep_id',$id)->with('parentDepartments:id,title_en,slug,sub_type','parentDepartments.getSubType:id,title,slug')->first();
                            if ($mapdata) {
                                $section = array();
                                if ($mapdata->parentDepartments) {
                                    if($mapdata->parentDepartments->title_ur == NULL){
                                        $section['title'] = $mapdata->parentDepartments->title_en;
                                    }else{ 
                                        $section['title'] = $mapdata->parentDepartments->title_ur;
                                    }
                                     
                                    $section['slug'] = $mapdata->parentDepartments->slug;
                                }
                               $dataUR['section_from']= $section;
                            }
                        }

                        //Get Custom link
                        $customlinks = array(); 
                        $cus_rel_Links = CustomRelatedLink::orderBy('order_on','asc')->orderBy('created_at','desc')->where('ac_non_ac_id',$id)->get();
                        if ($cus_rel_Links) {
                            foreach ($cus_rel_Links as $key => $value) {
                                $customlinks[$key]['id'] = $value->id;
                                if($value->link_name_ur == NULL){
                                    $customlinks[$key]['cus_link'] = $value->link_name_en; 
                                }else{ 
                                    $customlinks[$key]['cus_link'] = $value->link_name_ur; 
                                }
                    
                                $customlinks[$key]['rel_slug'] = $value->rel_slug;
                            }
                            $dataUR['customlinks']=$customlinks;
                        }
                              
                        $seo = array();                      
                        $seo['meta_title'] = $academic->meta_title;
                        $seo['meta_description'] = $academic->meta_description;
                        $seo['meta_keyword'] = $academic->meta_keyword;
                        $seo['og_image'] = $academic->image;
                        $dataUR['seo'] = $seo;        
                        $dataUR['links']=$links; 
                        $dataUR['about']=$about;
                        $dataUR['head']=$chairman;
                        $dataUR['head_login'] = $academic->getHeadType; 
                        $dataUR['sub_type'] = $academic->getSubType;                       
                        if ($dataUR){                        
                            return $this->sendResponse($dataUR, trans($locale.'_lang.DATA_FOUND'),200);
                        } else {
                            return $this->sendResponse($dataUR, trans($locale.'_lang.DATA_NOT_FOUND'),404);
                        }
                        break;
                    case "hi": //GET widget IN Hindi
                        $dataHI = array();
                        $academic =  AcademicAndNonAcademic::where('slug',request()->slug)->with('getSubType:id,title,slug','getHeadType:id,title,role_type')->first();
                        $id = $academic->id;
                        $chairman = array();
                        $userData = UserVisibility::where('ac_non_ac_id',$id)->where('role_id',$academic->head)->with('getUser.getContact','getDesignation','getRole:id,title')->first();

                        if (!empty($userData)) {
                            $chairman['url'] = $userData->getUser->url;
                            $chairman['slug'] = $userData->getUser->slug;
                            $chairman['title'] = $userData->getUser->title;
                            $chairman['first_name'] = $userData->getUser->first_name;
                            $chairman['last_name'] = $userData->getUser->last_name;
                            $chairman['middle_name'] = $userData->getUser->middle_name;
                            if($userData->getUser->image !=NULL){
                               $userImage = asset('storage').$userData->getUser->image;
                            }else{
                               $userImage = asset('storage').'/images/default-img.png';
                            }
                            $chairman['image'] = $userImage;
                            $chairman['designation'] = $userData->getRole->title . ' and ' . $userData->getDesignation->name;
                        }else{
                            $chairman['image'] = asset('storage').'/images/default-img.png';
                        }



                        
                        $aboutSlug = $academic->getSubType->slug.'/'.request()->slug.'/about-the-'.$academic->getSubType->slug;
                        $aboutId = RelatedLink::where('slug',$aboutSlug)->pluck('id');
                        $about = array();
                        if ($aboutId) {
                            $aboutData = RelatedLinkData::where('related_link_id',$aboutId)->with('getImages')->first();
                            if ($aboutData) {
                                $about['id'] = $aboutData->id;
                                if($aboutData->link_hi == NULL){
                                    $about['link'] = $aboutData->link_en; 
                                }else{ 
                                    $about['link'] = $aboutData->link_hi; 
                                }
                                if($aboutData->link_description_hi == NULL){
                                    $about['link_description'] = $aboutData->link_description_en;
                                }else{
                                    $about['link_description'] = $aboutData->link_description_hi;
                                }
                                $latest_update = UserLog::select('updated_at')->where('ac_non_ac_id',$id)->orderBy('updated_at','DESC')->first();
                                if ($latest_update) {
                                    $about['updated_at'] = $latest_update->updated_at;
                                }else{
                                    $about['updated_at'] = '';
                                }
                                if ($aboutData->getImages->count()) {
                                    $about['slider'] = $aboutData->getImages;
                                }else{
                                    $about['slider'][0] = array('image' => '/images/amu-aligarh2.jpg');
                                }
                            }
                        }
                        $grouplinkcount = RelatedLink::select('group_type_id')->groupBy('group_type_id')->where('ac_non_ac_id',$id)->get()->toArray();
                        $grouplink = array_column($grouplinkcount, 'group_type_id');
                        $links = GroupLink::select('title_hi as title','id')->whereIn('id', $grouplink)
                                ->with(['getRelatedLink' => function($q)  use($id){ 
                                  $q->where('ac_non_ac_id', '=',$id);
                                  $q->orderBy('link_order', 'ASC');}])
                                ->get();
                        if ($academic->getSubType->slug == 'department') {
                            if($id == 47 || $id == 96 || $id == 100 ||  $id == 406 || $id == 407  ) {
                                if($academic->title_hi == NULL){
                                    $dataHI['title'] =  $academic->title_en;
                                }else{ 
                                    $dataHI['title']= $academic->title_hi;
                                }

                            }else{
                                if($academic->title_hi == NULL){
                                    $dataHI['title'] =  trans('en_lang.DEPARTMENT_OF') . $academic->title_en;
                                }else{ 
                                    $dataHI['title']= $academic->title_hi . trans('hi_lang.DEPARTMENT_OF');
                                } 
                            }
                              
                        }else{
                            $dataHI['title'] = $academic->title_en;

                            //Get other section data
                            $mapdata =  MappingDepartments::where('dep_id',$id)->with('parentDepartments:id,title_en,slug,sub_type','parentDepartments.getSubType:id,title,slug')->first();
                            if ($mapdata) {
                                $section = array();
                                if ($mapdata->parentDepartments) {
                                    if($mapdata->parentDepartments->title_hi == NULL){
                                        $section['title'] = $mapdata->parentDepartments->title_en;
                                    }else{ 
                                        $section['title'] = $mapdata->parentDepartments->title_hi;
                                    }
                                     
                                    $section['slug'] = $mapdata->parentDepartments->slug;
                                }
                               $dataHI['section_from']= $section;
                            }
                        }

                        //Get Custom link
                        $customlinks = array(); 
                        $cus_rel_Links = CustomRelatedLink::orderBy('order_on','asc')->orderBy('created_at','desc')->where('ac_non_ac_id',$id)->get();
                        if ($cus_rel_Links) {
                            foreach ($cus_rel_Links as $key => $value) {
                                $customlinks[$key]['id'] = $value->id;
                                if($value->link_name_hi == NULL){
                                    $customlinks[$key]['cus_link'] = $value->link_name_en; 
                                }else{ 
                                    $customlinks[$key]['cus_link'] = $value->link_name_hi; 
                                }
                    
                                $customlinks[$key]['rel_slug'] = $value->rel_slug;
                            }
                            $dataHI['customlinks']=$customlinks;
                        }
                                
                        
                        $seo = array();                      
                        $seo['meta_title'] = $academic->meta_title;
                        $seo['meta_description'] = $academic->meta_description;
                        $seo['meta_keyword'] = $academic->meta_keyword;
                        $seo['og_image'] = $academic->image;
                        $dataHI['seo'] = $seo;      
                        $dataHI['links']=$links; 
                        $dataHI['about']=$about;
                        $dataHI['head']=$chairman; 
                        $dataHI['head_login'] = $academic->getHeadType;
                        $dataHI['sub_type'] = $academic->getSubType; 
                        if ($dataHI){                        
                            return $this->sendResponse($dataHI, trans($locale.'_lang.DATA_FOUND'),200);
                        } else {
                            return $this->sendResponse($dataHI, trans($locale.'_lang.DATA_NOT_FOUND'),404);
                        }
                        break;
                    default: //GET widget IN English
                        $data = array();
                        $academic =  AcademicAndNonAcademic::where('slug',request()->slug)->with('getSubType:id,title,slug','getHeadType:id,title,role_type')->first();

                        $sub_type_slug = $academic->getSubType->slug;
                        $id = $academic->id;

                            $chairman = array();
                            $userData = UserVisibility::where('ac_non_ac_id',$id)->where('role_id',$academic->head)->with('getUser.getContact','getDesignation','getRole:id,title')->first();
                            if (!empty($userData)) {
                                $chairman['url'] = $userData->getUser->url;
                                $chairman['slug'] = $userData->getUser->slug;
                                $chairman['title'] = $userData->getUser->title;
                                $chairman['first_name'] = $userData->getUser->first_name;
                                $chairman['last_name'] = $userData->getUser->last_name;
                                $chairman['middle_name'] = $userData->getUser->middle_name;
                                if($userData->getUser->image !=NULL){
                                   $userImage = asset('storage').$userData->getUser->image;
                                }else{
                                   $userImage = asset('storage').'/images/default-img.png';
                                }
                                $chairman['image'] = $userImage;
                                $chairman['designation'] = $userData->getRole->title . ' and ' . $userData->getDesignation->name;
                            }else{
                                $chairman['image'] = asset('storage').'/images/default-img.png';
                            }
                            
                        $aboutSlug = $academic->getSubType->slug.'/'.request()->slug.'/about-the-'.$academic->getSubType->slug;
                        $aboutId = RelatedLink::where('slug',$aboutSlug)->pluck('id');
                        $about = array();
                        if ($aboutId) {
                            $aboutData = RelatedLinkData::where('related_link_id',$aboutId)->with('getImages')->first();
                            if ($aboutData) {
                                $about['id'] = $aboutData->id;
                                $about['link'] = $aboutData->link_en;
                                $about['link_description'] = $aboutData->link_description_en;                                
                                $latest_update = UserLog::select('updated_at')->where('ac_non_ac_id',$id)->orderBy('updated_at','DESC')->first();
                                if ($latest_update) {
                                    $about['updated_at'] = $latest_update->updated_at;
                                }else{
                                    $about['updated_at'] = '';
                                }
                                if ($aboutData->getImages->count()) {
                                    $about['slider'] = $aboutData->getImages;
                                }else{
                                    $about['slider'][0] = array('image' => '/images/amu-aligarh2.jpg');
                                }
                                
                            }
                        }
                        
                        $grouplinkcount = RelatedLink::select('group_type_id')->groupBy('group_type_id')->where('ac_non_ac_id',$id)->get()->toArray();
                        $grouplink = array_column($grouplinkcount, 'group_type_id');
                        $links = GroupLink::select('title_en as title','id')->whereIn('id', $grouplink)
                                ->with(['getRelatedLink' => function($q)  use($id){ 
                                  $q->where('ac_non_ac_id', '=',$id);
                                  $q->orderBy('link_order', 'ASC'); }])
                                ->get();
                        $seo = array();                      
                        $seo['meta_title'] = $academic->meta_title;
                        $seo['meta_description'] = $academic->meta_description;
                        $seo['meta_keyword'] = $academic->meta_keyword;
                        $seo['og_image'] = $academic->image;
                        $data['seo'] = $seo; 
                        if ($academic->getSubType->slug == 'department') {
                            if($id == 47 || $id == 96 || $id == 100 ||  $id == 406 || $id == 407  ) {
                                $data['title'] = $academic->title_en;
                                
                            }else{
                               $data['title'] =  trans('en_lang.DEPARTMENT_OF') . $academic->title_en;  
                            }                              
                        }else{
                            $data['title'] = $academic->title_en;
                            //Get other section data
                            $mapdata =  MappingDepartments::where('dep_id',$id)->with('parentDepartments:id,title_en,slug,sub_type','parentDepartments.getSubType:id,title,slug')->first();
                            if ($mapdata) {
                                $section = array();
                                if ($mapdata->parentDepartments) {
                                    $section['title'] = $mapdata->parentDepartments->title_en; 
                                    $section['slug'] = $mapdata->parentDepartments->slug;
                                }
                               $data['section_from']= $section;
                            }
                        }

                        //Get Custom link
                        $customlinks = array(); 
                        $cus_rel_Links = CustomRelatedLink::orderBy('order_on','asc')->orderBy('created_at','desc')->where('ac_non_ac_id',$id)->get();
                        if ($cus_rel_Links) {
                            foreach ($cus_rel_Links as $key => $value) {
                                $customlinks[$key]['id'] = $value->id;
                                $customlinks[$key]['cus_link'] = $value->link_name_en;
                                $customlinks[$key]['rel_slug'] = $value->rel_slug;
                            }
                            $data['customlinks']=$customlinks;
                        }

                        $data['links']=$links;
                        $data['about']=$about; 
                        $data['head']=$chairman; 
                        $data['head_login'] = $academic->getHeadType; 
                        $data['sub_type'] = $academic->getSubType;
                        if (empty($data)){                        
                            return $this->sendResponse($departments, trans($locale.'_lang.DATA_NOT_FOUND'),404);
                        }                        
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

    public function getDepartmentLinkData(Request $request){
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
                $linkId = RelatedLink::where('slug',request()->slug)->with('getAcAndNonAc')->first();
                if (empty($linkId)){                        
                    return $this->sendResponse($linkId, trans($locale.'_lang.DATA_NOT_FOUND'),404);
                }
                $academic =  AcademicAndNonAcademic::select('id','head','sub_type')->where('id',$linkId->ac_non_ac_id)->first();

                $data = array();
                switch ($locale) {
                    case "ur": //GET widget IN Urdu
                        $dataUR = array();
                        $linkId = RelatedLink::where('slug',request()->slug)->with('getAcAndNonAc')->first();

                        if (empty($linkId)){                        
                            return $this->sendResponse($getData, trans($locale.'_lang.DATA_NOT_FOUND'),404);
                        }

                        if(strpos(request()->slug,'about-the-department') !== false || strpos(request()->slug,'contact-us') !== false || strpos(request()->slug,'about-the-section') !== false || strpos(request()->slug,'about-the-amucentres') !== false){
                            $getData = RelatedLinkData::where('related_link_id',$linkId->id)->orderBy('order_on','ASC')->first();
                             
                                $dataUR['id'] = $getData->id;
                                $dataUR['link'] = $getData->link_en; 
                                $dataUR['link_description'] = $getData->link_description_en;
                                $dataUR['file'] = $getData->file;
                                $latest_update = UserLog::select('updated_at')->where('ac_non_ac_id',$linkId->id)->orderBy('updated_at','DESC')->first();
                                if ($latest_update) {
                                    $dataUR['updated_at'] = $latest_update->updated_at;
                                }else{
                                    $dataUR['updated_at'] = '';
                                }
                           
                        }elseif(strpos(request()->slug,'faculty-members') !== false || strpos(request()->slug ,'non-teaching-staff') !== false || strpos(request()->slug ,'pg-student') !== false || strpos(request()->slug ,'retired-faculty-member') !== false){
                            if(strpos(request()->slug, 'faculty-members') !== false)
                            {
                                $faculties = UserVisibility::where('ac_non_ac_id',$linkId->ac_non_ac_id)->where('for_id','1')->where('status','1')->with('getUser.getContact','getUser.getDepartment.getAcNonAcItem','getDesignation','getSubType','getRole')->orderBy('order_on','ASC')->orderBy('role_id','ASC')->get();
                                if (empty($faculties)){                        
                                    return $this->sendResponse($getData, trans($locale.'_lang.DATA_NOT_FOUND'),404);
                                }
                                $key=0;
                                foreach ($faculties as $value) {
                                    if($value->getUser != NULL){
                                        $dataUR[$key]['user_id'] = $value->user_id;
                                        $dataUR[$key]['order_on'] = $value->order_on;
                                        $dataUR[$key]['url'] = $value->getUser->url;
                                        $dataUR[$key]['slug'] = $value->getUser->slug;
                                        $dataUR[$key]['title'] = $value->getUser->title;
                                        $dataUR[$key]['first_name'] = $value->getUser->first_name;
                                        $dataUR[$key]['last_name'] = $value->getUser->last_name;
                                        $dataUR[$key]['middle_name'] = $value->getUser->middle_name;
                                        $dataUR[$key]['status'] = $value->getUser->status;
                                        if($value->getUser->image !=NULL){
                                           $userImage = asset('storage').$value->getUser->image;
                                        }else{
                                           $userImage = asset('storage').'/images/default-img.png';
                                        }
                                        $dataUR[$key]['image'] = $userImage;
                                        if($academic->sub_type == '2' && $value->role_id == $academic->head){
                                          $dataUR[$key]['designation'] = $value->getRole->title.' and ' .$value->getDesignation->name;
                                        }else{
                                          $dataUR[$key]['designation'] = $value->getDesignation->name;  
                                        }
                                        if ($value->getUser->getContact) {
                                            foreach ($value->getUser->getContact as $val) {
                                                if ($val->email_visibility == '1') {
                                                    $dataUR[$key]['email'] = $val->email;
                                                }
                                                if ($val->mobile_visibility == '1') {
                                                    $dataUR[$key]['mobile_no'] = $val->mobile_no;
                                                }
                                                $dataUR[$key]['telephone_no'] = $val->telephone_no;
                                            }
                                        }else{
                                            $dataUR[$key]['email'] = '';
                                            $dataUR[$key]['mobile_no'] = '';
                                            $dataUR[$key]['telephone_no'] = '';
                                        }
                                        $dataUR[$key]['core_department'] = $value->getUser->getDepartment->getAcNonAcItem[0]->slug;
                                        $key++;
                                    }
                                }
                            }
                            if(strpos(request()->slug, 'non-teaching-staff') !== false) {

                                $faculties = UserVisibility::where('ac_non_ac_id',$linkId->ac_non_ac_id)->where('for_id','2')->where('status','1')->with('getUser.getContact','getUser.getDepartment.getAcNonAcItem','getDesignation')->orderBy('order_on','ASC')->orderBy('role_id','ASC')->get();
                                if (empty($faculties)){                        
                                    return $this->sendResponse($getData, trans($locale.'_lang.DATA_NOT_FOUND'),404);
                                }
                                $key=0;
                                foreach ($faculties as $value) {
                                    if($value->getUser != NULL){
                                        $dataUR[$key]['user_id'] = $value->user_id;
                                        $dataUR[$key]['order_on'] = $value->order_on;
                                        $dataUR[$key]['url'] = $value->getUser->url;
                                        $dataUR[$key]['slug'] = $value->getUser->slug;
                                        $dataUR[$key]['title'] = $value->getUser->title;
                                        $dataUR[$key]['first_name'] = $value->getUser->first_name;
                                        $dataUR[$key]['last_name'] = $value->getUser->last_name;
                                        $dataUR[$key]['middle_name'] = $value->getUser->middle_name;
                                        $dataUR[$key]['status'] = $value->getUser->status;
                                        if($value->getUser->image !=NULL){
                                           $userImage = asset('storage').$value->getUser->image;
                                        }else{
                                           $userImage = asset('storage').'/images/default-img.png';
                                        }
                                        $dataUR[$key]['image'] = $userImage;
                                        $dataUR[$key]['designation'] = $value->getDesignation->name;  
                                        
                                        if ($value->getUser->getContact) {
                                            foreach ($value->getUser->getContact as $val) {
                                                if ($val->email_visibility == '1') {
                                                    $dataUR[$key]['email'] = $val->email;
                                                }
                                                if ($val->mobile_visibility == '1') {
                                                    $dataUR[$key]['mobile_no'] = $val->mobile_no;
                                                }
                                                $dataUR[$key]['telephone_no'] = $val->telephone_no;
                                            }
                                        }else{
                                            $dataUR[$key]['email'] = '';
                                            $dataUR[$key]['mobile_no'] = '';
                                            $dataUR[$key]['telephone_no'] = '';
                                        }
                                        $dataUR[$key]['core_department'] = $value->getUser->getDepartment->getAcNonAcItem[0]->slug;
                                        $key++;
                                    }
                                }
                            }
                            if(strpos(request()->slug ,'pg-student') !== false) {

                                $faculties = UserVisibility::where('ac_non_ac_id',$linkId->ac_non_ac_id)->where('for_id','3')->where('status','1')->with('getUser.getContact','getUser.getDepartment.getAcNonAcItem','getDesignation')->orderBy('order_on','ASC')->orderBy('role_id','ASC')->get();
                                if (empty($faculties)){                        
                                    return $this->sendResponse($getData, trans($locale.'_lang.DATA_NOT_FOUND'),404);
                                }
                                $key=0;
                                foreach ($faculties as $value) {
                                    if($value->getUser != NULL){
                                        $dataUR[$key]['user_id'] = $value->user_id;
                                        $dataUR[$key]['order_on'] = $value->order_on;
                                        $dataUR[$key]['url'] = $value->getUser->url;
                                        $dataUR[$key]['slug'] = $value->getUser->slug;
                                        $dataUR[$key]['title'] = $value->getUser->title;
                                        $dataUR[$key]['first_name'] = $value->getUser->first_name;
                                        $dataUR[$key]['last_name'] = $value->getUser->last_name;
                                        $dataUR[$key]['middle_name'] = $value->getUser->middle_name;
                                        $dataUR[$key]['status'] = $value->getUser->status;
                                        if($value->getUser->image !=NULL){
                                           $userImage = asset('storage').$value->getUser->image;
                                        }else{
                                           $userImage = asset('storage').'/images/default-img.png';
                                        }
                                        $dataUR[$key]['image'] = $userImage;
                                        $dataUR[$key]['designation'] = $value->getDesignation->name;
                                        
                                        if ($value->getUser->getContact) {
                                            foreach ($value->getUser->getContact as $val) {
                                                if ($val->email_visibility == '1') {
                                                    $dataUR[$key]['email'] = $val->email;
                                                }
                                                if ($val->mobile_visibility == '1') {
                                                    $dataUR[$key]['mobile_no'] = $val->mobile_no;
                                                }
                                                $dataUR[$key]['telephone_no'] = $val->telephone_no;
                                            }
                                        }else{
                                            $dataUR[$key]['email'] = '';
                                            $dataUR[$key]['mobile_no'] = '';
                                            $dataUR[$key]['telephone_no'] = '';
                                        }
                                        $dataUR[$key]['core_department'] = $value->getUser->getDepartment->getAcNonAcItem[0]->slug;
                                        $key++;
                                    }
                                }
                            }
                            if(strpos(request()->slug ,'retired-faculty-member') !== false) {
                                $faculties = UserVisibility::where('ac_non_ac_id',$linkId->ac_non_ac_id)->where('status','2')->where('for_id','1')->with('getRetireUser.getContact','getRetireUser.getDepartment.getAcNonAcItem','getDesignation','getSubType')->orderBy('order_on','ASC')->orderBy('role_id','ASC')->get();

                                if (empty($faculties)){                        
                                    return $this->sendResponse($faculties, trans($locale.'_lang.DATA_NOT_FOUND'),404);
                                }
                                
                                $key=0;
                                foreach ($faculties as $value) {
                                    if($value->getRetireUser != NULL){
                                        $dataUR[$key]['user_id'] = $value->user_id;
                                        $dataUR[$key]['order_on'] = $value->order_on;
                                        $dataUR[$key]['type'] = strtolower($value->getSubType->title) ;
                                        $dataUR[$key]['url'] = $value->getRetireUser->url;
                                        $dataUR[$key]['slug'] = $value->getRetireUser->slug;
                                        $dataUR[$key]['title'] = $value->getRetireUser->title;
                                        $dataUR[$key]['first_name'] = $value->getRetireUser->first_name;
                                        $dataUR[$key]['middle_name'] = $value->getRetireUser->middle_name;
                                        $dataUR[$key]['last_name'] = $value->getRetireUser->last_name;
                                        $dataUR[$key]['status'] = $value->getRetireUser->status;
                                        
                                        if($value->getRetireUser->image !=NULL){
                                           $userImage = asset('storage').$value->getRetireUser->image;
                                        }else{
                                           $userImage = asset('storage').'/images/default-img.png';
                                        }
                                        $dataUR[$key]['image'] = $userImage;                                     
                                        $dataUR[$key]['designation'] = $value->getDesignation->name;
                                        
                                        if ($value->getRetireUser->getContact) {
                                            foreach ($value->getRetireUser->getContact as $val) {
                                                if ($val->email_visibility == '1') {
                                                    $dataUR[$key]['email'] = $val->email;
                                                }
                                                if ($val->mobile_visibility == '1') {
                                                    $dataUR[$key]['mobile_no'] = $val->mobile_no;
                                                }
                                                $dataUR[$key]['telephone_no'] = $val->telephone_no;
                                            }
                                        }else{
                                            $dataUR[$key]['email'] = '';
                                            $dataUR[$key]['mobile_no'] = '';
                                            $dataUR[$key]['telephone_no'] = '';
                                        }
                                        $key++;

                                    }
                                }
                            }
                        }elseif(strpos(request()->slug,'list-of-former-chairperson') !== false){
                            $getData = FormerChairPerson::/*where('approval_status','Approved')->*/where('ac_non_ac_id',$linkId->ac_non_ac_id)->orderBy('order_on','ASC')->get();
                            
                            foreach ($getData as $key => $value) {
                                $dataUR[$key]['id'] = $value->id;
                                if($value->name_ur == NULL){
                                    $dataUR[$key]['name'] = $value->name; 
                                }else{ 
                                    $dataUR[$key]['name'] = $value->name_ur; 
                                }
                                $dataUR[$key]['from_date'] = $value->from_date;
                                $dataUR[$key]['till_date'] = $value->till_date;
                                $dataUR[$key]['order_on'] = $value->order_on;
                                $dataUR[$key]['updated_at'] = $value->updated_at;
                            }    

                        }elseif( strpos(request()->slug, 'phd') !== false || strpos(request()->slug, 'post-graduate') !== false || strpos(request()->slug, 'under-graduate') !== false || strpos(request()->slug, 'other-program') !== false || strpos(request()->slug, 'mphil-program') !== false || strpos(request()->slug, 'diploma') !== false){
                            if (strpos(request()->slug, 'phd') !== false) {
                                $getData = Course::where('approval_status','Approved')->where('ac_non_ac_id',$linkId->ac_non_ac_id)->where('type','1')->orderBy('order_on','ASC')->get();
                                
                                foreach ($getData as $key => $value) {
                                    $dataUR[$key]['id']  = $value->id;
                                    if($value->name_ur == NULL){
                                        $dataUR[$key]['name'] = $value->name_en; 
                                    }else{ 
                                        $dataUR[$key]['name'] = $value->name_ur; 
                                    }
                                    if($value->nos_ur == NULL){
                                        $dataUR[$key]['nos'] = $value->nos_en; 
                                    }else{ 
                                        $dataUR[$key]['nos'] = $value->nos_ur; 
                                    }
                                    if($value->dr_ur == NULL){
                                        $dataUR[$key]['dr'] = $value->dr_en; 
                                    }else{ 
                                        $dataUR[$key]['dr'] = $value->dr_ur; 
                                    }
                                    if($value->cr_ur == NULL){
                                        $dataUR[$key]['cr'] = $value->cr_en; 
                                    }else{ 
                                        $dataUR[$key]['cr'] = $value->cr_ur; 
                                    }
                                    $dataUR[$key]['acrr'] = $value->acrr;
                                    if($value->jp_ur == NULL){
                                        $dataUR[$key]['jp'] = $value->jp_en; 
                                    }else{ 
                                        $dataUR[$key]['jp'] = $value->jp_ur; 
                                    }
                                    if($value->spec_ur == NULL){
                                        $dataUR[$key]['spec'] = $value->spec_en; 
                                    }else{ 
                                        $dataUR[$key]['spec'] = $value->spec_ur; 
                                    }
                                    if($value->peo_ur == NULL){
                                        $dataUR[$key]['peo'] = $value->peo_en; 
                                    }else{ 
                                        $dataUR[$key]['peo'] = $value->peo_ur; 
                                    }
                                    if($value->po_ur == NULL){
                                        $dataUR[$key]['po'] = $value->po_en; 
                                    }else{ 
                                        $dataUR[$key]['po'] = $value->po_ur; 
                                    }
                                    $dataUR[$key]['currl'] = $value->currl;
                                    $dataUR[$key]['syll'] = $value->syll;
                                }
                            }else if (strpos(request()->slug, 'post-graduate') !== false) {
                               
                                $getData = Course::where('approval_status','Approved')->where('ac_non_ac_id',$linkId->ac_non_ac_id)->where('type','2')->orderBy('order_on','ASC')->get();
                                
                                foreach ($getData as $key => $value) {
                                    $dataUR[$key]['id']  = $value->id;
                                    if($value->name_ur == NULL){
                                        $dataUR[$key]['name'] = $value->name_en; 
                                    }else{ 
                                        $dataUR[$key]['name'] = $value->name_ur; 
                                    }
                                    if($value->nos_ur == NULL){
                                        $dataUR[$key]['nos'] = $value->nos_en; 
                                    }else{ 
                                        $dataUR[$key]['nos'] = $value->nos_ur; 
                                    }
                                    if($value->dr_ur == NULL){
                                        $dataUR[$key]['dr'] = $value->dr_en; 
                                    }else{ 
                                        $dataUR[$key]['dr'] = $value->dr_ur; 
                                    }
                                    if($value->cr_ur == NULL){
                                        $dataUR[$key]['cr'] = $value->cr_en; 
                                    }else{ 
                                        $dataUR[$key]['cr'] = $value->cr_ur; 
                                    }
                                    $dataUR[$key]['acrr'] = $value->acrr;
                                    if($value->jp_ur == NULL){
                                        $dataUR[$key]['jp'] = $value->jp_en; 
                                    }else{ 
                                        $dataUR[$key]['jp'] = $value->jp_ur; 
                                    }
                                    if($value->spec_ur == NULL){
                                        $dataUR[$key]['spec'] = $value->spec_en; 
                                    }else{ 
                                        $dataUR[$key]['spec'] = $value->spec_ur; 
                                    }
                                    if($value->peo_ur == NULL){
                                        $dataUR[$key]['peo'] = $value->peo_en; 
                                    }else{ 
                                        $dataUR[$key]['peo'] = $value->peo_ur; 
                                    }
                                    if($value->po_ur == NULL){
                                        $dataUR[$key]['po'] = $value->po_en; 
                                    }else{ 
                                        $dataUR[$key]['po'] = $value->po_ur; 
                                    }
                                    $dataUR[$key]['currl'] = $value->currl;
                                    $dataUR[$key]['syll'] = $value->syll;
                                }

                            }else if (strpos(request()->slug, 'under-graduate') !== false) {
                                
                                $getData = Course::where('approval_status','Approved')->where('ac_non_ac_id',$linkId->ac_non_ac_id)->where('type','3')->orderBy('order_on','ASC')->get();
                               
                                foreach ($getData as $key => $value) {
                                    $dataUR[$key]['id']  = $value->id;
                                    if($value->name_ur == NULL){
                                        $dataUR[$key]['name'] = $value->name_en; 
                                    }else{ 
                                        $dataUR[$key]['name'] = $value->name_ur; 
                                    }
                                    if($value->nos_ur == NULL){
                                        $dataUR[$key]['nos'] = $value->nos_en; 
                                    }else{ 
                                        $dataUR[$key]['nos'] = $value->nos_ur; 
                                    }
                                    if($value->dr_ur == NULL){
                                        $dataUR[$key]['dr'] = $value->dr_en; 
                                    }else{ 
                                        $dataUR[$key]['dr'] = $value->dr_ur; 
                                    }
                                    if($value->cr_ur == NULL){
                                        $dataUR[$key]['cr'] = $value->cr_en; 
                                    }else{ 
                                        $dataUR[$key]['cr'] = $value->cr_ur; 
                                    }
                                    $dataUR[$key]['acrr'] = $value->acrr;
                                    if($value->jp_ur == NULL){
                                        $dataUR[$key]['jp'] = $value->jp_en; 
                                    }else{ 
                                        $dataUR[$key]['jp'] = $value->jp_ur; 
                                    }
                                    if($value->spec_ur == NULL){
                                        $dataUR[$key]['spec'] = $value->spec_en; 
                                    }else{ 
                                        $dataUR[$key]['spec'] = $value->spec_ur; 
                                    }
                                    if($value->peo_ur == NULL){
                                        $dataUR[$key]['peo'] = $value->peo_en; 
                                    }else{ 
                                        $dataUR[$key]['peo'] = $value->peo_ur; 
                                    }
                                    if($value->po_ur == NULL){
                                        $dataUR[$key]['po'] = $value->po_en; 
                                    }else{ 
                                        $dataUR[$key]['po'] = $value->po_ur; 
                                    }
                                    $dataUR[$key]['currl'] = $value->currl;
                                    $dataUR[$key]['syll'] = $value->syll;
                                }

                            }else if (strpos(request()->slug, 'other-program') !== false || strpos(request()->slug, 'diploma') !== false) {
                                
                                $getData = Course::where('approval_status','Approved')->where('ac_non_ac_id',$linkId->ac_non_ac_id)->where('type','4')->orderBy('order_on','ASC')->get();
                                
                                foreach ($getData as $key => $value) {
                                    $dataUR[$key]['id']  = $value->id;
                                    if($value->name_ur == NULL){
                                        $dataUR[$key]['name'] = $value->name_en; 
                                    }else{ 
                                        $dataUR[$key]['name'] = $value->name_ur; 
                                    }
                                    if($value->nos_ur == NULL){
                                        $dataUR[$key]['nos'] = $value->nos_en; 
                                    }else{ 
                                        $dataUR[$key]['nos'] = $value->nos_ur; 
                                    }
                                    if($value->dr_ur == NULL){
                                        $dataUR[$key]['dr'] = $value->dr_en; 
                                    }else{ 
                                        $dataUR[$key]['dr'] = $value->dr_ur; 
                                    }
                                    if($value->cr_ur == NULL){
                                        $dataUR[$key]['cr'] = $value->cr_en; 
                                    }else{ 
                                        $dataUR[$key]['cr'] = $value->cr_ur; 
                                    }
                                    $dataUR[$key]['acrr'] = $value->acrr;
                                    if($value->jp_ur == NULL){
                                        $dataUR[$key]['jp'] = $value->jp_en; 
                                    }else{ 
                                        $dataUR[$key]['jp'] = $value->jp_ur; 
                                    }
                                    if($value->spec_ur == NULL){
                                        $dataUR[$key]['spec'] = $value->spec_en; 
                                    }else{ 
                                        $dataUR[$key]['spec'] = $value->spec_ur; 
                                    }
                                    if($value->peo_ur == NULL){
                                        $dataUR[$key]['peo'] = $value->peo_en; 
                                    }else{ 
                                        $dataUR[$key]['peo'] = $value->peo_ur; 
                                    }
                                    if($value->po_ur == NULL){
                                        $dataUR[$key]['po'] = $value->po_en; 
                                    }else{ 
                                        $dataUR[$key]['po'] = $value->po_ur; 
                                    }
                                    $dataUR[$key]['currl'] = $value->currl;
                                    $dataUR[$key]['syll'] = $value->syll;
                                }

                            }else if (strpos(request()->slug, 'mphil-program') !== false) {
                                
                                $getData = Course::where('approval_status','Approved')->where('ac_non_ac_id',$linkId->ac_non_ac_id)->where('type','5')->orderBy('order_on','ASC')->get();
                                
                                foreach ($getData as $key => $value) {
                                    $dataUR[$key]['id']  = $value->id;
                                    if($value->name_ur == NULL){
                                        $dataUR[$key]['name'] = $value->name_en; 
                                    }else{ 
                                        $dataUR[$key]['name'] = $value->name_ur; 
                                    }
                                    if($value->nos_ur == NULL){
                                        $dataUR[$key]['nos'] = $value->nos_en; 
                                    }else{ 
                                        $dataUR[$key]['nos'] = $value->nos_ur; 
                                    }
                                    if($value->dr_ur == NULL){
                                        $dataUR[$key]['dr'] = $value->dr_en; 
                                    }else{ 
                                        $dataUR[$key]['dr'] = $value->dr_ur; 
                                    }
                                    if($value->cr_ur == NULL){
                                        $dataUR[$key]['cr'] = $value->cr_en; 
                                    }else{ 
                                        $dataUR[$key]['cr'] = $value->cr_ur; 
                                    }
                                    $dataUR[$key]['acrr'] = $value->acrr;
                                    if($value->jp_ur == NULL){
                                        $dataUR[$key]['jp'] = $value->jp_en; 
                                    }else{ 
                                        $dataUR[$key]['jp'] = $value->jp_ur; 
                                    }
                                    if($value->spec_ur == NULL){
                                        $dataUR[$key]['spec'] = $value->spec_en; 
                                    }else{ 
                                        $dataUR[$key]['spec'] = $value->spec_ur; 
                                    }
                                    if($value->peo_ur == NULL){
                                        $dataUR[$key]['peo'] = $value->peo_en; 
                                    }else{ 
                                        $dataUR[$key]['peo'] = $value->peo_ur; 
                                    }
                                    if($value->po_ur == NULL){
                                        $dataUR[$key]['po'] = $value->po_en; 
                                    }else{ 
                                        $dataUR[$key]['po'] = $value->po_ur; 
                                    }
                                    $dataUR[$key]['currl'] = $value->currl;
                                    $dataUR[$key]['syll'] = $value->syll;
                                }
                            }
                       }elseif( strpos(request()->slug, 'notice-and-circular') !== false ){
                            $dataUR = NoticeCircular::select('id','ac_non_ac_id','title_en','title_ur','to_date','file','status','approval_status','created_at','updated_at')/*->where('approval_status','Approved')*/->where('ac_non_ac_id',$linkId->ac_non_ac_id)->orderBy('created_at','desc')
                                ->when(request()->search, function ($query) {
                                    $query->where('title_en', 'LIKE', '%'.request()->search.'%');
                                })
                                ->paginate(env('ITEM_PER_PAGE'));
                            
                            foreach ($dataUR as $key => $value) {
                                
                                if($value->title_ur == NULL){
                                    $value['title'] = $value->title_en; 
                                }else{ 
                                    $value['title'] = $value->title_ur; 
                                }

                                $curDateTime = date("Y-m-d H:i:s");
                                $days_ago = date('Y-m-d', strtotime('-15 days', strtotime($curDateTime)));
                                $created_at = date('Y-m-d', strtotime($value->created_at));
                                if($created_at > $days_ago){
                                   $value['new'] = 1;    
                                }else{
                                   $value['new'] = 0;    
                                }
                               
                            }

                       }elseif(strpos(request()->slug,'on-going-research-projects') !== false || strpos(request()->slug ,'completed-research-projects') !== false){
                            if(strpos(request()->slug, 'on-going-research-projects') !== false)
                            {
                                $getData = OnGoingProject::/*where('approval_status','Approved')->*/where('ac_non_ac_id',$linkId->ac_non_ac_id)->orderBy('order_on','ASC')->orderBy('id','ASC')->get(); 
                                
                                foreach ($getData as $key => $value) {
                                    $dataUR[$key]['id'] = $value->id;
                                    if($value->about_ur == NULL){
                                        $dataUR[$key]['about'] = $value->about_en; 
                                    }else{ 
                                        $dataUR[$key]['about'] = $value->about_ur; 
                                    } 
                                    $dataUR[$key]['fagency'] = $value->fagency;
                                    $dataUR[$key]['famount'] = $value->famount;
                                    $dataUR[$key]['pi'] = $value->pi;
                                    $dataUR[$key]['cpi'] = $value->cpi;
                                    $dataUR[$key]['dt'] = $value->dt;
                                    $dataUR[$key]['updated_at'] = $value->updated_at;
                                }
                            }
                            if(strpos(request()->slug, 'completed-research-projects') !== false) {
                                $getData = CompletedProject::/*where('approval_status','Approved')->*/where('ac_non_ac_id',$linkId->ac_non_ac_id)->orderBy('order_on','ASC')->orderBy('id','ASC')->get();
                                
                                foreach ($getData as $key => $value) {
                                    $dataUR[$key]['id'] = $value->id;
                                    if($value->about_ur == NULL){
                                        $dataUR[$key]['about'] = $value->about_en; 
                                    }else{ 
                                        $dataUR[$key]['about'] = $value->about_ur; 
                                    } 
                                    $dataUR[$key]['fagency'] = $value->fagency;
                                    $dataUR[$key]['famount'] = $value->famount;
                                    $dataUR[$key]['pi'] = $value->pi;
                                    $dataUR[$key]['cpi'] = $value->cpi;
                                    if($value->pub_ur == NULL){
                                        $dataUR[$key]['pub'] = $value->pub_en; 
                                    }else{ 
                                        $dataUR[$key]['pub'] = $value->pub_ur; 
                                    }
                                    if($value->awards_ur == NULL){
                                        $dataUR[$key]['awards'] = $value->awards_en; 
                                    }else{ 
                                        $dataUR[$key]['awards'] = $value->awards_ur; 
                                    }
                                    $dataUR[$key]['dt'] = $value->dt;
                                    $dataUR[$key]['updated_at'] = $value->updated_at;
                                }
                            }
                       }elseif (strpos(request()->slug, 'photo-gallery') !== false) {

                            $getData = AcNonAcGallery::where('ac_non_ac_id', $linkId->ac_non_ac_id)->orderBy('order_on','ASC')->get();
                            
                            foreach ($getData as $key => $value) {
                                $dataUR[$key]['id'] = $value->id;
                                if($value->title_ur == NULL){
                                    $dataUR[$key]['title'] = $value->title_en; 
                                }else{ 
                                    $dataUR[$key]['title'] = $value->title_ur; 
                                }
                                $dataUR[$key]['image'] = $value->image;
                                $dataUR[$key]['updated_at'] = $value->updated_at;
                            }

                            
                       }else{
                            $getData = RelatedLinkData::/*where('approval_status','Approved')->*/where('related_link_id',$linkId->id)->orderBy('created_at','desc')->orderBy('order_on','ASC')->get();
                            
                            foreach ($getData as $key => $value) {
                                $dataUR[$key]['id'] = $value->id;
                                if($value->link_ur == NULL){
                                    $dataUR[$key]['link'] = $value->link_en; 
                                }else{ 
                                    $dataUR[$key]['link'] = $value->link_ur; 
                                }
                                if($value->link_description_ur == NULL){
                                    $dataUR[$key]['link_description'] = $value->link_description_en;
                                }else{
                                    $dataUR[$key]['link_description'] = $value->link_description_ur;
                                }
                                $dataUR[$key]['file'] = $value->file;
                                $dataUR[$key]['updated_at'] = $value->updated_at;
                            }
                       }                        
                        if ($dataUR){
                            $seo_title = $linkId->link_name_en.' - '.$linkId->getAcAndNonAc->title_en;
                            $data['seo_title'] = $seo_title.' | AMU'; 
                            $data['data'] = $dataUR;
                            $latest_update = UserLog::select('updated_at')->where('ac_non_ac_id',$linkId->ac_non_ac_id)->orderBy('updated_at','DESC')->first();
                            if ($latest_update) {
                                $data['last_update'] = $latest_update->updated_at;
                            }else{
                                $data['last_update'] = '';
                            }                       
                            return $this->sendResponse($data, trans($locale.'_lang.DATA_FOUND'),200);
                        } else {
                            return $this->sendResponse($data, trans($locale.'_lang.DATA_NOT_FOUND'),404);
                        }
                        break;
                    case "hi": //GET widget IN Hindi
                        $dataHI = array();
                        $linkId = RelatedLink::where('slug',request()->slug)->with('getAcAndNonAc')->first();
                        if (empty($linkId)){                        
                            return $this->sendResponse($getData, trans($locale.'_lang.DATA_NOT_FOUND'),404);
                        }
                        if(strpos(request()->slug,'about-the-department') !== false || strpos(request()->slug,'contact-us') !== false || strpos(request()->slug,'about-the-section') !== false || strpos(request()->slug,'about-the-amucentres') !== false){
                            $getData = RelatedLinkData::where('related_link_id',$linkId->id)->orderBy('order_on','ASC')->first();
                            
                                $dataHI['id'] = $getData->id;
                                $dataHI['link'] = $getData->link_en; 
                                $dataHI['link_description'] = $getData->link_description_en;
                                $dataHI['file'] = $getData->file;
                                $latest_update = UserLog::select('updated_at')->where('ac_non_ac_id',$linkId->id)->orderBy('updated_at','DESC')->first();
                                if ($latest_update) {
                                    $dataHI['updated_at'] = $latest_update->updated_at;
                                }else{
                                    $dataHI['updated_at'] = '';
                                }
                           
                        }elseif(strpos(request()->slug,'faculty-members') !== false || strpos(request()->slug ,'non-teaching-staff') !== false || strpos(request()->slug ,'pg-student') !== false || strpos(request()->slug ,'retired-faculty-member') !== false){
                            if(strpos(request()->slug, 'faculty-members') !== false)
                            {
                                $faculties = UserVisibility::where('ac_non_ac_id',$linkId->ac_non_ac_id)->where('for_id','1')->where('status','1')->with('getUser.getContact','getUser.getDepartment.getAcNonAcItem','getDesignation','getRole')->orderBy('order_on','ASC')->orderBy('role_id','ASC')->get();
                                
                                if (empty($faculties)){                        
                                    return $this->sendResponse($getData, trans($locale.'_lang.DATA_NOT_FOUND'),404);
                                }
                                $key=0;
                                foreach ($faculties as $value) {
                                    if($value->getUser != NULL){
                                        $dataHI[$key]['user_id'] = $value->user_id;
                                        $dataHI[$key]['order_on'] = $value->order_on;
                                        $dataHI[$key]['url'] = $value->getUser->url;
                                        $dataHI[$key]['slug'] = $value->getUser->slug;
                                        $dataHI[$key]['title'] = $value->getUser->title;
                                        $dataHI[$key]['first_name'] = $value->getUser->first_name;
                                        $dataHI[$key]['last_name'] = $value->getUser->last_name;
                                        $dataHI[$key]['middle_name'] = $value->getUser->middle_name;
                                        $dataHI[$key]['status'] = $value->getUser->status;
                                        if($value->getUser->image !=NULL){
                                           $userImage = asset('storage').$value->getUser->image;
                                        }else{
                                           $userImage = asset('storage').'/images/default-img.png';
                                        }
                                        $dataHI[$key]['image'] = $userImage;
                                        if($academic->sub_type == '2' && $value->role_id == $academic->head){
                                          $dataHI[$key]['designation'] = $value->getRole->title.' and ' .$value->getDesignation->name;
                                        }else{
                                          $dataHI[$key]['designation'] = $value->getDesignation->name;  
                                        }
                                        
                                        if ($value->getUser->getContact) {
                                            foreach ($value->getUser->getContact as $val) {
                                                if ($val->email_visibility == '1') {
                                                    $dataHI[$key]['email'] = $val->email;
                                                }
                                                if ($val->mobile_visibility == '1') {
                                                    $dataHI[$key]['mobile_no'] = $val->mobile_no;
                                                }
                                                $dataHI[$key]['telephone_no'] = $val->telephone_no;
                                            }
                                        }else{
                                            $dataHI[$key]['email'] = '';
                                            $dataHI[$key]['mobile_no'] = '';
                                            $dataHI[$key]['telephone_no'] = '';
                                        }
                                        $dataHI[$key]['core_department'] = $value->getUser->getDepartment->getAcNonAcItem[0]->slug;
                                        $key++;
                                    }
                                }
                            }
                            if(strpos(request()->slug, 'non-teaching-staff') !== false) {
                                $faculties = UserVisibility::where('ac_non_ac_id',$linkId->ac_non_ac_id)->where('for_id','2')->where('status','1')->with('getUser.getContact','getUser.getDepartment.getAcNonAcItem','getDesignation')->orderBy('order_on','ASC')->orderBy('role_id','ASC')->get();
                                
                                if (empty($faculties)){                        
                                    return $this->sendResponse($getData, trans($locale.'_lang.DATA_NOT_FOUND'),404);
                                }
                                $key=0;
                                foreach ($faculties as $value) {
                                    if($value->getUser != NULL){
                                        $dataHI[$key]['user_id'] = $value->user_id;
                                        $dataHI[$key]['order_on'] = $value->order_on;
                                        $dataHI[$key]['url'] = $value->getUser->url;
                                        $dataHI[$key]['slug'] = $value->getUser->slug;
                                        $dataHI[$key]['title'] = $value->getUser->title;
                                        $dataHI[$key]['first_name'] = $value->getUser->first_name;
                                        $dataHI[$key]['last_name'] = $value->getUser->last_name;
                                        $dataHI[$key]['middle_name'] = $value->getUser->middle_name;
                                        $dataHI[$key]['status'] = $value->getUser->status;
                                        if($value->getUser->image !=NULL){
                                           $userImage = asset('storage').$value->getUser->image;
                                        }else{
                                           $userImage = asset('storage').'/images/default-img.png';
                                        }
                                        $dataHI[$key]['image'] = $userImage;
                                        $dataHI[$key]['designation'] = $value->getDesignation->name;
                                        
                                        if ($value->getUser->getContact) {
                                            foreach ($value->getUser->getContact as $val) {
                                                if ($val->email_visibility == '1') {
                                                    $dataHI[$key]['email'] = $val->email;
                                                }
                                                if ($val->mobile_visibility == '1') {
                                                    $dataHI[$key]['mobile_no'] = $val->mobile_no;
                                                }
                                                $dataHI[$key]['telephone_no'] = $val->telephone_no;
                                            }
                                        }else{
                                            $dataHI[$key]['email'] = '';
                                            $dataHI[$key]['mobile_no'] = '';
                                            $dataHI[$key]['telephone_no'] = '';
                                        }
                                        $dataHI[$key]['core_department'] = $value->getUser->getDepartment->getAcNonAcItem[0]->slug;
                                        $key++;
                                    }
                                }
                            }
                            if(strpos(request()->slug ,'pg-student') !== false) {
                                $faculties = UserVisibility::where('ac_non_ac_id',$linkId->ac_non_ac_id)->where('for_id','3')->where('status','1')->with('getUser.getContact','getUser.getDepartment.getAcNonAcItem','getDesignation')->orderBy('order_on','ASC')->orderBy('role_id','ASC')->get();
                                
                                if (empty($faculties)){                        
                                    return $this->sendResponse($getData, trans($locale.'_lang.DATA_NOT_FOUND'),404);
                                }
                                $key=0;
                                foreach ($faculties as $value) {
                                    if($value->getUser != NULL){
                                        $dataHI[$key]['user_id'] = $value->user_id;
                                        $dataHI[$key]['order_on'] = $value->order_on;
                                        $dataHI[$key]['url'] = $value->getUser->url;
                                        $dataHI[$key]['slug'] = $value->getUser->slug;
                                        $dataHI[$key]['title'] = $value->getUser->title;
                                        $dataHI[$key]['first_name'] = $value->getUser->first_name;
                                        $dataHI[$key]['last_name'] = $value->getUser->last_name;
                                        $dataHI[$key]['middle_name'] = $value->getUser->middle_name;
                                        $dataHI[$key]['status'] = $value->getUser->status;
                                        if($value->getUser->image !=NULL){
                                           $userImage = asset('storage').$value->getUser->image;
                                        }else{
                                           $userImage = asset('storage').'/images/default-img.png';
                                        }
                                        $dataHI[$key]['image'] = $userImage;
                                        $dataHI[$key]['designation'] = $value->getDesignation->name;
                                        
                                        if ($value->getUser->getContact) {
                                            foreach ($value->getUser->getContact as $val) {
                                                if ($val->email_visibility == '1') {
                                                    $dataHI[$key]['email'] = $val->email;
                                                }
                                                if ($val->mobile_visibility == '1') {
                                                    $dataHI[$key]['mobile_no'] = $val->mobile_no;
                                                }
                                                $dataHI[$key]['telephone_no'] = $val->telephone_no;
                                            }
                                        }else{
                                            $dataHI[$key]['email'] = '';
                                            $dataHI[$key]['mobile_no'] = '';
                                            $dataHI[$key]['telephone_no'] = '';
                                        }
                                        $dataHI[$key]['core_department'] = $value->getUser->getDepartment->getAcNonAcItem[0]->slug;
                                        $key++;
                                    }
                                }
                            }
                            if(strpos(request()->slug ,'retired-faculty-member') !== false) {
                                $faculties = UserVisibility::where('ac_non_ac_id',$linkId->ac_non_ac_id)->where('status','2')->where('for_id','1')->with('getRetireUser.getContact','getRetireUser.getDepartment.getAcNonAcItem','getDesignation','getSubType')->orderBy('order_on','ASC')->orderBy('role_id','ASC')->get();

                                if (empty($faculties)){                        
                                    return $this->sendResponse($faculties, trans($locale.'_lang.DATA_NOT_FOUND'),404);
                                }
                                
                                $key=0;
                                foreach ($faculties as $value) {
                                    if($value->getRetireUser != NULL){
                                        $dataHI[$key]['user_id'] = $value->user_id;
                                        $dataHI[$key]['order_on'] = $value->order_on;
                                        $dataHI[$key]['type'] = strtolower($value->getSubType->title) ;
                                        $dataHI[$key]['url'] = $value->getRetireUser->url;
                                        $dataHI[$key]['slug'] = $value->getRetireUser->slug;
                                        $dataHI[$key]['title'] = $value->getRetireUser->title;
                                        $dataHI[$key]['first_name'] = $value->getRetireUser->first_name;
                                        $dataHI[$key]['middle_name'] = $value->getRetireUser->middle_name;
                                        $dataHI[$key]['last_name'] = $value->getRetireUser->last_name;
                                        $dataHI[$key]['status'] = $value->getRetireUser->status;
                                        
                                        if($value->getRetireUser->image !=NULL){
                                           $userImage = asset('storage').$value->getRetireUser->image;
                                        }else{
                                           $userImage = asset('storage').'/images/default-img.png';
                                        }
                                        $dataHI[$key]['image'] = $userImage;                                     
                                        $dataHI[$key]['designation'] = $value->getDesignation->name; 
                                        
                                        if ($value->getRetireUser->getContact) {
                                            foreach ($value->getRetireUser->getContact as $val) {
                                                if ($val->email_visibility == '1') {
                                                    $dataHI[$key]['email'] = $val->email;
                                                }
                                                if ($val->mobile_visibility == '1') {
                                                    $dataHI[$key]['mobile_no'] = $val->mobile_no;
                                                }
                                                $dataHI[$key]['telephone_no'] = $val->telephone_no;
                                            }
                                        }else{
                                            $dataHI[$key]['email'] = '';
                                            $dataHI[$key]['mobile_no'] = '';
                                            $dataHI[$key]['telephone_no'] = '';
                                        }
                                        $key++;

                                    }
                                }
                            }
                        }elseif(strpos(request()->slug,'list-of-former-chairperson') !== false){
                            $getData = FormerChairPerson::/*where('approval_status','Approved')->*/where('ac_non_ac_id',$linkId->ac_non_ac_id)->orderBy('order_on','ASC')->get();
                            
                            foreach ($getData as $key => $value) {
                                $dataHI[$key]['id']  = $value->id;
                                if($value->name_hi == NULL){
                                    $dataHI[$key]['name'] = $value->name; 
                                }else{ 
                                    $dataHI[$key]['name'] = $value->name_hi; 
                                }
                                $dataHI[$key]['from_date'] = $value->from_date;
                                $dataHI[$key]['till_date'] = $value->till_date;
                                $dataHI[$key]['order_on'] = $value->order_on;
                                $dataHI[$key]['updated_at'] = $value->updated_at;
                            }    

                        }elseif( strpos(request()->slug, 'phd') !== false || strpos(request()->slug, 'post-graduate') !== false || strpos(request()->slug, 'under-graduate') !== false || strpos(request()->slug, 'other-program') !== false || strpos(request()->slug, 'mphil-program') !== false || strpos(request()->slug, 'diploma') !== false){
                            if (strpos(request()->slug, 'phd') !== false) {
                                $getData = Course::where('approval_status','approved')->where('ac_non_ac_id',$linkId->ac_non_ac_id)->where('type','1')->orderBy('order_on','ASC')->get();
                                
                                foreach ($getData as $key => $value) {
                                    $dataHI[$key]['id']  = $value->id;
                                    if($value->name_hi == NULL){
                                        $dataHI[$key]['name'] = $value->name_en; 
                                    }else{ 
                                        $dataHI[$key]['name'] = $value->name_hi; 
                                    }
                                    if($value->nos_hi == NULL){
                                        $dataHI[$key]['nos'] = $value->nos_en; 
                                    }else{ 
                                        $dataHI[$key]['nos'] = $value->nos_hi; 
                                    }
                                    if($value->dr_hi == NULL){
                                        $dataHI[$key]['dr'] = $value->dr_en; 
                                    }else{ 
                                        $dataHI[$key]['dr'] = $value->dr_hi; 
                                    }
                                    if($value->cr_hi == NULL){
                                        $dataHI[$key]['cr'] = $value->cr_en; 
                                    }else{ 
                                        $dataHI[$key]['cr'] = $value->cr_hi; 
                                    }
                                    $dataHI[$key]['acrr'] = $value->acrr;
                                    if($value->jp_hi == NULL){
                                        $dataHI[$key]['jp'] = $value->jp_en; 
                                    }else{ 
                                        $dataHI[$key]['jp'] = $value->jp_hi; 
                                    }
                                    if($value->spec_hi == NULL){
                                        $dataHI[$key]['spec'] = $value->spec_en; 
                                    }else{ 
                                        $dataHI[$key]['spec'] = $value->spec_hi; 
                                    }
                                    if($value->peo_hi == NULL){
                                        $dataHI[$key]['peo'] = $value->peo_en; 
                                    }else{ 
                                        $dataHI[$key]['peo'] = $value->peo_hi; 
                                    }
                                    if($value->po_hi == NULL){
                                        $dataHI[$key]['po'] = $value->po_en; 
                                    }else{ 
                                        $dataHI[$key]['po'] = $value->po_hi; 
                                    }
                                    $dataHI[$key]['currl'] = $value->currl;
                                    $dataHI[$key]['syll'] = $value->syll;
                                }
                            }else if (strpos(request()->slug, 'post-graduate') !== false) {
                               
                                $getData = Course::where('approval_status','approved')->where('ac_non_ac_id',$linkId->ac_non_ac_id)->where('type','2')->orderBy('order_on','ASC')->get();
                                
                                foreach ($getData as $key => $value) {
                                    $dataHI[$key]['id']  = $value->id;
                                    if($value->name_hi == NULL){
                                        $dataHI[$key]['name'] = $value->name_en; 
                                    }else{ 
                                        $dataHI[$key]['name'] = $value->name_hi; 
                                    }
                                    if($value->nos_hi == NULL){
                                        $dataHI[$key]['nos'] = $value->nos_en; 
                                    }else{ 
                                        $dataHI[$key]['nos'] = $value->nos_hi; 
                                    }
                                    if($value->dr_hi == NULL){
                                        $dataHI[$key]['dr'] = $value->dr_en; 
                                    }else{ 
                                        $dataHI[$key]['dr'] = $value->dr_hi; 
                                    }
                                    if($value->cr_hi == NULL){
                                        $dataHI[$key]['cr'] = $value->cr_en; 
                                    }else{ 
                                        $dataHI[$key]['cr'] = $value->cr_hi; 
                                    }
                                    $dataHI[$key]['acrr'] = $value->acrr;
                                    if($value->jp_hi == NULL){
                                        $dataHI[$key]['jp'] = $value->jp_en; 
                                    }else{ 
                                        $dataHI[$key]['jp'] = $value->jp_hi; 
                                    }
                                    if($value->spec_hi == NULL){
                                        $dataHI[$key]['spec'] = $value->spec_en; 
                                    }else{ 
                                        $dataHI[$key]['spec'] = $value->spec_hi; 
                                    }
                                    if($value->peo_hi == NULL){
                                        $dataHI[$key]['peo'] = $value->peo_en; 
                                    }else{ 
                                        $dataHI[$key]['peo'] = $value->peo_hi; 
                                    }
                                    if($value->po_hi == NULL){
                                        $dataHI[$key]['po'] = $value->po_en; 
                                    }else{ 
                                        $dataHI[$key]['po'] = $value->po_hi; 
                                    }
                                    $dataHI[$key]['currl'] = $value->currl;
                                    $dataHI[$key]['syll'] = $value->syll;
                                }

                            }else if (strpos(request()->slug, 'under-graduate') !== false) {
                                
                                $getData = Course::where('approval_status','approved')->where('ac_non_ac_id',$linkId->ac_non_ac_id)->where('type','3')->orderBy('order_on','ASC')->get();
                                
                                foreach ($getData as $key => $value) {
                                    $dataHI[$key]['id']  = $value->id;
                                    if($value->name_hi == NULL){
                                        $dataHI[$key]['name'] = $value->name_en; 
                                    }else{ 
                                        $dataHI[$key]['name'] = $value->name_hi; 
                                    }
                                    if($value->nos_hi == NULL){
                                        $dataHI[$key]['nos'] = $value->nos_en; 
                                    }else{ 
                                        $dataHI[$key]['nos'] = $value->nos_hi; 
                                    }
                                    if($value->dr_hi == NULL){
                                        $dataHI[$key]['dr'] = $value->dr_en; 
                                    }else{ 
                                        $dataHI[$key]['dr'] = $value->dr_hi; 
                                    }
                                    if($value->cr_hi == NULL){
                                        $dataHI[$key]['cr'] = $value->cr_en; 
                                    }else{ 
                                        $dataHI[$key]['cr'] = $value->cr_hi; 
                                    }
                                    $dataHI[$key]['acrr'] = $value->acrr;
                                    if($value->jp_hi == NULL){
                                        $dataHI[$key]['jp'] = $value->jp_en; 
                                    }else{ 
                                        $dataHI[$key]['jp'] = $value->jp_hi; 
                                    }
                                    if($value->spec_hi == NULL){
                                        $dataHI[$key]['spec'] = $value->spec_en; 
                                    }else{ 
                                        $dataHI[$key]['spec'] = $value->spec_hi; 
                                    }
                                    if($value->peo_hi == NULL){
                                        $dataHI[$key]['peo'] = $value->peo_en; 
                                    }else{ 
                                        $dataHI[$key]['peo'] = $value->peo_hi; 
                                    }
                                    if($value->po_hi == NULL){
                                        $dataHI[$key]['po'] = $value->po_en; 
                                    }else{ 
                                        $dataHI[$key]['po'] = $value->po_hi; 
                                    }
                                    $dataHI[$key]['currl'] = $value->currl;
                                    $dataHI[$key]['syll'] = $value->syll;
                                }

                            }else if (strpos(request()->slug, 'other-program') !== false || strpos(request()->slug, 'diploma') !== false) {
                                
                                $getData = Course::where('approval_status','approved')->where('ac_non_ac_id',$linkId->ac_non_ac_id)->where('type','4')->orderBy('order_on','ASC')->get();
                                
                                foreach ($getData as $key => $value) {
                                    $dataHI[$key]['id']  = $value->id;
                                    if($value->name_hi == NULL){
                                        $dataHI[$key]['name'] = $value->name_en; 
                                    }else{ 
                                        $dataHI[$key]['name'] = $value->name_hi; 
                                    }
                                    if($value->nos_hi == NULL){
                                        $dataHI[$key]['nos'] = $value->nos_en; 
                                    }else{ 
                                        $dataHI[$key]['nos'] = $value->nos_hi; 
                                    }
                                    if($value->dr_hi == NULL){
                                        $dataHI[$key]['dr'] = $value->dr_en; 
                                    }else{ 
                                        $dataHI[$key]['dr'] = $value->dr_hi; 
                                    }
                                    if($value->cr_hi == NULL){
                                        $dataHI[$key]['cr'] = $value->cr_en; 
                                    }else{ 
                                        $dataHI[$key]['cr'] = $value->cr_hi; 
                                    }
                                    $dataHI[$key]['acrr'] = $value->acrr;
                                    if($value->jp_hi == NULL){
                                        $dataHI[$key]['jp'] = $value->jp_en; 
                                    }else{ 
                                        $dataHI[$key]['jp'] = $value->jp_hi; 
                                    }
                                    if($value->spec_hi == NULL){
                                        $dataHI[$key]['spec'] = $value->spec_en; 
                                    }else{ 
                                        $dataHI[$key]['spec'] = $value->spec_hi; 
                                    }
                                    if($value->peo_hi == NULL){
                                        $dataHI[$key]['peo'] = $value->peo_en; 
                                    }else{ 
                                        $dataHI[$key]['peo'] = $value->peo_hi; 
                                    }
                                    if($value->po_hi == NULL){
                                        $dataHI[$key]['po'] = $value->po_en; 
                                    }else{ 
                                        $dataHI[$key]['po'] = $value->po_hi; 
                                    }
                                    $dataHI[$key]['currl'] = $value->currl;
                                    $dataHI[$key]['syll'] = $value->syll;
                                }

                            }else if (strpos(request()->slug, 'mphil-program') !== false) {
                                
                                $getData = Course::where('approval_status','approved')->where('ac_non_ac_id',$linkId->ac_non_ac_id)->where('type','5')->orderBy('order_on','ASC')->get();
                                
                                foreach ($getData as $key => $value) {
                                    $dataHI[$key]['id']  = $value->id;
                                    if($value->name_hi == NULL){
                                        $dataHI[$key]['name'] = $value->name_en; 
                                    }else{ 
                                        $dataHI[$key]['name'] = $value->name_hi; 
                                    }
                                    if($value->nos_hi == NULL){
                                        $dataHI[$key]['nos'] = $value->nos_en; 
                                    }else{ 
                                        $dataHI[$key]['nos'] = $value->nos_hi; 
                                    }
                                    if($value->dr_hi == NULL){
                                        $dataHI[$key]['dr'] = $value->dr_en; 
                                    }else{ 
                                        $dataHI[$key]['dr'] = $value->dr_hi; 
                                    }
                                    if($value->cr_hi == NULL){
                                        $dataHI[$key]['cr'] = $value->cr_en; 
                                    }else{ 
                                        $dataHI[$key]['cr'] = $value->cr_hi; 
                                    }
                                    $dataHI[$key]['acrr'] = $value->acrr;
                                    if($value->jp_hi == NULL){
                                        $dataHI[$key]['jp'] = $value->jp_en; 
                                    }else{ 
                                        $dataHI[$key]['jp'] = $value->jp_hi; 
                                    }
                                    if($value->spec_hi == NULL){
                                        $dataHI[$key]['spec'] = $value->spec_en; 
                                    }else{ 
                                        $dataHI[$key]['spec'] = $value->spec_hi; 
                                    }
                                    if($value->peo_hi == NULL){
                                        $dataHI[$key]['peo'] = $value->peo_en; 
                                    }else{ 
                                        $dataHI[$key]['peo'] = $value->peo_hi; 
                                    }
                                    if($value->po_hi == NULL){
                                        $dataHI[$key]['po'] = $value->po_en; 
                                    }else{ 
                                        $dataHI[$key]['po'] = $value->po_hi; 
                                    }
                                    $dataHI[$key]['currl'] = $value->currl;
                                    $dataHI[$key]['syll'] = $value->syll;
                                }
                            }
                        }elseif(strpos(request()->slug,'on-going-research-projects') !== false || strpos(request()->slug ,'completed-research-projects') !== false){
                            if(strpos(request()->slug, 'on-going-research-projects') !== false) {
                                $getData = OnGoingProject::/*where('approval_status','approved')->*/where('ac_non_ac_id',$linkId->ac_non_ac_id)->orderBy('order_on','ASC')->orderBy('id','ASC')->get(); 
                                
                                foreach ($getData as $key => $value) {
                                    $dataHI[$key]['id'] = $value->id;
                                    if($value->about_hi == NULL){
                                        $dataHI[$key]['about'] = $value->about_en; 
                                    }else{ 
                                        $dataHI[$key]['about'] = $value->about_hi; 
                                    } 
                                    $dataHI[$key]['fagency'] = $value->fagency;
                                    $dataHI[$key]['famount'] = $value->famount;
                                    $dataHI[$key]['pi'] = $value->pi;
                                    $dataHI[$key]['cpi'] = $value->cpi;
                                    $dataHI[$key]['dt'] = $value->dt;
                                    $dataHI[$key]['updated_at'] = $value->updated_at;
                                }
                            }
                            if(strpos(request()->slug, 'completed-research-projects') !== false) {
                                $getData = CompletedProject::/*where('approval_status','approved')->*/where('ac_non_ac_id',$linkId->ac_non_ac_id)->orderBy('order_on','ASC')->orderBy('id','ASC')->get();
                                
                                foreach ($getData as $key => $value) {
                                    $dataHI[$key]['id'] = $value->id;
                                    if($value->about_hi == NULL){
                                        $dataHI[$key]['about'] = $value->about_en; 
                                    }else{ 
                                        $dataHI[$key]['about'] = $value->about_hi; 
                                    } 
                                    $dataHI[$key]['fagency'] = $value->fagency;
                                    $dataHI[$key]['famount'] = $value->famount;
                                    $dataHI[$key]['pi'] = $value->pi;
                                    $dataHI[$key]['cpi'] = $value->cpi;
                                    if($value->pub_hi == NULL){
                                        $dataUR[$key]['pub'] = $value->pub_en; 
                                    }else{ 
                                        $dataUR[$key]['pub'] = $value->pub_hi; 
                                    }
                                    if($value->awards_hi == NULL){
                                        $dataUR[$key]['awards'] = $value->awards_en; 
                                    }else{ 
                                        $dataUR[$key]['awards'] = $value->awards_hi; 
                                    }
                                    $dataHI[$key]['dt'] = $value->dt;
                                    $dataHI[$key]['updated_at'] = $value->updated_at;
                                }
                            }
                        }elseif( strpos(request()->slug, 'notice-and-circular') !== false ){
                            $dataHI = NoticeCircular::select('id','ac_non_ac_id','title_en','title_hi','to_date','file','status','approval_status','created_at','updated_at')->where('approval_status','Approved')->where('ac_non_ac_id',$linkId->ac_non_ac_id)->orderBy('created_at','desc')
                                ->when(request()->search, function ($query) {
                                    $query->where('title_en', 'LIKE', '%'.request()->search.'%');
                                })
                                ->paginate(env('ITEM_PER_PAGE'));
                            
                            foreach ($dataHI as $key => $value) {
                                
                                if($value->title_hi == NULL){
                                    $value['title'] = $value->title_en; 
                                }else{ 
                                    $value['title'] = $value->title_hi; 
                                }

                                $curDateTime = date("Y-m-d H:i:s");
                                $days_ago = date('Y-m-d', strtotime('-15 days', strtotime($curDateTime)));
                                $created_at = date('Y-m-d', strtotime($value->created_at));
                                if($created_at > $days_ago){
                                   $value['new'] = 1;    
                                }else{
                                   $value['new'] = 0;    
                                }
                            }

                        }elseif (strpos(request()->slug, 'photo-gallery') !== false) {

                            $getData = AcNonAcGallery::where('ac_non_ac_id', $linkId->ac_non_ac_id)->orderBy('order_on','ASC')->get();
                            
                            foreach ($getData as $key => $value) {
                                $dataHI[$key]['id'] = $value->id;
                                if($value->title_ur == NULL){
                                    $dataHI[$key]['title'] = $value->title_en; 
                                }else{ 
                                    $dataHI[$key]['title'] = $value->title_hi; 
                                }
                                $dataHI[$key]['image'] = $value->image;
                                $dataHI[$key]['updated_at'] = $value->updated_at;
                            }
                        }else{
                            $getData = RelatedLinkData::/*where('approval_status','approved')->*/where('related_link_id',$linkId->id)->orderBy('created_at','desc')->orderBy('order_on','ASC')->get();
                            
                            foreach ($getData as $key => $value) {
                                $dataHI[$key]['id'] = $value->id;
                                if($value->link_hi == NULL){
                                    $dataHI[$key]['link'] = $value->link_en; 
                                }else{ 
                                    $dataHI[$key]['link'] = $value->link_hi; 
                                }
                                if($value->link_description_hi == NULL){
                                    $dataHI[$key]['link_description'] = $value->link_description_en;
                                }else{
                                    $dataHI[$key]['link_description'] = $value->link_description_hi;
                                }
                                $dataHI[$key]['file'] = $value->file;
                                $dataHI[$key]['updated_at'] = $value->updated_at;
                            }
                        }
                        if ($dataHI){  
                            $seo_title = $linkId->link_name_en.' - '.$linkId->getAcAndNonAc->title_en;
                            $data['seo_title'] = $seo_title.' | AMU';
                            $data['data'] = $dataHI;
                            $latest_update = UserLog::select('updated_at')->where('ac_non_ac_id',$linkId->ac_non_ac_id)->orderBy('updated_at','DESC')->first();
                            if ($latest_update) {
                                $data['last_update'] = $latest_update->updated_at;
                            }else{
                                $data['last_update'] = '';
                            }
                                                  
                            return $this->sendResponse($data, trans($locale.'_lang.DATA_FOUND'),200);
                        } else {
                            return $this->sendResponse($data, trans($locale.'_lang.DATA_NOT_FOUND'),404);
                        }
                        break;
                    default: //GET widget IN English
                        $dataEn = array();
                        
                        $linkId = RelatedLink::where('slug',request()->slug)->with('getAcAndNonAc')->first();
                        if (empty($linkId)){                        
                            return $this->sendResponse($linkId, trans($locale.'_lang.DATA_NOT_FOUND'),404);
                        }
                        if(strpos(request()->slug,'about-the-department') !== false || strpos(request()->slug,'contact-us') !== false || strpos(request()->slug,'about-the-section') !== false || strpos(request()->slug,'about-the-amucentres') !== false){
                            $getData = RelatedLinkData::where('related_link_id',$linkId->id)->orderBy('order_on','ASC')->first();
                            if ($getData) {
                                $dataEn['id'] = $getData->id;
                                $dataEn['link'] = $getData->link_en; 
                                $dataEn['link_description'] = $getData->link_description_en;
                                $dataEn['file'] = $getData->file;
                                $latest_update = UserLog::select('updated_at')->where('ac_non_ac_id',$linkId->ac_non_ac_id)->orderBy('updated_at','DESC')->first();
                               
                                if ($latest_update) {
                                    $dataEn['updated_at'] = $latest_update->updated_at;
                                }else{
                                    $dataEn['updated_at'] = '';
                                }
                            }
                             
                                
                           
                        }elseif(strpos(request()->slug,'faculty-members') !== false || strpos(request()->slug ,'non-teaching-staff') !== false || strpos(request()->slug ,'pg-student') !== false || strpos(request()->slug ,'retired-faculty-member') !== false){                            
                            if(strpos(request()->slug, 'faculty-members') !== false)
                            {
                                $faculties = UserVisibility::where('ac_non_ac_id',$linkId->ac_non_ac_id)->where('for_id','1')->where('status','1')->with('getUser.getContact','getUser.getDepartment.getAcNonAcItem','getDesignation','getSubType','getRole')->orderBy('order_on','ASC')->orderBy('role_id','ASC')->get();
                                if (empty($faculties)){                        
                                    return $this->sendResponse($faculties, trans($locale.'_lang.DATA_NOT_FOUND'),404);
                                }
                                
                                $key=0;
                                foreach ($faculties as $value) {
                                
                                    if($value->getUser != NULL){
                                        $dataEn[$key]['user_id'] = $value->user_id;
                                        $dataEn[$key]['core'] = $value->core;
                                        $dataEn[$key]['type'] = strtolower($value->getSubType->title) ;
                                        $dataEn[$key]['url'] = $value->getUser->url;
                                        $dataEn[$key]['slug'] = $value->getUser->slug;
                                        $dataEn[$key]['title'] = $value->getUser->title;
                                        $dataEn[$key]['first_name'] = $value->getUser->first_name;
                                        $dataEn[$key]['last_name'] = $value->getUser->last_name;
                                        $dataEn[$key]['middle_name'] = $value->getUser->middle_name;
                                        $dataEn[$key]['status'] = $value->getUser->status;
                                        if($value->getUser->image !=NULL){
                                           $userImage = asset('storage').$value->getUser->image;
                                        }else{
                                           $userImage = asset('storage').'/images/default-img.png';
                                        }
                                        $dataEn[$key]['image'] = $userImage;

                                        if($academic->sub_type == '2' && $value->role_id == $academic->head){
                                          $dataEn[$key]['designation'] = $value->getRole->title.' and ' .$value->getDesignation->name;
                                        }else{
                                          $dataEn[$key]['designation'] = $value->getDesignation->name;  
                                        }
                                        
                                        if ($value->getUser->getContact) {
                                            foreach ($value->getUser->getContact as $val) {
                                                if ($val->email_visibility == '1') {
                                                    $dataEn[$key]['email'] = $val->email;
                                                }
                                                if ($val->mobile_visibility == '1') {
                                                    $dataEn[$key]['mobile_no'] = $val->mobile_no;
                                                }
                                                $dataEn[$key]['telephone_no'] = $val->telephone_no;
                                            }
                                        }else{
                                            $dataEn[$key]['email'] = '';
                                            $dataEn[$key]['mobile_no'] = '';
                                            $dataEn[$key]['telephone_no'] = '';
                                        }
                                        $key++;
                                    }
                                }
                            }
                            if(strpos(request()->slug, 'non-teaching-staff') !== false) {                                
                                $faculties = UserVisibility::where('ac_non_ac_id',$linkId->ac_non_ac_id)->where('for_id','2')->where('status','1')->with('getUser.getContact','getUser.getDepartment.getAcNonAcItem','getDesignation','getSubType')->orderBy('order_on','ASC')->orderBy('role_id','ASC')->get();
                                if (empty($faculties)){                        
                                    return $this->sendResponse($faculties, trans($locale.'_lang.DATA_NOT_FOUND'),404);
                                }
                                
                                $key=0;
                                foreach ($faculties as $value) {
                                    if($value->getUser != NULL){
                                        $dataEn[$key]['user_id'] = $value->user_id;
                                        $dataEn[$key]['order_on'] = $value->order_on;
                                        $dataEn[$key]['type'] = strtolower($value->getSubType->title) ;
                                        $dataEn[$key]['url'] = $value->getUser->url;
                                        $dataEn[$key]['slug'] = $value->getUser->slug;
                                        $dataEn[$key]['title'] = $value->getUser->title;
                                        $dataEn[$key]['first_name'] = $value->getUser->first_name;
                                        $dataEn[$key]['last_name'] = $value->getUser->last_name;
                                        $dataEn[$key]['middle_name'] = $value->getUser->middle_name;
                                        $dataEn[$key]['status'] = $value->getUser->status;
                                        if($value->getUser->image !=NULL){
                                           $userImage = asset('storage').$value->getUser->image;
                                        }else{
                                           $userImage = asset('storage').'/images/default-img.png';
                                        }

                                        $dataEn[$key]['image'] = $userImage;                                   
                                        $dataEn[$key]['designation'] = $value->getDesignation->name; 
                                        
                                        if ($value->getUser->getContact) {
                                            foreach ($value->getUser->getContact as $val) {
                                                if ($val->email_visibility == '1') {
                                                    $dataEn[$key]['email'] = $val->email;
                                                }
                                                if ($val->mobile_visibility == '1') {
                                                    $dataEn[$key]['mobile_no'] = $val->mobile_no;
                                                }
                                                $dataEn[$key]['telephone_no'] = $val->telephone_no;
                                            }
                                        }else{
                                            $dataEn[$key]['email'] = '';
                                            $dataEn[$key]['mobile_no'] = '';
                                            $dataEn[$key]['telephone_no'] = '';
                                        }
                                        $key++;

                                    }
                                }
                            }
                            if(strpos(request()->slug ,'pg-student') !== false) {                                
                                $faculties = UserVisibility::where('ac_non_ac_id',$linkId->ac_non_ac_id)->where('for_id','3')->where('status','1')->with('getUser.getContact','getUser.getDepartment.getAcNonAcItem','getDesignation','getSubType')->orderBy('order_on','ASC')->orderBy('role_id','ASC')->get();
                                if (empty($faculties)){                        
                                    return $this->sendResponse($faculties, trans($locale.'_lang.DATA_NOT_FOUND'),404);
                                }
                                
                                $key=0;
                                foreach ($faculties as $value) {
                                    if($value->getUser != NULL){
                                        $dataEn[$key]['user_id'] = $value->user_id;
                                        $dataEn[$key]['order_on'] = $value->order_on;
                                        $dataEn[$key]['type'] = strtolower($value->getSubType->title) ;
                                        $dataEn[$key]['url'] = $value->getUser->url;
                                        $dataEn[$key]['slug'] = $value->getUser->slug;
                                        $dataEn[$key]['title'] = $value->getUser->title;
                                        $dataEn[$key]['first_name'] = $value->getUser->first_name;
                                        $dataEn[$key]['last_name'] = $value->getUser->last_name;
                                        $dataEn[$key]['middle_name'] = $value->getUser->middle_name;
                                        $dataEn[$key]['status'] = $value->getUser->status;
                                        if($value->getUser->image !=NULL){
                                           $userImage = asset('storage').$value->getUser->image;
                                        }else{
                                           $userImage = asset('storage').'/images/default-img.png';
                                        }
                                        $dataEn[$key]['image'] = $userImage;                                     
                                        $dataEn[$key]['designation'] = $value->getDesignation->name;  
                                        
                                        if ($value->getUser->getContact) {
                                            foreach ($value->getUser->getContact as $val) {
                                                if ($val->email_visibility == '1') {
                                                    $dataEn[$key]['email'] = $val->email;
                                                }
                                                if ($val->mobile_visibility == '1') {
                                                    $dataEn[$key]['mobile_no'] = $val->mobile_no;
                                                }
                                                $dataEn[$key]['telephone_no'] = $val->telephone_no;
                                            }
                                        }else{
                                            $dataEn[$key]['email'] = '';
                                            $dataEn[$key]['mobile_no'] = '';
                                            $dataEn[$key]['telephone_no'] = '';
                                        }
                                        $key++;

                                    }
                                }
                            }
                            if(strpos(request()->slug ,'retired-faculty-member') !== false) {                                
                                $retire = UserVisibility::where('ac_non_ac_id',$linkId->ac_non_ac_id)->where('status','2')->where('for_id','1')->with('getRetireUser.getContact','getRetireUser.getDepartment.getAcNonAcItem','getDesignation','getSubType')->orderBy('order_on','ASC')->orderBy('role_id','ASC')->get();

                                if (empty($retire)){                        
                                    return $this->sendResponse($retire, trans($locale.'_lang.DATA_NOT_FOUND'),404);
                                }
                                $key=0;
                                foreach ($retire as $value) {
                                    if($value->getRetireUser != NULL){
                                        $dataEn[$key]['user_id'] = $value->user_id;
                                        $dataEn[$key]['order_on'] = $value->order_on;
                                        $dataEn[$key]['type'] = strtolower($value->getSubType->title) ;
                                        $dataEn[$key]['url'] = $value->getRetireUser->url;
                                        $dataEn[$key]['slug'] = $value->getRetireUser->slug;
                                        $dataEn[$key]['title'] = $value->getRetireUser->title;
                                        $dataEn[$key]['first_name'] = $value->getRetireUser->first_name;
                                        $dataEn[$key]['middle_name'] = $value->getRetireUser->middle_name;
                                        $dataEn[$key]['last_name'] = $value->getRetireUser->last_name;
                                        $dataEn[$key]['status'] = $value->getRetireUser->status;
                                        
                                        if($value->getRetireUser->image !=NULL){
                                           $userImage = asset('storage').$value->getRetireUser->image;
                                        }else{
                                           $userImage = asset('storage').'/images/default-img.png';
                                        }
                                        $dataEn[$key]['image'] = $userImage;  
                                        
                                        $dataEn[$key]['designation'] = $value->getDesignation->name; 
                                        
                                        if ($value->getRetireUser->getContact) {
                                            foreach ($value->getRetireUser->getContact as $val) {
                                                if ($val->email_visibility == '1') {
                                                    $dataEn[$key]['email'] = $val->email;
                                                }
                                                if ($val->mobile_visibility == '1') {
                                                    $dataEn[$key]['mobile_no'] = $val->mobile_no;
                                                }
                                                $dataEn[$key]['telephone_no'] = $val->telephone_no;
                                            }
                                        }else{
                                            $dataEn[$key]['email'] = '';
                                            $dataEn[$key]['mobile_no'] = '';
                                            $dataEn[$key]['telephone_no'] = '';
                                        }
                                        $key++;

                                    }
                                }
                            }
                        }elseif(strpos(request()->slug,'list-of-former-chairperson') !== false){
                            $getData = FormerChairPerson::/*where('approval_status','approved')->*/where('ac_non_ac_id',$linkId->ac_non_ac_id)->orderBy('order_on','ASC')->get();

                            
                            foreach ($getData as $key => $value) {
                                $dataEn[$key]['id']  = $value->id;
                                $dataEn[$key]['name'] = $value->name; 
                                $dataEn[$key]['from_date'] = $value->from_date;
                                $dataEn[$key]['till_date'] = $value->till_date;
                                $dataEn[$key]['order_on'] = $value->order_on;
                                $dataEn[$key]['updated_at'] = $value->updated_at;
                                
                            }
                        }elseif( strpos(request()->slug, 'phd') !== false || strpos(request()->slug, 'post-graduate') !== false || strpos(request()->slug, 'under-graduate') !== false || strpos(request()->slug, 'other-program') !== false || strpos(request()->slug, 'mphil-program') !== false || strpos(request()->slug, 'diploma') !== false){  

                            if (strpos(request()->slug, 'phd') !== false) {
                                $getData = Course::where('approval_status','approved')->where('ac_non_ac_id',$linkId->ac_non_ac_id)->where('type','1')->orderBy('order_on','ASC')->get();
                                $latest_update = Course::select('updated_at')->where('ac_non_ac_id',$linkId->ac_non_ac_id)->where('type','1')->orderBy('updated_at','DESC')->first();
                                foreach ($getData as $key => $value) {
                                    $dataEn[$key]['id']  = $value->id;
                                    $dataEn[$key]['name'] = $value->name_en; 
                                    $dataEn[$key]['nos'] = $value->nos_en;
                                    $dataEn[$key]['dr'] = $value->dr_en;
                                    $dataEn[$key]['cr'] = $value->cr_en;
                                    $dataEn[$key]['acrr'] = $value->acrr;
                                    $dataEn[$key]['jp'] = $value->jp_en;
                                    $dataEn[$key]['spec'] = $value->spec_en;
                                    $dataEn[$key]['peo'] = $value->peo_en;
                                    $dataEn[$key]['po'] = $value->po_en;
                                    $dataEn[$key]['currl'] = $value->currl;
                                    $dataEn[$key]['syll'] = $value->syll;
                                }
                            }else if (strpos(request()->slug, 'post-graduate') !== false) {
                               
                                $getData = Course::where('approval_status','approved')->where('ac_non_ac_id',$linkId->ac_non_ac_id)->where('type','2')->orderBy('order_on','ASC')->get();
                                
                                foreach ($getData as $key => $value) {
                                    $dataEn[$key]['id']  = $value->id;
                                    $dataEn[$key]['name'] = $value->name_en; 
                                    $dataEn[$key]['nos'] = $value->nos_en;
                                    $dataEn[$key]['dr'] = $value->dr_en;
                                    $dataEn[$key]['cr'] = $value->cr_en;
                                    $dataEn[$key]['acrr'] = $value->acrr;
                                    $dataEn[$key]['jp'] = $value->jp_en;
                                    $dataEn[$key]['spec'] = $value->spec_en;
                                    $dataEn[$key]['peo'] = $value->peo_en;
                                    $dataEn[$key]['po'] = $value->po_en;
                                    $dataEn[$key]['currl'] = $value->currl;
                                    $dataEn[$key]['syll'] = $value->syll;
                                }

                            }else if (strpos(request()->slug, 'under-graduate') !== false) {
                                
                                $getData = Course::where('approval_status','approved')->where('ac_non_ac_id',$linkId->ac_non_ac_id)->where('type','3')->orderBy('order_on','ASC')->get();
                                
                                foreach ($getData as $key => $value) {
                                    $dataEn[$key]['id']  = $value->id;
                                    $dataEn[$key]['name'] = $value->name_en; 
                                    $dataEn[$key]['nos'] = $value->nos_en;
                                    $dataEn[$key]['dr'] = $value->dr_en;
                                    $dataEn[$key]['cr'] = $value->cr_en;
                                    $dataEn[$key]['acrr'] = $value->acrr;
                                    $dataEn[$key]['jp'] = $value->jp_en;
                                    $dataEn[$key]['spec'] = $value->spec_en;
                                    $dataEn[$key]['peo'] = $value->peo_en;
                                    $dataEn[$key]['po'] = $value->po_en;
                                    $dataEn[$key]['currl'] = $value->currl;
                                    $dataEn[$key]['syll'] = $value->syll;
                                }

                            }else if (strpos(request()->slug, 'other-program') !== false || strpos(request()->slug, 'diploma') !== false) {
                                
                                $getData = Course::where('approval_status','approved')->where('ac_non_ac_id',$linkId->ac_non_ac_id)->where('type','4')->orderBy('order_on','ASC')->get();
                                
                                foreach ($getData as $key => $value) {
                                    $dataEn[$key]['id']  = $value->id;
                                    $dataEn[$key]['name'] = $value->name_en; 
                                    $dataEn[$key]['nos'] = $value->nos_en;
                                    $dataEn[$key]['dr'] = $value->dr_en;
                                    $dataEn[$key]['cr'] = $value->cr_en;
                                    $dataEn[$key]['acrr'] = $value->acrr;
                                    $dataEn[$key]['jp'] = $value->jp_en;
                                    $dataEn[$key]['spec'] = $value->spec_en;
                                    $dataEn[$key]['peo'] = $value->peo_en;
                                    $dataEn[$key]['po'] = $value->po_en;
                                    $dataEn[$key]['currl'] = $value->currl;
                                    $dataEn[$key]['syll'] = $value->syll;
                                }

                            }else if (strpos(request()->slug, 'mphil-program') !== false) {
                                
                                $getData = Course::where('approval_status','approved')->where('ac_non_ac_id',$linkId->ac_non_ac_id)->where('type','5')->orderBy('order_on','ASC')->get();
                                
                                foreach ($getData as $key => $value) {
                                    $dataEn[$key]['id']  = $value->id;
                                    $dataEn[$key]['name'] = $value->name_en; 
                                    $dataEn[$key]['nos'] = $value->nos_en;
                                    $dataEn[$key]['dr'] = $value->dr_en;
                                    $dataEn[$key]['cr'] = $value->cr_en;
                                    $dataEn[$key]['acrr'] = $value->acrr;
                                    $dataEn[$key]['jp'] = $value->jp_en;
                                    $dataEn[$key]['spec'] = $value->spec_en;
                                    $dataEn[$key]['peo'] = $value->peo_en;
                                    $dataEn[$key]['po'] = $value->po_en;
                                    $dataEn[$key]['currl'] = $value->currl;
                                    $dataEn[$key]['syll'] = $value->syll;
                                }
                            }
                        }elseif(strpos(request()->slug,'on-going-research-projects') !== false || strpos(request()->slug ,'completed-research-projects') !== false){
                            if(strpos(request()->slug, 'on-going-research-projects') !== false) {
                                $getData = OnGoingProject::/*where('approval_status','approved')->*/where('ac_non_ac_id',$linkId->ac_non_ac_id)->orderBy('order_on','ASC')->orderBy('id','ASC')->get(); 
                                
                                foreach ($getData as $key => $value) {
                                    $dataEn[$key]['id'] = $value->id;
                                    $dataEn[$key]['about'] = $value->about_en; 
                                    $dataEn[$key]['fagency'] = $value->fagency;
                                    $dataEn[$key]['famount'] = $value->famount;
                                    $dataEn[$key]['pi'] = $value->pi;
                                    $dataEn[$key]['cpi'] = $value->cpi;
                                    $dataEn[$key]['dt'] = $value->dt;
                                    $dataEn[$key]['updated_at'] = $value->updated_at;
                                }
                            }
                            if(strpos(request()->slug, 'completed-research-projects') !== false) {
                                $getData = CompletedProject::/*where('approval_status','approved')->*/where('ac_non_ac_id',$linkId->ac_non_ac_id)->orderBy('order_on','ASC')->orderBy('id','ASC')->get();
                                
                                foreach ($getData as $key => $value) {
                                    $dataEn[$key]['id'] = $value->id;
                                    $dataEn[$key]['about'] = $value->about_en; 
                                    $dataEn[$key]['fagency'] = $value->fagency;
                                    $dataEn[$key]['famount'] = $value->famount;
                                    $dataEn[$key]['pi'] = $value->pi;
                                    $dataEn[$key]['cpi'] = $value->cpi;
                                    $dataEn[$key]['pub'] = $value->pub_en;
                                    $dataEn[$key]['awards'] = $value->awards_en;
                                    $dataEn[$key]['dt'] = $value->dt;
                                    $dataEn[$key]['updated_at'] = $value->updated_at;
                                }
                            }
                        }elseif( strpos(request()->slug, 'notice-and-circular') !== false ){
                            $dataEn = NoticeCircular::select('id','ac_non_ac_id','title_en as title','to_date','file','status','approval_status','created_at','updated_at')->where('approval_status','Approved')->where('ac_non_ac_id',$linkId->ac_non_ac_id)->orderBy('created_at','desc')
                            ->when(request()->search, function ($query) {
                                $query->where('title_en', 'LIKE', '%'.request()->search.'%');
                            })->paginate(env('ITEM_PER_PAGE'));

                            
                            foreach ($dataEn as $key => $value) {

                                $curDateTime = date("Y-m-d H:i:s");
                                $days_ago = date('Y-m-d', strtotime('-15 days', strtotime($curDateTime)));
                                $created_at = date('Y-m-d', strtotime($value->created_at));
                                if($created_at > $days_ago){
                                   $value['new'] = 1;    
                                }else{
                                   $value['new'] = 0;    
                                }
                               
                            }

                        }elseif (strpos(request()->slug, 'photo-gallery') !== false) {
                            $getData = AcNonAcGallery::where('ac_non_ac_id',$linkId->ac_non_ac_id)->orderBy('order_on','ASC')->get();
                            
                            foreach ($getData as $key => $value) {
                                $dataEn[$key]['id'] = $value->id;
                                $dataEn[$key]['title'] = $value->title_en;
                                $dataEn[$key]['image'] = $value->image;
                                $dataEn[$key]['updated_at'] = $value->updated_at;
                            }                            
                       }else{
                            $getData = RelatedLinkData::/*where('approval_status','approved')->*/where('related_link_id',$linkId->id)->orderBy('created_at','desc')->orderBy('order_on','ASC')->get();
                            
                            foreach ($getData as $key => $value) {
                                $dataEn[$key]['id'] = $value->id;
                                $dataEn[$key]['link'] = $value->link_en; 
                                $dataEn[$key]['link_description'] = $value->link_description_en;
                                $dataEn[$key]['file'] = $value->file;
                                $dataEn[$key]['updated_at'] = $value->updated_at;
                            } 
                        }                      
                        if ($dataEn){ 
                            $seo_title = $linkId->link_name_en.' - '.$linkId->getAcAndNonAc->title_en;
                            $data['seo_title'] = $seo_title.' | AMU';
                            $data['data'] = $dataEn;
                            $latest_update = UserLog::select('updated_at')->where('ac_non_ac_id',$linkId->ac_non_ac_id)->orderBy('updated_at','DESC')->first();
                            if ($latest_update) {
                                $data['last_update'] = $latest_update->updated_at;
                            }else{
                                $data['last_update'] = '';
                            }
                                                   
                            return $this->sendResponse($data, trans($locale.'_lang.DATA_FOUND'),200);
                        } else {
                            return $this->sendResponse($dataEn, trans($locale.'_lang.DATA_NOT_FOUND'),404);
                        }
                    break;
                }
            }catch (Exception $e) {
               return $this->sendError(trans($locale.'_lang.MODEL_NOT_FOUND'), $e->getMessage(), 404);
            }
        }
    }

    // Get Departments Ticker 
    public function getFeaturedTicker(Request $request) {
        $validator = Validator::make($request->all(), [
            'lang' => 'required|in:en,hi,ur',
            'slug' => 'required',
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
                        $deptID = AcademicAndNonAcademic::where('slug',request()->slug)->pluck('id');                           
                        $notifications = Notification::where('ac_non_ac_id',$deptID)->orderBy('order_on','asc')->where('featured','1')->where('status','1')->where('to_date', '>=', date('Y-m-d'))->where('approval_status','Approved')->get();
                        if (empty($notifications)){                        
                            return $this->sendResponse($notifications, trans($locale.'_lang.DATA_NOT_FOUND'),404);
                        }
                        foreach ($notifications as $key => $notification) {
                            $dataUR[$key]['id'] = $notification->id;
                            if($notification->title_ur == NULL){
                                $dataUR[$key]['title'] = $notification->title_en;  
                            }else{
                                $dataUR[$key]['title'] = $notification->title_ur; 
                            }
                            $dataUR[$key]['file'] = $notification->file;
                            $dataUR[$key]['hyperlink'] = $notification->hyperlink;
                            $dataUR[$key]['featured'] = $notification->featured;
                            $dataUR[$key]['status'] = $notification->status;
                            $dataUR[$key]['date'] =  date("F d, Y", strtotime($notification->created_at));
                            $dataUR[$key]['to_date'] =  date("F d, Y", strtotime($notification->to_date));
                            
                        }
                        if ($dataUR){                        
                            return $this->sendResponse($dataUR, trans($locale.'_lang.DATA_FOUND'),200);
                        } else {
                            return $this->sendResponse($dataUR, trans($locale.'_lang.DATA_NOT_FOUND'),404);
                        }
                        break;
                    case "hi": //GET widget IN Hindi
                        $dataHI = array();
                        $deptID = AcademicAndNonAcademic::where('slug',request()->slug)->pluck('id');                           
                        $notifications = Notification::where('ac_non_ac_id',$deptID)->orderBy('order_on','asc')->where('featured','1')->where('status','1')->where('to_date', '>=', date('Y-m-d'))->where('approval_status','Approved')->get();
                        if (empty($notifications)){                        
                            return $this->sendResponse($notifications, trans($locale.'_lang.DATA_NOT_FOUND'),404);
                        }
                        foreach ($notifications as $key => $notification) {
                            $dataHI[$key]['id'] = $notification->id;
                            if($notification->title_hi == NULL){
                                $dataHI[$key]['title'] = $notification->title_en;  
                            }else{
                                $dataHI[$key]['title'] = $notification->title_hi; 
                            }
                            $dataHI[$key]['file'] = $notification->file;
                            $dataHI[$key]['hyperlink'] = $notification->hyperlink;
                            $dataHI[$key]['featured'] = $notification->featured;
                            $dataHI[$key]['status'] = $notification->status;
                            $dataHI[$key]['date'] =   date("F d, Y", strtotime($notification->created_at));
                            $dataHI[$key]['to_date'] =   date("F d, Y", strtotime($notification->to_date));
                            
                        }
                        if ($dataHI){                        
                            return $this->sendResponse($dataHI, trans($locale.'_lang.DATA_FOUND'),200);
                        } else {
                            return $this->sendResponse($dataHI, trans($locale.'_lang.DATA_NOT_FOUND'),404);
                        }
                        break;
                    default: //GET widget IN English
                        $data = array();
                        $deptID = AcademicAndNonAcademic::where('slug',request()->slug)->pluck('id');  

                        $notifications = Notification::where('ac_non_ac_id',$deptID)->orderBy('order_on','asc')->where('featured','1')->where('status','1')->where('to_date', '>=', date('Y-m-d'))->where('approval_status','Approved')->get(); 
                        if (empty($notifications)){                        
                            return $this->sendResponse($notifications, trans($locale.'_lang.DATA_NOT_FOUND'),404);
                        }
                        foreach ($notifications as $key => $notification) {
                            $data[$key]['id'] = $notification->id;
                            $data[$key]['title'] = $notification->title_en; 
                            $data[$key]['file'] = $notification->file;
                            $data[$key]['hyperlink'] = $notification->hyperlink;
                            $data[$key]['featured'] = $notification->featured;
                            $data[$key]['status'] = $notification->status;
                            $data[$key]['date'] =  date("F d, Y", strtotime($notification->created_at));
                            $data[$key]['to_date'] =  date("F d, Y", strtotime($notification->to_date));
                        }
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

    /********Research Scholar Functions Start************/
    public function getResearchScholarList(Request $request) {
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
                        $researchScholars = ResearchScholars::with('getDepartment')->when(request()->dept, function ($query) {
                              $query->where('ac_non_ac_id', '=' , request()->dept)->orderBy('order_on','asc');
                              })->when(request()->search, function ($query) {
                              $query->where('name', 'LIKE', '%'.request()->search.'%')->orWhere('regno', 'LIKE', '%'.request()->search.'%')->orderBy('order_on','asc');
                              })->orderBy('name','asc')->paginate(env('ITEM_PER_PAGE'));
                        if (empty($researchScholars)){                        
                            return $this->sendResponse($departments, trans($locale.'_lang.DATA_NOT_FOUND'),404);
                        }
                        $dataUR['data'] = $researchScholars;
                        if(request()->dept){
                            $dataUR['department'] = request()->dept;
                        }else{
                            $dataUR['department'] = '';
                        }
                        if(request()->search){
                            $dataUR['search'] = request()->search;
                        }else{
                            $dataUR['search'] = '';
                        }
                        if ($dataUR){                        
                            return $this->sendResponse($dataUR, trans($locale.'_lang.DATA_FOUND'),200);
                        } else {
                            return $this->sendResponse($dataUR, trans($locale.'_lang.DATA_NOT_FOUND'),404);
                        }
                        break;
                    case "hi": //GET widget IN Hindi
                        $dataHI = array();
                        $researchScholars = ResearchScholars::with('getDepartment')->when(request()->dept, function ($query) {
                              $query->where('ac_non_ac_id', '=' , request()->dept)->orderBy('order_on','asc');
                              })->when(request()->search, function ($query) {
                              $query->where('name', 'LIKE', '%'.request()->search.'%')->orWhere('regno', 'LIKE', '%'.request()->search.'%')->orderBy('order_on','asc');
                              })->orderBy('name','asc')->paginate(env('ITEM_PER_PAGE'));
                        if (empty($researchScholars)){                        
                            return $this->sendResponse($departments, trans($locale.'_lang.DATA_NOT_FOUND'),404);
                        }
                        $dataHI['data'] = $researchScholars;
                        if(request()->dept){
                            $dataHI['department'] = request()->dept;
                        }else{
                            $dataHI['department'] = '';
                        }
                        if(request()->search){
                            $dataHI['search'] = request()->search;
                        }else{
                            $dataHI['search'] = '';
                        }
                        if ($dataHI){                        
                            return $this->sendResponse($dataHI, trans($locale.'_lang.DATA_FOUND'),200);
                        } else {
                            return $this->sendResponse($dataHI, trans($locale.'_lang.DATA_NOT_FOUND'),404);
                        }
                        break;
                    default: //GET widget IN English
                        $data = array();
                        $researchScholars = ResearchScholars::with('getDepartment')->when(request()->dept, function ($query) {
                              $query->where('ac_non_ac_id', '=' , request()->dept)->orderBy('order_on','asc');
                              })->when(request()->search, function ($query) {
                              $query->where('name', 'LIKE', '%'.request()->search.'%')->orWhere('regno', 'LIKE', '%'.request()->search.'%')->orderBy('order_on','asc');
                              })->orderBy('name','asc')->paginate(env('ITEM_PER_PAGE'));
                        if (empty($researchScholars)){                        
                            return $this->sendResponse($departments, trans($locale.'_lang.DATA_NOT_FOUND'),404);
                        }
                        $data['data'] = $researchScholars;
                        if(request()->dept){
                            $data['department'] = request()->dept;
                        }else{
                            $data['department'] = '';
                        }
                        if(request()->search){
                            $data['search'] = request()->search;
                        }else{
                            $data['search'] = '';
                        }
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

    public function getResearchScholarDetail(Request $request) {
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
                        $researchScholar = ResearchScholars::where('id',request()->id)->with('getDepartment')->first();
                        if (empty($researchScholar)){                        
                            return $this->sendResponse($departments, trans($locale.'_lang.DATA_NOT_FOUND'),404);
                        }
                        $dataUR['enrolno'] = $researchScholar->enrolno;
                        $dataUR['name'] = $researchScholar->name;
                        $dataUR['dept'] = $researchScholar->getDepartment->title_en;
                        $dataUR['faculty'] = $researchScholar->faculty;
                        $dataUR['regno'] = $researchScholar->regno;
                        $dataUR['regdate'] = date("F d, Y", strtotime($researchScholar->regdate));
                        $dataUR['completiondate'] = date("F d, Y", strtotime($researchScholar->completiondate));
                        $dataUR['supervisor'] = $researchScholar->supervisor;
                        $dataUR['topic'] = $researchScholar->topic;
                        $dataUR['availingfellowship'] = $researchScholar->availingfellowship;
                        $dataUR['fundingagency'] = $researchScholar->fundingagency;
                        $dataUR['mode'] = $researchScholar->mode;
                        $dataUR['status'] = $researchScholar->status;
                        $dataUR['submit_awardRef'] = $researchScholar->submit_awardRef;
                        $dataUR['idtype'] = $researchScholar->idtype;
                        $dataUR['idno'] = $researchScholar->idno;
                        $dataUR['image'] = $researchScholar->image ? $researchScholar->image : '/images/default-img.png' ;
                        $dataUR['Remark'] = $researchScholar->Remark;
                        
                        if ($dataUR){                        
                            return $this->sendResponse($dataUR, trans($locale.'_lang.DATA_FOUND'),200);
                        } else {
                            return $this->sendResponse($dataUR, trans($locale.'_lang.DATA_NOT_FOUND'),404);
                        }

                        break;
                    case "hi": //GET widget IN Hindi
                        $dataHI = array();
                        $researchScholar = ResearchScholars::where('id',request()->id)->with('getDepartment')->first();
                        if (empty($researchScholar)){                        
                            return $this->sendResponse($departments, trans($locale.'_lang.DATA_NOT_FOUND'),404);
                        }
                        $dataHI['enrolno'] = $researchScholar->enrolno;
                        $dataHI['name'] = $researchScholar->name;
                        $dataHI['dept'] = $researchScholar->getDepartment->title_en;
                        $dataHI['faculty'] = $researchScholar->faculty;
                        $dataHI['regno'] = $researchScholar->regno;
                        $dataHI['regdate'] = date("F d, Y", strtotime($researchScholar->regdate));
                        $dataHI['completiondate'] = date("F d, Y", strtotime($researchScholar->completiondate));
                        $dataHI['supervisor'] = $researchScholar->supervisor;
                        $dataHI['topic'] = $researchScholar->topic;
                        $dataHI['availingfellowship'] = $researchScholar->availingfellowship;
                        $dataHI['fundingagency'] = $researchScholar->fundingagency;
                        $dataHI['mode'] = $researchScholar->mode;
                        $dataHI['status'] = $researchScholar->status;
                        $dataHI['submit_awardRef'] = $researchScholar->submit_awardRef;
                        $dataHI['idtype'] = $researchScholar->idtype;
                        $dataHI['idno'] = $researchScholar->idno;
                        $dataHI['image'] = $researchScholar->image ? $researchScholar->image : '/images/default-img.png' ;
                        $dataHI['Remark'] = $researchScholar->Remark;
                        if ($dataHI){                        
                            return $this->sendResponse($dataHI, trans($locale.'_lang.DATA_FOUND'),200);
                        } else {
                            return $this->sendResponse($dataHI, trans($locale.'_lang.DATA_NOT_FOUND'),404);
                        }
                        break;
                    default: //GET widget IN English
                        $data = array();
                        $researchScholar = ResearchScholars::where('id',request()->id)->with('getDepartment')->first();
                        if (empty($researchScholar)){                        
                            return $this->sendResponse($departments, trans($locale.'_lang.DATA_NOT_FOUND'),404);
                        }
                        $data['enrolno'] = $researchScholar->enrolno;
                        $data['name'] = $researchScholar->name;
                        $data['dept'] = $researchScholar->getDepartment->title_en;
                        $data['faculty'] = $researchScholar->faculty;
                        $data['regno'] = $researchScholar->regno;
                        $data['regdate'] = $researchScholar->regdate;
                        $data['completiondate'] = $researchScholar->completiondate;
                        $data['supervisor'] = $researchScholar->supervisor;
                        $data['topic'] = $researchScholar->topic;
                        $data['availingfellowship'] = $researchScholar->availingfellowship;
                        $data['fundingagency'] = $researchScholar->fundingagency;
                        $data['mode'] = $researchScholar->mode;
                        $data['status'] = $researchScholar->status;
                        $data['submit_awardRef'] = $researchScholar->submit_awardRef;
                        $data['idtype'] = $researchScholar->idtype;
                        $data['idno'] = $researchScholar->idno;
                        $data['image'] = $researchScholar->image ? $researchScholar->image : '/images/default-img.png' ;
                        $data['Remark'] = $researchScholar->Remark;
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
    /********Research Scholar Functions End************/

    // Store Academic Alumni.
    public function storeAlumni(Request $request)
    {

        $input = $request->all();
        $validator = Validator::make($request->all(), [
            'slug' => 'required',
            'enrolno' => 'required',
            'name' => 'required|max:200',
            'email' => 'required|email:rfc,dns',
            'affiliation' => 'required',
            'job' => 'required',
            'gender' => 'required',
        ]);
        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors(), 422, false);
        } else {
            $data = array();
            $academic =  AcademicAndNonAcademic::where('slug', request()->slug)->first();

            $ac_non_ac_id = $academic->id;
            if ($request->isMethod('post')) {

                    $data['enrolno']      = $request->enrolno;
                    $data['did']          = $ac_non_ac_id;
                    $data['title']        = $request->title;
                    $data['name']         = $request->name;
                    $data['gender']       = $request->gender;
                    $data['phd_year']     = $request->phd_year;
                    $data['mphill_year']  = $request->mphill_year;
                    $data['msc_year']     = $request->msc_year;
                    $data['diploma_year'] = $request->diploma_year;
                    $data['certificate_year'] = $request->certificate_year;
                    $data['affiliation']      = $request->affiliation;
                    $data['aff_institutes']   = $request->aff_institutes;
                    $data['add_of_aff_Institute'] = $request->add_of_aff_Institute;
                    $data['job']           = $request->job;
                    $data['present_desg']  = $request->present_desg;
                    $data['address']       = $request->address;
                    $data['teleno']        = $request->teleno;
                    $data['mobile']        = $request->mobile;
                    $data['email']         = $request->email;
                    $data['intent_of_part']= $request->intent_of_part;
                    $data['status']        = '1';
                    $data = Alumni::create($data);

                    if ($data) {
                       /* $logtype = LogType::select('id')->where('type', '=', 'academic')->first();
                        if (is_null($logtype)) {
                            return $this->sendResponse($logtype, 'Log type data not found', 404);
                        } else {
                            $dta = array();
                            $dta['log_type_id'] = $logtype['id'];
                            $dta['user_id'] = $user_id;
                            $dta['ip'] = $request->ip();
                            $dta['action'] = 'added new alumni';
                            $dta['ac_non_ac_id'] = $ac_non_ac_id;
                            $dta['related_link_id'] = 'alumni-registration';
                            UserLogSave($dta);
                        }*/
                        return $this->sendResponse($data, trans('en_lang.DATA_CREATED'), 201);
                    } else {
                        return $this->sendResponse($data, 'Some thing went wrong', 204, false);
                    }
            } else {
                return $this->sendResponse($data,'Some thing went wrong', 204, false);
            }
        }
    }
}