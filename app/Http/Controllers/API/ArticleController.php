<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use DB;
use Carbon\Carbon;
use App\Article;
use Illuminate\Support\Facades\Hash;
use App\Traits\GlobalFunctions;
use App\Traits\NotificationFunctions;
use App\Traits\ArticleServices;
use App\Traits\ArticleFamilyServices;
use App\Traits\PatternServices;
use App\Traits\LogServices;

class ArticleController extends Controller
{
    use GlobalFunctions, NotificationFunctions, ArticleServices, LogServices;
    private $controllerName = '[ArticleController]';
     /**
     * @OA\Get(
     *      path="/api/article",
     *      operationId="getArticles",
     *      tags={"ArticleControllerService"},
     *      summary="Get list of articles",
     *      description="Returns list of articles",
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
     *          description="Successfully retrieved list of articles"
     *       ),
     *       @OA\Response(
     *          response="default",
     *          description="Unable to retrieve list of articles")
     *    )
     */
    public function index(Request $request)
    {
        error_log('Retrieving list of articles.');
        // api/article (GET)
        $articles = $this->getArticles($request->user());
        if ($this->isEmpty($articles)) {
            return $this->errorPaginateResponse('Articles');
        } else {
            return $this->successPaginateResponse('Articles', $articles, $this->toInt($request->pageSize), $this->toInt($request->pageNumber));
        }
    }

    /**
     * @OA\Get(
     *      path="/api/filter/article",
     *      operationId="filterArticles",
     *      tags={"ArticleControllerService"},
     *      summary="Filter list of articles",
     *      description="Returns list of filtered articles",
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
     *          description="Successfully retrieved list of filtered articles"
     *       ),
     *       @OA\Response(
     *          response="default",
     *          description="Unable to retrieve list of articles")
     *    )
     */
    public function filter(Request $request)
    {
        error_log('Retrieving list of filtered articles.');
        // api/article/filter (GET)
        $params = collect([
            'keyword' => $request->keyword,
            'fromdate' => $request->fromdate,
            'todate' => $request->todate,
            'status' => $request->status,
            'onsale' => $request->onsale,
            'article_id' => $request->article_id,
        ]);
        //Convert To Json Object
        $params = json_decode(json_encode($params));
        $articles = $this->getArticles($request->user());
        $articles = $this->filterArticles($articles, $params);

        if ($this->isEmpty($articles)) {
            return $this->errorPaginateResponse('Articles');
        } else {
            return $this->successPaginateResponse('Articles', $articles, $this->toInt($request->pageSize), $this->toInt($request->pageNumber));
        }

    }


    /**
     * @OA\Get(
     *   tags={"ArticleControllerService"},
     *   path="/api/article/{uid}",
     *   summary="Retrieves article by Uid.",
     *     operationId="getArticleByUid",
     *   @OA\Parameter(
     *     name="uid",
     *     in="path",
     *     description="Article_ID, NOT 'ID'.",
     *     required=true,
     *     @OA\Schema(type="string")
     *   ),
     *   @OA\Response(
     *     response=200,
     *     description="Article has been retrieved successfully."
     *   ),
     *   @OA\Response(
     *     response="default",
     *     description="Unable to retrieve the article."
     *   )
     * )
     */
    public function show(Request $request, $uid)
    {
        // api/article/{articleid} (GET)
        error_log('Retrieving article of uid:' . $uid);
        $article = $this->getArticle($uid);
        if ($this->isEmpty($article)) {
            return $this->notFoundResponse('Article');
        } else {
            return $this->successResponse('Article', $article, 'retrieve');
        }
    }



