<?php

namespace App\Http\Controllers\API;

use App\Inventory;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Traits\GlobalFunctions;
use App\Traits\NotificationFunctions;
use DB;
use Carbon\Carbon;

class InventoryController extends Controller
{
    use GlobalFunctions, NotificationFunctions;
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $inventories = Inventory::where('status' , true)->get();
        //Page Pagination Result List
        //Default return 10
        $paginateddata = $this->paginateResult($inventories , $request->result, $request->page);
        $data['data'] = $paginateddata;
        $data['maximunPage'] = $this->getMaximumPaginationPage($inventories->count(), $request->result);
        $data['msg'] = $this->getRetrievedSuccessMsg('Inventories');
        return response()->json($data, 200);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
         
        // $this->validate($request, [
        //     'company_id' => 'required',
        //     'name' => 'required|string|max:191',
        //     'code' => 'nullable',
        //     'sku' => 'required|string|max:191',
        //     'cost' => 'required|numeric|min:0',
        //     'price' => 'required|numeric|min:0',
        //     'stock' => 'required|numeric|min:0',
        //     'backorder' => 'required|boolean',
        //     'stockthreshold' => 'required|numeric|min:0',
        // ]);
        if(Inventory::where('company_id',$request->company_id)->where('code',$request->code)->count() > 0 || Inventory::where('company_id',$request->company_id)->where('sku',$request->sku)->count() > 0 ){
            
            $payload['status'] = 'error';
            $payload['msg'] = 'Code / Sku has been used.';

            return response()->json($payload, 404);
        }

        DB::beginTransaction();

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
        $inventory->code = $request->code;
        $inventory->name = $request->name;
        $inventory->sku = $request->sku;
        $inventory->cost = $request->cost;
        $inventory->price = $request->price;
        $inventory->desc = $request->desc;
        $inventory->stock = $request->stock;
        $inventory->backorder = $request->backorder;
        $inventory->stockthreshold = $request->stockthreshold;
        $inventory->salesqty = 0;
        // $company = Company::find($request->company_id);
        $company = Company::find(1);
        if(empty($company)){
            $payload['status'] = 'error';
            $payload['msg'] = 'Company Not Found.';

            return response()->json($payload, 404);
        }

        $inventory->company()->associate($company);
        if(!$inventory->save()){
            DB::rollBack();
            $payload['status'] = 'error';
            $payload['msg'] = 'Inventory Cannot Save.';

            return response()->json($payload, 404);
        }

        // if($inventory->stock > 0){

        //     $batch = new Batch();
        //     $batch->uid = $inventory->uid.'-'.($inventory->batches()->where('status','!=','cancel')->count() + 1);
        //     $batch->cost = $inventory->cost;
        //     $batch->price = $inventory->price;
        //     $batch->stock = $inventory->stock;
        //     $batch->salesqty = $inventory->salesqty;
        //     $batch->batchno = $inventory->batches()->where('status', true)->count() + 1;
        //     $batch->curbatch = true;
        //     $batch->inventory()->associate($inventory);

            
        //     if(!$batch->save()){
        //         DB::rollBack();
        //         $payload['status'] = 'error';
        //         $payload['msg'] = 'Batch Cannot Save.';

        //         return response()->json($payload, 404);
        //     }
        // }

        
        // $oriinventory = Inventory::where('sku', $request->sku)->where('company_id',1)->first();
        // if(!empty($oriinventory)){
        //     //Price Lists
        //     $accounts = $oriinventory->accounts;
        //     $inventory = $inventory->refresh();
        //     $inventory->accounts()->detach();
        //     foreach($accounts as $account){
        //         $inventory->accounts()->attach($account->id , ['min' => $account->pivot->min, 'price' => $account->pivot->price]);
        //     }
        // }


        DB::commit();
        $payload['status'] = 'success';
        $payload['msg'] = 'Inventory Saved.';
        $payload['inventory'] = $inventory->refresh();

