<?php

namespace App\Http\Controllers\API\v1;

use App\Http\Controllers\API\v1\BaseController as BaseController;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\GalleryImages;
use App\Gallery;
use Validator;
use Auth;

class ImageController extends BaseController
{
    //Get Image Gallery for Home Page
    public function homeImageGallery(Request $request) {
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
                        $images = GalleryImages::where('featured','1')->orderBy('created_at','DESC')->take(10)->get();
                        if (empty($images)){                        
                            return $this->sendResponse($images, trans($locale.'_lang.DATA_NOT_FOUND'),404);
                        }
                        foreach ($images as $key => $image) {
                            $arr = explode("/",$image->image);
                            array_push($arr,'thumb/'.array_pop($arr));
                            $thumb = implode("/",$arr);
                            $dataUR[$key]['thumb'] = $thumb;
                            $dataUR[$key]['image'] = $image->image;                      
                        }
                        if ($dataUR){                        
                            return $this->sendResponse($dataUR, trans($locale.'_lang.DATA_FOUND'),200);
                        } else {
                            return $this->sendResponse($dataUR, trans($locale.'_lang.DATA_NOT_FOUND'),404);
                        }
                        break;
                    case "hi": //GET widget IN Hindi
                        $dataHI = array();
                        $images = GalleryImages::where('featured','1')->orderBy('created_at','DESC')->take(10)->get();
                        if (empty($images)){                        
                            return $this->sendResponse($images, trans($locale.'_lang.DATA_NOT_FOUND'),404);
                        }
                        foreach ($images as $key => $image) {
                            $arr = explode("/",$image->image);
                            array_push($arr,'thumb/'.array_pop($arr));
                            $thumb = implode("/",$arr);
                            $dataHI[$key]['thumb'] = $thumb;
                            $dataHI[$key]['image'] = $image->image;
                        }
                        if ($dataHI){                        
                            return $this->sendResponse($dataHI, trans($locale.'_lang.DATA_FOUND'),200);
                        } else {
                            return $this->sendResponse($dataHI, trans($locale.'_lang.DATA_NOT_FOUND'),404);
                        }
                        break;
                    default: //GET widget IN English
                        $data = array();
                        $images = GalleryImages::where('featured','1')->orderBy('created_at','DESC')->take(10)->get();
                        if (empty($images)){                        
                            return $this->sendResponse($images, trans($locale.'_lang.DATA_NOT_FOUND'),404);
                        }
                        foreach ($images as $key => $image) {
                            $arr = explode("/",$image->image);
                            array_push($arr,'thumb/'.array_pop($arr));
                            $thumb = implode("/",$arr);
                            $data[$key]['thumb'] = $thumb;
                            $data[$key]['image'] = $image->image;
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

    //Get All Images Gallery
    public function imageGallery(Request $request) {
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
                        $images = Gallery::where('status','1')
                                ->when(request()->slug, function ($query) {
                                    $query->where('slug',request()->slug);
                                })
                                ->with(['getImages' => function($q){ 
                                  $q->where('status','1'); }])
                                ->orderBy('order_on','ASC')->orderBy('created_at','DESC')->first();
                        if (empty($images)){                        
                            return $this->sendResponse($images, trans($locale.'_lang.DATA_NOT_FOUND'),404);
                        }
                         $img = array();   
                        foreach ($images->getImages as $key => $image) {
                            $arr = explode("/",$image->image);
                            array_push($arr,'thumb_275/'.array_pop($arr));
                            $thumb = implode("/",$arr);
                            
                            $img[$key]['thumb'] = $thumb;
                            $img[$key]['image'] = $image->image;
                        }
                            if($images->title_ur == NULL){
                                $dataUR[$key]['title'] = ucfirst($images->title);  
                            }else{
                                $dataUR[$key]['title'] = ucfirst($images->title_ur);
                            }
                            if($images->description_hi == NULL){
                                $dataUR[$key]['description'] = $images->description_en;  
                            }else{
                                $dataUR[$key]['description'] = $images->description_ur;
                            }
                        $dataUR['images'] = $img;     
                        if ($dataUR){                        
                            return $this->sendResponse($dataUR, trans($locale.'_lang.DATA_FOUND'),200);
                        } else {
                            return $this->sendResponse($dataUR, trans($locale.'_lang.DATA_NOT_FOUND'),404);
                        }
                        break;
                    case "hi": //GET widget IN Hindi
                        $dataHI = array();
                        $images = Gallery::where('status','1')
                                ->when(request()->slug, function ($query) {
                                    $query->where('slug',request()->slug);
                                })
                                ->with(['getImages' => function($q){ 
                                  $q->where('status','1'); }])
                                ->orderBy('order_on','ASC')->orderBy('created_at','DESC')->first();        
                        if (empty($images)){                        
                            return $this->sendResponse($images, trans($locale.'_lang.DATA_NOT_FOUND'),404);
                        }
                        $img = array();    
                        foreach ($images->getImages as $key => $image) {
                            $arr = explode("/",$image->image);
                            array_push($arr,'thumb_275/'.array_pop($arr));
                            $thumb = implode("/",$arr);                            
                            $img[$key]['thumb'] = $thumb;
                            $img[$key]['image'] = $image->image;
                        }
                        if($images->title_hi == NULL){
                            $dataHI['title'] = ucfirst($images->title);  
                        }else{
                            $dataHI['title'] = ucfirst($images->title_hi);
                        }
                        if($images->description_hi == NULL){
                            $dataHI['description'] = $images->description_en;  
                        }else{
                            $dataHI['description'] = $images->description_hi;
                        }
                        $dataHI['images'] = $img; 
                        if ($dataHI){                        
                            return $this->sendResponse($dataHI, trans($locale.'_lang.DATA_FOUND'),200);
                        } else {
                            return $this->sendResponse($dataHI, trans($locale.'_lang.DATA_NOT_FOUND'),404);
                        }
                        break;
                    default: //GET widget IN English
                        $data = array();
                        $images = Gallery::where('status','1')
                                ->when(request()->slug, function ($query) {
                                    $query->where('slug',request()->slug);
                                })
                                ->with(['getImages' => function($q){ 
                                  $q->where('status','1'); }])
                                ->orderBy('order_on','ASC')->orderBy('created_at','DESC')->first();

                        if (empty($images)){                        
                            return $this->sendResponse($images, trans($locale.'_lang.DATA_NOT_FOUND'),404);
                        } 
                        $img = array();                         
                        foreach ($images->getImages as $key => $image) {
                            $arr = explode("/",$image->image);
                            array_push($arr,'thumb_275/'.array_pop($arr));
                            $thumb = implode("/",$arr);                               
                            $img[$key]['thumb'] = $thumb;
                            $img[$key]['image'] = $image->image;
                        }
                        $data['title'] = ucfirst($images->title); 
                        $data['description'] = $images->description_en;
                        $data['images'] = $img; 
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


    public function galleryArchive(Request $request) {
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
                switch ($locale) {
                    case "ur": //GET Menu IN Urdu
                        $archive = array();                             
                        $images = Gallery::where('status','1')->orderBy('order_on','ASC')->get();
                        if (empty($images)){                        
                            return $this->sendResponse($archive, trans($locale.'_lang.DATA_NOT_FOUND'),404);
                        }
                        foreach ($images as $key => $n) {
                            $archive[$key]['id'] = $n->id;
                            if($n->title_ur == NULL){
                                $archive[$key]['title'] = $n->title; 
                            }else{ 
                                $archive[$key]['title'] = $n->title_ur; 
                            }                            
                            $archive[$key]['slug'] = $n->slug;
                            $archive[$key]['status'] = $n->status;
                            $archive[$key]['order_on'] = $n->order_on;
                        }
                        if ($archive){                         
                            return $this->sendResponse($archive, trans($locale.'_lang.DATA_FOUND'),200);
                        } else {
                            return $this->sendResponse($archive, trans($locale.'_lang.DATA_NOT_FOUND'),404);
                        }
                        break;
                    case "hi": //GET Menu In Hindi
                        $images = Gallery::where('status','1')->orderBy('order_on','ASC')->get();
                        if (empty($images)){                        
                            return $this->sendResponse($archive, trans($locale.'_lang.DATA_NOT_FOUND'),404);
                        }
                        foreach ($images as $key => $n) {
                            $archive[$key]['id'] = $n->id;
                            if($n->title_hi == NULL){
                                $archive[$key]['title'] = $n->title; 
                            }else{ 
                                $archive[$key]['title'] = $n->title_hi; 
                            }                            
                            $archive[$key]['slug'] = $n->slug;
                            $archive[$key]['status'] = $n->status;
                            $archive[$key]['order_on'] = $n->order_on;
                        }
                        if ($archive){                        
                            return $this->sendResponse($archive, trans($locale.'_lang.DATA_FOUND'),200);
                        } else {
                            return $this->sendResponse($archive, trans($locale.'_lang.DATA_NOT_FOUND'),404);
                        }
                        
                        break;
                    default: //GET slider IN English
                        $archive = Gallery::select('id','title','slug','order_on','status')->where('status',1)->orderBy('order_on','ASC')->get();
                        if ($archive){                        
                            return $this->sendResponse($archive, trans($locale.'_lang.DATA_FOUND'),200);
                        } else {
                            return $this->sendResponse($archive, trans($locale.'_lang.DATA_NOT_FOUND'),404);
                        }
                        break;
                }
            } catch (Exception $e) {
               return $this->sendError(trans($locale.'_lang.MODEL_NOT_FOUND'), $e->getMessage(), 404);
            }
        }
    }


    
}
