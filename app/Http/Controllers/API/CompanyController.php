<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use DB;
use Carbon\Carbon;
use App\Company;
use Illuminate\Support\Facades\Hash;
use App\Traits\GlobalFunctions;
use App\Traits\NotificationFunctions;
use App\Traits\CompanyServices;
use App\Traits\LogServices;

class CompanyController extends Controller
{
    use GlobalFunctions, NotificationFunctions, CompanyServices, LogServices;

    /**
     * @OA\Get(
     *      path="/api/company",
     *      operationId="getCompanyList",
     *      tags={"CompanyControllerService"},
     *      summary="Get list of companies",
     *      description="Returns list of companies",
     *   @OA\Parameter(
     *     name="page",
     *     in="query",
     *     description="Page number",
     *     @OA\Schema(type="integer")
     *   ),
     *   @OA\Parameter(
     *     name="result",
     *     in="query",
     *     description="number of result",
     *     @OA\Schema(type="integer")
     *   ),
     *      @OA\Response(
     *          response=200,
     *          description="Successfully retrieved list of companies"
     *       ),
     *       @OA\Response(
     *          response="default",
     *          description="Unable to retrieve list of companies")
     *    )
     */
    public function index(Request $request)
    {
        error_log('Retrieving list of companies.');
        // api/company (GET)
        $companies = $this->getCompanyListing($request->user());
        if ($this->isEmpty($companies)) {
            $data['status'] = 'error';
            $data['data'] = null;
            $data['maximumPages'] = 0;
            $data['msg'] = $this->getNotFoundMsg('Companies');
            $data['code'] = 404;
            return response()->json($data, 404);
        } else {
            //Page Pagination Result List
            //Default return 10
            $paginateddata = $this->paginateResult($companies, $request->result, $request->page);
            $data['status'] = 'success';
            $data['data'] = $paginateddata;
            $data['maximumPages'] = $this->getMaximumPaginationPage($companies->count(), $request->result);
            $data['msg'] = $this->getRetrievedSuccessMsg('Companies');
            $data['code'] = 200;
            return response()->json($data, 200);
        }
    }
    /**
     * @OA\Get(
     *      path="/api/pluck/companies",
     *      operationId="pluckCompanyList",
     *      tags={"CompanyControllerService"},
     *      summary="pluck list of companies",
     *      description="Returns list of plucked companies",
     *   @OA\Parameter(
     *     name="page",
     *     in="query",
     *     description="Page number",
     *     @OA\Schema(type="integer")
     *   ),
     *   @OA\Parameter(
     *     name="result",
     *     in="query",
     *     description="number of result",
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
     *          description="Successfully retrieved list of companies"
     *       ),
     *       @OA\Response(
     *          response="default",
     *          description="Unable to retrieve list of companies")
     *    )
     */
    public function pluckIndex(Request $request)
    {
        error_log('Retrieving list of plucked companies.');
        // api/pluck/companies (GET)
        error_log("columns = " . collect($this->splitToArray($request->cols)));
        $companies = $this->pluckCompanyIndex($this->splitToArray($request->cols));
        if ($this->isEmpty($companies)) {
            $data['status'] = 'error';
            $data['data'] = null;
            $data['maximumPages'] = 0;
            $data['msg'] = $this->getNotFoundMsg('Companies');
            $data['code'] = 404;
            return response()->json($data, 404);
        } else {
            //Page Pagination Result List
            //Default return 10
            $paginateddata = $this->paginateResult($companies, $request->result, $request->page);
            $data['status'] = 'success';
            $data['data'] = $paginateddata;
            $data['maximumPages'] = $this->getMaximumPaginationPage($companies->count(), $request->result);
            $data['msg'] = $this->getRetrievedSuccessMsg('Companies');
            $data['code'] = 200;
            return response()->json($data, 200);
        }
    }

