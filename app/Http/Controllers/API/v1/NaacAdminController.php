<?php

namespace App\Http\Controllers\API\v1;

use App\Http\Controllers\API\v1\BaseController as BaseController;
use Illuminate\Http\Request;
use App\Exports\BulkExport;
use Maatwebsite\Excel\Facades\Excel;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use App\AcademicAndNonAcademic;
use App\NaacCriteria;
use App\NaacDepartment;
use App\NaacFile;
use App\LogType; 
use Validator;
use Auth;
use File;

class NaacAdminController extends BaseController
{
    public function __construct()
    {
        $this->middleware('auth:api');
    }


    //Store Naac Criteria!
    public function storeNaacCriteria(Request $request)
    {

        $input = $request->all();
        $validator = Validator::make($request->all(), [
            'slug' => 'required',
            'role' => 'required',
            'type' => 'required',
            'path' => 'required',
            'approval_status' => 'required',
            'criteria_id' => 'required'
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
                $criteria = NaacCriteria::select('id','criteria_name','pid','path')->where('id',request()->criteria_id)->first();

                if (is_null($criteria)) {
                    return $this->sendResponse('false', 'Something went Wrong!', 404);
                }

                if ($criteria->pid == 1 ) {
                    $dep = NaacDepartment::select('id','department_name','code','ac_non_ac_id')->where('id',request()->department)->first();
                    $path = $criteria->path.$dep->code.'/'.$dep->code.'_'.$criteria->criteria_name;
                    $naacfile = NaacFile::where('criteria_id',request()->criteria_id)->where('naac_deparment_id',request()->department)->first();

                    $data['naac_deparment_id'] = $request->department;
                    $data['file_name']      = $dep->department_name;

                }elseif($criteria->id == 167){
                    $path = $criteria->path;
                    $naacfile = NaacFile::where('criteria_id',request()->criteria_id)->first();
                }else{
                    $validator = Validator::make($input, [
                        'file_name' => 'required',
                        'title' => 'required',
                        'file' => 'required'
                    ]);                

                    if($validator->fails()){
                       return $this->sendError('Validation Error.', $validator->errors(),422);     
                    }

                    $path = $criteria->path.trim($request->file_name);

                    if($request->hasFile('file')) {
                        $uploaded_file = $path.'.'.$request->file('file')->guessExtension();

                        $file_path = $request->file('file')->storeAs('file/naac',$uploaded_file,'public');
                    }

                    $data['criteria_id']    = $request->criteria_id;
                    $data['file_name']      = $request->title;
                    $data['file_path']      = $file_path;
                    $data['approval_status']= $request->approval_status;
                    $data = NaacFile::create($data);

                    if ($data) {
                        $logtype = LogType::select('id')->where('type', '=', 'naac')->first();
                        if (is_null($logtype)) {
                            return $this->sendResponse($logtype, 'Log type data not found', 404);
                        } else {
                            $dta = array();
                            $dta['log_type_id'] = $logtype['id'];
                            $dta['user_id'] = $user_id;
                            $dta['ip'] = $request->ip();
                            $dta['action'] = $request->approval_status;
                            $dta['action_summary'] = 'Uploaded naac file in criteria '.$criteria->criteria_name ;
                            $dta['ac_non_ac_id'] = $ac_non_ac_id;
                            $dta['related_link_id'] = 'naac-panal';
                            UserLogSave($dta);
                        }
                        return $this->sendResponse($data, trans('en_lang.DATA_CREATED'), 201);
                    } else {
                        return $this->sendResponse($data, 'Some thing went wrong', 204, false);
                    }
                }
                
                if ($naacfile) {
                    
                    if($request->hasFile('file')) {
                        $files = Storage::disk('public')->exists($naacfile->file_path);

                        if($files == true) {
                            DeleteOldPicture($naacfile->file_path);
                        }

                        $uploaded_file = $path.'.'.$request->file('file')->guessExtension();
                        $file_path = $request->file('file')->storeAs('file/naac',$uploaded_file,'public'); 
                    }

                    
                    if($request->hasFile('file')) {
                       $data['file_path']      = $file_path; 
                    }                    
                    $data['approval_status']= $request->approval_status;
                    $data = NaacFile::where('id',$naacfile->id)->update($data);


                    $logtype = LogType::select('id')->where('type', '=', 'naac')->first();
                    if (is_null($logtype)) {
                        return $this->sendResponse($logtype, 'Log type data not found', 404);
                    } else {
                        $dta = array();
                        $dta['log_type_id'] = $logtype['id'];
                        $dta['user_id'] = $user_id;
                        $dta['ip'] = $request->ip();
                        $dta['action'] = $request->approval_status;
                        $dta['action_summary'] = 'Uploaded naac file in criteria '.$criteria->criteria_name ;
                        $dta['ac_non_ac_id'] = $ac_non_ac_id;
                        $dta['related_link_id'] = 'naac-panal';
                        UserLogSave($dta);
                    }
                    return $this->sendResponse($data, trans('en_lang.DATA_UPDATED'), 200);
                    
                }else{
                    $validator = Validator::make($input, [
                        'file' => 'required'
                    ]);                

                    if($validator->fails()){
                       return $this->sendError('Validation Error.', $validator->errors(),422);     
                    }

                    if($request->hasFile('file')) {
                        $uploaded_file = $path.'.'.$request->file('file')->guessExtension();

                        $file_path = $request->file('file')->storeAs('file/naac',$uploaded_file,'public');
                    }

                    $data['criteria_id']    = $request->criteria_id;
                    $data['file_path']      = $file_path;
                    $data['approval_status']= $request->approval_status;
                    $data = NaacFile::create($data);

                    if ($data) {
                        $logtype = LogType::select('id')->where('type', '=', 'naac')->first();
                        if (is_null($logtype)) {
                            return $this->sendResponse($logtype, 'Log type data not found', 404);
                        } else {
                            $dta = array();
                            $dta['log_type_id'] = $logtype['id'];
                            $dta['user_id'] = $user_id;
                            $dta['ip'] = $request->ip();
                            $dta['action'] = $request->approval_status;
                            $dta['action_summary'] = 'Uploaded naac file in criteria '.$criteria->criteria_name ;
                            $dta['ac_non_ac_id'] = $ac_non_ac_id;
                            $dta['related_link_id'] = 'naac-panal';
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

    //Update Naac Criteria!
    public function updateNaacCriteria(Request $request)
    {

        $input = $request->all();
        $validator = Validator::make($request->all(), [
            'slug' => 'required',
            'role' => 'required',
            'type' => 'required',
            'path' => 'required',
            'approval_status' => 'required',
            'id' => 'required'
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

                $naacfile = NaacFile::where('id',request()->id)->first();
                $criteria = NaacCriteria::select('id','criteria_name','pid','path')->where('id',$naacfile->criteria_id)->first();                
                if($request->hasFile('file')) {
                    $oldfilePath = substr($naacfile->file_path, 5);
                    $path = substr($oldfilePath, 0 , (strrpos($oldfilePath, ".")));
                    $files = Storage::disk('public')->exists($naacfile->file_path);

                    if($files == true) {
                        DeleteOldPicture($naacfile->file_path);
                    }

                    $uploaded_file = $path.'.'.$request->file('file')->guessExtension();
                    $file_path = $request->file('file')->storeAs('file',$uploaded_file,'public');
                }

                    
                    if($request->hasFile('file')) {
                       $data['file_path']      = $file_path; 
                    }                    
                    $data['approval_status']= $request->approval_status;
                    NaacFile::where('id',$naacfile->id)->update($data);
                    $data = NaacFile::where('id',request()->id)->first();


                    $logtype = LogType::select('id')->where('type', '=', 'naac')->first();
                    if (is_null($logtype)) {
                        return $this->sendResponse($logtype, 'Log type data not found', 404);
                    } else {
                        $dta = array();
                        $dta['log_type_id'] = $logtype['id'];
                        $dta['user_id'] = $user_id;
                        $dta['ip'] = $request->ip();
                        $dta['action'] = $request->approval_status;
                        $dta['action_summary'] = 'Updated naac file in criteria '.$criteria->criteria_name ;
                        $dta['ac_non_ac_id'] = $ac_non_ac_id;
                        $dta['related_link_id'] = 'naac-panal';
                        UserLogSave($dta);
                    }
                    return $this->sendResponse($data, trans('en_lang.DATA_UPDATED'), 200);
               
            } else {
                return $this->sendResponse($data, trans('en_lang.ACCESS_DENIED'), 401, false);
            }
        }
    }

    // Get Criteria  list
    public function getNaacCriteriaList(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'slug' => 'required',
            'role' => 'required',
            'type' => 'required',
            'criteria_id' => 'required',
        ]);
        $input = $request->all();
        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors(), 422, false);
        } else {
            $academic =  AcademicAndNonAcademic::where('slug', request()->slug)->first();
            $ac_non_ac_id = $academic->id;
            $user_id = $request->user()->id;
            $role = request()->role;
            if (checkUserRole($ac_non_ac_id, $user_id, $role)) {
                $data = NaacFile::where('criteria_id', request()->criteria_id)->orderBy('order_on', 'ASC')->orderBy('id', 'desc')->with('geDepartment:id,code')->paginate('100');
                //env('ITEM_PER_PAGE')
                if ($data) {
                    return $this->sendResponse($data, trans('en_lang.DATA_FOUND'), 200);
                } else {
                    return $this->sendResponse($data, trans('en_lang.DATA_NOT_FOUND'),404);
                }
            } else {
                return $this->sendResponse($data, trans('en_lang.ACCESS_DENIED'), 401, false);
            }
        }
    }

    // Get Single Criteria
    public function getSingleNaacCriteria(Request $request)
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

                $data = NaacFile::where('id', request()->id)->with('getCriteria')->first();
                
                if ($data) {
                    return $this->sendResponse($data, trans('en_lang.DATA_FOUND'), 200);
                } else {
                    return $this->sendResponse($data, trans('en_lang.DATA_NOT_FOUND'),404);
                }
            } else {
                return $this->sendResponse($data, trans('en_lang.ACCESS_DENIED'), 401, false);
            }
        }
    }   

    // Delete  NAAC Criteria
    public function deleteNaacCriteria(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required',
            'role' => 'required',
            'slug' => 'required',
            'type' => 'required',
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
                    $naacfile = NaacFile::where('id', request()->id)->first();
                    $files = Storage::disk('public')->exists($naacfile->file_path);

                    if($files == true) {
                        DeleteOldPicture($naacfile->file_path);
                    }

                    if (NaacFile::where('id', request()->id)->delete()) {

                        $logtype = LogType::select('id')->where('type', '=', 'naac')->first();

                        if (is_null($logtype)) {

                            return $this->sendResponse($logtype, 'Log type data not found', 404);
                        } else {

                            $dta = array();
                            $dta['log_type_id'] = $logtype['id'];
                            $dta['user_id'] = $user_id;
                            $dta['ip'] = $request->ip();
                            $dta['action'] = 'Naac Criteria Deleted';
                            $dta['action_summary'] = 'file '.$naacfile->file_path. ' has been deleted!';
                            $dta['ac_non_ac_id'] = $ac_non_ac_id;
                            $dta['related_link_id'] = 'naac-panal';
                            UserLogSave($dta);
                        }

                        return $this->sendResponse('true', 'Naac Criteria Deleted', 200);
                    } else {
                        return $this->sendResponse('false', 'Something went Wrong!', 404, false);
                    }
                }

            } else {
                return $this->sendResponse($data, trans('en_lang.ACCESS_DENIED'), 401, false);
            }
        }
    }

    // Update Naac Critaria order number
    public function updateNaacCriteriaOrder(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'slug' => 'required',
            'role' => 'required',
            'type' => 'required',
            'orders' => 'required',
        ]);
        $input = $request->all();
        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors(), 422, false);
        } else {
            $academic =  AcademicAndNonAcademic::where('slug', request()->slug)->first();
            $ac_non_ac_id = $academic->id;
            $user_id = $request->user()->id;
            $role = request()->role;
            if (checkUserRole($ac_non_ac_id, $user_id, $role)) {              

                    if (!empty($request->orders)) {
                        foreach ($request->orders as $key => $order) {

                            NaacFile::where('id', $order['id'])->update(['order_on' => $key + 1]);
                        }
                        return $this->sendResponse('Update Successfully.', 'DATA FOUND', 200);
                    }

                    $logtype = LogType::select('id')->where('type', '=', 'naac')->first();

                    if (is_null($logtype)) {

                        return $this->sendResponse($logtype, 'Log type data not found', 404);
                    } else {

                        $dta = array();
                        $dta['log_type_id'] = $logtype['id'];
                        $dta['user_id'] = $user_id;
                        $dta['ip'] = $request->ip();
                        $dta['action'] = 'Order list updated for Naac Criteria';
                        $dta['action_summary'] = 'Order list updated for Naac Criteria';
                        $dta['ac_non_ac_id'] = $ac_non_ac_id;
                        $dta['related_link_id'] = 'naac-panal';
                        UserLogSave($dta);
                    }
               
            } else {
                return $this->sendResponse('false', trans('en_lang.ACCESS_DENIED'), 401);
            }
        }
    }

    // Export Naac Criteria
    public function naacExport(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'slug' => 'required',
            'role' => 'required',
            'type' => 'required',
            'path' => 'required',
            'id' => 'required',
        ]);
        $input = $request->all();
        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors(), 422, false);
        } else {
            $academic =  AcademicAndNonAcademic::where('slug', request()->slug)->first();
            $ac_non_ac_id = $academic->id;
            $user_id = $request->user()->id;
            $role = request()->role;
            if (checkUserRole($ac_non_ac_id, $user_id, $role)) {

                $logtype = LogType::select('id')->where('type', '=', 'naac')->first();

                if (is_null($logtype)) {

                    return $this->sendResponse($logtype, 'Log type data not found', 404);
                } else {

                    $dta = array();
                    $dta['log_type_id'] = $logtype['id'];
                    $dta['user_id'] = $user_id;
                    $dta['ip'] = $request->ip();
                    $dta['action'] = 'Export Naac Criteria to Excel';
                    $dta['action_summary'] = 'Export Naac Criteria to Excel';
                    $dta['ac_non_ac_id'] = $ac_non_ac_id;
                    $dta['related_link_id'] = 'naac-panal';
                    UserLogSave($dta);
                    $criteria = NaacCriteria::select('criteria_name')->where('id',$request->id)->first();
                    $fileName = 'CR-'.$criteria->criteria_name.'.xlsx';
                    $export = Excel::store(new BulkExport($request->id), $fileName ,'public');

                    if ($export) {
                        $filePath = asset('storage').'/'.$fileName;
                        return $this->sendResponse($filePath, 'DATA FOUND', 200);
                    }

                   return $this->sendResponse($export, 'DATA FOUND', 200);
                    //return Excel::download(new BulkExport($request->id), 'naacExport.xlsx');
                }

                return $this->sendResponse('Naac Export', 'DATA FOUND', 200);
               
            } else {
                return $this->sendResponse('false', trans('en_lang.ACCESS_DENIED'), 401);
            }
        }
    }
}
