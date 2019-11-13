<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use DB;
use Carbon\Carbon;
use App\Category;
use Illuminate\Support\Facades\Hash;
use App\Traits\GlobalFunctions;
use App\Traits\NotificationFunctions;
use App\Traits\CategoryServices;
use App\Traits\LogServices;

class CategoryController extends Controller
{
    use GlobalFunctions, NotificationFunctions, CategoryServices, LogServices;

    /**
     * @OA\Get(
     *      path="/api/category",
     *      operationId="getCategoryList",
     *      tags={"CategoryControllerService"},
     *      summary="Get list of categories",
     *      description="Returns list of categories",
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
     *          description="Successfully retrieved list of categories"
     *       ),
     *       @OA\Response(
     *          response="default",
     *          description="Unable to retrieve list of categories")
     *    )
     */
    public function index(Request $request)
    {
        error_log('Retrieving list of categories.');
        // api/category (GET)
        $categories = $this->getCategoryListing($request->user());
        if ($this->isEmpty($categories)) {
            $data['status'] = 'error';
            $data['data'] = null;
            $data['maximumPages'] = 0;
            $data['msg'] = $this->getNotFoundMsg('Categories');
            $data['code'] = 404;
            return response()->json($data, 404);
        } else {
            //Page Pagination Result List
            //Default return 10
            $paginateddata = $this->paginateResult($categories, $request->pageSize, $request->pageNumber);
            $data['status'] = 'success';
            $data['data'] = $paginateddata;
            $data['maximumPages'] = $this->getMaximumPaginationPage($categories->count(), $request->pageSize);
            $data['msg'] = $this->getRetrievedSuccessMsg('Categories');
            $data['code'] = 200;
            return response()->json($data, 200);
        }
    }
    /**
     * @OA\Get(
     *      path="/api/pluck/categories",
     *      operationId="pluckCategoryList",
     *      tags={"CategoryControllerService"},
     *      summary="pluck list of categories",
     *      description="Returns list of plucked categories",
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
     *     name="cols",
     *     in="query",
     *     required=true,
     *     description="Columns for pluck",
     *     @OA\Schema(type="string")
     *   ),
     *      @OA\Response(
     *          response=200,
     *          description="Successfully retrieved list of categories"
     *       ),
     *       @OA\Response(
     *          response="default",
     *          description="Unable to retrieve list of categories")
     *    )
     */
    public function pluckIndex(Request $request)
    {
        error_log('Retrieving list of plucked categories.');
        // api/pluck/categories (GET)
        error_log("columns = " . collect($this->splitToArray($request->cols)));
        $categories = $this->pluckCategoryIndex($this->splitToArray($request->cols));
        if ($this->isEmpty($categories)) {
            $data['status'] = 'error';
            $data['data'] = null;
            $data['maximumPages'] = 0;
            $data['msg'] = $this->getNotFoundMsg('Categories');
            $data['code'] = 404;
            return response()->json($data, 404);
        } else {
            //Page Pagination Result List
            //Default return 10
            $paginateddata = $this->paginateResult($categories, $request->pageSize, $request->pageNumber);
            $data['status'] = 'success';
            $data['data'] = $paginateddata;
            $data['maximumPages'] = $this->getMaximumPaginationPage($categories->count(), $request->pageSize);
            $data['msg'] = $this->getRetrievedSuccessMsg('Categories');
            $data['code'] = 200;
            return response()->json($data, 200);
        }
    }

    /**
     * @OA\Get(
     *      path="/api/filter/category",
     *      operationId="filterCategoryList",
     *      tags={"CategoryControllerService"},
     *      summary="Filter list of categories",
     *      description="Returns list of filtered categories",
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
     *      @OA\Response(
     *          response=200,
     *          description="Successfully retrieved list of filtered categories"
     *       ),
     *       @OA\Response(
     *          response="default",
     *          description="Unable to retrieve list of categories")
     *    )
     */
    public function filter(Request $request)
    {
        error_log('Retrieving list of filtered categories.');
        // api/category/filter (GET)
        $params = collect([
            'keyword' => $request->keyword,
            'fromdate' => $request->fromdate,
            'todate' => $request->todate,
            'status' => $request->status,
            'category_id' => $request->category_id,
        ]);
        //Convert To Json Object
        $params = json_decode(json_encode($params));
        $categories = $this->filterCategoryListing($request->user(), $params);

        if ($this->isEmpty($categories)) {
            $data['data'] = null;
            $data['maximumPages'] = 0;
            $data['msg'] = $this->getNotFoundMsg('Categories');
            $data['code'] = 404;
            return response()->json($data, 404);
        } else {
            //Page Pagination Result List
            //Default return 10
            $paginateddata = $this->paginateResult($categories, $request->pageSize, $request->pageNumber);
            $data['data'] = $paginateddata;
            $data['maximumPages'] = $this->getMaximumPaginationPage($categories->count(), $request->pageSize);
            $data['msg'] = $this->getRetrievedSuccessMsg('Categories');
            $data['code'] = 200;
            return response()->json($data, 200);
        }

    }

