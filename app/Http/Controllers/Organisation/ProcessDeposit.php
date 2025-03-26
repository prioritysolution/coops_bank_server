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
use App\Traits\SendSMS;

class ProcessDeposit extends Controller
{
    use SpecimenUpload;
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
    public function get_org_prodtype(Request $request){
        try {
           
            $sql = DB::select("Select Id,Option_Value From mst_org_product_parameater Where Id In(Select Product_Type From mst_org_deposit_product Where Id In(Select Prod_Id From map_org_deposit_product Where Org_Id=?));",[$request->org_id]);

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
    public function get_org_deposit_product(Request $request){
        try {
           
            $sql = DB::select("Select m.Id,m.Prd_SH_Name,p.Deposit_Type From map_org_deposit_product m join mst_org_deposit_product p on p.Id=m.Prod_Id Where m.Org_Id=? And p.Product_Type=?;",[$request->org_id,$request->type]);

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

    public function get_deposit_duration(){
        try {
           
            $sql = DB::select("Select Id,Option_Value From mst_org_product_parameater Where Module_Name=? And Option_Name=? And Is_Active=?;",['Deposit','Term',1]);

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

    public function get_operation_mode(){
        try {
           
            $sql = DB::select("Select Id,Option_Value From mst_org_product_parameater Where Module_Name=? And Option_Name=? And Is_Active=?;",['Deposit','Operation Mode',1]);

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

    public function get_mature_instruction(){
        try {
           
            $sql = DB::select("Select Id,Option_Value From mst_org_product_parameater Where Module_Name=? And Option_Name=? And Is_Active=?;",['Deposit','Maturity Instruction',1]);

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

    public function get_interest_payout(){
        try {
           
            $sql = DB::select("Select Id,Option_Value From mst_org_product_parameater Where Module_Name=? And Option_Name=? And Is_Active=?;",['Deposit','Interest Payout',1]);

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

    public function check_dep_amount(Request $request){
            try {

                $sql = DB::select("Select UDF_GET_ORG_SCHEMA(?) as db;",[$request->org_id]);
                if(!$sql){
                  throw new Exception;
                }
                $org_schema = $sql[0]->db;
                $db = Config::get('database.connections.mysql');
                $db['database'] = $org_schema;
                config()->set('database.connections.coops', $db);

                $sql = DB::connection('coops')->select("Call USP_CHECK_DEPOSIT_PARAM(?,?,?,?,?);",['Amount',$request->prod_id,null,null,$request->amount]);
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
                        'details' => 'Data Matched !!',
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

    public function check_dep_duration(Request $request){
            try {

                $sql = DB::select("Select UDF_GET_ORG_SCHEMA(?) as db;",[$request->org_id]);
                if(!$sql){
                  throw new Exception;
                }
                $org_schema = $sql[0]->db;
                $db = Config::get('database.connections.mysql');
                $db['database'] = $org_schema;
                config()->set('database.connections.coops', $db);

                $sql = DB::connection('coops')->select("Call USP_CHECK_DEPOSIT_PARAM(?,?,?,?,?);",['Duration',$request->prod_id,$request->duration,$request->duration_unit,null]);
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
                        'details' => 'Data Matched !!',
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

    public function get_deposit_mature(Request $request){
            try {

                $sql = DB::select("Select UDF_GET_ORG_SCHEMA(?) as db;",[$request->org_id]);
                if(!$sql){
                  throw new Exception;
                }
                $org_schema = $sql[0]->db;
                $db = Config::get('database.connections.mysql');
                $db['database'] = $org_schema;
                config()->set('database.connections.coops', $db);

                $sql = DB::connection('coops')->select("Select UDF_CAL_DEPOSIT_MATURE_VALUE(?,?,?,?,?) As Mat_Amt;",[$request->prod_id,$request->amount,$request->roi,$request->duration,$request->duration_unit]);

                if (empty($sql)) {
                    // Custom validation for no data found
                    return response()->json([
                        'message' => 'No Data Found',
                        'details' => [],
                    ], 200);
                }

                $mat_val = $sql[0]->Mat_Amt;

                    return response()->json([
                        'message' => 'Data Found',
                        'details' => $mat_val,
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

    public function get_dep_payout_interest(Request $request){
            try {

                $sql = DB::select("Select UDF_GET_ORG_SCHEMA(?) as db;",[$request->org_id]);
                if(!$sql){
                  throw new Exception;
                }
                $org_schema = $sql[0]->db;
                $db = Config::get('database.connections.mysql');
                $db['database'] = $org_schema;
                config()->set('database.connections.coops', $db);

                $sql = DB::connection('coops')->select("Select UDF_CAL_DEPOSIT_PAYOUT_INTT(?,?,?,?) As Mat_Amt;",[$request->prod_id,$request->amount,$request->roi,$request->type_id]);

                if (empty($sql)) {
                    // Custom validation for no data found
                    return response()->json([
                        'message' => 'No Data Found',
                        'details' => [],
                    ], 200);
                }

                $mat_val = $sql[0]->Mat_Amt;

                    return response()->json([
                        'message' => 'Data Found',
                        'details' => $mat_val,
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

    public function get_ecs_account(Request $request){
        try {

            $sql = DB::select("Select UDF_GET_ORG_SCHEMA(?) as db;",[$request->org_id]);
            if(!$sql){
              throw new Exception;
            }
            $org_schema = $sql[0]->db;
            $db = Config::get('database.connections.mysql');
            $db['database'] = $org_schema;
            config()->set('database.connections.coops', $db);

            $sql = DB::connection('coops')->select("Select Id,Account_No From mst_deposit_account_master Where Prod_Type=1 And Mem_Id=?;",[$request->memb_id]);

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

    public function process_deposit_account(Request $request){
        $validator = Validator::make($request->all(),[
            'member_id' => 'required',
            'open_date' => 'required',
            'prod_type' => 'required',
            'dep_type' => 'required',
            'prod_id' => 'required',
            'oper_mode' => 'required',
            'proi' => 'required',
            'pamount' => 'required',
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
                $imageName=NULL;
                $singname=null;
                if ($request->hasFile('spec_image')) {
                    $image = $request->file('spec_image');
                    $extension = strtolower($image->getClientOriginalExtension());
                    $allowedExtensions = ['jpeg', 'jpg', 'png'];
                    if(in_array($extension, $allowedExtensions)){
                        // Define the directory dynamically
                        $directory = 'specimen/' . $request->org_id;
                            
                        // Upload and compress the image
                        $path = $this->uploadAndCompressImage($image, 'img',$directory);
                        $imageName = $path;
                        // Save the path to the database or perform other actions
                    }
                    else{
                        throw new Exception("Invalid File Format !!");
                    }
        
                }
                if ($request->hasFile('spec_sing')) {
                    $image = $request->file('spec_sing');
                    $extension = strtolower($image->getClientOriginalExtension());
                    $allowedExtensions = ['jpeg', 'jpg', 'png'];
                    if(in_array($extension, $allowedExtensions)){
                        // Define the directory dynamically
                        $directory = 'specimen/' . $request->org_id;
                            
                        // Upload and compress the image
                        $path = $this->uploadAndCompressImage($image, 'sing',$directory);
                        $singname = $path;
                        // Save the path to the database or perform other actions
                    }
                    else{
                        throw new Exception("Invalid File Format !!");
                    }
        
                }
                $joint_hld1 = $request->input('joint_hld1') === 'null' ? null : $request->input('joint_hld1');
                $joint_hld2 = $request->input('joint_hld2') === 'null' ? null : $request->input('joint_hld2');
                $ecs_ac_id = $request->input('ecs_ac_id') === 'null' ? null : $request->input('ecs_ac_id');
                $pay_mode = $request->input('pay_mode') === 'null' ? null : $request->input('pay_mode');
                $pay_amt = $request->input('pay_amt') === 'null' ? null : $request->input('pay_amt');
                $cbs_ac_no = $request->input('cbs_ac_no') === 'null' ? null : $request->input('cbs_ac_no');
                $sb_id = $request->input('sb_id') === 'null' ? null : $request->input('sb_id');
                $bank_id = $request->input('bank_id') === 'null' ? null : $request->input('bank_id');
                $duration = $request->input('duration') === 'null' ? null : $request->input('duration');
                $dur_unit = $request->input('dur_unit') === 'null' ? null : $request->input('dur_unit');
                $matur_ins = $request->input('matur_ins') === 'null' ? null : $request->input('matur_ins');
                $matur_date = $request->input('matur_date') === 'null' ? null : $request->input('matur_date');
                $matur_amt = $request->input('matur_amt') === 'null' ? null : $request->input('matur_amt');
                $is_int_payout = $request->input('is_int_payout') === 'null' ? null : $request->input('is_int_payout');
                $agent_id = $request->input('agent_id') === 'null' ? null : $request->input('agent_id');
                $proi = $request->input('proi') === 'null' ? null : $request->input('proi');
                $pref_vouch = $request->input('ref_vouch') === 'null' ? null : $request->input('ref_vouch');

                $sql = DB::connection('coops')->statement("Call USP_ADD_DEPOSIT_ACCOUNT(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,@error,@message,@mem_name,@acct_no,@mobile,@open_date);",[$request->member_id,$request->ref_ac_no,$request->ledg_folio,$request->open_date,$pref_vouch,$request->prod_type,$request->dep_type,$request->prod_id,$request->oper_mode,$proi,$request->pamount,$duration,$dur_unit,$matur_ins,$matur_date,$matur_amt,$request->nom_name,$request->nom_rel,$request->nom_add,$request->nom_age,$joint_hld1,$joint_hld2,$request->ecs_avail,$ecs_ac_id,$is_int_payout,$pay_mode,$pay_amt,$cbs_ac_no,$agent_id,$sb_id,$bank_id,$imageName,$singname,$request->branch_id,$request->fin_id,auth()->user()->Id]);


                if(!$sql){
                    throw new Exception('No Data Found !!');
                }

                $result = DB::connection('coops')->select("Select @error As Error_No,@message As Message,@mem_name As Name,@acct_no As Account,@mobile As Mobile,@open_date As Open_Date;");
                $error_no = $result[0]->Error_No;
                $error_message = $result[0]->Message;
                $member_name = $result[0]->Name;
                $acct_No = $result[0]->Account;
                $mobile = $result[0]->Mobile;
                $open_date = $result[0]->Open_Date;

                if($error_no<0){
                    DB::connection('coops')->rollBack();
                    return response()->json([
                        'message' => 'Error Found',
                        'details' => $error_message,
                    ],200);
                }
                else{
                    DB::connection('coops')->commit();
                    if($mobile<>0 || preg_match('/^\d{10}$/', $mobile)){
                    $this->send_deposit_account_open($request->org_id,$member_name,$acct_No,$mobile,$open_date);
                    }
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

    public function get_dep_account_data(Request $request){
            try {

                $sql = DB::select("Select UDF_GET_ORG_SCHEMA(?) as db;",[$request->org_id]);
                if(!$sql){
                  throw new Exception;
                }
                $org_schema = $sql[0]->db;
                $db = Config::get('database.connections.mysql');
                $db['database'] = $org_schema;
                config()->set('database.connections.coops', $db);

                $sql = DB::connection('coops')->select("Call USP_GET_DEP_ACCOUNT_DATA(?,?,?);",[$request->pAcct_No,$request->ptype,$request->date]);

                if (empty($sql)) {
                    // Custom validation for no data found
                    return response()->json([
                        'message' => 'No Data Found',
                        'details' => "Invalid Account Entred !!",
                    ], 200);
                }

                    $error_no = $sql[0]->Error;
                    $message = $sql[0]->Message;

                    if($error_no<0){
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
                DB::connection('coops')->rollBack();
                $response = response()->json([
                    'message' => 'Error Found',
                    'details' => $ex->getMessage(),
                ],400);
    
                throw new HttpResponseException($response);
            }
    }

    public function search_account(Request $request){
        try {
            $sql = DB::select("SELECT UDF_GET_ORG_SCHEMA(?) as db;", [$request->org_id]);
            if (!$sql) {
                throw new Exception('Database schema not found.');
            }
        
            $org_schema = $sql[0]->db;
            $db = Config::get('database.connections.mysql');
            $db['database'] = $org_schema;
            config()->set('database.connections.coops', $db);
        
            // Get pagination parameters
            $perPage = request()->get('limit', 10); // Default limit: 10
            $page = request()->get('page', 1); // Default page: 1
            $offset = ($page - 1) * $perPage;
        
            // Fetch all results from the stored procedure
            $results = DB::connection('coops')->select("CALL USP_GET_MEMBER_DEP_ACCOUNT(?,?);", [$request->type, $request->keyword]);
        
            // Convert results to a collection to handle pagination manually
            $collection = collect($results);
        
            // Paginate results
            $paginatedData = $collection->slice($offset, $perPage)->values();
            $total = $collection->count();
        
            if ($paginatedData->isEmpty()) {
                return response()->json([
                    'message' => 'No Data Found',
                    'details' => [],
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

    public function process_deposit_post(Request $request){
        $validator = Validator::make($request->all(),[
            'member_id' => 'required',
            'account_id' => 'required',
            'trans_date' => 'required',
            'pamount' => 'required',
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
           
                

                $sql = DB::connection('coops')->statement("Call USP_ADD_DEP_RECEIPT_PAYMENT(?,?,?,?,?,?,?,?,?,?,?,?,@error,@message,@mobile,@Acct_No,@trans_date,@amount,@balance);",[$request->account_id,$request->member_id,$request->trans_date,$request->ref_vouch,$request->pamount,$request->fine_amt,$request->sb_id,$request->bank_id,1,$request->branch_id,$request->fin_id,auth()->user()->Id]);


                if(!$sql){
                    throw new Exception('No Data Found !!');
                }

                $result = DB::connection('coops')->select("Select @error As Error_No,@message As Message,@Acct_No As Acct_No,@trans_date As Trans_Date,@amount As Amount,@balance As Balance,@mobile As Mobile;");
                $error_no = $result[0]->Error_No;
                $error_message = $result[0]->Message;
                $mobile = $result[0]->Mobile;
                $acct_no = $result[0]->Acct_No;
                $trans_date = $result[0]->Trans_Date;
                $amount = $result[0]->Amount;
                $balance = $result[0]->Balance;

                if($error_no<0){
                    DB::connection('coops')->rollBack();
                    return response()->json([
                        'message' => 'Error Found',
                        'details' => $error_message,
                    ],200);
                }
                else{
                    DB::connection('coops')->commit();
                    if($mobile<>0 || preg_match('/^\d{10}$/', $mobile)){
                        $this->send_deposit_credit($request->org_id,$acct_no,$mobile,$trans_date,$amount,$balance);
                    }
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

    public function process_deposit_withdrwan(Request $request){
        $validator = Validator::make($request->all(),[
            'member_id' => 'required',
            'account_id' => 'required',
            'trans_date' => 'required',
            'pamount' => 'required',
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
           

                $sql = DB::connection('coops')->statement("Call USP_ADD_DEP_RECEIPT_PAYMENT(?,?,?,?,?,?,?,?,?,?,?,?,@error,@message,@mobile,@Acct_No,@trans_date,@amount,@balance);",[$request->account_id,$request->member_id,$request->trans_date,$request->ref_vouch,$request->pamount,0,$request->sb_id,$request->bank_id,2,$request->branch_id,$request->fin_id,auth()->user()->Id]);


                if(!$sql){
                    throw new Exception('No Data Found !!');
                }

                $result = DB::connection('coops')->select("Select @error As Error_No,@message As Message,@Acct_No As Acct_No,@trans_date As Trans_Date,@amount As Amount,@balance As Balance,@mobile As Mobile;");
                $error_no = $result[0]->Error_No;
                $error_message = $result[0]->Message;
                $mobile = $result[0]->Mobile;
                $acct_no = $result[0]->Acct_No;
                $trans_date = $result[0]->Trans_Date;
                $amount = $result[0]->Amount;
                $balance = $result[0]->Balance;

                if($error_no<0){
                    DB::connection('coops')->rollBack();
                    return response()->json([
                        'message' => 'Error Found',
                        'details' => $error_message,
                    ],200);
                }
                else{
                    DB::connection('coops')->commit();
                    if($mobile<>0 || preg_match('/^\d{10}$/', $mobile)){
                        $this->send_deposit_account_debit($request->org_id,$acct_no,$mobile,$trans_date,$amount,$balance);
                    }
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

    public function get_specimen(Request $request){
        try {

            $sql = DB::select("Select UDF_GET_ORG_SCHEMA(?) as db;",[$request->org_id]);
            if(!$sql){
              throw new Exception;
            }
            $org_schema = $sql[0]->db;
            $db = Config::get('database.connections.mysql');
            $db['database'] = $org_schema;
            config()->set('database.connections.coops', $db);

            $sql = DB::connection('coops')->select("Select Image_Name,Sing_Name From mst_deposit_specimen Where Is_Active=1 And Acct_Id=?;",[$request->acct_id]);

            if (empty($sql)) {
                // Custom validation for no data found
                $img_url = $this->getUrl($org_id,null);
                $sing_url = $this->getUrl($org_id,null);
                // return response()->json([
                //     'message' => 'No Data Found',
                //     'details' => [],
                // ], 200);
                return response()->json(["image_link" => $img_url,"sing_url" => $sing_url],200);
            }

            $image = $sql[0]->Image_Name;
            $singfile = $sql[0]->Sing_Name;

            $img_url = $this->getUrl($org_id,$image);
            $sing_url = $this->getUrl($org_id,$singfile);

            return response()->json(["image_link" => $img_url,"sing_url" => $sing_url],200);

        } catch (Exception $ex) {
            $response = response()->json([
                'message' => 'Error Found',
                'details' => $ex->getMessage(),
            ],400);

            throw new HttpResponseException($response);
        }
    }

    public function get_dep_intt_rate(Request $request){
            try {

                $sql = DB::select("Select UDF_GET_ORG_SCHEMA(?) as db;",[$request->org_id]);
                if(!$sql){
                  throw new Exception;
                }
                $org_schema = $sql[0]->db;
                $db = Config::get('database.connections.mysql');
                $db['database'] = $org_schema;
                config()->set('database.connections.coops', $db);

                $sql = DB::connection('coops')->select("Select UDF_GET_INTT_RATE(?,?,?,?) As Intt",[$request->prod_id,$request->duration,$request->dur_unit,$request->date]);
                
                if (empty($sql)) {
                // Custom validation for no data found
                return response()->json([
                    'message' => 'No Data Found',
                    'details' => [],
                ], 200);
            }

                $intt_rate = $sql[0]->Intt;

                return response()->json([
                    'message' => 'Data Found',
                    'details' => $intt_rate,
                ],200);


            } catch (Exception $ex) {
                
                $response = response()->json([
                    'message' => 'Error Found',
                    'details' => $ex->getMessage(),
                ],400);
    
                throw new HttpResponseException($response);
            }
    }

    public function get_close_acct_Data(Request $request){
            try {

                $sql = DB::select("Select UDF_GET_ORG_SCHEMA(?) as db;",[$request->org_id]);
                if(!$sql){
                  throw new Exception;
                }
                $org_schema = $sql[0]->db;
                $db = Config::get('database.connections.mysql');
                $db['database'] = $org_schema;
                config()->set('database.connections.coops', $db);

                $sql = DB::connection('coops')->select("Call USP_GET_CLOSE_ACCOUNT_DATA(?,?,?);",[$request->acct_no,$request->date,1]);
                
                if(!$sql){
                    throw new Exception("No Data Found");
                }
                $erroe_No = $sql[0]->Error;
                $message = $sql[0]->Message;

                if($erroe_No<0){
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

    public function get_mature_data(Request $request){
            try {

                $sql = DB::select("Select UDF_GET_ORG_SCHEMA(?) as db;",[$request->org_id]);
                if(!$sql){
                  throw new Exception;
                }
                $org_schema = $sql[0]->db;
                $db = Config::get('database.connections.mysql');
                $db['database'] = $org_schema;
                config()->set('database.connections.coops', $db);

                $sql = DB::connection('coops')->select("Call USP_GET_CLOSE_ACCOUNT_DATA(?,?,?);",[$request->acct_no,$request->date,2]);
                
                if(!$sql){
                    throw new Exception("No Data Found");
                }

                $erroe_No = $sql[0]->Error;
                $message = $sql[0]->Message;

                if($erroe_No<0){
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

    public function process_close_account(Request $request){
        $validator = Validator::make($request->all(),[
            'member_id' => 'required',
            'account_id' => 'required',
            'trans_date' => 'required',
            'intt_amt' => 'required',
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
           

                $sql = DB::connection('coops')->statement("Call USP_POST_DEP_ACCOUNT_CLOSE(?,?,?,?,?,?,?,?,?,?,@error,@message);",[$request->account_id,$request->member_id,$request->trans_date,$request->ref_vouch,$request->intt_amt,$request->sb_id,$request->bank_id,$request->fin_id,$request->branch_id,auth()->user()->Id]);


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

    public function calculate_mature_interest(Request $request){
            try {

                $sql = DB::select("Select UDF_GET_ORG_SCHEMA(?) as db;",[$request->org_id]);
                if(!$sql){
                  throw new Exception;
                }
                $org_schema = $sql[0]->db;
                $db = Config::get('database.connections.mysql');
                $db['database'] = $org_schema;
                config()->set('database.connections.coops', $db);

                $sql = DB::connection('coops')->select("Select UDF_CAL_MATURE_INTT(?,?,?,?) As Mature_Intt;",[$request->acct_id,$request->date,$request->man_roi,1]);
                
                if (empty($sql)) {
                // Custom validation for no data found
                return response()->json([
                    'message' => 'No Data Found',
                    'details' => [],
                ], 200);
            }

                $mat_intt = $sql[0]->Mature_Intt;
                
                    return response()->json([
                        'message' => 'Data Found',
                        'details' => $mat_intt,
                    ],200);
                

            } catch (Exception $ex) {
                
                $response = response()->json([
                    'message' => 'Error Found',
                    'details' => $ex->getMessage(),
                ],400);
    
                throw new HttpResponseException($response);
            }
    }
    
    public function calculate_bonus_intt(Request $request){
            try {

                $sql = DB::select("Select UDF_GET_ORG_SCHEMA(?) as db;",[$request->org_id]);
                if(!$sql){
                  throw new Exception;
                }
                $org_schema = $sql[0]->db;
                $db = Config::get('database.connections.mysql');
                $db['database'] = $org_schema;
                config()->set('database.connections.coops', $db);

                $sql = DB::connection('coops')->select("Select UDF_CAL_MATURE_INTT(?,?,?,?) As Mature_Intt;",[$request->acct_id,$request->date,$request->roi,2]);
                
                if (empty($sql)) {
                    // Custom validation for no data found
                    return response()->json([
                        'message' => 'No Data Found',
                        'details' => [],
                    ], 200);
                }

                $mat_intt = $sql[0]->Mature_Intt;
                
                    return response()->json([
                        'message' => 'Data Found',
                        'details' => $mat_intt,
                    ],200);
                

            } catch (Exception $ex) {
                
                $response = response()->json([
                    'message' => 'Error Found',
                    'details' => $ex->getMessage(),
                ],400);
    
                throw new HttpResponseException($response);
            }
    }

    public function process_mature_account(Request $request){
        $validator = Validator::make($request->all(),[
            'trans_date' => 'required',
            'account_id' => 'required',
            'member_id' => 'required',
            'principal_amt' => 'required',
            'intt_amt' => 'required',
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
           

                $sql = DB::connection('coops')->statement("Call USP_ADD_DEP_MATURE(?,?,?,?,?,?,?,?,?,?,?,?,?,@error,@message);",[$request->trans_date,$request->ref_vouch,$request->account_id,$request->member_id,$request->principal_amt,$request->intt_amt,$request->bonus_amt,$request->bonus_rate,$request->sb_id,$request->bank_id,$request->fin_id,$request->branch_id,auth()->user()->Id]);


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

    public function process_account_renewal(Request $request){
        $validator = Validator::make($request->all(),[
            'trans_date' => 'required',
            'account_id' => 'required',
            'member_id' => 'required',
            'principal_amt' => 'required',
            'intt_amt' => 'required',
            'pay_amt' => 'required',
            'roi' => 'required',
            'matur_val' => 'required',
            'duration' => 'required',
            'dur_unit' => 'required',
            'mature_date' => 'required',
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
           
                $sql = DB::connection('coops')->statement("Call USP_ADD_DEP_RENEWAL(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,@error,@message);",[$request->trans_date,$request->ref_vouch,$request->account_id,$request->member_id,$request->principal_amt,$request->intt_amt,$request->pay_amt,$request->roi,$request->matur_val,$request->duration,$request->dur_unit,$request->mature_date,$request->sb_id,$request->bank_id,$request->fin_id,$request->branch_id,auth()->user()->Id]);

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

    public function get_payout_account(Request $request){
            try {

                $sql = DB::select("Select UDF_GET_ORG_SCHEMA(?) as db;",[$request->org_id]);
                if(!$sql){
                  throw new Exception;
                }
                $org_schema = $sql[0]->db;
                $db = Config::get('database.connections.mysql');
                $db['database'] = $org_schema;
                config()->set('database.connections.coops', $db);

                $sql = DB::connection('coops')->select("Call USP_DEP_GET_INTT_PAYOUT_DATA(?,?,?,?,?,?);",[$request->date,$request->month,$request->year,$request->acct_no,$request->type,$request->mode]);
                
                if (empty($sql)) {
                    // Custom validation for no data found
                    return response()->json([
                        'message' => 'No Data Found',
                        'details' => "No Data Found",
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

    public function process_blkintt_payout(Request $request){
        $validator = Validator::make($request->all(),[
            'trans_date' => 'required',
            'payout_data' => 'required',
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

                $cash_drop_table = DB::connection('coops')->statement("Drop Temporary Table If Exists temp_payout_data;");
                $cash_create_table = DB::connection('coops')->statement("Create Temporary Table temp_payout_data
                                                                        (
                                                                            schd_id			Int,
                                                                            Acct_Id			Int,
                                                                            Ecs_Id			Int,
                                                                            Amount			Numeric(18,2),
                                                                            Frm_Gl			Int,
                                                                            To_Gl			Int
                                                                        );");
                if(is_array($request->payout_data)){
                    $cash_data = $this->convertToObject($request->payout_data);

                    foreach ($cash_data as $pay_details) {
                        $meter_insert =  DB::connection('coops')->statement("Insert Into temp_payout_data (schd_id,Acct_Id,Ecs_Id,Amount) Values (?,?,?,?);",[$pay_details->sch_id,$pay_details->acct_id,$pay_details->ecs_id,$pay_details->amount]);
                    }
                }

                $sql = DB::connection('coops')->statement("Call USP_DEP_POST_BLK_PAYOUT(?,?,?,?,?,@error,@message);",[$request->trans_date,$request->ref_vouch,$request->fin_id,$request->branch_id,auth()->user()->Id]);


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

    public function process_singintt_payout(Request $request){
        $validator = Validator::make($request->all(),[
            'trans_date' => 'required',
            'acct_id' => 'required',
            'payout_data' => 'required',
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

                $payout_drop_table = DB::connection('coops')->statement("Drop Temporary Table If Exists temp_payout_data;");
                $payout_create_table = DB::connection('coops')->statement("Create Temporary Table temp_payout_data
                                                                        (
                                                                            schd_id			Int,
                                                                            Acct_Id			Int,
                                                                            Ecs_Id			Int,
                                                                            Amount			Numeric(18,2),
                                                                            Frm_Gl			Int,
                                                                            To_Gl			Int
                                                                        );");
                if(is_array($request->payout_data)){
                    $payout_data = $this->convertToObject($request->payout_data);

                    foreach ($payout_data as $pay_details) {
                        $meter_insert =  DB::connection('coops')->statement("Insert Into temp_payout_data (schd_id,Acct_Id,Ecs_Id,Amount) Values (?,?,?,?);",[$pay_details->sch_id,$pay_details->acct_id,$pay_details->ecs_id,$pay_details->amount]);
                    }
                }
                
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

                $sql = DB::connection('coops')->statement("Call USP_DEP_POST_SINGLE_PAYOUT(?,?,?,?,?,?,?,?,@error,@message);",[$request->trans_date,$request->ref_vouch,$request->acct_id,$request->sb_id,$request->bank_id,$request->fin_id,$request->branch_id,auth()->user()->Id]);


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

    public function get_account_balance(Request $request){
            try {

                $sql = DB::select("Select UDF_GET_ORG_SCHEMA(?) as db;",[$request->org_id]);
                if(!$sql){
                  throw new Exception;
                }
                $org_schema = $sql[0]->db;
                $db = Config::get('database.connections.mysql');
                $db['database'] = $org_schema;
                config()->set('database.connections.coops', $db);

                $sql = DB::connection('coops')->select("Select UDF_GET_DEP_BALANCE(?,?) As Balance;",[$request->Acct_Id,$request->Date]);
                
                if (empty($sql)) {
                    // Custom validation for no data found
                    return response()->json([
                        'message' => 'No Data Found',
                        'details' => [],
                    ], 200);
                }

                    return response()->json([
                        'message' => 'Data Found',
                        'details' => $sql[0]->Balance,
                    ],200);
                

            } catch (Exception $ex) {
                
                $response = response()->json([
                    'message' => 'Error Found',
                    'details' => $ex->getMessage(),
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

                $sql = DB::connection('coops')->select("Call USP_RPT_GET_DEPOSIT_LEDGER(?,?,?,?);",[$request->Acct_Id,$request->form_date,$request->to_date,$request->mode]);
                
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

    public function only_savings_account(Request $request){
        try {
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
            $results = DB::connection('coops')->select("CALL USP_GET_MEMBER_DEP_ACCOUNT_SB(?,?);", [$request->type, $request->value]);
        
            // Convert results to a collection to handle pagination manually
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

    public function process_passbook(Request $request){
        try {
            $sql = DB::select("SELECT UDF_GET_ORG_SCHEMA(?) AS db;", [$request->org_id]);
            if (!$sql) {
                throw new Exception('Organization schema not found.');
            }
            
            $org_schema = $sql[0]->db;
            $db = Config::get('database.connections.mysql');
            $db['database'] = $org_schema;
            config()->set('database.connections.coops', $db);
        
            // Ensure trans_date is passed as NULL if not provided
            $trans_date = empty($request->trans_date) ? null : $request->trans_date;
            $trans_sl = empty($request->trans_sl) ? null : $request->trans_sl;
        
            $sql = DB::connection('coops')->statement(
                "CALL USP_RPT_DEPOSIT_PASSBOOK(?,?,?,?,?,@error,@message,@param,@data);",
                [$request->org_id, $request->account_no, $trans_date, $trans_sl, $request->mode]
            );
        
            if (empty($sql)) {
                return response()->json([
                    'message' => 'No Data Found',
                    'details' => [],
                ], 200);
            }
        
            $result = DB::connection('coops')->select("SELECT @error AS Error_No, @message AS Message, @param AS Param, @data AS Data;");
            $error = $result[0]->Error_No;
            $message = $result[0]->Message;
            $param = json_decode($result[0]->Param);
            $data = json_decode($result[0]->Data);
            $filter_data = [];
            if (isset($data[0]->Organisation_Name)) {
                
                if ($data[0]->dep_type === 6) {
                    $filter_data = array_merge($filter_data, [
                        $data[0]->Organisation_Name,
                        $data[0]->Branch_Name,
                        $data[0]->Product_Name,
                        "DEPOSITOR NAME : " . $data[0]->Member_Name,
                        "FATHER / HUSBAND NAME : " . $data[0]->Rel_Name,
                        "ADDRESS : " . $data[0]->Address,
                        "MOBILE NO : " . $data[0]->Mobile_No,
                        "ACCOUNT NO : " . $data[0]->Account_No,
                        "CIF No : " . $data[0]->Member_Code,
                        "OPERATION MODE : " . $data[0]->Operation_Mode,
                        "DATE OF OPENING : " . $data[0]->Opening_Date,
                    ]);
                }
        
                if ($data[0]->dep_type === 7 || $data[0]->dep_type === 79) {
                    $filter_data = array_merge($filter_data, [
                        $data[0]->Organisation_Name,
                        $data[0]->Branch_Name,
                        $data[0]->Product_Name,
                        "DEPOSITOR NAME : " . $data[0]->Member_Name,
                        "FATHER / HUSBAND NAME : " . $data[0]->Rel_Name,
                        "ADDRESS : " . $data[0]->Address,
                        "MOBILE NO : " . $data[0]->Mobile_No,
                        "ACCOUNT NO : " . $data[0]->Account_No,
                        "CIF No : " . $data[0]->Member_Code,
                        "OPERATION MODE : " . $data[0]->Operation_Mode,
                        "DATE OF OPENING : " . $data[0]->Opening_Date,
                        "ROI : " . $data[0]->Roi,
                        "INSTALLMENT AMOUNT : " . $data[0]->Installment_Amount,
                        "MATURITY DATE : " . $data[0]->Maturity_Date,
                    ]);
                }
        
                if (!empty($data[0]->Joint_Holder_1) && !empty($data[0]->Joint_Holder_2)) {
                    $filter_data = array_merge($filter_data, [
                        "JOINT HOLDER NAME : " . $data[0]->Joint_Holder_1,
                        "                  : " . $data[0]->Joint_Holder_2
                    ]);
                } elseif (!empty($data[0]->Joint_Holder_1)) {
                    $filter_data = array_merge($filter_data, [
                        "JOINT HOLDER'S NAME : " . $data[0]->Joint_Holder_1
                    ]);
                }
        
                if (!empty($data[0]->Nomination)) {
                    $filter_data = array_merge($filter_data, [
                        "NOM. REGISTERED : " . $data[0]->Nomination
                    ]);
                }
            } else {
                $filter_data = $data;
            }
        
            if ($error < 0) {
                return response()->json([
                    'message' => 'Error Found',
                    'details' => $message,
                ], 200);
            } else {
                return response()->json([
                    'message' => 'Data Found',
                    'details' => [
                        "Parameater" => $param,
                        "Details" => $filter_data
                    ],
                ], 200);
            }
        } catch (Exception $ex) {
            $response = response()->json([
                'message' => 'Error Found',
                'details' => $ex->getMessage(),
            ], 400);
        
            throw new HttpResponseException($response);
        }
    }

    public function log_last_print(Request $request){
        $validator = Validator::make($request->all(),[
            'org_id' => 'required',
            'acct_id' => 'required',
            'last_date' => 'required',
            'last_line' => 'required',
            'last_trans_sl' => 'required'
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
    
                 $sql = DB::connection('coops')->statement("INSERT INTO log_module_passbook_print (Module_Id,Acct_Id,Last_Date,Last_Line,Last_Trans_Sl,Created_By) VALUES (?,?,?,?,?,?);",[2,$request->acct_id,$request->last_date,$request->last_line,$request->last_trans_sl,auth()->user()->Id]);
    
                 if(!$sql){
                     throw new Exception;
                 }
                
                    DB::connection('coops')->commit();
                    return response()->json([
                        'message' => 'Success',
                        'details' => 'Passbook Log Successfully Created !!',
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