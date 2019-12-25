<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use DB;
use Carbon\Carbon;
use App\InventoryImage;
use Illuminate\Support\Facades\Hash;
use App\Traits\GlobalFunctions;
use App\Traits\NotificationFunctions;
use App\Traits\InventoryImageServices;
use App\Traits\LogServices;

class InventoryImageController extends Controller
{
    use GlobalFunctions, NotificationFunctions, InventoryImageServices, LogServices;
    private $controllerName = '[InventoryImageController]';
/**
     * @OA\Get(
     *      path="/api/inventoryimage",
     *      operationId="getInventoryImages",
     *      tags={"InventoryImageControllerService"},
     *      summary="Get list of inventoryimages",
     *      description="Returns list of inventoryimages",
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
     *          description="Successfully retrieved list of inventoryimages"
     *       ),
     *       @OA\Response(
     *          response="default",
     *          description="Unable to retrieve list of inventoryimages")
     *    )
     */
    public function index(Request $request)
    {
        error_log('Retrieving list of inventoryimages.');
        // api/inventoryimage (GET)
        $inventoryimages = $this->getInventoryImages($request->user());
       
        if ($this->isEmpty($inventoryimages)) {
            return $this->errorPaginateResponse('InventoryImages');
        } else {
            return $this->successPaginateResponse('InventoryImages', $inventoryimages, $this->toInt($request->pageSize), $this->toInt($request->pageNumber));
        }
    }
    
    /**
     * @OA\Get(
     *      path="/api/filter/inventoryimage",
     *      operationId="filterInventoryImages",
     *      tags={"InventoryImageControllerService"},
     *      summary="Filter list of inventoryimages",
     *      description="Returns list of filtered inventoryimages",
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
     *     name="status",
     *     in="query",
     *     description="status for filter",
     *     @OA\Schema(type="string")
     *   ),
     *      @OA\Response(
     *          response=200,
     *          description="Successfully retrieved list of filtered inventoryimages"
     *       ),
     *       @OA\Response(
     *          response="default",
     *          description="Unable to retrieve list of inventoryimages")
     *    )
     */
    public function filter(Request $request)
    {
        error_log('Retrieving list of filtered inventoryimages.');
        // api/inventoryimage/filter (GET)
        $params = collect([
            'keyword' => $request->keyword,
            'fromdate' => $request->fromdate,
            'todate' => $request->todate,
            'status' => $request->status,
            'inventoryimage_id' => $request->inventoryimage_id,
        ]);
        //Convert To Json Object
        $params = json_decode(json_encode($params));
        $inventoryimages = $this->getInventoryImages($request->user());
        $inventoryimages = $this->filterInventoryImages($inventoryimages, $params);

        if ($this->isEmpty($inventoryimages)) {
            return $this->errorPaginateResponse('InventoryImages');
        } else {
            return $this->successPaginateResponse('InventoryImages', $inventoryimages, $this->toInt($request->pageSize), $this->toInt($request->pageNumber));
        }

    }

   
    /**
     * @OA\Get(
     *   tags={"InventoryImageControllerService"},
     *   path="/api/inventoryimage/{uid}",
     *   summary="Retrieves inventoryimage by Uid.",
     *     operationId="getInventoryImageByUid",
     *   @OA\Parameter(
     *     name="uid",
     *     in="path",
     *     description="InventoryImage_ID, NOT 'ID'.",
     *     required=true,
     *     @OA\Schema(type="string")
     *   ),
     *   @OA\Response(
     *     response=200,
     *     description="InventoryImage has been retrieved successfully."
     *   ),
     *   @OA\Response(
     *     response="default",
     *     description="Unable to retrieve the inventoryimage."
     *   )
     * )
     */
    public function show(Request $request, $uid)
    {
        // api/inventoryimage/{inventoryimageid} (GET)
        error_log('Retrieving inventoryimage of uid:' . $uid);
        $inventoryimage = $this->getInventoryImage($uid);
        if ($this->isEmpty($inventoryimage)) {
            return $this->notFoundResponse('InventoryImage');
        } else {
            return $this->successResponse('InventoryImage', $inventoryimage, 'retrieve');
        }
    }

  
      
