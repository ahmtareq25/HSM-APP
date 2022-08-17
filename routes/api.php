<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

/*Route::post('/hsm/test', function (Request $request) {
    return (new \App\Http\CardManagement\HSM())->sendHSMCommand();
    dd($result);
    $value = "123412341234";
    $cardInformation = new \App\Http\CardManagement\CardInformation();
    $encrypted_value = $cardInformation->ecrypt($value);
    $decrypted_plain_data = $cardInformation->decrypt($encrypted_value);
    echo "Before Encryption: ".$value."<br>";
    echo "<br>";
    echo "Encryption: ".$encrypted_value;
    echo "<br>";
    echo "<br>";
    echo "After decryption: ".$decrypted_plain_data;
    exit;
});*/

Route::group(['middleware' => 'CardMiddleware'], function () {

    Route::post('generate-card-token', [App\Http\CardManagement\Controllers\ApiController::class, 'generateToken']);
    Route::post('get-card-info', [App\Http\CardManagement\Controllers\ApiController::class, 'getCardInfo']);
    Route::post('update-card-info', [App\Http\CardManagement\Controllers\ApiController::class, 'updateCardInfo'])->name('updateCardInfoByToken');
    Route::post('delete-card-info', [App\Http\CardManagement\Controllers\ApiController::class, 'deleteCardInfo'])->name('deleteCardInfoByToken');
});

