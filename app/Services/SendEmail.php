<?php

namespace App\Services;

use App\Mail\SendMail;
use Exception;
use Illuminate\Support\Facades\Mail;


class SendEmail
{
    public function sendEmail(string|array $to_email, string|array $from_email, string $subject, string $template, array $data, mixed $attachment = null): bool
    {
        $mail_send_status = false;
        $logData['action'] = "EMAIL_SENDING";
        try {

            Mail::to($to_email)
                ->send(
                    new SendMail($subject, $from_email, $template, $data, $attachment)
                );

            $mail_send_status = true;
            $logData['email_status'] = true;
        } catch (Exception $exception) {
            $logData['email_status'] = false;
            $logData['email_error'] = $exception->getMessage();
        }

        try {
            if (! is_null($attachment)) {
                file_delete($attachment);
                $logData['email_attachment_delete_status'] = true;
            }
        } catch (Exception $exception) {
            $logData['email_attachment_delete_status'] = false;
            $logData['email_attachment_delete_error'] = $exception->getMessage();
        }

        $logData['subject'] = $subject;
        $logData['email_to'] = $to_email;
        $logData['from_to'] = $from_email;

        appLog()->createLog($logData);

        return $mail_send_status;
    }
}
