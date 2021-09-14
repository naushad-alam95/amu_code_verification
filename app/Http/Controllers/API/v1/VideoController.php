<?php

namespace App\Http\Controllers\API\v1;

use App\Http\Controllers\API\v1\BaseController as BaseController;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Video;
use Validator;
use Auth;

class VideoController extends BaseController
{
    //Get Video for Home Page and List Page
    public function homeVideos(Request $request) {
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
                        $videos = Video::orderBy('order_on','ASC')->orderBy('created_at','DESC')
                                ->take(1)->get();
                        if (empty($videos)){                        
                            return $this->sendResponse($videos, trans($locale.'_lang.DATA_NOT_FOUND'),404);
                        }
                        foreach ($videos as $key => $video) {
                            $dataUR[$key]['id'] = $video->id;
                            if($video->heading_ur == NULL){
                                $dataUR[$key]['heading'] = $video->heading;  
                            }else{
                                $dataUR[$key]['heading'] = $video->heading_ur; 
                            }
                            if($video->sub_heading_ur == NULL){
                                $dataUR[$key]['sub_heading'] = $video->sub_heading;  
                            }else{
                                $dataUR[$key]['sub_heading'] = $video->sub_heading_ur;
                            }
                            $dataUR[$key]['image'] = $video->image;
                            $dataUR[$key]['url'] = $video->url;
                        }
                        if ($dataUR){                        
                            return $this->sendResponse($dataUR, trans($locale.'_lang.DATA_FOUND'),200);
                        } else {
                            return $this->sendResponse($dataUR, trans($locale.'_lang.DATA_NOT_FOUND'),404);
                        }
                        break;
                    case "hi": //GET widget IN Hindi
                        $dataHI = array();
                        $videos = Video::orderBy('order_on','ASC')->orderBy('created_at','DESC')
                                ->take(1)->get();
                        if (empty($videos)){                        
                            return $this->sendResponse($videos, trans($locale.'_lang.DATA_NOT_FOUND'),404);
                        }
                        foreach ($videos as $key => $video) {
                            $dataHI[$key]['id'] = $video->id;
                            if($video->heading_hi == NULL){
                                $dataHI[$key]['heading'] = $video->heading;  
                            }else{
                                $dataHI[$key]['heading'] = $video->heading_hi; 
                            }
                            if($video->sub_heading_hi == NULL){
                                $dataHI[$key]['sub_heading'] = $video->sub_heading;  
                            }else{
                                $dataHI[$key]['sub_heading'] = $video->sub_heading_hi;
                            }
                            $dataHI[$key]['image'] = $video->image;
                            $dataHI[$key]['url'] = $video->url;
                        }
                        if ($dataHI){                        
                            return $this->sendResponse($dataHI, trans($locale.'_lang.DATA_FOUND'),200);
                        } else {
                            return $this->sendResponse($dataHI, trans($locale.'_lang.DATA_NOT_FOUND'),404);
                        }
                        break;
                    default: //GET widget IN English
                        $data = array();
                        $videos = Video::orderBy('order_on','ASC')->orderBy('created_at','DESC')
                                ->take(1)->get();
                        if (empty($videos)){                        
                            return $this->sendResponse($videos, trans($locale.'_lang.DATA_NOT_FOUND'),404);
                        }
                        foreach ($videos as $key => $video) {
                            $data[$key]['id'] = $video->id;
                            $data[$key]['heading'] = $video->heading;  
                            $data[$key]['sub_heading'] = $video->sub_heading;  
                            $data[$key]['image'] = $video->image;
                            $data[$key]['url'] = $video->url;
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

    //Get Video for List Page and List Page
    public function listVideos(Request $request) {
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
                        $videos = Video::select('id','heading','description_en','description_ur','image','url')->orderBy('order_on','ASC')->orderBy('created_at','DESC')
                                ->where('status','1')->paginate(5);
                        if (empty($videos)){                        
                            return $this->sendResponse($videos, trans($locale.'_lang.DATA_NOT_FOUND'),404);
                        }
                        foreach ($videos as $key => $video) {
                            if($video->heading_ur == NULL){
                                $video['heading'] = $video->heading;  
                            }else{
                                $video['heading'] = $video->heading_ur; 
                            }
                            if($video->description_ur == NULL){
                                $video['description'] = $video->description_en;  
                            }else{
                                $video['description'] = $video->description_ur;
                            }
                        }
                        if ($videos){                        
                            return $this->sendResponse($videos, trans($locale.'_lang.DATA_FOUND'),200);
                        } else {
                            return $this->sendResponse($dataUR, trans($locale.'_lang.DATA_NOT_FOUND'),404);
                        }
                        break;
                    case "hi": //GET widget IN Hindi
                        $dataHI = array();
                        $videos = Video::select('id','heading','description_en','description_hi','image','url')->orderBy('order_on','ASC')->orderBy('created_at','DESC')
                                ->where('status','1')->paginate(5);
                        if (empty($videos)){                        
                            return $this->sendResponse($videos, trans($locale.'_lang.DATA_NOT_FOUND'),404);
                        }
                        foreach ($videos as $key => $video) {
                           
                            if($video->heading_hi == NULL){
                                $video['heading'] = $video->heading;  
                            }else{
                                $video['heading'] = $video->heading_hi; 
                            }
                            if($video->description_hi == NULL){
                                $video['description'] = $video->description_en;  
                            }else{
                                $video['description'] = $video->description_hi;
                            }
                        }
                        if ($videos){                        
                            return $this->sendResponse($videos, trans($locale.'_lang.DATA_FOUND'),200);
                        } else {
                            return $this->sendResponse($dataHI, trans($locale.'_lang.DATA_NOT_FOUND'),404);
                        }
                        break;
                    default: //GET widget IN English
                        $data = array();
                        $videos = Video::select('id','heading','description_en as description','image','url')->orderBy('order_on','ASC')->orderBy('created_at','DESC')->where('status','1')->paginate(5);
                        if (empty($videos)){                        
                            return $this->sendResponse($videos, trans($locale.'_lang.DATA_NOT_FOUND'),404);
                        }
                        
                        if ($videos){                        
                            return $this->sendResponse($videos, trans($locale.'_lang.DATA_FOUND'),200);
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
}
