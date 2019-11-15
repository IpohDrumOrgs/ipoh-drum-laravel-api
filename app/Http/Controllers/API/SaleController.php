<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use DB;
use Carbon\Carbon;
use App\Sale;
use Illuminate\Support\Facades\Hash;
use App\Traits\GlobalFunctions;
use App\Traits\NotificationFunctions;
use App\Traits\SaleServices;
use App\Traits\LogServices;

class SaleController extends Controller
{
    use GlobalFunctions, NotificationFunctions, SaleServices, LogServices;

    /**
     * @OA\Get(
     *      path="/api/sale",
     *      operationId="getSaleList",
     *      tags={"SaleControllerService"},
     *      summary="Get list of sales",
     *      description="Returns list of sales",
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
     *          description="Successfully retrieved list of sales"
     *       ),
     *       @OA\Response(
     *          response="default",
     *          description="Unable to retrieve list of sales")
     *    )
     */
    public function index(Request $request)
    {
        error_log('Retrieving list of sales.');
        // api/sale (GET)
        $sales = $this->getSaleListing($request->user());
        if ($this->isEmpty($sales)) {
            $data['status'] = 'error';
            $data['data'] = null;
            $data['maximumPages'] = 0;
            $data['msg'] = $this->getNotFoundMsg('Sales');
            $data['code'] = 404;
            return response()->json($data, 404);
        } else {
            //Page Pagination Result List
            //Default return 10
            $paginateddata = $this->paginateResult($sales, $request->pageSize, $request->pageNumber);
            $data['status'] = 'success';
            $data['data'] = $paginateddata;
            $data['maximumPages'] = $this->getMaximumPaginationPage($sales->count(), $request->pageSize);
            $data['msg'] = $this->getRetrievedSuccessMsg('Sales');
            $data['code'] = 200;
            return response()->json($data, 200);
        }
    }
    /**
     * @OA\Get(
     *      path="/api/pluck/sales",
     *      operationId="pluckSaleList",
     *      tags={"SaleControllerService"},
     *      summary="pluck list of sales",
     *      description="Returns list of plucked sales",
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
     *          description="Successfully retrieved list of sales"
     *       ),
     *       @OA\Response(
     *          response="default",
     *          description="Unable to retrieve list of sales")
     *    )
     */
    public function pluckIndex(Request $request)
    {
        error_log('Retrieving list of plucked sales.');
        // api/pluck/sales (GET)
        error_log("columns = " . collect($this->splitToArray($request->cols)));
        $sales = $this->pluckSaleIndex($this->splitToArray($request->cols));
        if ($this->isEmpty($sales)) {
            $data['status'] = 'error';
            $data['data'] = null;
            $data['maximumPages'] = 0;
            $data['msg'] = $this->getNotFoundMsg('Sales');
            $data['code'] = 404;
            return response()->json($data, 404);
        } else {
            //Page Pagination Result List
            //Default return 10
            $paginateddata = $this->paginateResult($sales, $request->pageSize, $request->pageNumber);
            $data['status'] = 'success';
            $data['data'] = $paginateddata;
            $data['maximumPages'] = $this->getMaximumPaginationPage($sales->count(), $request->pageSize);
            $data['msg'] = $this->getRetrievedSuccessMsg('Sales');
            $data['code'] = 200;
            return response()->json($data, 200);
        }
    }

    /**
     * @OA\Get(
     *      path="/api/filter/sale",
     *      operationId="filterSaleList",
     *      tags={"SaleControllerService"},
     *      summary="Filter list of sales",
     *      description="Returns list of filtered sales",
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
     *          description="Successfully retrieved list of filtered sales"
     *       ),
     *       @OA\Response(
     *          response="default",
     *          description="Unable to retrieve list of sales")
     *    )
     */
    public function filter(Request $request)
    {
        error_log('Retrieving list of filtered sales.');
        // api/sale/filter (GET)
        $params = collect([
            'keyword' => $request->keyword,
            'fromdate' => $request->fromdate,
            'todate' => $request->todate,
            'status' => $request->status,
            'onsale' => $request->onsale,
        ]);
        //Convert To Json Object
        $params = json_decode(json_encode($params));
        $sales = $this->filterSaleListing($request->user(), $params);

        if ($this->isEmpty($sales)) {
            $data['data'] = null;
            $data['maximumPages'] = 0;
            $data['msg'] = $this->getNotFoundMsg('Sales');
            $data['code'] = 404;
            return response()->json($data, 404);
        } else {
            //Page Pagination Result List
            //Default return 10
            $paginateddata = $this->paginateResult($sales, $request->pageSize, $request->pageNumber);
            $data['data'] = $paginateddata;
            $data['maximumPages'] = $this->getMaximumPaginationPage($sales->count(), $request->pageSize);
            $data['msg'] = $this->getRetrievedSuccessMsg('Sales');
            $data['code'] = 200;
            return response()->json($data, 200);
        }

    }

