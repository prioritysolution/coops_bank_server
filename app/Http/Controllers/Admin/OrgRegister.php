<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use Illuminate\Http\Exceptions\HttpResponseException;
use Hash;
use Exception;
use Session;
use DB;
use \stdClass;

class OrgRegister extends Controller
{
    public function get_org_type(){
        try {
            
            $sql = DB::select("Select Id,Type_Name From mst_org_type Where Is_Active=?",[1]);

            if(!$sql){
                throw new Exception;
            }

            return response()->json([
                'message' => 'Data Found',
                'details' => $sql
            ]);

        } catch (Exception $ex) {
            $response = response()->json([
                'message' => 'Error Found',
                'details' => $ex->getMessage(),
            ],400);

            throw new HttpResponseException($response);
        }
    }

    public function get_org_module(){
        try {
            
            $sql = DB::select("Select Id,Module_Name From mst_org_module Where Is_Active=?",[1]);

            if(!$sql){
                throw new Exception;
            }

            return response()->json([
                'message' => 'Data Found',
                'details' => $sql
            ]);

        } catch (Exception $ex) {
            $response = response()->json([
                'message' => 'Error Found',
                'details' => $ex->getMessage(),
            ],400);

            throw new HttpResponseException($response);
        }
    }

    public function process_org(Request $request){
        $validator = Validator::make($request->all(),[
            'org_type' =>'required',
            'org_name' =>'required',
            'org_address' => 'required',
            'org_mobile' => 'required',
            'org_mail' => 'required|email',
            'org_reg_no' => 'required',
            'org_reg_date' => 'required'
        ]);

        if($validator->passes()){
            try {
               
                DB::beginTransaction();
                $sql = DB::statement("Call USP_POST_ORG(?,?,?,?,?,?,?,?,?,@error,@message);",[$request->org_type,$request->org_name,$request->org_address,$request->org_mobile,$request->org_mail,$request->org_reg_no,$request->org_reg_date,auth()->user()->Id,$request->org_gstin]);

                if(!$sql){
                    throw new Exception;
                }

                $result = DB::select("Select @error As Error,@message As Message");
                $error = $result[0]->Error;
                $message = $result[0]->Message;

                if($error<0){
                    DB::rollBack(); 
                    return response()->json([
                        'message' => 'Error Found',
                        'details' => $message,
                    ],200);
                }
                else{
                    DB::commit();
                    return response()->json([
                        'message' => 'Success',
                        'details' => 'Organisation Is Successfully Registred !!',
                    ],200);
                }

            } catch (Exception $ex) {
                DB::rollBack(); 
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

    public function get_org_list(){
        try {
            
            $sql = DB::select("Select m.Id,m.Org_Name,m.Org_Address,m.Org_Mobile,m.Org_Mail,concat(m.Org_Reg_No,' - ',DATE_FORMAT(m.Org_Reg_Date, '%d-%M-%Y')) As Reg_No,t.Type_Name,Case When m.Is_Active=1 Then 'Active' Else 'De-Active' End As Status From mst_org_register m join mst_org_type t on t.Id=m.Og_Type;");

            if(!$sql){
                throw new Exception;
            }

            return response()->json([
                'message' => 'Data Found',
                'details' => $sql
            ]);

        } catch (Exception $ex) {
            $response = response()->json([
                'message' => 'Error Found',
                'details' => $ex->getMessage(),
            ],400);

            throw new HttpResponseException($response);
        }
    }
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
    public function process_org_module(Request $request){
        $validator = Validator::make($request->all(),[
            'module_list' =>'required',
            'add_data' => 'required',
            'org_id' => 'required'
        ]);

        if($validator->passes()){

            try {
                DB::beginTransaction();

                $module_table_drop = DB::statement("Drop Temporary Table If Exists Temp_Module_Data;");
                if(!$module_table_drop){
                    throw new Exception;
                }

                $module_table_create = DB::statement("Create Temporary Table Temp_Module_Data
                                        (
                                            Module_Id			Int
                                        );");
                if(!$module_table_create){
                    throw new Exception;
                }

                $module_Data = $this->convertToObject($request->module_list);

                foreach ($module_Data as $module) {
                    DB::statement("Insert Into Temp_Module_Data (Module_Id) Values (?);",[$module->module_id]);
                }

                $add_table_drop = DB::statement("Drop Temporary Table If Exists TempAddData;");

                if(!$add_table_drop){
                    throw new Exception;
                }

                $add_table_create = DB::statement("Create Temporary Table TempAddData
                                        (
                                            Is_Demand			Int,
                                            Is_SMS				Int
                                        );");
                
                if(!$add_table_create){
                    throw new Exception;
                }

                $add_data = $this->convertToObject($request->add_data);
                $demand=0;
                $sms=0;
                foreach ($add_data as $param) {
                    if($param->type==1){
                        $demand = $param->value;
                    }
                    if($param->type==2){
                        $sms = $param->value;
                    } 
                }
                $add_insert = DB::statement("Insert Into TempAddData (Is_Demand,Is_SMS) Values (?,?);",[$demand,$sms]);

                if(!$add_insert){
                    throw new Exception;
                }

                $sql = DB::statement("Call USP_POST_ORG_MODULE(?,?);",[$request->org_id,auth()->user()->Id]);

                if(!$sql){
                    throw new Exception;
                }

                DB::commit();

                return response()->json([
                    'message' => 'Success',
                    'details' => 'Module Successfully Registred !!',
                ],200);

            } catch (Exception $ex) {
                DB::rollBack(); 
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

    public function process_org_fin_year(Request $request){
        $validator = Validator::make($request->all(),[
            'start_date' =>'required',
            'end_date' => 'required',
            'org_id' => 'required'
        ]);

        if($validator->passes()){
            try {
                DB::beginTransaction();

                $sql = DB::statement("Call USP_ADD_ORG_FIN_YEAR(?,?,?,?,@err,@message);",[$request->org_id,$request->start_date,$request->end_date,auth()->user()->Id]);

                if(!$sql){
                    throw new Exception;
                }

                $result = DB::select("Select @err As Error_No,@message As Message");
                $error_No = $result[0]->Error_No;
                $message = $result[0]->Message;

                if($error_No<0){
                    DB::rollBack(); 

                    return response()->json([
                        'message' => 'Error Found',
                        'details' => $message,
                    ],200);
                }
                else{
                    DB::commit();

                    return response()->json([
                        'message' => 'Success',
                        'details' => "Financial Year Setup Successfully !!",
                    ],200);
                }


            } catch (Exception $ex) {
                DB::rollBack(); 
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

    public function get_org_module_list(){
        try {
            
            $sql = DB::select("Select Id,Org_Name,Org_Address,Org_Mobile From mst_org_register Where Id in(Select Org_Id From map_orgwise_module Where Is_Active=1);");

            if(!$sql){
                throw new Exception;
            }

            return response()->json([
                'message' => 'Data Found',
                'details' => $sql
            ]);

        } catch (Exception $ex) {
            $response = response()->json([
                'message' => 'Error Found',
                'details' => $ex->getMessage(),
            ],400);

            throw new HttpResponseException($response);
        } 
    }

    public function process_org_rental(Request $request){
        $validator = Validator::make($request->all(),[
            'start_date' =>'required',
            'end_date' => 'required',
            'org_id' => 'required'
        ]);

        if($validator->passes()){
            try {
                DB::beginTransaction();

                $sql = DB::statement("Call USP_POST_RENTEL_DATA(?,?,?,?,@err,@message);",[$request->org_id,$request->start_date,$request->end_date,auth()->user()->Id]);

                if(!$sql){
                    throw new Exception;
                }

                $result = DB::select("Select @err As Error_No,@message As Message");
                $error_No = $result[0]->Error_No;
                $message = $result[0]->Message;

                if($error_No<0){
                    DB::rollBack(); 

                    return response()->json([
                        'message' => 'Error Found',
                        'details' => $message,
                    ],200);
                }
                else{
                    DB::commit();

                    return response()->json([
                        'message' => 'Success',
                        'details' => "Server Rental Setup Successfully !!",
                    ],200);
                }


            } catch (Exception $ex) {
                DB::rollBack(); 
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

    public function process_sms_date(Request $request){
        $validator = Validator::make($request->all(),[
            'sms_qnty' =>'required',
            'end_date' => 'required',
            'org_id' => 'required'
        ]);

        if($validator->passes()){
            try {
                DB::beginTransaction();

                $sql = DB::statement("Call USP_POST_SMS_DATA(?,?,?,?,@err,@message);",[$request->org_id,$request->sms_qnty,$request->end_date,auth()->user()->Id]);

                if(!$sql){
                    throw new Exception;
                }

                $result = DB::select("Select @err As Error_No,@message As Message");
                $error_No = $result[0]->Error_No;
                $message = $result[0]->Message;

                if($error_No<0){
                    DB::rollBack(); 

                    return response()->json([
                        'message' => 'Error Found',
                        'details' => $message,
                    ],200);
                }
                else{
                    DB::commit();

                    return response()->json([
                        'message' => 'Success',
                        'details' => "SMS Reffiled Successfully !!",
                    ],200);
                }


            } catch (Exception $ex) {
                DB::rollBack(); 
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

    public function get_org_user_role(){
        try {
            
            $sql = DB::select("Select Id,Role_Name From org_user_role Where Is_Active=1;");

            if(!$sql){
                throw new Exception;
            }

            return response()->json([
                'message' => 'Data Found',
                'details' => $sql
            ]);

        } catch (Exception $ex) {
            $response = response()->json([
                'message' => 'Error Found',
                'details' => $ex->getMessage(),
            ],400);

            throw new HttpResponseException($response);
        } 
    }

    public function process_org_admin_user(Request $request){
        $validator = Validator::make($request->all(),[
            'full_name' =>'required',
            'user_mail' => 'required|email',
            'user_mob' => 'required',
            'user_pass' => 'required',
            'org_id' => 'required',
            'branch_id' => 'required'
        ]);

        if($validator->passes()){
            try {
                DB::beginTransaction();

                $sql = DB::statement("Call USP_POST_ORG_USER(?,?,?,?,?,?,?,?,@err,@message);",[$request->org_id,$request->branch_id,1,$request->full_name,$request->user_mail,$request->user_mob,Hash::make($request->user_pass),auth()->user()->Id]);

                if(!$sql){
                    throw new Exception;
                }

                $result = DB::select("Select @err As Error_No,@message As Message");
                $error_No = $result[0]->Error_No;
                $message = $result[0]->Message;

                if($error_No<0){
                    DB::rollBack(); 

                    return response()->json([
                        'message' => 'Error Found',
                        'details' => $message,
                    ],200);
                }
                else{
                    DB::commit();

                    return response()->json([
                        'message' => 'Success',
                        'details' => "User Added Successfully !!",
                    ],200);
                }


            } catch (Exception $ex) {
                DB::rollBack(); 
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

    public function check_fin_year(Int $org_id){
        try {
           
            $sql = DB::select("Call USP_CHECK_ORG_FIN_YEAR(?);",[$org_id]);

            if(!$sql){
                throw new Exception;
            }

            $error = $sql[0]->Error;
            $start_date = $sql[0]->Start_Date;
            $end_date = $sql[0]->End_Date;

            if($error<0){
                return response()->json([
                    'message' => 'Error Found',
                    'details' => 'Nothing To Be Found !!',
                ],200);
            }
            else{
                return response()->json([
                    'message' => 'Data Found',
                    'details' => ['Start_Date'=>$start_date,'End_Date'=>$end_date],
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

    public function get_org_acct_headlist(){
        try {
           
            $sql = DB::select("Select Id,Head_Name From mst_org_accounts_head Where Is_Active=?",[1]);

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

    public function process_org_acct_sub_head(Request $request){
        $validator = Validator::make($request->all(),[
          'head_id' => 'required',
          'head_Name' => 'required'
        ]);

        if($validator->passes()){
            try {
                DB::beginTransaction();

                $sql = DB::statement("Call USP_ADD_SUBHEAD(?,?,?,@err,@message);",[$request->head_id,$request->head_Name,auth()->user()->Id]);

                if(!$sql){
                    throw new Exception;
                }

                $result = DB::select("Select @err As Error_No,@message As Message");
                $error_No = $result[0]->Error_No;
                $message = $result[0]->Message;

                if($error_No<0){
                    DB::rollBack(); 

                    return response()->json([
                        'message' => 'Error Found',
                        'details' => $message,
                    ],200);
                }
                else{
                    DB::commit();

                    return response()->json([
                        'message' => 'Success',
                        'details' => "Accounst Sub-Head Added Successfully !!",
                    ],200);
                }


            } catch (Exception $ex) {
                DB::rollBack(); 
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

    public function process_org_acct_ledger(Request $request){
        $validator = Validator::make($request->all(),[
            'sub_head_id' => 'required',
            'ledger_name' => 'required'
          ]);
  
          if($validator->passes()){
              try {
                  DB::beginTransaction();
  
                  $sql = DB::statement("Call USP_ADD_ORG_ACCT_LEDGER(?,?,?,@err,@message);",[$request->sub_head_id,$request->ledger_name,auth()->user()->Id]);
  
                  if(!$sql){
                      throw new Exception;
                  }
  
                  $result = DB::select("Select @err As Error_No,@message As Message");
                  $error_No = $result[0]->Error_No;
                  $message = $result[0]->Message;
  
                  if($error_No<0){
                      DB::rollBack(); 
  
                      return response()->json([
                          'message' => 'Error Found',
                          'details' => $message,
                      ],200);
                  }
                  else{
                      DB::commit();
  
                      return response()->json([
                          'message' => 'Success',
                          'details' => "Accounst Ledger Added Successfully !!",
                      ],200);
                  }
  
  
              } catch (Exception $ex) {
                  DB::rollBack(); 
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

    public function get_org_acct_sub_head(){
        try {
           
            $sql = DB::select("Select m.Id,m.Sub_Head_Name,h.Head_Name From mst_org_acct_sub_head m  join mst_org_accounts_head h on h.Id=m.Head_Id Where m.Is_Active=?",[1]);

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

    public function get_org_acct_ledger(){
        try {
           
            $sql = DB::select("Select m.Id,m.Ledger_Name,s.Sub_Head_Name From mst_org_acct_ledger m join mst_org_acct_sub_head s on s.Id=m.Sub_Head Where m.Is_Active=?;",[1]);

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

    public function get_deposit_prod_type(){
        try {
           
            $sql = DB::select("Select Id,Option_Value From mst_org_product_parameater Where Module_Name=? And Option_Name=? And Is_Active=?;",['Deposit','Product Type',1]);

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

    public function get_deposit_type(){
        try {
           
            $sql = DB::select("Select Id,Option_Value From mst_org_product_parameater Where Module_Name=? And Option_Name=? And Is_Active=?;",['Deposit','Deposit Type',1]);

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

    public function get_deposit_gl(String $type_name){
        try {

            if($type_name==''){
                return response()->json([
                    'message' => 'Error Found',
                    'details' => 'Type Name Cannot Be Empty !!',
                ],400);
            }
            else{
                if($type_name=='Prn'){
                    $sql = DB::select("Select Id,Ledger_Name From mst_org_acct_ledger Where Is_Active=? And Sub_Head=?",[1,6]);
                }
                else if($type_name=='Intt'){
                    $sql = DB::select("Select Id,Ledger_Name From mst_org_acct_ledger Where Is_Active=? And Sub_Head=?",[1,35]);
                }
                else if($type_name=='Prov'){
                    $sql = DB::select("Select Id,Ledger_Name From mst_org_acct_ledger Where Is_Active=? And Sub_Head=?",[1,7]);
                }
                else{
                    return response()->json([
                        'message' => 'Error Found',
                        'details' => 'Type Name Cannot Be Matched !!',
                    ],200); 
                }
                
                if(!$sql){
                    throw new Exception;
                }

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

    public function process_deposit_product(Request $request){
        $validator = Validator::make($request->all(),[
            'product_type' =>'required',
            'deposit_type' =>'required',
            'product_name' => 'required',
            'prn_gl' => 'required',
            'intt_gl' => 'required',
            'prov_gl' => 'required'
        ]);

        if($validator->passes()){

            try {
                DB::beginTransaction();

                $sql = DB::statement("Call USP_POST_ORG_DEPOSIT_PRODUCT (?,?,?,?,?,?,?,@error,@msg);",[$request->product_type,$request->deposit_type,$request->product_name,$request->prn_gl,$request->intt_gl,$request->prov_gl,auth()->user()->Id]);

                if(!$sql){
                    throw new Exception;
                }

                $response = DB::select("Select @error As Error_No,@msg As Message;");
                $error_No = $response[0]->Error_No;
                $message = $response[0]->Message;

                if($error_No<0){
                    DB::rollBack(); 
                    return response()->json([
                        'message' => 'Error Found',
                        'details' => $message,
                    ],200);

                }
                else{
                    DB::commit();

                    return response()->json([
                        'message' => 'Success',
                        'details' => 'Deposit Product Successfully Added !!',
                    ],200);
                }

            } catch (Exception $ex) {
                DB::rollBack(); 
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

    public function get_product_list(){
        try {
           
            $sql = DB::select("Select Id,UDF_GET_PAREAM_NAME(Product_Type) As Product_Type,UDF_GET_PAREAM_NAME(Deposit_Type) As Deposit_Type,Product_Name From mst_org_deposit_product;");

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

    public function check_org_deposit_module(Int $org_id){
        try {
           
            $sql = DB::select("Select UDF_CHECK_ORG_MODULE(?,?) As Error;",[$org_id,'DEP']);

            if(!$sql){
                throw new Exception;
            }

            $count = $sql[0]->Error;

            if($count<0){
                return response()->json([
                    'message' => 'Error Found',
                    'details' => 'Organisation Dose Not Exists Deposit Module !!',
                ],200);
            }
            else{
                return response()->json([
                    'message' => 'Success',
                    'details' => 'Organisation Have Deposit Module',
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

    public function get_deposit_intt_type(){
        try {
           
            $sql = DB::select("Select Id,Option_Value From mst_org_product_parameater Where Module_Name=? And Option_Name=? And Is_Active=?;",['Deposit','Interest',1]);

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

    public function get_deposit_dur_unit(){
        try {
           
            $sql = DB::select("Select Id,Option_Value From mst_org_product_parameater Where Module_Name=? And Option_Name=? And Is_Active=?;",['Deposit','Term',1]);

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

    public function get_deposit_fine_on(){
        try {
           
            $sql = DB::select("Select Id,Option_Value From mst_org_product_parameater Where Module_Name=? And Option_Name=? And Is_Active=?;",['Deposit','Peneal Charge',1]);

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

    public function process_org_deposit_product(Request $request){
        $validator = Validator::make($request->all(),[
            'org_id' =>'required',
            'prod_id' =>'required',
            'product_name' => 'required',
            'interest_type' => 'required',
            'min_amt' => 'required',
            'max_amt' => 'required',
            'roi' => 'required',
            'min_dur' => 'required',
            'max_dur' => 'required',
            'dur_unit' => 'required',
            'lock_days' => 'required',
            'pass_fees' => 'required',
            'default_fine' => 'required',
            'fine_on' => 'required',
            'inoperative_month' => 'required',
            'dormat_month' => 'required'
        ]);

        if($validator->passes()){

            try {
                DB::beginTransaction();

                $sql = DB::statement("Call USP_MAP_ORG_DEPOSIT_PRODUCT (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,@error,@msg);",[$request->org_id,$request->prod_id,$request->product_name,$request->interest_type,$request->min_amt,$request->max_amt,$request->roi,$request->min_dur,$request->max_dur,$request->dur_unit,$request->lock_days,$request->pass_fees,$request->default_fine,$request->fine_on,$request->inoperative_month,$request->dormat_month,auth()->user()->Id]);

                if(!$sql){
                    throw new Exception;
                }

                $response = DB::select("Select @error As Error_No,@msg As Message;");
                $error_No = $response[0]->Error_No;
                $message = $response[0]->Message;

                if($error_No<0){
                    DB::rollBack(); 
                    return response()->json([
                        'message' => 'Error Found',
                        'details' => $message,
                    ],200);

                }
                else{
                    DB::commit();

                    return response()->json([
                        'message' => 'Success',
                        'details' => 'Deposit Product Maped Successfully !!',
                    ],200);
                }

            } catch (Exception $ex) {
                DB::rollBack(); 
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

    public function get_loan_prd_type(){
        try {
           
            $sql = DB::select("Select Id,Option_Value From mst_org_product_parameater Where Module_Name=? And Option_Name=? And Is_Active=?;",['Loan','Product Type',1]);

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

    public function get_loan_gl(String $type_name){
        try {

            if($type_name==''){
                return response()->json([
                    'message' => 'Error Found',
                    'details' => 'Type Name Cannot Be Empty !!',
                ],200);
            }
            else{
                if($type_name=='Prn_Curr'){
                    $sql = DB::select("Select Id,Ledger_Name From mst_org_acct_ledger Where Is_Active=? And Sub_Head=?",[1,15]);
                }
                else if($type_name=='Prn_Od'){
                    $sql = DB::select("Select Id,Ledger_Name From mst_org_acct_ledger Where Is_Active=? And Sub_Head=?",[1,15]);
                }
                else if($type_name=='Intt_Curr'){
                    $sql = DB::select("Select Id,Ledger_Name From mst_org_acct_ledger Where Is_Active=? And Sub_Head=?",[1,30]);
                }
                else if($type_name=='Intt_Od'){
                    $sql = DB::select("Select Id,Ledger_Name From mst_org_acct_ledger Where Is_Active=? And Sub_Head=?",[1,30]);
                }
                else if($type_name=='Prov_Curr'){
                    $sql = DB::select("Select Id,Ledger_Name From mst_org_acct_ledger Where Is_Active=? And Sub_Head=?",[1,18]);
                }
                else if($type_name=='Prov_Od'){
                    $sql = DB::select("Select Id,Ledger_Name From mst_org_acct_ledger Where Is_Active=? And Sub_Head=?",[1,18]);
                }
                else{
                    return response()->json([
                        'message' => 'Error Found',
                        'details' => 'Type Name Cannot Be Matched !!',
                    ],200); 
                }
                
                if(!$sql){
                    throw new Exception;
                }

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

    public function process_loan_product(Request $request){
        $validator = Validator::make($request->all(),[
         'product_name' => 'required',
         'prod_type' => 'required',
         'prn_curr' => 'required',
         'prn_od' => 'required',
         'intt_curr' => 'required',
         'intt_od' => 'required',
         'prov_curr' => 'required',
         'prov_Od' => 'required'
        ]);

        if($validator->passes()){

            try {
                DB::beginTransaction();

                $sql = DB::statement("Call USP_POST_ORG_LOAN_PRODUCT (?,?,?,?,?,?,?,?,?,@error,@msg);",[$request->product_name,$request->prod_type,$request->prn_curr,$request->prn_od,$request->intt_curr,$request->intt_od,$request->prov_curr,$request->prov_Od,auth()->user()->Id]);

                if(!$sql){
                    throw new Exception;
                }

                $response = DB::select("Select @error As Error_No,@msg As Message;");
                $error_No = $response[0]->Error_No;
                $message = $response[0]->Message;

                if($error_No<0){
                    DB::rollBack(); 
                    return response()->json([
                        'message' => 'Error Found',
                        'details' => $message,
                    ],200);

                }
                else{
                    DB::commit();

                    return response()->json([
                        'message' => 'Success',
                        'details' => 'Loan Product Successfully Added !!',
                    ],200);
                }

            } catch (Exception $ex) {
                DB::rollBack(); 
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

    public function get_losn_prod_list(){
        try {
           
            $sql = DB::select("Select Id,UDF_GET_PAREAM_NAME(Product_Type) As Product_Type,Product_Name From mst_org_loan_product;");

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

    public function check_org_loan_module(Int $org_id){
        try {
           
            $sql = DB::select("Select UDF_CHECK_ORG_MODULE(?,?) As Error;",[$org_id,'LN']);

            if(!$sql){
                throw new Exception;
            }

            $count = $sql[0]->Error;

            if($count<0){
                return response()->json([
                    'message' => 'Error Found',
                    'details' => 'Organisation Dose Not Exists Deposit Module !!',
                ],200);
            }
            else{
                return response()->json([
                    'message' => 'Success',
                    'details' => 'Organisation Have Deposit Module',
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

    public function get_loan_repay_type(){
        try {
           
            $sql = DB::select("Select Id,Option_Value From mst_org_product_parameater Where Module_Name=? And Option_Name=? And Is_Active=?;",['Loan','Loan Type',1]);

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

    public function get_loan_dur_unit(){
        try {
           
            $sql = DB::select("Select Id,Option_Value From mst_org_product_parameater Where Module_Name=? And Option_Name=? And Is_Active=?;",['Loan','Duration',1]);

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

    public function get_loan_overdue_type(){
        try {
           
            $sql = DB::select("Select Id,Option_Value From mst_org_product_parameater Where Module_Name=? And Option_Name=? And Is_Active=?;",['Loan','Overdue On',1]);

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

    public function get_loan_greace_type(){
        try {
           
            $sql = DB::select("Select Id,Option_Value From mst_org_product_parameater Where Module_Name=? And Option_Name=? And Is_Active=?;",['Loan','Grace On',1]);

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

    public function get_orgwise_deposit_prod(Int $org_id){
        try {
           
            $sql = DB::select("Select m.Id,d.Product_Name From map_org_deposit_product m join mst_org_deposit_product d on d.Id=m.Prod_Id Where m.Org_Id=?;",[$org_id]);

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

    public function process_org_loan_product(Request $request){
        $validator = Validator::make($request->all(),[
            'org_id' => 'required',
            'prod_id' => 'required',
            'loan_type' => 'required',
            'prod_name' => 'required',
            'min_amt' => 'required',
            'max_amt' => 'required',
            'min_dur' => 'required',
            'max_dur' => 'required',
            'dur_unit' => 'required',
            'roi' => 'required',
            'mis_nature' => 'required',
            'mis_amt' => 'required'
           ]);
   
           if($validator->passes()){
   
               try {
                   DB::beginTransaction();
   
                   $sql = DB::statement("Call USP_MAP_ORG_LOAN_PRODUCT (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,@error,@msg);",[$request->org_id,$request->prod_id,$request->loan_type,$request->prod_name,$request->min_amt,$request->max_amt,$request->min_dur,$request->max_dur,$request->dur_unit,$request->roi,$request->is_overdue,$request->overdue_on,$request->overdue_count,$request->overdue_rate,$request->grace_days,$request->grace_on,$request->is_npa,$request->npa_after,$request->is_share_ded,$request->share_perc,$request->is_deposit_ded,$request->deposit_perc,$request->deposit_prod,$request->mis_amt,$request->sec_prod_id,$request->max_allow,$request->mis_nature,auth()->user()->Id]);
   
                   if(!$sql){
                       throw new Exception;
                   }
   
                   $response = DB::select("Select @error As Error_No,@msg As Message;");
                   $error_No = $response[0]->Error_No;
                   $message = $response[0]->Message;
   
                   if($error_No<0){
                       DB::rollBack(); 
                       return response()->json([
                           'message' => 'Error Found',
                           'details' => $message,
                       ],200);
   
                   }
                   else{
                       DB::commit();
   
                       return response()->json([
                           'message' => 'Success',
                           'details' => 'Loan Product Successfully Maped !!',
                       ],200);
                   }
   
               } catch (Exception $ex) {
                   DB::rollBack(); 
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

    public function process_org_branch(Request $request){
        $validator = Validator::make($request->all(),[
            'org_id' => 'required',
            'branch_name' => 'required',
            'branch_add' => 'required',
            'mobile_no' => 'required',
            'email' => 'required'
           ]);
   
           if($validator->passes()){
   
               try {
                   DB::beginTransaction();
   
                   $sql = DB::statement("Call USP_MAP_ORG_BRANCH (?,?,?,?,?,?,@error,@msg);",[$request->org_id,$request->branch_name,$request->branch_add,$request->mobile_no,$request->email,auth()->user()->Id]);
   
                   if(!$sql){
                       throw new Exception;
                   }
   
                   $response = DB::select("Select @error As Error_No,@msg As Message;");
                   $error_No = $response[0]->Error_No;
                   $message = $response[0]->Message;
   
                   if($error_No<0){
                       DB::rollBack(); 
                       return response()->json([
                           'message' => 'Error Found',
                           'details' => $message,
                       ],200);
   
                   }
                   else{
                       DB::commit();
   
                       return response()->json([
                           'message' => 'Success',
                           'details' => 'Branch Successfully Added !!',
                       ],200);
                   }
   
               } catch (Exception $ex) {
                   DB::rollBack(); 
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

    public function get_orgwise_branch(Int $org_id){
        try {
           
            $sql = DB::select("Select Id,Branch_Name From map_org_branch Where Org_Id=? And Is_Active=?;",[$org_id,1]);

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

    public function get_member_type(){
        try {
           
            $sql = DB::select("Select Id,Option_Value From mst_org_product_parameater Where Module_Name=? And Option_Name=? And Is_Active=?;",['Member','Member Type',1]);

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

    public function get_admission_gl(String $type_name){
        try {

            if($type_name==''){
                return response()->json([
                    'message' => 'Error Found',
                    'details' => 'Type Name Cannot Be Empty !!',
                ],200);
            }
            else{
                if($type_name=='Adm'){
                    $sql = DB::select("Select Id,Ledger_Name From mst_org_acct_ledger Where Is_Active=? And Id=?",[1,186]);
                }
                else if($type_name=='Shr'){
                    $sql = DB::select("Select Id,Ledger_Name From mst_org_acct_ledger Where Is_Active=? And Sub_Head=?",[1,1]);
                }
                else if($type_name=='Div'){
                    $sql = DB::select("Select Id,Ledger_Name From mst_org_acct_ledger Where Is_Active=? And Id=?",[1,63]);
                }
                else{
                    return response()->json([
                        'message' => 'Error Found',
                        'details' => 'Type Name Cannot Be Matched !!',
                    ],200); 
                }
                
                if(!$sql){
                    throw new Exception;
                }

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

    public function process_share_product(Request $request){
        $validator = Validator::make($request->all(),[
            'mem_type' => 'required',
            'adm_gl' => 'required',
            'share_gl' => 'required',
            'div_gl' => 'required'
           ]);
   
           if($validator->passes()){
   
               try {
                   DB::beginTransaction();
   
                   $sql = DB::statement("Call USP_ADD_EDIT_SHARE_PRODUCT (?,?,?,?,?,?,?,@error,@msg);",[null,$request->mem_type,$request->adm_gl,$request->share_gl,$request->div_gl,auth()->user()->Id,1]);
   
                   if(!$sql){
                       throw new Exception;
                   }
   
                   $response = DB::select("Select @error As Error_No,@msg As Message;");
                   $error_No = $response[0]->Error_No;
                   $message = $response[0]->Message;
   
                   if($error_No<0){
                       DB::rollBack(); 
                       return response()->json([
                           'message' => 'Error Found',
                           'details' => $message,
                       ],200);
   
                   }
                   else{
                       DB::commit();
   
                       return response()->json([
                           'message' => 'Success',
                           'details' => 'Share Product Added Successfull !!',
                       ],200);
                   }
   
               } catch (Exception $ex) {
                   DB::rollBack(); 
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