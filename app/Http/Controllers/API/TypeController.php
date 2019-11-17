<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use DB;
use Carbon\Carbon;
use App\Type;
use Illuminate\Support\Facades\Hash;
use App\Traits\GlobalFunctions;
use App\Traits\NotificationFunctions;
use App\Traits\TypeServices;
use App\Traits\LogServices;

class TypeController extends Controller
{
    use GlobalFunctions, NotificationFunctions, TypeServices, LogServices;
    private $controllerName = '[TypeController]';

    /**
     * @OA\Get(
     *      path="/api/type",
     *      operationId="getTypeList",
     *      tags={"TypeControllerService"},
     *      summary="Get list of types",
     *      description="Returns list of types",
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
     *          description="Successfully retrieved list of types"
     *       ),
     *       @OA\Response(
     *          response="default",
     *          description="Unable to retrieve list of types")
     *    )
     */
    public function index(Request $request)
    {
        error_log('Retrieving list of types.');
        // api/type (GET)
        $types = $this->getTypeListing($request->user());
        if ($this->isEmpty($types)) {
            $data['status'] = 'error';
            $data['data'] = null;
            $data['maximumPages'] = 0;
            $data['msg'] = $this->getNotFoundMsg('Types');
            $data['code'] = 404;
            return response()->json($data, 404);
        } else {
            //Page Pagination Result List
            //Default return 10
            $paginateddata = $this->paginateResult($types, $request->pageSize, $request->pageNumber);
            $data['status'] = 'success';
            $data['data'] = $paginateddata;
            $data['maximumPages'] = $this->getMaximumPaginationPage($types->count(), $request->pageSize);
            $data['msg'] = $this->getRetrievedSuccessMsg('Types');
            $data['code'] = 200;
            return response()->json($data, 200);
        }
    }
    /**
     * @OA\Get(
     *      path="/api/pluck/types",
     *      operationId="pluckTypeList",
     *      tags={"TypeControllerService"},
     *      summary="pluck list of types",
     *      description="Returns list of plucked types",
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
     *     name="cols",
     *     in="query",
     *     required=true,
     *     description="Columns for pluck",
     *     @OA\Schema(type="string")
     *   ),
     *      @OA\Response(
     *          response=200,
     *          description="Successfully retrieved list of types"
     *       ),
     *       @OA\Response(
     *          response="default",
     *          description="Unable to retrieve list of types")
     *    )
     */
    public function pluckIndex(Request $request)
    {
        error_log('Retrieving list of plucked types.');
        // api/pluck/types (GET)
        error_log("columns = " . collect($this->splitToArray($request->cols)));
        $types = $this->pluckTypeIndex($this->splitToArray($request->cols));
        if ($this->isEmpty($types)) {
            $data['status'] = 'error';
            $data['data'] = null;
            $data['maximumPages'] = 0;
            $data['msg'] = $this->getNotFoundMsg('Types');
            $data['code'] = 404;
            return response()->json($data, 404);
        } else {
            //Page Pagination Result List
            //Default return 10
            $paginateddata = $this->paginateResult($types, $request->pageSize, $request->pageNumber);
            $data['status'] = 'success';
            $data['data'] = $paginateddata;
            $data['maximumPages'] = $this->getMaximumPaginationPage($types->count(), $request->pageSize);
            $data['msg'] = $this->getRetrievedSuccessMsg('Types');
            $data['code'] = 200;
            return response()->json($data, 200);
        }
    }

