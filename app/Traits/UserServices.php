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

        $data = collect();
        $companies = $requester->companies;
        foreach($companies as $company){
            $clearance = $this->checkClearance($requester, $company ,  $this->getModule('user','index'));
            error_log($clearance);
            switch ($clearance) {
                //System Wide
                case 1:
                    $temp = User::where('status', true)->get();
                    $data = $data->merge($temp);
                    break;
                //Company Wide
                case 2:
                    $temp = $company->users()->get();
                    $data = $data->merge($temp);
                    break;
                //Group Wide
                case 3:
                    $groups = $requester->groups;
                    foreach($groups as $group){
                        $data = $data->merge($group->users);
                    }
                    break;
                //Own Wide
                case 4:
                    return $data = $data->push($requester);
                    break;
                default:
                    break;
            }
    
        }
        
        $data = $data->unique('id');

        return $data;
    
    }

    
    private function pluckUserIndex($cols) {

        $data = User::where('status',true)->get($cols);
        return $data;
    
    }


    private function filterUserListing($requester , $params) {

        error_log('Filtering users....');
        $data = $this->getUserListing($requester);

        if($params->keyword){
            error_log('Filtering users with keyword....');
            $keyword = $params->keyword;
            $data = $data->filter(function($item)use($keyword){
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
            $data = $data->filter(function ($item) use ($date) {
                return (Carbon::parse(data_get($item, 'created_at')) >= $date);
            });
        }

        if($params->todate){
            error_log('Filtering users with todate....');
            $date = Carbon::parse($request->todate)->endOfDay();
            $data = $data->filter(function ($item) use ($date) {
                return (Carbon::parse(data_get($item, 'created_at')) <= $date);
            });
            
        } 

        if($params->status){
            error_log('Filtering users with status....');
            if($params->status == 'true'){
                $data = $data->where('status', true);
            }else if($params->status == 'false'){
                $data = $data->where('status', false);
            }else{
                $data = $data->where('status', '!=', null);
            }
        }
        
        if($params->company_id){
            error_log('Filtering users with company id....');
            $company_id = $params->company_id;
            $data = $data->filter(function ($item) use($company_id) {
                return $item->companies->contains('id' , $company_id);
            });
        }

       
        $data = $data->unique('id');

        return $data;
    }

    
    private function pluckUserFilter($cols , $params) {

        //Unauthorized users cannot access deleted data
        $data = User::where('status',true)->get();

        if($params->keyword){
            error_log('Filtering users with keyword....');
            $keyword = $params->keyword;
            $data = $data->filter(function($item)use($keyword){
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
            $data = $data->filter(function ($item) use ($date) {
                return (Carbon::parse(data_get($item, 'created_at')) >= $date);
            });
        }

        if($params->todate){
            error_log('Filtering users with todate....');
            $date = Carbon::parse($request->todate)->endOfDay();
            $data = $data->filter(function ($item) use ($date) {
                return (Carbon::parse(data_get($item, 'created_at')) <= $date);
            });
            
        } 
        
        if($params->company_id){
            error_log('Filtering users with company id....');
            $company_id = $params->company_id;
            $data = $data->filter(function ($item) use($company_id) {
                return $item->companies->contains('id' , $company_id);
            });
        }

        $data = $data->unique('id');

        //Pluck Columns
        $data = $data->map(function($item)use($cols){
            return $item->only($cols);
        });
        
        return $data;
    
    }


    private function getUser($requester , $uid) {
        $data = User::with('roles', 'groups.company')->where('uid', $uid)->where('status', 1)->first();
        return $data;
    }

    private function pluckUser($cols , $uid) {
        $data = User::where('uid', $uid)->where('status', 1)->get($cols)->first();
        return $data;
    }

    private function createUser($requester , $params) {


            DB::beginTransaction();
            $data = new User();
            $data->uid = Carbon::now()->timestamp . User::count();
            $data->name = $params->name;
            $data->email = $params->email;
            $data->icno = $params->icno;
            $data->tel1 = $params->tel1;
            $data->tel2 = $params->tel2;
            $data->address1 = $params->address1;
            $data->address2 = $params->address2;
            $data->postcode = $params->postcode;
            $data->city = $params->city;
            $data->state = $params->state;
            $data->country = $params->country;
            $data->password = Hash::make($params->password);
            $data->status = true;
            try {
                $data->save();
                $this->createLog($requester->id , [$data->id], 'store', 'user');
            } catch (Exception $e) {
                DB::rollBack();
                return null;
            }

            DB::commit();
            return $data->refresh();
        
        
    }

    //Make Sure User is not empty when calling this function
    private function updateUser($requester, $data,  $params) {
        
        DB::beginTransaction();
        $grouparr = [];
        $data->name = $params->name;
        $data->email = $params->email;
        $data->icno = $params->icno;
        $data->tel1 = $params->tel1;
        $data->tel2 = $params->tel2;
        $data->address1 = $params->address1;
        $data->address2 = $params->address2;
        $data->postcode = $params->postcode;
        $data->city = $params->city;
        $data->state = $params->state;
        $data->country = $params->country;
        try {
            $data->save();
            $this->createLog($requester->id , [$data->id], 'update', 'user');
        } catch (Exception $e) {
            DB::rollBack();
            return null;
        }

        DB::commit();
        return $data->refresh();
    }

    private function deleteUser($requester , $id) {
        DB::beginTransaction();
        $data = User::find($id);
        $data->status = false;
        try {
            $data->save();
            $this->createLog($requester->id , [$data->id], 'delete', 'user');
        } catch (Exception $e) {
            DB::rollBack();
            return null;
        }

        DB::commit();
        return $data->refresh();
    }

    
}