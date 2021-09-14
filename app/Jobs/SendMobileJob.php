<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use LaravelQueueMonitorProvider;
use App\EmailLogReciever;
use App\EmailLog;

class SendMobileJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $data = [];

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($data)
    {
        if ($data) {

            $this->data = $data;

            foreach($this->data['mobile'] as $mob){
                $url        = env('SMS_APIURL', 'https://www.txtguru.in/imobile/api.php?');
                $username   = env('SMS_USERNAME', 'hashmi78');
                $password   = env('SMS_PASSWORD', 'Ccentre@1970'); 
                $source     = env('SMS_SOURCE', 'AMUCCD'); 

                $mobile     = $mob['mobile'];
                $message    = $this->data['message'];
                $post       = 'username='.$username.'&password='.$password.'&source='.$source.'&dmobile='.$mobile.'&message='.$message;

                $ch = curl_init($url);
                curl_setopt($ch, CURLOPT_POST, 1); 
                curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                $y = curl_exec($ch);
                $status_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                if($status_code == 200){
                    $smsLog = EmailLog::where('id',$this->data['sms_log'])->first();
                    $rec      = array();
                    $rec['email_log_id'] = $smsLog->id;
                    $rec['user_email']   = $mob['mobile'];
                    $rec['status']       = 'success';
                    $reciever = EmailLogReciever::create($rec);
                }else{
                    $smsLog = EmailLog::where('id',$this->data['sms_log'])->first();
                    $rec      = array();
                    $rec['email_log_id'] = $smsLog->id;
                    $rec['user_email']   = $mob['mobile'];
                    $rec['status'] = 'failed';
                    $reciever = EmailLogReciever::create($rec);
                }
            }
            return redirect()->back()->with('message', 'Your sms is in a queue, you can check status in sms log');            
        }else{
            return redirect()->back()->with('message', 'Something went wrong please try again');
        }
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        //
    }
}
