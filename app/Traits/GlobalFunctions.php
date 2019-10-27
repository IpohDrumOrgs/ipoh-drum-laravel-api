<?php

namespace App\Traits;
use App\User;
use App\Role;
use App\Inventory;
use App\InventoryBatch;
use App\Module;
use App\Batch;
use App\PurchaseItem;
use App\SaleItem;
use App\Sale;
use Carbon\Carbon;
use DB;

trait GlobalFunctions {


    public function checkAccessibility($user, $module, $affected_id,$provider) {
        
        $usermodule = $user->role->modules()->wherePivot('module_id',$module->id)->wherePivot('role_id',$user->role->id)->first();
        if(empty($usermodule)){
            return false;
        }else{ 
            
            //Get User company for wide checking
            $groups = $user->groups;
            $companies = collect();
            foreach($groups as $group){
                $companies = $companies->push($group->company);
                foreach($group->company->branches as $branch){
                    $companies = $companies->push($branch);
                }
            }
            
            $ownwide = false;
            $companywide = false;
            //Performance that affected the wide

            $owner = $item->company;
            foreach($usercompanies as $usercompany){
                if($usercompany->contains('id',$owner->id)){
                    $owner = true;
                    $companywide = true;
                    break;
                }
            }

            //Check The minimum authority of this operation
            $maxclearance = 1;
            //Own wide
            if($ownwide){
                $maxclearance = 3;
            }else if($companywide){
                //Company wide
                $maxclearance = 2;
            }else {
                //System wide
                $maxclearance = 1;
            }

            //Check the request user got the authority to do this operation or not
            if($usermodule->pivot->clearance <= $maxclearance){
                return true;
            }else{
                return false;
            }

            
        }


        
    }
    
    public function checkClearance($user, $module) {
        if($user == null || $module == null){
            return null;
        }
        $module = $user->role->modules()->wherePivot('module_id',$module->id)->wherePivot('role_id',$user->role->id)->first();
        if(empty($module)){
            return null;
        }else{ 
            return $module->pivot->clearance;
        }
       
    }
    
    public function getModule($provider,$action) {

        $module = Module::where('provider',$provider)->where('action',$action)->first();
        if(empty($module)){
            return null;
        }else{
            return $module;
        }
    }
    
