<?php

namespace App\Http\Controllers\API\v1;

use App\Http\Controllers\API\v1\BaseController as BaseController;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\AcademicAndNonAcademic;
use App\AlumniUser;
use App\Pension;
use App\LogType;
use App\UserLog;
use Validator;
use Auth;
use Log;

class PensionAdminController extends BaseController
{
    public function __construct()
    {
        $this->middleware('auth:api');
    } 
    // Get Faculty List
    public function getPensionList(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'slug' => 'required',
            'role' => 'required',
            'type' => 'required',
            'path' => 'required',
        ]);
        $input = $request->all();
        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors(), 422, false);
        } else {
            $data = array();
            $academic =  AcademicAndNonAcademic::where('slug', request()->slug)->first();
            $ac_non_ac_id = $academic->id;
            $user_id = $request->user()->id;
            $role = request()->role;
            if (checkUserRole($ac_non_ac_id, $user_id, $role)) {

                $data = Pension::orderBy('created_at', 'ASC')->when(request()->eid, function ($query) {$query->where('eid',request()->eid);
                })->paginate(env('ITEM_PER_PAGE'));
                                        
                if (!empty($data)) {
                    return $this->sendResponse($data, trans('en_lang.DATA_FOUND'), 200);
                } else {
                    return $this->sendResponse($data, trans('en_lang.DATA_NOT_FOUND'), 204);
                }
            } else {
                return $this->sendResponse($data, trans('en_lang.ACCESS_DENIED'), 401, false);
            }
        }
    }

    // Store Pension Status data
    public function storePensionStatus(Request $request)
    {

        $input = $request->all();
        $validator = Validator::make($request->all(), [
            'slug' => 'required',
            'role' => 'required',
            'type' => 'required',
            'path' => 'required',
            'eid' => 'required',
            'name' => 'required',
            'deptt' => 'required',
            'desig' => 'required',
            'dob' => 'required',
            'dor' => 'required',
            'yr' => 'required',
            'status' => 'required',
        ]);
        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors(), 422, false);
        } else {
            $data = array();
            $academic =  AcademicAndNonAcademic::where('slug', request()->slug)->first();
            $ac_non_ac_id = $academic->id;
            $user_id      =  $request->user()->id;
            $role         = request()->role;
            if (checkUserRole($ac_non_ac_id, $user_id, $role)) {

                if (isset($request->id) != null) {

                    if($request->hasFile('order_file')){

                        $validator = Validator::make($request->all(), [

                            'order_file'  => 'required|mimes:pdf'
                        ]);

                        if ($validator->fails()) {
                            return $this->sendError('Validation Error.', $validator->errors(), 422, false);
                        }else{

                            $uploaded_file = $request->eid.'.'.$request->file('order_file')->guessExtension();
                            $data['order_file'] = $request->file('order_file')->storeAs('/file/pension_order',$uploaded_file,'public');
                            $data['pension_order_status'] = '1';
                        }
                    }

                    if ($request->status) {
                        $data['status'] = $request->status;
                    }                       
                    
                    $data = Pension::where('id', request()->id)->update($data);
                    if ($data) {
                        $logtype = LogType::select('id')->where('type', '=', 'pension')->first();
                        if (is_null($logtype)) {
                            return $this->sendResponse($logtype, 'Log type data not found', 404);
                        } else {
                            $dta = array();
                            $dta['log_type_id'] = $logtype['id'];
                            $dta['user_id'] = $user_id;
                            $dta['ip'] = $request->ip();
                            $dta['action'] = $request->status;
                            $dta['ac_non_ac_id'] = $ac_non_ac_id;
                            $dta['related_link_id'] = $request->path;
                            UserLogSave($dta);
                        }
                        return $this->sendResponse($data, trans('en_lang.DATA_UPDATED'), 200);
                    } else {
                        return $this->sendResponse($data, 'Some thing went wrong', 204, false);
                    }
                } else {

                    $data['eid']         = $request->eid;
                    $data['name']        = $request->name;
                    $data['deptt']       = $request->deptt;
                    $data['desig']       = $request->desig;
                    $data['dob']         = $request->dob;
                    $data['dor']         = $request->dor;
                    $data['yr']          = $request->yr;
                    $data['status']      = $request->status;
                    
                    $data = Pension::create($data);

                    if ($data) {

                        $logtype = LogType::select('id')->where('type', '=', 'pension')->first();
                        if (is_null($logtype)) {
                            return $this->sendResponse($logtype, 'Log type data not found', 404);
                        } else {
                            $dta = array();
                            $dta['log_type_id'] = $logtype['id'];
                            $dta['user_id'] = $user_id;
                            $dta['ip'] = $request->ip();
                            $dta['action'] = $request->status;
                            $dta['ac_non_ac_id'] = $ac_non_ac_id;
                            $dta['related_link_id'] = $request->path;
                            UserLogSave($dta);
                        }
                        return $this->sendResponse($data, trans('en_lang.DATA_CREATED'), 201);
                    } else {
                        return $this->sendResponse($data, 'Some thing went wrong', 204, false);
                    }
                }
            } else {
                return $this->sendResponse($data, trans('en_lang.ACCESS_DENIED'), 401, false);
            }
        }
    }

    // Get Pension Single Data
    public function getSinglePension(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'slug' => 'required',
            'role' => 'required',
            'type' => 'required',
            'id' => 'required',
        ]);
        $input = $request->all();
        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors(), 422, false);
        } else {
            $data = array();
            $academic =  AcademicAndNonAcademic::where('slug', request()->slug)->first();
            $ac_non_ac_id = $academic->id;
            $user_id = $request->user()->id;
            $role = request()->role;
            if (checkUserRole($ac_non_ac_id, $user_id, $role)) {

                $data = Pension::where('id', request()->id)->first();
                
                if ($data) {
                    return $this->sendResponse($data, trans('en_lang.DATA_FOUND'), 200);
                } else {
                    return $this->sendResponse($data, trans('en_lang.DATA_NOT_FOUND'), 204);
                }
            } else {
                return $this->sendResponse($data, trans('en_lang.ACCESS_DENIED'), 401, false);
            }
        }
    } 

    // Delete Pension Data
    public function deletePension(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required',
            'type' => 'required',
            'role' => 'required',
            'slug' => 'required',
            'path' => 'required',
        ]);
        $input = $request->all();
        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors(), 422, false);
        } else {
            $academic =  AcademicAndNonAcademic::where('slug', request()->slug)->with('getHeadType')->first();
            $ac_non_ac_id = $academic->id;
            $user_id = $request->user()->id;
            $role = request()->role;
            $head = $academic->getHeadType->role_type;
            if (checkUserRole($ac_non_ac_id, $user_id, $role)) {
                if ($role == $head || $role == "Approver") {                   

                    if (Pension::where('id', request()->id)->delete()) {

                        $logtype = LogType::select('id')->where('type', '=', 'pension')->first();

                        if (is_null($logtype)) {

                            return $this->sendResponse($logtype, 'Log type data not found', 404);
                        } else {

                            $dta = array();
                            $dta['log_type_id'] = $logtype['id'];
                            $dta['user_id'] = $user_id;
                            $dta['ip'] = $request->ip();
                            $dta['action'] = 'Pension ' . request()->id . ' data deleted';
                            $dta['ac_non_ac_id'] = $ac_non_ac_id;
                            $dta['related_link_id'] = $request->path;
                            UserLogSave($dta);
                        }

                        return $this->sendResponse('true', 'Pension Deleted', 200);

                    } else {

                        return $this->sendResponse('false', 'Something went Wrong!', 404);
                    }
                } else {
                    return $this->sendResponse("false", trans('en_lang.ACCESS_DENIED'), 401);
                }
            } else {
                return $this->sendResponse("false", trans('en_lang.ACCESS_DENIED'), 401);
            }
        }
    }

}
