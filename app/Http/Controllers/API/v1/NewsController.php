<?php

namespace App\Http\Controllers\API\v1;

use App\Http\Controllers\API\v1\BaseController as BaseController;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\News;
use Validator;
use Auth;
use Carbon;
use DB;

class NewsController extends BaseController
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
                switch ($locale) {
                    case "ur": //GET Menu IN Urdu
                        $dataUR = array();     
                        $news = News::where('status','1')->orderBy('created_at','DESC')->latest()->take(10)->get();
                        if (empty($news)){                        
                            return $this->sendResponse($news, trans($locale.'_lang.DATA_NOT_FOUND'),404);
                        }
                        foreach ($news as $key => $n) {
                            $dataUR[$key]['id'] = $n->id;
                            if($n->title_ur == NULL){
                                $dataUR[$key]['title'] = $n->title_en; 
                            }else{ 
                                $dataUR[$key]['title'] = $n->title_ur; 
                            }
                            if($n->description_ur == NULL){
                                $dataUR[$key]['description'] = $n->description_en;  
                            }else{
                                $dataUR[$key]['description'] = string_cut($n->description_ur, 100,'...');
                            }
                            $dataUR[$key]['slug'] = $n->slug;
                            $dataUR[$key]['file'] = $n->file;
                            $dataUR[$key]['date'] = date("M d, Y", strtotime($n->created_at));
                        }
                        if ($dataUR){                        
                            return $this->sendResponse($dataUR, trans($locale.'_lang.DATA_FOUND'),200);
                        } else {
                            return $this->sendResponse($dataUR, trans($locale.'_lang.DATA_NOT_FOUND'),404);
                        }
                        break;
                    case "hi": //GET Menu In Hindi
                        $dataHI = array();
                        $news = News::where('status','1')->orderBy('created_at','DESC')->latest()->take(10)->get();
                        if (empty($news)){                        
                            return $this->sendResponse($news, trans($locale.'_lang.DATA_NOT_FOUND'),404);
                        }
                        foreach ($news as $key => $n) {
                            $dataHI[$key]['id'] = $n->id;
                            if($n->title_ur == NULL){
                                $dataHI[$key]['title'] = $n->title_en; 
                            }else{ 
                                $dataHI[$key]['title'] = $n->title_hi; 
                            }
                            if($n->description_hi == NULL){
                                $dataHI[$key]['description'] = $n->description_en;  
                            }else{
                                $dataHI[$key]['description'] = string_cut($n->description_hi, 100,'...');
                            }
                            $dataHI[$key]['slug'] = $n->slug;
                            $dataHI[$key]['file'] = $n->file;
                            $dataHI[$key]['date'] = date("M d, Y", strtotime($n->created_at));
                        }
                        if ($dataHI){                        
                            return $this->sendResponse($dataHI, trans($locale.'_lang.DATA_FOUND'),200);
                        } else {
                            return $this->sendResponse($dataHI, trans($locale.'_lang.DATA_NOT_FOUND'),404);
                        }
                        
                        break;
                    default: //GET slider IN English
                        $data =array();
                        $news = News::where('status','1')->orderBy('created_at','DESC')->latest()->take(10)->get();
                        if (empty($news)){                        
                            return $this->sendResponse($news, trans($locale.'_lang.DATA_NOT_FOUND'),404);
                        }
                        foreach ($news as $key => $n) {
                            $data[$key]['id'] = $n->id;
                            $data[$key]['title'] = $n->title_en;  
                            $data[$key]['description'] = string_cut($n->description_en, 100,'...');
                            $data[$key]['slug'] = $n->slug;
                            $data[$key]['file'] = $n->file;
                            $data[$key]['date'] =  date("M d, Y", strtotime($n->created_at));
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
    
    public function newsList(Request $request) {
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
                           $news = News::whereMonth('created_at', $request->month)->whereYear('created_at', $request->year)->orderBy('created_at','DESC')->where('status','1')->get();     
                        }else{
                           $news = News::orderBy('created_at', 'DESC')->whereYear('created_at', date('Y'))->where('status','1')->get();
                        }
                        if (empty($news)){                        
                            return $this->sendResponse($news, trans($locale.'_lang.DATA_NOT_FOUND'),404);
                        }
                        foreach ($news as $key => $n) {
                            $dataUR[$key]['id'] = $n->id;
                            if($n->title_ur == NULL){
                                $dataUR[$key]['title'] = $n->title_en; 
                            }else{ 
                                $dataUR[$key]['title'] = $n->title_ur; 
                            }
                            if($n->description_ur == NULL){
                                $dataUR[$key]['description'] = strip_tags(string_cut($n->description_en, 200,'...'));
                            }else{
                                $dataUR[$key]['description'] = strip_tags(string_cut($n->description_ur, 200,'...'));
                            }
                            $dataUR[$key]['slug'] = $n->slug;
                            $dataUR[$key]['date'] = $n->created_at;
                            $dataUR[$key]['file'] = $n->file;
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
                           $news = News::whereMonth('created_at', $request->month)->whereYear('created_at', $request->year)->orderBy('created_at','DESC')->where('status','1')->get();     
                        }else{
                           $news = News::orderBy('created_at', 'DESC')->whereYear('created_at', date('Y'))->where('status','1')->get();
                        }
                        if (empty($news)){                        
                            return $this->sendResponse($news, trans($locale.'_lang.DATA_NOT_FOUND'),404);
                        }
                        foreach ($news as $key => $n) {
                            $dataHI[$key]['id'] = $n->id;
                            if($n->title_ur == NULL){
                                $dataHI[$key]['title'] = $n->title_en; 
                            }else{ 
                                $dataHI[$key]['title'] = $n->title_hi; 
                            }
                            if($n->description_hi == NULL){
                                $dataHI[$key]['description'] = strip_tags(string_cut($n->description_en, 200,'...'));
                            }else{
                                $dataHI[$key]['description'] = strip_tags(string_cut($n->description_hi, 200,'...'));
                            }
                            $dataHI[$key]['slug'] = $n->slug;
                            $dataHI[$key]['date'] = $n->created_at;
                            $dataHI[$key]['file'] = $n->file;
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
                           $news = News::whereMonth('created_at', $request->month)->whereYear('created_at', $request->year)->orderBy('created_at','DESC')->where('status','1')->get();     
                        }else{
                           $news = News::orderBy('created_at', 'DESC')->whereYear('created_at', date('Y'))->where('status','1')->get();
                        }
                        if (empty($news)){                        
                            return $this->sendResponse($news, trans($locale.'_lang.DATA_NOT_FOUND'),404);
                        }
                        foreach ($news as $key => $m) {
                            $data[$key]['id'] = $m->id;
                            $data[$key]['title'] = $m->title_en;  
                            $data[$key]['description'] = strip_tags(string_cut($m->description_en, 200,'...'));
                            $data[$key]['slug'] = $m->slug;
                            $data[$key]['file'] = $m->file;
                            $data[$key]['date'] = $m->created_at;
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

    public function newsArchive(Request $request) {
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
                        $news = News::selectRaw('year(created_at) year, count(*) published')->groupBy('year')->orderByRaw('min(created_at) DESC')->where('status','1')->get();
                        foreach ($news as $key => $n) {
                            $archiveMonth = News::selectRaw('month(created_at) month, count(*) published')->whereYear('created_at',$n->year)->groupBy('month')->orderByRaw('min(created_at) DESC')->where('status','1')->get();
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
                        $news = News::selectRaw('year(created_at) year, count(*) published')->groupBy('year')->orderByRaw('min(created_at) DESC')->where('status','1')->get();
                        foreach ($news as $key => $n) {
                            $archiveMonth = News::selectRaw('month(created_at) month, count(*) published')->whereYear('created_at',$n->year)->groupBy('month')->orderByRaw('min(created_at) DESC')->where('status','1')->get();
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
                        $news = News::selectRaw('year(created_at) year, count(*) published')->groupBy('year')->orderByRaw('min(created_at) DESC')->where('status','1')->get();
                        foreach ($news as $key => $n) {
                            $archiveMonth = News::selectRaw('month(created_at) month, count(*) published')->whereYear('created_at',$n->year)->groupBy('month')->orderByRaw('min(created_at) DESC')->where('status','1')->get();
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

    public function newsDetail(Request $request) {
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
                        $img =array();                      
                        $news = News::where('slug',$request->slug)->with('getNewsImage')->first();
                        if (empty($news)){                        
                            return $this->sendResponse($news, trans($locale.'_lang.DATA_NOT_FOUND'),404);
                        }
                        $dataUR['id'] = $news->id;
                        if($news->title_ur == NULL){
                            $dataUR['title'] = $news->title_en; 
                        }else{ 
                            $dataUR['title'] = $news->title_ur; 
                        }
                        if($news->description_ur == NULL){
                            $dataUR['description'] = $news->description_en;
                        }else{
                            $dataUR['description'] = $news->description_ur;
                        }
                        $dataUR['slug'] = $news->slug;
                        $dataUR['date'] = $news->created_at;
                        $dataUR['image'] = $news->file;
                        if(!empty($news->getNewsImage)){
                            foreach ($news->getNewsImage as $key => $value) {
                                $img[$key]['file']=$value->file;
                                $img[$key]['heading']=$value->heading;
                            }
                        }
                        $dataUR['image_gallery'] = $img;
                        if ($dataUR){                        
                            return $this->sendResponse($dataUR, trans($locale.'_lang.DATA_FOUND'),200);
                        } else {
                            return $this->sendResponse($dataUR, trans($locale.'_lang.DATA_NOT_FOUND'),404);
                        }
                        break;
                    case "hi": //GET Menu In Hindi
                        $dataHI =array();  
                        $img =array();                      
                        $news = News::where('slug',$request->slug)->with('getNewsImage')->first();
                        if (empty($news)){                        
                            return $this->sendResponse($news, trans($locale.'_lang.DATA_NOT_FOUND'),404);
                        }
                        $dataHI['id'] = $news->id;
                        if($news->title_ur == NULL){
                            $dataHI['title'] = $news->title_en; 
                        }else{ 
                            $dataHI['title'] = $news->title_hi; 
                        }
                        if($news->description_hi == NULL){
                            $dataHI['description'] = $news->description_en;
                        }else{
                            $dataHI['description'] = $news->description_hi;
                        }
                        $dataHI['slug'] = $news->slug;
                        $dataHI['date'] = $news->created_at;
                        $dataHI['image'] = $news->file;
                        if(!empty($news->getNewsImage)){
                            foreach ($news->getNewsImage as $key => $value) {
                                $img[$key]['file']=$value->file;
                                $img[$key]['heading']=$value->heading;
                            }
                        }
                        $dataHI['image_gallery'] = $img;
                        if ($dataHI){                        
                            return $this->sendResponse($dataHI, trans($locale.'_lang.DATA_FOUND'),200);
                        } else {
                            return $this->sendResponse($dataHI, trans($locale.'_lang.DATA_NOT_FOUND'),404);
                        }
                        
                        break;
                    default: //GET slider IN English
                        $data =array();  
                        $img =array();                      
                        $news = News::where('slug',$request->slug)->with('getNewsImage')->first();
                        if (empty($news)){                        
                            return $this->sendResponse($news, trans($locale.'_lang.DATA_NOT_FOUND'),404);
                        }
                        $data['id'] = $news->id;
                        $data['title'] = $news->title_en;  
                        $data['description'] = $news->description_en;
                        $data['slug'] = $news->slug;
                        $data['date'] = $news->created_at;
                        $data['image'] = $news->file;
                        if(!empty($news->getNewsImage)){
                            foreach ($news->getNewsImage as $key => $value) {
                                $img[$key]['file']=$value->file;
                                $img[$key]['heading']=$value->heading;
                            }
                        }
                        $data['image_gallery'] = $img;
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