    /**
     * @OA\Get(
     *      path="/api/filter/company",
     *      operationId="filterCompanyList",
     *      tags={"CompanyControllerService"},
     *      summary="Filter list of companies",
     *      description="Returns list of filtered companies",
     *   @OA\Parameter(
     *     name="page",
     *     in="query",
     *     description="Page number",
     *     @OA\Schema(type="integer")
     *   ),
     *   @OA\Parameter(
     *     name="result",
     *     in="query",
     *     description="number of result",
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
     *          description="Successfully retrieved list of filtered companies"
     *       ),
     *       @OA\Response(
     *          response="default",
     *          description="Unable to retrieve list of companies")
     *    )
     */
    public function filter(Request $request)
    {
        error_log('Retrieving list of filtered companies.');
        // api/company/filter (GET)
        $params = collect([
            'keyword' => $request->keyword,
            'fromdate' => $request->fromdate,
            'todate' => $request->todate,
            'status' => $request->status,
            'company_id' => $request->company_id,
        ]);
        //Convert To Json Object
        $params = json_decode(json_encode($params));
        $companies = $this->filterCompanyListing($request->user(), $params);

        if ($this->isEmpty($companies)) {
            $data['data'] = null;
            $data['maximumPages'] = 0;
            $data['msg'] = $this->getNotFoundMsg('Companies');
            $data['code'] = 404;
            return response()->json($data, 404);
        } else {
            //Page Pagination Result List
            //Default return 10
            $paginateddata = $this->paginateResult($companies, $request->result, $request->page);
            $data['data'] = $paginateddata;
            $data['maximumPages'] = $this->getMaximumPaginationPage($companies->count(), $request->result);
            $data['msg'] = $this->getRetrievedSuccessMsg('Companies');
            $data['code'] = 200;
            return response()->json($data, 200);
        }

    }

    /**
     * @OA\Get(
     *      path="/api/pluck/filter/company",
     *      operationId="filterPluckedCompanyList",
     *      tags={"CompanyControllerService"},
     *      summary="Filter list of plucked companies",
     *      description="Returns list of filtered companies",
     *   @OA\Parameter(
     *     name="page",
     *     in="query",
     *     description="Page number",
     *     @OA\Schema(type="integer")
     *   ),
     *   @OA\Parameter(
     *     name="result",
     *     in="query",
     *     description="number of result",
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
     *     name="company_id",
     *     in="query",
     *     description="Company id for filter",
     *     @OA\Schema(type="string")
     *   ),
     *      @OA\Response(
     *          response=200,
     *          description="Successfully retrieved list of filtered companies"
     *       ),
     *       @OA\Response(
     *          response="default",
     *          description="Unable to retrieve list of companies")
     *    )
     */
    public function pluckFilter(Request $request)
    {
        error_log('Retrieving list of filtered and plucked companies.');
        // api/pluck/filter/company (GET)
        $params = collect([
            'keyword' => $request->keyword,
            'fromdate' => $request->fromdate,
            'todate' => $request->todate,
            'status' => $request->status,
            'company_id' => $request->company_id,
        ]);
        //Convert To Json Object
        $params = json_decode(json_encode($params));
        $companies = $this->pluckCompanyFilter($this->splitToArray($request->cols) , $params);

        if ($this->isEmpty($companies)) {
            $data['data'] = null;
            $data['maximumPages'] = 0;
            $data['msg'] = $this->getNotFoundMsg('Companies');
            $data['code'] = 404;
            return response()->json($data, 404);
        } else {
            //Page Pagination Result List
            //Default return 10
            $paginateddata = $this->paginateResult($companies, $request->result, $request->page);
            $data['data'] = $paginateddata;
            $data['maximumPages'] = $this->getMaximumPaginationPage($companies->count(), $request->result);
            $data['msg'] = $this->getRetrievedSuccessMsg('Companies');
            $data['code'] = 200;
            return response()->json($data, 200);
        }

    }

    /**
     * @OA\Get(
     *   tags={"CompanyControllerService"},
     *   path="/api/company/{uid}",
     *   summary="Retrieves company by companyId.",
     *     operationId="getCompanyByCompanyId",
     *   @OA\Parameter(
     *     name="uid",
     *     in="path",
     *     description="Company_ID, NOT 'ID'.",
     *     required=true,
     *     @OA\Schema(type="string")
     *   ),
     *   @OA\Response(
     *     response=200,
     *     description="Company has been retrieved successfully."
     *   ),
     *   @OA\Response(
     *     response="default",
     *     description="Unable to retrieve the company."
     *   )
     * )
     */
    public function show(Request $request, $uid)
    {
        // api/company/{companyid} (GET)
        error_log('Retrieving company of uid:' . $uid);
        $company = $this->getCompany($request->user(), $uid);
        if ($this->isEmpty($company)) {
            $data['data'] = null;
            $data['msg'] = $this->getNotFoundMsg('Company');
            $data['status'] = 'error';
            $data['code'] = 404;
            return response()->json($data, 404);
        } else {
            $data['data'] = $company;
            $data['msg'] = $this->getRetrievedSuccessMsg('Company');
            $data['status'] = 'success';
            $data['code'] = 200;
            return response()->json($data, 200);
        }
    }

