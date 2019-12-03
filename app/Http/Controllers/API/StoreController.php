<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use DB;
use Carbon\Carbon;
use App\Store;
use App\ProductPromotion;
use App\Warranty;
use App\Shipping;
use Illuminate\Support\Facades\Hash;
use App\Traits\GlobalFunctions;
use App\Traits\NotificationFunctions;
use App\Traits\StoreServices;
use App\Traits\LogServices;

class StoreController extends Controller
{
    use GlobalFunctions, NotificationFunctions, StoreServices, LogServices;
    private $controllerName = '[StoreController]';
    /**
     * @OA\Get(
     *      path="/api/store",
     *      operationId="getStores",
     *      tags={"StoreControllerService"},
     *      summary="Get list of stores",
     *      description="Returns list of stores",
     *   @OA\Parameter(
     *     name="pageNumber",
     *     in="query",
     *     description="Page number",
     *     @OA\Schema(type="integer")
     *   ),
     *   @OA\Parameter(
     *     name="pageSize",
     *     in="query",
     *     description="number of pageSize",
     *     @OA\Schema(type="integer")
     *   ),
     *      @OA\Response(
     *          response=200,
     *          description="Successfully retrieved list of stores"
     *       ),
     *       @OA\Response(
     *          response="default",
     *          description="Unable to retrieve list of stores")
     *    )
     */
    public function index(Request $request)
    {
        error_log('Retrieving list of stores.');
        // api/store (GET)
        $stores = $this->getStores($request->user());
        if ($this->isEmpty($stores)) {
            $data['status'] = 'error';
            $data['data'] = null;
            $data['maximumPages'] = 0;
            $data['msg'] = $this->getNotFoundMsg('Stores');
            $data['code'] = 404;
            return response()->json($data, 404);
        } else {
            //Page Pagination Result List
            //Default return 10
            $paginateddata = $this->paginateResult($stores, $request->pageSize, $request->pageNumber);
            $data['status'] = 'success';
            $data['data'] = $paginateddata;
            $data['maximumPages'] = $this->getMaximumPaginationPage($stores->count(), $request->pageSize);
            $data['msg'] = $this->getRetrievedSuccessMsg('Stores');
            $data['code'] = 200;
            return response()->json($data, 200);
        }
    }
    
