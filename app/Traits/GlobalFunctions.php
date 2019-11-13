<?php

namespace App\Traits;
use App\User;
use App\Role;
use App\Inventory;
use App\InventoryBatch;
use App\Module;
use App\Batch;
use App\PurchaseItem;
use App\SaleItem;
use App\Sale;
use Carbon\Carbon;
use DB;

trait GlobalFunctions {


    public function checkAccessibility($user, $company ,  $clearance) {
        
        $usermodule = $user->role->modules()->wherePivot('module_id',$module->id)->wherePivot('role_id',$user->role->id)->first();
        if(empty($usermodule)){
            return false;
        }else{ 
            
            //Get User company for wide checking
            $groups = $user->groups;
            $companies = collect();
            foreach($groups as $group){
                $companies = $companies->push($group->company);
                foreach($group->company->branches as $branch){
                    $companies = $companies->push($branch);
                }
            }
            
            $ownwide = false;
            $companywide = false;
            //Performance that affected the wide

            $owner = $item->company;
            foreach($usercompanies as $usercompany){
                if($usercompany->contains('id',$owner->id)){
                    $owner = true;
                    $companywide = true;
                    break;
                }
            }

            //Check The minimum authority of this operation
            $maxclearance = 1;
            //Own wide
            if($ownwide){
                $maxclearance = 3;
            }else if($companywide){
                //Company wide
                $maxclearance = 2;
            }else {
                //System wide
                $maxclearance = 1;
            }

            //Check the request user got the authority to do this operation or not
            if($usermodule->pivot->clearance <= $maxclearance){
                return true;
            }else{
                return false;
            }

            
        }


        
    }
    
    public function checkClearance($user, $company, $module) {
        if($user == null || $module == null){
            return null;
        }
        if($company == null){
            $roles = $user->roles()->where('name' , 'superadmin')->get();
            if(empty($roles)){
                return null;
            }else{
                // Is super admin return highest authority level
                return 1;
            }
        }else{
            $role = $user->roles()->wherePivot('company_id', $company->id)->first();
            $module = $role->modules()->wherePivot('module_id',$module->id)->first();
            if(empty($module)){
                return null;
            }else{ 
                return $module->pivot->clearance;
            }
       
        }
    }
    
    public function getModule($provider,$name) {

        $module = Module::where('provider',$provider)->where('name',$name)->first();
        if(empty($module)){
            return null;
        }else{
            return $module;
        }
    }
    
    //Page Pagination
    public function paginateResult($data , $result , $page){
        
        if($result == null || $result == "" || $result == 0){
            $result = 10;
        }
        if($page == null || $page == "" || $page == 0){
            $page = 1;
        }
        $data = $data->slice(($page-1) * $result)->take($result);

        return $data;
    }

    //Get Maximun Pages
    public function getMaximumPaginationPage($dataNo , $result){
        
        if($result == null  || $result == "" || $result == 0){
            $result = 10;
        }
        
        $maximunPage = ceil($dataNo / $result);

        return $maximunPage;
    }

    //Get Maximun Pages
    public function isEmpty($collection){
        
        $collection = collect($collection);
        if($collection == null  || empty($collection) || $collection->count() == 0){
            return true;
        }else{
            return false;
        }
    }

    
    //Split the string to array
    public function splitToArray($params){
        if($this->isEmpty($data)){
            return null;
        }else{
            $params = collect(explode(',' , $params));
            $params = $params->map(function ($item, $key) {
                return trim($item);
            });
            return $params->toArray();
        }
    }

    
    //convert string to double
    public function toDouble($data){
        if($this->isEmpty($data)){
            return null;
        }else{
            return number_format((float)($data), 2,'.','');
        }
    }

    //convert string to double
    public function toInt($data){
        if($this->isEmpty($data)){
            return null;
        }else{
            return (int)$data;
        }
    }
    
    //convert string to double
    public function toDate($data){
        if($this->isEmpty($data)){
            return null;
        }else{
            return Carbon::parse($data);
        }
    }
}