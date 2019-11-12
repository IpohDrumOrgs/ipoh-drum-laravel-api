<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use DB;
use Carbon\Carbon;
use App\ProductFeature;
use Illuminate\Support\Facades\Hash;
use App\Traits\GlobalFunctions;
use App\Traits\NotificationFunctions;
use App\Traits\ProductFeatureServices;
use App\Traits\LogServices;

class ProductFeatureController extends Controller
{
    use GlobalFunctions, NotificationFunctions, ProductFeatureServices, LogServices;

    /**
     * @OA\Get(
     *      path="/api/productfeature",
     *      operationId="getProductFeatureList",
     *      tags={"ProductFeatureControllerService"},
     *      summary="Get list of productfeatures",
     *      description="Returns list of productfeatures",
     *   @OA\Parameter(
     *     name="page",
     *     in="query",
     *     description="Page number",
     *     @OA\Schema(type="integer")
     *   ),
     *   @OA\Parameter(
     *     name="result",
     *     in="query",
     *     description="number of result",
     *     @OA\Schema(type="integer")
     *   ),
     *      @OA\Response(
     *          response=200,
     *          description="Successfully retrieved list of productfeatures"
     *       ),
     *       @OA\Response(
     *          response="default",
     *          description="Unable to retrieve list of productfeatures")
     *    )
     */
    public function index(Request $request)
    {
        error_log('Retrieving list of productfeatures.');
        // api/productfeature (GET)
        $productfeatures = $this->getProductFeatureListing($request->user());
        if ($this->isEmpty($productfeatures)) {
            $data['status'] = 'error';
            $data['data'] = null;
            $data['maximumPages'] = 0;
            $data['msg'] = $this->getNotFoundMsg('Product Features');
            $data['code'] = 404;
            return response()->json($data, 404);
        } else {
            //Page Pagination Result List
            //Default return 10
            $paginateddata = $this->paginateResult($productfeatures, $request->result, $request->page);
            $data['status'] = 'success';
            $data['data'] = $paginateddata;
            $data['maximumPages'] = $this->getMaximumPaginationPage($productfeatures->count(), $request->result);
            $data['msg'] = $this->getRetrievedSuccessMsg('Product Features');
            $data['code'] = 200;
            return response()->json($data, 200);
        }
    }
    /**
     * @OA\Get(
     *      path="/api/pluck/productfeatures",
     *      operationId="pluckProductFeatureList",
     *      tags={"ProductFeatureControllerService"},
     *      summary="pluck list of productfeatures",
     *      description="Returns list of plucked productfeatures",
     *   @OA\Parameter(
     *     name="page",
     *     in="query",
     *     description="Page number",
     *     @OA\Schema(type="integer")
     *   ),
     *   @OA\Parameter(
     *     name="result",
     *     in="query",
     *     description="number of result",
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
     *          description="Successfully retrieved list of productfeatures"
     *       ),
     *       @OA\Response(
     *          response="default",
     *          description="Unable to retrieve list of productfeatures")
     *    )
     */
    public function pluckIndex(Request $request)
    {
        error_log('Retrieving list of plucked productfeatures.');
        // api/pluck/productfeatures (GET)
        error_log("columns = " . collect($this->splitToArray($request->cols)));
        $productfeatures = $this->pluckProductFeatureIndex($this->splitToArray($request->cols));
        if ($this->isEmpty($productfeatures)) {
            $data['status'] = 'error';
            $data['data'] = null;
            $data['maximumPages'] = 0;
            $data['msg'] = $this->getNotFoundMsg('Product Features');
            $data['code'] = 404;
            return response()->json($data, 404);
        } else {
            //Page Pagination Result List
            //Default return 10
            $paginateddata = $this->paginateResult($productfeatures, $request->result, $request->page);
            $data['status'] = 'success';
            $data['data'] = $paginateddata;
            $data['maximumPages'] = $this->getMaximumPaginationPage($productfeatures->count(), $request->result);
            $data['msg'] = $this->getRetrievedSuccessMsg('Product Features');
            $data['code'] = 200;
            return response()->json($data, 200);
        }
    }

