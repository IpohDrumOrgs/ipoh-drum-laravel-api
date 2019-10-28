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


/**
 * @SWG\Definition()
 */
class UserController extends Controller
{

    use GlobalFunctions, NotificationFunctions;

    /**
     * @SWG\Get(
     *   path="api/user",
     *   summary="List of users",
     *   @SWG\Response(
     *     response=200,
     *     description="A list of users."
     *   ),
     *   @SWG\Response(
     *     response="default",
     *     description="an ""unexpected"" error"
     *   )
     * )
     */
    public function index(Request $request)
    {
        $users = User::all();
        //Page Pagination Result List
        //Default return 10
        $paginateddata = $this->paginateResult($users, $request->result, $request->page);
        $data['data'] = $paginateddata;
        $data['msg'] = $this->getRetrievedSuccessMsg('Users');
        return response()->json($data, 200);
    }

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
        $data['data'] =  $user;

        return response()->json($data, 200);
    }

    public function show(Request $request)
    { }

    public function update(Request $request)
    { }

    public function destroy(Request $request)
    { }

    public function authentication(Request $request)
    {

        return response()->json($request->user(), 200);
    }
}
