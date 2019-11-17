<?php

namespace App\Traits;
use App\User;
use App\CompanyType;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;
use App\Traits\GlobalFunctions;
use App\Traits\LogServices;

trait CompanyTypeServices {

    use GlobalFunctions, LogServices;

    private function getCompanyTypeListing($requester) {

        $data = collect();

        $temp = CompanyType::where('status', true)->get();
        $data = $data->merge($temp);

        $data = $data->unique('id')->sortBy('id')->flatten(1);

        return $data;

    }


    private function pluckCompanyTypeIndex($cols) {

        $data = CompanyType::where('status',true)->get($cols);
        return $data;

    }


    private function filterCompanyTypeListing($requester , $params) {

        error_log('Filtering companytypes....');
        $data = $this->getCompanyTypeListing($requester);

        if($params->keyword){
            error_log('Filtering companytypes with keyword....');
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
            error_log('Filtering companytypes with fromdate....');
            $date = Carbon::parse($params->fromdate)->startOfDay();
            $data = $data->filter(function ($item) use ($date) {
                return (Carbon::parse(data_get($item, 'created_at')) >= $date);
            });
        }

        if($params->todate){
            error_log('Filtering companytypes with todate....');
            $date = Carbon::parse($request->todate)->endOfDay();
            $data = $data->filter(function ($item) use ($date) {
                return (Carbon::parse(data_get($item, 'created_at')) <= $date);
            });

        }

        if($params->status){
            error_log('Filtering companytypes with status....');
            if($params->status == 'true'){
                $data = $data->where('status', true);
            }else if($params->status == 'false'){
                $data = $data->where('status', false);
            }else{
                $data = $data->where('status', '!=', null);
            }
        }

        if($params->onsale){
            error_log('Filtering companytypes with on sale status....');
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


    private function pluckCompanyTypeFilter($cols , $params) {

        //Unauthorized users cannot access deleted data
        $data = CompanyType::where('status',true)->get();

        if($params->keyword){
            error_log('Filtering companytypes with keyword....');
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
            error_log('Filtering companytypes with fromdate....');
            $date = Carbon::parse($params->fromdate)->startOfDay();
            $data = $data->filter(function ($item) use ($date) {
                return (Carbon::parse(data_get($item, 'created_at')) >= $date);
            });
        }

        if($params->todate){
            error_log('Filtering companytypes with todate....');
            $date = Carbon::parse($request->todate)->endOfDay();
            $data = $data->filter(function ($item) use ($date) {
                return (Carbon::parse(data_get($item, 'created_at')) <= $date);
            });

        }

        if($params->onsale){
            error_log('Filtering companytypes with on sale status....');
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


    private function getCompanyType($requester , $uid) {
        $data = CompanyType::where('uid', $uid)->where('status', 1)->first();
        return $data;
    }

    private function pluckCompanyType($cols , $uid) {
        $data = CompanyType::where('uid', $uid)->where('status', 1)->get($cols)->first();
        return $data;
    }

    private function createCompanyType($requester , $params) {

        $data = new CompanyType();
        $data->uid = Carbon::now()->timestamp . CompanyType::count();
        $data->name = $params->name;
        $data->desc = $params->desc;
        $data->status = true;

        try {
            $data->save();
            $this->createLog($requester->id , [$data->id], 'store', 'companytype');
        } catch (Exception $e) {
            return null;
        }

        return $data->refresh();
    }

    //Make Sure CompanyType is not empty when calling this function
    private function updateCompanyType($requester, $data,  $params) {

        $data->name = $params->name;
        $data->desc = $params->desc;

        try {
            $data->save();
            $this->createLog($requester->id , [$data->id], 'update', 'companytype');
        } catch (Exception $e) {
            return null;
        }

        return $data->refresh();
    }

    private function deleteCompanyType($requester , $id) {
        $data = CompanyType::find($id);
        $data->status = false;
        try {
            $data->save();
            $this->createLog($requester->id , [$data->id], 'delete', 'companytype');
        } catch (Exception $e) {
            return null;
        }

        return $data->refresh();
    }


}