    /**
     * @OA\Get(
     *      path="/api/filter/type",
     *      operationId="filterTypeList",
     *      tags={"TypeControllerService"},
     *      summary="Filter list of types",
     *      description="Returns list of filtered types",
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
     *          description="Successfully retrieved list of filtered types"
     *       ),
     *       @OA\Response(
     *          response="default",
     *          description="Unable to retrieve list of types")
     *    )
     */
    public function filter(Request $request)
    {
        error_log('Retrieving list of filtered types.');
        // api/type/filter (GET)
        $params = collect([
            'keyword' => $request->keyword,
            'fromdate' => $request->fromdate,
            'todate' => $request->todate,
            'status' => $request->status,
            'type_id' => $request->type_id,
        ]);
        //Convert To Json Object
        $params = json_decode(json_encode($params));
        $types = $this->filterTypeListing($request->user(), $params);

        if ($this->isEmpty($types)) {
            $data['data'] = null;
            $data['maximumPages'] = 0;
            $data['msg'] = $this->getNotFoundMsg('Types');
            $data['code'] = 404;
            return response()->json($data, 404);
        } else {
            //Page Pagination Result List
            //Default return 10
            $paginateddata = $this->paginateResult($types, $request->pageSize, $request->pageNumber);
            $data['data'] = $paginateddata;
            $data['maximumPages'] = $this->getMaximumPaginationPage($types->count(), $request->pageSize);
            $data['msg'] = $this->getRetrievedSuccessMsg('Types');
            $data['code'] = 200;
            return response()->json($data, 200);
        }

    }

    /**
     * @OA\Get(
     *      path="/api/pluck/filter/type",
     *      operationId="filterPluckedTypeList",
     *      tags={"TypeControllerService"},
     *      summary="Filter list of plucked types",
     *      description="Returns list of filtered types",
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
     *          description="Successfully retrieved list of filtered types"
     *       ),
     *       @OA\Response(
     *          response="default",
     *          description="Unable to retrieve list of types")
     *    )
     */
    public function pluckFilter(Request $request)
    {
        error_log('Retrieving list of filtered and plucked types.');
        // api/pluck/filter/type (GET)
        $params = collect([
            'keyword' => $request->keyword,
            'fromdate' => $request->fromdate,
            'todate' => $request->todate,
            'status' => $request->status,
            'type_id' => $request->type_id,
        ]);
        //Convert To Json Object
        $params = json_decode(json_encode($params));
        $types = $this->pluckTypeFilter($this->splitToArray($request->cols) , $params);

        if ($this->isEmpty($types)) {
            $data['data'] = null;
            $data['maximumPages'] = 0;
            $data['msg'] = $this->getNotFoundMsg('Types');
            $data['code'] = 404;
            return response()->json($data, 404);
        } else {
            //Page Pagination Result List
            //Default return 10
            $paginateddata = $this->paginateResult($types, $request->pageSize, $request->pageNumber);
            $data['data'] = $paginateddata;
            $data['maximumPages'] = $this->getMaximumPaginationPage($types->count(), $request->pageSize);
            $data['msg'] = $this->getRetrievedSuccessMsg('Types');
            $data['code'] = 200;
            return response()->json($data, 200);
        }

    }

    /**
     * @OA\Get(
     *   tags={"TypeControllerService"},
     *   path="/api/type/{uid}",
     *   summary="Retrieves type by Uid.",
     *     operationId="getTypeByUid",
     *   @OA\Parameter(
     *     name="uid",
     *     in="path",
     *     description="Type_ID, NOT 'ID'.",
     *     required=true,
     *     @OA\Schema(type="string")
     *   ),
     *   @OA\Response(
     *     response=200,
     *     description="Type has been retrieved successfully."
     *   ),
     *   @OA\Response(
     *     response="default",
     *     description="Unable to retrieve the type."
     *   )
     * )
     */
    public function show(Request $request, $uid)
    {
        // api/type/{typeid} (GET)
        error_log('Retrieving type of uid:' . $uid);
        $type = $this->getType($request->user(), $uid);
        if ($this->isEmpty($type)) {
            $data['data'] = null;
            $data['msg'] = $this->getNotFoundMsg('Type');
            $data['status'] = 'error';
            $data['code'] = 404;
            return response()->json($data, 404);
        } else {
            $data['data'] = $type;
            $data['msg'] = $this->getRetrievedSuccessMsg('Type');
            $data['status'] = 'success';
            $data['code'] = 200;
            return response()->json($data, 200);
        }
    }

