<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use DB;
use Carbon\Carbon;
use App\Module;
use Illuminate\Support\Facades\Hash;
use App\Traits\GlobalFunctions;
use App\Traits\NotificationFunctions;
use App\Traits\ModuleServices;
use App\Traits\LogServices;

class ModuleController extends Controller
{
    use GlobalFunctions, NotificationFunctions, ModuleServices, LogServices;

    /**
     * @OA\Get(
     *      path="/api/module",
     *      operationId="getModuleList",
     *      tags={"ModuleControllerService"},
     *      summary="Get list of modules",
     *      description="Returns list of modules",
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
     *          description="Successfully retrieved list of modules"
     *       ),
     *       @OA\Response(
     *          response="default",
     *          description="Unable to retrieve list of modules")
     *    )
     */
    public function index(Request $request)
    {
        error_log('Retrieving list of modules.');
        // api/module (GET)
        $modules = $this->getModuleListing($request->user());
        if ($this->isEmpty($modules)) {
            $data['status'] = 'error';
            $data['data'] = null;
            $data['maximumPages'] = 0;
            $data['msg'] = $this->getNotFoundMsg('Modules');
            $data['code'] = 404;
            return response()->json($data, 404);
        } else {
            //Page Pagination Result List
            //Default return 10
            $paginateddata = $this->paginateResult($modules, $request->pageSize, $request->pageNumber);
            $data['status'] = 'success';
            $data['data'] = $paginateddata;
            $data['maximumPages'] = $this->getMaximumPaginationPage($modules->count(), $request->pageSize);
            $data['msg'] = $this->getRetrievedSuccessMsg('Modules');
            $data['code'] = 200;
            return response()->json($data, 200);
        }
    }
    /**
     * @OA\Get(
     *      path="/api/pluck/modules",
     *      operationId="pluckModuleList",
     *      tags={"ModuleControllerService"},
     *      summary="pluck list of modules",
     *      description="Returns list of plucked modules",
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
     *          description="Successfully retrieved list of modules"
     *       ),
     *       @OA\Response(
     *          response="default",
     *          description="Unable to retrieve list of modules")
     *    )
     */
    public function pluckIndex(Request $request)
    {
        error_log('Retrieving list of plucked modules.');
        // api/pluck/modules (GET)
        error_log("columns = " . collect($this->splitToArray($request->cols)));
        $modules = $this->pluckModuleIndex($this->splitToArray($request->cols));
        if ($this->isEmpty($modules)) {
            $data['status'] = 'error';
            $data['data'] = null;
            $data['maximumPages'] = 0;
            $data['msg'] = $this->getNotFoundMsg('Modules');
            $data['code'] = 404;
            return response()->json($data, 404);
        } else {
            //Page Pagination Result List
            //Default return 10
            $paginateddata = $this->paginateResult($modules, $request->pageSize, $request->pageNumber);
            $data['status'] = 'success';
            $data['data'] = $paginateddata;
            $data['maximumPages'] = $this->getMaximumPaginationPage($modules->count(), $request->pageSize);
            $data['msg'] = $this->getRetrievedSuccessMsg('Modules');
            $data['code'] = 200;
            return response()->json($data, 200);
        }
    }

