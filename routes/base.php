<?php

use App\Http\Base\Controllers\Auth\ForgotPasswordController;
use App\Http\Base\Controllers\Auth\ResetPasswordController;
use App\Http\Base\Controllers\AuthController;
use App\Http\Base\Controllers\RolesController;
use App\Http\Base\Controllers\UsersController;
use Illuminate\Support\Facades\Route;
use App\Http\Base\Controllers\RolePageController;
use App\Http\Base\Controllers\UserGroupController;
use App\Http\Base\Controllers\UserGroupRolesController;

Route::controller(AuthController::class)
    ->group(function () {
        Route::post('login', 'logIn');
        Route::post('resend-otp', 'resendOTP');
        Route::post('verify-otp', 'verifyOTP');
        Route::post('renew-auth-token', 'renewAuthToken');
    });

Route::controller(ForgotPasswordController::class)
    ->prefix('forget-password')
    ->group(function () {
        Route::post('', 'forgetPassword')->name('forget-password.forgetpassword');
    });

Route::controller(ResetPasswordController::class)
    ->prefix('reset-password')
    ->group(function () {
        Route::post('', 'resetPassword')->name('reset-password.resetpassword');
    });

Route::middleware(['auth:api', 'permission'])
    ->group(function () {

        Route::controller(UsersController::class)
            ->prefix('users')
            ->name('users.')
            ->group(function () {
                Route::get('', 'index')->name('index');
                Route::post('create', 'create')->name('create');
                Route::put('{user_id}/update', 'update')->name('update');
                Route::delete('{user_id}/delete', 'destroy')->name('destroy');
            });

        Route::controller(RolesController::class)
            ->prefix('roles')
            ->group(function () {
                Route::get('', 'index')->name('roles.index');
                Route::post('create', 'create')->name('roles.create');
                Route::put('{role_id}/update', 'update')->name('roles.update');
                Route::delete('{role_id}/delete', 'destroy')->name('roles.destroy');
            });

        //user group roles
        Route::controller(UserGroupRolesController::class)
            ->prefix('user-group-roles')
            ->group(function () {
                Route::post('{id}/list', 'list')->name('user-group-roles.list');
                Route::post('{id}/update', 'update')->name('user-group-roles.update');
            });

        //user groups
        Route::controller(UserGroupController::class)
            ->prefix('user-group')
            ->group(function () {
                Route::get('', 'index')->name('user-group.index');
                Route::post('create', 'create')->name('user-group.create');
                Route::get('{id}/show', 'show')->name('user-group.show');
                Route::put('{id}/update', 'update')->name('user-group.update');
                Route::delete('{id}/delete', 'destroy')->name('user-group.destroy');
            });

        //role pages
        Route::controller(RolePageController::class)
            ->prefix('role-pages')
            ->group(function () {
                Route::post('{id}/list', 'list')->name('role-pages.list');
                Route::post('update', 'update')->name('role-pages.update');
            });
    });