    /**
     * @OA\Get(
     *      path="/api/pluck/filter/category",
     *      operationId="filterPluckedCategoryList",
     *      tags={"CategoryControllerService"},
     *      summary="Filter list of plucked categories",
     *      description="Returns list of filtered categories",
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
     *      @OA\Response(
     *          response=200,
     *          description="Successfully retrieved list of filtered categories"
     *       ),
     *       @OA\Response(
     *          response="default",
     *          description="Unable to retrieve list of categories")
     *    )
     */
    public function pluckFilter(Request $request)
    {
        error_log('Retrieving list of filtered and plucked categories.');
        // api/pluck/filter/category (GET)
        $params = collect([
            'keyword' => $request->keyword,
            'fromdate' => $request->fromdate,
            'todate' => $request->todate,
            'status' => $request->status,
            'category_id' => $request->category_id,
        ]);
        //Convert To Json Object
        $params = json_decode(json_encode($params));
        $categories = $this->pluckCategoryFilter($this->splitToArray($request->cols) , $params);

        if ($this->isEmpty($categories)) {
            $data['data'] = null;
            $data['maximumPages'] = 0;
            $data['msg'] = $this->getNotFoundMsg('Categories');
            $data['code'] = 404;
            return response()->json($data, 404);
        } else {
            //Page Pagination Result List
            //Default return 10
            $paginateddata = $this->paginateResult($categories, $request->pageSize, $request->pageNumber);
            $data['data'] = $paginateddata;
            $data['maximumPages'] = $this->getMaximumPaginationPage($categories->count(), $request->pageSize);
            $data['msg'] = $this->getRetrievedSuccessMsg('Categories');
            $data['code'] = 200;
            return response()->json($data, 200);
        }

    }

    /**
     * @OA\Get(
     *   tags={"CategoryControllerService"},
     *   path="/api/category/{uid}",
     *   summary="Retrieves category by Uid.",
     *     operationId="getCategoryByUid",
     *   @OA\Parameter(
     *     name="uid",
     *     in="path",
     *     description="Category_ID, NOT 'ID'.",
     *     required=true,
     *     @OA\Schema(type="string")
     *   ),
     *   @OA\Response(
     *     response=200,
     *     description="Category has been retrieved successfully."
     *   ),
     *   @OA\Response(
     *     response="default",
     *     description="Unable to retrieve the category."
     *   )
     * )
     */
    public function show(Request $request, $uid)
    {
        // api/category/{categoryid} (GET)
        error_log('Retrieving category of uid:' . $uid);
        $category = $this->getCategory($request->user(), $uid);
        if ($this->isEmpty($category)) {
            $data['data'] = null;
            $data['msg'] = $this->getNotFoundMsg('Category');
            $data['status'] = 'error';
            $data['code'] = 404;
            return response()->json($data, 404);
        } else {
            $data['data'] = $category;
            $data['msg'] = $this->getRetrievedSuccessMsg('Category');
            $data['status'] = 'success';
            $data['code'] = 200;
            return response()->json($data, 200);
        }
    }

    /**
     * @OA\Get(
     *      path="/api/pluck/category/{uid}",
     *      operationId="pluckCategoryByUid",
     *      tags={"CategoryControllerService"},
     *      summary="pluck category",
     *      description="Returns plucked categories",
     *   @OA\Parameter(
     *     name="uid",
     *     in="path",
     *     description="Category_ID, NOT 'ID'.",
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
     *          description="Successfully retrieved list of categories"
     *       ),
     *       @OA\Response(
     *          response="default",
     *          description="Unable to retrieve list of categories")
     *    )
     */
    public function pluckShow(Request $request , $uid)
    {
        error_log('Retrieving plucked categories.');
        // api/pluck/category/{uid} (GET)
        error_log("columns = " . collect($this->splitToArray($request->cols)));
        $category = $this->pluckCategory($this->splitToArray($request->cols) , $uid);
        if ($this->isEmpty($category)) {
            $data['data'] = null;
            $data['status'] = 'error';
            $data['msg'] = $this->getNotFoundMsg('Category');
            $data['code'] = 404;
            return response()->json($data, 404);
        } else {
            $data['status'] = 'success';
            $data['msg'] = $this->getRetrievedSuccessMsg('Category');
            $data['data'] = $category;
            $data['code'] = 200;
            return response()->json($data, 200);
        }
    }

    
    /**
     * @OA\Post(
     *   tags={"CategoryControllerService"},
     *   path="/api/category",
     *   summary="Creates a category.",
     *   operationId="createCategory",
     * @OA\Parameter(
     * name="name",
     * in="query",
     * description="Category name",
     * required=true,
     * @OA\Schema(
     *              type="string"
     *          )
     * ),
     * @OA\Parameter(
     * name="desc",
     * in="query",
     * description="Category Description",
     * required=true,
     * @OA\Schema(
     *              type="string"
     *          )
     * ),
     *   @OA\Response(
     *     response=200,
     *     description="Category has been created successfully."
     *   ),
     *   @OA\Response(
     *     response="default",
     *     description="Unable to create the category."
     *   )
     * )
     */
    public function store(Request $request)
    {
        // Can only be used by Authorized personnel
        // api/category (POST)
        $this->validate($request, [
            'name' => 'required|string',
            'desc' => 'required|string',
        ]);
        error_log('Creating category.');
        $params = collect([
            'name' => $request->name,
            'desc' => $request->desc,
        ]);
        //Convert To Json Object
        $params = json_decode(json_encode($params));
        $category = $this->createCategory($request->user(), $params);

        if ($this->isEmpty($category)) {
            $data['data'] = null;
            $data['status'] = 'error';
            $data['msg'] = $this->getErrorMsg();
            $data['code'] = 404;
            return response()->json($data, 404);
        } else {
            $data['status'] = 'success';
            $data['msg'] = $this->getCreatedSuccessMsg('Category');
            $data['data'] = $category;
            $data['code'] = 200;
            return response()->json($data, 200);
        }
    }