    /**
     * @OA\Get(
     *      path="/api/filter/store",
     *      operationId="filterStores",
     *      tags={"StoreControllerService"},
     *      summary="Filter list of stores",
     *      description="Returns list of filtered stores",
     *   @OA\Parameter(
     *     name="pageNumber",
     *     in="query",
     *     description="Page number",
     *     @OA\Schema(type="integer")
     *   ),
     *   @OA\Parameter(
     *     name="pageSize",
     *     in="query",
     *     description="number of pageSize",
     *     @OA\Schema(type="integer")
     *   ),
     *   @OA\Parameter(
     *     name="keyword",
     *     in="query",
     *     description="Keyword for filter",
     *     @OA\Schema(type="string")
     *   ),
     *   @OA\Parameter(
     *     name="fromdate",
     *     in="query",
     *     description="From Date for filter",
     *     @OA\Schema(type="string")
     *   ),
     *   @OA\Parameter(
     *     name="todate",
     *     in="query",
     *     description="To date for filter",
     *     @OA\Schema(type="string")
     *   ),
     *   @OA\Parameter(
     *     name="status",
     *     in="query",
     *     description="status for filter",
     *     @OA\Schema(type="string")
     *   ),
     *      @OA\Response(
     *          response=200,
     *          description="Successfully retrieved list of filtered stores"
     *       ),
     *       @OA\Response(
     *          response="default",
     *          description="Unable to retrieve list of stores")
     *    )
     */
    public function filter(Request $request)
    {
        error_log('Retrieving list of filtered stores.');
        // api/store/filter (GET)
        $params = collect([
            'keyword' => $request->keyword,
            'fromdate' => $request->fromdate,
            'todate' => $request->todate,
            'status' => $request->status,
            'store_id' => $request->store_id,
        ]);
        //Convert To Json Object
        $params = json_decode(json_encode($params));
        $stores = $this->getStores($request->user());
        $stores = $this->filterStores($stores, $params);

        if ($this->isEmpty($stores)) {
            $data['data'] = null;
            $data['maximumPages'] = 0;
            $data['msg'] = $this->getNotFoundMsg('Stores');
            $data['code'] = 404;
            return response()->json($data, 404);
        } else {
            //Page Pagination Result List
            //Default return 10
            $paginateddata = $this->paginateResult($stores, $request->pageSize, $request->pageNumber);
            $data['data'] = $paginateddata;
            $data['maximumPages'] = $this->getMaximumPaginationPage($stores->count(), $request->pageSize);
            $data['msg'] = $this->getRetrievedSuccessMsg('Stores');
            $data['code'] = 200;
            return response()->json($data, 200);
        }

    }

   
    /**
     * @OA\Get(
     *   tags={"StoreControllerService"},
     *   path="/api/store/{uid}",
     *   summary="Retrieves store by Uid.",
     *     operationId="getStoreByUid",
     *   @OA\Parameter(
     *     name="uid",
     *     in="path",
     *     description="Store_ID, NOT 'ID'.",
     *     required=true,
     *     @OA\Schema(type="string")
     *   ),
     *   @OA\Response(
     *     response=200,
     *     description="Store has been retrieved successfully."
     *   ),
     *   @OA\Response(
     *     response="default",
     *     description="Unable to retrieve the store."
     *   )
     * )
     */
    public function show(Request $request, $uid)
    {
        // api/store/{storeid} (GET)
        error_log('Retrieving store of uid:' . $uid);
        $store = $this->getStore($uid);
        if ($this->isEmpty($store)) {
            $data['data'] = null;
            $data['msg'] = $this->getNotFoundMsg('Store');
            $data['status'] = 'error';
            $data['code'] = 404;
            return response()->json($data, 404);
        } else {
            $data['data'] = $store;
            $data['msg'] = $this->getRetrievedSuccessMsg('Store');
            $data['status'] = 'success';
            $data['code'] = 200;
            return response()->json($data, 200);
        }
    }

  
     
