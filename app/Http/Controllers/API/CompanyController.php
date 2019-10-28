<?php


namespace App\Http\Controllers\API;

use App\Company;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Traits\GlobalFunctions;
use App\Traits\NotificationFunctions;
use DB;
use Carbon\Carbon;

class CompanyController extends Controller
{
    use GlobalFunctions, NotificationFunctions;
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $companies = Company::where('status' , true)->get();
        //Page Pagination Result List
        //Default return 10
        $paginateddata = $this->paginateResult($companies , $request->result, $request->page);
        $data['data'] = $paginateddata;
        $data['maximunPage'] = $this->getMaximumPaginationPage($companies->count(), $request->result);
        $data['msg'] = $this->getRetrievedSuccessMsg('Companies');
        return response()->json($data, 200);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Company  $company
     * @return \Illuminate\Http\Response
     */
    public function show($uid)
    {
        $company = Company::where('uid',$uid)->with(['companytype' => function($q){
            $q->where('status', 1);
        }])->first();
    
        if(empty($company) || $company->companytype == null){
            $payload['status'] = 'error';
            $payload['msg'] = 'Company Not Found.';
            return response()->json($payload, 404);
        }
        $payload['status'] = 'success';
        $payload['msg'] = 'Company Retrieved.';
        $payload['data'] = $company;

        return response()->json($payload, 200);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Company  $company
     * @return \Illuminate\Http\Response
     */
    public function edit(Company $company)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Company  $company
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $uid)
    {
        $company = Company::where('uid',$uid)->where('status', true)->first();
        if(empty($company)){
            $payload['status'] = 'error';
            $payload['msg'] = 'Company not found.';
            return response()->json($payload, 404);
        }

        $this->validate($request, [
            'name' => 'required|string|max:191',
            'regno' => 'string|nullable',
        ]);

        // $companytype = CompanyType::where('name', $request->company_type)->first();
        // if(empty($companytype)){
        //     $payload['status'] = 'error';
        //     $payload['msg'] = 'Company Type Not Found.';

        //     return response()->json($payload, 404);
        // }
        // $company->companytype()->associate($companytype);

        $company->name = $request->name;
        $company->regno = $request->regno;
        $company->fax1 = $request->fax1;
        $company->fax2 = $request->fax2;
        $company->email1 = $request->email1;
        $company->email2 = $request->email2;
        $company->address1 = $request->address1;
        $company->address2 = $request->address2;
        $company->postcode = $request->postcode;
        $company->city = $request->city;
        $company->state = $request->state;
        $company->country = $request->country;
        $company->status = true;
        $company->lastedit_by = $request->user()->name;

         try{
            $company->save();
        }catch(Exception $e){
            $payload['status'] = 'error';
            $payload['msg'] = 'Company cannot be update.';

            return response()->json($payload, 500);
        }
        // $account = Account::where('id', $request->account['id'])->first();
        // if(empty($account)){
        //     $payload['status'] = 'error';
        //     $payload['msg'] = 'Account Not Found.';

        //     return response()->json($payload, 404);
        // }

        // $account->fname = $request->fname;
        // $account->lname = $request->lname;
        // $account->email = $request->email1;
        // $account->icno = $request->icno;
        // $account->tel1 = $request->tel1;
        // $account->tel2 = $request->tel2;
        
        // $account->company()->associate($company);
        
        // try{
        //     $account->save();
        // }catch(Exception $e){
        //     DB::rollBack();
        //     $payload['status'] = 'error';
        //     $payload['msg'] = 'Account cannot be update.';

        //     return response()->json($payload, 500);
        // }
        // $group= Group::where('company_id', $request->id)->first();
        // $group->name = $company->name . '\'s default group';
        // $group->company()->associate($company);
        // try{
        //     $group->save();
        // }catch(Exception $e){
        //     DB::rollBack();
        //     $payload['status'] = 'error';
        //     $payload['msg'] = 'Group cannot be update.';

        //     return response()->json($payload, 500);
        // }

        DB::commit();
        $payload['status'] = 'success';
        $payload['msg'] = 'Company successfully updated.';
        $payload['data'] =  $company->refresh();

        return response()->json($payload, 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Company  $company
     * @return \Illuminate\Http\Response
     */
    public function destroy(Company $company)
    {
        //
    }
}
