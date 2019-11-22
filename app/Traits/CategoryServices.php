<?php

namespace App\Traits;
use App\Category;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;
use App\Traits\GlobalFunctions;
use App\Traits\LogServices;
use App\Traits\AssignDatabaseRelationship;

trait CategoryServices {

    use GlobalFunctions, LogServices, AssignDatabaseRelationship;

    private function getCategories($requester) {

        $data = collect();

        $temp = Category::where('status', true)->get();
        $data = $data->merge($temp);

        $data = $data->unique('id')->sortBy('id')->flatten(1);

        return $data;

    }



    private function filterCategories($data , $params) {

        error_log('Filtering categories....');

        if($params->keyword){
            error_log('Filtering categories with keyword....');
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
            error_log('Filtering categories with fromdate....');
            $date = Carbon::parse($params->fromdate)->startOfDay();
            $data = $data->filter(function ($item) use ($date) {
                return (Carbon::parse(data_get($item, 'created_at')) >= $date);
            });
        }

        if($params->todate){
            error_log('Filtering categories with todate....');
            $date = Carbon::parse($request->todate)->endOfDay();
            $data = $data->filter(function ($item) use ($date) {
                return (Carbon::parse(data_get($item, 'created_at')) <= $date);
            });

        }

        if($params->status){
            error_log('Filtering categories with status....');
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


    private function getCategory($uid) {
        $data = Category::where('uid', $uid)->where('status', 1)->first();
        return $data;
    }


    private function createCategory($params) {

        $data = new Category();
        $data->uid = Carbon::now()->timestamp . Category::count();
        $data->name = $params->name;
        $data->desc = $params->desc;
        try {
            $data->save();
        } catch (Exception $e) {
            return null;
        }

        // if(!$this->isEmpty($params->ticketids)){

        //     $ids = $this->splitToArray($params->ticketids);
        //     $params = collect();
        //     foreach($ids as $id){
        //         $temp = collect(['id' => $id , 'remark' => null]);
        //         $params = $params->push($temp);
        //     }
        //     $params = json_decode(json_encode($params));
        //     if($this->assignTicketsToMany($data, $params)){

        //     }
        // }

        return $data->refresh();
    }

    //Make Sure Category is not empty when calling this function
    private function updateCategory($data,  $params) {

        $data->name = $params->name;
        $data->desc = $params->desc;
        try {
            $data->save();
        } catch (Exception $e) {
            return null;
        }

        // if(!$this->isEmpty($params->ticketids)){

        //     $ids = $this->splitToArray($params->ticketids);
        //     $params = collect();
        //     foreach($ids as $id){
        //         $temp = collect(['id' => $id , 'remark' => null]);
        //         $params = $params->push($temp);
        //     }
        //     $params = json_decode(json_encode($params));
        //     if($this->assignTicketsToMany($data, $params)){

        //     }
        // }
        return $data->refresh();
    }

    private function deleteCategory($data) {
        $data->status = false;
        try {
            $data->save();
        } catch (Exception $e) {
            return null;
        }

        return $data->refresh();
    }

}
