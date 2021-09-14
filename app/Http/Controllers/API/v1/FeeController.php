<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\API\v1\BaseController as BaseController;
use App\Http\Controllers\Controller;
use App\AcademicAndNonAcademic;
use Illuminate\Http\Response;
use Illuminate\Http\Request;
use App\FeeStructure;
use App\PaymentDue;
use App\UserLog;
use App\LogType;
use Validator;
use Log;

class FeeController extends BaseController
{


    /*
    *Get Cod Class Name
    */
    public function getCodClass(Request $request)
    {
        $data = FeeStructure::select('id','cod','codclass')->orderBy('codclass','ASC')->get();
        if (is_null($data)){                        
            return $this->sendResponse($data, trans('en_lang.DATA_NOT_FOUND'),404);
        }
        return $this->sendResponse($data, trans('en_lang.DATA_FOUND'),200);
    }

    /*
    *
    *Get Hall and Amu special center list
    *
    */
    public function getHallCenter(Request $request) {
        $data = AcademicAndNonAcademic::select('id','title_en as title','slug')->orderBy('sub_type','asc')->orderBy('title_en','asc')->whereIn('sub_type', [9,15])->where('status', '1')->get();

        if (is_null($data)){                        
            return $this->sendResponse($data, trans('en_lang.DATA_NOT_FOUND'),404);
        }
        return $this->sendResponse($data, trans('en_lang.DATA_FOUND'),200);       
    }

    /*
    *Get Data from payment form
    */
    public function getPaymentData(Request $request)
    {   
        $input = $request->all();
       // \Log::info($input);
        if ($request->isMethod('post')){
            $validator = Validator::make($request->all(), [
              'enrol'           => 'required',
              'sname'           => 'required',
              'coursename'      => 'required',
              'part'            => 'required', 
              'facno'           => 'required', 
              'pay_installment' => 'required', 
              'residential'     => 'required',  
              'mobile'          => 'required',    
              'email'           => 'required'
            ]);

            if($validator->fails()){
                return $this->sendError('Validation Error.', $validator->errors(),422);      
            }else{
                try {
                    $data = array();
                    //Get Total Fee.
                    if ($request->residential == 'Resident' || $request->pay_installment == '1') {
                        $feeData = FeeStructure::select('id','codclass','re100','codfaculty','faculty')->where('cod',$request->coursename)->first();
                        $data['total_pay']  = $feeData->re100.'.00';
                        

                    }elseif ($request->residential == 'Resident' || $request->pay_installment == '2') {
                        $feeData = FeeStructure::select('id','codclass','re501','codfaculty','faculty')->where('cod',$request->coursename)->first();
                        $data['total_pay']  = $feeData->re501.'.00';
                        

                    }
                    elseif ($request->residential == 'Resident' || $request->pay_installment == '3') {
                        $feeData = FeeStructure::select('id','codclass','re502','codfaculty','faculty')->where('cod',$request->coursename)->first();
                        $data['total_pay']  = $feeData->re502.'.00';
                        
                    }elseif ($request->residential == 'Non Resident' || $request->pay_installment == '1') {
                        $feeData = FeeStructure::select('id','codclass','ne100','codfaculty','faculty')->where('cod',$request->coursename)->first();
                        $data['total_pay']  = $feeData->ne100.'.00';

                    }elseif ($request->residential == 'Non Resident' || $request->pay_installment == '2') {
                        $feeData = FeeStructure::select('id','codclass','ne501','codfaculty','faculty')->where('cod',$request->coursename)->first();
                        $data['total_pay']  = $feeData->ne501.'.00';

                    }
                    elseif ($request->residential == 'Non Resident' || $request->pay_installment == '3') {
                        $feeData = FeeStructure::select('id','codclass','ne502','codfaculty','faculty')->where('cod',$request->coursename)->first();
                        $data['total_pay']  = $feeData->ne502.'.00';
                        

                    }

                    if ($feeData) {                        
                        $data['cod']        = $request->coursename;
                        $data['coursename'] = $feeData->codclass;
                        $data['codfaculty'] = $feeData->codfaculty;
                        $data['faculty']    = $feeData->faculty;
                    }

                    $data['enrol']           = $request->enrol;
                    $data['sname']           = $request->sname;
                    $data['part']            = $request->part;
                    $data['facno']           = $request->facno;
                    $data['pay_installment'] = $request->pay_installment;
                    $data['residential']     = $request->residential;
                    if ($request->hall) {
                        $data['hall']            = $request->hall;
                    }            
                    $data['mobile']          = $request->mobile;
                    $data['email']           = $request->email;
                    $data['rmrk']            = 'Payment not recieved';
                    if ($data) {                      

                       return $this->sendResponse($data, trans('en_lang.DATA_FOUND'),200);
                       
                    } else {
                        return $this->sendResponse($data, trans('en_lang.ACCESS_DENIED'), 401, false);
                    }
                }catch (Exception $e) {
                   return $this->sendError(trans($locale.'_lang.MODEL_NOT_FOUND'), $e->getMessage(), 401);
                }
            }
        }        
    }