    /**
     * @OA\Get(
     *      path="/api/pluck/company/{uid}",
     *      operationId="pluckCompany",
     *      tags={"CompanyControllerService"},
     *      summary="pluck company",
     *      description="Returns plucked companies",
     *   @OA\Parameter(
     *     name="uid",
     *     in="path",
     *     description="Company_ID, NOT 'ID'.",
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
     *          description="Successfully retrieved list of companies"
     *       ),
     *       @OA\Response(
     *          response="default",
     *          description="Unable to retrieve list of companies")
     *    )
     */
    public function pluckShow(Request $request , $uid)
    {
        error_log('Retrieving plucked companies.');
        // api/pluck/company/{uid} (GET)
        error_log("columns = " . collect($this->splitToArray($request->cols)));
        $company = $this->pluckCompany($this->splitToArray($request->cols) , $uid);
        if ($this->isEmpty($company)) {
            $data['data'] = null;
            $data['status'] = 'error';
            $data['msg'] = $this->getNotFoundMsg('Company');
            $data['code'] = 404;
            return response()->json($data, 404);
        } else {
            $data['status'] = 'success';
            $data['msg'] = $this->getRetrievedSuccessMsg('Company');
            $data['data'] = $company;
            $data['code'] = 200;
            return response()->json($data, 200);
        }
    }

    
    /**
     * @OA\Post(
     *   tags={"CompanyControllerService"},
     *   path="/api/company",
     *   summary="Creates a company.",
     *   operationId="createCompany",
     * @OA\Parameter(
     * name="name",
     * in="query",
     * description="Companyname",
     * required=true,
     * @OA\Schema(
     *              type="string"
     *          )
     * ),
     * @OA\Parameter(
     * name="companytypeid",
     * in="query",
     * description="Company Type ID",
     * required=true,
     * @OA\Schema(
     *              type="string"
     *          )
     * ),
     * @OA\Parameter(
     * name="email1",
     * in="query",
     * description="Email 1",
     * @OA\Schema(
     *              type="string"
     *          )
     * ),
     * @OA\Parameter(
     * name="email2",
     * in="query",
     * description="Email 2",
     * @OA\Schema(
     *              type="string"
     *          )
     * ),
     * @OA\Parameter(
     * name="regno",
     * in="query",
     * description="Registration No",
     * required=true,
     * @OA\Schema(
     *              type="string"
     *          )
     * ),
     * @OA\Parameter(
     * name="tel1",
     * in="query",
     * description="Contact No 1",
     * @OA\Schema(
     *              type="string"
     *          )
     * ),
     * @OA\Parameter(
     * name="tel2",
     * in="query",
     * description="Contact No 2",
     * @OA\Schema(
     *              type="string"
     *          )
     * ),
     * @OA\Parameter(
     * name="fax1",
     * in="query",
     * description="Fax",
     * @OA\Schema(
     *              type="string"
     *          )
     * ),
     * @OA\Parameter(
     * name="fax2",
     * in="query",
     * description="Fax 2",
     * @OA\Schema(
     *              type="string"
     *          )
     * ),
     * @OA\Parameter(
     * name="address1",
     * in="query",
     * description="Address",
     * @OA\Schema(
     *              type="string"
     *          )
     * ),
     * @OA\Parameter(
     * name="address2",
     * in="query",
     * description="Address 2",
     * @OA\Schema(
     *              type="string"
     *          )
     * ),
     * @OA\Parameter(
     * name="postcode",
     * in="query",
     * description="Post Code",
     * @OA\Schema(
     *              type="string"
     *          )
     * ),
     * @OA\Parameter(
     * name="state",
     * in="query",
     * description="State",
     * @OA\Schema(
     *              type="string"
     *          )
     * ),
     * @OA\Parameter(
     * name="city",
     * in="query",
     * description="City",
     * @OA\Schema(
     *  type="string"
     *  )
     * ),
     * @OA\Parameter(
     * name="Country",
     * in="query",
     * description="Country",
     * @OA\Schema(
     *              type="string"
     *          )
     * ),
     *   @OA\Response(
     *     response=200,
     *     description="Company has been created successfully."
     *   ),
     *   @OA\Response(
     *     response="default",
     *     description="Unable to create the company."
     *   )
     * )
     */
    public function store(Request $request)
    {
        // Can only be used by Authorized personnel
        // api/company (POST)
        $this->validate($request, [
            'email1' => 'nullable|email|max:191|unique:companies',
            'email2' => 'nullable|email|max:191|unique:companies',
            'fax1' => 'nullable|string|max:191|unique:companies',
            'fax2' => 'nullable|string|max:191|unique:companies',
            'tel1' => 'nullable|string|max:191|unique:companies',
            'tel2' => 'nullable|string|max:191|unique:companies',
            'companytypeid' => 'required|string',
            'name' => 'required|string',
        ]);
        error_log('Creating company.');
        $params = collect([
            'regno' => $request->regno,
            'name' => $request->name,
            'email1' => $request->email1,
            'email2' => $request->email2,
            'tel1' => $request->tel1,
            'tel2' => $request->tel2,
            'fax1' => $request->fax1,
            'fax2' => $request->fax2,
            'address1' => $request->address1,
            'address2' => $request->address2,
            'postcode' => $request->postcode,
            'state' => $request->state,
            'city' => $request->city,
            'country' => $request->country,
            'companytypeid' => $request->companytypeid,
        ]);
        //Convert To Json Object
        $params = json_decode(json_encode($params));
        $company = $this->createCompany($request->user(), $params);

        if ($this->isEmpty($company)) {
            $data['data'] = null;
            $data['status'] = 'error';
            $data['msg'] = $this->getErrorMsg();
            $data['code'] = 404;
            return response()->json($data, 404);
        } else {
            $data['status'] = 'success';
            $data['msg'] = $this->getCreatedSuccessMsg('Company');
            $data['data'] = $company;
            $data['code'] = 200;
            return response()->json($data, 200);
        }
    }


