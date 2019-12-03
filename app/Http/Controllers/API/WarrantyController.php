<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use DB;
use Carbon\Carbon;
use App\Warranty;
use Illuminate\Support\Facades\Hash;
use App\Traits\GlobalFunctions;
use App\Traits\NotificationFunctions;
use App\Traits\WarrantyServices;
use App\Traits\LogServices;

class WarrantyController extends Controller
{
    use GlobalFunctions, NotificationFunctions, WarrantyServices, LogServices;
    private $controllerName = '[WarrantyController]';
     /**
     * @OA\Get(
     *      path="/api/warranty",
     *      operationId="getWarranties",
     *      tags={"WarrantyControllerService"},
     *      summary="Get list of warranties",
     *      description="Returns list of warranties",
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
     *          description="Successfully retrieved list of warranties"
     *       ),
     *       @OA\Response(
     *          response="default",
     *          description="Unable to retrieve list of warranties")
     *    )
     */
    public function index(Request $request)
    {
        error_log('Retrieving list of warranties.');
        // api/warranty (GET)
        $warranties = $this->getWarranties($request->user());
        if ($this->isEmpty($warranties)) {
            $data['status'] = 'error';
            $data['data'] = null;
            $data['maximumPages'] = 0;
            $data['msg'] = $this->getNotFoundMsg('Warranties');
            $data['code'] = 404;
            return response()->json($data, 404);
        } else {
            //Page Pagination Result List
            //Default return 10
            $paginateddata = $this->paginateResult($warranties, $request->pageSize, $request->pageNumber);
            $data['status'] = 'success';
            $data['data'] = $paginateddata;
            $data['maximumPages'] = $this->getMaximumPaginationPage($warranties->count(), $request->pageSize);
            $data['msg'] = $this->getRetrievedSuccessMsg('Warranties');
            $data['code'] = 200;
            return response()->json($data, 200);
        }
    }
    
    /**
     * @OA\Get(
     *      path="/api/filter/warranty",
     *      operationId="filterWarranties",
     *      tags={"WarrantyControllerService"},
     *      summary="Filter list of warranties",
     *      description="Returns list of filtered warranties",
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
     *          description="Successfully retrieved list of filtered warranties"
     *       ),
     *       @OA\Response(
     *          response="default",
     *          description="Unable to retrieve list of warranties")
     *    )
     */
    public function filter(Request $request)
    {
        error_log('Retrieving list of filtered warranties.');
        // api/warranty/filter (GET)
        $params = collect([
            'keyword' => $request->keyword,
            'fromdate' => $request->fromdate,
            'todate' => $request->todate,
            'status' => $request->status,
            'warranty_id' => $request->warranty_id,
        ]);
        //Convert To Json Object
        $params = json_decode(json_encode($params));
        $warranties = $this->getWarranties($request->user());
        $warranties = $this->filterWarranties($warranties, $params);

        if ($this->isEmpty($warranties)) {
            $data['data'] = null;
            $data['maximumPages'] = 0;
            $data['msg'] = $this->getNotFoundMsg('Warranties');
            $data['code'] = 404;
            return response()->json($data, 404);
        } else {
            //Page Pagination Result List
            //Default return 10
            $paginateddata = $this->paginateResult($warranties, $request->pageSize, $request->pageNumber);
            $data['data'] = $paginateddata;
            $data['maximumPages'] = $this->getMaximumPaginationPage($warranties->count(), $request->pageSize);
            $data['msg'] = $this->getRetrievedSuccessMsg('Warranties');
            $data['code'] = 200;
            return response()->json($data, 200);
        }

    }

   
    /**
     * @OA\Get(
     *   tags={"WarrantyControllerService"},
     *   path="/api/warranty/{uid}",
     *   summary="Retrieves warranty by Uid.",
     *     operationId="getWarrantyByUid",
     *   @OA\Parameter(
     *     name="uid",
     *     in="path",
     *     description="Warranty_ID, NOT 'ID'.",
     *     required=true,
     *     @OA\Schema(type="string")
     *   ),
     *   @OA\Response(
     *     response=200,
     *     description="Warranty has been retrieved successfully."
     *   ),
     *   @OA\Response(
     *     response="default",
     *     description="Unable to retrieve the warranty."
     *   )
     * )
     */
    public function show(Request $request, $uid)
    {
        // api/warranty/{warrantyid} (GET)
        error_log('Retrieving warranty of uid:' . $uid);
        $warranty = $this->getWarranty($uid);
        if ($this->isEmpty($warranty)) {
            return $this->notFoundResponse('Warranty');
        } else {
            return $this->successResponse('Warranty', $warranty, 'retrieve');
        }
    }

  
    
