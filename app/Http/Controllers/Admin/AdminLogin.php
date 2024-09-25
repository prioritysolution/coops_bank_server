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

class AdminLogin extends Controller
{
    public function process_admin_login(Request $request){
        $validator = Validator::make($request->all(),[
            'email' =>'required|email',
            'password' =>'required'
        ]);

        if($validator->passes()){
            try {
            
                $sql = DB::select("Call USP_PUSH_ADMIN_LOGIN(?,?);",[$request->email,$request->password]);

                if(!$sql){
                    throw new Exception;
                }

                $db_error = $sql[0]->Error_No;
                $db_message = $sql[0]->Error_Message;
                $user_pass = $sql[0]->User_Password;

                if($db_error<0){
                    $response = response()->json([
                        'message' => 'Error Found',
                        'details' => $db_message,
                    ],400);
        
                    return $response;
                }
                else{

                    if(Hash::check($request->password, $user_pass)){
                        $user = User::where("User_Mail", $request->email)->first();
                        $token = $user->CreateToken("AdminAuthAPI")->plainTextToken;
                        return response()->json([
                            'message' => 'Login Successful',
                            'token'=>$token
                        ],200);
                    }
                    else{
                        $response = response()->json([
                            'message' => 'Error Found',
                            'details' => 'Invalid Password'
                        ],400);
                    
                        return $response;
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
        else{
            $errors = $validator->errors();

        $response = response()->json([
          'message' => 'Invalid data send',
          'details' => $errors->messages(),
      ],400);
  
      throw new HttpResponseException($response);
        }

    }

    public function get_admin_dashboard(){
        try {
            $module = DB::select("Call USP_GET_ADMIN_DASHBOARD(?,?,?);",[auth()->user()->Id,1,1]);
            $menue_set = [];

            foreach ($module as $module_key) {
                $sub_m = DB::select("Call USP_GET_ADMIN_DASHBOARD(?,?,?);",[auth()->user()->Id,2,$module_key->Id]);
                $menue_set [] = ["title"=>$module_key->Module_Name,"Icon"=>$module_key->Module_Icon,"path"=>$module_key->Page_Alies,"childLinks"=>$sub_m];
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
    public function logout(){
        auth()->user()->tokens()->delete();
        return response()->json([
            'message' => 'Logout Successfull'
            
        ],200);
    
    }

    public function get_user_mappling_module(){
        try {
            $module = DB::select("Select Id,Module_Name From mst_admin_module Where Is_Active=? And Id<>? Order By Sl Asc;",[1,1]);
            $menue_set = [];

            foreach ($module as $module_key) {
                $sub_m = DB::select("Select Id,Module_Id,Menue_name From mst_admin_module_menue Where Module_Id=? And Is_Active=? Order By Sl Asc;",[$module_key->Id,1]);
                $menue_set [] = ["Module_Id"=>$module_key->Id,"Module_Name"=>$module_key->Module_Name,"childLinks"=>$sub_m];
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

    public function process_admin_user(Request $request){
        $validator = Validator::make($request->all(),[
            'user_mail' =>'required|email',
            'user_name' =>'required',
            'user_mob' => 'required',
            'user_pass' => 'required',
            'is_admin' => 'required'
        ]);

        if($validator->passes()){
            try {
                DB::beginTransaction();
                $sql = DB::statement("Call USP_ADD_ADMIN_USER(?,?,?,?,?,?,@error,@messg);",[$request->user_name,$request->user_mail,$request->user_mob,Hash::make($request->user_pass),$request->is_admin,auth()->user()->Id]);

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
                    ],400);
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

    public function process_update_Password(Request $request){
        $validator = Validator::make($request->all(),[
            'user_pass' =>'required',
            'confirm_password' => 'required_with:user_pass|same:user_pass'
        ]);

        if($validator->passes()){
            try {
                DB::beginTransaction();
                $sql = DB::statement("Call USP_CHANGE_ADMIN_USER_PASS(?,?,?,@error,@messg);",[auth()->user()->Id,Hash::make($request->user_pass),Hash::make($request->confirm_password)]);

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
                    ],400);
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

    public function get_admin_user_list(){
        try {
           
            $sql = DB::select("Select Id,User_Name From mst_admin_user Where Is_Active=? And Is_Admin=?;",[1,0]);

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
    public function process_role_menue(Request $request){
        $validator = Validator::make($request->all(),[
            'user_id' =>'required',
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

                $sql = DB::statement("Call USP_MAP_ADMIN_MODULE_MENUE(?,?,@error,@messg);",[$request->user_id,auth()->user()->Id]);

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
                    ],400);
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
