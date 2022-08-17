<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;


class SendMail extends Mailable
{
    use Queueable, SerializesModels;

    public $subject;
    public $from_email;
    public $from_name;
    public $template;
    public $data;
    public $attachment;


    public function __construct($subject, $from_email, $template, $data, $attachment = null)
    {
        $this->subject = $subject;
        $this->from_email = $from_email;
        $this->template = $template;
        $this->data = $data;
        $this->attachment = $attachment;

        $this->from_name = config('constants.defines.MAIL_FROM_NAME');
    }

    public function build(): SendMail
    {
        return $this->subject($this->subject)
            ->from($this->from_email, $this->from_name)
            ->when(! is_null($this->attachment), function ($sendMail) {
                $sendMail->attach($this->attachment);
            })
            ->markdown('email.' . $this->template)
            ->with([
                'data' => $this->data
            ]);
    }
}
