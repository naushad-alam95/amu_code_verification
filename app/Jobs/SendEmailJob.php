<?php 
namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use LaravelQueueMonitorProvider;
use App\Mail\SendAMUEmail;
use App\EmailLogReciever;
use App\EmailLog;
use Carbon\Carbon;
use Mail;

class SendEmailJob implements ShouldQueue

{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    //public $data = [];
    public $tries = 3;

    public function __construct($data){
  
        if ($data) {
            $this->data = $data;

            foreach($this->data['users'] as $key => $email){                 
                $email['email'] = filter_var($email['email'], FILTER_SANITIZE_EMAIL);
                if(!str_starts_with($email['email'], '--@') && filter_var($email['email'], FILTER_VALIDATE_EMAIL)) {
                    //\Log::info("Email: ".$key++ .' - ' . $email['email']);
                    $this->data['name']  = $email['name']; 
                    $this->data['email'] = $email['email'];
                    $emails = new SendAMUEmail($this->data);
                    try {
                        Mail::to($this->data['email'])->send($emails);
                        $emailLog = EmailLog::where('id',$this->data['email_log'])->first();
                        $rec      = array();
                        $rec['email_log_id'] = $emailLog->id;
                        $rec['user_email'] = $this->data['email'];
                        $rec['status'] = 'success';
                        $reciever = EmailLogReciever::create($rec);
                    } 
                    catch (Swift_TransportException $e) {
                        //\Log::info("failures: " . $email_address);
                          $emailLog = EmailLog::where('id',$this->data['email_log'])->first();
                          $rec = array();
                          $rec['email_log_id'] = $emailLog->id;
                          $rec['user_email'] = $email_address;
                          $rec['status'] = 'failed';
                          $reciever = EmailLogReciever::create($rec);
                        return redirect()->back()->with('message', $e->getMessage());
                    }
                }else{
                        $emailLog = EmailLog::where('id',$this->data['email_log'])->first();
                        $rec = array();
                        $rec['email_log_id'] = $emailLog->id;
                        $rec['user_email'] = $email['email'];
                        $rec['status'] = 'failed';
                        $reciever = EmailLogReciever::create($rec);
                }
            }
            return redirect()->back()->with('message', 'Your email is in a queue, you can check status in email log');
        }else{
            return redirect()->back()->with('message', 'Something went wrong please try again');
        }
    }
    
    public function handle(){
       
    }
}