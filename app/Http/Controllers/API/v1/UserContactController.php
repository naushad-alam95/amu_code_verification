<?php

namespace App\Http\Controllers\API\v1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
Use App\UserContact;
use Validator;
use App\User;
use Auth;

class UserContactController extends BaseController
{
    
    public function __construct()
    {
        $this->middleware('auth:api');
    }    

    /**
     * Display a listing of the User Contact.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
       
        $userContact = UserContact::select('id','user_id','email','mobile_no','address','primary','updated_at')->where('user_id',$request->user()->id)->get();

        if (is_null($userContact)){                        
            return $this->sendResponse($userContact, trans('en_lang.DATA_NOT_FOUND'),404);
        }
        return $this->sendResponse($userContact, trans('en_lang.DATA_FOUND'),200);
    }

    
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $input = $request->all();

        $validator = Validator::make($input, [
            'mobile_no' => 'required',
        ]);

        if($validator->fails()){
            return $this->sendError('Validation Error.', $validator->errors(),422);      
        }
        /*if ($request->get('primary') == '1') {
            UserContact::where('user_id',$request->user()->id)->update(['primary' => '0']);
        }*/
        if ($request->has('email')) {
            $validator = Validator::make($request->all(), [
                'email'  => 'email:rfc,dns'
            ]);
            if ($validator->fails()) {
                return $this->sendError('Validation Error.', $validator->errors(),422);
            }
        }
        $userContact = new UserContact([
            'user_id' => $request->user()->id,
            'email' => $request->email,
            'address' => $request->address,
            'mobile_no' => $request->mobile_no, 
            'email_visibility' => $request->email_visibility,
            'mobile_visibility' => $request->mobile_visibility,
            'addr_visibility' => $request->addr_visibility,
        ]);
        $userContact->save();

        return $this->sendResponse($userContact,trans('en_lang.DATA_CREATED'),201);

    }

    /**
     *Edit User Contact.
     *
     * @return \Illuminate\Http\Response
     */
    public function edit(Request $request, $id)
    {
       
        $userContact = UserContact::select('id','user_id','primary','email','email_visibility','mobile_no','mobile_visibility','address','addr_visibility','updated_at')->where('user_id',$request->user()->id)->FindorFail($id);

        if (is_null($userContact)){                        
            return $this->sendResponse($userContact, trans('en_lang.DATA_NOT_FOUND'),404);
        }
        return $this->sendResponse($userContact, trans('en_lang.DATA_FOUND'),200);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    /*public function show(Request $request, $id)
    {
       // $id = $request->id;
        $userContact = UserContact::select('id','user_id','UserContact','from_year','from_where','updated_at')->where('user_id',$request->user()->id)->find($id);

        if (is_null($userContact)) {
            return $this->sendResponse($userContact, trans('en_lang.DATA_NOT_FOUND'),404);
        }

        return $this->sendResponse($userContact, trans('en_lang.DATA_FOUND'),200);
    }*/

    
    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $input = $request->all();
        //\Log::info($request->all());
        $validator = Validator::make($input, [
            //'email' => 'required|email'
            'mobile_no' => 'required',
            // 'address' => 'required'
        ]);


        if($validator->fails()){
            return $this->sendError('Validation Error.', $validator->errors(),422);      
        }
        /*if ($request->primary == '1') {
            UserContact::where('user_id',$request->user()->id)->update(['primary' => '0']);
        }*/
        if ($request->has('email')) {
            $validator = Validator::make($request->all(), [
                'email'  => 'email:rfc,dns'
            ]);
            if ($validator->fails()) {
                return $this->sendError('Validation Error.', $validator->errors(),422);
            }
        }
        $userContact = UserContact::where('user_id',$request->user()->id)->find($id);

        $userContact->user_id         = $request->user()->id;
        $userContact->email           = $request->email;
        $userContact->mobile_no       =  $request->mobile_no;
        $userContact->address         = $request->address;
        $userContact->addr_visibility   = $request->addr_visibility;
        $userContact->email_visibility  = $request->email_visibility;
        $userContact->mobile_visibility =  $request->mobile_visibility;
        $userContact->save();

        return $this->sendResponse($userContact,trans('en_lang.DATA_UPDATED'),200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, $id)
    {
        $delete = UserContact::where('user_id',$request->user()->id)->find($id);  
        $delete->delete();

        return $this->sendResponse('UserContact successfully deleted', trans('en_lang.DATA_DELETED'),204);
    }

    public function primaryContact(Request $request)
    {
        $input = $request->all();
        $validator = Validator::make($input, [
            'primary' => 'required',
            'id'      => 'required',
        ]);


        if($validator->fails()){
            return $this->sendError('Validation Error.', $validator->errors(),422);      
        }        
        UserContact::where('user_id',$request->user()->id)->where('primary','!=','0')->update(['primary' => '0']);
        
        if ($request->primary == '1') {
            $primary = UserContact::where('id',$request->id)->where('user_id',$request->user()->id)->update(['primary' => '1']);
        }      

        return $this->sendResponse($primary,trans('en_lang.DATA_UPDATED'),200);
    }
}