    /**
     * @OA\Get(
     *      path="/api/pluck/type/{uid}",
     *      operationId="pluckTypeByUid",
     *      tags={"TypeControllerService"},
     *      summary="pluck type",
     *      description="Returns plucked types",
     *   @OA\Parameter(
     *     name="uid",
     *     in="path",
     *     description="Type_ID, NOT 'ID'.",
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
     *          description="Successfully retrieved list of types"
     *       ),
     *       @OA\Response(
     *          response="default",
     *          description="Unable to retrieve list of types")
     *    )
     */
    public function pluckShow(Request $request , $uid)
    {
        error_log('Retrieving plucked types.');
        // api/pluck/type/{uid} (GET)
        error_log("columns = " . collect($this->splitToArray($request->cols)));
        $type = $this->pluckType($this->splitToArray($request->cols) , $uid);
        if ($this->isEmpty($type)) {
            $data['data'] = null;
            $data['status'] = 'error';
            $data['msg'] = $this->getNotFoundMsg('Type');
            $data['code'] = 404;
            return response()->json($data, 404);
        } else {
            $data['status'] = 'success';
            $data['msg'] = $this->getRetrievedSuccessMsg('Type');
            $data['data'] = $type;
            $data['code'] = 200;
            return response()->json($data, 200);
        }
    }


    /**
     * @OA\Post(
     *   tags={"TypeControllerService"},
     *   path="/api/type",
     *   summary="Creates a type.",
     *   operationId="createType",
     * @OA\Parameter(
     * name="name",
     * in="query",
     * description="Type name",
     * required=true,
     * @OA\Schema(
     *              type="string"
     *          )
     * ),
     * @OA\Parameter(
     * name="desc",
     * in="query",
     * description="Type Description",
     * required=true,
     * @OA\Schema(
     *              type="string"
     *          )
     * ),
     * @OA\Parameter(
     * name="icon",
     * in="query",
     * description="Icon",
     * required=true,
     * @OA\Schema(
     *              type="string"
     *          )
     * ),
     *   @OA\Response(
     *     response=200,
     *     description="Type has been created successfully."
     *   ),
     *   @OA\Response(
     *     response="default",
     *     description="Unable to create the type."
     *   )
     * )
     */
    public function store(Request $request)
    {
        DB::beginTransaction();
        // Can only be used by Authorized personnel
        // api/type (POST)
        $this->validate($request, [
            'name' => 'required|string',
            'desc' => 'required|string',
            'icon' => 'required|string',
        ]);
        error_log('Creating type.');
        $params = collect([
            'name' => $request->name,
            'desc' => $request->desc,
            'icon' => $request->icon,
        ]);
        //Convert To Json Object
        $params = json_decode(json_encode($params));
        $type = $this->createType($request->user(), $params);

        if ($this->isEmpty($type)) {
            DB::rollBack();
            $data['data'] = null;
            $data['status'] = 'error';
            $data['msg'] = $this->getErrorMsg();
            $data['code'] = 404;
            return response()->json($data, 404);
        } else {
            DB::commit();
            $data['status'] = 'success';
            $data['msg'] = $this->getCreatedSuccessMsg('Type');
            $data['data'] = $type;
            $data['code'] = 200;
            return response()->json($data, 200);
        }
    }


