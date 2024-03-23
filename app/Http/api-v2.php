<?php

Route::group(['middleware' => ['api']], function () {
    Route::post('/login', 'CustomerController@login');
    Route::get('/login/{provider}', 'CustomerController@redirectToProvider');
    Route::get('/login/{provider}/callback', 'CustomerController@providerCallback');
    Route::group(['prefix' => '/customer', 'middleware' => ['jwt.verify']], function () {
        Route::get('/reports/available', 'CustomerController@getCustomerAvailableReports');
        Route::get('/report/{report_id}/{area}', 'CustomerController@getCustomerReportDetail');
        Route::get('/report/{report_id}', 'CustomerController@getCustomerReport');
        Route::post('/sub-customers', 'CustomerController@createSubCustomer');
        Route::get('/get-sub-customers', 'CustomerController@getSubCustomer');
        Route::get('/get-otp', 'CustomerController@generateOTP');
        Route::get('/', 'CustomerController@getCurrentCustomer');
        Route::put('/update', 'CustomerController@update');
        Route::get('/logout', 'CustomerController@logout');
        Route::group(['prefix' => '/addresses'], function () {
            Route::get('/', 'AddressController@index');
            Route::post('/', 'AddressController@store');
            Route::put('/{id}', 'AddressController@update');
            Route::delete('/{id}', 'AddressController@delete');
        });
    });
    Route::get('/audits', 'AuditController@index');
    Route::get('/analytics', 'AuditController@analytics');
    Route::post('/orders', 'OrderController@store');
    Route::get('/payment-return', 'OrderController@processPayments');
    Route::get('/order-by-accesskey/{accessKey}', 'OrderController@getOrderByAccessKey');
    Route::get('/orders/payment/check', 'OrderController@getPaymentHistory');
    Route::group(['prefix' => '/orders', 'middleware' => ['api-v2', 'jwt.verify']], function () {
        //        Route::get('/payment/check', 'OrderController@getPaymentHistory');
        Route::post('/payment/init', 'OrderController@initPayment');
        Route::get('/payment/vnpay', 'OrderController@vnpayPayment');
        Route::get('/payment/process', 'OrderController@processPayment');
        Route::get('/payment/get-link/{id}', 'OrderController@getPaymentLink');
        Route::get('/processing/{id}/cancel', 'OrderController@cancel');
        Route::get('/shipping/{id}/cancel', 'OrderController@cancelOrder');
        Route::post('/shipping-options/{partner}', 'OrderController@getShippingOptions');
        Route::get('/', 'OrderController@index');
        Route::get('{type}/{id}', 'OrderController@getOrderById');
        Route::put('{type}/{id}', 'OrderController@update');
        Route::put('/delete-seri/{product_id}/{order_id}', 'OrderController@deleteSeriForOrder');
        Route::get('/list-seri', 'OrderController@getListSeri');
        Route::put('/add-seri/{product_id}/{order_id}/{seri_number}', 'OrderController@addSeriForOrder');
        Route::get('/get-sub-order/{id}', 'OrderController@getSubOrders');
    });
    Route::group(['prefix' => '/transfer-orders', 'middleware' => ['api-v2', 'jwt.verify']], function () {
        Route::get('/get-seri-histories', 'TransferOrderController@getProductSeriHistories');
        Route::get('/get-seris', 'TransferOrderController@getAvailableSeris');
        Route::post('/', 'TransferOrderController@store');
        Route::get('/', 'TransferOrderController@index');
        Route::get('/{id}', 'TransferOrderController@get');
    });
    Route::group(['prefix' => '/configs'], function () {
        Route::get('/homePage', 'ConfigController@homePage');
        Route::get('/sliders', 'ConfigController@homePageSlider');
        Route::get('/banks', 'ConfigController@getBanks');
        Route::get('/available-payments', 'ConfigController@getAvailablePaymentMethod');
    });
    Route::group(['prefix' => '/productcategories', 'middleware' => ['api-v2']], function () {
        Route::get('/', 'ProductCategoryController@index');
    });
    Route::group(['prefix' => '/serial', 'middleware' => ['api-v2', 'jwt.verify']], function () {
        Route::get('/', 'ProductSeriController@index');
    });
    Route::group(['prefix' => '/products'], function () {
        Route::get('/store-available', 'ProductController@getAvailableStores');
        Route::get('/quicksearch', 'ProductController@quickSearch');
        Route::get('/{id}/{seri}', 'ProductController@getProductBySeri');
        Route::post('/seri/{seri}/active', 'ProductController@activateWarrantyForSeri');
        Route::get('/{sku}', 'ProductController@getProductBySku');
        Route::get('/', 'ProductController@index');
    });
    Route::group(['prefix' => '/request-warranties'], function () {
        Route::get('/', 'RequestWarrantiesController@index');
        Route::post('/', 'RequestWarrantiesController@store');
        Route::get('/{id}', 'RequestWarrantiesController@show');
    });
    Route::group(['prefix' => '/address'], function () {
        Route::get('/provinces', 'AddressController@getProvinces');
        Route::get('/province/{id}/districts', 'AddressController@getDistrictsByProvince');
        Route::get('/district/{id}/wards', 'AddressController@getWardsByDistrict');
    });
    Route::group(['prefix' => '/pages'], function () {
        Route::get('/{slug}', 'PagesController@getPageBySlug');
        Route::get('/create', 'PagesController@create');
        Route::post('/store', 'PagesController@store');
        Route::get('{slug}/edit', 'PagesController@edit');
        Route::put('{slug}/update', 'PagesController@update');
    });
    Route::group(['prefix' => '/vouchers', 'middleware' => ['api-v2']], function () {
        Route::get('/', 'VouchersController@index');
    });
    Route::group(['prefix' => '/draws', 'middleware' => ['api-v2', 'jwt.verify']], function () {
        Route::get('/', 'ConfigController@getDraws');
        Route::get('/{id}', 'ConfigController@getDrawWinner');
    });
    Route::get('/pc', 'PostsController@getCates');
    Route::get('/posts', 'PostsController@index');
    Route::get('/posts/{id}', 'PostsController@getById');
});