    /**
     * @OA\Get(
     *      path="/api/pluck/filter/sale",
     *      operationId="filterPluckedSaleList",
     *      tags={"SaleControllerService"},
     *      summary="Filter list of plucked sales",
     *      description="Returns list of filtered sales",
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
     *          description="Successfully retrieved list of filtered sales"
     *       ),
     *       @OA\Response(
     *          response="default",
     *          description="Unable to retrieve list of sales")
     *    )
     */
    public function pluckFilter(Request $request)
    {
        error_log('Retrieving list of filtered and plucked sales.');
        // api/pluck/filter/sale (GET)
        $params = collect([
            'keyword' => $request->keyword,
            'fromdate' => $request->fromdate,
            'todate' => $request->todate,
            'status' => $request->status,
            'onsale' => $request->onsale,
        ]);
        //Convert To Json Object
        $params = json_decode(json_encode($params));
        $sales = $this->pluckSaleFilter($this->splitToArray($request->cols) , $params);

        if ($this->isEmpty($sales)) {
            $data['data'] = null;
            $data['maximumPages'] = 0;
            $data['msg'] = $this->getNotFoundMsg('Sales');
            $data['code'] = 404;
            return response()->json($data, 404);
        } else {
            //Page Pagination Result List
            //Default return 10
            $paginateddata = $this->paginateResult($sales, $request->pageSize, $request->pageNumber);
            $data['data'] = $paginateddata;
            $data['maximumPages'] = $this->getMaximumPaginationPage($sales->count(), $request->pageSize);
            $data['msg'] = $this->getRetrievedSuccessMsg('Sales');
            $data['code'] = 200;
            return response()->json($data, 200);
        }

    }

    /**
     * @OA\Get(
     *   tags={"SaleControllerService"},
     *   path="/api/sale/{uid}",
     *   summary="Retrieves sale by Uid.",
     *     operationId="getSaleByUid",
     *   @OA\Parameter(
     *     name="uid",
     *     in="path",
     *     description="Sale_ID, NOT 'ID'.",
     *     required=true,
     *     @OA\Schema(type="string")
     *   ),
     *   @OA\Response(
     *     response=200,
     *     description="Sale has been retrieved successfully."
     *   ),
     *   @OA\Response(
     *     response="default",
     *     description="Unable to retrieve the sale."
     *   )
     * )
     */
    public function show(Request $request, $uid)
    {
        // api/sale/{saleid} (GET)
        error_log('Retrieving sale of uid:' . $uid);
        $sale = $this->getSale($request->user(), $uid);
        if ($this->isEmpty($sale)) {
            $data['data'] = null;
            $data['msg'] = $this->getNotFoundMsg('Sale');
            $data['status'] = 'error';
            $data['code'] = 404;
            return response()->json($data, 404);
        } else {
            $data['data'] = $sale;
            $data['msg'] = $this->getRetrievedSuccessMsg('Sale');
            $data['status'] = 'success';
            $data['code'] = 200;
            return response()->json($data, 200);
        }
    }

    /**
     * @OA\Get(
     *      path="/api/pluck/sale/{uid}",
     *      operationId="pluckSaleByUid",
     *      tags={"SaleControllerService"},
     *      summary="pluck sale",
     *      description="Returns plucked sales",
     *   @OA\Parameter(
     *     name="uid",
     *     in="path",
     *     description="Sale_ID, NOT 'ID'.",
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
     *          description="Successfully retrieved list of sales"
     *       ),
     *       @OA\Response(
     *          response="default",
     *          description="Unable to retrieve list of sales")
     *    )
     */
    public function pluckShow(Request $request , $uid)
    {
        error_log('Retrieving plucked sales.');
        // api/pluck/sale/{uid} (GET)
        error_log("columns = " . collect($this->splitToArray($request->cols)));
        $sale = $this->pluckSale($this->splitToArray($request->cols) , $uid);
        if ($this->isEmpty($sale)) {
            $data['data'] = null;
            $data['status'] = 'error';
            $data['msg'] = $this->getNotFoundMsg('Sale');
            $data['code'] = 404;
            return response()->json($data, 404);
        } else {
            $data['status'] = 'success';
            $data['msg'] = $this->getRetrievedSuccessMsg('Sale');
            $data['data'] = $sale;
            $data['code'] = 200;
            return response()->json($data, 200);
        }
    }

    
    /**
     * @OA\Post(
     *   tags={"SaleControllerService"},
     *   path="/api/sale",
     *   summary="Creates a sale.",
     *   operationId="createSale",
     * @OA\Parameter(
     * name="name",
     * in="query",
     * description="Salename",
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
     *     description="Sale has been created successfully."
     *   ),
     *   @OA\Response(
     *     response="default",
     *     description="Unable to create the sale."
     *   )
     * )
     */
    public function store(Request $request)
    {
        DB::beginTransaction();
        // Can only be used by Authorized personnel
        // api/sale (POST)
        
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
        error_log('Creating sale.');
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
        $sale = $this->createSale($request->user(), $params);

        if ($this->isEmpty($sale)) {
            DB::rollBack();
            $data['data'] = null;
            $data['status'] = 'error';
            $data['msg'] = $this->getErrorMsg();
            $data['code'] = 404;
            return response()->json($data, 404);
        } else {
            DB::commit();
            $data['status'] = 'success';
            $data['msg'] = $this->getCreatedSuccessMsg('Sale');
            $data['data'] = $sale;
            $data['code'] = 200;
            return response()->json($data, 200);
        }
    }


