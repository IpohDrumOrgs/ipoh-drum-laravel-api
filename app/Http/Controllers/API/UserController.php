<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use DB;
use Carbon\Carbon;
use App\User;
use Illuminate\Support\Facades\Hash;
use App\Traits\GlobalFunctions;
use App\Traits\NotificationFunctions;
use App\Traits\UserServices;
use App\Traits\LogServices;

class UserController extends Controller
{
    use GlobalFunctions, NotificationFunctions, UserServices, LogServices;

    /**
     * @OA\Get(
     *      path="/api/user",
     *      operationId="getUserList",
     *      tags={"UserControllerService"},
     *      summary="Get list of users",
     *      description="Returns list of users",
     *      @OA\Response(
     *          response=200,
     *          description="Successfully retrieved list of users"
     *       ),
     *       @OA\Response(
     *          response="default",
     *          description="Unable to retrieve list of users")
     *    )
     */
    public function index(Request $request)
    {
        error_log('Retrieving list of users.');
        // api/user (GET)
        $users = $this->getUserListing($request->user());
        if ($this->isEmpty($users)) {
            $data['status'] = 'error';
            $data['data'] = null;
            $data['maximumPages'] = 0;
            $data['msg'] = $this->getNotFoundMsg('Users');
            $data['code'] = 404;
            return response()->json($data, 404);
        } else {
            //Page Pagination Result List
            //Default return 10
            $paginateddata = $this->paginateResult($users, $request->result, $request->page);
            $data['status'] = 'success';
            $data['data'] = $paginateddata;
            $data['maximumPages'] = $this->getMaximumPaginationPage($users->count(), $request->result);
            $data['msg'] = $this->getRetrievedSuccessMsg('Users');
            $data['code'] = 200;
            return response()->json($data, 200);
        }
    }
    /**
     * @OA\Get(
     *      path="/api/pluck/users",
     *      operationId="pluckUserList",
     *      tags={"UserControllerService"},
     *      summary="pluck list of users",
     *      description="Returns list of plucked users",
     *   @OA\Parameter(
     *     name="cols",
     *     in="query",
     *     required=true,
     *     description="Columns for pluck",
     *     @OA\Schema(type="string")
     *   ),
     *      @OA\Response(
     *          response=200,
     *          description="Successfully retrieved list of users"
     *       ),
     *       @OA\Response(
     *          response="default",
     *          description="Unable to retrieve list of users")
     *    )
     */
    public function pluckIndex(Request $request)
    {
        error_log('Retrieving list of plucked users.');
        // api/pluck/users (GET)
        error_log("columns = " . collect($this->splitToArray($request->cols)));
        $users = $this->pluckUserIndex($this->splitToArray($request->cols));
        if ($this->isEmpty($users)) {
            $data['status'] = 'error';
            $data['data'] = null;
            $data['maximumPages'] = 0;
            $data['msg'] = $this->getNotFoundMsg('Users');
            $data['code'] = 404;
            return response()->json($data, 404);
        } else {
            //Page Pagination Result List
            //Default return 10
            $paginateddata = $this->paginateResult($users, $request->result, $request->page);
            $data['status'] = 'success';
            $data['data'] = $paginateddata;
            $data['maximumPages'] = $this->getMaximumPaginationPage($users->count(), $request->result);
            $data['msg'] = $this->getRetrievedSuccessMsg('Users');
            $data['code'] = 200;
            return response()->json($data, 200);
        }
    }

    /**
     * @OA\Get(
     *      path="/api/filter/user",
     *      operationId="filterUserList",
     *      tags={"UserControllerService"},
     *      summary="Filter list of users",
     *      description="Returns list of filtered users",
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
     *     name="company_id",
     *     in="query",
     *     description="Company id for filter",
     *     @OA\Schema(type="string")
     *   ),
     *      @OA\Response(
     *          response=200,
     *          description="Successfully retrieved list of filtered users"
     *       ),
     *       @OA\Response(
     *          response="default",
     *          description="Unable to retrieve list of users")
     *    )
     */
    public function filter(Request $request)
    {
        error_log('Retrieving list of filtered users.');
        // api/user/filter (GET)
        $params = collect([
            'keyword' => $request->keyword,
            'fromdate' => $request->fromdate,
            'todate' => $request->todate,
            'status' => $request->status,
            'company_id' => $request->company_id,
        ]);
        //Convert To Json Object
        $params = json_decode(json_encode($params));
        $users = $this->filterUserListing($request->user(), $params);

        if ($this->isEmpty($users)) {
            $data['data'] = null;
            $data['maximumPages'] = 0;
            $data['msg'] = $this->getNotFoundMsg('Users');
            $data['code'] = 404;
            return response()->json($data, 404);
        } else {
            //Page Pagination Result List
            //Default return 10
            $paginateddata = $this->paginateResult($users, $request->result, $request->page);
            $data['data'] = $paginateddata;
            $data['maximumPages'] = $this->getMaximumPaginationPage($users->count(), $request->result);
            $data['msg'] = $this->getRetrievedSuccessMsg('Users');
            $data['code'] = 200;
            return response()->json($data, 200);
        }

    }

