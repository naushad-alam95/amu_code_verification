<?php

namespace App\Http\Controllers\API\v1;

use App\Http\Controllers\API\v1\BaseController as BaseController;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
Use App\TenderCategory;
use App\Tender;
use Validator;
use Auth;
use Carbon;
use DB;

class TenderController extends BaseController
{
    
    /*
    *Get Tender Category
    */

    public function getTenderCategory(Request $request)
    {
        $tender_category = TenderCategory::select('id','name')->orderby('name','ASC')->get();

        if (is_null($tender_category)){                        
            return $this->sendResponse($tender_category, trans('en_lang.DATA_NOT_FOUND'),404);
        }
        return $this->sendResponse($tender_category, trans('en_lang.DATA_FOUND'),200);
    }

    /*
    *Get Tender List
    */
    public function tenderList(Request $request) {
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
            $type = TenderCategory::select('id','name')->orderby('name','ASC')->get();
            try {
                switch ($locale) {
                    case "ur": //GET Menu IN Urdu
                        $dataUR = array();     
                        if(isset($request->month) && isset($request->year)){
                           $tenders = Tender::where('approval_status','Approved')->whereMonth('created_at', $request->month)->whereYear('created_at', $request->year)->orderBy('created_at','DESC')->when(request()->type, function ($query) {
                              $query->where('tender_type', '=' , request()->type);
                              })->when(request()->keyword, function ($query) {
                              $query->where('description','LIKE', '%'.request()->keyword.'%');
                              })->whereIn('tender_type',[1,2,3,4,5])->paginate(env('ITEM_PER_PAGE'));
                        }else{
                           $tenders = Tender::where('approval_status','Approved')->orderBy('created_at', 'DESC')->when(request()->type, function ($query) {
                              $query->where('tender_type', '=' , request()->type);
                              })->when(request()->keyword, function ($query) {
                              $query->where('description','LIKE', '%'.request()->keyword.'%');
                              })->whereIn('tender_type',[1,2,3,4,5])->paginate(env('ITEM_PER_PAGE'));
                        }
                        if (empty($tenders)){                        
                            return $this->sendResponse($tenders, trans($locale.'_lang.DATA_NOT_FOUND'),404);
                        }
                        $dataUR['data'] = $tenders;
                        $dataUR['type'] = $type;
                        if ($dataUR){                        
                            return $this->sendResponse($dataUR, trans($locale.'_lang.DATA_FOUND'),200);
                        } else {
                            return $this->sendResponse($dataUR, trans($locale.'_lang.DATA_NOT_FOUND'),404);
                        }
                        break;
                    case "hi": //GET Menu In Hindi
                        $dataHI = array();
                        if(isset($request->month) && isset($request->year)){
                           $tenders = Tender::where('approval_status','Approved')->whereMonth('created_at', $request->month)->whereYear('created_at', $request->year)->orderBy('created_at','DESC')->when(request()->type, function ($query) {
                              $query->where('tender_type', '=' , request()->type);
                              })->when(request()->keyword, function ($query) {
                              $query->where('description','LIKE', '%'.request()->keyword.'%');
                              })->whereIn('tender_type',[1,2,3,4,5])->paginate(env('ITEM_PER_PAGE'));
                        }else{
                           $tenders = Tender::where('approval_status','Approved')->orderBy('created_at', 'DESC')->when(request()->type, function ($query) {
                              $query->where('tender_type', '=' , request()->type);
                              })->when(request()->keyword, function ($query) {
                              $query->where('description','LIKE', '%'.request()->keyword.'%');
                              })->whereIn('tender_type',[1,2,3,4,5])->paginate(env('ITEM_PER_PAGE'));
                        }
                        if (empty($tenders)){                        
                            return $this->sendResponse($tenders, trans($locale.'_lang.DATA_NOT_FOUND'),404);
                        }
                        $dataHI['data'] = $tenders;
                        $dataHI['type'] = $type;
                        if ($dataHI){                        
                            return $this->sendResponse($dataHI, trans($locale.'_lang.DATA_FOUND'),200);
                        } else {
                            return $this->sendResponse($dataHI, trans($locale.'_lang.DATA_NOT_FOUND'),404);
                        }
                        
                        break;
                    default: //GET slider IN English
                        $data =array();
                        if(isset($request->month) && isset($request->year)){
                           $tenders = Tender::where('approval_status','Approved')->whereMonth('created_at', $request->month)->whereYear('created_at', $request->year)->orderBy('created_at','DESC')->when(request()->type, function ($query) {
                              $query->where('tender_type', '=' , request()->type);
                              })->when(request()->keyword, function ($query) {
                              $query->where('description','LIKE', '%'.request()->keyword.'%');
                              })->whereIn('tender_type',[1,2,3,4,5])->paginate(env('ITEM_PER_PAGE'));
                        }else{
                           $tenders = Tender::where('approval_status','Approved')->orderBy('created_at', 'DESC')->when(request()->type, function ($query) {
                              $query->where('tender_type', '=' , request()->type);
                              })->when(request()->keyword, function ($query) {
                              $query->where('description','LIKE', '%'.request()->keyword.'%');
                              })->whereIn('tender_type',[1,2,3,4,5])->paginate(env('ITEM_PER_PAGE'));
                        }
                        if (empty($tenders)){                        
                            return $this->sendResponse($tenders, trans($locale.'_lang.DATA_NOT_FOUND'),404);
                        }
                        $data['data'] = $tenders;
                        $data['type'] = $type;
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

    public function tenderArchive(Request $request) {
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
                        $tender = Tender::selectRaw('year(created_at) year, count(*) published')->groupBy('year')->orderByRaw('min(created_at) DESC')->get();
                        foreach ($tender as $key => $n) {
                            $archiveMonth = Tender::selectRaw('month(created_at) month, count(*) published')->whereYear('created_at',$n->year)->groupBy('month')->orderByRaw('min(created_at) DESC')->whereIn('tender_type',[1,2,3,4,5])->get();
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
                        $tender = Tender::selectRaw('year(created_at) year, count(*) published')->groupBy('year')->orderByRaw('min(created_at) DESC')->get();
                        foreach ($tender as $key => $n) {
                            $archiveMonth = Tender::selectRaw('month(created_at) month, count(*) published')->whereYear('created_at',$n->year)->groupBy('month')->orderByRaw('min(created_at) DESC')->whereIn('tender_type',[1,2,3,4,5])->get();
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
                        $tender = Tender::selectRaw('year(created_at) year, count(*) published')->groupBy('year')->orderByRaw('min(created_at) DESC')->get();
                        foreach ($tender as $key => $n) {
                            $archiveMonth = Tender::selectRaw('month(created_at) month, count(*) published')->whereYear('created_at',$n->year)->groupBy('month')->orderByRaw('min(created_at) DESC')->whereIn('tender_type',[1,2,3,4,5])->get();
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


    /*
    *Get Campus Notice list
    */
    public function campusNotice(Request $request) {
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
            //$type = TenderCategory::select('id','name')->orderby('name','ASC')->get();
            try {
                $campusNotice = Tender::select('id','description as title','tender_type','file','created_at')->where('tender_type',15)->orderBy('created_at','DESC')
                    ->when(request()->search, function ($query) {
                        $query->where('description', 'LIKE', '%'.request()->search.'%');
                    })->paginate(env('ITEM_PER_PAGE'));
                if ($campusNotice){                        
                    return $this->sendResponse($campusNotice, trans($locale.'_lang.DATA_FOUND'),200);
                } else {
                    return $this->sendResponse($campusNotice, trans($locale.'_lang.DATA_NOT_FOUND'),404);
                }
            } catch (Exception $e) {
               return $this->sendError(trans($locale.'_lang.MODEL_NOT_FOUND'), $e->getMessage(), 404);
            }
        }
    }

}