    /**
     * @OA\Post(
     *   tags={"StoreControllerService"},
     *   path="/api/store",
     *   summary="Creates a store.",
     *   operationId="createStore",
     * @OA\Parameter(
     * name="name",
     * in="query",
     * description="Storename",
     * required=true,
     * @OA\Schema(
     *              type="string"
     *          )
     * ),
     * @OA\Parameter(
     * name="companyid",
     * in="query",
     * description="Company ID",
     * @OA\Schema(
     *              type="integer"
     *          )
     * ),
     * @OA\Parameter(
     * name="userid",
     * in="query",
     * description="User ID",
     * @OA\Schema(
     *              type="integer"
     *          )
     * ),
     * @OA\Parameter(
     * name="companyBelongings",
     * in="query",
     * description="Store belongs to Company",
     * required=true,
     * @OA\Schema(
     *              type="integer"
     *          )
     * ),
     * @OA\Parameter(
     * name="contact",
     * in="query",
     * description="Contact",
     * @OA\Schema(
     *              type="string"
     *          )
     * ),
     * @OA\Parameter(
     * name="desc",
     * in="query",
     * description="Description",
     * @OA\Schema(
     *              type="string"
     *          )
     * ),
     * @OA\Parameter(
     * name="email",
     * in="query",
     * description="Email",
     * @OA\Schema(
     *              type="string"
     *          )
     * ),
     * @OA\Parameter(
     * name="address",
     * in="query",
     * description="Address",
     * @OA\Schema(
     *              type="string"
     *          )
     * ),
     * @OA\Parameter(
     * name="postcode",
     * in="query",
     * description="Post Code",
     * @OA\Schema(
     *              type="string"
     *          )
     * ),
     * @OA\Parameter(
     * name="state",
     * in="query",
     * description="State",
     * @OA\Schema(
     *              type="string"
     *          )
     * ),
     * @OA\Parameter(
     * name="city",
     * in="query",
     * description="City",
     * @OA\Schema(
     *  type="string"
     *  )
     * ),
     * @OA\Parameter(
     * name="Country",
     * in="query",
     * description="Country",
     * @OA\Schema(
     *              type="string"
     *          )
     * ),
     *   @OA\Response(
     *     response=200,
     *     description="Store has been created successfully."
     *   ),
     *   @OA\Response(
     *     response="default",
     *     description="Unable to create the store."
     *   )
     * )
     */
    public function store(Request $request)
    {
        DB::beginTransaction();
        // Can only be used by Authorized personnel
        // api/store (POST)
        
        $this->validate($request, [
            'name' => 'required|string|max:191',
            'desc' => 'required|string',
            'companyBelongings' => 'required|boolean',
        ]);
        error_log('Creating store.');
        $params = collect([
            'name' => $request->name,
            'desc' => $request->desc,
            'contact' => $request->contact,
            'email' => $request->email,
            'address' => $request->address,
            'postcode' => $request->postcode,
            'city' => $request->city,
            'state' => $request->state,
            'country' => $request->country,
            'companyBelongings' => $request->companyBelongings,
            'companyid' => $request->companyid,
            'userid' => $request->userid,
        ]);
        //Convert To Json Object
        $params = json_decode(json_encode($params));
        $store = $this->createStore($params);

        if ($this->isEmpty($store)) {
            DB::rollBack();
            $data['data'] = null;
            $data['status'] = 'error';
            $data['msg'] = $this->getErrorMsg();
            $data['code'] = 404;
            return response()->json($data, 404);
        } else {
            DB::commit();
            $data['status'] = 'success';
            $data['msg'] = $this->getCreatedSuccessMsg('Store');
            $data['data'] = $store;
            $data['code'] = 200;
            return response()->json($data, 200);
        }
    }