    /**
     * @OA\Get(
     *      path="/api/filter/productfeature",
     *      operationId="filterProductFeatureList",
     *      tags={"ProductFeatureControllerService"},
     *      summary="Filter list of productfeatures",
     *      description="Returns list of filtered productfeatures",
     *   @OA\Parameter(
     *     name="page",
     *     in="query",
     *     description="Page number",
     *     @OA\Schema(type="integer")
     *   ),
     *   @OA\Parameter(
     *     name="result",
     *     in="query",
     *     description="number of result",
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
     *      @OA\Response(
     *          response=200,
     *          description="Successfully retrieved list of filtered productfeatures"
     *       ),
     *       @OA\Response(
     *          response="default",
     *          description="Unable to retrieve list of productfeatures")
     *    )
     */
    public function filter(Request $request)
    {
        error_log('Retrieving list of filtered productfeatures.');
        // api/productfeature/filter (GET)
        $params = collect([
            'keyword' => $request->keyword,
            'fromdate' => $request->fromdate,
            'todate' => $request->todate,
            'status' => $request->status,
            'productfeature_id' => $request->productfeature_id,
        ]);
        //Convert To Json Object
        $params = json_decode(json_encode($params));
        $productfeatures = $this->filterProductFeatureListing($request->user(), $params);

        if ($this->isEmpty($productfeatures)) {
            $data['data'] = null;
            $data['maximumPages'] = 0;
            $data['msg'] = $this->getNotFoundMsg('Product Features');
            $data['code'] = 404;
            return response()->json($data, 404);
        } else {
            //Page Pagination Result List
            //Default return 10
            $paginateddata = $this->paginateResult($productfeatures, $request->result, $request->page);
            $data['data'] = $paginateddata;
            $data['maximumPages'] = $this->getMaximumPaginationPage($productfeatures->count(), $request->result);
            $data['msg'] = $this->getRetrievedSuccessMsg('Product Features');
            $data['code'] = 200;
            return response()->json($data, 200);
        }

    }

    /**
     * @OA\Get(
     *      path="/api/pluck/filter/productfeature",
     *      operationId="filterPluckedProductFeatureList",
     *      tags={"ProductFeatureControllerService"},
     *      summary="Filter list of plucked productfeatures",
     *      description="Returns list of filtered productfeatures",
     *   @OA\Parameter(
     *     name="page",
     *     in="query",
     *     description="Page number",
     *     @OA\Schema(type="integer")
     *   ),
     *   @OA\Parameter(
     *     name="result",
     *     in="query",
     *     description="number of result",
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
     *      @OA\Response(
     *          response=200,
     *          description="Successfully retrieved list of filtered productfeatures"
     *       ),
     *       @OA\Response(
     *          response="default",
     *          description="Unable to retrieve list of productfeatures")
     *    )
     */
    public function pluckFilter(Request $request)
    {
        error_log('Retrieving list of filtered and plucked productfeatures.');
        // api/pluck/filter/productfeature (GET)
        $params = collect([
            'keyword' => $request->keyword,
            'fromdate' => $request->fromdate,
            'todate' => $request->todate,
            'status' => $request->status,
            'productfeature_id' => $request->productfeature_id,
        ]);
        //Convert To Json Object
        $params = json_decode(json_encode($params));
        $productfeatures = $this->pluckProductFeatureFilter($this->splitToArray($request->cols) , $params);

        if ($this->isEmpty($productfeatures)) {
            $data['data'] = null;
            $data['maximumPages'] = 0;
            $data['msg'] = $this->getNotFoundMsg('Product Features');
            $data['code'] = 404;
            return response()->json($data, 404);
        } else {
            //Page Pagination Result List
            //Default return 10
            $paginateddata = $this->paginateResult($productfeatures, $request->result, $request->page);
            $data['data'] = $paginateddata;
            $data['maximumPages'] = $this->getMaximumPaginationPage($productfeatures->count(), $request->result);
            $data['msg'] = $this->getRetrievedSuccessMsg('Product Features');
            $data['code'] = 200;
            return response()->json($data, 200);
        }

    }

