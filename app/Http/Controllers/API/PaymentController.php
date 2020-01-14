<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use DB;
use Carbon\Carbon;
use App\Payment;
use Illuminate\Support\Facades\Hash;
use App\Traits\AllServices;
use Stripe;

class PaymentController extends Controller
{
    use AllServices;
    private $controllerName = '[PaymentController]';
     /**
     * @OA\Get(
     *      path="/api/payment",
     *      operationId="getPayments",
     *      tags={"PaymentControllerService"},
     *      summary="Get list of payments",
     *      description="Returns list of payments",
     *   @OA\Parameter(
     *     name="pageNumber",
     *     in="query",
     *     description="Page number",
     *     @OA\Schema(type="integer")
     *   ),
     *   @OA\Parameter(
     *     name="pageSize",
     *     in="query",
     *     description="number of pageSize",
     *     @OA\Schema(type="integer")
     *   ),
     *      @OA\Response(
     *          response=200,
     *          description="Successfully retrieved list of payments"
     *       ),
     *       @OA\Response(
     *          response="default",
     *          description="Unable to retrieve list of payments")
     *    )
     */
    public function index(Request $request)
    {
        error_log('Retrieving list of payments.');
        // api/payment (GET)
        $payments = $this->getPayments($request->user());
        if ($this->isEmpty($payments)) {
            return $this->errorPaginateResponse('Payments');
        } else {
            return $this->successPaginateResponse('Payments', $payments, $this->toInt($request->pageSize), $this->toInt($request->pageNumber));
        }
    }

    /**
     * @OA\Get(
     *      path="/api/filter/payment",
     *      operationId="filterPayments",
     *      tags={"PaymentControllerService"},
     *      summary="Filter list of payments",
     *      description="Returns list of filtered payments",
     *   @OA\Parameter(
     *     name="pageNumber",
     *     in="query",
     *     description="Page number",
     *     @OA\Schema(type="integer")
     *   ),
     *   @OA\Parameter(
     *     name="pageSize",
     *     in="query",
     *     description="number of pageSize",
     *     @OA\Schema(type="integer")
     *   ),
     *   @OA\Parameter(
     *     name="keyword",
     *     in="query",
     *     description="Keyword for filter",
     *     @OA\Schema(type="string")
     *   ),
     *   @OA\Parameter(
     *     name="fromdate",
     *     in="query",
     *     description="From Date for filter",
     *     @OA\Schema(type="string")
     *   ),
     *   @OA\Parameter(
     *     name="todate",
     *     in="query",
     *     description="To date for filter",
     *     @OA\Schema(type="string")
     *   ),
     *   @OA\Parameter(
     *     name="onsale",
     *     in="query",
     *     description="On sale for filter",
     *     @OA\Schema(type="string")
     *   ),
     *   @OA\Parameter(
     *     name="status",
     *     in="query",
     *     description="status for filter",
     *     @OA\Schema(type="string")
     *   ),
     *      @OA\Response(
     *          response=200,
     *          description="Successfully retrieved list of filtered payments"
     *       ),
     *       @OA\Response(
     *          response="default",
     *          description="Unable to retrieve list of payments")
     *    )
     */
    public function filter(Request $request)
    {
        error_log('Retrieving list of filtered payments.');
        // api/payment/filter (GET)
        $params = collect([
            'keyword' => $request->keyword,
            'fromdate' => $request->fromdate,
            'todate' => $request->todate,
            'status' => $request->status,
            'onsale' => $request->onsale,
            'payment_id' => $request->payment_id,
        ]);
        //Convert To Json Object
        $params = json_decode(json_encode($params));
        $payments = $this->getPayments($request->user());
        $payments = $this->filterPayments($payments, $params);

        if ($this->isEmpty($payments)) {
            return $this->errorPaginateResponse('Payments');
        } else {
            return $this->successPaginateResponse('Payments', $payments, $this->toInt($request->pageSize), $this->toInt($request->pageNumber));
        }

    }


