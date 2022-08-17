<?php

namespace App\Http\Base\Controllers\Auth;


use App\Http\Base\Requests\ResetPasswordRequest;
use App\Http\Controllers\Controller;
use App\Models\PasswordReset;
use App\Models\User;
use App\Services\SendEmail;

class ResetPasswordController extends Controller
{
    public function resetPassword(ResetPasswordRequest $request)
    {

        $log_action = 'RESET_PASSWORD_DATA';

        try {
            $error_code = "";
            $error_message = "";

            $input_data = $request->all();
            $user = getResource('users');
            if(empty($user)){
                $error_code = 404;
                $error_message = __("User Not Found!");
            }

            if (empty($error_code)) {
                list($error_code, $error_message) = (new PasswordReset())->processResetPassword($input_data, $user->id);
            }

            if (empty($error_code)) {
                $this->sendResetPasswordNotification($user);
            }

            $logData = [
                'action' => $log_action,
                'error_code' => $error_code,
                'error_message' => $error_message,
            ];
            appLog()->createLog($logData);

            if(empty($error_code)){
                return appResponse()->success(config('status_code.success'), 'Success', __("your password successfully changed"));
            }
            return appResponse()->failed($error_code, 'failed', $error_message);

        } catch (\Exception $e) {
            appLog()->errorHandlingLog($e, $log_action);
            return appResponse()->failed(config('status_code.server_error'), 'failed', $e->getMessage());
        }
    }

    public function sendResetPasswordNotification($user): bool
    {
        $data['name'] = $user->name;
        $data['email'] = $user->email;
        $template = 'admin_password_reset.password_reset_email';
        $from = config('app.SYSTEM_NO_REPLY_ADDRESS');
        $subject = __("Your password successfully changed");
        return (new SendEmail())->sendEmail($user->email, $from, $subject, $template, $data);
    }
}
