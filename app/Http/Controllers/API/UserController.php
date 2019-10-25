<?php


namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use DB;
use Carbon\Carbon;
use App\User;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index(Request $request){
        
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
        $user->uid = Carbon::now()->timestamp .User::count();
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

        try{
            $user->save();
        }catch(Exception $e){
            DB::rollBack();
            $payload['status'] = 'error';
            $payload['msg'] = 'User cannot save.';

            return response()->json($payload, 500);
        }
       
        DB::commit();
        $payload['status'] = 'success';
        $payload['msg'] = 'User Saved.';
        $payload['user'] =  $user;

        return response()->json($payload, 200);
    }

    public function show(Request $request){
        
    }

    public function update(Request $request){
        
    }

    public function destroy(Request $request){
        
    }

    public function authentication(Request $request){
        
        return response()->json($request->user(), 200);
    }
     
}
