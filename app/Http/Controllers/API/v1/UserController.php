<?php
namespace App\Http\Controllers\API\v1;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;
use App\Rules\MatchOldPassword;
use App\AcademicAndNonAcademic;
use Illuminate\Http\Request;
Use App\UserVisibility;
Use App\Qualification;
use Carbon\Carbon;
use Validator;
use App\User;
use App\ELeave;
use App\EProvidentFund;
use App\EPaySlips;
use Auth;
use File;
use Hash;
use Mail;
use Log;
use DB;


class UserController extends BaseController
{
        /**
     * Login user and create token
     *
     * @param  [string] email
     * @param  [string] password
     * @param  [boolean] remember_me
     * @return [string] access_token
     * @return [string] token_type
     * @return [string] expires_at
     */
      
    /**
     * Get the authenticated User
     *
     * @return [json] user object
     */
    // Get User Data
    public function getData(Request $request)
    {
        $employee = UserVisibility::where('user_id','=',$request->user()->id)->where('core','=','1')->with('getUser.getContact','getUser.getStudyMaterial','getUser.getThrustArea','getUser.getQualification', 'getUser.getJournal','getUser.getDepartment.getAcNonAcItem','getDesignation','getRole')->orderBy('role_id','ASC')->orderBy('order_on','ASC')->first();
        if (empty($employee)){                        
            return $this->sendResponse($employee, trans('en_lang.DATA_FOUND'),404);
        }
        return $this->sendResponse($employee, trans('en_lang.DATA_FOUND'),200);
    }

    //Get Basic Information
    public function getBasicInfo(Request $request)
    {
        $token = $request->bearerToken();
        $basicInfo =  UserVisibility::select('id','user_id','role_id','ac_non_ac_id','for_id')->where('user_id','=',$request->user()->id)->where('core','=','1')->with('getUser','getRole','getDepartment')->orderBy('role_id','ASC')->orderBy('order_on','ASC')->first();
        $academic =  AcademicAndNonAcademic::where('id',$basicInfo->ac_non_ac_id)->first();
        if ($academic->type == '1') {
            $basicInfo['ac_type']= 'academic';
        }else{
            $basicInfo['ac_type']= 'non-academic';
        } 
        $basicInfo->access_token = $token; 
        if (empty($basicInfo)){                        
            return $this->sendResponse($basicInfo, trans('en_lang.DATA_FOUND'),404);
        }
        return $this->sendResponse($basicInfo, trans('en_lang.DATA_FOUND'),200);
    }

    // Get Conatct Detail
    public function getContact(Request $request)
    {
        $token = $request->bearerToken();
        $contact =  UserVisibility::select('id','user_id','role_id')->where('user_id','=',$request->user()->id)->where('core','=','1')->with('getUser.getContact','getRole')->orderBy('role_id','ASC')->orderBy('order_on','ASC')->first();
        $contact->access_token = $token; 
        if (empty($contact)){                        
            return $this->sendResponse($contact, trans('en_lang.DATA_FOUND'),404);
        }
        return $this->sendResponse($contact, trans('en_lang.DATA_FOUND'),200);
    } 

    // Get Study Material
    public function getStudyMaterial(Request $request)
    {
        $token = $request->bearerToken();
        $studyMaterial =  UserVisibility::select('id','user_id','role_id')->where('user_id','=',$request->user()->id)->where('core','=','1')->with('getUser.getStudyMaterial','getRole')->orderBy('role_id','ASC')->orderBy('order_on','ASC')->first();
        $studyMaterial->access_token = $token; 
        if (empty($studyMaterial)){                        
            return $this->sendResponse($studyMaterial, trans('en_lang.DATA_FOUND'),404);
        }
        return $this->sendResponse($studyMaterial, trans('en_lang.DATA_FOUND'),200);
    }

