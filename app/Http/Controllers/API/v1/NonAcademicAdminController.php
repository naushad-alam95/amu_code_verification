<?php

namespace App\Http\Controllers\API\v1;

use App\Http\Controllers\API\v1\BaseController as BaseController;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;
use App\AcademicAndNonAcademic;
use App\AcNonAcCommitteeFiles;
use App\SectionContactDetail;
use App\FormerChairPerson;
use App\ResearchScholars;
use App\OnGoingProject;
use App\CompletedProject;
use App\Course;
use App\NoticeCircular;
use App\UserVisibility;
use App\AcNonAcGallery;
use App\RelatedLink;
use App\CustomRelatedLink;
use App\RelatedLinkData;
use App\GroupLink;
use App\LogType; 
use App\AcNonAcImage;
use App\UserContact;
use App\Notification;
use App\ELeave;
use Carbon\Carbon;
use App\EProvidentFund;
use App\EPaySlips;
use App\VCLink;
use App\UserLog;
use App\Tender;
use App\User;
use Validator;
use Auth;
use Log;
use Hash;
use File;
use DB;

class NonAcademicAdminController extends BaseController
{

    public function __construct()
    {
        $this->middleware('auth:api');
    }    

    // Get Non Academic related link data by related link path
    public function getNonAcContent(Request $request)
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
               
                $slug = request()->slug.'/'.request()->path;
                $rlink_id = RelatedLink::where('slug', $slug)->pluck('id');
                if ($rlink_id->isEmpty()) {
                    return $this->sendResponse($rlink_id, 'Path Not Found', 404, false);
                }

