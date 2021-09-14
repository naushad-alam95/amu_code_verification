<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\API\v1\BaseController as BaseController;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Harimayco\Menu\Facades\Menu;
use App\Directory;
use App\Widget;
use Validator;
use Auth;
use DB;

class DirectoryController extends BaseController
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
                $directory = Directory::where('status','1')->orderBy('order_on','asc')->get();
               
                switch ($locale) {
                    case "ur": //GET Annual Report IN Urdu
                        $dataUR = array();
                        if (empty($directory)){                        
                            return $this->sendResponse($directory, trans($locale.'_lang.DATA_NOT_FOUND'),404);
                        }
                        foreach ($directory as $key => $dir) {
                            
                            $dataUR[$key]['id'] = $dir->id;
                            if($dir->title_ur == NULL){
                               $dataUR[$key]['title'] = $dir->title_en;  
                            }else{
                               $dataUR[$key]['title'] = $dir->title_ur; 
                            }
                            $dataUR[$key]['slug'] = $dir->slug;                            
                        }
                        if ($dataUR){                        
                            return $this->sendResponse($dataUR, trans($locale.'_lang.DATA_FOUND'),200);
                        } else {
                            return $this->sendResponse($dataUR, trans($locale.'_lang.DATA_NOT_FOUND'),404);
                        }
                        break;
                    case "hi": //GET Annual Report IN Hindi
                        $dataHi = array();
                        if (empty($directory)){                        
                            return $this->sendResponse($directory, trans($locale.'_lang.DATA_NOT_FOUND'),404);
                        }
                        foreach ($directory as $key => $dir) {                            
                            $dataHi[$key]['id'] = $dir->id;
                            if($dir->title_hi == NULL){
                               $dataHi[$key]['title'] = $dir->title_en;  
                            }else{
                                $dataHi[$key]['title'] = $dir->title_hi; 
                            }
                            $dataHi[$key]['slug'] = $dir->slug;
                        }
                        if ($dataHi){                        
                            return $this->sendResponse($dataHi, trans($locale.'_lang.DATA_FOUND'),200);
                        } else {
                            return $this->sendResponse($dataHi, trans($locale.'_lang.DATA_NOT_FOUND'),404);
                        }
                        break;
                    default: //GET Annual Report IN English
                        $data = array();
                        if (empty($directory)){                        
                            return $this->sendResponse($directory, trans($locale.'_lang.DATA_NOT_FOUND'),404);
                        }
                        foreach ($directory as $key => $dir) {
                            $data[$key]['id'] = $dir->id;
                            $data[$key]['title'] = $dir->title_en; 
                            $data[$key]['slug'] = $dir->slug;                            
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

    public function directoryDetail(Request $request) {
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
                        $dataUR =array();                   
                        $directory = Directory::where('slug',$request->slug)->first();
                        if (empty($directory)){                        
                            return $this->sendResponse($news, trans($locale.'_lang.DATA_NOT_FOUND'),404);
                        }
                        $dataUR['id'] = $directory->id;
                        if($directory->title_ur == NULL){
                            $dataUR['title'] = $directory->title_en; 
                        }else{ 
                            $dataUR['title'] = $directory->title_ur; 
                        }
                        if($directory->description_ur == NULL || $directory->description_ur == ''){
                            $dataUR['description'] = $directory->description_en;
                        }else{
                            $dataUR['description'] = $directory->description_ur;
                        }
                        $dataUR['slug'] = $directory->slug; 
                        $dataUR['update_at'] = $directory->update_at;                       
                        if ($dataUR){                        
                            return $this->sendResponse($dataUR, trans($locale.'_lang.DATA_FOUND'),200);
                        } else {
                            return $this->sendResponse($dataUR, trans($locale.'_lang.DATA_NOT_FOUND'),404);
                        }
                        break;
                    case "hi": //GET Menu In Hindi
                        $dataHI =array();                  
                        $directory = Directory::where('slug',$request->slug)->first();
                        if (empty($directory)){                        
                            return $this->sendResponse($directory, trans($locale.'_lang.DATA_NOT_FOUND'),404);
                        }
                        $dataHI['id'] = $directory->id;
                        if($directory->title_ur == NULL){
                            $dataHI['title'] = $directory->title_en; 
                        }else{ 
                            $dataHI['title'] = $directory->title_hi; 
                        }
                        if($directory->description_hi == NULL){
                            $dataHI['description'] = $directory->description_en;
                        }else{
                            $dataHI['description'] = $directory->description_hi;
                        }
                        $dataHI['slug'] = $directory->slug;
                        $dataHI['update_at'] = $directory->update_at;                         
                        if ($dataHI){                        
                            return $this->sendResponse($dataHI, trans($locale.'_lang.DATA_FOUND'),200);
                        } else {
                            return $this->sendResponse($dataHI, trans($locale.'_lang.DATA_NOT_FOUND'),404);
                        }
                        
                        break;
                    default: //GET slider IN English
                        $data =array();                    
                        $directory = Directory::where('slug',$request->slug)->first();
                        if (empty($directory)){                        
                            return $this->sendResponse($directory, trans($locale.'_lang.DATA_NOT_FOUND'),404);
                        }
                        $data['id'] = $directory->id;
                        $data['title'] = $directory->title_en;  
                        $data['description'] = $directory->description_en;
                        $data['slug'] = $directory->slug;
                        $data['update_at'] = $directory->update_at;                        
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