    /**
     * @OA\Get(
     *   tags={"PaymentControllerService"},
     *   path="/api/payment/{uid}",
     *   summary="Retrieves payment by Uid.",
     *     operationId="getPaymentByUid",
     *   @OA\Parameter(
     *     name="uid",
     *     in="path",
     *     description="Payment_ID, NOT 'ID'.",
     *     required=true,
     *     @OA\Schema(type="string")
     *   ),
     *   @OA\Response(
     *     response=200,
     *     description="Payment has been retrieved successfully."
     *   ),
     *   @OA\Response(
     *     response="default",
     *     description="Unable to retrieve the payment."
     *   )
     * )
     */
    public function show(Request $request, $uid)
    {
        // api/payment/{paymentid} (GET)
        error_log('Retrieving payment of uid:' . $uid);
        $payment = $this->getPayment($uid);
        if ($this->isEmpty($payment)) {
            return $this->notFoundResponse('Payment');
        } else {
            return $this->successResponse('Payment', $payment, 'retrieve');
        }
    }



    /**
     * @OA\Post(
     *   tags={"PaymentControllerService"},
     *   path="/api/payment",
     *   summary="Creates a payment.",
     *   operationId="createPayment",
     * @OA\Parameter(
     * name="token",
     * in="query",
     * description="Stripe token",
     * required=true,
     * @OA\Schema(
     *              type="string"
     *          )
     * ),
     * @OA\Parameter(
     * name="email",
     * in="query",
     * description="Email",
     * required=true,
     * @OA\Schema(
     *              type="string"
     *          )
     * ),
     * @OA\Parameter(
     * name="contact",
     * in="query",
     * description="Contact Person",
     * required=true,
     * @OA\Schema(
     *              type="string"
     *          )
     * ),
     * @OA\Parameter(
     * name="selectedstores",
     * in="query",
     * description="Involved Store",
     * required=true,
     * @OA\Schema(
     *              type="string"
     *          )
     * ),
     *   @OA\Response(
     *     response=200,
     *     description="Payment has been created successfully."
     *   ),
     *   @OA\Response(
     *     response="default",
     *     description="Unable to create the payment."
     *   )
     * )
     */
    public function store(Request $request)
    {
        DB::beginTransaction();
        $this->validate($request, [
            'token' => 'required|string|max:500',
            'email' => 'required|email|max:255',
            'contact' => 'required|string|max:20',
            'selectedstores' =>'required|string',
        ]);

        
        $selectedstores = collect(json_decode($request->selectedstores));
        $totalpayment = 0;
        foreach($selectedstores as $selectedstore){
            
            $params = collect([
                'store_id' => $selectedstore->store_id,
                'saleitems' => $selectedstore->saleitems,
            ]);
            $params = json_decode(json_encode($params));
            //Creating Sale
            $sale = $this->createSale($params);
            if($this->isEmpty($sale)){
                DB::rollBack();
                return $this->errorResponse();
            }
            
            try{
                error_log($sale);
                $charge = Stripe::charges()->create([
                    'amount' => $sale->grandtotal,
                    'currency' => 'MYR',
                    'source' => $request->token,
                    'description' => 'Sale '. $sale->uid. ' Payment',
                    'receipt_email' => $request->email,
                    'metadata' => [
                        // 'contents' => $contents,
                        // 'quantity' => 1,
                        // 'discount' => collect(session()->get('coupon'))->toJson(),
                    ],
                ]);
            }catch(Exception $e){
                
                DB::rollBack();
                return $this->errorResponse();
            }

        }

        
        
       
        DB::commit();
        return $this->successResponse('Payment', $request, 'create');
    }


