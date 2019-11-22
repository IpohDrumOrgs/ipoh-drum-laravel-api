<?php

namespace App\Traits;
use App\Type;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;
use App\Traits\GlobalFunctions;
use App\Traits\LogServices;

trait TypeServices {

    use GlobalFunctions, LogServices;

    private function getTypes($requester) {

        $data = collect();
        $temp = Type::where('status', true)->get();
        $data = $data->merge($temp);

        $data = $data->unique('id')->sortBy('id')->flatten(1);

        return $data;
    }



    private function filterTypes($data , $params) {

        error_log('Filtering types....');

        if($params->keyword){
            error_log('Filtering types with keyword....');
            $keyword = $params->keyword;
            $data = $data->filter(function($item)use($keyword){
                //check string exist inside or not
                if(stristr($item->uid, $keyword) == TRUE || stristr($item->name, $keyword) == TRUE) {
                    return true;
                }else{
                    return false;
                }

            });
        }


        if($params->fromdate){
            error_log('Filtering types with fromdate....');
            $date = Carbon::parse($params->fromdate)->startOfDay();
            $data = $data->filter(function ($item) use ($date) {
                return (Carbon::parse(data_get($item, 'created_at')) >= $date);
            });
        }

        if($params->todate){
            error_log('Filtering types with todate....');
            $date = Carbon::parse($request->todate)->endOfDay();
            $data = $data->filter(function ($item) use ($date) {
                return (Carbon::parse(data_get($item, 'created_at')) <= $date);
            });

        }

        if($params->status){
            error_log('Filtering types with status....');
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



    private function getType($uid) {
        $data = Type::where('uid', $uid)->where('status', 1)->first();
        return $data;
    }

    private function createType($params) {

        $data = new Type();
        $data->uid = Carbon::now()->timestamp . Type::count();
        $data->name = $params->name;
        $data->desc = $params->desc;
        $data->icon = $params->icon;
        try {
            $data->save();
        } catch (Exception $e) {
            return null;
        }

        return $data->refresh();
    }

    //Make Sure Type is not empty when calling this function
    private function updateType($data,  $params) {

        $data->name = $params->name;
        $data->desc = $params->desc;
        $data->icon = $params->icon;
        try {
            $data->save();
        } catch (Exception $e) {
            return null;
        }

        return $data->refresh();
    }

    private function deleteType($data) {
        $data->status = false;
        try {
            $data->save();
        } catch (Exception $e) {
            return null;
        }

        return $data->refresh();
    }


}
