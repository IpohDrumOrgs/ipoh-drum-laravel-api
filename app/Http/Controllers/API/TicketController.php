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

    /**
     * @OA\Get(
     *      path="/api/ticket",
     *      operationId="getTicketList",
     *      tags={"TicketControllerService"},
     *      summary="Get list of tickets",
     *      description="Returns list of tickets",
     *   @OA\Parameter(
     *     name="pageNumber",
     *     in="query",
     *     description="Page number.",
     *     @OA\Schema(type="integer")
     *   ),
     *   @OA\Parameter(
     *     name="pageSize",
     *     in="query",
     *     description="Page size.",
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
        $tickets = $this->getTicketListing($request->user());
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
     *      path="/api/pluck/tickets",
     *      operationId="pluckTicketList",
     *      tags={"TicketControllerService"},
     *      summary="pluck list of tickets",
     *      description="Returns list of plucked tickets",
     *   @OA\Parameter(
     *     name="pageNumber",
     *     in="query",
     *     description="Page number",
     *     @OA\Schema(type="integer")
     *   ),
     *   @OA\Parameter(
     *     name="pageSize",
     *     in="query",
     *     description="Page size",
     *     @OA\Schema(type="integer")
     *   ),
     *   @OA\Parameter(
     *     name="cols",
     *     in="query",
     *     required=true,
     *     description="Columns for pluck",
     *     @OA\Schema(type="string")
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
    public function pluckIndex(Request $request)
    {
        error_log('Retrieving list of plucked tickets.');
        // api/pluck/tickets (GET)
        error_log("columns = " . collect($this->splitToArray($request->cols)));
        $tickets = $this->pluckTicketIndex($this->splitToArray($request->cols));
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
     *      operationId="filterTicketList",
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
     *     description="Page size",
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
     *     description="To string for filter",
     *     @OA\Schema(type="string")
     *   ),
     *   @OA\Parameter(
     *     name="status",
     *     in="query",
     *     description="status for filter",
     *     @OA\Schema(type="string")
     *   ),
     *   @OA\Parameter(
     *     name="onsale",
     *     in="query",
     *     description="onsale for filter",
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
        ]);
        //Convert To Json Object
        $params = json_decode(json_encode($params));
        $tickets = $this->filterTicketListing($request->user(), $params);

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
     *      path="/api/pluck/filter/ticket",
     *      operationId="filterPluckedTicketList",
     *      tags={"TicketControllerService"},
     *      summary="Filter list of plucked tickets",
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
     *     description="Page size",
     *     @OA\Schema(type="integer")
     *   ),
     *   @OA\Parameter(
     *     name="cols",
     *     in="query",
     *     required=true,
     *     description="Columns for pluck",
     *     @OA\Schema(type="string")
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
     *     description="To string for filter",
     *     @OA\Schema(type="string")
     *   ),
     *   @OA\Parameter(
     *     name="status",
     *     in="query",
     *     description="status for filter",
     *     @OA\Schema(type="string")
     *   ),
     *   @OA\Parameter(
     *     name="onsale",
     *     in="query",
     *     description="onsale for filter",
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
    public function pluckFilter(Request $request)
    {
        error_log('Retrieving list of filtered and plucked tickets.');
        // api/pluck/filter/ticket (GET)
        $params = collect([
            'keyword' => $request->keyword,
            'fromdate' => $request->fromdate,
            'todate' => $request->todate,
            'status' => $request->status,
            'onsale' => $request->onsale,
        ]);
        //Convert To Json Object
        $params = json_decode(json_encode($params));
        $tickets = $this->pluckTicketFilter($this->splitToArray($request->cols) , $params);

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
        $ticket = $this->getTicket($request->user(), $uid);
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
     * @OA\Get(
     *      path="/api/pluck/ticket/{uid}",
     *      operationId="pluckTicketByUid",
     *      tags={"TicketControllerService"},
     *      summary="pluck ticket",
     *      description="Returns plucked tickets",
     *   @OA\Parameter(
     *     name="uid",
     *     in="path",
     *     description="Ticket_ID, NOT 'ID'.",
     *     required=true,
     *     @OA\Schema(type="string")
     *   ),
     *   @OA\Parameter(
     *     name="cols",
     *     in="query",
     *     required=true,
     *     description="Columns for pluck",
     *     @OA\Schema(type="string")
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
    public function pluckShow(Request $request , $uid)
    {
        error_log('Retrieving plucked tickets.');
        // api/pluck/ticket/{uid} (GET)
        error_log("columns = " . collect($this->splitToArray($request->cols)));
        $ticket = $this->pluckTicket($this->splitToArray($request->cols) , $uid);
        if ($this->isEmpty($ticket)) {
            $data['data'] = null;
            $data['status'] = 'error';
            $data['msg'] = $this->getNotFoundMsg('Ticket');
            $data['code'] = 404;
            return response()->json($data, 404);
        } else {
            $data['status'] = 'success';
            $data['msg'] = $this->getRetrievedSuccessMsg('Ticket');
            $data['data'] = $ticket;
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
     * name="price",
     * in="query",
     * description="Product Selling Price",
     * required=true,
     * @OA\Schema(
     *              type="number"
     *          )
     * ),
     * @OA\Parameter(
     * name="disc",
     * in="query",
     * description="Product Discount",
     * @OA\Schema(
     *              type="number"
     *          )
     * ),
     * @OA\Parameter(
     * name="promoprice",
     * in="query",
     * description="Promotion Price",
     * @OA\Schema(
     *              type="string"
     *          )
     * ),
     * @OA\Parameter(
     * name="promostartdate",
     * in="query",
     * description="Promotion Start Date",
     * @OA\Schema(
     *              type="string"
     *          )
     * ),
     * @OA\Parameter(
     * name="promoenddate",
     * in="query",
     * description="Promotion End Date",
     * @OA\Schema(
     *              type="string"
     *          )
     * ),
     * @OA\Parameter(
     * name="enddate",
     * in="query",
     * description="Ticket End Date",
     * required=true,
     * @OA\Schema(
     *              type="string"
     *          )
     * ),
     * @OA\Parameter(
     * name="stock",
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
            'price' => 'required|numeric|min:0',
            'disc' => 'nullable|numeric|min:0',
            'promoprice' => 'nullable|numeric|min:0',
            'promostartdate' => 'nullable|date',
            'promoenddate' => 'nullable|date',
            'enddate' =>  'required|date',
            'stock' => 'required|numeric|min:0',
            'stockthreshold' => 'nullable|numeric|min:0',
            'onsale' => 'required|boolean',
        ]);
        error_log('Creating ticket.');
        $params = collect([
            'storeid' => $request->storeid,
            'name' => $request->name,
            'code' => $request->code,
            'sku' => $request->sku,
            'desc' => $request->desc,
            'price' => $request->price,
            'disc' => $request->disc,
            'promoprice' => $request->promoprice,
            'promostartdate' => $request->promostartdate,
            'promoenddate' => $request->promoenddate,
            'enddate' => $request->enddate,
            'stock' => $request->stock,
            'stockthreshold' => $request->stockthreshold,
            'onsale' => $request->onsale,
        ]);
        //Convert To Json Object
        $params = json_decode(json_encode($params));
        $ticket = $this->createTicket($request->user(), $params);

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
     * name="price",
     * required=true,
     * in="query",
     * description="Product Selling Price",
     * @OA\Schema(
     *              type="number"
     *          )
     * ),
     * @OA\Parameter(
     * name="disc",
     * in="query",
     * description="Product Discount",
     * @OA\Schema(
     *              type="number"
     *          )
     * ),
     * @OA\Parameter(
     * name="promoprice",
     * in="query",
     * description="Promotion Price",
     * @OA\Schema(
     *              type="string"
     *          )
     * ),
     * @OA\Parameter(
     * name="promostartdate",
     * in="query",
     * description="Promotion Start Date",
     * @OA\Schema(
     *              type="string"
     *          )
     * ),
     * @OA\Parameter(
     * name="promoenddate",
     * in="query",
     * description="Promotion End Date",
     * @OA\Schema(
     *              type="string"
     *          )
     * ),
     * @OA\Parameter(
     * name="enddate",
     * in="query",
     * description="Ticket End Date",
     * required=true,
     * @OA\Schema(
     *              type="string"
     *          )
     * ),
     * @OA\Parameter(
     * name="stock",
     * in="query",
     * required=true,
     * description="Stock Qty",
     * @OA\Schema(
     *              type="integer"
     *          )
     * ),
     * @OA\Parameter(
     * name="stockthreshold",
     * in="query",
     * required=true,
     * description="Stock Threshold",
     * @OA\Schema(
     *              type="integer"
     *          )
     * ),
     * @OA\Parameter(
     * name="onsale",
     * in="query",
     * required=true,
     * description="On Sale",
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
        error_log('Updating ticket of uid: ' . $uid);
        $ticket = $this->getTicket($request->user(), $uid);
       
        $this->validate($request, [
            'storeid' => 'required',
            'name' => 'required|string|max:191',
            'code' => 'nullable',
            'sku' => 'required|string|max:191',
            'desc' => 'nullable',
            'price' => 'required|numeric|min:0',
            'disc' => 'nullable|numeric|min:0',
            'promoprice' => 'nullable|numeric|min:0',
            'promostartdate' => 'nullable|date',
            'promoenddate' => 'nullable|date',
            'enddate' =>  'required|date',
            'stock' => 'required|numeric|min:0',
            'stockthreshold' => 'nullable|numeric|min:0',
            'onsale' => 'required|boolean',
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
            'name' => $request->name,
            'code' => $request->code,
            'sku' => $request->sku,
            'desc' => $request->desc,
            'price' => $request->price,
            'disc' => $request->disc,
            'promoprice' => $request->promoprice,
            'promostartdate' => $request->promostartdate,
            'promoenddate' => $request->promoenddate,
            'enddate' => $request->enddate,
            'stock' => $request->stock,
            'stockthreshold' => $request->stockthreshold,
            'onsale' => $request->onsale,
        ]);
        
        //Convert To Json Object
        $params = json_decode(json_encode($params));
        $ticket = $this->updateTicket($request->user(), $ticket, $params);
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
        $ticket = $this->getTicket($request->user(), $uid);
        if ($this->isEmpty($ticket)) {
            DB::rollBack();
            $data['status'] = 'error';
            $data['msg'] = $this->getNotFoundMsg('Ticket');
            $data['data'] = null;
            $data['code'] = 404;
            return response()->json($data, 404);
        }
        $ticket = $this->deleteTicket($request->user(), $ticket->id);
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
            $data['data'] = $ticket;
            $data['code'] = 200;
            return response()->json($data, 200);
        }
    }

}
