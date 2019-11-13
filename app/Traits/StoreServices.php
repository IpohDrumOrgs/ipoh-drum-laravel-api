<?php

namespace App\Traits;
use App\Store;
use App\Company;
use App\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;
use App\Traits\GlobalFunctions;
use App\Traits\LogServices;
use DB;

trait StoreServices {

    use GlobalFunctions, LogServices;

    private function getStoreListing($requester) {

        $data = collect();
        $companies = $requester->companies;
        foreach($companies as $company){
            $clearance = $this->checkClearance($requester, $company ,  $this->getModule('store','index'));
            error_log($clearance);
            switch ($clearance) {
                //System Wide
                case 1:
                    $temp = Store::where('status', true)->get();
                    $data = $data->merge($temp);
                    break;
                //Store Wide
                case 2:
                //Group Wide
                case 3:
                    $stores = $company->stores;
                    foreach($stores as $store){
                        $data = $data->merge($store->inventories);
                    }
                    $stores = $requester->stores;
                    foreach($stores as $store){
                        $data = $data->merge($store->inventories);
                    }
                    break;
                //Own Wide
                case 4:
                    $stores = $requester->stores;
                    foreach($stores as $store){
                        $data = $data->merge($store->inventories);
                    }
                    break;
                default:
                    break;
            }
    
        }
        
        $data = $data->unique('id');

        return $data;
    
    }

    
    private function pluckStoreIndex($cols) {

        $data = Store::where('status',true)->get($cols);
        return $data;
    
    }


    private function filterStoreListing($requester , $params) {

        error_log('Filtering stores....');
        $data = $this->getStoreListing($requester);

        if($params->keyword){
            error_log('Filtering stores with keyword....');
            $keyword = $params->keyword;
            $data = $data->filter(function($item)use($keyword){
                //check string exist inside or not
                if(stristr($item->name, $keyword) == TRUE || stristr($item->regno, $keyword) == TRUE || stristr($item->uid, $keyword) == TRUE ) {
                    return true;
                }else{
                    return false;
                }
            
            });
        }

             
        if($params->fromdate){
            error_log('Filtering stores with fromdate....');
            $date = Carbon::parse($params->fromdate)->startOfDay();
            $data = $data->filter(function ($item) use ($date) {
                return (Carbon::parse(data_get($item, 'created_at')) >= $date);
            });
        }

        if($params->todate){
            error_log('Filtering stores with todate....');
            $date = Carbon::parse($request->todate)->endOfDay();
            $data = $data->filter(function ($item) use ($date) {
                return (Carbon::parse(data_get($item, 'created_at')) <= $date);
            });
            
        } 

        if($params->status){
            error_log('Filtering stores with status....');
            if($params->status == 'true'){
                $data = $data->where('status', true);
            }else if($params->status == 'false'){
                $data = $data->where('status', false);
            }else{
                $data = $data->where('status', '!=', null);
            }
        }
        
       
        $data = $data->unique('id');

        return $data;
    }

    
    private function pluckStoreFilter($cols , $params) {

        //Unauthorized users cannot access deleted data
        $data = Store::where('status',true)->get();

        if($params->keyword){
            error_log('Filtering stores with keyword....');
            $keyword = $params->keyword;
            $data = $data->filter(function($item)use($keyword){
                //check string exist inside or not
                if(stristr($item->name, $keyword) == TRUE || stristr($item->regno, $keyword) == TRUE || stristr($item->uid, $keyword) == TRUE ) {
                    return true;
                }else{
                    return false;
                }
            
            });
        }

             
        if($params->fromdate){
            error_log('Filtering stores with fromdate....');
            $date = Carbon::parse($params->fromdate)->startOfDay();
            $data = $data->filter(function ($item) use ($date) {
                return (Carbon::parse(data_get($item, 'created_at')) >= $date);
            });
        }

        if($params->todate){
            error_log('Filtering stores with todate....');
            $date = Carbon::parse($request->todate)->endOfDay();
            $data = $data->filter(function ($item) use ($date) {
                return (Carbon::parse(data_get($item, 'created_at')) <= $date);
            });
            
        } 

       

        $data = $data->unique('id');

        //Pluck Columns
        $data = $data->map(function($item)use($cols){
            return $item->only($cols);
        });
        
        return $data;
    
    }


    private function getStore($requester , $uid) {
        $data = Store::where('uid', $uid)->where('status', 1)->first();
        return $data;
    }

    private function pluckStore($cols , $uid) {
        $data = Store::where('uid', $uid)->where('status', 1)->get($cols)->first();
        return $data;
    }

    private function createStore($requester , $params) {

        DB::beginTransaction();
        $data = new Store();
        $data->uid = Carbon::now()->timestamp . Store::count();
        $data->name = $params->name;
        $data->contact = $params->contact;
        $data->email = $params->email;
        $data->address = $params->address;
        $data->postcode = $params->postcode;
        $data->rating = 0;
        $data->city = $params->city;
        $data->state = $params->state;
        $data->country = $params->country;
        $data->companyBelongings = $params->companyBelongings;

        //Assign Owner
        if($data->companyBelongings){
            $company = Company::find($params->companyid);
            if($this->isEmpty($company)){
                return null;
            }
            $data->company()->associate($company);
            $data->user_id = null;
        }else{
            $user = User::find($params->userid);
            if($this->isEmpty($user)){
                return null;
            }
            $data->user()->associate($user);
            $data->company_id = null;
        }

        $data->status = true;

        try {
            $data->save();
            $this->createLog($requester->id , [$data->id], 'store', 'store');
        } catch (Exception $e) {
            DB::rollBack();
            return null;
        }

        DB::commit();
        return $data->refresh();
    }

    //Make Sure Store is not empty when calling this function
    private function updateStore($requester, $data,  $params) {
        
        DB::beginTransaction();
        $data->name = $params->name;
        $data->contact = $params->contact;
        $data->email = $params->email;
        $data->address = $params->address;
        $data->postcode = $params->postcode;
        $data->city = $params->city;
        $data->state = $params->state;
        $data->country = $params->country;
        $data->companyBelongings = $params->companyBelongings;

        //Assign Owner
        if($data->companyBelongings){
            $company = Company::find($params->companyid);
            if($this->isEmpty($company)){
                return null;
            }
            $data->company()->associate($companyid);
            $data->user_id = null;
        }else{
            $user = User::find($params->userid);
            if($this->isEmpty($user)){
                return null;
            }
            $data->user()->associate($user);
            $data->company_id = null;
        }

        try {
            $data->save();
            $this->createLog($requester->id , [$data->id], 'update', 'store');
        } catch (Exception $e) {
            DB::rollBack();
            return null;
        }

        DB::commit();
        return $data->refresh();
    }

    private function deleteStore($requester , $id) {
        DB::beginTransaction();
        $data = Store::find($id);
        $data->status = false;
        try {
            $data->save();
            $this->createLog($requester->id , [$data->id], 'delete', 'store');
        } catch (Exception $e) {
            DB::rollBack();
            return null;
        }

        DB::commit();
        return $data->refresh();
    }

    
}