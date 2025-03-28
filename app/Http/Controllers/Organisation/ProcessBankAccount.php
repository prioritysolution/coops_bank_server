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

class ProcessBankAccount extends Controller
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
    public function get_account_type(){
        try {
           
            $sql = DB::select("Select Id,Option_Value From mst_org_product_parameater Where Module_Name=? And Option_Name=? And Is_Active=?;",['Bank','Account Type',1]);

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

    public function get_bank_gl(){
        try {
           
            $sql = DB::select("Select Id,Ledger_Name From mst_org_acct_ledger Where Sub_Head=13;");

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

    public function process_bank_account(Request $request){
        $validator = Validator::make($request->all(),[
            'bank_name' => 'required',
            'branch_name' => 'required',
            'ifsc_code' => 'required',
            'account_no' => 'required',
            'account_type' => 'required',
            'under_gl' => 'required',
            'opening_date' => 'required',
            'branch_id' => 'required',
            'org_id' => 'required'
        ]);

        if($validator->passes()){

            try {

                $sql = DB::select("Select UDF_GET_ORG_SCHEMA(?) as db;",[$request->org_id]);
                if(!$sql){
                  throw new Exception('Organisation Setup Error !!');
                }
                $org_schema = $sql[0]->db;
                $db = Config::get('database.connections.mysql');
                $db['database'] = $org_schema;
                config()->set('database.connections.coops', $db);

                DB::connection('coops')->beginTransaction();

                $sql = DB::connection('coops')->statement("Call USP_ADD_BANK_ACCOUNT(?,?,?,?,?,?,?,?,?,@error,@message);",[$request->bank_name,$request->branch_name,$request->ifsc_code,$request->account_no,$request->account_type,$request->under_gl,$request->opening_date,$request->branch_id,auth()->user()->Id]);

                if(!$sql){
                    throw new Exception('Could not process your request !!');
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
                        'details' => 'Bank Account Successfully Added !!',
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

    public function get_bank_account(Request $request){
        try {

            $sql = DB::select("Select UDF_GET_ORG_SCHEMA(?) as db;",[$request->org_id]);
            if(!$sql){
              throw new Exception;
            }
            $org_schema = $sql[0]->db;
            $db = Config::get('database.connections.mysql');
            $db['database'] = $org_schema;
            config()->set('database.connections.coops', $db);

            $sql = DB::connection('coops')->select("Select Id,Bank_Name,Bank_Branch,Bank_IFSC,Account_No,UDF_GET_OPTION_NAME(Account_Type) As Type,Concat(Bank_Name,' - ',Account_No) As New_Account From mst_bank_account_master Where Is_Active=1;");

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

    public function get_bank_balance(Request $request){
            try {

                $sql = DB::select("Select UDF_GET_ORG_SCHEMA(?) as db;",[$request->org_id]);
                if(!$sql){
                  throw new Exception('Organisation Setup Error !!');
                }
                $org_schema = $sql[0]->db;
                $db = Config::get('database.connections.mysql');
                $db['database'] = $org_schema;
                config()->set('database.connections.coops', $db);

                $sql = DB::connection('coops')->select("Select UDF_CAL_BANK_BAL(?,?) As Balance;",[$request->account_id,$request->date]);

                if (empty($sql)) {
                    // Custom validation for no data found
                    return response()->json([
                        'message' => 'No Data Found',
                        'details' => [],
                    ], 200);
                }

                $balance = $sql[0]->Balance;

                
                    return response()->json([
                        'message' => 'Data Found',
                        'details' => $balance,
                    ],200);
                


            } catch (Exception $ex) {
                DB::connection('coops')->rollBack();
                $response = response()->json([
                    'message' => 'Error Found',
                    'details' => $ex->getMessage(),
                ],400);
    
                throw new HttpResponseException($response);
            }
    }

    public function process_bank_deposit(Request $request){
        $validator = Validator::make($request->all(),[
            'trans_date' => 'required',
            'Account_Id' => 'required',
            'Amount' => 'required',
            'fin_id' => 'required',
            'branch_id' => 'required',
            'org_id' => 'required'
        ]);

        if($validator->passes()){

            try {

                $sql = DB::select("Select UDF_GET_ORG_SCHEMA(?) as db;",[$request->org_id]);
                if(!$sql){
                  throw new Exception('Organisation Setup Error !!');
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

                $ledger_drop_table = DB::connection('coops')->statement("Drop Temporary Table If Exists temp_vouch_data;");
                $ledger_create_table = DB::connection('coops')->statement("Create Temporary Table temp_vouch_data
                                                                        (
                                                                        Gl_Id			Int,
                                                                        Amount			Numeric(18,2),
                                                                        Sub_Ledg_Id		Int,
                                                                        Subledg_Narr	Varchar(100),
                                                                        Subledg_Type	Varchar(10)
                                                                        );");
                if(is_array($request->ledger_data)){
                    $ledger_date = $this->convertToObject($request->ledger_data);

                    foreach ($ledger_date as $ledger) {
                        $ledger_insert = DB::connection('coops')->statement("Insert Into temp_vouch_data (Gl_Id,Amount,Sub_Ledg_Id,Subledg_Narr,Subledg_Type) Values (?,?,?,?,?);",[$ledger->gl_id,$ledger->amount,$ledger->subgl_id,$ledger->subgl_narr,$ledger->subgl_type]);
                    }
                }

                $sql = DB::connection('coops')->statement("Call USP_ADD_BANK_TRANS(?,?,?,?,?,?,?,?,?,?,?,@error,@message);",[$request->trans_date,$request->ref_vouch,$request->narration,$request->Account_Id,1,$request->Amount,$request->to_account_id,$request->fin_id,$request->branch_id,auth()->user()->Id,$request->trans_mode]);

                if(!$sql){
                    throw new Exception('Could not process your request !!');
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

    public function process_bank_withdrwan(Request $request){
        $validator = Validator::make($request->all(),[
            'trans_date' => 'required',
            'Account_Id' => 'required',
            'Amount' => 'required',
            'fin_id' => 'required',
            'branch_id' => 'required',
            'org_id' => 'required'
        ]);

        if($validator->passes()){

            try {

                $sql = DB::select("Select UDF_GET_ORG_SCHEMA(?) as db;",[$request->org_id]);
                if(!$sql){
                  throw new Exception('Organisation Setup Error !!');
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

                $ledger_drop_table = DB::connection('coops')->statement("Drop Temporary Table If Exists temp_vouch_data;");
                $ledger_create_table = DB::connection('coops')->statement("Create Temporary Table temp_vouch_data
                                                                        (
                                                                        Gl_Id			Int,
                                                                        Amount			Numeric(18,2),
                                                                        Sub_Ledg_Id		Int,
                                                                        Subledg_Narr	Varchar(100),
                                                                        Subledg_Type	Varchar(10)
                                                                        );");
                                                                        
                if(is_array($request->ledger_data)){
                    $ledger_date = $this->convertToObject($request->ledger_data);

                    foreach ($ledger_date as $ledger) {
                        $ledger_insert = DB::connection('coops')->statement("Insert Into temp_vouch_data (Gl_Id,Amount,Sub_Ledg_Id,Subledg_Narr,Subledg_Type) Values (?,?,?,?,?);",[$ledger->gl_id,$ledger->amount,$ledger->subgl_id,$ledger->subgl_narr,$ledger->subgl_type]);
                    }
                }

                $sql = DB::connection('coops')->statement("Call USP_ADD_BANK_TRANS(?,?,?,?,?,?,?,?,?,?,?,@error,@message);",[$request->trans_date,$request->ref_vouch,$request->narration,$request->Account_Id,2,$request->Amount,$request->to_account_id,$request->fin_id,$request->branch_id,auth()->user()->Id,$request->trans_mode]);

                if(!$sql){
                    throw new Exception('Could not process your request !!');
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

    public function process_bank_transfer(Request $request){
        $validator = Validator::make($request->all(),[
            'trans_date' => 'required',
            'Account_Id' => 'required',
            'Amount' => 'required',
            'to_account_id' => 'required',
            'fin_id' => 'required',
            'branch_id' => 'required',
            'org_id' => 'required'
        ]);

        if($validator->passes()){

            try {

                $sql = DB::select("Select UDF_GET_ORG_SCHEMA(?) as db;",[$request->org_id]);
                if(!$sql){
                  throw new Exception('Organisation Setup Error !!');
                }
                $org_schema = $sql[0]->db;
                $db = Config::get('database.connections.mysql');
                $db['database'] = $org_schema;
                config()->set('database.connections.coops', $db);
                DB::connection('coops')->beginTransaction();


                $sql = DB::connection('coops')->statement("Call USP_ADD_BANK_TRANS(?,?,?,?,?,?,?,?,?,?,?,@error,@message);",[$request->trans_date,$request->ref_vouch,null,$request->Account_Id,3,$request->Amount,$request->to_account_id,$request->fin_id,$request->branch_id,auth()->user()->Id,null]);

                if(!$sql){
                    throw new Exception('Could not process your request !!');
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

    public function process_close_account(Request $request){
        $validator = Validator::make($request->all(),[
            'trans_date' => 'required',
            'Account_Id' => 'required',
            'Amount' => 'required',
            'fin_id' => 'required',
            'branch_id' => 'required',
            'org_id' => 'required'
        ]);

        if($validator->passes()){

            try {

                $sql = DB::select("Select UDF_GET_ORG_SCHEMA(?) as db;",[$request->org_id]);
                if(!$sql){
                  throw new Exception('Organisation Setup Error !!');
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

                $sql = DB::connection('coops')->statement("Call USP_ADD_BANK_TRANS(?,?,?,?,?,?,?,?,?,?,?,@error,@message);",[$request->trans_date,$request->ref_vouch,null,$request->Account_Id,4,$request->Amount,$request->to_account_id,$request->fin_id,$request->branch_id,auth()->user()->Id,null]);

                if(!$sql){
                    throw new Exception('Could not process your request !!');
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

    public function process_bank_ledger(Request $request){
            try {

                $sql = DB::select("Select UDF_GET_ORG_SCHEMA(?) as db;",[$request->org_id]);
                if(!$sql){
                  throw new Exception('Organisation Setup Error !!');
                }
                $org_schema = $sql[0]->db;
                $db = Config::get('database.connections.mysql');
                $db['database'] = $org_schema;
                config()->set('database.connections.coops', $db);

                $sql = DB::connection('coops')->select("Call USP_RPT_BANK_LEDGER(?,?,?,?);",[$request->bank_id,$request->form_date,$request->to_date,$request->mode]);

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
                DB::connection('coops')->rollBack();
                $response = response()->json([
                    'message' => 'Error Found',
                    'details' => $ex->getMessage(),
                ],400);
    
                throw new HttpResponseException($response);
            }

    }
}