    /**
     * @OA\Post(
     *   tags={"PaymentControllerService"},
     *   path="/api/payment/{uid}",
     *   summary="Update payment by Uid.",
     *     operationId="updatePaymentByUid",
     *   @OA\Parameter(
     *     name="uid",
     *     in="path",
     *     description="Payment_ID, NOT 'ID'.",
     *     required=true,
     *     @OA\Schema(type="string")
     *   ),
     * @OA\Parameter(
     * name="name",
     * in="query",
     * description="Paymentname",
     * required=true,
     * @OA\Schema(
     *              type="string"
     *          )
     * ),
     * @OA\Parameter(
     * name="store_id",
     * in="query",
     * description="Store ID",
     * required=true,
     * @OA\Schema(
     *              type="integer"
     *          )
     * ),
     * @OA\Parameter(
     * name="product_promotion_id",
     * in="query",
     * description="Promotion ID",
     * required=false,
     * @OA\Schema(
     *              type="integer"
     *          )
     * ),
     * @OA\Parameter(
     * name="warranty_id",
     * in="query",
     * description="Warranty ID",
     * required=false,
     * @OA\Schema(
     *              type="integer"
     *          )
     * ),
     * @OA\Parameter(
     * name="shipping_id",
     * in="query",
     * description="Shipping ID",
     * required=false,
     * @OA\Schema(
     *              type="integer"
     *          )
     * ),
     * @OA\Parameter(
     * name="paymentfamilies",
     * in="query",
     * description="Payment Families",
     * required=true,
     * @OA\Schema(
     *              type="string"
     *          )
     * ),
     * @OA\Parameter(
     * name="code",
     * in="query",
     * description="Code",
     * @OA\Schema(
     *              type="string"
     *          )
     * ),
     * @OA\Parameter(
     * name="sku",
     * in="query",
     * description="Sku",
     * @OA\Schema(
     *              type="string"
     *          )
     * ),
     * @OA\Parameter(
     * name="desc",
     * in="query",
     * description="Product Description",
     * @OA\Schema(
     *              type="string"
     *          )
     * ),
     * 	@OA\RequestBody(
*          required=true,
*          @OA\MediaType(
*              mediaType="multipart/form-data",
*              @OA\Schema(
*                  @OA\Property(
*                      property="img",
*                      description="Image",
*                      type="file",
*                      @OA\Items(type="string", format="binary")
*                   ),
*               ),
*           ),
*       ),
     * @OA\Parameter(
     * name="cost",
     * in="query",
     * description="Product Cost",
     * required=true,
     * @OA\Schema(
     *              type="number"
     *          )
     * ),
     * @OA\Parameter(
     * name="price",
     * in="query",
     * description="Product Selling Price",
     * required=true,
     * @OA\Schema(
     *              type="number"
     *          )
     * ),
     * @OA\Parameter(
     * name="qty",
     * in="query",
     * description="Stock Qty",
     * required=true,
     * @OA\Schema(
     *              type="integer"
     *          )
     * ),
     * @OA\Parameter(
     * name="stockthreshold",
     * in="query",
     * description="Stock Threshold",
     * @OA\Schema(
     *              type="integer"
     *          )
     * ),
     * @OA\Parameter(
     * name="onsale",
     * in="query",
     * description="On Sale",
     * required=true,
     * @OA\Schema(
     *              type="integer"
     *          )
     * ),
     * @OA\Parameter(
     *     name="_method",
     *     in="query",
     *     description="For spoofing purposes.",
     *     required=false,
     *     example="PUT",
     *     @OA\Schema(type="string")
     *    ),
     *   @OA\Response(
     *     response=200,
     *     description="Payment has been created successfully."
     *   ),
     *   @OA\Response(
     *     response="default",
     *     description="Unable to create the payment."
     *   )
     * )
     */
    public function update(Request $request, $uid)
    {
        error_log("test");
        $proccessingimgids = collect();
        DB::beginTransaction();
        // api/payment/{paymentid} (PUT)
        error_log($this->controllerName.'Updating payment of uid: ' . $uid);
        $this->validate($request, [
            'store_id' => 'required',
            'name' => 'required|string|max:191',
            'code' => 'nullable',
            'sku' => 'required|string|max:191',
            'desc' => 'nullable',
            'cost' => 'required|numeric|min:0',
            'price' => 'required|numeric|min:0',
            'qty' => 'required|numeric|min:0',
            'onsale' => 'required|numeric',
        ]);

        $payment = $this->getPayment($uid);
        if ($this->isEmpty($payment)) {
            DB::rollBack();
            return $this->notFoundResponse('Payment');
        }

        $params = collect([
            'store_id' => $request->store_id,
            'product_promotion_id' => $request->product_promotion_id,
            'warranty_id' => $request->warranty_id,
            'shipping_id' => $request->shipping_id,
            'name' => $request->name,
            'code' => $request->code,
            'sku' => $request->sku,
            'desc' => $request->desc,
            'imgpath' => $request->imgpath,
            'cost' => $request->cost,
            'price' => $request->price,
            'qty' => $request->qty,
            'stockthreshold' => $request->stockthreshold,
            'onsale' => $request->onsale,
        ]);
        $params = json_decode(json_encode($params));

        //Updating payment
        $payment = $this->updatePayment($payment, $params);
        if($this->isEmpty($payment)){
            DB::rollBack();
            $this->deleteImages($proccessingimgids);
            return $this->errorResponse();
        }


        //Associating Image Relationship
        if($request->file('img') != null){
            error_log($request->img);
            error_log($request->file('img'));
            $img = $this->uploadImage($request->file('img') , "/Payment/". $payment->uid);
            if(!$this->isEmpty($img)){
                //Delete Previous Image
                if($payment->imgpublicid){
                    if(!$this->deletePaymentImage($payment->imgpublicid)){
                        DB::rollBack();
                        $this->deleteImages($proccessingimgids);
                        return $this->errorResponse();
                    }
                }

                $payment->imgpath = $img->imgurl;
                $payment->imgpublicid = $img->publicid;
                $proccessingimgids->push($img->publicid);
                if(!$this->saveModel($payment)){
                    DB::rollBack();
                    $this->deleteImages($proccessingimgids);
                    return $this->errorResponse();
                }
                //Attach Image to PaymentImage
                $paymentimage = $this->associateImageWithPayment($payment , $img);
                if($this->isEmpty($paymentimage)){
                    DB::rollBack();
                    $this->deleteImages($proccessingimgids);
                    return $this->errorResponse();
                }
            }else{
                DB::rollBack();
                $this->deleteImages($proccessingimgids);
                return $this->errorResponse();
            }
        }

        //Updating sliders
        // $count = $payment->paymentimage()->count();
        // if($request->file('sliders') != null){
        //     error_log($request->sliders);
        //     error_log($request->file('sliders'));
        //     foreach($sliders as $slider){
        //         $count++;
        //         if($count > 6){
        //             break;
        //         }
        //         $img = $this->uploadImage($slider , "/Payment/". $payment->uid . "/sliders");
        //         if(!$this->isEmpty($img)){
        //             $proccessingimgids->push($img->publicid);
        //             if(!$this->saveModel($payment)){
        //                 DB::rollBack();
        //                 $this->deleteImages($proccessingimgids);
        //                 return $this->errorResponse();
        //             }
        //             //Attach Image to PaymentImage
        //             $paymentimage = $this->associateImageWithPayment($payment , $img);
        //             if($this->isEmpty($paymentimage)){
        //                 DB::rollBack();
        //                 $this->deleteImages($proccessingimgids);
        //                 return $this->errorResponse();
        //             }
        //         }else{
        //             DB::rollBack();
        //             $this->deleteImages($proccessingimgids);
        //             return $this->errorResponse();
        //         }
        //     }
        // }

        //Associating Payment Family Relationship

        $paymentfamilies = collect(json_decode($request->paymentfamilies));
        $originvfamiliesids = $payment->paymentfamilies()->pluck('id');
        $paymentfamiliesids = $paymentfamilies->pluck('id');
        //get ids not in list previously
        $forinsertids = $paymentfamiliesids->diff($originvfamiliesids);
        //get ids that not longer in payment families
        $fordeleteids = $originvfamiliesids->diff($paymentfamiliesids);

        foreach($forinsertids as $id){
            $paymentfamily = $this->getPaymentFamilyById($id);
            if($this->isEmpty($paymentfamily)){
                 DB::rollBack();
                 $this->deleteImages($proccessingimgids);
                 return $this->notFoundResponse('PaymentFamily');
             }
            $paymentfamily->payment()->associate($payment);
        }

        foreach($fordeleteids as $id){
            $paymentfamily = $this->getPaymentFamilyById($id);
            if($this->isEmpty($paymentfamily)){
                 DB::rollBack();
                 $this->deleteImages($proccessingimgids);
                 return $this->notFoundResponse('PaymentFamily');
             }
            if(!$this->deletePaymentFamily($paymentfamily)){
                DB::rollBack();
                $this->deleteImages($proccessingimgids);
                return $this->errorResponse();
            }
        }


        $this->createLog($request->user()->id , [$payment->id], 'update', 'payment');
        DB::commit();

        return $this->successResponse('Payment', $payment, 'update');
    }