    /**
     * @OA\Get(
     *      path="/api/filter/module",
     *      operationId="filterModuleList",
     *      tags={"ModuleControllerService"},
     *      summary="Filter list of modules",
     *      description="Returns list of filtered modules",
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
     *     name="onmodule",
     *     in="query",
     *     description="onmodule for filter",
     *     @OA\Schema(type="string")
     *   ),
     *      @OA\Response(
     *          response=200,
     *          description="Successfully retrieved list of filtered modules"
     *       ),
     *       @OA\Response(
     *          response="default",
     *          description="Unable to retrieve list of modules")
     *    )
     */
    public function filter(Request $request)
    {
        error_log('Retrieving list of filtered modules.');
        // api/module/filter (GET)
        $params = collect([
            'keyword' => $request->keyword,
            'fromdate' => $request->fromdate,
            'todate' => $request->todate,
            'status' => $request->status,
            'onmodule' => $request->onmodule,
        ]);
        //Convert To Json Object
        $params = json_decode(json_encode($params));
        $modules = $this->filterModuleListing($request->user(), $params);

        if ($this->isEmpty($modules)) {
            $data['data'] = null;
            $data['maximumPages'] = 0;
            $data['msg'] = $this->getNotFoundMsg('Modules');
            $data['code'] = 404;
            return response()->json($data, 404);
        } else {
            //Page Pagination Result List
            //Default return 10
            $paginateddata = $this->paginateResult($modules, $request->pageSize, $request->pageNumber);
            $data['data'] = $paginateddata;
            $data['maximumPages'] = $this->getMaximumPaginationPage($modules->count(), $request->pageSize);
            $data['msg'] = $this->getRetrievedSuccessMsg('Modules');
            $data['code'] = 200;
            return response()->json($data, 200);
        }

    }

    /**
     * @OA\Get(
     *      path="/api/pluck/filter/module",
     *      operationId="filterPluckedModuleList",
     *      tags={"ModuleControllerService"},
     *      summary="Filter list of plucked modules",
     *      description="Returns list of filtered modules",
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
     *     name="onmodule",
     *     in="query",
     *     description="onmodule for filter",
     *     @OA\Schema(type="string")
     *   ),
     *      @OA\Response(
     *          response=200,
     *          description="Successfully retrieved list of filtered modules"
     *       ),
     *       @OA\Response(
     *          response="default",
     *          description="Unable to retrieve list of modules")
     *    )
     */
    public function pluckFilter(Request $request)
    {
        error_log('Retrieving list of filtered and plucked modules.');
        // api/pluck/filter/module (GET)
        $params = collect([
            'keyword' => $request->keyword,
            'fromdate' => $request->fromdate,
            'todate' => $request->todate,
            'status' => $request->status,
            'onmodule' => $request->onmodule,
        ]);
        //Convert To Json Object
        $params = json_decode(json_encode($params));
        $modules = $this->pluckModuleFilter($this->splitToArray($request->cols) , $params);

        if ($this->isEmpty($modules)) {
            $data['data'] = null;
            $data['maximumPages'] = 0;
            $data['msg'] = $this->getNotFoundMsg('Modules');
            $data['code'] = 404;
            return response()->json($data, 404);
        } else {
            //Page Pagination Result List
            //Default return 10
            $paginateddata = $this->paginateResult($modules, $request->pageSize, $request->pageNumber);
            $data['data'] = $paginateddata;
            $data['maximumPages'] = $this->getMaximumPaginationPage($modules->count(), $request->pageSize);
            $data['msg'] = $this->getRetrievedSuccessMsg('Modules');
            $data['code'] = 200;
            return response()->json($data, 200);
        }

    }

    /**
     * @OA\Get(
     *   tags={"ModuleControllerService"},
     *   path="/api/module/{uid}",
     *   summary="Retrieves module by Uid.",
     *     operationId="getModuleByUid",
     *   @OA\Parameter(
     *     name="uid",
     *     in="path",
     *     description="Module_ID, NOT 'ID'.",
     *     required=true,
     *     @OA\Schema(type="string")
     *   ),
     *   @OA\Response(
     *     response=200,
     *     description="Module has been retrieved successfully."
     *   ),
     *   @OA\Response(
     *     response="default",
     *     description="Unable to retrieve the module."
     *   )
     * )
     */
    public function show(Request $request, $uid)
    {
        // api/module/{moduleid} (GET)
        error_log('Retrieving module of uid:' . $uid);
        $module = $this->getModule($request->user(), $uid);
        if ($this->isEmpty($module)) {
            $data['data'] = null;
            $data['msg'] = $this->getNotFoundMsg('Module');
            $data['status'] = 'error';
            $data['code'] = 404;
            return response()->json($data, 404);
        } else {
            $data['data'] = $module;
            $data['msg'] = $this->getRetrievedSuccessMsg('Module');
            $data['status'] = 'success';
            $data['code'] = 200;
            return response()->json($data, 200);
        }
    }

