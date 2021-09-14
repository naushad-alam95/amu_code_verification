<?php

namespace App\Http\Controllers\API\v1;

use App\Http\Controllers\API\v1\BaseController as BaseController;
use Illuminate\Http\Request;
use App\AcademicAndNonAcademic;
use App\AcNonAcGallery;
use App\OnGoingProject;
use App\CompletedProject;
use App\SubType;
use App\Widget;
use App\NoticeCircular;
use App\UserVisibility;
use App\RelatedLink;
use App\CustomRelatedLink;
use App\RelatedLinkData;
use App\MappingDepartments;
Use App\GroupLink;
use App\VCLink;
use Validator;

class NonAcademicController extends BaseController
{

    // Get Non Academic list
    public function getNonAcademicList(Request $request) {
        $validator = Validator::make($request->all(), [
            'lang' => 'required|in:en,hi,ur',
            'type' => 'required',
            
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
                $type =  SubType::select('id')->where('title', request()->type)->first();
                if (empty($type)){                        
                    return $this->sendResponse($type, trans($locale.'_lang.DATA_NOT_FOUND'),404);
                }
                if($type->id == 5){
                    $non_ac_list = AcademicAndNonAcademic::orderBy('order_on','asc')->whereIn('id', ['200','10001','10002','10003','10005','10006','10009','10010','10011','10014','10020','10022','10115','10170','10205','10225','10234','10262','10266','10275','10324','10329','10328'])->where('status', '1')->get(); 
                }else{
                    $non_ac_list = AcademicAndNonAcademic::orderBy('order_on','asc')->where('sub_type', $type->id)->where('status', '1')->where('type', '2')->get(); 
                }
                 
                if (empty($non_ac_list)){                        
                    return $this->sendResponse($non_ac_list, trans($locale.'_lang.DATA_NOT_FOUND'),404);
                }               
                $datawidget = array(); 
                $data = array();
                $widget = Widget::where('slug',request()->type)->first();
                switch ($locale) {
                    case "ur": 
                        $dataUR = array();                         
                        foreach ($non_ac_list as $key => $list) {
                            $dataUR[$key]['id'] = $list->id;
                            if($list->title_ur == NULL){
                                $dataUR[$key]['title'] = $list->title_en;  
                            }else{
                                $dataUR[$key]['title'] = $list->title_ur; 
                            } 
                            if($list->description_ur == NULL){
                                $dataUR[$key]['description'] = $list->description_en;  
                            }else{
                                $dataUR[$key]['description'] = $list->description_ur; 
                            }                            
                            $dataUR[$key]['slug'] = $list->slug;
                            $dataUR[$key]['image'] = $list->image;
                        }
                        $data['list'] = $dataUR;
                        if (!empty($widget)) {
                            $datawidget['id'] = $widget->id;
                            $datawidget['title'] = $widget->title_en; 
                            $datawidget['description'] = $widget->description_en;                           
                            $data['widget'] = $datawidget;
                        }
                        if ($data){                        
                            return $this->sendResponse($data, trans($locale.'_lang.DATA_FOUND'),200);
                        } else {
                            return $this->sendResponse($data, trans($locale.'_lang.DATA_NOT_FOUND'),404);
                        }
                        break;
                    case "hi": 
                        $dataHI = array();
                        foreach ($non_ac_list as $key => $list) {
                            $dataHI[$key]['id'] = $list->id;
                            if($list->title_hi == NULL){
                                $dataHI[$key]['title'] = $list->title_en;  
                            }else{
                                $dataHI[$key]['title'] = $list->title_hi; 
                            } 
                            if($list->description_hi == NULL){
                                $dataHI[$key]['description'] = $list->description_en;  
                            }else{
                                $dataHI[$key]['description'] = $list->description_hi; 
                            }                            
                            $dataHI[$key]['slug'] = $list->slug;
                            $dataHI[$key]['image'] = $list->image;
                        }
                        $data['list'] = $dataHI;
                        if (!empty($widget)) {
                            $datawidget['id'] = $widget->id;
                            $datawidget['title'] = $widget->title_en; 
                            $datawidget['description'] = $widget->description_en;                           
                            $data['widget'] = $datawidget;
                        }
                        if ($data){                        
                            return $this->sendResponse($data, trans($locale.'_lang.DATA_FOUND'),200);
                        } else {
                            return $this->sendResponse($data, trans($locale.'_lang.DATA_NOT_FOUND'),404);
                        }
                        break;
                    default: 
                        $dataEN = array();
                        foreach ($non_ac_list as $key => $list) {
                            $dataEN[$key]['id'] = $list->id;
                            $dataEN[$key]['title'] = $list->title_en;
                            $dataEN[$key]['description'] = $list->description_en;                            
                            $dataEN[$key]['slug'] = $list->slug;
                            $dataEN[$key]['image'] = $list->image;

                        }
                        $data['list'] = $dataEN;
                        if (!empty($widget)) {
                            $datawidget['id'] = $widget->id;
                            $datawidget['title'] = $widget->title_en; 
                            $datawidget['description'] = $widget->description_en;                           
                            $data['widget'] = $datawidget;
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

    // Get Non Academic list
    public function getHallList(Request $request) {
        $validator = Validator::make($request->all(), [
            'lang' => 'required|in:en,hi,ur',
            'type' => 'required',
            
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
                
                $girls = AcademicAndNonAcademic::orderBy('order_on','asc')->orderBy('title_en','asc')->where('sub_type', '9')->where('status', '1')->where('gender', 'f')->where('type', '2')->get();
                $boys = AcademicAndNonAcademic::orderBy('order_on','asc')->orderBy('title_en','asc')->where('gender', 'm')->where('sub_type', '9')->where('status', '1')->where('type', '2')->get(); 
                if (empty($girls) || empty($boys)){                        
                    return $this->sendResponse($boys, trans($locale.'_lang.DATA_NOT_FOUND'),404);
                }               
                $datawidget = array(); 
                $data = array();
                $widget = Widget::where('slug',request()->type)->first();
                switch ($locale) {
                    case "ur": 
                        $dataGh = array();
                        $dataBh = array();
                        foreach ($girls as $key => $g) {
                            if ($g->gender == 'f') {
                                $dataGh[$key]['id'] = $g->id;
                                if($g->title_ur == NULL){
                                    $dataGh[$key]['title'] = $g->title_en;  
                                }else{
                                    $dataGh[$key]['title'] = $g->title_ur; 
                                } 
                                if($g->description_ur == NULL){
                                    $dataGh[$key]['description'] = $g->description_en;  
                                }else{
                                    $dataGh[$key]['description'] = $g->description_ur; 
                                }
                                $dataGh[$key]['slug'] = $g->slug;
                                $dataGh[$key]['image'] = $g->image;
                                $dataGh[$key]['gender'] = $g->gender;
                            }
                        }
                        foreach ($boys as $key => $b) {
                            if ($b->gender == 'm') {
                                $dataBh[$key]['id'] = $b->id;
                               if($b->title_ur == NULL){
                                    $dataBh[$key]['title'] = $b->title_en;  
                                }else{
                                    $dataBh[$key]['title'] = $b->title_ur; 
                                } 
                                if($b->description_ur == NULL){
                                    $dataBh[$key]['description'] = $b->description_en;  
                                }else{
                                    $dataBh[$key]['description'] = $b->description_ur; 
                                }
                                $dataBh[$key]['slug'] = $b->slug;
                                $dataBh[$key]['image'] = $b->image;
                                $dataBh[$key]['gender'] = $b->gender;
                            }
                        }
                        $data['girls_hall'] = $dataGh;
                        $data['boys_hall'] = $dataBh;
                        if (!empty($widget)) {
                            $datawidget['id'] = $widget->id;
                            if($b->title_ur == NULL){
                                    $datawidget['title'] = $widget->title_en;  
                                }else{
                                    $datawidget['title'] = $widget->title_ur; 
                                } 
                                if($b->description_ur == NULL){
                                    $datawidget['description'] = $widget->description_en;  
                                }else{
                                    $datawidget['description'] = $widget->description_ur; 
                                }                           
                            $data['widget'] = $datawidget;
                        }
                        if ($data){                        
                            return $this->sendResponse($data, trans($locale.'_lang.DATA_FOUND'),200);
                        } else {
                            return $this->sendResponse($data, trans($locale.'_lang.DATA_NOT_FOUND'),404);
                        }
                        break;
                    case "hi": 
                        $dataGh = array();
                        $dataBh = array();
                        foreach ($girls as $key => $g) {
                            if ($g->gender == 'f') {
                                $dataGh[$key]['id'] = $g->id;
                                if($g->title_hi == NULL){
                                    $dataGh[$key]['title'] = $g->title_en;  
                                }else{
                                    $dataGh[$key]['title'] = $g->title_hi; 
                                } 
                                if($g->description_hi == NULL){
                                    $dataGh[$key]['description'] = $g->description_en;  
                                }else{
                                    $dataGh[$key]['description'] = $g->description_hi; 
                                }
                                $dataGh[$key]['slug'] = $g->slug;
                                $dataGh[$key]['image'] = $g->image;
                                $dataGh[$key]['gender'] = $g->gender;
                            }
                        }
                        foreach ($boys as $key => $b) {
                            if ($b->gender == 'm') {
                                $dataBh[$key]['id'] = $b->id;
                               if($b->title_hi == NULL){
                                    $dataBh[$key]['title'] = $b->title_en;  
                                }else{
                                    $dataBh[$key]['title'] = $b->title_hi; 
                                } 
                                if($b->description_hi == NULL){
                                    $dataBh[$key]['description'] = $b->description_en;  
                                }else{
                                    $dataBh[$key]['description'] = $b->description_hi; 
                                }
                                $dataBh[$key]['slug'] = $b->slug;
                                $dataBh[$key]['image'] = $b->image;
                                $dataBh[$key]['gender'] = $b->gender;
                            }
                        }
                        $data['girls_hall'] = $dataGh;
                        $data['boys_hall'] = $dataBh;
                        if (!empty($widget)) {
                            $datawidget['id'] = $widget->id;
                            if($b->title_hi == NULL){
                                    $datawidget['title'] = $widget->title_en;  
                                }else{
                                    $datawidget['title'] = $widget->title_hi; 
                                } 
                                if($b->description_hi == NULL){
                                    $datawidget['description'] = $widget->description_en;  
                                }else{
                                    $datawidget['description'] = $widget->description_hi; 
                                }                           
                            $data['widget'] = $datawidget;
                        }
                        if ($data){                        
                            return $this->sendResponse($data, trans($locale.'_lang.DATA_FOUND'),200);
                        } else {
                            return $this->sendResponse($data, trans($locale.'_lang.DATA_NOT_FOUND'),404);
                        }
                        break;
                    default: 
                        $dataGh = array();
                        $dataBh = array();
                        foreach ($girls as $key => $g) {
                            if ($g->gender == 'f') {
                                $dataGh[$key]['id'] = $g->id;
                                $dataGh[$key]['title'] = $g->title_en;
                                $dataGh[$key]['description'] = $g->description_en;
                                $dataGh[$key]['slug'] = $g->slug;
                                $dataGh[$key]['image'] = $g->image;
                                $dataGh[$key]['gender'] = $g->gender;
                            }
                        }
                        foreach ($boys as $key => $b) {
                            if ($b->gender == 'm') {
                                $dataBh[$key]['id'] = $b->id;
                                $dataBh[$key]['title'] = $b->title_en;
                                $dataBh[$key]['description'] = $b->description_en;
                                $dataBh[$key]['slug'] = $b->slug;
                                $dataBh[$key]['image'] = $b->image;
                                $dataBh[$key]['gender'] = $b->gender;
                            }
                        }
                        $data['girls_hall'] = $dataGh;
                        $data['boys_hall'] = $dataBh;
                        if (!empty($widget)) {
                            $datawidget['id'] = $widget->id;
                            $datawidget['title'] = $widget->title_en; 
                            $datawidget['description'] = $widget->description_en;                           
                            $data['widget'] = $datawidget;
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

    // Get AMU special center list
    public function getSpecialCenterList(Request $request) {
        $validator = Validator::make($request->all(), [
            'lang' => 'required|in:en,hi,ur',
            'type' => 'required',
            
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
                /*$type =  SubType::select('id')->where('title', request()->type)->first();
                if (empty($type)){                        
                    return $this->sendResponse($type, trans($locale.'_lang.DATA_NOT_FOUND'),404);
                }*/
                $non_ac_list = AcademicAndNonAcademic::whereIn('id',[400,402,10207])->orderBy('order_on','asc')->where('status', '1')->get(); 
                if (empty($non_ac_list)){                        
                    return $this->sendResponse($non_ac_list, trans($locale.'_lang.DATA_NOT_FOUND'),404);
                }               
                $datawidget = array(); 
                $data = array();
                $widget = Widget::where('slug',request()->type)->first();
                switch ($locale) {
                    case "ur": 
                        $dataUR = array();                         
                        foreach ($non_ac_list as $key => $list) {
                            $dataUR[$key]['id'] = $list->id;
                            if($list->title_ur == NULL){
                                $dataUR[$key]['title'] = $list->title_en;  
                            }else{
                                $dataUR[$key]['title'] = $list->title_ur; 
                            } 
                            if($list->description_ur == NULL){
                                $dataUR[$key]['description'] = $list->description_en;  
                            }else{
                                $dataUR[$key]['description'] = $list->description_ur; 
                            }                            
                            $dataUR[$key]['slug'] = $list->slug;
                            $dataUR[$key]['image'] = $list->image;
                            $dataUR[$key]['type'] = $list->type;
                        }
                        $data['list'] = $dataUR;
                        if (!empty($widget)) {
                            $datawidget['id'] = $widget->id;
                            $datawidget['title'] = $widget->title_en; 
                            $datawidget['description'] = $widget->description_en;                           
                            $data['widget'] = $datawidget;
                        }
                        if ($data){                        
                            return $this->sendResponse($data, trans($locale.'_lang.DATA_FOUND'),200);
                        } else {
                            return $this->sendResponse($data, trans($locale.'_lang.DATA_NOT_FOUND'),404);
                        }
                        break;
                    case "hi": 
                        $dataHI = array();
                        foreach ($non_ac_list as $key => $list) {
                            $dataHI[$key]['id'] = $list->id;
                            if($list->title_hi == NULL){
                                $dataHI[$key]['title'] = $list->title_en;  
                            }else{
                                $dataHI[$key]['title'] = $list->title_hi; 
                            } 
                            if($list->description_hi == NULL){
                                $dataHI[$key]['description'] = $list->description_en;  
                            }else{
                                $dataHI[$key]['description'] = $list->description_hi; 
                            }                            
                            $dataHI[$key]['slug'] = $list->slug;
                            $dataHI[$key]['image'] = $list->image;
                            $dataHI[$key]['type'] = $list->type;
                        }
                        $data['list'] = $dataHI;
                        if (!empty($widget)) {
                            $datawidget['id'] = $widget->id;
                            $datawidget['title'] = $widget->title_en; 
                            $datawidget['description'] = $widget->description_en;                           
                            $data['widget'] = $datawidget;
                        }
                        if ($data){                        
                            return $this->sendResponse($data, trans($locale.'_lang.DATA_FOUND'),200);
                        } else {
                            return $this->sendResponse($data, trans($locale.'_lang.DATA_NOT_FOUND'),404);
                        }
                        break;
                    default: 
                        $dataEN = array();
                        foreach ($non_ac_list as $key => $list) {
                            $dataEN[$key]['id'] = $list->id;
                            $dataEN[$key]['title'] = $list->title_en;
                            $dataEN[$key]['description'] = $list->description_en;                            
                            $dataEN[$key]['slug'] = $list->slug;
                            $dataEN[$key]['image'] = $list->image;
                            $dataEN[$key]['type'] = $list->type;

                        }
                        $data['list'] = $dataEN;
                        if (!empty($widget)) {
                            $datawidget['id'] = $widget->id;
                            $datawidget['title'] = $widget->title_en; 
                            $datawidget['description'] = $widget->description_en;                           
                            $data['widget'] = $datawidget;
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

    // Get Non academic Related link detail
    public function getNonAcademiceDetail(Request $request){
        $validator = Validator::make($request->all(), [
            'lang' => 'required|in:en,hi,ur',
            'slug' => 'required',
        ]);
        $input = $request->all();
        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors(),422);
        } else {
            $locale='';
            $ac_non_ac_id_array = array(10265, 10257);
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
                        $head = array();
                        $userData = UserVisibility::where('ac_non_ac_id',$id)->where('role_id',$academic->head)->with('getHeadFaculty.getContact','getDesignation','getRole','getSectionContact')->first(); 
                        if(!empty($userData)){
                            $head['slug'] = $userData->getHeadFaculty->slug;
                            $head['title'] = $userData->getHeadFaculty->title;
                            $head['first_name'] = $userData->getHeadFaculty->first_name;
                            $head['last_name'] = $userData->getHeadFaculty->last_name;
                            $head['middle_name'] = $userData->getHeadFaculty->middle_name;
                            $head['url'] = $userData->getHeadFaculty->url;
                            if($userData->getHeadFaculty->image !=NULL){
                               $head['image'] = asset('storage/').$userData->getHeadFaculty->image;
                            }else{
                               $head['image'] = asset('storage').'/images/default-img.png';
                            }

                            if ($userData->getRole->title == $userData->getDesignation->name) {
                               $head['designation'] = $userData->getDesignation->name; 
                            }else{
                                if ($academic->sub_type == 4) {
                                    $head['designation'] = $userData->getRole->title; 
                                }elseif (in_array($academic->id, $ac_non_ac_id_array)) {
                                    $head['designation'] = $userData->getRole->title; 
                                }else{
                                    $head['designation'] = $userData->getRole->title . ' and ' . $userData->getDesignation->name; 
                                }    
                            }
                            
                            if($userData->getSectionContact != NULL){
                               $head['office_ext'] = $userData->getSectionContact->office_ext;
                               $head['office']     = $userData->getSectionContact->office;
                               $head['phone_ext']  = $userData->getSectionContact->phone_ext;
                               $head['phone']      = $userData->getSectionContact->phone;
                               $head['email']      = $userData->getSectionContact->email;
                            }
                        }else{
                            $head['slug'] = '';
                            $head['title'] = '';
                            $head['first_name'] = '';
                            $head['last_name'] = '';
                            $head['middle_name'] = '';
                            $head['image'] = asset('storage').'/images/default-img.png';
                            $head['designation'] = '';
                        }
                        $aboutSlug = request()->slug.'/home-page';
                        $about = array();
                        if ($aboutSlug) {
                            $aboutId = RelatedLink::where('slug',$aboutSlug)->first('id');
                            $getData = RelatedLinkData::/*where('approval_status','approved')->*/where('related_link_id',$aboutId->id)->with('slider')->first();
                           
                            if ($getData) {
                                $about['id'] = $getData->id;
                                if($getData->link_ur == NULL){
                                    $about['link'] = $getData->link_en; 
                                }else{ 
                                    $about['link'] = $getData->link_ur; 
                                }
                                if($getData->link_description_ur == NULL){
                                    $about['link_description'] = $getData->link_description_en;
                                }else{
                                    $about['link_description'] = $getData->link_description_ur;
                                }
                                $about['updated_at'] = $getData->updated_at;
                                //$about['slider'] = $getData->slider;
                                if ($getData->slider->count()) {
                                    $about['slider'] = $getData->slider;
                                }else{
                                    $about['slider'][0] = array('image' => '/images/amu-aligarh2.jpg');
                                }
                            }
                        }
                        

                        $links = array(); 
                        $relatedLinks = RelatedLink::orderBy('link_order','asc')->where('ac_non_ac_id',$id)->get();
                        foreach ($relatedLinks as $key => $value) {
                                $links[$key]['id'] = $value->id;
                                if($value->link_name_ur == NULL){
                                    $links[$key]['link'] = $value->link_name_en; 
                                }else{ 
                                    $links[$key]['link'] = $value->link_name_ur; 
                                }
                                $links[$key]['slug'] = $academic->getSubType->slug.'/'.$value->slug;
                        }
                                               
                        $dataUR['links']=$links;

                        //Get Custom link
                        $customlinks = array(); 
                        $cus_rel_Links = CustomRelatedLink::/*where('approval_status','approved')->*/orderBy('order_on','asc')->where('ac_non_ac_id',$id)->get();
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
                        
                        //Get other section data
                        $othermap =  MappingDepartments::where('ac_non_ac_id',$id)->orwhere('dep_id',$id)->first();
                        if ($othermap) {
                            $section = array(); 
                            $mapdata = MappingDepartments::select('id','ac_non_ac_id','dep_id','type')->where('ac_non_ac_id',$othermap->ac_non_ac_id)->with('departments:id,title_en,slug,sub_type','departments.getSubType:id,title,slug')->orderBy('id','ASC')->get();
                            if($mapdata->count()){
                                foreach ($mapdata as $key => $value){
                                    if ($value->departments) {
                                        $section[$key]['id'] = $value->departments->id;
                                        if($value->departments->title_ur == NULL){
                                            $section[$key]['title'] = $value->departments->title_en; 
                                        }else{ 
                                            $section[$key]['title'] = $value->departments->title_ur; 
                                        }
                                        $section[$key]['slug'] = $value->departments->getSubType->slug.'/'.$value->departments->slug;
                                    }                                    
                                }
                            }
                            $dataUR['section']=$section;
                        }
                        $seo = array();                      
                        $seo['meta_title'] = $academic->meta_title;
                        $seo['meta_description'] = $academic->meta_description;
                        $seo['meta_keyword'] = $academic->meta_keyword;
                        $seo['og_image'] = $academic->image;
                        $dataUR['head']=$head;
                        $dataUR['about']=$about;
                        $dataUR['head_login'] = $academic->getHeadType;
                        $dataUR['sub_type'] = $academic->getSubType;
                        $dataUR['SEO'] = $seo;
                        if($academic->title_ur == NULL){
                            $dataUR['title'] = $academic->title_en;  
                        }else{
                            $dataUR['title'] = $academic->title_ur; 
                        }                        
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
                        $head = array();
                        $userData = UserVisibility::where('ac_non_ac_id',$id)->where('role_id',$academic->head)->with('getHeadFaculty.getContact','getDesignation','getRole','getSectionContact')->first();
                        
                        if(!empty($userData)){
                        $head['slug'] = $userData->getHeadFaculty->slug;
                        $head['title'] = $userData->getHeadFaculty->title;
                        $head['first_name'] = $userData->getHeadFaculty->first_name;
                        $head['last_name'] = $userData->getHeadFaculty->last_name;
                        $head['middle_name'] = $userData->getHeadFaculty->middle_name;
                        $head['url'] = $userData->getHeadFaculty->url;
                        if($userData->getHeadFaculty->image !=NULL){
                           $head['image'] = asset('storage/').$userData->getHeadFaculty->image;
                        }else{
                           $head['image'] = asset('storage').'/images/default-img.png';
                        }
                        if ($userData->getRole->title == $userData->getDesignation->name) {
                           $head['designation'] = $userData->getDesignation->name; 
                        }else{
                            if ($academic->sub_type == 4) {
                                $head['designation'] = $userData->getRole->title; 
                            }elseif (in_array($academic->id, $ac_non_ac_id_array)) {
                                $head['designation'] = $userData->getRole->title; 
                            }else{
                                $head['designation'] = $userData->getRole->title . ' and ' . $userData->getDesignation->name; 
                            }
                            
                        }
                        if($userData->getSectionContact != NULL){
                               $head['office_ext'] = $userData->getSectionContact->office_ext;
                               $head['office']     = $userData->getSectionContact->office;
                               $head['phone_ext']  = $userData->getSectionContact->phone_ext;
                               $head['phone']      = $userData->getSectionContact->phone;
                               $head['email']      = $userData->getSectionContact->email;
                            }
                        }else{
                            $head['slug'] = '';
                            $head['title'] = '';
                            $head['first_name'] = '';
                            $head['last_name'] = '';
                            $head['middle_name'] = '';
                            $head['image'] = asset('storage').'/images/default-img.png';
                            $head['designation'] = '';
                        }
                        $aboutSlug = request()->slug.'/home-page';
                        $about = array();
                        if ($aboutSlug) {
                            $aboutId = RelatedLink::where('slug',$aboutSlug)->first('id');
                            $getData = RelatedLinkData::/*where('approval_status','approved')->*/where('related_link_id',$aboutId->id)->with('slider')->first();
                            
                            if ($getData) {
                                $about['id'] = $getData->id;
                                if($getData->link_hi == NULL){
                                    $about['link'] = $getData->link_en; 
                                }else{ 
                                    $about['link'] = $getData->link_hi; 
                                }
                                if($getData->link_description_hi == NULL){
                                    $about['link_description'] = $getData->link_description_en;
                                }else{
                                    $about['link_description'] = $getData->link_description_hi;
                                }
                                $about['updated_at'] = $getData->updated_at;
                                if ($getData->slider->count()) {
                                    $about['slider'] = $getData->slider;
                                }else{
                                    $about['slider'][0] = array('image' => '/images/amu-aligarh2.jpg');
                                }
                            }
                        }
                        

                        $links = array(); 
                        $relatedLinks = RelatedLink::orderBy('link_order','asc')->where('ac_non_ac_id',$id)->get();
                        foreach ($relatedLinks as $key => $value) {
                                $links[$key]['id'] = $value->id;
                                if($value->link_name_hi == NULL){
                                    $links[$key]['link'] = $value->link_name_en; 
                                }else{ 
                                    $links[$key]['link'] = $value->link_name_hi; 
                                }
                                $links[$key]['slug'] = $academic->getSubType->slug.'/'.$value->slug;
                        }
                        $dataHI['links']=$links;

                        //Get Custom link
                        $customlinks = array(); 
                        $cus_rel_Links = CustomRelatedLink::/*where('approval_status','approved')->*/orderBy('order_on','asc')->where('ac_non_ac_id',$id)->get();
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

                        //Get other section data
                        $othermap =  MappingDepartments::where('ac_non_ac_id',$id)->orwhere('dep_id',$id)->first();
                        if ($othermap) {
                            $section = array(); 
                            $mapdata = MappingDepartments::select('id','ac_non_ac_id','dep_id','type')->where('ac_non_ac_id',$othermap->ac_non_ac_id)->with('departments:id,title_en,slug,sub_type','departments.getSubType:id,title,slug')->orderBy('id','ASC')->get();
                            if($mapdata->count()){
                                foreach ($mapdata as $key => $value){
                                    if ($value->departments) {
                                        $section[$key]['id'] = $value->departments->id;
                                        if($value->departments->title_hi == NULL){
                                            $section[$key]['title'] = $value->departments->title_en; 
                                        }else{ 
                                            $section[$key]['title'] = $value->departments->title_hi; 
                                        }
                                        $section[$key]['slug'] = $value->departments->getSubType->slug.'/'.$value->departments->slug;
                                    }                                    
                                }
                            }
                            $dataHI['section']=$section;
                        }  
                        $seo = array();                      
                        $seo['meta_title'] = $academic->meta_title;
                        $seo['meta_description'] = $academic->meta_description;
                        $seo['meta_keyword'] = $academic->meta_keyword;
                        $seo['og_image'] = $academic->image;
                        $dataHI['head']=$head;
                        $dataHI['about']=$about;
                        $dataHI['head_login'] = $academic->getHeadType; 
                        $dataHI['sub_type'] = $academic->getSubType;
                        $dataHI['SEO'] = $seo;
                        if($academic->title_hi == NULL){
                            $dataHI['title'] = $academic->title_en;  
                        }else{
                            $dataHI['title'] = $academic->title_hi; 
                        }  
                        if ($dataHI){                        
                            return $this->sendResponse($dataHI, trans($locale.'_lang.DATA_FOUND'),200);
                        } else {
                            return $this->sendResponse($dataHI, trans($locale.'_lang.DATA_NOT_FOUND'),404);
                        }
                        break;
                    default: //GET widget IN English
                        $data = array();                        
                        
                            $academic =  AcademicAndNonAcademic::where('slug',request()->slug)->with('getSubType:id,title,slug','getHeadType:id,title,role_type')->first();
                            $id = $academic->id;
                            $head = array();
                            $userData = UserVisibility::where('ac_non_ac_id',$id)->where('role_id',$academic->head)->with('getHeadFaculty.getContact','getDesignation','getRole','getSectionContact')->first();
                            if ($userData){                                           
                                $head['slug'] = $userData->getHeadFaculty->slug;
                                $head['title'] = $userData->getHeadFaculty->title;
                                $head['first_name'] = $userData->getHeadFaculty->first_name;
                                $head['last_name'] = $userData->getHeadFaculty->last_name;
                                $head['middle_name'] = $userData->getHeadFaculty->middle_name;
                                $head['url'] = $userData->getHeadFaculty->url;
                                if($userData->getHeadFaculty->image !=NULL){
                                   $head['image'] = asset('storage/').'/'.$userData->getHeadFaculty->image;
                                }else{
                                   $head['image'] = asset('storage').'/images/default-img.png';
                                }
                                if ($userData->getRole->title == $userData->getDesignation->name) {
                                   $head['designation'] = $userData->getDesignation->name; 
                                }else{
                                    if ($academic->sub_type == 4) {
                                        $head['designation'] = $userData->getRole->title; 
                                    }elseif (in_array($academic->id, $ac_non_ac_id_array)) {
                                        $head['designation'] = $userData->getRole->title; 
                                    }else{
                                        $head['designation'] = $userData->getRole->title . ' and ' . $userData->getDesignation->name; 
                                    }                                    
                                }
                                if($userData->getSectionContact != NULL){
                                   $head['office_ext'] = $userData->getSectionContact->office_ext;
                                   $head['office']     = $userData->getSectionContact->office;
                                   $head['phone_ext']  = $userData->getSectionContact->phone_ext;
                                   $head['phone']      = $userData->getSectionContact->phone;
                                   $head['email']      = $userData->getSectionContact->email;
                                }
                            }else{
                                $head['slug'] = '';
                                $head['title'] = '';
                                $head['first_name'] = '';
                                $head['last_name'] = '';
                                $head['middle_name'] = '';
                                $head['image'] = asset('storage').'/images/default-img.png';
                                $head['designation'] = '';
                            }                      
                            $aboutSlug = request()->slug.'/home-page';
                            $about = array();
                            if ($aboutSlug) {
                                $aboutId = RelatedLink::where('slug',$aboutSlug)->first('id');
                                $getData = RelatedLinkData::/*where('approval_status','approved')->*/where('related_link_id',$aboutId->id)->with('slider')->first();
                                   
                                    if ($getData) {
                                        $about['id'] = $getData->id;
                                        $about['link'] = $getData->link_en; 
                                        $about['link_description'] = $getData->link_description_en;
                                        $about['updated_at'] = $getData->updated_at;
                                        if ($getData->slider->count()) {
                                            $about['slider'] = $getData->slider;
                                        }else{
                                            $about['slider'][0] = array('image' => '/images/amu-aligarh2.jpg');
                                        }
                                    }
                            }
                            
                            
                            $links = array(); 
                            $relatedLinks = RelatedLink::orderBy('link_order','asc')->where('ac_non_ac_id',$id)->get();
                            foreach ($relatedLinks as $key => $value) {
                                    $links[$key]['id'] = $value->id;
                                    $links[$key]['link'] = $value->link_name_en;
                                    $links[$key]['slug'] = $academic->getSubType->slug.'/'.$value->slug;
                            }
                            
                            $data['links']=$links;

                            //Get Custom link
                            $customlinks = array(); 
                            $cus_rel_Links = CustomRelatedLink::/*where('approval_status','approved')->*/orderBy('order_on','asc')->where('ac_non_ac_id',$id)->get();
                            if ($cus_rel_Links) {
                                foreach ($cus_rel_Links as $key => $value) {
                                    $customlinks[$key]['id'] = $value->id;
                                    $customlinks[$key]['cus_link'] = $value->link_name_en;
                                    $customlinks[$key]['rel_slug'] = $value->rel_slug;
                                }
                                $data['customlinks']=$customlinks;
                            }                             
                            //Get other section data
                            $othermap =  MappingDepartments::where('ac_non_ac_id',$id)->orwhere('dep_id',$id)->first();
                            if ($othermap) {
                                $section = array(); 
                                $mapdata = MappingDepartments::select('id','ac_non_ac_id','dep_id','type')->where('ac_non_ac_id',$othermap->ac_non_ac_id)->with('departments:id,title_en,slug,sub_type','departments.getSubType:id,title,slug')->orderBy('id','ASC')->get();
                                if($mapdata->count()){
                                    foreach ($mapdata as $key => $value){
                                        if ($value->departments) {
                                            $section[$key]['id'] = $value->departments->id;
                                            $section[$key]['title'] = $value->departments->title_en;
                                            $section[$key]['slug'] = $value->departments->getSubType->slug.'/'.$value->departments->slug;
                                        }
                                        
                                    }
                                }
                                $data['section']=$section;
                            } 
                            $seo = array();                      
                            $seo['meta_title'] = $academic->meta_title;
                            $seo['meta_description'] = $academic->meta_description;
                            $seo['meta_keyword'] = $academic->meta_keyword;
                            $seo['og_image'] = $academic->image;
                            $data['SEO'] = $seo;                          
                            $data['head']=$head; 
                            $data['title'] = $academic->title_en;
                            $data['head_login'] = $academic->getHeadType;
                            $data['sub_type'] = $academic->getSubType; 
                            $data['about'] = $about;  
                                                
                        if (empty($data)){                        
                            return $this->sendResponse($data, trans($locale.'_lang.DATA_NOT_FOUND'),404);
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

    // Get Vice Chancellor and Pro  Vice Chancellor Detail
    public function getAmuChancellorDetail(Request $request){
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
                        $academic =  AcademicAndNonAcademic::where('slug',request()->slug)->with('getSubType:id,title,slug','getHeadType:id,title,role_type')->first();
                        $id = $academic->id;
                        $head = array();
                        $userData = UserVisibility::where('ac_non_ac_id',$id)->where('role_id',$academic->head)->with('getHeadFaculty.getContact','getDesignation','getRole','getSectionContact')->first(); 
                        if(!empty($userData)){
                            $head['title'] = $userData->getHeadFaculty->title;
                            $head['first_name'] = $userData->getHeadFaculty->first_name;
                            $head['middle_name'] = $userData->getHeadFaculty->middle_name;
                            $head['last_name'] = $userData->getHeadFaculty->last_name;
                            $head['url'] = $userData->getHeadFaculty->url;
                            if($userData->getHeadFaculty->image !=NULL){
                               $head['image'] = asset('storage/').$userData->getHeadFaculty->image;
                            }else{
                               $head['image'] = asset('storage').'/images/default-img.png';
                            }
                            $head['designation'] = $userData->getRole->title . ' and ' . $userData->getDesignation->name; 
                            if($userData->getSectionContact != NULL){
                               $head['office_ext'] = $userData->getSectionContact->office_ext;
                               $head['office']     = $userData->getSectionContact->office;
                               $head['phone_ext']  = $userData->getSectionContact->phone_ext;
                               $head['phone']      = $userData->getSectionContact->phone;
                               $head['email']      = $userData->getSectionContact->email;
                            }
                        }else{
                            $head['slug'] = '';
                            $head['title'] = '';
                            $head['first_name'] = '';
                            $head['last_name'] = '';
                            $head['middle_name'] = '';
                            $head['image'] = asset('storage').'/images/default-img.png';
                            $head['designation'] = '';
                        }
                        $aboutSlug = request()->slug.'/home-page';
                        $about = array();
                        if ($aboutSlug) {
                            $aboutId = RelatedLink::where('slug',$aboutSlug)->first('id');
                            $getData = RelatedLinkData::/*where('approval_status','approved')->*/where('related_link_id',$aboutId->id)->with('slider')->first();
                           
                            if ($getData) {
                                $about['id'] = $getData->id;
                                if($getData->link_ur == NULL){
                                    $about['link'] = $getData->link_en; 
                                }else{ 
                                    $about['link'] = $getData->link_ur; 
                                }
                                if($getData->link_description_ur == NULL){
                                    $about['link_description'] = $getData->link_description_en;
                                }else{
                                    $about['link_description'] = $getData->link_description_ur;
                                }
                                $about['updated_at'] = $getData->updated_at;
                                if ($getData->slider->count()) {
                                    $about['slider'] = $getData->slider;
                                }else{
                                    $about['slider'][0] = array('image' => '/images/amu-aligarh2.jpg');
                                }
                            }
                        }
                        

                        $links = array(); 
                        $relatedLinks = RelatedLink::orderBy('link_order','asc')->where('ac_non_ac_id',$id)->get();
                        foreach ($relatedLinks as $key => $value) {
                                $links[$key]['id'] = $value->id;
                                if($value->link_name_ur == NULL){
                                    $links[$key]['link'] = $value->link_name_en; 
                                }else{ 
                                    $links[$key]['link'] = $value->link_name_ur; 
                                }
                                $links[$key]['slug'] = $value->slug;
                        }
                                               
                        $dataUR['links']=$links;

                        //Get Custom link
                        $customlinks = array(); 
                        $cus_rel_Links = CustomRelatedLink::/*where('approval_status','approved')->*/orderBy('order_on','asc')->where('ac_non_ac_id',$id)->get();
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
                        
                        //Get other section data
                        /*$othermap =  MappingDepartments::where('ac_non_ac_id',$id)->orwhere('dep_id',$id)->first();
                        if ($othermap) {
                            $section = array(); 
                            $mapdata = MappingDepartments::select('id','ac_non_ac_id','dep_id','type')->where('ac_non_ac_id',$othermap->ac_non_ac_id)->with('departments:id,title_en,slug,sub_type','departments.getSubType:id,title')->orderBy('id','ASC')->get();
                            if($mapdata->count()){
                                foreach ($mapdata as $key => $value){
                                    $section[$key]['id'] = $value->departments->id;
                                    if($value->departments->title_ur == NULL){
                                        $section[$key]['title'] = $value->departments->title_en; 
                                    }else{ 
                                        $section[$key]['title'] = $value->departments->title_ur; 
                                    }
                                    $section[$key]['slug'] = str_slug($value->departments->getSubType->title, '-').'/'.$value->departments->slug;
                                }
                            }
                            $dataUR['section']=$section;
                        }*/
                        $seo = array();                      
                        $seo['meta_title'] = $academic->meta_title;
                        $seo['meta_description'] = $academic->meta_description;
                        $seo['meta_keyword'] = $academic->meta_keyword;
                        $seo['og_image'] = $academic->image;
                        $dataUR['SEO'] = $seo;                        
                        $dataUR['head']=$head;
                        $dataUR['about']=$about;
                        $dataUR['head_login'] = $academic->getHeadType;
                        $dataUR['sub_type'] = $academic->getSubType;
                        if($academic->title_ur == NULL){
                            $dataUR['title'] = $academic->title_en;  
                        }else{
                            $dataUR['title'] = $academic->title_ur; 
                        }                        
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
                        $head = array();
                        $userData = UserVisibility::where('ac_non_ac_id',$id)->where('role_id',$academic->head)->with('getHeadFaculty.getContact','getDesignation','getRole','getSectionContact')->first();
                        
                        if(!empty($userData)){
                        $head['title'] = $userData->getHeadFaculty->title;
                        $head['first_name'] = $userData->getHeadFaculty->first_name;
                        $head['middle_name'] = $userData->getHeadFaculty->middle_name;
                        $head['last_name'] = $userData->getHeadFaculty->last_name;
                        $head['url'] = $userData->getHeadFaculty->url;
                        if($userData->getHeadFaculty->image !=NULL){
                           $head['image'] = asset('storage/').$userData->getHeadFaculty->image;
                        }else{
                           $head['image'] = asset('storage').'/images/default-img.png';
                        }
                        $head['designation'] = $userData->getRole->title . ' and ' . $userData->getDesignation->name;
                        if($userData->getSectionContact != NULL){
                               $head['office_ext'] = $userData->getSectionContact->office_ext;
                               $head['office']     = $userData->getSectionContact->office;
                               $head['phone_ext']  = $userData->getSectionContact->phone_ext;
                               $head['phone']      = $userData->getSectionContact->phone;
                               $head['email']      = $userData->getSectionContact->email;
                            }
                        }else{
                            $head['slug'] = '';
                            $head['title'] = '';
                            $head['first_name'] = '';
                            $head['last_name'] = '';
                            $head['middle_name'] = '';
                            $head['image'] = asset('storage').'/images/default-img.png';
                            $head['designation'] = '';
                        }
                        $aboutSlug = request()->slug.'/home-page';
                        $about = array();
                        if ($aboutSlug) {
                            $aboutId = RelatedLink::where('slug',$aboutSlug)->first('id');
                            $getData = RelatedLinkData::/*where('approval_status','approved')->*/where('related_link_id',$aboutId->id)->with('slider')->first();
                            
                            if ($getData) {
                                $about['id'] = $getData->id;
                                if($getData->link_hi == NULL){
                                    $about['link'] = $getData->link_en; 
                                }else{ 
                                    $about['link'] = $getData->link_hi; 
                                }
                                if($getData->link_description_hi == NULL){
                                    $about['link_description'] = $getData->link_description_en;
                                }else{
                                    $about['link_description'] = $getData->link_description_hi;
                                }
                                $about['updated_at'] = $getData->updated_at;
                                if ($getData->slider->count()) {
                                    $about['slider'] = $getData->slider;
                                }else{
                                    $about['slider'][0] = array('image' => '/images/amu-aligarh2.jpg');
                                }
                            }
                        }
                        

                        $links = array(); 
                        $relatedLinks = RelatedLink::orderBy('link_order','asc')->where('ac_non_ac_id',$id)->get();
                        foreach ($relatedLinks as $key => $value) {
                                $links[$key]['id'] = $value->id;
                                if($value->link_name_hi == NULL){
                                    $links[$key]['link'] = $value->link_name_en; 
                                }else{ 
                                    $links[$key]['link'] = $value->link_name_hi; 
                                }
                                $links[$key]['slug'] = $value->slug;
                        }
                        $dataHI['links']=$links;

                        //Get Custom link
                        $customlinks = array(); 
                        $cus_rel_Links = CustomRelatedLink::/*where('approval_status','approved')->*/orderBy('order_on','asc')->where('ac_non_ac_id',$id)->get();
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

                        //Get other section data
                       /* $othermap =  MappingDepartments::where('ac_non_ac_id',$id)->orwhere('dep_id',$id)->first();
                        if ($othermap) {
                            $section = array(); 
                            $mapdata = MappingDepartments::select('id','ac_non_ac_id','dep_id','type')->where('ac_non_ac_id',$othermap->ac_non_ac_id)->with('departments:id,title_en,slug,sub_type','departments.getSubType:id,title')->orderBy('id','ASC')->get();
                            if($mapdata->count()){
                                foreach ($mapdata as $key => $value){
                                    $section[$key]['id'] = $value->departments->id;
                                    if($value->departments->title_hi == NULL){
                                        $section[$key]['title'] = $value->departments->title_en; 
                                    }else{ 
                                        $section[$key]['title'] = $value->departments->title_hi; 
                                    }
                                    $section[$key]['slug'] = str_slug($value->departments->getSubType->title, '-').'/'.$value->departments->slug;
                                }
                            }
                            $dataHI['section']=$section;
                        }  */
                        $seo = array();                      
                        $seo['meta_title'] = $academic->meta_title;
                        $seo['meta_description'] = $academic->meta_description;
                        $seo['meta_keyword'] = $academic->meta_keyword;
                        $seo['og_image'] = $academic->image;
                        $dataHI['SEO'] = $seo;
                        $dataHI['head']=$head;
                        $dataHI['about']=$about;
                        $dataHI['head_login'] = $academic->getHeadType; 
                        $dataHI['sub_type'] = $academic->getSubType;
                        if($academic->title_hi == NULL){
                            $dataHI['title'] = $academic->title_en;  
                        }else{
                            $dataHI['title'] = $academic->title_hi; 
                        }  
                        if ($dataHI){                        
                            return $this->sendResponse($dataHI, trans($locale.'_lang.DATA_FOUND'),200);
                        } else {
                            return $this->sendResponse($dataHI, trans($locale.'_lang.DATA_NOT_FOUND'),404);
                        }
                        break;
                    default: //GET widget IN English
                        $data = array();                        
                        
                            $academic =  AcademicAndNonAcademic::where('slug',request()->slug)->with('getSubType:id,title,slug','getHeadType:id,title,role_type')->first();
                            $id = $academic->id;
                            $head = array();
                            $userData = UserVisibility::where('ac_non_ac_id',$id)->where('role_id',$academic->head)->with('getHeadFaculty.getContact','getDesignation','getRole','getSectionContact')->first();
                            if ($userData){ 
                                $head['title'] = $userData->getHeadFaculty->title;
                                $head['first_name'] = $userData->getHeadFaculty->first_name;
                                $head['middle_name'] = $userData->getHeadFaculty->middle_name;
                                $head['last_name'] = $userData->getHeadFaculty->last_name;
                                $head['url'] = $userData->getHeadFaculty->url;
                                if($userData->getHeadFaculty->image !=NULL){
                                   $head['image'] = asset('storage/').'/'.$userData->getHeadFaculty->image;
                                }else{
                                   $head['image'] = asset('storage').'/images/default-img.png';
                                }
                                $head['designation'] = $userData->getRole->title . ' and ' . $userData->getDesignation->name;
                                if($userData->getSectionContact != NULL){
                                   $head['office_ext'] = $userData->getSectionContact->office_ext;
                                   $head['office']     = $userData->getSectionContact->office;
                                   $head['phone_ext']  = $userData->getSectionContact->phone_ext;
                                   $head['phone']      = $userData->getSectionContact->phone;
                                   $head['email']      = $userData->getSectionContact->email;
                                }
                            }else{
                                $head['slug'] = '';
                                $head['title'] = '';
                                $head['first_name'] = '';
                                $head['last_name'] = '';
                                $head['middle_name'] = '';
                                $head['image'] = asset('storage').'/images/default-img.png';
                                $head['designation'] = '';
                            }                      
                            $aboutSlug = request()->slug.'/home-page';
                            $about = array();
                            if ($aboutSlug) {
                                $aboutId = RelatedLink::where('slug',$aboutSlug)->first('id');
                                $getData = RelatedLinkData::/*where('approval_status','approved')->*/where('related_link_id',$aboutId->id)->with('slider')->first();
                                   
                                    if ($getData) {
                                        $about['id'] = $getData->id;
                                        $about['link'] = $getData->link_en; 
                                        $about['link_description'] = $getData->link_description_en;
                                        $about['updated_at'] = $getData->updated_at;
                                        if ($getData->slider->count()) {
                                            $about['slider'] = $getData->slider;
                                        }else{
                                            $about['slider'][0] = array('image' => '/images/amu-aligarh2.jpg');
                                        }   
                                    }
                            }
                            
                            
                            $links = array(); 
                            $relatedLinks = RelatedLink::orderBy('link_order','asc')->where('ac_non_ac_id',$id)->get();
                            foreach ($relatedLinks as $key => $value) {
                                    $links[$key]['id'] = $value->id;
                                    $links[$key]['link'] = $value->link_name_en;
                                    $links[$key]['slug'] = $value->slug;
                            }
                            
                            $data['links']=$links;

                            //Get Custom link
                            $customlinks = array(); 
                            $cus_rel_Links = CustomRelatedLink::/*where('approval_status','approved')->*/orderBy('order_on','asc')->where('ac_non_ac_id',$id)->get();
                            if ($cus_rel_Links) {
                                foreach ($cus_rel_Links as $key => $value) {
                                    $customlinks[$key]['id'] = $value->id;
                                    $customlinks[$key]['cus_link'] = $value->link_name_en;
                                    $customlinks[$key]['rel_slug'] = $value->rel_slug;
                                }
                                $data['customlinks']=$customlinks;
                            }                        
                            
                            
                            //Get other section data
                           /* $othermap =  MappingDepartments::where('ac_non_ac_id',$id)->orwhere('dep_id',$id)->first();
                            if ($othermap) {
                                $section = array(); 
                                $mapdata = MappingDepartments::select('id','ac_non_ac_id','dep_id','type')->where('ac_non_ac_id',$othermap->ac_non_ac_id)->with('departments:id,title_en,slug,sub_type','departments.getSubType:id,title')->orderBy('id','ASC')->get();
                                if($mapdata->count()){
                                    foreach ($mapdata as $key => $value){
                                        $section[$key]['id'] = $value->departments->id;
                                        $section[$key]['title'] = $value->departments->title_en;
                                        $section[$key]['slug'] = str_slug($value->departments->getSubType->title, '-').'/'.$value->departments->slug;
                                    }
                                }
                                $data['section']=$section;
                            } */
                            $seo = array();                      
                            $seo['meta_title'] = $academic->meta_title;
                            $seo['meta_description'] = $academic->meta_description;
                            $seo['meta_keyword'] = $academic->meta_keyword;
                            $seo['og_image'] = $academic->image;
                            $data['SEO'] = $seo;                          
                            $data['head']=$head; 
                            $data['title'] = $academic->title_en;
                            $data['head_login'] = $academic->getHeadType; 
                            $data['sub_type'] = $academic->getSubType;
                            $data['about'] = $about;                         
                        if (empty($data)){                        
                            return $this->sendResponse($data, trans($locale.'_lang.DATA_NOT_FOUND'),404);
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

    // Get AMU Vice Chancellor Detail
    public function getAmuVcChancellorDetail(Request $request){
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
                        $academic =  AcademicAndNonAcademic::where('slug',request()->slug)->with('getSubType:id,title,slug','getHeadType:id,title,role_type')->first();
                        $id = $academic->id;
                        $head = array();
                        $userData = UserVisibility::where('ac_non_ac_id',$id)->where('role_id',$academic->head)->with('getHeadFaculty.getContact','getDesignation','getRole','getSectionContact')->first();
                        if ($userData){ 
                            $head['title'] = $userData->getHeadFaculty->title;
                            $head['first_name'] = $userData->getHeadFaculty->first_name;
                            $head['middle_name'] = $userData->getHeadFaculty->middle_name;
                            $head['last_name'] = $userData->getHeadFaculty->last_name;
                            $head['url'] = $userData->getHeadFaculty->url;
                            if($userData->getHeadFaculty->image !=NULL){
                               $head['image'] = asset('storage/').'/'.$userData->getHeadFaculty->image;
                            }else{
                               $head['image'] = asset('storage').'/images/default-img.png';
                            }
                            $head['designation'] = $userData->getRole->title . ' and ' . $userData->getDesignation->name;
                            if($userData->getSectionContact != NULL){
                               $head['office_ext'] = $userData->getSectionContact->office_ext;
                               $head['office']     = $userData->getSectionContact->office;
                               $head['phone_ext']  = $userData->getSectionContact->phone_ext;
                               $head['phone']      = $userData->getSectionContact->phone;
                               $head['email']      = $userData->getSectionContact->email;
                            }
                        }else{
                            $head['slug'] = '';
                            $head['title'] = '';
                            $head['first_name'] = '';
                            $head['last_name'] = '';
                            $head['middle_name'] = '';
                            $head['image'] = asset('storage').'/images/default-img.png';
                            $head['designation'] = '';
                        }                      
                        $aboutSlug = request()->slug.'/home';
                        $about = array();
                        if ($aboutSlug) {
                            $getData = VCLink::/*where('approval_status','approved')->*/where('slug',$aboutSlug)->with('slider')->first();
                               
                                if ($getData) {
                                    $about['id'] = $getData->id;
                                    if($getData->link_name_ur == NULL){
                                        $about['link_name'] = $getData->link_name_en; 
                                    }else{ 
                                        $about['link_name'] = $getData->link_name_ur; 
                                    }
                                    if($getData->description_ur == NULL){
                                        $about['description'] = $getData->description_en; 
                                    }else{ 
                                        $about['description'] = $getData->description_ur; 
                                    }
                                    $about['updated_at'] = $getData->updated_at;
                                    if ($getData->slider->count()) {
                                        $about['slider'] = $getData->slider;
                                    }else{
                                        $about['slider'][0] = array('image' => '/images/amu-aligarh2.jpg');
                                    }
                                }
                        }
                        
                        
                        $grouplinkcount = VCLink::select('group_type_id')->groupBy('group_type_id')->where('ac_non_ac_id',$id)->get()->toArray();
                        $grouplink = array_column($grouplinkcount, 'group_type_id');
                        $links = GroupLink::select('title_ur as title','id')->whereIn('id', $grouplink)
                                ->with(['getVCRelatedLink' => function($q)  use($id){ 
                                  $q->where('ac_non_ac_id', '=',$id);
                                  $q->orderBy('link_order', 'ASC'); }])
                                ->get();
                        $linkData = array();
                        foreach ($links as $key => $value) {
                            $vcRelatedLink = array();
                            foreach ($value->getVCRelatedLink as $k => $v) {
                                $vcRelatedLink[$k]['id']=$v->id; 
                                $vcRelatedLink[$k]['group_type_id'] = $v->group_type_id;
                                $vcRelatedLink[$k]['content_type'] = $v->content_type;
                                if($v->link_name_ur == NULL){
                                  $vcRelatedLink[$k]['link_name'] = $v->link_name_en;      
                                }else{
                                  $vcRelatedLink[$k]['link_name'] = $v->link_name_ur;  
                                }                                
                                $vcRelatedLink[$k]['slug'] = $v->slug;
                                $vcRelatedLink[$k]['file'] = $v->file;
                                $vcRelatedLink[$k]['link'] = $v->link; 
                                $vcRelatedLink[$k]['link_order'] = $v->link_order;
                                $vcRelatedLink[$k]['approval_status'] = $v->approval_status;
                            }
                            $linkData[$key]['id']=$value->id;
                            $linkData[$key]['title'] = $value->title; 
                            $linkData[$key]['get_v_c_related_link'] = $vcRelatedLink;
                            
                        }
                        $seo = array();                      
                        $seo['meta_title'] = $academic->meta_title;
                        $seo['meta_description'] = $academic->meta_description;
                        $seo['meta_keyword'] = $academic->meta_keyword;
                        $seo['og_image'] = $academic->image;
                        $dataUR['SEO'] = $seo;     
                        $dataUR['links']=$linkData;
                        $dataUR['head']=$head;
                        if($academic->title_ur == NULL){
                          $dataUR['title'] = $academic->title_en;  
                        }else{
                          $dataUR['title'] = $academic->title_ur;  
                        }
                        $dataUR['head_login'] = $academic->getHeadType; 
                        $dataUR['sub_type'] = $academic->getSubType;
                        $dataUR['about'] = $about;                         
                        if (empty($dataUR)){                        
                            return $this->sendResponse($dataUR, trans($locale.'_lang.DATA_NOT_FOUND'),404);
                        }                        
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
                        $head = array();
                        $userData = UserVisibility::where('ac_non_ac_id',$id)->where('role_id',$academic->head)->with('getHeadFaculty.getContact','getDesignation','getRole','getSectionContact')->first();
                        if ($userData){ 
                            $head['title'] = $userData->getHeadFaculty->title;
                            $head['first_name'] = $userData->getHeadFaculty->first_name;
                            $head['middle_name'] = $userData->getHeadFaculty->middle_name;
                            $head['last_name'] = $userData->getHeadFaculty->last_name;
                            $head['url'] = $userData->getHeadFaculty->url;
                            if($userData->getHeadFaculty->image !=NULL){
                               $head['image'] = asset('storage/').'/'.$userData->getHeadFaculty->image;
                            }else{
                               $head['image'] = asset('storage').'/images/default-img.png';
                            }
                            $head['designation'] = $userData->getRole->title . ' and ' . $userData->getDesignation->name;
                            if($userData->getSectionContact != NULL){
                               $head['office_ext'] = $userData->getSectionContact->office_ext;
                               $head['office']     = $userData->getSectionContact->office;
                               $head['phone_ext']  = $userData->getSectionContact->phone_ext;
                               $head['phone']      = $userData->getSectionContact->phone;
                               $head['email']      = $userData->getSectionContact->email;
                            }
                        }else{
                            $head['slug'] = '';
                            $head['title'] = '';
                            $head['first_name'] = '';
                            $head['last_name'] = '';
                            $head['middle_name'] = '';
                            $head['image'] = asset('storage').'/images/default-img.png';
                            $head['designation'] = '';
                        }                      
                        $aboutSlug = request()->slug.'/home';
                        $about = array();
                        if ($aboutSlug) {
                            $getData = VCLink::/*where('approval_status','approved')->*/where('slug',$aboutSlug)->with('slider')->first();
                               
                                if ($getData) {
                                    $about['id'] = $getData->id;
                                    if($getData->link_name_hi == NULL){
                                        $about['link_name'] = $getData->link_name_en; 
                                    }else{ 
                                        $about['link_name'] = $getData->link_name_hi; 
                                    }
                                    if($getData->description_hi == NULL){
                                        $about['description'] = $getData->description_en; 
                                    }else{ 
                                        $about['description'] = $getData->description_hi; 
                                    }
                                    $about['updated_at'] = $getData->updated_at;
                                    if ($getData->slider->count()) {
                                        $about['slider'] = $getData->slider;
                                    }else{
                                        $about['slider'][0] = array('image' => '/images/amu-aligarh2.jpg');
                                    }
                                }
                        }
                        
                        
                        $grouplinkcount = VCLink::select('group_type_id')->groupBy('group_type_id')->where('ac_non_ac_id',$id)->get()->toArray();
                        $grouplink = array_column($grouplinkcount, 'group_type_id');
                        $links = GroupLink::select('title_hi as title','id')->whereIn('id', $grouplink)
                                ->with(['getVCRelatedLink' => function($q)  use($id){ 
                                  $q->where('ac_non_ac_id', '=',$id);
                                  $q->orderBy('link_order', 'ASC'); }])
                                ->get();
                        $linkData = array();
                        foreach ($links as $key => $value) {
                            $vcRelatedLink = array();
                            foreach ($value->getVCRelatedLink as $k => $v) {
                                $vcRelatedLink[$k]['id']=$v->id; 
                                $vcRelatedLink[$k]['group_type_id'] = $v->group_type_id;
                                $vcRelatedLink[$k]['content_type'] = $v->content_type;
                                if($v->link_name_hi == NULL){
                                  $vcRelatedLink[$k]['link_name'] = $v->link_name_en;      
                                }else{
                                  $vcRelatedLink[$k]['link_name'] = $v->link_name_hi;  
                                }                                
                                $vcRelatedLink[$k]['slug'] = $v->slug;
                                $vcRelatedLink[$k]['file'] = $v->file;
                                $vcRelatedLink[$k]['link'] = $v->link; 
                                $vcRelatedLink[$k]['link_order'] = $v->link_order;
                                $vcRelatedLink[$k]['approval_status'] = $v->approval_status;
                            }
                            $linkData[$key]['id']=$value->id;
                            $linkData[$key]['title'] = $value->title; 
                            $linkData[$key]['get_v_c_related_link'] = $vcRelatedLink;
                            
                        }
                        $seo = array();                      
                        $seo['meta_title'] = $academic->meta_title;
                        $seo['meta_description'] = $academic->meta_description;
                        $seo['meta_keyword'] = $academic->meta_keyword;
                        $seo['og_image'] = $academic->image;
                        $dataHI['SEO'] = $seo;     
                        $dataHI['links']=$linkData;
                        $dataHI['head']=$head;
                        if($academic->title_hi == NULL){
                          $dataHI['title'] = $academic->title_en;  
                        }else{
                          $dataHI['title'] = $academic->title_hi;  
                        }
                        $dataHI['head_login'] = $academic->getHeadType; 
                        $dataHI['sub_type'] = $academic->getSubType;
                        $dataHI['about'] = $about;                         
                        if (empty($dataHI)){                        
                            return $this->sendResponse($dataHI, trans($locale.'_lang.DATA_NOT_FOUND'),404);
                        }                        
                        if ($dataHI){                        
                            return $this->sendResponse($dataHI, trans($locale.'_lang.DATA_FOUND'),200);
                        } else {
                            return $this->sendResponse($dataHI, trans($locale.'_lang.DATA_NOT_FOUND'),404);
                        }
                        break;
                    default: //GET widget IN English
                        $data = array();
                        $academic =  AcademicAndNonAcademic::where('slug',request()->slug)->with('getSubType:id,title,slug','getHeadType:id,title,role_type')->first();
                        $id = $academic->id;
                        $head = array();
                        $userData = UserVisibility::where('ac_non_ac_id',$id)->where('role_id',$academic->head)->with('getHeadFaculty.getContact','getDesignation','getRole','getSectionContact')->first();
                        if ($userData){ 
                            $head['title'] = $userData->getHeadFaculty->title;
                            $head['first_name'] = $userData->getHeadFaculty->first_name;
                            $head['middle_name'] = $userData->getHeadFaculty->middle_name;
                            $head['last_name'] = $userData->getHeadFaculty->last_name;
                            $head['url'] = $userData->getHeadFaculty->url;
                            if($userData->getHeadFaculty->image !=NULL){
                               $head['image'] = asset('storage/').'/'.$userData->getHeadFaculty->image;
                            }else{
                               $head['image'] = asset('storage').'/images/default-img.png';
                            }
                            $head['designation'] = $userData->getRole->title . ' and ' . $userData->getDesignation->name;
                            if($userData->getSectionContact != NULL){
                               $head['office_ext'] = $userData->getSectionContact->office_ext;
                               $head['office']     = $userData->getSectionContact->office;
                               $head['phone_ext']  = $userData->getSectionContact->phone_ext;
                               $head['phone']      = $userData->getSectionContact->phone;
                               $head['email']      = $userData->getSectionContact->email;
                            }
                        }else{
                            $head['slug'] = '';
                            $head['title'] = '';
                            $head['first_name'] = '';
                            $head['last_name'] = '';
                            $head['middle_name'] = '';
                            $head['image'] = asset('storage').'/images/default-img.png';
                            $head['designation'] = '';
                        }                      
                        $aboutSlug = request()->slug.'/home';
                        $about = array();
                        if ($aboutSlug) {
                           
                            $getData = VCLink::/*where('approval_status','approved')->*/where('slug',$aboutSlug)->with('slider')->first();
                               
                                if ($getData) {
                                    $about['id'] = $getData->id;
                                    $about['link_name'] = $getData->link_name_en; 
                                    $about['description'] = $getData->description_en;
                                    $about['updated_at'] = $getData->updated_at;
                                    if ($getData->slider->count()) {
                                        $about['slider'] = $getData->slider;
                                    }else{
                                        $about['slider'][0] = array('image' => '/images/amu-aligarh2.jpg');
                                    }
                                }
                        }
                        
                        
                        $grouplinkcount = VCLink::select('group_type_id')->groupBy('group_type_id')->where('ac_non_ac_id',$id)->get()->toArray();
                        $grouplink = array_column($grouplinkcount, 'group_type_id');
                        $links = GroupLink::select('title_en as title','id')->whereIn('id', $grouplink)
                                ->with(['getVCRelatedLink' => function($q)  use($id){ 
                                  $q->where('ac_non_ac_id', '=',$id);
                                  $q->orderBy('link_order', 'ASC'); }])
                                ->get();
                        $linkData = array();
                        foreach ($links as $key => $value) {
                            $vcRelatedLink = array();
                            foreach ($value->getVCRelatedLink as $k => $v) {
                                $vcRelatedLink[$k]['id']=$v->id; 
                                $vcRelatedLink[$k]['group_type_id'] = $v->group_type_id;
                                $vcRelatedLink[$k]['content_type'] = $v->content_type; 
                                $vcRelatedLink[$k]['link_name'] = $v->link_name_en;
                                /*if ($v->content_type == 'text') {
                                    $vcRelatedLink[$k]['slug'] = $v->slug;
                                }elseif ($v->content_type == 'file') {
                                    $vcRelatedLink[$k]['slug'] = $v->file;
                                }elseif ($v->content_type == 'link') {
                                    $vcRelatedLink[$k]['slug'] = $v->link;
                                }*/
                                $vcRelatedLink[$k]['slug'] = $v->slug;
                                $vcRelatedLink[$k]['file'] = $v->file;
                                $vcRelatedLink[$k]['link'] = $v->link; 
                                $vcRelatedLink[$k]['link_order'] = $v->link_order;
                                $vcRelatedLink[$k]['approval_status'] = $v->approval_status;
                            }
                            $linkData[$key]['id']=$value->id;
                            $linkData[$key]['title'] = $value->title; 
                            $linkData[$key]['get_v_c_related_link'] = $vcRelatedLink;
                            
                        }
                        $seo = array();                      
                        $seo['meta_title'] = $academic->meta_title;
                        $seo['meta_description'] = $academic->meta_description;
                        $seo['meta_keyword'] = $academic->meta_keyword;
                        $seo['og_image'] = $academic->image;
                        $data['SEO'] = $seo;     
                        $data['links']=$linkData; 
                        $data['head']=$head; 
                        $data['title'] = $academic->title_en;
                        $data['head_login'] = $academic->getHeadType; 
                        $data['sub_type'] = $academic->getSubType;
                        $data['about'] = $about;                         
                        if (empty($data)){                        
                            return $this->sendResponse($data, trans($locale.'_lang.DATA_NOT_FOUND'),404);
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

    // Get Non Academic's related link data by related link path
    public function getAmuVcChancellorContent(Request $request)
    {
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
                        $slug = request()->slug;
                        if ($slug) {                            
                            $getData = VCLink::/*where('approval_status','approved')->*/where('slug',$slug)->first();
                            if ($getData) {
                                $dataUR['id'] = $getData->id;
                                if($getData->link_ur == NULL){
                                    $dataUR['link'] = $getData->link_name_en; 
                                }else{ 
                                    $dataUR['link'] = $getData->link_name_ur; 
                                }
                                if($getData->link_description_ur == NULL){
                                    $dataUR['link_description'] = $getData->description_en;
                                }else{
                                    $dataUR['link_description'] = $getData->description_ur;
                                }
                                $dataUR['updated_at'] = $getData->updated_at;
                            }
                        }                                 
                        if ($dataUR){                        
                            return $this->sendResponse($dataUR, trans($locale.'_lang.DATA_FOUND'),200);
                        } else {
                            return $this->sendResponse($dataUR, trans($locale.'_lang.DATA_NOT_FOUND'),404);
                        }
                        break;
                    case "hi": //GET widget IN Hindi
                        $dataHI = array();
                        $slug = request()->slug;
                        if ($slug) {                            
                            $getData = VCLink::/*where('approval_status','approved')->*/where('slug',$slug)->first();
                            if ($getData) {
                                $dataHI['id'] = $getData->id;
                                if($getData->link_hi == NULL){
                                    $dataHI['link'] = $getData->link_name_en; 
                                }else{ 
                                    $dataHI['link'] = $getData->link_name_hi; 
                                }
                                if($getData->link_description_hi == NULL){
                                    $dataHI['link_description'] = $getData->description_en;
                                }else{
                                    $dataHI['link_description'] = $getData->description_hi;
                                }
                                $dataHI['updated_at'] = $getData->updated_at;
                            }
                        }                        
                        if ($dataHI){                        
                            return $this->sendResponse($dataHI, trans($locale.'_lang.DATA_FOUND'),200);
                        } else {
                            return $this->sendResponse($dataHI, trans($locale.'_lang.DATA_NOT_FOUND'),404);
                        }
                        break;
                    default: //GET widget IN English
                        $data = array(); 
                        $slug = request()->slug;
                        if ($slug) {                            
                            $getData = VCLink::/*where('approval_status','approved')->*/where('slug',$slug)->first();
                            if ($getData) {
                                $data['id'] = $getData->id;
                                $data['link'] = $getData->link_name_en;
                                $data['link_description'] = $getData->description_en;
                                $data['updated_at'] = $getData->updated_at;
                            }
                        }
                        if (empty($data)){                        
                            return $this->sendResponse($data, trans($locale.'_lang.DATA_NOT_FOUND'),404);
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


    // Get Non academic section wise related link detail
    public function getNonAcademiceSectionDetail(Request $request){
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
                        $academic =  AcademicAndNonAcademic::where('slug',request()->slug)->first();
                        $id = $academic->id;
                        $head = array();
                        $userData = UserVisibility::where('ac_non_ac_id',$id)->where('role_id',$academic->head)->with('getHeadFaculty.getContact','getDesignation','getRole')->first();
                        if(!empty($userData)){
                            $head['slug'] = $userData->getHeadFaculty->slug;
                            $head['title'] = $userData->getHeadFaculty->title;
                            $head['first_name'] = $userData->getHeadFaculty->first_name;
                            $head['last_name'] = $userData->getHeadFaculty->last_name;
                            $head['middle_name'] = $userData->getHeadFaculty->middle_name;
                            $head['url'] = $userData->getHeadFaculty->url;
                            if($userData->getHeadFaculty->image !=NULL){
                               $head['image'] = asset('storage/').$userData->getHeadFaculty->image;
                            }else{
                               $head['image'] = asset('storage').'/images/default-img.png';
                            }
                            $head['designation'] = $userData->getDesignation->name;  
                        }else{
                            $head['slug'] = '';
                            $head['title'] = '';
                            $head['first_name'] = '';
                            $head['last_name'] = '';
                            $head['middle_name'] = '';
                            $head['image'] = asset('storage').'/images/default-img.png';
                            $head['designation'] = '';
                        }
                        
                        
                        $links = array(); 
                        $relatedLinks = RelatedLink::orderBy('link_order','asc')->where('ac_non_ac_id',$id)->get();
                        foreach ($relatedLinks as $key => $value) {
                                $links[$key]['id'] = $value->id;
                                if($value->link_name_ur == NULL){
                                    $links[$key]['link'] = $value->link_name_en; 
                                }else{ 
                                    $links[$key]['link'] = $value->link_name_ur; 
                                }
                                $links[$key]['slug'] = $value->slug;
                        }
                        $aboutSlug = request()->slug.'/home-page';
                        $aboutId = RelatedLink::where('slug',$aboutSlug)->first('id');
                        $getData = RelatedLinkData::/*where('approval_status','approved')->*/where('related_link_id',$aboutId->id)->with('slider')->first();
                        $about = array();
                        if ($getData) {
                            $about['id'] = $getData->id;
                            if($getData->link_ur == NULL){
                                $about['link'] = $getData->link_en; 
                            }else{ 
                                $about['link'] = $getData->link_ur; 
                            }
                            if($getData->link_description_ur == NULL){
                                $about['link_description'] = $getData->link_description_en;
                            }else{
                                $about['link_description'] = $getData->link_description_ur;
                            }
                            $about['updated_at'] = $getData->updated_at;
                            if ($getData->slider->count()) {
                                $about['slider'] = $getData->slider;
                            }else{
                                $about['slider'][0] = array('image' => '/images/amu-aligarh2.jpg');
                            }
                        }                      
                        $dataUR['links']=$links;
                        $dataUR['head']=$head;
                        $dataUR['about']=$about;
                        if($academic->title_ur == NULL){
                            $dataUR['title'] = $academic->title_en;  
                        }else{
                            $dataUR['title'] = $academic->title_ur; 
                        }                        
                        if ($dataUR){                        
                            return $this->sendResponse($dataUR, trans($locale.'_lang.DATA_FOUND'),200);
                        } else {
                            return $this->sendResponse($dataUR, trans($locale.'_lang.DATA_NOT_FOUND'),404);
                        }
                        break;
                    case "hi": //GET widget IN Hindi
                        $dataHI = array();
                        $academic =  AcademicAndNonAcademic::where('slug',request()->slug)->first();
                        $id = $academic->id;
                        $head = array();
                        $userData = UserVisibility::where('ac_non_ac_id',$id)->where('role_id',$academic->head)->with('getHeadFaculty.getContact','getDesignation','getRole')->first();
                        if(!empty($userData)){
                        $head['slug'] = $userData->getHeadFaculty->slug;
                        $head['title'] = $userData->getHeadFaculty->title;
                        $head['first_name'] = $userData->getHeadFaculty->first_name;
                        $head['last_name'] = $userData->getHeadFaculty->last_name;
                        $head['middle_name'] = $userData->getHeadFaculty->middle_name;
                        $head['url'] = $userData->getHeadFaculty->url;
                        if($userData->getHeadFaculty->image !=NULL){
                           $head['image'] = asset('storage/').$userData->getHeadFaculty->image;
                        }else{
                           $head['image'] = asset('storage').'/images/default-img.png';
                        }
                        $head['designation'] = $userData->getDesignation->name;
                        }else{
                            $head['slug'] = '';
                            $head['title'] = '';
                            $head['first_name'] = '';
                            $head['last_name'] = '';
                            $head['middle_name'] = '';
                            $head['image'] = asset('storage').'/images/default-img.png';
                            $head['designation'] = '';
                        }
                        $aboutSlug = request()->slug.'/home-page';
                        $aboutId = RelatedLink::where('slug',$aboutSlug)->first('id');
                        $getData = RelatedLinkData::/*where('approval_status','approved')->*/where('related_link_id',$aboutId->id)->with('slider')->first();
                        $about = array();
                        if ($getData) {
                            $about['id'] = $getData->id;
                            if($getData->link_hi == NULL){
                                $about['link'] = $getData->link_en; 
                            }else{ 
                                $about['link'] = $getData->link_hi; 
                            }
                            if($getData->link_description_hi == NULL){
                                $about['link_description'] = $getData->link_description_en;
                            }else{
                                $about['link_description'] = $getData->link_description_hi;
                            }
                            $about['updated_at'] = $getData->updated_at;
                            if ($getData->slider->count()) {
                                $about['slider'] = $getData->slider;
                            }else{
                                $about['slider'][0] = array('image' => '/images/amu-aligarh2.jpg');
                            }
                        }
                        $links = array(); 
                        $relatedLinks = RelatedLink::orderBy('link_order','asc')->where('ac_non_ac_id',$id)->get();
                        foreach ($relatedLinks as $key => $value) {
                                $links[$key]['id'] = $value->id;
                                if($value->link_name_hi == NULL){
                                    $links[$key]['link'] = $value->link_name_en; 
                                }else{ 
                                    $links[$key]['link'] = $value->link_name_hi; 
                                }
                                $links[$key]['slug'] = $value->slug;
                        }
                        $dataHI['links']=$links;
                        $dataHI['head']=$head;
                        $dataHI['about']=$about;
                        if($academic->title_hi == NULL){
                            $dataHI['title'] = $academic->title_en;  
                        }else{
                            $dataHI['title'] = $academic->title_hi; 
                        }  
                        if ($dataHI){                        
                            return $this->sendResponse($dataHI, trans($locale.'_lang.DATA_FOUND'),200);
                        } else {
                            return $this->sendResponse($dataHI, trans($locale.'_lang.DATA_NOT_FOUND'),404);
                        }
                        break;
                    default: //GET widget IN English
                        $data = array();                        
                        
                            $academic =  AcademicAndNonAcademic::where('slug',request()->slug)->first();
                          
                           
                            $id = $academic->id;
                            $head = array();
                            $userData = UserVisibility::where('ac_non_ac_id',$id)->where('role_id',$academic->head)->with('getHeadFaculty.getContact','getDesignation','getRole')->first();
                            if ($userData){                        
                                $head['slug'] = $userData->getHeadFaculty->slug;
                                $head['title'] = $userData->getHeadFaculty->title;
                                $head['first_name'] = $userData->getHeadFaculty->first_name;
                                $head['last_name'] = $userData->getHeadFaculty->last_name;
                                $head['middle_name'] = $userData->getHeadFaculty->middle_name;
                                $head['url'] = $userData->getHeadFaculty->url;
                                if($userData->getHeadFaculty->image !=NULL){
                                   $head['image'] = asset('storage/').'/'.$userData->getHeadFaculty->image;
                                }else{
                                   $head['image'] = asset('storage').'/images/default-img.png';
                                }
                                $head['designation'] = $userData->getDesignation->name;
                            }else{
                                $head['slug'] = '';
                                $head['title'] = '';
                                $head['first_name'] = '';
                                $head['last_name'] = '';
                                $head['middle_name'] = '';
                                $head['image'] = asset('storage').'/images/default-img.png';
                                $head['designation'] = '';
                            }                          
                            $aboutSlug = request()->slug.'/home-page';
                            $aboutId = RelatedLink::where('slug',$aboutSlug)->first('id');
                            $getData = RelatedLinkData::/*where('approval_status','approved')->*/where('related_link_id',$aboutId->id)->with('slider')->first();
                            $about = array();
                            if ($getData) {
                                $about['id'] = $getData->id;
                                $about['link'] = $getData->link_en;
                                $about['link_description'] = $getData->link_description_en;
                                $about['updated_at'] = $getData->updated_at;
                                if ($getData->slider->count()) {
                                    $about['slider'] = $getData->slider;
                                }else{
                                    $about['slider'][0] = array('image' => '/images/amu-aligarh2.jpg');
                                }
                            }
                            
                            $links = array(); 
                            $relatedLinks = RelatedLink::orderBy('link_order','asc')->where('ac_non_ac_id',$id)->get();
                            foreach ($relatedLinks as $key => $value) {
                                    $links[$key]['id'] = $value->id;
                                    $links[$key]['link'] = $value->link_name_en;
                                    $links[$key]['slug'] = $value->slug;
                            }
                            $data['links']=$links;
                            
                            $othermap =  MappingDepartments::where('ac_non_ac_id',$id)->orwhere('dep_id',$id)->first();
                            if ($othermap) {
                                $section = array(); 
                                $mapdata = MappingDepartments::select('id','ac_non_ac_id','dep_id','type')->where('ac_non_ac_id',$othermap->ac_non_ac_id)->with('departments:id,title_en,slug')->orderBy('id','ASC')->get();
                                if($mapdata->count()){
                                    foreach ($mapdata as $key => $value){
                                        $section[$key]['id'] = $value->departments->id;
                                        $section[$key]['title'] = $value->departments->title_en;
                                        $section[$key]['slug'] = $value->departments->slug;
                                    }
                                }
                                $data['section']=$section;
                            }
                            
                                                        
                            $data['head']=$head; 
                            $data['title'] = $academic->title_en; 
                            $data['about'] = $about;                         
                        if (empty($data)){                        
                            return $this->sendResponse($data, trans($locale.'_lang.DATA_NOT_FOUND'),404);
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

    // Get Non Academic's related link data by related link path
    public function getNonAcadmicContent(Request $request)
    {
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
                $linkId = RelatedLink::where('slug',request()->slug)->with('getAcAndNonAc')->first();
                if (empty($linkId)){                        
                    return $this->sendResponse($linkId, trans($locale.'_lang.DATA_NOT_FOUND'),404);
                }
                $academic =  AcademicAndNonAcademic::select('id','head','sub_type')->where('id',$linkId->ac_non_ac_id)->first();
                
                switch ($locale) {
                    case "ur": //GET widget IN Urdu
                        $dataUR = array();
                        
                        if(strpos(request()->slug,'home-page') !== false || strpos(request()->slug,'contact-us') !== false){
                            $getData = RelatedLinkData::/*where('approval_status','approved')->*/where('related_link_id',$linkId->id)->orderBy('order_on','ASC')->first();
                            if ($getData) {
                                $dataUR['id'] = $getData->id;
                                $dataUR['link'] = $getData->link_en; 
                                $dataUR['link_description'] = $getData->link_description_en;
                                $dataUR['file'] = $getData->file;
                                $dataUR['updated_at'] = $getData->updated_at;
                            }
                        }elseif(strpos(request()->slug,'faculty-members') !== false || strpos(request()->slug,'staff-members') !== false || strpos(request()->slug ,'non-teaching-staff') !== false || strpos(request()->slug,'staff-member-teaching') !== false){
                            if(strpos(request()->slug,'faculty-members') !== false || strpos(request()->slug, 'staff-member-teaching') !== false)
                            {
                                $faculties = UserVisibility::where('ac_non_ac_id',$linkId->ac_non_ac_id)->where('for_id','1')->where('status','1')->with('getUser.getContact','getUser.getDepartment.getAcNonAcItem','getDesignation','getRole')->orderBy('role_id','ASC')->orderBy('order_on','ASC')->get();
                                if (empty($faculties)){                        
                                    return $this->sendResponse($getData, trans($locale.'_lang.DATA_NOT_FOUND'),404);
                                }
                                $key=0;
                                foreach ($faculties as $value) {
                                    if($value->getUser != NULL){
                                        $dataUR[$key]['user_id'] = $value->user_id;
                                        $dataUR[$key]['for_id'] = $value->for_id;
                                        $dataUR[$key]['order_on'] = $value->order_on;
                                        $dataUR[$key]['slug'] = $value->getUser->slug;
                                        $dataUR[$key]['title'] = $value->getUser->title;
                                        $dataUR[$key]['first_name'] = $value->getUser->first_name;
                                        $dataUR[$key]['last_name'] = $value->getUser->last_name;
                                        $dataUR[$key]['middle_name'] = $value->getUser->middle_name;
                                        $dataUR[$key]['url'] = $value->getUser->url;
                                        if($value->getUser->image !=NULL){
                                           $dataUR[$key]['image'] = asset('storage').$value->getUser->image;
                                        }else{
                                           $dataUR[$key]['image'] = asset('storage').'/images/default-img.png';
                                        }
                                        //$dataUR[$key]['image'] = $userImage;
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
                                        $key++;
                                    }
                                }

                            }
                            if(strpos(request()->slug, 'staff-members') !== false)
                            {
                                $faculties = UserVisibility::where('ac_non_ac_id',$linkId->ac_non_ac_id)->where('status','1')
                                ->when($academic, function ($query) use($academic){
                                  $query->where('role_id','!=',$academic->head);
                                  $query->where('core','!=', '0');
                                })->with('getUser.getContact','getUser.getDepartment.getAcNonAcItem','getDesignation')->orderBy('order_on','ASC')->orderBy('role_id','ASC')->get();
                                if (empty($faculties)){                        
                                    return $this->sendResponse($getData, trans($locale.'_lang.DATA_NOT_FOUND'),404);
                                }
                                $key=0;
                                foreach ($faculties as $value) {
                                    if($value->getUser != NULL){
                                        $dataUR[$key]['user_id'] = $value->user_id;
                                        $dataUR[$key]['for_id'] = $value->for_id;
                                        $dataUR[$key]['order_on'] = $value->order_on;
                                        $dataUR[$key]['slug'] = $value->getUser->slug;
                                        $dataUR[$key]['title'] = $value->getUser->title;
                                        $dataUR[$key]['first_name'] = $value->getUser->first_name;
                                        $dataUR[$key]['last_name'] = $value->getUser->last_name;
                                        $dataUR[$key]['middle_name'] = $value->getUser->middle_name;
                                        $dataUR[$key]['url'] = $value->getUser->url;
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
                                        $key++;
                                    }
                                }
                            }
                            if(strpos(request()->slug, 'non-teaching-staff') !== false) {
                                $faculties = UserVisibility::where('ac_non_ac_id',$linkId->ac_non_ac_id)->where('for_id','2')->where('status','1')->with('getUser.getContact','getUser.getDepartment.getAcNonAcItem','getDesignation')->orderBy('role_id','ASC')->orderBy('order_on','ASC')->get();
                                if (empty($faculties)){                        
                                    return $this->sendResponse($getData, trans($locale.'_lang.DATA_NOT_FOUND'),404);
                                }
                                $key=0;
                                foreach ($faculties as $value) {
                                    if($value->getUser != NULL){
                                        $dataUR[$key]['user_id'] = $value->user_id;
                                        $dataUR[$key]['for_id'] = $value->for_id;
                                        $dataUR[$key]['order_on'] = $value->order_on;
                                        $dataUR[$key]['slug'] = $value->getUser->slug;
                                        $dataUR[$key]['title'] = $value->getUser->title;
                                        $dataUR[$key]['first_name'] = $value->getUser->first_name;
                                        $dataUR[$key]['last_name'] = $value->getUser->last_name;
                                        $dataUR[$key]['middle_name'] = $value->getUser->middle_name;
                                        $dataUR[$key]['url'] = $value->getUser->url;
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
                                        $key++;
                                    }
                                }
                            }

                            if ($academic->head != '') {
                                $id = sectionHeadDetail($academic->id, $academic->head);
                                $results = json_decode($id->content(), true);
                                if ($results['code'] == 1000) {
                                    $dataUR = array_merge($results['head'], $dataUR);
                                }                            
                            }
                        }elseif( strpos(request()->slug, 'notice-and-circular') !== false){
                            $dataUR = NoticeCircular::select('id','title_en','title_ur','to_date','file','status','approval_status','created_at','updated_at')->where('approval_status','Approved')->where('ac_non_ac_id',$linkId->ac_non_ac_id)->orderBy('created_at','desc')
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

                       }elseif (strpos(request()->slug, 'photo-gallery') !== false) {

                            $getData = AcNonAcGallery::where('ac_non_ac_id', $linkId->ac_non_ac_id)->orderBy('order_on','ASC')->orderBy('updated_at','DESC')->get();
                            foreach ($getData as $key => $value) {
                                $dataUR[$key]['id'] = $value->id;
                                if($value->title_ur == NULL){
                                    $dataUR[$key]['title'] = $value->title_en; 
                                }else{ 
                                    $dataUR[$key]['title'] = $value->title_ur; 
                                }
                                $dataUR[$key]['image'] = $value->image;
                                $dataUR[$key]['order_on'] = $value->order_on;
                                $dataUR[$key]['updated_at'] = $value->updated_at;
                            }

                            if ($dataUR){                        
                                return $this->sendResponse($dataUR, trans($locale.'_lang.DATA_FOUND'),200);
                            } else {
                                return $this->sendResponse($dataUR, trans($locale.'_lang.DATA_NOT_FOUND'),404);
                            }
                       }else{
                            $getData = RelatedLinkData::orderBy('order_on', 'ASC')->where('related_link_id',$linkId->id)->get();
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
                            return $this->sendResponse($dataUR, trans($locale.'_lang.DATA_FOUND'),200);
                        } else {
                            return $this->sendResponse($dataUR, trans($locale.'_lang.DATA_NOT_FOUND'),404);
                        }
                        break;
                    case "hi": //GET widget IN Hindi
                        $dataHI = array();
                       
                        if(strpos(request()->slug,'home-page') !== false || strpos(request()->slug,'contact-us') !== false){
                            $getData = RelatedLinkData::/*where('approval_status','approved')->*/where('related_link_id',$linkId->id)->orderBy('order_on','ASC')->first();
                            if ($getData) {
                                $dataHI['id'] = $getData->id;
                                $dataHI['link'] = $getData->link_en; 
                                $dataHI['link_description'] = $getData->link_description_en;
                                $dataHI['file'] = $getData->file;
                                $dataHI['updated_at'] = $getData->updated_at;
                             } 
                                
                           
                        }elseif(strpos(request()->slug,'faculty-members') !== false || strpos(request()->slug,'staff-members') !== false || strpos(request()->slug ,'non-teaching-staff') !== false || strpos(request()->slug,'staff-member-teaching') !== false){
                            if(strpos(request()->slug,'faculty-members') !== false|| strpos(request()->slug, 'staff-member-teaching') !== false)
                            {
                                $faculties = UserVisibility::where('ac_non_ac_id',$linkId->ac_non_ac_id)->where('for_id','1')->where('status','1')->with('getUser.getContact','getUser.getDepartment.getAcNonAcItem','getDesignation','getRole')->orderBy('order_on','ASC')->orderBy('role_id','ASC')->get();
                                if (empty($faculties)){                        
                                    return $this->sendResponse($getData, trans($locale.'_lang.DATA_NOT_FOUND'),404);
                                }
                                $key=0;
                                foreach ($faculties as $value) {
                                    if($value->getUser != NULL){
                                        $dataHI[$key]['user_id'] = $value->user_id;
                                        $dataHI[$key]['for_id'] = $value->for_id;
                                        $dataHI[$key]['order_on'] = $value->order_on;
                                        $dataHI[$key]['slug'] = $value->getUser->slug;
                                        $dataHI[$key]['title'] = $value->getUser->title;
                                        $dataHI[$key]['first_name'] = $value->getUser->first_name;
                                        $dataHI[$key]['last_name'] = $value->getUser->last_name;
                                        $dataHI[$key]['middle_name'] = $value->getUser->middle_name;
                                        $dataUR[$key]['url'] = $value->getUser->url;
                                        if($value->getUser->image != NULL){
                                           $userImage = asset('storage/').$value->getUser->image;
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
                                        $key++;
                                    }
                                }
                            }
                            if(strpos(request()->slug, 'staff-members') !== false)
                            {
                                $faculties = UserVisibility::where('ac_non_ac_id',$linkId->ac_non_ac_id)->where('status','1')
                                ->when($academic, function ($query) use($academic){
                                  $query->where('role_id','!=',$academic->head);
                                  $query->where('core','!=', '0');
                                })->with('getUser.getContact','getUser.getDepartment.getAcNonAcItem','getDesignation')->orderBy('order_on','ASC')->orderBy('role_id','ASC')->get();
                                if (empty($faculties)){                        
                                    return $this->sendResponse($getData, trans($locale.'_lang.DATA_NOT_FOUND'),404);
                                }
                                $key=0;
                                foreach ($faculties as $value) {
                                    if($value->getUser != NULL){
                                        $dataHI[$key]['user_id'] = $value->user_id;
                                        $dataHI[$key]['for_id'] = $value->for_id;
                                        $dataHI[$key]['order_on'] = $value->order_on;
                                        $dataHI[$key]['slug'] = $value->getUser->slug;
                                        $dataHI[$key]['title'] = $value->getUser->title;
                                        $dataHI[$key]['first_name'] = $value->getUser->first_name;
                                        $dataHI[$key]['last_name'] = $value->getUser->last_name;
                                        $dataHI[$key]['middle_name'] = $value->getUser->middle_name;
                                        $dataUR[$key]['url'] = $value->getUser->url;
                                        if($value->getUser->image != NULL){
                                           $userImage = asset('storage/').$value->getUser->image;
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
                                        $key++;
                                    }
                                }
                            }
                            if(strpos(request()->slug, 'non-teaching-staff') !== false) {
                                $faculties = UserVisibility::where('ac_non_ac_id',$linkId->ac_non_ac_id)->where('for_id','2')->where('status','1')->with('getUser.getContact','getUser.getDepartment.getAcNonAcItem','getDesignation')->orderBy('role_id','ASC')->orderBy('order_on','ASC')->get();
                                if (empty($faculties)){                        
                                    return $this->sendResponse($getData, trans($locale.'_lang.DATA_NOT_FOUND'),404);
                                }
                                $key=0;
                                foreach ($faculties as $value) {
                                    if($value->getUser != NULL){
                                        $dataHI[$key]['user_id'] = $value->user_id;
                                        $dataHI[$key]['for_id'] = $value->for_id;
                                        $dataHI[$key]['order_on'] = $value->order_on;
                                        $dataHI[$key]['slug'] = $value->getUser->slug;
                                        $dataHI[$key]['title'] = $value->getUser->title;
                                        $dataHI[$key]['first_name'] = $value->getUser->first_name;
                                        $dataHI[$key]['last_name'] = $value->getUser->last_name;
                                        $dataHI[$key]['middle_name'] = $value->getUser->middle_name;
                                        $dataHI[$key]['url'] = $value->getUser->url;
                                        if($value->getUser->image !=NULL){
                                           $userImage = asset('storage/').$value->getUser->image;
                                        }else{
                                           $userImage = asset('storage').'/images/default-img.png';
                                        }
                                        $dataHI[$key]['image'] = $userImage;
                                        $dataHI[$key]['designation'] = $value->getDesignation->name; 
                                        
                                        $dataHI[$key]['email'] = $value->getUser->getContact[0]->email;
                                        $dataHI[$key]['mobile_no'] = $value->getUser->getContact[0]->mobile_no;
                                        $dataHI[$key]['telephone_no'] = $value->getUser->getContact[0]->telephone_no;
                                        $dataHI[$key]['core_department'] = $value->getUser->getDepartment->getAcNonAcItem[0]->slug;
                                        $key++;
                                    }
                                }
                            }
                            if ($academic->head != '') {
                                $id = sectionHeadDetail($academic->id, $academic->head);
                                $results = json_decode($id->content(), true);
                                if ($results['code'] == 1000) {
                                    $dataHI = array_merge($results['head'], $dataHI);
                                }                            
                            }
                        }elseif( strpos(request()->slug, 'notice-and-circular') !== false){
                            $dataHI = NoticeCircular::select('id','title_en','title_hi','to_date','file','status','approval_status','created_at','updated_at')->where('approval_status','Approved')->where('ac_non_ac_id',$linkId->ac_non_ac_id)->orderBy('created_at','desc')
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

                            $getData = AcNonAcGallery::where('ac_non_ac_id', $linkId->ac_non_ac_id)->orderBy('order_on','ASC')->orderBy('updated_at','DESC')->get();
                            foreach ($getData as $key => $value) {
                                $dataHI[$key]['id'] = $value->id;
                                if($value->title_ur == NULL){
                                    $dataHI[$key]['title'] = $value->title_en; 
                                }else{ 
                                    $dataHI[$key]['title'] = $value->title_hi; 
                                }
                                $dataHI[$key]['image'] = $value->image;
                                $dataHI[$key]['order_on'] = $value->order_on;
                                $dataHI[$key]['updated_at'] = $value->updated_at;
                            }

                            if ($dataHI){                        
                                return $this->sendResponse($dataHI, trans($locale.'_lang.DATA_FOUND'),200);
                            } else {
                                return $this->sendResponse($dataHI, trans($locale.'_lang.DATA_NOT_FOUND'),404);
                            }
                        }else{
                            $getData = RelatedLinkData::orderBy('order_on', 'ASC')->where('related_link_id',$linkId->id)->get();
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
                            return $this->sendResponse($dataHI, trans($locale.'_lang.DATA_FOUND'),200);
                        } else {
                            return $this->sendResponse($dataHI, trans($locale.'_lang.DATA_NOT_FOUND'),404);
                        }
                        break;
                    default: //GET widget IN English
                        $data = array(); 
                        if(strpos(request()->slug,'home-page') !== false || strpos(request()->slug,'contact-us') !== false){
                            $getData = RelatedLinkData::/*where('approval_status','approved')->*/where('related_link_id',$linkId->id)->orderBy('order_on','ASC')->first();
                            if ($getData) {
                                $data['id'] = $getData->id;
                                $data['link'] = $getData->link_en; 
                                $data['link_description'] = $getData->link_description_en;
                                $data['file'] = $getData->file;
                                $data['updated_at'] = $getData->updated_at;
                            }
                        }elseif(strpos(request()->slug,'faculty-members') !== false || strpos(request()->slug,'staff-members') !== false || strpos(request()->slug ,'non-teaching-staff') !== false || strpos(request()->slug,'staff-member-teaching') !== false){
                            
                            if(strpos(request()->slug,'faculty-members') !== false || strpos(request()->slug, 'staff-member-teaching') !== false)
                            {
                                
                                $faculties = UserVisibility::where('ac_non_ac_id',$linkId->ac_non_ac_id)->where('for_id','1')->where('status','1')->when($academic, function ($query) use($academic){
                                  $query->where('role_id','!=',$academic->head);
                                  //$query->where('core','!=', '0');
                                })->with('getUser.getContact','getUser.getDepartment.getAcNonAcItem','getDesignation','getRole')->orderBy('order_on','ASC')->orderBy('role_id','ASC')->get();

                                if (empty($faculties)){                        
                                    return $this->sendResponse($faculties, trans($locale.'_lang.DATA_NOT_FOUND'),404);
                                }
                                $key=0;
                                foreach ($faculties as $value) {
                                    if($value->getUser != NULL){
                                        $data[$key]['user_id'] = $value->user_id;
                                        $data[$key]['for_id'] = $value->for_id;
                                        $data[$key]['order_on'] = $value->order_on;
                                        $data[$key]['slug'] = $value->getUser->slug;
                                        $data[$key]['title'] = $value->getUser->title;
                                        $data[$key]['first_name'] = $value->getUser->first_name;
                                        $data[$key]['last_name'] = $value->getUser->last_name;
                                        $data[$key]['middle_name'] = $value->getUser->middle_name;
                                        $data[$key]['url'] = $value->getUser->url;
                                        if($value->getUser->image != NULL || $value->getUser->image != ''){
                                           $userImage = asset('storage/').'/'.$value->getUser->image;
                                        }else{
                                           $userImage = asset('storage').'/images/default-img.png';
                                        }
                                        $data[$key]['image'] = $userImage;
                                        if($academic->sub_type == '2' && $value->role_id == $academic->head){
                                          $data[$key]['designation'] = $value->getRole->title.' and ' .$value->getDesignation->name;
                                        }else{
                                          $data[$key]['designation'] = $value->getDesignation->name;  
                                        }
                                        
                                        if ($value->getUser->getContact) {
                                            foreach ($value->getUser->getContact as $val) {
                                                if ($val->email_visibility == '1') {
                                                    $data[$key]['email'] = $val->email;
                                                }
                                                if ($val->mobile_visibility == '1') {
                                                    $data[$key]['mobile_no'] = $val->mobile_no;
                                                }
                                                $data[$key]['telephone_no'] = $val->telephone_no;
                                            }
                                        }else{
                                            $data[$key]['email'] = '';
                                            $data[$key]['mobile_no'] = '';
                                            $data[$key]['telephone_no'] = '';
                                        }
                                        $key++;
                                    }
                                }
                            }
                            if(strpos(request()->slug, 'staff-members') !== false)
                            {
                                                            
                                $faculties = UserVisibility::where('ac_non_ac_id',$linkId->ac_non_ac_id)->where('status','1')
                                ->when($academic, function ($query) use($academic){
                                  $query->where('role_id','!=',$academic->head);
                                  //$query->where('core','!=', '0');
                                })->with('getUser.getContact','getUser.getDepartment.getAcNonAcItem','getDesignation')->orderBy('order_on','ASC')->orderBy('role_id','ASC')->get();
                                if (empty($faculties)){                        
                                    return $this->sendResponse($faculties, trans($locale.'_lang.DATA_NOT_FOUND'),404);
                                }

                                $key=0;
                                foreach ($faculties as $value) {
                                    if($value->getUser != NULL){
                                        $data[$key]['user_id'] = $value->user_id;
                                        $data[$key]['url'] = $value->getUser->url;
                                        $data[$key]['for_id'] = $value->for_id;
                                        $data[$key]['slug'] = $value->getUser->slug;
                                        $data[$key]['title'] = $value->getUser->title;
                                        $data[$key]['first_name'] = $value->getUser->first_name;
                                        $data[$key]['middle_name']= $value->getUser->middle_name;
                                        $data[$key]['last_name'] = $value->getUser->last_name;
                                        
                                        if($value->getUser->image != NULL || $value->getUser->image != ''){
                                           $userImage = asset('storage/').'/'.$value->getUser->image;
                                        }else{
                                           $userImage = asset('storage').'/images/default-img.png';
                                        }
                                        $data[$key]['image'] = $userImage;
                                        $data[$key]['designation'] = $value->getDesignation->name;
                                        
                                        if ($value->getUser->getContact) {
                                            foreach ($value->getUser->getContact as $val) {
                                                if ($val->email_visibility == '1') {
                                                    $data[$key]['email'] = $val->email;
                                                }
                                                if ($val->mobile_visibility == '1') {
                                                    $data[$key]['mobile_no'] = $val->mobile_no;
                                                }
                                                $data[$key]['telephone_no'] = $val->telephone_no;
                                            }
                                        }else{
                                            $data[$key]['email'] = '';
                                            $data[$key]['mobile_no'] = '';
                                            $data[$key]['telephone_no'] = '';
                                        }
                                        $key++;
                                    }
                                }
                            }
                            if(strpos(request()->slug, 'non-teaching-staff') !== false || strpos(request()->slug, 'staff-list') !== false) {
                                $faculties = UserVisibility::where('ac_non_ac_id',$linkId->ac_non_ac_id)->where('for_id','2')->where('status','1')->when($academic, function ($query) use($academic){
                                  $query->where('role_id','!=',$academic->head);
                                  //$query->where('core','!=', '0');
                                })->with('getUser.getContact','getUser.getDepartment.getAcNonAcItem','getDesignation')->orderBy('role_id','ASC')->orderBy('order_on','ASC')->get();
                                if (empty($faculties)){                        
                                    return $this->sendResponse($faculties, trans($locale.'_lang.DATA_NOT_FOUND'),404);
                                }
                                $key=0;
                                foreach ($faculties as $value) {
                                    if($value->getUser != NULL){
                                        $data[$key]['user_id'] = $value->user_id;
                                        $data[$key]['for_id'] = $value->for_id;
                                        $data[$key]['order_on'] = $value->order_on;
                                        $data[$key]['slug'] = $value->getUser->slug;
                                        $data[$key]['title'] = $value->getUser->title;
                                        $data[$key]['first_name'] = $value->getUser->first_name;
                                        $data[$key]['last_name'] = $value->getUser->last_name;
                                        $data[$key]['middle_name'] = $value->getUser->middle_name;
                                        $data[$key]['url'] = $value->getUser->url;
                                        if($value->getUser->image !=NULL){
                                           $userImage = asset('storage/').$value->getUser->image;
                                        }else{
                                           $userImage = asset('storage').'/images/default-img.png';
                                        }
                                        $data[$key]['image'] = $userImage;
                                        $data[$key]['designation'] = $value->getDesignation->name; 
                                        
                                        if ($value->getUser->getContact) {
                                            foreach ($value->getUser->getContact as $val) {
                                                if ($val->email_visibility == '1') {
                                                    $data[$key]['email'] = $val->email;
                                                }
                                                if ($val->mobile_visibility == '1') {
                                                    $data[$key]['mobile_no'] = $val->mobile_no;
                                                }
                                                $data[$key]['telephone_no'] = $val->telephone_no;
                                            }
                                        }else{
                                            $data[$key]['email'] = '';
                                            $data[$key]['mobile_no'] = '';
                                            $data[$key]['telephone_no'] = '';
                                        }
                                        $key++;
                                    }
                                }
                            }

                            if ($academic->head != '') {
                                $id = sectionHeadDetail($academic->id, $academic->head);
                                $results = json_decode($id->content(), true);
                                if ($results['code'] == 1000) {
                                    $data = array_merge($results['head'], $data);
                                }                            
                            }
                        }elseif( strpos(request()->slug, 'notice-and-circular') !== false){

                            $data = NoticeCircular::select('id','title_en as title','to_date','file','status','approval_status','created_at','updated_at')->where('approval_status','Approved')->where('ac_non_ac_id',$linkId->ac_non_ac_id)->orderBy('created_at','desc')
                                ->when(request()->search, function ($query) {
                                    $query->where('title_en', 'LIKE', '%'.request()->search.'%');
                                })
                                ->paginate(env('ITEM_PER_PAGE'));
                            foreach ($data as $key => $value) {

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
                            $getData = AcNonAcGallery::where('ac_non_ac_id',$linkId->ac_non_ac_id)->orderBy('order_on','ASC')->orderBy('updated_at','DESC')->get();
                            foreach ($getData as $key => $value) {
                                $data[$key]['id'] = $value->id;
                                $data[$key]['title'] = $value->title_en;
                                $data[$key]['image'] = $value->image;
                                $data[$key]['order_on'] = $value->order_on;
                                $data[$key]['updated_at'] = $value->updated_at;
                            }

                            if ($data){                        
                                return $this->sendResponse($data, trans($locale.'_lang.DATA_FOUND'),200);
                            } else {
                                return $this->sendResponse($data, trans($locale.'_lang.DATA_NOT_FOUND'),404);
                            }
                       }else{
                            $getData = RelatedLinkData::orderBy('order_on', 'ASC')->where('related_link_id',$linkId->id)->get();
                            foreach ($getData as $key => $value) {
                                $data[$key]['id'] = $value->id;
                                $data[$key]['link'] = $value->link_en; 
                                $data[$key]['link_description'] = $value->link_description_en;
                                $data[$key]['file'] = $value->file;
                                $data[$key]['updated_at'] = $value->updated_at;
                            } 
                        }
                        if (empty($data)){                        
                            return $this->sendResponse($data, trans($locale.'_lang.DATA_NOT_FOUND'),404);
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

    /**Get Faculty List**/
    public function getFacultyList(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'lang' => 'required|in:en,hi,ur',
        ]);
            
        if($validator->fails()){
            return $this->sendError('Validation Error.', $validator->errors(),422);      
        }

        $faculty  = AcademicAndNonAcademic::select('id','title_en As title')->where('sub_type',1)->where('status',1)->orderBy('title','ASC')->orderBy('id','ASC')->get();

        if (is_null($faculty)){                        
            return $this->sendResponse($faculty, trans('en_lang.DATA_NOT_FOUND'),404);
        }
        return $this->sendResponse($faculty, trans('en_lang.DATA_FOUND'),200);
    }

    /**Get Department list according to faculty**/
    public function getDepartmentList(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'lang' => 'required|in:en,hi,ur',
            'id' => 'required'
        ]);
            
        if($validator->fails()){
            return $this->sendError('Validation Error.', $validator->errors(),422);      
        }

        $mappingdta = MappingDepartments::select('dep_id')->groupBy('dep_id')->where('ac_non_ac_id',request()->id)->get()->toArray();        
        $ac_non_ac_id = array_column($mappingdta, 'dep_id');
        $departments = AcademicAndNonAcademic::select('id','title_en As title')->whereIn('id', $ac_non_ac_id)->where('status',1)->orderBy('title_en', 'ASC')->get();

        if (is_null($departments)){                        
            return $this->sendResponse($departments, trans('en_lang.DATA_NOT_FOUND'),404);
        }
        return $this->sendResponse($departments, trans('en_lang.DATA_FOUND'),200);
    } 

    /**Get Department Members List By Faculty**/
    public function getFacultyMembers(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'lang' => 'required|in:en,hi,ur',
            'ac_non_ac_id' => 'required',
        ]);
            
        if($validator->fails()){
            return $this->sendError('Validation Error.', $validator->errors(),422);      
        }

        $f_members  = RelatedLink::select('id','link_name_en','slug')->where('ac_non_ac_id',request()->ac_non_ac_id)->where('link_name_en', 'LIKE', 'Faculty Members')->first();
           
        if (is_null($f_members)){                        
            return $this->sendResponse($f_members, trans('en_lang.DATA_NOT_FOUND'),404);
        }
        return $this->sendResponse($f_members, trans('en_lang.DATA_FOUND'),200);
    }    

    
}
