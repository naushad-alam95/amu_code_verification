<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\API\v1\BaseController as BaseController;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\FlipBook;
use App\Widget;
use Validator;
use Auth;
use DB;

class FlipBookController extends BaseController
{

    public function index(Request $request) {
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
                $FlipBook = FlipBook::where('status','1')->orderBy('order_on','asc')->get();
                
                switch ($locale) {
                    case "ur": //GET FlipBook IN Urdu
                        $dataUR = array();
                        if (empty($FlipBook)){                        
                            return $this->sendResponse($FlipBook, trans($locale.'_lang.DATA_NOT_FOUND'),404);
                        }                      
                       
                        foreach ($FlipBook as $key => $dir) {
                            
                            $dataUR[$key]['id'] = $dir->id;
                            if($dir->title_ur == NULL){
                               $dataUR[$key]['title'] = $dir->title_en;  
                            }else{
                                $dataUR[$key]['title'] = $dir->title_ur; 
                            }
                            if($dir->author_ur == NULL){
                                $dataUR[$key]['author'] = $dir->author_en;  
                             }else{
                                 $dataUR[$key]['author'] = $dir->author_ur; 
                             }
                            $dataUR[$key]['slug'] = $dir->slug;
                            $dataUR[$key]['thumb'] = $dir->thumb;  
                            $dataUR[$key]['file'] = $dir->file;
                        }
                        if ($dataUR){                        
                            return $this->sendResponse($dataUR, trans($locale.'_lang.DATA_FOUND'),200);
                        } else {
                            return $this->sendResponse($dataUR, trans($locale.'_lang.DATA_NOT_FOUND'),404);
                        }
                        break;
                    case "hi": //GET FlipBook IN Hindi
                        $dataHi = array();
                        if (empty($FlipBook)){                        
                            return $this->sendResponse($FlipBook, trans($locale.'_lang.DATA_NOT_FOUND'),404);
                        }                      
                       
                        foreach ($FlipBook as $key => $dir) {
                            
                            $dataHi[$key]['id'] = $dir->id;
                            if($dir->title_hi == NULL){
                               $dataHi[$key]['title'] = $dir->title_en;  
                            }else{
                                $dataHi[$key]['title'] = $dir->title_hi; 
                            }
                            if($dir->author_hi == NULL){
                                $dataHi[$key]['author'] = $dir->author_en;  
                             }else{
                                 $dataHi[$key]['author'] = $dir->author_hi; 
                             }
                            $dataHi[$key]['slug'] = $dir->slug;
                            $dataHi[$key]['thumb'] = $dir->thumb;  
                            $dataHi[$key]['file'] = $dir->file;
                            
                            
                        }
                        if ($dataHi){                        
                            return $this->sendResponse($dataHi, trans($locale.'_lang.DATA_FOUND'),200);
                        } else {
                            return $this->sendResponse($dataHi, trans($locale.'_lang.DATA_NOT_FOUND'),404);
                        }
                        break;
                    default: //GET FlipBook IN English
                        $data = array();
                        if (empty($FlipBook)){                        
                            return $this->sendResponse($FlipBook, trans($locale.'_lang.DATA_NOT_FOUND'),404);
                        }                       
                       
                        foreach ($FlipBook as $key => $dir) {
                            
                            $data[$key]['id'] = $dir->id;
                            $data[$key]['title'] = $dir->title_en;
                            $data[$key]['author'] = $dir->author_en;
                            $data[$key]['slug'] = $dir->slug;  
                            $data[$key]['thumb'] = $dir->thumb;  
                            $data[$key]['file'] = $dir->file;
                            
                            
                        }
                        if ($data){                        
                            return $this->sendResponse($data, trans($locale.'_lang.DATA_FOUND'),200);
                        } else {
                            return $this->sendResponse($data, trans($locale.'_lang.DATA_NOT_FOUND'),404);
                        }
                        break;
                }
            } catch (Exception $e) {
               return $this->sendError(trans($locale.'_lang.MODEL_NOT_FOUND'), $e->getMessage(), 404);
            }
        }
    }

    public function detailFlipBook(Request $request) {
        $validator = Validator::make($request->all(), [
            'lang' => 'required|in:en,hi,ur',
            'slug' => 'required',
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
                $FlipBook = FlipBook::where('slug',$request->slug)->where('status','1')->first();
                
                switch ($locale) {
                    case "ur": //GET FlipBook IN Urdu
                        $dataUR = array();
                        if (empty($FlipBook)){                        
                            return $this->sendResponse($FlipBook, trans($locale.'_lang.DATA_NOT_FOUND'),404);
                        } 
                            
                            $dataUR['id'] = $FlipBook->id;
                            if($FlipBook->title_ur == NULL){
                               $dataUR['title'] = $FlipBook->title_en;  
                            }else{
                                $dataUR['title'] = $FlipBook->title_ur; 
                            }
                            if($FlipBook->author_ur == NULL){
                                $dataUR['author'] = $FlipBook->author_en;  
                             }else{
                                 $dataUR['author'] = $FlipBook->author_ur; 
                             }
                            $dataUR['slug'] = $FlipBook->slug;
                            $dataUR['thumb'] = $FlipBook->thumb; 
                            $dataUR['file'] = $FlipBook->file;
                        if ($dataUR){                        
                            return $this->sendResponse($dataUR, trans($locale.'_lang.DATA_FOUND'),200);
                        } else {
                            return $this->sendResponse($dataUR, trans($locale.'_lang.DATA_NOT_FOUND'),404);
                        }
                        break;
                    case "hi": //GET FlipBook IN Hindi
                        $dataHi = array();
                        if (empty($FlipBook)){                        
                            return $this->sendResponse($FlipBook, trans($locale.'_lang.DATA_NOT_FOUND'),404);
                        }                      
                       
                            $dataHi['id'] = $FlipBook->id;
                            if($FlipBook->title_hi == NULL){
                               $dataHi['title'] = $FlipBook->title_en;  
                            }else{
                                $dataHi['title'] = $FlipBook->title_hi; 
                            }
                            if($FlipBook->author_hi == NULL){
                                $dataHi['author'] = $FlipBook->author_en;  
                             }else{
                                 $dataHi['author'] = $FlipBook->author_hi; 
                             }
                            $dataHi['slug'] = $FlipBook->slug;
                            $dataHi['thumb'] = $FlipBook->thumb; 
                            $dataHi['file'] = $FlipBook->file;
                        if ($dataHi){                        
                            return $this->sendResponse($dataHi, trans($locale.'_lang.DATA_FOUND'),200);
                        } else {
                            return $this->sendResponse($dataHi, trans($locale.'_lang.DATA_NOT_FOUND'),404);
                        }
                        break;
                    default: //GET FlipBook IN English
                        $data = array();
                        if (empty($FlipBook)){                        
                            return $this->sendResponse($FlipBook, trans($locale.'_lang.DATA_NOT_FOUND'),404);
                        }
                            
                            $data['id'] = $FlipBook->id;
                            $data['title'] = $FlipBook->title_en;
                            $data['author'] = $FlipBook->author_en;
                            $data['slug'] = $FlipBook->slug; 
                            $data['thumb'] = $FlipBook->thumb; 
                            $data['file'] = $FlipBook->file;

                        if ($data){                        
                            return $this->sendResponse($data, trans($locale.'_lang.DATA_FOUND'),200);
                        } else {
                            return $this->sendResponse($data, trans($locale.'_lang.DATA_NOT_FOUND'),404);
                        }
                        break;
                }
            } catch (Exception $e) {
               return $this->sendError(trans($locale.'_lang.MODEL_NOT_FOUND'), $e->getMessage(), 404);
            }
        }
    }
}
