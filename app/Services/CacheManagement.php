<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;

class CacheManagement
{
    const PERMISSION_GROUP_NAME = 'PERMISSION_GROUP_NAME';

    public static function setSystemCache($key, $value, $time_in_sec = 0){
        if (empty($time_in_sec)){
            Cache::put($key, $value);
        }else{
            Cache::put($key, $value, $time_in_sec);
        }

    }

    public static function getSystemCache($key){
        return Cache::get($key);
    }

    public static function unsetSystemCache($key){
        Cache::forget($key);
    }

    public static function hasSystemCache($key){
        return Cache::has($key);
    }

    public static function setOrGetSystemCacheForever($key, $callback){
        return Cache::get($key, $callback);
    }

}
