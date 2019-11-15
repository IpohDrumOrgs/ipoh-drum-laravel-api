<?php

namespace App\Traits;
use App\User;
use App\Role;
use App\Module;
use App\Inventory;
use App\Ticket;
use App\InventoryBatch;
use App\Batch;
use App\PurchaseItem;
use App\SaleItem;
use App\Sale;
use Carbon\Carbon;
use DB;

trait AssignDatabaseRelationship {

    private function assignInventoriesToMany($data , $params){
        foreach($params as $param){
            $model = Inventory::where('status', true)->where('id' , $param->id)->first();
            if($this->isEmpty($model)){
                return false;
            }
            $data->inventories()->syncWithoutDetaching([$param->id => ['remark' => $param->remark]]);
        }
        return true;
    }

    private function assignTicketsToMany($data , $params){
        foreach($params as $param){
            $model = Ticket::where('status', true)->where('id' , $param->id)->first();
            if($this->isEmpty($model)){
                return false;
            }
            $data->tickets()->syncWithoutDetaching([$param->id => ['remark' => $param->remark]]);
        }
        return true;
    }

    
    private function assignCompanyType($data , $param){
        $data->companytype()->associate($param->id);
        try {
            $data->save();
        } catch (Exception $e) {
            return false;
        }
        return true;
    }

    //Company assign roles 
    private function assignRolesToManyWithCompany($data , $params){
        foreach($params as $param){
            $model = Role::where('status', true)->where('id' , $param->id)->first();
            if($this->isEmpty($model)){
                return false;
            }
            $data->roles()->syncWithoutDetaching([$param->id => ['user_id' => $param->userid , 'assigned_by' => $param->assigned_by, 'assigned_at' => Carbon::now() ]]);
        }
        return true;
    }

    
    //Company assign users 
    private function assignUsersToManyWithCompany($data , $params){
        foreach($params as $param){
            $model = User::where('status', true)->where('id' , $param->id)->first();
            if($this->isEmpty($model)){
                return false;
            }
            $data->roles()->syncWithoutDetaching([$param->id => ['role_id' => $param->roleid , 'assigned_by' => $param->assigned_by, 'assigned_at' => Carbon::now() ]]);
        }
        return true;
    }
    //Company assign roles 
    // private function detachRolesToManyWithUser($data , $params){
    //     foreach($params as $param){

    //         //Model Validation
    //         $model = Role::where('status', true)->where('id' , $param->id)->first();
    //         if($this->isEmpty($model)){
    //             return false;
    //         }
    //         $data->roles()->wherePivot('company_id' , $param->companyid )->detach($param->id);
    //     }
    //     return true;
    // }
}