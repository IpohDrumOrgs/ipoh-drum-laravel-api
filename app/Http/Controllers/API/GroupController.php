<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use DB;
use Carbon\Carbon;
use App\Group;
use Illuminate\Support\Facades\Hash;
use App\Traits\GlobalFunctions;
use App\Traits\NotificationFunctions;
use App\Traits\GroupServices;
use App\Traits\LogServices;

class GroupController extends Controller
{
    use GlobalFunctions, NotificationFunctions, GroupServices, LogServices;
    private $controllerName = '[GroupController]';
    /**
     * @OA\Get(
     *      path="/api/group",
     *      operationId="getGroupList",
     *      tags={"GroupControllerService"},
     *      summary="Get list of groups",
     *      description="Returns list of groups",
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
     *          description="Successfully retrieved list of groups"
     *       ),
     *       @OA\Response(
     *          response="default",
     *          description="Unable to retrieve list of groups")
     *    )
     */
    public function index(Request $request)
    {
        error_log('Retrieving list of groups.');
        // api/group (GET)
        $groups = $this->getGroupListing($request->user());
        if ($this->isEmpty($groups)) {
            $data['status'] = 'error';
            $data['data'] = null;
            $data['maximumPages'] = 0;
            $data['msg'] = $this->getNotFoundMsg('Groups');
            $data['code'] = 404;
            return response()->json($data, 404);
        } else {
            //Page Pagination Result List
            //Default return 10
            $paginateddata = $this->paginateResult($groups, $request->pageSize, $request->pageNumber);
            $data['status'] = 'success';
            $data['data'] = $paginateddata;
            $data['maximumPages'] = $this->getMaximumPaginationPage($groups->count(), $request->pageSize);
            $data['msg'] = $this->getRetrievedSuccessMsg('Groups');
            $data['code'] = 200;
            return response()->json($data, 200);
        }
    }
    /**
     * @OA\Get(
     *      path="/api/pluck/groups",
     *      operationId="pluckGroupList",
     *      tags={"GroupControllerService"},
     *      summary="pluck list of groups",
     *      description="Returns list of plucked groups",
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
     *          description="Successfully retrieved list of groups"
     *       ),
     *       @OA\Response(
     *          response="default",
     *          description="Unable to retrieve list of groups")
     *    )
     */
    public function pluckIndex(Request $request)
    {
        error_log('Retrieving list of plucked groups.');
        // api/pluck/groups (GET)
        error_log("columns = " . collect($this->splitToArray($request->cols)));
        $groups = $this->pluckGroupIndex($this->splitToArray($request->cols));
        if ($this->isEmpty($groups)) {
            $data['status'] = 'error';
            $data['data'] = null;
            $data['maximumPages'] = 0;
            $data['msg'] = $this->getNotFoundMsg('Groups');
            $data['code'] = 404;
            return response()->json($data, 404);
        } else {
            //Page Pagination Result List
            //Default return 10
            $paginateddata = $this->paginateResult($groups, $request->pageSize, $request->pageNumber);
            $data['status'] = 'success';
            $data['data'] = $paginateddata;
            $data['maximumPages'] = $this->getMaximumPaginationPage($groups->count(), $request->pageSize);
            $data['msg'] = $this->getRetrievedSuccessMsg('Groups');
            $data['code'] = 200;
            return response()->json($data, 200);
        }
    }