        return response()->json($payload, 200);


    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Inventory  $inventory
     * @return \Illuminate\Http\Response
     */
    public function show($uid)
    {
        try{
            
            $inventory = Inventory::where('uid', $uid)->with('company')->first();
        }catch(Exception $e){
            $payload['status'] = 'error';
            $payload['msg'] = 'Error 404';

            return response()->json($payload, 404);
        }
        
        $payload['status'] = 'success';
        $payload['msg'] = 'Inventories Retrieved Success.';
        $payload['data'] = $inventory;

        return response()->json($payload, 200);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Inventory  $inventory
     * @return \Illuminate\Http\Response
     */
    public function edit(Inventory $inventory)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Inventory  $inventory
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $uid)
    {
        $inventory = Inventory::where('uid',$uid)->where('status',true)->first();
        if(empty($inventory)){
            $payload['status'] = 'error';
            $payload['msg'] = 'Inventory Not Found.';

            return response()->json($payload, 404);
        }
        // $this->validate($request, [
        //     'company_id' => 'required',
        //     'code' => 'nullable|string|max:191',
        //     'sku' => 'required|string|max:191',
        //     'name' => 'required|string|max:191',
        //     'cost' => 'required|numeric|min:0',
        //     'price' => 'required|numeric|min:0',
        //     'backorder' => 'required|boolean',
        //     'stockthreshold' => 'required|numeric|min:0',
        // ]);
        
        
        if($inventory->sku != $request->sku){
            if(Inventory::where('company_id',$request->company_id)->where('code',$request->code)->count() > 0 || Inventory::where('company_id',$request->company_id)->where('sku',$request->sku)->count() > 0 ){
            
                $payload['status'] = 'error';
                $payload['msg'] = 'Code / Sku has been used.';
    
                return response()->json($payload, 404);
            }
        }
        $inventory->code = $request->code;
        $inventory->name = $request->name;
        $inventory->sku = $request->sku;
        $inventory->cost = $request->cost;
        $inventory->price = $request->price;
        $inventory->desc = $request->desc;
        $inventory->backorder = $request->backorder;
        $inventory->stockthreshold = $request->stockthreshold;

        // $company = Company::find($request->company_id);

        // if(empty($company)){
        //     $payload['status'] = 'error';
        //     $payload['msg'] = 'Company Not Found.';

        //     return response()->json($payload, 404);
        // }
        // $inventory->company()->associate($company);
        if(!$inventory->save()){
            $payload['status'] = 'error';
            $payload['msg'] = 'Inventory Cannot Save.';

            return response()->json($payload, 404);
        }

        
        // $oriinventory = Inventory::where('sku', $request->sku)->where('company_id',1)->first();
        // if(!empty($oriinventory)){
        //     //Price Lists
        //     $accounts = $oriinventory->accounts;
        //     $inventory = $inventory->refresh();
        //     $inventory->accounts()->detach();
        //     foreach($accounts as $account){
        //         $inventory->accounts()->attach($account->id , ['min' => $account->pivot->min, 'price' => $account->pivot->price]);
        //     }
        // }

        $payload['status'] = 'success';
        $payload['msg'] = 'Inventories Updated Success.';
        $payload['data'] = $inventory;

        return response()->json($payload, 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Inventory  $inventory
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, $uid)
    {
         $inventory = Inventory::where('uid',$uid)->where('status', true)->first();
        if(empty($inventory)){
            $payload['status'] = 'error';
            $payload['msg'] = 'Inventory Not Found.';

            return response()->json($payload, 404);
        }
        // $batches = $inventory->batches;

        $inventory->status = false;

        // DB::beginTransaction();
        // foreach($batches as $batch){
        //     if(!empty($batch->purchaseitem) || !$batch->saleitems->isEmpty()){
        //         error_log($batch->purchaseitem);
        //         error_log($batch->saleitems);

        //         DB::rollBack();
        //         $payload['status'] = 'error';
        //         $payload['msg'] = 'Cannot Delete This Inventory! Because it had been used in others operation.';

        //         return response()->json($payload, 404);
        //     }else{
        //         $batch->delete();
        //     }
        // }

        $inventory->save();

        // DB::commit();

        $payload['status'] = 'success';
        $payload['msg'] = 'Inventory Delete Successful.';
        $payload['data'] = $inventory;

        return response()->json($payload, 200);
    }
}
