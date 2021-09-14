<?php

  

namespace App\Mail;

  

use Illuminate\Bus\Queueable;

use Illuminate\Contracts\Queue\ShouldQueue;

use Illuminate\Mail\Mailable;

use Illuminate\Queue\SerializesModels;

  

class SendAMUEmail extends Mailable

{

    use Queueable, SerializesModels;

  

    /**

     * Create a new message instance.

     *

     * @return void

     */

    public function __construct($data)

    {

        $this->data = $data;

    }

  

    /**

     * Build the message.

     *

     * @return $this

     */

    public function build()

    {
        
        $subject = $this->data['subject'];
        if (isset($this->data['file'])) {
           return $this->view('emails.broadcastEmail')
                    //->from($address, $name)
                    //->cc($address, $name)
                    //->bcc($this->data['email'])
                    //->replyTo($address, $name)
                    ->subject($subject)
                    ->attach($this->data['file']->getRealPath(),
                    [
                        'as' => $this->data['file']->getClientOriginalName(),
                        'mime' => $this->data['file']->getClientMimeType(),
                    ])
                    ->with([
                    'name'     => $this->data['name'],     
                    'email'     => $this->data['email'], 
                    'subject'     => $this->data['subject'], 
                    'messages'     => $this->data['messages'],
                ]);
        }else{
            return $this->view('emails.test')
                        //->from($address, $name)
                        //->cc($address, $name)
                        //->bcc($this->data['email'])
                        //->replyTo($address, $name)
                        ->subject($subject)
                        ->with([
                        'name'     => $this->data['name'], 
                        'email'     => $this->data['email'], 
                        'subject'     => $this->data['subject'], 
                        'messages'     => $this->data['messages'],
                    ]);
        }
        
    }

}