<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use DB;
use Carbon\Carbon;
use App\Ticket;
use Illuminate\Support\Facades\Hash;
use App\Traits\GlobalFunctions;
use App\Traits\NotificationFunctions;
use App\Traits\TicketServices;
use App\Traits\LogServices;

class TicketController extends Controller
{
    use GlobalFunctions, NotificationFunctions, TicketServices, LogServices;
    private $controllerName = '[TicketController]';
    /**
     * @OA\Get(
     *      path="/api/ticket",
     *      operationId="getTickets",
     *      tags={"TicketControllerService"},
     *      summary="Get list of tickets",
     *      description="Returns list of tickets",
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
     *          description="Successfully retrieved list of tickets"
     *       ),
     *       @OA\Response(
     *          response="default",
     *          description="Unable to retrieve list of tickets")
     *    )
     */
    public function index(Request $request)
    {
        error_log('Retrieving list of tickets.');
        // api/ticket (GET)
        $tickets = $this->getTickets($request->user());
        if ($this->isEmpty($tickets)) {
            $data['status'] = 'error';
            $data['data'] = null;
            $data['maximumPages'] = 0;
            $data['msg'] = $this->getNotFoundMsg('Tickets');
            $data['code'] = 404;
            return response()->json($data, 404);
        } else {
            //Page Pagination Result List
            //Default return 10
            $paginateddata = $this->paginateResult($tickets, $request->pageSize, $request->pageNumber);
            $data['status'] = 'success';
            $data['data'] = $paginateddata;
            $data['maximumPages'] = $this->getMaximumPaginationPage($tickets->count(), $request->pageSize);
            $data['msg'] = $this->getRetrievedSuccessMsg('Tickets');
            $data['code'] = 200;
            return response()->json($data, 200);
        }
    }
    
    /**
     * @OA\Get(
     *      path="/api/filter/ticket",
     *      operationId="filterTickets",
     *      tags={"TicketControllerService"},
     *      summary="Filter list of tickets",
     *      description="Returns list of filtered tickets",
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
     *          description="Successfully retrieved list of filtered tickets"
     *       ),
     *       @OA\Response(
     *          response="default",
     *          description="Unable to retrieve list of tickets")
     *    )
     */
    public function filter(Request $request)
    {
        error_log('Retrieving list of filtered tickets.');
        // api/ticket/filter (GET)
        $params = collect([
            'keyword' => $request->keyword,
            'fromdate' => $request->fromdate,
            'todate' => $request->todate,
            'status' => $request->status,
            'onsale' => $request->onsale,
            'ticket_id' => $request->ticket_id,
        ]);
        //Convert To Json Object
        $params = json_decode(json_encode($params));
        $tickets = $this->getTickets($request->user());
        $tickets = $this->filterTickets($tickets, $params);

        if ($this->isEmpty($tickets)) {
            $data['data'] = null;
            $data['maximumPages'] = 0;
            $data['msg'] = $this->getNotFoundMsg('Tickets');
            $data['code'] = 404;
            return response()->json($data, 404);
        } else {
            //Page Pagination Result List
            //Default return 10
            $paginateddata = $this->paginateResult($tickets, $request->pageSize, $request->pageNumber);
            $data['data'] = $paginateddata;
            $data['maximumPages'] = $this->getMaximumPaginationPage($tickets->count(), $request->pageSize);
            $data['msg'] = $this->getRetrievedSuccessMsg('Tickets');
            $data['code'] = 200;
            return response()->json($data, 200);
        }

    }

   
    /**
     * @OA\Get(
     *   tags={"TicketControllerService"},
     *   path="/api/ticket/{uid}",
     *   summary="Retrieves ticket by Uid.",
     *     operationId="getTicketByUid",
     *   @OA\Parameter(
     *     name="uid",
     *     in="path",
     *     description="Ticket_ID, NOT 'ID'.",
     *     required=true,
     *     @OA\Schema(type="string")
     *   ),
     *   @OA\Response(
     *     response=200,
     *     description="Ticket has been retrieved successfully."
     *   ),
     *   @OA\Response(
     *     response="default",
     *     description="Unable to retrieve the ticket."
     *   )
     * )
     */
    public function show(Request $request, $uid)
    {
        // api/ticket/{ticketid} (GET)
        error_log('Retrieving ticket of uid:' . $uid);
        $ticket = $this->getTicket($uid);
        if ($this->isEmpty($ticket)) {
            $data['data'] = null;
            $data['msg'] = $this->getNotFoundMsg('Ticket');
            $data['status'] = 'error';
            $data['code'] = 404;
            return response()->json($data, 404);
        } else {
            $data['data'] = $ticket;
            $data['msg'] = $this->getRetrievedSuccessMsg('Ticket');
            $data['status'] = 'success';
            $data['code'] = 200;
            return response()->json($data, 200);
        }
    }

  
    
