<?php

namespace App\Http\Base\Controllers\Auth;

use App\Http\Base\Requests\ForgotPasswordRequest;
use App\Http\Controllers\Controller;
use App\Models\PasswordReset;
use App\Models\User;
use App\Services\SendEmail;


class ForgotPasswordController extends Controller
{
    public function forgetPassword(ForgotPasswordRequest $request){
        $log_action = 'FORGOT_PASSWORD_DATA';
        try {
            $input_data = $request->all();
            $error_code = "";
            $error_message = "";
            $token = "";

            $user = getResource('users');
            if (empty($user)) {
                $error_code = 404;
                $error_message = __("User Not found");
            }

            if (empty($error_code)) {
                list($error_code, $error_message, $token) = (new PasswordReset())->processForgotPassword($input_data);
            }

            if (empty($error_code)) {
                $this->sendForgotPasswordNotification($user , $token);
            }

            $logData = [
                'action' => $log_action,
                'error_code' => $error_code,
                'error_message' => $error_message,
            ];
            appLog()->createLog($logData);

            if(empty($error_code)){
                return appResponse()->success(config('status_code.success'), 'Success', __("A reset link has been sent to your email address."));
            }
            return appResponse()->failed($error_code, 'failed', $error_message);

        } catch (\Exception $e){
            appLog()->errorHandlingLog($e, $log_action);
            return appResponse()->failed(config('status_code.server_error'), 'failed', $e->getMessage());
        }
    }

    public function sendForgotPasswordNotification($user, $token): bool
    {
        $data['name'] = $user->name;
        $data['token'] = $token;
        $data['reset_url'] = config('app.url') . '/password/reset/' . $token;
        $template = 'admin_forget_password.admin_forget_password';
        $from = config('app.SYSTEM_NO_REPLY_ADDRESS');
        $subject = __("Reset your password");
        return (new SendEmail())->sendEmail($user->email, $from, $subject, $template, $data);
    }
}
