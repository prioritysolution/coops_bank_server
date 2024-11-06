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

class ProcessBorrowings extends Controller
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
    
    public function get_prod_type(){
        try {
           
            $sql = DB::select("Select Id,Option_Value From mst_org_product_parameater Where Module_Name=? And Option_Name=? And Is_Active=?;",['Borrowings','Product Type',1]);

            if(!$sql){
                throw new Exception;
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

    public function get_repay_mode(){
        try {
           
            $sql = DB::select("Select Id,Option_Value From mst_org_product_parameater Where Module_Name=? And Option_Name=? And Is_Active=?;",['Borrowings','Repay Mode',1]);

            if(!$sql){
                throw new Exception;
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

    public function get_ledger(Int $type){
        try {
            switch ($type) {
                case 1:
                    $subHead = 3;
                    break;
                case 2:
                    $subHead = 26;
                    break;
                default:
                    throw new Exception("Invalid type provided");
            }
            $sql = DB::select("Select Id,Ledger_Name From mst_org_acct_ledger Where Sub_Head=?;",[$subHead]);

            if(!$sql){
                throw new Exception;
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

    public function process_account(Request $request){
        $validator = Validator::make($request->all(),[
            'prod_name' => 'required',
            'prod_type' => 'required',
            'repay_mode' => 'required',
            'bank_name' => 'required',
            'acct_no' => 'required',
            'disb_date' => 'required',
            'amount' => 'required',
            'roi' => 'required',
            'over_rate' => 'required',
            'duration' => 'required',
            'due_date' => 'required',
            'prn_ledg' => 'required',
            'intt_ledg' => 'required',
            'bank_id' => 'required',
            'branch_id' => 'required',
            'fin_id' => 'required',
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

                DB::connection('coops')->beginTransaction();

                $sql = DB::connection('coops')->statement("Call USP_BORROW_ADD_ACCOUNT(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,@error,@message);",[$request->prod_name,$request->prod_type,$request->repay_mode,$request->bank_name,$request->acct_no,$request->disb_date,$request->amount,$request->roi,$request->over_rate,$request->duration,$request->due_date,$request->prn_ledg,$request->intt_ledg,$request->bank_id,$request->fin_id,$request->branch_id,auth()->user()->Id]);


                if(!$sql){
                    throw new Exception('Operation Error Found !!');
                }

                $result = DB::connection('coops')->select("Select @error As Error_No,@message As Message");
                $error_no = $result[0]->Error_No;
                $error_message = $result[0]->Message;

                if($error_no<0){
                    DB::connection('coops')->rollBack();
                    return response()->json([
                        'message' => 'Error Found',
                        'details' => $error_message,
                    ],200);
                }
                else{
                    DB::connection('coops')->commit();
                    return response()->json([
                        'message' => 'Success',
                        'details' => $error_message,
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

    public function get_account_list(Int $org_id){
        try {

            $sql = DB::select("Select UDF_GET_ORG_SCHEMA(?) as db;",[$org_id]);
            if(!$sql){
              throw new Exception;
            }
            $org_schema = $sql[0]->db;
            $db = Config::get('database.connections.mysql');
            $db['database'] = $org_schema;
            config()->set('database.connections.coops', $db);

            $sql = DB::connection('coops')->select("Select Id,Concat(Product_Name,' - ',Account_No) As Account_No From mst_bank_borrowings Where Is_Active=1");

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

    public function get_account_info(Request $request){
        $validator = Validator::make($request->all(),[
            'borrow_id' => 'required',
            'date' => 'required',
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

                $sql = DB::connection('coops')->select("Call USP_GET_BORROW_ACCOUNT_INFO(?,?);",[$request->borrow_id,$request->date]);

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

    public function process_disburse(Request $request){
        $validator = Validator::make($request->all(),[
            'borrow_id' => 'required',
            'disb_date' => 'required',
            'disb_amt' => 'required',
            'prn_ledg' => 'required',
            'bank_id' => 'required',
            'branch_id' => 'required',
            'fin_id' => 'required',
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

                DB::connection('coops')->beginTransaction();

                $sql = DB::connection('coops')->statement("Call USP_BORROW_ADD_DISBURSE(?,?,?,?,?,?,?,?,@error,@message);",[$request->borrow_id,$request->disb_date,$request->disb_amt,$request->bank_id,$request->prn_ledg,$request->fin_id,$request->branch_id,auth()->user()->Id]);


                if(!$sql){
                    throw new Exception('Operation Error Found !!');
                }

                $result = DB::connection('coops')->select("Select @error As Error_No,@message As Message");
                $error_no = $result[0]->Error_No;
                $error_message = $result[0]->Message;

                if($error_no<0){
                    DB::connection('coops')->rollBack();
                    return response()->json([
                        'message' => 'Error Found',
                        'details' => $error_message,
                    ],200);
                }
                else{
                    DB::connection('coops')->commit();
                    return response()->json([
                        'message' => 'Success',
                        'details' => $error_message,
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

    public function process_repayment(Request $request){
        $validator = Validator::make($request->all(),[
            'borrow_id' => 'required',
            'disb_date' => 'required',
            'prn_amt' => 'required',
            'intt_amt' => 'required',
            'intt_gl' => 'required',
            'prn_ledg' => 'required',
            'branch_id' => 'required',
            'fin_id' => 'required',
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

                $sql = DB::connection('coops')->statement("Call USP_BORROW_ADD_REPAYMENT(?,?,?,?,?,?,?,?,?,?,@error,@message);",[$request->borrow_id,$request->disb_date,$request->prn_amt,$request->intt_amt,$request->bank_id,$request->prn_ledg,$request->intt_gl,$request->fin_id,$request->branch_id,auth()->user()->Id]);


                if(!$sql){
                    throw new Exception('Operation Error Found !!');
                }

                $result = DB::connection('coops')->select("Select @error As Error_No,@message As Message");
                $error_no = $result[0]->Error_No;
                $error_message = $result[0]->Message;

                if($error_no<0){
                    DB::connection('coops')->rollBack();
                    return response()->json([
                        'message' => 'Error Found',
                        'details' => $error_message,
                    ],200);
                }
                else{
                    DB::connection('coops')->commit();
                    return response()->json([
                        'message' => 'Success',
                        'details' => $error_message,
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
            'borrow_id' => 'required',
            'form_date' => 'required',
            'to_date' => 'required',
            'mode' => 'required',
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

                $sql = DB::connection('coops')->select("Call USP_RPT_BORROW_LEDGER(?,?,?,?);",[$request->borrow_id,$request->form_date,$request->to_date,$request->mode]);

                if(!$sql){
                    throw new Exception('Could not process your request !!');
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