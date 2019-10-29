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

class UserController extends Controller
{
    use GlobalFunctions, NotificationFunctions;

    /**
     * @SWG\Get(
     *   tags={"UserControllerService"},
     *   path="/user",
     *   summary="Retrieve list of users",
     *   produces={"application/json"},
     *   @SWG\Response(
     *     response=200,
     *     description="Successfully retrieved list of users."
     *   ),
     *   @SWG\Response(
     *     response="default",
     *     description="Unable to retrieve list of users."
     *   )
     * )
     */
    public function index(Request $request)
    {
        $users = User::where('status', true)->get();
        //Page Pagination Result List
        //Default return 10
        $paginateddata = $this->paginateResult($users, $request->result, $request->page);
        $data['data'] = $paginateddata;
        $data['maximunPage'] = $this->getMaximumPaginationPage($users->count(), $request->result);
        $data['msg'] = $this->getRetrievedSuccessMsg('Users');
        return response()->json($data, 200);
    }

    /**
     * @SWG\Post(
     *   tags={"UserControllerService"},
     *   path="/store",
     *   summary="Creates a user.",
     *     consumes={"application/json"},
     *     produces={application/json"},
     *   @SWG\Response(
     *     response=200,
     *     description="User has been created successfully."
     *   ),
     *   @SWG\Response(
     *     response="default",
     *     description="Unable to create the user."
     *   )
     * )
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'email' => 'nullable|string|email|max:191|unique:users',
            'password' => 'required|string|min:6|confirmed',
        ]);
        DB::beginTransaction();
        $user = new User();
        $grouparr = [];
        $user->uid = Carbon::now()->timestamp . User::count();
        $user->name = $request->name;
        $user->email = $request->email;
        $user->icno = $request->icno;
        $user->tel1 = $request->tel1;
        $user->tel2 = $request->tel2;
        $user->address1 = $request->address1;
        $user->address2 = $request->address2;
        $user->postcode = $request->postcode;
        $user->city = $request->city;
        $user->state = $request->state;
        $user->country = $request->country;
        $user->password = Hash::make($request->password);
        $user->status = true;
        try {
            $user->save();
        } catch (Exception $e) {
            DB::rollBack();
            $data['status'] = 'error';
            $data['msg'] = 'User cannot save.';
            return response()->json($data, 500);
        }
        DB::commit();
        $data['status'] = 'success';
        $data['msg'] = 'User Saved.';
        $data['data'] =  $user->refresh();
        return response()->json($data, 200);
    }

    public function show($uid)
    {
        $user = User::with('role', 'groups.company')->where('uid', $uid)->where('status', 1)->first();
        if (empty($user)) {
            $payload['status'] = 'error';
            $payload['msg'] = 'User Cannot Found.';
            return response()->json($payload, 404);
        } else {
            $payload['status'] = 'success';
            $payload['msg'] = 'User Found.';
            $payload['data'] = $user;

            return response()->json($payload, 200);
        }
    }

    public function update(Request $request, $uid)
    {

        $user = User::where('uid', $uid)->where('status', 1)->first();
        if (empty($user)) {
            $payload['status'] = 'error';
            $payload['msg'] = 'User not found.';
            return response()->json($payload, 404);
        }
        $this->validate($request, [
            'email' => 'required|string|max:191|unique:users,email,' . $user->id,
            'name' => 'required|string|max:191',
        ]);
        DB::beginTransaction();
        // $role = Role::where('name', '=', $request->role_name)->first();
        // if(empty($role)){
        //     $payload['status'] = 'error';
        //     $payload['msg'] = 'Role Not Found.';
        //     return response()->json($payload, 404);
        // }
        // $user->role()->associate($role);
        // $company = Company::with('groups')->where('id', $request->company)->first();
        // $group = $company->groups->first();
        // $company = Company::where('name', '=', $request->company)->first();
        // if(empty($group)){
        //     $payload['status'] = 'error';
        //     $payload['msg'] = 'Company\'s group not found.';
        //     return response()->json($payload, 404);
        // }
        $user->name = $request->name;
        $user->email = $request->email;
        $user->icno = $request->icno;
        $user->tel1 = $request->tel1;
        $user->address1 = $request->address1;
        $user->postcode = $request->postcode;
        $user->city = $request->city;
        $user->state = $request->state;
        $user->country = $request->country;
        $user->status = true;
        $user->lastedit_by = $request->user()->name;

        try {
            $user->save();
        } catch (Exception $e) {
            DB::rollBack();
            $payload['status'] = 'error';
            $payload['msg'] = 'User Cannot Be Updated.';
            return response()->json($payload, 404);
        }
        // $user->groups()->attach($group);
        DB::commit();
        $payload['status'] = 'success';
        $payload['msg'] = 'User Updated.';
        $payload['data'] =  $user->refresh();
        return response()->json($payload, 200);
    }

    public function destroy(Request $request, $uid)
    {
        $user = User::where('uid', $uid)->where('status', true)->first();
        if (empty($user)) {
            $payload['status'] = 'error';
            $payload['msg'] = 'User not found.';
            return response()->json($payload, 404);
        }
        $user->status = false;
        DB::beginTransaction();
        try {
            DB::commit();
            $user->save();
            $payload['status'] = 'success';
            $payload['msg'] = 'User Deleted.';
            $payload['user'] =  $user->refresh();
            return response()->json($payload, 200);
        } catch (Exception $e) {
            DB::rollBack();
            $payload['status'] = 'error';
            $payload['msg'] = 'User Cannot Be Deleted.';
            return response()->json($payload, 404);
        }
    }

    /**
     * @SWG\Post(
     *   tags={"UserControllerService"},
     *   path="/authentication",
     *   summary="Authenticate user",
     *   @SWG\Response(
     *     response=200,
     *     description="Successfully authenticated user."
     *   ),
     *   @SWG\Response(
     *     response="default",
     *     description="Unable to authenticate the user."
     *   )
     * )
     */
    public function authentication(Request $request)
    {

        return response()->json($request->user(), 200);
    }
}
