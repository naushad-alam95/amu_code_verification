<?php

namespace App\Http\Controllers\API\v1;

use App\Http\Controllers\API\v1\BaseController as BaseController;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\AcademicAndNonAcademic;
use App\AcNonAcCommitteeFiles;
use App\FormerChairPerson;
use App\ResearchScholars;
use App\OnGoingProject;
use App\CompletedProject;
use App\Course;
use App\NoticeCircular;
use App\UserVisibility;
use App\AcNonAcGallery;
use App\RelatedLink;
use App\RelatedLinkData;
use App\GroupLink;
use App\LogType; 
use App\AcNonAcImage;
use App\UserContact;
use App\Notification;
use App\FileManagement;
use App\TransferStaff;
use App\UserLog;
use App\Tender;
use App\Job;
use App\User;
use Validator;
use Auth;
use Log;
use Hash;
use File;
use DB;
class AcademicAdminController extends BaseController
{

    public function __construct()
    {
        $this->middleware('auth:api');
    }

    // Get Department's related link data by related link path
    public function getDepartmentContent(Request $request)
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
                if ($academic->type == '1') {
                    $slug = request()->type.'/'.request()->slug.'/'.request()->path;
                }else{
                    $slug = request()->slug.'/'.request()->path;
                }
                $rlink_id = RelatedLink::where('slug', $slug)->pluck('id');
                if ($rlink_id->isEmpty()) {
                    return $this->sendResponse($rlink_id, 'Path Not Found', 404, false);
                }