    /**
     * @OA\Post(
     *   tags={"TicketControllerService"},
     *   path="/api/ticket",
     *   summary="Creates a ticket.",
     *   operationId="createTicket",
     
     * @OA\Parameter(
     * name="name",
     * in="query",
     * description="Ticketname",
     * required=true,
     * @OA\Schema(
     *              type="string"
     *          )
     * ),
     * @OA\Parameter(
     * name="storeid",
     * in="query",
     * description="Store ID",
     * required=true,
     * @OA\Schema(
     *              type="integer"
     *          )
     * ),
     * @OA\Parameter(
     * name="promotionid",
     * in="query",
     * description="Promotion ID",
     * required=true,
     * @OA\Schema(
     *              type="integer"
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
     * required=true,
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
     * @OA\Parameter(
     * name="imgpath",
     * in="query",
     * description="Image Path",
     * @OA\Schema(
     *              type="string"
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
     * name="enddate",
     * in="query",
     * description="Valid end date",
     * required=true,
     * @OA\Schema(
     *              type="string"
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
     *   @OA\Response(
     *     response=200,
     *     description="Ticket has been created successfully."
     *   ),
     *   @OA\Response(
     *     response="default",
     *     description="Unable to create the ticket."
     *   )
     * )
     */
    public function store(Request $request)
    {
        DB::beginTransaction();
        // Can only be used by Authorized personnel
        // api/ticket (POST)

        $this->validate($request, [
            'storeid' => 'required',
            'name' => 'required|string|max:191',
            'code' => 'nullable',
            'sku' => 'required|string|max:191',
            'desc' => 'nullable',
            'cost' => 'required|numeric|min:0',
            'price' => 'required|numeric|min:0',
            'qty' => 'required|numeric|min:0',
            'onsale' => 'required|numeric',
        ]);
        error_log($this->controllerName.'Creating ticket.');
        $params = collect([
            'storeid' => $request->storeid,
            'promotionid' => $request->promotionid,
            'name' => $request->name,
            'code' => $request->code,
            'sku' => $request->sku,
            'imgpath' => $request->imgpath,
            'desc' => $request->desc,
            'price' => $request->price,
            'enddate' => $request->enddate,
            'qty' => $request->qty,
            'stockthreshold' => $request->stockthreshold,
            'onsale' => $request->onsale,
        ]);
        //Convert To Json Object
        $params = json_decode(json_encode($params));
        $ticket = $this->createTicket($params);

        if ($this->isEmpty($ticket)) {
            DB::rollBack();
            $data['data'] = null;
            $data['status'] = 'error';
            $data['msg'] = $this->getErrorMsg();
            $data['code'] = 404;
            return response()->json($data, 404);
        } else {
            DB::commit();
            $data['status'] = 'success';
            $data['msg'] = $this->getCreatedSuccessMsg('Ticket');
            $data['data'] = $ticket;
            $data['code'] = 200;
            return response()->json($data, 200);
        }
    }


