<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use DB;
use Carbon\Carbon;
use App\Inventory;
use Illuminate\Support\Facades\Hash;
use App\Traits\GlobalFunctions;
use App\Traits\NotificationFunctions;
use App\Traits\InventoryServices;
use App\Traits\LogServices;

class InventoryController extends Controller
{
    use GlobalFunctions, NotificationFunctions, InventoryServices, LogServices;
    private $controllerName = '[InventoryController]';
     /**
     * @OA\Get(
     *      path="/api/inventory",
     *      operationId="getInventories",
     *      tags={"InventoryControllerService"},
     *      summary="Get list of inventories",
     *      description="Returns list of inventories",
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
     *          description="Successfully retrieved list of inventories"
     *       ),
     *       @OA\Response(
     *          response="default",
     *          description="Unable to retrieve list of inventories")
     *    )
     */
    public function index(Request $request)
    {
        error_log('Retrieving list of inventories.');
        // api/inventory (GET)
        $inventories = $this->getInventories($request->user());
        if ($this->isEmpty($inventories)) {
            $data['status'] = 'error';
            $data['data'] = null;
            $data['maximumPages'] = 0;
            $data['msg'] = $this->getNotFoundMsg('Inventories');
            $data['code'] = 404;
            return response()->json($data, 404);
        } else {
            //Page Pagination Result List
            //Default return 10
            $paginateddata = $this->paginateResult($inventories, $request->pageSize, $request->pageNumber);
            $data['status'] = 'success';
            $data['data'] = $paginateddata;
            $data['maximumPages'] = $this->getMaximumPaginationPage($inventories->count(), $request->pageSize);
            $data['msg'] = $this->getRetrievedSuccessMsg('Inventories');
            $data['code'] = 200;
            return response()->json($data, 200);
        }
    }
    
    /**
     * @OA\Get(
     *      path="/api/filter/inventory",
     *      operationId="filterInventories",
     *      tags={"InventoryControllerService"},
     *      summary="Filter list of inventories",
     *      description="Returns list of filtered inventories",
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
     *     name="onsale",
     *     in="query",
     *     description="On sale for filter",
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
     *          description="Successfully retrieved list of filtered inventories"
     *       ),
     *       @OA\Response(
     *          response="default",
     *          description="Unable to retrieve list of inventories")
     *    )
     */
    public function filter(Request $request)
    {
        error_log('Retrieving list of filtered inventories.');
        // api/inventory/filter (GET)
        $params = collect([
            'keyword' => $request->keyword,
            'fromdate' => $request->fromdate,
            'todate' => $request->todate,
            'status' => $request->status,
            'onsale' => $request->onsale,
            'inventory_id' => $request->inventory_id,
        ]);
        //Convert To Json Object
        $params = json_decode(json_encode($params));
        $inventories = $this->getInventories($request->user());
        $inventories = $this->filterInventories($inventories, $params);

        if ($this->isEmpty($inventories)) {
            $data['data'] = null;
            $data['maximumPages'] = 0;
            $data['msg'] = $this->getNotFoundMsg('Inventories');
            $data['code'] = 404;
            return response()->json($data, 404);
        } else {
            //Page Pagination Result List
            //Default return 10
            $paginateddata = $this->paginateResult($inventories, $request->pageSize, $request->pageNumber);
            $data['data'] = $paginateddata;
            $data['maximumPages'] = $this->getMaximumPaginationPage($inventories->count(), $request->pageSize);
            $data['msg'] = $this->getRetrievedSuccessMsg('Inventories');
            $data['code'] = 200;
            return response()->json($data, 200);
        }

    }

   
    /**
     * @OA\Get(
     *   tags={"InventoryControllerService"},
     *   path="/api/inventory/{uid}",
     *   summary="Retrieves inventory by Uid.",
     *     operationId="getInventoryByUid",
     *   @OA\Parameter(
     *     name="uid",
     *     in="path",
     *     description="Inventory_ID, NOT 'ID'.",
     *     required=true,
     *     @OA\Schema(type="string")
     *   ),
     *   @OA\Response(
     *     response=200,
     *     description="Inventory has been retrieved successfully."
     *   ),
     *   @OA\Response(
     *     response="default",
     *     description="Unable to retrieve the inventory."
     *   )
     * )
     */
    public function show(Request $request, $uid)
    {
        // api/inventory/{inventoryid} (GET)
        error_log('Retrieving inventory of uid:' . $uid);
        $inventory = $this->getInventory($uid);
        if ($this->isEmpty($inventory)) {
            $data['data'] = null;
            $data['msg'] = $this->getNotFoundMsg('Inventory');
            $data['status'] = 'error';
            $data['code'] = 404;
            return response()->json($data, 404);
        } else {
            $data['data'] = $inventory;
            $data['msg'] = $this->getRetrievedSuccessMsg('Inventory');
            $data['status'] = 'success';
            $data['code'] = 200;
            return response()->json($data, 200);
        }
    }

  
    
