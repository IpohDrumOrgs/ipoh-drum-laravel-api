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
    Route::get('/filter/user', 'API\UserController@filter');


    Route::resource('group', 'API\GroupController');
    Route::get('/filter/group', 'API\GroupController@filter');

    Route::resource('company', 'API\CompanyController');
    Route::post('/get-all-company','API\CompanyController@getAllCompany');
    Route::get('/filter/company', 'API\CompanyController@filter');

    Route::resource('companytype', 'API\CompanyTypeController');
    Route::get('/filter/companytype', 'API\CompanyTypeController@filter');

    Route::resource('role', 'API\RoleController');
    Route::get('/filter/role', 'API\RoleController@filter');

    Route::resource('log', 'API\LogController');
    Route::get('/filter/log', 'API\LogController@filter');

    Route::resource('module', 'API\ModuleController');
    Route::get('/filter/module', 'API\ModuleController@filter');

    //Store Related Route =======================================================
    
    Route::resource('/category', 'API\CategoryController');
    Route::get('/filter/category', 'API\CategoryController@filter');

    Route::resource('/type', 'API\TypeController');
    Route::get('/filter/type', 'API\TypeController@filter');

    Route::resource('/productfeature', 'API\ProductFeatureController');
    Route::get('/filter/productfeature', 'API\ProductFeatureController@filter');

    Route::resource('store', 'API\StoreController');
    Route::get('/filter/store', 'API\StoreController@filter');
    Route::get('/store/{uid}/promotions', 'API\StoreController@getPromotions');
    Route::get('/store/{uid}/warranties', 'API\StoreController@getWarranties');
    Route::get('/store/{uid}/shippings', 'API\StoreController@getShippings');
    Route::get('/store/{uid}/inventories', 'API\StoreController@getInventories');

    Route::resource('storereview', 'API\StoreReviewController');
    Route::get('/filter/storereview', 'API\StoreReviewController@filter');

    Route::resource('productpromotion', 'API\ProductPromotionController');
    Route::get('/filter/productpromotion', 'API\ProductPromotionController@filter');

    Route::resource('productreview', 'API\ProductReviewController');
    Route::get('/filter/productreview', 'API\ProductReviewController@filter');
    // Route::post('/productreview/{uid}/edit', 'API\ProductReviewController@update');

    Route::resource('warranty', 'API\WarrantyController');
    Route::get('/filter/warranty', 'API\WarrantyController@filter');

    Route::resource('shipping', 'API\ShippingController');
    Route::get('/filter/shipping', 'API\ShippingController@filter');

    Route::resource('inventory', 'API\InventoryController');
    Route::get('/filter/inventory', 'API\InventoryController@filter');

    Route::resource('ticket', 'API\TicketController');
    Route::get('/filter/ticket', 'API\TicketController@filter');

    Route::resource('sale', 'API\SaleController');
    Route::get('/filter/sale', 'API\SaleController@filter');

    Route::resource('payment', 'API\PaymentController');
    Route::get('/filter/payment', 'API\PaymentController@filter');
    
});


Route::get('/category', 'API\CategoryController@index');
Route::get('/type', 'API\TypeController@index');
Route::get('/productfeature', 'API\ProductFeatureController@index');

Route::post('/register', 'API\UserController@register');

Route::get('/productfeature/{uid}/products', 'API\ProductFeatureController@getFeaturedProducts');


Route::get('/inventory/{uid}/onsale', 'API\InventoryController@getOnSaleInventory');