    /**
     * @OA\Post(
     *   tags={"WarrantyControllerService"},
     *   path="/api/warranty",
     *   summary="Creates a warranty.",
     *   operationId="createWarranty",
     * @OA\Parameter(
     * name="name",
     * in="query",
     * description="Warrantyname",
     * required=true,
     * @OA\Schema(
     *              type="string"
     *          )
     * ),
     * @OA\Parameter(
     * name="store_id",
     * in="query",
     * description="Store ID",
     * required=true,
     * @OA\Schema(
     *              type="integer"
     *          )
     * ),
     * @OA\Parameter(
     * name="desc",
     * in="query",
     * description="Warranty description",
     * @OA\Schema(
     *              type="string"
     *          )
     * ),
     * @OA\Parameter(
     * name="period",
     * in="query",
     * required=true,
     * description="Warranty Period",
     * @OA\Schema(
     *              type="integer"
     *          )
     * ),
     * @OA\Parameter(
     * name="policy",
     * in="query",
     * required=true,
     * description="Warranty Policy",
     * @OA\Schema(
     *              type="string"
     *          )
     * ),
     *   @OA\Response(
     *     response=200,
     *     description="Warranty has been created successfully."
     *   ),
     *   @OA\Response(
     *     response="default",
     *     description="Unable to create the warranty."
     *   )
     * )
     */
    public function store(Request $request)
    {
        DB::beginTransaction();
        // Can only be used by Authorized personnel
        // api/warranty (POST)

        $this->validate($request, [
            'name' => 'required|string|max:191',
            'period' => 'required|numeric',
            'policy' => 'required|string',
        ]);
        error_log($this->controllerName.'Creating warranty.');
        $params = collect([
            'store_id' => $request->store_id,
            'name' => $request->name,
            'desc' => $request->desc,
            'period' => $request->period,
            'policy' => $request->policy,
        ]);
        $params = json_decode(json_encode($params));
        $warranty = $this->createWarranty($params);
        if ($this->isEmpty($warranty)) {
            DB::rollBack();
            return $this->errorResponse();
        }
    
        $this->createLog($request->user()->id , [$warranty->id], 'create', 'warranty');
        DB::commit();

        return $this->successResponse('Warranty', $warranty, 'create');
    }


    /**
     * @OA\Put(
     *   tags={"WarrantyControllerService"},
     *   path="/api/warranty/{uid}",
     *   summary="Update warranty by Uid.",
     *     operationId="updateWarrantyByUid",
     *   @OA\Parameter(
     *     name="uid",
     *     in="path",
     *     description="Warranty_ID, NOT 'ID'.",
     *     required=true,
     *     @OA\Schema(type="string")
     *   ),
     * @OA\Parameter(
     * name="name",
     * in="query",
     * description="Warrantyname",
     * required=true,
     * @OA\Schema(
     *              type="string"
     *          )
     * ),
     * @OA\Parameter(
     * name="store_id",
     * in="query",
     * description="Store ID",
     * required=true,
     * @OA\Schema(
     *              type="integer"
     *          )
     * ),
     * @OA\Parameter(
     * name="desc",
     * in="query",
     * description="Warranty description",
     * @OA\Schema(
     *              type="string"
     *          )
     * ),
     * @OA\Parameter(
     * name="period",
     * in="query",
     * required=true,
     * description="Warranty Period",
     * @OA\Schema(
     *              type="integer"
     *          )
     * ),
     * @OA\Parameter(
     * name="policy",
     * in="query",
     * required=true,
     * description="Warranty Policy",
     * @OA\Schema(
     *              type="string"
     *          )
     * ),
     *   @OA\Response(
     *     response=200,
     *     description="Warranty has been created successfully."
     *   ),
     *   @OA\Response(
     *     response="default",
     *     description="Unable to create the warranty."
     *   )
     * )
     */
    public function update(Request $request, $uid)
    {
        DB::beginTransaction();
        // api/warranty/{warrantyid} (PUT)
        error_log($this->controllerName.'Updating warranty of uid: ' . $uid);
        $this->validate($request, [
            'name' => 'required|string|max:191',
            'period' => 'required|numeric',
            'policy' => 'required|string',
        ]);

        $warranty = $this->getWarranty($uid);
        if ($this->isEmpty($warranty)) {
            DB::rollBack();
            return $this->notFoundResponse('Warranty');
        }

        $params = collect([
            'store_id' => $request->store_id,
            'name' => $request->name,
            'desc' => $request->desc,
            'period' => $request->period,
            'policy' => $request->policy,
        ]);
        $params = json_decode(json_encode($params));
        $warranty = $this->updateWarranty($warranty, $params);
        if ($this->isEmpty($warranty)) {
            DB::rollBack();
            return $this->errorResponse();
        }

        $this->createLog($request->user()->id , [$warranty->id], 'update', 'warranty');
        DB::commit();

        return $this->successResponse('Warranty', $warranty, 'update');
    }

    /**
     * @OA\Delete(
     *   tags={"WarrantyControllerService"},
     *   path="/api/warranty/{uid}",
     *   summary="Set warranty's 'status' to 0.",
     *     operationId="deleteWarrantyByUid",
     *   @OA\Parameter(
     *     name="uid",
     *     in="path",
     *     description="Warranty ID, NOT 'ID'.",
     *     required=true,
     *     @OA\SChema(type="string")
     *   ),
     *   @OA\Response(
     *     response=200,
     *     description="Warranty has been 'deleted' successfully."
     *   ),
     *   @OA\Response(
     *     response="default",
     *     description="Unable to 'delete' the warranty."
     *   )
     * )
     */
    public function destroy(Request $request, $uid)
    {
        DB::beginTransaction();
        // TODO ONLY TOGGLES THE status = 1/0
        // api/warranty/{warrantyid} (DELETE)
        error_log('Deleting warranty of uid: ' . $uid);
        $warranty = $this->getWarranty($uid);
        if ($this->isEmpty($warranty)) {
            DB::rollBack();
            return $this->notFoundResponse('Warranty');
        }
        $warranty = $this->deleteWarranty($warranty);
        $this->createLog($request->user()->id , [$warranty->id], 'delete', 'warranty');
        if ($this->isEmpty($warranty)) {
            DB::rollBack();
            return $this->errorResponse();
        } else {
            DB::commit();
            return $this->successResponse('Warranty', $warranty, 'delete');
        }
    }

}