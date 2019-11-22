<?php

use Illuminate\Http\Request;

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


Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::group(['middleware' => ['auth:api']], function (){

    Route::get('/userInfo',function (Request $request) {
        return $request->user();
    });

    Route::post('/authentication', 'API\UserController@authentication');

    Route::get('/user-profile', 'API\UserController@getUser');
    Route::resource('user', 'API\UserController');

    Route::post('/category', 'API\CategoryController@store');
    Route::get('/category/{uid}', 'API\CategoryController@show');
    Route::put('/category/{uid}', 'API\CategoryController@update');
    Route::delete('/category/{uid}', 'API\CategoryController@destroy');

    Route::post('/type', 'API\TypeController@store');
    Route::get('/type/{uid}', 'API\TypeController@show');
    Route::put('/type/{uid}', 'API\TypeController@update');
    Route::delete('/type/{uid}', 'API\TypeController@destroy');

    Route::post('/productfeature', 'API\ProductFeatureController@store');
    Route::get('/productfeature/{uid}', 'API\ProductFeatureController@show');
    Route::put('/productfeature/{uid}', 'API\ProductFeatureController@update');
    Route::delete('/productfeature/{uid}', 'API\ProductFeatureController@destroy');

    Route::resource('group', 'API\GroupController');

    Route::resource('company', 'API\CompanyController');
    Route::post('/get-all-company','API\CompanyController@getAllCompany');

    //Generate report in PDF
    // Route::get('/inventory/generate-pdf', 'API\InventoryController@exportPDF');
    // Route::get('/stocktransfer/generate-pdf', 'API\StockTransferController@exportPDF');
    // Route::get('/generate-pdf', 'API\SaleController@exportPDF')->name('generate-pdf');

    // Route::resource('account', 'API\AccountController');
    Route::resource('companytype', 'API\CompanyTypeController');
    Route::resource('role', 'API\RoleController');
    Route::resource('log', 'API\LogController');
    Route::resource('module', 'API\ModuleController');
    Route::resource('inventory', 'API\InventoryController');
    Route::resource('store', 'API\StoreController');
    Route::resource('ticket', 'API\TicketController');
    Route::resource('sale', 'API\SaleController');
    Route::resource('payment', 'API\PaymentController');
    // Route::resource('purchase', 'API\PurchaseController');
    // Route::resource('batch', 'API\BatchController');
    // Route::resource('stocktransfer', 'API\StockTransferController');
    // Route::resource('inventorybatch', 'API\InventoryBatchController');
    // // Route::resource('report', 'API\ReportController');
    // Route::resource('pricelist', 'API\PriceListController');

    //Filter
    Route::get('/filter/user', 'API\UserController@filter');
    Route::get('/filter/group', 'API\GroupController@filter');
    Route::get('/filter/company', 'API\CompanyController@filter');
    Route::get('/filter/companytype', 'API\CompanyTypeController@filter');
    Route::get('/filter/role', 'API\RoleController@filter');
    Route::get('/filter/log', 'API\LogController@filter');
    Route::get('/filter/module', 'API\ModuleController@filter');
    Route::get('/filter/category', 'API\CategoryController@filter');
    Route::get('/filter/type', 'API\TypeController@filter');
    Route::get('/filter/productfeature', 'API\ProductFeatureController@filter');
    Route::get('/filter/inventory', 'API\InventoryController@filter');
    Route::get('/filter/store', 'API\StoreController@filter');
    Route::get('/filter/ticket', 'API\TicketController@filter');
    Route::get('/filter/inventorybatch', 'API\InventoryBatchController@filter');
    Route::get('/filter/sale', 'API\SaleController@filter');
    Route::get('/filter/payment', 'API\PaymentController@filter');
    Route::get('/filter/purchase', 'API\PurchaseController@filter');
    Route::get('/filter/batch', 'API\BatchController@filter');
    Route::get('/filter/stocktransfer', 'API\StockTransferController@filter');
    Route::get('/filter/get-cashiers', 'API\UserController@getCashiers');
    Route::get('/filter/filter-report', 'API\ReportController@filter');

    // Route::get('/awaiting-approve', 'API\StockTransferController@awaitingApprove');

    // Route::post('/change-password','API\UserController@changePassword');

});


Route::get('/category', 'API\CategoryController@index');
Route::get('/type', 'API\TypeController@index');
Route::get('/productfeature', 'API\ProductFeatureController@index');

Route::post('/register', 'API\UserController@register');

Route::get('/productfeature/{uid}/products', 'API\ProductFeatureController@getFeaturedProducts');


Route::get('/onsale/inventory/{uid}', 'API\InventoryController@getOnSaleInventory');


