<?php

namespace App\Http\Controllers\API\v1;

use App\Http\Controllers\API\v1\BaseController as BaseController;
use App\Http\Controllers\Controller;
Use App\AcademicAndNonAcademic;
use Illuminate\Http\Request;
use Harimayco\Menu\Facades\Menu;
use App\HomePageSlider;
Use App\UserLog;
use App\Widget;
use App\User;
use Validator;
use Auth;
use DB;

class HomeController extends BaseController
{

    public function headerMenu(Request $request) {
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
                        $menu = Menu::get(10);
                        if (!empty($menu)){                        
                            return $this->sendResponse($menu, trans($locale.'_lang.DATA_FOUND'),200);
                        } else {
                            return $this->sendResponse($menu, trans($locale.'_lang.DATA_NOT_FOUND'),404);
                        }
                        break;
                    case "hi": //GET Menu In Hindi
                        $menu = Menu::get(9);
                        if (!empty($menu)){                        
                            return $this->sendResponse($menu, trans($locale.'_lang.DATA_FOUND'),200);
                        } else {
                            return $this->sendResponse($menu, trans($locale.'_lang.DATA_NOT_FOUND'),404);
                        }
                        
                        break;
                    default: //GET slider IN English
                        $menu = Menu::get(5);
                        if (!empty($menu)){                        
                            return $this->sendResponse($menu, trans($locale.'_lang.DATA_FOUND'),200);
                        } else {
                            return $this->sendResponse($menu, trans($locale.'_lang.DATA_NOT_FOUND'),404);
                        }
                        break;
                }
            }catch (Exception $e) {
               return $this->sendError(trans($locale.'_lang.MODEL_NOT_FOUND'), $e->getMessage(), 404);
            }
        }
    }

    public function footerMenu(Request $request) {
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
                        $menu = Menu::get(8);
                        if (!empty($menu)){                        
                            return $this->sendResponse($menu, trans($locale.'_lang.DATA_FOUND'),200);
                        } else {
                            return $this->sendResponse($menu, trans($locale.'_lang.DATA_NOT_FOUND'),404);
                        }
                        break;
                    case "hi": //GET Menu In Hindi
                        $menu = Menu::get(7);
                        if (!empty($menu)){                        
                            return $this->sendResponse($menu, trans($locale.'_lang.DATA_FOUND'),200);
                        } else {
                            return $this->sendResponse($menu, trans($locale.'_lang.DATA_NOT_FOUND'),404);
                        }
                        
                        break;
                    default: //GET slider IN English
                        $menu = Menu::get(6);
                        if (!empty($menu)){                        
                            return $this->sendResponse($menu, trans($locale.'_lang.DATA_FOUND'),200);
                        } else {
                            return $this->sendResponse($menu, trans($locale.'_lang.DATA_NOT_FOUND'),404);
                        }
                        break;
                }
            }catch (Exception $e) {
               return $this->sendError(trans($locale.'_lang.MODEL_NOT_FOUND'), $e->getMessage(), 404);
            }
        }
    }

    public function menu(Request $request) {
        $dataMenu = array();
        $locale='en';
        $menu = DB::table('menus')->where('id', request()->id)->first();
        if (empty($menu)){                        
            return $this->sendResponse($menu, trans($locale.'_lang.DATA_NOT_FOUND'),404);
        }
        $dataMenu['menu'] = $menu->name;
        $dataMenu['menuitem'] = Menu::get($menu->id); 
        if ($dataMenu){                    
            return $this->sendResponse($dataMenu, trans($locale.'_lang.DATA_FOUND'),200);
        } else {
            return $this->sendResponse($dataMenu, trans($locale.'_lang.DATA_NOT_FOUND'),404);
        }       
    }    

    public function leftSideMenu(Request $request) {
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
                        $menu = Menu::get(17);
                        if (!empty($menu)){                        
                            return $this->sendResponse($menu, trans($locale.'_lang.DATA_FOUND'),200);
                        } else {
                            return $this->sendResponse($menu, trans($locale.'_lang.DATA_NOT_FOUND'),404);
                        }
                        break;
                    case "hi": //GET Menu In Hindi
                        $menu = Menu::get(12);
                        if (!empty($menu)){                        
                            return $this->sendResponse($menu, trans($locale.'_lang.DATA_FOUND'),200);
                        } else {
                            return $this->sendResponse($menu, trans($locale.'_lang.DATA_NOT_FOUND'),404);
                        }
                        
                        break;
                    default: //GET slider IN English
                        $menu = Menu::get(11);
                        if (!empty($menu)){                        
                            return $this->sendResponse($menu, trans($locale.'_lang.DATA_FOUND'),200);
                        } else {
                            return $this->sendResponse($menu, trans($locale.'_lang.DATA_NOT_FOUND'),404);
                        }
                        break;
                }
            }catch (Exception $e) {
               return $this->sendError(trans($locale.'_lang.MODEL_NOT_FOUND'), $e->getMessage(), 404);
            }
        }
    }    

    public function homeSlider(Request $request) {
        $validator = Validator::make($request->all(), [
            'lang' => 'required|in:en,hi,ur',
        ]);
        $input = $request->all();
        if ($validator->fails()) {
            return $this->sendResponse('Validation Error.', $validator->errors(),422);
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
                    case "ur": //GET slider IN Urdu
                        $dataUR = array();
                        $sliders = HomePageSlider::where('status','1')->orderBy('order_on','ASC')->orderBy('created_at','desc')->get();
                        if (empty($sliders)){                        
                            return $this->sendResponse($sliders, trans($locale.'_lang.DATA_NOT_FOUND'),404);
                        }
                        foreach ($sliders as $key => $slider) {
                            $dataUR[$key]['id'] = $slider->id;
                            if($slider->heading_ur == NULL){
                                $dataUR[$key]['heading'] = $slider->heading;  
                            }else{
                                $dataUR[$key]['heading'] = $slider->heading_ur; 
                            }
                            if($slider->sub_heading_ur == NULL){
                                $dataUR[$key]['sub_heading'] = $slider->sub_heading;  
                            }else{
                                $dataUR[$key]['sub_heading'] = $slider->sub_heading_ur;
                            }
                            $dataUR[$key]['image'] = $slider->image;
                            $dataUR[$key]['url'] = $slider->url;
                            $dataUR[$key]['status'] = $slider->status;
                            $dataUR[$key]['created_at'] = $slider->created_at;
                        }
                        if ($dataUR){                        
                            return $this->sendResponse($dataUR, trans($locale.'_lang.DATA_FOUND'),200);
                        } else {
                            return $this->sendResponse($dataUR, trans($locale.'_lang.DATA_NOT_FOUND'),404);
                        }

                        break;
                    case "hi": //GET slider IN Hindi
                        $dataHI = array();
                        $sliders = HomePageSlider::where('status','1')->orderBy('order_on','ASC')->orderBy('created_at','desc')->get();
                        if (empty($sliders)){                        
                            return $this->sendResponse($sliders, trans($locale.'_lang.DATA_NOT_FOUND'),404);
                        }
                        foreach ($sliders as $key => $slider) {
                            $dataHI[$key]['id'] = $slider->id;
                            if($slider->heading_hi == NULL){
                                $dataHI[$key]['heading'] = $slider->heading;  
                            }else{
                                $dataHI[$key]['heading'] = $slider->heading_hi; 
                            }
                            if($slider->sub_heading_hi == NULL){
                                $dataHI[$key]['sub_heading'] = $slider->sub_heading;  
                            }else{
                                $dataHI[$key]['sub_heading'] = $slider->sub_heading_hi;
                            }
                            $dataHI[$key]['image'] = $slider->image;
                            $dataHI[$key]['url'] = $slider->url;
                        }
                        if ($dataHI){                        
                            return $this->sendResponse($dataHI, trans($locale.'_lang.DATA_FOUND'),200);
                        } else {
                            return $this->sendResponse($dataHI, trans($locale.'_lang.DATA_NOT_FOUND'),404);
                        }
                        
                        break;
                    default: //GET slider IN English
                        $data = array();
                        $sliders = HomePageSlider::where('status','1')->orderBy('order_on','ASC')->orderBy('created_at','desc')->get();
                        if (empty($sliders)){                        
                            return $this->sendResponse($sliders, trans($locale.'_lang.DATA_NOT_FOUND'),404);
                        }
                        foreach ($sliders as $key => $slider) {
                            $data[$key]['id'] = $slider->id;
                            $data[$key]['heading'] = $slider->heading;
                            $data[$key]['sub_heading'] = $slider->sub_heading;
                            $data[$key]['image'] = $slider->image;
                            $data[$key]['url'] = $slider->url;
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
        return $response;
    }

    public function widget(Request $request) {
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
                        $widgets = Widget::orderBy('order_on','desc')->when(request()->slug, function ($query) {
                              $query->where('slug', '=' , request()->slug);
                              })->get();
                        if (empty($widgets)){                        
                            return $this->sendResponse($widgets, trans($locale.'_lang.DATA_NOT_FOUND'),404);
                        }
                        foreach ($widgets as $key => $widget) {
                            $dataUR[$key]['id'] = $widget->id;
                            if($widget->title_ur == NULL){
                                $dataUR[$key]['title'] = $widget->title_en;  
                            }else{
                                $dataUR[$key]['title'] = $widget->title_ur; 
                            }
                            if($widget->description_ur == NULL){
                                $dataUR[$key]['description'] = $widget->description_en;  
                            }else{
                                $dataUR[$key]['description'] = $widget->description_ur;
                            }
                            $dataUR[$key]['slug'] = $widget->slug;
                        }
                        if ($dataUR){                        
                            return $this->sendResponse($dataUR, trans($locale.'_lang.DATA_FOUND'),200);
                        } else {
                            return $this->sendResponse($dataUR, trans($locale.'_lang.DATA_NOT_FOUND'),404);
                        }
                        break;
                    case "hi": //GET widget IN Hindi
                        $dataHI = array();
                        $widgets = Widget::orderBy('updated_at','desc')->when(request()->slug, function ($query) {
                              $query->where('slug', '=' , request()->slug);
                              })->get();
                        if (empty($widgets)){                        
                            return $this->sendResponse($widgets, trans($locale.'_lang.DATA_NOT_FOUND'),404);
                        }
                        foreach ($widgets as $key => $widget) {
                            $dataHI[$key]['id'] = $widget->id;
                            if($widget->title_hi == NULL){
                                $dataHI[$key]['title'] = $widget->title_en;  
                            }else{
                                $dataHI[$key]['title'] = $widget->title_hi; 
                            }
                            if($widget->description_hi == NULL){
                                $dataHI[$key]['description'] = $widget->description_en;  
                            }else{
                                $dataHI[$key]['description'] = $widget->description_hi;
                            }
                            $dataHI[$key]['slug'] = $widget->slug;
                        }
                        if ($dataHI){                        
                            return $this->sendResponse($dataHI, trans($locale.'_lang.DATA_FOUND'),200);
                        } else {
                            return $this->sendResponse($dataHI, trans($locale.'_lang.DATA_NOT_FOUND'),404);
                        }                      
                        break;
                    default: //GET widget IN English
                        $data = array();
                        $widgets = Widget::orderBy('order_on','desc')->when(request()->slug, function ($query) {
                              $query->where('slug', '=' , request()->slug);
                              })->get();
                        if (empty($widgets)){                        
                            return $this->sendResponse($widgets, trans($locale.'_lang.DATA_NOT_FOUND'),404);
                        }
                        foreach ($widgets as $key => $widget) {
                            $data[$key]['id'] = $widget->id;
                            $data[$key]['title'] = $widget->title_en;  
                            $data[$key]['description'] = $widget->description_en;  
                            $data[$key]['slug'] = $widget->slug;
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

    public function mainWidget(Request $request) {
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
                        $widgets = Widget::orderBy('order_on','asc')->whereIN('id', [6, 8, 9,10])->get();
                        if (empty($widgets)){                        
                            return $this->sendResponse($widgets, trans($locale.'_lang.DATA_NOT_FOUND'),404);
                        }
                        foreach ($widgets as $key => $widget) {
                            $dataUR[$key]['id'] = $widget->id;
                            if($widget->title_ur == NULL){
                                $dataUR[$key]['title'] = $widget->title;  
                            }else{
                                $dataUR[$key]['title'] = $widget->title_ur; 
                            }
                            if($widget->description_ur == NULL){
                                $dataUR[$key]['description'] = $widget->description;  
                            }else{
                                $dataUR[$key]['description'] = $widget->description_ur;
                            }
                            $dataUR[$key]['slug'] = $widget->slug;
                        }
                        if ($dataUR){                        
                            return $this->sendResponse($dataUR, trans($locale.'_lang.DATA_FOUND'),200);
                        } else {
                            return $this->sendResponse($dataUR, trans($locale.'_lang.DATA_NOT_FOUND'),404);
                        }
                        break;
                    case "hi": //GET widget IN Hindi
                        $dataHI = array();
                        $widgets = Widget::orderBy('order_on','asc')->whereIN('id', [6, 8, 9,10])->get();
                        if (empty($widgets)){                        
                            return $this->sendResponse($widgets, trans($locale.'_lang.DATA_NOT_FOUND'),404);
                        }
                        foreach ($widgets as $key => $widget) {
                            $dataHI[$key]['id'] = $widget->id;
                            if($widget->title_hi == NULL){
                                $dataHI[$key]['title'] = $widget->title;  
                            }else{
                                $dataHI[$key]['title'] = $widget->title_hi; 
                            }
                            if($widget->description_hi == NULL){
                                $dataHI[$key]['description'] = $widget->description;  
                            }else{
                                $dataHI[$key]['description'] = $widget->description_hi;
                            }
                            $dataHI[$key]['slug'] = $widget->slug;
                        }
                        if ($dataHI){                        
                            return $this->sendResponse($dataHI, trans($locale.'_lang.DATA_FOUND'),200);
                        } else {
                            return $this->sendResponse($dataHI, trans($locale.'_lang.DATA_NOT_FOUND'),404);
                        }
                        break;
                    default: //GET widget IN English
                        $data = array();
                        $widgets = Widget::orderBy('order_on','asc')->whereIN('id', [6, 8, 9,10])->get();
                        if (empty($widgets)){                        
                            return $this->sendResponse($widgets, trans($locale.'_lang.DATA_NOT_FOUND'),404);
                        }
                        foreach ($widgets as $key => $widget) {
                            $data[$key]['id'] = $widget->id;
                            $data[$key]['title'] = $widget->title_en;  
                            $data[$key]['description'] = $widget->description_en;  
                            $data[$key]['slug'] = $widget->slug;
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

    public function userSearch(Request $request){
        $input = $request->all();
        $name = $input['search'];
        $users = User::where('id','!=',Auth::id())->where('status','=','1')->where('eid','!=','')
        ->when(request()->for_id, function ($query) {
            $query->where('for_id','=',request()->for_id);
        })
        ->where(function($query) use ($name){
            $query->orWhere('eid', 'LIKE', '%'.$name.'%');
            $query->orWhere('title', 'LIKE', '%'.$name.'%');
            $query->orWhere('first_name', 'LIKE', '%'.$name.'%');
            $query->orWhere('last_name', 'LIKE', '%'.$name.'%');
            $query->orWhere('middle_name', 'LIKE', '%'.$name.'%');
            $query->orWhereRaw("CONCAT(`first_name`, ' ', `last_name`) LIKE ?", ['%'.$name.'%']);
            $query->orWhereRaw("CONCAT(`first_name`, ' ', `middle_name`, ' ', `last_name`) LIKE ?", ['%'.$name.'%']);
        })        
        ->orderBy('created_at','desc')->get();
        if(count($users) > 0){
            return response()
            ->json([
                'data' => $users->toArray(),
                'code' => 1000,
            ]);
        }else{
            return response()
            ->json([
                'message' => 'user not found!',
                'code' => 1001,
            ]);
            } 
    }

    public function departmentSearch(Request $request){
        $departments = AcademicAndNonAcademic::where('type','=',request()->type)->where('status','=',1)->orderBy('order_on', 'asc')
            ->when(request()->search, function ($query) {
                $query->where('title_en', 'LIKE', '%'.request()->search.'%');
                $query->orwhere('id', 'LIKE', '%'.request()->search.'%');
            })
            ->get();
        if(count($departments) > 0){
            return response()
            ->json([
                'data' => $departments->toArray(),
                'code' => 1000,
            ]);
        }else{
            return response()
            ->json([
                'message' => 'departments not found!',
                'code' => 1001,
            ]);
            } 
    }

    /*
    *Get Last Updated home page
    */

    public function getHomeUpdated(Request $request)
    {
        $data = UserLog::where('log_type_id','3')->orderby('updated_at','DESC')->first();
        return $this->sendResponse($data, trans('en_lang.DATA_FOUND'),200);
    }
}