    public function createInventory($item,$company, $inventory_id) {


        $inventory = new Inventory();
        $checkid = false;
        $uid = '';
        while(!$checkid){
            $uid = '4' . Carbon::now()->timestamp;
            if (!Inventory::where('uid', '=', $uid)->exists()) {
                // user found
                $checkid = true;
            }
        }

        $inventory->uid = $uid;
        $inventory->code = $item->code;
        $inventory->name = $item->name;
        $inventory->sku = $item->sku;
        $inventory->cost = $item->cost;
        $inventory->price = $item->price;
        $inventory->desc = $item->desc;
        $inventory->stock = $item->stock;
        $inventory->stockthreshold = $item->stockthreshold;
        $inventory->salesqty = 0;
        $inventory->company()->associate($company);
        try{
            $inventory->save();
        }catch(Exception $e){
            DB::rollBack();
            return false;
        }

        $hqinventory = Inventory::find($inventory_id);
        $inventorybatches = $hqinventory->inventorybatches;

        foreach($inventorybatches as $inventorybatch){
            $new = new InventoryBatch();
            $new->code = $inventorybatch->code;
            $new->sku = $inventorybatch->sku;
            $new->qty = $inventorybatch->qty;
            $new->inventory()->associate($inventory->refresh());
            $new->save();
        }
        if($inventory->stock > 0){

            $batch = new Batch();
            $batch->uid = $inventory->uid.'-'.($inventory->batches()->count() + 1);
            $batch->cost = $inventory->cost;
            $batch->price = $inventory->price;
            $batch->stock = $inventory->stock;
            $batch->salesqty = $inventory->salesqty;
            $batch->batchno = $inventory->batches()->max('batchno') + 1;
            $batch->curbatch = true;
            $batch->inventory()->associate($inventory);

            
            try{
                $batch->save();
            }catch(Exception $e){
                DB::rollBack();
                return false;
            }

              
        }

        return true;
    }

    
    public function deductInventoryBatchWithCost($inventoryid ,$qty , $uid, $provider) {

        if($provider == 'saleitem'){
            $item = SaleItem::where('uid', $uid)->first();
            if(empty($item)){
                DB::rollback();
                return null;
            }
        }
        $inventory = Inventory::find($inventoryid);
        if(empty($inventory)){
            DB::rollback();
            return null;
        }
        $oristock = $inventory->stock;
        $inventory->stock -= $qty;
        if($inventory->stock < 0 && !$inventory->backorder){
            DB::rollback();
            return null;
        }
        $inventory->salesqty += $qty;
        
        $totalcost = 0;
        $backorderqty = 0;

        if($inventory->stock < 0){
            $leftqty = $oristock;
            $backorderqty = $oristock - $qty;
        }else{
            $leftqty = $qty;
        }

        if($leftqty > 0){
            
            $curbatch = $inventory->batches()->where('curbatch',true)->first();
            if(empty($curbatch)){
                DB::rollback();
                return null;
            }
            do{
                if($curbatch->stock < $leftqty){
                    $leftqty = $leftqty - $curbatch->stock;
                    
                    if($provider == 'saleitem'){
                        $item->batches()->attach($curbatch->id,['stock' => $curbatch->stock]);
                    }
                    $curbatch->salesqty += $curbatch->stock;
                    $totalcost += $curbatch->stock * $curbatch->cost;
                    $curbatch->stock = 0;
                    $curbatch->status = false;
                    $curbatch->curbatch = false;

                    try{
                        $curbatch->save();
                    }catch(Exception $e){
                        DB::rollBack();
                        return null;
                    }
                    $minbatchno = $inventory->batches()->where('status',true)->min('batchno');
                    $curbatch = $inventory->batches()->where('batchno',$minbatchno)->first();
                    if(empty($curbatch)){
                        DB::rollback();
                        return null;
                    }
                }else{
                    $curbatch->salesqty += $leftqty;
                    if($provider == 'saleitem'){
                        $item->batches()->attach($curbatch->id,['stock' => $leftqty]);
                    }
                    $curbatch->stock -= $leftqty;
                    $totalcost += $leftqty * $curbatch->cost;
                    if($curbatch->stock == 0){
                        $curbatch->status = false;
                        $curbatch->curbatch = false;
                        try{
                            $curbatch->save();
                        }catch(Exception $e){
                            DB::rollBack();
                            return null;
                        }
                        $this->setCurrentBatch($inventory->id);
                    }else{
                        $curbatch->status = true;
                        $curbatch->curbatch = true;
                        try{
                            $curbatch->save();
                        }catch(Exception $e){
                            DB::rollBack();
                            return null;
                        }
                        
                    }
                    $leftqty = 0;
                }

            }while($leftqty != 0);
        }

        if($inventory->batches()->where('status',true)->where('backorder',false)->count() != 0){
            $inventory->cost = $inventory->batches()->where('status',true)->min('cost');
            $inventory->price = $inventory->batches()->where('status',true)->max('price');
        }

        if($backorderqty < 0 ){
            $batch = $inventory->batches()->where('status',true)->where('backorder',true)->first();
            if(empty($batch)){
                $batch = new Batch();
                $batch->uid = $inventory->uid.'-'.($inventory->batches()->count() + 1);
                $batch->cost = 0;
                $batch->price = 0;
                $batch->salesqty = 0;
                $batch->batchno = $inventory->batches()->max('batchno') + 1;
                if($inventory->batches()->where('curbatch', true)->where('status', true)->count() == 0){
                    $batch->curbatch = true;
                }else{
                    $batch->curbatch = false;
                }
                $batch->inventory()->associate($inventory);
            }
            $oristock = $batch->stock;
            $batch->stock = $backorderqty;
            $batch->salesqty = -$backorderqty;
            $batch->backorder = true;

            try{
                $batch->save();
            }catch(Exception $e){
                DB::rollBack();
                return false;
            }
            
            if($provider == 'saleitem'){
                
                $item->batches()->attach($batch->refresh()->id, ['stock' =>  $oristock - $batch->stock , 'status' => 'open']);
            }
        }
        
        try{
            $inventory->save();
        }catch(Exception $e){
            DB::rollBack();
            return null;
        }

        return $totalcost;
    }

    
    public function addInventoryBatch($inventoryid, $data, $uid, $provider) {

        $inventory = Inventory::find($inventoryid);
        if(empty($inventory)){
            DB::rollback();
            return false;
        }

        $inventory->stock += $data->stock;

        if($provider == 'purchaseitem'){
            $item = PurchaseItem::where('uid',$uid)->first();
            if(empty($item)){
                DB::rollback();
                return false;
            }
        }
        

        $batch = new Batch();
        $batch->uid = $inventory->uid.'-'.($inventory->batches()->count() + 1);
        $batch->cost = $data->cost;
        $batch->price = $data->price;
        $batch->stock = $data->stock;
        $batch->salesqty = 0;
        $batch->batchno = $inventory->batches()->max('batchno') + 1;
        if($inventory->batches()->where('curbatch', true)->where('status', true)->count() == 0){
            $batch->curbatch = true;
        }else{
            $batch->curbatch = false;
        }
        $batch->inventory()->associate($inventory);

        
        try{
            $batch->save();
        }catch(Exception $e){
            DB::rollBack();
            return false;
        }

        if($provider == 'purchaseitem'){
            $item->batch()->associate($batch);
            try{
                $item->save();
            }catch(Exception $e){
                DB::rollBack();
                return false;
            }
        }

        if($inventory->batches()->where('status',true)->where('backorder',false)->count() != 0){
            $inventory->cost = $inventory->batches()->where('status',true)->min('cost');
            $inventory->price = $inventory->batches()->where('status',true)->max('price');
        }

        
        try{
            $inventory->save();
        }catch(Exception $e){
            DB::rollBack();
            return false;
        }

        return true;
    }

