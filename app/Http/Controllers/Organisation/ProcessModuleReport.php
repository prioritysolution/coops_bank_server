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

class ProcessModuleReport extends Controller
{
    public function get_mem_report_type(){
        try {
           
            $sql = DB::select("Select Id,Option_Value From mst_org_product_parameater Where Module_Name=? And Option_Name=?",['Member','Report Type']);

            if(!$sql){
                throw new Exception("No Data Found !!");
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

    public function process_member_register(Request $request){
        $validator = Validator::make($request->all(),[
            'branch_id' => 'required',
            'form_date' => 'required',
            'to_date' => 'required',
            'mem_id' => 'required',
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

                $sql = DB::connection('coops')->select("Call USP_RPT_MEMBER_REGISTER(?,?,?,?);",[$request->form_date,$request->to_date,$request->mem_id,$request->branch_id]);
                
                if(!$sql){
                    throw new Exception("No Data Found");
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
        else{

            $errors = $validator->errors();

            $response = response()->json([
                'message' => 'Invalid data send',
                'details' => $errors->messages(),
            ],400);
        
            throw new HttpResponseException($response);
        }
    }

    public function process_member_trans_register(Request $request){
        $validator = Validator::make($request->all(),[
            'branch_id' => 'required',
            'form_date' => 'required',
            'to_date' => 'required',
            'mem_id' => 'required',
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

                $sql = DB::connection('coops')->select("Call USP_RPT_MEMBER_TRANS_REGISTER(?,?,?,?);",[$request->form_date,$request->to_date,$request->mem_id,$request->branch_id]);
                
                if(!$sql){
                    throw new Exception("No Data Found");
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
        else{

            $errors = $validator->errors();

            $response = response()->json([
                'message' => 'Invalid data send',
                'details' => $errors->messages(),
            ],400);
        
            throw new HttpResponseException($response);
        } 
    }

    public function process_member_withdrw_register(Request $request){
        $validator = Validator::make($request->all(),[
            'branch_id' => 'required',
            'form_date' => 'required',
            'to_date' => 'required',
            'mem_id' => 'required',
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

                $sql = DB::connection('coops')->select("Call USP_RPT_MEMBER_WITHDRW_REGISTER(?,?,?,?);",[$request->form_date,$request->to_date,$request->mem_id,$request->branch_id]);
                
                if(!$sql){
                    throw new Exception("No Data Found");
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
        else{

            $errors = $validator->errors();

            $response = response()->json([
                'message' => 'Invalid data send',
                'details' => $errors->messages(),
            ],400);
        
            throw new HttpResponseException($response);
        } 
    }

    public function process_member_detailedlist(Request $request){
        $validator = Validator::make($request->all(),[
            'branch_id' => 'required',
            'form_date' => 'required',
            'to_date' => 'required',
            'mem_id' => 'required',
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

                $sql = DB::connection('coops')->select("Call USP_RPT_MEMBER_DETAILED_LIST(?,?,?,?);",[$request->form_date,$request->to_date,$request->mem_id,$request->branch_id]);
                
                if(!$sql){
                    throw new Exception("No Data Found");
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
        else{

            $errors = $validator->errors();

            $response = response()->json([
                'message' => 'Invalid data send',
                'details' => $errors->messages(),
            ],400);
        
            throw new HttpResponseException($response);
        }
    }

    public function process_member_dividendlist(Request $request){
        $validator = Validator::make($request->all(),[
            'branch_id' => 'required',
            'to_date' => 'required',
            'mem_id' => 'required',
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

                $sql = DB::connection('coops')->select("Call USP_RPT_MEMBER_DIVIDEND_LIST(?,?,?);",[$request->to_date,$request->mem_id,$request->branch_id]);
                
                if(!$sql){
                    throw new Exception("No Data Found");
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
        else{

            $errors = $validator->errors();

            $response = response()->json([
                'message' => 'Invalid data send',
                'details' => $errors->messages(),
            ],400);
        
            throw new HttpResponseException($response);
        }  
    }

    public function get_dep_report_type(){
        try {
           
            $sql = DB::select("Select Id,Option_Value From mst_org_product_parameater Where Module_Name=? And Option_Name=?",['Deposit','Report Type']);

            if(!$sql){
                throw new Exception("No Data Found !!");
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

    public function process_dep_detailedlist(Request $request){
        
    }
}