    /*
    *Fee Payment Proceed form
    */
    public function paymentProceed(Request $request){
       
        $input = $request->all();
        //\Log::info($input);
        if ($request->isMethod('post')){
            $validator = Validator::make($request->all(), [
              'enrol'           => 'required',
              'sname'           => 'required',
              'coursename'      => 'required',
              'part'            => 'required', 
              'facno'           => 'required', 
              'pay_installment' => 'required', 
              'residential'     => 'required',  
              'mobile'          => 'required',    
              'email'           => 'required',
              'total_pay'       => 'required',
              'rmrk'            =>'required'
            ]);

            if($validator->fails()){
                return $this->sendError('Validation Error.', $validator->errors(),422);      
            }else{
                try {
                    $data = array();
                    $data['pay']             = $request->total_pay;
                    $data['enrol']           = $request->enrol;
                    $data['sname']           = $request->sname;
                    $data['coursename']      = $request->coursename;
                    $data['part']            = $request->part;
                    $data['facno']           = $request->facno;
                    $data['tpay']            = $request->pay_installment;
                    $data['residential']     = $request->residential;
                    $data['hall']            = $request->hall;
                    $data['mobile']          = $request->mobile;
                    $data['email']           = $request->email;
                    $data['rmrk']            = $request->rmrk;
                    $dta = PaymentDue::create($data);
                    if ($dta) {   

                    $str = 'AMU|'.$dta->id.'|NA|'.$request->total_pay.'|NA|NA|NA|INR|NA|R|amu|NA|NA|F|'.$request->enrol.'|'.$request->sname.'|'.$request->facno.'|'.$request->residential.'|'.$request->hall.'|'.$request->mobile.'|'.$request->email.'|https://beta1.amu.ac.in/payment-response';  
                        //\Log::info('string = '.$str);                      
                   
                        $checksum = hash_hmac('sha256',$str,env('BILLDESK_CHECKSUM_KEY'), false);
                        $checksum = strtoupper($checksum);

                        $checksumData = $str.'|'.$checksum;
                        $data['checksum'] = $checksumData;
                        //\Log::info('final string = '.$checksumData);      
                       return $this->sendResponse($data, trans('en_lang.DATA_CREATED'),201);
                       
                    } else {
                        return $this->sendResponse($data, trans('en_lang.ACCESS_DENIED'), 401, false);
                    }
                }catch (Exception $e) {
                   return $this->sendError(trans($locale.'_lang.MODEL_NOT_FOUND'), $e->getMessage(), 401);
                }
            }
        }
    }

    /*
    *Fee Payment response from Billdesk
    */
    public function paymentResponse(Request $request){
       
        $input = $request->all();
        //\Log::info($input);
    }

    /*
    *Get Student Payment History
    */
    public function getPayHistory(Request $request)
    {
        if ($request->isMethod('get')) {
            $validator = Validator::make($request->all(), [
                'lang' => 'required|in:en,hi,ur',
                'enrol' => 'required',
            ]);
            $input = $request->all();
            if ($validator->fails()) {
                return $this->sendError('Validation Error.', $validator->errors(), 422, false);
            } else {
                $data = PaymentDue::where('enrol',$request->enrol)->orderBy('created_at', 'DESC')->get();
                                        
                if (!empty($data)) {
                    return $this->sendResponse($data, trans('en_lang.DATA_FOUND'), 200);
                } else {
                    return $this->sendResponse($data, trans('en_lang.DATA_NOT_FOUND'),404);
                }
            }
        }else{
            return $this->sendResponse($data, trans('en_lang.MODEL_NOT_FOUND'), 404);
        }
        
    }

    /*
    *Download Student Payment History
    */
    public function downloadPDF(Request $request)
    {
        if ($request->isMethod('get')) {
            $validator = Validator::make($request->all(), [
                'lang' => 'required|in:en,hi,ur',
                'id' => 'required',
            ]);
            $input = $request->all();
            if ($validator->fails()) {
                return $this->sendError('Validation Error.', $validator->errors(), 422, false);
            } else {
                $data = PaymentDue::where('id',$request->id)->first();
                if ($data->tpay = '1') {
                    $data->tpay = '100% Payment';
                }elseif ($data->tpay = '2') {
                    $data->tpay = 'First Installment (50%)';
                }elseif ($data->tpay = '3') {
                    $data->tpay = 'Secound Installment (50%)';
                }                                      
                if (!empty($data)) {
                    return $this->sendResponse($data, trans('en_lang.DATA_FOUND'), 200);
                } else {
                    return $this->sendResponse($data, trans('en_lang.DATA_NOT_FOUND'),404);
                }
            }
        }else{
            return $this->sendResponse($data, trans('en_lang.MODEL_NOT_FOUND'), 404);
        }
        
    }
}