    /**
     * @OA\Get(
     *      path="/api/filter/group",
     *      operationId="filterGroupList",
     *      tags={"GroupControllerService"},
     *      summary="Filter list of groups",
     *      description="Returns list of filtered groups",
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
     *     name="ongroup",
     *     in="query",
     *     description="ongroup for filter",
     *     @OA\Schema(type="string")
     *   ),
     *      @OA\Response(
     *          response=200,
     *          description="Successfully retrieved list of filtered groups"
     *       ),
     *       @OA\Response(
     *          response="default",
     *          description="Unable to retrieve list of groups")
     *    )
     */
    public function filter(Request $request)
    {
        error_log('Retrieving list of filtered groups.');
        // api/group/filter (GET)
        $params = collect([
            'keyword' => $request->keyword,
            'fromdate' => $request->fromdate,
            'todate' => $request->todate,
            'status' => $request->status,
            'ongroup' => $request->ongroup,
        ]);
        //Convert To Json Object
        $params = json_decode(json_encode($params));
        $groups = $this->filterGroupListing($request->user(), $params);

        if ($this->isEmpty($groups)) {
            $data['data'] = null;
            $data['maximumPages'] = 0;
            $data['msg'] = $this->getNotFoundMsg('Groups');
            $data['code'] = 404;
            return response()->json($data, 404);
        } else {
            //Page Pagination Result List
            //Default return 10
            $paginateddata = $this->paginateResult($groups, $request->pageSize, $request->pageNumber);
            $data['data'] = $paginateddata;
            $data['maximumPages'] = $this->getMaximumPaginationPage($groups->count(), $request->pageSize);
            $data['msg'] = $this->getRetrievedSuccessMsg('Groups');
            $data['code'] = 200;
            return response()->json($data, 200);
        }

    }

    /**
     * @OA\Get(
     *      path="/api/pluck/filter/group",
     *      operationId="filterPluckedGroupList",
     *      tags={"GroupControllerService"},
     *      summary="Filter list of plucked groups",
     *      description="Returns list of filtered groups",
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
     *     name="ongroup",
     *     in="query",
     *     description="ongroup for filter",
     *     @OA\Schema(type="string")
     *   ),
     *      @OA\Response(
     *          response=200,
     *          description="Successfully retrieved list of filtered groups"
     *       ),
     *       @OA\Response(
     *          response="default",
     *          description="Unable to retrieve list of groups")
     *    )
     */
    public function pluckFilter(Request $request)
    {
        error_log('Retrieving list of filtered and plucked groups.');
        // api/pluck/filter/group (GET)
        $params = collect([
            'keyword' => $request->keyword,
            'fromdate' => $request->fromdate,
            'todate' => $request->todate,
            'status' => $request->status,
            'ongroup' => $request->ongroup,
        ]);
        //Convert To Json Object
        $params = json_decode(json_encode($params));
        $groups = $this->pluckGroupFilter($this->splitToArray($request->cols) , $params);

        if ($this->isEmpty($groups)) {
            $data['data'] = null;
            $data['maximumPages'] = 0;
            $data['msg'] = $this->getNotFoundMsg('Groups');
            $data['code'] = 404;
            return response()->json($data, 404);
        } else {
            //Page Pagination Result List
            //Default return 10
            $paginateddata = $this->paginateResult($groups, $request->pageSize, $request->pageNumber);
            $data['data'] = $paginateddata;
            $data['maximumPages'] = $this->getMaximumPaginationPage($groups->count(), $request->pageSize);
            $data['msg'] = $this->getRetrievedSuccessMsg('Groups');
            $data['code'] = 200;
            return response()->json($data, 200);
        }

    }

    /**
     * @OA\Get(
     *   tags={"GroupControllerService"},
     *   path="/api/group/{uid}",
     *   summary="Retrieves group by Uid.",
     *     operationId="getGroupByUid",
     *   @OA\Parameter(
     *     name="uid",
     *     in="path",
     *     description="Group_ID, NOT 'ID'.",
     *     required=true,
     *     @OA\Schema(type="string")
     *   ),
     *   @OA\Response(
     *     response=200,
     *     description="Group has been retrieved successfully."
     *   ),
     *   @OA\Response(
     *     response="default",
     *     description="Unable to retrieve the group."
     *   )
     * )
     */
    public function show(Request $request, $uid)
    {
        // api/group/{groupid} (GET)
        error_log('Retrieving group of uid:' . $uid);
        $group = $this->getGroup($request->user(), $uid);
        if ($this->isEmpty($group)) {
            $data['data'] = null;
            $data['msg'] = $this->getNotFoundMsg('Group');
            $data['status'] = 'error';
            $data['code'] = 404;
            return response()->json($data, 404);
        } else {
            $data['data'] = $group;
            $data['msg'] = $this->getRetrievedSuccessMsg('Group');
            $data['status'] = 'success';
            $data['code'] = 200;
            return response()->json($data, 200);
        }
    }