    /**
     * @OA\Put(
     *   tags={"CategoryControllerService"},
     *   path="/api/category/{uid}",
     *   summary="Update category by Uid.",
     *     operationId="updateCategoryByUid",
     *   @OA\Parameter(
     *     name="uid",
     *     in="path",
     *     description="Category_ID, NOT 'ID'.",
     *     required=true,
     *     @OA\Schema(type="string")
     *   ), 
     * * @OA\Parameter(
     * name="name",
     * in="query",
     * description="Category name",
     * required=true,
     * @OA\Schema(
     *              type="string"
     *          )
     * ),
     * @OA\Parameter(
     * name="desc",
     * in="query",
     * description="Category Description",
     * required=true,
     * @OA\Schema(
     *              type="string"
     *          )
     * ),
     *   @OA\Response(
     *     response=200,
     *     description="Category has been updated successfully."
     *   ),
     *   @OA\Response(
     *     response="default",
     *     description="Unable to update the category."
     *   )
     * )
     */
    public function update(Request $request, $uid)
    {
        // api/category/{categoryid} (PUT) 
        error_log('Updating category of uid: ' . $uid);
        $category = $this->getCategory($request->user(), $uid);
        error_log($category);
        $this->validate($request, [
            'name' => 'required|string',
            'desc' => 'required|string',
        ]);

        if ($this->isEmpty($category)) {
            $data['data'] = null;
            $data['msg'] = $this->getNotFoundMsg('Category');
            $data['status'] = 'error';
            $data['code'] = 404;
            return response()->json($data, 404);
        }
        
        $params = collect([
            'name' => $request->name,
            'desc' => $request->desc,
        ]);
        //Convert To Json Object
        $params = json_decode(json_encode($params));
        $category = $this->updateCategory($request->user(), $category, $params);
        if ($this->isEmpty($category)) {
            $data['data'] = null;
            $data['msg'] = $this->getErrorMsg('Category');
            $data['status'] = 'error';
            $data['code'] = 404;
            return response()->json($data, 404);
        } else {
            $data['status'] = 'success';
            $data['msg'] = $this->getUpdatedSuccessMsg('Category');
            $data['data'] = $category;
            $data['status'] = 'success';
            $data['code'] = 200;
            return response()->json($data, 200);
        }
    }

    /**
     * @OA\Delete(
     *   tags={"CategoryControllerService"},
     *   path="/api/category/{uid}",
     *   summary="Set category's 'status' to 0.",
     *     operationId="deleteCategoryByUid",
     *   @OA\Parameter(
     *     name="uid",
     *     in="path",
     *     description="Category ID, NOT 'ID'.",
     *     required=true,
     *     @OA\SChema(type="string")
     *   ),
     *   @OA\Response(
     *     response=200,
     *     description="Category has been 'deleted' successfully."
     *   ),
     *   @OA\Response(
     *     response="default",
     *     description="Unable to 'delete' the category."
     *   )
     * )
     */
    public function destroy(Request $request, $uid)
    {
        // TODO ONLY TOGGLES THE status = 1/0
        // api/category/{categoryid} (DELETE)
        error_log('Deleting category of uid: ' . $uid);
        $category = $this->getCategory($request->user(), $uid);
        if ($this->isEmpty($category)) {
            $data['status'] = 'error';
            $data['msg'] = $this->getNotFoundMsg('Category');
            $data['data'] = null;
            $data['code'] = 404;
            return response()->json($data, 404);
        }
        $category = $this->deleteCategory($request->user(), $category->id);
        if ($this->isEmpty($category)) {
            $data['status'] = 'error';
            $data['msg'] = $this->getErrorMsg();
            $data['data'] = null;
            $data['code'] = 404;
            return response()->json($data, 404);
        } else {
            $data['status'] = 'success';
            $data['msg'] = $this->getDeletedSuccessMsg('Category');
            $data['data'] = $category;
            $data['code'] = 200;
            return response()->json($data, 200);
        }
    }

}
