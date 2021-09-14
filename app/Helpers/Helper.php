<?php

/**
* change plain number to formatted currency
*
* @param $number
* @param $currency
*/
use Illuminate\Support\Facades\Validator;
use App\AcademicAndNonAcademic;
use App\MappingDepartments;
use App\UserVisibility;
use App\RelatedLink;
use App\CustomRelatedLink;
use App\RelatedLinkData;
use App\GroupLink;
use App\UserLog;
use App\RoleType;
use Intervention\Image\ImageManagerStatic as Image;

// For image upload

function ImageUpload($image_str){ 
  if (!isset($image_str['path'])){
    return response()->json([ 
      'message' => 'please give file name to store image!',
      'code' => 2000
    ]);
  }elseif (!isset($image_str['image'])){
    return response()->json([ 
      'message' => 'please upload image!',
      'code' => 2000
    ]);
  }elseif (!isset($image_str['filename'])){
    return response()->json([ 
      'message' => 'please give image name!',
      'code' => 2000
    ]);
  }
  if (isset($image_str['image'])) {
    $uploaded_thumb_rules = array(
    'image' => 'required|mimes:png,jpg,jpeg'
    );
    $validator = Validator::make($image_str, $uploaded_thumb_rules);
    if ($validator->passes()) {
      $image          =  $image_str['image'];
      $uploaded_image = time().'_'.$image_str['filename'];
      $path           = '/public/'.$image_str['path'].'/'.$uploaded_image; 
      $image_thumb    =  Image::make($image)->resize(134,86);          
      $image_normal   =  Image::make($image);      
      $image_normal   = $image_normal->stream()->detach();
      $image_thumb    = $image_thumb->stream()->detach();
      
      if (request()->segment(1) == 'gallery-images') {
        $list_thumb     =  Image::make($image)->resize(275, null, function ($constraint) {
            $constraint->aspectRatio();
        });
        $list_thumb     = $list_thumb->stream()->detach();
        Storage::disk('local')->put('/public/'.$image_str['path'].'/thumb_275/'.$uploaded_image, $list_thumb);
      } 
      Storage::disk('local')->put('/public/'.$image_str['path'].'/thumb/'.$uploaded_image, $image_thumb);  
           
      $imageUploadSuccess = Storage::disk('local')->put( $path , $image_normal);
      
      if ($imageUploadSuccess == true) {
        $path = substr($path, 7);
        return response()->json([ 
          'message' => 'sucessfully image upload!',
          'code' => 1000,
          'path'=>$path
        ]);
      }else {
        return response()->json([ 
          'message' => 'please upload only image!',
          'code' => 2000
        ]);
      }
    } else {
        return response()->json([ 
        'message' => 'please upload image and path!',
        'code' => 2000
      ]);
    }
  }
}

// For user Log 
function UserLogSave($data){
  if(isset($data)){
    UserLog::create($data);
  }
}

function DeleteOldPicture($path){
  if(!Storage::disk('public')->exists($path))
  {
    return 0;
  }else{    
    unlink(storage_path('app/public/'.$path));
    return 1;
  }
}

function FileUpload($file_str){
//\Log::info("response: " . $file_str); 
  if (!isset($file_str['path'])){
    return response()->json([ 
      'message' => 'please give file name to store image!',
      'code' => 2000
    ]);
  }elseif (!isset($file_str['file'])){
    return response()->json([ 
      'message' => 'please upload file!',
      'code' => 2000
    ]);
  }elseif (!isset($file_str['filename'])){
    return response()->json([ 
      'message' => 'please give file name!',
      'code' => 2000
    ]);
  }

  if (isset($file_str['file'])){
    $uploaded_thumb_rules = array(
      'file' => 'required'
    );
    $validator = Validator::make($file_str, $uploaded_thumb_rules);
    if($validator->passes()){
      $file = $file_str['file'];
      $uploaded_file = time() . '.' . $file_str['file']->guessExtension(); 
      $path           = '/public/'.$file_str['path'].'/'.$uploaded_file;
      $imageUploadSuccess =  Storage::disk('local')->put($path, file_get_contents($file->getRealPath()));
      //\Log::info("response: " . $imageUploadSuccess);
      if ($imageUploadSuccess){
        $path = substr($path, 7);
        return response()->json([ 
          'message' => 'sucessfully file upload!',
          'code' => 1000,
          'path'=>$path
        ]);
      } else {
          return response()->json([ 
          'message' => 'please upload only file!',
          'code' => 2000
        ]);
      }
    } else {
      return response()->json([ 
        'message' => 'please upload file and path!',
        'code' => 2000
      ]);
    }
  } 
}

