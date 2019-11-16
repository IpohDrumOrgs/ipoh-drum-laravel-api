<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use DB;
use Carbon\Carbon;
use App\VerificationCode;
use Illuminate\Support\Facades\Hash;
use App\Traits\GlobalFunctions;
use App\Traits\NotificationFunctions;
use App\Traits\VerificationCodeServices;
use App\Traits\LogServices;

class VerificationCodeController extends Controller
{
    use GlobalFunctions, NotificationFunctions, VerificationCodeServices, LogServices;
    private $controllerName = '[VerificationCodeController]';

    /**
     * @OA\Get(
     *      path="/api/verificationcode",
     *      operationId="getVerificationCodeList",
     *      tags={"VerificationCodeControllerService"},
     *      summary="Get list of verificationcodes",
     *      description="Returns list of verificationcodes",
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
     *          description="Successfully retrieved list of verificationcodes"
     *       ),
     *       @OA\Response(
     *          response="default",
     *          description="Unable to retrieve list of verificationcodes")
     *    )
     */
    public function index(Request $request)
    {
        error_log('Retrieving list of verificationcodes.');
        // api/verificationcode (GET)
        $verificationcodes = $this->getVerificationCodeListing($request->user());
        if ($this->isEmpty($verificationcodes)) {
            $data['status'] = 'error';
            $data['data'] = null;
            $data['maximumPages'] = 0;
            $data['msg'] = $this->getNotFoundMsg('VerificationCodes');
            $data['code'] = 404;
            return response()->json($data, 404);
        } else {
            //Page Pagination Result List
            //Default return 10
            $paginateddata = $this->paginateResult($verificationcodes, $request->pageSize, $request->pageNumber);
            $data['status'] = 'success';
            $data['data'] = $paginateddata;
            $data['maximumPages'] = $this->getMaximumPaginationPage($verificationcodes->count(), $request->pageSize);
            $data['msg'] = $this->getRetrievedSuccessMsg('VerificationCodes');
            $data['code'] = 200;
            return response()->json($data, 200);
        }
    }
    /**
     * @OA\Get(
     *      path="/api/pluck/verificationcodes",
     *      operationId="pluckVerificationCodeList",
     *      tags={"VerificationCodeControllerService"},
     *      summary="pluck list of verificationcodes",
     *      description="Returns list of plucked verificationcodes",
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
     *          description="Successfully retrieved list of verificationcodes"
     *       ),
     *       @OA\Response(
     *          response="default",
     *          description="Unable to retrieve list of verificationcodes")
     *    )
     */
    public function pluckIndex(Request $request)
    {
        error_log('Retrieving list of plucked verificationcodes.');
        // api/pluck/verificationcodes (GET)
        error_log("columns = " . collect($this->splitToArray($request->cols)));
        $verificationcodes = $this->pluckVerificationCodeIndex($this->splitToArray($request->cols));
        if ($this->isEmpty($verificationcodes)) {
            $data['status'] = 'error';
            $data['data'] = null;
            $data['maximumPages'] = 0;
            $data['msg'] = $this->getNotFoundMsg('VerificationCodes');
            $data['code'] = 404;
            return response()->json($data, 404);
        } else {
            //Page Pagination Result List
            //Default return 10
            $paginateddata = $this->paginateResult($verificationcodes, $request->pageSize, $request->pageNumber);
            $data['status'] = 'success';
            $data['data'] = $paginateddata;
            $data['maximumPages'] = $this->getMaximumPaginationPage($verificationcodes->count(), $request->pageSize);
            $data['msg'] = $this->getRetrievedSuccessMsg('VerificationCodes');
            $data['code'] = 200;
            return response()->json($data, 200);
        }
    }

