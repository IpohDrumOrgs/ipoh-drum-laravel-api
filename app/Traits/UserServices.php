<?php

namespace App\Traits;
use App\User;
use App\Role;
use App\Inventory;
use App\InventoryBatch;
use App\Module;
use App\PurchaseItem;
use App\SaleItem;
use App\Sale;
use App\Article;
use App\Category;
use App\CompanyType;
use App\Group;
use App\Log;
use App\Payment;
use App\Video;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;
use DB;

trait UserServices {


    private function getUserListing() {
        $users = User::where('status', true)->get();
        return $users;
    
    }

    private function getUser() {
        
       
    }

    private function createUser($request) {
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
            return null;
        }

        DB::commit();
        return $user->refresh();
    }

    private function updateUser() {
        
       
    }

    private function destroyUser() {
        
    }
}