    /**
     * @OA\Get(
     *   tags={"ProductFeatureControllerService"},
     *   path="/api/productfeature/{uid}",
     *   summary="Retrieves productfeature by productfeatureId.",
     *     operationId="getProductFeatureByProductFeatureId",
     *   @OA\Parameter(
     *     name="uid",
     *     in="path",
     *     description="ProductFeature_ID, NOT 'ID'.",
     *     required=true,
     *     @OA\Schema(type="string")
     *   ),
     *   @OA\Response(
     *     response=200,
     *     description="ProductFeature has been retrieved successfully."
     *   ),
     *   @OA\Response(
     *     response="default",
     *     description="Unable to retrieve the productfeature."
     *   )
     * )
     */
    public function show(Request $request, $uid)
    {
        // api/productfeature/{productfeatureid} (GET)
        error_log('Retrieving productfeature of uid:' . $uid);
        $productfeature = $this->getProductFeature($request->user(), $uid);
        if ($this->isEmpty($productfeature)) {
            $data['data'] = null;
            $data['msg'] = $this->getNotFoundMsg('ProductFeature');
            $data['status'] = 'error';
            $data['code'] = 404;
            return response()->json($data, 404);
        } else {
            $data['data'] = $productfeature;
            $data['msg'] = $this->getRetrievedSuccessMsg('ProductFeature');
            $data['status'] = 'success';
            $data['code'] = 200;
            return response()->json($data, 200);
        }
    }

    /**
     * @OA\Get(
     *      path="/api/pluck/productfeature/{uid}",
     *      operationId="pluckProductFeature",
     *      tags={"ProductFeatureControllerService"},
     *      summary="pluck productfeature",
     *      description="Returns plucked productfeatures",
     *   @OA\Parameter(
     *     name="uid",
     *     in="path",
     *     description="ProductFeature_ID, NOT 'ID'.",
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
     *          description="Successfully retrieved list of productfeatures"
     *       ),
     *       @OA\Response(
     *          response="default",
     *          description="Unable to retrieve list of productfeatures")
     *    )
     */
    public function pluckShow(Request $request , $uid)
    {
        error_log('Retrieving plucked productfeatures.');
        // api/pluck/productfeature/{uid} (GET)
        error_log("columns = " . collect($this->splitToArray($request->cols)));
        $productfeature = $this->pluckProductFeature($this->splitToArray($request->cols) , $uid);
        if ($this->isEmpty($productfeature)) {
            $data['data'] = null;
            $data['status'] = 'error';
            $data['msg'] = $this->getNotFoundMsg('ProductFeature');
            $data['code'] = 404;
            return response()->json($data, 404);
        } else {
            $data['status'] = 'success';
            $data['msg'] = $this->getRetrievedSuccessMsg('ProductFeature');
            $data['data'] = $productfeature;
            $data['code'] = 200;
            return response()->json($data, 200);
        }
    }

    
    /**
     * @OA\Post(
     *   tags={"ProductFeatureControllerService"},
     *   path="/api/productfeature",
     *   summary="Creates a productfeature.",
     *   operationId="createProductFeature",
     * @OA\Parameter(
     * name="name",
     * in="query",
     * description="ProductFeature name",
     * required=true,
     * @OA\Schema(
     *              type="string"
     *          )
     * ),
     * @OA\Parameter(
     * name="desc",
     * in="query",
     * description="ProductFeature Description",
     * required=true,
     * @OA\Schema(
     *              type="string"
     *          )
     * ),
     *   @OA\Response(
     *     response=200,
     *     description="ProductFeature has been created successfully."
     *   ),
     *   @OA\Response(
     *     response="default",
     *     description="Unable to create the productfeature."
     *   )
     * )
     */
    public function store(Request $request)
    {
        // Can only be used by Authorized personnel
        // api/productfeature (POST)
        $this->validate($request, [
            'name' => 'required|string',
            'desc' => 'required|string',
        ]);
        error_log('Creating productfeature.');
        $params = collect([
            'name' => $request->name,
            'desc' => $request->desc,
        ]);
        //Convert To Json Object
        $params = json_decode(json_encode($params));
        $productfeature = $this->createProductFeature($request->user(), $params);

        if ($this->isEmpty($productfeature)) {
            $data['data'] = null;
            $data['status'] = 'error';
            $data['msg'] = $this->getErrorMsg();
            $data['code'] = 404;
            return response()->json($data, 404);
        } else {
            $data['status'] = 'success';
            $data['msg'] = $this->getCreatedSuccessMsg('ProductFeature');
            $data['data'] = $productfeature;
            $data['code'] = 200;
            return response()->json($data, 200);
        }
    }


