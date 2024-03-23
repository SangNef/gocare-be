<?php

Route::group(['middleware' => ['api']], function () {
    Route::group(['prefix' => 'orders'], function () {
        Route::group(['prefix' => 'draft', 'middleware' => ['auth.basic']], function () {
            Route::get('/', 'DraftOrdersController@index');
            Route::post('/', 'DraftOrdersController@store');
            Route::get('/{id}', 'DraftOrdersController@get');
            Route::delete('/{id}', 'DraftOrdersController@destroy');
            Route::post('/{id}/{seri}', 'DraftOrdersController@addProductToOrder');
            Route::delete('/{id}/{seri}', 'DraftOrdersController@removeProductFromOrder');
        });
    });
    Route::group(['middleware' => ['access']], function () {
        Route::group(['prefix' => 'az-order'], function () {
            Route::post('/admin-bank', 'AzOrderController@adminHandle');
            Route::post('/bank-noti', 'AzOrderController@notiHandle');
            Route::post('/update-noti', 'AzOrderController@updateByRequetId');
        });
        Route::group(['prefix' => 'device-order'], function () {
            Route::get('/suppliers-and-products', 'DeviceOrderController@getSuppliersAndProducts');
            Route::post('/sim-trading-order', 'DeviceOrderController@simTradingOrder');
        });
        Route::post('/{partner}/tracking-order', 'CODOrderController@trackingOrder');
    });
});
