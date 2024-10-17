<?php

namespace App\Http\Controllers\Organisation;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Config;
use App\Models\OrgUser;
use Illuminate\Http\Exceptions\HttpResponseException;
use App\Traits\SpecimenUpload;
use Exception;
use Session;
use Storage;
use DB;
use \stdClass;

class ProcessLoan extends Controller
{
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
        $validator = Validator::make($request->all(),[
            'org_id' => 'required',
            'mem_no' => 'required',
            'date' => 'required'
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

            $sql = DB::connection('coops')->select("Call USP_GET_LOAN_MEMBER_DATA(?,?);",[$request->mem_no,$request->date]);

            if(!$sql){
                throw new Exception('No Data Found !!');
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
    else{
        $errors = $validator->errors();

            $response = response()->json([
                'message' => 'Invalid data send',
                'details' => $errors->messages(),
            ],400);
        
            throw new HttpResponseException($response);
    }
    }

    public function get_loan_product(Int $org_Id){
        try {

            $sql = DB::select("Select Id,Prod_Sh_Name,Loan_Type From map_org_loan_product Where org_Id=?;",[$org_Id]);

            if(!$sql){
                throw new Exception('No Data Found !!');
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

    public function get_prod_duration(Int $prod_id, Int $org_id){
        try {

            $sql = DB::select("Select UDF_GET_ORG_SCHEMA(?) as db;",[$org_id]);
            if(!$sql){
              throw new Exception;
            }
            $org_schema = $sql[0]->db;
            $db = Config::get('database.connections.mysql');
            $db['database'] = $org_schema;
            config()->set('database.connections.coops', $db);

            $sql = DB::connection('coops')->select("Call USP_GET_LOAN_PARAM(?,?);",[$prod_id,2]);

            if(!$sql){
                throw new Exception('No Data Found !!');
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

    public function get_prod_repay_mode(Int $prod_id, Int $org_id){
        try {

            $sql = DB::select("Select UDF_GET_ORG_SCHEMA(?) as db;",[$org_id]);
            if(!$sql){
              throw new Exception;
            }
            $org_schema = $sql[0]->db;
            $db = Config::get('database.connections.mysql');
            $db['database'] = $org_schema;
            config()->set('database.connections.coops', $db);

            $sql = DB::connection('coops')->select("Call USP_GET_LOAN_PARAM(?,?);",[$prod_id,1]);

            if(!$sql){
                throw new Exception('No Data Found !!');
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
        $validator = Validator::make($request->all(),[
            'org_id' => 'required',
            'prod_id' => 'required',
            'amount' => 'required'
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

            $sql = DB::connection('coops')->select("Call USP_CHECK_LOAN_PARAM(?,?,?,?,?);",[$request->prod_id,$request->amount,null,null,1]);

            if(!$sql){
                throw new Exception('No Data Found !!');
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
    else{
        $errors = $validator->errors();

            $response = response()->json([
                'message' => 'Invalid data send',
                'details' => $errors->messages(),
            ],400);
        
            throw new HttpResponseException($response);
    }
    }

    public function check_duration(Request $request){
        $validator = Validator::make($request->all(),[
            'org_id' => 'required',
            'prod_id' => 'required',
            'duration' => 'required',
            'dur_unit' => 'required'
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

            $sql = DB::connection('coops')->select("Call USP_CHECK_LOAN_PARAM(?,?,?,?,?);",[$request->prod_id,null,$request->duration,$request->dur_unit,2]);

            if(!$sql){
                throw new Exception('No Data Found !!');
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
    else{
        $errors = $validator->errors();

            $response = response()->json([
                'message' => 'Invalid data send',
                'details' => $errors->messages(),
            ],400);
        
            throw new HttpResponseException($response);
    }
    }

    public function get_interest_rate(Int $org_id, Int $prod_id){
        try {

            $sql = DB::select("Select Roi From map_org_loan_product Where org_Id=? And Id=?;",[$org_id,$prod_id]);

            if(!$sql){
                throw new Exception('No Data Found !!');
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
        $validator = Validator::make($request->all(),[
            'org_id' => 'required',
            'principal' => 'required',
            'roi' => 'required',
            'duration' => 'required'
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

            $sql = DB::connection('coops')->select("Select UDF_GEN_LOAN_EMI(?,?,?) As Emi;",[$request->principal,$request->roi,$request->duration]);

            if(!$sql){
                throw new Exception('No Data Found !!');
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
    else{
        $errors = $validator->errors();

            $response = response()->json([
                'message' => 'Invalid data send',
                'details' => $errors->messages(),
            ],400);
        
            throw new HttpResponseException($response);
    }
    }

    public function chek_loan_eligible(Request $request){
        $validator = Validator::make($request->all(),[
            'org_id' => 'required',
            'prod_id' => 'required',
            'mem_id' => 'required',
            'date' => 'required'
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

            $sql = DB::connection('coops')->select("Call USP_CHECK_ELIGIBLE_LOAN(?,?,?);",[$request->mem_id,$request->prod_id,$request->date]);

            if(!$sql){
                throw new Exception('No Data Found !!');
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
    else{
        $errors = $validator->errors();

            $response = response()->json([
                'message' => 'Invalid data send',
                'details' => $errors->messages(),
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
            'ref_ac_no' => 'required',
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

            $sql = DB::connection('coops')->statement("Call USP_LOAN_ADD_APPLICATION(?,?,?,?,?,?,?,?,?,?,?,?,?,@error,@message);",[$request->appl_date,$request->mem_id,$request->appl_no,$request->ref_ac_no,$request->prod_id,$request->roi,$request->duration,$request->dur_unit,$request->appl_amount,$request->repay_mode,$request->repay_within,auth()->user()->Id,$request->branch_Id]);

            if(!$sql){
                throw new Exception('Operation Error Found !!');
            }
            $result = DB::connection('coops')->select("Select @error As Error_No,@message As Message;");
            $error_No = $result[0]->Error_No;
            $message = $result[0]->Message;

            if($error_No<0){
                return response()->json([
                    'message' => 'Error Found',
                    'details' => $message,
                ],200);
            }
            else{
                return response()->json([
                    'message' => 'Success',
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
    else{
        $errors = $validator->errors();

            $response = response()->json([
                'message' => 'Invalid data send',
                'details' => $errors->messages(),
            ],400);
        
            throw new HttpResponseException($response);
    } 
    }

    public function get_pending_disb_list(Int $org_id, Int $branch_id){
        try {

            $sql = DB::select("Select UDF_GET_ORG_SCHEMA(?) as db;",[$org_id]);
            if(!$sql){
              throw new Exception;
            }
            $org_schema = $sql[0]->db;
            $db = Config::get('database.connections.mysql');
            $db['database'] = $org_schema;
            config()->set('database.connections.coops', $db);

            $sql = DB::connection('coops')->select("Call USP_LOAN_GET_DISB_LIST(?);",[$branch_id]);

            if(!$sql){
                throw new Exception('No Data Found !!');
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
        $validator = Validator::make($request->all(),[
            'org_id' => 'required',
            'mode' => 'required'
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

            $sql = DB::connection('coops')->select("Call USP_LOAN_SEARCH_ACCOUNT(?,?,?);",[$request->member_name,$request->member_no,$request->mode]);

            if(!$sql){
                throw new Exception('No Data Found !!');
            }
           
            return response()->json([
                    'message' => 'Success',
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
    else{
        $errors = $validator->errors();

            $response = response()->json([
                'message' => 'Invalid data send',
                'details' => $errors->messages(),
            ],400);
        
            throw new HttpResponseException($response);
    } 
    }

    public function generate_schdule(Int $org_id, String $acct_id, Int $mode){
        try {

            $sql = DB::select("Select UDF_GET_ORG_SCHEMA(?) as db;",[$org_id]);
            if(!$sql){
              throw new Exception;
            }
            $org_schema = $sql[0]->db;
            $db = Config::get('database.connections.mysql');
            $db['database'] = $org_schema;
            config()->set('database.connections.coops', $db);

            $sql = DB::connection('coops')->select("Call USP_LOAN_SCHDULE_DATA(?,?);",[$acct_id,$mode]);

            if(!$sql){
                throw new Exception('No Data Found !!');
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
        $validator = Validator::make($request->all(),[
            'org_id' => 'required',
            'prod_id' => 'required',
            'mem_id' => 'required',
            'date' => 'required'
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

            $sql = DB::connection('coops')->select("Call USP_LOAN_GET_DISB_SHDEP_BALANCE(?,?,?);",[$request->mem_id,$request->prod_id,$request->date]);

            if(!$sql){
                throw new Exception('No Data Found !!');
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
    else{
        $errors = $validator->errors();

            $response = response()->json([
                'message' => 'Invalid data send',
                'details' => $errors->messages(),
            ],400);
        
            throw new HttpResponseException($response);
    } 
    }

    public function get_disb_cal_amount(Request $request){
        $validator = Validator::make($request->all(),[
            'org_id' => 'required',
            'mem_id' => 'required',
            'share_bal' => 'required',
            'prod_id' => 'required',
            'disb_amount' => 'required',
            'date' => 'required'
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

            $sql = DB::connection('coops')->select("Call USP_LOAN_DISB_NEED_AMOUNT(?,?,?,?,?);",[$request->mem_id,$request->share_bal,$request->prod_id,$request->disb_amount,$request->date]);

            if(!$sql){
                throw new Exception('No Data Found !!');
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
    else{
        $errors = $validator->errors();

            $response = response()->json([
                'message' => 'Invalid data send',
                'details' => $errors->messages(),
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
            'share_amt' => 'required',
            'dep_amt' => 'required',
            'ins_amt' => 'required',
            'mis_amt' => 'required',
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

            $sql = DB::connection('coops')->statement("Call USP_LOAN_POST_DISBURSE(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,@error,@message);",[$request->disb_date,$request->prod_id,$request->act_id,$request->mem_id,$request->share_id,$request->dep_id,$request->disb_amt,$request->share_amt,$request->dep_amt,$request->ins_amt,$request->mis_amt,$request->share_gl,$request->dep_gl,$request->mis_gl,$request->sb_id,$request->bank_id,$request->fin_id,$request->branch_Id,auth()->user()->Id]);

            if(!$sql){
                throw new Exception('Operation Not Complete !!');
            }
            $result = DB::connection('coops')->select("Select @error As Error_No,@message As Message");
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

    public function get_repay_date(Request $request){
        $validator = Validator::make($request->all(),[
            'org_id' => 'required',
            'acct_no' => 'required',
            'date' => 'required'
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

            $sql = DB::connection('coops')->select("Call USP_GET_LON_REPAY_DATA(?,?);",[$request->acct_no,$request->date]);

            if(!$sql){
                throw new Exception('No Data Found !!');
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
    else{
        $errors = $validator->errors();

            $response = response()->json([
                'message' => 'Invalid data send',
                'details' => $errors->messages(),
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

            $sql = DB::connection('coops')->statement("Call USP_LOAN_POST_REPAY(?,?,?,?,?,?,?,?,?,?,?,?,?,@error,@message);",[$request->acct_id,$request->mem_id,$request->date,$request->prn_amt,$request->intt_amt,$request->due_intt,$request->prn_gl,$request->intt_gl,$request->bank_id,$request->sb_id,$request->fin_id,$request->branch_Id,auth()->user()->Id]);

            if(!$sql){
                throw new Exception('Operation Not Complete !!');
            }
            $result = DB::connection('coops')->select("Select @error As Error_No,@message As Message");
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

    public function process_ledger(Request $request){
        $validator = Validator::make($request->all(),[
            'Acct_Id' => 'required',
            'form_date' => 'required',
            'to_date' => 'required',
            'mode' => 'required',
            'org_id' => 'required'
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

                $sql = DB::connection('coops')->select("Call USP_RPT_LOAN_LEDGER(?,?,?,?);",[$request->Acct_Id,$request->form_date,$request->to_date,$request->mode]);
                
                if(!$sql){
                    throw new Exception("No Data Found");
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
        else{

            $errors = $validator->errors();

            $response = response()->json([
                'message' => 'Invalid data send',
                'details' => $errors->messages(),
            ],400);
        
            throw new HttpResponseException($response);
        }
    }
}