<?php

namespace App\Services;

use Exception;
use File;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AppLog
{
    public function errorHandlingLog($exception, $action = ''): void
    {
        if ($exception instanceof Exception) {
            $logData = [
                'action' => $action,
                'exception' => $exception->getMessage(),
                'code' => $exception->getCode(),
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
                'trace' => $exception->getTrace(),
                'exceptionTrace' => $exception->getTraceAsString(),
            ];
            $this->createLog($logData);
        }
    }

    public function createLog($logData): bool
    {
        try {
            $logData['auth_user'] = auth()->user();
            $json_data = safe_json_encode($logData);
            Log::info($json_data);
        } catch (Exception $e) {
            $status = false;
        }
        return $status ?? false;
    }

    public function enableGlobalQueryLog(): void
    {
        $query_log_file_path = storage_path('/logs/query.log');

        File::delete($query_log_file_path);

        DB::listen(function ($query) use ($query_log_file_path) {
            $actual_query = vsprintf(
                str_replace('?', '%s', $query->sql),
                array_map(
                    function ($binding) {
                        if($binding instanceof \DateTime) {
                            $binding = $binding->format('Y-m-d H:i:s');
                        }
                        $binding = addslashes($binding);
                        return is_numeric($binding) ? $binding : "'{$binding}'";
                    },
                    $query->bindings
                )
            );

            File::append(
                $query_log_file_path,
                safe_json_encode([
                    'query' => $actual_query,
                    'execution_time' => $query->time
                ]) . PHP_EOL
            );
        });
    }

    public static function unsetKeys($data){

        if (isset($data['action'])){
            $newArr['action'] = $data['action'];
            $data = array_merge($newArr,$data);
        }

        $removeKeys = array('expiry',  'cvv', 'client_secret', 'pan');

        foreach ($removeKeys as $item){
            self::removeKey($data, $item);
        }
        return $data;
    }

    private static function removeKey(&$array, $key)
    {
        if (is_array($array))
        {
            if (isset($array[$key]))
            {
                if ($key == "pan"){

                    $array[$key] = cardMasking($array[$key]);
                }else{

                    unset($array[$key]);
                }

            }
            if (count($array) > 0)
            {
                foreach ($array as $k => $arr)
                {
                    self::removeKey($array[$k], $key);
                }
            }
        }
    }


}