function string_cut($text, $length = 100,$end = ''){
  mb_strlen($text);
  if (mb_strlen($text) > $length){
    $text = mb_substr($text, 0, $length);
    return $text.$end;
  } else {
    return $text;
  }
}

function checkUserRole($ac_non_ac_id, $user_id, $role){
        $roleType = RoleType::where('role_type',$role)->where('full_access','=','1')->first();
        if (!empty($roleType)) {
           $user = UserVisibility::where('user_id','=',$user_id)->where('ac_non_ac_id','=',$ac_non_ac_id)->where('role_id','=',$roleType->id)->first();
        }else{
            $user = UserVisibility::where('user_id','=',$user_id)->where('ac_non_ac_id','=',$ac_non_ac_id)->where('special_role','=',$role)->first(); 
        }    
        if (is_null($user)){                        
            return false;
        }else{
            return true;
        }
}

function sectionHeadDetail($id, $head){ 
    $data = array();
    $userData = UserVisibility::select('user_id','for_id','ac_non_ac_id','designation_id','role_id','core')->where('ac_non_ac_id',$id)->where('role_id',$head)->where('core','1')->where('display','1')->with('getUser.getContact','getDesignation')->first();
    $key=0;
    if(!empty($userData)){
        $data[$key]['user_id']      = $userData->user_id;
        $data[$key]['url']          = $userData->getUser->url;
        $data[$key]['for_id']       = $userData->for_id;
        $data[$key]['slug']         = $userData->getUser->slug;
        $data[$key]['title']        = $userData->getUser->title;
        $data[$key]['first_name']   = $userData->getUser->first_name;
        $data[$key]['middle_name']  = $userData->getUser->middle_name;
        $data[$key]['last_name']    = $userData->getUser->last_name;
        
        if($userData->getUser->image != NULL || $userData->getUser->image != ''){
          $data[$key]['image'] = asset('storage/').'/'.$userData->getUser->image;
        }else{
          $data[$key]['image'] = asset('storage').'/images/default-img.png';
        }

        $data[$key]['designation'] = $userData->getDesignation->name;
        
        if ($userData->getUser->getContact) {
            foreach ($userData->getUser->getContact as $val) {
                if ($val->email_visibility == '1') {
                    $data[$key]['email'] = $val->email;
                }
                if ($val->mobile_visibility == '1') {
                    $data[$key]['mobile_no'] = $val->mobile_no;
                }
                $data[$key]['telephone_no'] = $val->telephone_no;
            }
        }else{
            $data[$key]['email'] = '';
            $data[$key]['mobile_no'] = '';
            $data[$key]['telephone_no'] = '';
        }

        return response()->json([ 
          'message' => 'Data Found',
          'code' => 1000,
          'head'=>$data,
        ]);

    }else {
          return response()->json([ 
          'message' => 'Data not Found',
          'code' => 404
        ]);
    }
}

function academicRelatedLink($id, $lang){ 
  if ($id) {
    $chairman = array();
    $userData = UserVisibility::where('ac_non_ac_id',$id)->where('role_id','2')->with('getHeadFaculty.getContact','getDesignation')->first();
    if (!empty($userData)) {
      $chairman['url'] = $userData->getHeadFaculty->url;
      $chairman['slug'] = $userData->getHeadFaculty->slug;
      $chairman['title'] = $userData->getHeadFaculty->title;
      $chairman['first_name'] = $userData->getHeadFaculty->first_name;
      $chairman['last_name'] = $userData->getHeadFaculty->last_name;
      $chairman['middle_name'] = $userData->getHeadFaculty->middle_name;
      if($userData->getHeadFaculty->image !=NULL){
         $userImage = asset('storage').$userData->getHeadFaculty->image;
      }else{
         $userImage = asset('storage').'/images/default-img.png';
      }
      $chairman['image'] = $userImage;
      
      $chairman['designation'] = $userData->getDesignation->name.' and Chairman';
    }else{
        $chairman['slug'] = '';
        $chairman['title'] = '';
        $chairman['first_name'] = '';
        $chairman['last_name'] = '';
        $chairman['middle_name'] = '';
        $chairman['image'] = asset('storage').'/images/default-img.png';
        $chairman['designation'] = '';
    }
    

    if ($lang == 'ur') {
      $grouplinkcount = RelatedLink::select('group_type_id')->groupBy('group_type_id')->where('ac_non_ac_id',$id)->get()->toArray();
      $grouplink = array_column($grouplinkcount, 'group_type_id');
      $links = GroupLink::select('title_ur as title','id')->whereIn('id', $grouplink)
              ->with(['getRelatedLink' => function($q)  use($id){ 
                $q->where('ac_non_ac_id', '=',$id);}])
              ->get();
    }elseif ($lang == 'hi') {
      $grouplinkcount = RelatedLink::select('group_type_id')->groupBy('group_type_id')->where('ac_non_ac_id',$id)->get()->toArray();
      $grouplink = array_column($grouplinkcount, 'group_type_id');
      $links = GroupLink::select('title_hi as title','id')->whereIn('id', $grouplink)
              ->with(['getRelatedLink' => function($q)  use($id){ 
                $q->where('ac_non_ac_id', '=',$id);}])
              ->get();
    }else{
      $grouplinkcount = RelatedLink::select('group_type_id')->groupBy('group_type_id')->where('ac_non_ac_id',$id)->get()->toArray();
      $grouplink = array_column($grouplinkcount, 'group_type_id');
      $links = GroupLink::select('title_en as title','id')->whereIn('id', $grouplink)
              ->with(['getRelatedLink' => function($q)  use($id){ 
                $q->where('ac_non_ac_id', '=',$id);
                $q->orderBy('link_order', 'ASC'); }])
              ->get();
    }

    return response()->json([ 
          'message' => 'Data Found',
          'code' => 1000,
          'head'=>$chairman,
          'links'=>$links
        ]);
  } else {
          return response()->json([ 
          'message' => 'Data not Found',
          'code' => 2000
        ]);
  }
}

