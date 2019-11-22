<?php

namespace App\Traits;
use App\Store;
use App\Company;
use App\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;
use App\Traits\GlobalFunctions;
use App\Traits\LogServices;
use App\Traits\CompanyServices;

trait StoreServices {

    use GlobalFunctions, LogServices , CompanyServices;

    private function getStores($requester) {
        $data = collect();

        //Role Based Retrieved Done in Company Services
        $companies = $this->getCompanies($requester);
        foreach($companies as $company){
            $data = $data->merge($company->stores()->where('status',true)->get());
        }

        $data = $data->unique('id')->sortBy('id')->flatten(1);

        return $data;

    }


    private function filterStores($data , $params) {

        error_log('Filtering stores....');

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

    private function getStore($uid) {
        $data = Store::where('uid', $uid)->where('status', 1)->first();
        return $data;
    }

    private function createStore($params) {

        $data = new Store();
        $data->uid = Carbon::now()->timestamp . Store::count();
        $data->name = $params->name;
        $data->desc = $params->desc;
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
        } catch (Exception $e) {
            return null;
        }

        return $data->refresh();
    }

    //Make Sure Store is not empty when calling this function
    private function updateStore($data,  $params) {

        $data->name = $params->name;
        $data->contact = $params->contact;
        $data->desc = $params->desc;
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

        try {
            $data->save();
        } catch (Exception $e) {
            return null;
        }

        return $data->refresh();
    }

    private function deleteStore($data) {
        $data->status = false;
        try {
            $data->save();
        } catch (Exception $e) {
            return null;
        }

        return $data->refresh();
    }


}