    /**
     * @OA\Put(
     *   tags={"SaleControllerService"},
     *   path="/api/sale/{uid}",
     *   summary="Update sale by Uid.",
     *     operationId="updateSaleByUid",
     *   @OA\Parameter(
     *     name="uid",
     *     in="path",
     *     description="Sale_ID, NOT 'ID'.",
     *     required=true,
     *     @OA\Schema(type="string")
     *   ),
   * @OA\Parameter(
     * name="name",
     * in="query",
     * description="Salename",
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
     * name="userid",
     * in="query",
     * description="User ID",
     * required=true,
     * @OA\Schema(
     *              type="integer"
     *          )
     * ),
     * @OA\Parameter(
     * name="sono",
     * in="query",
     * description="Sale Order No",
     * @OA\Schema(
     *              type="string"
     *          )
     * ),
     * @OA\Parameter(
     * name="sku",
     * in="query",
     * description="totalqty",
     * required=true,
     * @OA\Schema(
     *              type="integer"
     *          )
     * ),
     * @OA\Parameter(
     * name="linetotal",
     * in="query",
     * description="Line Total",
     * @OA\Schema(
     *              type="number"
     *          )
     * ),
     * @OA\Parameter(
     * name="totalcost",
     * in="query",
     * required=true,
     * description="All Product Cost",
     * @OA\Schema(
     *              type="number"
     *          )
     * ),
     * @OA\Parameter(
     * name="totaldisc",
     * required=true,
     * in="query",
     * description="Total Discount",
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
     *     description="Sale has been updated successfully."
     *   ),
     *   @OA\Response(
     *     response="default",
     *     description="Unable to update the sale."
     *   )
     * )
     */
    public function update(Request $request, $uid)
    {
        DB::beginTransaction();
        // api/sale/{saleid} (PUT) 
        error_log('Updating sale of uid: ' . $uid);
        $sale = $this->getSale($request->user(), $uid);
       
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
      
        if ($this->isEmpty($sale)) {
            DB::rollBack();
            $data['data'] = null;
            $data['msg'] = $this->getNotFoundMsg('Sale');
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
        $sale = $this->updateSale($request->user(), $sale, $params);
        if ($this->isEmpty($sale)) {
            DB::rollBack();
            $data['data'] = null;
            $data['msg'] = $this->getErrorMsg('Sale');
            $data['status'] = 'error';
            $data['code'] = 404;
            return response()->json($data, 404);
        } else {
            DB::commit();
            $data['status'] = 'success';
            $data['msg'] = $this->getUpdatedSuccessMsg('Sale');
            $data['data'] = $sale;
            $data['status'] = 'success';
            $data['code'] = 200;
            return response()->json($data, 200);
        }
    }

    /**
     * @OA\Delete(
     *   tags={"SaleControllerService"},
     *   path="/api/sale/{uid}",
     *   summary="Set sale's 'status' to 0.",
     *     operationId="deleteSaleByUid",
     *   @OA\Parameter(
     *     name="uid",
     *     in="path",
     *     description="Sale ID, NOT 'ID'.",
     *     required=true,
     *     @OA\SChema(type="string")
     *   ),
     *   @OA\Response(
     *     response=200,
     *     description="Sale has been 'deleted' successfully."
     *   ),
     *   @OA\Response(
     *     response="default",
     *     description="Unable to 'delete' the sale."
     *   )
     * )
     */
    public function destroy(Request $request, $uid)
    {
        DB::beginTransaction();
        // TODO ONLY TOGGLES THE status = 1/0
        // api/sale/{saleid} (DELETE)
        error_log('Deleting sale of uid: ' . $uid);
        $sale = $this->getSale($request->user(), $uid);
        if ($this->isEmpty($sale)) {
            DB::rollBack();
            $data['status'] = 'error';
            $data['msg'] = $this->getNotFoundMsg('Sale');
            $data['data'] = null;
            $data['code'] = 404;
            return response()->json($data, 404);
        }
        $sale = $this->deleteSale($request->user(), $sale->id);
        if ($this->isEmpty($sale)) {
            DB::rollBack();
            $data['status'] = 'error';
            $data['msg'] = $this->getErrorMsg();
            $data['data'] = null;
            $data['code'] = 404;
            return response()->json($data, 404);
        } else {
            DB::commit();
            $data['status'] = 'success';
            $data['msg'] = $this->getDeletedSuccessMsg('Sale');
            $data['data'] = $sale;
            $data['code'] = 200;
            return response()->json($data, 200);
        }
    }

}
