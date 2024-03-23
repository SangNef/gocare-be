<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
*/

Route::get('/', function () {
    if (auth()->check()) {
        return redirect('/admin');
    }
    return redirect('/login');
});

/* ================== Homepage + AdminPanel Routes ================== */

require __DIR__.'/admin_routes.php';
Route::get('/esms-callback', 'HomeController@eSMSCallback')->name('esms-callback');
Route::get('/sendemail', 'HomeController@sendEmail')->name('sendmail');
Route::post('/viettel-callback/xac-nhan-thanh-toan', 'HomeController@confirmPaymentFromViettel');
Route::post('/viettel-callback/xac-nhan-ket-qua-thanh-toan', 'HomeController@pingbackPaymentFromViettel');
Route::post('/activated-callback', 'HomeController@activatedPingback');
Route::get('/vnpay-ipn', 'HomeController@vnpayIpn');
