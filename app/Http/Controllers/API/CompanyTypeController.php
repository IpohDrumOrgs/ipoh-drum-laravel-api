<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use DB;
use Carbon\Carbon;
use App\CompanyType;
use Illuminate\Support\Facades\Hash;
use App\Traits\GlobalFunctions;
use App\Traits\NotificationFunctions;
use App\Traits\CompanyTypeServices;
use App\Traits\LogServices;

class CompanyTypeController extends Controller
{
    use GlobalFunctions, NotificationFunctions, CompanyTypeServices, LogServices;
    private $controllerName = '[CompanyTypeController]';
     /**
     * @OA\Get(
     *      path="/api/companytype",
     *      operationId="getCompanyTypes",
     *      tags={"CompanyTypeControllerService"},
     *      summary="Get list of companytypes",
     *      description="Returns list of companytypes",
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
     *          description="Successfully retrieved list of companytypes"
     *       ),
     *       @OA\Response(
     *          response="default",
     *          description="Unable to retrieve list of companytypes")
     *    )
     */
    public function index(Request $request)
    {
        error_log('Retrieving list of companytypes.');
        // api/companytype (GET)
        $companytypes = $this->getCompanyTypes($request->user());
        if ($this->isEmpty($companytypes)) {
            $data['status'] = 'error';
            $data['data'] = null;
            $data['maximumPages'] = 0;
            $data['msg'] = $this->getNotFoundMsg('CompanyTypes');
            $data['code'] = 404;
            return response()->json($data, 404);
        } else {
            //Page Pagination Result List
            //Default return 10
            $paginateddata = $this->paginateResult($companytypes, $request->pageSize, $request->pageNumber);
            $data['status'] = 'success';
            $data['data'] = $paginateddata;
            $data['maximumPages'] = $this->getMaximumPaginationPage($companytypes->count(), $request->pageSize);
            $data['msg'] = $this->getRetrievedSuccessMsg('CompanyTypes');
            $data['code'] = 200;
            return response()->json($data, 200);
        }
    }
    
    /**
     * @OA\Get(
     *      path="/api/filter/companytype",
     *      operationId="filterCompanyTypes",
     *      tags={"CompanyTypeControllerService"},
     *      summary="Filter list of companytypes",
     *      description="Returns list of filtered companytypes",
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
     *          description="Successfully retrieved list of filtered companytypes"
     *       ),
     *       @OA\Response(
     *          response="default",
     *          description="Unable to retrieve list of companytypes")
     *    )
     */
    public function filter(Request $request)
    {
        error_log('Retrieving list of filtered companytypes.');
        // api/companytype/filter (GET)
        $params = collect([
            'keyword' => $request->keyword,
            'fromdate' => $request->fromdate,
            'todate' => $request->todate,
            'status' => $request->status,
            'companytype_id' => $request->companytype_id,
        ]);
        //Convert To Json Object
        $params = json_decode(json_encode($params));
        $companytypes = $this->getCompanyTypes($request->user());
        $companytypes = $this->filterCompanyTypes($companytypes, $params);

        if ($this->isEmpty($companytypes)) {
            $data['data'] = null;
            $data['maximumPages'] = 0;
            $data['msg'] = $this->getNotFoundMsg('CompanyTypes');
            $data['code'] = 404;
            return response()->json($data, 404);
        } else {
            //Page Pagination Result List
            //Default return 10
            $paginateddata = $this->paginateResult($companytypes, $request->pageSize, $request->pageNumber);
            $data['data'] = $paginateddata;
            $data['maximumPages'] = $this->getMaximumPaginationPage($companytypes->count(), $request->pageSize);
            $data['msg'] = $this->getRetrievedSuccessMsg('CompanyTypes');
            $data['code'] = 200;
            return response()->json($data, 200);
        }

    }

   
    /**
     * @OA\Get(
     *   tags={"CompanyTypeControllerService"},
     *   path="/api/companytype/{uid}",
     *   summary="Retrieves companytype by Uid.",
     *     operationId="getCompanyTypeByUid",
     *   @OA\Parameter(
     *     name="uid",
     *     in="path",
     *     description="CompanyType_ID, NOT 'ID'.",
     *     required=true,
     *     @OA\Schema(type="string")
     *   ),
     *   @OA\Response(
     *     response=200,
     *     description="CompanyType has been retrieved successfully."
     *   ),
     *   @OA\Response(
     *     response="default",
     *     description="Unable to retrieve the companytype."
     *   )
     * )
     */
    public function show(Request $request, $uid)
    {
        // api/companytype/{companytypeid} (GET)
        error_log('Retrieving companytype of uid:' . $uid);
        $companytype = $this->getCompanyType($uid);
        if ($this->isEmpty($companytype)) {
            $data['data'] = null;
            $data['msg'] = $this->getNotFoundMsg('CompanyType');
            $data['status'] = 'error';
            $data['code'] = 404;
            return response()->json($data, 404);
        } else {
            $data['data'] = $companytype;
            $data['msg'] = $this->getRetrievedSuccessMsg('CompanyType');
            $data['status'] = 'success';
            $data['code'] = 200;
            return response()->json($data, 200);
        }
    }

