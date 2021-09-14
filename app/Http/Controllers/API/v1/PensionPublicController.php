<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\API\v1\BaseController as BaseController;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\RelatedLinkData;
use App\MouFile;
use App\Pension;
use App\UserLog;
use App\LogType;
use Validator;
use Log;

class PensionPublicController extends BaseController
{


    /*
    *Get Pension Status
    */
    public function getStatus(Request $request) {
        $validator = Validator::make($request->all(), [
            'lang' => 'required|in:en,hi,ur',
            'eid' => 'required',
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
                $pension = Pension::where('eid',$request->eid)->orderBy('created_at','asc')->get();
                if ($pension){                        
                    return $this->sendResponse($pension, trans($locale.'_lang.DATA_FOUND'),200);
                } else {
                    return $this->sendResponse($pension, trans($locale.'_lang.DATA_NOT_FOUND'),404);
                }
            } catch (Exception $e) {
               return $this->sendError(trans($locale.'_lang.MODEL_NOT_FOUND'), $e->getMessage(), 401);
            }
        }
    }
    
    /*
    *Download Pension Status
    */
    public function downloadPensionStatus(Request $request) {
        $validator = Validator::make($request->all(), [
            'lang' => 'required|in:en,hi,ur',
            'eid' => 'required',
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
                $pension = Pension::select('id','eid','order_file')->where('eid',$request->eid)->where('order_file','!=','')->first();
                if ($pension){                        
                    return $this->sendResponse($pension, trans($locale.'_lang.DATA_FOUND'),200);
                } else {
                    return $this->sendResponse($pension, trans($locale.'_lang.DATA_NOT_FOUND'),404);
                }
            } catch (Exception $e) {
               return $this->sendError(trans($locale.'_lang.MODEL_NOT_FOUND'), $e->getMessage(), 401);
            }
        }
    }

    /*
    *Download MOU File
    */
    public function downloadedMouFile(Request $request)
    {
        $input = $request->all();
        if ($request->isMethod('post')){
            $validator = Validator::make($request->all(), [
                'downloaded_by' => 'required',
                'file_id'       => 'required',
                'path'          => 'required',
                'user_type'     => 'required',
            ]);
            
            if($validator->fails()){
                return $this->sendError('Validation Error.', $validator->errors(),422);      
            }else{
                try {
                    $dta = array();
                    $dta['downloaded_by'] = $request->downloaded_by;
                    $dta['file_id']       = $request->file_id;
                    $dta['ip']            = $request->ip();
                    $dta['path']          = $request->path;
                    $dta['user_type']     = $request->user_type;
                    $data = MouFile::create($dta);

                    if ($data) {
                        $fileData = RelatedLinkData::select('id','file')->where('id',$request->file_id)->first();

                        //Get PDF file Path
                        $filePath= $fileData->file;

                        //return response()->download(storage_path("app/public/{$filePath}"));
                        return $this->sendResponse($fileData, trans('en_lang.DATA_FOUND'), 200);
                       
                    } else {
                        return $this->sendResponse($data, trans('en_lang.ACCESS_DENIED'), 401, false);
                    }
                }catch (Exception $e) {
                   return $this->sendError(trans($locale.'_lang.MODEL_NOT_FOUND'), $e->getMessage(), 401);
                }
            }
        }        
    }
}
