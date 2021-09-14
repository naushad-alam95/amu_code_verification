<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\API\v1\BaseController as BaseController;
use App\Http\Controllers\Controller;
use Harimayco\Menu\Facades\Menu;
use Illuminate\Http\Request;
use App\CMS;
use App\Widget;
use Validator;
use Auth;
use DB;

class CMSController extends BaseController
{

    public function index(Request $request) {
        $validator = Validator::make($request->all(), [
            'lang' => 'required|in:en,hi,ur',
            'slug' => 'required',
        ]);
        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors(),422);
        } else {

            $slug  = $request->slug;
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
                        $cms = CMS::where('slug',$slug)->with('getParent')->with('getCMSMenu')->with('getCMSWidget')->first();
                        if (empty($cms)){                        
                            return $this->sendResponse($cms, trans($locale.'_lang.DATA_NOT_FOUND'),404);
                        }
                        $dataUR['id'] = $cms->id;
                        if($cms->title_ur == NULL){
                            $dataUR['title'] = $cms->title_en;  
                        }else{
                            $dataUR['title'] = $cms->title_ur; 
                        }
                        if($cms->description_ur == NULL){
                            $dataUR['description'] = $cms->description_en;  
                        }else{
                            $dataUR['description'] = $cms->description_ur;
                        }
                        $dataUR['seo_title'] = $cms->seo_title;
                        $dataUR['seo_description'] = $cms->seo_description;
                        $dataUR['slug'] = $cms->slug;
                        $dataUR['image'] = $cms->image;
                        $dataUR['last_update'] = date("F d, Y", strtotime($cms->updated_at));
                        $datawidget = array();
                        foreach ($cms->getCMSWidget as $k => $value) {                           
                            $widget = Widget::where('id',$value->widget_id)->first();                           
                            $datawidget[$k]['id'] = $widget->id;
                            if($widget->title_ur == NULL){
                                $datawidget[$k]['title'] = $widget->title_en;  
                            }else{
                                $datawidget[$k]['title'] = $widget->title_ur; 
                            }
                            if($widget->description_ur == NULL){
                                $datawidget[$k]['description'] = $widget->description_en;  
                            }else{
                                $datawidget[$k]['description'] = $widget->description_ur;
                            }
                            $datawidget[$k]['slug'] = $widget->slug;
                        }
                        if ($datawidget){
                            $dataUR['widget'] = $datawidget;  
                        }else{
                            $dataUR['widget'] = '';
                        }
                        $dataMenu = array();
                        foreach ($cms->getCMSMenu as $key => $value) {
                            $menu = DB::table('menus')->where('id', $value->menu_ur_id)->first();
                            $dataMenu[$key]['menu'] = $menu->name;
                            $dataMenu[$key]['menuitem'] = Menu::get($value->menu_ur_id);
                        }
                        if ($dataMenu){
                            $dataUR['menu'] = $dataMenu;  
                        }else{
                            $dataUR['menu'] = '';
                        }

                        if ($dataUR){                        
                            return $this->sendResponse($dataUR, trans($locale.'_lang.DATA_FOUND'),200);
                        } else {
                            return $this->sendResponse($dataUR, trans($locale.'_lang.DATA_NOT_FOUND'),404);
                        }

                        break;
                    case "hi": //GET Menu In Hindi
                        $dataHI = array();  
                        $cms = CMS::where('slug',$slug)->with('getParent')->with('getCMSMenu')->with('getCMSWidget')->first();
                        if (empty($cms)){                        
                            return $this->sendResponse($cms, trans($locale.'_lang.DATA_NOT_FOUND'),404);
                        }
                        $dataHI['id'] = $cms->id;
                        if($cms->title_hi == NULL){
                            $dataHI['title'] = $cms->title_en;  
                        }else{
                            $dataHI['title'] = $cms->title_hi; 
                        }
                        if($cms->description_hi == NULL){
                            $dataHI['description'] = $cms->description_en;  
                        }else{
                            $dataHI['description'] = $cms->description_hi;
                        }
                        $dataHI['seo_title'] = $cms->seo_title;
                        $dataHI['seo_description'] = $cms->seo_description;
                        $dataHI['slug'] = $cms->slug;
                        $dataHI['image'] = $cms->image;
                        $dataHI['last_update'] = date("F d, Y", strtotime($cms->updated_at));
                        $datawidget = array();
                        foreach ($cms->getCMSWidget as $k => $value) {                           
                            $widget = Widget::where('id',$value->widget_id)->first();                           
                            $datawidget[$k]['id'] = $widget->id;
                            if($widget->title_hi == NULL){
                                $datawidget[$k]['title'] = $widget->title_en;  
                            }else{
                                $datawidget[$k]['title'] = $widget->title_hi; 
                            }
                            if($widget->description_hi == NULL){
                                $datawidget[$k]['description'] = $widget->description_en;  
                            }else{
                                $datawidget[$k]['description'] = $widget->description_hi;
                            }
                            $datawidget[$k]['slug'] = $widget->slug;
                        }
                        if ($datawidget){
                            $dataHI['widget'] = $datawidget;  
                        }else{
                            $dataHI['widget'] = '';
                        }
                        $dataMenu = array();
                        foreach ($cms->getCMSMenu as $key => $value) {
                            $menu = DB::table('menus')->where('id', $value->menu_hi_id)->first();
                            $dataMenu[$key]['menu'] = $menu->name;
                            $dataMenu[$key]['menuitem'] = Menu::get($value->menu_hi_id);
                        }
                        if ($dataMenu){
                            $dataHI['menu'] = $dataMenu;  
                        }else{
                            $dataHI['menu'] = '';
                        }
                        if ($dataHI){                        
                            return $this->sendResponse($dataHI, trans($locale.'_lang.DATA_FOUND'),200);
                        } else {
                            return $this->sendResponse($dataHI, trans($locale.'_lang.DATA_NOT_FOUND'),404);
                        }
                        
                        break;
                    default: //GET slider IN English
                        $data = array();  
                        $cms = CMS::where('slug',$slug)->with('getParent')->with('getCMSMenu')->with('getCMSWidget')->first();
                        if (empty($cms)){                        
                            return $this->sendResponse($cms, trans($locale.'_lang.DATA_NOT_FOUND'),404);
                        }
                        $data['id'] = $cms->id;
                        $data['title'] = $cms->title_en;  
                        $data['description'] = $cms->description_en;
                        $data['seo_title'] = $cms->seo_title;
                        $data['seo_description'] = $cms->seo_description;
                        $data['slug'] = $cms->slug;
                        $data['image'] = $cms->image;
                        $data['last_update'] = date("F d, Y", strtotime($cms->updated_at));
                        $datawidget = array();
                        foreach ($cms->getCMSWidget as $k => $value) {                           
                            $widget = Widget::where('id',$value->widget_id)->first();                           
                            $datawidget[$k]['id'] = $widget->id;
                            $datawidget[$k]['title'] = $widget->title_en;  
                            $datawidget[$k]['description'] = $widget->description_en;                            
                            $datawidget[$k]['slug'] = $widget->slug;
                        }
                        if ($datawidget){
                            $data['widget'] = $datawidget;  
                        }else{
                            $data['widget'] = '';
                        }
                        $dataMenu = array();
                        foreach ($cms->getCMSMenu as $key => $value) {
                            $menu = DB::table('menus')->where('id', $value->menu_en_id)->first();
                            $dataMenu[$key]['menu'] = $menu->name;
                            $dataMenu[$key]['menuitem'] = Menu::get($value->menu_en_id);
                        }
                        if ($dataMenu){
                            $data['menu'] = $dataMenu;  
                        }else{
                            $data['menu'] = '';
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
