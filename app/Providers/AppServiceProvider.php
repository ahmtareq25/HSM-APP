<?php

namespace App\Providers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\ServiceProvider;
use Spatie\Activitylog\Models\Activity;

class AppServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton('appResponse', function ($app) {
            return new \App\Services\AppResponse();
        });

        $this->app->singleton('appLog', function ($app) {
            return new \App\Services\AppLog();
        });
    }

    public function boot()
    {
        if (config('logging.enable_query_log')) {
            appLog()->enableGlobalQueryLog();
        }

        if (app()->environment(['local'])) {
            Model::preventsLazyLoading();
        }

    }
}