    /**
     * @OA\Post(
     *   tags={"InventoryControllerService"},
     *   path="/api/inventory",
     *   summary="Creates a inventory.",
     *   operationId="createInventory",
     * @OA\Parameter(
     * name="name",
     * in="query",
     * description="Inventoryname",
     * required=true,
     * @OA\Schema(
     *              type="string"
     *          )
     * ),
     * @OA\Parameter(
     * name="storeid",
     * in="query",
     * description="Store ID",
     * required=true,
     * @OA\Schema(
     *              type="integer"
     *          )
     * ),
     * @OA\Parameter(
     * name="promotionid",
     * in="query",
     * description="Promotion ID",
     * required=true,
     * @OA\Schema(
     *              type="integer"
     *          )
     * ),
     * @OA\Parameter(
     * name="warrantyid",
     * in="query",
     * description="Warranty ID",
     * required=true,
     * @OA\Schema(
     *              type="integer"
     *          )
     * ),
     * @OA\Parameter(
     * name="shippingid",
     * in="query",
     * description="Shipping ID",
     * required=true,
     * @OA\Schema(
     *              type="integer"
     *          )
     * ),
     * @OA\Parameter(
     * name="code",
     * in="query",
     * description="Code",
     * @OA\Schema(
     *              type="string"
     *          )
     * ),
     * @OA\Parameter(
     * name="sku",
     * in="query",
     * description="Sku",
     * required=true,
     * @OA\Schema(
     *              type="string"
     *          )
     * ),
     * @OA\Parameter(
     * name="desc",
     * in="query",
     * description="Product Description",
     * @OA\Schema(
     *              type="string"
     *          )
     * ),
     * @OA\Parameter(
     * name="imgpath",
     * in="query",
     * description="Image Path",
     * @OA\Schema(
     *              type="string"
     *          )
     * ),
     * @OA\Parameter(
     * name="cost",
     * in="query",
     * description="Product Cost",
     * required=true,
     * @OA\Schema(
     *              type="number"
     *          )
     * ),
     * @OA\Parameter(
     * name="price",
     * in="query",
     * description="Product Selling Price",
     * required=true,
     * @OA\Schema(
     *              type="number"
     *          )
     * ),
     * @OA\Parameter(
     * name="qty",
     * in="query",
     * description="Stock Qty",
     * required=true,
     * @OA\Schema(
     *              type="integer"
     *          )
     * ),
     * @OA\Parameter(
     * name="stockthreshold",
     * in="query",
     * description="Stock Threshold",
     * @OA\Schema(
     *              type="integer"
     *          )
     * ),
     * @OA\Parameter(
     * name="onsale",
     * in="query",
     * description="On Sale",
     * required=true,
     * @OA\Schema(
     *              type="integer"
     *          )
     * ),
     *   @OA\Response(
     *     response=200,
     *     description="Inventory has been created successfully."
     *   ),
     *   @OA\Response(
     *     response="default",
     *     description="Unable to create the inventory."
     *   )
     * )
     */
    public function store(Request $request)
    {
        DB::beginTransaction();
        // Can only be used by Authorized personnel
        // api/inventory (POST)

        $this->validate($request, [
            'storeid' => 'required',
            'name' => 'required|string|max:191',
            'code' => 'nullable',
            'sku' => 'required|string|max:191',
            'desc' => 'nullable',
            'cost' => 'required|numeric|min:0',
            'price' => 'required|numeric|min:0',
            'qty' => 'required|numeric|min:0',
            'onsale' => 'required|numeric',
        ]);
        error_log($this->controllerName.'Creating inventory.');
        $params = collect([
            'storeid' => $request->storeid,
            'promotionid' => $request->promotionid,
            'warrantyid' => $request->warrantyid,
            'shippingid' => $request->shippingid,
            'name' => $request->name,
            'code' => $request->code,
            'sku' => $request->sku,
            'desc' => $request->desc,
            'imgpath' => $request->imgpath,
            'cost' => $request->cost,
            'price' => $request->price,
            'qty' => $request->qty,
            'stockthreshold' => $request->stockthreshold,
            'onsale' => $request->onsale,
        ]);
        //Convert To Json Object
        $params = json_decode(json_encode($params));
        $inventory = $this->createInventory($params);

        if ($this->isEmpty($inventory)) {
            DB::rollBack();
            $data['data'] = null;
            $data['status'] = 'error';
            $data['msg'] = $this->getErrorMsg();
            $data['code'] = 404;
            return response()->json($data, 404);
        } else {
            DB::commit();
            $data['status'] = 'success';
            $data['msg'] = $this->getCreatedSuccessMsg('Inventory');
            $data['data'] = $inventory;
            $data['code'] = 200;
            return response()->json($data, 200);
        }
    }


