<?php

namespace App\Services;

class StringManagement
{
    public static function generateUniqueKey($unique_string = ''){
        $token = "";
        $length = 32;
        try {
            $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
            $charactersLength = strlen($characters);
            $randomString = '';
            for ($i = 0; $i < $length; $i++) {
                $randomString .= $characters[rand(0, $charactersLength - 1)];
            }

            $token = md5($unique_string . time() . $randomString);
        } catch (\Exception $ex) {

        }

        return $token;
    }

    public static function generateUniqueInteger($length = 10, int $postfix = null)
    {
        $min = 1 . str_repeat(0, $length - 1);
        $max = str_repeat(9, $length);
        $unique_integer = mt_rand($min, $max);
        if (!is_null($postfix)) {
            $postfix_length = strlen($postfix);
            $unique_integer = substr_replace($unique_integer, $postfix, - $postfix_length);
        }
        return (int)$unique_integer;
    }

}
