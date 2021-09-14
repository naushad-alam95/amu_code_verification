<?php

namespace App\Http\Controllers\API\v1;

use App\Http\Controllers\API\v1\BaseController as BaseController;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Notification;
use Validator;
use Auth;

class NotificationController extends BaseController
{

    public function homeNotification(Request $request) {
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
                        $notifications = Notification::whereNull('ac_non_ac_id')->orderBy('updated_at','desc')->where('featured','0')->where('status','1')->latest()->take(10)->get();
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
                            if($notification->description_ur == NULL){
                                $dataUR[$key]['description'] = $notification->description_en;  
                            }else{
                                $dataUR[$key]['description'] = $notification->description_ur;
                            }
                            $dataUR[$key]['slug'] = $notification->slug;
                            $dataUR[$key]['file'] = $notification->file;
                            $dataUR[$key]['hyperlink'] = $notification->hyperlink;
                            $dataUR[$key]['date'] =  date("M d, Y", strtotime($notification->created_at));
                            
                        }
                        if ($dataUR){                        
                            return $this->sendResponse($dataUR, trans($locale.'_lang.DATA_FOUND'),200);
                        } else {
                            return $this->sendResponse($dataUR, trans($locale.'_lang.DATA_NOT_FOUND'),404);
                        }
                        break;
                    case "hi": //GET widget IN Hindi
                        $dataHI = array();
                        $notifications = Notification::whereNull('ac_non_ac_id')->orderBy('updated_at','desc')->where('featured','0')->where('status','1')->latest()->take(10)->get();
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
                            if($notification->description_hi == NULL){
                                $dataHI[$key]['description'] = $notification->description_en;  
                            }else{
                                $dataHI[$key]['description'] = $notification->description_hi;
                            }
                            $dataHI[$key]['slug'] = $notification->slug;
                            $dataHI[$key]['file'] = $notification->file;
                            $dataHI[$key]['hyperlink'] = $notification->hyperlink;
                            $dataHI[$key]['date'] =   date("M d, Y", strtotime($notification->created_at));
                            
                        }
                        if ($dataHI){                        
                            return $this->sendResponse($dataHI, trans($locale.'_lang.DATA_FOUND'),200);
                        } else {
                            return $this->sendResponse($dataHI, trans($locale.'_lang.DATA_NOT_FOUND'),404);
                        }
                        break;
                    default: //GET widget IN English
                        $data = array();
                        $notifications = Notification::whereNull('ac_non_ac_id')->orderBy('updated_at','desc')->where('featured','0')->where('status','1')->latest()->take(10)->get();
                        if (empty($notifications)){                        
                            return $this->sendResponse($notifications, trans($locale.'_lang.DATA_NOT_FOUND'),404);
                        }
                        foreach ($notifications as $key => $notification) {
                            $data[$key]['id'] = $notification->id;
                            $data[$key]['title'] = $notification->title_en;  
                            $data[$key]['description'] = $notification->description_en;
                            $data[$key]['slug'] = $notification->slug;
                            $data[$key]['file'] = $notification->file;
                            $data[$key]['hyperlink'] = $notification->hyperlink;
                            $data[$key]['date'] =  date("M d, Y", strtotime($notification->created_at));
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

    public function homeTicker(Request $request) {
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
                        $notifications = Notification::whereNull('ac_non_ac_id')->orderBy('updated_at','desc')->where('featured','1')->where('status','1')->latest()->take(10)->get();
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
                            if($notification->description_ur == NULL){
                                $dataUR[$key]['description'] = $notification->description_en;  
                            }else{
                                $dataUR[$key]['description'] = $notification->description_ur;
                            }
                            $dataUR[$key]['slug'] = $notification->slug;
                            $dataUR[$key]['file'] = $notification->file;
                            $dataUR[$key]['hyperlink'] = $notification->hyperlink;
                            $dataUR[$key]['date'] =  date("M d, Y", strtotime($notification->created_at));
                            
                        }
                        if ($dataUR){                        
                            return $this->sendResponse($dataUR, trans($locale.'_lang.DATA_FOUND'),200);
                        } else {
                            return $this->sendResponse($dataUR, trans($locale.'_lang.DATA_NOT_FOUND'),404);
                        }
                        break;
                    case "hi": //GET widget IN Hindi
                        $dataHI = array();
                        $notifications = Notification::whereNull('ac_non_ac_id')->orderBy('updated_at','desc')->where('featured','1')->where('status','1')->latest()->take(10)->get();
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
                            if($notification->description_hi == NULL){
                                $dataHI[$key]['description'] = $notification->description_en;  
                            }else{
                                $dataHI[$key]['description'] = $notification->description_hi;
                            }
                            $dataHI[$key]['slug'] = $notification->slug;
                            $dataHI[$key]['file'] = $notification->file;
                            $dataHI[$key]['hyperlink'] = $notification->hyperlink;
                            $dataHI[$key]['date'] =   date("M d, Y", strtotime($notification->created_at));
                            
                        }
                        if ($dataHI){                        
                            return $this->sendResponse($dataHI, trans($locale.'_lang.DATA_FOUND'),200);
                        } else {
                            return $this->sendResponse($dataHI, trans($locale.'_lang.DATA_NOT_FOUND'),404);
                        }
                        break;
                    default: //GET widget IN English
                        $data = array();
                        $notifications = Notification::whereNull('ac_non_ac_id')->orderBy('updated_at','desc')->where('featured','1')->where('status','1')->latest()->take(10)->get();
                        if (empty($notifications)){                        
                            return $this->sendResponse($notifications, trans($locale.'_lang.DATA_NOT_FOUND'),404);
                        }
                        foreach ($notifications as $key => $notification) {
                            $data[$key]['id'] = $notification->id;
                            $data[$key]['title'] = $notification->title_en;  
                            $data[$key]['description'] = $notification->description_en;
                            $data[$key]['slug'] = $notification->slug;
                            $data[$key]['file'] = $notification->file;
                            $data[$key]['hyperlink'] = $notification->hyperlink;
                            $data[$key]['date'] =  date("M d, Y", strtotime($notification->created_at));
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

    public function notificationList(Request $request) {
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
                        $dataUR = array();     
                        if(isset($request->month) && isset($request->year)){
                           $notifications = Notification::whereNull('ac_non_ac_id')->orderBy('updated_at','desc')->where('featured','0')->whereMonth('created_at', $request->month)->whereYear('created_at', $request->year)->where('status','1')->get(); 
                        }else{
                           $notifications = Notification::whereNull('ac_non_ac_id')->orderBy('updated_at','desc')->where('featured','0')->where('status','1')->get();
                        }
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
                            if($notification->description_ur == NULL){
                                $dataUR[$key]['description'] = $notification->description_en;  
                            }else{
                                $dataUR[$key]['description'] = $notification->description_ur;
                            }
                            $dataUR[$key]['slug'] = $notification->slug;
                            $dataUR[$key]['file'] = $notification->file;
                            $dataUR[$key]['hyperlink'] = $notification->hyperlink;
                            $dataUR[$key]['date'] = date("M d, Y", strtotime($notification->created_at));
                            $dataUR[$key]['created_at'] =   $notification->created_at;
                            
                        }
                        if ($dataUR){                        
                            return $this->sendResponse($dataUR, trans($locale.'_lang.DATA_FOUND'),200);
                        } else {
                            return $this->sendResponse($dataUR, trans($locale.'_lang.DATA_NOT_FOUND'),404);
                        }
                        break;
                    case "hi": //GET Menu In Hindi
                        $dataHI = array();
                        if(isset($request->month) && isset($request->year)){
                           $notifications = Notification::whereNull('ac_non_ac_id')->orderBy('updated_at','desc')->where('featured','0')->whereNull('ac_non_ac_id')->orderBy('updated_at','desc')->whereMonth('created_at', $request->month)->whereYear('created_at', $request->year)->where('status','1')->get(); 
                        }else{
                           $notifications = Notification::whereNull('ac_non_ac_id')->orderBy('updated_at','desc')->where('featured','0')->where('status','1')->get();
                        }
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
                            if($notification->description_hi == NULL){
                                $dataHI[$key]['description'] = $notification->description_en;  
                            }else{
                                $dataHI[$key]['description'] = $notification->description_hi;
                            }
                            $dataHI[$key]['slug'] = $notification->slug;
                            $dataHI[$key]['file'] = $notification->file;
                            $dataHI[$key]['hyperlink'] = $notification->hyperlink;
                            $dataHi[$key]['date'] = date("M d, Y", strtotime($notification->created_at));
                            $dataHI[$key]['created_at'] =   $notification->created_at;
                            
                        }
                        if ($dataHI){                        
                            return $this->sendResponse($dataHI, trans($locale.'_lang.DATA_FOUND'),200);
                        } else {
                            return $this->sendResponse($dataHI, trans($locale.'_lang.DATA_NOT_FOUND'),404);
                        }
                        
                        break;
                    default: //GET slider IN English
                        $data =array();
                        if(isset($request->month) && isset($request->year)){
                           $notifications = Notification::whereNull('ac_non_ac_id')->orderBy('updated_at','desc')->where('featured','0')->whereMonth('created_at', $request->month)->whereYear('created_at', $request->year)->where('status','1')->get(); 
                        }else{
                           $notifications = Notification::whereNull('ac_non_ac_id')->orderBy('updated_at','desc')->where('featured','0')->where('status','1')->get();
                        }
                        if (empty($notifications)){                        
                            return $this->sendResponse($notifications, trans($locale.'_lang.DATA_NOT_FOUND'),404);
                        }
                        foreach ($notifications as $key => $notification) {
                            $data[$key]['id'] = $notification->id;
                            $data[$key]['title'] = $notification->title_en;  
                            $data[$key]['description'] = $notification->description_en;
                            $data[$key]['slug'] = $notification->slug;
                            $data[$key]['file'] = $notification->file;
                            $data[$key]['hyperlink'] = $notification->hyperlink;
                            $data[$key]['date'] = date("M d, Y", strtotime($notification->created_at));
                            $data[$key]['created_at'] =   $notification->created_at;
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

    public function notificationArchive(Request $request) {
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
                        $archive =array();
                        $notifications = Notification::whereNull('ac_non_ac_id')->selectRaw('year(created_at) year, count(*) published')->groupBy('year')->orderByRaw('min(created_at) DESC')->where('featured','0')->where('status','1')->get();
                        foreach ($notifications as $key => $n) {
                            $archiveMonth = Notification::whereNull('ac_non_ac_id')->selectRaw('month(created_at) month, count(*) published')->whereYear('created_at',$n->year)->groupBy('month')->orderByRaw('min(created_at) DESC')->where('featured','0')->where('status','1')->get();
                            $archive[$key]['year'] = $n->year;
                            $archive[$key]['mon'] = $archiveMonth;  
                        }
                        if ($archive){                         
                            return $this->sendResponse($archive, trans($locale.'_lang.DATA_FOUND'),200);
                        } else {
                            return $this->sendResponse($archive, trans($locale.'_lang.DATA_NOT_FOUND'),404);
                        }
                        break;
                    case "hi": //GET Menu In Hindi
                        $archive =array();
                        $notifications = Notification::whereNull('ac_non_ac_id')->selectRaw('year(created_at) year, count(*) published')->groupBy('year')->orderByRaw('min(created_at) DESC')->where('featured','0')->where('status','1')->get();
                        foreach ($notifications as $key => $n) {
                            $archiveMonth = Notification::whereNull('ac_non_ac_id')->selectRaw('month(created_at) month, count(*) published')->whereYear('created_at',$n->year)->groupBy('month')->orderByRaw('min(created_at) DESC')->where('featured','0')->where('status','1')->get();
                            $archive[$key]['year'] = $n->year;
                            $archive[$key]['mon'] = $archiveMonth;    
                        }
                        if ($archive){                        
                            return $this->sendResponse($archive, trans($locale.'_lang.DATA_FOUND'),200);
                        } else {
                            return $this->sendResponse($archive, trans($locale.'_lang.DATA_NOT_FOUND'),404);
                        }
                        
                        break;
                    default: //GET slider IN English
                        $archive =array();
                        $notifications = Notification::whereNull('ac_non_ac_id')->selectRaw('year(created_at) year, count(*) published')->groupBy('year')->orderByRaw('min(created_at) DESC')->where('featured','0')->where('status','1')->get();
                        foreach ($notifications as $key => $n) {
                            $archiveMonth = Notification::whereNull('ac_non_ac_id')->selectRaw('month(created_at) month, count(*) published')->whereYear('created_at',$n->year)->groupBy('month')->orderByRaw('min(created_at) DESC')->where('featured','0')->where('status','1')->get();
                            $archive[$key]['year'] = $n->year;
                            $archive[$key]['mon'] = $archiveMonth;  
                        }
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