    /**
     * @OA\Put(
     *   tags={"ProductFeatureControllerService"},
     *   path="/api/productfeature/{uid}",
     *   summary="Update productfeature by productfeatureId.",
     *     operationId="updateProductFeatureByProductFeatureId",
     *   @OA\Parameter(
     *     name="uid",
     *     in="path",
     *     description="ProductFeature_ID, NOT 'ID'.",
     *     required=true,
     *     @OA\Schema(type="string")
     *   ), 
     * * @OA\Parameter(
     * name="name",
     * in="query",
     * description="ProductFeature name",
     * required=true,
     * @OA\Schema(
     *              type="string"
     *          )
     * ),
     * @OA\Parameter(
     * name="desc",
     * in="query",
     * description="ProductFeature Description",
     * required=true,
     * @OA\Schema(
     *              type="string"
     *          )
     * ),
     *   @OA\Response(
     *     response=200,
     *     description="ProductFeature has been updated successfully."
     *   ),
     *   @OA\Response(
     *     response="default",
     *     description="Unable to update the productfeature."
     *   )
     * )
     */
    public function update(Request $request, $uid)
    {
        // api/productfeature/{productfeatureid} (PUT) 
        error_log('Updating productfeature of uid: ' . $uid);
        $productfeature = $this->getProductFeature($request->user(), $uid);
        error_log($productfeature);
        $this->validate($request, [
            'name' => 'required|string',
            'desc' => 'required|string',
        ]);

        if ($this->isEmpty($productfeature)) {
            $data['data'] = null;
            $data['msg'] = $this->getNotFoundMsg('ProductFeature');
            $data['status'] = 'error';
            $data['code'] = 404;
            return response()->json($data, 404);
        }
        
        $params = collect([
            'name' => $request->name,
            'desc' => $request->desc,
        ]);
        //Convert To Json Object
        $params = json_decode(json_encode($params));
        $productfeature = $this->updateProductFeature($request->user(), $productfeature, $params);
        if ($this->isEmpty($productfeature)) {
            $data['data'] = null;
            $data['msg'] = $this->getErrorMsg('ProductFeature');
            $data['status'] = 'error';
            $data['code'] = 404;
            return response()->json($data, 404);
        } else {
            $data['status'] = 'success';
            $data['msg'] = $this->getUpdatedSuccessMsg('ProductFeature');
            $data['data'] = $productfeature;
            $data['status'] = 'success';
            $data['code'] = 200;
            return response()->json($data, 200);
        }
    }

    /**
     * @OA\Delete(
     *   tags={"ProductFeatureControllerService"},
     *   path="/api/productfeature/{uid}",
     *   summary="Set productfeature's 'status' to 0.",
     *     operationId="deleteProductFeatureByProductFeatureId",
     *   @OA\Parameter(
     *     name="uid",
     *     in="path",
     *     description="ProductFeature ID, NOT 'ID'.",
     *     required=true,
     *     @OA\SChema(type="string")
     *   ),
     *   @OA\Response(
     *     response=200,
     *     description="ProductFeature has been 'deleted' successfully."
     *   ),
     *   @OA\Response(
     *     response="default",
     *     description="Unable to 'delete' the productfeature."
     *   )
     * )
     */
    public function destroy(Request $request, $uid)
    {
        // TODO ONLY TOGGLES THE status = 1/0
        // api/productfeature/{productfeatureid} (DELETE)
        error_log('Deleting productfeature of uid: ' . $uid);
        $productfeature = $this->getProductFeature($request->user(), $uid);
        if ($this->isEmpty($productfeature)) {
            $data['status'] = 'error';
            $data['msg'] = $this->getNotFoundMsg('ProductFeature');
            $data['data'] = null;
            $data['code'] = 404;
            return response()->json($data, 404);
        }
        $productfeature = $this->deleteProductFeature($request->user(), $productfeature->id);
        if ($this->isEmpty($productfeature)) {
            $data['status'] = 'error';
            $data['msg'] = $this->getErrorMsg();
            $data['data'] = null;
            $data['code'] = 404;
            return response()->json($data, 404);
        } else {
            $data['status'] = 'success';
            $data['msg'] = $this->getDeletedSuccessMsg('ProductFeature');
            $data['data'] = $productfeature;
            $data['code'] = 200;
            return response()->json($data, 200);
        }
    }

}