    /**
     * @OA\Get(
     *      path="/api/pluck/filter/user",
     *      operationId="filterPluckedUserList",
     *      tags={"UserControllerService"},
     *      summary="Filter list of plucked users",
     *      description="Returns list of filtered users",
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
     *     name="company_id",
     *     in="query",
     *     description="Company id for filter",
     *     @OA\Schema(type="string")
     *   ),
     *      @OA\Response(
     *          response=200,
     *          description="Successfully retrieved list of filtered users"
     *       ),
     *       @OA\Response(
     *          response="default",
     *          description="Unable to retrieve list of users")
     *    )
     */
    public function pluckFilter(Request $request)
    {
        error_log('Retrieving list of filtered and plucked users.');
        // api/pluck/filter/user (GET)
        $params = collect([
            'keyword' => $request->keyword,
            'fromdate' => $request->fromdate,
            'todate' => $request->todate,
            'status' => $request->status,
            'company_id' => $request->company_id,
        ]);
        //Convert To Json Object
        $params = json_decode(json_encode($params));
        $users = $this->pluckUserFilter($this->splitToArray($request->cols) , $params);

        if ($this->isEmpty($users)) {
            $data['data'] = null;
            $data['maximumPages'] = 0;
            $data['msg'] = $this->getNotFoundMsg('Users');
            $data['code'] = 404;
            return response()->json($data, 404);
        } else {
            //Page Pagination Result List
            //Default return 10
            $paginateddata = $this->paginateResult($users, $request->result, $request->page);
            $data['data'] = $paginateddata;
            $data['maximumPages'] = $this->getMaximumPaginationPage($users->count(), $request->result);
            $data['msg'] = $this->getRetrievedSuccessMsg('Users');
            $data['code'] = 200;
            return response()->json($data, 200);
        }

    }

    /**
     * @OA\Get(
     *   tags={"UserControllerService"},
     *   path="/api/user/{uid}",
     *   summary="Retrieves user by userId.",
     *     operationId="getUserByUserId",
     *   @OA\Parameter(
     *     name="uid",
     *     in="path",
     *     description="User_ID, NOT 'ID'.",
     *     required=true,
     *     @OA\Schema(type="string")
     *   ),
     *   @OA\Response(
     *     response=200,
     *     description="User has been retrieved successfully."
     *   ),
     *   @OA\Response(
     *     response="default",
     *     description="Unable to retrieve the user."
     *   )
     * )
     */
    public function show(Request $request, $uid)
    {
        // api/user/{userid} (GET)
        error_log('Retrieving user of uid:' . $uid);
        $user = $this->getUser($request->user(), $uid);
        if ($this->isEmpty($user)) {
            $data['data'] = null;
            $data['msg'] = $this->getNotFoundMsg('User');
            $data['status'] = 'error';
            $data['code'] = 404;
            return response()->json($data, 404);
        } else {
            $data['data'] = $user;
            $data['msg'] = $this->getRetrievedSuccessMsg('User');
            $data['status'] = 'success';
            $data['code'] = 200;
            return response()->json($data, 200);
        }
    }

