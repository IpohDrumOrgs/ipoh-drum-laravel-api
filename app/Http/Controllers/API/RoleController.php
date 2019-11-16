<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use DB;
use Carbon\Carbon;
use App\Role;
use Illuminate\Support\Facades\Hash;
use App\Traits\GlobalFunctions;
use App\Traits\NotificationFunctions;
use App\Traits\RoleServices;
use App\Traits\LogServices;

class RoleController extends Controller
{
    use GlobalFunctions, NotificationFunctions, RoleServices, LogServices;
    private $controllerName = '[RoleController]';
    /**
     * @OA\Get(
     *      path="/api/role",
     *      operationId="getRoleList",
     *      tags={"RoleControllerService"},
     *      summary="Get list of roles",
     *      description="Returns list of roles",
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
     *          description="Successfully retrieved list of roles"
     *       ),
     *       @OA\Response(
     *          response="default",
     *          description="Unable to retrieve list of roles")
     *    )
     */
    public function index(Request $request)
    {
        error_log('Retrieving list of roles.');
        // api/role (GET)
        $roles = $this->getRoleListing($request->user());
        if ($this->isEmpty($roles)) {
            $data['status'] = 'error';
            $data['data'] = null;
            $data['maximumPages'] = 0;
            $data['msg'] = $this->getNotFoundMsg('Roles');
            $data['code'] = 404;
            return response()->json($data, 404);
        } else {
            //Page Pagination Result List
            //Default return 10
            $paginateddata = $this->paginateResult($roles, $request->pageSize, $request->pageNumber);
            $data['status'] = 'success';
            $data['data'] = $paginateddata;
            $data['maximumPages'] = $this->getMaximumPaginationPage($roles->count(), $request->pageSize);
            $data['msg'] = $this->getRetrievedSuccessMsg('Roles');
            $data['code'] = 200;
            return response()->json($data, 200);
        }
    }
    /**
     * @OA\Get(
     *      path="/api/pluck/roles",
     *      operationId="pluckRoleList",
     *      tags={"RoleControllerService"},
     *      summary="pluck list of roles",
     *      description="Returns list of plucked roles",
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
     *          description="Successfully retrieved list of roles"
     *       ),
     *       @OA\Response(
     *          response="default",
     *          description="Unable to retrieve list of roles")
     *    )
     */
    public function pluckIndex(Request $request)
    {
        error_log('Retrieving list of plucked roles.');
        // api/pluck/roles (GET)
        error_log("columns = " . collect($this->splitToArray($request->cols)));
        $roles = $this->pluckRoleIndex($this->splitToArray($request->cols));
        if ($this->isEmpty($roles)) {
            $data['status'] = 'error';
            $data['data'] = null;
            $data['maximumPages'] = 0;
            $data['msg'] = $this->getNotFoundMsg('Roles');
            $data['code'] = 404;
            return response()->json($data, 404);
        } else {
            //Page Pagination Result List
            //Default return 10
            $paginateddata = $this->paginateResult($roles, $request->pageSize, $request->pageNumber);
            $data['status'] = 'success';
            $data['data'] = $paginateddata;
            $data['maximumPages'] = $this->getMaximumPaginationPage($roles->count(), $request->pageSize);
            $data['msg'] = $this->getRetrievedSuccessMsg('Roles');
            $data['code'] = 200;
            return response()->json($data, 200);
        }
    }

    /**
     * @OA\Get(
     *      path="/api/filter/role",
     *      operationId="filterRoleList",
     *      tags={"RoleControllerService"},
     *      summary="Filter list of roles",
     *      description="Returns list of filtered roles",
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
     *     name="onrole",
     *     in="query",
     *     description="onrole for filter",
     *     @OA\Schema(type="string")
     *   ),
     *      @OA\Response(
     *          response=200,
     *          description="Successfully retrieved list of filtered roles"
     *       ),
     *       @OA\Response(
     *          response="default",
     *          description="Unable to retrieve list of roles")
     *    )
     */
    public function filter(Request $request)
    {
        error_log('Retrieving list of filtered roles.');
        // api/role/filter (GET)
        $params = collect([
            'keyword' => $request->keyword,
            'fromdate' => $request->fromdate,
            'todate' => $request->todate,
            'status' => $request->status,
            'onrole' => $request->onrole,
        ]);
        //Convert To Json Object
        $params = json_decode(json_encode($params));
        $roles = $this->filterRoleListing($request->user(), $params);

        if ($this->isEmpty($roles)) {
            $data['data'] = null;
            $data['maximumPages'] = 0;
            $data['msg'] = $this->getNotFoundMsg('Roles');
            $data['code'] = 404;
            return response()->json($data, 404);
        } else {
            //Page Pagination Result List
            //Default return 10
            $paginateddata = $this->paginateResult($roles, $request->pageSize, $request->pageNumber);
            $data['data'] = $paginateddata;
            $data['maximumPages'] = $this->getMaximumPaginationPage($roles->count(), $request->pageSize);
            $data['msg'] = $this->getRetrievedSuccessMsg('Roles');
            $data['code'] = 200;
            return response()->json($data, 200);
        }

    }

