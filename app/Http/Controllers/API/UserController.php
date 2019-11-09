<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use DB;
use Carbon\Carbon;
use App\User;
use App\Company;
use Illuminate\Support\Facades\Hash;
use App\Traits\GlobalFunctions;
use App\Traits\NotificationFunctions;
use App\Traits\UserServices;
use App\Traits\LogServices;

class UserController extends Controller
{
    use GlobalFunctions, NotificationFunctions, UserServices , LogServices;


    /**
     * @OA\Get(
     *      path="/api/user",
     *      operationId="getUserList",
     *      tags={"UserControllerService"},
     *      summary="Get list of users",
     *      description="Returns list of users",
     *      @OA\Parameter(
     *          name="company_id",
     *          in="query",
     *          description="Company ID",
     *          required=true,
     *              @OA\Schema(
     *              type="string"
     *          )
     *      ),
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
        if($this->isEmpty($users)){
            $data['data'] = null;
            $data['maximumPages'] = 0;
            $data['msg'] = $this->getNotFoundMsg('Users');
            return response()->json($data, 404);
        }else{

              //Page Pagination Result List
            //Default return 10
            $paginateddata = $this->paginateResult($users, $request->result, $request->page);
            $data['data'] = $paginateddata;
            $data['maximumPages'] = $this->getMaximumPaginationPage($users->count(), $request->result);
            $data['msg'] = $this->getRetrievedSuccessMsg('Users');
            return response()->json($data, 200);
        }
    }

    public function filter(Request $request)
    {
        error_log('Retrieving list of filtered users.');
        // api/user/filter (GET)
        $condition = collect([
            'keyword' => $request->keyword,
            'fromdate' => $request->fromdate,
            'todate' => $request->todate,
            'status' => $request->status,
        ]);
        //Convert To Json Object
        $condition = json_decode(json_encode($condition));
        $users = $this->filterUserListing($request->user() , $condition );

        if($this->isEmpty($user)){
            $data['data'] = null;
            $data['maximumPages'] = 0;
            $data['msg'] = $this->getNotFoundMsg('Users');
            return response()->json($data, 404);
        }else{
            //Page Pagination Result List
            //Default return 10
            $paginateddata = $this->paginateResult($users, $request->result, $request->page);
            $data['data'] = $paginateddata;
            $data['maximumPages'] = $this->getMaximumPaginationPage($users->count(), $request->result);
            $data['msg'] = $this->getRetrievedSuccessMsg('Users');
            return response()->json($data, 200);
        }

    }
    /**
     * @OA\Post(
     *   tags={"UserControllerService"},
     *   path="/api/user",
     *   summary="Creates a user.",
     *     operationId="createUser",
     *   @OA\Parameter(
     *     name="name",
     *     in="query",
     *     description="Username",
     *     required=true,
     *              @OA\Schema(
     *              type="string"
     *          )
     *   ),
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
     *      * @OA\Parameter(
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
     * description="Country Name",
     * required=false,
     * @OA\Schema(
     *              type="string"
     *          )
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
            'email' => $request->email,
            'address1' => $request->address1,
            'postcode' => $request->postcode,
            'address1' => $request->address1,
            'state' => $request->state,
            'city' => $request->city,
            'tel2' => $request->tel2,
            'address2' => $request->address2,
            'country' => $request->country,
            'password' => $request->password,
        ]);
        //Convert To Json Object
        $params = json_decode(json_encode($params));
        $user = $this->createUser($request->user(), $params );
        $this->createLog($request->user()->id , [$user->id] , 'store' , 'user');

        if($this->isEmpty($user)){
            $data['data'] = null;
            $data['status'] = 'error';
            $data['msg'] = $this->getErrorMsg();
            return response()->json($data, 404);
        }else{
            $data['status'] = 'success';
            $data['msg'] = $this->getCreateSuccessMsg('User');
            $data['data'] =  $user;
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
        $user = $this->getUser($request->user() , $uid);
        error_log($user);
        if ($this->isEmpty($user)) {
            $data['data'] = null;
            $data['status'] = 'error';
            $data['msg'] = $this->getNotFoundMsg('User');
            return response()->json($data, 404);
        } else {
            $data['status'] = 'success';
            $data['msg'] = $this->getRetrievedSuccessMsg('User');
            $data['data'] = $user;
            error_log($user);
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
    // TODO: Change required to false for country in the future
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
            $data['status'] = 'error';
            $data['msg'] = $this->getNotFoundMsg('User');
            return response()->json($data, 404);
        }

        $params = collect([
            'icno' => $request->icno,
            'name' => $request->name,
            'email' => $request->email,
            'tel1' => $request->tel1,
            'email' => $request->email,
            'address1' => $request->address1,
            'postcode' => $request->postcode,
            'address1' => $request->address1,
            'state' => $request->state,
            'city' => $request->city,
            'tel2' => $request->tel2,
            'address2' => $request->address2,
            'country' => $request->country,
        ]);
        //Convert To Json Object
        $params = json_decode(json_encode($params));
        $user = $this->updateUser($request->user(), $user , $params);
        if ($this->isEmpty($user)) {
            $data['data'] = null;
            $data['status'] = 'error';
            $data['msg'] = $this->getErrorMsg();
            return response()->json($data, 404);
        } else {
            $data['status'] = 'success';
            $data['msg'] = $this->getUpdateSuccessMsg('User');
            $data['data'] = $user;

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
        // api/user/{userid} (DELETE)
        error_log('Deleting user of uid: ' . $uid);
        $user = $this->getUser($request->user() , $uid);
        if ($this->isEmpty($user)) {
            $data['data'] = null;
            $data['status'] = 'error';
            $data['msg'] = $this->getNotFoundMsg('User');
            return response()->json($data, 404);
        }

        $user = $this->deleteUser($request->user() , $user->id);

        if ($this->isEmpty($user)) {
            $data['data'] = null;
            $data['status'] = 'error';
            $data['msg'] = $this->getErrorMsg();
            return response()->json($data, 404);
        } else {
            $data['status'] = 'success';
            $data['msg'] = $this->getDeleteSuccessMsg('User');
            $data['data'] = $user;
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
        error_log('Authenticating user.');
        return response()->json($request->user(), 200);
    }


    public function register(Request $request)
    {
        error_log('Registering user.');
        $params = collect([
            'email' => $request->email,
            'password' => $request->password,
            'country' => 'MALAYSIA',
        ]);
        //Convert To Json Object
        $params = json_decode(json_encode($params));
        $user = $this->createUser($request->user() , $params);
        return response()->json($request->user(), 200);
    }
}