    /**
     * @OA\Delete(
     *   tags={"PaymentControllerService"},
     *   path="/api/payment/{uid}",
     *   summary="Set payment's 'status' to 0.",
     *     operationId="deletePaymentByUid",
     *   @OA\Parameter(
     *     name="uid",
     *     in="path",
     *     description="Payment ID, NOT 'ID'.",
     *     required=true,
     *     @OA\SChema(type="string")
     *   ),
     *   @OA\Response(
     *     response=200,
     *     description="Payment has been 'deleted' successfully."
     *   ),
     *   @OA\Response(
     *     response="default",
     *     description="Unable to 'delete' the payment."
     *   )
     * )
     */
    public function destroy(Request $request, $uid)
    {
        DB::beginTransaction();
        // TODO ONLY TOGGLES THE status = 1/0
        // api/payment/{paymentid} (DELETE)
        error_log('Deleting payment of uid: ' . $uid);
        $payment = $this->getPayment($uid);
        if ($this->isEmpty($payment)) {
            DB::rollBack();
            return $this->notFoundResponse('Payment');
        }
        $payment = $this->deletePayment($payment);
        if ($this->isEmpty($payment)) {
            DB::rollBack();
            return $this->errorResponse();
        } else {
            $this->createLog($request->user()->id , [$payment->id], 'delete', 'payment');
            DB::commit();
            return $this->successResponse('Payment', $payment, 'delete');
        }
    }


