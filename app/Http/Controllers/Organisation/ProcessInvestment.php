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

class ProcessInvestment extends Controller
{
    public function get_account_type(){
        try {
           
            $sql = DB::select("Select Id,Option_Value From mst_org_product_parameater Where Module_Name=? And Option_Name=? And Is_Active=?;",['Investment','Account Type',1]);

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

    public function get_invest_type(){
        try {
           
            $sql = DB::select("Select Id,Option_Value From mst_org_product_parameater Where Module_Name=? And Option_Name=? And Is_Active=?;",['Investment','Type',1]);

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

    public function get_interest_type(){
        try {
           
            $sql = DB::select("Select Id,Option_Value From mst_org_product_parameater Where Module_Name=? And Option_Name=? And Is_Active=?;",['Investment','Interest Type',1]);

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

    public function calculate_mature_val(Request $request){
        $validator = Validator::make($request->all(),[
            'org_id' => 'required',
            'acct_type' => 'required',
            'amount' => 'required',
            'roi' => 'required',
            'duration' => 'required',
            'intt_type' => 'required'
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

            $sql = DB::connection('coops')->select("Select UDF_CAL_INVEST_MATURE_VALUE(?,?,?,?,?) As Mature_Val;",[$request->acct_type,$request->amount,$request->roi,$request->duration,$request->intt_type]);

            if(!$sql){
                throw new Exception('No Data Found !!');
            }

                return response()->json([
                    'message' => 'Data Found',
                    'details' => $sql[0]->Mature_Val,
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

    public function get_invest_ledger(Int $mode){
        try {
           if($mode=1){
            $sql = DB::select("Select Id,Ledger_Name From mst_org_acct_ledger Where Sub_Head=16 And Is_Active=1");
           }
           if($mode=2){
            $sql = DB::select("Select Id,Ledger_Name From mst_org_acct_ledger Where Sub_Head in(24,25) And Is_Active=1");
           }
            

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
}