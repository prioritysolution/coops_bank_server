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

class ProcessVoucherEntry extends Controller
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

    public function get_ledger_list(){
        try {
           
            $sql = DB::select("Select Id,Ledger_Name From mst_org_acct_ledger Where Sub_Head Not In (4,12,13,14,15,16,25,22,28) Order By Sub_Head;");

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

    public function get_sub_ledger_list(Request $request){
        try {
            // Fetch organization schema
            $sql = DB::select("SELECT UDF_GET_ORG_SCHEMA(?) AS db;", [$request->org_id]);
            if (!$sql) {
                throw new Exception('Organization Schema Not Found');
            }
        
            $org_schema = $sql[0]->db;
            $db = Config::get('database.connections.mysql');
            $db['database'] = $org_schema;
            config()->set('database.connections.coops', $db);
        
            // Get pagination parameters
            $perPage = request()->get('limit', 10); // Default items per page: 10
            $page = request()->get('page', 1); // Default page: 1
            $offset = ($page - 1) * $perPage;
            $searchQuery = $request->input('search');
            $searchQuery = ($searchQuery === '' || $searchQuery === null) ? null : $searchQuery;
        
            // Execute stored procedure
            $results = DB::connection('coops')->select("CALL USP_GET_SUB_LEDGER_LIST(?, ?, ?);", [$request->gl_id, $request->org_id, $searchQuery]);
        
            // Convert results to a collection for manual pagination
            $collection = collect($results);
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

    public function get_subledger_balance(Request $request){
            try {

                $sql = DB::select("Select UDF_GET_ORG_SCHEMA(?) as db;",[$request->org_id]);
                if(!$sql){
                  throw new Exception;
                }
                $org_schema = $sql[0]->db;
                $db = Config::get('database.connections.mysql');
                $db['database'] = $org_schema;
                config()->set('database.connections.coops', $db);

                $sql = DB::connection('coops')->select("Select UDF_GET_SUB_LEDGER_BALANCE(?,?,?) As Balance;",[$request->type,$request->date,$request->subgl_id]);
                $message = $sql[0]->Balance;

                    return response()->json([
                        'message' => 'Data Found',
                        'details' => $message,
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

    public function process_voucher_posting(Request $request){
        $validator = Validator::make($request->all(),[
            'trans_date' => 'required',
            'vouch_type' => 'required',
            'narration' => 'required',
            'amount' => 'required',
            'fin_id' => 'required',
            'branch_id' => 'required',
            'vouch_data' => 'required',
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

                $vouch_details_drop_table = DB::connection('coops')->statement("Drop Temporary Table If Exists temp_vouch_data;");
                $vouch_details_create_table = DB::connection('coops')->statement("Create Temporary Table temp_vouch_data
                                                                                (
                                                                                    Gl_Id			Int,
                                                                                    Trans_Typ		Char(1),
                                                                                    Amount			Numeric(18,2),
                                                                                    Sub_Ledg_Id		Int,
                                                                                    Subledg_Narr	Varchar(100),
                                                                                    Subledg_Type	Varchar(10)
                                                                                );");
                if(is_array($request->vouch_data)){
                    $vouch_data = $this->convertToObject($request->vouch_data);
                    foreach ($vouch_data as $vouch_details) {
                        DB::connection('coops')->statement("Insert Into temp_vouch_data (Gl_Id,Trans_Typ,Amount,Sub_Ledg_Id,Subledg_Narr,Subledg_Type) Values (?,?,?,?,?,?);",[$vouch_details->gl,$vouch_details->drCr,$vouch_details->amount,$vouch_details->subLedger,$vouch_details->ledgerNarration,$vouch_details->subledg_type]);
                    }
                    
                }
                
                $sql = DB::connection('coops')->statement("Call USP_ADD_VOUCHER(?,?,?,?,?,?,?,?,@error,@message);",[$request->trans_date,$request->vouch_type,$request->narration,$request->manual_vouch_no,$request->amount,$request->fin_id,$request->branch_id,auth()->user()->Id]);
                
                if(!$sql){
                    throw new Exception('Operation Error !!');
                }

                $sql = DB::connection('coops')->select("Select @error As Error,@message As Message");
                $error_No = $sql[0]->Error;
                $message = $sql[0]->Message;

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

    public function get_adj_ledger_list(){
        try {
           
            $sql = DB::select("Select Id,Ledger_Name From mst_org_acct_ledger Where Sub_Head Not In (4,12,25) Order By Sub_Head;");

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

    public function process_adj_voucher(Request $request){
        $validator = Validator::make($request->all(),[
            'trans_date' => 'required',
            'narration' => 'required',
            'fin_id' => 'required',
            'branch_id' => 'required',
            'vouch_data' => 'required',
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

                $vouch_details_drop_table = DB::connection('coops')->statement("Drop Temporary Table If Exists tempdetails;");
                $vouch_details_create_table = DB::connection('coops')->statement("Create Temporary Table tempdetails
                                                                                (
                                                                                   Gl_Id			Int,
                                                                                   Trans_Type		Char(1),
                                                                                   Amount			Numeric(18,2)
                                                                                );");
                if(is_array($request->vouch_data)){
                    $vouch_data = $this->convertToObject($request->vouch_data);
                    foreach ($vouch_data as $vouch_details) {
                        DB::connection('coops')->statement("Insert Into tempdetails (Gl_Id,Trans_Type,Amount) Values (?,?,?);",[$vouch_details->gl,$vouch_details->drCr,$vouch_details->amount]);
                    }
                    
                }
                
                $sql = DB::connection('coops')->statement("Call USP_POST_ADJ_VOUCHER(?,?,?,?,?,?,@error,@message);",[$request->trans_date,$request->manual_vouch_no,$request->narration,$request->fin_id,auth()->user()->Id,$request->branch_id]);
                
                if(!$sql){
                    throw new Exception('Operation Error !!');
                }

                $sql = DB::connection('coops')->select("Select @error As Error,@message As Message");
                $error_No = $sql[0]->Error;
                $message = $sql[0]->Message;

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