<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use DB;
use Carbon\Carbon;
use App\Video;
use Illuminate\Support\Facades\Hash;
use App\Traits\GlobalFunctions;
use App\Traits\NotificationFunctions;
use App\Traits\VideoServices;
use App\Traits\LogServices;

class VideoController extends Controller
{
    use GlobalFunctions, NotificationFunctions, VideoServices, LogServices;

    private $controllerName = '[VideoController]';
    /**
     * @OA\Get(
     *      path="/api/video",
     *      operationId="getVideoList",
     *      tags={"VideoControllerService"},
     *      summary="Get list of videos",
     *      description="Returns list of videos",
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
     *          description="Successfully retrieved list of videos"
     *       ),
     *       @OA\Response(
     *          response="default",
     *          description="Unable to retrieve list of videos")
     *    )
     */
    public function index(Request $request)
    {
        error_log($this->controllerName.'Retrieving list of videos.');
        // api/video (GET)
        $videos = $this->getVideoListing($request->video());
        if ($this->isEmpty($videos)) {
            return $this->errorPaginateResponse('Videos');
        } else {
            return $this->successPaginateResponse('Videos', $videos, $this->toInt($request->pageSize), $this->toInt($request->pageNumber));
        }
    }

    /**
     * @OA\Get(
     *      path="/api/filter/video",
     *      operationId="filterVideoList",
     *      tags={"VideoControllerService"},
     *      summary="Filter list of videos",
     *      description="Returns list of filtered videos",
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
     *          description="Successfully retrieved list of filtered videos"
     *       ),
     *       @OA\Response(
     *          response="default",
     *          description="Unable to retrieve list of videos")
     *    )
     */
    public function filter(Request $request)
    {
        error_log($this->controllerName.'Retrieving list of filtered videos.');
        // api/video/filter (GET)
        $params = collect([
            'keyword' => $request->keyword,
            'fromdate' => $request->fromdate,
            'todate' => $request->todate,
            'status' => $request->status,
            'company_id' => $request->company_id,
        ]);
        //Convert To Json Object
        $params = json_decode(json_encode($params));
        $videos = $this->filterVideoListing($request->video(), $params);

        if ($this->isEmpty($videos)) {
            return $this->errorPaginateResponse('Videos');
        } else {
            return $this->successPaginateResponse('Videos', $videos, $this->toInt($request->pageSize), $this->toInt($request->pageNumber));
        }

    }
    /**
     * @OA\Get(
     *   tags={"VideoControllerService"},
     *   path="/api/video/{uid}",
     *   summary="Retrieves video by Uid.",
     *     operationId="getVideoByUid",
     *   @OA\Parameter(
     *     name="uid",
     *     in="path",
     *     description="Video_ID, NOT 'ID'.",
     *     required=true,
     *     @OA\Schema(type="string")
     *   ),
     *   @OA\Response(
     *     response=200,
     *     description="Video has been retrieved successfully."
     *   ),
     *   @OA\Response(
     *     response="default",
     *     description="Unable to retrieve the video."
     *   )
     * )
     */
    public function show(Request $request, $uid)
    {
        // api/video/{videoid} (GET)
        error_log($this->controllerName.'Retrieving video of uid:' . $uid);
        $video = $this->getVideo($uid);
        if ($this->isEmpty($video)) {
            $data['data'] = null;
            return $this->notFoundResponse('Video');
        } else {
            return $this->successResponse('Video', $video, 'retrieve');
        }
    }