    /**
     * @OA\Get(
     *      path="/api/pluck/module/{uid}",
     *      operationId="pluckModuleByUid",
     *      tags={"ModuleControllerService"},
     *      summary="pluck module",
     *      description="Returns plucked modules",
     *   @OA\Parameter(
     *     name="uid",
     *     in="path",
     *     description="Module_ID, NOT 'ID'.",
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
     *          description="Successfully retrieved list of modules"
     *       ),
     *       @OA\Response(
     *          response="default",
     *          description="Unable to retrieve list of modules")
     *    )
     */
    public function pluckShow(Request $request , $uid)
    {
        error_log('Retrieving plucked modules.');
        // api/pluck/module/{uid} (GET)
        error_log("columns = " . collect($this->splitToArray($request->cols)));
        $module = $this->pluckModule($this->splitToArray($request->cols) , $uid);
        if ($this->isEmpty($module)) {
            $data['data'] = null;
            $data['status'] = 'error';
            $data['msg'] = $this->getNotFoundMsg('Module');
            $data['code'] = 404;
            return response()->json($data, 404);
        } else {
            $data['status'] = 'success';
            $data['msg'] = $this->getRetrievedSuccessMsg('Module');
            $data['data'] = $module;
            $data['code'] = 200;
            return response()->json($data, 200);
        }
    }

    
    /**
     * @OA\Post(
     *   tags={"ModuleControllerService"},
     *   path="/api/module",
     *   summary="Creates a module.",
     *   operationId="createModule",
     * @OA\Parameter(
     * name="name",
     * in="query",
     * description="Module Name",
     * required=true,
     * @OA\Schema(
     *              type="string"
     *          )
     * ),
     * @OA\Parameter(
     * name="desc",
     * in="query",
     * description="Module Description",
     * @OA\Schema(
     *              type="string"
     *          )
     * ),
     * @OA\Parameter(
     * name="provider",
     * in="query",
     * description="Provider of Model",
     * required=true,
     * @OA\Schema(
     *              type="string"
     *          )
     * ),
     *   @OA\Response(
     *     response=200,
     *     description="Module has been created successfully."
     *   ),
     *   @OA\Response(
     *     response="default",
     *     description="Unable to create the module."
     *   )
     * )
     */
    public function store(Request $request)
    {
        DB::beginTransaction();
        // Can only be used by Authorized personnel
        // api/module (POST)
        
        $this->validate($request, [
            'name' => 'required|string|max:191',
            'desc' => 'nullable',
            'provider' => 'required|string|max:191',
        ]);
        error_log('Creating module.');
        $params = collect([
            'name' => $request->name,
            'desc' => $request->desc,
            'provider' => $request->provider,
        ]);
        //Convert To Json Object
        $params = json_decode(json_encode($params));
        $module = $this->createModule($request->user(), $params);

        if ($this->isEmpty($module)) {
            DB::rollBack();
            $data['data'] = null;
            $data['status'] = 'error';
            $data['msg'] = $this->getErrorMsg();
            $data['code'] = 404;
            return response()->json($data, 404);
        } else {
            DB::commit();
            $data['status'] = 'success';
            $data['msg'] = $this->getCreatedSuccessMsg('Module');
            $data['data'] = $module;
            $data['code'] = 200;
            return response()->json($data, 200);
        }
    }