    /**
     * @OA\Put(
     *   tags={"StoreControllerService"},
     *   path="/api/store/{uid}",
     *   summary="Update store by Uid.",
     *     operationId="updateStoreByUid",
     *   @OA\Parameter(
     *     name="uid",
     *     in="path",
     *     description="Store_ID, NOT 'ID'.",
     *     required=true,
     *     @OA\Schema(type="string")
     *   ),
   * @OA\Parameter(
     * name="name",
     * in="query",
     * description="Storename",
     * required=true,
     * @OA\Schema(
     *              type="string"
     *          )
     * ),
     * @OA\Parameter(
     * name="companyid",
     * in="query",
     * description="Company ID",
     * @OA\Schema(
     *              type="integer"
     *          )
     * ),
     * @OA\Parameter(
     * name="userid",
     * in="query",
     * description="User ID",
     * @OA\Schema(
     *              type="integer"
     *          )
     * ),
     * @OA\Parameter(
     * name="companyBelongings",
     * in="query",
     * description="Store belongs to Company",
     * required=true,
     * @OA\Schema(
     *              type="integer"
     *          )
     * ),
     * @OA\Parameter(
     * name="desc",
     * in="query",
     * description="Description",
     * @OA\Schema(
     *              type="string"
     *          )
     * ),
     * @OA\Parameter(
     * name="contact",
     * in="query",
     * description="Contact",
     * @OA\Schema(
     *              type="string"
     *          )
     * ),
     * @OA\Parameter(
     * name="email",
     * in="query",
     * description="Email",
     * @OA\Schema(
     *              type="string"
     *          )
     * ),
     * @OA\Parameter(
     * name="address",
     * in="query",
     * description="Address",
     * @OA\Schema(
     *              type="string"
     *          )
     * ),
     * @OA\Parameter(
     * name="postcode",
     * in="query",
     * description="Post Code",
     * @OA\Schema(
     *              type="string"
     *          )
     * ),
     * @OA\Parameter(
     * name="state",
     * in="query",
     * description="State",
     * @OA\Schema(
     *              type="string"
     *          )
     * ),
     * @OA\Parameter(
     * name="city",
     * in="query",
     * description="City",
     * @OA\Schema(
     *  type="string"
     *  )
     * ),
     * @OA\Parameter(
     * name="Country",
     * in="query",
     * description="Country",
     * @OA\Schema(
     *              type="string"
     *          )
     * ),
     *   @OA\Response(
     *     response=200,
     *     description="Store has been updated successfully."
     *   ),
     *   @OA\Response(
     *     response="default",
     *     description="Unable to update the store."
     *   )
     * )
     */
    public function update(Request $request, $uid)
    {
        DB::beginTransaction();
        // api/store/{storeid} (PUT) 
        error_log('Updating store of uid: ' . $uid);
        $store = $this->getStore($uid);
        $this->validate($request, [
            'name' => 'required|string|max:191',
            'desc' => 'required|string',
            'companyBelongings' => 'required|boolean',
        ]);
        
        if ($this->isEmpty($store)) {
            DB::rollBack();
            $data['data'] = null;
            $data['msg'] = $this->getNotFoundMsg('Store');
            $data['status'] = 'error';
            $data['code'] = 404;
            return response()->json($data, 404);
        }
        $params = collect([
            'name' => $request->name,
            'desc' => $request->desc,
            'contact' => $request->contact,
            'email' => $request->email,
            'address' => $request->address,
            'postcode' => $request->postcode,
            'city' => $request->city,
            'state' => $request->state,
            'country' => $request->country,
            'companyBelongings' => $request->companyBelongings,
            'companyid' => $request->companyid,
            'userid' => $request->userid,
        ]);
        //Convert To Json Object
        $params = json_decode(json_encode($params));
        $store = $this->updateStore($store, $params);
        if ($this->isEmpty($store)) {
            DB::rollBack();
            $data['data'] = null;
            $data['msg'] = $this->getErrorMsg('Store');
            $data['status'] = 'error';
            $data['code'] = 404;
            return response()->json($data, 404);
        } else {
            DB::commit();
            $data['status'] = 'success';
            $data['msg'] = $this->getUpdatedSuccessMsg('Store');
            $data['data'] = $store;
            $data['status'] = 'success';
            $data['code'] = 200;
            return response()->json($data, 200);
        }
    }

    /**
     * @OA\Delete(
     *   tags={"StoreControllerService"},
     *   path="/api/store/{uid}",
     *   summary="Set store's 'status' to 0.",
     *     operationId="deleteStoreByUid",
     *   @OA\Parameter(
     *     name="uid",
     *     in="path",
     *     description="Store ID, NOT 'ID'.",
     *     required=true,
     *     @OA\SChema(type="string")
     *   ),
     *   @OA\Response(
     *     response=200,
     *     description="Store has been 'deleted' successfully."
     *   ),
     *   @OA\Response(
     *     response="default",
     *     description="Unable to 'delete' the store."
     *   )
     * )
     */
    public function destroy(Request $request, $uid)
    {
        DB::beginTransaction();
        // TODO ONLY TOGGLES THE status = 1/0
        // api/store/{storeid} (DELETE)
        error_log('Deleting store of uid: ' . $uid);
        $store = $this->getStore($uid);
        if ($this->isEmpty($store)) {
            DB::rollBack();
            $data['status'] = 'error';
            $data['msg'] = $this->getNotFoundMsg('Store');
            $data['data'] = null;
            $data['code'] = 404;
            return response()->json($data, 404);
        }
        $store = $this->deleteStore($store);
        $this->createLog($request->user()->id , [$store->id], 'delete', 'store');
        if ($this->isEmpty($store)) {
            DB::rollBack();
            $data['status'] = 'error';
            $data['msg'] = $this->getErrorMsg();
            $data['data'] = null;
            $data['code'] = 404;
            return response()->json($data, 404);
        } else {
            DB::commit();
            $data['status'] = 'success';
            $data['msg'] = $this->getDeletedSuccessMsg('Store');
            $data['data'] = null;
            $data['code'] = 200;
            return response()->json($data, 200);
        }
    }