    /**
     * @OA\Post(
     *   tags={"ArticleControllerService"},
     *   path="/api/article",
     *   summary="Creates a article.",
     *   operationId="createArticle",
     * @OA\Parameter(
     * name="name",
     * in="query",
     * description="Article name",
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
     * @OA\Schema(
     *              type="integer"
     *          )
     * ),
     * @OA\Parameter(
     * name="warranty_id",
     * in="query",
     * description="Warranty ID",
     * @OA\Schema(
     *              type="integer"
     *          )
     * ),
     * @OA\Parameter(
     * name="shipping_id",
     * in="query",
     * description="Shipping ID",
     * @OA\Schema(
     *              type="integer"
     *          )
     * ),
     * @OA\Parameter(
     * name="articlefamilies",
     * in="query",
     * description="Article Families",
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
*                  @OA\Property(
*                      property="sliders",
*                      description="Sliders Image",
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
     * description="Product Base Price",
     * required=true,
     * @OA\Schema(
     *              type="number"
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
     *   @OA\Response(
     *     response=200,
     *     description="Article has been created successfully."
     *   ),
     *   @OA\Response(
     *     response="default",
     *     description="Unable to create the article."
     *   )
     * )
     */
    public function store(Request $request)
    {
        $proccessingimgids = collect();
        DB::beginTransaction();
        // Can only be used by Authorized personnel
        // api/article (POST)

        $this->validate($request, [
            'store_id' => 'required',
            'name' => 'required|string|max:191',
            'code' => 'nullable',
            'sku' => 'required|string|max:191',
            'desc' => 'nullable',
            'cost' => 'required|numeric|min:0',
            'price' => 'required|numeric|min:0',
        ]);
        error_log($this->controllerName.'Creating article.');
        $params = collect([
            'store_id' => $request->store_id,
            'product_promotion_id' => $request->product_promotion_id,
            'warranty_id' => $request->warranty_id,
            'shipping_id' => $request->shipping_id,
            'name' => $request->name,
            'code' => $request->code,
            'sku' => $request->sku,
            'desc' => $request->desc,
            'cost' => $request->cost,
            'price' => $request->price,
            'stockthreshold' => $request->stockthreshold,
        ]);
        $params = json_decode(json_encode($params));
        $article = $this->createArticle($params);
        if ($this->isEmpty($article)) {
            DB::rollBack();
            $this->deleteImages($proccessingimgids);
            return $this->errorResponse();
        }


        //Associating Image Relationship
        if($request->file('img') != null){
            error_log('Image Is Detected');
            error_log(collect($request->file('img')));
            $img = $this->uploadImage($request->file('img') , "/Article/". $article->uid);
            if(!$this->isEmpty($img)){
                $article->imgpath = $img->imgurl;
                $article->imgpublicid = $img->publicid;
                $proccessingimgids->push($img->publicid);
                if(!$this->saveModel($article)){
                    error_log('error here0');
                    DB::rollBack();
                    $this->deleteImages($proccessingimgids);
                    return $this->errorResponse();
                }
                //Attach Image to ArticleImage
                $articleimage = $this->associateImageWithArticle($article , $img);
                if($this->isEmpty($articleimage)){
                    error_log('error here');
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

        $count = 0;
        if($request->file('sliders') != null){
            error_log('Slider Images Is Detected');
            $sliders = $request->file('sliders');
            error_log(collect($sliders));
            foreach($sliders as $slider){
                error_log('Inside slider');
                $count++;
                if($count > 6){
                    break;
                }
                $img = $this->uploadImage($slider , "/Article/". $article->uid . "/sliders");
                error_log(collect($img));
                if(!$this->isEmpty($img)){
                    $proccessingimgids->push($img->publicid);
                    if(!$this->saveModel($article)){
                        error_log('error here2');
                        DB::rollBack();
                        $this->deleteImages($proccessingimgids);
                        return $this->errorResponse();
                    }
                    //Attach Image to ArticleImage
                    $articleimage = $this->associateImageWithArticle($article , $img);
                    if($this->isEmpty($articleimage)){
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

        //Associating Article Family Relationship
        $articlefamilies = json_decode($request->articlefamilies);
        $temp = json_decode(json_encode($request->articlefamilies));
        $articletotalqty = 0;
        $articlefamilytotalqty = 0;
        $onsale = false;
        if(!$this->isEmpty($articlefamilies)){
           foreach($articlefamilies as $articlefamily){
                $articlefamilyqty = $articlefamily->qty;
                $articlefamilytotalqty += $articlefamily->qty;
                $patterns = $articlefamily->patterns;

                if($articlefamily->onsale && !$onsale){
                    $onsale = true;
                }

                $articlefamily->article_id = $article->refresh()->id;
                $articlefamily = $this->associateArticleFamilyWithArticle($article, $articlefamily);
                $this->createLog($request->user()->id , [$articlefamily->id], 'create', 'articlefamily');

                if($this->isEmpty($articlefamily)){
                    DB::rollBack();
                    $this->deleteImages($proccessingimgids);
                    return $this->errorResponse();
                }

                //Patterns
                $patterntotalqty = 0;
                foreach($patterns as $pattern){
                    $patterntotalqty += $pattern->qty;
                    $pattern->article_family_id = $articlefamily->refresh()->id;
                    $pattern = $this->associatePatternWithArticleFamily($articlefamily, $pattern);
                    $this->createLog($request->user()->id , [$pattern->id], 'create', 'pattern');
                    if($this->isEmpty($pattern)){
                        DB::rollBack();
                        $this->deleteImages($proccessingimgids);
                        return $this->errorResponse();
                    }

                    if($pattern->onsale && !$onsale){
                        $onsale = true;
                    }
                }

                //Detect use what total qty
                if($patterntotalqty > 0){
                    $articlefamily->qty = $this->toInt($patterntotalqty);
                }else{
                    $articlefamily->qty = $this->toInt($articlefamilyqty);
                }

                if(!$this->saveModel($articlefamily)){
                    DB::rollBack();
                    $this->deleteImages($proccessingimgids);
                    return $this->errorResponse();
                }

                $articletotalqty += $this->toInt($articlefamily->qty);
           }
        }else{
            error_log('error here4');
            DB::rollBack();
            $this->deleteImages($proccessingimgids);
            return $this->errorResponse();
        }

        $article->qty = $this->toInt($articletotalqty);
        $article->onsale = $onsale;

        if(!$this->saveModel($article)){
            DB::rollBack();
            $this->deleteImages($proccessingimgids);
            return $this->errorResponse();
        }

        $this->createLog($request->user()->id , [$article->id], 'create', 'article');
        DB::commit();

        return $this->successResponse('Article', $article, 'create');
    }


    /**
     * @OA\Put(
     *   tags={"ArticleControllerService"},
     *   path="/api/article/{uid}",
     *   summary="Update article by Uid.",
     *     operationId="updateArticleByUid",
     *   @OA\Parameter(
     *     name="uid",
     *     in="path",
     *     description="Article_ID, NOT 'ID'.",
     *     required=true,
     *     @OA\Schema(type="string")
     *   ),
     * @OA\Parameter(
     * name="name",
     * in="query",
     * description="Articlename",
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
     * required=true,
     * @OA\Schema(
     *              type="integer"
     *          )
     * ),
     * @OA\Parameter(
     * name="warranty_id",
     * in="query",
     * description="Warranty ID",
     * required=true,
     * @OA\Schema(
     *              type="integer"
     *          )
     * ),
     * @OA\Parameter(
     * name="shipping_id",
     * in="query",
     * description="Shipping ID",
     * required=true,
     * @OA\Schema(
     *              type="integer"
     *          )
     * ),
     * @OA\Parameter(
     * name="articlefamilies",
     * in="query",
     * description="Article Families",
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
     *   @OA\Response(
     *     response=200,
     *     description="Article has been created successfully."
     *   ),
     *   @OA\Response(
     *     response="default",
     *     description="Unable to create the article."
     *   )
     * )
     */
    public function update(Request $request, $uid)
    {
        $proccessingimgids = collect();
        DB::beginTransaction();
        // api/article/{articleid} (PUT)
        error_log($this->controllerName.'Updating article of uid: ' . $uid);
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

        $article = $this->getArticle($uid);
        if ($this->isEmpty($article)) {
            DB::rollBack();
            return $this->notFoundResponse('Article');
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

        //Updating article
        $article = $this->updateArticle($article, $params);
        if($this->isEmpty($article)){
            DB::rollBack();
            $this->deleteImages($proccessingimgids);
            return $this->errorResponse();
        }

        
        //Associating Image Relationship
        if($request->file('img') != null){
            $img = $this->uploadImage($request->file('img') , "/Article/". $article->uid);
            if(!$this->isEmpty($img)){
                //Delete Previous Image
                if($article->imgpublicid){
                    if(!$this->deleteArticleImage($article->imgpublicid)){
                        DB::rollBack();
                        $this->deleteImages($proccessingimgids);
                        return $this->errorResponse();
                    }
                }

                $article->imgpath = $img->imgurl;
                $article->imgpublicid = $img->publicid;
                $proccessingimgids->push($img->publicid);
                if(!$this->saveModel($article)){
                    DB::rollBack();
                    $this->deleteImages($proccessingimgids);
                    return $this->errorResponse();
                }
                //Attach Image to ArticleImage
                $articleimage = $this->associateImageWithArticle($article , $img);
                if($this->isEmpty($articleimage)){
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
        $count = $article->articleimage()->count();
        if($request->file('sliders') != null){
            $sliders = $request->file('sliders');
            foreach($sliders as $slider){
                $count++;
                if($count > 6){
                    break;
                }
                $img = $this->uploadImage($slider , "/Article/". $article->uid . "/sliders");
                if(!$this->isEmpty($img)){
                    $proccessingimgids->push($img->publicid);
                    if(!$this->saveModel($article)){
                        DB::rollBack();
                        $this->deleteImages($proccessingimgids);
                        return $this->errorResponse();
                    }
                    //Attach Image to ArticleImage
                    $articleimage = $this->associateImageWithArticle($article , $img);
                    if($this->isEmpty($articleimage)){
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
        }

        //Associating Article Family Relationship

        $articlefamilies = collect(json_decode($request->articlefamilies));
        $originvfamiliesids = $article->articlefamilies()->pluck('id');
        $articlefamiliesids = $articlefamilies->pluck('id');
        //get ids not in list previously
        $forinsertids = $articlefamiliesids->diff($originvfamiliesids);
        //get ids that not longer in article families
        $fordeleteids = $originvfamiliesids->diff($articlefamiliesids);

        foreach($forinsertids as $id){
            $articlefamily = $this->getArticleFamilyById($id);
            if($this->isEmpty($articlefamily)){
                 DB::rollBack();
                 $this->deleteImages($proccessingimgids);
                 return $this->notFoundResponse('ArticleFamily');
             }
            $articlefamily->article()->associate($article);
        }

        foreach($fordeleteids as $id){
            $articlefamily = $this->getArticleFamilyById($id);
            if($this->isEmpty($articlefamily)){
                 DB::rollBack();
                 $this->deleteImages($proccessingimgids);
                 return $this->notFoundResponse('ArticleFamily');
             }
            if(!$this->deleteArticleFamily($articlefamily)){
                DB::rollBack();
                $this->deleteImages($proccessingimgids);
                return $this->errorResponse();
            }
        }


        $this->createLog($request->user()->id , [$article->id], 'update', 'article');
        DB::commit();

        return $this->successResponse('Article', $article, 'update');
    }

    /**
     * @OA\Delete(
     *   tags={"ArticleControllerService"},
     *   path="/api/article/{uid}",
     *   summary="Set article's 'status' to 0.",
     *     operationId="deleteArticleByUid",
     *   @OA\Parameter(
     *     name="uid",
     *     in="path",
     *     description="Article ID, NOT 'ID'.",
     *     required=true,
     *     @OA\SChema(type="string")
     *   ),
     *   @OA\Response(
     *     response=200,
     *     description="Article has been 'deleted' successfully."
     *   ),
     *   @OA\Response(
     *     response="default",
     *     description="Unable to 'delete' the article."
     *   )
     * )
     */
    public function destroy(Request $request, $uid)
    {
        DB::beginTransaction();
        // TODO ONLY TOGGLES THE status = 1/0
        // api/article/{articleid} (DELETE)
        error_log('Deleting article of uid: ' . $uid);
        $article = $this->getArticle($uid);
        if ($this->isEmpty($article)) {
            DB::rollBack();
            return $this->notFoundResponse('Article');
        }
        $article = $this->deleteArticle($article);
        if ($this->isEmpty($article)) {
            DB::rollBack();
            return $this->errorResponse();
        } else {
            $this->createLog($request->user()->id , [$article->id], 'delete', 'article');
            DB::commit();
            return $this->successResponse('Article', $article, 'delete');
        }
    }


    /**
     * @OA\Get(
     *   tags={"ArticleControllerService"},
     *   path="/api/article/{uid}/onsale",
     *   summary="Retrieves onsale article by Uid.",
     *     operationId="getOnSaleArticleByUid",
     *   @OA\Parameter(
     *     name="uid",
     *     in="path",
     *     description="Article_ID, NOT 'ID'.",
     *     required=true,
     *     @OA\Schema(type="string")
     *   ),
     *   @OA\Response(
     *     response=200,
     *     description="Article has been retrieved successfully."
     *   ),
     *   @OA\Response(
     *     response="default",
     *     description="Unable to retrieve the article."
     *   )
     * )
     */
    public function getOnSaleArticle(Request $request, $uid)
    {
        // api/article/{articleid} (GET)
        error_log($this->controllerName.'Retrieving onsale article of uid:' . $uid);
        $cols = $this->articleDefaultCols();
        $article = $this->getArticle($uid);
        if($article->onsale){
            $article = $this->itemPluckCols($article , $cols);
            $article = json_decode(json_encode($article));
            $article = $this->calculatePromotionPrice($article);
            $article = $this->countProductReviews($article);
        }else{
            $article = null;
        }
        if ($this->isEmpty($article)) {
            return $this->notFoundResponse('Article');
        } else {
            return $this->successResponse('Article', $article, 'retrieve');
        }
    }


    // TODO: TEst upload image
    /** @OA\Post(
     *  tags={"ArticleControllerService"},
     *   path="/api/thumbnail",
     *   summary="Upload article thumbnail.",
     *   operationId="uploadArticleThumbnail",
     *     @OA\Parameter(
     * name="uid",
     * in="query",
     * description="ArticleUID",
     * required=true,
     * @OA\Schema(
     *              type="string"
     *          )
     * ),
     * @OA\RequestBody(
     *          required=true,
     *          @OA\MediaType(
     *              mediaType="multipart/form-data",
     *              @OA\Schema(
     *                  @OA\Property(
     *                      property="img",
     *                      description="Image",
     *                      type="file",
     *                      @OA\Items(type="string", format="binary")
     *                   )
     *               )
     *           )
     *       ),
     *   @OA\Response(
     *     response=200,
     *     description="Thumbnail uploaded"
     *   ),
     *   @OA\Response(
     *     response="default",
     *     description="Unable to upload thumbnail."
     *   )
     * )
     */
    public function uploadArticleThumbnail(Request $request) {
        error_log('Uploading article thumbnail');
        //Associating Image Relationship
        if($request->file('img') != null){
            error_log('Image Is Detected');
        } else {
            error_log('no image');
        }
    }
}
