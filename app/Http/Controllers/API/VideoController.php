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
use App\Traits\VideoImageServices;
use App\Traits\CommentServices;
use App\Traits\LogServices;

class VideoController extends Controller
{
    use GlobalFunctions, NotificationFunctions, VideoServices, VideoImageServices, LogServices , CommentServices;

    private $controllerName = '[VideoController]';
    /**
     * @OA\Get(
     *      path="/api/video",
     *      operationId="getVideos",
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
        $videos = $this->getVideos($request->user());
        if ($this->isEmpty($videos)) {
            return $this->errorPaginateResponse('Videos');
        } else {
            return $this->successPaginateResponse('Videos', $videos, $this->toInt($request->pageSize), $this->toInt($request->pageNumber));
        }
    }

    /**
     * @OA\Get(
     *      path="/api/filter/video",
     *      operationId="filterVideos",
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
        $videos = $this->filterVideos($videos, $params);

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
     * name="channel_id",
     * in="query",
     * description="Video belongs To which Channel",
     * required=true,
     * @OA\Schema(
     *              type="integer"
     *          )
     * ),
     * @OA\Parameter(
     * name="title",
     * in="query",
     * description="Video title",
     * required=true,
     * @OA\Schema(
     *              type="string"
     *          )
     * ),
     * @OA\Parameter(
     * name="desc",
     * in="query",
     * description="Video description",
     * @OA\Schema(
     *              type="string"
     *          )
     * ),
     * @OA\Parameter(
     * name="scope",
     * in="query",
     * description="Is this video public?",
     * @OA\Schema(
     *              type="string"
     *          )
     * ),
     * @OA\Parameter(
     * name="videopath",
     * in="query",
     * description="Is this video public?",
     * @OA\Schema(
     *              type="string"
     *          )
     * ),
     * @OA\Parameter(
     * name="videopublicid",
     * in="query",
     * description="Is this video public?",
     * @OA\Schema(
     *              type="string"
     *          )
     * ),
     * @OA\Parameter(
     * name="totallength",
     * in="query",
     * description="Is this video public?",
     * @OA\Schema(
     *              type="string"
     *          )
     * ),
     * @OA\Parameter(
     * name="free",
     * in="query",
     * description="Is this video free?",
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
*                      property="imgs",
*                      description="Video Cover Image",
*                      type="file",
*                      @OA\Items(type="string", format="binary")
*                   ),
*               ),
*           ),
*       ),
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
        $proccessingimgids = collect();
        DB::beginTransaction();
        // Can only be used by Authorized personnel
        // api/video (POST)
        $this->validate($request, [
            'title' => 'required|string',
            'blogger_id' => 'required|numeric',
        ]);
        error_log($this->controllerName.'Creating video.');
        $params = collect([
            'blogger_id' => $request->blogger_id,
            'title' => $request->title,
            'desc' => $request->desc,
            'scope' => $request->scope,
        ]);
        //Convert To Json Object
        $params = json_decode(json_encode($params));
        $video = $this->createVideo($params);
        if ($this->isEmpty($video)) {
            DB::rollBack();
            return $this->errorResponse();
        }

        $count = 0;
        if($request->file('imgs') != null){
            error_log('Video Images Is Detected');
            $imgs = $request->file('imgs');
            foreach($imgs as $img){
                error_log('Inside img');
                $count++;
                if($count > 6){
                    break;
                }
                $img = $this->uploadImage($img , "/Video/". $video->uid . "/imgs");
                error_log(collect($img));
                if(!$this->isEmpty($img)){
                    $proccessingimgids->push($img->publicid);

                    //Attach Image to VideoImage
                    $params = collect([
                        'imgpath' => $img->imgurl,
                        'imgpublicid' => $img->publicid,
                        'video_id' => $video->id,
                    ]);
                    $params = json_decode(json_encode($params));
                    $videoimage = $this->createVideoImage($params);
                    if($this->isEmpty($videoimage)){
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
        return $this->successResponse('Video', $video, 'create');
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
     * @OA\Parameter(
     * name="blogger_id",
     * in="query",
     * description="Video belongs To which Blogger",
     * required=true,
     * @OA\Schema(
     *              type="integer"
     *          )
     * ),
     * @OA\Parameter(
     * name="title",
     * in="query",
     * description="Video title",
     * required=true,
     * @OA\Schema(
     *              type="string"
     *          )
     * ),
     * @OA\Parameter(
     * name="desc",
     * in="query",
     * description="Video description",
     * @OA\Schema(
     *              type="string"
     *          )
     * ),
     * @OA\Parameter(
     * name="scope",
     * in="query",
     * description="Is this video public?",
     * @OA\Schema(
     *              type="string"
     *          )
     * ),
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
        $this->validate($request, [
            'title' => 'required|string',
            'blogger_id' => 'required|numeric',
        ]);
        $video = $this->getVideo($uid);
        if ($this->isEmpty($video)) {
            DB::rollBack();
            return $this->notFoundResponse('Video');
        }
        $params = collect([
            'blogger_id' => $request->blogger_id,
            'title' => $request->title,
            'desc' => $request->desc,
            'scope' => $request->scope,
        ]);
        //Convert To Json Object
        $params = json_decode(json_encode($params));
        $video = $this->updateVideo($video, $params);
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
        $video = $this->getVideo( $uid);
        if ($this->isEmpty($video)) {
            DB::rollBack();
            return $this->notFoundResponse('Video');
        }
        $video = $this->deleteVideo($video->id);
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
        $video = $this->setCommentCount($video);
       
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
     *     operationId="getPublicVideos",
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
        $videos->map(function($item){
            return $this->setCommentCount($item);
        });

        if ($this->isEmpty($videos)) {
            return $this->errorPaginateResponse('Videos');
        } else {
            return $this->successPaginateResponse('Videos', $videos, $this->toInt($request->pageSize), $this->toInt($request->pageNumber));
        }
    }
      
    /**
     * @OA\Get(
     *   tags={"VideoControllerService"},
     *   path="/api/public/video/{uid}/comments",
     *   summary="Retrieves all public comments.",
     *     operationId="getPublicComments",
     *   @OA\Parameter(
     *     name="uid",
     *     in="path",
     *     description="Video ID, NOT 'ID'.",
     *     required=true,
     *     @OA\SChema(type="string")
     *   ),
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
     *     description="Comments has been retrieved successfully."
     *   ),
     *   @OA\Response(
     *     response="default",
     *     description="Unable to retrieved the comments."
     *   )
     * )
     */
    public function getVideoComments(Request $request, $uid)
    {
        error_log($this->controllerName.'Retrieving video comments listing');
        $video = $this->getVideo($uid);
        if ($this->isEmpty($video)) {
            DB::rollBack();
            return $this->notFoundResponse('Video');
        }

        $comments = $this->getCommentsByVideo($video);

        if ($this->isEmpty($comments)) {
            return $this->errorPaginateResponse('Comments');
        } else {
            return $this->successPaginateResponse('Comments', $comments, $this->toInt($request->pageSize), $this->toInt($request->pageNumber));
        }
    }
}