    /**
     * @OA\Post(
     *   tags={"VideoControllerService"},
     *   path="/api/video",
     *   summary="Creates a video.",
     *   operationId="createVideo",
     * @OA\Parameter(
     * name="name",
     * in="query",
     * description="Videoname",
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
     * name="password",
     * in="query",
     * description="Password",
     * required=true,
     * @OA\Schema(
     *              type="string"
     *          )
     * ),
     * @OA\Parameter(
     * name="password_confirmation",
     * in="query",
     * description="Password Confirmation",
     * required=true,
     * @OA\Schema(
     *              type="string"
     *          )
     * ),
     * @OA\Parameter(
     * name="country",
     * in="query",
     * description="Country",
     * @OA\Schema(
     *  type="string"
     *  )
     * ),
     *   @OA\Response(
     *     response=200,
     *     description="Video has been created successfully."
     *   ),
     *   @OA\Response(
     *     response="default",
     *     description="Unable to create the video."
     *   )
     * )
     */
    public function store(Request $request)
    {
        DB::beginTransaction();
        // Can only be used by Authorized personnel
        // api/video (POST)
        $this->validate($request, [
            'email' => 'nullable|string|email|max:191|unique:videos',
            'password' => 'required|string|min:6|confirmed',
        ]);
        error_log($this->controllerName.'Creating video.');
        $params = collect([
            'icno' => $request->icno,
            'name' => $request->name,
            'email' => $request->email,
            'tel1' => $request->tel1,
            'tel2' => $request->tel2,
            'address1' => $request->address1,
            'address2' => $request->address2,
            'postcode' => $request->postcode,
            'state' => $request->state,
            'city' => $request->city,
            'country' => $request->country,
            'password' => $request->password,
        ]);
        //Convert To Json Object
        $params = json_decode(json_encode($params));
        $video = $this->createVideo($request->video(), $params);

        if ($this->isEmpty($video)) {
            DB::rollBack();
            return $this->errorResponse();
        } else {
            DB::commit();
            return $this->successResponse('Video', $video, 'create');
        }
    }

    /**
     * @OA\Put(
     *   tags={"VideoControllerService"},
     *   path="/api/video/{uid}",
     *   summary="Update video by Uid.",
     *     operationId="updateVideoByUid",
     *   @OA\Parameter(
     *     name="uid",
     *     in="path",
     *     description="Video_ID, NOT 'ID'.",
     *     required=true,
     *     @OA\Schema(type="string")
     *   ),
     *   @OA\Parameter(
     *     name="name",
     *     in="query",
     *     description="Videoname.",
     *     required=true,
     *     @OA\Schema(type="string")
     *   ),
     *  @OA\Parameter(
     *     name="email",
     *     in="query",
     *     description="Email.",
     *     required=true,
     *     @OA\Schema(type="string")
     *   ),
     *  @OA\Parameter(
     *     name="tel1",
     *     in="query",
     *     description="Telephone Number #1.",
     *     required=false,
     *     @OA\Schema(type="string")
     *   ),
     *  @OA\Parameter(
     *     name="address1",
     *     in="query",
     *     description="Address #1.",
     *     required=false,
     *     @OA\Schema(type="string")
     *   ),
     *  @OA\Parameter(
     *     name="city",
     *     in="query",
     *     description="City.",
     *     required=false,
     *     @OA\Schema(type="string")
     *   ),
     *  @OA\Parameter(
     *     name="postcode",
     *     in="query",
     *     description="PostCode.",
     *     required=false,
     *     @OA\Schema(type="string")
     *   ),
     *  @OA\Parameter(
     *     name="state",
     *     in="query",
     *     description="State.",
     *     required=false,
     *     @OA\Schema(type="string")
     *   ),
     *  @OA\Parameter(
     *     name="country",
     *     in="query",
     *     description="Country.",
     *     required=true,
     *     @OA\Schema(type="string")
     *   ),
     *   @OA\Parameter(
     *     name="icno",
     *     in="query",
     *     description="IC Number.",
     *     required=false,
     *     @OA\Schema(type="string")
     *     ),
     *   @OA\Response(
     *     response=200,
     *     description="Video has been updated successfully."
     *   ),
     *   @OA\Response(
     *     response="default",
     *     description="Unable to update the video."
     *   )
     * )
     */
    public function update(Request $request, $uid)
    {
        DB::beginTransaction();
        // api/video/{videoid} (PUT)
        error_log($this->controllerName.'Updating video of uid: ' . $uid);
        $video = $this->getVideo($request->video(), $uid);
        $this->validate($request, [
            'email' => 'required|string|max:191|unique:videos,email,' . $video->id,
            'name' => 'required|string|max:191',
        ]);
        if ($this->isEmpty($video)) {
            DB::rollBack();
            return $this->notFoundResponse('Video');
        }
        $params = collect([
            'icno' => $request->icno,
            'name' => $request->name,
            'email' => $request->email,
            'tel1' => $request->tel1,
            'tel2' => $request->tel2,
            'address1' => $request->address1,
            'address2' => $request->address2,
            'postcode' => $request->postcode,
            'state' => $request->state,
            'city' => $request->city,
            'country' => $request->country,
        ]);
        //Convert To Json Object
        $params = json_decode(json_encode($params));
        $video = $this->updateVideo($request->video(), $video, $params);
        if ($this->isEmpty($video)) {
            DB::rollBack();
            return $this->errorResponse();
        } else {
            DB::commit();
            return $this->successResponse('Video', $video, 'update');
        }
    }

