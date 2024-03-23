<?php

/* ================== Homepage ================== */
Route::get('/', function () {
    if (auth()->check()) {
        return redirect('/admin');
    }
    return redirect('/login');
});

Route::auth();
Route::get(config('laraadmin.adminRoute') . '/change-language/{lang}', function ($lang) {
	\Illuminate\Support\Facades\Session::put('lang', $lang);
	return back();
})->name('change-lang');

/* ================== Access Uploaded Files ================== */
Route::get('files/{hash}/{name}', 'LA\UploadsController@get_file');
/*
|--------------------------------------------------------------------------
| AdminPanel Application Routes
|--------------------------------------------------------------------------
*/

$as = "";
if (\Dwij\Laraadmin\Helpers\LAHelper::laravel_ver() == 5.3) {
	$as = config('laraadmin.adminRoute') . '.';

	// Routes for Laravel 5.3
	Route::get('/logout', 'Auth\LoginController@logout');
}

Route::group(['as' => $as, 'middleware' => ['auth', 'permission:ADMIN_PANEL', 'localization']], function () {
	Route::group(['middleware' => ['role:STORE_OWNER']], function () {
        Route::get(config('laraadmin.adminRoute') . '/custom-modules', 'LA\CustomModuleController@index');
        Route::get(config('laraadmin.adminRoute') . '/custom-modules/{id}', 'LA\CustomModuleController@show');
        Route::post(config('laraadmin.adminRoute') . '/save_custom_role_module_permissions/{id}', 'LA\CustomModuleController@save_role_module_permissions');
    });
	

	Route::get(config('laraadmin.adminRoute') . '/ajax-select', 'LA\ModelController@index')->name('ajax-select');

	/* ================== Dashboard ================== */

	Route::get(config('laraadmin.adminRoute'), 'LA\DashboardController@index');
	Route::get(config('laraadmin.adminRoute') . '/dashboard', 'LA\DashboardController@index');

	/* ================== Users ================== */
	Route::resource(config('laraadmin.adminRoute') . '/users', 'LA\UsersController');
	Route::get(config('laraadmin.adminRoute') . '/user_dt_ajax', 'LA\UsersController@dtajax');

	/* ================== Uploads ================== */
	Route::resource(config('laraadmin.adminRoute') . '/uploads', 'LA\UploadsController');
	Route::post(config('laraadmin.adminRoute') . '/upload_files', 'LA\UploadsController@upload_files');
	Route::get(config('laraadmin.adminRoute') . '/uploaded_files', 'LA\UploadsController@uploaded_files');
	Route::post(config('laraadmin.adminRoute') . '/uploads_update_caption', 'LA\UploadsController@update_caption');
	Route::post(config('laraadmin.adminRoute') . '/uploads_update_filename', 'LA\UploadsController@update_filename');
	Route::post(config('laraadmin.adminRoute') . '/uploads_update_public', 'LA\UploadsController@update_public');
	Route::post(config('laraadmin.adminRoute') . '/uploads_delete_file', 'LA\UploadsController@delete_file');

	/* ================== Roles ================== */
	Route::resource(config('laraadmin.adminRoute') . '/roles', 'LA\RolesController');
	Route::get(config('laraadmin.adminRoute') . '/role_dt_ajax', 'LA\RolesController@dtajax');
	Route::post(config('laraadmin.adminRoute') . '/save_module_role_permissions/{id}', 'LA\RolesController@save_module_role_permissions');

	/* ================== Permissions ================== */
	Route::resource(config('laraadmin.adminRoute') . '/permissions', 'LA\PermissionsController');
	Route::get(config('laraadmin.adminRoute') . '/permission_dt_ajax', 'LA\PermissionsController@dtajax');
	Route::post(config('laraadmin.adminRoute') . '/save_permissions/{id}', 'LA\PermissionsController@save_permissions');

	/* ================== Departments ================== */
	Route::resource(config('laraadmin.adminRoute') . '/departments', 'LA\DepartmentsController');
	Route::get(config('laraadmin.adminRoute') . '/department_dt_ajax', 'LA\DepartmentsController@dtajax');

	/* ================== Employees ================== */
	Route::resource(config('laraadmin.adminRoute') . '/employees', 'LA\EmployeesController');
	Route::get(config('laraadmin.adminRoute') . '/employee_dt_ajax', 'LA\EmployeesController@dtajax');
	Route::post(config('laraadmin.adminRoute') . '/change_password/{id}', 'LA\EmployeesController@change_password');

	/* ================== Organizations ================== */
	Route::resource(config('laraadmin.adminRoute') . '/organizations', 'LA\OrganizationsController');
	Route::get(config('laraadmin.adminRoute') . '/organization_dt_ajax', 'LA\OrganizationsController@dtajax');

	/* ================== Backups ================== */
	Route::resource(config('laraadmin.adminRoute') . '/backups', 'LA\BackupsController');
	Route::get(config('laraadmin.adminRoute') . '/backup_dt_ajax', 'LA\BackupsController@dtajax');
	Route::post(config('laraadmin.adminRoute') . '/create_backup_ajax', 'LA\BackupsController@create_backup_ajax');
	Route::get(config('laraadmin.adminRoute') . '/downloadBackup/{id}', 'LA\BackupsController@downloadBackup');

	/* ================== Customers ================== */
    Route::get(config('laraadmin.adminRoute') . '/customers/{id}/report/{reportId}', 'LA\CustomersController@acceptReport')->name('customer.report.accept');
	Route::get(config('laraadmin.adminRoute') . '/customers/{id}/sub-customers', 'LA\CustomersController@getSubCustomers')->name('customers.sub');
	Route::get(config('laraadmin.adminRoute') . '/customers/{id}/store', 'LA\CustomersController@getStoreCustomer');
    Route::get(config('laraadmin.adminRoute') . '/customers/{id}/group-discount', 'LA\CustomersController@getGroupDiscount');
	Route::get(config('laraadmin.adminRoute') . '/customers/search-username', 'LA\CustomersController@getCustomerByUsername');
	Route::resource(config('laraadmin.adminRoute') . '/customers', 'LA\CustomersController');
	Route::get(config('laraadmin.adminRoute') . '/customer_dt_ajax', 'LA\CustomersController@dtajax');
	Route::get(config('laraadmin.adminRoute') . '/get-address', 'LA\CustomersController@getAddress')->name('customer.get-address');
	Route::get(config('laraadmin.adminRoute') . '/export-customer', 'LA\CustomersController@export')->name('customer.export');
	Route::post(config('laraadmin.adminRoute') . '/create-customer', 'LA\CustomersController@createUserFromOrder')->name('customer.create-user-from-order');

	/* ================== Groups ================== */
    Route::get(config('laraadmin.adminRoute') . '/groups/discount/delete', 'LA\GroupsController@deleteDiscount')->name('group.discount.delete');
    Route::post(config('laraadmin.adminRoute') . '/groups/discount', 'LA\GroupsController@updateDiscountQuantity')->name('group.discount.add');
	Route::resource(config('laraadmin.adminRoute') . '/groups', 'LA\GroupsController');
	Route::get(config('laraadmin.adminRoute') . '/group_dt_ajax', 'LA\GroupsController@dtajax');

	/* ================== Banks ================== */
	Route::resource(config('laraadmin.adminRoute') . '/banks', 'LA\BanksController');
	Route::get(config('laraadmin.adminRoute') . '/bank_dt_ajax', 'LA\BanksController@dtajax');

	/* ================== ProductCategories ================== */
	Route::resource(config('laraadmin.adminRoute') . '/productcategories', 'LA\ProductCategoriesController');
	Route::get(config('laraadmin.adminRoute') . '/productcategory_dt_ajax', 'LA\ProductCategoriesController@dtajax');

	/* ================== Products ================== */
    Route::post(config('laraadmin.adminRoute') . '/products/import-seri', 'LA\ProductsController@importSeri')->name('products.seri.import');
	Route::get(config('laraadmin.adminRoute') . '/products/seri-info', 'LA\ProductsController@getSeriInfo')->name('products.seri.get');
	Route::get(config('laraadmin.adminRoute') . '/products/{id}/attribute/save', 'LA\ProductsController@saveAttributes')->name('products.attribute.save');
	Route::get(config('laraadmin.adminRoute') . '/products/{id}/attribute-value/save', 'LA\ProductsController@saveAttributeValues')->name('products.attribute-value.save');
	Route::post(config('laraadmin.adminRoute') . '/products/{id}/attribute-value/seri/save', 'LA\ProductsController@saveSeriForGroupAttributeValues')->name('products.attribute-value.seri.save');
	Route::post(config('laraadmin.adminRoute') . '/products/{id}/attribute-value/media/save', 'LA\ProductsController@saveGroupAttributeValues')->name('products.attribute-value.media.save');
	Route::get(config('laraadmin.adminRoute') . '/products/{id}/attribute-value/media', 'LA\ProductsController@getAttributeValues')->name('products.attribute-value.media.get');
	Route::get(config('laraadmin.adminRoute') . '/products/{id}/attribute', 'LA\ProductsController@attributes')->name('products.attribute');
	Route::post(config('laraadmin.adminRoute') . '/products/reorder-position', 'LA\ProductsController@reorderPosition')->name('products.reorder-position');
	Route::get(config('laraadmin.adminRoute') . '/products/export', 'LA\ProductsController@export')->name('products.export');
	Route::get(config('laraadmin.adminRoute') . '/products/add-related-product/{id}', 'LA\ProductsController@addRelatedProduct')->name('products.related.add');
	Route::get(config('laraadmin.adminRoute') . '/products/delete-related-product/{id}', 'LA\ProductsController@deleteRelatedProduct')->name('products.related.delete');
	Route::get(config('laraadmin.adminRoute') . '/products/get', 'LA\ProductsController@get')->name('products.get');
	Route::get(config('laraadmin.adminRoute') . '/products/get-product', 'LA\ProductsController@getProduct')->name('products.get.id');
	Route::get(config('laraadmin.adminRoute') . '/products/remove-saved-price', 'LA\ProductsController@deleteSavedPrice')->name('products.delete.savedprice');
	Route::get(config('laraadmin.adminRoute') . '/products/{productId}/load-series', 'LA\ProductsController@loadSeries')->name('products.load-series');
	Route::resource(config('laraadmin.adminRoute') . '/products', 'LA\ProductsController');
	Route::get(config('laraadmin.adminRoute') . '/product_dt_ajax', 'LA\ProductsController@dtajax');
	Route::get(config('laraadmin.adminRoute') . '/product-series-print/{productId}', 'LA\ProductsController@printProductSeries')->name('product.series-print');
	Route::get(config('laraadmin.adminRoute') . '/product-series/update-status/{productId}', 'LA\ProductsController@updateStatusProductSeries')->name('product.series-update-status');
	Route::post(config('laraadmin.adminRoute') . '/extra-series', 'LA\ProductsController@extraSeries')->name('product.extra-series');
	Route::get(config('laraadmin.adminRoute') . '/product-series/delete/{productId}', 'LA\ProductsController@deleteSeries')->name('product.delete-series');

	/* ================== Orders ================== */
	Route::get(config('laraadmin.adminRoute') . '/orders/get-product-list-seri', 'LA\OrdersController@orderProductSelectedListSeri')->name('orders.get.list.seri');
	Route::get(config('laraadmin.adminRoute') . '/orders/get-product-seri', 'LA\OrdersController@orderProductSelectedSeri')->name('orders.get.seri');
	Route::post(config('laraadmin.adminRoute') . '/orders/switch-product', 'LA\OrdersController@switchProduct')->name('order.switchProduct.create');
	Route::get(config('laraadmin.adminRoute') . '/orders/{id}/create-refund-order', 'LA\OrdersController@createRefundOrder');
	Route::get(config('laraadmin.adminRoute') . '/orders/{id}/cancel', 'LA\OrdersController@cancelOrder')->name('order.cancel');
	Route::post(config('laraadmin.adminRoute') . '/orders/{id}/update-status/', 'LA\OrdersController@updateStatus')->name('order.status.update');
	Route::get(config('laraadmin.adminRoute') . '/orders/{id}/print', 'LA\OrdersController@printOrder')->name('order.print');
	Route::post(config('laraadmin.adminRoute') . '/orders/print-transport', 'LA\OrdersController@printTransport')->name('order.print-transport');
	Route::get(config('laraadmin.adminRoute') . '/orders/print-orders', 'LA\OrdersController@printOrders')->name('order.print-orders');
	Route::get(config('laraadmin.adminRoute') . '/orders/fetch-draft-order/{id}', 'LA\OrdersController@fetchDraftOrder')->name('order.fetch-draft-order');
	Route::resource(config('laraadmin.adminRoute') . '/orders', 'LA\OrdersController');
	Route::resource(config('laraadmin.adminRoute') . '/orders', 'LA\OrdersController');
	Route::get(config('laraadmin.adminRoute') . '/order_dt_ajax', 'LA\OrdersController@dtajax');
	Route::get(config('laraadmin.adminRoute') . '/approve-order', 'LA\OrdersController@approve')->name('order.approve');
	Route::get(config('laraadmin.adminRoute') . '/get-customer', 'LA\OrdersController@getCustomer')->name('orderproduct.get-customer');
	Route::get(config('laraadmin.adminRoute') . '/modify-price', 'LA\OrdersController@modifyPrice')->name('order.modify-price');

	/* ================== OrderProducts ================== */
	Route::resource(config('laraadmin.adminRoute') . '/orderproducts', 'LA\OrderProductsController');
	Route::get(config('laraadmin.adminRoute') . '/orderproduct_dt_ajax', 'LA\OrderProductsController@dtajax');

	/* ================== Transactions ================== */
	Route::resource(config('laraadmin.adminRoute') . '/transactions', 'LA\TransactionsController');
	Route::get(config('laraadmin.adminRoute') . '/transaction_dt_ajax', 'LA\TransactionsController@dtajax');
	Route::post(config('laraadmin.adminRoute') . '/transfer', 'LA\TransactionsController@moneyTransfer');
	Route::get(config('laraadmin.adminRoute') . '/approve-transaction', 'LA\TransactionsController@approve');

	/* ================== Configs ================== */
	Route::get(config('laraadmin.adminRoute') . '/configs/azpro-configs', 'LA\ConfigsController@azproConfig')->name('configs.azpro');
	Route::resource(config('laraadmin.adminRoute') . '/configs', 'LA\ConfigsController');
	Route::get(config('laraadmin.adminRoute') . '/config_dt_ajax', 'LA\ConfigsController@dtajax');

	Route::get(config('laraadmin.adminRoute') . '/qr-code/order/{id}', 'LA\QRCodeController@showQrCodeForOrder')->name('qrcode.order');
	Route::get(config('laraadmin.adminRoute') . '/qr-code/warrantyorder/{id}', 'LA\QRCodeController@showQrCodeForWarrantyOrder')->name('qrcode.warrantyorder');
	Route::get(config('laraadmin.adminRoute') . '/qr-code/shipping-order/{id}', 'LA\QRCodeController@showQrCodeForShippingOrder')->name('qrcode.shipping_order');

	/* ================== Settings ================== */
	Route::resource(config('laraadmin.adminRoute') . '/settings', 'LA\SettingsController');
	Route::get(config('laraadmin.adminRoute') . '/setting_dt_ajax', 'LA\SettingsController@dtajax');

	/* ================== AccessTokens ================== */
	Route::resource(config('laraadmin.adminRoute') . '/accesstokens', 'LA\AccessTokensController');
	Route::get(config('laraadmin.adminRoute') . '/accesstoken_dt_ajax', 'LA\AccessTokensController@dtajax');

	/* ================== TransactionHistories ================== */
	Route::resource(config('laraadmin.adminRoute') . '/transactionhistories', 'LA\TransactionhistoriesController');
	Route::get(config('laraadmin.adminRoute') . '/transactionhistory_dt_ajax', 'LA\TransactionhistoriesController@dtajax');


	Route::get(config('laraadmin.adminRoute') . '/dashboard/customers/export', 'LA\DashboardController@exportCustomers');
	Route::get(config('laraadmin.adminRoute') . '/dashboard/customers', 'LA\DashboardController@customers');
	Route::get(config('laraadmin.adminRoute') . '/dashboard/report', 'LA\DashboardController@report');

	/* ================== Smssents ================== */
	Route::resource(config('laraadmin.adminRoute') . '/smssents', 'LA\SmssentsController');
	Route::get(config('laraadmin.adminRoute') . '/smssent_dt_ajax', 'LA\SmssentsController@dtajax');

	Route::group(['prefix' => config('laraadmin.adminRoute') . '/cod-orders', 'namespace' => 'LA'], function () {
		Route::get('/partner-inventories/{type}/{orderId}', 'CODOrdersController@getPartnerInventory')->name('co.get-inventories');
		Route::get('/{type}', 'CODOrdersController@index')->name('co.index');
		Route::get('/{type}/dt_ajax', 'CODOrdersController@dtajax');
		Route::get('/{type}/{id}/edit', 'CODOrdersController@edit')->name('co.edit');
		Route::put('/{type}/{id}', 'CODOrdersController@update')->name('co.update');
		Route::get('/{type}/check-bills', 'CODOrdersController@checkBills')->name('co.check-bills');
		Route::get('/{type}/get-money', 'CODOrdersController@getMoney')->name('co.get-money');
		Route::post('/{type}/compare/update-bank-balance', 'CODOrdersController@updateBankBalance')->name('co.update-bank-balance');
		Route::post('/{type}/{code}/update-status', 'CODOrdersController@updateProviderStatus')->name('co.update-provider-status');
		// Create Bill of lading
		Route::get('get-address/{partner}', 'CODOrdersController@getAddress')->name('co.get-address');
		Route::get('get-price/{partner}', 'CODOrdersController@getPrice')->name('co.get-price');
		Route::get('ghn/get-services', 'CODOrdersController@ghnGetServices')->name('co.ghn-services');
		Route::post('create-bill/{id}/{partner}', 'CODOrdersController@createBillLading')->name('co.create-bill');
		Route::post('create-bill-warranty/{id}/{partner}', 'CODOrdersController@createBillLadingForWarrantyOrder')->name('co.create-bill-warranty');
		// Fake bill
		Route::post('/fake-bill/{orderId}', 'CODOrdersController@fakeBill')->name('co.fake-bill');
    });
    
	/* ================== ShippingOrders ================== */
	Route::get(config('laraadmin.adminRoute') . '/cod-orders-shipping/search-order', 'LA\CODOrdersShippingController@searchOrder')->name('cos.search');
	Route::get(config('laraadmin.adminRoute') . '/cod-orders-shipping/validate-codes', 'LA\CODOrdersShippingController@checkCODCode')->name('cos.check-code');
	Route::get(config('laraadmin.adminRoute') . '/cod-orders-shipping/get-order', 'LA\CODOrdersShippingController@getOrder')->name('cos.get.id');
	Route::get(config('laraadmin.adminRoute') . '/cod-orders-shipping/{id}/print', 'LA\CODOrdersShippingController@print')->name('cos.print');
	Route::resource(config('laraadmin.adminRoute') . '/cod-orders-shipping', 'LA\CODOrdersShippingController');
	Route::get(config('laraadmin.adminRoute') . '/cod_orders_shipping_dt_ajax', 'LA\CODOrdersShippingController@dtajax');

	/* ================== RequestWarranties ================== */
	Route::resource(config('laraadmin.adminRoute') . '/request-warranties', 'LA\RequestWarrantiesController');
	Route::get(config('laraadmin.adminRoute') . '/request_warranties_dt_ajax', 'LA\RequestWarrantiesController@dtajax');


	/* ================== Pages ================== */
	Route::resource(config('laraadmin.adminRoute') . '/pages', 'LA\PagesController');
	Route::get(config('laraadmin.adminRoute') . '/page_dt_ajax', 'LA\PagesController@dtajax');

	/* ================== Stores ================== */
	Route::get(config('laraadmin.adminRoute') . '/stores/{id}/group-attribute-extra', 'LA\StoresController@getGroupAttributeExtra')->name('store.product.group-attribute-extra');
	Route::post(config('laraadmin.adminRoute') . '/stores/{id}/group-attribute-extra', 'LA\StoresController@saveGroupAttributeExtra')->name('store.product.group-attribute-extra.save');
	Route::get(config('laraadmin.adminRoute') . '/stores/{id}/product-price', 'LA\StoresController@getProductPrice')->name('store.product.product-price');
	Route::post(config('laraadmin.adminRoute') . '/stores/{id}/product-price', 'LA\StoresController@saveProductPrice')->name('store.product.product-price.save');
	Route::post(config('laraadmin.adminRoute') . '/stores/{id}/setting', 'LA\StoresController@setting')->name('store.product.setting');
	Route::post(config('laraadmin.adminRoute') . '/stores/{id}/sharing', 'LA\StoresController@saveSharing')->name('store.sharing');
	Route::post(config('laraadmin.adminRoute') . '/stores/{id}/product-minimum', 'LA\StoresController@updateProductMinimum')->name('store.product.minimum');
	Route::get(config('laraadmin.adminRoute') . '/stores/{id}/observes', 'LA\StoresController@getObserves')->name('store.observes.get');
	Route::get(config('laraadmin.adminRoute') . '/stores/{id}/observes-audit', 'LA\StoresController@getObserverAudit')->name('store.observes.audit.get');
	Route::post(config('laraadmin.adminRoute') . '/stores/{id}/add-observer', 'LA\StoresController@addObserver')->name('store.observer.add');
	Route::get(config('laraadmin.adminRoute') . '/stores/{id}/remove-observer', 'LA\StoresController@removeObserver')->name('store.observer.remove');
	Route::get(config('laraadmin.adminRoute') . '/stores/{id}/products', 'LA\StoresController@getProducts')->name('store.products.get');
	Route::get(config('laraadmin.adminRoute') . '/stores/{id}/export-products', 'LA\StoresController@exportProducts')->name('store.export-products');
	Route::get(config('laraadmin.adminRoute') . '/stores/{id}/get-excluded-customers', 'LA\StoresController@getExcludedCustomers')->name('store.excluded-customers.get');
	Route::post(config('laraadmin.adminRoute') . '/stores/{id}/exclude-customer', 'LA\StoresController@excludeCustomer')->name('store.exclude-customer');
	Route::get(config('laraadmin.adminRoute') . '/stores/{id}/remove-excluded-customer', 'LA\StoresController@removeExcludedCustomer')->name('store.remove-excluded-customer');
	Route::post(config('laraadmin.adminRoute') . '/stores/{id}/update-shipping/{provider}', 'LA\StoresController@updateShipping')->name('store.update-shipping');
	Route::post(config('laraadmin.adminRoute') . '/stores/{id}/update-quantity', 'LA\StoresController@updateQuantity')->name('store.update-quantity');
	Route::get(config('laraadmin.adminRoute') . '/stores/{id}/get-vnpost-senders', 'LA\StoresController@getVnpostSenders')->name('store.vnpost-senders.get');
	Route::post(config('laraadmin.adminRoute') . '/stores/{id}/get-vnpost-senders', 'LA\StoresController@createVnpostSender')->name('store.vnpost-senders.post');
	Route::get(config('laraadmin.adminRoute') . '/stores/{id}/remove-vnpost-sender', 'LA\StoresController@removeVnpostSender')->name('store.vnpost-senders.remove');
	Route::put(config('laraadmin.adminRoute') . '/stores/{id}/update-vnpost-sender', 'LA\StoresController@updateVnpostSender')->name('store.vnpost-senders.update');
	Route::resource(config('laraadmin.adminRoute') . '/stores', 'LA\StoresController');
	Route::get(config('laraadmin.adminRoute') . '/store_dt_ajax', 'LA\StoresController@dtajax');
	/* ================== ActivatedWarranties ================== */
	Route::resource(config('laraadmin.adminRoute') . '/activated-warranties', 'LA\ActivatedWarrantiesController');
	Route::get(config('laraadmin.adminRoute') . '/activated_warranties_dt_ajax', 'LA\ActivatedWarrantiesController@dtajax');

	/* ================== StoreObserves ================== */
	Route::resource(config('laraadmin.adminRoute') . '/storeobserves', 'LA\StoreObservesController');
	Route::get(config('laraadmin.adminRoute') . '/storeobserf_dt_ajax', 'LA\StoreObservesController@dtajax');

	/* ================== Audits ================== */
	Route::resource(config('laraadmin.adminRoute') . '/audits', 'LA\AuditsController');
	Route::get(config('laraadmin.adminRoute') . '/audit_dt_ajax', 'LA\AuditsController@dtajax');

	/* ================== Commissions ================== */
	Route::resource(config('laraadmin.adminRoute') . '/commissions', 'LA\CommissionsController');
	Route::get(config('laraadmin.adminRoute') . '/commission_dt_ajax', 'LA\CommissionsController@dtajax');


	/* ================== Attributes ================== */
	Route::resource(config('laraadmin.adminRoute') . '/attributes', 'LA\AttributesController');
	Route::get(config('laraadmin.adminRoute') . '/attribute_dt_ajax', 'LA\AttributesController@dtajax');

	/* ================== AttributeValues ================== */
	Route::resource(config('laraadmin.adminRoute') . '/attributevalues', 'LA\AttributeValuesController');
	Route::get(config('laraadmin.adminRoute') . '/attributevalue_dt_ajax', 'LA\AttributeValuesController@dtajax');
	/* ================== WarrantyOrders ================== */
	Route::get(config('laraadmin.adminRoute') . '/warrantyorders/get-cod-services/{partner}', 'LA\WarrantyOrdersController@getCODServices');
	Route::get(config('laraadmin.adminRoute') . '/warrantyorders/get-data-for-bill-lading/{orderId}', 'LA\WarrantyOrdersController@getDataForBillLading');
	Route::get(config('laraadmin.adminRoute') . '/warrantyorders/{orderId}/bill-lading', 'LA\WarrantyOrdersController@getBillLading');
	Route::get(config('laraadmin.adminRoute') . '/warrantyorders/print', 'LA\WarrantyOrdersController@print')->name('warrantyorders.print');
	Route::resource(config('laraadmin.adminRoute') . '/warrantyorders', 'LA\WarrantyOrdersController');
	Route::get(config('laraadmin.adminRoute') . '/warrantyorder_dt_ajax', 'LA\WarrantyOrdersController@dtajax');

	/* ================== ProductCombos ================== */
	Route::resource(config('laraadmin.adminRoute') . '/productcombos', 'LA\ProductCombosController');
	Route::get(config('laraadmin.adminRoute') . '/productcombo_dt_ajax', 'LA\ProductCombosController@dtajax');

	Route::post(config('laraadmin.adminRoute') . '/orders/save-draft', 'LA\OrdersController@saveDraft')->name('order.save-draft');
	Route::get(config('laraadmin.adminRoute') . '/orders/{id}/create-from-draft', 'LA\OrdersController@createFromDraft');

	/* ================== OnlineCustomers ================== */
	Route::get(config('laraadmin.adminRoute') . '/onlinecustomers/{id}/create-customer', 'LA\OnlineCustomersController@createCustomer');
	Route::resource(config('laraadmin.adminRoute') . '/onlinecustomers', 'LA\OnlineCustomersController');
	Route::get(config('laraadmin.adminRoute') . '/onlinecustomer_dt_ajax', 'LA\OnlineCustomersController@dtajax');

	/* ================== CtvCustomers ================== */
	Route::get(config('laraadmin.adminRoute') . '/ctv', 'LA\CTVController@index');
	Route::get(config('laraadmin.adminRoute') . '/ctv_dt_ajax', 'LA\CTVController@dtajax');

	/* ================== Addresses ================== */
	Route::resource(config('laraadmin.adminRoute') . '/addresses', 'LA\AddressesController');
	Route::get(config('laraadmin.adminRoute') . '/address_dt_ajax', 'LA\AddressesController@dtajax');

	/* ================== Produces ================== */
	Route::get(config('laraadmin.adminRoute') . '/produces/product/attributes', 'LA\ProducesController@getProductAttributes')->name('produce.product.attribute');
	Route::get(config('laraadmin.adminRoute') . '/produces/{id}/success', 'LA\ProducesController@success')->name('product.success');
	Route::get(config('laraadmin.adminRoute') . '/produces/{id}/copy', 'LA\ProducesController@copy')->name('product.copy');
	Route::get(config('laraadmin.adminRoute') . '/produces/{id}/print', 'LA\ProducesController@print')->name('product.print');
	Route::resource(config('laraadmin.adminRoute') . '/produces', 'LA\ProducesController');
	Route::get(config('laraadmin.adminRoute') . '/produce_dt_ajax', 'LA\ProducesController@dtajax');

	/* ================== ProduceProducts ================== */
	Route::resource(config('laraadmin.adminRoute') . '/produceproducts', 'LA\ProduceProductsController');
	Route::get(config('laraadmin.adminRoute') . '/produceproduct_dt_ajax', 'LA\ProduceProductsController@dtajax');

	/* ================== ProductSeriHistories ================== */
	Route::resource(config('laraadmin.adminRoute') . '/productserihistories', 'LA\ProductSeriHistoriesController');
	Route::get(config('laraadmin.adminRoute') . '/productserihistory_dt_ajax', 'LA\ProductSeriHistoriesController@dtajax');

    /* ================== PaymentHistories ================== */
    Route::resource(config('laraadmin.adminRoute') . '/paymenthistories', 'LA\PaymentHistoriesController');
    Route::get(config('laraadmin.adminRoute') . '/paymenthistory_dt_ajax', 'LA\PaymentHistoriesController@dtajax');
    
	/* ================== Statistics ================== */
    Route::resource(config('laraadmin.adminRoute') . '/statistics', 'LA\StatisticController');
    Route::get(config('laraadmin.adminRoute') . '/paymenthistory_dt_ajax', 'LA\PaymentHistoriesController@dtajax');

    /* ================== Revenues ================== */
	Route::resource(config('laraadmin.adminRoute') . '/revenues', 'LA\RevenuesController');
	Route::get(config('laraadmin.adminRoute') . '/revenue_dt_ajax', 'LA\RevenuesController@dtajax');

	/* ================== Vouchers ================== */
	Route::resource(config('laraadmin.adminRoute') . '/vouchers', 'LA\VouchersController');
	Route::get(config('laraadmin.adminRoute') . '/voucher_dt_ajax', 'LA\VouchersController@dtajax');


	/* ================== ActivateToEarns ================== */
	Route::resource(config('laraadmin.adminRoute') . '/activatetoearns', 'LA\ActivateToEarnsController');
	Route::get(config('laraadmin.adminRoute') . '/activatetoearn_dt_ajax', 'LA\ActivateToEarnsController@dtajax');

	/* ================== AZPoints ================== */
	Route::resource(config('laraadmin.adminRoute') . '/azpoints', 'LA\AZPointsController');
	Route::get(config('laraadmin.adminRoute') . '/azpoint_dt_ajax', 'LA\AZPointsController@dtajax');

	/* ================== ProductQuantityAudits ================== */
	Route::resource(config('laraadmin.adminRoute') . '/productquantityaudits', 'LA\ProductQuantityAuditsController');
	Route::get(config('laraadmin.adminRoute') . '/productquantityaudit_dt_ajax', 'LA\ProductQuantityAuditsController@dtajax');

	/* ================== Imports ================== */
    Route::get(config('laraadmin.adminRoute') . '/imports/{id}/series-print', 'LA\ImportsController@printProductSeries')->name('import.series-print');
    Route::get(config('laraadmin.adminRoute') . '/imports/{id}/print', 'LA\ImportsController@print')->name('imports.print');
    Route::post(config('laraadmin.adminRoute') . '/imports/{id}/update-done', 'LA\ImportsController@updateDone')->name('imports.update-done');
	Route::resource(config('laraadmin.adminRoute') . '/imports', 'LA\ImportsController');
	Route::get(config('laraadmin.adminRoute') . '/import_dt_ajax', 'LA\ImportsController@dtajax');

	/* ================== ImportProducts ================== */
	Route::resource(config('laraadmin.adminRoute') . '/importproducts', 'LA\ImportProductsController');
	Route::get(config('laraadmin.adminRoute') . '/importproduct_dt_ajax', 'LA\ImportProductsController@dtajax');

	/* ================== ImportOrders ================== */
	Route::resource(config('laraadmin.adminRoute') . '/importorders', 'LA\ImportOrdersController');
	Route::get(config('laraadmin.adminRoute') . '/importorder_dt_ajax', 'LA\ImportOrdersController@dtajax');

	/* ================== IOSeris ================== */
	Route::resource(config('laraadmin.adminRoute') . '/ioseris', 'LA\IOSerisController');
	Route::get(config('laraadmin.adminRoute') . '/ioseri_dt_ajax', 'LA\IOSerisController@dtajax');

	/* ================== Draws ================== */
	Route::resource(config('laraadmin.adminRoute') . '/draws', 'LA\DrawsController');
	Route::get(config('laraadmin.adminRoute') . '/draw_dt_ajax', 'LA\DrawsController@dtajax');
    Route::get(config('laraadmin.adminRoute') . '/draw_update_lists', 'LA\DrawsController@updateLists')->name('draws.update-lists');

	/* ================== PostCategories ================== */
	Route::resource(config('laraadmin.adminRoute') . '/postcategories', 'LA\PostCategoriesController');
	Route::get(config('laraadmin.adminRoute') . '/postcategory_dt_ajax', 'LA\PostCategoriesController@dtajax');

	/* ================== Posts ================== */
	Route::resource(config('laraadmin.adminRoute') . '/posts', 'LA\PostsController');
	Route::get(config('laraadmin.adminRoute') . '/post_dt_ajax', 'LA\PostsController@dtajax');

	/* ================== Voucherhistories ================== */
    Route::get(config('laraadmin.adminRoute') . '/voucherhistories/export', 'LA\VoucherhistoriesController@export')->name('voucherhistory.export');
	Route::resource(config('laraadmin.adminRoute') . '/voucherhistories', 'LA\VoucherhistoriesController');
	Route::get(config('laraadmin.adminRoute') . '/voucherhistory_dt_ajax', 'LA\VoucherhistoriesController@dtajax');
});
