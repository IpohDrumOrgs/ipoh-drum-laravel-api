<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use DB;
use Carbon\Carbon;
use App\Slider;
use Illuminate\Support\Facades\Hash;
use App\Traits\AllServices;

class SliderController extends Controller
{
    use AllServices;

    private $controllerName = '[SliderController]';
    /**
     * @OA\Get(
     *      path="/api/slider",
     *      operationId="getSliders",
     *      tags={"SliderControllerService"},
     *      summary="Get list of sliders",
     *      description="Returns list of sliders",
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
     *          description="Successfully retrieved list of sliders"
     *       ),
     *       @OA\Response(
     *          response="default",
     *          description="Unable to retrieve list of sliders")
     *    )
     */
    public function index(Request $request)
    {
        error_log($this->controllerName.'Retrieving list of sliders.');
        // api/slider (GET)
        $sliders = $this->getSliders($request->user());
        if ($this->isEmpty($sliders)) {
            return $this->errorPaginateResponse('Sliders');
        } else {
            return $this->successPaginateResponse('Sliders', $sliders, $this->toInt($request->pageSize), $this->toInt($request->pageNumber));
        }
    }

    /**
     * @OA\Get(
     *      path="/api/filter/slider",
     *      operationId="filterSliders",
     *      tags={"SliderControllerService"},
     *      summary="Filter list of sliders",
     *      description="Returns list of filtered sliders",
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
     *     name="company_id",
     *     in="query",
     *     description="Company id for filter",
     *     @OA\Schema(type="string")
     *   ),
     *      @OA\Response(
     *          response=200,
     *          description="Successfully retrieved list of filtered sliders"
     *       ),
     *       @OA\Response(
     *          response="default",
     *          description="Unable to retrieve list of sliders")
     *    )
     */
    public function filter(Request $request)
    {
        error_log($this->controllerName.'Retrieving list of filtered sliders.');
        // api/slider/filter (GET)
        $params = collect([
            'keyword' => $request->keyword,
            'fromdate' => $request->fromdate,
            'todate' => $request->todate,
            'status' => $request->status,
            'company_id' => $request->company_id,
        ]);
        //Convert To Json Object
        $params = json_decode(json_encode($params));
        $sliders = $this->filterSliders($sliders, $params);

        if ($this->isEmpty($sliders)) {
            return $this->errorPaginateResponse('Sliders');
        } else {
            return $this->successPaginateResponse('Sliders', $sliders, $this->toInt($request->pageSize), $this->toInt($request->pageNumber));
        }

    }
    /**
     * @OA\Get(
     *   tags={"SliderControllerService"},
     *   path="/api/slider/{uid}",
     *   summary="Retrieves slider by Uid.",
     *     operationId="getSliderByUid",
     *   @OA\Parameter(
     *     name="uid",
     *     in="path",
     *     description="Slider_ID, NOT 'ID'.",
     *     required=true,
     *     @OA\Schema(type="string")
     *   ),
     *   @OA\Response(
     *     response=200,
     *     description="Slider has been retrieved successfully."
     *   ),
     *   @OA\Response(
     *     response="default",
     *     description="Unable to retrieve the slider."
     *   )
     * )
     */
    public function show(Request $request, $uid)
    {
        // api/slider/{sliderid} (GET)
        error_log($this->controllerName.'Retrieving slider of uid:' . $uid);
        $slider = $this->getSlider($uid);
        if ($this->isEmpty($slider)) {
            $data['data'] = null;
            return $this->notFoundResponse('Slider');
        } else {
            return $this->successResponse('Slider', $slider, 'retrieve');
        }
    }

