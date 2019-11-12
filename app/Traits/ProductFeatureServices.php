<?php

namespace App\Traits;
use App\ProductFeature;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;
use App\Traits\GlobalFunctions;
use App\Traits\LogServices;
use DB;

trait ProductFeatureServices {

    use GlobalFunctions, LogServices;

    private function getProductFeatureListing($requester) {

        $data = collect();
        $companies = $requester->companies;
        foreach($companies as $company){
            $clearance = $this->checkClearance($requester, $company ,  $this->getModule('productfeature','index'));
            error_log($clearance);
            switch ($clearance) {
                //System Wide
                case 1:
                //ProductFeature Wide
                case 2:
                //Group Wide
                case 3:
                //Own Wide
                case 4:
                    $temp = ProductFeature::where('status', true)->get();
                    $data = $data->merge($temp);
                    break;
                default:
                    break;
            }
    
        }
        
        $data = $data->unique('id');

        return $data;
    
    }

    
    private function pluckProductFeatureIndex($cols) {

        $data = ProductFeature::where('status',true)->get($cols);
        return $data;
    
    }


    private function filterProductFeatureListing($requester , $params) {

        error_log('Filtering productfeatures....');
        $data = $this->getProductFeatureListing($requester);

        if($params->keyword){
            error_log('Filtering productfeatures with keyword....');
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
            error_log('Filtering productfeatures with fromdate....');
            $date = Carbon::parse($params->fromdate)->startOfDay();
            $data = $data->filter(function ($item) use ($date) {
                return (Carbon::parse(data_get($item, 'created_at')) >= $date);
            });
        }

        if($params->todate){
            error_log('Filtering productfeatures with todate....');
            $date = Carbon::parse($request->todate)->endOfDay();
            $data = $data->filter(function ($item) use ($date) {
                return (Carbon::parse(data_get($item, 'created_at')) <= $date);
            });
            
        } 

        if($params->status){
            error_log('Filtering productfeatures with status....');
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

    
    private function pluckProductFeatureFilter($cols , $params) {

        //Unauthorized users cannot access deleted data
        $data = ProductFeature::where('status',true)->get();

        if($params->keyword){
            error_log('Filtering productfeatures with keyword....');
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
            error_log('Filtering productfeatures with fromdate....');
            $date = Carbon::parse($params->fromdate)->startOfDay();
            $data = $data->filter(function ($item) use ($date) {
                return (Carbon::parse(data_get($item, 'created_at')) >= $date);
            });
        }

        if($params->todate){
            error_log('Filtering productfeatures with todate....');
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


    private function getProductFeature($requester , $uid) {
        $data = ProductFeature::where('uid', $uid)->where('status', 1)->first();
        return $data;
    }

    private function pluckProductFeature($cols , $uid) {
        $data = ProductFeature::where('uid', $uid)->where('status', 1)->get($cols)->first();
        return $data;
    }

    private function createProductFeature($requester , $params) {

        DB::beginTransaction();
        $data = new ProductFeature();
        $data->uid = Carbon::now()->timestamp . ProductFeature::count();
        $data->name = $params->name;
        $data->desc = $params->desc;
        try {
            $data->save();
            $this->createLog($requester->id , [$data->id], 'store', 'productfeature');
        } catch (Exception $e) {
            DB::rollBack();
            return null;
        }

        DB::commit();
        return $data->refresh();
    }

    //Make Sure ProductFeature is not empty when calling this function
    private function updateProductFeature($requester, $data,  $params) {
        
        DB::beginTransaction();
        $data->name = $params->name;
        $data->desc = $params->desc;
        try {
            $data->save();
            $this->createLog($requester->id , [$data->id], 'update', 'productfeature');
        } catch (Exception $e) {
            DB::rollBack();
            return null;
        }

        DB::commit();
        return $data->refresh();
    }

    private function deleteProductFeature($requester , $id) {
        DB::beginTransaction();
        $data = ProductFeature::find($id);
        $data->status = false;
        try {
            $data->save();
            $this->createLog($requester->id , [$data->id], 'delete', 'productfeature');
        } catch (Exception $e) {
            DB::rollBack();
            return null;
        }

        DB::commit();
        return $data->refresh();
    }

    
}