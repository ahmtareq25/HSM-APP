<?php

namespace App\Services;

use Exception;
use File;

class OTPService
{
    private string $otp_file_name = 'otp.txt';

    private bool $is_enable_otp_to_mail = false;
    private bool $is_enable_otp_to_sms = false;
    private bool $is_enable_otp_to_file = false;

    private int|string $identifier;
    private string $to_email;
    private null|string $from_email;
    private null|string $to_phone;
    private string $purpose;

    public bool $is_success = false;
    public string $status_message;
    public mixed $status_data;

    public function __construct(int|string $key_postfix, null|string $to_email = '', null|string $to_phone = '')
    {
        $this->identifier = $key_postfix;
        $this->to_email = $to_email;
        $this->from_email = config('app.SYSTEM_NO_REPLY_ADDRESS');
        $this->to_phone = $to_phone;

        // May be need later
        $this->is_enable_otp_to_file = true;
        $this->is_enable_otp_to_mail = true;
        $this->is_enable_otp_to_sms = true;
    }

    public function processOTP(string $purpose): bool
    {
        try {
            $this->purpose = $purpose;
            $otp = $this->generateNewOTP();

            if ($this->is_enable_otp_to_file) {
                $this->writeOTPToFile($otp);
            }

            if ($this->is_enable_otp_to_mail) {
                $this->sendOTPToMail($otp);
            }

            if ($this->is_enable_otp_to_sms) {
                $this->sendOTPToSMS($otp);
            }

            return true;
        } catch (Exception $exception) {
            appLog()->createLog([
                'action' => 'OTP_SEND_FAILED_WITH_EXCEPTION',
                'exception' => $exception
            ]);
        }
        return false;
    }

    public function matchOTP(int|string $OTP): bool
    {
        $match_status = CacheManagement::getSystemCache($this->OTPKey()) == $OTP;

        if ($match_status) {
            CacheManagement::unsetSystemCache($this->OTPKey());
        }

        return $match_status;
    }

    private function generateNewOTP(): int
    {
        $OTP = rand(100000, 999999);
        CacheManagement::setSystemCache(
            $this->OTPKey(),
            $OTP,
            now()->addMinutes(config('auth.otp_lifetime'))
        );
        return $OTP;
    }

    private function OTPKey(): string
    {
        return "OTP_for_{$this->identifier}";
    }

    private function writeOTPToFile(int|string $otp): void
    {
        try {
            $folder = 'files/';
            $file_path = public_path($folder . $this->otp_file_name);

            if (! File::exists($file_path)) {
                File::makeDirectory($folder, 0777);
            }

            File::put(
                $file_path,
                $this->purpose . ': ' . $otp . PHP_EOL
            );
        } catch (Exception $exception) {
            appLog()->errorHandlingLog($exception, 'Failed to write OTP on file.');
        }
    }

    private function sendOTPToMail($otp): void
    {
        $data = [
            'otp' => $otp
        ];
        $template = 'OTP.otp_for_token';
        $subject = __($this->purpose);

        (new SendEmail())->sendEmail($this->to_email, $this->from_email, $subject, $template, $data);
    }

    private function sendOTPToSMS($otp): void
    {
        // TODO:: Write code for send otp to SMS
    }
}