    /**
     * @OA\Post(
     *   tags={"SliderControllerService"},
     *   path="/api/slider",
     *   summary="Creates a slider.",
     *   operationId="createSlider",
     * @OA\Parameter(
     * name="blogger_id",
     * in="query",
     * description="Slider belongs To which Blogger",
     * required=true,
     * @OA\Schema(
     *              type="integer"
     *          )
     * ),
     * @OA\Parameter(
     * name="title",
     * in="query",
     * description="Slider title",
     * required=true,
     * @OA\Schema(
     *              type="string"
     *          )
     * ),
     * @OA\Parameter(
     * name="desc",
     * in="query",
     * description="Slider description",
     * @OA\Schema(
     *              type="string"
     *          )
     * ),
     * @OA\Parameter(
     * name="scope",
     * in="query",
     * description="Is this slider public?",
     * @OA\Schema(
     *              type="string"
     *          )
     * ),
     * 	@OA\RequestBody(
*          @OA\MediaType(
*              mediaType="multipart/form-data",
*              @OA\Schema(
*                  @OA\Property(
*                      property="imgs[]",
*                      description="Slider Images",
*                      type="file",
*                      @OA\Items(type="string", format="binary")
*                   ),
*               ),
*           ),
*       ),
     *   @OA\Response(
     *     response=200,
     *     description="Slider has been created successfully."
     *   ),
     *   @OA\Response(
     *     response="default",
     *     description="Unable to create the slider."
     *   )
     * )
     */
    public function store(Request $request)
    {
        $proccessingimgids = collect();
        DB::beginTransaction();
        // Can only be used by Authorized personnel
        // api/slider (POST)
        $this->validate($request, [
            'title' => 'required|string',
            'blogger_id' => 'required|numeric',
        ]);
        error_log($this->controllerName.'Creating slider.');
        $params = collect([
            'blogger_id' => $request->blogger_id,
            'title' => $request->title,
            'desc' => $request->desc,
            'scope' => $request->scope,
        ]);
        //Convert To Json Object
        $params = json_decode(json_encode($params));
        $slider = $this->createSlider($params);
        if ($this->isEmpty($slider)) {
            DB::rollBack();
            return $this->errorResponse();
        }

        $count = 0;
        if($request->file('imgs') != null){
            error_log('Slider Images Is Detected');
            $imgs = $request->file('imgs');
            foreach($imgs as $img){
                error_log('Inside img');
                $count++;
                if($count > 6){
                    break;
                }
                $img = $this->uploadImage($img , "/Slider/". $slider->uid . "/imgs");
                error_log(collect($img));
                if(!$this->isEmpty($img)){
                    $proccessingimgids->push($img->publicid);

                    //Attach Image to SliderImage
                    $params = collect([
                        'imgpath' => $img->imgurl,
                        'imgpublicid' => $img->publicid,
                        'slider_id' => $slider->id,
                    ]);
                    $params = json_decode(json_encode($params));
                    $sliderimage = $this->createSliderImage($params);
                    if($this->isEmpty($sliderimage)){
                        error_log('error here1');
                        DB::rollBack();
                        $this->deleteImages($proccessingimgids);
                        return $this->errorResponse();
                    }
                }else{
                    error_log('error here3');
                    DB::rollBack();
                    $this->deleteImages($proccessingimgids);
                    return $this->errorResponse();
                }
            }
        }

        DB::commit();
        return $this->successResponse('Slider', $slider, 'create');
    }

