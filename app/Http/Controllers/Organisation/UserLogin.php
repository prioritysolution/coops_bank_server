<?php

namespace App\Http\Controllers\Organisation;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\OrgUser;
use Illuminate\Http\Exceptions\HttpResponseException;
use Hash;
use Exception;
use Session;
use DB;

class UserLogin extends Controller
{
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

            if(!$sql){
                throw new Exception('No data found');
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
            $module = DB::select("Call USP_GET_ORG_USER_DASHBOARD(?,?,?,?,?);",[$org_id,auth()->user()->Id,null,null,1]);
            $menue_set = [];

            foreach ($module as $module_key) {
                $sub_m = DB::select("Call USP_GET_ORG_USER_DASHBOARD(?,?,?,?,?);",[$org_id,auth()->user()->Id,$module_key->Id,$module_key->Module_Id,2]);
                $menue_set [] = ["title"=>$module_key->Sub_Mod_Name,"Icon"=>$module_key->Icon,"path"=>$module_key->Page_Alies,"childLinks"=>$sub_m];
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
} 