function nonacRelatedLink($id, $headid, $lang,$type){ 
  if ($id) {
    $head = array();
    $userData = UserVisibility::where('ac_non_ac_id',$id)->where('role_id',$headid)->with('getHeadFaculty.getContact','getDesignation','getRole','getSectionContact')->first(); 

    if(!empty($userData)){
        $head['slug'] = $userData->getHeadFaculty->slug;
        $head['title'] = $userData->getHeadFaculty->title;
        $head['first_name'] = $userData->getHeadFaculty->first_name;
        $head['last_name'] = $userData->getHeadFaculty->last_name;
        $head['middle_name'] = $userData->getHeadFaculty->middle_name;
        $head['url'] = $userData->getHeadFaculty->url;
        if($userData->getHeadFaculty->image !=NULL){
           $head['image'] = asset('storage/').$userData->getHeadFaculty->image;
        }else{
           $head['image'] = asset('storage').'/images/default-img.png';
        }
        $head['designation'] = $userData->getDesignation->name; 
        if($userData->getSectionContact != NULL){
             $head['office_ext'] = $userData->getSectionContact->office_ext;
             $head['office']     = $userData->getSectionContact->office;
             $head['phone_ext']  = $userData->getSectionContact->phone_ext;
             $head['phone']      = $userData->getSectionContact->phone;
             $head['email']      = $userData->getSectionContact->email;
          } 
    }else{
        $head['slug'] = '';
        $head['title'] = '';
        $head['first_name'] = '';
        $head['last_name'] = '';
        $head['middle_name'] = '';
        $head['image'] = asset('storage').'/images/default-img.png';
        $head['designation'] = '';
    }

    if ($lang == 'ur') {
      $links = array(); 
      $relatedLinks = RelatedLink::orderBy('link_order','asc')->where('ac_non_ac_id',$id)->get();
      foreach ($relatedLinks as $key => $value) {
              $links[$key]['id'] = $value->id;
              if($value->link_name_ur == NULL){
                  $links[$key]['link'] = $value->link_name_en; 
              }else{ 
                  $links[$key]['link'] = $value->link_name_ur; 
              }
              $links[$key]['slug'] = $type.'/'.$value->slug;
      }

      //Get Custom link
      $customlinks = array(); 
      $cus_rel_Links = CustomRelatedLink::where('approval_status','Approved')->orderBy('order_on','asc')->where('ac_non_ac_id',$id)->get();
      if ($cus_rel_Links) {
          foreach ($cus_rel_Links as $key => $value) {
              $customlinks[$key]['id'] = $value->id;
              if($value->link_name_ur == NULL){
                  $customlinks[$key]['cus_link'] = $value->link_name_en; 
              }else{ 
                  $customlinks[$key]['cus_link'] = $value->link_name_ur; 
              }
              $customlinks[$key]['rel_slug'] = $value->rel_slug;
          }
      }
      //Get other section data
      $othermap =  MappingDepartments::where('ac_non_ac_id',$id)->orwhere('dep_id',$id)->first();
      $section = array(); 
      if ($othermap) {
          
          $mapdata = MappingDepartments::select('id','ac_non_ac_id','dep_id','type')->where('ac_non_ac_id',$othermap->ac_non_ac_id)->with('departments:id,title_en,slug')->orderBy('id','ASC')->get();
          if($mapdata->count()){
              foreach ($mapdata as $key => $value){
                  $section[$key]['id'] = $value->departments->id;
                  if($value->departments->title_ur == NULL){
                      $section[$key]['title'] = $value->departments->title_en; 
                  }else{ 
                      $section[$key]['title'] = $value->departments->title_ur; 
                  }
                  $section[$key]['slug'] = $value->departments->slug;
              }
          }
      }
    }elseif ($lang == 'hi') {
      $links = array(); 
      $relatedLinks = RelatedLink::orderBy('link_order','asc')->where('ac_non_ac_id',$id)->get();
      foreach ($relatedLinks as $key => $value) {
              $links[$key]['id'] = $value->id;
              if($value->link_name_hi == NULL){
                  $links[$key]['link'] = $value->link_name_en; 
              }else{ 
                  $links[$key]['link'] = $value->link_name_hi; 
              }
              $links[$key]['slug'] = $type.'/'.$value->slug;
      }

      //Get Custom link
      $customlinks = array(); 
      $cus_rel_Links = CustomRelatedLink::where('approval_status','Approved')->orderBy('order_on','asc')->where('ac_non_ac_id',$id)->get();
      if ($cus_rel_Links) {
          foreach ($cus_rel_Links as $key => $value) {
              $customlinks[$key]['id'] = $value->id;
              if($value->link_name_hi == NULL){
                  $customlinks[$key]['cus_link'] = $value->link_name_en; 
              }else{ 
                  $customlinks[$key]['cus_link'] = $value->link_name_hi; 
              }
              $customlinks[$key]['rel_slug'] = $value->rel_slug;
          }
      }

      //Get other section data
      $othermap =  MappingDepartments::where('ac_non_ac_id',$id)->orwhere('dep_id',$id)->first();
      $section = array(); 
      if ($othermap) {          
          $mapdata = MappingDepartments::select('id','ac_non_ac_id','dep_id','type')->where('ac_non_ac_id',$othermap->ac_non_ac_id)->with('departments:id,title_en,slug')->orderBy('id','ASC')->get();
          if($mapdata->count()){
              foreach ($mapdata as $key => $value){
                  $section[$key]['id'] = $value->departments->id;
                  if($value->departments->title_hi == NULL){
                      $section[$key]['title'] = $value->departments->title_en; 
                  }else{ 
                      $section[$key]['title'] = $value->departments->title_hi; 
                  }
                  $section[$key]['slug'] = $value->departments->slug;
              }
          }
      }
    }else{
      $links = array(); 
      $relatedLinks = RelatedLink::orderBy('link_order','asc')->where('ac_non_ac_id',$id)->get();
      foreach ($relatedLinks as $key => $value) {
              $links[$key]['id'] = $value->id;
              $links[$key]['link'] = $value->link_name_en;
              $links[$key]['slug'] = $type.'/'.$value->slug;
      }

      //Get Custom link
      $customlinks = array(); 
      $cus_rel_Links = CustomRelatedLink::where('approval_status','Approved')->orderBy('order_on','asc')->where('ac_non_ac_id',$id)->get();
      if ($cus_rel_Links) {
          foreach ($cus_rel_Links as $key => $value) {
              $customlinks[$key]['id'] = $value->id;
              $customlinks[$key]['cus_link'] = $value->link_name_en;
              $customlinks[$key]['rel_slug'] = $value->rel_slug;
          }
      }

      //Get other section data
      $othermap =  MappingDepartments::where('ac_non_ac_id',$id)->orwhere('dep_id',$id)->first();
      $section = array(); 
      if ($othermap) {          
          $mapdata = MappingDepartments::select('id','ac_non_ac_id','dep_id','type')->where('ac_non_ac_id',$othermap->ac_non_ac_id)->with('departments:id,title_en,slug')->orderBy('id','ASC')->get();
          if($mapdata->count()){
              foreach ($mapdata as $key => $value){
                  $section[$key]['id'] = $value->departments->id;
                  $section[$key]['title'] = $value->departments->title_en;
                  $section[$key]['slug'] = $value->departments->slug;
              }
          }
      }
    }
    return response()->json([ 
          'message' => 'Data Found',
          'code' => 1000,
          'head'=>$head,
          'links'=>$links,
          'section'=>$section
        ]);
  } else {
          return response()->json([ 
          'message' => 'Data not Found',
          'code' => 2000
        ]);
  }
}
