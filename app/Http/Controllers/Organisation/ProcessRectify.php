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

class ProcessRectify extends Controller
{
    // Membership Rectification Start Here

    public function get_mem_rectify_drop(){
        try {
           
            $sql = DB::select("Select Id,Option_Value From mst_org_product_parameater Where Is_Active=1 And Module_Name=? And Option_Name=?;",['Member','Rectify']);

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

    public function get_mem_rec_data(Request $request){
        try {

            $sql = DB::select("Select UDF_GET_ORG_SCHEMA(?) as db;",[$request->org_id]);
            if(!$sql){
              throw new Exception;
            }
            $org_schema = $sql[0]->db;
            $db = Config::get('database.connections.mysql');
            $db['database'] = $org_schema;
            config()->set('database.connections.coops', $db);

            

            $sql = DB::connection('coops')->select("Call USP_GET_RECTIFY_MEMBERSHIP(?,?,?);",[$request->mem_no,$request->date,$request->type]);

            if(empty($sql)){
                return response()->json([
                    'message' => 'Error Found',
                    'details' => 'No Data Found !!',
                ],200);
            }

            if(!$sql){
                throw new Exception("No Data Found !!");
            }

         
            $error_no = $sql[0]->Error_No;
            $error_message = $sql[0]->Message;
            

            if($error_no<0){
              
                return response()->json([
                    'message' => 'Error Found',
                    'details' => $error_message,
                ],200);
            }
            else{
               
                return response()->json([
                    'message' => 'Success',
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

    public function process_mem_rectify(Request $request){
        $validator = Validator::make($request->all(),[
            'mem_id' => 'required',
            'trans_id' => 'required',
            'type' => 'required',
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

                $sql = DB::connection('coops')->statement("Call USP_RECTIFY_MEMBERSHIP(?,?,?,@error,@message);",[$request->mem_id,$request->trans_id,$request->type]);

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
                        'details' => 'This Entry Successfully Removed From System !!',
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
    // Membership Module End Here

    // Deposit Start Here

    public function get_dep_rectify_type(){
        try {
           
            $sql = DB::select("Select Id,Option_Value From mst_org_product_parameater Where Is_Active=1 And Module_Name=? And Option_Name=?;",['Deposit','Rectify']);

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

    public function get_dep_rec_data(Request $request){
        try {

            $sql = DB::select("Select UDF_GET_ORG_SCHEMA(?) as db;",[$request->org_id]);
            if(!$sql){
              throw new Exception;
            }
            $org_schema = $sql[0]->db;
            $db = Config::get('database.connections.mysql');
            $db['database'] = $org_schema;
            config()->set('database.connections.coops', $db);

            

            $sql = DB::connection('coops')->select("Call USP_GET_DEPOSIT_RECTIFY(?,?,?,?);",[$request->acct_no,$request->prod_id,$request->type,$request->date]);

            if(empty($sql)){
                return response()->json([
                    'message' => 'Error Found',
                    'details' => 'No Data Found !!',
                ],200);
            }

            if(!$sql){
                throw new Exception("No Data Found !!");
            }

         
            $error_no = $sql[0]->Error_No;
            $error_message = $sql[0]->Message;
            

            if($error_no<0){
              
                return response()->json([
                    'message' => 'Error Found',
                    'details' => $error_message,
                ],200);
            }
            else{
               
                return response()->json([
                    'message' => 'Success',
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


    public function process_dep_rectify(Request $request){
        $validator = Validator::make($request->all(),[
            'acct_id' => 'required',
            'trans_id' => 'required',
            'type' => 'required',
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

                $sql = DB::connection('coops')->statement("Call USP_DEPOSIT_RECTIFY(?,?,?,@error,@message);",[$request->acct_id,$request->trans_id,$request->type]);

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
                        'details' => 'This Entry Successfully Removed From System !!',
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

    // Deposit End Here
}