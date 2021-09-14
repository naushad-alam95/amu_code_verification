<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\API\v1\BaseController as BaseController;
use App\Http\Controllers\Controller;
use Illuminate\Http\Response;
use Illuminate\Http\Request;
use App\NaacCriteria;
use App\NaacDepartment;
use App\NaacFile;
use Validator;
use Log;

class NaacController extends BaseController
{        

    /*
    *Get NAAC Criteria
    */
    public function getNaacCriteria(Request $request)
    {
        $validator = Validator::make($request->all(), [
                'pid' => 'required|integer',
            ]);
            
        if($validator->fails()){
            return $this->sendError('Validation Error.', $validator->errors(),422);      
        }
        
        $criteria = NaacCriteria::select('id','criteria_name','criteria_desc','pid','path')->where('pid',request()->pid)->get();

        if (is_null($criteria)){                        
            return $this->sendResponse($criteria, trans('en_lang.DATA_NOT_FOUND'),404);
        }
        return $this->sendResponse($criteria, trans('en_lang.DATA_FOUND'),200);
    } 

    /*
    *Get NAAC Department
    */
    public function getNaacDepartment(Request $request)
    {
        $validator = Validator::make($request->all(), [
                'criteria_id' => 'required|integer',
            ]);
            
        if($validator->fails()){
            return $this->sendError('Validation Error.', $validator->errors(),422);      
        }       
        $naacfile = NaacFile::where('criteria_id',request()->criteria_id)->pluck('naac_deparment_id');
        if ($naacfile->isEmpty()) {
            $department = NaacDepartment::select('id','department_name','code','ac_non_ac_id')->orderBy('department_name','ASC')->get();
        }else{
            $department = NaacDepartment::select('id','department_name','code','ac_non_ac_id')->whereNotIn('id', $naacfile)->orderBy('id','ASC')->get();
        }

        

        if (is_null($department)){                        
            return $this->sendResponse($department, trans('en_lang.DATA_NOT_FOUND'),404);
        }
        return $this->sendResponse($department, trans('en_lang.DATA_FOUND'),200);
    } 

    /*
    *Get NAAC Criteria Public list
    */

    public function getPublicNaac(Request $request)
    {
        $validator = Validator::make($request->all(), [
                'pid' => 'required|integer',
            ]);
            
        if($validator->fails()){
            return $this->sendError('Validation Error.', $validator->errors(),422);      
        }

        if(request()->pid == 1){
            $criteria = NaacCriteria::select('id','criteria_name','criteria_desc','pid','path')->where('pid',request()->pid)->orderBy('order_on','ASC')->with('getNaacFileOrderByTitle')->get();
        }else if (request()->pid == 167) {
            $criteria = NaacCriteria::select('id','criteria_name','criteria_desc','pid','path')->where('id',request()->pid)->orderBy('order_on','ASC')->with('getNaacFile')->get();
        }else if (request()->pid == 168) {
            $criteria = NaacCriteria::select('id','criteria_name','criteria_desc','pid','path')->where('id',request()->pid)->orderBy('order_on','ASC')->with('getNaacFile')->get();
        }else{
            $criteria = NaacCriteria::select('id','criteria_name','criteria_desc','pid','path')->where('pid',request()->pid)->orderBy('order_on','ASC')->with('getNaacFile')->get();
        }

        

        if (is_null($criteria)){                        
            return $this->sendResponse($criteria, trans('en_lang.DATA_NOT_FOUND'),404);
        }
        return $this->sendResponse($criteria, trans('en_lang.DATA_FOUND'),200);
    }


    public function getNaacById(Request $request)
    {
        $validator = Validator::make($request->all(), [
                'id' => 'required|integer',
            ]);
            
        if($validator->fails()){
            return $this->sendError('Validation Error.', $validator->errors(),422);      
        }

        if (request()->id == 167) {
            $criteria = NaacCriteria::select('id','criteria_name','criteria_desc')->where('id',request()->id)->first();
        }else{
            $criteria = NaacCriteria::select('id','criteria_name','criteria_desc')->where('id',request()->id)->first();
        }       
        

        if (is_null($criteria)){                        
            return $this->sendResponse($criteria, trans('en_lang.DATA_NOT_FOUND'),404);
        }
        return $this->sendResponse($criteria, trans('en_lang.DATA_FOUND'),200);
    }
}
