<?php

namespace App\Http\Controllers\Organisation;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Config;
use App\Models\OrgUser;
use Illuminate\Http\Exceptions\HttpResponseException;
use Exception;
use Session;
use Storage;
use DB;
use \stdClass;
use App\Traits\SendSMS;

class ProcessLoan extends Controller
{
    use SendSMS;
    public function convertToObject($array) {
        $object = new stdClass();
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                $value = $this->convertToObject($value);
            }
            $object->$key = $value;
        }
        return $object;
    }

    public function get_member_info(Request $request){
        try {

            $sql = DB::select("Select UDF_GET_ORG_SCHEMA(?) as db;",[$request->org_id]);
            if(!$sql){
              throw new Exception;
            }
            $org_schema = $sql[0]->db;
            $db = Config::get('database.connections.mysql');
            $db['database'] = $org_schema;
            config()->set('database.connections.coops', $db);

            $sql = DB::connection('coops')->select("Call USP_GET_LOAN_MEMBER_DATA(?,?);",[$request->mem_no,$request->date]);

            if (empty($sql)) {
                // Custom validation for no data found
                return response()->json([
                    'message' => 'No Data Found',
                    'details' => [],
                ], 200);
            }

            $result = $sql[0]->Error_No;
            $message = $sql[0]->Message;

            if($result<0){
                return response()->json([
                    'message' => 'Error Found',
                    'details' => $message,
                ],200);
            }
            else{
                return response()->json([
                    'message' => 'Data Found',
                    'details' => $sql,
                ],200);
            }
            

        } catch (Exception $ex) {
            $response = response()->json([
                'message' => 'Error Found',
                'details' => $ex->getMessage(),
            ],400);

            throw new HttpResponseException($response);
        }
    }

    public function get_loan_product(Request $request){
        try {

            $sql = DB::select("Select Id,Prod_Sh_Name,Loan_Type From map_org_loan_product Where org_Id=?;",[$request->org_id]);

            if (empty($sql)) {
                // Custom validation for no data found
                return response()->json([
                    'message' => 'No Data Found',
                    'details' => [],
                ], 200);
            }
           
            return response()->json([
                'message' => 'Data Found',
                'details' => $sql,
            ],200);
        

        } catch (Exception $ex) {
            $response = response()->json([
                'message' => 'Error Found',
                'details' => $ex->getMessage(),
            ],400);

            throw new HttpResponseException($response);
        }
    }

    public function get_prod_duration(Request $request){
        try {

            $sql = DB::select("Select UDF_GET_ORG_SCHEMA(?) as db;",[$request->org_id]);
            if(!$sql){
              throw new Exception;
            }
            $org_schema = $sql[0]->db;
            $db = Config::get('database.connections.mysql');
            $db['database'] = $org_schema;
            config()->set('database.connections.coops', $db);

            $sql = DB::connection('coops')->select("Call USP_GET_LOAN_PARAM(?,?);",[$request->prod_id,2]);

            if (empty($sql)) {
                // Custom validation for no data found
                return response()->json([
                    'message' => 'No Data Found',
                    'details' => [],
                ], 200);
            }

                return response()->json([
                    'message' => 'Data Found',
                    'details' => $sql,
                ],200);

        } catch (Exception $ex) {
            $response = response()->json([
                'message' => 'Error Found',
                'details' => $ex->getMessage(),
            ],400);

            throw new HttpResponseException($response);
        }
    }

    public function get_prod_repay_mode(Request $request){
        try {

            $sql = DB::select("Select UDF_GET_ORG_SCHEMA(?) as db;",[$request->org_id]);
            if(!$sql){
              throw new Exception;
            }
            $org_schema = $sql[0]->db;
            $db = Config::get('database.connections.mysql');
            $db['database'] = $org_schema;
            config()->set('database.connections.coops', $db);

            $sql = DB::connection('coops')->select("Call USP_GET_LOAN_PARAM(?,?);",[$request->prod_id,1]);

            if (empty($sql)) {
                // Custom validation for no data found
                return response()->json([
                    'message' => 'No Data Found',
                    'details' => [],
                ], 200);
            }

                return response()->json([
                    'message' => 'Data Found',
                    'details' => $sql,
                ],200);

        } catch (Exception $ex) {
            $response = response()->json([
                'message' => 'Error Found',
                'details' => $ex->getMessage(),
            ],400);

            throw new HttpResponseException($response);
        }
    }

    public function check_appl_amount(Request $request){
        try {

            $sql = DB::select("Select UDF_GET_ORG_SCHEMA(?) as db;",[$request->org_id]);
            if(!$sql){
              throw new Exception;
            }
            $org_schema = $sql[0]->db;
            $db = Config::get('database.connections.mysql');
            $db['database'] = $org_schema;
            config()->set('database.connections.coops', $db);

            $sql = DB::connection('coops')->select("Call USP_CHECK_LOAN_PARAM(?,?,?,?,?);",[$request->prod_id,$request->amount,null,null,1]);

            if (empty($sql)) {
                // Custom validation for no data found
                return response()->json([
                    'message' => 'No Data Found',
                    'details' => [],
                ], 200);
            }

            $result = $sql[0]->Error_No;
            $message = $sql[0]->Message;

            if($result<0){
                return response()->json([
                    'message' => 'Error Found',
                    'details' => $message,
                ],200);
            }
            else{
                return response()->json([
                    'message' => 'Data Found',
                    'details' => $message,
                ],200);
            }
            

        } catch (Exception $ex) {
            $response = response()->json([
                'message' => 'Error Found',
                'details' => $ex->getMessage(),
            ],400);

            throw new HttpResponseException($response);
        }
    }

    public function check_duration(Request $request){
        try {

            $sql = DB::select("Select UDF_GET_ORG_SCHEMA(?) as db;",[$request->org_id]);
            if(!$sql){
              throw new Exception;
            }
            $org_schema = $sql[0]->db;
            $db = Config::get('database.connections.mysql');
            $db['database'] = $org_schema;
            config()->set('database.connections.coops', $db);

            $sql = DB::connection('coops')->select("Call USP_CHECK_LOAN_PARAM(?,?,?,?,?);",[$request->prod_id,null,$request->duration,$request->dur_unit,2]);

            if (empty($sql)) {
                // Custom validation for no data found
                return response()->json([
                    'message' => 'No Data Found',
                    'details' => [],
                ], 200);
            }

            $result = $sql[0]->Error_No;
            $message = $sql[0]->Message;

            if($result<0){
                return response()->json([
                    'message' => 'Error Found',
                    'details' => $message,
                ],200);
            }
            else{
                return response()->json([
                    'message' => 'Data Found',
                    'details' => $message,
                ],200);
            }
            

        } catch (Exception $ex) {
            $response = response()->json([
                'message' => 'Error Found',
                'details' => $ex->getMessage(),
            ],400);

            throw new HttpResponseException($response);
        }
    }

    public function get_interest_rate(Request $request){
        try {

            $sql = DB::select("Select Roi From map_org_loan_product Where org_Id=? And Id=?;",[$request->org_id,$request->prod_id]);

            if (empty($sql)) {
                // Custom validation for no data found
                return response()->json([
                    'message' => 'No Data Found',
                    'details' => [],
                ], 200);
            }
           
            return response()->json([
                'message' => 'Data Found',
                'details' => $sql,
            ],200);
        

        } catch (Exception $ex) {
            $response = response()->json([
                'message' => 'Error Found',
                'details' => $ex->getMessage(),
            ],400);

            throw new HttpResponseException($response);
        }
    }

    public function get_emi_amount(Request $request){
        try {

            $sql = DB::select("Select UDF_GET_ORG_SCHEMA(?) as db;",[$request->org_id]);
            if(!$sql){
              throw new Exception;
            }
            $org_schema = $sql[0]->db;
            $db = Config::get('database.connections.mysql');
            $db['database'] = $org_schema;
            config()->set('database.connections.coops', $db);

            $sql = DB::connection('coops')->select("Select UDF_GEN_LOAN_EMI(?,?,?) As Emi;",[$request->principal,$request->roi,$request->duration]);

            if (empty($sql)) {
                // Custom validation for no data found
                return response()->json([
                    'message' => 'No Data Found',
                    'details' => [],
                ], 200);
            }

            $result = $sql[0]->Emi;
          
            return response()->json([
                'message' => 'Data Found',
                'details' => $result,
            ],200);
            
        } catch (Exception $ex) {
            $response = response()->json([
                'message' => 'Error Found',
                'details' => $ex->getMessage(),
            ],400);

            throw new HttpResponseException($response);
        }
    }

    public function chek_loan_eligible(Request $request){
        try {

            $sql = DB::select("Select UDF_GET_ORG_SCHEMA(?) as db;",[$request->org_id]);
            if(!$sql){
              throw new Exception;
            }
            $org_schema = $sql[0]->db;
            $db = Config::get('database.connections.mysql');
            $db['database'] = $org_schema;
            config()->set('database.connections.coops', $db);

            $sql = DB::connection('coops')->select("Call USP_CHECK_ELIGIBLE_LOAN(?,?,?);",[$request->mem_id,$request->prod_id,$request->date]);

            if (empty($sql)) {
                // Custom validation for no data found
                return response()->json([
                    'message' => 'No Data Found',
                    'details' => [],
                ], 200);
            }

            $error_No = $sql[0]->Error_No;
            $message = $sql[0]->Message;

            if($error_No<0){
                return response()->json([
                    'message' => 'Error Found',
                    'details' => $message,
                ],200);
            }
            else{
                return response()->json([
                    'message' => 'Data Found',
                    'details' => 'All Check Done !!',
                ],200);
            }
            
        } catch (Exception $ex) {
            $response = response()->json([
                'message' => 'Error Found',
                'details' => $ex->getMessage(),
            ],400);

            throw new HttpResponseException($response);
        }
    }

    public function process_loan_application(Request $request){
        $validator = Validator::make($request->all(),[
            'org_id' => 'required',
            'appl_date' => 'required',
            'mem_id' => 'required',
            'appl_no' => 'required',
            'prod_id' => 'required',
            'roi' => 'required',
            'duration' => 'required',
            'dur_unit' => 'required',
            'appl_amount' => 'required',
            'repay_mode' => 'required',
            'repay_within' => 'required',
            'branch_Id' => 'required'
        ]);
        if($validator->passes()){
        try {

            $sql = DB::select("Select UDF_GET_ORG_SCHEMA(?) as db;",[$request->org_id]);
            if(!$sql){
              throw new Exception;
            }
            $org_schema = $sql[0]->db;
            $db = Config::get('database.connections.mysql');
            $db['database'] = $org_schema;
            config()->set('database.connections.coops', $db);
            DB::connection('coops')->beginTransaction();

            $cash_drop_table = DB::connection('coops')->statement("Drop Temporary Table If Exists tempsecurity;");
            $cash_create_table = DB::connection('coops')->statement("Create Temporary Table tempsecurity
                                                                    (
                                                                        Dep_Id			Int,
                                                                        Dep_Balance		Numeric(18,2)
                                                                    );");
                if(is_array($request->sec_details)){
                    $cash_data = $this->convertToObject($request->sec_details);

                    foreach ($cash_data as $denom_data) {
                        $meter_insert =  DB::connection('coops')->statement("Insert Into tempsecurity (Dep_Id,Dep_Balance) Values (?,?);",[$denom_data->dep_id,$denom_data->balance]);
                    }
                }

            $sql = DB::connection('coops')->statement("Call USP_LOAN_ADD_APPLICATION(?,?,?,?,?,?,?,?,?,?,?,?,?,?,@error,@message);",[$request->appl_date,$request->mem_id,$request->appl_no,$request->ref_ac_no,$request->ledg_folio,$request->prod_id,$request->roi,$request->duration,$request->dur_unit,$request->appl_amount,$request->repay_mode,$request->repay_within,auth()->user()->Id,$request->branch_Id]);

            if(!$sql){
                throw new Exception('Operation Error Found !!');
            }
            $result = DB::connection('coops')->select("Select @error As Error_No,@message As Message;");
            $error_No = $result[0]->Error_No;
            $message = $result[0]->Message;

            if($error_No<0){
                DB::connection('coops')->rollBack();
                return response()->json([
                    'message' => 'Error Found',
                    'details' => $message,
                ],200);
            }
            else{
                DB::connection('coops')->commit();
                return response()->json([
                    'message' => 'Success',
                    'details' => $message,
                ],200);
            }
            
        } catch (Exception $ex) {
            DB::connection('coops')->rollBack();
            $response = response()->json([
                'message' => 'Error Found',
                'details' => $ex->getMessage(),
            ],400);

            throw new HttpResponseException($response);
        }
    }
    else{
        $errors = $validator->errors();

            $response = response()->json([
                'message' => 'Invalid data send',
                'details' => $errors->messages(),
            ],400);
        
            throw new HttpResponseException($response);
    } 
    }

    public function get_pending_disb_list(Request $request){
        try {

            $sql = DB::select("Select UDF_GET_ORG_SCHEMA(?) as db;",[$request->org_id]);
            if(!$sql){
              throw new Exception;
            }
            $org_schema = $sql[0]->db;
            $db = Config::get('database.connections.mysql');
            $db['database'] = $org_schema;
            config()->set('database.connections.coops', $db);

            $sql = DB::connection('coops')->select("Call USP_LOAN_GET_DISB_LIST(?);",[$request->branch_id]);

            if (empty($sql)) {
                // Custom validation for no data found
                return response()->json([
                    'message' => 'No Data Found',
                    'details' => [],
                ], 200);
            }

                return response()->json([
                    'message' => 'Data Found',
                    'details' => $sql,
                ],200);

        } catch (Exception $ex) {
            $response = response()->json([
                'message' => 'Error Found',
                'details' => $ex->getMessage(),
            ],400);

            throw new HttpResponseException($response);
        }
    }

    public function search_account(Request $request){
        try {
            // Get organization schema
            $sql = DB::select("SELECT UDF_GET_ORG_SCHEMA(?) as db;", [$request->org_id]);
            if (!$sql) {
                throw new Exception('Database schema not found.');
            }
        
            $org_schema = $sql[0]->db;
            $db = Config::get('database.connections.mysql');
            $db['database'] = $org_schema;
            config()->set('database.connections.coops', $db);
        
            // Get pagination parameters
            $perPage = request()->get('limit', 10); // Default items per page: 10
            $page = request()->get('page', 1); // Default page: 1
            $offset = ($page - 1) * $perPage;
        
            // Fetch all results from the stored procedure
            $results = DB::connection('coops')->select(
                "CALL USP_LOAN_SEARCH_ACCOUNT(?,?,?);",
                [$request->member_name, $request->member_no, $request->mode]
            );
        
            // Convert results to a collection for manual pagination
            $collection = collect($results);
        
            // Paginate results
            $paginatedData = $collection->slice($offset, $perPage)->values();
            $total = $collection->count();
        
            if ($paginatedData->isEmpty()) {
                return response()->json([
                    'message' => 'No Data Found',
                    'data' => [],
                ], 200);
            }
        
            return response()->json([
                'message' => 'Data Found',
                'data' => [
                    'current_page' => $page,
                    'per_page' => $perPage,
                    'total' => $total,
                    'last_page' => ceil($total / $perPage),
                    'data' => $paginatedData,
                ],
            ], 200);
        
        } catch (Exception $ex) {
            $response = response()->json([
                'message' => 'Error Found',
                'details' => $ex->getMessage(),
            ], 400);
        
            throw new HttpResponseException($response);
        }
        
    }

    public function generate_schdule(Request $request){
        try {

            $sql = DB::select("Select UDF_GET_ORG_SCHEMA(?) as db;",[$request->org_id]);
            if(!$sql){
              throw new Exception;
            }
            $org_schema = $sql[0]->db;
            $db = Config::get('database.connections.mysql');
            $db['database'] = $org_schema;
            config()->set('database.connections.coops', $db);

            $sql = DB::connection('coops')->select("Call USP_LOAN_SCHDULE_DATA(?,?);",[$request->acct_id,$request->mode]);

            if (empty($sql)) {
                // Custom validation for no data found
                return response()->json([
                    'message' => 'No Data Found',
                    'details' => [],
                ], 200);
            }

                return response()->json([
                    'message' => 'Data Found',
                    'details' => $sql,
                ],200);

        } catch (Exception $ex) {
            $response = response()->json([
                'message' => 'Error Found',
                'details' => $ex->getMessage(),
            ],400);

            throw new HttpResponseException($response);
        }
    }

    public function get_dep_share_balance(Request $request){
        try {

            $sql = DB::select("Select UDF_GET_ORG_SCHEMA(?) as db;",[$request->org_id]);
            if(!$sql){
              throw new Exception;
            }
            $org_schema = $sql[0]->db;
            $db = Config::get('database.connections.mysql');
            $db['database'] = $org_schema;
            config()->set('database.connections.coops', $db);

            $sql = DB::connection('coops')->select("Call USP_LOAN_GET_DISB_SHDEP_BALANCE(?,?,?);",[$request->mem_id,$request->prod_id,$request->date]);

            if (empty($sql)) {
                // Custom validation for no data found
                return response()->json([
                    'message' => 'No Data Found',
                    'details' => [],
                ], 200);
            }

                return response()->json([
                    'message' => 'Data Found',
                    'details' => $sql,
                ],200);
            
        } catch (Exception $ex) {
            $response = response()->json([
                'message' => 'Error Found',
                'details' => $ex->getMessage(),
            ],400);

            throw new HttpResponseException($response);
        }
    }

    public function get_disb_cal_amount(Request $request){
        try {

            $sql = DB::select("Select UDF_GET_ORG_SCHEMA(?) as db;",[$request->org_id]);
            if(!$sql){
              throw new Exception;
            }
            $org_schema = $sql[0]->db;
            $db = Config::get('database.connections.mysql');
            $db['database'] = $org_schema;
            config()->set('database.connections.coops', $db);

            $sql = DB::connection('coops')->select("Call USP_LOAN_DISB_NEED_AMOUNT(?,?,?,?,?);",[$request->mem_id,$request->share_bal,$request->prod_id,$request->disb_amount,$request->date]);

            if (empty($sql)) {
                // Custom validation for no data found
                return response()->json([
                    'message' => 'No Data Found',
                    'details' => [],
                ], 200);
            }

                return response()->json([
                    'message' => 'Data Found',
                    'details' => $sql,
                ],200);
            
        } catch (Exception $ex) {
            $response = response()->json([
                'message' => 'Error Found',
                'details' => $ex->getMessage(),
            ],400);

            throw new HttpResponseException($response);
        }
    }
    public function process_loan_disburse(Request $request){
        $validator = Validator::make($request->all(),[
            'org_id' => 'required',
            'disb_date' => 'required',
            'prod_id' => 'required',
            'act_id' => 'required',
            'mem_id' => 'required',
            'disb_amt' => 'required',
            'fin_id' => 'required',
            'branch_Id' => 'required'
        ]);
        if($validator->passes()){
        try {

            $sql = DB::select("Select UDF_GET_ORG_SCHEMA(?) as db;",[$request->org_id]);
            if(!$sql){
              throw new Exception;
            }
            $org_schema = $sql[0]->db;
            $db = Config::get('database.connections.mysql');
            $db['database'] = $org_schema;
            config()->set('database.connections.coops', $db);

            DB::connection('coops')->beginTransaction();

                $cash_drop_table = DB::connection('coops')->statement("Drop Temporary Table If Exists tempcashnote;");
                $cash_create_table = DB::connection('coops')->statement("Create Temporary Table tempcashnote
                                                    (
                                                        Denom_Id			Int,
                                                        Denom_In_Val		Int,
                                                        Denom_Out_Val		Int,
                                                        Denom_Amt			Numeric(18,2)
                                                    );");
                if(is_array($request->cash_details)){
                    $cash_data = $this->convertToObject($request->cash_details);

                    foreach ($cash_data as $denom_data) {
                        $meter_insert =  DB::connection('coops')->statement("Insert Into tempcashnote (Denom_Id,Denom_In_Val,Denom_Out_Val,Denom_Amt) Values (?,?,?,?);",[$denom_data->note_id,$denom_data->in_qnty,$denom_data->out_qnty,$denom_data->tot_amount]);
                    }
                }

            $sql = DB::connection('coops')->statement("Call USP_LOAN_POST_DISBURSE(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,@error,@message,@acct_no,@mobile,@disb_date,@disb_amt);",[$request->disb_date,$request->ref_vouch,$request->prod_id,$request->act_id,$request->mem_id,$request->share_id,$request->dep_id,$request->disb_amt,$request->share_amt,$request->dep_amt,$request->ins_amt,$request->mis_amt,$request->share_gl,$request->dep_gl,$request->mis_gl,$request->sb_id,$request->bank_id,$request->fin_id,$request->branch_Id,auth()->user()->Id]);

            if(!$sql){
                throw new Exception('Operation Not Complete !!');
            }
            $result = DB::connection('coops')->select("Select @error As Error_No,@message As Message,@acct_no As Account,@disb_date As Date,@disb_amt As Amount,@mobile As Mobile");
            $error_No = $result[0]->Error_No;
            $message = $result[0]->Message;
            $acct_no = $result[0]->Account;
            $mobile = $result[0]->Mobile;
            $disb_date = $result[0]->Date;
            $disb_amt = $result[0]->Amount;

            if($error_No<0){
                DB::connection('coops')->rollBack();
                return response()->json([
                    'message' => 'Error Found',
                    'details' => $message,
                ],200);
            }
            else{
                DB::connection('coops')->commit();
                if($mobile<>0 || preg_match('/^\d{10}$/', $mobile)){
                    $this->send_on_loan_disburse($request->org_id,$acct_no,$disb_amt,$disb_date,$mobile);
                }
                return response()->json([
                    'message' => 'Success',
                    'details' => $message,
                ],200);
            }
   
        } catch (Exception $ex) {
            DB::connection('coops')->rollBack();
            $response = response()->json([
                'message' => 'Error Found',
                'details' => $ex->getMessage(),
            ],400);

            throw new HttpResponseException($response);
        }
    }
    else{
        $errors = $validator->errors();

            $response = response()->json([
                'message' => 'Invalid data send',
                'details' => $errors->messages(),
            ],400);
        
            throw new HttpResponseException($response);
    }   
    }

    public function get_repay_data(Request $request){
        try {

            $sql = DB::select("Select UDF_GET_ORG_SCHEMA(?) as db;",[$request->org_id]);
            if(!$sql){
              throw new Exception;
            }
            $org_schema = $sql[0]->db;
            $db = Config::get('database.connections.mysql');
            $db['database'] = $org_schema;
            config()->set('database.connections.coops', $db);

            $sql = DB::connection('coops')->select("Call USP_GET_LON_REPAY_DATA(?,?);",[$request->acct_no,$request->date]);

            if (empty($sql)) {
                // Custom validation for no data found
                return response()->json([
                    'message' => 'No Data Found',
                    'details' => [],
                ], 200);
            }

            $error = $sql[0]->Err_No;
            $message = $sql[0]->Message;

            if($error<0){
                return response()->json([
                    'message' => 'Error Found',
                    'details' => $message,
                ],200);
            }
            else{
                return response()->json([
                    'message' => 'Data Found',
                    'details' => $sql,
                ],200);
            }

        } catch (Exception $ex) {
            $response = response()->json([
                'message' => 'Error Found',
                'details' => $ex->getMessage(),
            ],400);

            throw new HttpResponseException($response);
        }
    }

    public function process_loan_repayment(Request $request){
        $validator = Validator::make($request->all(),[
            'org_id' => 'required',
            'acct_id' => 'required',
            'mem_id' => 'required',
            'date' => 'required',
            'prn_amt' => 'required',
            'intt_amt' => 'required',
            'due_intt' => 'required',
            'prn_gl' => 'required',
            'intt_gl' => 'required',
            'fin_id' => 'required',
            'branch_Id' => 'required'
        ]);
        if($validator->passes()){
        try {

            $sql = DB::select("Select UDF_GET_ORG_SCHEMA(?) as db;",[$request->org_id]);
            if(!$sql){
              throw new Exception;
            }
            $org_schema = $sql[0]->db;
            $db = Config::get('database.connections.mysql');
            $db['database'] = $org_schema;
            config()->set('database.connections.coops', $db);

            DB::connection('coops')->beginTransaction();

            $cash_drop_table = DB::connection('coops')->statement("Drop Temporary Table If Exists tempcashnote;");
                $cash_create_table = DB::connection('coops')->statement("Create Temporary Table tempcashnote
                                                    (
                                                        Denom_Id			Int,
                                                        Denom_In_Val		Int,
                                                        Denom_Out_Val		Int,
                                                        Denom_Amt			Numeric(18,2)
                                                    );");
                if(is_array($request->cash_details)){
                    $cash_data = $this->convertToObject($request->cash_details);

                    foreach ($cash_data as $denom_data) {
                        $meter_insert =  DB::connection('coops')->statement("Insert Into tempcashnote (Denom_Id,Denom_In_Val,Denom_Out_Val,Denom_Amt) Values (?,?,?,?);",[$denom_data->note_id,$denom_data->in_qnty,$denom_data->out_qnty,$denom_data->tot_amount]);
                    }
                }

            $sql = DB::connection('coops')->statement("Call USP_LOAN_POST_REPAY(?,?,?,?,?,?,?,?,?,?,?,?,?,?,@error,@message,@mobile,@acct,@prn,@intt,@outs,@repay_date);",[$request->acct_id,$request->mem_id,$request->date,$request->ref_vouch,$request->prn_amt,$request->intt_amt,$request->due_intt,$request->prn_gl,$request->intt_gl,$request->bank_id,$request->sb_id,$request->fin_id,$request->branch_Id,auth()->user()->Id]);

            if(!$sql){
                throw new Exception('Operation Not Complete !!');
            }
            $result = DB::connection('coops')->select("Select @error As Error_No,@message As Message,@mobile As Mobile,@acct As Account,@prn As Prn,@intt As Intt,@outs As Outs,@repay_date As Repay_Date;");
            $error_No = $result[0]->Error_No;
            $message = $result[0]->Message;
            $mobile = $result[0]->Mobile;
            $acct = $result[0]->Account;
            $prn = $result[0]->Prn;
            $intt = $result[0]->Intt;
            $outs = $result[0]->Outs;
            $repay_date = $result[0]->Repay_Date;

            if($error_No<0){
                DB::connection('coops')->rollBack();
                return response()->json([
                    'message' => 'Error Found',
                    'details' => $message,
                ],200);
            }
            else{
                DB::connection('coops')->commit();
                if($mobile<>0 || preg_match('/^\d{10}$/', $mobile)){
                    $this->send_on_loan_repayment($request->org_id,$acct,$prn,$intt,$repay_date,$outs,$mobile);
                }
                return response()->json([
                    'message' => 'Success',
                    'details' => $message,
                ],200);
            }
   
        } catch (Exception $ex) {
            DB::connection('coops')->rollBack();
            $response = response()->json([
                'message' => 'Error Found',
                'details' => $ex->getMessage(),
            ],400);

            throw new HttpResponseException($response);
        }
    }
    else{
        $errors = $validator->errors();

            $response = response()->json([
                'message' => 'Invalid data send',
                'details' => $errors->messages(),
            ],400);
        
            throw new HttpResponseException($response);
    } 
    }

    public function process_ledger(Request $request){
            try {

                $sql = DB::select("Select UDF_GET_ORG_SCHEMA(?) as db;",[$request->org_id]);
                if(!$sql){
                  throw new Exception;
                }
                $org_schema = $sql[0]->db;
                $db = Config::get('database.connections.mysql');
                $db['database'] = $org_schema;
                config()->set('database.connections.coops', $db);

                $sql = DB::connection('coops')->select("Call USP_RPT_LOAN_LEDGER(?,?,?,?);",[$request->Acct_Id,$request->form_date,$request->to_date,$request->mode]);
                
                if (empty($sql)) {
                    // Custom validation for no data found
                    return response()->json([
                        'message' => 'No Data Found',
                        'details' => [],
                    ], 200);
                }

                    return response()->json([
                        'message' => 'Data Found',
                        'details' => $sql,
                    ],200);
                

            } catch (Exception $ex) {
                
                $response = response()->json([
                    'message' => 'Error Found',
                    'details' => $ex->getMessage(),
                ],400);
    
                throw new HttpResponseException($response);
            }
    }

    public function get_secure_prod_list(Request $request){
        try {
            
            $sql = DB::select("Select Secure_Prod_Id,Max_Allowed From map_org_loan_product Where Id=?",[$request->prod_id]);

            if(!$sql){
                throw new Exception;
            }
            $prod_id = $sql[0]->Secure_Prod_Id;
            $max_allow = $sql[0]->Max_Allowed;
            if($prod_id!=null && $max_allow!=null){
                $sql = DB::select("Select UDF_GET_ORG_SCHEMA(?) as db;",[$request->org_id]);
                if(!$sql){
                  throw new Exception;
                }
                $org_schema = $sql[0]->db;
                $db = Config::get('database.connections.mysql');
                $db['database'] = $org_schema;
                config()->set('database.connections.coops', $db);
                $sql = DB::connection('coops')->select("Select Id,Account_No,UDF_GET_DEP_BALANCE(Id,?) As Balance From mst_deposit_account_master Where Prod_Id=? And Mem_Id=? And Is_Active=1;",[$request->date,$prod_id,$request->mem_id]);
                if (empty($sql)) {
                    
                    return response()->json([
                        'message' => 'Error Found',
                        'details' => "No Account Found !!",
                    ], 200);
                }
                return response()->json([
                    'message' => 'Data Found',
                    'details' => ["DropdownData" => $sql,"maxAllow" =>$max_allow]
                ],200);
            }
            else{
                return response()->json([
                    'message' => 'Error Found',
                    'details' => 'No Security Found'
                ],200);
            }
           

        } catch (Exception $ex) {
            $response = response()->json([
                'message' => 'Error Found',
                'details' => $ex->getMessage(),
            ],400);

            throw new HttpResponseException($response);
        } 
    }
}