    /**
     * @OA\Put(
     *   tags={"TypeControllerService"},
     *   path="/api/type/{uid}",
     *   summary="Update type by Uid.",
     *     operationId="updateTypeByUid",
     *   @OA\Parameter(
     *     name="uid",
     *     in="path",
     *     description="Type_ID, NOT 'ID'.",
     *     required=true,
     *     @OA\Schema(type="string")
     *   ),
     * * @OA\Parameter(
     * name="name",
     * in="query",
     * description="Type name",
     * required=true,
     * @OA\Schema(
     *              type="string"
     *          )
     * ),
     * @OA\Parameter(
     * name="desc",
     * in="query",
     * description="Type Description",
     * required=true,
     * @OA\Schema(
     *              type="string"
     *          )
     * ),
     * @OA\Parameter(
     * name="icon",
     * in="query",
     * description="Icon",
     * required=true,
     * @OA\Schema(
     *              type="string"
     *          )
     * ),
     *   @OA\Response(
     *     response=200,
     *     description="Type has been updated successfully."
     *   ),
     *   @OA\Response(
     *     response="default",
     *     description="Unable to update the type."
     *   )
     * )
     */
    public function update(Request $request, $uid)
    {
        DB::beginTransaction();
        // api/type/{typeid} (PUT)
        error_log('Updating type of uid: ' . $uid);
        $type = $this->getType($request->user(), $uid);
        error_log($type);
        $this->validate($request, [
            'name' => 'required|string',
            'desc' => 'required|string',
            'icon' => 'required|string',
        ]);

        if ($this->isEmpty($type)) {
            DB::rollBack();
            $data['data'] = null;
            $data['msg'] = $this->getNotFoundMsg('Type');
            $data['status'] = 'error';
            $data['code'] = 404;
            return response()->json($data, 404);
        }

        $params = collect([
            'name' => $request->name,
            'desc' => $request->desc,
            'icon' => $request->icon,
        ]);
        //Convert To Json Object
        $params = json_decode(json_encode($params));
        $type = $this->updateType($request->user(), $type, $params);
        if ($this->isEmpty($type)) {
            DB::rollBack();
            $data['data'] = null;
            $data['msg'] = $this->getErrorMsg('Type');
            $data['status'] = 'error';
            $data['code'] = 404;
            return response()->json($data, 404);
        } else {
            DB::commit();
            $data['status'] = 'success';
            $data['msg'] = $this->getUpdatedSuccessMsg('Type');
            $data['data'] = $type;
            $data['status'] = 'success';
            $data['code'] = 200;
            return response()->json($data, 200);
        }
    }

    /**
     * @OA\Delete(
     *   tags={"TypeControllerService"},
     *   path="/api/type/{uid}",
     *   summary="Set type's 'status' to 0.",
     *     operationId="deleteTypeByUid",
     *   @OA\Parameter(
     *     name="uid",
     *     in="path",
     *     description="Type ID, NOT 'ID'.",
     *     required=true,
     *     @OA\SChema(type="string")
     *   ),
     *   @OA\Response(
     *     response=200,
     *     description="Type has been 'deleted' successfully."
     *   ),
     *   @OA\Response(
     *     response="default",
     *     description="Unable to 'delete' the type."
     *   )
     * )
     */
    public function destroy(Request $request, $uid)
    {
        DB::beginTransaction();
        // TODO ONLY TOGGLES THE status = 1/0
        // api/type/{typeid} (DELETE)
        error_log('Deleting type of uid: ' . $uid);
        $type = $this->getType($request->user(), $uid);
        if ($this->isEmpty($type)) {
            DB::rollBack();
            $data['status'] = 'error';
            $data['msg'] = $this->getNotFoundMsg('Type');
            $data['data'] = null;
            $data['code'] = 404;
            return response()->json($data, 404);
        }
        $type = $this->deleteType($request->user(), $type->id);
        if ($this->isEmpty($type)) {
            DB::rollBack();
            $data['status'] = 'error';
            $data['msg'] = $this->getErrorMsg();
            $data['data'] = null;
            $data['code'] = 404;
            return response()->json($data, 404);
        } else {
            DB::commit();
            $data['status'] = 'success';
            $data['msg'] = $this->getDeletedSuccessMsg('Type');
            $data['data'] = $type;
            $data['code'] = 200;
            return response()->json($data, 200);
        }
    }

}
