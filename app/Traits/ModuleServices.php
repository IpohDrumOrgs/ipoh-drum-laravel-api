<?php

namespace App\Traits;
use App\User;
use App\Module;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;
use App\Traits\GlobalFunctions;
use App\Traits\LogServices;
use App\Traits\RoleServices;

trait ModuleServices {

    use GlobalFunctions, LogServices, RoleServices;

    private function getModuleListing($requester) {

        $data = collect();
        //Role Based Retrieved Done in Role Services
        $roles = $this->getRoleListing($requester);
        foreach($roles as $role){
            $data = $data->merge($role->modules()->where('status',true)->get());
        }


        $data = $data->unique('id')->sortBy('id')->flatten(1);

        return $data;

    }


    private function pluckModuleIndex($cols) {

        $data = Module::where('status',true)->get($cols);
        return $data;

    }


    private function filterModuleListing($requester , $params) {

        error_log('Filtering modules....');
        $data = $this->getModuleListing($requester);

        if($params->keyword){
            error_log('Filtering modules with keyword....');
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
            error_log('Filtering modules with fromdate....');
            $date = Carbon::parse($params->fromdate)->startOfDay();
            $data = $data->filter(function ($item) use ($date) {
                return (Carbon::parse(data_get($item, 'created_at')) >= $date);
            });
        }

        if($params->todate){
            error_log('Filtering modules with todate....');
            $date = Carbon::parse($request->todate)->endOfDay();
            $data = $data->filter(function ($item) use ($date) {
                return (Carbon::parse(data_get($item, 'created_at')) <= $date);
            });

        }

        if($params->status){
            error_log('Filtering modules with status....');
            if($params->status == 'true'){
                $data = $data->where('status', true);
            }else if($params->status == 'false'){
                $data = $data->where('status', false);
            }else{
                $data = $data->where('status', '!=', null);
            }
        }

        if($params->onsale){
            error_log('Filtering modules with on sale status....');
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


    private function pluckModuleFilter($cols , $params) {

        //Unauthorized users cannot access deleted data
        $data = Module::where('status',true)->get();

        if($params->keyword){
            error_log('Filtering modules with keyword....');
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
            error_log('Filtering modules with fromdate....');
            $date = Carbon::parse($params->fromdate)->startOfDay();
            $data = $data->filter(function ($item) use ($date) {
                return (Carbon::parse(data_get($item, 'created_at')) >= $date);
            });
        }

        if($params->todate){
            error_log('Filtering modules with todate....');
            $date = Carbon::parse($request->todate)->endOfDay();
            $data = $data->filter(function ($item) use ($date) {
                return (Carbon::parse(data_get($item, 'created_at')) <= $date);
            });

        }

        if($params->onsale){
            error_log('Filtering modules with on sale status....');
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


    private function getModule($requester , $uid) {
        $data = Module::where('uid', $uid)->where('status', 1)->first();
        return $data;
    }

    private function pluckModule($cols , $uid) {
        $data = Module::where('uid', $uid)->where('status', 1)->get($cols)->first();
        return $data;
    }

    private function createModule($requester , $params) {

        $data = new Module();
        $data->uid = Carbon::now()->timestamp . Module::count();
        $data->name = $params->name;
        $data->desc = $params->desc;
        $data->provider = $params->provider;
        $data->status = true;

        try {
            $data->save();
            $this->createLog($requester->id , [$data->id], 'store', 'module');
        } catch (Exception $e) {
            return null;
        }

        return $data->refresh();
    }

    //Make Sure Module is not empty when calling this function
    private function updateModule($requester, $data,  $params) {

        $data->name = $params->name;
        $data->desc = $params->desc;
        $data->provider = $params->provider;

        try {
            $data->save();
            $this->createLog($requester->id , [$data->id], 'update', 'module');
        } catch (Exception $e) {
            return null;
        }

        return $data->refresh();
    }

    private function deleteModule($requester , $id) {
        $data = Module::find($id);
        $data->status = false;
        try {
            $data->save();
            $this->createLog($requester->id , [$data->id], 'delete', 'module');
        } catch (Exception $e) {
            return null;
        }

        return $data->refresh();
    }


}
