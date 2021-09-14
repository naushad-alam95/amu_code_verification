<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\API\v1\BaseController as BaseController;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Harimayco\Menu\Facades\Menu;
use App\AnnualReport;
use App\Widget;
use Validator;
use Auth;
use DB;

class AnnualReportController extends BaseController
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
                $annualReport = AnnualReport::where('status','1')->orderBy('order_on','asc')->orderBy('updated_at','desc')->with('getFile')->get();
               /* $datawidget = array();  
                $widget = Widget::where('id','31')->first();*/
                switch ($locale) {
                    case "ur": //GET Annual Report IN Urdu
                        $dataUR = array();
                        if (empty($annualReport)){                        
                            return $this->sendResponse($annualReport, trans($locale.'_lang.DATA_NOT_FOUND'),404);
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
                        }  */                      
                       
                        foreach ($annualReport as $key => $report) {
                            
                            $dataUR[$key]['id'] = $report->id;
                            if($report->title_ur == NULL){
                               $dataHi[$key]['title'] = $report->title_en;  
                            }else{
                                $dataUR[$key]['title'] = $report->title_ur; 
                            }
                            $dataUR[$key]['order_on'] = $report->order_on;  
                            $datafile = array();
                            if (!empty($report->getFile)) {
                                foreach ($report->getFile as $k => $value) {
                                    $datafile[$k]['id'] = $value->id;
                                    $datafile[$k]['file_name'] = $value->file_name;
                                    $datafile[$k]['file'] = $value->file;
                                }
                                if ($datafile){
                                    $dataUR[$key]['file'] = $datafile;  
                                }else{
                                    $dataUR[$key]['file'] = '';
                                }
                            }
                            
                           /* if ($datawidget){
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
                        if (empty($annualReport)){                        
                            return $this->sendResponse($annualReport, trans($locale.'_lang.DATA_NOT_FOUND'),404);
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
                        }*/                        

                       /* $dataMenu = array();
                        $menu = DB::table('menus')->where('id', '42')->first();
                        if (!empty($menu)) {
                            $dataMenu['menu'] = $menu->name;
                            $dataMenu['menuitem'] = Menu::get(42);
                        } */                       
                       
                        foreach ($annualReport as $key => $report) {
                            
                            $dataHi[$key]['id'] = $report->id;
                            if($report->title_hi == NULL){
                               $dataHi[$key]['title'] = $report->title_en;  
                            }else{
                                $dataHi[$key]['title'] = $report->title_hi; 
                            }
                            $dataHi[$key]['order_on'] = $report->order_on;  
                            $datafile = array();
                            if (!empty($report->getFile)) {
                                foreach ($report->getFile as $k => $value) {
                                    $datafile[$k]['id'] = $value->id;
                                    $datafile[$k]['file_name'] = $value->file_name;
                                    $datafile[$k]['file'] = $value->file;
                                }
                                if ($datafile){
                                    $dataHi[$key]['file'] = $datafile;  
                                }else{
                                    $dataHi[$key]['file'] = '';
                                }
                            }
                            
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
                        if (empty($annualReport)){                        
                            return $this->sendResponse($annualReport, trans($locale.'_lang.DATA_NOT_FOUND'),404);
                        }
                        /*$datawidget = array();  
                        $widget = Widget::where('id','31')->first();
                        if (!empty($widget)) {
                            $datawidget['id'] = $widget->id;                           
                            $datawidget['title'] = $widget->title_en; 
                            $datawidget['description'] = $widget->description_en;
                            $datawidget['slug'] = $widget->slug;
                        }   */                     

                       /* $dataMenu = array();
                        $menu = DB::table('menus')->where('id', '42')->first();
                        if (!empty($menu)) {
                            $dataMenu['menu'] = $menu->name;
                            $dataMenu['menuitem'] = Menu::get(42);
                        }   */                     
                       
                        foreach ($annualReport as $key => $report) {
                            
                            $data[$key]['id'] = $report->id;
                            $data[$key]['title'] = $report->title_en;  
                            $data[$key]['order_on'] = $report->order_on;  
                            $datafile = array();
                            if (!empty($report->getFile)) {
                                foreach ($report->getFile as $k => $value) {
                                    $datafile[$k]['id'] = $value->id;
                                    $datafile[$k]['file_name'] = $value->file_name;
                                    $datafile[$k]['file'] = $value->file;
                                }
                                if ($datafile){
                                    $data[$key]['file'] = $datafile;  
                                }else{
                                    $data[$key]['file'] = '';
                                }
                               /* if ($datawidget){
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