    /**
     * @OA\Put(
     *   tags={"InventoryControllerService"},
     *   path="/api/inventory/{uid}",
     *   summary="Update inventory by Uid.",
     *     operationId="updateInventoryByUid",
     *   @OA\Parameter(
     *     name="uid",
     *     in="path",
     *     description="Inventory_ID, NOT 'ID'.",
     *     required=true,
     *     @OA\Schema(type="string")
     *   ),
     * @OA\Parameter(
     * name="name",
     * in="query",
     * description="Inventoryname",
     * required=true,
     * @OA\Schema(
     *              type="string"
     *          )
     * ),
     * @OA\Parameter(
     * name="storeid",
     * in="query",
     * description="Store ID",
     * required=true,
     * @OA\Schema(
     *              type="integer"
     *          )
     * ),
     * @OA\Parameter(
     * name="promotionid",
     * in="query",
     * description="Promotion ID",
     * required=true,
     * @OA\Schema(
     *              type="integer"
     *          )
     * ),
     * @OA\Parameter(
     * name="warrantyid",
     * in="query",
     * description="Warranty ID",
     * required=true,
     * @OA\Schema(
     *              type="integer"
     *          )
     * ),
     * @OA\Parameter(
     * name="shippingid",
     * in="query",
     * description="Shipping ID",
     * required=true,
     * @OA\Schema(
     *              type="integer"
     *          )
     * ),
     * @OA\Parameter(
     * name="code",
     * in="query",
     * description="Code",
     * @OA\Schema(
     *              type="string"
     *          )
     * ),
     * @OA\Parameter(
     * name="sku",
     * in="query",
     * description="Sku",
     * required=true,
     * @OA\Schema(
     *              type="string"
     *          )
     * ),
     * @OA\Parameter(
     * name="desc",
     * in="query",
     * description="Product Description",
     * @OA\Schema(
     *              type="string"
     *          )
     * ),
     * @OA\Parameter(
     * name="imgpath",
     * in="query",
     * description="Image Path",
     * @OA\Schema(
     *              type="string"
     *          )
     * ),
     * @OA\Parameter(
     * name="cost",
     * in="query",
     * description="Product Cost",
     * required=true,
     * @OA\Schema(
     *              type="number"
     *          )
     * ),
     * @OA\Parameter(
     * name="price",
     * in="query",
     * description="Product Selling Price",
     * required=true,
     * @OA\Schema(
     *              type="number"
     *          )
     * ),
     * @OA\Parameter(
     * name="qty",
     * in="query",
     * description="Stock Qty",
     * required=true,
     * @OA\Schema(
     *              type="integer"
     *          )
     * ),
     * @OA\Parameter(
     * name="stockthreshold",
     * in="query",
     * description="Stock Threshold",
     * @OA\Schema(
     *              type="integer"
     *          )
     * ),
     * @OA\Parameter(
     * name="onsale",
     * in="query",
     * description="On Sale",
     * required=true,
     * @OA\Schema(
     *              type="integer"
     *          )
     * ),
     *   @OA\Response(
     *     response=200,
     *     description="Inventory has been updated successfully."
     *   ),
     *   @OA\Response(
     *     response="default",
     *     description="Unable to update the inventory."
     *   )
     * )
     */
    public function update(Request $request, $uid)
    {
        DB::beginTransaction();
        // api/inventory/{inventoryid} (PUT)
        error_log($this->controllerName.'Updating inventory of uid: ' . $uid);
        $inventory = $this->getInventory($uid);

        $this->validate($request, [
            'storeid' => 'required',
            'name' => 'required|string|max:191',
            'code' => 'nullable',
            'sku' => 'required|string|max:191',
            'desc' => 'nullable',
            'cost' => 'required|numeric|min:0',
            'price' => 'required|numeric|min:0',
            'qty' => 'required|numeric|min:0',
            'onsale' => 'required|numeric',
        ]);

        if ($this->isEmpty($inventory)) {
            DB::rollBack();
            $data['data'] = null;
            $data['msg'] = $this->getNotFoundMsg('Inventory');
            $data['status'] = 'error';
            $data['code'] = 404;
            return response()->json($data, 404);
        }

        $params = collect([
            'storeid' => $request->storeid,
            'promotionid' => $request->promotionid,
            'warrantyid' => $request->warrantyid,
            'shippingid' => $request->shippingid,
            'name' => $request->name,
            'code' => $request->code,
            'sku' => $request->sku,
            'imgpath' => $request->imgpath,
            'desc' => $request->desc,
            'cost' => $request->cost,
            'price' => $request->price,
            'qty' => $request->qty,
            'stockthreshold' => $request->stockthreshold,
            'onsale' => $request->onsale,
        ]);

        //Convert To Json Object
        $params = json_decode(json_encode($params));
        $inventory = $this->updateInventory($inventory, $params);
        if ($this->isEmpty($inventory)) {
            DB::rollBack();
            $data['data'] = null;
            $data['msg'] = $this->getErrorMsg('Inventory');
            $data['status'] = 'error';
            $data['code'] = 404;
            return response()->json($data, 404);
        } else {
            DB::commit();
            $data['status'] = 'success';
            $data['msg'] = $this->getUpdatedSuccessMsg('Inventory');
            $data['data'] = $inventory;
            $data['status'] = 'success';
            $data['code'] = 200;
            return response()->json($data, 200);
        }
    }

