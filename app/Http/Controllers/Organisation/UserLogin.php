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

class UserLogin extends Controller
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

    public function get_current_financial_year(Int $org_id){
        try {
           
            $sql = DB::select("Select Id,Start_Dtae As Start_Date,End_Date From mst_org_financial_year Where Org_Id=? And Is_Active=?;",[$org_id,1]);

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

    public function get_dashboard(Int $org_id){
        try {
            $result = DB::select("CALL USP_GET_ORG_USER_DASHBOARD(?, ?);", [$org_id, auth()->user()->Id]);
            
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
        $validator = Validator::make($request->all(),[
            'user_name' => 'required',
            'user_mod' => 'required',
            'user_pass' =>'required',
            'confirm_password' => 'required_with:user_pass|same:user_pass'
        ]);

        if($validator->passes()){
            try {
                DB::beginTransaction();
                $sql = DB::statement("Call USP_UPDATE_USER_PROF(?,?,?,?,@error,@messg);",[auth()->user()->Id,$request->user_name,$request->user_mod,Hash::make($request->user_pass)]);

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
                        'details' => "User Password Successfully Changed !!",
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
            'user_mod' => 'required',
            'user_pass' =>'required',
            'confirm_password' => 'required_with:user_pass|same:user_pass'
        ]);

        if($validator->passes()){
            try {
                DB::beginTransaction();
                $sql = DB::statement("Call USP_POST_ORG_USER(?,?,?,?,?,?,?,?,@error,@messg);",[$request->org_id,$request->branch_Id,$request->user_role,$request->user_name,$request->user_mail,$request->user_mod,Hash::make($request->user_pass,auth()->user()->Id)]);

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
                        'details' => "User Password Successfully Changed !!",
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

    public function get_org_user_list(){
        try {
           
            $sql = DB::select("Select Id,User_Mail From mst_org_user Where User_Role<>1");

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

    public function get_module_menue_list(){
        try {
            $module = DB::select("Select Id,Sub_Mod_Name From mst_org_sub_module Where Is_Active=1 And Id<>13 Order By Sl Asc;");
            $menue_set = [];

            foreach ($module as $module_key) {
                $sub_m = DB::select("Select Id,Sub_Mod_Id,Menue_Name From mst_org_module_menue Where Sub_Mod_Id=? And Is_Active=? Order By Sl Asc;",[$module_key->Id,1]);
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
} 