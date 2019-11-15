<?php

namespace App\Traits;
use App\User;
use App\Group;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;
use App\Traits\GlobalFunctions;
use App\Traits\LogServices;

trait GroupServices {

    use GlobalFunctions, LogServices;

    private function getGroupListing($requester) {

        $data = collect();
        $companies = $requester->companies;
        foreach($companies as $company){
            $clearance = $this->checkClearance($requester, $company ,  $this->checkModule('group','index'));
            error_log($clearance);
            switch ($clearance) {
                //System Wide
                case 1:
                    $temp = Group::where('status', true)->get();
                    $data = $data->merge($temp);
                    break;
                //Company Wide
                case 2:
                //Group Wide
                case 3:
                    $data = $data->merge($company->groups()->where('status',true)->get());
                    break;
                //Own Wide
                case 4:
                    $data = $data->merge($requester->groups()->where('status',true)->get());
                    break;
                default:
                    break;
            }
    
        }
        
        $data = $data->unique('id');

        return $data;
    
    }

    
    private function pluckGroupIndex($cols) {

        $data = Group::where('status',true)->get($cols);
        return $data;
    
    }


    private function filterGroupListing($requester , $params) {

        error_log('Filtering groups....');
        $data = $this->getGroupListing($requester);

        if($params->keyword){
            error_log('Filtering groups with keyword....');
            $keyword = $params->keyword;
            $data = $data->filter(function($item)use($keyword){
                //check string exist inside or not
                if(stristr($item->name, $keyword) == TRUE || stristr($item->uid, $keyword) == TRUE ) {
                    return true;
                }else{
                    return false;
                }
            
            });
        }

             
        if($params->fromdate){
            error_log('Filtering groups with fromdate....');
            $date = Carbon::parse($params->fromdate)->startOfDay();
            $data = $data->filter(function ($item) use ($date) {
                return (Carbon::parse(data_get($item, 'created_at')) >= $date);
            });
        }

        if($params->todate){
            error_log('Filtering groups with todate....');
            $date = Carbon::parse($request->todate)->endOfDay();
            $data = $data->filter(function ($item) use ($date) {
                return (Carbon::parse(data_get($item, 'created_at')) <= $date);
            });
            
        } 

        if($params->status){
            error_log('Filtering groups with status....');
            if($params->status == 'true'){
                $data = $data->where('status', true);
            }else if($params->status == 'false'){
                $data = $data->where('status', false);
            }else{
                $data = $data->where('status', '!=', null);
            }
        }
        
        if($params->onsale){
            error_log('Filtering groups with on sale status....');
            if($params->onsale == 'true'){
                $data = $data->where('onsale', true);
            }else if($params->onsale == 'false'){
                $data = $data->where('onsale', false);
            }else{
                $data = $data->where('onsale', '!=', null);
            }
        }

       
        $data = $data->unique('id');

        return $data;
    }

    
    private function pluckGroupFilter($cols , $params) {

        //Unauthorized users cannot access deleted data
        $data = Group::where('status',true)->get();

        if($params->keyword){
            error_log('Filtering groups with keyword....');
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
            error_log('Filtering groups with fromdate....');
            $date = Carbon::parse($params->fromdate)->startOfDay();
            $data = $data->filter(function ($item) use ($date) {
                return (Carbon::parse(data_get($item, 'created_at')) >= $date);
            });
        }

        if($params->todate){
            error_log('Filtering groups with todate....');
            $date = Carbon::parse($request->todate)->endOfDay();
            $data = $data->filter(function ($item) use ($date) {
                return (Carbon::parse(data_get($item, 'created_at')) <= $date);
            });
            
        } 
        
        if($params->onsale){
            error_log('Filtering groups with on sale status....');
            if($params->onsale == 'true'){
                $data = $data->where('onsale', true);
            }else if($params->onsale == 'false'){
                $data = $data->where('onsale', false);
            }else{
                $data = $data->where('onsale', '!=', null);
            }
        }

        $data = $data->unique('id');

        //Pluck Columns
        $data = $data->map(function($item)use($cols){
            return $item->only($cols);
        });
        
        return $data;
    
    }


    private function getGroup($requester , $uid) {
        $data = Group::where('uid', $uid)->where('status', 1)->first();
        return $data;
    }

    private function pluckGroup($cols , $uid) {
        $data = Group::where('uid', $uid)->where('status', 1)->get($cols)->first();
        return $data;
    }

    private function createGroup($requester , $params) {

        $data = new Group();
        $data->uid = Carbon::now()->timestamp . Group::count();
        $data->name = $params->name;
        $data->desc = $params->desc;

        $company = Store::find($params->companyid);
        if($this->isEmpty($company)){
            return null;
        }
        $data->company()->associate($company);

        $data->status = true;

        try {
            $data->save();
            $this->createLog($requester->id , [$data->id], 'store', 'group');
        } catch (Exception $e) {
            return null;
        }

        return $data->refresh();
    }

    //Make Sure Group is not empty when calling this function
    private function updateGroup($requester, $data,  $params) {
        
        $data->name = $params->name;
        $data->desc = $params->desc;

        $company = Store::find($params->companyid);
        if($this->isEmpty($company)){
            return null;
        }
        $data->company()->associate($company);

        try {
            $data->save();
            $this->createLog($requester->id , [$data->id], 'update', 'group');
        } catch (Exception $e) {
            return null;
        }

        return $data->refresh();
    }

    private function deleteGroup($requester , $id) {
        $data = Group::find($id);
        $data->status = false;
        try {
            $data->save();
            $this->createLog($requester->id , [$data->id], 'delete', 'group');
        } catch (Exception $e) {
            return null;
        }

        return $data->refresh();
    }

    
}