    /**
     * @OA\Get(
     *      path="/api/pluck/group/{uid}",
     *      operationId="pluckGroupByUid",
     *      tags={"GroupControllerService"},
     *      summary="pluck group",
     *      description="Returns plucked groups",
     *   @OA\Parameter(
     *     name="uid",
     *     in="path",
     *     description="Group_ID, NOT 'ID'.",
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
     *          description="Successfully retrieved list of groups"
     *       ),
     *       @OA\Response(
     *          response="default",
     *          description="Unable to retrieve list of groups")
     *    )
     */
    public function pluckShow(Request $request , $uid)
    {
        error_log('Retrieving plucked groups.');
        // api/pluck/group/{uid} (GET)
        error_log("columns = " . collect($this->splitToArray($request->cols)));
        $group = $this->pluckGroup($this->splitToArray($request->cols) , $uid);
        if ($this->isEmpty($group)) {
            $data['data'] = null;
            $data['status'] = 'error';
            $data['msg'] = $this->getNotFoundMsg('Group');
            $data['code'] = 404;
            return response()->json($data, 404);
        } else {
            $data['status'] = 'success';
            $data['msg'] = $this->getRetrievedSuccessMsg('Group');
            $data['data'] = $group;
            $data['code'] = 200;
            return response()->json($data, 200);
        }
    }

    
    /**
     * @OA\Post(
     *   tags={"GroupControllerService"},
     *   path="/api/group",
     *   summary="Creates a group.",
     *   operationId="createGroup",
     * @OA\Parameter(
     * name="companyid",
     * in="query",
     * description="Group belongs to which company",
     * required=true,
     * @OA\Schema(
     *              type="integer"
     *          )
     * ),
     * @OA\Parameter(
     * name="name",
     * in="query",
     * description="Group Name",
     * required=true,
     * @OA\Schema(
     *              type="string"
     *          )
     * ),
     * @OA\Parameter(
     * name="desc",
     * in="query",
     * description="Group Description",
     * @OA\Schema(
     *              type="string"
     *          )
     * ),
     *   @OA\Response(
     *     response=200,
     *     description="Group has been created successfully."
     *   ),
     *   @OA\Response(
     *     response="default",
     *     description="Unable to create the group."
     *   )
     * )
     */
    public function store(Request $request)
    {
        DB::beginTransaction();
        // Can only be used by Authorized personnel
        // api/group (POST)
        
        $this->validate($request, [
            'companyid' => 'required|integer',
            'name' => 'required|string|max:191',
            'desc' => 'nullable',
        ]);
        error_log('Creating group.');
        $params = collect([
            'name' => $request->name,
            'desc' => $request->desc,
            'companyid' => $request->companyid,
        ]);
        //Convert To Json Object
        $params = json_decode(json_encode($params));
        $group = $this->createGroup($request->user(), $params);

        if ($this->isEmpty($group)) {
            DB::rollBack();
            $data['data'] = null;
            $data['status'] = 'error';
            $data['msg'] = $this->getErrorMsg();
            $data['code'] = 404;
            return response()->json($data, 404);
        } else {
            DB::commit();
            $data['status'] = 'success';
            $data['msg'] = $this->getCreatedSuccessMsg('Group');
            $data['data'] = $group;
            $data['code'] = 200;
            return response()->json($data, 200);
        }
    }


