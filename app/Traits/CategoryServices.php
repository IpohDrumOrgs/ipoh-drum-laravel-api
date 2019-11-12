<?php

namespace App\Traits;
use App\Category;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;
use App\Traits\GlobalFunctions;
use App\Traits\LogServices;
use DB;

trait CategoryServices {

    use GlobalFunctions, LogServices;

    private function getCategoryListing($requester) {

        $data = collect();
        $companies = $requester->companies;
        foreach($companies as $company){
            $clearance = $this->checkClearance($requester, $company ,  $this->getModule('category','index'));
            error_log($clearance);
            switch ($clearance) {
                //System Wide
                case 1:
                //Category Wide
                case 2:
                //Group Wide
                case 3:
                //Own Wide
                case 4:
                    $temp = Category::where('status', true)->get();
                    $data = $data->merge($temp);
                    break;
                default:
                    break;
            }
    
        }
        
        $data = $data->unique('id');

        return $data;
    
    }

    
    private function pluckCategoryIndex($cols) {

        $data = Category::where('status',true)->get($cols);
        return $data;
    
    }


    private function filterCategoryListing($requester , $params) {

        error_log('Filtering categories....');
        $data = $this->getCategoryListing($requester);

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

    
    private function pluckCategoryFilter($cols , $params) {

        //Unauthorized users cannot access deleted data
        $data = Category::where('status',true)->get();

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

       

        $data = $data->unique('id');

        //Pluck Columns
        $data = $data->map(function($item)use($cols){
            return $item->only($cols);
        });
        
        return $data;
    
    }


    private function getCategory($requester , $uid) {
        $data = Category::where('uid', $uid)->where('status', 1)->first();
        return $data;
    }

    private function pluckCategory($cols , $uid) {
        $data = Category::where('uid', $uid)->where('status', 1)->get($cols)->first();
        return $data;
    }

    private function createCategory($requester , $params) {

        DB::beginTransaction();
        $data = new Category();
        $data->uid = Carbon::now()->timestamp . Category::count();
        $data->name = $params->name;
        $data->desc = $params->desc;
        try {
            $data->save();
            $this->createLog($requester->id , [$data->id], 'store', 'category');
        } catch (Exception $e) {
            DB::rollBack();
            return null;
        }

        DB::commit();
        return $data->refresh();
    }

    //Make Sure Category is not empty when calling this function
    private function updateCategory($requester, $data,  $params) {
        
        DB::beginTransaction();
        $data->name = $params->name;
        $data->desc = $params->desc;
        try {
            $data->save();
            $this->createLog($requester->id , [$data->id], 'update', 'category');
        } catch (Exception $e) {
            DB::rollBack();
            return null;
        }

        DB::commit();
        return $data->refresh();
    }

    private function deleteCategory($requester , $id) {
        DB::beginTransaction();
        $data = Category::find($id);
        $data->status = false;
        try {
            $data->save();
            $this->createLog($requester->id , [$data->id], 'delete', 'category');
        } catch (Exception $e) {
            DB::rollBack();
            return null;
        }

        DB::commit();
        return $data->refresh();
    }

    
}