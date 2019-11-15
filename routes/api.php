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


    Route::get('/user-profile', 'API\UserController@getUser');
    Route::resource('user', 'API\UserController');


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
    Route::resource('category', 'API\CategoryController');
    Route::resource('type', 'API\TypeController');
    Route::resource('productfeature', 'API\ProductFeatureController');
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


Route::middleware('auth:api')->post('/authentication', 'API\UserController@authentication');

Route::post('/register', 'API\UserController@register');

Route::get('/pluck/modules', 'API\ModuleController@pluckIndex');
Route::get('/pluck/module/{uid}', 'API\ModuleController@pluckShow');
Route::get('/pluck/filter/module', 'API\ModuleController@pluckFilter');

Route::get('/pluck/roles', 'API\RoleController@pluckIndex');
Route::get('/pluck/role/{uid}', 'API\RoleController@pluckShow');
Route::get('/pluck/filter/role', 'API\RoleController@pluckFilter');

Route::get('/pluck/users', 'API\UserController@pluckIndex');
Route::get('/pluck/user/{uid}', 'API\UserController@pluckShow');
Route::get('/pluck/filter/user', 'API\UserController@pluckFilter');

Route::get('/pluck/companies', 'API\CompanyController@pluckIndex');
Route::get('/pluck/company/{uid}', 'API\CompanyController@pluckShow');
Route::get('/pluck/filter/company', 'API\CompanyController@pluckFilter');

Route::get('/pluck/inventories', 'API\InventoryController@pluckIndex');
Route::get('/pluck/inventory/{uid}', 'API\InventoryController@pluckShow');
Route::get('/pluck/filter/inventory', 'API\InventoryController@pluckFilter');

Route::get('/pluck/tickets', 'API\TicketController@pluckIndex');
Route::get('/pluck/ticket/{uid}', 'API\TicketController@pluckShow');
Route::get('/pluck/filter/ticket', 'API\TicketController@pluckFilter');

Route::get('/pluck/categories', 'API\CategoryController@pluckIndex');
Route::get('/pluck/category/{uid}', 'API\CategoryController@pluckShow');
Route::get('/pluck/filter/category', 'API\CategoryController@pluckFilter');

Route::get('/pluck/types', 'API\TypeController@pluckIndex');
Route::get('/pluck/type/{uid}', 'API\TypeController@pluckShow');
Route::get('/pluck/filter/type', 'API\TypeController@pluckFilter');

Route::get('/pluck/productfeatures', 'API\ProductFeatureController@pluckIndex');
Route::get('/pluck/productfeature/{uid}', 'API\ProductFeatureController@pluckShow');
Route::get('/pluck/filter/productfeature', 'API\ProductFeatureController@pluckFilter');

Route::get('/pluck/stores', 'API\StoreController@pluckIndex');
Route::get('/pluck/store/{uid}', 'API\StoreController@pluckShow');
Route::get('/pluck/filter/store', 'API\StoreController@pluckFilter');
