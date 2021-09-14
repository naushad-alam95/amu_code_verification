<?php

namespace App\Http\Controllers\API\v1;

use App\Http\Controllers\API\v1\BaseController as BaseController;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Events;
use Validator;
use Auth;

class EventsController extends BaseController
{

    public function homeEvents(Request $request) {
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
                        $events = Events::orderBy('start_date','asc')->where('status','1')->whereDate('end_date', '>=', date('Y-m-d'))->get();
                        if (empty($events)){                        
                            return $this->sendResponse($events, trans($locale.'_lang.DATA_NOT_FOUND'),404);
                        }
                        foreach ($events as $key => $event) {
                            $dataUR[$key]['id'] = $event->id;
                            if($event->title_ur == NULL){
                                $dataUR[$key]['title'] = $event->title_en;  
                            }else{
                                $dataUR[$key]['title'] = $event->title_ur; 
                            }
                            if($event->description_ur == NULL){
                                $dataUR[$key]['description'] = $event->description_en;  
                            }else{
                                $dataUR[$key]['description'] = $event->description_ur;
                            }
                            $dataUR[$key]['slug'] = $event->slug;
                            $dataUR[$key]['start_date'] = $event->start_date;
                            $dataUR[$key]['end_date'] = $event->end_date;
                            $dataUR[$key]['file'] = $event->file;
                            $dataUR[$key]['image'] = $event->image;
                            $dataUR[$key]['venue'] = $event->venue;
                        }
                        if ($dataUR){                        
                            return $this->sendResponse($dataUR, trans($locale.'_lang.DATA_FOUND'),200);
                        } else {
                            return $this->sendResponse($dataUR, trans($locale.'_lang.DATA_NOT_FOUND'),404);
                        }
                        break;
                    case "hi": //GET widget IN Hindi
                        $dataHI = array();
                        $events = Events::orderBy('start_date','asc')->where('status','1')->whereDate('end_date', '>=', date('Y-m-d'))->get();
                        if (empty($events)){                        
                            return $this->sendResponse($events, trans($locale.'_lang.DATA_NOT_FOUND'),404);
                        }
                        foreach ($events as $key => $event) {
                            $dataHI[$key]['id'] = $event->id;
                            if($event->title_ur == NULL){
                                $dataHI[$key]['title'] = $event->title_en;  
                            }else{
                                $dataHI[$key]['title'] = $event->title_hi; 
                            }
                            if($event->description_ur == NULL){
                                $dataHI[$key]['description'] = $event->description_en;  
                            }else{
                                $dataHI[$key]['description'] = $event->description_hi;
                            }
                            $dataHI[$key]['slug'] = $event->slug;
                            $dataHI[$key]['start_date'] = $event->start_date;
                            $dataHI[$key]['end_date'] = $event->end_date;
                            $dataHI[$key]['file'] = $event->file;
                            $dataHI[$key]['image'] = $event->image;
                            $dataHI[$key]['venue'] = $event->venue;
                        }
                        if ($dataHI){                        
                            return $this->sendResponse($dataHI, trans($locale.'_lang.DATA_FOUND'),200);
                        } else {
                            return $this->sendResponse($dataHI, trans($locale.'_lang.DATA_NOT_FOUND'),404);
                        }
                        break;
                    default: //GET widget IN English
                        $data = array();
                        $events = Events::orderBy('start_date','asc')->where('status','1')->whereDate('end_date', '>=', date('Y-m-d'))->get();
                        if (empty($events)){                        
                            return $this->sendResponse($events, trans($locale.'_lang.DATA_NOT_FOUND'),404);
                        }
                        foreach ($events as $key => $event) {
                            $data[$key]['id'] = $event->id;
                            $data[$key]['title'] = $event->title_en;  
                            $data[$key]['description'] = $event->description_en;  
                            $data[$key]['slug'] = $event->slug;
                            $data[$key]['start_date'] = $event->start_date;
                            $data[$key]['end_date'] = $event->end_date;
                            $data[$key]['file'] = $event->file;
                            $data[$key]['image'] = $event->image;
                            $data[$key]['venue'] = $event->venue;
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

    public function eventList(Request $request) {
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
                           $events = Events::select('id','title_en','title_ur','description_en','description_ur','slug','start_date','end_date','file','image','venue')->whereMonth('start_date', $request->month)->where('status','1')->whereYear('start_date', $request->year)->orderBy('start_date','desc')->get(); 
                        }else{
                           $events = Events::select('id','title_en','title_ur','description_en','description_ur','slug','start_date','end_date','file','image','venue')->orderBy('start_date','desc')->where('status','1')->whereDate('end_date', '<=', date('Y-m-d'))->get();
                        }
                        if (empty($events)){                        
                            return $this->sendResponse($events, trans($locale.'_lang.DATA_NOT_FOUND'),404);
                        }
                        foreach ($events as $key => $event) {
                            if($event->title_ur == NULL){
                                $event['title'] = $event->title_en;  
                            }else{
                                $event['title'] = $event->title_ur; 
                            }
                            if($event->description_ur == NULL){
                                $event['description'] = $event->description_en;  
                            }else{
                                $event['description'] = $event->description_ur;
                            }
                            
                        }
                        if ($events){                        
                            return $this->sendResponse($events, trans($locale.'_lang.DATA_FOUND'),200);
                        } else {
                            return $this->sendResponse($events, trans($locale.'_lang.DATA_NOT_FOUND'),404);
                        }
                        break;
                    case "hi": //GET Menu In Hindi
                        $dataHI = array();
                        if(isset($request->month) && isset($request->year)){
                           $events = Events::select('id','title_en','title_hi','description_en','description_hi','slug','start_date','end_date','file','image','venue')->whereMonth('start_date', $request->month)->whereYear('start_date', $request->year)->orderBy('start_date','desc')->where('status','1')->get(); 
                        }else{
                           $events = Events::select('id','title_en','title_hi','description_en','description_hi','slug','start_date','end_date','file','image','venue')->orderBy('start_date','desc')->where('status','1')->whereDate('end_date', '<=', date('Y-m-d'))->get();
                        }
                        if (empty($events)){                        
                            return $this->sendResponse($events, trans($locale.'_lang.DATA_NOT_FOUND'),404);
                        }
                        foreach ($events as $key => $event) {
                           
                            if($event->title_hi == NULL){
                                $event['title'] = $event->title_en;  
                            }else{
                                $event['title'] = $event->title_hi; 
                            }
                            if($event->description_hi == NULL){
                                $event['description'] = $event->description_en;  
                            }else{
                                $event['description'] = $event->description_hi;
                            }
                            
                        }
                        if ($events){                        
                            return $this->sendResponse($events, trans($locale.'_lang.DATA_FOUND'),200);
                        } else {
                            return $this->sendResponse($events, trans($locale.'_lang.DATA_NOT_FOUND'),404);
                        }
                        
                        break;
                    default: //GET slider IN English
                        $data =array();
                        if(isset($request->month) && isset($request->year)){
                           $events = Events::select('id','title_en as title','description_en as description','slug','start_date','end_date','file','image','venue')->whereMonth('start_date', $request->month)->where('status','1')->whereYear('start_date', $request->year)->orderBy('start_date','desc')->get(); 
                        }else{
                           $events = Events::select('id','title_en as title','description_en as description','slug','start_date','end_date','file','image','venue')->orderBy('start_date','desc')->where('status','1')->whereDate('end_date', '<=', date('Y-m-d'))->get();
                        }
                        if (empty($events)){                        
                            return $this->sendResponse($events, trans($locale.'_lang.DATA_NOT_FOUND'),404);
                        }
                        
                        if ($events){                        
                            return $this->sendResponse($events, trans($locale.'_lang.DATA_FOUND'),200);
                        } else {
                            return $this->sendResponse($events, trans($locale.'_lang.DATA_NOT_FOUND'),404);
                        }
                        break;
                }
            } catch (Exception $e) {
               return $this->sendError(trans($locale.'_lang.MODEL_NOT_FOUND'), $e->getMessage(), 404);
            }
        }
    }

    public function eventDetail(Request $request) {
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
                switch ($locale) {
                    case "ur": //GET Menu IN Urdu
                        $dataUR =array();                   
                        $event = events::where('slug',$request->slug)->first();
                        if (empty($event)){                        
                            return $this->sendResponse($event, trans($locale.'_lang.DATA_NOT_FOUND'),404);
                        }
                        $dataUR['id'] = $event->id;
                        if($event->title_ur == NULL){
                            $dataUR['title'] = $event->title_en; 
                        }else{ 
                            $dataUR['title'] = $event->title_ur; 
                        }
                        if($event->description_ur == NULL){
                            $dataUR['description'] = $event->description_en;
                        }else{
                            $dataUR['description'] = $event->description_ur;
                        }
                        $dataUR['slug'] = $event->slug;
                        $dataUR['start_date'] = $event->start_date;
                        $dataUR['end_date'] = $event->end_date;
                        $dataUR['file'] = $event->file;
                        $dataUR['image'] = $event->image;
                        $dataUR['venue'] = $event->venue;
                        if ($dataUR){                        
                            return $this->sendResponse($dataUR, trans($locale.'_lang.DATA_FOUND'),200);
                        } else {
                            return $this->sendResponse($dataUR, trans($locale.'_lang.DATA_NOT_FOUND'),404);
                        }
                        break;
                    case "hi": //GET Menu In Hindi
                        $dataHI =array();                   
                        $event = events::where('slug',$request->slug)->first();
                        if (empty($event)){                        
                            return $this->sendResponse($event, trans($locale.'_lang.DATA_NOT_FOUND'),404);
                        }
                        $dataHI['id'] = $event->id;
                        if($event->title_ur == NULL){
                            $dataHI['title'] = $event->title_en; 
                        }else{ 
                            $dataHI['title'] = $event->title_hi; 
                        }
                        if($event->description_hi == NULL){
                            $dataHI['description'] = $event->description_en;
                        }else{
                            $dataHI['description'] = $event->description_hi;
                        }
                        $dataHI['slug'] = $event->slug;
                        $dataHI['start_date'] = $event->start_date;
                        $dataHI['end_date'] = $event->end_date;
                        $dataHI['file'] = $event->file;
                        $dataHI['image'] = $event->image;
                        $dataHI['venue'] = $event->venue;
                        
                        if ($dataHI){                        
                            return $this->sendResponse($dataHI, trans($locale.'_lang.DATA_FOUND'),200);
                        } else {
                            return $this->sendResponse($dataHI, trans($locale.'_lang.DATA_NOT_FOUND'),404);
                        }
                        
                        break;
                    default: //GET slider IN English
                        $data =array();                     
                        $event = Events::where('slug',$request->slug)->first();
                        if (empty($event)){                        
                            return $this->sendResponse($event, trans($locale.'_lang.DATA_NOT_FOUND'),404);
                        }
                        $data['id'] = $event->id;
                        $data['title'] = $event->title_en;  
                        $data['description'] = $event->description_en;  
                        $data['slug'] = $event->slug;
                        $data['start_date'] = $event->start_date;
                        $data['end_date'] = $event->end_date;
                        $data['file'] = $event->file;
                        $data['image'] = $event->image;
                        $data['venue'] = $event->venue;
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

    public function eventArchive(Request $request) {
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
                        $events = Events::selectRaw('year(start_date) year, count(*) published')->groupBy('year')->orderByRaw('min(start_date) DESC')->get();
                        foreach ($events as $key => $n) {
                            $archiveMonth = Events::selectRaw('month(start_date) month, count(*) published')->whereYear('start_date',$n->year)->groupBy('month')->orderByRaw('min(start_date) DESC')->get();
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
                        $events = Events::selectRaw('year(start_date) year, count(*) published')->groupBy('year')->orderByRaw('min(start_date) DESC')->get();
                        foreach ($events as $key => $n) {
                            $archiveMonth = Events::selectRaw('month(start_date) month, count(*) published')->whereYear('start_date',$n->year)->groupBy('month')->orderByRaw('min(start_date) DESC')->get();
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
                        $events = Events::selectRaw('year(start_date) year, count(*) published')->groupBy('year')->orderByRaw('min(start_date) DESC')->get();
                        foreach ($events as $key => $n) {
                            $archiveMonth = Events::selectRaw('month(start_date) month, count(*) published')->whereYear('start_date',$n->year)->groupBy('month')->orderByRaw('min(start_date) DESC')->get();
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