    /**
     * @OA\Delete(
     *   tags={"InventoryControllerService"},
     *   path="/api/inventory/{uid}",
     *   summary="Set inventory's 'status' to 0.",
     *     operationId="deleteInventoryByUid",
     *   @OA\Parameter(
     *     name="uid",
     *     in="path",
     *     description="Inventory ID, NOT 'ID'.",
     *     required=true,
     *     @OA\SChema(type="string")
     *   ),
     *   @OA\Response(
     *     response=200,
     *     description="Inventory has been 'deleted' successfully."
     *   ),
     *   @OA\Response(
     *     response="default",
     *     description="Unable to 'delete' the inventory."
     *   )
     * )
     */
    public function destroy(Request $request, $uid)
    {
        DB::beginTransaction();
        // TODO ONLY TOGGLES THE status = 1/0
        // api/inventory/{inventoryid} (DELETE)
        error_log('Deleting inventory of uid: ' . $uid);
        $inventory = $this->getInventory($uid);
        if ($this->isEmpty($inventory)) {
            DB::rollBack();
            $data['status'] = 'error';
            $data['msg'] = $this->getNotFoundMsg('Inventory');
            $data['data'] = null;
            $data['code'] = 404;
            return response()->json($data, 404);
        }
        $inventory = $this->deleteInventory($inventory);
        $this->createLog($request->user()->id , [$inventory->id], 'delete', 'inventory');
        if ($this->isEmpty($inventory)) {
            DB::rollBack();
            $data['status'] = 'error';
            $data['msg'] = $this->getErrorMsg();
            $data['data'] = null;
            $data['code'] = 404;
            return response()->json($data, 404);
        } else {
            DB::commit();
            $data['status'] = 'success';
            $data['msg'] = $this->getDeletedSuccessMsg('Inventory');
            $data['data'] = null;
            $data['code'] = 200;
            return response()->json($data, 200);
        }
    }


    /**
     * @OA\Get(
     *   tags={"InventoryControllerService"},
     *   path="/api/onsale/inventory/{uid}",
     *   summary="Retrieves onsale inventory by Uid.",
     *     operationId="getOnSaleInventoryByUid",
     *   @OA\Parameter(
     *     name="uid",
     *     in="path",
     *     description="Inventory_ID, NOT 'ID'.",
     *     required=true,
     *     @OA\Schema(type="string")
     *   ),
     *   @OA\Response(
     *     response=200,
     *     description="Inventory has been retrieved successfully."
     *   ),
     *   @OA\Response(
     *     response="default",
     *     description="Unable to retrieve the inventory."
     *   )
     * )
     */
    public function getOnSaleInventory(Request $request, $uid)
    {
        // api/inventory/{inventoryid} (GET)
        error_log($this->controllerName.'Retrieving onsale inventory of uid:' . $uid);
        $cols = $this->inventoryDefaultCols();
        $inventory = $this->getInventory($uid);
        if($inventory->onsale){
            $inventory = $this->itemPluckCols($inventory , $cols);
            $inventory = json_decode(json_encode($inventory));
            $inventory = $this->calculatePromotionPrice($inventory);
            $inventory = $this->countProductReviews($inventory);
        }else{
            $inventory = null;
        }
        if ($this->isEmpty($inventory)) {
            $data['data'] = null;
            $data['msg'] = $this->getNotFoundMsg('Inventory');
            $data['status'] = 'error';
            $data['code'] = 404;
            return response()->json($data, 404);
        } else {
            $data['data'] = $inventory;
            $data['msg'] = $this->getRetrievedSuccessMsg('Inventory');
            $data['status'] = 'success';
            $data['code'] = 200;
            return response()->json($data, 200);
        }
    }

}
