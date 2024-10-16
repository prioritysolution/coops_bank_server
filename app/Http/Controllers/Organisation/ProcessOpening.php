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

            $sql = DB::connection('coops')->statement("Call USP_PUSH_OPN_MEMBERSHIP(?,?,?,?,?,?,?,?,?,?,?,?,?,?,@error,@message);",[$request->open_date,$request->mem_id,$request->adm_no,$request->adm_date,$request->ledg_no,$request->mem_type,$request->nom_name,$request->nom_rel,$request->nom_age,$request->nom_add,$request->open_share,$request->open_div,auth()->user()->Id,$request->branch_Id]);

            if(!$sql){
                throw new Exception('Operation Error Found !!');
            }
            $result = DB::connection('coops')->select("Select @error As Error_No,@message As Message;");
            $error_No = $result[0]->Error_No;
            $message = $result[0]->Message;

            if($error_No<0){
                return response()->json([
                    'message' => 'Error Found',
                    'details' => $message,
                ],200);
            }
            else{
                return response()->json([
                    'message' => 'Success',
                    'details' => $message,
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