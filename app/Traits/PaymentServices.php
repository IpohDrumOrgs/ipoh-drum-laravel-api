<?php

namespace App\Traits;
use App\Payment;
use App\Company;
use App\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;
use Stripe;
use App\Traits\AllServices;

trait PaymentServices {

    use AllServices;

    private function getPayments($requester) {
        $data = collect();

        //Role Based Retrieved Done in Company Services
        $sales = $this->getSales($requester);
        foreach($sales as $sale){
            $data = $data->merge($sale->payments()->where('status',true)->get());
        }

        $data = $data->merge($requester->payments()->where('status',true)->get());

        $data = $data->unique('id')->sortBy('id')->flatten(1);

        return $data;

    }


    private function filterPayments($data , $params) {

        error_log('Filtering payments....');

        if($params->keyword){
            error_log('Filtering payments with keyword....');
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
            error_log('Filtering payments with fromdate....');
            $date = Carbon::parse($params->fromdate)->startOfDay();
            $data = $data->filter(function ($item) use ($date) {
                return (Carbon::parse(data_get($item, 'created_at')) >= $date);
            });
        }

        if($params->todate){
            error_log('Filtering payments with todate....');
            $date = Carbon::parse($request->todate)->endOfDay();
            $data = $data->filter(function ($item) use ($date) {
                return (Carbon::parse(data_get($item, 'created_at')) <= $date);
            });

        }

        if($params->status){
            error_log('Filtering payments with status....');
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

    private function getPayment($uid) {
        $data = Payment::where('uid', $uid)->where('status', 1)->first();
        return $data;
    }

    private function getPaymentById($id) {
        $data = Payment::where('id', $id)->where('status', 1)->first();
        return $data;
    }

    private function createPayment($params) {

        $params = $this->checkUndefinedProperty($params , $this->paymentAllCols());

    //     $data = new Payment();
    //     $data->uid = Carbon::now()->timestamp . Payment::count();
    //     $data->name = $params->name;
    //     $data->desc = $params->desc;
    //     $data->contact = $params->contact;
    //     $data->email = $params->email;
    //     $data->address = $params->address;
    //     $data->postcode = $params->postcode;
    //     $data->rating = 0;
    //     $data->city = $params->city;
    //     $data->state = $params->state;
    //     $data->country = $params->country;
    //     $data->companyBelongings = $params->companyBelongings;
    //     $data->status = true;

    //    //Assign Owner
    //    if($data->companyBelongings){
    //         $company = $this->getCompanyById($params->company_id);
    //         error_log($params->company_id);
    //         error_log($company);
    //         if($this->isEmpty($company)){
    //             return null;
    //         }
    //         $data->company()->associate($company);
    //         $data->user_id = null;
    //     }else{
    //         $user = $this->getUserById($params->user_id);
    //         error_log($user);
    //         if($this->isEmpty($user)){
    //             return null;
    //         }
    //         $data->user()->associate($user);
    //         $data->company_id = null;
    //     }
        
    //     if(!$this->saveModel($data)){
    //         return null;
    //     }
            

    //     return $data->refresh();
    }

    //Make Sure Payment is not empty when calling this function
    private function updatePayment($data,  $params) {

        $params = $this->checkUndefinedProperty($params , $this->paymentAllCols());

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
            $company = $this->getCompanyById($params->company_id);
            if($this->isEmpty($company)){
                return null;
            }
            $data->company()->associate($company);
            $data->user_id = null;
        }else{
            $user = $this->getUserById($params->user_id);
            if($this->isEmpty($user)){
                return null;
            }
            $data->user()->associate($user);
            $data->company_id = null;
        }
        
        if(!$this->saveModel($data)){
            return null;
        }

        return $data->refresh();
    }

    private function deletePayment($data) {

        $reviews = $data->reviews;
        foreach($reviews as $review){
            if(!$this->deletePaymentReview($review)){
                return null;
            }
        }
        
        $inventories = $data->inventories;
        foreach($inventories as $inventory){
            if(!$this->deleteInventory($inventory)){
                return null;
            }
        }

        $tickets = $data->tickets;
        foreach($tickets as $ticket){
            if(!$this->deleteTicket($ticket)){
                return null;
            }
        }

        $promotions = $data->promotions;
        foreach($promotions as $promotion){
            if(!$this->deleteProductPromotion($promotion)){
                return null;
            }
        }

        $warranties = $data->warranties;
        foreach($warranties as $warranty){
            if(!$this->deleteWarranty($warranty)){
                return null;
            }
        }

        $shippings = $data->shippings;
        foreach($shippings as $shipping){
            if(!$this->deleteShipping($shipping)){
                return null;
            }
        }

        $vouchers = $data->vouchers;
        foreach($vouchers as $voucher){
            if(!$this->deleteVoucher($voucher)){
                return null;
            }
        }

        $data->status = false;
        if(!$this->saveModel($data)){
            return null;
        }

        return $data->refresh();
    }


    // Modifying Display Data
    // -----------------------------------------------------------------------------------------------------------------------------------------

    public function paymentAllCols() {

        return ['id','company_id', 'user_id', 'uid' ,'name', 'contact', 'desc' , 
        'imgpath' , 'imgpublicid'  , 'email', 'rating' , 'freeshippingminpurchase' , 
        'address' , 'state' , 'postcode' , 'city','country','status','companyBelongings'];

    }

    // Charge Data
    // -----------------------------------------------------------------------------------------------------------------------------------------

    
    public function getStripeChargePercentage() {

        return 0.034;
    }

    public function getStripeChargePrice() {

        return 2;
    }

    public function getAppChargePrice() {

        return 0;
    }

    public function getAppChargePercentage() {

        return 0;
    }

    public function getChargedPrice($price) {

        return $this->toDouble(($price * $this->getStripeChargePercentage()) + ($price * $this->getAppChargePercentage()) + $this->getAppChargePrice() + $this->getStripeChargePrice());
    }

    // Stripe Services
    // -----------------------------------------------------------------------------------------------------------------------------------------

    public function createStripeCustomer($params) {

        try{
            $customer = Stripe::customers()->create([
                'email' => $params->email,
                'name' => $params->name,
                'phone' => $params->phone,
                'address' => $params->address,
                'description' => $params->description,
            ]);
            return $customer->id;
        }catch(Exception $e){
            return null;
        }
    }

}