    /**
     * @OA\Post(
     *   tags={"InventoryImageControllerService"},
     *   path="/api/inventoryimage",
     *   summary="Creates a inventoryimage.",
     *   operationId="createInventoryImage",
     * @OA\Parameter(
     * name="inventory_id",
     * in="query",
     * description="Inventory Id",
     * required=true,
     * @OA\Schema(
     *              type="integer"
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
     *   @OA\Response(
     *     response=200,
     *     description="InventoryImage has been created successfully."
     *   ),
     *   @OA\Response(
     *     response="default",
     *     description="Unable to create the inventoryimage."
     *   )
     * )
     */
    public function store(Request $request)
    {
        $proccessingimgids = collect();
        DB::beginTransaction();
        // Can only be used by Authorized personnel
        // api/inventoryimage (POST)
        
        $this->validate($request, [
            'img' => 'required',
            'inventory_id' => 'required',
        ]);
        error_log('Creating inventoryimage.');
        
        if($request->file('img') != null){
            $img = $this->uploadImage($request->file('img') , "/Inventory/". $inventory->uid);
            if(!$this->isEmpty($img)){
                $params = collect([
                    'name' => $request->name,
                    'desc' => $request->desc,
                    'imgpath' => $img->imgurl,
                    'imgpublicid' => $img->publicid,
                    'inventory_id' => $request->inventory_id,
                ]);
                $proccessingimgids->push($img->publicid);
            }else{
                DB::rollBack();
                $this->deleteImages($proccessingimgids);
                return $this->errorResponse();
            }
        }
        
        //Convert To Json Object
        $params = json_decode(json_encode($params));
        $inventoryimage = $this->createInventoryImage($params);

        if ($this->isEmpty($inventoryimage)) {
            DB::rollBack();
            return $this->errorResponse();
        } else {
            DB::commit();
            return $this->successResponse('InventoryImage', $inventoryimage, 'create');
        }
    }


    /**
     * @OA\Put(
     *   tags={"InventoryImageControllerService"},
     *   path="/api/inventoryimage/{uid}",
     *   summary="Update inventoryimage by Uid.",
     *     operationId="updateInventoryImageByUid",
     *   @OA\Parameter(
     *     name="uid",
     *     in="path",
     *     description="InventoryImage_ID, NOT 'ID'.",
     *     required=true,
     *     @OA\Schema(type="string")
     *   ),
     * @OA\Parameter(
     * name="name",
     * in="query",
     * description="InventoryImagename",
     * required=true,
     * @OA\Schema(
     *              type="string"
     *          )
     * ),
     * @OA\Parameter(
     * name="desc",
     * in="query",
     * description="InventoryImage Description",
     * @OA\Schema(
     *              type="string"
     *          )
     * ),
     * @OA\Parameter(
     * name="provider",
     * in="query",
     * description="Provider of Model",
     * required=true,
     * @OA\Schema(
     *              type="string"
     *          )
     * ),
     *   @OA\Response(
     *     response=200,
     *     description="InventoryImage has been updated successfully."
     *   ),
     *   @OA\Response(
     *     response="default",
     *     description="Unable to update the inventoryimage."
     *   )
     * )
     */
    public function update(Request $request, $uid)
    {
        DB::beginTransaction();
        // api/inventoryimage/{inventoryimageid} (PUT) 
        error_log('Updating inventoryimage of uid: ' . $uid);
        $inventoryimage = $this->getInventoryImage($uid);
       
       
        $this->validate($request, [
            'name' => 'required|string|max:191',
            'desc' => 'nullable',
            'provider' => 'required|string|max:191',
        ]);
      
        if ($this->isEmpty($inventoryimage)) {
            DB::rollBack();
            return $this->notFoundResponse('InventoryImage');
        }
        
        $params = collect([
            'name' => $request->name,
            'desc' => $request->desc,
            'provider' => $request->provider,
        ]);
        
        //Convert To Json Object
        $params = json_decode(json_encode($params));
        $inventoryimage = $this->updateInventoryImage($inventoryimage, $params);
        if ($this->isEmpty($inventoryimage)) {
            DB::rollBack();
            return $this->errorResponse();
        } else {
            DB::commit();
            return $this->successResponse('InventoryImage', $inventoryimage, 'update');
        }
    }


    /**
     * @OA\Delete(
     *   tags={"InventoryImageControllerService"},
     *   path="/api/inventoryimage/{uid}",
     *   summary="Set inventoryimage's 'status' to 0.",
     *     operationId="deleteInventoryImageByUid",
     *   @OA\Parameter(
     *     name="uid",
     *     in="path",
     *     description="InventoryImage ID, NOT 'ID'.",
     *     required=true,
     *     @OA\SChema(type="string")
     *   ),
     *   @OA\Response(
     *     response=200,
     *     description="InventoryImage has been 'deleted' successfully."
     *   ),
     *   @OA\Response(
     *     response="default",
     *     description="Unable to 'delete' the inventoryimage."
     *   )
     * )
     */
    public function destroy(Request $request, $uid)
    {
        DB::beginTransaction();
        // TODO ONLY TOGGLES THE status = 1/0
        // api/inventoryimage/{inventoryimageid} (DELETE)
        error_log('Deleting inventoryimage of uid: ' . $uid);
        $inventoryimage = $this->getInventoryImage($uid);
        if ($this->isEmpty($inventoryimage)) {
            DB::rollBack();
            return $this->notFoundResponse('InventoryImage');
        }

        if ($this->forceDeleteModel($inventoryimage)) {
            DB::rollBack();
            return $this->errorResponse();
        } else {
            DB::commit();
            return $this->successResponse('InventoryImage', null, 'delete');
        }
    }


}