    /**
     * @OA\Get(
     *      path="/api/pluck/user/{uid}",
     *      operationId="pluckUser",
     *      tags={"UserControllerService"},
     *      summary="pluck user",
     *      description="Returns plucked users",
     *   @OA\Parameter(
     *     name="uid",
     *     in="path",
     *     description="User_ID, NOT 'ID'.",
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
     *          description="Successfully retrieved list of users"
     *       ),
     *       @OA\Response(
     *          response="default",
     *          description="Unable to retrieve list of users")
     *    )
     */
    public function pluckShow(Request $request , $uid)
    {
        error_log('Retrieving plucked users.');
        // api/pluck/user/{uid} (GET)
        error_log("columns = " . collect($this->splitToArray($request->cols)));
        $user = $this->pluckUser($this->splitToArray($request->cols) , $uid);
        if ($this->isEmpty($user)) {
            $data['data'] = null;
            $data['status'] = 'error';
            $data['msg'] = $this->getNotFoundMsg('User');
            $data['code'] = 404;
            return response()->json($data, 404);
        } else {
            $data['status'] = 'success';
            $data['msg'] = $this->getRetrievedSuccessMsg('User');
            $data['data'] = $user;
            $data['code'] = 200;
            return response()->json($data, 200);
        }
    }

    
    /**
     * @OA\Post(
     *   tags={"UserControllerService"},
     *   path="/api/user",
     *   summary="Creates a user.",
     *   operationId="createUser",
     * @OA\Parameter(
     * name="name",
     * in="query",
     * description="Username",
     * required=true,
     * @OA\Schema(
     *              type="string"
     *          )
     * ),
     * @OA\Parameter(
     * name="email",
     * in="query",
     * description="Email",
     * required=true,
     * @OA\Schema(
     *              type="string"
     *          )
     * ),
     * @OA\Parameter(
     * name="password",
     * in="query",
     * description="Password",
     * required=true,
     * @OA\Schema(
     *              type="string"
     *          )
     * ),
     * @OA\Parameter(
     * name="password_confirmation",
     * in="query",
     * description="Password Confirmation",
     * required=true,
     * @OA\Schema(
     *              type="string"
     *          )
     * ),
     * @OA\Parameter(
     * name="country",
     * in="query",
     * description="Country",
     * @OA\Schema(
     *  type="string"
     *  )
     * ),
     *   @OA\Response(
     *     response=200,
     *     description="User has been created successfully."
     *   ),
     *   @OA\Response(
     *     response="default",
     *     description="Unable to create the user."
     *   )
     * )
     */
    public function store(Request $request)
    {
        // Can only be used by Authorized personnel
        // api/user (POST)
        $this->validate($request, [
            'email' => 'nullable|string|email|max:191|unique:users',
            'password' => 'required|string|min:6|confirmed',
        ]);
        error_log('Creating user.');
        $params = collect([
            'icno' => $request->icno,
            'name' => $request->name,
            'email' => $request->email,
            'tel1' => $request->tel1,
            'tel2' => $request->tel2,
            'address1' => $request->address1,
            'address2' => $request->address2,
            'postcode' => $request->postcode,
            'state' => $request->state,
            'city' => $request->city,
            'country' => $request->country,
            'password' => $request->password,
        ]);
        //Convert To Json Object
        $params = json_decode(json_encode($params));
        $user = $this->createUser($request->user(), $params);

        if ($this->isEmpty($user)) {
            $data['data'] = null;
            $data['status'] = 'error';
            $data['msg'] = $this->getErrorMsg();
            $data['code'] = 404;
            return response()->json($data, 404);
        } else {
            $data['status'] = 'success';
            $data['msg'] = $this->getCreatedSuccessMsg('User');
            $data['data'] = $user;
            $data['code'] = 200;
            return response()->json($data, 200);
        }
    }

    /**
     * @OA\Put(
     *   tags={"UserControllerService"},
     *   path="/api/user/{uid}",
     *   summary="Update user by userId.",
     *     operationId="updateUserByUserId",
     *   @OA\Parameter(
     *     name="uid",
     *     in="path",
     *     description="User_ID, NOT 'ID'.",
     *     required=true,
     *     @OA\Schema(type="string")
     *   ),
     *   @OA\Parameter(
     *     name="name",
     *     in="query",
     *     description="Username.",
     *     required=true,
     *     @OA\Schema(type="string")
     *   ),
     *  @OA\Parameter(
     *     name="email",
     *     in="query",
     *     description="Email.",
     *     required=true,
     *     @OA\Schema(type="string")
     *   ),
     *  @OA\Parameter(
     *     name="tel1",
     *     in="query",
     *     description="Telephone Number #1.",
     *     required=false,
     *     @OA\Schema(type="string")
     *   ),
     *  @OA\Parameter(
     *     name="address1",
     *     in="query",
     *     description="Address #1.",
     *     required=false,
     *     @OA\Schema(type="string")
     *   ),
     *  @OA\Parameter(
     *     name="city",
     *     in="query",
     *     description="City.",
     *     required=false,
     *     @OA\Schema(type="string")
     *   ),
     *  @OA\Parameter(
     *     name="postcode",
     *     in="query",
     *     description="PostCode.",
     *     required=false,
     *     @OA\Schema(type="string")
     *   ),
     *  @OA\Parameter(
     *     name="state",
     *     in="query",
     *     description="State.",
     *     required=false,
     *     @OA\Schema(type="string")
     *   ),
     *  @OA\Parameter(
     *     name="country",
     *     in="query",
     *     description="Country.",
     *     required=true,
     *     @OA\Schema(type="string")
     *   ),
     *   @OA\Parameter(
     *     name="icno",
     *     in="query",
     *     description="IC Number.",
     *     required=false,
     *     @OA\Schema(type="string")
     *     ),
     *   @OA\Response(
     *     response=200,
     *     description="User has been updated successfully."
     *   ),
     *   @OA\Response(
     *     response="default",
     *     description="Unable to update the user."
     *   )
     * )
     */
    public function update(Request $request, $uid)
    {
        // api/user/{userid} (PUT)
        error_log('Updating user of uid: ' . $uid);
        $user = $this->getUser($request->user(), $uid);
        $this->validate($request, [
            'email' => 'required|string|max:191|unique:users,email,' . $user->id,
            'name' => 'required|string|max:191',
        ]);
        if ($this->isEmpty($user)) {
            $data['data'] = null;
            $data['msg'] = $this->getNotFoundMsg('User');
            $data['status'] = 'error';
            $data['code'] = 404;
            return response()->json($data, 404);
        }
        $params = collect([
            'icno' => $request->icno,
            'name' => $request->name,
            'email' => $request->email,
            'tel1' => $request->tel1,
            'tel2' => $request->tel2,
            'address1' => $request->address1,
            'address2' => $request->address2,
            'postcode' => $request->postcode,
            'state' => $request->state,
            'city' => $request->city,
            'country' => $request->country,
        ]);
        //Convert To Json Object
        $params = json_decode(json_encode($params));
        $user = $this->updateUser($request->user(), $user, $params);
        if ($this->isEmpty($user)) {
            $data['data'] = null;
            $data['msg'] = $this->getErrorMsg('User');
            $data['status'] = 'error';
            $data['code'] = 404;
            return response()->json($data, 404);
        } else {
            $data['status'] = 'success';
            $data['msg'] = $this->getUpdatedSuccessMsg('User');
            $data['data'] = $user;
            $data['status'] = 'success';
            $data['code'] = 200;
            return response()->json($data, 200);
        }
    }

