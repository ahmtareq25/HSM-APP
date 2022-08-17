<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class PasswordReset extends BaseModel
{
    use HasFactory;

    protected $guarded = [];


    public function findByToken($token){
        return $this->query()
            ->where("token", $token)
            ->first();
    }

    public function deleteByEmail($email){

        return $this->query()
            ->where("email", $email)
            ->delete();
    }

    public function createData($data){
        return $this->create($data);
    }

    public function processForgotPassword($input_data){

        $error_code = "";
        $error_message = "";
        $token = "";

        if (empty($error_code)) {
            $this->deleteByEmail($input_data['email']);
        }

        $data = [
            'email' => $input_data['email'],
            'token' => generate_random_string(64),
            'created_at' => Carbon::now()
        ];

        if(empty($error_code)){
            $insert_reset_password = $this->createData($data);
            if (empty($insert_reset_password)) {
                $error_code = 500;
                $error_message = __("Token not created!");
            }
            $token = $insert_reset_password->token;
        }

        return [$error_code ,$error_message , $token];
    }

    public function processResetPassword($input_data , $user_id){
        $error_code = "";
        $error_message = "";

        if(empty($error_code)){
            $tokenData = $this->findByToken($input_data['token']);
            if (empty($tokenData)){
                $error_code = 404;
                $error_message = __("Token not found!");
            };
        }

        if(empty($error_code)){
            $data = [
                'password' => $input_data['password']
            ];
            $userObj = (new User())->updateUser($data, $user_id);
            if(empty($userObj)){
                $error_code = 404;
                $error_message = __("your password successfully not changed!, Please try again");
            }
        }

        if (empty($error_code)) {
            $this->deleteByEmail($input_data['email']);
        }

        return [$error_message , $error_message];
    }
}