    //Update User General Information
    public function updateBasicInfo(Request $request) {

        $input = $request->all();
        $validator = Validator::make($input, [
            'title' => 'required',
            'first_name' => 'required',
        ]);

        if($validator->fails()){
           return $this->sendError('Validation Error.', $validator->errors(),422);     
        }         

        $User = Auth::user();       
        $User->title = $request->title;
        $User->first_name = $request->first_name;

        if ($request->dob == 'null' || $request->dob == Null){
	       $User->dob = Null;
    	}else{
    	   $User->dob = date("Y-m-d", strtotime($request->dob));
    	}

        if ($request->middle_name == 'null' || $request->middle_name == Null) {
            $User->middle_name = Null;
        }else{
             $User->middle_name = $request->middle_name;
        }

        if ($request->last_name == 'null' || $request->last_name == Null) {
            $User->last_name = '';
        }else{
             $User->last_name = $request->last_name;
        }

        if ($request->has('profile'))
            $User->profile = $request->profile; 
        

        if($request->hasFile('image'))
        {
            $files = Storage::disk('public')->exists($User->image);

            if($files == true) {
                DeleteOldPicture($User->image);
            }

            $uploaded_image = $request->user()->id.'-'.time().'.'.$request->file('image')->guessExtension();

            
            $thumb_path     = '/images/empphoto/'.$uploaded_image ;
            $list_thumb     =  Image::make($request->file('image'))->fit(260, 320, function ($constraint) {
                    $constraint->upsize();
                });
            $list_thumb     = $list_thumb->stream()->detach();
            Storage::disk('local')->put('/public'.$thumb_path, $list_thumb);
            $User->image    = $thumb_path;

        }

        if($request->hasFile('cv'))
        {
            $files = Storage::disk('public')->exists($User->cv);
            if($files == true) {
                DeleteOldPicture($User->cv);
            }
            $uploaded_file = $request->user()->id.'-'.time().'.'.$request->file('cv')->guessExtension();
            $User->cv = '/'.$request->file('cv')->storeAs('/images/empcv',$uploaded_file,'public');
        }
        //\Log::info($User);
        $User->save();
        return $this->sendResponse('Successfully Updated', trans('en_lang.DATA_FOUND'),200);

    }

    public function deleteCv(Request $request)
    {
       
        $user = User::where('id',$request->user()->id)->first(); 
        if ($user->cv != '') {
            DeleteOldPicture($user->cv);
            User::where('id',$request->user()->id)->update(['cv' => NULL]);
        }

        return $this->sendResponse('CV successfully deleted', trans('en_lang.DATA_DELETED'),204);
    }

    public function updateUserPassword(Request $request)
    {
        
        $input = $request->all();
        $validator = Validator::make($input, [
            'currentPassword' => 'required',
            'newPassword' => ['required', 'string', 'min:8']
        ]);

        $user = User::find($request->user()->id); 

        if (!Hash::check($request->currentPassword, $user->password)) {
           return $this->sendError('The old password does not match our records.', $validator->errors(),422);
        }

        $request->user()->fill([
            'password' => Hash::make($request->newPassword)
        ])->save();  

        return $this->sendResponse($user,trans('en_lang.DATA_UPDATED'),200);
    }


    public function EditorImage(Request $request){
        
        if ($request->isMethod('post')) {

            $input = $request->all();
            $url = url('/');

            $validator = Validator::make($input, [
                'UploadFiles' => 'required|mimes:jpeg,jpg,png,bmp,gif,svg',
            ]);

            if($validator->fails()){

               return $this->sendError('Validation Error.', $validator->errors(),422); 

            }else{

                $uploaded_image = time().'_'.$request->user()->eid.'.'.$request->file('UploadFiles')->guessExtension();
                $imgpath = $request->file('UploadFiles')->storeAs('images/editor/'.$request->user()->eid, $uploaded_image,'public');

                return response()->json(['UploadFiles' =>  $url.'/storage/'.$imgpath]);
            }
            
        }        
    }

    /*
    ** Forget password Method
    */

