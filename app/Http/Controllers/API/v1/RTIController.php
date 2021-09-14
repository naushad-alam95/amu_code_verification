<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\API\v1\BaseController as BaseController;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Harimayco\Menu\Facades\Menu;
use App\RTI;
use App\Widget;
use Validator;
use Auth;
use DB;

class RTIController extends BaseController
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
                $rti = RTI::where('status','1')->orderBy('order_on','asc')->get();
                /*$datawidget = array();  
                $widget = Widget::where('id','31')->first();*/
                switch ($locale) {
                    case "ur": //GET Annual Report IN Urdu
                        $dataUR = array();
                        if (empty($rti)){                        
                            return $this->sendResponse($rti, trans($locale.'_lang.DATA_NOT_FOUND'),404);
                        }                        
                        /*if (!empty($widget)) {
                            $datawidget['id'] = $widget->id; 
                            if($widget->title_ur == NULL){
                                $datawidget['title'] = $widget->title_en;  
                            }else{
                                $datawidget['title'] = $widget->title_ur; 
                            }
                            if($widget->description_ur == NULL){
                                $datawidget['description'] = $widget->description_en;  
                            }else{
                                $datawidget['description'] = $widget->description_ur;
                            } 
                            $datawidget['slug'] = $widget->slug;
                        }                       

                        $dataMenu = array();
                        $menu = DB::table('menus')->where('id', '42')->first();
                        if (!empty($menu)) {
                            $dataMenu['menu'] = $menu->name;
                            $dataMenu['menuitem'] = Menu::get(42);
                        } */                       
                       
                        foreach ($rti as $key => $dir) {
                            
                            $dataUR[$key]['id'] = $dir->id;
                            if($dir->title_ur == NULL){
                               $dataUR[$key]['title'] = $dir->title_en;  
                            }else{
                                $dataUR[$key]['title'] = $dir->title_ur; 
                            }
                            $dataUR[$key]['order_on'] = $dir->order_on;  
                            $dataUR[$key]['file'] = $dir->file;
                            
                            /*if ($datawidget){
                                $dataUR['widget'] = $datawidget;  
                            }else{
                                $dataUR['widget'] = '';
                            } 
                            if ($dataMenu){
                                $dataUR['menu'] = $dataMenu;  
                            }else{
                                $dataUR['menu'] = '';
                            }*/
                        }
                        if ($dataUR){                        
                            return $this->sendResponse($dataUR, trans($locale.'_lang.DATA_FOUND'),200);
                        } else {
                            return $this->sendResponse($dataUR, trans($locale.'_lang.DATA_NOT_FOUND'),404);
                        }
                        break;
                    case "hi": //GET Annual Report IN Hindi
                        $dataHi = array();
                        if (empty($rti)){                        
                            return $this->sendResponse($rti, trans($locale.'_lang.DATA_NOT_FOUND'),404);
                        }                        
                        /*if (!empty($widget)) {
                            $datawidget['id'] = $widget->id; 
                            if($widget->title_hi == NULL){
                                $datawidget['title'] = $widget->title_en;  
                            }else{
                                $datawidget['title'] = $widget->title_hi; 
                            }
                            if($widget->description_hi == NULL){
                                $datawidget['description'] = $widget->description_en;  
                            }else{
                                $datawidget['description'] = $widget->description_hi;
                            } 
                            $datawidget['slug'] = $widget->slug;
                        }                        

                        $dataMenu = array();
                        $menu = DB::table('menus')->where('id', '42')->first();
                        if (!empty($menu)) {
                            $dataMenu['menu'] = $menu->name;
                            $dataMenu['menuitem'] = Menu::get(42);
                        }*/                        
                       
                        foreach ($rti as $key => $dir) {
                            
                            $dataHi[$key]['id'] = $dir->id;
                            if($dir->title_hi == NULL){
                               $dataHi[$key]['title'] = $dir->title_en;  
                            }else{
                                $dataHi[$key]['title'] = $dir->title_hi; 
                            }
                            $dataHi[$key]['order_on'] = $dir->order_on;  
                            $dataHi[$key]['file'] = $dir->file;
                            
                            /*if ($datawidget){
                                $dataHi['widget'] = $datawidget;  
                            }else{
                                $dataHi['widget'] = '';
                            } 
                            if ($dataMenu){
                                $dataHi['menu'] = $dataMenu;  
                            }else{
                                $dataHi['menu'] = '';
                            }*/
                        }
                        if ($dataHi){                        
                            return $this->sendResponse($dataHi, trans($locale.'_lang.DATA_FOUND'),200);
                        } else {
                            return $this->sendResponse($dataHi, trans($locale.'_lang.DATA_NOT_FOUND'),404);
                        }
                        break;
                    default: //GET Annual Report IN English
                        $data = array();
                        if (empty($rti)){                        
                            return $this->sendResponse($rti, trans($locale.'_lang.DATA_NOT_FOUND'),404);
                        }
                        /*$datawidget = array();  
                        $widget = Widget::where('id','31')->first();
                        if (!empty($widget)) {
                            $datawidget['id'] = $widget->id;                           
                            $datawidget['title'] = $widget->title_en; 
                            $datawidget['description'] = $widget->description_en;
                            $datawidget['slug'] = $widget->slug;
                        }                        

                        $dataMenu = array();
                        $menu = DB::table('menus')->where('id', '42')->first();
                        if (!empty($menu)) {
                            $dataMenu['menu'] = $menu->name;
                            $dataMenu['menuitem'] = Menu::get(42);
                        } */                       
                       
                        foreach ($rti as $key => $dir) {
                            
                            $data[$key]['id'] = $dir->id;
                            $data[$key]['title'] = $dir->title_en;  
                            $data[$key]['order_on'] = $dir->order_on;  
                            $data[$key]['file'] = $dir->file;
                            
                            /*if ($datawidget){
                                $data['widget'] = $datawidget;  
                            }else{
                                $data['widget'] = '';
                            } 
                            if ($dataMenu){
                                $data['menu'] = $dataMenu;  
                            }else{
                                $data['menu'] = '';
                            }*/
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
}