    /**
     * @OA\Delete(
     *   tags={"UserControllerService"},
     *   path="/api/user/{uid}",
     *   summary="Set user's 'status' to 0.",
     *     operationId="deleteUserByUserId",
     *   @OA\Parameter(
     *     name="uid",
     *     in="path",
     *     description="User ID, NOT 'ID'.",
     *     required=true,
     *     @OA\SChema(type="string")
     *   ),
     *   @OA\Response(
     *     response=200,
     *     description="User has been 'deleted' successfully."
     *   ),
     *   @OA\Response(
     *     response="default",
     *     description="Unable to 'delete' the user."
     *   )
     * )
     */
    public function destroy(Request $request, $uid)
    {
        // TODO ONLY TOGGLES THE status = 1/0
        // api/user/{userid} (DELETE)
        error_log('Deleting user of uid: ' . $uid);
        $user = $this->getUser($request->user(), $uid);
        if ($this->isEmpty($user)) {
            $data['status'] = 'error';
            $data['msg'] = $this->getNotFoundMsg('User');
            $data['data'] = null;
            $data['code'] = 404;
            return response()->json($data, 404);
        }
        $user = $this->deleteUser($request->user(), $user->id);
        if ($this->isEmpty($user)) {
            $data['status'] = 'error';
            $data['msg'] = $this->getErrorMsg();
            $data['data'] = null;
            $data['code'] = 404;
            return response()->json($data, 404);
        } else {
            $data['status'] = 'success';
            $data['msg'] = $this->getDeletedSuccessMsg('User');
            $data['data'] = $user;
            $data['code'] = 200;
            return response()->json($data, 200);
        }
    }

    

    /**
     * @OA\Post(
     *   tags={"UserControllerService"},
     *   summary="Authenticates current request's user.",
     *     operationId="authenticateCurrentRequestsUser",
     * path="/api/authentication",
     *   @OA\Response(
     *     response=200,
     *     description="User is already authenticated."
     *   ),
     *   @OA\Response(
     *     response="default",
     *     description="User is not authenticated."
     *   )
     * )
     */
    public function authentication(Request $request)
    {
        // TODO Authenticate currently logged in user
        error_log('Authenticating user.');
        return response()->json($request->user(), 200);
    }

    public function register(Request $request)
    {
        // TODO Registers users without needing authorization
        error_log('Registering user.'); 
        // api/register (POST)
        $this->validate($request, [
            'name' => 'required|string|max:191',
            'email' => 'required|string|email|max:191|unique:users',
            'password' => 'required|string|min:6|confirmed',
        ]);
        DB::beginTransaction();
        $user = new User();
        $user->uid = Carbon::now()->timestamp . User::count();
        $user->name = $request->name;
        $user->email = $request->email;
        $user->password = Hash::make($request->password);
        $user->status = true;
        try {
            DB::commit();
            $user->save();
            $data['status'] = 'success';
            $data['msg'] = $this->getCreatedSuccessMsg('User Account');
            $data['data'] = $user;
            $data['code'] = 200;
            return response()->json($data, 200);
        } catch (Exception $e) {
            DB::rollBack();
            $data['data'] = null;
            $data['status'] = 'error';
            $data['msg'] = $this->getErrorMsg();
            $data['code'] = 404;
            return response()->json($data, 404);
        }
    }
}