    /**
     * @OA\Put(
     *   tags={"TicketControllerService"},
     *   path="/api/ticket/{uid}",
     *   summary="Update ticket by Uid.",
     *     operationId="updateTicketByUid",
     *   @OA\Parameter(
     *     name="uid",
     *     in="path",
     *     description="Ticket_ID, NOT 'ID'.",
     *     required=true,
     *     @OA\Schema(type="string")
     *   ),
     * @OA\Parameter(
     * name="name",
     * in="query",
     * description="Ticketname",
     * required=true,
     * @OA\Schema(
     *              type="string"
     *          )
     * ),
     * @OA\Parameter(
     * name="storeid",
     * in="query",
     * description="Store ID",
     * required=true,
     * @OA\Schema(
     *              type="integer"
     *          )
     * ),
     * @OA\Parameter(
     * name="promotionid",
     * in="query",
     * description="Promotion ID",
     * required=true,
     * @OA\Schema(
     *              type="integer"
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
     * required=true,
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
     * @OA\Parameter(
     * name="imgpath",
     * in="query",
     * description="Image Path",
     * @OA\Schema(
     *              type="string"
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
     * name="enddate",
     * in="query",
     * description="Valid end date",
     * required=true,
     * @OA\Schema(
     *              type="string"
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
     *   @OA\Response(
     *     response=200,
     *     description="Ticket has been updated successfully."
     *   ),
     *   @OA\Response(
     *     response="default",
     *     description="Unable to update the ticket."
     *   )
     * )
     */
    public function update(Request $request, $uid)
    {
        DB::beginTransaction();
        // api/ticket/{ticketid} (PUT)
        error_log($this->controllerName.'Updating ticket of uid: ' . $uid);
        $ticket = $this->getTicket($uid);

        $this->validate($request, [
            'storeid' => 'required',
            'name' => 'required|string|max:191',
            'code' => 'nullable',
            'sku' => 'required|string|max:191',
            'desc' => 'nullable',
            'price' => 'required|numeric|min:0',
            'qty' => 'required|numeric|min:0',
            'onsale' => 'required|numeric',
            'enddate' => 'required|date',
        ]);

        if ($this->isEmpty($ticket)) {
            DB::rollBack();
            $data['data'] = null;
            $data['msg'] = $this->getNotFoundMsg('Ticket');
            $data['status'] = 'error';
            $data['code'] = 404;
            return response()->json($data, 404);
        }

        $params = collect([
            'storeid' => $request->storeid,
            'promotionid' => $request->promotionid,
            'name' => $request->name,
            'code' => $request->code,
            'sku' => $request->sku,
            'imgpath' => $request->imgpath,
            'desc' => $request->desc,
            'price' => $request->price,
            'enddate' => $request->enddate,
            'qty' => $request->qty,
            'stockthreshold' => $request->stockthreshold,
            'onsale' => $request->onsale,
        ]);

        //Convert To Json Object
        $params = json_decode(json_encode($params));
        $ticket = $this->updateTicket($ticket, $params);
        if ($this->isEmpty($ticket)) {
            DB::rollBack();
            $data['data'] = null;
            $data['msg'] = $this->getErrorMsg('Ticket');
            $data['status'] = 'error';
            $data['code'] = 404;
            return response()->json($data, 404);
        } else {
            DB::commit();
            $data['status'] = 'success';
            $data['msg'] = $this->getUpdatedSuccessMsg('Ticket');
            $data['data'] = $ticket;
            $data['status'] = 'success';
            $data['code'] = 200;
            return response()->json($data, 200);
        }
    }

    /**
     * @OA\Delete(
     *   tags={"TicketControllerService"},
     *   path="/api/ticket/{uid}",
     *   summary="Set ticket's 'status' to 0.",
     *     operationId="deleteTicketByUid",
     *   @OA\Parameter(
     *     name="uid",
     *     in="path",
     *     description="Ticket ID, NOT 'ID'.",
     *     required=true,
     *     @OA\SChema(type="string")
     *   ),
     *   @OA\Response(
     *     response=200,
     *     description="Ticket has been 'deleted' successfully."
     *   ),
     *   @OA\Response(
     *     response="default",
     *     description="Unable to 'delete' the ticket."
     *   )
     * )
     */
    public function destroy(Request $request, $uid)
    {
        DB::beginTransaction();
        // TODO ONLY TOGGLES THE status = 1/0
        // api/ticket/{ticketid} (DELETE)
        error_log('Deleting ticket of uid: ' . $uid);
        $ticket = $this->getTicket($uid);
        if ($this->isEmpty($ticket)) {
            DB::rollBack();
            $data['status'] = 'error';
            $data['msg'] = $this->getNotFoundMsg('Ticket');
            $data['data'] = null;
            $data['code'] = 404;
            return response()->json($data, 404);
        }
        $ticket = $this->deleteTicket($ticket);
        $this->createLog($request->user()->id , [$ticket->id], 'delete', 'ticket');
        if ($this->isEmpty($ticket)) {
            DB::rollBack();
            $data['status'] = 'error';
            $data['msg'] = $this->getErrorMsg();
            $data['data'] = null;
            $data['code'] = 404;
            return response()->json($data, 404);
        } else {
            DB::commit();
            $data['status'] = 'success';
            $data['msg'] = $this->getDeletedSuccessMsg('Ticket');
            $data['data'] = null;
            $data['code'] = 200;
            return response()->json($data, 200);
        }
    }


}