                if (request()->path == 'about-the-department' || request()->path == 'home-page' || request()->path == 'office-of-the-dean' || request()->path == 'about-the-section' || request()->path == 'about-the-amucentres') {

                    $aboutData = RelatedLinkData::where('ac_non_ac_id', $ac_non_ac_id)->where('related_link_id', $rlink_id[0])->with('getImages')->first();
                    if (empty($aboutData)) {
                        return $this->sendResponse($aboutData, trans('en_lang.DATA_NOT_FOUND'),404);
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
                    $data['created_at'] = $aboutData->created_at; 
                    $data['updated_at'] = $aboutData->updated_at;                   
                    $data['slider'] = $aboutData->getImages;

                    if ($data) {
                        return $this->sendResponse($data, 'DATA FOUND', 200);
                    } else {
                        return $this->sendResponse($data, 'DATA NOT FOUND', 404, false);
                    }
                }else if (request()->path == 'contact-us' || request()->path == 'contact-details') {

                    $contactUs = RelatedLinkData::where('ac_non_ac_id', $ac_non_ac_id)->where('related_link_id', $rlink_id[0])->first();
                    if (empty($contactUs)) {
                        return $this->sendResponse($contactUs, trans('en_lang.DATA_NOT_FOUND'),404);
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
                        $data['link_en'] = $contactUs->link_en_draft ??  $contactUs->link_en;
                        $data['link_description_en'] = $contactUs->link_description_en_draft ?? $contactUs->link_description_en;
                        $data['link_hi'] = $contactUs->link_hi_draft ??  $contactUs->link_hi;
                        $data['link_description_hi'] = $contactUs->link_description_hi_draft ?? $contactUs->link_description_hi;
                        $data['link_ur'] = $contactUs->link_ur_draft ?? $contactUs->link_ur;
                        $data['link_description_ur'] = $contactUs->link_description_ur_draft ??  $contactUs->link_description_ur;
                    }
                    $data['created_at'] = $aboutData->created_at; 
                    $data['updated_at'] = $aboutData->updated_at;   
                    $data['approval_status'] = $contactUs->approval_status;
                    if ($data) {
                        return $this->sendResponse($data, 'DATA FOUND', 200);
                    } else {
                        return $this->sendResponse($data, 'DATA NOT FOUND', 404, false);
                    }
                }elseif (request()->path == 'faculty-members' || request()->path == 'non-teaching-staff' || request()->path == 'pg-student' || request()->path == 'staff-list' || request()->path == 'staff-members' || request()->path == 'staff-member-teaching' || request()->path == 'retired-faculty-member') {
                    if (request()->path == 'faculty-members' || request()->path == 'staff-member-teaching') {

                        $faculties = UserVisibility::where('ac_non_ac_id', $ac_non_ac_id)->where('for_id','1')->where('status','1')->with('getUser.getContact', 'getDesignation', 'getRole')->orderBy('order_on', 'ASC')->get();
                        if (empty($faculties)) {
                            return $this->sendResponse($faculties, trans('en_lang.DATA_NOT_FOUND'),404);
                        }
                        $key = 0;
                        foreach ($faculties as $value) {
                            if ($value->getUser != NULL) {
                                $data[$key]['id'] = $value->id;
                                $data[$key]['user_id'] = $value->user_id;
                                $data[$key]['url'] = $value->getUser->url;
                                $data[$key]['order_on'] = $value->order_on;
                                $data[$key]['slug'] = $value->getUser->slug;
                                $data[$key]['title'] = $value->getUser->title;
                                $data[$key]['first_name'] = $value->getUser->first_name;
                                $data[$key]['last_name'] = $value->getUser->last_name;
                                $data[$key]['middle_name'] = $value->getUser->middle_name;
                                $data[$key]['created_at'] = $value->created_at; 
                                $data[$key]['updated_at'] = $value->updated_at;   
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
                            return $this->sendResponse($nonTeachingStaff, trans('en_lang.DATA_NOT_FOUND'),404);
                        }
                        $key = 0;
                        foreach ($nonTeachingStaff as $value) {

                            if ($value->getUser != NULL) {
                                $data[$key]['id'] = $value->id;
                                $data[$key]['user_id'] = $value->user_id;
                                $data[$key]['order_on'] = $value->order_on;
                                $data[$key]['url'] = $value->getUser->url;
                                $data[$key]['slug'] = $value->getUser->slug;
                                $data[$key]['title'] = $value->getUser->title;
                                $data[$key]['first_name'] = $value->getUser->first_name;
                                $data[$key]['last_name'] = $value->getUser->last_name;
                                $data[$key]['middle_name'] = $value->getUser->middle_name;
                                $data[$key]['created_at'] = $value->created_at; 
                                $data[$key]['updated_at'] = $value->updated_at;
                                if ($value->getUser->image != NULL) {
                                    $userImage = asset('storage').$value->getUser->image;
                                } else {
                                    $userImage = asset('storage').'/images/default-img.png';                                }
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
                    } else if (request()->path == 'non-teaching-staff' || request()->path == 'staff-list') {

                        $nonTeachingStaff = UserVisibility::where('ac_non_ac_id', $ac_non_ac_id)->where('for_id','2')->where('status','1')->with('getUser.getContact', 'getDesignation', 'getRole')->orderBy('order_on', 'ASC')->get();
                        
                        if (empty($nonTeachingStaff)) {
                            return $this->sendResponse($nonTeachingStaff, trans('en_lang.DATA_NOT_FOUND'),404);
                        }
                        $key = 0;
                        foreach ($nonTeachingStaff as $value) {

                            if ($value->getUser != NULL) {
                                $data[$key]['id'] = $value->id;
                                $data[$key]['user_id'] = $value->user_id;
                                $data[$key]['order_on'] = $value->order_on;
                                $data[$key]['url'] = $value->getUser->url;
                                $data[$key]['slug'] = $value->getUser->slug;
                                $data[$key]['title'] = $value->getUser->title;
                                $data[$key]['first_name'] = $value->getUser->first_name;
                                $data[$key]['last_name'] = $value->getUser->last_name;
                                $data[$key]['middle_name'] = $value->getUser->middle_name;
                                $data[$key]['created_at'] = $value->created_at; 
                                $data[$key]['updated_at'] = $value->updated_at;
                                if ($value->getUser->image != NULL) {
                                    $userImage = asset('storage').$value->getUser->image;
                                } else {
                                    $userImage = asset('storage').'/images/default-img.png';                                }
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
                    } else if (request()->path == 'pg-student') {
                        $pgStudent = UserVisibility::where('ac_non_ac_id', $ac_non_ac_id)->where('for_id','3')->where('status','1')->with('getUser.getContact', 'getDesignation', 'getRole')->orderBy('order_on', 'ASC')->get();
                        if (empty($pgStudent)) {
                            return $this->sendResponse($pgStudent, trans('en_lang.DATA_NOT_FOUND'),404);
                        }
                        $key = 0;
                        foreach ($pgStudent as $value) {
                            if ($value->getUser != NULL) {
                                $data[$key]['id'] = $value->id;
                                $data[$key]['user_id'] = $value->user_id;
                                $data[$key]['order_on'] = $value->order_on;
                                $data[$key]['url'] = $value->getUser->url;
                                $data[$key]['slug'] = $value->getUser->slug;
                                $data[$key]['title'] = $value->getUser->title;
                                $data[$key]['first_name'] = $value->getUser->first_name;
                                $data[$key]['last_name'] = $value->getUser->last_name;
                                $data[$key]['middle_name'] = $value->getUser->middle_name;
                                $data[$key]['created_at'] = $value->created_at; 
                                $data[$key]['updated_at'] = $value->updated_at;
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
                    } else if (request()->path == 'retired-faculty-member') {
                        $retireFaculty = UserVisibility::where('ac_non_ac_id', $ac_non_ac_id)->where('status','2')->where('for_id','1')->with('getRetireUser.getContact', 'getDesignation', 'getRole')->orderBy('order_on', 'ASC')->get();
                        if (empty($retireFaculty)) {
                            return $this->sendResponse($retireFaculty, trans('en_lang.DATA_NOT_FOUND'),404);
                        }
                        $key = 0;
                        foreach ($retireFaculty as $value) {
                            if ($value->getRetireUser != NULL) {
                                $data[$key]['id'] = $value->id;
                                $data[$key]['user_id'] = $value->user_id;
                                $data[$key]['title'] = $value->getRetireUser->title;
                                $data[$key]['first_name'] = $value->getRetireUser->first_name;
                                $data[$key]['middle_name'] = $value->getRetireUser->middle_name;
                                $data[$key]['last_name'] = $value->getRetireUser->last_name;
                                $data[$key]['designation'] = $value->getDesignation->name;
                                $data[$key]['created_at'] = $value->created_at; 
                                $data[$key]['updated_at'] = $value->updated_at;
                                
                                $key++;
                            }
                        }
                    }

                    if ($data) {
                        return $this->sendResponse($data, trans('en_lang.DATA_FOUND'), 200);
                    } else {
                        return $this->sendResponse($data, trans('en_lang.DATA_NOT_FOUND'),404);
                    }
                }elseif (request()->path == 'list-of-former-chairperson') {

                    $getData = FormerChairPerson::where('ac_non_ac_id', $ac_non_ac_id)->orderBy('order_on', 'ASC')->get();
                    foreach ($getData as $key => $value) {
                        $data[$key]['id']  = $value->id;
                        $data[$key]['name'] = $value->name; 
                        $data[$key]['from_date'] = $value->from_date;
                        $data[$key]['till_date'] = $value->till_date;
                        $data[$key]['order_on'] = $value->order_on;
                        $data[$key]['created_at'] = $value->created_at; 
                        $data[$key]['updated_at'] = $value->updated_at;
                    } 
                    if ($data) {
                        return $this->sendResponse($data, trans('en_lang.DATA_FOUND'), 200);
                    } else {
                        return $this->sendResponse($data, trans('en_lang.DATA_NOT_FOUND'),404);
                    }
                }elseif (request()->path == 'phd' || request()->path == 'post-graduate' || request()->path == 'under-graduate' || request()->path == 'other-program' || request()->path == 'mphil-program' || request()->path == 'diploma') {

                    if (request()->path == 'phd') {

                        $data = Course::where('ac_non_ac_id', $ac_non_ac_id)->orderBy('order_on', 'ASC')->where('type', '1')->get();
                    } else if (request()->path == 'post-graduate') {

                        $data = Course::where('ac_non_ac_id', $ac_non_ac_id)->orderBy('order_on', 'ASC')->where('type', '2')->get();
                    } else if (request()->path == 'under-graduate') {

                        $data = Course::where('ac_non_ac_id', $ac_non_ac_id)->orderBy('order_on', 'ASC')->where('type', '3')->get();
                    } else if (request()->path == 'other-program' || request()->path == 'diploma') {

                        $data = Course::where('ac_non_ac_id', $ac_non_ac_id)->orderBy('order_on', 'ASC')->where('type', '4')->get();
                    } else if (request()->path == 'mphil-program') {

                        $data = Course::where('ac_non_ac_id', $ac_non_ac_id)->orderBy('order_on', 'ASC')->where('type', '5')->get();
                    }

                    if ($data->isNotEmpty()) {
                        return $this->sendResponse($data, trans('en_lang.DATA_FOUND'), 200);
                    } else {
                        return $this->sendResponse($data, trans('en_lang.DATA_NOT_FOUND'),404);
                    }
                }elseif (request()->path == 'on-going-research-projects' || request()->path == 'completed-research-projects') {

                    if (request()->path == 'on-going-research-projects') {

                        $data = OnGoingProject::select('id', 'about_en', 'about_hi', 'about_ur', 'fagency', 'famount', 'pi', 'cpi', 'pid', 'order_on', 'dt','created_at','updated_at')->where('ac_non_ac_id', $ac_non_ac_id)->orderBy('order_on', 'ASC')->orderBy('id', 'ASC')->get();
                    } else if (request()->path == 'completed-research-projects') {

                        $data = CompletedProject::select('id', 'about_en', 'about_hi', 'about_ur', 'fagency', 'famount', 'pi', 'cpi', 'pid', 'order_on', 'dt','created_at','updated_at')->where('ac_non_ac_id', $ac_non_ac_id)->orderBy('order_on', 'ASC')->orderBy('id', 'ASC')->get();
                    }

                    if ($data->isNotEmpty()) {
                        return $this->sendResponse($data, trans('en_lang.DATA_FOUND'), 200);
                    } else {
                        return $this->sendResponse($data, trans('en_lang.DATA_NOT_FOUND'),404);
                    }
                }elseif (request()->path == 'notice-and-circular') {

                    $data = NoticeCircular::select('id','title_en','title_hi','title_ur','file','order_on','created_at','updated_at')->where('ac_non_ac_id', $ac_non_ac_id)->orderBy('order_on', 'asc')->orderBy('created_at', 'desc')->paginate(env('ITEM_PER_PAGE'));

                    if ($data->isNotEmpty()) {
                        return $this->sendResponse($data, trans('en_lang.DATA_FOUND'), 200);
                    } else {
                        return $this->sendResponse($data, trans('en_lang.DATA_NOT_FOUND'),404);
                    }
                }elseif (request()->path == 'photo-gallery') {

                    $data = AcNonAcGallery::where('ac_non_ac_id', $ac_non_ac_id)->orderBy('order_on','ASC')->get();

                    if ($data->isNotEmpty()) {
                        return $this->sendResponse($data, trans('en_lang.DATA_FOUND'), 200);
                    } else {
                        return $this->sendResponse($data, trans('en_lang.DATA_NOT_FOUND'),404);
                    }
                }else {

                    $commonData = RelatedLinkData::where('ac_non_ac_id', $ac_non_ac_id)->where('related_link_id', $rlink_id[0])->orderBy('order_on', 'ASC')->get();
                    if (empty($commonData)) {
                        return $this->sendResponse($commonData, trans('en_lang.DATA_NOT_FOUND'),404);
                    }
                    $key = 0;
                    foreach ($commonData as $value) {
                        $data[$key]['id'] = $value->id;
                        $data[$key]['ac_non_ac_id'] = $value->ac_non_ac_id;
                        $data[$key]['related_link_id'] = $value->related_link_id;
                        $data[$key]['file'] = $value->file;
                        $data[$key]['order_on'] = $value->order_on;
                        $data[$key]['date'] = $value->created_at;
                        $data[$key]['created_at'] = $value->created_at; 
                        $data[$key]['updated_at'] = $value->updated_at;
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
                        return $this->sendResponse($data, trans('en_lang.DATA_NOT_FOUND'),404);
                    }
                }
            } else {
                return $this->sendResponse($data, trans('en_lang.ACCESS_DENIED'), 401, false);
            }
        }
    }

    // Get Department's  about-us data 
    public function getDepartmentDetail(Request $request)
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
            $data = array();
            $academic =  AcademicAndNonAcademic::where('slug', request()->slug)->first();
            $ac_non_ac_id = $academic->id;
            $user_id = $request->user()->id;
            $role = request()->role;
            if (checkUserRole($ac_non_ac_id, $user_id, $role)) {
                if ($academic->type == '1') {
                    $slug = request()->type.'/'.request()->slug.'/'.request()->path;
                }else{
                    $slug = request()->slug.'/'.request()->path;
                }
                $aboutId = RelatedLink::where('slug', $slug)->pluck('id');
		
                $aboutData = RelatedLinkData::where('related_link_id', $aboutId[0])->with('getImages')->first();
                if (empty($aboutData)) {
                    return $this->sendResponse($aboutData, trans('en_lang.DATA_NOT_FOUND'),404);
                }
                $about = array();
                if ($aboutData) {
                    $about['id'] = $aboutData->id;
                    $about['ac_non_ac_id'] = $aboutData->ac_non_ac_id;
                    $about['related_link_id'] = $aboutData->related_link_id;

                    if ($aboutData->approval_status == 'Approved') {
                        $about['link_en'] = $aboutData->link_en;
                        $about['link_description_en'] = $aboutData->link_description_en;
                        $about['link_hi'] = $aboutData->link_hi;
                        $about['link_description_hi'] = $aboutData->link_description_hi;
                        $about['link_ur'] = $aboutData->link_ur;
                        $about['link_description_ur'] = $aboutData->link_description_ur;
                    } else {
                        $about['link_en'] = $aboutData->link_en_draft ? $aboutData->link_en_draft : $aboutData->link_en;
                        $about['link_description_en'] = $aboutData->link_description_en_draft ? $aboutData->link_description_en_draft : $aboutData->link_description_en;
                        $about['link_hi'] = $aboutData->link_hi_draft ? $aboutData->link_hi_draft : $aboutData->link_hi;
                        $about['link_description_hi'] = $aboutData->link_description_hi_draft ? $aboutData->link_description_hi_draft : $aboutData->link_description_hi;
                        $about['link_ur'] = $aboutData->link_ur_draft ? $aboutData->link_ur_draft : $aboutData->link_ur;
                        $about['link_description_ur'] = $aboutData->link_description_ur_draft ? $aboutData->link_description_ur_draft : $aboutData->link_description_ur;
                    }
                        $about['updated_at'] = $aboutData->updated_at;
                        $about['approval_status'] = $aboutData->approval_status;
                        if (request()->path == 'about-the-department' || request()->path == 'home-page' || request()->path == 'office-of-the-dean' || request()->path == 'about-the-section' || request()->path == 'about-the-amucentres') {
                                $about['slider'] = $aboutData->getImages;
                            }
                }
                $data['about'] = $about;
                if ($data) {
                    return $this->sendResponse($data, 'DATA FOUND', 200);
                } else {
                    return $this->sendResponse($data, 'DATA NOT FOUND', 404, false);
                }
            } else {
                return $this->sendResponse($data, trans('en_lang.ACCESS_DENIED'), 401, false);
            }
        }
    }

    // Store Department's  about-us data 
    public function storeDepartmentDetail(Request $request)
    {

        $input = $request->all();
        $validator = Validator::make($request->all(), [
            'slug' => 'required',
            'role' => 'required',
            'type' => 'required',
            'approval_status' => 'required',
            'link_description_en' => 'required',
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
                if ($academic->type == '1') {
                    $slug = request()->type.'/'.request()->slug.'/'.request()->path;
                }else{
                    $slug = request()->slug.'/'.request()->path;
                }
                $rlink = RelatedLink::select('id')->where('slug', $slug)->first();

                if (isset($request->id) != null) {
                    $RelatedLinkData = RelatedLinkData::where('id', request()->id)->first();
                    if (request()->approval_status == 'Approved') {
                        $aboutData['link_en']             = $request->link_en;
                        if ($request->link_hi == 'null' || $request->link_hi == Null) {
                            $aboutData['link_hi'] = Null;
                        }else{
                            $aboutData['link_hi'] = $request->link_hi;
                        }
                        if ($request->link_ur == 'null' || $request->link_ur == Null) {
                            $aboutData['link_ur'] = Null;
                        }else{
                            $aboutData['link_ur'] = $request->link_ur;
                        }
                        if ($request->link_description_en == 'null' || $request->link_description_en == Null) {
                            $aboutData['link_description_en'] = Null;
                        }else{
                            $aboutData['link_description_en'] = $request->link_description_en;
                        }
                        if ($request->link_description_hi == 'null' || $request->link_description_hi == Null) {
                            $aboutData['link_description_hi'] = Null;
                        }else{
                            $aboutData['link_description_hi'] = $request->link_description_hi;
                        }
                        if ($request->link_description_ur == 'null' || $request->link_description_ur == Null) {
                            $aboutData['link_description_ur'] = Null;
                        }else{
                            $aboutData['link_description_ur'] = $request->link_description_ur;
                        }
                        
                        $aboutData['link_en_draft']             = '';
                        $aboutData['link_hi_draft']             = '';
                        $aboutData['link_ur_draft']             = '';
                        $aboutData['link_description_en_draft'] = '';
                        $aboutData['link_description_hi_draft'] = '';
                        $aboutData['link_description_ur_draft'] = '';
                        
                    } else {
                        $aboutData['link_en_draft']             = $request->link_en;
                        if ($request->link_hi == 'null' || $request->link_hi == Null) {
                            $aboutData['link_hi_draft'] = Null;
                        }else{
                            $aboutData['link_hi_draft'] = $request->link_hi;
                        }
                        if ($request->link_ur == 'null' || $request->link_ur == Null) {
                            $aboutData['link_ur_draft'] = Null;
                        }else{
                            $aboutData['link_ur_draft'] = $request->link_ur;
                        }
                        if ($request->link_description_en == 'null' || $request->link_description_en == Null) {
                            $aboutData['link_description_en_draft'] = Null;
                        }else{
                            $aboutData['link_description_en_draft'] = $request->link_description_en;
                        }
                        if ($request->link_description_hi == 'null' || $request->link_description_hi == Null) {
                            $aboutData['link_description_hi_draft'] = Null;
                        }else{
                            $aboutData['link_description_hi_draft'] = $request->link_description_hi;
                        }
                        if ($request->link_description_ur == 'null' || $request->link_description_ur == Null) {
                            $aboutData['link_description_ur_draft'] = Null;
                        }else{
                            $aboutData['link_description_ur_draft'] = $request->link_description_ur;
                        }
                        
                    }
                    $aboutData['approval_status'] =  $request->approval_status;
                    $data = RelatedLinkData::where('id', request()->id)->update($aboutData);
                    if ($data) {
                        $rlink = RelatedLinkData::where('id', request()->id)->first();
                        $logtype = LogType::select('id')->where('type', '=', 'academic')->first();
                        if (is_null($logtype)) {
                            return $this->sendResponse($logtype, 'Log type data not found', 404);
                        } else {
                            $dta = array();
                            $dta['log_type_id'] = $logtype['id'];
                            $dta['user_id'] = $user_id;
                            $dta['ip'] = $request->ip();
                            $dta['action'] = $request->approval_status;
                            $dta['ac_non_ac_id'] = $ac_non_ac_id;
                            $dta['related_link_id'] = $rlink->related_link_id;
                            UserLogSave($dta);
                        }
                        return $this->sendResponse($data, trans('en_lang.DATA_UPDATED'), 200);
                    } else {
                        return $this->sendResponse($data, 'Some thing went wrong', 204, false);
                    }
                } else {
                    $aboutData['type_id']             = $academic->type;
                    $aboutData['ac_non_ac_id']        = $ac_non_ac_id;
                    $aboutData['related_link_id']     = $rlink->id;
                    $aboutData['link_en']             = $request->link_en;
                    $aboutData['link_description_en'] = $request->link_description_en;
                    $aboutData['link_hi']             = $request->link_hi;
                    $aboutData['link_description_hi'] = $request->link_description_hi;
                    $aboutData['link_ur']             = $request->link_ur;
                    $aboutData['link_description_ur'] = $request->link_description_ur;
                    $aboutData['approval_status']     = $request->approval_status;
                    if (request()->approval_status != 'Approved') {
                        $aboutData['link_en_draft']       = $request->link_en;
                        $aboutData['link_hi_draft']       = $request->link_hi;
                        $aboutData['link_ur_draft']       = $request->link_ur;
                        $aboutData['link_description_en_draft']= $request->link_description_en;
                        $aboutData['link_description_hi_draft']= $request->link_description_hi;
                        $aboutData['link_description_ur_draft']= $request->link_description_ur; 
                    }
                    $data = RelatedLinkData::create($aboutData);
                    if ($data) {
                        $logtype = LogType::select('id')->where('type', '=', 'academic')->first();
                        if (is_null($logtype)) {
                            return $this->sendResponse($logtype, 'Log type data not found', 404);
                        } else {
                            $dta = array();
                            $dta['log_type_id'] = $logtype['id'];
                            $dta['user_id'] = $user_id;
                            $dta['ip'] = $request->ip();
                            $dta['action'] = $request->approval_status;
                            $dta['ac_non_ac_id'] = $ac_non_ac_id;
                            $dta['related_link_id'] = $data->related_link_id;
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

    // Store Department's Images
    public function addImage(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'role' => 'required',
            'slug' => 'required',
            'image' => 'required',
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

                if ($academic->type == '1') {
                    $slug = request()->type.'/'.request()->slug.'/'.request()->path;
                }else{
                    $slug = request()->slug.'/'.request()->path;
                }
                $rlink = RelatedLink::select('id','link_name_en')->where('slug', $slug)->first();


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
                    $final_data['related_link_id'] = $rlink->id;
                    $final_data['image'] = $results['path'];
                    $data = AcNonAcImage::create($final_data);
                    $logtype = LogType::select('id')->where('type', '=', 'academic')->first();
                    if (is_null($logtype)) {
                        return $this->sendResponse($logtype, 'Log type data not found', 404);
                    } else {
                        $dta = array();
                        $dta['log_type_id'] = $logtype->id;
                        $dta['user_id'] = $user_id;
                        $dta['ip'] = $request->ip();
                        $dta['action'] = 'Updated ' . strtolower($rlink->link_name_en) . ' image';
                        $dta['ac_non_ac_id'] = $ac_non_ac_id;
                        $dta['related_link_id'] = $rlink->id;
                        UserLogSave($dta);
                    }
                    return $this->sendResponse($data, 'Image Uploaded', 200, true);
                } else {
                    return $this->sendResponse($data, trans('en_lang.ACCESS_DENIED'), 401, false);
                }
            } else {
                return $this->sendResponse($data, trans('en_lang.ACCESS_DENIED'), 401, false);
            }
        }
    }

    // Delete Department's Images
    public function deleteImage(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'image_id' => 'required',
            'role' => 'required',
            'slug' => 'required',
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
                $slider = AcNonAcImage::where('id', request()->image_id)->first();
                if (AcNonAcImage::where('id', request()->image_id)->delete()) {
                    $logtype = LogType::select('id')->where('type', '=', 'academic')->first();
                            if (is_null($logtype)) {
                                return $this->sendResponse($logtype, 'Log type data not found', 404);
                            } else {
                                $dta = array();
                                $dta['log_type_id'] = $logtype['id'];
                                $dta['user_id'] = $user_id;
                                $dta['ip'] = $request->ip();
                                $dta['action'] = 'Image Deleted';
                                $dta['ac_non_ac_id'] = $ac_non_ac_id;
                                $dta['related_link_id'] = $slider->related_link_id;
                                UserLogSave($dta);
                            }
                    return $this->sendResponse($data, 'Image Deleted', 200);
                     
                } else {
                    return $this->sendResponse($data, 'Error coming', 404, false);
                }
            } else {
                return $this->sendResponse($data, trans('en_lang.ACCESS_DENIED'), 401, false);
            }
        }
    }

    // Store Department's Files
    public function storeFiles(Request $request)
    {

        $input = $request->all();
        $validator = Validator::make($request->all(), [
            'slug' => 'required',
            'role' => 'required',
            'type' => 'required',
            'path' => 'required',
            'title' => 'required',
            'file' => 'required|mimes:doc,docx,pdf',


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

                $slug = request()->type.'/' . request()->slug . '/' . request()->path;
                $rlink = RelatedLink::select('id', 'link_name_en')->where('slug', $slug)->first();
                if ($rlink->isEmpty()) {
                    return $this->sendResponse($rlink, 'Path Not Found', 404, false);
                }

                if ($request->hasFile('file')) {
                    $file_data = array();
                    $results = array();
                    $final_data = array();
                    $file_data['file']  = $request->file('file');
                    $file_data['path']     = 'file/'.$ac_non_ac_id.'/'.request()->path;
                    $file_data['filename'] = $request->file('file')->getClientOriginalName();
                    $fileUp = FileUpload($file_data);
                    $results = json_decode($fileUp->content(), true);
                    if ($results['code'] == 1000) {
                        $final_data['title'] = $request->title;
                        $final_data['ac_non_ac_id'] = $ac_non_ac_id;
                        $final_data['link_id'] = $rlink->id;
                        $final_data['order_on'] = $request->order_on;
                        $final_data['file'] = $results['path'];
                        $data = AcNonAcCommitteeFiles::create($final_data);
                        $logtype = LogType::select('id')->where('type', '=', 'academic')->first();
                        if (is_null($logtype)) {
                            return $this->sendResponse($logtype, 'Log type data not found', 404);
                        } else {
                            $dta = array();
                            $dta['log_type_id'] = $logtype->id;
                            $dta['user_id'] = $user_id;
                            $dta['ip'] = $request->ip();
                            $dta['action'] = 'Uploaded ' . strtolower($rlink->link_name_en) . ' File';
                            $dta['ac_non_ac_id'] = $ac_non_ac_id;
                            $dta['related_link_id'] = $rlink->id;
                            UserLogSave($dta);
                        }
                        return $this->sendResponse($data, 'File Uploaded', 200, true);
                    } else {
                        return $this->sendResponse($data, trans('en_lang.ACCESS_DENIED'), 401, false);
                    }
                }
            } else {
                return $this->sendResponse($data, trans('en_lang.ACCESS_DENIED'), 401, false);
            }
        }
    }

    // Delete Related link File Data
    public function deleteFiles(Request $request)
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

                    $slug = request()->type.'/' . request()->slug . '/' . request()->path;
                    $rlink_id = RelatedLink::where('slug', $slug)->pluck('id');
                    if ($rlink_id->isEmpty()) {
                        return $this->sendResponse($rlink_id, 'Path Not Found', 404, false);
                    }

                    if (AcNonAcCommitteeFiles::where('id', request()->id)->delete()) {

                        return $this->sendResponse('true', 'File Deleted', 200);
                    } else {

                        return $this->sendResponse('false', 'Something went Wrong!', 404);
                    }

                    $logtype = LogType::select('id')->where('type', '=', 'academic')->first();

                    if (is_null($logtype)) {

                        return $this->sendResponse($logtype, 'Log type data not found', 404);
                    } else {

                        $dta = array();
                        $dta['log_type_id'] = $logtype['id'];
                        $dta['user_id'] = $user_id;
                        $dta['ip'] = $request->ip();
                        $dta['action'] = 'Academic ' . request()->path . ' file data deleted';
                        $dta['ac_non_ac_id'] = $ac_non_ac_id;
                        $dta['related_link_id'] = $rlink_id[0];
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

    // Update Department's Faculty Staff list Order Number
    public function updateFacultyMemberOrder(Request $request)
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
                if (!empty($request->members)) {
                    foreach ($request->members as $key => $member) {
                        UserVisibility::where('id', $member['id'])->update(['order_on' => $key + 1]);
                    }
                    return $this->sendResponse('Update Successfully.', 'DATA FOUND', 200);
                }
            } else {
                return $this->sendResponse('ACCESS DENIED', trans('en_lang.ACCESS_DENIED'), 401, false);
            }
        }
    }

    // Get Department's User Log list
    public function getUserLog(Request $request)
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

                if ($academic->type == '1') {
                    $slug = request()->type.'/'.request()->slug.'/'.request()->path;
                }else{
                    $slug = request()->slug.'/'.request()->path;
                }
                $rlink = RelatedLink::select('id')->where('slug', $slug)->first();
                if ($rlink) {
                   $rlink_id = $rlink->id;
                }else{
                    $rlink_id = request()->path;
                }
                $userLog = UserLog::select('id', 'user_id', 'ip', 'action', 'ac_non_ac_id', 'related_link_id', 'created_at')->where('ac_non_ac_id', $ac_non_ac_id)->where('related_link_id', $rlink_id)
                    ->with(['getUserRole' => function($q)  use($ac_non_ac_id){ 
                        $q->where('ac_non_ac_id', '=',$ac_non_ac_id);
                        $q->with('getUser:id,title,first_name,middle_name,last_name','getRole','getDesignation');
                    }])->orderBy('created_at', 'DESC')->paginate(env('ITEM_PER_PAGE'));

                if (sizeof($userLog)) {
                    return $this->sendResponse($userLog, trans('en_lang.DATA_FOUND'), 200);
                } else {
                    return $this->sendResponse($userLog, trans('en_lang.DATA_NOT_FOUND'),404);
                }
            } else {
                return $this->sendResponse($userLog, trans('en_lang.ACCESS_DENIED'), 401, false);
            }
        }
    }

    // Store  Department's New User's
    public function addNewAcademicUsers(Request $request)
    {

        $input = $request->all();
        $validator = Validator::make($request->all(), [
            'slug' => 'required',
            'role' => 'required',
            'type' => 'required',
            'eid' => 'required',
            'title' => 'required',
            'first_name' => 'required',
            'designation_id' => 'required',
            'path' => 'required',
            'for_id' => 'required'

        ]);
        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors(), 422, false);
        } else {
            $data = array();
            $visibility = array();
            $academic =  AcademicAndNonAcademic::where('slug', request()->slug)->first();
            $ac_non_ac_id = $academic->id;
            $user_id      = $request->user()->id;
            $role         = request()->role;
            if (checkUserRole($ac_non_ac_id, $user_id, $role)) {

                $user = User::where('eid','=',$request->eid)->first();
                if ($user) {
                   return $this->sendResponse($user, 'This eid is already exists!', 401);
                }

                if ($academic->type == '1') {
                    $slug = request()->type.'/'.request()->slug.'/'.request()->path;
                }else{
                    $slug = request()->slug.'/'.request()->path;
                }
                if ($request->last_name != null) {
                    $request->last_name = $request->last_name;
                }else{
                    $request->last_name = '';
                }
                        
                $rlink_id = RelatedLink::where('slug', $slug)->pluck('id');
                $data['eid']             = $request->eid;
                $data['title']           = $request->title;
                $data['first_name']      = $request->first_name;
                $data['middle_name']     = $request->middle_name ?? '';
                $data['last_name']       = $request->last_name;
                $data['for_id']          = $request->for_id;
                $data['status']          = 1;
                $data['email']           = $request->email ?? '';
                $data['password']        = Hash::make(str_random(8));
                $user = User::create($data);                

                if ($user->id) {

                    if (request()->for_id == '1') {
                        $url = 'faculty/'.$academic->slug.'/'.$user->slug;
                    }elseif (request()->for_id == '2'){
                        $url = 'non-teaching/'.$academic->slug.'/'.$user->slug;
                    }else{
                        $url = 'pg-student/'.$academic->slug.'/'.$user->slug;
                    }               
                    
                    User::where('id', $user->id)->update(['url' => $url]);
                    if (isset($request->email)) {
                        $validator = Validator::make($request->all(), [
                            'email'  => 'email:rfc,dns'
                        ]);
                        if ($validator->fails()) {
                            return $this->sendError('Validation Error.', $validator->errors(),422);
                        }
                    }
                    $userContact    = new UserContact([
                        'user_id'   => $user->id,
                        'email'     => $request->email,
                        'mobile_no' => $request->mobile_no,
                        'primary'   => '1',           
                    ]);
                    $userContact->save();

                    if ($request->role_id == $academic->head) {
                        UserVisibility::where('ac_non_ac_id',$ac_non_ac_id)->where('role_id',$academic->head)->update(['role_id' => '3']);
                    }

                    $visibility['user_id']          = $user->id;
                    $visibility['type_id']          = $academic->type;
                    $visibility['sub_type_id']      = $academic->sub_type;
                    $visibility['ac_non_ac_id']     = $ac_non_ac_id;
                    $visibility['designation_id']   = $request->designation_id;
                    $visibility['role_id']          = $request->role_id ?? '';
                    $visibility['for_id']           = $request->for_id;
                    $visibility['core']             = 1;
                    $visibility['status']           = '1';
                    $visibility['special_role']     = $request->special_role ?? '';
                    $visibility = UserVisibility::create($visibility);
                    $logtype = LogType::select('id')->where('type', '=', 'user')->first();

                    if (is_null($logtype)) {

                        return $this->sendResponse($logtype, 'Log type data not found', 404);
                    } else {

                        $dta = array();
                        $dta['log_type_id'] = $logtype['id'];
                        $dta['user_id'] = $user_id;
                        $dta['ip'] = $request->ip();
                        $dta['action'] = 'Added New User';
                        $dta['ac_non_ac_id'] = $ac_non_ac_id;
                        $dta['related_link_id'] = $rlink_id[0];
                        UserLogSave($dta);
                    }

                    return $this->sendResponse($user, trans('en_lang.DATA_CREATED'), 201);
                } else {
                    return $this->sendResponse($user, 'Some thing went wrong', 204, false);
                }
            } else {
                return $this->sendResponse($user, 'You do not have permission to perform this action.', 204, false);
            }
        }
    }

    // Store  Department's Existing User's
    public function addExistingAcademicUsers(Request $request)
    {

        $input = $request->all();
        $validator = Validator::make($request->all(), [
            'slug' => 'required',
            'role' => 'required',
            'type' => 'required',
            'user_id' => 'required',
            'designation_id' => 'required',
            'path' => 'required',

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

                if ($academic->type == '1') {
                    $slug = request()->type.'/'.request()->slug.'/'.request()->path;
                }else{
                    $slug = request()->slug.'/'.request()->path;
                }
                $rlink_id = RelatedLink::where('slug', $slug)->pluck('id');

                if ($request->role_id == $academic->head) {
                    UserVisibility::where('ac_non_ac_id',$ac_non_ac_id)->where('role_id',$academic->head)->update(['role_id' => '3']);
                }

                if (isset($request->id) != null) {

                    $data['designation_id']   = $request->designation_id;
                    $data['role_id']          = $request->role_id ?? '';;
                    $data['special_role']     = $request->special_role ?? '';
                    
                    if (isset($request->title)) {
                        User::where('id', request()->user_id)->update(['title' => $request->title]);
                    }                    

                    
                    if ($request->retire == true) {
                       $data['status']   = '2';
                       User::where('id', request()->user_id)->update(['status' => 2]);
                    }elseif ($request->withdrawal == true) {
                        $visibility = UserVisibility::where('id', request()->id)->delete();
                    }elseif ($request->deactivate == true) {
                       $data['status']   = '0'; 
                       User::where('id', request()->user_id)->update(['status' => 0]);
                       $visibility = UserVisibility::where('id', request()->id)->delete();
                    }
                    $visibility = UserVisibility::where('id', request()->id)->update($data);
                    if ($visibility) {


                        $logtype = LogType::select('id')->where('type', '=', 'user')->first();

                        if (is_null($logtype)) {

                            return $this->sendResponse($logtype, 'Log type data not found', 404);
                        } else {

                            $dta = array();
                            $dta['log_type_id'] = $logtype['id'];
                            $dta['user_id'] = $user_id;
                            $dta['ip'] = $request->ip();
                            $dta['action'] = 'Updated User';
                            $dta['ac_non_ac_id'] = $ac_non_ac_id;
                            $dta['related_link_id'] = $rlink_id[0];
                            UserLogSave($dta);
                        }

                        return $this->sendResponse($data, trans('en_lang.DATA_UPDATED'), 200);
                    } else {
                        return $this->sendResponse($data, 'Some thing went wrong', 204, false);
                    }
                } else {

                    $eid = substr($request->user_id, strrpos($request->user_id, '-' )+1);
                    $u_id = User::select('id','for_id')->where('eid',$eid)->first();

                    $visibility = UserVisibility::where('user_id', $u_id->id)->where('ac_non_ac_id', $ac_non_ac_id)->first();

                    if (!empty($visibility)) {
                       return $this->sendResponse($visibility, 'User already added',404);
                    }else{
                        if (request()->path == 'faculty-members' || request()->path == 'staff-members-teaching') {
                            $data['for_id']           = '1';
                        }elseif (request()->path == 'non-teaching-staff' ||  request()->path == 'staff-members') {
                            $data['for_id']           = '2';
                        }elseif (request()->path == 'pg-student') {
                            $data['for_id']           = '3';
                        }
                        
                        $data['user_id']          = $u_id->id;
                        $data['type_id']          = $academic->type;
                        $data['sub_type_id']      = $academic->sub_type;
                        $data['ac_non_ac_id']     = $ac_non_ac_id;
                        $data['designation_id']   = $request->designation_id;
                        $data['role_id']          = $request->role_id ?? '';
                        $data['special_role']     = $request->special_role ?? '';
                        $data['status']           = '1';
                        $visibility = UserVisibility::create($data);
                        if ($visibility) {

                            $logtype = LogType::select('id')->where('type', '=', 'user')->first();

                            if (is_null($logtype)) {

                                return $this->sendResponse($logtype, 'Log type data not found', 404);
                            } else {

                                $dta = array();
                                $dta['log_type_id'] = $logtype['id'];
                                $dta['user_id'] = $user_id;
                                $dta['ip'] = $request->ip();
                                $dta['action'] = 'Added  User';
                                $dta['ac_non_ac_id'] = $ac_non_ac_id;
                                $dta['related_link_id'] = $rlink_id[0];
                                UserLogSave($dta);
                            }

                            return $this->sendResponse($data, trans('en_lang.DATA_CREATED'), 201);
                        } else {
                            return $this->sendResponse($data, 'Some thing went wrong', 204, false);
                        }
                            
                    }
                    
                }
            } else {
                return $this->sendResponse($data, trans('en_lang.ACCESS_DENIED'), 401, false);
            }
        }
    }

    // get  Department's Single Staff Members
    public function getDepartmentStaffMembers(Request $request)
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
            $academic =  AcademicAndNonAcademic::where('slug', request()->slug)->first();
            $ac_non_ac_id = $academic->id;
            $user_id = $request->user()->id;
            $role = request()->role;
            if (checkUserRole($ac_non_ac_id, $user_id, $role)) {
                $user = UserVisibility::select('id', 'user_id', 'designation_id', 'role_id', 'special_role','core','for_id')->where('id', '=', request()->id)
                    ->with('getUser:id,eid,url,title,first_name,middle_name,last_name,slug')
                    ->first();

                if ($user) {
                    return $this->sendResponse($user, trans('en_lang.DATA_FOUND'), 200);
                } else {
                    return $this->sendResponse($user, trans('en_lang.DATA_NOT_FOUND'),404);
                }
            } else {
                return $this->sendResponse($user, trans('en_lang.ACCESS_DENIED'), 401, false);
            }
        }
    }

    // Delete  Department's User's
    public function deleteUser(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'visibility_id' => 'required',
            'role' => 'required',
            'slug' => 'required',
            'path' => 'required',
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

                if ($academic->type == '1') {
                    $slug = request()->type.'/'.request()->slug.'/'.request()->path;
                }else{
                    $slug = request()->slug.'/'.request()->path;
                }
                $rlink_id = RelatedLink::where('slug', $slug)->pluck('id');
                if ($rlink_id->isEmpty()) {
                    return $this->sendResponse($rlink_id, 'Path Not Found', 404, false);
                }
                $visibility = UserVisibility::where('id',request()->visibility_id)->first();

                if ($visibility->core == '1') {
                   $delete =  UserVisibility::where('user_id', $visibility->user_id)->delete();
                    User::where('id', $visibility->user_id)->update(['status' => '0']);
                }else{
                   $delete = UserVisibility::where('id', request()->visibility_id)->delete();  
                }

                if ($visibility) {

                    $logtype = LogType::select('id')->where('type', '=', 'user')->first();

                    if (is_null($logtype)) {

                        return $this->sendResponse($logtype, 'Log type data not found', 404);
                    } else {

                        $dta = array();
                        $dta['log_type_id'] = $logtype['id'];
                        $dta['user_id'] = $user_id;
                        $dta['ip'] = $request->ip();
                        $dta['action'] = 'User Deleted';
                        $dta['ac_non_ac_id'] = $ac_non_ac_id;
                        $dta['related_link_id'] = $rlink_id[0];
                        UserLogSave($dta);
                    }

                    return $this->sendResponse('true', 'User Deleted', 200);
                } else {
                    return $this->sendResponse('false', 'Something went Wrong!', 404, false);
                }
            } else {
                return $this->sendResponse($data, trans('en_lang.ACCESS_DENIED'), 401, false);
            }
        }
    }

    // Store related link common data
    public function storeDepartmentCommonData(Request $request)
    {

        $input = $request->all();
        $validator = Validator::make($request->all(), [
            'slug' => 'required',
            'role' => 'required',
            'type' => 'required',
            'approval_status' => 'required',
            'link_en' => 'required',
            'path' => 'required',

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

                if ($academic->type == '1') {
                    $slug = request()->type.'/'.request()->slug.'/'.request()->path;
                }else{
                    $slug = request()->slug.'/'.request()->path;
                }
                $rlink_id = RelatedLink::where('slug', $slug)->pluck('id');

                if ($rlink_id->isEmpty()) {
                    return $this->sendResponse($rlink_id, 'Path Not Found', 404, false);
                }

                if ($request->hasFile('file')) {
                    $file_data = array();
                    $file_data['file']     = $input['file'];
                    $file_data['path']     = 'file/' .$ac_non_ac_id. '/' . request()->path;
                    $file_data['filename'] = $input['file']->getClientOriginalName();
                    $fileUp = FileUpload($file_data);
                    $results = json_decode($fileUp->content(), true);
                    if ($results['code'] == 1000) {
                        $input['file'] = $results['path'];
                    }
                }

                if (isset($request->id) != null) {

                    $RelatedLinkData = RelatedLinkData::where('id', request()->id)->first();
                    if (request()->approval_status == 'Approved') {

                        $data['link_en'] = $request->link_en;
                        if ($request->link_hi == 'null' || $request->link_hi == Null) {
                            $data['link_hi'] = Null;
                        }else{
                            $data['link_hi'] = $request->link_hi;
                        }
                        if ($request->link_ur == 'null' || $request->link_ur == Null) {
                            $data['link_ur'] = Null;
                        }else{
                            $data['link_ur'] = $request->link_ur;
                        }
                        if ($request->link_description_en == 'null' || $request->link_description_en == Null) {
                            $data['link_description_en'] = Null;
                        }else{
                            $data['link_description_en'] = $request->link_description_en;
                        }
                        if ($request->link_description_hi == 'null' || $request->link_description_hi == Null) {
                            $data['link_description_hi'] = Null;
                        }else{
                            $data['link_description_hi'] = $request->link_description_hi;
                        }
                        if ($request->link_description_ur == 'null' || $request->link_description_ur == Null) {
                            $data['link_description_ur'] = Null;
                        }else{
                            $data['link_description_ur'] = $request->link_description_ur;
                        }
                        $data['link_en_draft']             = '';
                        $data['link_description_en_draft'] = '';
                        $data['link_hi_draft']             = '';
                        $data['link_description_hi_draft'] = '';
                        $data['link_ur_draft']             = '';
                        $data['link_description_ur_draft'] = '';
                    } else {
                        $data['link_en_draft'] = $request->link_en;
                        if ($request->link_hi == 'null' || $request->link_hi == Null) {
                            $data['link_hi_draft'] = Null;
                        }else{
                            $data['link_hi_draft'] = $request->link_hi;
                        }
                        if ($request->link_ur == 'null' || $request->link_ur == Null) {
                            $data['link_ur_draft'] = Null;
                        }else{
                            $data['link_ur_draft'] = $request->link_ur;
                        }
                        if ($request->link_description_en == 'null' || $request->link_description_en == Null) {
                            $data['link_description_en_draft'] = Null;
                        }else{
                            $data['link_description_en_draft'] = $request->link_description_en;
                        }
                        if ($request->link_description_hi == 'null' || $request->link_description_hi == Null) {
                            $data['link_description_hi_draft'] = Null;
                        }else{
                            $data['link_description_hi_draft'] = $request->link_description_hi;
                        }
                        if ($request->link_description_ur == 'null' || $request->link_description_ur == Null) {
                            $data['link_description_ur_draft'] = Null;
                        }else{
                            $data['link_description_ur_draft'] = $request->link_description_ur;
                        }
                    }
                    if ($request->hasFile('file')) {
                        if ($RelatedLinkData->file != '') {
                            DeleteOldPicture($RelatedLinkData->file);
                        }
                        $data['file'] =  $input['file'];
                    }
                    $data['order_on'] =  $request->order_on;
                    $data['approval_status']   = $request->approval_status;

                    $data = RelatedLinkData::where('id', request()->id)->update($data);
                    if ($data) {

                        $logtype = LogType::select('id')->where('type', '=', 'academic')->first();
                        if (is_null($logtype)) {
                            return $this->sendResponse($logtype, 'Log type data not found', 404);
                        } else {
                            $dta = array();
                            $dta['log_type_id'] = $logtype['id'];
                            $dta['user_id'] = $user_id;
                            $dta['ip'] = $request->ip();
                            $dta['action'] = $request->approval_status;
                            $dta['ac_non_ac_id'] = $ac_non_ac_id;
                            $dta['related_link_id'] = $rlink_id[0];
                            UserLogSave($dta);
                        }
                        return $this->sendResponse($data, trans('en_lang.DATA_UPDATED'), 200);
                    } else {
                        return $this->sendResponse($data, 'Some thing went wrong', 204, false);
                    }
                } else {

                    $data['type_id']             = $academic->type;
                    $data['ac_non_ac_id']        = $ac_non_ac_id;
                    $data['related_link_id']     = $rlink_id[0];
                    $data['link_en']             = $request->link_en;
                    $data['link_description_en'] = $request->link_description_en;
                    $data['link_hi']             = $request->link_hi;
                    $data['link_description_hi'] = $request->link_description_hi;
                    $data['link_ur']             = $request->link_ur;
                    $data['link_description_ur'] = $request->link_description_ur;
                    if (request()->approval_status != 'Approved') {
                        $data['link_en_draft']       = $request->link_en;
                        $data['link_hi_draft']       = $request->link_hi;
                        $data['link_ur_draft']       = $request->link_ur;
                        $data['link_description_en_draft'] = $request->link_description_en;
                        $data['link_description_hi_draft'] = $request->link_description_hi;
                        $data['link_description_ur_draft'] = $request->link_description_ur;
                    }                    
                    $data['order_on']            = $request->order_on;
                    $data['approval_status']     = $request->approval_status;
                    if ($request->hasFile('file')) {
                        $data['file'] =  $input['file'];
                    }

                    $data = RelatedLinkData::create($data);
                    if ($data) {
                        $logtype = LogType::select('id')->where('type', '=', 'academic')->first();
                        if (is_null($logtype)) {
                            return $this->sendResponse($logtype, 'Log type data not found', 404);
                        } else {
                            $dta = array();
                            $dta['log_type_id'] = $logtype['id'];
                            $dta['user_id'] = $user_id;
                            $dta['ip'] = $request->ip();
                            $dta['action'] = $request->approval_status;
                            $dta['ac_non_ac_id'] = $ac_non_ac_id;
                            $dta['related_link_id'] = $rlink_id[0];
                            UserLogSave($dta);
                        }
                        return $this->sendResponse($data, trans('en_lang.DATA_CREATED'), 200);
                    } else {
                        return $this->sendResponse($data, 'Some thing went wrong', 204, false);
                    }
                }
            } else {
                return $this->sendResponse($data, trans('en_lang.ACCESS_DENIED'), 401, false);
            }
        }
    }

    // Store related link notice and circular data
    public function storeNoticeCircular(Request $request)
    {

        $input = $request->all();
        $validator = Validator::make($request->all(), [
            'slug' => 'required',
            'role' => 'required',
            'type' => 'required',
            'approval_status' => 'required',
            'title_en' => 'required',
            'path' => 'required',

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

                if ($academic->type == '1') {
                    $slug = request()->type.'/'.request()->slug.'/'.request()->path;
                }else{
                    $slug = request()->slug.'/'.request()->path;
                }
                $rlink_id = RelatedLink::where('slug', $slug)->pluck('id');

                if ($rlink_id->isEmpty()) {
                    return $this->sendResponse($rlink_id, 'Path Not Found', 404, false);
                }

                if ($request->hasFile('file')) {
                    $file_data = array();
                    $file_data['file']     = $input['file'];
                    $file_data['path']     = 'file/'.$ac_non_ac_id.'/'.request()->path;
                    $file_data['filename'] = $input['file']->getClientOriginalName();
                    $fileUp = FileUpload($file_data);
                    $results = json_decode($fileUp->content(), true);
                    if ($results['code'] == 1000) {
                        $input['file'] = $results['path'];
                    }
                }

                if (isset($request->id) != null) {

                    $noticeCircular = NoticeCircular::where('id', request()->id)->first();
                    if (request()->approval_status == 'Approved') {
                        $data['title_en']             = $request->title_en;
                        if ($request->title_hi == 'null' || $request->title_hi == Null) {
                            $data['title_hi'] = Null;
                        }else{
                            $data['title_hi'] = $request->title_hi;
                        }
                        if ($request->title_ur == 'null' || $request->title_ur == Null) {
                            $data['title_ur'] = Null;
                        }else{
                            $data['title_ur'] = $request->title_ur;
                        }
                        $data['title_en_draft']       = '';
                        $data['description_en_draft'] = '';
                        $data['title_hi_draft']       = '';
                        $data['description_hi_draft'] = '';
                        $data['title_ur_draft']       = '';
                        $data['description_ur_draft'] = '';
                    } else {
                        $data['title_en_draft'] = $request->title_en;
                        if ($request->title_hi == 'null' || $request->title_hi == Null) {
                            $data['title_hi_draft'] = Null;
                        }else{
                            $data['title_hi_draft'] = $request->title_hi;
                        }
                        if ($request->title_ur == 'null' || $request->title_ur == Null) {
                            $data['title_ur_draft'] = Null;
                        }else{
                            $data['title_ur_draft'] = $request->title_ur;
                        }                        
                    }
                    $data['status'] = 1;
                    $data['approval_status'] = $request->approval_status;
                    if ($request->hasFile('file')) {
                        if ($noticeCircular->file != '') {
                            DeleteOldPicture($noticeCircular->file);
                        }
                        $data['file'] =  $input['file'];
                    }
                    if ($request->to_date == 'null' || $request->to_date == Null){
                        $data['to_date'] = Null;
                    }else{
                       $data['to_date'] = date("Y-m-d", strtotime($input['to_date']));
                    }
                    

                    $data = NoticeCircular::where('id', request()->id)->update($data);
                    if ($data) {

                        $logtype = LogType::select('id')->where('type', '=', 'academic')->first();
                        if (is_null($logtype)) {
                            return $this->sendResponse($logtype, 'Log type data not found', 404);
                        } else {
                            $dta = array();
                            $dta['log_type_id'] = $logtype['id'];
                            $dta['user_id'] = $user_id;
                            $dta['ip'] = $request->ip();
                            $dta['action'] = $request->approval_status;
                            $dta['ac_non_ac_id'] = $ac_non_ac_id;
                            $dta['related_link_id'] = $rlink_id[0];
                            UserLogSave($dta);
                        }
                        return $this->sendResponse($data, trans('en_lang.DATA_UPDATED'), 200);
                    } else {
                        return $this->sendResponse($data, 'Some thing went wrong', 204, false);
                    }
                } else {

                    $data['type_id']   = $academic->type;
                    $data['sub_type_id']        = $academic->sub_type;
                    $data['ac_non_ac_id']       = $ac_non_ac_id;
                    $data['related_link_id']    = $rlink_id[0];
                    $data['title_en']           = $request->title_en;
                    $data['title_hi']       = $request->title_hi;
                    $data['title_ur']       = $request->title_ur;

                    if (request()->approval_status != 'Approved') {
                        $data['link_en_draft'] = $request->title_en;
                        $data['link_hi_draft'] = $request->title_hi;
                        $data['link_ur_draft'] = $request->title_ur;
                    }
                    $data['status']   = 1;
                    $data['approval_status']   = $request->approval_status;
                    if ($request->hasFile('file')) {
                        $data['file'] =  $input['file'];
                    }
                    if ($request->to_date == 'null' || $request->to_date == Null){
                        $data['to_date'] = Null;
                    }else{
                       $data['to_date'] = date("Y-m-d", strtotime($input['to_date']));
                    }
                    
                    $data = NoticeCircular::create($data);

                    if ($data) {

                        $logtype = LogType::select('id')->where('type', '=', 'academic')->first();
                        if (is_null($logtype)) {
                            return $this->sendResponse($logtype, 'Log type data not found', 404);
                        } else {
                            $dta = array();
                            $dta['log_type_id'] = $logtype['id'];
                            $dta['user_id'] = $user_id;
                            $dta['ip'] = $request->ip();
                            $dta['action'] = $request->approval_status;
                            $dta['ac_non_ac_id'] = $ac_non_ac_id;
                            $dta['related_link_id'] = $rlink_id[0];
                            UserLogSave($dta);
                        }
                        return $this->sendResponse($data, trans('en_lang.DATA_CREATED'), 200);
                    } else {
                        return $this->sendResponse($data, 'Some thing went wrong', 204, false);
                    }
                }
            } else {
                return $this->sendResponse($data, trans('en_lang.ACCESS_DENIED'), 401, false);
            }
        }
    }

    // Store related link chair person data
    public function storeChairPersons(Request $request)
    {

        $input = $request->all();
        $validator = Validator::make($request->all(), [
            'slug' => 'required',
            'role' => 'required',
            'type' => 'required',
            'path' => 'required',
            'name' => 'required',
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

                $slug = request()->type.'/' . request()->slug . '/' . request()->path;
                $rlink_id = RelatedLink::where('slug', $slug)->pluck('id');

                if ($rlink_id->isEmpty()) {
                    return $this->sendResponse($rlink_id, 'Path Not Found', 404, false);
                }

                if (isset($request->id) != null) {

                    $chairPerson = FormerChairPerson::where('id', request()->id)->first();
                    $data['name']         = $request->name;
                    $data['name_hi']      = $request->name_hi;
                    $data['name_ur']      = $request->name_ur;
                    $data['order_on']     = $request->order_on;
                    $data['from_date']    = $request->from_date;
                    $data['till_date']    = $request->till_date;
                    $data['approval_status']  = $request->approval_status;
                    $data = FormerChairPerson::where('id', request()->id)->update($data);
                    if ($data) {

                        $logtype = LogType::select('id')->where('type', '=', 'academic')->first();
                        if (is_null($logtype)) {
                            return $this->sendResponse($logtype, 'Log type data not found', 404);
                        } else {
                            $dta = array();
                            $dta['log_type_id'] = $logtype['id'];
                            $dta['user_id'] = $user_id;
                            $dta['ip'] = $request->ip();
                            $dta['action'] = $request->approval_status;
                            $dta['ac_non_ac_id'] = $ac_non_ac_id;
                            $dta['related_link_id'] = $rlink_id[0];
                            UserLogSave($dta);
                        }
                        return $this->sendResponse($data, trans('en_lang.DATA_UPDATED'), 200);
                    } else {
                        return $this->sendResponse($data, 'Some thing went wrong', 204, false);
                    }
                } else {
                    $data['ac_non_ac_id'] =  $ac_non_ac_id;
                    $data['name']         = $request->name;
                    $data['name_hi']      = $request->name_hi;
                    $data['name_ur']      = $request->name_ur;
                    $data['order_on']     = $request->order_on;
                    $data['from_date']    = $request->from_date;
                    $data['till_date']    = $request->till_date;
                    $data['approval_status'] = $request->approval_status;
                    $data = FormerChairPerson::create($data);
                    if ($data) {
                        $logtype = LogType::select('id')->where('type', '=', 'academic')->first();
                        if (is_null($logtype)) {
                            return $this->sendResponse($logtype, 'Log type data not found', 404);
                        } else {
                            $dta = array();
                            $dta['log_type_id'] = $logtype['id'];
                            $dta['user_id'] = $user_id;
                            $dta['ip'] = $request->ip();
                            $dta['action'] = $request->approval_status;
                            $dta['ac_non_ac_id'] = $ac_non_ac_id;
                            $dta['related_link_id'] = $rlink_id[0];
                            UserLogSave($dta);
                        }
                        return $this->sendResponse($data, trans('en_lang.DATA_CREATED'), 200);
                    } else {
                        return $this->sendResponse($data, 'Some thing went wrong', 204, false);
                    }
                }
            } else {
                return $this->sendResponse($data, trans('en_lang.ACCESS_DENIED'), 401, false);
            }
        }
    }

    // Store related link courses data
    public function storeDepartmentCourses(Request $request)
    {

        $input = $request->all();
        $validator = Validator::make($request->all(), [
            'slug' => 'required',
            'role' => 'required',
            'type' => 'required',
            'path' => 'required',
            'name_en' => 'required|max:255',
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

                $slug = request()->type.'/' . request()->slug . '/' . request()->path;
                $rlink_id = RelatedLink::where('slug', $slug)->pluck('id');
                $course = Course::where('id', request()->id)->first();
                if (request()->path == 'phd') {
                    $type = '1';
                } else if (request()->path == 'post-graduate') {
                    $type = '2';
                } else if (request()->path == 'under-graduate') {
                    $type = '3';
                } else if (request()->path == 'other-program' || request()->path == 'diploma') {
                    $type = '4';
                } else if (request()->path == 'mphil-program') {
                    $type = '5';
                }

                if ($rlink_id->isEmpty()) {
                    return $this->sendResponse($rlink_id, 'Path Not Found', 404, false);
                }
                if ($request->hasFile('currl')) {
                    $currl_data = array();
                    $currl_data['file']     = $input['currl'];
                    $currl_data['path']     ='file/'.$ac_non_ac_id.'/'.request()->path.'/currl';
                    $currl_data['filename'] = $input['currl']->getClientOriginalName();
                    $fileUp = FileUpload($currl_data);
                    $results = json_decode($fileUp->content(), true);
                    if ($results['code'] == 1000) {
                        $input['currl'] = $results['path'];
                        if (!empty($course)) {
                            if ($course->currl != '') {
                                DeleteOldPicture($course->currl);
                            }
                        }
                    }
                }
                if ($request->hasFile('syll')) {
                    $syll_data = array();
                    $syll_data['file']     = $input['syll'];
                    $syll_data['path']     = 'file/'.$ac_non_ac_id.'/'.request()->path.'/syll';
                    $syll_data['filename'] = $input['syll']->getClientOriginalName();
                    $fileUp = FileUpload($syll_data);
                    $results = json_decode($fileUp->content(), true);
                    if ($results['code'] == 1000) {
                        $input['syll'] = $results['path'];
                        if (!empty($course)) {
                            if ($course->syll != '') {
                                DeleteOldPicture($course->syll);
                            }
                        }
                    }
                }
                if (isset($request->id) != null) {

                    $data['name_en']   = $request->name_en;
                    $data['nos_en']    = $request->nos_en;
                    if ($request->name_hi == 'null' || $request->name_hi == Null) {
                        $data['name_hi'] = Null;
                    }else{
                        $data['name_hi'] = $request->name_hi;
                    }

                    if ($request->name_ur == 'null' || $request->name_ur == Null) {
                        $data['name_ur'] = Null;
                    }else{
                        $data['name_ur']   = $request->name_ur;
                    }

                    if ($request->name_ur == 'null' || $request->name_ur == Null) {
                        $data['name_ur'] = Null;
                    }else{
                        $data['name_ur']   = $request->name_ur;
                    }

                    if ($request->dr_en == 'null' || $request->dr_en == Null) {
                        $data['dr_en'] = Null;
                    }else{
                        $data['dr_en']   = $request->dr_en;
                    }

                    if ($request->dr_hi == 'null' || $request->dr_hi == Null) {
                        $data['dr_hi'] = Null;
                    }else{
                        $data['dr_hi']   = $request->dr_hi;
                    }

                    if ($request->dr_ur == 'null' || $request->dr_ur == Null) {
                        $data['dr_ur'] = Null;
                    }else{
                        $data['dr_ur']   = $request->dr_ur;
                    }

                    if ($request->cr_en == 'null' || $request->cr_en == Null) {
                        $data['cr_en'] = Null;
                    }else{
                        $data['cr_en']   = $request->cr_en;
                    }

                    if ($request->cr_hi == 'null' || $request->cr_hi == Null) {
                        $data['cr_hi'] = Null;
                    }else{
                        $data['cr_hi']   = $request->cr_hi;
                    }

                    if ($request->cr_ur == 'null' || $request->cr_ur == Null) {
                        $data['cr_ur'] = Null;
                    }else{
                        $data['cr_ur']   = $request->cr_ur;
                    }

                    if ($request->jp_en == 'null' || $request->jp_en == Null) {
                        $data['jp_en'] = Null;
                    }else{
                        $data['jp_en']   = $request->jp_en;
                    }

                    if ($request->jp_hi == 'null' || $request->jp_hi == Null) {
                        $data['jp_hi'] = Null;
                    }else{
                        $data['jp_hi']   = $request->jp_hi;
                    }

                    if ($request->jp_ur == 'null' || $request->jp_ur == Null) {
                        $data['jp_ur'] = Null;
                    }else{
                        $data['jp_ur']   = $request->jp_ur;
                    }

                    if ($request->spec_en == 'null' || $request->spec_en == Null) {
                        $data['spec_en'] = Null;
                    }else{
                        $data['spec_en']   = $request->spec_en;
                    }

                    if ($request->spec_hi == 'null' || $request->spec_hi == Null) {
                        $data['spec_hi'] = Null;
                    }else{
                        $data['spec_hi']   = $request->spec_hi;
                    }

                    if ($request->spec_ur == 'null' || $request->spec_ur == Null) {
                        $data['spec_ur'] = Null;
                    }else{
                        $data['spec_ur']   = $request->spec_ur;
                    }

                    if ($request->peo_en == 'null' || $request->peo_en == Null) {
                        $data['peo_en'] = Null;
                    }else{
                        $data['peo_en']   = $request->peo_en;
                    }

                    if ($request->peo_hi == 'null' || $request->peo_hi == Null) {
                        $data['peo_hi'] = Null;
                    }else{
                        $data['peo_hi']   = $request->peo_hi;
                    }

                    if ($request->peo_ur == 'null' || $request->peo_ur == Null) {
                        $data['peo_ur'] = Null;
                    }else{
                        $data['peo_ur']   = $request->peo_ur;
                    }

                    if ($request->po_en == 'null' || $request->po_en == Null) {
                        $data['po_en'] = Null;
                    }else{
                        $data['po_en']   = $request->po_en;
                    }

                    if ($request->po_hi == 'null' || $request->po_hi == Null) {
                        $data['po_hi'] = Null;
                    }else{
                        $data['po_hi']   = $request->po_hi;
                    }

                    if ($request->po_ur == 'null' || $request->po_ur == Null) {
                        $data['po_ur'] = Null;
                    }else{
                        $data['po_ur']   = $request->po_ur;
                    }
                    
                    $data['approval_status']  = $request->approval_status;
                    if ($request->hasFile('currl')) {
                        $data['currl'] = $input['currl'];
                    }
                    if ($request->hasFile('syll')) {
                        $data['syll'] = $input['syll'];
                    }
                    if ($request->acrr == '1') {
                        $data['acrr']   = 'Yes';
                    }else{
                        $data['acrr']   = 'No';
                    }
                    $data = Course::where('id', request()->id)->update($data);
                    if ($data) {

                        $logtype = LogType::select('id')->where('type', '=', 'academic')->first();
                        if (is_null($logtype)) {
                            return $this->sendResponse($logtype, 'Log type data not found', 404);
                        } else {
                            $dta = array();
                            $dta['log_type_id'] = $logtype['id'];
                            $dta['user_id'] = $user_id;
                            $dta['ip'] = $request->ip();
                            $dta['action'] = $request->approval_status;
                            $dta['ac_non_ac_id'] = $ac_non_ac_id;
                            $dta['related_link_id'] = $rlink_id[0];
                            UserLogSave($dta);
                        }
                        return $this->sendResponse($data, trans('en_lang.DATA_UPDATED'), 200);
                    } else {
                        return $this->sendResponse($data, 'Some thing went wrong', 204, false);
                    }
                } else {

                    $data['ac_non_ac_id']     = $ac_non_ac_id;
                    $data['type']             = $type;
                    $data['name_en']          = $request->name_en;
                    $data['name_hi']          = $request->name_hi ? $request->name_hi : '';
                    $data['name_ur']          = $request->name_ur ? $request->name_ur : '';
                    $data['nos_en']           = $request->nos_en ? $request->nos_en : '';
                    $data['nos_hi']           = $request->nos_hi ? $request->nos_hi : '';
                    $data['nos_ur']           = $request->nos_ur ? $request->nos_ur : '';
                    $data['dr_en']            = $request->dr_en ? $request->dr_en : '';
                    $data['dr_hi']            = $request->dr_hi ? $request->dr_hi : '';
                    $data['dr_ur']            = $request->dr_ur ? $request->dr_ur : '';
                    $data['cr_en']            = $request->cr_en ? $request->cr_en : '';
                    $data['cr_hi']            = $request->cr_hi ? $request->cr_hi : '';
                    $data['cr_ur']            = $request->cr_ur ? $request->cr_ur : '';
                    $data['jp_en']            = $request->jp_en ? $request->jp_en : '';
                    $data['jp_hi']            = $request->jp_hi ? $request->jp_hi : '';
                    $data['jp_ur']            = $request->jp_ur ? $request->jp_ur : '';
                    $data['spec_en']          = $request->spec_en ? $request->spec_en : '';
                    $data['spec_hi']          = $request->spec_hi ? $request->spec_hi : '';
                    $data['spec_ur']          = $request->spec_ur ? $request->spec_ur : '';
                    $data['peo_en']           = $request->peo_en ? $request->peo_en : '';
                    $data['peo_hi']           = $request->peo_hi ? $request->peo_hi : '';
                    $data['peo_ur']           = $request->peo_ur ? $request->peo_ur : '';
                    $data['po_en']            = $request->po_en ? $request->po_en : '';
                    $data['po_hi']            = $request->po_hi ? $request->po_hi : '';
                    $data['po_ur']            = $request->po_ur ? $request->po_ur : '';
                    if ($request->acrr == '1') {
                        $data['acrr']   = 'Yes';
                    }else{
                        $data['acrr']   = 'No';
                    }
                    $data['approval_status']           = $request->approval_status;
                    $data['order_on']         = $request->order_on;
                    if ($request->hasFile('currl')) {
                        $data['currl'] = $input['currl'];
                    }
                    if ($request->hasFile('syll')) {
                        $data['syll'] = $input['syll'];
                    }

                    $data = Course::create($data);
                    if ($data) {
                        $logtype = LogType::select('id')->where('type', '=', 'academic')->first();
                        if (is_null($logtype)) {
                            return $this->sendResponse($logtype, 'Log type data not found', 404);
                        } else {
                            $dta = array();
                            $dta['log_type_id'] = $logtype['id'];
                            $dta['user_id'] = $user_id;
                            $dta['ip'] = $request->ip();
                            $dta['action'] = $request->approval_status;
                            $dta['ac_non_ac_id'] = $ac_non_ac_id;
                            $dta['related_link_id'] = $rlink_id[0];
                            UserLogSave($dta);
                        }
                        return $this->sendResponse($data, trans('en_lang.DATA_CREATED'), 200);
                    } else {
                        return $this->sendResponse($data, 'Some thing went wrong', 204, false);
                    }
                }
            } else {
                return $this->sendResponse($data, trans('en_lang.ACCESS_DENIED'), 401, false);
            }
        }
    }

    //Update Course Order List. 
    public function updateCourseOrderList(Request $request)
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
            $academic =  AcademicAndNonAcademic::where('slug', request()->slug)->with('getHeadType')->first();
            $ac_non_ac_id = $academic->id;
            $user_id = $request->user()->id;
            $role = request()->role;
            $head = $academic->getHeadType->role_type;  
            if (checkUserRole($ac_non_ac_id, $user_id, $role)) {
                if ($role == $head || $role == "Approver") {

                    $slug = request()->type.'/' . request()->slug . '/' . request()->path;
                    $rlink_id = RelatedLink::where('slug', $slug)->pluck('id');
                    if ($rlink_id->isEmpty()) {
                        return $this->sendResponse($rlink_id, 'Path Not Found', 404, false);
                    }

                    if (!empty($request->orders)) {
                        foreach ($request->orders as $key => $order) {
                            Course::where('id', $order['id'])->update(['order_on' => $key + 1]);
                        }
                        return $this->sendResponse('Updated Successfully.', 'DATA FOUND', 200);
                    }

                    $logtype = LogType::select('id')->where('type', '=', 'academic')->first();

                    if (is_null($logtype)) {

                        return $this->sendResponse($logtype, 'Log type data not found', 404);
                    } else {

                        $dta = array();
                        $dta['log_type_id'] = $logtype['id'];
                        $dta['user_id'] = $user_id;
                        $dta['ip'] = $request->ip();
                        $dta['action'] = 'Course Order list updated';
                        $dta['ac_non_ac_id'] = $ac_non_ac_id;
                        $dta['related_link_id'] = $rlink_id[0];
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

    // Store related link ongoing research project data
    public function storeOnGoingProjects(Request $request)
    {

        $input = $request->all();
        $validator = Validator::make($request->all(), [
            'slug' => 'required',
            'role' => 'required',
            'type' => 'required',
            'path' => 'required',
            'about_en' => 'required',
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

                $slug = request()->type.'/'  . request()->slug . '/' . request()->path;
                $rlink_id = RelatedLink::where('slug', $slug)->pluck('id');
                if ($rlink_id->isEmpty()) {
                    return $this->sendResponse($rlink_id, 'Path Not Found', 404, false);
                }

                if (isset($request->id) != null) {
                    $ongProject = OnGoingProject::where('id', request()->id)->first();
                    $data['about_en']   = $request->about_en;
                    $data['about_hi']   = $request->about_hi ? $request->about_hi : $ongProject->about_hi;
                    $data['about_ur']   = $request->about_ur ? $request->about_ur : $ongProject->about_ur;
                    $data['fagency']    = $request->fagency ? $request->fagency : $ongProject->fagency;
                    $data['famount']    = $request->famount ? $request->famount : $ongProject->famount;
                    $data['pi']    = $request->pi ? $request->pi : $ongProject->pi;
                    $data['cpi']     = $request->cpi ? $request->cpi : $ongProject->cpi;
                    $data['order_on']     = $request->order_on ? $request->order_on : $ongProject->order_on;
                    $data['approval_status']    = $request->approval_status;

                    $data = OnGoingProject::where('id', request()->id)->update($data);
                    if ($data) {

                        $logtype = LogType::select('id')->where('type', '=', 'academic')->first();
                        if (is_null($logtype)) {
                            return $this->sendResponse($logtype, 'Log type data not found', 404);
                        } else {
                            $dta = array();
                            $dta['log_type_id'] = $logtype['id'];
                            $dta['user_id'] = $user_id;
                            $dta['ip'] = $request->ip();
                            $dta['action'] = $request->approval_status; //'Updated academic ongoing research project'; 
                            $dta['ac_non_ac_id'] = $ac_non_ac_id;
                            $dta['related_link_id'] = $rlink_id[0];
                            UserLogSave($dta);
                        }
                        return $this->sendResponse($data, trans('en_lang.DATA_UPDATED'), 200);
                    } else {
                        return $this->sendResponse($data, 'Some thing went wrong', 204, false);
                    }
                } else {

                    $data['ac_non_ac_id']  = $ac_non_ac_id;
                    $data['about_en']   = $request->about_en;
                    $data['about_hi']   = $request->about_hi ? $request->about_hi : '';
                    $data['about_ur']   = $request->about_ur ? $request->about_ur : '';
                    $data['fagency']    = $request->fagency ? $request->fagency : '';
                    $data['famount']    = $request->famount ? $request->famount : '';
                    $data['pi']         = $request->pi ? $request->pi : '';
                    $data['cpi']        = $request->cpi ? $request->cpi : '';
                    $data['order_on']   = $request->order_on ? $request->order_on : '0';
                    $data['approval_status']     = $request->approval_status;

                    $data = OnGoingProject::create($data);

                    if ($data) {
                        $logtype = LogType::select('id')->where('type', '=', 'academic')->first();
                        if (is_null($logtype)) {
                            return $this->sendResponse($logtype, 'Log type data not found', 404);
                        } else {
                            $dta = array();
                            $dta['log_type_id'] = $logtype['id'];
                            $dta['user_id'] = $user_id;
                            $dta['ip'] = $request->ip();
                            $dta['action'] = $request->approval_status; //'Added academic ongoing research project'; 
                            $dta['ac_non_ac_id'] = $ac_non_ac_id;
                            $dta['related_link_id'] = $rlink_id[0];
                            UserLogSave($dta);
                        }
                        return $this->sendResponse($data, trans('en_lang.DATA_CREATED'), 200);
                    } else {
                        return $this->sendResponse($data, 'Some thing went wrong', 204, false);
                    }
                }
            } else {
                return $this->sendResponse($data, trans('en_lang.ACCESS_DENIED'), 401, false);
            }
        }
    }

    // Store related link completed research projects data
    public function storeCompletedProjects(Request $request)
    {

        $input = $request->all();
        $validator = Validator::make($request->all(), [
            'slug' => 'required',
            'role' => 'required',
            'type' => 'required',
            'path' => 'required',
            'about_en' => 'required',
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

                $slug = request()->type.'/' . request()->slug . '/' . request()->path;
                $rlink_id = RelatedLink::where('slug', $slug)->pluck('id');
                if ($rlink_id->isEmpty()) {
                    return $this->sendResponse($rlink_id, 'Path Not Found', 404, false);
                }

                if (isset($request->id) != null) {
                    $compltedProject = CompletedProject::where('id', request()->id)->first();
                    $data['about_en']   = $request->about_en;
                    $data['about_hi']   = $request->about_hi ? $request->about_hi : $compltedProject->about_hi;
                    $data['about_ur']   = $request->about_ur ? $request->about_ur : $compltedProject->about_ur;
                    $data['fagency']    = $request->fagency ? $request->fagency : $compltedProject->fagency;
                    $data['famount']    = $request->famount ? $request->famount : $compltedProject->famount;
                    $data['pi']    = $request->pi ? $request->pi : $compltedProject->pi;
                    $data['cpi']     = $request->cpi ? $request->cpi : $compltedProject->cpi;
                    $data['order_on']     = $request->order_on ? $request->order_on : $compltedProject->order_on;
                    $data['awards_en']     = $request->awards_en ? $request->awards_en : $compltedProject->awards_en;
                    $data['awards_hi']     = $request->awards_hi ? $request->awards_hi : $compltedProject->awards_hi;
                    $data['awards_ur']     = $request->awards_ur ? $request->awards_ur : $compltedProject->awards_ur;
                    $data['pub_en']     = $request->pub_en ? $request->pub_en : $compltedProject->pub_en;
                    $data['pub_hi']     = $request->pub_hi ? $request->pub_hi : $compltedProject->pub_hi;
                    $data['pub_ur']     = $request->pub_ur ? $request->pub_ur : $compltedProject->pub_ur;
                    $data['approval_status']    = $request->approval_status;

                    $data = CompletedProject::where('id', request()->id)->update($data);
                    if ($data) {

                        $logtype = LogType::select('id')->where('type', '=', 'academic')->first();
                        if (is_null($logtype)) {
                            return $this->sendResponse($logtype, 'Log type data not found', 404);
                        } else {
                            $dta = array();
                            $dta['log_type_id'] = $logtype['id'];
                            $dta['user_id'] = $user_id;
                            $dta['ip'] = $request->ip();
                            $dta['action'] = $request->approval_status; //'Updated academic completed projects'; 
                            $dta['ac_non_ac_id'] = $ac_non_ac_id;
                            $dta['related_link_id'] = $rlink_id[0];
                            UserLogSave($dta);
                        }
                        return $this->sendResponse($data, trans('en_lang.DATA_UPDATED'), 200);
                    } else {
                        return $this->sendResponse($data, 'Some thing went wrong', 204, false);
                    }
                } else {

                    $data['ac_non_ac_id']  = $ac_non_ac_id;
                    $data['about_en']   = $request->about_en;
                    $data['about_hi']   = $request->about_hi ? $request->about_hi : '';
                    $data['about_ur']   = $request->about_ur ? $request->about_ur : '';
                    $data['fagency']    = $request->fagency ? $request->fagency : '';
                    $data['famount']    = $request->famount ? $request->famount : '';
                    $data['pi']         = $request->pi ? $request->pi : '';
                    $data['cpi']        = $request->cpi ? $request->cpi : '';
                    $data['order_on']   = $request->order_on ? $request->order_on : '0';
                    $data['awards_en']  = $request->awards_en ? $request->awards_en : '';
                    $data['awards_hi']  = $request->awards_hi ? $request->awards_hi : '';
                    $data['awards_ur']  = $request->awards_ur ? $request->awards_ur : '';
                    $data['pub_en']     = $request->pub_en ? $request->pub_en : '';
                    $data['pub_hi']     = $request->pub_hi ? $request->pub_hi : '';
                    $data['pub_ur']     = $request->pub_ur ? $request->pub_ur : '';
                    $data['approval_status']     = $request->approval_status;

                    $data = CompletedProject::create($data);

                    if ($data) {
                        $logtype = LogType::select('id')->where('type', '=', 'academic')->first();
                        if (is_null($logtype)) {
                            return $this->sendResponse($logtype, 'Log type data not found', 404);
                        } else {
                            $dta = array();
                            $dta['log_type_id'] = $logtype['id'];
                            $dta['user_id'] = $user_id;
                            $dta['ip'] = $request->ip();
                            $dta['action'] = $request->approval_status; //'Added academic completed projects'; 
                            $dta['ac_non_ac_id'] = $ac_non_ac_id;
                            $dta['related_link_id'] = $rlink_id[0];
                            UserLogSave($dta);
                        }
                        return $this->sendResponse($data, trans('en_lang.DATA_CREATED'), 200);
                    } else {
                        return $this->sendResponse($data, 'Some thing went wrong', 204, false);
                    }
                }
            } else {
                return $this->sendResponse($data, trans('en_lang.ACCESS_DENIED'), 401, false);
            }
        }
    }

    // Store Department's Photo Gallery
    public function storePhotoGallery(Request $request)
    {

        $input = $request->all();
        $validator = Validator::make($request->all(), [
            'slug' => 'required',
            'role' => 'required',
            'type' => 'required',
            'path' => 'required',
            'title_en' => 'required',
            'image' => 'required|mimes:jpg,jpeg,bmp,png',
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

                if ($academic->type == '1') {
                    $slug = request()->type.'/'.request()->slug.'/'.request()->path;
                }else{
                    $slug = request()->slug.'/'.request()->path;
                }

                $rlink = RelatedLink::select('id', 'link_name_en')->where('slug', $slug)->first();
                if (empty($rlink)) {
                    return $this->sendResponse($rlink, 'Path Not Found', 404, false);
                }

                if ($request->hasFile('image')) {
                    $image_data = array();
                    $results = array();
                    $final_data = array();
                    $image_data['image'] = $input['image'];
                    $image_data['path']     = 'images/'.$ac_non_ac_id.'/'. request()->path;
                    $image_data['filename'] = $input['image']->getClientOriginalName();
                    $fileUp = ImageUpload($image_data);
                    $results = json_decode($fileUp->content(), true);
                    if ($results['code'] == 1000) {
                        $final_data['title_en'] = $request->title_en;
                        $final_data['title_hi'] = $request->title_hi;
                        $final_data['title_ur'] = $request->title_ur;
                        $final_data['ac_non_ac_id'] = $ac_non_ac_id;
                        $final_data['order_on'] = $request->order_on;
                        $final_data['image'] = $results['path'];
                        $data = AcNonAcGallery::create($final_data);
                        $logtype = LogType::select('id')->where('type', '=', 'academic')->first();
                        if (is_null($logtype)) {
                            return $this->sendResponse($logtype, 'Log type data not found', 404);
                        } else {
                            $dta = array();
                            $dta['log_type_id'] = $logtype->id;
                            $dta['user_id'] = $user_id;
                            $dta['ip'] = $request->ip();
                            $dta['action'] = 'Uploaded ' . strtolower($rlink->link_name_en) . ' Gallery';
                            $dta['ac_non_ac_id'] = $ac_non_ac_id;
                            $dta['related_link_id'] = $rlink->id;
                            UserLogSave($dta);
                        }
                        return $this->sendResponse($data, 'Image Uploaded', 200, true);
                    } else {
                        return $this->sendResponse($data, trans('en_lang.ACCESS_DENIED'), 401, false);
                    }
                }
            } else {
                return $this->sendResponse($data, trans('en_lang.ACCESS_DENIED'), 401, false);
            }
        }
    }

    // Get Related link Single Data
    public function getDepartmentSingleData(Request $request)
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
            $data = array();
            $academic =  AcademicAndNonAcademic::where('slug', request()->slug)->first();
            $ac_non_ac_id = $academic->id;
            $user_id = $request->user()->id;
            $role = request()->role;
            if (checkUserRole($ac_non_ac_id, $user_id, $role)) {

                if (request()->path == 'contact-us' || request()->path == 'contact-details') {

                    $contactUs = RelatedLinkData::where('ac_non_ac_id', $ac_non_ac_id)->where('related_link_id', $rlink_id[0])->first();

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
                        $data['link_en'] = $contactUs->link_en_draft ??  $contactUs->link_en;
                        $data['link_description_en'] = $contactUs->link_description_en_draft ?? $contactUs->link_description_en;
                        $data['link_hi'] = $contactUs->link_hi_draft ??  $contactUs->link_hi;
                        $data['link_description_hi'] = $contactUs->link_description_hi_draft ?? $contactUs->link_description_hi;
                        $data['link_ur'] = $contactUs->link_ur_draft ?? $contactUs->link_ur;
                        $data['link_description_ur'] = $contactUs->link_description_ur_draft ??  $contactUs->link_description_ur;
                    }
                    $data['approval_status'] = $contactUs->approval_status;
                    if ($data) {
                        return $this->sendResponse($data, 'DATA FOUND', 200);
                    } else {
                        return $this->sendResponse($data, 'DATA NOT FOUND', 404, false);
                    }
                } elseif (request()->path == 'faculty-members' || request()->path == 'non-teaching-staff' || request()->path == 'pg-student' || request()->path == 'staff-members' || request()->path == 'staff-members-teaching') {

                    $data = UserVisibility::select('id', 'user_id', 'designation_id', 'role_id', 'special_role')->where('id', '=', request()->id)
                        ->with('getUser:id,eid,title,first_name,middle_name,last_name,slug')
                        ->first();;
                } elseif (request()->path == 'list-of-former-chairperson') {

                    $data = FormerChairPerson::where('id', request()->id)->first();
                } elseif (request()->path == 'phd' || request()->path == 'post-graduate' || request()->path == 'under-graduate' || request()->path == 'other-program' || request()->path == 'mphil-program' || request()->path == 'diploma') {

                    $data = Course::where('id', request()->id)->first();
                    if ($data->acrr == 'Yes') {
                        $data['acrr'] = 1;
                    }else{
                        $data['acrr'] = 0;
                    }
                } elseif (request()->path == 'on-going-research-projects') {

                    $data = OnGoingProject::where('id', request()->id)->first();
                } elseif (request()->path == 'completed-research-projects') {

                    $data = CompletedProject::where('id', request()->id)->first();
                } elseif (request()->path == 'notice-and-circular') {

                    $dta = NoticeCircular::where('id', request()->id)->first();
                    $data['id'] = $dta->id;
                    $data['approval_status'] = $dta->approval_status;
                    $data['file'] = $dta->file;
                    $data['date'] = $dta->date;
                    $data['to_date'] = $dta->to_date;
                    $data['order_on'] = $dta->order_on;
                    if ($dta->approval_status == 'Approved') {
                        $data['title_en'] = $dta->title_en;
                        $data['description_en'] = $dta->description_en;
                        $data['title_hi'] = $dta->title_hi;
                        $data['description_hi'] = $dta->description_hi;
                        $data['title_ur'] = $dta->title_ur;
                        $data['description_ur'] = $dta->description_ur;
                    } else {
                        $data['title_en'] = $dta->title_en_draft ? $dta->title_en_draft : $dta->title_en;
                        $data['description_en'] = $dta->description_en_draft ? $dta->description_en_draft : $dta->description_en;
                        $data['title_hi'] = $dta->title_hi_draft ? $dta->title_hi_draft : $dta->title_hi;
                        $data['description_hi'] = $dta->description_hi_draft ? $dta->description_hi_draft : $dta->description_hi;
                        $data['title_ur'] = $dta->title_ur_draft ? $dta->title_ur_draft : $dta->title_ur;
                        $data['description_ur'] = $dta->description_ur_draft ? $dta->description_ur_draft : $dta->description_ur;
                    }
                } elseif (request()->path == 'photo-gallery') {

                    $data = AcNonAcGallery::where('id', request()->id)->first();
                } else {
                    $dta = RelatedLinkData::where('id', request()->id)->first();
                    $data['id'] = $dta->id;
                    $data['approval_status'] = $dta->approval_status;
                    $data['order_on'] = $dta->order_on;
                    if ($dta->file != '' && $dta->file != '') {
                        $data['file'] = $dta->file;
                    }
                    if ($dta->approval_status == 'Approved') {
                        $data['link_en'] = $dta->link_en;
                        $data['link_description_en'] = $dta->link_description_en;
                        $data['link_hi'] = $dta->link_hi;
                        $data['link_description_hi'] = $dta->link_description_hi;
                        $data['link_ur'] = $dta->link_ur;
                        $data['link_description_ur'] = $dta->link_description_ur;
                    } else {
                        $data['link_en'] = $dta->link_en_draft ? $dta->link_en_draft : $dta->link_en;
                        $data['link_description_en'] = $dta->link_description_en_draft ? $dta->link_description_en_draft : $dta->link_description_en;
                        $data['link_hi'] = $dta->link_hi_draft ? $dta->link_hi_draft : $dta->link_hi;
                        $data['link_description_hi'] = $dta->link_description_hi_draft ? $dta->link_description_hi_draft : $dta->link_description_hi;
                        $data['link_ur'] = $dta->link_ur_draft ? $dta->link_ur_draft : $dta->link_ur;
                        $data['link_description_ur'] = $dta->link_description_ur_draft ? $dta->link_description_ur_draft : $dta->link_description_ur;
                    }
                }

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

    // Delete Related link Sinle Data
    public function deleteDepartmentSingleData(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'id' => 'required',
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

                    if ($academic->type == '1') {
                        $slug = request()->type.'/'.request()->slug.'/'.request()->path;
                    }else{
                        $slug = request()->slug.'/'.request()->path;
                    }
                    $rlink_id = RelatedLink::where('slug', $slug)->pluck('id');
                    $logtype = LogType::select('id')->where('type', '=', 'academic')->first();
                    if ($rlink_id->isEmpty()) {
                        return $this->sendResponse($rlink_id, 'Path Not Found', 404, false);
                    }

                    if (request()->path == 'faculty-members' || request()->path == 'non-teaching-staff' || request()->path == 'pg-student' || request()->path == 'staff-members' || request()->path == 'staff-members-teaching') {
                        
                        $visibility = UserVisibility::where('id',request()->id)->first();
                
                        if ($visibility->core == '1') {
                           $delete =  UserVisibility::where('user_id', $visibility->user_id)->delete();
                            User::where('user_id', $visibility->user_id)->update(['status' => '0']);
                        }else{
                           $delete = UserVisibility::where('id', request()->id)->delete(); 
                        }

                        if ($visibility) {

                            if (is_null($logtype)) {
                                return $this->sendResponse($logtype, 'Log type data not found', 404);
                            } else {

                                $dta = array();
                                $dta['log_type_id'] = $logtype['id'];
                                $dta['user_id'] = $user_id;
                                $dta['ip'] = $request->ip();
                                $dta['action'] = 'Academic ' . request()->path . ' Data Deleted';
                                $dta['ac_non_ac_id'] = $ac_non_ac_id;
                                $dta['related_link_id'] = $rlink_id[0];
                                UserLogSave($dta);
                            }

                            return $this->sendResponse('true', 'academic user deleted', 200);
                        } else {

                            return $this->sendResponse('false', 'Something went Wrong!', 404);
                        }
                    } elseif (request()->path == 'list-of-former-chairperson') {

                        if (FormerChairPerson::where('id', request()->id)->delete()) {

                            if (is_null($logtype)) {
                                return $this->sendResponse($logtype, 'Log type data not found', 404);
                            } else {

                                $dta = array();
                                $dta['log_type_id'] = $logtype['id'];
                                $dta['user_id'] = $user_id;
                                $dta['ip'] = $request->ip();
                                $dta['action'] = 'Academic ' . request()->path . ' Data Deleted';
                                $dta['ac_non_ac_id'] = $ac_non_ac_id;
                                $dta['related_link_id'] = $rlink_id[0];
                                UserLogSave($dta);
                            }
                            return $this->sendResponse('true', 'Academic former chair person data deleted', 200);
                        } else {

                            return $this->sendResponse('false', 'Something went Wrong!', 404);
                        }
                    } elseif (request()->path == 'phd' || request()->path == 'post-graduate' || request()->path == 'under-graduate' || request()->path == 'other-program' || request()->path == 'mphil-program' || request()->path == 'diploma') {

                        if (Course::where('id', request()->id)->delete()) {

                            if (is_null($logtype)) {
                                return $this->sendResponse($logtype, 'Log type data not found', 404);
                            } else {

                                $dta = array();
                                $dta['log_type_id'] = $logtype['id'];
                                $dta['user_id'] = $user_id;
                                $dta['ip'] = $request->ip();
                                $dta['action'] = 'Academic ' . request()->path . ' Data Deleted';
                                $dta['ac_non_ac_id'] = $ac_non_ac_id;
                                $dta['related_link_id'] = $rlink_id[0];
                                UserLogSave($dta);
                            }
                            return $this->sendResponse('true', 'Academic course data deleted', 200);
                        } else {

                            return $this->sendResponse('false', 'Something went Wrong!', 404);
                        }
                    } elseif (request()->path == 'on-going-research-projects') {

                        if (OnGoingProject::where('id', request()->id)->delete()) {

                            if (is_null($logtype)) {
                                return $this->sendResponse($logtype, 'Log type data not found', 404);
                            } else {

                                $dta = array();
                                $dta['log_type_id'] = $logtype['id'];
                                $dta['user_id'] = $user_id;
                                $dta['ip'] = $request->ip();
                                $dta['action'] = 'Academic ' . request()->path . ' Data Deleted';
                                $dta['ac_non_ac_id'] = $ac_non_ac_id;
                                $dta['related_link_id'] = $rlink_id[0];
                                UserLogSave($dta);
                            }
                            return $this->sendResponse('true', 'Academic On Going Project Deleted', 200);
                        } else {

                            return $this->sendResponse('false', 'Something went Wrong!', 404);
                        }
                    } elseif (request()->path == 'completed-research-projects') {

                        if (CompletedProject::where('id', request()->id)->delete()) {

                            if (is_null($logtype)) {
                                return $this->sendResponse($logtype, 'Log type data not found', 404);
                            } else {

                                $dta = array();
                                $dta['log_type_id'] = $logtype['id'];
                                $dta['user_id'] = $user_id;
                                $dta['ip'] = $request->ip();
                                $dta['action'] = 'Academic ' . request()->path . ' Data Deleted';
                                $dta['ac_non_ac_id'] = $ac_non_ac_id;
                                $dta['related_link_id'] = $rlink_id[0];
                                UserLogSave($dta);
                            }
                            return $this->sendResponse('true', 'Academic Completed Research Project Deleted', 200);
                        } else {

                            return $this->sendResponse('false', 'Something went Wrong!', 404);
                        }
                    } elseif (request()->path == 'notice-and-circular') {

                        if (NoticeCircular::where('id', request()->id)->delete()) {

                            if (is_null($logtype)) {
                                return $this->sendResponse($logtype, 'Log type data not found', 404);
                            } else {

                                $dta = array();
                                $dta['log_type_id'] = $logtype['id'];
                                $dta['user_id'] = $user_id;
                                $dta['ip'] = $request->ip();
                                $dta['action'] = 'Academic ' . request()->path . ' Data Deleted';
                                $dta['ac_non_ac_id'] = $ac_non_ac_id;
                                $dta['related_link_id'] = $rlink_id[0];
                                UserLogSave($dta);
                            }
                            return $this->sendResponse('true', 'Academic Notice And Circular Deleted', 200);
                        } else {

                            return $this->sendResponse('false', 'Something went Wrong!', 404);
                        }
                    } elseif (request()->path == 'photo-gallery') {

                        if (AcNonAcGallery::where('id', request()->id)->delete()) {

                            if (is_null($logtype)) {
                                return $this->sendResponse($logtype, 'Log type data not found', 404);
                            } else {

                                $dta = array();
                                $dta['log_type_id'] = $logtype['id'];
                                $dta['user_id'] = $user_id;
                                $dta['ip'] = $request->ip();
                                $dta['action'] = 'Academic ' . request()->path . ' Data Deleted';
                                $dta['ac_non_ac_id'] = $ac_non_ac_id;
                                $dta['related_link_id'] = $rlink_id[0];
                                UserLogSave($dta);
                            }
                            return $this->sendResponse('true', 'Academic Photo Gallery Deleted', 200);
                        } else {

                            return $this->sendResponse('false', 'Something went Wrong!', 404);
                        }
                    } else {

                        if (RelatedLinkData::where('id', request()->id)->delete()) {

                            if (is_null($logtype)) {
                                return $this->sendResponse($logtype, 'Log type data not found', 404);
                            } else {

                                $dta = array();
                                $dta['log_type_id'] = $logtype['id'];
                                $dta['user_id'] = $user_id;
                                $dta['ip'] = $request->ip();
                                $dta['action'] = 'Academic ' . request()->path . ' Data Deleted';
                                $dta['ac_non_ac_id'] = $ac_non_ac_id;
                                $dta['related_link_id'] = $rlink_id[0];
                                UserLogSave($dta);
                            }
                            return $this->sendResponse('true', 'Academic Data Deleted', 200);
                        } else {

                            return $this->sendResponse('false', 'Something went Wrong!', 404);
                        }
                    }
                } else {
                    return $this->sendResponse("false", trans('en_lang.ACCESS_DENIED'), 401);
                }
            } else {
                return $this->sendResponse("false", trans('en_lang.ACCESS_DENIED'), 401);
            }
        }
    }

    // Get Department's User Log list
    public function getCommonUserLog(Request $request)
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

                if ($academic->type == '1') {
                    $slug = request()->type.'/'.request()->slug.'/'.request()->path;
                }else{
                    $slug = request()->slug.'/'.request()->path;
                }
                $rlink = RelatedLink::select('id')->where('slug', $slug)->first();
                if ($rlink) {
                   $rlink_id = $rlink->id;
                }else{
                    $rlink_id = request()->path;
                }
                $userLog = UserLog::select('id', 'user_id', 'ip', 'action','action_summary', 'ac_non_ac_id', 'related_link_id', 'created_at')->where('related_link_id', $rlink_id)->where('ac_non_ac_id', $ac_non_ac_id)
                    ->with(['getUserRole' => function($q)  use($ac_non_ac_id){ 
                        $q->where('ac_non_ac_id', '=',$ac_non_ac_id);
                        $q->with('getUser:id,title,first_name,middle_name,last_name','getRole','getDesignation');
                    }])->orderBy('created_at', 'DESC')->paginate(env('ITEM_PER_PAGE'));

                if ($userLog) {
                    return $this->sendResponse($userLog, trans('en_lang.DATA_FOUND'), 200);
                } else {
                    return $this->sendResponse($userLog, trans('en_lang.DATA_NOT_FOUND'),404);
                }
            } else {
                return $this->sendResponse($userLog, trans('en_lang.ACCESS_DENIED'), 401, false);
            }
        }
    }

    // Update order number for research project data
    public function updateOrderResearchProject(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'slug' => 'required',
            'role' => 'required',
            'type' => 'required',
            'path' => 'required',
            'orders' => 'required',
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

                    $slug = request()->type.'/' . request()->slug . '/' . request()->path;
                    $rlink_id = RelatedLink::where('slug', $slug)->pluck('id');
                    if ($rlink_id->isEmpty()) {
                        return $this->sendResponse($rlink_id, 'Path Not Found', 404, false);
                    }

                    if (request()->path == 'on-going-research-projects') {

                        if (!empty($request->orders)) {
                            foreach ($request->orders as $key => $order) {
                                OnGoingProject::where('id', $order['id'])->update(['order_on' => $key + 1]);
                            }
                            return $this->sendResponse('Update Successfully.', 'DATA FOUND', 200);
                        }
                    } else {

                        if (!empty($request->orders)) {
                            foreach ($request->orders as $key => $order) {
                                CompletedProject::where('id', $order['id'])->update(['order_on' => $key + 1]);
                            }
                            return $this->sendResponse('Update Successfully.', 'DATA FOUND', 200);
                        }
                    }

                    $logtype = LogType::select('id')->where('type', '=', 'academic')->first();

                    if (is_null($logtype)) {

                        return $this->sendResponse($logtype, 'Log type data not found', 404);
                    } else {

                        $dta = array();
                        $dta['log_type_id'] = $logtype['id'];
                        $dta['user_id'] = $user_id;
                        $dta['ip'] = $request->ip();
                        $dta['action'] = 'Order list updated for academic research project data';
                        $dta['ac_non_ac_id'] = $ac_non_ac_id;
                        $dta['related_link_id'] = $rlink_id[0];
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

    // Update order number for related link data
    public function updateOrderDepartmentCommonData(Request $request)
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
            $academic =  AcademicAndNonAcademic::where('slug', request()->slug)->first();
            $ac_non_ac_id = $academic->id;
            $user_id = $request->user()->id;
            $role = request()->role;
            if (checkUserRole($ac_non_ac_id, $user_id, $role)) {

                    if ($academic->type == '1') {
                        $slug = request()->type.'/'.request()->slug.'/'.request()->path;
                    }else{
                        $slug = request()->slug.'/'.request()->path;
                    }
                    $rlink_id = RelatedLink::where('slug', $slug)->pluck('id');
                    if ($rlink_id->isEmpty()) {
                        return $this->sendResponse($rlink_id, 'Path Not Found', 404, false);
                    }

                    if (request()->path == 'list-of-former-chairperson') {

                        if (!empty($request->orders)) {
                            foreach ($request->orders as $key => $order) {
                                FormerChairPerson::where('id', $order['id'])->update(['order_on' => $key + 1]);
                            }
                            return $this->sendResponse('Update Successfully.', 'DATA FOUND', 200);
                        }
                    }else if (request()->path == 'notice-and-circular') {

                        if (!empty($request->orders)) {
                            foreach ($request->orders as $key => $order) {
                                NoticeCircular::where('id', $order['id'])->update(['order_on' => $key + 1]);
                            }
                            return $this->sendResponse('Update Successfully.', 'DATA FOUND', 200);
                        }
                    }else if (request()->path == 'photo-gallery') {

                        if (!empty($request->orders)) {
                            foreach ($request->orders as $key => $order) {
                                AcNonAcGallery::where('id', $order['id'])->update(['order_on' => $key + 1]);
                            }
                            return $this->sendResponse('Update Successfully.', 'DATA FOUND', 200);
                        }
                    } else {

                        if (!empty($request->orders)) {
                            foreach ($request->orders as $key => $order) {
                                RelatedLinkData::where('id', $order['id'])->update(['order_on' => $key + 1]);
                               


                            }
                            return $this->sendResponse('Update Successfully.', 'DATA FOUND', 200);
                        }
                    }

                    $logtype = LogType::select('id')->where('type', '=', 'academic')->first();

                    if (is_null($logtype)) {

                        return $this->sendResponse($logtype, 'Log type data not found', 404);
                    } else {

                        $dta = array();
                        $dta['log_type_id'] = $logtype['id'];
                        $dta['user_id'] = $user_id;
                        $dta['ip'] = $request->ip();
                        $dta['action'] = 'Order list updated for academic data';
                        $dta['ac_non_ac_id'] = $ac_non_ac_id;
                        $dta['related_link_id'] = $rlink_id[0];
                        UserLogSave($dta);
                    }
                
            } else {
                return $this->sendResponse('false', trans('en_lang.ACCESS_DENIED'), 401);
            }
        }
    }

    // Get Department's Tender list
    public function getTenderList(Request $request)
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
                $data = Tender::where('ac_non_ac_id', $ac_non_ac_id)->with('getTenderCategory')->orderBy('updated_at', 'DESC')->paginate(env('ITEM_PER_PAGE'));
                
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

    // Store Academic Tender Data..
    public function storeTenders(Request $request)
    {

        $input = $request->all();
        $validator = Validator::make($request->all(), [
            'slug' => 'required',
            'role' => 'required',
            'type' => 'required',
            'description' => 'required|max:255',
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

               
                $tender = Tender::where('id', request()->id)->first();
                if ($request->hasFile('file')) {
                    $file_data = array();
                    $file_data['file']     = $input['file'];
                    $file_data['path']     = 'file/'.$ac_non_ac_id.'/tender';
                    $file_data['filename'] = $input['file']->getClientOriginalName();
                    $fileUp = FileUpload($file_data);
                    $results = json_decode($fileUp->content(), true);
                    if ($results['code'] == 1000) {
                        $input['file'] = $results['path'];
                        if (!empty($tender)) {
                            if ($tender->file != '') {
                                DeleteOldPicture($tender->file);
                            }
                        }
                    }
                }

                if (isset($request->id) != null) {
                    $data['description']   = $request->description;
                    if ($request->hasFile('file')) {                        
                        $data['file'] =  $input['file'];
                    }
                    $data['approval_status']    = $request->approval_status;

                    $data = Tender::where('id', request()->id)->update($data);
                    if ($data) {

                        $logtype = LogType::select('id')->where('type', '=', 'academic')->first();
                        if (is_null($logtype)) {
                            return $this->sendResponse($logtype, 'Log type data not found', 404);
                        } else {
                            $dta = array();
                            $dta['log_type_id'] = $logtype['id'];
                            $dta['user_id'] = $user_id;
                            $dta['ip'] = $request->ip();
                            $dta['action'] = $request->approval_status; 
                            $dta['ac_non_ac_id'] = $ac_non_ac_id;
                            $dta['related_link_id'] = 'tender';
                            UserLogSave($dta);
                        }
                        return $this->sendResponse($data, trans('en_lang.DATA_UPDATED'), 200);
                    } else {
                        return $this->sendResponse($data, 'Some thing went wrong', 204, false);
                    }
                } else {
                    if (request()->slug == 'building-department') {
                        $data['tender_type']          = '1';
                    }else if (request()->slug == 'electricity-department') {
                        $data['tender_type']          = $request->tender_type;
                    }else if (request()->slug == 'central-purchase-office') {
                        $data['tender_type']          = '4';
                    }else{
                        $data['tender_type']          = $request->tender_type;
                    }

                    $data['description']   = $request->description;
                    $data['ac_non_ac_id']  = $ac_non_ac_id;
                    $data['dataType']      = 'tender';
                    if (isset($input['file'])) {                        
                        $data['file'] =  $input['file'];
                    }
                    $data['approval_status']    = $request->approval_status;
                    $data = Tender::create($data);

                    if ($data) {
                        $logtype = LogType::select('id')->where('type', '=', 'academic')->first();
                        if (is_null($logtype)) {
                            return $this->sendResponse($logtype, 'Log type data not found', 404);
                        } else {
                            $dta = array();
                            $dta['log_type_id'] = $logtype['id'];
                            $dta['user_id'] = $user_id;
                            $dta['ip'] = $request->ip();
                            $dta['action'] = $request->approval_status;
                            $dta['ac_non_ac_id'] = $ac_non_ac_id;
                            $dta['related_link_id'] = 'tender';
                            UserLogSave($dta);
                        }
                        return $this->sendResponse($data, trans('en_lang.DATA_CREATED'), 200);
                    } else {
                        return $this->sendResponse($data, 'Some thing went wrong', 204, false);
                    }
                }
            } else {
                return $this->sendResponse($data, trans('en_lang.ACCESS_DENIED'), 401, false);
            }
        }
    }

    // Get Department Tender  Single Data
    public function getSingleTender(Request $request)
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

                $data = Tender::where('id', request()->id)->first();

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

    // Delete  Department's Tenders
    public function deleteTender(Request $request)
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

                    if (Tender::where('id', request()->id)->delete()) {

                        $logtype = LogType::select('id')->where('type', '=', 'academic')->first();

                        if (is_null($logtype)) {

                            return $this->sendResponse($logtype, 'Log type data not found', 404);
                        } else {

                            $dta = array();
                            $dta['log_type_id'] = $logtype['id'];
                            $dta['user_id'] = $user_id;
                            $dta['ip'] = $request->ip();
                            $dta['action'] = 'Tender Deleted';
                            $dta['ac_non_ac_id'] = $ac_non_ac_id;
                            $dta['related_link_id'] = 'tender';
                            UserLogSave($dta);
                        }

                        return $this->sendResponse('true', 'Tender Deleted', 200);
                    } else {
                        return $this->sendResponse('false', 'Something went Wrong!', 404, false);
                    }
                }

            } else {
                return $this->sendResponse($data, trans('en_lang.ACCESS_DENIED'), 401, false);
            }
        }
    }

    // Update order number for academic tender
    public function updateTenderOrder(Request $request)
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
            $academic =  AcademicAndNonAcademic::where('slug', request()->slug)->with('getHeadType')->first();
            $ac_non_ac_id = $academic->id;
            $user_id = $request->user()->id;
            $role = request()->role;
            $head = $academic->getHeadType->role_type;
            if (checkUserRole($ac_non_ac_id, $user_id, $role)) {
                if ($role == $head || $role == "Approver") {                   

                    if (!empty($request->orders)) {
                        foreach ($request->orders as $key => $order) {

                            Tender::where('id', $order['id'])->update(['order_on' => $key + 1]);
                        }
                        return $this->sendResponse('Update Successfully.', 'DATA FOUND', 200);
                    }

                    $logtype = LogType::select('id')->where('type', '=', 'academic')->first();

                    if (is_null($logtype)) {

                        return $this->sendResponse($logtype, 'Log type data not found', 404);
                    } else {

                        $dta = array();
                        $dta['log_type_id'] = $logtype['id'];
                        $dta['user_id'] = $user_id;
                        $dta['ip'] = $request->ip();
                        $dta['action'] = 'Order list updated for academic tender';
                        $dta['ac_non_ac_id'] = $ac_non_ac_id;
                        $dta['related_link_id'] = 'tender';
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

    // Get Department's Job list
    public function getJobList(Request $request)
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
                $data = Job::where('ac_non_ac_id', $ac_non_ac_id)->orderBy('id', 'desc')->paginate(env('ITEM_PER_PAGE'));
                
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

    // Get Department Job  Single Data
    public function getSingleJob(Request $request)
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

                $data = Job::where('id', request()->id)->first();

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

    // Store Academic Job Data..
    public function storeJobs(Request $request)
    {

        $input = $request->all();
        $validator = Validator::make($request->all(), [
            'slug' => 'required',
            'role' => 'required',
            'type' => 'required',
            'title' => 'required|max:255',
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

                $job = Job::where('id', request()->id)->first();
                if ($request->hasFile('file')) {
                    $file_data = array();
                    $file_data['file']     = $input['file'];
                    $file_data['path']     = 'file/'.$ac_non_ac_id.'/jobs';
                    $file_data['filename'] = $input['file']->getClientOriginalName();
                    $fileUp = FileUpload($file_data);
                    $results = json_decode($fileUp->content(), true);
                    if ($results['code'] == 1000) {
                        $input['file'] = $results['path'];
                        if (!empty($job)) {
                            if ($job->file != '') {
                                DeleteOldPicture($job->file);
                            }
                        }
                    }
                }

                if (isset($request->id) != null) {
                    $data['title']   = $request->title;
                    $data['order_on']      = $request->order_on;
                    if ($request->hasFile('file')) {                        
                        $data['file'] =  $input['file'];
                    }
                    $data['approval_status']    = $request->approval_status;

                    $data = Job::where('id', request()->id)->update($data);
                    if ($data) {

                        $logtype = LogType::select('id')->where('type', '=', 'academic')->first();
                        if (is_null($logtype)) {
                            return $this->sendResponse($logtype, 'Log type data not found', 404);
                        } else {
                            $dta = array();
                            $dta['log_type_id'] = $logtype['id'];
                            $dta['user_id'] = $user_id;
                            $dta['ip'] = $request->ip();
                            $dta['action'] = $request->approval_status; 
                            $dta['ac_non_ac_id'] = $ac_non_ac_id;
                            $dta['related_link_id'] = 'vacancies';
                            UserLogSave($dta);
                        }
                        return $this->sendResponse($data, trans('en_lang.DATA_UPDATED'), 200);
                    } else {
                        return $this->sendResponse($data, 'Some thing went wrong', 204, false);
                    }
                } else {

                    $data['title']   = $request->title;
                    $data['ac_non_ac_id']  = $ac_non_ac_id;
                    $data['order_on']      = $request->order_on;
                    if (isset($input['file'])) {                        
                        $data['file'] =  $input['file'];
                    }
                    $data['approval_status']    = $request->approval_status;
                    $data = Job::create($data);

                    if ($data) {
                        $logtype = LogType::select('id')->where('type', '=', 'academic')->first();
                        if (is_null($logtype)) {
                            return $this->sendResponse($logtype, 'Log type data not found', 404);
                        } else {
                            $dta = array();
                            $dta['log_type_id'] = $logtype['id'];
                            $dta['user_id'] = $user_id;
                            $dta['ip'] = $request->ip();
                            $dta['action'] = $request->approval_status;
                            $dta['ac_non_ac_id'] = $ac_non_ac_id;
                            $dta['related_link_id'] = 'vacancies';
                            UserLogSave($dta);
                        }
                        return $this->sendResponse($data, trans('en_lang.DATA_CREATED'), 200);
                    } else {
                        return $this->sendResponse($data, 'Some thing went wrong', 204, false);
                    }
                }
            } else {
                return $this->sendResponse($data, trans('en_lang.ACCESS_DENIED'), 401, false);
            }
        }
    }

    // Delete  Department's Job
    public function deleteJob(Request $request)
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
                    if (Job::where('id', request()->id)->delete()) {

                        $logtype = LogType::select('id')->where('type', '=', 'academic')->first();

                        if (is_null($logtype)) {

                            return $this->sendResponse($logtype, 'Log type data not found', 404);
                        } else {

                            $dta = array();
                            $dta['log_type_id'] = $logtype['id'];
                            $dta['user_id'] = $user_id;
                            $dta['ip'] = $request->ip();
                            $dta['action'] = 'Job Deleted';
                            $dta['ac_non_ac_id'] = $ac_non_ac_id;
                            $dta['related_link_id'] = 'vacancies';
                            UserLogSave($dta);
                        }

                        return $this->sendResponse('true', 'JOb Deleted', 200);
                    } else {
                        return $this->sendResponse('false', 'Something went Wrong!', 404, false);
                    }
                }

            } else {
                return $this->sendResponse($data, trans('en_lang.ACCESS_DENIED'), 401, false);
            }
        }
    }

    // Update order number for academic tender
    public function updateJobOrder(Request $request)
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
            $academic =  AcademicAndNonAcademic::where('slug', request()->slug)->with('getHeadType')->first();
            $ac_non_ac_id = $academic->id;
            $user_id = $request->user()->id;
            $role = request()->role;
            $head = $academic->getHeadType->role_type;
            if (checkUserRole($ac_non_ac_id, $user_id, $role)) {
                if ($role == $head || $role == "Approver") {                   

                    if (!empty($request->orders)) {
                        foreach ($request->orders as $key => $order) {

                            Job::where('id', $order['id'])->update(['order_on' => $key + 1]);
                        }
                        return $this->sendResponse('Update Successfully.', 'DATA FOUND', 200);
                    }

                    $logtype = LogType::select('id')->where('type', '=', 'academic')->first();

                    if (is_null($logtype)) {

                        return $this->sendResponse($logtype, 'Log type data not found', 404);
                    } else {

                        $dta = array();
                        $dta['log_type_id'] = $logtype['id'];
                        $dta['user_id'] = $user_id;
                        $dta['ip'] = $request->ip();
                        $dta['action'] = 'Order list updated for academic job';
                        $dta['ac_non_ac_id'] = $ac_non_ac_id;
                        $dta['related_link_id'] = 'job';
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

    // Get Department's Research Scholars list
    public function getResearchScholarsList(Request $request)
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
                $data = ResearchScholars::where('ac_non_ac_id', $ac_non_ac_id)->orderBy('order_on', 'asc')->paginate(env('ITEM_PER_PAGE'));
                
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

    // Store Academic Research Scholars.
    public function storeResearchScholars(Request $request)
    {

        $input = $request->all();
        $validator = Validator::make($request->all(), [
            'slug' => 'required',
            'role' => 'required',
            'type' => 'required',
            'enrolno' => 'required',
            'name' => 'required',
            'topic' => 'required',
        ]);
        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors(), 422, false);
        } else {
            $data = array();
            $academic =  AcademicAndNonAcademic::where('slug', request()->slug)->with('getdepFaculty.facultyName')->first();

            $ac_non_ac_id = $academic->id;
            $user_id      =  $request->user()->id;
            $role         = request()->role;
            if (checkUserRole($ac_non_ac_id, $user_id, $role)) {

               
                $scholar = ResearchScholars::where('id', request()->id)->first();
                if ($request->hasFile('image')) {
                    $img_data = array();
                    $results = array();
                    $img_data['image'] = $request->file('image');
                    $img_data['path'] = 'images/'.$ac_non_ac_id.'/re-scholars';
                    $img_data['filename'] = $request->file('image')->getClientOriginalName();
                    
                    $fileUp = ImageUpload($img_data);
                    $results = json_decode($fileUp->content(), true);
                    if ($results['code'] == 1000) {
                        $input['image'] = $results['path'];
                        if (!empty($scholar)) {
                            if ($scholar->image != '') {
                                DeleteOldPicture($scholar->image);
                            }
                        }
                    }
                }

                if (isset($request->id) != null) {

                    
                    $data['enrolno']       = $request->enrolno;
                    $data['name']          = $request->name;
                    $data['regno']         = $request->regno;
                    $data['supervisor']    = $request->supervisor;
                    $data['topic']         = $request->topic;
                    if ($request->availingfellowship == true) {
                        $data['availingfellowship']     = 'Yes';
                    }else{
                        $data['availingfellowship']     = 'NO';
                    }

                    if ($request->hasFile('image')) {
                        $data['image']     = $input['image'];
                    }
                    
                    $data['fundingagency']          = $request->fundingagency;
                    $data['mode']         = $request->mode;
                    $data['status']       = $request->status;
                    $data['Remark']       = $request->Remark;
                    $data['idtype']       = $request->idtype;
                    $data['idno']         = $request->idno;
                    $data['order_on']     = $request->order_on;
                    if($request->regdate != null || $request->regdate != ''){
                        $data['regdate']      = date("Y-m-d", strtotime($input['regdate']));
                    }else{
                        $data['regdate']      = Null;
                    }
                    if($request->completiondate != null || $request->completiondate != ''){
                        $data['completiondate']     = date("Y-m-d", strtotime($input['completiondate']));
                    }else{
                        $data['completiondate']      = Null;
                    }                   
                    
                    $data['approval_status']    = $request->approval_status;

                    $data = ResearchScholars::where('id', request()->id)->update($data);
                    if ($data) {

                        $logtype = LogType::select('id')->where('type', '=', 'academic')->first();
                        if (is_null($logtype)) {
                            return $this->sendResponse($logtype, 'Log type data not found', 404);
                        } else {
                            $dta = array();
                            $dta['log_type_id'] = $logtype['id'];
                            $dta['user_id'] = $user_id;
                            $dta['ip'] = $request->ip();
                            $dta['action'] = $request->approval_status; 
                            $dta['ac_non_ac_id'] = $ac_non_ac_id;
                            $dta['related_link_id'] = 'research-scholars';
                            UserLogSave($dta);
                        }
                        return $this->sendResponse($data, trans('en_lang.DATA_UPDATED'), 200);
                    } else {
                        return $this->sendResponse($data, 'Some thing went wrong', 204, false);
                    }
                } else {
                    if ($academic->getdepFaculty) {
                        $faculty = $academic->getdepFaculty->facultyName->title_en;
                    }
                    
                    $reScholars = ResearchScholars::where('enrolno', $request->enrolno)->first();

                    if (!empty($reScholars)) {
                       return $this->sendResponse($reScholars, 'User Enrolment Id Already Exist!',404);
                    }else{

                        $data['enrolno']       = $request->enrolno;
                        $data['ac_non_ac_id']  = $ac_non_ac_id;
                        $data['name']          = $request->name;
                        $data['dept']          = $academic->title_en;
                        if ($academic->getdepFaculty) {
                            $data['faculty']   = $faculty;
                        }
                        
                        $data['regno']         = $request->regno;
                        $data['supervisor']    = $request->supervisor;
                        $data['topic']         = $request->topic;
                        if ($request->availingfellowship == true) {
                            $data['availingfellowship']     = 'Yes';
                        }else{
                            $data['availingfellowship']     = 'NO';
                        }
                        if ($request->hasFile('image')) {
                            $data['image']         = $input['image'];
                        }
                        $data['fundingagency']          = $request->fundingagency;
                        $data['mode']         = $request->mode;
                        $data['status']       = $request->status;
                        $data['Remark']       = $request->Remark;
                        $data['idtype']       = $request->idtype;
                        $data['idno']         = $request->idno;
                        $data['order_on']     = $request->order_on;
                        if($request->regdate != null || $request->regdate != ''){
                            $data['regdate']      = date("Y-m-d", strtotime($input['regdate']));
                        }else{
                            $data['regdate']      = Null;
                        }
                        if($request->completiondate != null || $request->completiondate != ''){
                            $data['completiondate']     = date("Y-m-d", strtotime($input['completiondate']));
                        }else{
                            $data['completiondate']      = Null;
                        }
                        $data['approval_status']    = $request->approval_status;
                        $data = ResearchScholars::create($data);

                        if ($data) {
                            $logtype = LogType::select('id')->where('type', '=', 'academic')->first();
                            if (is_null($logtype)) {
                                return $this->sendResponse($logtype, 'Log type data not found', 404);
                            } else {
                                $dta = array();
                                $dta['log_type_id'] = $logtype['id'];
                                $dta['user_id'] = $user_id;
                                $dta['ip'] = $request->ip();
                                $dta['action'] = $request->approval_status;
                                $dta['ac_non_ac_id'] = $ac_non_ac_id;
                                $dta['related_link_id'] = 'research-scholars';
                                UserLogSave($dta);
                            }
                            return $this->sendResponse($data, trans('en_lang.DATA_CREATED'), 200);
                        } else {
                            return $this->sendResponse($data, 'Some thing went wrong', 204, false);
                        }                        
                    }
                }
            } else {
                return $this->sendResponse($data, trans('en_lang.ACCESS_DENIED'), 401, false);
            }
        }
    }

    // Get Department Research Scholor Single Data
    public function getSingleResearchScholar(Request $request)
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

                $data = ResearchScholars::where('id', request()->id)->first();
                
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

     // Delete  Department's Research Scholor
    public function deleteResearchScholar(Request $request)
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

                    if (ResearchScholars::where('id', request()->id)->delete()) {

                        $logtype = LogType::select('id')->where('type', '=', 'academic')->first();

                        if (is_null($logtype)) {

                            return $this->sendResponse($logtype, 'Log type data not found', 404);
                        } else {

                            $dta = array();
                            $dta['log_type_id'] = $logtype['id'];
                            $dta['user_id'] = $user_id;
                            $dta['ip'] = $request->ip();
                            $dta['action'] = 'Research Scholor Deleted';
                            $dta['ac_non_ac_id'] = $ac_non_ac_id;
                            $dta['related_link_id'] = 'research-scholars';
                            UserLogSave($dta);
                        }

                        return $this->sendResponse('true', 'Research Scholor Deleted', 200);
                    } else {
                        return $this->sendResponse('false', 'Something went Wrong!', 404, false);
                    }
                }

            } else {
                return $this->sendResponse($data, trans('en_lang.ACCESS_DENIED'), 401, false);
            }
        }
    }

     // Update order number for academic research scholars
    public function updateResearchScholarsOrder(Request $request)
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
            $academic =  AcademicAndNonAcademic::where('slug', request()->slug)->with('getHeadType')->first();
            $ac_non_ac_id = $academic->id;
            $user_id = $request->user()->id;
            $role = request()->role;
            $head = $academic->getHeadType->role_type;
            if (checkUserRole($ac_non_ac_id, $user_id, $role)) {
                if ($role == $head || $role == "Approver") {                   

                    if (!empty($request->orders)) {
                        foreach ($request->orders as $key => $order) {

                            ResearchScholars::where('id', $order['id'])->update(['order_on' => $key + 1]);
                        }
                        return $this->sendResponse('Update Successfully.', 'DATA FOUND', 200);
                    }

                    $logtype = LogType::select('id')->where('type', '=', 'academic')->first();

                    if (is_null($logtype)) {

                        return $this->sendResponse($logtype, 'Log type data not found', 404);
                    } else {

                        $dta = array();
                        $dta['log_type_id'] = $logtype['id'];
                        $dta['user_id'] = $user_id;
                        $dta['ip'] = $request->ip();
                        $dta['action'] = 'Order list updated for academic research scholars';
                        $dta['ac_non_ac_id'] = $ac_non_ac_id;
                        $dta['related_link_id'] = 'research-scholars';
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
    

    // Get Department's Ticker list
    public function getTickerList(Request $request)
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
                $data = Notification::where('ac_non_ac_id', $ac_non_ac_id)->orderBy('order_on', 'ASC')->orderBy('id', 'desc')->paginate(env('ITEM_PER_PAGE'));
                
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

    // Store Academic Department Ticker.
    public function storeDepartmentTickers(Request $request)
    {

        $input = $request->all();
        $validator = Validator::make($request->all(), [
            'slug' => 'required',
            'role' => 'required',
            'type' => 'required',
            'title_en' => 'required|max:255',
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
                $notfication = Notification::where('id', request()->id)->first();
                if ($request->hasFile('file')) {
                    $file_data = array();
                    $file_data['file']     = $input['file'];
                    $file_data['path']     = 'file/'.$ac_non_ac_id.'/notification';
                    $file_data['filename'] = $input['file']->getClientOriginalName();
                    $fileUp = FileUpload($file_data);
                    $results = json_decode($fileUp->content(), true);
                    if ($results['code'] == 1000) {
                        $input['file'] = $results['path'];
                        if (!empty($notfication)) {
                            if ($notfication->file != '') {
                                DeleteOldPicture($notfication->file);
                            }
                        }
                    }
                }

                if (isset($request->id) != null) {
                    $data['title_en']   = $request->title_en;
                    if ($request->title_hi == 'null' || $request->title_hi == Null) {
                        $data['title_hi'] = Null;
                    }else{
                        $data['title_hi'] = $request->title_hi;
                    }
                    if ($request->title_ur == 'null' || $request->title_ur == Null) {
                        $data['title_ur'] = Null;
                    }else{
                        $data['title_ur'] = $request->title_ur;
                    }
                    $data['featured']       = $request->featured;
                    $data['status']         = $request->status;
                    $data['hyperlink']      = $request->hyperlink;
                    $data['approval_status']= $request->approval_status;
                    $data['to_date']       = date("Y-m-d", strtotime($input['to_date']));
                    if ($request->hasFile('file')) {
                        $data['file'] =  $input['file'];
                    }
                    $data = Notification::where('id', request()->id)->update($data);
                    if ($data) {

                        $logtype = LogType::select('id')->where('type', '=', 'academic')->first();
                        if (is_null($logtype)) {
                            return $this->sendResponse($logtype, 'Log type data not found', 404);
                        } else {
                            $dta = array();
                            $dta['log_type_id'] = $logtype['id'];
                            $dta['user_id'] = $user_id;
                            $dta['ip'] = $request->ip();
                            $dta['action'] = $request->approval_status; 
                            $dta['ac_non_ac_id'] = $ac_non_ac_id;
                            $dta['related_link_id'] = 'ticker';
                            UserLogSave($dta);
                        }
                        return $this->sendResponse($data, trans('en_lang.DATA_UPDATED'), 200);
                    } else {
                        return $this->sendResponse($data, 'Some thing went wrong', 204, false);
                    }
                } else {
                    
                    $data['ac_non_ac_id']   = $ac_non_ac_id;
                    $data['title_en']       = $request->title_en;
                    $data['title_hi']       = $request->title_hi;
                    $data['title_ur']       = $request->title_ur;
                    $data['featured']       = $request->featured;
                    $data['hyperlink']      = $request->hyperlink;
                    $data['status']         = $request->status;
                    $data['approval_status']= $request->approval_status;
                    $data['to_date']       = date("Y-m-d", strtotime($input['to_date']));
                    if ($request->hasFile('file')) {
                        $data['file'] =  $input['file'];
                    }
                    $data = Notification::create($data);

                    if ($data) {
                        $logtype = LogType::select('id')->where('type', '=', 'academic')->first();
                        if (is_null($logtype)) {
                            return $this->sendResponse($logtype, 'Log type data not found', 404);
                        } else {
                            $dta = array();
                            $dta['log_type_id'] = $logtype['id'];
                            $dta['user_id'] = $user_id;
                            $dta['ip'] = $request->ip();
                            $dta['action'] = $request->approval_status;
                            $dta['ac_non_ac_id'] = $ac_non_ac_id;
                            $dta['related_link_id'] = 'ticker';
                            UserLogSave($dta);
                        }
                        return $this->sendResponse($data, trans('en_lang.DATA_CREATED'), 200);
                    } else {
                        return $this->sendResponse($data, 'Some thing went wrong', 204, false);
                    }
                }
            } else {
                return $this->sendResponse($data, trans('en_lang.ACCESS_DENIED'), 401, false);
            }
        }
    }

    // Get Department Ticker Single Data
    public function getSingleTicker(Request $request)
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

                $data = Notification::where('id', request()->id)->first();
                
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

    // Delete  Department's Ticker
    public function deleteTicker(Request $request)
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

                    if (Notification::where('id', request()->id)->delete()) {

                        $logtype = LogType::select('id')->where('type', '=', 'academic')->first();

                        if (is_null($logtype)) {

                            return $this->sendResponse($logtype, 'Log type data not found', 404);
                        } else {

                            $dta = array();
                            $dta['log_type_id'] = $logtype['id'];
                            $dta['user_id'] = $user_id;
                            $dta['ip'] = $request->ip();
                            $dta['action'] = 'Ticker Deleted';
                            $dta['ac_non_ac_id'] = $ac_non_ac_id;
                            $dta['related_link_id'] = 'ticker';
                            UserLogSave($dta);
                        }

                        return $this->sendResponse('true', 'Ticker Deleted', 200);
                    } else {
                        return $this->sendResponse('false', 'Something went Wrong!', 404, false);
                    }
                }

            } else {
                return $this->sendResponse($data, trans('en_lang.ACCESS_DENIED'), 401, false);
            }
        }
    }

    // Update order number for academic ticker
    public function updateTickerOrder(Request $request)
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

                            Notification::where('id', $order['id'])->update(['order_on' => $key + 1]);
                        }
                        return $this->sendResponse('Update Successfully.', 'DATA FOUND', 200);
                    }

                    $logtype = LogType::select('id')->where('type', '=', 'academic')->first();

                    if (is_null($logtype)) {

                        return $this->sendResponse($logtype, 'Log type data not found', 404);
                    } else {

                        $dta = array();
                        $dta['log_type_id'] = $logtype['id'];
                        $dta['user_id'] = $user_id;
                        $dta['ip'] = $request->ip();
                        $dta['action'] = 'Order list updated for academic Ticker';
                        $dta['ac_non_ac_id'] = $ac_non_ac_id;
                        $dta['related_link_id'] = 'ticker';
                        UserLogSave($dta);
                    }
               
            } else {
                return $this->sendResponse('false', trans('en_lang.ACCESS_DENIED'), 401);
            }
        }
    }

    // Delete Related link.
    public function deleteRelatedLink(Request $request)
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

                    $logtype = LogType::select('id')->where('type', '=', 'academic')->first();

                    if (is_null($logtype)) {

                        return $this->sendResponse($logtype, 'Log type data not found', 404);
                    } else {

                        $dta = array();
                        $dta['log_type_id'] = $logtype['id'];
                        $dta['user_id'] = $user_id;
                        $dta['ip'] = $request->ip();
                        $dta['action'] = 'Academic related link data deleted';
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

    // Store Department's  related links
    public function storeRelatedLink(Request $request)
    {

        $input = $request->all();
        $user = Auth::user();
        $validator = Validator::make($request->all(), [
            'slug' => 'required',
            'role' => 'required',
            'type' => 'required',
            'link_name_en' => 'required',
            'group_type_id' => 'required',
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
                    $slug = request()->type.'/'.request()->slug.'/'.$link_slug;

                    $data = array();
                    $data['ac_non_ac_id']   =   $ac_non_ac_id;
                    $data['group_type_id']  =   $request->group_type_id;
                    $data['link_name_en']   =   $request->link_name_en;
                    $data['link_name_hi']   =   $request->link_name_hi;
                    $data['link_name_ur']   =   $request->link_name_ur;
                    $data['slug']           =   $slug;
                    $data['link_order']     =   $request->link_order;
                    RelatedLink::create($data);
                    if ($data) {
                        $logtype = LogType::select('id')->where('type', '=', 'academic')->first();
                        if (is_null($logtype)) {
                            return $this->sendResponse($logtype, 'Log type data not found', 404);
                        } else {
                            $dta = array();
                            $dta['log_type_id'] = $logtype['id'];
                            $dta['user_id'] = $user_id;
                            $dta['ip'] = $request->ip();
                            $dta['action'] = 'Academic related link created';
                            $dta['ac_non_ac_id'] = $ac_non_ac_id;
                            $dta['related_link_id'] = 'related_link';
                            UserLogSave($dta);
                        }
                        return $this->sendResponse($data, trans('en_lang.DATA_CREATED'), 201);
                    } else {
                        return $this->sendResponse($data, 'Some thing went wrong', 204, false);
                    }
            } else {
                return $this->sendResponse($data, trans('en_lang.ACCESS_DENIED'), 401, false);
            }
        }
    }

    // Update order number for academic related link
    public function updateRelatedLinkOrder(Request $request)
    {
        $input = $request->all();
        
        $validator = Validator::make($request->all(), [
            'slug' => 'required',
            'role' => 'required',
            'type' => 'required',
            'orders' => 'required',
            'group_type_id' => 'required'
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
                            
                            RelatedLink::where('group_type_id', request()->group_type_id)->where('id', $order['id'])->update(['link_order' => $key + 1]);
                        }
                        return $this->sendResponse('Update Successfully.', 'DATA FOUND', 200);
                    }

                    $logtype = LogType::select('id')->where('type', '=', 'academic')->first();

                    if (is_null($logtype)) {

                        return $this->sendResponse($logtype, 'Log type data not found', 404);
                    } else {

                        $dta = array();
                        $dta['log_type_id'] = $logtype['id'];
                        $dta['user_id'] = $user_id;
                        $dta['ip'] = $request->ip();
                        $dta['action'] = 'Order list updated for academic related link';
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

    // Get Department's File Management
    public function getFileManagement(Request $request)
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
                $data = FileManagement::where('ac_non_ac_id', $ac_non_ac_id)->orderBy('id', 'desc')->paginate(env('ITEM_PER_PAGE'));
                
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

    // Get Department Single File Management
    public function getSingleFileManagement(Request $request)
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

                $data = FileManagement::where('id', request()->id)->first();
                
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

    // Store Department's File Management
    public function storeFileManagement(Request $request)
    {

        $input = $request->all();
        $validator = Validator::make($request->all(), [
            'slug' => 'required',
            'role' => 'required',
            'type' => 'required',
            'title' => 'required'
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
               
                $file = FileManagement::where('id', request()->id)->first();
                if ($request->hasFile('file')) {
                    $validator = Validator::make($request->all(), [
                       'file' => 'required|mimes:doc,docx,pdf'
                    ]);

                    if ($validator->fails()) {
                        return $this->sendError('Validation Error.', $validator->errors(), 422, false);
                    } 
                    $file_data = array();
                    $file_data['file']     = $input['file'];
                    $file_data['path']     = 'file/'. $ac_non_ac_id.'/file_management' ;
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

                if (isset($request->id) != null) {
                    $data['title'] = $input['title'];
                    if ($request->hasFile('file')) {
                        $data['file'] = $input['file'];
                    }
                    $data['approval_status'] = $input['approval_status'];
                    $data = FileManagement::where('id', request()->id)->update($data);
                    if ($data) {

                        $logtype = LogType::select('id')->where('type', '=', 'academic')->first();
                        if (is_null($logtype)) {
                            return $this->sendResponse($logtype, 'Log type data not found', 404);
                        } else {
                            $dta = array();
                            $dta['log_type_id'] = $logtype['id'];
                            $dta['user_id'] = $user_id;
                            $dta['ip'] = $request->ip();
                            $dta['action'] = $request->approval_status; 
                            $dta['ac_non_ac_id'] = $ac_non_ac_id;
                            $dta['related_link_id'] = 'file-uploader';
                            UserLogSave($dta);
                        }
                        return $this->sendResponse($data, trans('en_lang.DATA_UPDATED'), 200);
                    } else {
                        return $this->sendResponse($data, 'Some thing went wrong', 204, false);
                    }
                } else {

                    $data['title'] = $input['title'];
                    $data['ac_non_ac_id'] = $ac_non_ac_id;
                    $data['file'] = $input['file'];
                    $data['approval_status'] = $input['approval_status'];
                    $data = FileManagement::create($data);

                    if ($data) {
                        $logtype = LogType::select('id')->where('type', '=', 'academic')->first();
                        if (is_null($logtype)) {
                            return $this->sendResponse($logtype, 'Log type data not found', 404);
                        } else {
                            $dta = array();
                            $dta['log_type_id'] = $logtype['id'];
                            $dta['user_id'] = $user_id;
                            $dta['ip'] = $request->ip();
                            $dta['action'] = $request->approval_status;
                            $dta['ac_non_ac_id'] = $ac_non_ac_id;
                            $dta['related_link_id'] = 'file-uploader';
                            UserLogSave($dta);
                        }
                        return $this->sendResponse($data, trans('en_lang.DATA_CREATED'), 200);
                    } else {
                        return $this->sendResponse($data, 'Some thing went wrong', 204, false);
                    }
                }
            } else {
                return $this->sendResponse($data, trans('en_lang.ACCESS_DENIED'), 401, false);
            }
        }
    }

    // Delete Department's File Management
    public function deleteFileManagement(Request $request)
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

                    if (FileManagement::where('id', request()->id)->delete()) {

                        $logtype = LogType::select('id')->where('type', '=', 'academic')->first();

                        if (is_null($logtype)) {

                            return $this->sendResponse($logtype, 'Log type data not found', 404);
                        } else {

                            $dta = array();
                            $dta['log_type_id'] = $logtype['id'];
                            $dta['user_id'] = $user_id;
                            $dta['ip'] = $request->ip();
                            $dta['action'] = 'Deleted ' . strtolower($academic->title_en) . ' File Management';
                            $dta['ac_non_ac_id'] = $ac_non_ac_id;
                            $dta['related_link_id'] = 'file-uploader';
                            UserLogSave($dta);
                        }

                        return $this->sendResponse('true', 'File Deleted', 200);
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

    // Get Teaching and Non Teaching List
    public function getTeachAndNonTeachList(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'type_id' => 'required',
            'type' => 'required',
            'role' => 'required',
            'slug' => 'required',
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

                $visibility = UserVisibility::select('user_id')->where('user_id','!=',$request->user()->id)->where('ac_non_ac_id',$ac_non_ac_id)->where('for_id',request()->type_id)->where('core','1')->where('status','1')->groupBy('user_id')->get()->toArray();
                $user_id = array_column($visibility, 'user_id');
                $data = User::select('id','title','first_name','middle_name','last_name','for_id')->whereIn('id', $user_id)->orderBy('first_name', 'ASC')->get();
                

                if (count($data) > 0){   
                    return $this->sendResponse($data, trans('en_lang.DATA_FOUND'),200); 
                }else{
                    return $this->sendResponse($data, trans('en_lang.DATA_NOT_FOUND'),404);
                }
                

            } else {
                return $this->sendResponse("false", trans('en_lang.ACCESS_DENIED'), 401);
            }
        }
    }

    // Store Transfer Staff
    public function storeTransferStaff(Request $request)
    {

        $input = $request->all();
        $validator = Validator::make($request->all(), [
            'slug' => 'required',
            'role' => 'required',
            'type' => 'required',
            'user_id' => 'required',
            'trans_to' => 'required'
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
               
               $transrq = TransferStaff::where('user_id', request()->user_id)->where('trans_to', request()->trans_to)->first();

               $visibility = UserVisibility::where('user_id', request()->user_id)->where('ac_non_ac_id', request()->trans_to)->first();

                if (!empty($transrq)) {
                   return $this->sendResponse($transrq, 'User already added',404);
                }elseif (!empty($visibility)) {
                   return $this->sendResponse($visibility, 'User already added',404);
                }else{
                    $data = array();
                    $data['user_id'] = request()->user_id;
                    $data['for_id'] = request()->type_id;
                    $data['trans_from'] = $ac_non_ac_id;
                    $data['trans_to'] = request()->trans_to;
                    $data['trans_status'] = 'pending';
                    $data['approval_status'] = request()->approval_status;
                    $data = TransferStaff::create($data);

                    if ($data) {
                        $logtype = LogType::select('id')->where('type', '=', 'academic')->first();
                        if (is_null($logtype)) {
                            return $this->sendResponse($logtype, 'Log type data not found', 204);
                        } else {
                            $dta = array();
                            $dta['log_type_id'] = $logtype['id'];
                            $dta['user_id'] = $user_id;
                            $dta['ip'] = $request->ip();
                            $dta['action'] = $request->approval_status;
                            $dta['ac_non_ac_id'] = $ac_non_ac_id;
                            $dta['related_link_id'] = 'staff-transfer';
                            UserLogSave($dta);
                        }
                        return $this->sendResponse($data, trans('en_lang.DATA_CREATED'), 200);
                    } else {
                        return $this->sendResponse($data, 'Some thing went wrong', 204, false);
                    }
                }   
            } else {
                return $this->sendResponse($data, trans('en_lang.ACCESS_DENIED'), 401, false);
            }
        }
    }

    // Get Teaching and Non Teaching Transfer Request List
    public function getTransferRequest(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'type' => 'required',
            'role' => 'required',
            'slug' => 'required',
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
                
                $data = TransferStaff::where('trans_to',$ac_non_ac_id)->with('getUser','getTransferFrom')->orderBy('id','desc')->paginate(env('ITEM_PER_PAGE'));

                if (count($data) > 0){   
                    return $this->sendResponse($data, trans('en_lang.DATA_FOUND'),200); 
                }else{
                    return $this->sendResponse($data, trans('en_lang.DATA_NOT_FOUND'),404);
                }
                

            } else {
                return $this->sendResponse("false", trans('en_lang.ACCESS_DENIED'), 401);
            }
        }
    }

    // Get Teaching and Non Teaching Transfer Request List Count
    public function getTransReqCount(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'type' => 'required',
            'role' => 'required',
            'slug' => 'required',
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
                
                $data = TransferStaff::where('trans_to',$ac_non_ac_id)->where('trans_status','pending')->count();               

                return $this->sendResponse($data, trans('en_lang.DATA_FOUND'),200);
                

            } else {
                return $this->sendResponse("false", trans('en_lang.ACCESS_DENIED'), 401);
            }
        }
    }

    // Store Transfer Staff Request Accept or Reject
    public function storeTransferRequestAction(Request $request)
    {

        $input = $request->all();
        $validator = Validator::make($request->all(), [
            'slug' => 'required',
            'role' => 'required',
            'type' => 'required',
            'user_id' => 'required',
            'trans_from' => 'required',
            'flag' => 'required'

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

                if (request()->flag == '0') {
                    $transrq = TransferStaff::where('user_id', request()->user_id)->where('trans_to', $ac_non_ac_id)->update(['trans_status' => 'rejected']);
                   return $this->sendResponse($transrq, 'User request rejected',200);
                }elseif(request()->flag == '1'){
                    DB::beginTransaction();                    
                    try{
                        $transrq = TransferStaff::where('user_id', request()->user_id)->where('trans_to', $ac_non_ac_id)->update(['trans_status' => 'accepted']);


                        $visibility = UserVisibility::where('user_id', $input['user_id'])->where('ac_non_ac_id', $input['trans_from'])->where('core','1')->first();

                        $user_fid = TransferStaff::where('user_id', request()->user_id)->where('trans_to', $ac_non_ac_id)->first();


                        $data['user_id']          = request()->user_id;
                        $data['type_id']          = $academic->type;
                        $data['sub_type_id']      = $academic->sub_type;
                        $data['ac_non_ac_id']     = $ac_non_ac_id;
                        $data['designation_id']   = $visibility->designation_id;
                        $data['role_id']          = $visibility->role_id;
                        $data['for_id']           = $user_fid->for_id;
                        $data['core']             = 1;
                        $data = UserVisibility::create($data);
                        $deletecore = UserVisibility::where('user_id', request()->user_id)->where('ac_non_ac_id', request()->trans_from)->where('core','1')->delete();
			            $user = User::where('id',request()->user_id)->first();
                        
                        $url  =  explode('/',$user->url);
                        $url[1]=$academic['slug'];
                        $newURL = implode("/",$url);
                        User::where('id', $input['user_id'])->update(['url' => $newURL]);
                        $logtype = LogType::select('id')->where('type', '=', 'academic')->first();
                        if (is_null($logtype)) {
                            return $this->sendResponse($logtype, 'Log type data not found', 404);
                        } else {
                            $dta = array();
                            $dta['log_type_id'] = $logtype['id'];
                            $dta['user_id'] = $user_id;
                            $dta['ip'] = $request->ip();
                            $dta['action'] = $request->approval_status;
                            $dta['ac_non_ac_id'] = $ac_non_ac_id;
                            $dta['related_link_id'] = 'staff-transfer';
                            UserLogSave($dta);
                        }

                        DB::commit();

                        return $this->sendResponse('User request accepted', trans('en_lang.DATA_CREATED'), 200);

                    }catch(\Exception $ex){
                        DB::rollback();
                        return $this->sendResponse('error', 'Something went wrong', 204);
                    }
                }   
            } else {
                return $this->sendResponse($data, trans('en_lang.ACCESS_DENIED'), 401, false);
            }
        }
    }
}
