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
use App\Traits\LogServices;

class ArticleController extends Controller
{
    use GlobalFunctions, NotificationFunctions, ArticleServices, LogServices;

    private $controllerName = '[ArticleController]';
    /**
     * @OA\Get(
     *      path="/api/article",
     *      operationId="getArticleList",
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
        error_log($this->controllerName.'Retrieving list of articles.');
        // api/article (GET)
        $articles = $this->getArticleListing($request->article());
        if ($this->isEmpty($articles)) {
            return $this->errorPaginateResponse('Articles');
        } else {
            return $this->successPaginateResponse('Articles', $articles, $this->toInt($request->pageSize), $this->toInt($request->pageNumber));
        }
    }

    /**
     * @OA\Get(
     *      path="/api/filter/article",
     *      operationId="filterArticleList",
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
     *          description="Successfully retrieved list of filtered articles"
     *       ),
     *       @OA\Response(
     *          response="default",
     *          description="Unable to retrieve list of articles")
     *    )
     */
    public function filter(Request $request)
    {
        error_log($this->controllerName.'Retrieving list of filtered articles.');
        // api/article/filter (GET)
        $params = collect([
            'keyword' => $request->keyword,
            'fromdate' => $request->fromdate,
            'todate' => $request->todate,
            'status' => $request->status,
            'company_id' => $request->company_id,
        ]);
        //Convert To Json Object
        $params = json_decode(json_encode($params));
        $articles = $this->filterArticleListing($request->article(), $params);

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
        error_log($this->controllerName.'Retrieving article of uid:' . $uid);
        $article = $this->getArticle($uid);
        if ($this->isEmpty($article)) {
            $data['data'] = null;
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
     * description="Articlename",
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
        DB::beginTransaction();
        // Can only be used by Authorized personnel
        // api/article (POST)
        $this->validate($request, [
            'email' => 'nullable|string|email|max:191|unique:articles',
            'password' => 'required|string|min:6|confirmed',
        ]);
        error_log($this->controllerName.'Creating article.');
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
        $article = $this->createArticle($request->article(), $params);

        if ($this->isEmpty($article)) {
            DB::rollBack();
            return $this->errorResponse();
        } else {
            DB::commit();
            return $this->successResponse('Article', $article, 'create');
        }
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
     *   @OA\Parameter(
     *     name="name",
     *     in="query",
     *     description="Articlename.",
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
     *     description="Article has been updated successfully."
     *   ),
     *   @OA\Response(
     *     response="default",
     *     description="Unable to update the article."
     *   )
     * )
     */
    public function update(Request $request, $uid)
    {
        DB::beginTransaction();
        // api/article/{articleid} (PUT)
        error_log($this->controllerName.'Updating article of uid: ' . $uid);
        $article = $this->getArticle($request->article(), $uid);
        $this->validate($request, [
            'email' => 'required|string|max:191|unique:articles,email,' . $article->id,
            'name' => 'required|string|max:191',
        ]);
        if ($this->isEmpty($article)) {
            DB::rollBack();
            return $this->notFoundResponse('Article');
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
        $article = $this->updateArticle($request->article(), $article, $params);
        if ($this->isEmpty($article)) {
            DB::rollBack();
            return $this->errorResponse();
        } else {
            DB::commit();
            return $this->successResponse('Article', $article, 'update');
        }
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
        error_log($this->controllerName.'Deleting article of uid: ' . $uid);
        $article = $this->getArticle($request->article(), $uid);
        if ($this->isEmpty($article)) {
            DB::rollBack();
            return $this->notFoundResponse('Article');
        }
        $article = $this->deleteArticle($request->article(), $article->id);
        if ($this->isEmpty($article)) {
            DB::rollBack();
            return $this->errorResponse();
        } else {
            DB::commit();
            return $this->successResponse('Article', $article, 'delete');
        }
    }

    


    /**
     * @OA\Get(
     *   tags={"ArticleControllerService"},
     *   path="/api/public/article/{uid}",
     *   summary="Retrieves public article by Uid.",
     *     operationId="getPublicArticleByUid",
     *   @OA\Parameter(
     *     name="uid",
     *     in="path",
     *     description="Article ID, NOT 'ID'.",
     *     required=true,
     *     @OA\SChema(type="string")
     *   ),
     *   @OA\Response(
     *     response=200,
     *     description="Articles has been retrieved successfully."
     *   ),
     *   @OA\Response(
     *     response="default",
     *     description="Unable to retrieved the articles."
     *   )
     * )
     */
    public function getPublicArticle(Request $request , $uid)
    {
        error_log($this->controllerName.'Retrieving public articles listing');
        $article = $this->getArticle($uid);
       
        if ($this->isEmpty($article) && $article->scope != "public") {
            $data['data'] = null;
            return $this->notFoundResponse('Article');
        } else {
            return $this->successResponse('Article', $article, 'retrieve');
        }
    }

    
    /**
     * @OA\Get(
     *   tags={"ArticleControllerService"},
     *   path="/api/public/articles",
     *   summary="Retrieves all public articles.",
     *     operationId="getPublicArticlesListing",
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
     *     description="Articles has been retrieved successfully."
     *   ),
     *   @OA\Response(
     *     response="default",
     *     description="Unable to retrieved the articles."
     *   )
     * )
     */
    public function getPublicArticles(Request $request)
    {
        error_log($this->controllerName.'Retrieving public articles listing');
        $articles = $this->getAllArticles();
        $params = collect([
            'scope' => 'public',
            'status' => true,
        ]);
        //Convert To Json Object
        $params = json_decode(json_encode($params));
        $articles = $this->filterArticles($articles , $params);

        if ($this->isEmpty($articles)) {
            return $this->errorPaginateResponse('Articles');
        } else {
            return $this->successPaginateResponse('Articles', $articles, $this->toInt($request->pageSize), $this->toInt($request->pageNumber));
        }
    }
}
