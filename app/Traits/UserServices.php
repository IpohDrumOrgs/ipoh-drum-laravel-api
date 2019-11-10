<?php

namespace App\Traits;
use App\User;
use App\Company;
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
use App\Traits\GlobalFunctions;
use DB;

trait UserServices {

    use GlobalFunctions;

    private function getUserListing($requester) {

        $users = collect();
        $companies = $requester->companies;
        foreach($companies as $company){
            $clearance = $this->checkClearance($requester, $company ,  $this->getModule('user','index'));
            error_log($clearance);
            switch ($clearance) {
                //System Wide
                case 1:
                    $compusers = User::where('status', true)->get();
                    $users = $users->merge($compusers);
                    break;
                //Company Wide
                case 2:
                    $compusers = $company->users()->get();
                    $users = $users->merge($compusers);
                    break;
                //Group Wide
                case 3:
                    $groups = $user->groups;
                    foreach($groups as $group){
                        $users = $users->merge($group->users);
                    }
                    break;
                //Own Wide
                case 4:
                    return $users = $users->push($requester);
                    break;
                default:
                    break;
            }
    
        }
        
        $users = $users->unique('id');

        return $users;
    
    }

    private function filterUserListing($requester , $condition) {

        $users = $this->getUserListing($requester);

        if($condition->keyword){
            $users = $users->filter(function($user)use($keyword){
                //check string exist inside or not
                if(stristr($user->uname, $keyword) == TRUE || stristr($user->email, $keyword) == TRUE || stristr($user->fname, $keyword) == TRUE || stristr($user->lname, $keyword) == TRUE) {
                    return true;
                }else{
                    return false;
                }
            
            });
        }

             
        if($condition->fromdate){
            $date = Carbon::parse($condition->fromdate)->startOfDay();
            $users = $users->filter(function ($item) use ($date) {
                return (Carbon::parse(data_get($item, 'created_at')) >= $date);
            });
        }

        if($condition->todate){
            $date = Carbon::parse($request->todate)->endOfDay();
            $users = $users->filter(function ($item) use ($date) {
                return (Carbon::parse(data_get($item, 'created_at')) <= $date);
            });
            
        } 

        if($condition->status){
            if($condition->status == 'true'){
                $users->where('status', true);
            }else if($condition->status == 'false'){
                $users->where('status', false);
            }else{
                $users->where('status', '!=', null);
            }
        }

        
        if($condition->company_id){
            $company_id = $condition->company_id;
            $users = $users->filter(function ($item) use($company_id) {
                return $item->companies->contains('id' , $company_id);
            });
        }

       
        $users = $users->unique('id');

        return $users;
    }


    private function getUser($requester , $uid) {
        $user = User::with('roles', 'groups.company')->where('uid', $uid)->where('status', 1)->first();
        return $user;
    }

    private function createUser($requester , $data) {

        DB::beginTransaction();
        $user = new User();
        $grouparr = [];
        $user->uid = Carbon::now()->timestamp . User::count();
        $user->name = $data->name;
        $user->email = $data->email;
        $user->icno = $data->icno;
        $user->tel1 = $data->tel1;
        $user->tel2 = $data->tel2;
        $user->address1 = $data->address1;
        $user->address2 = $data->address2;
        $user->postcode = $data->postcode;
        $user->city = $data->city;
        $user->state = $data->state;
        $user->country = $data->country;
        $user->password = Hash::make($data->password);
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

    //Make Sure User is not empty when calling this function
    private function updateUser($requester, $user,  $data) {
        
        DB::beginTransaction();
        $grouparr = [];
        $user->name = $data->name;
        $user->email = $data->email;
        $user->icno = $data->icno;
        $user->tel1 = $data->tel1;
        $user->tel2 = $data->tel2;
        $user->address1 = $data->address1;
        $user->address2 = $data->address2;
        $user->postcode = $data->postcode;
        $user->city = $data->city;
        $user->state = $data->state;
        $user->country = $data->country;
        try {
            $user->save();
        } catch (Exception $e) {
            DB::rollBack();
            return null;
        }

        DB::commit();
        return $user->refresh();
    }

    private function deleteUser($requester , $userid) {
        DB::beginTransaction();
        $user = User::find($userid);
        $user->status = false;
        try {
            $user->save();
        } catch (Exception $e) {
            DB::rollBack();
            return null;
        }

        DB::commit();
        return $user->refresh();
    }
}