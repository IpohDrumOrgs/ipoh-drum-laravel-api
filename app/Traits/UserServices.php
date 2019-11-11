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
use App\Traits\LogServices;
use DB;

trait UserServices {

    use GlobalFunctions, LogServices;

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

    
    private function pluckUserIndex($cols) {

        $users = User::get($cols);
        return $users;
    
    }


    private function filterUserListing($requester , $params) {

        error_log('Filtering users....');
        $users = $this->getUserListing($requester);

        if($params->keyword){
            error_log('Filtering users with keyword....');
            $keyword = $params->keyword;
            $users = $users->filter(function($item)use($keyword){
                //check string exist inside or not
                if(stristr($item->name, $keyword) == TRUE || stristr($item->email, $keyword) == TRUE || stristr($item->icno, $keyword) == TRUE ) {
                    return true;
                }else{
                    return false;
                }
            
            });
        }

             
        if($params->fromdate){
            error_log('Filtering users with fromdate....');
            $date = Carbon::parse($params->fromdate)->startOfDay();
            $users = $users->filter(function ($item) use ($date) {
                return (Carbon::parse(data_get($item, 'created_at')) >= $date);
            });
        }

        if($params->todate){
            error_log('Filtering users with todate....');
            $date = Carbon::parse($request->todate)->endOfDay();
            $users = $users->filter(function ($item) use ($date) {
                return (Carbon::parse(data_get($item, 'created_at')) <= $date);
            });
            
        } 

        if($params->status){
            error_log('Filtering users with status....');
            if($params->status == 'true'){
                $users = $users->where('status', true);
            }else if($params->status == 'false'){
                $users = $users->where('status', false);
            }else{
                $users = $users->where('status', '!=', null);
            }
        }
        
        if($params->company_id){
            error_log('Filtering users with company id....');
            $company_id = $params->company_id;
            $users = $users->filter(function ($item) use($company_id) {
                return $item->companies->contains('id' , $company_id);
            });
        }

       
        $users = $users->unique('id');

        return $users;
    }

    
    private function pluckUserFilter($cols , $params) {

        $users = User::all();

        if($params->keyword){
            error_log('Filtering users with keyword....');
            $keyword = $params->keyword;
            $users = $users->filter(function($item)use($keyword){
                //check string exist inside or not
                if(stristr($item->name, $keyword) == TRUE || stristr($item->email, $keyword) == TRUE || stristr($item->icno, $keyword) == TRUE ) {
                    return true;
                }else{
                    return false;
                }
            
            });
        }

             
        if($params->fromdate){
            error_log('Filtering users with fromdate....');
            $date = Carbon::parse($params->fromdate)->startOfDay();
            $users = $users->filter(function ($item) use ($date) {
                return (Carbon::parse(data_get($item, 'created_at')) >= $date);
            });
        }

        if($params->todate){
            error_log('Filtering users with todate....');
            $date = Carbon::parse($request->todate)->endOfDay();
            $users = $users->filter(function ($item) use ($date) {
                return (Carbon::parse(data_get($item, 'created_at')) <= $date);
            });
            
        } 

        if($params->status){
            error_log('Filtering users with status....');
            if($params->status == 'true'){
                $users = $users->where('status', true);
            }else if($params->status == 'false'){
                $users = $users->where('status', false);
            }else{
                $users = $users->where('status', '!=', null);
            }
        }
        
        if($params->company_id){
            error_log('Filtering users with company id....');
            $company_id = $params->company_id;
            $users = $users->filter(function ($item) use($company_id) {
                return $item->companies->contains('id' , $company_id);
            });
        }

        $users = $users->unique('id');

        //Pluck Columns
        $users = $users->map(function($item)use($cols){
            return $item->only($cols);
        });
        
        return $users;
    
    }


    private function getUser($requester , $uid) {
        $user = User::with('roles', 'groups.company')->where('uid', $uid)->where('status', 1)->first();
        return $user;
    }

    private function pluckUser($cols , $uid) {
        $user = User::where('uid', $uid)->where('status', 1)->get($cols)->first();
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
            $this->createLog($requester->id , [$user->id], 'store', 'user');
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
            $this->createLog($requester->id , [$user->id], 'update', 'user');
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
            $this->createLog($requester->id , [$user->id], 'delete', 'user');
        } catch (Exception $e) {
            DB::rollBack();
            return null;
        }

        DB::commit();
        return $user->refresh();
    }

    
}