    /**
     * @OA\Put(
     *   tags={"ModuleControllerService"},
     *   path="/api/module/{uid}",
     *   summary="Update module by Uid.",
     *     operationId="updateModuleByUid",
     *   @OA\Parameter(
     *     name="uid",
     *     in="path",
     *     description="Module_ID, NOT 'ID'.",
     *     required=true,
     *     @OA\Schema(type="string")
     *   ),
     * @OA\Parameter(
     * name="name",
     * in="query",
     * description="Modulename",
     * required=true,
     * @OA\Schema(
     *              type="string"
     *          )
     * ),
     * @OA\Parameter(
     * name="desc",
     * in="query",
     * description="Module Description",
     * @OA\Schema(
     *              type="string"
     *          )
     * ),
     * @OA\Parameter(
     * name="provider",
     * in="query",
     * description="Provider of Model",
     * required=true,
     * @OA\Schema(
     *              type="string"
     *          )
     * ),
     *   @OA\Response(
     *     response=200,
     *     description="Module has been updated successfully."
     *   ),
     *   @OA\Response(
     *     response="default",
     *     description="Unable to update the module."
     *   )
     * )
     */
    public function update(Request $request, $uid)
    {
        DB::beginTransaction();
        // api/module/{moduleid} (PUT) 
        error_log('Updating module of uid: ' . $uid);
        $module = $this->getModule($request->user(), $uid);
       
       
        $this->validate($request, [
            'name' => 'required|string|max:191',
            'desc' => 'nullable',
            'provider' => 'required|string|max:191',
        ]);
      
        if ($this->isEmpty($module)) {
            DB::rollBack();
            $data['data'] = null;
            $data['msg'] = $this->getNotFoundMsg('Module');
            $data['status'] = 'error';
            $data['code'] = 404;
            return response()->json($data, 404);
        }
        
        $params = collect([
            'name' => $request->name,
            'desc' => $request->desc,
            'provider' => $request->provider,
        ]);
        
        //Convert To Json Object
        $params = json_decode(json_encode($params));
        $module = $this->updateModule($request->user(), $module, $params);
        if ($this->isEmpty($module)) {
            DB::rollBack();
            $data['data'] = null;
            $data['msg'] = $this->getErrorMsg('Module');
            $data['status'] = 'error';
            $data['code'] = 404;
            return response()->json($data, 404);
        } else {
            DB::commit();
            $data['status'] = 'success';
            $data['msg'] = $this->getUpdatedSuccessMsg('Module');
            $data['data'] = $module;
            $data['status'] = 'success';
            $data['code'] = 200;
            return response()->json($data, 200);
        }
    }

    /**
     * @OA\Delete(
     *   tags={"ModuleControllerService"},
     *   path="/api/module/{uid}",
     *   summary="Set module's 'status' to 0.",
     *     operationId="deleteModuleByUid",
     *   @OA\Parameter(
     *     name="uid",
     *     in="path",
     *     description="Module ID, NOT 'ID'.",
     *     required=true,
     *     @OA\SChema(type="string")
     *   ),
     *   @OA\Response(
     *     response=200,
     *     description="Module has been 'deleted' successfully."
     *   ),
     *   @OA\Response(
     *     response="default",
     *     description="Unable to 'delete' the module."
     *   )
     * )
     */
    public function destroy(Request $request, $uid)
    {
        DB::beginTransaction();
        // TODO ONLY TOGGLES THE status = 1/0
        // api/module/{moduleid} (DELETE)
        error_log('Deleting module of uid: ' . $uid);
        $module = $this->getModule($request->user(), $uid);
        if ($this->isEmpty($module)) {
            DB::rollBack();
            $data['status'] = 'error';
            $data['msg'] = $this->getNotFoundMsg('Module');
            $data['data'] = null;
            $data['code'] = 404;
            return response()->json($data, 404);
        }
        $module = $this->deleteModule($request->user(), $module->id);
        if ($this->isEmpty($module)) {
            DB::rollBack();
            $data['status'] = 'error';
            $data['msg'] = $this->getErrorMsg();
            $data['data'] = null;
            $data['code'] = 404;
            return response()->json($data, 404);
        } else {
            DB::commit();
            $data['status'] = 'success';
            $data['msg'] = $this->getDeletedSuccessMsg('Module');
            $data['data'] = $module;
            $data['code'] = 200;
            return response()->json($data, 200);
        }
    }

}
