<?php

namespace App\Http\Controllers\API\v1;

use App\Http\Controllers\API\v1\BaseController as BaseController;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Jobs\SendEmailJob;
use Redirect;
use Validator;
use Mail;
use Session;
use App\User;

class EmailController extends BaseController
{
    

    // Send Broadcast mail 
    public function sendTestEmail(Request $request){
        $data = array();
        $data['email']    = 'naushad.a@ahatechnocrats.com';
        $data['subject']  = 'Testing';
        $data['messages'] = 'Testing Purpose'; 
        $data['name']     = 'Team AHA';
         
        
        if ($data) {
            //$this->dispatch(new SendEmailJob($data));
           $mail =  Mail::send('emails.testing', $data, function($message){
                $message->from('noreply@amu.ac.in','AMU | Team');
                $message->subject("Testing Purpose");
                $message->to('hakhan.cc@amu.ac.in')->cc(['parwez.usmani@gmail.com','hakhancc@hotmail.com']);
            });
             die('Mail Sent!');

            //return redirect()->back()->with('message', 'Message has been sent successfully');
        }else{
            return redirect()->back()->with('message', 'Something went wrong please try again');
        }
                
	}
}