    /**
     * @OA\Get(
     *   tags={"PaymentControllerService"},
     *   path="/api/payment/{uid}/onsale",
     *   summary="Retrieves onsale payment by Uid.",
     *     operationId="getOnSalePaymentByUid",
     *   @OA\Parameter(
     *     name="uid",
     *     in="path",
     *     description="Payment_ID, NOT 'ID'.",
     *     required=true,
     *     @OA\Schema(type="string")
     *   ),
     *   @OA\Response(
     *     response=200,
     *     description="Payment has been retrieved successfully."
     *   ),
     *   @OA\Response(
     *     response="default",
     *     description="Unable to retrieve the payment."
     *   )
     * )
     */
    public function getOnSalePayment(Request $request, $uid)
    {
        // api/payment/{paymentid} (GET)
        error_log($this->controllerName.'Retrieving onsale payment of uid:' . $uid);
        $cols = $this->paymentDefaultCols();
        $payment = $this->getPayment($uid);
        if($payment->onsale){
            $payment = $this->itemPluckCols($payment , $cols);
            $payment = json_decode(json_encode($payment));
            $payment = $this->calculatePromotionPrice($payment);
            $payment = $this->countProductReviews($payment);
        }else{
            $payment = null;
        }
        if ($this->isEmpty($payment)) {
            return $this->notFoundResponse('Payment');
        } else {
            return $this->successResponse('Payment', $payment, 'retrieve');
        }
    }
}