    /**
     * @OA\Delete(
     *   tags={"VideoControllerService"},
     *   path="/api/video/{uid}",
     *   summary="Set video's 'status' to 0.",
     *     operationId="deleteVideoByUid",
     *   @OA\Parameter(
     *     name="uid",
     *     in="path",
     *     description="Video ID, NOT 'ID'.",
     *     required=true,
     *     @OA\SChema(type="string")
     *   ),
     *   @OA\Response(
     *     response=200,
     *     description="Video has been 'deleted' successfully."
     *   ),
     *   @OA\Response(
     *     response="default",
     *     description="Unable to 'delete' the video."
     *   )
     * )
     */
    public function destroy(Request $request, $uid)
    {
        DB::beginTransaction();
        // TODO ONLY TOGGLES THE status = 1/0
        // api/video/{videoid} (DELETE)
        error_log($this->controllerName.'Deleting video of uid: ' . $uid);
        $video = $this->getVideo($request->video(), $uid);
        if ($this->isEmpty($video)) {
            DB::rollBack();
            return $this->notFoundResponse('Video');
        }
        $video = $this->deleteVideo($request->video(), $video->id);
        if ($this->isEmpty($video)) {
            DB::rollBack();
            return $this->errorResponse();
        } else {
            DB::commit();
            return $this->successResponse('Video', $video, 'delete');
        }
    }

    


    /**
     * @OA\Get(
     *   tags={"VideoControllerService"},
     *   path="/api/public/video/{uid}",
     *   summary="Retrieves public video by Uid.",
     *     operationId="getPublicVideoByUid",
     *   @OA\Parameter(
     *     name="uid",
     *     in="path",
     *     description="Video ID, NOT 'ID'.",
     *     required=true,
     *     @OA\SChema(type="string")
     *   ),
     *   @OA\Response(
     *     response=200,
     *     description="Videos has been retrieved successfully."
     *   ),
     *   @OA\Response(
     *     response="default",
     *     description="Unable to retrieved the videos."
     *   )
     * )
     */
    public function getPublicVideo(Request $request , $uid)
    {
        error_log($this->controllerName.'Retrieving public videos listing');
        $video = $this->getVideo($uid);
       
        if ($this->isEmpty($video) && $video->scope != "public") {
            $data['data'] = null;
            return $this->notFoundResponse('Video');
        } else {
            return $this->successResponse('Video', $video, 'retrieve');
        }
    }

    
    /**
     * @OA\Get(
     *   tags={"VideoControllerService"},
     *   path="/api/public/videos",
     *   summary="Retrieves all public videos.",
     *     operationId="getPublicVideosListing",
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
     *     description="Videos has been retrieved successfully."
     *   ),
     *   @OA\Response(
     *     response="default",
     *     description="Unable to retrieved the videos."
     *   )
     * )
     */
    public function getPublicVideos(Request $request)
    {
        error_log($this->controllerName.'Retrieving public videos listing');
        $videos = $this->getAllVideos();
        $params = collect([
            'scope' => 'public',
            'status' => true,
        ]);
        //Convert To Json Object
        $params = json_decode(json_encode($params));
        $videos = $this->filterVideos($videos , $params);

        if ($this->isEmpty($videos)) {
            return $this->errorPaginateResponse('Videos');
        } else {
            return $this->successPaginateResponse('Videos', $videos, $this->toInt($request->pageSize), $this->toInt($request->pageNumber));
        }
    }
}