    /**
     * @OA\Get(
     *      path="/api/filter/verificationcode",
     *      operationId="filterVerificationCodeList",
     *      tags={"VerificationCodeControllerService"},
     *      summary="Filter list of verificationcodes",
     *      description="Returns list of filtered verificationcodes",
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
     *     name="onverificationcode",
     *     in="query",
     *     description="onverificationcode for filter",
     *     @OA\Schema(type="string")
     *   ),
     *      @OA\Response(
     *          response=200,
     *          description="Successfully retrieved list of filtered verificationcodes"
     *       ),
     *       @OA\Response(
     *          response="default",
     *          description="Unable to retrieve list of verificationcodes")
     *    )
     */
    public function filter(Request $request)
    {
        error_log('Retrieving list of filtered verificationcodes.');
        // api/verificationcode/filter (GET)
        $params = collect([
            'keyword' => $request->keyword,
            'fromdate' => $request->fromdate,
            'todate' => $request->todate,
            'status' => $request->status,
            'onverificationcode' => $request->onverificationcode,
        ]);
        //Convert To Json Object
        $params = json_decode(json_encode($params));
        $verificationcodes = $this->filterVerificationCodeListing($request->user(), $params);

        if ($this->isEmpty($verificationcodes)) {
            $data['data'] = null;
            $data['maximumPages'] = 0;
            $data['msg'] = $this->getNotFoundMsg('VerificationCodes');
            $data['code'] = 404;
            return response()->json($data, 404);
        } else {
            //Page Pagination Result List
            //Default return 10
            $paginateddata = $this->paginateResult($verificationcodes, $request->pageSize, $request->pageNumber);
            $data['data'] = $paginateddata;
            $data['maximumPages'] = $this->getMaximumPaginationPage($verificationcodes->count(), $request->pageSize);
            $data['msg'] = $this->getRetrievedSuccessMsg('VerificationCodes');
            $data['code'] = 200;
            return response()->json($data, 200);
        }

    }

    /**
     * @OA\Get(
     *      path="/api/pluck/filter/verificationcode",
     *      operationId="filterPluckedVerificationCodeList",
     *      tags={"VerificationCodeControllerService"},
     *      summary="Filter list of plucked verificationcodes",
     *      description="Returns list of filtered verificationcodes",
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
     *     name="onverificationcode",
     *     in="query",
     *     description="onverificationcode for filter",
     *     @OA\Schema(type="string")
     *   ),
     *      @OA\Response(
     *          response=200,
     *          description="Successfully retrieved list of filtered verificationcodes"
     *       ),
     *       @OA\Response(
     *          response="default",
     *          description="Unable to retrieve list of verificationcodes")
     *    )
     */
    public function pluckFilter(Request $request)
    {
        error_log('Retrieving list of filtered and plucked verificationcodes.');
        // api/pluck/filter/verificationcode (GET)
        $params = collect([
            'keyword' => $request->keyword,
            'fromdate' => $request->fromdate,
            'todate' => $request->todate,
            'status' => $request->status,
            'onverificationcode' => $request->onverificationcode,
        ]);
        //Convert To Json Object
        $params = json_decode(json_encode($params));
        $verificationcodes = $this->pluckVerificationCodeFilter($this->splitToArray($request->cols) , $params);

        if ($this->isEmpty($verificationcodes)) {
            $data['data'] = null;
            $data['maximumPages'] = 0;
            $data['msg'] = $this->getNotFoundMsg('VerificationCodes');
            $data['code'] = 404;
            return response()->json($data, 404);
        } else {
            //Page Pagination Result List
            //Default return 10
            $paginateddata = $this->paginateResult($verificationcodes, $request->pageSize, $request->pageNumber);
            $data['data'] = $paginateddata;
            $data['maximumPages'] = $this->getMaximumPaginationPage($verificationcodes->count(), $request->pageSize);
            $data['msg'] = $this->getRetrievedSuccessMsg('VerificationCodes');
            $data['code'] = 200;
            return response()->json($data, 200);
        }

    }

    /**
     * @OA\Get(
     *   tags={"VerificationCodeControllerService"},
     *   path="/api/verificationcode/{uid}",
     *   summary="Retrieves verificationcode by Uid.",
     *     operationId="getVerificationCodeByUid",
     *   @OA\Parameter(
     *     name="uid",
     *     in="path",
     *     description="VerificationCode_ID, NOT 'ID'.",
     *     required=true,
     *     @OA\Schema(type="string")
     *   ),
     *   @OA\Response(
     *     response=200,
     *     description="VerificationCode has been retrieved successfully."
     *   ),
     *   @OA\Response(
     *     response="default",
     *     description="Unable to retrieve the verificationcode."
     *   )
     * )
     */
    public function show(Request $request, $uid)
    {
        // api/verificationcode/{verificationcodeid} (GET)
        error_log('Retrieving verificationcode of uid:' . $uid);
        $verificationcode = $this->getVerificationCode($request->user(), $uid);
        if ($this->isEmpty($verificationcode)) {
            $data['data'] = null;
            $data['msg'] = $this->getNotFoundMsg('VerificationCode');
            $data['status'] = 'error';
            $data['code'] = 404;
            return response()->json($data, 404);
        } else {
            $data['data'] = $verificationcode;
            $data['msg'] = $this->getRetrievedSuccessMsg('VerificationCode');
            $data['status'] = 'success';
            $data['code'] = 200;
            return response()->json($data, 200);
        }
    }