    /**
     * @OA\Put(
     *   tags={"GroupControllerService"},
     *   path="/api/group/{uid}",
     *   summary="Update group by Uid.",
     *     operationId="updateGroupByUid",
     *   @OA\Parameter(
     *     name="uid",
     *     in="path",
     *     description="Group_ID, NOT 'ID'.",
     *     required=true,
     *     @OA\Schema(type="string")
     *   ),
     * @OA\Parameter(
     * name="companyid",
     * in="query",
     * description="Group belongs to which company",
     * required=true,
     * @OA\Schema(
     *              type="integer"
     *          )
     * ),
     * @OA\Parameter(
     * name="name",
     * in="query",
     * description="Groupname",
     * required=true,
     * @OA\Schema(
     *              type="string"
     *          )
     * ),
     * @OA\Parameter(
     * name="desc",
     * in="query",
     * description="Group Description",
     * @OA\Schema(
     *              type="string"
     *          )
     * ),
     *   @OA\Response(
     *     response=200,
     *     description="Group has been updated successfully."
     *   ),
     *   @OA\Response(
     *     response="default",
     *     description="Unable to update the group."
     *   )
     * )
     */
    public function update(Request $request, $uid)
    {
        DB::beginTransaction();
        // api/group/{groupid} (PUT) 
        error_log('Updating group of uid: ' . $uid);
        $group = $this->getGroup($request->user(), $uid);
       
       
        $this->validate($request, [
            'companyid' => 'required|integer',
            'name' => 'required|string|max:191',
            'desc' => 'nullable',
        ]);
      
        if ($this->isEmpty($group)) {
            DB::rollBack();
            $data['data'] = null;
            $data['msg'] = $this->getNotFoundMsg('Group');
            $data['status'] = 'error';
            $data['code'] = 404;
            return response()->json($data, 404);
        }
        
        $params = collect([
            'name' => $request->name,
            'desc' => $request->desc,
            'companyid' => $request->companyid,
        ]);
        
        //Convert To Json Object
        $params = json_decode(json_encode($params));
        $group = $this->updateGroup($request->user(), $group, $params);
        if ($this->isEmpty($group)) {
            DB::rollBack();
            $data['data'] = null;
            $data['msg'] = $this->getErrorMsg('Group');
            $data['status'] = 'error';
            $data['code'] = 404;
            return response()->json($data, 404);
        } else {
            DB::commit();
            $data['status'] = 'success';
            $data['msg'] = $this->getUpdatedSuccessMsg('Group');
            $data['data'] = $group;
            $data['status'] = 'success';
            $data['code'] = 200;
            return response()->json($data, 200);
        }
    }

    /**
     * @OA\Delete(
     *   tags={"GroupControllerService"},
     *   path="/api/group/{uid}",
     *   summary="Set group's 'status' to 0.",
     *     operationId="deleteGroupByUid",
     *   @OA\Parameter(
     *     name="uid",
     *     in="path",
     *     description="Group ID, NOT 'ID'.",
     *     required=true,
     *     @OA\SChema(type="string")
     *   ),
     *   @OA\Response(
     *     response=200,
     *     description="Group has been 'deleted' successfully."
     *   ),
     *   @OA\Response(
     *     response="default",
     *     description="Unable to 'delete' the group."
     *   )
     * )
     */
    public function destroy(Request $request, $uid)
    {
        DB::beginTransaction();
        // TODO ONLY TOGGLES THE status = 1/0
        // api/group/{groupid} (DELETE)
        error_log('Deleting group of uid: ' . $uid);
        $group = $this->getGroup($request->user(), $uid);
        if ($this->isEmpty($group)) {
            DB::rollBack();
            $data['status'] = 'error';
            $data['msg'] = $this->getNotFoundMsg('Group');
            $data['data'] = null;
            $data['code'] = 404;
            return response()->json($data, 404);
        }
        $group = $this->deleteGroup($request->user(), $group->id);
        if ($this->isEmpty($group)) {
            DB::rollBack();
            $data['status'] = 'error';
            $data['msg'] = $this->getErrorMsg();
            $data['data'] = null;
            $data['code'] = 404;
            return response()->json($data, 404);
        } else {
            DB::commit();
            $data['status'] = 'success';
            $data['msg'] = $this->getDeletedSuccessMsg('Group');
            $data['data'] = $group;
            $data['code'] = 200;
            return response()->json($data, 200);
        }
    }

}