                if (request()->path == 'home-page'  || request()->path == 'office-of-the-dean') {

                    $aboutData = RelatedLinkData::where('ac_non_ac_id', $ac_non_ac_id)->where('related_link_id', $rlink_id[0])->with('getImages')->first();
                    if (empty($aboutData)) {
                        return $this->sendResponse($aboutData, trans('en_lang.DATA_NOT_FOUND'), 204);
                    }

                    $data = array();
                    $data['id'] = $aboutData->id;
                    $data['ac_non_ac_id'] = $aboutData->ac_non_ac_id;
                    $data['related_link_id'] = $aboutData->related_link_id;

                    if ($aboutData->approval_status == 'Approved') {
                        $data['link_en'] = $aboutData->link_en;
                        $data['link_description_en'] = $aboutData->link_description_en;
                        $data['link_hi'] = $aboutData->link_hi;
                        $data['link_description_hi'] = $aboutData->link_description_hi;
                        $data['link_ur'] = $aboutData->link_ur;
                        $data['link_description_ur'] = $aboutData->link_description_ur;
                    } else {
                        $data['link_en'] = $aboutData->link_en_draft ? $aboutData->link_en_draft : $aboutData->link_en;
                        $data['link_description_en'] = $aboutData->link_description_en_draft ? $aboutData->link_description_en_draft : $aboutData->link_description_en;
                        $data['link_hi'] = $aboutData->link_hi_draft ? $aboutData->link_hi_draft : $aboutData->link_hi;
                        $data['link_description_hi'] = $aboutData->link_description_hi_draft ? $aboutData->link_description_hi_draft : $aboutData->link_description_hi;
                        $data['link_ur'] = $aboutData->link_ur_draft ? $aboutData->link_ur_draft : $aboutData->link_ur;
                        $data['link_description_ur'] = $aboutData->link_description_ur_draft ? $aboutData->link_description_ur_draft : $aboutData->link_description_ur;
                    }
                    $data['approval_status'] = $aboutData->approval_status;
                    $data['slider'] = $aboutData->getImages;
                    if ($data) {
                        return $this->sendResponse($data, 'DATA FOUND', 200);
                    } else {
                        return $this->sendResponse($data, 'DATA NOT FOUND', 404, false);
                    }
                } if (request()->path == 'contact-us'  || request()->path == 'contact-details') {

                    $contactUs = RelatedLinkData::where('ac_non_ac_id', $ac_non_ac_id)->where('related_link_id', $rlink_id[0])->first();
                    if (empty($contactUs)) {
                        return $this->sendResponse($contactUs, trans('en_lang.DATA_NOT_FOUND'), 204);
                    }

                    $data = array();
                    $data['id'] = $contactUs->id;
                    $data['ac_non_ac_id'] = $contactUs->ac_non_ac_id;
                    $data['related_link_id'] = $contactUs->related_link_id;

                    if ($contactUs->approval_status == 'Approved') {
                        $data['link_en'] = $contactUs->link_en;
                        $data['link_description_en'] = $contactUs->link_description_en;
                        $data['link_hi'] = $contactUs->link_hi;
                        $data['link_description_hi'] = $contactUs->link_description_hi;
                        $data['link_ur'] = $contactUs->link_ur;
                        $data['link_description_ur'] = $contactUs->link_description_ur;
                    } else {
                        $data['link_en'] = $contactUs->link_en_draft ?? $contactUs->link_en;
                        $data['link_description_en'] = $contactUs->link_description_en_draft ?? $contactUs->link_description_en;
                        $data['link_hi'] = $contactUs->link_hi_draft ?? $contactUs->link_hi;
                        $data['link_description_hi'] = $contactUs->link_description_hi_draft ??  $contactUs->link_description_hi;
                        $data['link_ur'] = $contactUs->link_ur_draft ?? $contactUs->link_ur;
                        $data['link_description_ur'] = $contactUs->link_description_ur_draft ?? $contactUs->link_description_ur;
                    }
                    $data['approval_status'] = $contactUs->approval_status;
                    if ($data) {
                        return $this->sendResponse($data, 'DATA FOUND', 200);
                    } else {
                        return $this->sendResponse($data, 'DATA NOT FOUND', 404, false);
                    }
                } elseif (request()->path == 'faculty-members' || request()->path == 'non-teaching-staff' || request()->path == 'staff-member-teaching' || request()->path == 'staff-members') {

                    if (request()->path == 'faculty-members' || request()->path == 'staff-member-teaching') {

                        $faculties = UserVisibility::where('ac_non_ac_id', $ac_non_ac_id)->where('for_id','1')->where('status','1')->with('getUser.getContact', 'getDesignation', 'getRole')->orderBy('order_on', 'ASC')->get();
                        if (empty($faculties)) {
                            return $this->sendResponse($faculties, trans('en_lang.DATA_NOT_FOUND'), 204);
                        }
                        $key = 0;
                        foreach ($faculties as $value) {
                            if ($value->getUser != NULL) {
                                $data[$key]['id'] = $value->id;
                                $data[$key]['user_id'] = $value->user_id;
                                $data[$key]['order_on'] = $value->order_on;
                                $data[$key]['slug'] = $value->getUser->slug;
                                $data[$key]['title'] = $value->getUser->title;
                                $data[$key]['first_name'] = $value->getUser->first_name;
                                $data[$key]['last_name'] = $value->getUser->last_name;
                                $data[$key]['middle_name'] = $value->getUser->middle_name;
                                $data[$key]['url'] = $value->getUser->url;
                                if ($value->getUser->image != NULL) {
                                    $userImage = asset('storage').$value->getUser->image;
                                } else {
                                    $userImage = asset('storage').'/images/default-img.png';
                                }
                                $data[$key]['image'] = $userImage;
                                if ($value->role_id == '2') {
                                    $data[$key]['designation'] = $value->getDesignation->name . ' and Chairman';
                                } else {
                                    $data[$key]['designation'] = $value->getDesignation->name;
                                }
                                if ($value->special_role == '' && $value->special_role == Null) {
                                    $data[$key]['special_role'] = $value->getRole->title;
                                } else {
                                    $data[$key]['special_role'] = $value->special_role;
                                }

                                $key++;
                            }
                        }
                    } else if (request()->path == 'staff-members') {

                        $nonTeachingStaff = UserVisibility::where('ac_non_ac_id', $ac_non_ac_id)->where('status','1')->with('getUser.getContact', 'getDesignation', 'getRole')->orderBy('order_on', 'ASC')->get();
                        if (empty($nonTeachingStaff)) {
                            return $this->sendResponse($nonTeachingStaff, trans('en_lang.DATA_NOT_FOUND'), 204);
                        }
                        $key = 0;
                        foreach ($nonTeachingStaff as $value) {
                            if ($value->getUser != NULL) {
                                $data[$key]['id'] = $value->id;
                                $data[$key]['user_id'] = $value->user_id;
                                $data[$key]['order_on'] = $value->order_on;
                                $data[$key]['slug'] = $value->getUser->slug;
                                $data[$key]['title'] = $value->getUser->title;
                                $data[$key]['first_name'] = $value->getUser->first_name;
                                $data[$key]['last_name'] = $value->getUser->last_name;
                                $data[$key]['middle_name'] = $value->getUser->middle_name;
                                $data[$key]['url'] = $value->getUser->url;
                                if ($value->getUser->image != NULL) {
                                    $userImage = asset('storage').$value->getUser->image;
                                } else {
                                    $userImage = asset('storage').'/images/default-img.png';
                                }
                                $data[$key]['image'] = $userImage;
                                $data[$key]['designation'] = $value->getDesignation->name;
                                if ($value->special_role == '' && $value->special_role == Null) {
                                    $data[$key]['special_role'] = $value->getRole->title;
                                } else {
                                    $data[$key]['special_role'] = $value->special_role;
                                }
                                $key++;
                            }
                        }
                    } 
                    else if (request()->path == 'non-teaching-staff' || request()->path == 'staff-list') {

                        $nonTeachingStaff = UserVisibility::where('ac_non_ac_id', $ac_non_ac_id)->where('for_id','2')->where('status','1')->with('getUser.getContact', 'getDesignation', 'getRole')->orderBy('order_on', 'ASC')->get();
                        if (empty($nonTeachingStaff)) {
                            return $this->sendResponse($nonTeachingStaff, trans('en_lang.DATA_NOT_FOUND'), 204);
                        }
                        $key = 0;
                        foreach ($nonTeachingStaff as $value) {
                            if ($value->getUser != NULL) {
                                $data[$key]['id'] = $value->id;
                                $data[$key]['user_id'] = $value->user_id;
                                $data[$key]['order_on'] = $value->order_on;
                                $data[$key]['slug'] = $value->getUser->slug;
                                $data[$key]['title'] = $value->getUser->title;
                                $data[$key]['first_name'] = $value->getUser->first_name;
                                $data[$key]['last_name'] = $value->getUser->last_name;
                                $data[$key]['middle_name'] = $value->getUser->middle_name;
                                $data[$key]['url'] = $value->getUser->url;
                                if ($value->getUser->image != NULL) {
                                    $userImage = asset('storage').$value->getUser->image;
                                } else {
                                    $userImage = asset('storage').'/images/default-img.png';
                                }
                                $data[$key]['image'] = $userImage;
                                $data[$key]['designation'] = $value->getDesignation->name;
                                if ($value->special_role == '' && $value->special_role == Null) {
                                    $data[$key]['special_role'] = $value->getRole->title;
                                } else {
                                    $data[$key]['special_role'] = $value->special_role;
                                }
                                $key++;
                            }
                        }
                    } 

                    if ($data) {
                        return $this->sendResponse($data, trans('en_lang.DATA_FOUND'), 200);
                    } else {
                        return $this->sendResponse($data, trans('en_lang.DATA_NOT_FOUND'), 204);
                    }
                }  elseif (request()->path == 'notice-and-circular') {

                    $data = NoticeCircular::select('id','title_en','title_hi','title_ur','file','created_at')->where('ac_non_ac_id', $ac_non_ac_id)->orderBy('created_at', 'desc')->get();                

                    if ($data->isNotEmpty()) {
                        return $this->sendResponse($data, trans('en_lang.DATA_FOUND'), 200);
                    } else {
                        return $this->sendResponse($data, trans('en_lang.DATA_NOT_FOUND'), 204);
                    }
                } elseif (request()->path == 'photo-gallery') {

                    $data = AcNonAcGallery::where('ac_non_ac_id', $ac_non_ac_id)->get();

                    if ($data->isNotEmpty()) {
                        return $this->sendResponse($data, trans('en_lang.DATA_FOUND'), 200);
                    } else {
                        return $this->sendResponse($data, trans('en_lang.DATA_NOT_FOUND'), 204);
                    }
                } else {

                    $commonData = RelatedLinkData::where('ac_non_ac_id', $ac_non_ac_id)->where('related_link_id', $rlink_id[0])->orderBy('order_on', 'DESC')->get();
                    if (empty($commonData)) {
                        return $this->sendResponse($commonData, trans('en_lang.DATA_NOT_FOUND'), 204);
                    }
                    $key = 0;
                    foreach ($commonData as $value) {
                        $data[$key]['id'] = $value->id;
                        $data[$key]['ac_non_ac_id'] = $value->ac_non_ac_id;
                        $data[$key]['related_link_id'] = $value->related_link_id;
                        $data[$key]['file'] = $value->file;
                        $data[$key]['order_on'] = $value->order_on;
                        $data[$key]['date'] = $value->created_at;
                        $data[$key]['approval_status'] = $value->approval_status;

                        if ($value->approval_status == 'Approved') {
                            $data[$key]['link_en'] = $value->link_en;
                            $data[$key]['link_description_en'] = $value->link_description_en;
                            $data[$key]['link_hi'] = $value->link_hi;
                            $data[$key]['link_description_hi'] = $value->link_description_hi;
                            $data[$key]['link_ur'] = $value->link_ur;
                            $data[$key]['link_description_ur'] = $value->link_description_ur;
                        } else {
                            $data[$key]['link_en'] = $value->link_en_draft ? $value->link_en_draft : $value->link_en;
                            $data[$key]['link_description_en'] = $value->link_description_en_draft ? $value->link_description_en_draft : $value->link_description_en;
                            $data[$key]['link_hi'] = $value->link_hi_draft ? $value->link_hi_draft : $value->link_hi;
                            $data[$key]['link_description_hi'] = $value->link_description_hi_draft ? $value->link_description_hi_draft : $value->link_description_hi;
                            $data[$key]['link_ur'] = $value->link_ur_draft ? $value->link_ur_draft : $value->link_ur;
                            $data[$key]['link_description_ur'] = $value->link_description_ur_draft ? $value->link_description_ur_draft : $value->link_description_ur;
                        }
                        $key++;
                    }
                    if (!empty($data)) {
                        return $this->sendResponse($data, trans('en_lang.DATA_FOUND'), 200);
                    } else {
                        return $this->sendResponse($data, trans('en_lang.DATA_NOT_FOUND'), 204);
                    }
                }
            } else {
                return $this->sendResponse($data, trans('en_lang.ACCESS_DENIED'), 401, false);
            }
        }
    }   

    // Store Department's  related links
    public function storeNonAcRelatedLink(Request $request)
    {

        $input = $request->all();
       //\Log::info($input);
        $user = Auth::user();
        $validator = Validator::make($request->all(), [
            'slug' => 'required',
            'role' => 'required',
            'type' => 'required',
            'link_name_en' => 'required'
        ]);
        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors(), 422, false);
        } else {
            $academic =  AcademicAndNonAcademic::where('slug', request()->slug)->first();
            $ac_non_ac_id = $academic->id;
            $user_id      =  $request->user()->id;
            $role         = request()->role;
            if (checkUserRole($ac_non_ac_id, $user_id, $role)) {

                   
                    $link_slug = str_slug($request->link_name_en, '-');
                    $slug = request()->slug.'/'.$link_slug;
                    if (isset($request->id) != null) {
                        $data = array();
                        $data['link_name_en']   =   $request->link_name_en;
                        $data['link_name_hi']   =   $request->link_name_hi;
                        $data['link_name_ur']   =   $request->link_name_ur;
                        
                        $data = RelatedLink::where('id',$request->id)->update($data);
                        if ($data) {

                            $logtype = LogType::select('id')->where('type', '=', 'non-academic')->first();
                            if (is_null($logtype)) {
                                return $this->sendResponse($logtype, 'Log type data not found', 404);
                            } else {
                                $dta = array();
                                $dta['log_type_id'] = $logtype['id'];
                                $dta['user_id'] = $user_id;
                                $dta['ip'] = $request->ip();
                                $dta['action'] = 'Non Academic related link updated';
                                $dta['ac_non_ac_id'] = $ac_non_ac_id;
                                $dta['related_link_id'] = 'related_link';
                                UserLogSave($dta);
                            }
                            return $this->sendResponse($data, trans('en_lang.DATA_UPDATED'), 200);
                        } else {
                            return $this->sendResponse($data, 'Some thing went wrong', 204, false);
                        }
                    }else{
                        $data = array();
                        $data['ac_non_ac_id']   =   $ac_non_ac_id;
                        $data['link_name_en']   =   $request->link_name_en;
                        $data['link_name_hi']   =   $request->link_name_hi;
                        $data['link_name_ur']   =   $request->link_name_ur;
                        $data['slug']           =   $slug;
                        $data['link_order']     =   $request->link_order;
                        RelatedLink::create($data);
                        if ($data) {
                            $logtype = LogType::select('id')->where('type', '=', 'non-academic')->first();
                            if (is_null($logtype)) {
                                return $this->sendResponse($logtype, 'Log type data not found', 404);
                            } else {
                                $dta = array();
                                $dta['log_type_id'] = $logtype['id'];
                                $dta['user_id'] = $user_id;
                                $dta['ip'] = $request->ip();
                                $dta['action'] = 'Non Academic related link created';
                                $dta['ac_non_ac_id'] = $ac_non_ac_id;
                                $dta['related_link_id'] = 'related_link';
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

    // Get Single Related Link
    public function getSingleRelatedLink(Request $request)
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

                $data = RelatedLink::where('id', request()->id)->first();
                
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

    // Delete Related link.
    public function deleteNonAcRelatedLink(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required',
            'type' => 'required',
            'role' => 'required',
            'slug' => 'required',
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

                    if (RelatedLink::where('id', request()->id)->delete()) {

                        return $this->sendResponse('true', 'Related link deleted', 200);
                    } else {

                        return $this->sendResponse('false', 'Something went Wrong!', 404);
                    }

                    $logtype = LogType::select('id')->where('type', '=', 'non-academic')->first();

                    if (is_null($logtype)) {

                        return $this->sendResponse($logtype, 'Log type data not found', 404);
                    } else {

                        $dta = array();
                        $dta['log_type_id'] = $logtype['id'];
                        $dta['user_id'] = $user_id;
                        $dta['ip'] = $request->ip();
                        $dta['action'] = 'Non Academic related link data deleted';
                        $dta['ac_non_ac_id'] = $ac_non_ac_id;
                        $dta['related_link_id'] = 'related_link';
                        UserLogSave($dta);
                    }
                } else {
                    return $this->sendResponse("false", trans('en_lang.ACCESS_DENIED'), 401);
                }
            } else {
                return $this->sendResponse("false", trans('en_lang.ACCESS_DENIED'), 401);
            }
        }
    }

    // Update order number for non-academic related link
    public function updateNonAcLinkOrder(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'slug' => 'required',
            'role' => 'required',
            'type' => 'required',
            'orders' => 'required'
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

                    if (!empty($request->orders)) {
                        foreach ($request->orders as $key => $order) {
                            
                            RelatedLink::where('id', $order['id'])->update(['link_order' => $key + 1]);
                        }
                        return $this->sendResponse('Update Successfully.', 'DATA FOUND', 200);
                    }

                    $logtype = LogType::select('id')->where('type', '=', 'non-academic')->first();

                    if (is_null($logtype)) {

                        return $this->sendResponse($logtype, 'Log type data not found', 404);
                    } else {

                        $dta = array();
                        $dta['log_type_id'] = $logtype['id'];
                        $dta['user_id'] = $user_id;
                        $dta['ip'] = $request->ip();
                        $dta['action'] = 'Order list updated for Non-Academic related link';
                        $dta['ac_non_ac_id'] = $ac_non_ac_id;
                        $dta['related_link_id'] = 'related_link_order';
                        UserLogSave($dta);
                    }
                } else {
                    return $this->sendResponse('false', trans('en_lang.ACCESS_DENIED'), 401);
                }
            } else {
                return $this->sendResponse('false', trans('en_lang.ACCESS_DENIED'), 401);
            }
        }
    }

    // Get Non Academic section contact detail
    public function getSectionContactDetail(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'slug' => 'required',
            'role' => 'required',
            'type' => 'required',
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

                $sectionContact = SectionContactDetail::where('ac_non_ac_id', $ac_non_ac_id)->first();
                $data = array();
                if ($sectionContact) {
                    
                    $data['id'] = $sectionContact->id;
                    $data['ac_non_ac_id'] = $sectionContact->ac_non_ac_id;
                    $data['user_id'] = $sectionContact->user_id;

                    if ($sectionContact->approval_status == 'Approved') {
                        $data['office_ext'] = $sectionContact->office_ext;
                        $data['office']     = $sectionContact->office;
                        $data['phone_ext']  = $sectionContact->phone_ext;
                        $data['phone']      = $sectionContact->phone;
                        $data['email']      = $sectionContact->email;
                    } else {
                        $data['office_ext'] = $sectionContact->office_ext_draft;
                        $data['office']     = $sectionContact->office_draft;
                        $data['phone_ext']  = $sectionContact->phone_ext_draft;
                        $data['phone']      = $sectionContact->phone_draft;
                        $data['email']      = $sectionContact->email_draft;
                    }
                    $data['approval_status'] = $sectionContact->approval_status;
                    $data['update_at'] = $sectionContact->update_at;
                }

                    
                
                if ($sectionContact) {
                    return $this->sendResponse($data, trans('en_lang.DATA_FOUND'), 200);
                } else {
                    return $this->sendResponse($data, trans('en_lang.DATA_NOT_FOUND'), 204);
                }
            } else {
                return $this->sendResponse($data, trans('en_lang.ACCESS_DENIED'), 401, false);
            }
        }
    }

    // Store Non Academic  section contact data 
    public function storeSectionContactDetail(Request $request)
    {
        $input = $request->all();
        //\Log::info($input);
        $validator = Validator::make($request->all(), [
            'slug' => 'required',
            'role' => 'required',
            'type' => 'required',
            'approval_status' => 'required'
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
                    $sectionContact = SectionContactDetail::where('id', request()->id)->first();
                    if (request()->approval_status == 'Approved') {
                        $data['office_ext']         = $request->office_ext;
                        $data['office']             = $request->office;
                        $data['phone_ext']          = $request->phone_ext;
                        $data['phone']              = $request->phone;
                        $data['email']              = $request->email;
                        $data['office_ext_draft']   = '';
                        $data['office_draft']       = '';
                        $data['phone_ext_draft']    = '';
                        $data['phone_draft']        = '';
                        $data['email_draft']        = '';
                        
                    } else {
                        
                        $data['office_ext_draft']   = $request->office_ext;
                        $data['office_draft']       = $request->office;
                        $data['phone_ext_draft']    = $request->phone_ext;
                        $data['phone_draft']        = $request->phone;
                        $data['email_draft']        = $request->email;
                    }
                    $data['approval_status'] =  $request->approval_status;
                    $data = SectionContactDetail::where('id', request()->id)->update($data);
                    if ($data) {
                        $logtype = LogType::select('id')->where('type', '=', 'non-academic')->first();
                        if (is_null($logtype)) {
                            return $this->sendResponse($logtype, 'Log type data not found', 404);
                        } else {
                            $dta = array();
                            $dta['log_type_id'] = $logtype['id'];
                            $dta['user_id'] = $user_id;
                            $dta['ip'] = $request->ip();
                            $dta['action'] = $request->approval_status;
                            $dta['ac_non_ac_id'] = $ac_non_ac_id;
                            $dta['related_link_id'] = 'section-contact-detail';
                            UserLogSave($dta);
                        }
                        return $this->sendResponse($data, trans('en_lang.DATA_UPDATED'), 200);
                    } else {
                        return $this->sendResponse($data, 'Some thing went wrong', 204, false);
                    }
                } else {
                   
                    $data['ac_non_ac_id']        = $ac_non_ac_id;
                    $data['office_ext']          = $request->office_ext;
                    $data['office']              = $request->office;
                    $data['phone_ext']           = $request->phone_ext;
                    $data['phone']               = $request->phone;
                    $data['email']               = $request->email;
                    $data['approval_status']     = $request->approval_status;
                    if (request()->approval_status != 'Approved') {
                        $data['office_ext_draft']= $request->office_ext;
                        $data['office_draft']    = $request->office;
                        $data['phone_ext_draft'] = $request->phone_ext;
                        $data['phone_draft']     = $request->phone;
                        $data['email_draft']     = $request->email;
                    }
                    //\Log::info($data);
                    $data = SectionContactDetail::create($data);
                    if ($data) {

                        $logtype = LogType::select('id')->where('type', '=', 'non-academic')->first();
                        if (is_null($logtype)) {
                            return $this->sendResponse($logtype, 'Log type data not found', 404);
                        } else {
                            $dta = array();
                            $dta['log_type_id'] = $logtype['id'];
                            $dta['user_id'] = $user_id;
                            $dta['ip'] = $request->ip();
                            $dta['action'] = $request->approval_status;
                            $dta['ac_non_ac_id'] = $ac_non_ac_id;
                            $dta['related_link_id'] = 'section-contact-detail';
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

    // Store Non Academic custom related links
    public function storeCustomRelatedLink(Request $request)
    {

        $input = $request->all();
        $validator = Validator::make($request->all(), [
            'slug' => 'required',
            'role' => 'required',
            'type' => 'required',
            'link_name_en' => 'required',
            'rel_slug' => 'required'
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
                    $data['link_name_en']   =   $request->link_name_en;
                    $data['link_name_hi']   =   $request->link_name_hi;
                    $data['link_name_ur']   =   $request->link_name_ur;
                    $data['rel_slug']       =   $request->rel_slug;
                    $data['approval_status']    = $request->approval_status;
                    $data = CustomRelatedLink::where('id', request()->id)->update($data);

                    if ($data) {

                        $logtype = LogType::select('id')->where('type', '=', 'non-academic')->first();
                        if (is_null($logtype)) {
                            return $this->sendResponse($logtype, 'Log type data not found', 404);
                        } else {
                            $dta = array();
                            $dta['log_type_id'] = $logtype['id'];
                            $dta['user_id'] = $user_id;
                            $dta['ip'] = $request->ip();
                            $dta['action'] = $request->approval_status; 
                            $dta['ac_non_ac_id'] = $ac_non_ac_id;
                            $dta['related_link_id'] = 'custom-link';
                            UserLogSave($dta);
                        }
                        return $this->sendResponse($data, trans('en_lang.DATA_UPDATED'), 200);
                    } else {
                        return $this->sendResponse($data, 'Some thing went wrong', 204, false);
                    }
                } else {

                    $data['ac_non_ac_id']   =   $ac_non_ac_id;
                    $data['link_name_en']   =   $request->link_name_en;
                    $data['link_name_hi']   =   $request->link_name_hi;
                    $data['link_name_ur']   =   $request->link_name_ur;
                    $data['rel_slug']       =   $request->rel_slug;
                    $data['approval_status']= $request->approval_status;
                    CustomRelatedLink::create($data);

                    if ($data) {
                        $logtype = LogType::select('id')->where('type', '=', 'non-academic')->first();
                        if (is_null($logtype)) {
                            return $this->sendResponse($logtype, 'Log type data not found', 404);
                        } else {
                            $dta = array();
                            $dta['log_type_id'] = $logtype['id'];
                            $dta['user_id'] = $user_id;
                            $dta['ip'] = $request->ip();
                            $dta['action'] = $request->approval_status;
                            $dta['ac_non_ac_id'] = $ac_non_ac_id;
                            $dta['related_link_id'] = 'custom-link';
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

    // Get Non Academic custom related links
    public function getCustomLinks(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'slug' => 'required',
            'role' => 'required',
            'type' => 'required',
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
                $data = CustomRelatedLink::where('ac_non_ac_id', $ac_non_ac_id)->orderBy('order_on', 'asc')->paginate(env('ITEM_PER_PAGE'));
                
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

     // Get Single Non Academic custom related links
    public function getSingleCustomLink(Request $request)
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

                $data = CustomRelatedLink::where('id', request()->id)->first();
                
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

   
    // Delete Non Academic custom related links
    public function deleteCustomLink(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required',
            'type' => 'required',
            'role' => 'required',
            'slug' => 'required',
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

                    if (CustomRelatedLink::where('id', request()->id)->delete()) {

                        return $this->sendResponse('true', 'Custom Link Deleted', 200);
                    } else {

                        return $this->sendResponse('false', 'Something went Wrong!', 404);
                    }

                    $logtype = LogType::select('id')->where('type', '=', 'non-academic')->first();

                    if (is_null($logtype)) {

                        return $this->sendResponse($logtype, 'Log type data not found', 404);
                    } else {

                        $dta = array();
                        $dta['log_type_id'] = $logtype['id'];
                        $dta['user_id'] = $user_id;
                        $dta['ip'] = $request->ip();
                        $dta['action'] = 'Deleted ' . strtolower($academic->title_en) . ' custom related link';
                        $dta['ac_non_ac_id'] = $ac_non_ac_id;
                        $dta['related_link_id'] = 'custom-link';
                        UserLogSave($dta);
                    }
                } else {
                    return $this->sendResponse("false", trans('en_lang.ACCESS_DENIED'), 401);
                }
            } else {
                return $this->sendResponse("false", trans('en_lang.ACCESS_DENIED'), 401);
            }
        }
    }

    // Update order number for custom link
    public function updateCustomLinkOrder(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'slug' => 'required',
            'role' => 'required',
            'type' => 'required',
            'orders' => 'required'
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

                
                foreach ($request->orders as $key => $order) {
                    
                    CustomRelatedLink::where('id', $order['id'])->update(['order_on' => $key + 1]);
                }

                return $this->sendResponse('Update Successfully.', 'DATA FOUND', 200);

                $logtype = LogType::select('id')->where('type', '=', 'non-academic')->first();

                if (is_null($logtype)) {

                    return $this->sendResponse($logtype, 'Log type data not found', 404);
                } else {

                    $dta = array();
                    $dta['log_type_id'] = $logtype['id'];
                    $dta['user_id'] = $user_id;
                    $dta['ip'] = $request->ip();
                    $dta['action'] = 'Order list updated for Non-Academic custom related link';
                    $dta['ac_non_ac_id'] = $ac_non_ac_id;
                    $dta['related_link_id'] = 'custom-link';
                    UserLogSave($dta);
                }
                
            } else {
                return $this->sendResponse('false', trans('en_lang.ACCESS_DENIED'), 401);
            }
        }
    }


    // Store VC related links
    public function storeVCRelatedLink(Request $request)
    {

        $input = $request->all();
        //\Log::info($input);
        $user = Auth::user();
        $validator = Validator::make($request->all(), [
            'slug' => 'required',
            'role' => 'required',
            'type' => 'required',
            'link_name_en' => 'required',
            'group_type_id'=> 'required',
            'content_type' => 'required',
            'approval_status' => 'required'
        ]);
        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors(), 422, false);
        } else {
            $academic =  AcademicAndNonAcademic::where('slug', request()->slug)->first();

            $ac_non_ac_id = $academic->id;
            $user_id      =  $request->user()->id;
            $role         = request()->role;
            if (checkUserRole($ac_non_ac_id, $user_id, $role)) {

                    
                    $link_slug = str_slug($request->link_name_en, '-');
                    $slug = request()->slug.'/'.$link_slug;
                    $file = VCLink::where('id', request()->id)->first();
                    if ($request->hasFile('file')) {
                        $validator = Validator::make($request->all(), [
                           'file' => 'required|mimes:doc,docx,pdf'
                        ]);

                        if ($validator->fails()) {
                            return $this->sendError('Validation Error.', $validator->errors(), 422, false);
                        } 
                        if ($request->link_name_en == 'Curriculum-Vitae') {

                            $head = UserVisibility::where('ac_non_ac_id',$ac_non_ac_id)->where('role_id',$academic->head)->with('getHeadFaculty')->first(); 
                            if(!empty($head)){
                            
                                $vc_name = $head->getHeadFaculty->title.'_'.$head->getHeadFaculty->first_name.'_'.$head->getHeadFaculty->middle_name.'_'.$head->getHeadFaculty->last_name;
                            }else{
                                $vc_name = '';
                            }

                            $cv = Storage::disk('public')->exists($file->file);
                            if($cv == true) {
                                DeleteOldPicture($file->file);
                            }
                            $uploaded_file = 'CV_of_Vice_Chancellor('.$vc_name.')'.'.'.$request->file('file')->guessExtension();
                            $input['file'] = '/'.$request->file('file')->storeAs('/pdf',$uploaded_file,'public');
                        }else{
                            $file_data = array();
                            $file_data['file']     = $input['file'];
                            $file_data['path']     = 'file/'. $ac_non_ac_id.'/vc_pdf' ;
                            $file_data['filename'] = $input['file']->getClientOriginalName();
                            $fileUp = FileUpload($file_data);
                            $results = json_decode($fileUp->content(), true);
                            if ($results['code'] == 1000) {
                                $input['file'] = $results['path'];
                                if (!empty($file)) {
                                    DeleteOldPicture($file->file);
                                }
                            } 
                        }
                    }


                    if (isset($request->id) != null) {
                        $data = array();
                        if (request()->approval_status == 'Approved') {
                        $data['link_name_en']   = $request->link_name_en;
                        if ($request->link_name_hi == 'null' || $request->link_name_hi == Null) {
                            $data['link_name_hi'] = Null;
                        }else{
                            $data['link_name_hi'] = $request->link_name_hi;
                        }
                        if ($request->link_name_ur == 'null' || $request->link_name_ur == Null) {
                            $data['link_name_ur'] = Null;
                        }else{
                            $data['link_name_ur'] = $request->link_name_ur;
                        }
                        if($request->content_type == 'text') {
                            if ($request->description_en == 'null' || $request->description_en == Null) {
                                $data['description_en'] = Null;
                            }else{
                                $data['description_en'] = $request->description_en;
                            }
                            if ($request->description_hi == 'null' || $request->description_hi == Null) {
                                $data['description_hi'] = Null;
                            }else{
                                $data['description_hi'] = $request->description_hi;
                            }
                            if ($request->description_ur == 'null' || $request->description_ur == Null) {
                                $data['description_ur'] = Null;
                            }else{
                                $data['description_ur'] = $request->description_ur;
                            }
                        }elseif($request->content_type == 'file') {
                            $data['file'] = $input['file'];
                        }elseif($request->content_type == 'link') {
                            $data['link'] = $request->link;
                        }
                        
                        
                        $data['link_name_en_draft']   = '';
                        $data['link_name_hi_draft']   = '';
                        $data['link_name_ur_draft']   = '';
                        $data['description_en_draft'] = '';
                        $data['description_hi_draft'] = '';
                        $data['description_ur_draft'] = '';
                        
                    } else {
                        $data['link_name_en_draft']             = $request->link_name_en;
                        if ($request->link_name_hi == 'null' || $request->link_name_hi == Null) {
                            $data['link_name_hi_draft'] = Null;
                        }else{
                            $data['link_name_hi_draft'] = $request->link_name_hi;
                        }
                        if ($request->link_name_ur == 'null' || $request->link_name_ur == Null) {
                            $data['link_name_ur_draft'] = Null;
                        }else{
                            $data['link_name_ur_draft'] = $request->link_name_ur;
                        }
                        if ($request->description_en == 'null' || $request->description_en == Null) {
                            $data['description_en_draft'] = Null;
                        }else{
                            $data['description_en_draft'] = $request->description_en;
                        }
                        if ($request->description_hi == 'null' || $request->description_hi == Null) {
                            $data['description_hi_draft'] = Null;
                        }else{
                            $data['description_hi_draft'] = $request->description_hi;
                        }
                        if ($request->description_ur == 'null' || $request->description_ur == Null) {
                            $data['description_ur_draft'] = Null;
                        }else{
                            $data['description_ur_draft'] = $request->description_ur;
                        }
                        
                    }
                       $data['approval_status'] =  $request->approval_status;
                        
                        $data = VCLink::where('id',$request->id)->update($data);
                        if ($data) {

                            $logtype = LogType::select('id')->where('type', '=', 'non-academic')->first();
                            if (is_null($logtype)) {
                                return $this->sendResponse($logtype, 'Log type data not found', 404);
                            } else {
                                $dta = array();
                                $dta['log_type_id'] = $logtype['id'];
                                $dta['user_id'] = $user_id;
                                $dta['ip'] = $request->ip();
                                $dta['action'] = 'VC related link updated';
                                $dta['ac_non_ac_id'] = $ac_non_ac_id;
                                $dta['related_link_id'] = 'add-content';
                                UserLogSave($dta);
                            }
                            return $this->sendResponse($data, trans('en_lang.DATA_UPDATED'), 200);
                        } else {
                            return $this->sendResponse($data, 'Some thing went wrong', 204, false);
                        }
                    }else{
                        $vcdata = array();
                        $vcdata['content_type']        = $request->content_type;
                        $vcdata['ac_non_ac_id']        = $ac_non_ac_id;
                        $vcdata['group_type_id']       = $request->group_type_id;
                        $vcdata['link_name_en']        = $request->link_name_en;
                        $vcdata['link_name_hi']        = $request->link_name_hi;
                        $vcdata['link_name_ur']        = $request->link_name_ur;
                        $vcdata['approval_status']     = $request->approval_status;
                        if($request->content_type == 'text') {
                            $vcdata['slug']      = $slug;
                            $vcdata['description_en']      = $request->description_en;
                            $vcdata['description_hi']      = $request->description_hi;
                            $vcdata['description_ur']      = $request->description_ur;
                        }elseif($request->content_type == 'file') {
                            $vcdata['file']      = $input['file'];
                        }elseif($request->content_type == 'link') {
                            $vcdata['link']      = $input['link'];
                        } 
                        if (request()->approval_status != 'Approved') {
                            $vcdata['link_name_en_draft']  = $request->link_name_en;
                            $vcdata['link_name_hi_draft']  = $request->link_name_hi;
                            $vcdata['link_name_ur_draft']  = $request->link_name_ur;
                            $vcdata['description_en_draft']= $request->description_en;
                            $vcdata['description_hi_draft']= $request->description_hi;
                            $vcdata['description_ur_draft']= $request->description_ur;
                            $vcdata['file_draft']          = $request->file;
                            $vcdata['link_draft']          = $request->link;  
                        }                       
                        $data = VCLink::create($vcdata);
                        if ($data) {
                            $logtype = LogType::select('id')->where('type', '=', 'non-academic')->first();
                            if (is_null($logtype)) {
                                return $this->sendResponse($logtype, 'Log type data not found', 404);
                            } else {
                                $dta = array();
                                $dta['log_type_id'] = $logtype['id'];
                                $dta['user_id'] = $user_id;
                                $dta['ip'] = $request->ip();
                                $dta['action'] = 'VC related link created';
                                $dta['ac_non_ac_id'] = $ac_non_ac_id;
                                $dta['related_link_id'] = 'add-content';
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

    // Get Single VC links
    public function getSingleVcLink(Request $request)
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

                $data = VCLink::where('id', request()->id)->first();
                
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

    // Get VC links
    public function getVCLinks(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'slug' => 'required',
            'role' => 'required',
            'type' => 'required',
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
                $data = VCLink::where('ac_non_ac_id', $ac_non_ac_id)->orderBy('link_order', 'ASC')->orderBy('group_type_id', 'ASC')->get();
                
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

    // Delete Vc links
    public function deleteVcLink(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required',
            'type' => 'required',
            'role' => 'required',
            'slug' => 'required',
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

                    if (VCLink::where('id', request()->id)->delete()) {

                        $logtype = LogType::select('id')->where('type', '=', 'non-academic')->first();

                        if (is_null($logtype)) {

                            return $this->sendResponse($logtype, 'Log type data not found', 404);
                        } else {

                            $dta = array();
                            $dta['log_type_id'] = $logtype['id'];
                            $dta['user_id'] = $user_id;
                            $dta['ip'] = $request->ip();
                            $dta['action'] = 'Deleted  vc related link';
                            $dta['ac_non_ac_id'] = $ac_non_ac_id;
                            $dta['related_link_id'] = 'add-content';
                            UserLogSave($dta);
                        }

                        return $this->sendResponse('true', 'VC Link Deleted', 200);
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

    // Update order number for vc related link
    public function updateVCLinkOrder(Request $request)
    {
        $input = $request->all();
        
        $validator = Validator::make($request->all(), [
            'slug' => 'required',
            'role' => 'required',
            'type' => 'required',
            'group_type_id' => 'required',
            'orders' => 'required'
            
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

                    if (!empty($request->orders)) {
                        foreach ($request->orders as $key => $order) {
                            /*\Log::info('orders = '.$order);

                           \Log::info('group_type_id = '.request()->group_type_id);
                           //\Log::info('order_id = '.$order['id']);
                           //\Log::info('key = '.$key + 1);*/
                            
                            VCLink::where('group_type_id', request()->group_type_id)->where('id', $order['id'])->update(['link_order' => $key + 1]);
                        }
                        return $this->sendResponse('Update Successfully.', 'DATA FOUND', 200);
                    }

                    $logtype = LogType::select('id')->where('type', '=', 'non-academic')->first();

                    if (is_null($logtype)) {

                        return $this->sendResponse($logtype, 'Log type data not found', 404);
                    } else {

                        $dta = array();
                        $dta['log_type_id'] = $logtype['id'];
                        $dta['user_id'] = $user_id;
                        $dta['ip'] = $request->ip();
                        $dta['action'] = 'Order list updated for vc related link';
                        $dta['ac_non_ac_id'] = $ac_non_ac_id;
                        $dta['related_link_id'] = 'add-content';
                        UserLogSave($dta);
                    }
                } else {
                    return $this->sendResponse('false', trans('en_lang.ACCESS_DENIED'), 401);
                }
            } else {
                return $this->sendResponse('false', trans('en_lang.ACCESS_DENIED'), 401);
            }
        }
    }


    // Get VC Log list
    public function getVCUserLog(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'slug' => 'required',
            'role' => 'required',
            'type' => 'required',
            'path' => 'required'
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

                
                $slug = request()->slug.'/'.request()->path;
                $rlink = VCLink::select('id')->where('slug', $slug)->first();
                if ($rlink) {
                   $rlink_id = $rlink->id;
                }else{
                    $rlink_id = request()->path;
                }
                $userLog = UserLog::select('id', 'user_id', 'ip', 'action', 'ac_non_ac_id', 'related_link_id', 'created_at')->where('related_link_id', $rlink_id)->where('ac_non_ac_id', $ac_non_ac_id)->with('getUserRole.getUser:id,title,first_name,middle_name,last_name', 'getUserRole.getRole', 'getUserRole.getDesignation')->orderBy('created_at', 'DESC')->paginate(env('ITEM_PER_PAGE'));

                if ($userLog) {
                    return $this->sendResponse($userLog, trans('en_lang.DATA_FOUND'), 200);
                } else {
                    return $this->sendResponse($userLog, trans('en_lang.DATA_NOT_FOUND'), 204);
                }
            } else {
                return $this->sendResponse($userLog, trans('en_lang.ACCESS_DENIED'), 401, false);
            }
        }
    }


    // Store VC Slider
    public function addVCSlider(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'role' => 'required',
            'slug' => 'required',
            'image' => 'required'
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

                $img_data = array();
                $results = array();
                $final_data = array();
                $img_data['image'] = $input['image'];
                $img_data['path'] = 'images/'.$ac_non_ac_id.'/slider';
                $img_data['filename'] = $input['image']->getClientOriginalName();
                $fileUp = ImageUpload($img_data);
                $results = json_decode($fileUp->content(), true);
                if ($results['code'] == 1000) {
                    $final_data['ac_non_ac_id'] = $ac_non_ac_id;
                    $final_data['image'] = $results['path'];
                    $data = AcNonAcImage::create($final_data);
                    $logtype = LogType::select('id')->where('type', '=', 'non-academic')->first();
                    if (is_null($logtype)) {
                        return $this->sendResponse($logtype, 'Log type data not found', 404);
                    } else {
                        $dta = array();
                        $dta['log_type_id'] = $logtype->id;
                        $dta['user_id'] = $user_id;
                        $dta['ip'] = $request->ip();
                        $dta['action'] = 'VC Slider Added';
                        $dta['ac_non_ac_id'] = $ac_non_ac_id;
                        $dta['related_link_id'] = 'add-slider';
                        UserLogSave($dta);
                    }
                    return $this->sendResponse($data, 'VC Slider Uploaded', 200, true);
                } else {
                    return $this->sendResponse($data, trans('en_lang.ACCESS_DENIED'), 401, false);
                }
            } else {
                return $this->sendResponse($data, trans('en_lang.ACCESS_DENIED'), 401, false);
            }
        }
    }

    // Get VC Slider
    public function getVCSlider(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'slug' => 'required',
            'role' => 'required',
            'type' => 'required',
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
                $data = AcNonAcImage::where('ac_non_ac_id', $ac_non_ac_id)->orderBy('created_at', 'ASC')->get();
                
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

    // Get Epay Slips
    public function getEpaySlips(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'slug' => 'required',
            'role' => 'required',
            'type' => 'required',
            'eid' => 'required',
            'month' => 'required|numeric',
            'year' => 'required|numeric',
            'sal_type' => 'required'
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
                $check_user = EPaySlips::select(DB::raw("DESIG_NAME,DEPT_NAME,PAYBAND,GRADE,SAL_NO,INCR,SUM(BASIC_CAL) as BASIC_CAL,NAME,SUM(RNPA) as RNPA,SUM(RDA) as RDA,SUM(RHRA) as RHRA,SUM(RTRAN) as RTRAN,SUM(EDU) as EDU,SUM(OTH1A) as OTH1A,SUM(OTH2A) as OTH2A,SUM(OTH3A) as OTH3A,SUM(OTH4A) as OTH4A,SUM(OTH5A) as OTH5A,SUM(OTH6A) as OTH6A ,OTH_DES1,OTH_DES2,OTH_DES3,OTH_DES4,OTH_DES5, OTH_DES6,SUM(ITAX)  as ITAX,SUM(LIC) as LIC,SUM(LIN_FEE) as LIN_FEE,SUM(PF) as PF,SUM(PF_LOAN) as PF_LOAN,SUM(NPS) as NPS,SUM(CGIS) as CGIS,SUM(FES_ADV) as FES_ADV,SUM(VEH_ADV) as VEH_ADV,SUM(VC_LOAN) as VC_LOAN,SUM(HBL_UGC) as HBL_UGC,SUM(HBL_INT) as HBL_INT,SUM(ELECT) as ELECT,SUM(MAS) as MAS,SUM(TSA) as TSA,SUM(NTSA) as NTSA,SUM(TCHSA) as TCHSA,SUM(TWS) as TWS,SUM(VBSS) as VBSS,MIS_DES1,MISC1,MIS_DES2,MISC2,MIS_DES3,MISC3,MIS_DES4,MISC4,BANKNAME,BANK_AC,CHEQ_NO,CHEQ_DT,REMARK,SUM(DEDU) as DEDU,SUM(NET_PAY) as NET_PAY,SUM(RGROSS) as RGROSS,FFDATE,FTDATE,PER_ID,PAYPAYBAND,FGDES,SUM(REV_STM) as REV_STM,SUM(SAL_ADV) as SAL_ADV,SUM(RECOVRY) as RECOVRY,SUM(PER_PAY) as PER_PAY,SUM(SPL_PAY) as SPL_PAY,PAN, group_concat(concat(FFDATE, ' - ', FTDATE) separator ' , ') as period"))->where('PER_ID',request()->eid)->where('MTH_NO',request()->month)->where('YR_NO',request()->year)->where('SAL_CODE',request()->sal_type)->first();
                $data1 =array();
                $data1['month'] = date("F", strtotime('00-'.request()->month.'-01'));
                $data1['year'] = request()->year;
                if (request()->sal_type == "1.00") {
                   $data1['type'] = "Main";
                }elseif (request()->sal_type == "2.00") {
                   $data1['type'] = "Supplementary-I";
                }elseif (request()->sal_type == "3.00") {
                   $data1['type'] = "Supplementary-II";
                }elseif (request()->sal_type == "4.00") {
                   $data1['type'] = "Supplementary-III";
                }elseif (request()->sal_type == "5.00") {
                   $data1['type'] = "Supplementary-IV";
                }elseif (request()->sal_type == "6.00") {
                   $data1['type'] = "Supplementary-V";
                }elseif (request()->sal_type == "7.00") {
                   $data1['type'] = "Supplementary-VI";
                }elseif (request()->sal_type == "8.00") {
                   $data1['type'] = "Supplementary-VII";
                }elseif (request()->sal_type == "9.00") {
                   $data1['type'] = "Bonus";
                }

                $left = array();
                $right = array();
                
                if ($check_user->BASIC_CAL != null) {
                    //Start Left column

                    if($check_user->SPL_PAY == "" || $check_user->SPL_PAY == "0" ||  $check_user->SPL_PAY == "NULL" || $check_user->SPL_PAY == " " || $check_user->SPL_PAY == "0.00"){

                        $left['SPL_PAY_LABEL'] = "SPL. PAY";
                        $left['SPL_PAY_VALUE'] = "0.00";
                    }else{
                        $left['SPL_PAY_LABEL'] = "SPL. PAY";
                        $left['SPL_PAY_VALUE'] = $check_user->SPL_PAY;
                    }

                    if($check_user->PER_PAY == "" || $check_user->PER_PAY == "0" ||  $check_user->PER_PAY == "NULL" || $check_user->PER_PAY == " " || $check_user->PER_PAY == "0.00")  {

                        $left['PER_PAY_LABEL'] = "PERSONAL PAY";
                        $left['PER_PAY_VALUE'] = "0.00";
                    }else{
                        $left['PER_PAY_LABEL'] = "PERSONAL PAY";
                        $left['PER_PAY_VALUE'] = $check_user->PER_PAY;
                    }

                    if($check_user->BASIC_CAL == "" || $check_user->BASIC_CAL == "0" ||  $check_user->BASIC_CAL == "NULL" || $check_user->BASIC_CAL == " " || $check_user->BASIC_CAL == "0.00")  {

                        $left['BASICSAL_LABEL'] = "PAY";
                        $left['BASICSAL_VALUE'] = "0.00";
                    }else{
                        $left['BASICSAL_LABEL'] = "PAY";
                        $left['BASICSAL_VALUE'] = $check_user->BASIC_CAL;
                    }

                    if($check_user->RNPA == "" || $check_user->RNPA == "0" ||  $check_user->RNPA == "NULL" || $check_user->RNPA == " " || $check_user->RNPA == "0.00")  {

                        $left['RNPA_LABEL'] = "NPA";
                        $left['RNPA_VALUE'] = "0.00";
                    }else{
                        $left['RNPA_LABEL'] = "NPA";
                        $left['RNPA_VALUE'] = $check_user->RNPA;
                    }


                    if($check_user->RDA == "" || $check_user->RDA == "0" ||  $check_user->RDA == "NULL" || $check_user->RDA == " " || $check_user->RDA == "0.00")  {

                        $left['RDA_LABEL'] = "DA";
                        $left['RDA_VALUE'] = "0.00";
                    }else{
                        $left['RDA_LABEL'] = "DA";
                        $left['RDA_VALUE'] = $check_user->RDA;
                    }

                    if($check_user->RHRA == "" || $check_user->RHRA == "0" ||  $check_user->RHRA == "NULL" || $check_user->RHRA == " " || $check_user->RHRA == "0.00")  {
                        $left['RHRA_LABEL'] = "HRA";
                        $left['RHRA_VALUE'] = "0.00";
                    }else{
                        $left['RHRA_LABEL'] = "HRA";
                        $left['RHRA_VALUE'] = $check_user->RHRA;
                    }

                    if($check_user->RTRAN == "" || $check_user->RTRAN == "0" ||  $check_user->RTRAN == "NULL" || $check_user->RTRAN == " " || $check_user->RTRAN == "0.00")  {
                        $left['STRAN_LABEL'] = "TRANS";
                        $left['STRAN_VALUE'] = "0.00";
                    }else{
                        $left['STRAN_LABEL'] = "TRANS";
                        $left['STRAN_VALUE'] = $check_user->RTRAN;
                    }

                    if($check_user->EDU == "" || $check_user->EDU == "0" ||  $check_user->EDU == "NULL" || $check_user->EDU == " " || $check_user->EDU == "0.00")  {

                        $left['EDU_LABEL'] = "C. EDU";
                        $left['EDU_VALUE'] = "0.00";
                    }else{
                        $left['EDU_LABEL'] = "C. EDU";
                        $left['EDU_VALUE'] = $check_user->EDU;
                    }

                    if($check_user->OTH1A == "" || $check_user->OTH1A == "0" ||  $check_user->OTH1A == "NULL" || $check_user->OTH1A == " " || $check_user->OTH1A == "0.00")  {
                        $left['OTH1N_LABEL'] = $check_user->OTH_DES1;
                        $left['OTH1N_VALUE'] = "0.00";
                    }else{
                        $left['OTH1N_LABEL'] = $check_user->OTH_DES1;
                        $left['OTH1N_VALUE'] = $check_user->OTH1A;
                    }

                    if($check_user->OTH2A == "" || $check_user->OTH2A == "0" ||  $check_user->OTH2A == "NULL" || $check_user->OTH2A == " " || $check_user->OTH2A == "0.00")  {
                        $left['OTH2N_LABEL'] = $check_user->OTH_DES2;
                        $left['OTH2N_VALUE'] = "0.00";
                    }else{
                        $left['OTH2N_LABEL'] = $check_user->OTH_DES2;
                        $left['OTH2N_VALUE'] = $check_user->OTH2A;
                    }

                    if($check_user->OTH3A == "" || $check_user->OTH3A == "0" ||  $check_user->OTH3A == "NULL" || $check_user->OTH3A == " " || $check_user->OTH3A == "0.00")  {

                        $left['OTH3N_LABEL'] = $check_user->OTH_DES3;
                        $left['OTH3N_VALUE'] = "0.00";
                    }else{
                        $left['OTH3N_LABEL'] = $check_user->OTH_DES3;
                        $left['OTH3N_VALUE'] = $check_user->OTH3A;
                    }

                    if($check_user->OTH4A == "" || $check_user->OTH4A == "0" ||  $check_user->OTH4A == "NULL" || $check_user->OTH4A == " " || $check_user->OTH4A == "0.00")  {

                        $left['OTH4N_LABEL'] = $check_user->OTH_DES4;
                        $left['OTH4N_VALUE'] = "0.00";
                    }else{
                        $left['OTH4N_LABEL'] = $check_user->OTH_DES4;
                        $left['OTH4N_VALUE'] = $check_user->OTH4A;
                    }

                    if($check_user->OTH5A == "" || $check_user->OTH5A == "0" ||  $check_user->OTH5A == "NULL" || $check_user->OTH5A == " " || $check_user->OTH5A == "0.00")  {

                        $left['OTH5N_LABEL'] = $check_user->OTH_DES5;
                        $left['OTH5N_VALUE'] = "0.00";
                    }else{
                        $left['OTH5N_LABEL'] = $check_user->OTH_DES5;
                        $left['OTH5N_VALUE'] = $check_user->OTH5A;
                    }

                    if($check_user->OTH6A == "" || $check_user->OTH6A == "0" ||  $check_user->OTH6A == "NULL" || $check_user->OTH6A == " " || $check_user->OTH6A == "0.00")  {

                        $left['OTH6N_LABEL'] = $check_user->OTH_DES6;
                        $left['OTH6N_VALUE'] = "0.00";
                    }else{
                        $left['OTH6N_LABEL'] = $check_user->OTH_DES6;
                        $left['OTH6N_VALUE'] = $check_user->OTH6A;
                    }


                    //Start right column

                    if($check_user->ITAX == "" || $check_user->ITAX == "0" ||  $check_user->ITAX == "NULL" || $check_user->ITAX == " " || $check_user->ITAX == "0.00")  { 

                        $right['ITAX_LABEL'] = "INCOME TAX";
                        $right['ITAX_VALUE'] = "0.00";
                    }else{
                        $right['ITAX_LABEL'] = "INCOME TAX";
                        $right['ITAX_VALUE'] = $check_user->ITAX;
                    }

                    if($check_user->LIC == "" || $check_user->LIC == "0" ||  $check_user->LIC == "NULL" || $check_user->LIC == " " || $check_user->LIC == "0.00")  { 

                        $right['LIC_LABEL'] = "LIC";
                        $right['LIC_VALUE'] = "0.00";
                    }else{
                        $right['LIC_LABEL'] = "LIC";
                        $right['LIC_VALUE'] = $check_user->LIC;
                    }

                    if($check_user->LIN_FEE == "" || $check_user->LIN_FEE == "0" ||  $check_user->LIN_FEE == "NULL" || $check_user->LIN_FEE == " " || $check_user->LIN_FEE == "0.00")  { 

                        $right['LIN_FEE_LABEL'] = "LICENSE FEE";
                        $right['LIN_FEE_VALUE'] = "0.00";
                    }else{
                        $right['LIN_FEE_LABEL'] = "LICENSE FEE";
                        $right['LIN_FEE_VALUE'] = $check_user->LIN_FEE;
                    }

                    if($check_user->PF == "" || $check_user->PF == "0" ||  $check_user->PF == "NULL" || $check_user->PF == " " || $check_user->PF == "0.00")  { 

                        $right['PF_LABEL'] = "PF";
                        $right['PF_VALUE'] = "0.00";
                    }else{
                        $right['PF_LABEL'] = "PF";
                        $right['PF_VALUE'] = $check_user->PF;
                    }

                    if($check_user->PF_LOAN == "" || $check_user->PF_LOAN == "0" ||  $check_user->PF_LOAN == "NULL" || $check_user->PF_LOAN == " " || $check_user->PF_LOAN == "0.00")  { 

                        $right['PF_LOAN_LABEL'] = "PF LOAN";
                        $right['PF_LOAN_VALUE'] = "0.00";
                    }else{
                        $right['PF_LOAN_LABEL'] = "PF LOAN";
                        $right['PF_LOAN_VALUE'] = $check_user->PF_LOAN;
                    }

                    if($check_user->NPS == "" || $check_user->NPS == "0" ||  $check_user->NPS == "NULL" || $check_user->NPS == " " || $check_user->NPS == "0.00")  { 

                        $right['NPS_LABEL'] = "NPS";
                        $right['NPS_VALUE'] = "0.00";
                    }else{
                        $right['NPS_LABEL'] = "NPS";
                        $right['NPS_VALUE'] = $check_user->NPS;
                    }

                    if($check_user->CGIS == "" || $check_user->CGIS == "0" ||  $check_user->CGIS == "NULL" || $check_user->CGIS == " " || $check_user->CGIS == "0.00")  { 

                        $right['CGIS_LABEL'] = "CGIS";
                        $right['CGIS_VALUE'] = "0.00";
                    }else{
                        $right['CGIS_LABEL'] = "CGIS";
                        $right['CGIS_VALUE'] = $check_user->CGIS;
                    }

                    if($check_user->FES_ADV == "" || $check_user->FES_ADV == "0" ||  $check_user->FES_ADV == "NULL" || $check_user->FES_ADV == " " || $check_user->FES_ADV == "0.00")  { 

                        $right['FES_ADV_LABEL'] = "FESTIVAL ADV.";
                        $right['FES_ADV_VALUE'] = "0.00";
                    }else{
                        $right['FES_ADV_LABEL'] = "FESTIVAL ADV.";
                        $right['FES_ADV_VALUE'] = $check_user->FES_ADV;
                    }

                    if($check_user->VEH_ADV == "" || $check_user->VEH_ADV == "0" ||  $check_user->VEH_ADV == "NULL" || $check_user->VEH_ADV == " " || $check_user->VEH_ADV == "0.00")  { 

                        $right['VEH_ADV_LABEL'] = "VEHICLE ADV.";
                        $right['VEH_ADV_VALUE'] = "0.00";
                    }else{
                        $right['VEH_ADV_LABEL'] = "VEHICLE ADV.";
                        $right['VEH_ADV_VALUE'] = $check_user->VEH_ADV;
                    }

                    if($check_user->VC_LOAN == "" || $check_user->VC_LOAN == "0" ||  $check_user->VC_LOAN == "NULL" || $check_user->VC_LOAN == " " || $check_user->VC_LOAN == "0.00")  { 

                        $right['VC_LOAN_LABEL'] = "VC LOAN";
                        $right['VC_LOAN_VALUE'] = "0.00";
                    }else{
                        $right['VC_LOAN_LABEL'] = "VC LOAN";
                        $right['VC_LOAN_VALUE'] = $check_user->VC_LOAN;
                    }

                    if($check_user->HBL_UGC == "" || $check_user->HBL_UGC == "0" ||  $check_user->HBL_UGC == "NULL" || $check_user->HBL_UGC == " " || $check_user->HBL_UGC == "0.00")  { 

                        $right['HBL_UGC_LABEL'] = "HBL UGC";
                        $right['HBL_UGC_VALUE'] = "0.00";
                    }else{
                        $right['HBL_UGC_LABEL'] = "HBL UGC";
                        $right['HBL_UGC_VALUE'] = $check_user->HBL_UGC;
                    }

                    if($check_user->HBL_INT == "" || $check_user->HBL_INT == "0" ||  $check_user->HBL_INT == "NULL" || $check_user->HBL_INT == " " || $check_user->HBL_INT == "0.00")  {

                        $right['HBL_INT_LABEL'] = "HBL INT";
                        $right['HBL_INT_VALUE'] = "0.00";
                    }else{
                        $right['HBL_INT_LABEL'] = "HBL INT";
                        $right['HBL_INT_VALUE'] = $check_user->HBL_INT;
                    }

                    if($check_user->ELECT == "" || $check_user->ELECT == "0" ||  $check_user->ELECT == "NULL" || $check_user->ELECT == " " || $check_user->ELECT == "0.00")  {

                        $right['ELECT_LABEL'] = "ELECTRICITY";
                        $right['ELECT_VALUE'] = "0.00";
                    }else{
                        $right['ELECT_LABEL'] = "ELECTRICITY";
                        $right['ELECT_VALUE'] = $check_user->ELECT;
                    }

                    if($check_user->MAS == "" || $check_user->MAS == "0" ||  $check_user->MAS == "NULL" || $check_user->MAS == " " || $check_user->MAS == "0.00")  {

                        $right['MAS_LABEL'] = "MAS";
                        $right['MAS_VALUE'] = "0.00";
                    }else{
                        $right['MAS_LABEL'] = "MAS";
                        $right['MAS_VALUE'] = $check_user->MAS;
                    }

                    if($check_user->TSA == "" || $check_user->TSA == "0" ||  $check_user->TSA == "NULL" || $check_user->TSA == " " || $check_user->TSA == "0.00")  {

                        $right['TSA_LABEL'] = "TEACHING STAFF ASSO.";
                        $right['TSA_VALUE'] = "0.00";
                    }else{
                        $right['TSA_LABEL'] = "TEACHING STAFF ASSO.";
                        $right['TSA_VALUE'] = $check_user->TSA;
                    }

                    if($check_user->TCHSA == "" || $check_user->TCHSA == "0" ||  $check_user->TCHSA == "NULL" || $check_user->TCHSA == " " || $check_user->TCHSA == "0.00")  {

                        $right['TCHSA_LABEL'] = "TECHNICAL";
                        $right['TCHSA_VALUE'] = "0.00";
                    }else{
                        $right['TCHSA_LABEL'] = "TECHNICAL";
                        $right['TCHSA_VALUE'] = $check_user->TCHSA;
                    }

                    if($check_user->NTSA == "" || $check_user->NTSA == "0" ||  $check_user->NTSA == "NULL" || $check_user->NTSA == " " || $check_user->NTSA == "0.00")  {

                        $right['NTSA_LABEL'] = "NTSA";
                        $right['NTSA_VALUE'] = "0.00";
                    }else{
                        $right['NTSA_LABEL'] = "NTSA";
                        $right['NTSA_VALUE'] = $check_user->NTSA;
                    }

                    if($check_user->TWS == "" || $check_user->TWS == "0" ||  $check_user->TWS == "NULL" || $check_user->TWS == " " || $check_user->TWS == "0.00")  {

                        $right['TWS_LABEL'] = "TWS";
                        $right['TWS_VALUE'] = "0.00";
                    }else{
                        $right['TWS_LABEL'] = "TWS";
                        $right['TWS_VALUE'] = $check_user->TWS;
                    }

                    if($check_user->VBSS == "" || $check_user->VBSS == "0" ||  $check_user->VBSS == "NULL" || $check_user->VBSS == " " || $check_user->VBSS == "0.00")  {

                        $right['VBSS_LABEL'] = "VBSS";
                        $right['VBSS_VALUE'] = "0.00";
                    }else{
                        $right['VBSS_LABEL'] = "VBSS";
                        $right['VBSS_VALUE'] = $check_user->VBSS;
                    }

                    if($check_user->MISC1 == "" || $check_user->MISC1 == "0" ||  $check_user->MISC1 == "NULL" || $check_user->MISC1 == " " || $check_user->MISC1 == "0.00")  {

                        $right['MISC1_LABEL'] = $check_user->MIS_DES1;
                        $right['MISC1_VALUE'] = "0.00";
                    }else{
                        $right['MISC1_LABEL'] = $check_user->MIS_DES1;
                        $right['MISC1_VALUE'] = $check_user->MISC1;
                    }

                    if($check_user->MISC2 == "" || $check_user->MISC2 == "0" ||  $check_user->MISC2 == "NULL" || $check_user->MISC2 == " " || $check_user->MISC2 == "0.00")  {

                        $right['MISC2_LABEL'] = $check_user->MIS_DES2;
                        $right['MISC2_VALUE'] = "0.00";
                    }else{
                        $right['MISC2_LABEL'] = $check_user->MIS_DES2;
                        $right['MISC2_VALUE'] = $check_user->MISC2;
                    }

                    if($check_user->MISC3 == "" || $check_user->MISC3 == "0" ||  $check_user->MISC3 == "NULL" || $check_user->MISC3 == " " || $check_user->MISC3 == "0.00")  {

                        $right['MISC3_LABEL'] = $check_user->MIS_DES3;
                        $right['MISC3_VALUE'] = "0.00";
                    }else{
                        $right['MISC3_LABEL'] = $check_user->MIS_DES3;
                        $right['MISC3_VALUE'] = $check_user->MISC3;
                    }

                    if($check_user->MISC4 == "" || $check_user->MISC4 == "0" ||  $check_user->MISC4 == "NULL" || $check_user->MISC4 == " " || $check_user->MISC4 == "0.00")  {

                        $right['MISC4_LABEL'] = $check_user->MIS_DES4;
                        $right['MISC4_VALUE'] = "0.00";
                    }else{
                        $right['MISC4_LABEL'] = $check_user->MIS_DES4;
                        $right['MISC4_VALUE'] = $check_user->MISC4;
                    }

                    if($check_user->REV_STM == "" || $check_user->REV_STM == "0" ||  $check_user->REV_STM == "NULL" || $check_user->REV_STM == " " || $check_user->REV_STM == "0.00")  {

                        $right['REV_STM_LABEL'] = "REVENUE STAMP";
                        $right['REV_STM_VALUE'] = "0.00";
                    }else{
                        $right['REV_STM_LABEL'] = "REVENUE STAMP";
                        $right['REV_STM_VALUE'] = $check_user->REV_STM;
                    }

                    if($check_user->SAL_ADV == "" || $check_user->SAL_ADV == "0" ||  $check_user->SAL_ADV == "NULL" || $check_user->SAL_ADV == " " || $check_user->SAL_ADV == "0.00")  {

                        $right['SAL_ADV_LABEL'] = "SAL ADV";
                        $right['SAL_ADV_VALUE'] = "0.00";
                    }else{
                        $right['SAL_ADV_LABEL'] = "SAL ADV";
                        $right['SAL_ADV_VALUE'] = $check_user->SAL_ADV;
                    }

                    if($check_user->RECOVRY == "" || $check_user->RECOVRY == "0" ||  $check_user->RECOVRY == "NULL" || $check_user->RECOVRY == " " || $check_user->RECOVRY == "0.00")  {

                        $right['RECOVRY_LABEL'] = "RECOVRY OF PAY";
                        $right['RECOVRY_VALUE'] = "0.00";
                    }else{
                        $right['RECOVRY_LABEL'] = "RECOVRY OF PAY";
                        $right['RECOVRY_VALUE'] = $check_user->RECOVRY;
                    }

                    $data = array();
                    if(strlen($check_user->PER_ID) == 3 ){
                       $PER_ID = '00'.$check_user->PER_ID;
                    }elseif(strlen($check_user->PER_ID) == 4){
                       $PER_ID = '0'.$check_user->PER_ID;
                    }else{
                       $PER_ID = $check_user->PER_ID;
                    }
                    $data['id'] = $check_user->id;
                    $data['PER_ID'] = $PER_ID;;
                    $data['NAME'] = $check_user->NAME;
                    $data['DESIG_NAME'] = $check_user->DESIG_NAME;
                    $data['DEPT_NAME'] = $check_user->DEPT_NAME;           
                    $data['SAL_NO'] = $check_user->SAL_NO;
                    $data['INCR'] = $check_user->INCR;
                    $data['PAYBAND'] = $check_user->PAYBAND;
                    $data['GRADE'] = $check_user->GRADE;
                    $data['PAYPAYBAND'] = $check_user->PAYPAYBAND;
                    $data['RGROSS'] = $check_user->RGROSS;
                    $data['DEDU'] = $check_user->DEDU;
                    $data['NET_PAY'] = $check_user->NET_PAY;
                    $data['REMARK'] = $check_user->REMARK;
                    $data['BANKNAME'] = $check_user->BANKNAME;
                    $data['BANK_AC'] = $check_user->BANK_AC;
                    $data['CHEQ_NO'] = $check_user->CHEQ_NO;
                    $data['CHEQ_DT'] = $check_user->CHEQ_DT;
                    $data['FGDES'] = $check_user->FGDES;
                    $data['period'] = $check_user->period;
                    $data['PAN'] = $check_user->PAN;
                    $data['MTH_NO'] =  $data1['month'];
                    $data['YR_NO'] = request()->year;
                    $data['TPNAME'] =  $data1['type'];            
                    $data['left_column'] = $left;
                    $data['right_column'] = $right;
                    return $this->sendResponse($data, trans('en_lang.DATA_FOUND'),200);
                }else{
                    return $this->sendError($data1, trans('en_lang.DATA_NOT_FOUND'),404);
                } 
            } else {
                return $this->sendResponse($user_id, trans('en_lang.ACCESS_DENIED'), 401, false);
            }
        }
    }

    // Get E-providen tFund
    public function getEprovidentFund(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'slug' => 'required',
            'role' => 'required',
            'type' => 'required',
            'year' => 'required',
            'eid' => 'required',
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
                $EProvidentFund = EProvidentFund::where('PER_ID',request()->eid)->where('F_YR',request()->year)->first();
                if(!empty($EProvidentFund)){
                    return $this->sendResponse($EProvidentFund, trans('en_lang.DATA_FOUND'),200);
                }else{
                    return $this->sendError(trans('en_lang.DATA_NOT_FOUND'), trans('en_lang.DATA_NOT_FOUND'),404);
                }
            } else {
                return $this->sendResponse($user_id, trans('en_lang.ACCESS_DENIED'), 401, false);
            }
        }
    }

    // Get E-Leave
    public function getEleave(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'slug' => 'required',
            'role' => 'required',
            'type' => 'required',
            'year' => 'required',
            'eid' => 'required',
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
                $emp_leave = ELeave::where('PER_ID',request()->eid)->where('YEAR',request()->year)->first();
                if(!empty($emp_leave)){
                    return $this->sendResponse($emp_leave, trans('en_lang.DATA_FOUND'),200);
                }else{
                    return $this->sendError(trans('en_lang.DATA_NOT_FOUND'), trans('en_lang.DATA_NOT_FOUND'),404);
                } 
            } else {
                return $this->sendResponse($user_id, trans('en_lang.ACCESS_DENIED'), 401, false);
            }
        }
    }
}