    /**
     * @OA\Get(
     *   tags={"StoreControllerService"},
     *   path="/api/store/{uid}/promotions",
     *   summary="Retrieves store promotion plans by Uid.",
     *     operationId="getPromotionsByStoreUid",
     *   @OA\Parameter(
     *     name="uid",
     *     in="path",
     *     description="Store ID, NOT 'ID'.",
     *     required=true,
     *     @OA\SChema(type="string")
     *   ),
     *   @OA\Response(
     *     response=200,
     *     description="Promotions has been retrieved successfully."
     *   ),
     *   @OA\Response(
     *     response="default",
     *     description="Unable to retrieved the promotions."
     *   )
     * )
     */
    public function getPromotions(Request $request, $uid)
    {
        error_log($this->controllerName.'Retrieving store promotion plans by uid:' . $uid);
        $store = $this->getStore($uid);
        $promotions = collect();
        $promotions = $promotions->merge($store->promotions()->where('status',true)->get());
        $promotions = $promotions->merge(ProductPromotion::where('store_id' , null)->get());
        $promotions = $promotions->unique('id')->sortBy('id')->flatten(1);

        $data['data'] = $promotions;
        $data['msg'] = $this->getRetrievedSuccessMsg('Promotions');
        $data['status'] = 'success';
        $data['code'] = 200;
        return response()->json($data, 200);
    }
    /**
     * @OA\Get(
     *   tags={"StoreControllerService"},
     *   path="/api/store/{uid}/warranties",
     *   summary="Retrieves store warranties by Uid.",
     *     operationId="getWarrantiesByStoreUid",
     *   @OA\Parameter(
     *     name="uid",
     *     in="path",
     *     description="Store ID, NOT 'ID'.",
     *     required=true,
     *     @OA\SChema(type="string")
     *   ),
     *   @OA\Response(
     *     response=200,
     *     description="Warranties has been retrieved successfully."
     *   ),
     *   @OA\Response(
     *     response="default",
     *     description="Unable to retrieved the warranties."
     *   )
     * )
     */
    public function getWarranties(Request $request, $uid)
    {
        error_log($this->controllerName.'Retrieving store warranties by uid:' . $uid);
        $store = $this->getStore($uid);
        $warranties = collect();
        $warranties = $warranties->merge($store->warranties()->where('status',true)->get());
        $warranties = $warranties->merge(Warranty::where('store_id' , null)->get());
        $warranties = $warranties->unique('id')->sortBy('id')->flatten(1);

        $data['data'] = $warranties;
        $data['msg'] = $this->getRetrievedSuccessMsg('Warranties');
        $data['status'] = 'success';
        $data['code'] = 200;
        return response()->json($data, 200);
    }

    /**
     * @OA\Get(
     *   tags={"StoreControllerService"},
     *   path="/api/store/{uid}/shippings",
     *   summary="Retrieves store shippings by Uid.",
     *     operationId="getShippingsByStoreUid",
     *   @OA\Parameter(
     *     name="uid",
     *     in="path",
     *     description="Store ID, NOT 'ID'.",
     *     required=true,
     *     @OA\SChema(type="string")
     *   ),
     *   @OA\Response(
     *     response=200,
     *     description="Shippings has been retrieved successfully."
     *   ),
     *   @OA\Response(
     *     response="default",
     *     description="Unable to retrieved the shippings."
     *   )
     * )
     */
    public function getShippings(Request $request, $uid)
    {
        error_log($this->controllerName.'Retrieving store shippings by uid:' . $uid);
        $store = $this->getStore($uid);
        $shippings = collect();
        $shippings = $shippings->merge($store->shippings()->where('status',true)->get());
        $shippings = $shippings->merge(Shipping::where('store_id' , null)->get());
        $shippings = $shippings->unique('id')->sortBy('id')->flatten(1);

        $data['data'] = $shippings;
        $data['msg'] = $this->getRetrievedSuccessMsg('Shippings');
        $data['status'] = 'success';
        $data['code'] = 200;
        return response()->json($data, 200);
    }

}
