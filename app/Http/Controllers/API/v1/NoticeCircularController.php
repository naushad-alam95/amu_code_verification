<?php

namespace App\Http\Controllers\API\v1;

use App\Http\Controllers\API\v1\BaseController as BaseController;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\NoticeCircular;
use Validator;
use Auth;

class NoticeCircularController extends BaseController
{

    public function homeNoticeCircular(Request $request) {
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
                        $widget = Widget::select('id','title_ur as title','description_ur as description','slug','status','updated_at')->orderBy('order_on','asc')->whereIN('id', [6, 8, 9,10])->get();
                        foreach ($widgets as $widget) {
                            $dataUR['id'] = $widget->id;
                            if($widget->title_ur == NULL){
                                $dataUR['title'] = $widget->title;  
                            }else{
                                $dataUR['title'] = $widget->title_ur; 
                            }
                            if($widget->description_ur == NULL){
                                $dataUR['description'] = $widget->description;  
                            }else{
                                $dataUR['description'] = $widget->description_ur;
                            }
                            $dataUR['slug'] = $widget->slug;
                            $dataUR['url'] = $widget->url;
                        }
                        if ($dataUR){                        
                            return $this->sendResponse($dataUR, trans($locale.'_lang.DATA_FOUND'),200);
                        } else {
                            return $this->sendResponse(trans($locale.'_lang.DATA_NOT_FOUND'),404);
                        }
                        break;
                    case "hi": //GET widget IN Hindi
                        $dataHI = array();
                        $widgets = Widget::select('id','title_hi as title','description_hi as description','slug','status','updated_at')->orderBy('order_on','asc')->whereIN('id', [6, 8, 9,10])->get();
                        foreach ($widgets as $widget) {
                            $dataHI['id'] = $widget->id;
                            if($widget->title_hi == NULL){
                                $dataHI['title'] = $widget->title;  
                            }else{
                                $dataHI['title'] = $widget->title_hi; 
                            }
                            if($widget->description_hi == NULL){
                                $dataHI['description'] = $widget->description;  
                            }else{
                                $dataHI['description'] = $widget->description_hi;
                            }
                            $dataHI['slug'] = $widget->slug;
                            $dataHI['url'] = $widget->url;
                        }
                        if ($dataHI){                        
                            return $this->sendResponse($dataHI, trans($locale.'_lang.DATA_FOUND'),200);
                        } else {
                            return $this->sendResponse(trans($locale.'_lang.DATA_NOT_FOUND'),404);
                        }
                        break;
                    default: //GET widget IN English
                        $widget = Widget::select('id','title_en as title','description_en as description','slug','status','updated_at')->orderBy('order_on','asc')->whereIN('id', [6, 8, 9,10])->get();
                        if (!$widget->isEmpty()){                        
                            return $this->sendResponse($widget, trans($locale.'_lang.DATA_FOUND'),200);
                        } else {
                            return $this->sendResponse($widget, trans($locale.'_lang.DATA_NOT_FOUND'),404);
                        }
                        break;
                }
            }catch (Exception $e) {
               return $this->sendError(trans($locale.'_lang.MODEL_NOT_FOUND'), $e->getMessage(), 404);
            }
        }
    }

    /*
    *Get Recent Notice and circular list
    */
    public function recentNotice(Request $request) {
        $validator = Validator::make($request->all(), [
            'lang' => 'required|in:en,hi,ur',
        ]);
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
                $curDateTime = date("Y-m-d H:i:s");
                $days_ago = date('Y-m-d', strtotime('-30 days', strtotime($curDateTime)));
                switch ($locale) {
                    case "ur": //GET Menu IN Urdu
                        $dataUR = array();
                        $dataUR = NoticeCircular::select('id','title_en','title_ur','file','approval_status','created_at')/*->where('approval_status','Approved')*//*->where('created_at','>',$days_ago)*/->orderBy('created_at','desc')->when(request()->search, function ($query) {
                                $query->where('title_en', 'LIKE', '%'.request()->search.'%');
                            })->paginate(env('ITEM_PER_PAGE'));

                        if (empty($dataUR)){                        
                            return $this->sendResponse($dataUR, trans($locale.'_lang.DATA_NOT_FOUND'),404);
                        }
                        foreach ($dataUR as $key => $value) {
                                
                                if($value->title_ur == NULL){
                                    $value['title'] = $value->title_en; 
                                }else{ 
                                    $value['title'] = $value->title_ur; 
                                }
                            }
                       
                        if ($dataUR){                        
                            return $this->sendResponse($dataUR, trans($locale.'_lang.DATA_FOUND'),200);
                        } else {
                            return $this->sendResponse($dataUR, trans($locale.'_lang.DATA_NOT_FOUND'),404);
                        }
                        break;
                    case "hi": //GET Menu In Hindi
                        $dataHI = array();
                        $dataHI = NoticeCircular::select('id','title_en','title_hi','file','approval_status','created_at')/*->where('approval_status','Approved')*//*->where('created_at','>',$days_ago)*/->orderBy('created_at','desc')->when(request()->search, function ($query) {
                                $query->where('title_en', 'LIKE', '%'.request()->search.'%');
                            })->paginate(env('ITEM_PER_PAGE'));

                        if (empty($dataHI)){                        
                            return $this->sendResponse($dataHI, trans($locale.'_lang.DATA_NOT_FOUND'),404);
                        }
                        foreach ($dataHI as $key => $value) {
                                
                                if($value->title_hi == NULL){
                                    $value['title'] = $value->title_en; 
                                }else{ 
                                    $value['title'] = $value->title_hi; 
                                }
                            }
                        if ($dataHI){                        
                            return $this->sendResponse($dataHI, trans($locale.'_lang.DATA_FOUND'),200);
                        } else {
                            return $this->sendResponse($dataHI, trans($locale.'_lang.DATA_NOT_FOUND'),404);
                        }
                        
                        break;
                    default: //GET slider IN English
                    
                    $recentNotice = NoticeCircular::select('id','title_en as title','file','approval_status','created_at')/*->where('approval_status','Approved')*//*->where('created_at','>',$days_ago)*/->orderBy('created_at','desc')
                        ->when(request()->search, function ($query) {
                            $query->where('title_en', 'LIKE', '%'.request()->search.'%');
                        })->paginate(env('ITEM_PER_PAGE'));

                    if ($recentNotice){                        
                        return $this->sendResponse($recentNotice, trans($locale.'_lang.DATA_FOUND'),200);
                    } else {
                        return $this->sendResponse($recentNotice, trans($locale.'_lang.DATA_NOT_FOUND'),404);
                    }
                    break;
                }
            } catch (Exception $e) {
               return $this->sendError(trans($locale.'_lang.MODEL_NOT_FOUND'), $e->getMessage(), 404);
            }
        }
    }
}
