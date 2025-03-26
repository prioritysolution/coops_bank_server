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

class ProcessFinancialReport extends Controller
{
    public function process_gl_balancing(Request $request){
            try {

                $sql = DB::select("Select UDF_GET_ORG_SCHEMA(?) as db;",[$request->org_id]);
                if(!$sql){
                  throw new Exception;
                }
                $org_schema = $sql[0]->db;
                $db = Config::get('database.connections.mysql');
                $db['database'] = $org_schema;
                config()->set('database.connections.coops', $db);

                $sql = DB::connection('coops')->select("Call USP_RPT_GLBALANCING(?,?);",[$request->date,$request->branch_id]);
                
                if (empty($sql)) {
                    // Custom validation for no data found
                    return response()->json([
                        'message' => 'No Data Found',
                        'details' => [],
                    ], 200);
                }

                $data_set = [];
                $aray_key='';
                foreach ($sql as $module_key) {
                    if($module_key->Is_Heading==1){
                        $aray_key=$module_key->Heading_Name;
                        $data_set[$module_key->Heading_Name]=[];
                    }
                    else{
                        $data_set[$aray_key][]=["Sub_Heading"=>$module_key->Sub_Heading,"Ledger_Name"=>$module_key->Ledger_Name,"Gl_Balance"=>$module_key->Gl_Balance,"Dl_Balance"=>$module_key->Dl_Balance,"Difference"=>$module_key->Difference,"Remarks"=>$module_key->Remarks];
                    }

                }
                    return response()->json([
                        'message' => 'Data Found',
                        'details' => $data_set,
                    ],200);
                

            } catch (Exception $ex) {
                
                $response = response()->json([
                    'message' => 'Error Found',
                    'details' => $ex->getMessage(),
                ],400);
    
                throw new HttpResponseException($response);
            }
    }

    public function process_daybook(Request $request){
            try {

                $sql = DB::select("Select UDF_GET_ORG_SCHEMA(?) as db;",[$request->org_id]);
                if(!$sql){
                  throw new Exception;
                }
                $org_schema = $sql[0]->db;
                $db = Config::get('database.connections.mysql');
                $db['database'] = $org_schema;
                config()->set('database.connections.coops', $db);

                $sql = DB::connection('coops')->select("Call USP_RPT_DAYBOOK(?,?);",[$request->branch_id,$request->date]);
                
                if (empty($sql)) {
                    // Custom validation for no data found
                    return response()->json([
                        'message' => 'No Data Found',
                        'details' => [],
                    ], 200);
                }

                $daybook_data = [];

                foreach ($sql as $daybook) {
                    if($daybook->Opening_Cash){
                        $daybook_data['DayBook']=[
                            'Opening_Cash' => $daybook->Opening_Cash,
                            'Receipt_Data' => [],
                            'Payment_Data' => [],
                            'Closing_Cash'=>'',
                            'Denom_Data' => [],
                        ];
                    }

                    if($daybook->Rec_Count){
                        $daybook_data['DayBook']['Receipt_Data'][]=[
                            'Gl_Id' => $daybook->Gl_Id,
                            'Vouch_No' => $daybook->Rec_Count,
                            'Particular' => $daybook->Ledger_Name,
                            'Cash' => $daybook->Rec_Cash,
                            'Transfer' => $daybook->Rec_Trf,
                            'Total' => ($daybook->Rec_Cash ?? 0) + ($daybook->Rec_Trf ?? 0)
                        ];
                    }

                    if($daybook->Pay_Count){
                        $daybook_data['DayBook']['Payment_Data'][]=[
                            'Gl_Id' => $daybook->Gl_Id,
                            'Vouch_No' => $daybook->Pay_Count,
                            'Particular' => $daybook->Ledger_Name,
                            'Cash' => $daybook->Pay_Cash,
                            'Transfer' => $daybook->Pay_Trf,
                            'Total' => ($daybook->Pay_Cash ?? 0) + ($daybook->Pay_Trf ?? 0)
                        ];
                    }

                    if($daybook->Closing_Cash){
                        $daybook_data['DayBook']['Closing_Cash']=$daybook->Closing_Cash;
                    }

                    if($daybook->Denom_Id){
                        $daybook_data['DayBook']['Denom_Data'][]=[
                            'Denom_Id' => $daybook->Denom_Id,
                            'Denom_Label' => $daybook->Denom_Label,
                            'Denom_Balance' => $daybook->Denom_Bal,
                            'Denom_Value' => ($daybook->Denom_Label*$daybook->Denom_Bal)
                        ];
                    }

                }

                $daybook_data = array_values($daybook_data);

                    return response()->json([
                        'message' => 'Data Found',
                        'details' => $daybook_data,
                    ],200);
                

            } catch (Exception $ex) {
                
                $response = response()->json([
                    'message' => 'Error Found',
                    'details' => $ex->getMessage(),
                ],400);
    
                throw new HttpResponseException($response);
            }
    }

