<?php
namespace App\Http\Controllers\API\v1;

use App\Http\Controllers\Controller;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
Use App\UserVisibility;
Use App\AcademicAndNonAcademic;
use App\RevokeMessage;
use Carbon\Carbon;
use App\RoleType;
use App\UserContact;
use Validator;
use App\User;
use Hash;
use App\MouUser;
use Auth;
use DB;
use Mail;



class AuthController extends BaseController
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
    public function login(Request $request)
    {

        $message = RevokeMessage::where('status','1')->first();
        if (!empty($message)) {
            return $this->sendResponse($message, trans('en_lang.DATA_FOUND'),200);
        }
        
        $request->validate([
            'eid' => 'required',
            'password' => 'required|string',
            'remember_me' => 'boolean'
        ]);

        $log_user = User::where('eid',$request->eid)->where('status','1')->first();
        if (empty($log_user)) {
           return response()->json('Your account is Deactivated. Please contact to the Web master!', 401);
        }


        $credentials = request(['eid', 'password']);
        if(!Auth::attempt($credentials))
            return response()->json([
                'message' => "The EID or password that you've entered is incorrect."
            ], 401);
        $user = $request->user();
        $tokenResult = $user->createToken('Personal Access Token');
        $token = $tokenResult->token;
        $token->expires_at = Carbon::now()->addDays(10);
        $token->save();
        $user = UserVisibility::where('user_id','=',$request->user()->id)->where('core','=','1')->with('getUser','getRole','getDepartment')->orderBy('role_id','ASC')->orderBy('order_on','ASC')->first();
        $academic =  AcademicAndNonAcademic::where('id',$user->ac_non_ac_id)->first();
        if ($academic->type == '1') {
            $user['ac_type']= 'academic';
        }else{
            $user['ac_type']= 'non-academic';
        } 
        if($user->getUser->image !=NULL){
           $user->getUser->image = $user->getUser->image;
        }else{
           $user->getUser->image = '/images/default-img.png';
        }
        $user->getAuthority = array();
        $user->eid = $user->getUser->eid;
        $user->token_type = 'Bearer';
        $user->expires_at = Carbon::parse(
                $tokenResult->token->expires_at
            )->toDateTimeString();
        $user->access_token = $tokenResult->accessToken;
      //  $user->refresh_token = $tokenResult->refreshToken;
        $user->access_role = ''; 
        $user->access_section = ''; 
        $user->section_type = '';  
        $user->user_type = 'employee';  

        return $this->sendResponse($user, trans('en_lang.DATA_FOUND'),200);
    }
  
    /**
     * Logout user (Revoke the token)
     *
     * @return [string] message
     */
    public function logout(Request $request)
    {
        $input = $request->all();
        //\Log::info($input);
        if(isset($input['user_id'])){
           $user_id = $input['user_id'];
        }else{
           $user_id = $input[0];  
        }
        //\Log::info($input);
        DB::table('oauth_access_tokens')->where('user_id',$user_id)->update(['revoked' => true]);
        //$request->user()->token()->revoke();
        //auth('api')->user()->tokens
        /*auth('api')->user()->tokens->each(function ($token, $key) {
            $token->delete();
        });*/

        
        return response()->json([
            'message' => 'Successfully logged out'
        ]);
    }  

    public function roleLogin(Request $request)
    {
        $message = RevokeMessage::where('status','1')->first();
        if (!empty($message)) {
            return $this->sendResponse($message, trans('en_lang.DATA_FOUND'),200);
        }
        
        $validator = Validator::make($request->all(), [
            'eid' => 'required',
            'password' => 'required|string',
            'role' => 'required',
            'slug' => 'required',
            'type' => 'required',
        ]);
        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors(),422,false);
        } else {

            $log_user = User::where('eid',$request->eid)->where('status','1')->first();
            if (empty($log_user)) {
               return response()->json('Your account is Deactivated. Please contact to the Web master!', 401);
            }
            
            $credentials = request(['eid', 'password']);

            if(!Auth::attempt($credentials))
                return response()->json([
                    'message' => "The EID or password that you've entered is incorrect."
                ], 401);

            $user = $request->user();
            $tokenResult = $user->createToken('Personal Access Token');
            $token = $tokenResult->token;
            $department = AcademicAndNonAcademic::where('slug',$request->slug)->first();
            $department_id = $department->id;
            $role = RoleType::where('role_type',$request->role)->first();
            if (!empty($role)) {
               $user = UserVisibility::where('user_id','=',$user->id)->where('ac_non_ac_id','=',$department_id)->where('role_id','=',$role->id)->with('getUser','getRole','getDepartment')->first();

                if (is_null($user)){                        
                    return $this->sendResponse($user, trans('en_lang.ACCESS_DENIED'),401);
                }

               $user->getAuthority = array('Submitted for Moderation','Submitted for Rechecking','Submitted for Approval','Approved','Draft');
               $user->role_detail =array('head_role'=>$department->head,'user_role'=>$role->id);
            }else{
                $user = UserVisibility::where('user_id','=',$user->id)->where('ac_non_ac_id','=',$department_id)->where('special_role','=',$request->role)->with('getUser','getRole','getDepartment')->first();

                if (is_null($user)){                        
                    return $this->sendResponse($user, trans('en_lang.ACCESS_DENIED'),401);
                }

                $user->role_detail =array('head_role'=>'','user_role'=>'');
                if($request->role == 'Approver') {
                    $user->getAuthority = array('Submitted for Moderation','Submitted for Rechecking','Approved');
                }elseif ($request->role == 'Creator') {
                    $user->getAuthority = array('Submitted for Moderation','Draft');
                }elseif ($request->role == 'Moderator') {
                    $user->getAuthority = array('Submitted for Rechecking','Submitted for Approval');
                }
            } 
            

            
            $token->expires_at = Carbon::now()->addDays(10);
            $token->save();
            $user->eid = $user->getUser->eid;
            $user->token_type = 'Bearer';
            $user->expires_at = Carbon::parse(
                    $tokenResult->token->expires_at
                )->toDateTimeString();
            $user->access_token = $tokenResult->accessToken; 
            $user->access_role = $request->role;  
            $user->access_section = $request->slug; 
            $user->section_type = $request->type;
            $user->user_type = 'employee'; 
            if ($department->type == '1') {
                $user['ac_type']= 'academic';
            }else{
                $user['ac_type']= 'non-academic';
            }        
            return $this->sendResponse($user, trans('en_lang.DATA_FOUND'),200);
        }
    }

    /**
     * Register api for Mou Users
     *
     * @return \Illuminate\Http\Response
     */
    public function registerMouUser(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'type' => 'required',
            'eid' => 'required',
            'name' => 'required',
            'email' => 'required|email',
            'password' => 'required',
            'c_password' => 'required|same:password',
        ]);

        if ($validator->fails()) {
            $response = [
                'success' => false,
                'data' => 'Validation Error.',
                'message' => $validator->errors()
            ];
            return response()->json($response, 404);
        }
        $user = MouUser::where('eid',$request->eid)->first();
        if ($user) {
           return response()->json('This eid is already exists!', 401);
        }

        $input = $request->all();
        $data = array();
        $data['eid']             = $request->eid;
        $data['name']            = $request->name;
        $data['email']           = $request->email;
        $data['type']            = $request->type;
        $data['mobile']          = $request->mobile;
        $data['password']        = Hash::make($request->password);
        $data['access_token']    = Str::random(200);
        $mou_user = MouUser::create($data);
        return $this->sendResponse($mou_user, trans('en_lang.DATA_CREATED'), 201);
    }

    /**
     * Login api for Mou User
     *
     * @return \Illuminate\Http\Response
     */
    public function loginMouUser(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_type' => 'required',
            'eid' => 'required',
            'password' => 'required|string',
        ]);
        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors(),422,false);
        } else {
            $credentials = request(['eid', 'password']);
            
            if ($request->user_type == 'employee') {

                if(!Auth::attempt($credentials))
                return response()->json([
                    'message' => "The EID or password that you've entered is incorrect."
                ], 401);

                $user = $request->user();  
                $tokenResult = $user->createToken('Personal Access Token');
                $token = $tokenResult->token;
                $token->expires_at = Carbon::now()->addDays(10);
                $token->save();
                $user->token_type = 'Bearer';
                $user->expires_at = Carbon::parse(
                    $tokenResult->token->expires_at
                )->toDateTimeString();
                $user->access_token = $tokenResult->accessToken; 
                $user->user_type = $request->user_type; 
                  
                return $this->sendResponse($user, trans('en_lang.DATA_FOUND'),200);
            }else{

                // Fetch User
                $user = MouUser::where('eid',$request->eid)->first();
                $user->user_type = $request->user_type;  
                if($user) {
                    // Verify the password
                    if( password_verify($request->password, $user->password) ) {
                        if ($user->access_token == '') {
                           MouUser::where('eid',$request->eid)->update(['access_token' => Str::random(200)]);
                        }
                      return $this->sendResponse($user, trans('en_lang.DATA_FOUND'),200);
                    } else {
                      return response()->json([
                        'message' => 'Invalid Password',
                      ],401);
                    }
                } else {
                    return response()->json([
                      'message' => 'User not found',
                    ],401);
                }
            }
        }
    }

    public function forgetPassword(Request $request)
    {
        if($request->isMethod('post')){
            $input = $request->all();
            $validator = Validator::make($request->all(), [
                'email' => 'required',
                
            ]);
            if ($validator->fails()) {
                return $this->sendError('Validation Error.', $validator->errors(), 422, false);
            } else {
                $user = MouUser::where('email',$request->email)->first();
                if ($user) {
                    $this->confirmation_user_email = $user->email;
                    $this->confirmation_user_name = $user->name;
                    $password =str_random(8);
                    $subject = 'Reset Password';
                    Mail::send('emails.reset_password',
                    array(
                        'user_name' => $user->name,
                        'user_email' => $user->email,
                        'new_pass' => $password,
                    ), function($message) use($subject)
                    {
                        $message->to($this->confirmation_user_email, $this->confirmation_user_name)->subject($subject);
                    });
                
                    MouUser::where('id',$user->id)->update(['password' => Hash::make($password)]);        
                    return $this->sendResponse('An email has been successfully sent to your registered email ID.', trans('en_lang.DATA_FOUND'),200);
                }else{
                    return $this->sendError(trans('en_lang.ACCESS_DENIED'), 'This Email is not exist!',200);
                }
            }           
        }
    }
}