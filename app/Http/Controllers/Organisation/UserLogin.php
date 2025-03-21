<?php

namespace App\Http\Controllers\Organisation;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Config;
use App\Models\OrgUser;
use Illuminate\Http\Exceptions\HttpResponseException;
use Hash;
use Exception;
use Session;
use DB;
use \stdClass;
use App\Traits\SendMail;

class UserLogin extends Controller
{
    use SendMail;
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

    public function process_user_login(Request $request){
        $validator = Validator::make($request->all(),[
            'email' =>'required|email',
            'password' =>'required',
            'user_device' => 'required',
            'user_ip' => 'required',
        ]);

        if($validator->passes()){
            try {
                DB::beginTransaction();
                $sql = DB::statement("Call USP_PUSH_ORG_USER_LOGIN (?,?,?,@user_name,@error,@message,@user_pass,@org_id,@org_name,@org_branch,@branch_name,@org_add,@org_reg,@branch_add);",[$request->email,$request->user_device,$request->user_ip]);
                
                if(!$sql){
                    throw new Exception;
                }
                $response = DB::select("Select @user_name As User_Name,@error As Error_No,@message As Message,@user_pass As User_Pass,@org_id As Org_Id,@org_name As org_name,@org_branch As Branch_Id,@branch_name As Branch_Name,@org_add As Address,@org_reg As reg_no,@branch_add Branch_Add");
                $error_No = $response[0]->Error_No;
                $message = $response[0]->Message;
                $user_Pass = $response[0]->User_Pass;
                $org_id = $response[0]->Org_Id;
                $user_Name = $response[0]->User_Name;
                $org_Name = $response[0]->org_name;
                $org_branch = $response[0]->Branch_Id;
                $org_branch_name = $response[0]->Branch_Name;
                $org_add = $response[0]->Address;
                $reg_no = $response[0]->reg_no;
                $br_add = $response[0]->Branch_Add;

                if($error_No<0){
                    DB::rollBack();
                    $response = response()->json([
                        'message' => 'Error Found',
                        'details' => $message,
                    ],200);
        
                    return $response;
                }
                else{

                    if(Hash::check($request->password, $user_Pass)){
                        DB::commit();
                        $user = OrgUser::where("User_Mail", $request->email)->first();
                        $token = $user->CreateToken("UserAuthAPI")->plainTextToken;
                        return response()->json([
                            'message' => 'Login Successful',
                            'token'=>$token,
                            'User_Name' => $user_Name,
                            'org_id' => $org_id,
                            'org_name' => $org_Name,
                            'branch_id' => $org_branch,
                            'branch_name' => $org_branch_name,
                            'org_add' => $org_add,
                            'branch_add' => $br_add,
                            'org_reg' => $reg_no
                        ],200);
                    }
                    else{
                        DB::rollBack();
                        $response = response()->json([
                            'message' => 'Error Found',
                            'details' => 'Invalid Password'
                        ],200);
                    
                        return $response;
                    }
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

    public function get_current_financial_year(Request $request){
        try {
           
            $sql = DB::select("Select Id,Start_Dtae As Start_Date,End_Date From mst_org_financial_year Where Org_Id=? And Is_Active=?;",[$request->org_id,1]);

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

    public function get_dashboard(Request $request){
        try {
            $result = DB::select("CALL USP_GET_ORG_USER_DASHBOARD(?, ?);", [$request->org_id, auth()->user()->Id]);
            
            if (empty($result)) {
                // Custom validation for no data found
                return response()->json([
                    'message' => 'No Data Found',
                    'details' => [],
                ], 200);
            }

            $menu_set = [];
            
            foreach ($result as $row) {
                if (!isset($menu_set[$row->Module_Id])) {
                    $menu_set[$row->Module_Id] = [
                        "title" => $row->Sub_Mod_Name,
                        "Icon" => $row->Icon,
                        "path" => $row->Page_Alies,
                        "childLinks" => []
                    ];
                }
                if ($row->Menue_Name) {
                    $menu_set[$row->Module_Id]['childLinks'][] = [
                        "Menue_Name" => $row->Menue_Name,
                        "Icon" => $row->Menu_Icon,
                        "Page_Allies" => $row->Page_Allies
                    ];
                }
            }
    
            $menu_set = array_values($menu_set);
    
            return response()->json([
                'message' => 'Data Found',
                'Data' => $menu_set
            ], 200);
    
        } catch (Exception $ex) {
            return response()->json([
                'message' => 'Error Found',
                'details' => $ex->getMessage(),
            ], 400);
        }
    }

    public function process_logout(Request $request){
        try {
            DB::beginTransaction();

            $sql = DB::statement("Call USP_PUSH_ORG_USER_LOGOUT(?,@error,@message);",[auth()->user()->Id]);

            if(!$sql){
                throw new Exception();
            }
            
            $response = DB::select("Select @error As Error,@message As Message");
            $error_No = $response[0]->Error;
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
                auth()->user()->tokens()->delete();
                return response()->json([
                    'message' => 'Success',
                    'details' => 'Logout Successfull !!',
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

    public function get_dashboard_item(Request $request){
        $validator = Validator::make($request->all(),[
            'org_id' =>'required',
            'branch_id' =>'required',
            'date' => 'required',
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
            $sql = DB::connection('coops')->select("Call USP_GET_DASHBOARD_ITEM(?,?);",[$request->branch_id,$request->date]);

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
        else{
            $errors = $validator->errors();

        $response = response()->json([
        'message' => 'Invalid data send',
        'details' => $errors->messages(),
    ],400);

    throw new HttpResponseException($response);
        }
    }

    public function get_user_profile(){
        try {
           
            $sql = DB::select("Select m.Id,m.User_Name,m.User_Mail,m.User_Mob,r.Role_Name From mst_org_user m 
                                Join org_user_role r On r.Id=m.User_Role
                                Where m.Id=? And m.Is_Active=1",[auth()->user()->Id]);

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

    public function process_update_user_prof(Request $request){

            try {
                DB::beginTransaction();
                $sql = DB::statement("Call USP_UPDATE_USER_PROF(?,?,?,?,@error,@messg);",[auth()->user()->Id,$request->user_name,$request->user_mob,Hash::make($request->user_pass)]);

                if(!$sql){
                    throw new Exception;
                }

                $result = DB::select("Select @error As Error_No,@messg As Message");
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
                        'details' => "User Profile Successfully Updated !!",
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

    public function get_user_role(){
        try {
           
            $sql = DB::select("Select Id,Role_Name From org_user_role");

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
    
    public function process_org_user(Request $request){
        $validator = Validator::make($request->all(),[
            'org_id' => 'required',
            'branch_Id' => 'required',
            'user_role' => 'required',
            'user_name' => 'required',
            'user_mail' => 'required',
            'user_mob' => 'required',
            'user_pass' =>'required',
            'confirm_password' => 'required_with:user_pass|same:user_pass'
        ]);

        if($validator->passes()){
            try {
                DB::beginTransaction();
                $sql = DB::statement("Call USP_POST_ORG_USER(?,?,?,?,?,?,?,?,@error,@messg);",[$request->org_id,$request->branch_Id,$request->user_role,$request->user_name,$request->user_mail,$request->user_mob,Hash::make($request->user_pass),auth()->user()->Id]);

                if(!$sql){
                    throw new Exception;
                }

                $result = DB::select("Select @error As Error_No,@messg As Message");
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
                        'details' => "User Successfully Added !!",
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

    public function get_org_user_list(Request $request){
        try {
           
            $sql = DB::select("Select Id,User_Mail From mst_org_user Where User_Role<>1 And Org_Id=?",[$request->org_id]);

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

    public function get_org_all_user_list(Request $request){
        try {
           
            $sql = DB::select("Select m.Id,m.User_Name,m.User_Mail,m.User_Mob,r.Role_Name From mst_org_user m 
                                Join org_user_role r On r.Id=m.User_Role
                                Where m.Is_Active=1 And Org_Id=?",[$request->org_id]);

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

    public function get_module_menue_list(Request $request){
        try {
            $module = DB::select("Select Id,Sub_Mod_Name From mst_org_sub_module Where Is_Active=1 And Id<>13 And Module_Id In(SELECT Module_Id FROM map_orgwise_module WHERE Org_Id =? And Is_Active=1) Order By Sl Asc;",[$request->org_id]);
            $menue_set = [];
            $config_check = DB::select("Select Is_Demand From mst_org_config Where Org_Id=?",[$request->org_id]);
            foreach ($module as $module_key) {
                if($config_check[0]->Is_Demand===0){
                    $sub_m = DB::select("Select Id,Sub_Mod_Id,Menue_Name From mst_org_module_menue Where Sub_Mod_Id=? And Is_Active=? And Id Not in (40,65) Order By Sl Asc;",[$module_key->Id,1]);
                }
                else{
                    $sub_m = DB::select("Select Id,Sub_Mod_Id,Menue_Name From mst_org_module_menue Where Sub_Mod_Id=? And Is_Active=? Order By Sl Asc;",[$module_key->Id,1]);
                }
                
                $menue_set [] = ["Module_Id"=>$module_key->Id,"Module_Name"=>$module_key->Sub_Mod_Name,"childLinks"=>$sub_m];
            }
            return response()->json([
                'message' =>'Data Found',
                'Data' => $menue_set
            ],200);

        } catch (Exception $ex) {
           $response = response()->json([
                    'message' => 'Error Found',
                    'details' => $ex->getMessage(),
                ],400);
    
                throw new HttpResponseException($response);
        }
    }

    public function process_map_user_module(Request $request){
        $validator = Validator::make($request->all(),[
            'user_id' =>'required',
            'org_id' => 'required',
            'module_array' => 'required'
        ]);
        if($validator->passes()){
            try {

                DB::beginTransaction();

                $module_list = $this->convertToObject($request->module_array);
                $drop_table = DB::statement("Drop Temporary Table If Exists TempModuleList;");
                $create_tabl = DB::statement("Create Temporary Table TempModuleList
                                        (
                                            module_Id				Int,
                                            menue_Id				Int
                                        );");
                foreach ($module_list as $module) {
                   DB::statement("Insert Into TempModuleList (module_Id,menue_Id) Values (?,?);",[$module->module_id,$module->menue_id]);
                }

                $sql = DB::statement("Call USP_MAP_ORG_USER_MENUE(?,?,?,@error,@messg);",[$request->org_id,$request->user_id,auth()->user()->Id]);

                if(!$sql){
                    throw new Exception;
                }

                $result = DB::select("Select @error As Error_No,@messg As Message");
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
                        'details' => "User Module Maped Successfully !!",
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

    public function process_user_otp(String $mailid,Int $otpfor){
        try {
           
            $sql = DB::select("Select UDF_USEROTP(?,?,?) As OTP;",[$mailid,null,1]);

            if (empty($sql)) {
                // Custom validation for no data found
                return response()->json([
                    'message' => 'No Data Found',
                    'details' => [],
                ], 200);
            }

            $otp = $sql[0]->OTP;
            if($otp<0){
                return response()->json([
                    'message' => 'Error Found',
                    'details' => 'User Dosenot Exists !!',
                ],200);
            }
            else{
                $success = $this->otp_send($mailid,$otp,$otpfor);
                if($success){
                    return response()->json([
                        'message' => 'Success',
                        'details' => 'OTP Successfully Sent To Your Mail Id.',
                    ],200); 
                }
                else{
                    return response()->json([
                        'message' => 'Success',
                        'details' => 'Unable To Send OTP To Your Mail !!',
                    ],200); 
                }
                
            }
            

        } catch (Exception $ex) {
            $response = response()->json([
                'message' => 'Error Found',
                'details' => $ex->getMessage(),
            ],400);

            throw new HttpResponseException($response);
        }
    }

    public function process_otp_verify(Int $otp,String $mailid){
        try {
           
            $sql = DB::select("Select UDF_USEROTP(?,?,?) As OTP;",[$mailid,$otp,2]);

            if (empty($sql)) {
                // Custom validation for no data found
                return response()->json([
                    'message' => 'No Data Found',
                    'details' => [],
                ], 200);
            }

            $otp = $sql[0]->OTP;
            if($otp<0){
                return response()->json([
                    'message' => 'Error Found',
                    'details' => 'Invalid OTP Provide !!',
                ],200);
            }
            else{                
                    return response()->json([
                        'message' => 'Success',
                        'details' => 'OTP Verified Successfully !!',
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

    public function process_forgot_password(Request $request){
        $validator = Validator::make($request->all(),[
            'user_mail' => 'required',
            'user_pass' =>'required',
            'confirm_password' => 'required_with:user_pass|same:user_pass'
        ]);

        if($validator->passes()){
            try {
                DB::beginTransaction();
                $sql = DB::statement("Call USP_ORG_USER_CHANGE_PASSWORD(?,?,@error,@messg);",[$request->user_mail,Hash::make($request->user_pass)]);

                if(!$sql){
                    throw new Exception;
                }

                $result = DB::select("Select @error As Error_No,@messg As Message");
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
                        'details' => $message,
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

    public function process_terminate_session(Request $request){
        $validator = Validator::make($request->all(),[
            'user_mail' => 'required',
            'user_otp' =>'required',
        ]);

        if($validator->passes()){
            try {
                DB::beginTransaction();
                $sql = DB::statement("Call USP_TERMINATE_ORG_USER_LOG(?,?,@error,@messg);",[$request->user_mail,$request->user_otp]);

                if(!$sql){
                    throw new Exception;
                }

                $result = DB::select("Select @error As Error_No,@messg As Message");
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
                        'details' => $message,
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

    public function process_check_year(Request $request){
        $validator = Validator::make($request->all(),[
            'org_id' => 'required',
            'year_id' => 'required',
            'branch_id' => 'required',
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

                $sql = DB::connection('coops')->statement("Call USP_CHECK_FIN(?,?);",[$request->year_id,$request->branch_id]);

                if(!$sql){
                    throw new Exception;
                }

                DB::connection('coops')->commit();
                $response = response()->json([
                    'message' => 'Success',
                    'details' => 'Year Check Complete',
                ],200);
            }

             catch (Exception $ex) {
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