    /**
     * @OA\Get(
     *      path="/api/pluck/filter/role",
     *      operationId="filterPluckedRoleList",
     *      tags={"RoleControllerService"},
     *      summary="Filter list of plucked roles",
     *      description="Returns list of filtered roles",
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
     *     name="onrole",
     *     in="query",
     *     description="onrole for filter",
     *     @OA\Schema(type="string")
     *   ),
     *      @OA\Response(
     *          response=200,
     *          description="Successfully retrieved list of filtered roles"
     *       ),
     *       @OA\Response(
     *          response="default",
     *          description="Unable to retrieve list of roles")
     *    )
     */
    public function pluckFilter(Request $request)
    {
        error_log('Retrieving list of filtered and plucked roles.');
        // api/pluck/filter/role (GET)
        $params = collect([
            'keyword' => $request->keyword,
            'fromdate' => $request->fromdate,
            'todate' => $request->todate,
            'status' => $request->status,
            'onrole' => $request->onrole,
        ]);
        //Convert To Json Object
        $params = json_decode(json_encode($params));
        $roles = $this->pluckRoleFilter($this->splitToArray($request->cols) , $params);

        if ($this->isEmpty($roles)) {
            $data['data'] = null;
            $data['maximumPages'] = 0;
            $data['msg'] = $this->getNotFoundMsg('Roles');
            $data['code'] = 404;
            return response()->json($data, 404);
        } else {
            //Page Pagination Result List
            //Default return 10
            $paginateddata = $this->paginateResult($roles, $request->pageSize, $request->pageNumber);
            $data['data'] = $paginateddata;
            $data['maximumPages'] = $this->getMaximumPaginationPage($roles->count(), $request->pageSize);
            $data['msg'] = $this->getRetrievedSuccessMsg('Roles');
            $data['code'] = 200;
            return response()->json($data, 200);
        }

    }

    /**
     * @OA\Get(
     *   tags={"RoleControllerService"},
     *   path="/api/role/{uid}",
     *   summary="Retrieves role by Uid.",
     *     operationId="getRoleByUid",
     *   @OA\Parameter(
     *     name="uid",
     *     in="path",
     *     description="Role_ID, NOT 'ID'.",
     *     required=true,
     *     @OA\Schema(type="string")
     *   ),
     *   @OA\Response(
     *     response=200,
     *     description="Role has been retrieved successfully."
     *   ),
     *   @OA\Response(
     *     response="default",
     *     description="Unable to retrieve the role."
     *   )
     * )
     */
    public function show(Request $request, $uid)
    {
        // api/role/{roleid} (GET)
        error_log('Retrieving role of uid:' . $uid);
        $role = $this->getRole($request->user(), $uid);
        if ($this->isEmpty($role)) {
            $data['data'] = null;
            $data['msg'] = $this->getNotFoundMsg('Role');
            $data['status'] = 'error';
            $data['code'] = 404;
            return response()->json($data, 404);
        } else {
            $data['data'] = $role;
            $data['msg'] = $this->getRetrievedSuccessMsg('Role');
            $data['status'] = 'success';
            $data['code'] = 200;
            return response()->json($data, 200);
        }
    }

    /**
     * @OA\Get(
     *      path="/api/pluck/role/{uid}",
     *      operationId="pluckRoleByUid",
     *      tags={"RoleControllerService"},
     *      summary="pluck role",
     *      description="Returns plucked roles",
     *   @OA\Parameter(
     *     name="uid",
     *     in="path",
     *     description="Role_ID, NOT 'ID'.",
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
     *          description="Successfully retrieved list of roles"
     *       ),
     *       @OA\Response(
     *          response="default",
     *          description="Unable to retrieve list of roles")
     *    )
     */
    public function pluckShow(Request $request , $uid)
    {
        error_log('Retrieving plucked roles.');
        // api/pluck/role/{uid} (GET)
        error_log("columns = " . collect($this->splitToArray($request->cols)));
        $role = $this->pluckRole($this->splitToArray($request->cols) , $uid);
        if ($this->isEmpty($role)) {
            $data['data'] = null;
            $data['status'] = 'error';
            $data['msg'] = $this->getNotFoundMsg('Role');
            $data['code'] = 404;
            return response()->json($data, 404);
        } else {
            $data['status'] = 'success';
            $data['msg'] = $this->getRetrievedSuccessMsg('Role');
            $data['data'] = $role;
            $data['code'] = 200;
            return response()->json($data, 200);
        }
    }

    
    /**
     * @OA\Post(
     *   tags={"RoleControllerService"},
     *   path="/api/role",
     *   summary="Creates a role.",
     *   operationId="createRole",
     * @OA\Parameter(
     * name="name",
     * in="query",
     * description="Role Name",
     * required=true,
     * @OA\Schema(
     *              type="string"
     *          )
     * ),
     * @OA\Parameter(
     * name="desc",
     * in="query",
     * description="Role Description",
     * @OA\Schema(
     *              type="string"
     *          )
     * ),
     *   @OA\Response(
     *     response=200,
     *     description="Role has been created successfully."
     *   ),
     *   @OA\Response(
     *     response="default",
     *     description="Unable to create the role."
     *   )
     * )
     */
    public function store(Request $request)
    {
        DB::beginTransaction();
        // Can only be used by Authorized personnel
        // api/role (POST)
        
        $this->validate($request, [
            'name' => 'required|string|max:191',
            'desc' => 'nullable',
        ]);
        error_log('Creating role.');
        $params = collect([
            'name' => $request->name,
            'desc' => $request->desc,
        ]);
        //Convert To Json Object
        $params = json_decode(json_encode($params));
        $role = $this->createRole($request->user(), $params);

        if ($this->isEmpty($role)) {
            DB::rollBack();
            $data['data'] = null;
            $data['status'] = 'error';
            $data['msg'] = $this->getErrorMsg();
            $data['code'] = 404;
            return response()->json($data, 404);
        } else {
            DB::commit();
            $data['status'] = 'success';
            $data['msg'] = $this->getCreatedSuccessMsg('Role');
            $data['data'] = $role;
            $data['code'] = 200;
            return response()->json($data, 200);
        }
    }