    /**
     * @OA\Get(
     *      path="/api/pluck/verificationcode/{uid}",
     *      operationId="pluckVerificationCodeByUid",
     *      tags={"VerificationCodeControllerService"},
     *      summary="pluck verificationcode",
     *      description="Returns plucked verificationcodes",
     *   @OA\Parameter(
     *     name="uid",
     *     in="path",
     *     description="VerificationCode_ID, NOT 'ID'.",
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
     *          description="Successfully retrieved list of verificationcodes"
     *       ),
     *       @OA\Response(
     *          response="default",
     *          description="Unable to retrieve list of verificationcodes")
     *    )
     */
    public function pluckShow(Request $request , $uid)
    {
        error_log('Retrieving plucked verificationcodes.');
        // api/pluck/verificationcode/{uid} (GET)
        error_log("columns = " . collect($this->splitToArray($request->cols)));
        $verificationcode = $this->pluckVerificationCode($this->splitToArray($request->cols) , $uid);
        if ($this->isEmpty($verificationcode)) {
            $data['data'] = null;
            $data['status'] = 'error';
            $data['msg'] = $this->getNotFoundMsg('VerificationCode');
            $data['code'] = 404;
            return response()->json($data, 404);
        } else {
            $data['status'] = 'success';
            $data['msg'] = $this->getRetrievedSuccessMsg('VerificationCode');
            $data['data'] = $verificationcode;
            $data['code'] = 200;
            return response()->json($data, 200);
        }
    }

    
    /**
     * @OA\Post(
     *   tags={"VerificationCodeControllerService"},
     *   path="/api/verificationcode",
     *   summary="Creates a verificationcode.",
     *   operationId="createVerificationCode",
     * @OA\Parameter(
     * name="ticketid",
     * in="query",
     * description="Verification belongs to which Ticket",
     * required=true,
     * @OA\Schema(
     *              type="integer"
     *          )
     * ),
     * @OA\Parameter(
     * name="code",
     * in="query",
     * description="VerificationCode",
     * required=true,
     * @OA\Schema(
     *              type="string"
     *          )
     * ),
     *   @OA\Response(
     *     response=200,
     *     description="VerificationCode has been created successfully."
     *   ),
     *   @OA\Response(
     *     response="default",
     *     description="Unable to create the verificationcode."
     *   )
     * )
     */
    public function store(Request $request)
    {
        DB::beginTransaction();
        // Can only be used by Authorized personnel
        // api/verificationcode (POST)
        
        $this->validate($request, [
            'ticketid' => 'required|integer',
            'code' => 'required|string|max:191',
        ]);
        error_log('Creating verificationcode.');
        $params = collect([
            'ticketid' => $request->ticketid,
            'code' => $request->code,
        ]);
        //Convert To Json Object
        $params = json_decode(json_encode($params));
        $verificationcode = $this->createVerificationCode($request->user(), $params);

        if ($this->isEmpty($verificationcode)) {
            DB::rollBack();
            $data['data'] = null;
            $data['status'] = 'error';
            $data['msg'] = $this->getErrorMsg();
            $data['code'] = 404;
            return response()->json($data, 404);
        } else {
            DB::commit();
            $data['status'] = 'success';
            $data['msg'] = $this->getCreatedSuccessMsg('VerificationCode');
            $data['data'] = $verificationcode;
            $data['code'] = 200;
            return response()->json($data, 200);
        }
    }