    public function process_cash_balance(Request $request){
            try {

                $sql = DB::select("Select UDF_GET_ORG_SCHEMA(?) as db;",[$request->org_id]);
                if(!$sql){
                  throw new Exception;
                }
                $org_schema = $sql[0]->db;
                $db = Config::get('database.connections.mysql');
                $db['database'] = $org_schema;
                config()->set('database.connections.coops', $db);

                $sql = DB::connection('coops')->select("Call USP_GET_CASH_BALANCE(?,?,?);",[$request->branch_id,$request->date,$request->to_date]);
                
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

    public function process_cash_acct(Request $request){
            try {

                $sql = DB::select("Select UDF_GET_ORG_SCHEMA(?) as db;",[$request->org_id]);
                if(!$sql){
                  throw new Exception;
                }
                $org_schema = $sql[0]->db;
                $db = Config::get('database.connections.mysql');
                $db['database'] = $org_schema;
                config()->set('database.connections.coops', $db);

                $sql = DB::connection('coops')->select("Call USP_RPT_CASH_ACCT(?,?,?);",[$request->branch_id,$request->form_date,$request->to_date]);
                
                if (empty($sql)) {
                    // Custom validation for no data found
                    return response()->json([
                        'message' => 'No Data Found',
                        'details' => [],
                    ], 200);
                }

                $daybook_data = [];

                foreach ($sql as $daybook) {
                    if($daybook->Opening_Cash){
                        $daybook_data['DayBook']=[
                            'Opening_Cash' => $daybook->Opening_Cash,
                            'Receipt_Data' => [],
                            'Payment_Data' => [],
                            'Closing_Cash'=>'',
                            'Denom_Data' => [],
                        ];
                    }

                    if($daybook->Rec_Count){
                        $daybook_data['DayBook']['Receipt_Data'][]=[
                            'Gl_Id' => $daybook->Gl_Id,
                            'Vouch_No' => $daybook->Rec_Count,
                            'Particular' => $daybook->Ledger_Name,
                            'Cash' => $daybook->Rec_Cash,
                            'Transfer' => $daybook->Rec_Trf,
                            'Total' => ($daybook->Rec_Cash ?? 0) + ($daybook->Rec_Trf ?? 0)
                        ];
                    }

                    if($daybook->Pay_Count){
                        $daybook_data['DayBook']['Payment_Data'][]=[
                            'Gl_Id' => $daybook->Gl_Id,
                            'Vouch_No' => $daybook->Pay_Count,
                            'Particular' => $daybook->Ledger_Name,
                            'Cash' => $daybook->Pay_Cash,
                            'Transfer' => $daybook->Pay_Trf,
                            'Total' => ($daybook->Pay_Cash ?? 0) + ($daybook->Pay_Trf ?? 0)
                        ];
                    }

                    if($daybook->Closing_Cash){
                        $daybook_data['DayBook']['Closing_Cash']=$daybook->Closing_Cash;
                    }

                    if($daybook->Denom_Id){
                        $daybook_data['DayBook']['Denom_Data'][]=[
                            'Denom_Id' => $daybook->Denom_Id,
                            'Denom_Label' => $daybook->Denom_Label,
                            'Denom_Balance' => $daybook->Denom_Bal,
                            'Denom_Value' => ($daybook->Denom_Label*$daybook->Denom_Bal)
                        ];
                    }

                }

                $daybook_data = array_values($daybook_data);

                    return response()->json([
                        'message' => 'Data Found',
                        'details' => $daybook_data,
                    ],200);
                

            } catch (Exception $ex) {
                
                $response = response()->json([
                    'message' => 'Error Found',
                    'details' => $ex->getMessage(),
                ],400);
    
                throw new HttpResponseException($response);
            }
    }

    public function process_cash_book(Request $request){
            try {

                $sql = DB::select("Select UDF_GET_ORG_SCHEMA(?) as db;",[$request->org_id]);
                if(!$sql){
                  throw new Exception;
                }
                $org_schema = $sql[0]->db;
                $db = Config::get('database.connections.mysql');
                $db['database'] = $org_schema;
                config()->set('database.connections.coops', $db);

                $sql = DB::connection('coops')->select("Call USP_RPT_CASH_BOOK(?,?);",[$request->branch_id,$request->date]);
                
                if (empty($sql)) {
                    // Custom validation for no data found
                    return response()->json([
                        'message' => 'No Data Found',
                        'details' => [],
                    ], 200);
                }

                $daybook_data = [];

                foreach ($sql as $daybook) {
                    if($daybook->Opening_Cash){
                        $daybook_data['DayBook']=[
                            'Opening_Cash' => $daybook->Opening_Cash,
                            'Receipt_Data' => [],
                            'Payment_Data' => [],
                            'Closing_Cash'=>'',
                            'Denom_Data' => [],
                        ];
                    }

                    if($daybook->Rec_Cash){
                        $daybook_data['DayBook']['Receipt_Data'][]=[
                            'Trans_Id' => $daybook->Trans_Id,
                            'Vouch_No' => $daybook->Vouch_No,
                            'Particular' => $daybook->Particulars,
                            'Ledger_Name' => $daybook->Ledger_Name,
                            'Cash' => $daybook->Rec_Cash,
                        ];
                    }

                    if($daybook->Pay_Cash){
                        $daybook_data['DayBook']['Payment_Data'][]=[
                            'Trans_Id' => $daybook->Trans_Id,
                            'Vouch_No' => $daybook->Vouch_No,
                            'Particular' => $daybook->Particulars,
                            'Ledger_Name' => $daybook->Ledger_Name,
                            'Cash' => $daybook->Pay_Cash,
                           
                        ];
                    }

                    if($daybook->Closing_Cash){
                        $daybook_data['DayBook']['Closing_Cash']=$daybook->Closing_Cash;
                    }

                    if($daybook->Denom_Id){
                        $daybook_data['DayBook']['Denom_Data'][]=[
                            'Denom_Id' => $daybook->Denom_Id,
                            'Denom_Label' => $daybook->Denom_Label,
                            'Denom_Balance' => $daybook->Denom_Bal,
                            'Denom_Value' => ($daybook->Denom_Label*$daybook->Denom_Bal)
                        ];
                    }

                }

                $daybook_data = array_values($daybook_data);

                    return response()->json([
                        'message' => 'Data Found',
                        'details' => $daybook_data,
                    ],200);
                

            } catch (Exception $ex) {
                
                $response = response()->json([
                    'message' => 'Error Found',
                    'details' => $ex->getMessage(),
                ],400);
    
                throw new HttpResponseException($response);
            }
    }

    public function get_acct_ledger(){
        try {
           
            $sql = DB::select("Select Id,Ledger_Name From mst_org_acct_ledger Where Is_Active=1;");

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

    public function genereate_ledger(Request $request){
            try {

                $sql = DB::select("Select UDF_GET_ORG_SCHEMA(?) as db;",[$request->org_id]);
                if(!$sql){
                  throw new Exception;
                }
                $org_schema = $sql[0]->db;
                $db = Config::get('database.connections.mysql');
                $db['database'] = $org_schema;
                config()->set('database.connections.coops', $db);

                $sql = DB::connection('coops')->select("Call USP_RPT_ACCTLEDGER(?,?,?,?);",[$request->ledger_id,$request->form_date,$request->to_date,$request->branch_id]);
                
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

    public function get_voucher_list(Request $request){
        try {
            // Fetch organization schema
            $sql = DB::select("SELECT UDF_GET_ORG_SCHEMA(?) AS db;", [$request->org_id]);
            if (!$sql) {
                throw new Exception('Database schema not found.');
            }
    
            $org_schema = $sql[0]->db;
            $db = Config::get('database.connections.mysql');
            $db['database'] = $org_schema;
            config()->set('database.connections.coops', $db);
    
            // Get pagination parameters
            $perPage = max(1, intval($request->get('limit', 10))); // Default 10 items per page
            $page = max(1, intval($request->get('page', 1))); // Default page 1
            $offset = ($page - 1) * $perPage;
    
            // Fetch results from stored procedure
            $results = DB::connection('coops')->select("CALL USP_RPT_LIST_VOUCHER(?,?,?,?,?);", [
                $request->ledger_id, $request->frm_date, $request->to_date, $request->branch_id, $request->mode
            ]);
    
            // Convert results to a collection for pagination
            $collection = collect($results);
    
            // Paginate results manually
            $paginatedData = $collection->slice($offset, $perPage)->values();
            $total = $collection->count();
    
            // Return response
            return response()->json([
                'message' => $paginatedData->isEmpty() ? 'No Data Found' : 'Data Found',
                'data' => [
                    'current_page' => $page,
                    'per_page' => $perPage,
                    'total' => $total,
                    'last_page' => ceil($total / $perPage),
                    'data' => $paginatedData,
                ],
            ], 200);
    
        } catch (Exception $ex) {
            // Handle exceptions gracefully
            $response = response()->json([
                'message' => 'Error Found',
                'details' => $ex->getMessage(),
            ], 400);
    
            throw new HttpResponseException($response);
        }
    }

    public function get_voucher_details(Request $request){
        try {

            $sql = DB::select("Select UDF_GET_ORG_SCHEMA(?) as db;",[$request->org_id]);
            if(!$sql){
              throw new Exception;
            }
            $org_schema = $sql[0]->db;
            $db = Config::get('database.connections.mysql');
            $db['database'] = $org_schema;
            config()->set('database.connections.coops', $db);

            $sql = DB::connection('coops')->select("Call USP_GET_VOUCHER_DETAILS(?);",[$request->trans_id]);
            
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

    public function process_trail_balance(Request $request){
            try {

                $sql = DB::select("Select UDF_GET_ORG_SCHEMA(?) as db;",[$request->org_id]);
                if(!$sql){
                  throw new Exception;
                }
                $org_schema = $sql[0]->db;
                $db = Config::get('database.connections.mysql');
                $db['database'] = $org_schema;
                config()->set('database.connections.coops', $db);

                $sql = DB::connection('coops')->select("Call USP_RPT_TRAIL_BALANCE(?,?,?);",[$request->frm_date,$request->to_date,$request->branch_id]);
                
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

    public function process_pl_account(Request $request){
            try {

                $sql = DB::select("Select UDF_GET_ORG_SCHEMA(?) as db;",[$request->org_id]);
                if(!$sql){
                  throw new Exception;
                }
                $org_schema = $sql[0]->db;
                $db = Config::get('database.connections.mysql');
                $db['database'] = $org_schema;
                config()->set('database.connections.coops', $db);

                $sql = DB::connection('coops')->select("Call USP_RPT_PL_ACCOUNT(?,?,?);",[$request->frm_date,$request->to_date,$request->branch_id]);
                
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

    public function process_pl_appropriation(Request $request){
            try {

                $sql = DB::select("Select UDF_GET_ORG_SCHEMA(?) as db;",[$request->org_id]);
                if(!$sql){
                  throw new Exception;
                }
                $org_schema = $sql[0]->db;
                $db = Config::get('database.connections.mysql');
                $db['database'] = $org_schema;
                config()->set('database.connections.coops', $db);

                $sql = DB::connection('coops')->select("Call USP_RPT_PL_APPROPRIATION(?,?);",[$request->to_date,$request->branch_id]);
                
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

    public function process_balancesheet(Request $request){
            try {

                $sql = DB::select("Select UDF_GET_ORG_SCHEMA(?) as db;",[$request->org_id]);
                if(!$sql){
                  throw new Exception;
                }
                $org_schema = $sql[0]->db;
                $db = Config::get('database.connections.mysql');
                $db['database'] = $org_schema;
                config()->set('database.connections.coops', $db);

                $sql = DB::connection('coops')->select("Call USP_RPT_BALANCESHEET(?,?);",[$request->to_date,$request->branch_id]);
                
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
}