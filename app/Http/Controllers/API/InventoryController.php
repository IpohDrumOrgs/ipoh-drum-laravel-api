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

    /**
     * @OA\Get(
     *      path="/api/inventory",
     *      operationId="getInventoryList",
     *      tags={"InventoryControllerService"},
     *      summary="Get list of inventories",
     *      description="Returns list of inventories",
     *   @OA\Parameter(
     *     name="pageNumber",
     *     in="query",
     *     description="Page number.",
     *     @OA\Schema(type="integer")
     *   ),
     *   @OA\Parameter(
     *     name="pageSize",
     *     in="query",
     *     description="Page size.",
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
        $inventories = $this->getInventoryListing($request->user());
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
            $paginateddata = $this->paginateResult($inventories, $request->result, $request->page);
            $data['status'] = 'success';
            $data['data'] = $paginateddata;
            $data['maximumPages'] = $this->getMaximumPaginationPage($inventories->count(), $request->result);
            $data['msg'] = $this->getRetrievedSuccessMsg('Inventories');
            $data['code'] = 200;
            return response()->json($data, 200);
        }
    }
    /**
     * @OA\Get(
     *      path="/api/pluck/inventories",
     *      operationId="pluckInventoryList",
     *      tags={"InventoryControllerService"},
     *      summary="pluck list of inventories",
     *      description="Returns list of plucked inventories",
     *   @OA\Parameter(
     *     name="pageNumber",
     *     in="query",
     *     description="Page number",
     *     @OA\Schema(type="integer")
     *   ),
     *   @OA\Parameter(
     *     name="pageSize",
     *     in="query",
     *     description="Page size",
     *     @OA\Schema(type="integer")
     *   ),
     *   @OA\Parameter(
     *     name="cols",
     *     in="query",
     *     required=true,
     *     description="Columns for pluck",
     *     @OA\Schema(type="string")
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
    public function pluckIndex(Request $request)
    {
        error_log('Retrieving list of plucked inventories.');
        // api/pluck/inventories (GET)
        error_log("columns = " . collect($this->splitToArray($request->cols)));
        $inventories = $this->pluckInventoryIndex($this->splitToArray($request->cols));
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
            $paginateddata = $this->paginateResult($inventories, $request->result, $request->page);
            $data['status'] = 'success';
            $data['data'] = $paginateddata;
            $data['maximumPages'] = $this->getMaximumPaginationPage($inventories->count(), $request->result);
            $data['msg'] = $this->getRetrievedSuccessMsg('Inventories');
            $data['code'] = 200;
            return response()->json($data, 200);
        }
    }

    /**
     * @OA\Get(
     *      path="/api/filter/inventory",
     *      operationId="filterInventoryList",
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
     *     description="Page size",
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
     *     description="To string for filter",
     *     @OA\Schema(type="string")
     *   ),
     *   @OA\Parameter(
     *     name="status",
     *     in="query",
     *     description="status for filter",
     *     @OA\Schema(type="string")
     *   ),
     *   @OA\Parameter(
     *     name="onsale",
     *     in="query",
     *     description="onsale for filter",
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
        ]);
        //Convert To Json Object
        $params = json_decode(json_encode($params));
        $inventories = $this->filterInventoryListing($request->user(), $params);

        if ($this->isEmpty($inventories)) {
            $data['data'] = null;
            $data['maximumPages'] = 0;
            $data['msg'] = $this->getNotFoundMsg('Inventories');
            $data['code'] = 404;
            return response()->json($data, 404);
        } else {
            //Page Pagination Result List
            //Default return 10
            $paginateddata = $this->paginateResult($inventories, $request->result, $request->page);
            $data['data'] = $paginateddata;
            $data['maximumPages'] = $this->getMaximumPaginationPage($inventories->count(), $request->result);
            $data['msg'] = $this->getRetrievedSuccessMsg('Inventories');
            $data['code'] = 200;
            return response()->json($data, 200);
        }

    }

    /**
     * @OA\Get(
     *      path="/api/pluck/filter/inventory",
     *      operationId="filterPluckedInventoryList",
     *      tags={"InventoryControllerService"},
     *      summary="Filter list of plucked inventories",
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
     *     description="Page size",
     *     @OA\Schema(type="integer")
     *   ),
     *   @OA\Parameter(
     *     name="cols",
     *     in="query",
     *     required=true,
     *     description="Columns for pluck",
     *     @OA\Schema(type="string")
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
     *     description="To string for filter",
     *     @OA\Schema(type="string")
     *   ),
     *   @OA\Parameter(
     *     name="status",
     *     in="query",
     *     description="status for filter",
     *     @OA\Schema(type="string")
     *   ),
     *   @OA\Parameter(
     *     name="onsale",
     *     in="query",
     *     description="onsale for filter",
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
    public function pluckFilter(Request $request)
    {
        error_log('Retrieving list of filtered and plucked inventories.');
        // api/pluck/filter/inventory (GET)
        $params = collect([
            'keyword' => $request->keyword,
            'fromdate' => $request->fromdate,
            'todate' => $request->todate,
            'status' => $request->status,
            'onsale' => $request->onsale,
        ]);
        //Convert To Json Object
        $params = json_decode(json_encode($params));
        $inventories = $this->pluckInventoryFilter($this->splitToArray($request->cols) , $params);

        if ($this->isEmpty($inventories)) {
            $data['data'] = null;
            $data['maximumPages'] = 0;
            $data['msg'] = $this->getNotFoundMsg('Inventories');
            $data['code'] = 404;
            return response()->json($data, 404);
        } else {
            //Page Pagination Result List
            //Default return 10
            $paginateddata = $this->paginateResult($inventories, $request->result, $request->page);
            $data['data'] = $paginateddata;
            $data['maximumPages'] = $this->getMaximumPaginationPage($inventories->count(), $request->result);
            $data['msg'] = $this->getRetrievedSuccessMsg('Inventories');
            $data['code'] = 200;
            return response()->json($data, 200);
        }

    }

    /**
     * @OA\Get(
     *   tags={"InventoryControllerService"},
     *   path="/api/inventory/{uid}",
     *   summary="Retrieves inventory by inventoryId.",
     *     operationId="getInventoryByInventoryId",
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
        $inventory = $this->getInventory($request->user(), $uid);
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
     * @OA\Get(
     *      path="/api/pluck/inventory/{uid}",
     *      operationId="pluckInventory",
     *      tags={"InventoryControllerService"},
     *      summary="pluck inventory",
     *      description="Returns plucked inventories",
     *   @OA\Parameter(
     *     name="uid",
     *     in="path",
     *     description="Inventory_ID, NOT 'ID'.",
     *     required=true,
     *     @OA\Schema(type="string")
     *   ),
     *   @OA\Parameter(
     *     name="cols",
     *     in="query",
     *     required=true,
     *     description="Columns for pluck",
     *     @OA\Schema(type="string")
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
    public function pluckShow(Request $request , $uid)
    {
        error_log('Retrieving plucked inventories.');
        // api/pluck/inventory/{uid} (GET)
        error_log("columns = " . collect($this->splitToArray($request->cols)));
        $inventory = $this->pluckInventory($this->splitToArray($request->cols) , $uid);
        if ($this->isEmpty($inventory)) {
            $data['data'] = null;
            $data['status'] = 'error';
            $data['msg'] = $this->getNotFoundMsg('Inventory');
            $data['code'] = 404;
            return response()->json($data, 404);
        } else {
            $data['status'] = 'success';
            $data['msg'] = $this->getRetrievedSuccessMsg('Inventory');
            $data['data'] = $inventory;
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
     *              type="string"
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
     * name="disc",
     * in="query",
     * description="Product Discount",
     * @OA\Schema(
     *              type="number"
     *          )
     * ),
     * @OA\Parameter(
     * name="promoprice",
     * in="query",
     * description="Promotion Price",
     * @OA\Schema(
     *              type="string"
     *          )
     * ),
     * @OA\Parameter(
     * name="promostartdate",
     * in="query",
     * description="Promotion Start Date",
     * @OA\Schema(
     *              type="string"
     *          )
     * ),
     * @OA\Parameter(
     * name="promoenddate",
     * in="query",
     * description="Promotion End Date",
     * @OA\Schema(
     *              type="string"
     *          )
     * ),
     * @OA\Parameter(
     * name="stock",
     * in="query",
     * description="Stock Qty",
     * required=true,
     * @OA\Schema(
     *              type="integer"
     *          )
     * ),
     * @OA\Parameter(
     * name="warrantyperiod",
     * in="query",
     * description="Warranty Period",
     * @OA\Schema(
     *  type="integer"
     *  )
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
            'stock' => 'required|numeric|min:0',
            'onsale' => 'required|numeric',
        ]);
        error_log('Creating inventory.');
        $params = collect([
            'storeid' => $request->storeid,
            'name' => $request->name,
            'code' => $request->code,
            'sku' => $request->sku,
            'desc' => $request->desc,
            'cost' => $request->cost,
            'price' => $request->price,
            'disc' => $request->disc,
            'promoprice' => $request->promoprice,
            'promostartdate' => $request->promostartdate,
            'promoenddate' => $request->promoenddate,
            'stock' => $request->stock,
            'warrantyperiod' => $request->warrantyperiod,
            'stockthreshold' => $request->stockthreshold,
            'onsale' => $request->onsale,
        ]);
        //Convert To Json Object
        $params = json_decode(json_encode($params));
        $inventory = $this->createInventory($request->user(), $params);

        if ($this->isEmpty($inventory)) {
            $data['data'] = null;
            $data['status'] = 'error';
            $data['msg'] = $this->getErrorMsg();
            $data['code'] = 404;
            return response()->json($data, 404);
        } else {
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
     *   summary="Update inventory by inventoryId.",
     *     operationId="updateInventoryByInventoryId",
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
     *              type="string"
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
     * name="cost",
     * in="query",
     * required=true,
     * description="Product Cost",
     * @OA\Schema(
     *              type="number"
     *          )
     * ),
     * @OA\Parameter(
     * name="price",
     * required=true,
     * in="query",
     * description="Product Selling Price",
     * @OA\Schema(
     *              type="number"
     *          )
     * ),
     * @OA\Parameter(
     * name="disc",
     * in="query",
     * description="Product Discount",
     * @OA\Schema(
     *              type="number"
     *          )
     * ),
     * @OA\Parameter(
     * name="promoprice",
     * in="query",
     * description="Promotion Price",
     * @OA\Schema(
     *              type="string"
     *          )
     * ),
     * @OA\Parameter(
     * name="promostartdate",
     * in="query",
     * description="Promotion Start Date",
     * @OA\Schema(
     *              type="string"
     *          )
     * ),
     * @OA\Parameter(
     * name="promoenddate",
     * in="query",
     * description="Promotion End Date",
     * @OA\Schema(
     *              type="string"
     *          )
     * ),
     * @OA\Parameter(
     * name="stock",
     * in="query",
     * required=true,
     * description="Stock Qty",
     * @OA\Schema(
     *              type="integer"
     *          )
     * ),
     * @OA\Parameter(
     * name="warrantyperiod",
     * in="query",
     * description="Warranty Period",
     * @OA\Schema(
     *  type="integer"
     *  )
     * ),
     * @OA\Parameter(
     * name="stockthreshold",
     * in="query",
     * required=true,
     * description="Stock Threshold",
     * @OA\Schema(
     *              type="integer"
     *          )
     * ),
     * @OA\Parameter(
     * name="onsale",
     * in="query",
     * required=true,
     * description="On Sale",
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
        // api/inventory/{inventoryid} (PUT) 
        error_log('Updating inventory of uid: ' . $uid);
        $inventory = $this->getInventory($request->user(), $uid);
       
        $this->validate($request, [
            'storeid' => 'required',
            'name' => 'required|string|max:191',
            'code' => 'nullable',
            'sku' => 'required|string|max:191',
            'desc' => 'nullable',
            'cost' => 'required|numeric|min:0',
            'price' => 'required|numeric|min:0',
            'disc' => 'nullable|numeric|min:0',
            'promoprice' => 'nullable|numeric|min:0',
            'promostartdate' => 'nullable|date',
            'promoenddate' => 'nullable|date',
            'stock' => 'required|numeric|min:0',
            'warrantyperiod' => 'nullable|numeric|min:0',
            'stockthreshold' => 'nullable|numeric|min:0',
            'onsale' => 'required|boolean',
        ]);
      
        if ($this->isEmpty($inventory)) {
            $data['data'] = null;
            $data['msg'] = $this->getNotFoundMsg('Inventory');
            $data['status'] = 'error';
            $data['code'] = 404;
            return response()->json($data, 404);
        }
        
        $params = collect([
            'storeid' => $request->storeid,
            'name' => $request->name,
            'code' => $request->code,
            'sku' => $request->sku,
            'desc' => $request->desc,
            'cost' => $request->cost,
            'price' => $request->price,
            'disc' => $request->disc,
            'promoprice' => $request->promoprice,
            'promostartdate' => $request->promostartdate,
            'promoenddate' => $request->promoenddate,
            'stock' => $request->stock,
            'warrantyperiod' => $request->warrantyperiod,
            'stockthreshold' => $request->stockthreshold,
            'onsale' => $request->onsale,
        ]);
        
        //Convert To Json Object
        $params = json_decode(json_encode($params));
        $inventory = $this->updateInventory($request->user(), $inventory, $params);
        if ($this->isEmpty($inventory)) {
            $data['data'] = null;
            $data['msg'] = $this->getErrorMsg('Inventory');
            $data['status'] = 'error';
            $data['code'] = 404;
            return response()->json($data, 404);
        } else {
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
     *     operationId="deleteInventoryByInventoryId",
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
        // TODO ONLY TOGGLES THE status = 1/0
        // api/inventory/{inventoryid} (DELETE)
        error_log('Deleting inventory of uid: ' . $uid);
        $inventory = $this->getInventory($request->user(), $uid);
        if ($this->isEmpty($inventory)) {
            $data['status'] = 'error';
            $data['msg'] = $this->getNotFoundMsg('Inventory');
            $data['data'] = null;
            $data['code'] = 404;
            return response()->json($data, 404);
        }
        $inventory = $this->deleteInventory($request->user(), $inventory->id);
        if ($this->isEmpty($inventory)) {
            $data['status'] = 'error';
            $data['msg'] = $this->getErrorMsg();
            $data['data'] = null;
            $data['code'] = 404;
            return response()->json($data, 404);
        } else {
            $data['status'] = 'success';
            $data['msg'] = $this->getDeletedSuccessMsg('Inventory');
            $data['data'] = $inventory;
            $data['code'] = 200;
            return response()->json($data, 200);
        }
    }

}
