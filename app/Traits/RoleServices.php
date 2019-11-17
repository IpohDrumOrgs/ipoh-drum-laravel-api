<?php

namespace App\Traits;
use App\User;
use App\Role;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;
use App\Traits\GlobalFunctions;
use App\Traits\LogServices;
use App\Traits\CompanyServices;

trait RoleServices {

    use GlobalFunctions, LogServices , CompanyServices;

    private function getRoleListing($requester) {


        $data = collect();

        //Role Based Retrieve Done in Company Services
        $companies = $this->getCompanyListing($requester);
        foreach($companies as $company){
            $data = $data->merge($company->roles()->where('status',true)->get());
        }

        $data = $data->unique('id')->sortBy('id')->flatten(1);

        return $data;

    }


    private function pluckRoleIndex($cols) {

        $data = Role::where('status',true)->get($cols);
        return $data;

    }


    private function filterRoleListing($requester , $params) {

        error_log('Filtering roles....');
        $data = $this->getRoleListing($requester);

        if($params->keyword){
            error_log('Filtering roles with keyword....');
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
            error_log('Filtering roles with fromdate....');
            $date = Carbon::parse($params->fromdate)->startOfDay();
            $data = $data->filter(function ($item) use ($date) {
                return (Carbon::parse(data_get($item, 'created_at')) >= $date);
            });
        }

        if($params->todate){
            error_log('Filtering roles with todate....');
            $date = Carbon::parse($request->todate)->endOfDay();
            $data = $data->filter(function ($item) use ($date) {
                return (Carbon::parse(data_get($item, 'created_at')) <= $date);
            });

        }

        if($params->status){
            error_log('Filtering roles with status....');
            if($params->status == 'true'){
                $data = $data->where('status', true);
            }else if($params->status == 'false'){
                $data = $data->where('status', false);
            }else{
                $data = $data->where('status', '!=', null);
            }
        }

        if($params->onsale){
            error_log('Filtering roles with on sale status....');
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


    private function pluckRoleFilter($cols , $params) {

        //Unauthorized users cannot access deleted data
        $data = Role::where('status',true)->get();

        if($params->keyword){
            error_log('Filtering roles with keyword....');
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
            error_log('Filtering roles with fromdate....');
            $date = Carbon::parse($params->fromdate)->startOfDay();
            $data = $data->filter(function ($item) use ($date) {
                return (Carbon::parse(data_get($item, 'created_at')) >= $date);
            });
        }

        if($params->todate){
            error_log('Filtering roles with todate....');
            $date = Carbon::parse($request->todate)->endOfDay();
            $data = $data->filter(function ($item) use ($date) {
                return (Carbon::parse(data_get($item, 'created_at')) <= $date);
            });

        }

        if($params->onsale){
            error_log('Filtering roles with on sale status....');
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


    private function getRole($requester , $uid) {
        $data = Role::where('uid', $uid)->where('status', 1)->first();
        return $data;
    }

    private function pluckRole($cols , $uid) {
        $data = Role::where('uid', $uid)->where('status', 1)->get($cols)->first();
        return $data;
    }

    private function createRole($requester , $params) {

        $data = new Role();
        $data->uid = Carbon::now()->timestamp . Role::count();
        $data->name = $params->name;
        $data->desc = $params->desc;
        $data->status = true;

        try {
            $data->save();
            $this->createLog($requester->id , [$data->id], 'store', 'role');
        } catch (Exception $e) {
            return null;
        }

        return $data->refresh();
    }

    //Make Sure Role is not empty when calling this function
    private function updateRole($requester, $data,  $params) {

        $data->name = $params->name;
        $data->desc = $params->desc;

        try {
            $data->save();
            $this->createLog($requester->id , [$data->id], 'update', 'role');
        } catch (Exception $e) {
            return null;
        }

        return $data->refresh();
    }

    private function deleteRole($requester , $id) {
        $data = Role::find($id);
        $data->status = false;
        try {
            $data->save();
            $this->createLog($requester->id , [$data->id], 'delete', 'role');
        } catch (Exception $e) {
            return null;
        }

        return $data->refresh();
    }


}