    // Check User EID is Valid or not and get email and mobile no.
    public function checkValidEmpId(Request $request)
    {
        $input = $request->all();
        $validator = Validator::make($input, [
            'eid' => 'required|numeric',
        ]);

        if($validator->fails()){
           return $this->sendError('Validation Error.', $validator->errors(),422);     
        }

        $users = User::with('getPrimaryContact')->where('status','=','1')->where('eid','=',request()->eid)->first();
        if(!empty($users)){
            $data = array();
            $data['id'] = $users->id;
            $data['eid'] = $users->eid;
            if ($users->getPrimaryContact) {
                $data['email'] = $users->getPrimaryContact->email;
                $data['mobile_no'] = $users->getPrimaryContact->mobile_no;
            }else{
                $data['email'] = '';
                $data['mobile_no'] = '';
            }         
            return $this->sendResponse($data, trans('en_lang.DATA_FOUND'),200);
        }
        return $this->sendError(trans('en_lang.DATA_NOT_FOUND'), 'Something went wrong. please contact to the admin!',200);
    }

    // Check User Email and mobile no.
    public function getUserEmailOrMobile(Request $request)
    {
        $input = $request->all();
        $validator = Validator::make($input, [
            'id' => 'required|numeric',
        ]);

        if($validator->fails()){
           return $this->sendError('Validation Error.', $validator->errors(),422);     
        }

        $users = User::with('getPrimaryContact')->where('status','=','1')->where('id','=',request()->id)->first();

        if(!empty($users)){
            $data = array(); 
            if ($users->getPrimaryContact) {
                $data['email'] = $users->getPrimaryContact->email;
                $data['mobile_no'] = $users->getPrimaryContact->mobile_no;
            }else{
                $data['email'] = '';
                $data['mobile_no'] = '';
            }            
            return $this->sendResponse($data, trans('en_lang.DATA_FOUND'),200);
        }
        return $this->sendError(trans('en_lang.DATA_NOT_FOUND'), 'Something went wrong. please contact to the admin!',200);
    }