    /**
     * @OA\Put(
     *   tags={"CompanyControllerService"},
     *   path="/api/company/{uid}",
     *   summary="Update company by companyId.",
     *     operationId="updateCompanyByCompanyId",
     *   @OA\Parameter(
     *     name="uid",
     *     in="path",
     *     description="Company_ID, NOT 'ID'.",
     *     required=true,
     *     @OA\Schema(type="string")
     *   ),
     * @OA\Parameter(
     * name="name",
     * in="query",
     * description="Companyname",
     * required=true,
     * @OA\Schema(
     *              type="string"
     *          )
     * ),
     * @OA\Parameter(
     * name="companytypeid",
     * in="query",
     * description="Company Type ID",
     * required=true,
     * @OA\Schema(
     *              type="string"
     *          )
     * ),
     * @OA\Parameter(
     * name="email1",
     * in="query",
     * description="Email 1",
     * @OA\Schema(
     *              type="string"
     *          )
     * ),
     * @OA\Parameter(
     * name="email2",
     * in="query",
     * description="Email 2",
     * @OA\Schema(
     *              type="string"
     *          )
     * ),
     * @OA\Parameter(
     * name="regno",
     * in="query",
     * description="Registration No",
     * required=true,
     * @OA\Schema(
     *              type="string"
     *          )
     * ),
     * @OA\Parameter(
     * name="tel1",
     * in="query",
     * description="Contact No 1",
     * @OA\Schema(
     *              type="string"
     *          )
     * ),
     * @OA\Parameter(
     * name="tel2",
     * in="query",
     * description="Contact No 2",
     * @OA\Schema(
     *              type="string"
     *          )
     * ),
     * @OA\Parameter(
     * name="fax1",
     * in="query",
     * description="Fax",
     * @OA\Schema(
     *              type="string"
     *          )
     * ),
     * @OA\Parameter(
     * name="fax2",
     * in="query",
     * description="Fax 2",
     * @OA\Schema(
     *              type="string"
     *          )
     * ),
     * @OA\Parameter(
     * name="address1",
     * in="query",
     * description="Address",
     * @OA\Schema(
     *              type="string"
     *          )
     * ),
     * @OA\Parameter(
     * name="address2",
     * in="query",
     * description="Address 2",
     * @OA\Schema(
     *              type="string"
     *          )
     * ),
     * @OA\Parameter(
     * name="postcode",
     * in="query",
     * description="Post Code",
     * @OA\Schema(
     *              type="string"
     *          )
     * ),
     * @OA\Parameter(
     * name="state",
     * in="query",
     * description="State",
     * @OA\Schema(
     *              type="string"
     *          )
     * ),
     * @OA\Parameter(
     * name="city",
     * in="query",
     * description="City",
     * @OA\Schema(
     *  type="string"
     *  )
     * ),
     * @OA\Parameter(
     * name="Country",
     * in="query",
     * description="Country",
     * @OA\Schema(
     *              type="string"
     *          )
     * ),
     *   @OA\Response(
     *     response=200,
     *     description="Company has been updated successfully."
     *   ),
     *   @OA\Response(
     *     response="default",
     *     description="Unable to update the company."
     *   )
     * )
     */
    public function update(Request $request, $uid)
    {
        // api/company/{companyid} (PUT) 
        error_log('Updating company of uid: ' . $uid);
        $company = $this->getCompany($request->user(), $uid);
        error_log($company);
        $this->validate($request, [
            'email1' => 'required|email|max:191|unique:companies,email1,' . $company->id.'|unique:companies,email2,' . $company->id,
            'email2' => 'required|email|max:191|unique:companies,email1,' . $company->id.'|unique:companies,email2,' . $company->id,
            'fax1' => 'required|string|max:191|unique:companies,fax1,' . $company->id.'|unique:companies,fax2,' . $company->id,
            'fax2' => 'required|string|max:191|unique:companies,fax1,' . $company->id.'|unique:companies,fax2,' . $company->id,
            'tel1' => 'required|string|max:191|unique:companies,tel1,' . $company->id.'|unique:companies,tel2,' . $company->id,
            'tel2' => 'required|string|max:191|unique:companies,tel1,' . $company->id.'|unique:companies,tel2,' . $company->id,
            'name' => 'required|string|max:191',
            'companytypeid' => 'required|string',
        ]);
        
        if ($this->isEmpty($company)) {
            $data['data'] = null;
            $data['msg'] = $this->getNotFoundMsg('Company');
            $data['status'] = 'error';
            $data['code'] = 404;
            return response()->json($data, 404);
        }
        $params = collect([
            'regno' => $request->regno,
            'name' => $request->name,
            'email1' => $request->email1,
            'email2' => $request->email2,
            'tel1' => $request->tel1,
            'tel2' => $request->tel2,
            'fax1' => $request->fax1,
            'fax2' => $request->fax2,
            'address1' => $request->address1,
            'address2' => $request->address2,
            'postcode' => $request->postcode,
            'state' => $request->state,
            'city' => $request->city,
            'country' => $request->country,
            'companytypeid' => $request->companytypeid,
        ]);
        //Convert To Json Object
        $params = json_decode(json_encode($params));
        $company = $this->updateCompany($request->user(), $company, $params);
        if ($this->isEmpty($company)) {
            $data['data'] = null;
            $data['msg'] = $this->getErrorMsg('Company');
            $data['status'] = 'error';
            $data['code'] = 404;
            return response()->json($data, 404);
        } else {
            $data['status'] = 'success';
            $data['msg'] = $this->getUpdatedSuccessMsg('Company');
            $data['data'] = $company;
            $data['status'] = 'success';
            $data['code'] = 200;
            return response()->json($data, 200);
        }
    }