    /**
     * @OA\Put(
     *   tags={"RoleControllerService"},
     *   path="/api/role/{uid}",
     *   summary="Update role by Uid.",
     *     operationId="updateRoleByUid",
     *   @OA\Parameter(
     *     name="uid",
     *     in="path",
     *     description="Role_ID, NOT 'ID'.",
     *     required=true,
     *     @OA\Schema(type="string")
     *   ),
     * @OA\Parameter(
     * name="name",
     * in="query",
     * description="Rolename",
     * required=true,
     * @OA\Schema(
     *              type="string"
     *          )
     * ),
     * @OA\Parameter(
     * name="desc",
     * in="query",
     * description="Role Description",
     * @OA\Schema(
     *              type="string"
     *          )
     * ),
     *   @OA\Response(
     *     response=200,
     *     description="Role has been updated successfully."
     *   ),
     *   @OA\Response(
     *     response="default",
     *     description="Unable to update the role."
     *   )
     * )
     */
    public function update(Request $request, $uid)
    {
        DB::beginTransaction();
        // api/role/{roleid} (PUT) 
        error_log('Updating role of uid: ' . $uid);
        $role = $this->getRole($request->user(), $uid);
       
       
        $this->validate($request, [
            'name' => 'required|string|max:191',
            'desc' => 'nullable',
        ]);
      
        if ($this->isEmpty($role)) {
            DB::rollBack();
            $data['data'] = null;
            $data['msg'] = $this->getNotFoundMsg('Role');
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
        $role = $this->updateRole($request->user(), $role, $params);
        if ($this->isEmpty($role)) {
            DB::rollBack();
            $data['data'] = null;
            $data['msg'] = $this->getErrorMsg('Role');
            $data['status'] = 'error';
            $data['code'] = 404;
            return response()->json($data, 404);
        } else {
            DB::commit();
            $data['status'] = 'success';
            $data['msg'] = $this->getUpdatedSuccessMsg('Role');
            $data['data'] = $role;
            $data['status'] = 'success';
            $data['code'] = 200;
            return response()->json($data, 200);
        }
    }

    /**
     * @OA\Delete(
     *   tags={"RoleControllerService"},
     *   path="/api/role/{uid}",
     *   summary="Set role's 'status' to 0.",
     *     operationId="deleteRoleByUid",
     *   @OA\Parameter(
     *     name="uid",
     *     in="path",
     *     description="Role ID, NOT 'ID'.",
     *     required=true,
     *     @OA\SChema(type="string")
     *   ),
     *   @OA\Response(
     *     response=200,
     *     description="Role has been 'deleted' successfully."
     *   ),
     *   @OA\Response(
     *     response="default",
     *     description="Unable to 'delete' the role."
     *   )
     * )
     */
    public function destroy(Request $request, $uid)
    {
        DB::beginTransaction();
        // TODO ONLY TOGGLES THE status = 1/0
        // api/role/{roleid} (DELETE)
        error_log('Deleting role of uid: ' . $uid);
        $role = $this->getRole($request->user(), $uid);
        if ($this->isEmpty($role)) {
            DB::rollBack();
            $data['status'] = 'error';
            $data['msg'] = $this->getNotFoundMsg('Role');
            $data['data'] = null;
            $data['code'] = 404;
            return response()->json($data, 404);
        }
        $role = $this->deleteRole($request->user(), $role->id);
        if ($this->isEmpty($role)) {
            DB::rollBack();
            $data['status'] = 'error';
            $data['msg'] = $this->getErrorMsg();
            $data['data'] = null;
            $data['code'] = 404;
            return response()->json($data, 404);
        } else {
            DB::commit();
            $data['status'] = 'success';
            $data['msg'] = $this->getDeletedSuccessMsg('Role');
            $data['data'] = $role;
            $data['code'] = 200;
            return response()->json($data, 200);
        }
    }

}