    /**
     * @OA\Put(
     *   tags={"VerificationCodeControllerService"},
     *   path="/api/verificationcode/{uid}",
     *   summary="Update verificationcode by Uid.",
     *     operationId="updateVerificationCodeByUid",
     *   @OA\Parameter(
     *     name="uid",
     *     in="path",
     *     description="VerificationCode_ID, NOT 'ID'.",
     *     required=true,
     *     @OA\Schema(type="string")
     *   ),
     * @OA\Parameter(
     * name="ticketid",
     * in="query",
     * description="Verification belongs to which Ticket",
     * required=true,
     * @OA\Schema(
     *              type="integer"
     *          )
     * ),
     * @OA\Parameter(
     * name="code",
     * in="query",
     * description="VerificationCode",
     * required=true,
     * @OA\Schema(
     *              type="string"
     *          )
     * ),
     *   @OA\Response(
     *     response=200,
     *     description="VerificationCode has been updated successfully."
     *   ),
     *   @OA\Response(
     *     response="default",
     *     description="Unable to update the verificationcode."
     *   )
     * )
     */
    public function update(Request $request, $uid)
    {
        DB::beginTransaction();
        // api/verificationcode/{verificationcodeid} (PUT) 
        error_log('Updating verificationcode of uid: ' . $uid);
        $verificationcode = $this->getVerificationCode($request->user(), $uid);
        
        $this->validate($request, [
            'ticketid' => 'required|integer',
            'code' => 'required|string|max:191',
        ]);

        if ($this->isEmpty($verificationcode)) {
            DB::rollBack();
            $data['data'] = null;
            $data['msg'] = $this->getNotFoundMsg('VerificationCode');
            $data['status'] = 'error';
            $data['code'] = 404;
            return response()->json($data, 404);
        }
        
        $params = collect([
            'ticketid' => $request->ticketid,
            'code' => $request->code,
        ]);
        
        //Convert To Json Object
        $params = json_decode(json_encode($params));
        $verificationcode = $this->updateVerificationCode($request->user(), $verificationcode, $params);
        if ($this->isEmpty($verificationcode)) {
            DB::rollBack();
            $data['data'] = null;
            $data['msg'] = $this->getErrorMsg('VerificationCode');
            $data['status'] = 'error';
            $data['code'] = 404;
            return response()->json($data, 404);
        } else {
            DB::commit();
            $data['status'] = 'success';
            $data['msg'] = $this->getUpdatedSuccessMsg('VerificationCode');
            $data['data'] = $verificationcode;
            $data['status'] = 'success';
            $data['code'] = 200;
            return response()->json($data, 200);
        }
    }

    /**
     * @OA\Delete(
     *   tags={"VerificationCodeControllerService"},
     *   path="/api/verificationcode/{uid}",
     *   summary="Set verificationcode's 'status' to 0.",
     *     operationId="deleteVerificationCodeByUid",
     *   @OA\Parameter(
     *     name="uid",
     *     in="path",
     *     description="VerificationCode ID, NOT 'ID'.",
     *     required=true,
     *     @OA\SChema(type="string")
     *   ),
     *   @OA\Response(
     *     response=200,
     *     description="VerificationCode has been 'deleted' successfully."
     *   ),
     *   @OA\Response(
     *     response="default",
     *     description="Unable to 'delete' the verificationcode."
     *   )
     * )
     */
    public function destroy(Request $request, $uid)
    {
        DB::beginTransaction();
        // TODO ONLY TOGGLES THE status = 1/0
        // api/verificationcode/{verificationcodeid} (DELETE)
        error_log('Deleting verificationcode of uid: ' . $uid);
        $verificationcode = $this->getVerificationCode($request->user(), $uid);
        if ($this->isEmpty($verificationcode)) {
            DB::rollBack();
            $data['status'] = 'error';
            $data['msg'] = $this->getNotFoundMsg('VerificationCode');
            $data['data'] = null;
            $data['code'] = 404;
            return response()->json($data, 404);
        }
        $verificationcode = $this->deleteVerificationCode($request->user(), $verificationcode->id);
        if ($this->isEmpty($verificationcode)) {
            DB::rollBack();
            $data['status'] = 'error';
            $data['msg'] = $this->getErrorMsg();
            $data['data'] = null;
            $data['code'] = 404;
            return response()->json($data, 404);
        } else {
            DB::commit();
            $data['status'] = 'success';
            $data['msg'] = $this->getDeletedSuccessMsg('VerificationCode');
            $data['data'] = $verificationcode;
            $data['code'] = 200;
            return response()->json($data, 200);
        }
    }

}