  /**
     * @OA\Post(
     *   tags={"CompanyTypeControllerService"},
     *   path="/api/companytype",
     *   summary="Creates a companytype.",
     *   operationId="createCompanyType",
     * @OA\Parameter(
     * name="name",
     * in="query",
     * description="CompanyType Name",
     * required=true,
     * @OA\Schema(
     *              type="string"
     *          )
     * ),
     * @OA\Parameter(
     * name="desc",
     * in="query",
     * description="CompanyType Description",
     * @OA\Schema(
     *              type="string"
     *          )
     * ),
     *   @OA\Response(
     *     response=200,
     *     description="CompanyType has been created successfully."
     *   ),
     *   @OA\Response(
     *     response="default",
     *     description="Unable to create the companytype."
     *   )
     * )
     */
    public function store(Request $request)
    {
        DB::beginTransaction();
        // Can only be used by Authorized personnel
        // api/companytype (POST)
        
        $this->validate($request, [
            'name' => 'required|string|max:191',
            'desc' => 'nullable',
        ]);
        error_log('Creating companytype.');
        $params = collect([
            'name' => $request->name,
            'desc' => $request->desc,
        ]);
        //Convert To Json Object
        $params = json_decode(json_encode($params));
        $companytype = $this->createCompanyType($params);

        if ($this->isEmpty($companytype)) {
            DB::rollBack();
            $data['data'] = null;
            $data['status'] = 'error';
            $data['msg'] = $this->getErrorMsg();
            $data['code'] = 404;
            return response()->json($data, 404);
        } else {
            DB::commit();
            $data['status'] = 'success';
            $data['msg'] = $this->getCreatedSuccessMsg('CompanyType');
            $data['data'] = $companytype;
            $data['code'] = 200;
            return response()->json($data, 200);
        }
    }


    /**
     * @OA\Put(
     *   tags={"CompanyTypeControllerService"},
     *   path="/api/companytype/{uid}",
     *   summary="Update companytype by Uid.",
     *     operationId="updateCompanyTypeByUid",
     *   @OA\Parameter(
     *     name="uid",
     *     in="path",
     *     description="CompanyType_ID, NOT 'ID'.",
     *     required=true,
     *     @OA\Schema(type="string")
     *   ),
     * @OA\Parameter(
     * name="name",
     * in="query",
     * description="CompanyTypename",
     * required=true,
     * @OA\Schema(
     *              type="string"
     *          )
     * ),
     * @OA\Parameter(
     * name="desc",
     * in="query",
     * description="CompanyType Description",
     * @OA\Schema(
     *              type="string"
     *          )
     * ),
     *   @OA\Response(
     *     response=200,
     *     description="CompanyType has been updated successfully."
     *   ),
     *   @OA\Response(
     *     response="default",
     *     description="Unable to update the companytype."
     *   )
     * )
     */
    public function update(Request $request, $uid)
    {
        DB::beginTransaction();
        // api/companytype/{companytypeid} (PUT) 
        error_log('Updating companytype of uid: ' . $uid);
        $companytype = $this->getCompanyType($uid);
       
       
        $this->validate($request, [
            'name' => 'required|string|max:191',
            'desc' => 'nullable',
        ]);
      
        if ($this->isEmpty($companytype)) {
            DB::rollBack();
            $data['data'] = null;
            $data['msg'] = $this->getNotFoundMsg('CompanyType');
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
        $companytype = $this->updateCompanyType($companytype, $params);
        if ($this->isEmpty($companytype)) {
            DB::rollBack();
            $data['data'] = null;
            $data['msg'] = $this->getErrorMsg('CompanyType');
            $data['status'] = 'error';
            $data['code'] = 404;
            return response()->json($data, 404);
        } else {
            DB::commit();
            $data['status'] = 'success';
            $data['msg'] = $this->getUpdatedSuccessMsg('CompanyType');
            $data['data'] = $companytype;
            $data['status'] = 'success';
            $data['code'] = 200;
            return response()->json($data, 200);
        }
    }

    /**
     * @OA\Delete(
     *   tags={"CompanyTypeControllerService"},
     *   path="/api/companytype/{uid}",
     *   summary="Set companytype's 'status' to 0.",
     *     operationId="deleteCompanyTypeByUid",
     *   @OA\Parameter(
     *     name="uid",
     *     in="path",
     *     description="CompanyType ID, NOT 'ID'.",
     *     required=true,
     *     @OA\SChema(type="string")
     *   ),
     *   @OA\Response(
     *     response=200,
     *     description="CompanyType has been 'deleted' successfully."
     *   ),
     *   @OA\Response(
     *     response="default",
     *     description="Unable to 'delete' the companytype."
     *   )
     * )
     */
    public function destroy(Request $request, $uid)
    {
        DB::beginTransaction();
        // TODO ONLY TOGGLES THE status = 1/0
        // api/companytype/{companytypeid} (DELETE)
        error_log('Deleting companytype of uid: ' . $uid);
        $companytype = $this->getCompanyType($uid);
        if ($this->isEmpty($companytype)) {
            DB::rollBack();
            $data['status'] = 'error';
            $data['msg'] = $this->getNotFoundMsg('CompanyType');
            $data['data'] = null;
            $data['code'] = 404;
            return response()->json($data, 404);
        }
        $companytype = $this->deleteCompanyType($companytype);
        $this->createLog($request->user()->id , [$companytype->id], 'delete', 'companytype');
        if ($this->isEmpty($companytype)) {
            DB::rollBack();
            $data['status'] = 'error';
            $data['msg'] = $this->getErrorMsg();
            $data['data'] = null;
            $data['code'] = 404;
            return response()->json($data, 404);
        } else {
            DB::commit();
            $data['status'] = 'success';
            $data['msg'] = $this->getDeletedSuccessMsg('CompanyType');
            $data['data'] = null;
            $data['code'] = 200;
            return response()->json($data, 200);
        }
    }


}
