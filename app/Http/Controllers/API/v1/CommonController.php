<?php

namespace App\Http\Controllers\API\v1;

use App\Http\Controllers\API\v1\BaseController as BaseController;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\ResearchScholars;
Use App\RelatedLink;
Use App\AcNonAcImage;
Use App\FormerChairPerson;
Use App\AcNonAcGallery;
Use App\Designation;
Use App\GroupLink;
Use App\UserVisibility;
Use App\RelatedLinkData;
Use App\OnGoingProject;
Use App\CompletedProject;
use App\NoticeCircular;
use App\BroadcastMessage;
Use App\Course;
use Validator;
Use App\RoleType;
Use App\SubType;
Use App\User;
use Auth;

class CommonController extends BaseController
{
    public function __construct()
    {
        $this->middleware('auth:api');
    }   

    /*
    *Get User Designation
    */

    public function getDesignations(Request $request)
    {
        $validator = Validator::make($request->all(), [
                'category' => 'required|in:all,TS,NT,PGS',
            ]);
            
        if($validator->fails()){
            return $this->sendError('Validation Error.', $validator->errors(),422);      
        }
        if (request()->category == 'all') {
            $designation = Designation::select('id','name','category')->where('status',1)->orderBy('name','ASC')->get();
        }else{
            $designation = Designation::select('id','name','category')->where('status',1)->orderBy('name','ASC')
            ->when(request()->category, function ($query) {
                $query->where('category', 'LIKE', '%'.request()->category.'%');
            })->get();
        }
        

        if (is_null($designation)){                        
            return $this->sendResponse($designation, trans('en_lang.DATA_NOT_FOUND'),404);
        }
        return $this->sendResponse($designation, trans('en_lang.DATA_FOUND'),200);
    } 
    
    /*
    *Get User Role
    */

    public function getRoles(Request $request)
    {
        $types = SubType::where('slug',$request->type)->first();
        $head_id = explode(',', $types->head);
        $role_types = RoleType::select('id','title')->whereIn('id', $head_id)->whereNotIn('id', [1, 17])->orderby('title','ASC')->get();

        if (is_null($role_types)){                        
            return $this->sendResponse($role_types, trans('en_lang.DATA_NOT_FOUND'),404);
        }
        return $this->sendResponse($role_types, trans('en_lang.DATA_FOUND'),200);
    }

    public function getNonRoles(Request $request)
    {
        $types = SubType::where('slug',$request->type)->first();
        $nontech_role_id = explode(',', $types->nontech_role);
        $nonroles = RoleType::select('id','title')->whereIn('id', $nontech_role_id)->whereNotIn('id', [1, 17])->orderby('title','ASC')->get();

        if (is_null($nonroles)){                        
            return $this->sendResponse($nonroles, trans('en_lang.DATA_NOT_FOUND'),404);
        }
        return $this->sendResponse($nonroles, trans('en_lang.DATA_FOUND'),200);
    }

    public function getAllRoles(Request $request)
    {
        $types = SubType::where('slug',$request->type)->first();
        $head_id_tech = explode(',', $types->head);
        $head_id_nontech = explode(',', $types->nontech_role);
        $head_id = array_merge($head_id_tech,$head_id_nontech);
        $role_types = RoleType::select('id','title')->whereIn('id',$head_id)->whereNotIn('id', [1, 17])->orderby('title','ASC')->get();

        if (is_null($role_types)){                        
            return $this->sendResponse($role_types, trans('en_lang.DATA_NOT_FOUND'),404);
        }
        return $this->sendResponse($role_types, trans('en_lang.DATA_FOUND'),200);
    }

    /*
    *Get Academic's Group Links
    */

    public function getGroupLink(Request $request)
    {
        $grouplink = GroupLink::select('id','title_en','order_on')->where('type','1')->orderby('order_on','ASC')->get();

        if (is_null($grouplink)){                        
            return $this->sendResponse($grouplink, trans('en_lang.DATA_NOT_FOUND'),404);
        }
        return $this->sendResponse($grouplink, trans('en_lang.DATA_FOUND'),200);
    }

    /*
    *Get VC Group Links
    */

    public function getVcGroupLink(Request $request)
    {
        $grouplink = GroupLink::select('id','title_en','order_on')->where('type','2')->orderby('order_on','ASC')->get();

        if (is_null($grouplink)){                        
            return $this->sendResponse($grouplink, trans('en_lang.DATA_NOT_FOUND'),404);
        }
        return $this->sendResponse($grouplink, trans('en_lang.DATA_FOUND'),200);
    }

    /*
    *Check Employee EID
    */

    public function checkEmpId(Request $request)
    {
        $users = User::where('eid','=',request()->eid)->first();
        
        if(is_null($users)){
            return $this->sendResponse('false', trans('en_lang.DATA_NOT_FOUND'),404);
        }else{
            if($users->status == 0){
                return $this->sendResponse('User deactivated please contact WebMaster', trans('en_lang.DATA_FOUND'),200);
            }else{
                return $this->sendResponse('Employee ID is Already Exist', trans('en_lang.DATA_FOUND'),200);
            }
        }
        
    }

    /*
    *Check Research Scholar Employee EID exist or not
    */

    public function checkRScholarEmpId(Request $request)
    {
        $data = ResearchScholars::where('enrolno','=',request()->enrolno)->first();
        if(is_null($data)){
            return $this->sendResponse('false', trans('en_lang.DATA_NOT_FOUND'),404);
            
        }else{

            return $this->sendResponse('true', trans('en_lang.DATA_FOUND'),200);
        }
    }

    /*
    * User Search Api
    */
    public function userSearch(Request $request){
        if ($request->isMethod('post')){
            $validator = Validator::make($request->all(), [                
                'keyword' => 'required',
            ]);
            
            if($validator->fails()){
                return $this->sendError('Validation Error.', $validator->errors(),422);      
            }

            $input = $request->all();
            $name = $request->keyword;
            $users = User::select('id','eid','title','first_name','middle_name','last_name','status','for_id','created_at')->where('id','!=',$request->user()->id)->where('status','=','1')->where('eid','!=','')
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
            if ($users->isEmpty()){
                $users = array();
                $users[0]['eid'] = $name; 
                $users[0]['first_name'] = ' No data';    
                $users[0]['last_name'] = 'Found!';    
                $users[0]['title'] = 'Sorry,';                            
                return $this->sendResponse($users, trans('en_lang.DATA_FOUND'),200);
            }else{
                return $this->sendResponse($users, trans('en_lang.DATA_FOUND'),200);
            }
        }         
    }

     /*
    * User Broadcast Message
    */
    public function getBroadcastMessage(Request $request)
    {
       
        $message = BroadcastMessage::where('status','1')->first();
        if (is_null($message)){                        
            return $this->sendResponse($message, trans('en_lang.DATA_NOT_FOUND'),404);
        }
        return $this->sendResponse($message, trans('en_lang.DATA_FOUND'),200);
    }
}