    // Send OTP to the user
    public function sendOTP(Request $request)
    {
        $input = $request->all();

        if ($request->isMethod('post')) {
            $users = User::select('id')->with('getPrimaryContact')->where('status','=','1')->where('id','=',request()->id)->first();


            if (request()->mobile_no) {                

                $validator = Validator::make($input, [
                    'id' => 'required|numeric',
                    'mobile_no' => 'required|regex:/^[6-9][0-9]{9}$/',
                ]);                

                if($validator->fails()){
                   return $this->sendError('Validation Error.', $validator->errors(),422);     
                }

                if (request()->id == $users->id && request()->mobile_no == $users->getPrimaryContact->mobile_no) {
                    
                    $url        = env('SMS_APIURL', 'http://perfectbulksms.com/Sendsmsapi.aspx?');
                    $username   = env('SMS_USERNAME', 'amuotp');
                    $password   = env('SMS_PASSWORD', 'YXB0ZHhs'); 
                    $source     = env('SMS_SOURCE', 'AMUCCD'); 
                    $mobile     = '91'.request()->mobile_no;
                    $otp = rand(10000 , 99999);
                    $message = 'Kindly use One Time Password (OTP): ' .$otp. '  to reset your AMU Website personal profile password. This OTP is valid for 24 Hours.';
                   $post = 'USERID='.$username.'&PASSWORD='.$password.'&SERNDERID='.$source.'&TO='.$mobile.'&MESSAGE='.$message;
                       
                    $ch = curl_init($url);
                    curl_setopt($ch, CURLOPT_POST, 1); 
                    curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                    $y = curl_exec($ch);                    
                    $status_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);   //get status code
                    curl_close ($ch);
                    if($status_code == 200){   
                       $users = User::where('id','=',request()->id)->update(['otp' => $otp]); 

                        return $this->sendResponse('please enter the OTP. we sent to your mobile number.', trans('en_lang.DATA_FOUND'),200);
                    }
                    return $this->sendError(trans('en_lang.DATA_NOT_FOUND'),'Something went wrong. please contact to the admin!' ,200);
                }else{
                    return $this->sendError(trans('en_lang.DATA_NOT_FOUND'),'Something went wrong. please contact to the admin!' ,200);
                }
                
            }else{


                $validator = Validator::make($input, [
                    'id' => 'required|numeric',
                    'email' => 'required|email',
                ]);                

                if($validator->fails()){
                   return $this->sendError('Validation Error.', $validator->errors(),422);     
                }


                if (request()->id == $users->id && request()->email == $users->getPrimaryContact->email) {
                    
                    $this->user_email = request()->email;                
                    $subject = 'Sent OTP';
                    $data['otp'] = rand(10000 , 99999);


        
                    Mail::send('emails.forgot_password', $data, function($message) use($subject){
                        $message->from('noreply@amu.ac.in','AMU');                    
                        $message->to($this->user_email);
                        $message->subject($subject);
                    });
            
                    if (Mail::failures()) {
                       return response()->Fail('Sorry! Please try again latter');
                    }else{
                       $users = User::where('id','=',request()->id)->update(['otp' => $data['otp']]);
                       return $this->sendResponse('Great! Successfully sent OTP in your mail', trans('en_lang.DATA_FOUND'),200);
                    } 

                }else{
                    return $this->sendError(trans('en_lang.DATA_NOT_FOUND'),'Something went wrong. please contact to the admin!' ,200);
                }                              
            }
        }
        return $this->sendError(trans('en_lang.DATA_NOT_FOUND'),'Something went wrong. please contact to the admin!' ,200);       
    }

    // re-send OTP to the user
    public function reSendOTP(Request $request)
    {
        $input = $request->all();
        if ($request->isMethod('post')) {
            $validator = Validator::make($input, [
            'id' => 'required|numeric',
            'type' => 'required|in:mobile_no,email',
            'type_value' => 'required',
        ]);                

        if($validator->fails()){
           return $this->sendError('Validation Error.', $validator->errors(),422);     
        }
            $users = User::with('getPrimaryContact')->where('status','=','1')->where('id','=',request()->id)->first();

            if (request()->type == 'mobile_no') {

                $validator = Validator::make($input, [
                    'type_value' => 'required|regex:/^[6-9][0-9]{9}$/',
                ]);                

                if($validator->fails()){
                   return $this->sendError('Validation Error.', $validator->errors(),422);     
                }

                if (request()->id == $users->id && request()->type_value == $users->getPrimaryContact->mobile_no) {

                    $url        = env('SMS_APIURL', 'http://perfectbulksms.com/Sendsmsapi.aspx?');
                    $username   = env('SMS_USERNAME', 'amuotp');
                    $password   = env('SMS_PASSWORD', 'YXB0ZHhs');
                    $source     = env('SMS_SOURCE', 'AMUCCD');
                    $mobile     = '91'.request()->type_value;
                    $otp        = rand(10000 , 99999);
                    $message    = 'Kindly use One Time Password (OTP): ' .$otp. '  to reset your AMU Website personal profile password. This OTP is valid for 24 Hours.';
                    $post       = 'USERID='.$username.'&PASSWORD='.$password.'&SENDERID='.$source.'&TO='.$mobile.'&MESSAGE='.$message;


                    $ch = curl_init($url);
                    curl_setopt($ch, CURLOPT_POST, 1); 
                    curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                    $y = curl_exec($ch);
                    $status_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);   //get status code
                    curl_close ($ch);
                    if($status_code == 200){   
                       $users = User::where('id','=',request()->id)->update(['otp' => $otp]); 

                        return $this->sendResponse('please enter the OTP. we sent to your mobile number.', trans('en_lang.DATA_FOUND'),200);
                    }
                    return $this->sendError(trans('en_lang.DATA_NOT_FOUND'),'Something went wrong. please contact to the admin!' ,200);
                }else{
                    return $this->sendError(trans('en_lang.DATA_NOT_FOUND'),'Something went wrong. please contact to the admin!' ,200);
                }
            }else{

                $validator = Validator::make($input, [
                    'type_value' => 'required|email',
                ]);                

                if($validator->fails()){
                   return $this->sendError('Validation Error.', $validator->errors(),422);     
                }

                if (request()->id == $users->id && request()->type_value == $users->getPrimaryContact->email) {

                    $this->user_email = request()->type_value;                
                    $subject = 'Sent OTP';
                    $data['otp'] = rand(10000 , 99999);
        
                    Mail::send('emails.forgot_password', $data, function($message) use($subject){
                        $message->from('noreply@amu.ac.in','AMU');                    
                        $message->to($this->user_email);
                        $message->subject($subject);
                    });
            
                    if (Mail::failures()) {
                       return response()->Fail('Sorry! Please try again latter');
                    }else{
                       $users = User::where('id','=',request()->id)->update(['otp' => $data['otp']]);
                       return $this->sendResponse('Great! Successfully sent OTP in your mail', trans('en_lang.DATA_FOUND'),200);
                    }
                }else{
                    return $this->sendError(trans('en_lang.DATA_NOT_FOUND'),'Something went wrong. please contact to the admin!' ,200);
                }              
            }
        }
        return $this->sendError(trans('en_lang.DATA_NOT_FOUND'),'Something went wrong. please contact to the admin!' ,200);       
    }

    // Check OTP Valid or Not
    public function checkOTP(Request $request)
    {
        
        $input = $request->all();
        $validator = Validator::make($input, [
            'id' => 'required|numeric',
            'otp' => 'required|numeric',
        ]);

        if($validator->fails()){
           return $this->sendError('Validation Error.', $validator->errors(),422);     
        }

        $check_otp = User::where('id',request()->id)->where('otp',request()->otp)->first();
        if (!empty($check_otp)) {

            return $this->sendResponse('OTP  Matched!', trans('en_lang.DATA_FOUND'),200);

        }else{

            return $this->sendError(trans('en_lang.DATA_NOT_FOUND'),'OTP does not match!',200);
        }       
        
    }
    //Change Password
    public function changeUserPassword(Request $request)
    {
        
        $input = $request->all();
        $validator = Validator::make($input, [
            'id' => 'required|numeric',
            'otp' => 'required|numeric',
            'password' => ['required', 'string', 'min:8'],
            'confirm_password' => ['same:password'],
        ]);

        if($validator->fails()){
           return $this->sendError('Validation Error.', $validator->errors(),422);     
        }

        $check_user = User::where('id',request()->id)->where('otp',request()->otp)->first();
        if ($check_user) {
            User::where('id',request()->id)->update(['password' => Hash::make($request->password),'otp' => Null]);
            return $this->sendResponse('Password Updated!', trans('en_lang.DATA_FOUND'),200);
        }else{
            return $this->sendError(trans('en_lang.DATA_NOT_FOUND'),'Something went wrong!',200);
        }
    } 

    public function ePaySlips(Request $request)
    {
        
        $input = $request->all();
        $validator = Validator::make($input, [
            'month' => 'required|numeric',
            'year' => 'required|numeric',
            'type' => 'required'
        ]);
        if($validator->fails()){
           return $this->sendError('Validation Error.', $validator->errors(),422);     
        }
        $user = User::where('id',$request->user()->id)->first();
        
        $check_user = EPaySlips::select(DB::raw("DESIG_NAME,DEPT_NAME,PAYBAND,GRADE,SAL_NO,INCR,SUM(BASIC_CAL) as BASIC_CAL,NAME,SUM(RNPA) as RNPA,SUM(RDA) as RDA,SUM(RHRA) as RHRA,SUM(RTRAN) as RTRAN,SUM(EDU) as EDU,SUM(OTH1A) as OTH1A,SUM(OTH2A) as OTH2A,SUM(OTH3A) as OTH3A,SUM(OTH4A) as OTH4A,SUM(OTH5A) as OTH5A,SUM(OTH6A) as OTH6A ,OTH_DES1,OTH_DES2,OTH_DES3,OTH_DES4,OTH_DES5, OTH_DES6,SUM(ITAX) as ITAX,SUM(LIC) as LIC,SUM(LIN_FEE) as LIN_FEE,SUM(PF) as PF,SUM(PF_LOAN) as PF_LOAN,SUM(NPS) as NPS,SUM(CGIS) as CGIS,SUM(FES_ADV) as FES_ADV,SUM(VEH_ADV) as VEH_ADV,SUM(VC_LOAN) as VC_LOAN,SUM(HBL_UGC) as HBL_UGC,SUM(HBL_INT) as HBL_INT,SUM(ELECT) as ELECT,SUM(MAS) as MAS,SUM(TSA) as TSA,SUM(NTSA) as NTSA,SUM(TCHSA) as TCHSA,SUM(TWS) as TWS,SUM(VBSS) as VBSS,MIS_DES1,MISC1,MIS_DES2,MISC2,MIS_DES3,MISC3,MIS_DES4,MISC4,BANKNAME,BANK_AC,CHEQ_NO,CHEQ_DT,REMARK,SUM(DEDU) as DEDU,SUM(NET_PAY) as NET_PAY,SUM(RGROSS) as RGROSS,FFDATE,FTDATE,PER_ID,PAYPAYBAND,FGDES,SUM(REV_STM) as REV_STM,SUM(SAL_ADV) as SAL_ADV,SUM(RECOVRY) as RECOVRY,SUM(PER_PAY) as PER_PAY,SUM(SPL_PAY) as SPL_PAY,PAN, group_concat(concat(FFDATE, ' - ', FTDATE) separator ' , ') as period"))->where('PER_ID',$user->eid)->where('MTH_NO',request()->month)->where('YR_NO',request()->year)->where('SAL_CODE',request()->type)->first();

            $data1 =array();
            $data1['month'] = date("F", strtotime('00-'.request()->month.'-01'));
            $data1['year'] = request()->year;
            if (request()->type == "1.00") {
               $data1['type'] = "Main";
            }elseif (request()->type == "2.00") {
               $data1['type'] = "Supplementary-I";
            }elseif (request()->type == "3.00") {
               $data1['type'] = "Supplementary-II";
            }elseif (request()->type == "4.00") {
               $data1['type'] = "Supplementary-III";
            }elseif (request()->type == "5.00") {
               $data1['type'] = "Supplementary-IV";
            }elseif (request()->type == "6.00") {
               $data1['type'] = "Supplementary-V";
            }elseif (request()->type == "7.00") {
               $data1['type'] = "Supplementary-VI";
            }elseif (request()->type == "8.00") {
               $data1['type'] = "Supplementary-VII";
            }elseif (request()->type == "9.00") {
               $data1['type'] = "Bonus";
            }
        if ($check_user->BASIC_CAL != null) {           

            $left = array();
            $right = array();
        
        
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
            $data['PER_ID'] = $PER_ID;
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
    }  

    public function eProvidentFund(Request $request)
    {
        
        $input = $request->all();
        $validator = Validator::make($input, [
            'year' => 'required',
        ]);

        if($validator->fails()){
           return $this->sendError('Validation Error.', $validator->errors(),422);     
        }
        $user = User::where('id',$request->user()->id)->first();
        $EProvidentFund = EProvidentFund::where('PER_ID',$user->eid)->where('F_YR',request()->year)->first();
        if(!empty($EProvidentFund)){
            return $this->sendResponse($EProvidentFund, trans('en_lang.DATA_FOUND'),200);
        }else{
            return $this->sendError(trans('en_lang.DATA_NOT_FOUND'), trans('en_lang.DATA_NOT_FOUND'),404);
        }
    } 

    public function eLeave(Request $request)
    {        
        $input = $request->all();
        $validator = Validator::make($input, [
            'year' => 'required|numeric',
        ]);

        if($validator->fails()){
           return $this->sendError('Validation Error.', $validator->errors(),422);     
        }
        $user = User::where('id',$request->user()->id)->first();
        $emp_leave = ELeave::where('PER_ID',$user->eid)->where('YEAR',request()->year)->first();
        if(!empty($emp_leave)){
            return $this->sendResponse($emp_leave, trans('en_lang.DATA_FOUND'),200);
        }else{
            return $this->sendError(trans('en_lang.DATA_NOT_FOUND'), trans('en_lang.DATA_NOT_FOUND'),404);
        }       
    } 
}