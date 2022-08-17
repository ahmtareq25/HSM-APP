<?php

namespace App\Http\CardManagement;

class HSM
{
    const  HOST = '124.6.250.148';
    const  PORT = '44444';
    const PRE_SHARED_KEY = '50262C46168A96BAB8D6A2567244E048';


    const COMMAND_BB = "BB";
    const COMMAND_BA = "BA";
    const COMMAND_NG = "NG";

    private $socket;


    public function __construct()
    {
        $this->socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
        socket_connect($this->socket, self::HOST, self::PORT);
    }

    private function runCommand($command)
    {
        socket_write($this->socket, $command, strlen($command));
        $response = socket_read($this->socket, 1024);
        return $response;
    }

    function hex2str($hex) {
        $str = '';
        for($i=0;$i<strlen($hex);$i+=2) $str .= chr(hexdec(substr($hex,$i,2)));
        return $str;
    }

    public function sendHSMCommand($command)
    {
       // $command = "ABCDBA900000000012F123412341234";
        $res = dechex(strlen($command));

        do{
            $res = "0".$res;
        }while(strlen($res) < 4);

        $newVal = $this->hex2str($res).$command;
        $response = $this->runCommand($newVal);
        $response = preg_replace("~[^a-z0-9:]~i", "", $response);
        return $response;
    }



}