    public function setCurrentBatch($inventoryid){
        $inventory = Inventory::find($inventoryid);
        if(empty($inventory)){
            DB::rollback();
            return false;
        }

        $inventory->batches()->update(['curbatch' => false]);
        $minbatchno = $inventory->batches()->where('status',true)->min('batchno');
        $curbatch = $inventory->batches()->where('batchno',$minbatchno)->where('status',true)->first();
        
        if(!empty($curbatch)){
            $curbatch->curbatch = true;
            try{
                $curbatch->save();
                return true;
            }catch(Exception $e){
                DB::rollBack();
    
                return false;
            }
        }
        return true;
        
    }

    public function updateBackOrderBatch($inventoryid , $data , $uid, $provider){
        
        $inventory = Inventory::find($inventoryid);
        if(empty($inventory)){
            DB::rollback();
            return false;
        }

        $backorderbatch = $inventory->batches->where('status', true)->where('backorder', true)->where('curbatch',true)->first();
        if(empty($backorderbatch)){
            DB::rollback();
            return false;
        }
        
        if($provider == 'purchaseitem'){
            $item = PurchaseItem::where('uid',$uid)->first();
            if(empty($item)){
                DB::rollback();
                return false;
            }

            $item->batch()->associate($backorderbatch);

        }

        $backorderbatch->cost = $data->cost;
        $backorderbatch->price = $data->price;
        $backorderbatch->status = false;
        $backorderbatch->backorder = false;
        $backorderbatch->curbatch = false;
        
        try{
            $backorderbatch->save();
        }catch(Exception $e){
            DB::rollBack();
            return false;
        }
        
        //Left Back Order QTY
        $leftqty = $backorderbatch->stock + $data->stock;
        if($leftqty < 0){
            $oristock = $backorderbatch->stock;
            $backorderbatch->stock = 0;

            $batch = new Batch();
            $batch->uid = $inventory->uid.'-'.($inventory->batches()->count() + 1);
            $batch->cost = 0;
            $batch->price = 0;
            $batch->batchno = $inventory->batches()->max('batchno') + 1;
            if($inventory->batches()->where('curbatch', true)->where('status', true)->count() == 0){
                $batch->curbatch = true;
            }else{
                $batch->curbatch = false;
            }
            $batch->inventory()->associate($inventory);
            $batch->stock = $leftqty;
            $batch->salesqty = -$leftqty;
            $batch->backorder = true;
            try{
                $batch->save();
            }catch(Exception $e){
                DB::rollBack();
                return false;
            }
            
            //Qty that filled back order
            $filledqty = $leftqty - $oristock;
            $inventory->stock += $filledqty;

            $owesaleitems = $backorderbatch->saleitems;
            foreach($owesaleitems as $owesaleitem){

                if($filledqty == 0){
                    
                    $oristock = $owesaleitem->pivot->stock;
                    $owesaleitem->batches()->updateExistingPivot($backorderbatch , ['status' => 'cancel']);
                    $owesaleitem->batches()->attach($batch , ['stock' => $oristock , 'status' => 'open']);

                }else if($filledqty < $owesaleitem->pivot->stock){
                    
                    $owesaleitem->totalcost += $filledqty * $backorderbatch->cost;
                    try{
                        $owesaleitem->save();
                    }catch(Exception $e){
                        DB::rollBack();
                        return false;
                    }

                    $sale = $owesaleitem->sale;
                    $totalcost = 0;
                    
                    foreach($sale->saleitems as $saleitem){
                        $totalcost += $saleitem->totalcost;
                    }
                    $sale->totalcost = $totalcost;
                    try{
                        $sale->save();
                    }catch(Exception $e){
                        DB::rollBack();
                        return false;
                    }

                    $oristock = $owesaleitem->pivot->stock;
                    $owesaleitem->batches()->updateExistingPivot($backorderbatch , ['stock' => $filledqty , 'status' => 'close']);
                    $backorderbatch->stock = 0;

                    if($oristock - $filledqty != 0){
                        $owesaleitem->batches()->attach($batch , ['stock' => $oristock - $filledqty , 'status' => 'open']);
                    }

                    $filledqty = 0;

                    try{
                        $backorderbatch->save();
                    }catch(Exception $e){
                        DB::rollBack();
                        return false;
                    }

                }else{
                    $filledqty -= $owesaleitem->pivot->stock;
                    $owesaleitem->totalcost += $owesaleitem->pivot->stock * $backorderbatch->cost;
                    $owesaleitem->batches()->updateExistingPivot($backorderbatch , ['status' => 'close']);
                    try{
                        $owesaleitem->save();
                    }catch(Exception $e){
                        DB::rollBack();
                        return false;
                    }

                    $sale = $owesaleitem->sale;
                    $totalcost = 0;
                    
                    foreach($sale->saleitems as $saleitem){
                        $totalcost += $saleitem->totalcost;
                    }
                    $sale->totalcost = $totalcost;
                    try{
                        $sale->save();
                    }catch(Exception $e){
                        DB::rollBack();
                        return false;
                    }

                    try{
                        $backorderbatch->save();
                    }catch(Exception $e){
                        DB::rollBack();
                        return false;
                    }
                }
            }

        }else{
            $backorderbatch->stock += $data->stock;
            $inventory->stock += $data->stock;
            if($backorderbatch->stock == 0){
                $backorderbatch->curbatch = false;
                try{
                    $backorderbatch->save();
                }catch(Exception $e){
                    DB::rollBack();
                    return false;
                }
                $this->setCurrentBatch($inventory->id);
            }else{
                $backorderbatch->curbatch = true;
                try{
                    $backorderbatch->save();
                }catch(Exception $e){
                    DB::rollBack();
                    return false;
                }            
            }


            $owesaleitems = $backorderbatch->saleitems;
            foreach($owesaleitems as $owesaleitem){
                $owesaleitem->totalcost += $owesaleitem->pivot->stock * $backorderbatch->cost;
                try{
                    $owesaleitem->save();
                }catch(Exception $e){
                    DB::rollBack();
                    return false;
                }


                $sale = $owesaleitem->sale;
                $totalcost = 0;
                
                foreach($sale->saleitems as $saleitem){
                    $totalcost += $saleitem->totalcost;
                }
                $sale->totalcost = $totalcost;
                try{
                    $sale->save();
                }catch(Exception $e){
                    DB::rollBack();
                    return false;
                }
            }

        }

        try{
            $inventory->save();
        }catch(Exception $e){
            DB::rollBack();
            return false;
        }
            
        $this->setCurrentBatch($inventory->id);
        return true;
        
    }

    //Page Pagination
    public function paginateResult($data , $result , $page){
        
        if($result != null && $result != "" && $result != 0){
            $result = 10;
        }
        
        $data = $data->slice(($page-1) * $result)->take($result);

        return $data;
    }

    //Get Maximun Pages
    public function getMaximumPaginationPage($dataNo , $result){
        
        if($result != null && $result != "" && $result != 0){
            $result = 10;
        }
        
        $maximunPage = ceil($dataNo / $result);

        return $maximunPage;
    }
    
}