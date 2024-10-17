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

class ProcessOpening extends Controller
{
    public function process_opn_membership(Request $request){
        $validator = Validator::make($request->all(),[
            'org_id' => 'required',
            'open_date' => 'required',
            'mem_id' => 'required',
            'adm_no' => 'required',
            'adm_date' => 'required',
            'ledg_no' => 'required',
            'mem_type' => 'required',
            'nom_name' => 'required',
            'nom_rel' => 'required',
            'nom_age' => 'required',
            'nom_add' => 'required',
            'open_share' => 'required',
            'open_div' => 'required',
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
            $sql = DB::connection('coops')->statement("Call USP_PUSH_OPN_MEMBERSHIP(?,?,?,?,?,?,?,?,?,?,?,?,?,?,@error,@message);",[$request->open_date,$request->mem_id,$request->adm_no,$request->adm_date,$request->ledg_no,$request->mem_type,$request->nom_name,$request->nom_rel,$request->nom_age,$request->nom_add,$request->open_share,$request->open_div,auth()->user()->Id,$request->branch_Id]);

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

    public function process_deposit_account(Request $request){
        $validator = Validator::make($request->all(),[
            'org_id' => 'required',
            'start_date' => 'required',
            'mem_id' => 'required',
            'ref_ac_no' => 'required',
            'ledg_fol' => 'required',
            'open_date' => 'required',
            'prod_type' => 'required',
            'dep_type' => 'required',
            'prod_id' => 'required',
            'oper_mode' => 'required',
            'roi' => 'required',
            'dep_amount' => 'required',
            'open_intt' => 'required',
            'open_balance' => 'required',
            'branch_Id' => 'required',
            'fin_id' => 'required'
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
            $sql = DB::connection('coops')->statement("Call USP_PUSH_OPN_DEP_ACCOUNT(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,@error,@message);",[$request->start_date,$request->mem_id,$request->ref_ac_no,$request->ledg_fol,$request->open_date,$request->prod_type,$request->dep_type,$request->prod_id,$request->oper_mode,$request->roi,$request->dep_amount,$request->duration,$request->dur_unit,$request->mature_ins,$request->mature_date,$request->mature_amt,$request->nom_name,$request->nom_rel,$request->nom_add,$request->nom_age,$request->joint_hld1,$request->joint_hld2,$request->ecs_avail,$request->ecs_ac_id,$request->is_payout,$request->pay_mode,$request->pay_amt,$request->cbs_ac_no,$request->agent_id,$request->open_intt,$request->open_balance,$request->branch_Id,auth()->user()->Id,$request->fin_id]);

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

    public function process_investment(Request $request){
        $validator = Validator::make($request->all(),[
            'org_id' => 'required',
            'invest_type' => 'required',
            'acct_type' => 'required',
            'bank_name' => 'required',
            'acct_no' => 'required',
            'open_date' => 'required',
            'invest_amt' => 'required',
            'roi' => 'required',
            'intt_on' => 'required',
            'duration' => 'required',
            'mature_date' => 'required',
            'mature_val' => 'required',
            'prn_gl' => 'required',
            'intt_gl' => 'required',
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
            $sql = DB::connection('coops')->statement("Call USP_PUSH_OPN_INVESTMENT(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,@error,@message);",[$request->invest_type,$request->acct_type,$request->bank_name,$request->acct_no,$request->open_date,$request->invest_amt,$request->roi,$request->intt_on,$request->duration,$request->mature_date,$request->mature_val,$request->prn_gl,$request->intt_gl,$request->branch_Id,auth()->user()->Id]);

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

    public function process_bank_acct(Request $request){
        $validator = Validator::make($request->all(),[
            'org_id' => 'required',
            'bank_name' => 'required',
            'branch_name' => 'required',
            'ifsc' => 'required',
            'account_no' => 'required',
            'acct_type' => 'required',
            'under_gl' => 'required',
            'open_date' => 'required',
            'open_balance' => 'required',
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
            $sql = DB::connection('coops')->statement("Call USP_PUSH_OPN_BANK_ACCT(?,?,?,?,?,?,?,?,?,?,@error,@message);",[$request->bank_name,$request->branch_name,$request->ifsc,$request->account_no,$request->acct_type,$request->under_gl,$request->open_date,$request->open_balance,$request->branch_Id,auth()->user()->Id]);

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

    public function process_borrowings(Request $request){
        $validator = Validator::make($request->all(),[
            'org_id' => 'required',
            'start_date' => 'required',
            'prod_name' => 'required',
            'prod_type' => 'required',
            'repay_mode' => 'required',
            'bank_name' => 'required',
            'acct_no' => 'required',
            'disb_date' => 'required',
            'disb_amount' => 'required',
            'roi' => 'required',
            'over_rate' => 'required',
            'duration' => 'required',
            'due_date' => 'required',
            'prn_ledg' => 'required',
            'intt_ledg' => 'required',
            'outs_amt' => 'required',
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
            $sql = DB::connection('coops')->statement("Call USP_PUSH_OPN_BORROW_ACCT(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,@error,@message);",[$request->start_date,$request->prod_name,$request->prod_type,$request->repay_mode,$request->bank_name,$request->acct_no,$request->disb_date,$request->disb_amount,$request->roi,$request->over_rate,$request->duration,$request->due_date,$request->prn_ledg,$request->intt_ledg,$request->outs_amt,$request->branch_Id,auth()->user()->Id]);

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

    public function process_member_loan(Request $request){
        $validator = Validator::make($request->all(),[
            'org_id' => 'required',
            'start_date' => 'required',
            'disb_date' => 'required',
            'mem_id' => 'required',
            'appl_no' => 'required',
            'ref_ac_no' => 'required',
            'prod_id' => 'required',
            'roi' => 'required',
            'duration' => 'required',
            'dur_unit' => 'required',
            'disb_amount' => 'required',
            'repay_mode' => 'required',
            'repay_within' => 'required',
            'outs_balance' => 'required',
            'due_intt' => 'required',
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
            $sql = DB::connection('coops')->statement("Call USP_PUSH_OPN_LOAN_ACCOUNT(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,@error,@message);",[$request->start_date,$request->disb_date,$request->mem_id,$request->appl_no,$request->ref_ac_no,$request->prod_id,$request->roi,$request->duration,$request->dur_unit,$request->disb_amount,$request->repay_mode,$request->repay_within,$request->outs_balance,$request->due_intt,auth()->user()->Id,$request->branch_Id]);

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
}