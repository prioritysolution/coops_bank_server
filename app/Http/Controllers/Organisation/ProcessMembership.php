<?php

namespace App\Http\Controllers\Organisation;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Config;
use App\Models\OrgUser;
use Illuminate\Http\Exceptions\HttpResponseException;
use Exception;
use Session;
use DB;
use \stdClass;
use App\Traits\SendSMS;

class ProcessMembership extends Controller
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

    public function process_member_profile_add(Request $request){
        $validator = Validator::make($request->all(),[
            'mem_fst_name' => 'required',
            'mem_lst_name' => 'required',
            'mem_rela_name' => 'required',
            'mem_rel_type' => 'required',
            'mem_dob' => 'required',
            'mem_gend' => 'required',
            'mem_caste' => 'required',
            'mem_relig' => 'required',
            'mem_add' => 'required',
            'mem_state' => 'required',
            'mem_dist' => 'required',
            'mem_block' => 'required',
            'mem_village' => 'required',
            'mem_police' => 'required',
            'mem_post' => 'required',
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

                $sql = DB::connection('coops')->statement("Call USP_ADD_EDIT_MEMBER(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,@error,@message,@member_name,@mem_mob,@memcode);",[null,$request->member_no,$request->mem_fst_name,$request->mem_mid_name,$request->mem_lst_name,$request->mem_rela_name,$request->mem_rel_type,$request->mem_dob,$request->mem_gend,$request->mem_caste,$request->mem_relig,$request->mem_mob,$request->mem_mail,$request->mem_add,$request->mem_state,$request->mem_dist,$request->mem_block,$request->mem_village,$request->mem_police,$request->mem_post,$request->mem_unit,$request->mem_aadhar,$request->mem_voter,$request->mem_ration,$request->mem_pan,$request->fin_id,auth()->user()->Id,1]);

                if(!$sql){
                    throw new Exception;
                }

                $result = DB::connection('coops')->select("Select @error As Error_No,@message As Message,@member_name As Name,@mem_mob As Mobile,@memcode As Code;");
                $error_no = $result[0]->Error_No;
                $error_message = $result[0]->Message;
                $member_name = $result[0]->Name;
                $mem_mob = $result[0]->Mobile;
                $mem_code = $result[0]->Code;
 
                if($error_no<0){
                    DB::connection('coops')->rollBack();
                    return response()->json([
                        'message' => 'Error Found',
                        'details' => $error_message,
                    ],200);
                }
                else{
                    DB::connection('coops')->commit();
                    if($mem_mob<>0 || preg_match('/^\d{10}$/', $mem_mob)){
                        $this->send_welcome_member($request->org_id,$member_name,$mem_code,$mem_mob);
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

    public function get_member_update(Request $request){
        try {

            $sql = DB::select("Select UDF_GET_ORG_SCHEMA(?) as db;",[$request->org_id]);
            if(!$sql){
              throw new Exception;
            }
            $org_schema = $sql[0]->db;
            $db = Config::get('database.connections.mysql');
            $db['database'] = $org_schema;
            config()->set('database.connections.coops', $db);

            $sql = DB::connection('coops')->select("Call USP_GET_MEMBER_UPDATE_DATA(?);",[$request->mem_no]);

            if (empty($sql)) {
                // Custom validation for no data found
                return response()->json([
                    'message' => 'No Data Found',
                    'details' => [],
                ], 200);
            }

            $error = $sql[0]->Error_No;
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

    public function process_update_profile(Request $request){
        $validator = Validator::make($request->all(),[
            'mem_id' => 'required',
            'mem_fst_name' => 'required',
            'mem_lst_name' => 'required',
            'mem_rela_name' => 'required',
            'mem_rel_type' => 'required',
            'mem_dob' => 'required',
            'mem_gend' => 'required',
            'mem_caste' => 'required',
            'mem_relig' => 'required',
            'mem_add' => 'required',
            'mem_state' => 'required',
            'mem_dist' => 'required',
            'mem_block' => 'required',
            'mem_village' => 'required',
            'mem_police' => 'required',
            'mem_post' => 'required',
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

                $sql = DB::connection('coops')->statement("Call USP_ADD_EDIT_MEMBER(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,@error,@message,@member_name,@mem_mob,@memcode);",[$request->mem_id,$request->member_no,$request->mem_fst_name,$request->mem_mid_name,$request->mem_lst_name,$request->mem_rela_name,$request->mem_rel_type,$request->mem_dob,$request->mem_gend,$request->mem_caste,$request->mem_relig,$request->mem_mob,$request->mem_mail,$request->mem_add,$request->mem_state,$request->mem_dist,$request->mem_block,$request->mem_village,$request->mem_police,$request->mem_post,$request->mem_unit,$request->mem_aadhar,$request->mem_voter,$request->mem_ration,$request->mem_pan,$request->fin_id,auth()->user()->Id,2]);

                if(!$sql){
                    throw new Exception;
                }

                $result = DB::connection('coops')->select("Select @error As Error_No,@message As Message;");
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
                        'details' => 'Member Profile Successfully Updated !!',
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

    public function get_member_data(Request $request){
        try {

            $sql = DB::select("Select UDF_GET_ORG_SCHEMA(?) as db;",[$request->org_id]);
            if(!$sql){
              throw new Exception;
            }
            $org_schema = $sql[0]->db;
            $db = Config::get('database.connections.mysql');
            $db['database'] = $org_schema;
            config()->set('database.connections.coops', $db);

            $sql = DB::connection('coops')->select("Call USP_GET_GLOBAL_MEM_DET(?);",[$request->mem_no]);

            if (empty($sql)) {
                // Custom validation for no data found
                return response()->json([
                    'message' => 'No Data Found',
                    'details' => [],
                ], 200);
            }

            $error = $sql[0]->Error;
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

    public function get_membership_member_data(Request $request){
        try {

            $sql = DB::select("Select UDF_GET_ORG_SCHEMA(?) as db;",[$request->org_id]);
            if(!$sql){
              throw new Exception;
            }
            $org_schema = $sql[0]->db;
            $db = Config::get('database.connections.mysql');
            $db['database'] = $org_schema;
            config()->set('database.connections.coops', $db);

            $sql = DB::connection('coops')->select("Call USP_GET_MEMBERSHIP_MEM_DET(?);",[$request->mem_no]);

            if (empty($sql)) {
                // Custom validation for no data found
                return response()->json([
                    'message' => 'No Data Found',
                    'details' => [],
                ], 200);
            }

            $error = $sql[0]->Error;
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

    public function process_member_search(Request $request){
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
            $results = DB::connection('coops')->select("CALL USP_GLOBAL_MEMBER_SEARCH(?);", [$request->keyword]);
        
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

    public function get_shprod_details(Request $request){
        try {

            $sql = DB::select("Select UDF_GET_ORG_SCHEMA(?) as db;",[$request->org_id]);
            if(!$sql){
              throw new Exception;
            }
            $org_schema = $sql[0]->db;
            $db = Config::get('database.connections.mysql');
            $db['database'] = $org_schema;
            config()->set('database.connections.coops', $db);

            $sql = DB::connection('coops')->select("Call USP_GET_SHPROD_DETAILS(?);",[$request->type]);

            if (empty($sql)) {
                // Custom validation for no data found
                return response()->json([
                    'message' => 'No Data Found',
                    'details' => [],
                ], 200);
            }

            $result = $sql[0]->Error_No;

            if($result<0){
                return response()->json([
                    'message' => 'Error Found',
                    'details' => 'No Mapping Data Found !!',
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

    public function process_membership(Request $request){
        $validator = Validator::make($request->all(),[
            'trans_date' => 'required',
            'member_id' => 'required',
            'adm_fees' => 'required',
            'mem_type' => 'required',
            'no_of_share' => 'required',
            'share_rate' => 'required',
            'share_amt' => 'required',
            'tot_amt' => 'required',
            'adm_gl' => 'required',
            'share_gl' => 'required',
            'cash_details' => 'required',
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

                $sql = DB::connection('coops')->statement("Call USP_ADD_MEMBERSHIP(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,@error,@message);",[$request->trans_date,$request->ref_vouch,$request->member_id,$request->admm_No,$request->adm_fees,$request->mem_type,$request->nomin_name,$request->nom_add,$request->nom_rel,$request->nom_age,$request->no_of_share,$request->ledg_fol,$request->share_rate,$request->share_amt,$request->tot_amt,$request->adm_gl,$request->share_gl,$request->bank_id,$request->sb_id,$request->branch_id,$request->fin_id,auth()->user()->Id]);

                if(!$sql){
                    throw new Exception;
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

    public function get_share_details(Request $request){
        try {

            $sql = DB::select("Select UDF_GET_ORG_SCHEMA(?) as db;",[$request->org_id]);
            if(!$sql){
              throw new Exception;
            }
            $org_schema = $sql[0]->db;
            $db = Config::get('database.connections.mysql');
            $db['database'] = $org_schema;
            config()->set('database.connections.coops', $db);

            $sql = DB::connection('coops')->select("Call USP_GET_MEMBER_INFO(?,?);",[$request->mem_no,$request->date]);

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

    public function process_share_issue(Request $request){
        $validator = Validator::make($request->all(),[
            'trans_date' => 'required',
            'share_id' => 'required',
            'mem_id' => 'required',
            'share_gl' => 'required',
            'no_share' => 'required',
            'share_rate' => 'required',
            'tot_amt' => 'required',
            'cash_details' => 'required',
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

                $sql = DB::connection('coops')->statement("Call USP_ADD_SHARE_ISSUE(?,?,?,?,?,?,?,?,?,?,?,?,?,@error,@message);",[$request->trans_date,$request->ref_vouch,$request->share_id,$request->mem_id,$request->share_gl,$request->no_share,$request->share_rate,$request->tot_amt,$request->bank_id,$request->sb_id,$request->branch_id,$request->fin_id,auth()->user()->Id]);

                if(!$sql){
                    throw new Exception;
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

    public function process_refund_share(Request $request){
        $validator = Validator::make($request->all(),[
            'trans_date' => 'required',
            'share_id' => 'required',
            'mem_id' => 'required',
            'share_gl' => 'required',
            'no_share' => 'required',
            'share_rate' => 'required',
            'tot_amt' => 'required',
            'cash_details' => 'required',
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

                $sql = DB::connection('coops')->statement("Call USP_SHARE_REFUND(?,?,?,?,?,?,?,?,?,?,?,?,?,@error,@message);",[$request->trans_date,$request->ref_vouch,$request->share_id,$request->mem_id,$request->share_gl,$request->no_share,$request->share_rate,$request->tot_amt,$request->bank_id,$request->sb_id,$request->branch_id,$request->fin_id,auth()->user()->Id]);

                if(!$sql){
                    throw new Exception;
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

    public function process_withdrw_membership(Request $request){
        $validator = Validator::make($request->all(),[
            'trans_date' => 'required',
            'share_id' => 'required',
            'mem_id' => 'required',
            'share_gl' => 'required',
            'dividend_gl' => 'required',
            'share_amt' => 'required',
            'dividend_amt' => 'required',
            'tot_amt' => 'required',
            'cash_details' => 'required',
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

                $sql = DB::connection('coops')->statement("Call USP_WITHDRW_MEMBERSHIP(?,?,?,?,?,?,?,?,?,?,?,?,?,?,@error,@message);",[$request->trans_date,$request->ref_vouch,$request->share_id,$request->mem_id,$request->share_gl,$request->dividend_gl,$request->share_amt,$request->dividend_amt,$request->tot_amt,$request->bank_id,$request->sb_id,$request->branch_id,$request->fin_id,auth()->user()->Id]);

                if(!$sql){
                    throw new Exception;
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

    public function process_share_ledger(Request $request){
            try {

                $sql = DB::select("Select UDF_GET_ORG_SCHEMA(?) as db;",[$request->org_id]);
                if(!$sql){
                  throw new Exception;
                }
                $org_schema = $sql[0]->db;
                $db = Config::get('database.connections.mysql');
                $db['database'] = $org_schema;
                config()->set('database.connections.coops', $db);

                $sql = DB::connection('coops')->select("Call USP_RPT_SHARE_LEDGER(?,?,?,?);",[$request->Acct_Id,$request->form_date,$request->to_date,$request->mode]);
                
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

    public function process_member_info(Request $request){
            try {

                $sql = DB::select("Select UDF_GET_ORG_SCHEMA(?) as db;",[$request->org_id]);
                if(!$sql){
                  throw new Exception;
                }
                $org_schema = $sql[0]->db;
                $db = Config::get('database.connections.mysql');
                $db['database'] = $org_schema;
                config()->set('database.connections.coops', $db);

                $sql = DB::connection('coops')->select("Call USP_GET_MEM_ENQUERY(?,?,?);",[$request->mem_Id,$request->form_date,$request->to_date]);

                if (empty($sql)) {
                    // Custom validation for no data found
                    return response()->json([
                        'message' => 'No Data Found',
                        'details' => [],
                    ], 200);
                }
                
                $groupedData = [];
                foreach ($sql as $detail) {
                    $queryType = $detail->QueryType;
                    
                    // Initialize an array for each QueryType if it doesn't exist
                    if (!isset($groupedData[$queryType])) {
                        $groupedData[$queryType] = [];
                    }
                    
                    // Push the detail to the appropriate QueryType array
                    $groupedData[$queryType][] = $detail;
                }
                
                

                    return response()->json([
                        'message' => 'Data Found',
                        'details' => $groupedData,
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