    /**
     * @OA\Delete(
     *   tags={"CompanyControllerService"},
     *   path="/api/company/{uid}",
     *   summary="Set company's 'status' to 0.",
     *     operationId="deleteCompanyByCompanyId",
     *   @OA\Parameter(
     *     name="uid",
     *     in="path",
     *     description="Company ID, NOT 'ID'.",
     *     required=true,
     *     @OA\SChema(type="string")
     *   ),
     *   @OA\Response(
     *     response=200,
     *     description="Company has been 'deleted' successfully."
     *   ),
     *   @OA\Response(
     *     response="default",
     *     description="Unable to 'delete' the company."
     *   )
     * )
     */
    public function destroy(Request $request, $uid)
    {
        // TODO ONLY TOGGLES THE status = 1/0
        // api/company/{companyid} (DELETE)
        error_log('Deleting company of uid: ' . $uid);
        $company = $this->getCompany($request->user(), $uid);
        if ($this->isEmpty($company)) {
            $data['status'] = 'error';
            $data['msg'] = $this->getNotFoundMsg('Company');
            $data['data'] = null;
            $data['code'] = 404;
            return response()->json($data, 404);
        }
        $company = $this->deleteCompany($request->user(), $company->id);
        if ($this->isEmpty($company)) {
            $data['status'] = 'error';
            $data['msg'] = $this->getErrorMsg();
            $data['data'] = null;
            $data['code'] = 404;
            return response()->json($data, 404);
        } else {
            $data['status'] = 'success';
            $data['msg'] = $this->getDeletedSuccessMsg('Company');
            $data['data'] = $company;
            $data['code'] = 200;
            return response()->json($data, 200);
        }
    }

}
