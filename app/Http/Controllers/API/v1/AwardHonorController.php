<?php

namespace App\Http\Controllers\API\v1;

use App\Http\Controllers\API\v1\BaseController as BaseController;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\AwardHonor;
use Validator;
use Auth;

class AwardHonorController extends BaseController
{
    //Get Award and Honor for Home Page
    public function homeAwardHonor(Request $request) {
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
                        $awardHonor = AwardHonor::where('featured','1')->orderBy('updated_at','DESC')->first();
                        if (empty($awardHonor)){                        
                            return $this->sendResponse($awardHonor, trans($locale.'_lang.DATA_NOT_FOUND'),404);
                        }
                        $dataUR['id'] = $awardHonor->id;
                        if($awardHonor->title_ur == NULL){
                            $dataUR['title'] = $awardHonor->title_en;  
                        }else{
                            $dataUR['title'] = $awardHonor->title_ur; 
                        }
                        if($awardHonor->description_ur == NULL){
                            $dataUR['description'] = $awardHonor->description_en;  
                        }else{
                            $dataUR['description'] = $awardHonor->description_ur;
                        }
                        $dataUR['slug'] = $awardHonor->slug;
                        $dataUR['image'] = $awardHonor->image;
                        if ($dataUR){                        
                            return $this->sendResponse($dataUR, trans($locale.'_lang.DATA_FOUND'),200);
                        } else {
                            return $this->sendResponse($dataUR, trans($locale.'_lang.DATA_NOT_FOUND'),404);
                        }
                        break;
                    case "hi": //GET widget IN Hindi
                        $dataHI = array();
                        $awardHonor = AwardHonor::where('featured','1')->orderBy('updated_at','DESC')->first();
                        if (empty($awardHonor)){                        
                            return $this->sendResponse($awardHonor, trans($locale.'_lang.DATA_NOT_FOUND'),404);
                        }
                        $dataHI['id'] = $awardHonor->id;
                        if($awardHonor->title_ur == NULL){
                            $dataHI['title'] = $awardHonor->title_en;  
                        }else{
                            $dataHI['title'] = $awardHonor->title_hi; 
                        }
                        if($awardHonor->description_ur == NULL){
                            $dataHI['description'] = $awardHonor->description_en;  
                        }else{
                            $dataHI['description'] = $awardHonor->description_hi;
                        } 
                        $dataHI['slug'] = $awardHonor->slug;
                        $dataHI['image'] = $awardHonor->image;
                        if ($dataHI){                        
                            return $this->sendResponse($dataHI, trans($locale.'_lang.DATA_FOUND'),200);
                        } else {
                            return $this->sendResponse($dataHI, trans($locale.'_lang.DATA_NOT_FOUND'),404);
                        }
                        break;
                    default: //GET widget IN English
                        $data = array();
                        $awardHonor = AwardHonor::where('featured','1')->orderBy('updated_at','DESC')->first();
                        if (empty($awardHonor)){                        
                            return $this->sendResponse($awardHonor, trans($locale.'_lang.DATA_NOT_FOUND'),404);
                        }
                        $data['id'] = $awardHonor->id; 
                        $data['title'] = $awardHonor->title_en;  
                        $data['description'] = $awardHonor->description_en;  
                        $data['slug'] = $awardHonor->slug;
                        $data['image'] = $awardHonor->image;
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

    //Get Award and Honor for list page
    public function awardHonor(Request $request) {
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
                        $awardHonor = AwardHonor::where('status','1')->orderBy('updated_at','DESC')->get();
                        if (empty($awardHonor)){                        
                            return $this->sendResponse($awardHonor, trans($locale.'_lang.DATA_NOT_FOUND'),404);
                        }
                        foreach ($awardHonor as $key => $value) {
                            $dataUR[$key]['id'] = $value->id;
                            if($value->title_ur == NULL){
                                $dataUR[$key]['title'] = $value->title_en;  
                            }else{
                                $dataUR[$key]['title'] = $value->title_ur; 
                            }
                            if($value->description_ur == NULL){
                                $dataUR[$key]['description'] = $value->description_en;  
                            }else{
                                $dataUR[$key]['description'] = $value->description_ur;
                            }
                            $dataUR[$key]['slug'] = $value->slug;
                            $dataUR[$key]['image'] = $value->image;
                        }
                        
                        if ($dataUR){                        
                            return $this->sendResponse($dataUR, trans($locale.'_lang.DATA_FOUND'),200);
                        } else {
                            return $this->sendResponse($dataUR, trans($locale.'_lang.DATA_NOT_FOUND'),404);
                        }
                        break;
                    case "hi": //GET widget IN Hindi
                        $dataHI = array();
                        $awardHonor = AwardHonor::where('status','1')->orderBy('updated_at','DESC')->get();
                        if (empty($awardHonor)){                        
                            return $this->sendResponse($awardHonor, trans($locale.'_lang.DATA_NOT_FOUND'),404);
                        }
                        foreach ($awardHonor as $key => $value) {
                            $dataHI[$key]['id'] = $value->id;
                            if($value->title_hi == NULL){
                                $dataHI[$key]['title'] = $value->title_en;  
                            }else{
                                $dataHI[$key]['title'] = $value->title_hi; 
                            }
                            if($value->description_hi == NULL){
                                $dataHI[$key]['description'] = $value->description_en;  
                            }else{
                                $dataHI[$key]['description'] = $value->description_hi;
                            } 
                            $dataHI[$key]['slug'] = $value->slug;
                            $dataHI[$key]['image'] = $value->image;
                        }
                        
                        if ($dataHI){                        
                            return $this->sendResponse($dataHI, trans($locale.'_lang.DATA_FOUND'),200);
                        } else {
                            return $this->sendResponse($dataHI, trans($locale.'_lang.DATA_NOT_FOUND'),404);
                        }
                        break;
                    default: //GET widget IN English
                        $data = array();
                        $awardHonor = AwardHonor::where('status','1')->orderBy('updated_at','DESC')->get();
                        if (empty($awardHonor)){                        
                            return $this->sendResponse($awardHonor, trans($locale.'_lang.DATA_NOT_FOUND'),404);
                        }
                        foreach ($awardHonor as $key => $value) {
                            $data[$key]['id']            = $value->id; 
                            $data[$key]['title']         = $value->title_en;  
                            $data[$key]['description']   = $value->description_en;  
                            $data[$key]['slug']          = $value->slug;
                            $data[$key]['image']         = $value->image;
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
    
}