    /**
     * @OA\Put(
     *   tags={"SliderControllerService"},
     *   path="/api/slider/{uid}",
     *   summary="Update slider by Uid.",
     *     operationId="updateSliderByUid",
     *   @OA\Parameter(
     *     name="uid",
     *     in="path",
     *     description="Slider_ID, NOT 'ID'.",
     *     required=true,
     *     @OA\Schema(type="string")
     *   ),
     * @OA\Parameter(
     * name="blogger_id",
     * in="query",
     * description="Slider belongs To which Blogger",
     * required=true,
     * @OA\Schema(
     *              type="integer"
     *          )
     * ),
     * @OA\Parameter(
     * name="title",
     * in="query",
     * description="Slider title",
     * required=true,
     * @OA\Schema(
     *              type="string"
     *          )
     * ),
     * @OA\Parameter(
     * name="desc",
     * in="query",
     * description="Slider description",
     * @OA\Schema(
     *              type="string"
     *          )
     * ),
     * @OA\Parameter(
     * name="scope",
     * in="query",
     * description="Is this slider public?",
     * @OA\Schema(
     *              type="string"
     *          )
     * ),
     *   @OA\Response(
     *     response=200,
     *     description="Slider has been updated successfully."
     *   ),
     *   @OA\Response(
     *     response="default",
     *     description="Unable to update the slider."
     *   )
     * )
     */
    public function update(Request $request, $uid)
    {
        DB::beginTransaction();
        // api/slider/{sliderid} (PUT)
        error_log($this->controllerName.'Updating slider of uid: ' . $uid);
        $this->validate($request, [
            'title' => 'required|string',
            'blogger_id' => 'required|numeric',
        ]);
        $slider = $this->getSlider($uid);
        if ($this->isEmpty($slider)) {
            DB::rollBack();
            return $this->notFoundResponse('Slider');
        }
        $params = collect([
            'blogger_id' => $request->blogger_id,
            'title' => $request->title,
            'desc' => $request->desc,
            'scope' => $request->scope,
        ]);
        //Convert To Json Object
        $params = json_decode(json_encode($params));
        $slider = $this->updateSlider($slider, $params);
        if ($this->isEmpty($slider)) {
            DB::rollBack();
            return $this->errorResponse();
        } else {
            DB::commit();
            return $this->successResponse('Slider', $slider, 'update');
        }
    }

    /**
     * @OA\Delete(
     *   tags={"SliderControllerService"},
     *   path="/api/slider/{uid}",
     *   summary="Set slider's 'status' to 0.",
     *     operationId="deleteSliderByUid",
     *   @OA\Parameter(
     *     name="uid",
     *     in="path",
     *     description="Slider ID, NOT 'ID'.",
     *     required=true,
     *     @OA\SChema(type="string")
     *   ),
     *   @OA\Response(
     *     response=200,
     *     description="Slider has been 'deleted' successfully."
     *   ),
     *   @OA\Response(
     *     response="default",
     *     description="Unable to 'delete' the slider."
     *   )
     * )
     */
    public function destroy(Request $request, $uid)
    {
        DB::beginTransaction();
        // TODO ONLY TOGGLES THE status = 1/0
        // api/slider/{sliderid} (DELETE)
        error_log($this->controllerName.'Deleting slider of uid: ' . $uid);
        $slider = $this->getSlider( $uid);
        if ($this->isEmpty($slider)) {
            DB::rollBack();
            return $this->notFoundResponse('Slider');
        }
        $slider = $this->deleteSlider($slider);
        if ($this->isEmpty($slider)) {
            DB::rollBack();
            return $this->errorResponse();
        } else {
            DB::commit();
            return $this->successResponse('Slider', null, 'delete');
        }
    }





    /**
     * @OA\Get(
     *   tags={"SliderControllerService"},
     *   path="/api/public/sliders",
     *   summary="Retrieves all public sliders.",
     *     operationId="getPublicSliders",
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
     *   @OA\Response(
     *     response=200,
     *     description="Sliders has been retrieved successfully."
     *   ),
     *   @OA\Response(
     *     response="default",
     *     description="Unable to retrieved the sliders."
     *   )
     * )
     */
    public function getPublicSliders(Request $request)
    {
        error_log($this->controllerName.'Retrieving public sliders listing');
        $sliders = $this->getAllPublicSliders();

        if ($this->isEmpty($sliders)) {
            return $this->errorPaginateResponse('Sliders');
        } else {
            return $this->successPaginateResponse('Sliders', $sliders, $this->toInt($request->pageSize), $this->toInt($request->pageNumber));
        }
    }
    


}
