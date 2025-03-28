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

class ProcessMaster extends Controller
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

    public function process_add_state(Request $request){
        $validator = Validator::make($request->all(),[
            'state_name' => 'required',
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

                $sql = DB::connection('coops')->statement("Call USP_ADD_EDIT_STATE(?,?,?,?,@error,@message);",[null,$request->state_name,auth()->user()->Id,1]);

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
                        'details' => 'State Successfully Added !!',
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
    
    public function get_sate_list(Request $request){
        try {

            $sql = DB::select("Select UDF_GET_ORG_SCHEMA(?) as db;",[$request->org_id]);
            if(!$sql){
              throw new Exception;
            }
            $org_schema = $sql[0]->db;
            $db = Config::get('database.connections.mysql');
            $db['database'] = $org_schema;
            config()->set('database.connections.coops', $db);

            $sql = DB::connection('coops')->select("Select Id,State_Name From mst_area_state Order By Id");

            if (empty($sql)) {
                // Custom validation for no data found
                return response()->json([
                    'message' => 'No Data Found',
                    'details' => "No Data Found",
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

    public function process_update_state(Request $request){
        $validator = Validator::make($request->all(),[
            'state_id' => 'required',
            'state_name' => 'required',
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

                $sql = DB::connection('coops')->statement("Call USP_ADD_EDIT_STATE(?,?,?,?,@error,@message);",[$request->state_id,$request->state_name,auth()->user()->Id,2]);

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
                        'details' => 'State Updated Successfully !!',
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

    public function process_add_district(Request $request){
        $validator = Validator::make($request->all(),[
            'state_id' => 'required',
            'dist_name' => 'required',
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

                $sql = DB::connection('coops')->statement("Call USP_ADD_EDIT_DISTRICT(?,?,?,?,?,@error,@message);",[null,$request->dist_name,$request->state_id,auth()->user()->Id,1]);

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
                        'details' => 'District Added Successfully !!',
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

    public function get_dist_list(REquest $request){
        try {
            $sql = DB::select("SELECT UDF_GET_ORG_SCHEMA(?) as db;", [$request->org_id]);
            if (!$sql) {
                throw new Exception('Database schema not found.');
            }
        
            $org_schema = $sql[0]->db;
            $db = Config::get('database.connections.mysql');
            $db['database'] = $org_schema;
            config()->set('database.connections.coops', $db);
        
            // Fetch paginated results using query builder
            $sql = DB::connection('coops')->table('mst_area_district as m')
                ->join('mst_area_state as s', 's.Id', '=', 'm.State_Id')
                ->select('m.Id', 'm.Dist_Name', 's.State_Name', 's.Id AS State_Id')
                ->orderBy('m.Id')
                ->paginate(10); // Change 10 to any desired limit
        
            if ($sql->isEmpty()) {
                return response()->json([
                    'message' => 'No Data Found',
                    'details' => "No Data Found",
                ], 200);
            }
        
            return response()->json([
                'message' => 'Data Found',
                'data' => $sql,
            ], 200);
        
        } catch (Exception $ex) {
            $response = response()->json([
                'message' => 'Error Found',
                'details' => $ex->getMessage(),
            ], 400);
        
            throw new HttpResponseException($response);
        }
        
    }

    public function process_update_dist(Request $request){
        $validator = Validator::make($request->all(),[
            'dist_id' => 'required',
            'state_id' => 'required',
            'dist_name' => 'required',
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

                $sql = DB::connection('coops')->statement("Call USP_ADD_EDIT_DISTRICT(?,?,?,?,?,@error,@message);",[$request->dist_id,$request->dist_name,$request->state_id,auth()->user()->Id,2]);

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
                        'details' => 'District Updated Successfully !!',
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

    public function get_statewise_dist(Request $request){
        try {

            $sql = DB::select("Select UDF_GET_ORG_SCHEMA(?) as db;",[$request->org_id]);
            if(!$sql){
              throw new Exception;
            }
            $org_schema = $sql[0]->db;
            $db = Config::get('database.connections.mysql');
            $db['database'] = $org_schema;
            config()->set('database.connections.coops', $db);

            $sql = DB::connection('coops')->select("Select Id,Dist_Name From mst_area_district Where State_Id=?;",[$request->state_id]);

            if (empty($sql)) {
                // Custom validation for no data found
                return response()->json([
                    'message' => 'No Data Found',
                    'details' => "No Data Found",
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

    public function process_add_block(Request $request){
        $validator = Validator::make($request->all(),[
            'dist_id' => 'required',
            'state_id' => 'required',
            'block_name' => 'required',
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

                $sql = DB::connection('coops')->statement("Call USP_ADD_EDIT_BLOCK(?,?,?,?,?,?,@error,@message);",[null,$request->block_name,$request->state_id,$request->dist_id,auth()->user()->Id,1]);

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
                        'details' => 'Block Added Successfully !!',
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

    public function get_block_list(Request $request){
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
            $sql = DB::connection('coops')->table('mst_area_block as m')
                ->join('mst_area_state as s', 's.Id', '=', 'm.State_Id')
                ->join('mst_area_district as d', 'd.Id', '=', 'm.Dist_Id')
                ->select('m.Id', 'm.Block_Name', 's.State_Name', 's.Id AS State_Id', 'd.Dist_Name', 'd.Id AS Dist_Id')
                ->orderBy('m.Id')
                ->paginate($perPage); // Laravel's paginate method
        
            if ($sql->isEmpty()) {
                return response()->json([
                    'message' => 'No Data Found',
                    'details' => "No Data Found",
                ], 200);
            }
        
            return response()->json([
                'message' => 'Data Found',
                'data' => $sql,
            ], 200);
        
        } catch (Exception $ex) {
            $response = response()->json([
                'message' => 'Error Found',
                'details' => $ex->getMessage(),
            ], 400);
        
            throw new HttpResponseException($response);
        }
        
    }

    public function process_update_block(Request $request){
        $validator = Validator::make($request->all(),[
            'block_id' => 'required',
            'dist_id' => 'required',
            'state_id' => 'required',
            'block_name' => 'required',
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

                $sql = DB::connection('coops')->statement("Call USP_ADD_EDIT_BLOCK(?,?,?,?,?,?,@error,@message);",[$request->block_id,$request->block_name,$request->state_id,$request->dist_id,auth()->user()->Id,2]);

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
                        'details' => 'Block Updated Successfully !!',
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

    public function process_add_police(Request $request){
        $validator = Validator::make($request->all(),[
            'dist_id' => 'required',
            'state_id' => 'required',
            'station_name' => 'required',
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

                $sql = DB::connection('coops')->statement("Call USP_ADD_EDIT_POLICE(?,?,?,?,?,?,@error,@message);",[null,$request->station_name,$request->state_id,$request->dist_id,auth()->user()->Id,1]);

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
                        'details' => 'Police Station Added Successfully !!',
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

    public function get_police_list(Request $request){
        try {
            $sql = DB::select("SELECT UDF_GET_ORG_SCHEMA(?) as db;", [$request->org_id]);
            if (!$sql) {
                throw new Exception('Database schema not found.');
            }
        
            $org_schema = $sql[0]->db;
            $db = Config::get('database.connections.mysql');
            $db['database'] = $org_schema;
            config()->set('database.connections.coops', $db);
        
            // Get pagination parameters from request
            $perPage = request()->get('limit', 10); // Default limit: 10
        
            // Fetch paginated results using Laravel Query Builder
            $sql = DB::connection('coops')->table('mst_area_policestation as m')
                ->join('mst_area_state as s', 's.Id', '=', 'm.State_Id')
                ->join('mst_area_district as d', 'd.Id', '=', 'm.Dist_Id')
                ->select('m.Id', 'm.STation_Name', 's.State_Name', 's.Id AS State_Id', 'd.Dist_Name', 'd.Id AS Dist_Id')
                ->orderBy('m.Id')
                ->paginate($perPage); // Laravel handles LIMIT & OFFSET automatically
        
            if ($sql->isEmpty()) {
                return response()->json([
                    'message' => 'No Data Found',
                    'details' => "No Data Found",
                ], 200);
            }
        
            return response()->json([
                'message' => 'Data Found',
                'data' => $sql,
            ], 200);
        
        } catch (Exception $ex) {
            $response = response()->json([
                'message' => 'Error Found',
                'details' => $ex->getMessage(),
            ], 400);
        
            throw new HttpResponseException($response);
        }
        
    }

    public function process_update_police(Request $request){
        $validator = Validator::make($request->all(),[
            'police_id' => 'required',
            'dist_id' => 'required',
            'state_id' => 'required',
            'station_name' => 'required',
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

                $sql = DB::connection('coops')->statement("Call USP_ADD_EDIT_POLICE(?,?,?,?,?,?,@error,@message);",[$request->police_id,$request->station_name,$request->state_id,$request->dist_id,auth()->user()->Id,2]);

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
                        'details' => 'Police Station Updated Successfully !!',
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

    public function process_add_postoffice(Request $request){
        $validator = Validator::make($request->all(),[
            'dist_id' => 'required',
            'state_id' => 'required',
            'post_office_name' => 'required',
            'pin_code' => 'required',
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

                $sql = DB::connection('coops')->statement("Call USP_ADD_EDIT_POST_OFFICE(?,?,?,?,?,?,?,@error,@message);",[null,$request->post_office_name,$request->pin_code,$request->state_id,$request->dist_id,auth()->user()->Id,1]);

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
                        'details' => 'Post Office Added Successfully !!',
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

    public function get_post_office(Request $request){
        try {
            $sql = DB::select("SELECT UDF_GET_ORG_SCHEMA(?) as db;", [$request->org_id]);
            if (!$sql) {
                throw new Exception('Database schema not found.');
            }
        
            $org_schema = $sql[0]->db;
            $db = Config::get('database.connections.mysql');
            $db['database'] = $org_schema;
            config()->set('database.connections.coops', $db);
        
            // Get pagination parameters from request
            $perPage = request()->get('limit', 10); // Default limit: 10
        
            // Fetch paginated results using Laravel Query Builder
            $sql = DB::connection('coops')->table('mst_area_post_office as m')
                ->join('mst_area_state as s', 's.Id', '=', 'm.State_Id')
                ->join('mst_area_district as d', 'd.Id', '=', 'm.Dist_Id')
                ->select('m.Id', 'm.Post_Off_Name', 'm.Pin_Code', 's.State_Name', 's.Id AS State_Id', 'd.Dist_Name', 'd.Id AS Dist_Id')
                ->orderBy('m.Id')
                ->paginate($perPage); // Laravel's paginate method
        
            if ($sql->isEmpty()) {
                return response()->json([
                    'message' => 'No Data Found',
                    'details' => "No Data Found",
                ], 200);
            }
        
            return response()->json([
                'message' => 'Data Found',
                'data' => $sql,
            ], 200);
        
        } catch (Exception $ex) {
            $response = response()->json([
                'message' => 'Error Found',
                'details' => $ex->getMessage(),
            ], 400);
        
            throw new HttpResponseException($response);
        }
        
    }

    public function process_update_postoffice(Request $request){
        $validator = Validator::make($request->all(),[
            'post_id' => 'required',
            'dist_id' => 'required',
            'state_id' => 'required',
            'post_office_name' => 'required',
            'pin_code' => 'required',
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

                $sql = DB::connection('coops')->statement("Call USP_ADD_EDIT_POST_OFFICE(?,?,?,?,?,?,?,@error,@message);",[$request->post_id,$request->post_office_name,$request->pin_code,$request->state_id,$request->dist_id,auth()->user()->Id,2]);

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
                        'details' => 'Post Office Updated Successfully !!',
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

    public function get_distwise_block(Request $request){
        try {

            $sql = DB::select("Select UDF_GET_ORG_SCHEMA(?) as db;",[$request->org_id]);
            if(!$sql){
              throw new Exception;
            }
            $org_schema = $sql[0]->db;
            $db = Config::get('database.connections.mysql');
            $db['database'] = $org_schema;
            config()->set('database.connections.coops', $db);

            $sql = DB::connection('coops')->select("Select Id,Block_Name From mst_area_block Where State_Id=? And Dist_Id=?;",[$request->state_id,$request->dist_id]);

            if (empty($sql)) {
                // Custom validation for no data found
                return response()->json([
                    'message' => 'No Data Found',
                    'details' => "No Data Found",
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

    public function process_add_village(Request $request){
        $validator = Validator::make($request->all(),[
            'dist_id' => 'required',
            'state_id' => 'required',
            'block_id' => 'required',
            'village_name' => 'required',
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

                $sql = DB::connection('coops')->statement("Call USP_ADD_EDIT_VILLAGE(?,?,?,?,?,?,?,@error,@message);",[null,$request->village_name,$request->state_id,$request->dist_id,$request->block_id,auth()->user()->Id,1]);

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
                        'details' => 'Village Added Successfully !!',
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

    public function get_village_list(Request $request){
        try {
            $sql = DB::select("SELECT UDF_GET_ORG_SCHEMA(?) as db;", [$request->org_id]);
            if (!$sql) {
                throw new Exception('Database schema not found.');
            }
        
            $org_schema = $sql[0]->db;
            $db = Config::get('database.connections.mysql');
            $db['database'] = $org_schema;
            config()->set('database.connections.coops', $db);
        
            // Get pagination parameters from request
            $perPage = request()->get('limit', 10); // Default limit: 10
        
            // Fetch paginated results using Laravel Query Builder
            $sql = DB::connection('coops')->table('mst_area_village as m')
                ->join('mst_area_state as s', 's.Id', '=', 'm.State_Id')
                ->join('mst_area_district as d', 'd.Id', '=', 'm.Dist_Id')
                ->join('mst_area_block as b', 'b.Id', '=', 'm.Block_Id')
                ->select('m.Id', 'm.Vill_Name', 'm.State_Id', 's.State_Name', 'd.Dist_Name', 'b.Block_Name', 'm.Dist_Id', 'm.Block_Id')
                ->orderBy('m.Id')
                ->paginate($perPage); // Laravel's pagination method
        
            if ($sql->isEmpty()) {
                return response()->json([
                    'message' => 'No Data Found',
                    'details' => "No Data Found",
                ], 200);
            }
        
            return response()->json([
                'message' => 'Data Found',
                'data' => $sql,
            ], 200);
        
        } catch (Exception $ex) {
            $response = response()->json([
                'message' => 'Error Found',
                'details' => $ex->getMessage(),
            ], 400);
        
            throw new HttpResponseException($response);
        }
        
    }

    public function process_update_village(Request $request){
        $validator = Validator::make($request->all(),[
            'vill_id' => 'required',
            'dist_id' => 'required',
            'state_id' => 'required',
            'block_id' => 'required',
            'village_name' => 'required',
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

                $sql = DB::connection('coops')->statement("Call USP_ADD_EDIT_VILLAGE(?,?,?,?,?,?,?,@error,@message);",[$request->vill_id,$request->village_name,$request->state_id,$request->dist_id,$request->block_id,auth()->user()->Id,2]);

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
                        'details' => 'Village Updated Successfully !!',
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

    public function process_add_unit(Request $request){
        $validator = Validator::make($request->all(),[
            'unit_name' => 'required',
            'unit_no' => 'required',
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

                $sql = DB::connection('coops')->statement("Call USP_ADD_EDIT_UNIT(?,?,?,?,?,@error,@message);",[null,$request->unit_name,$request->unit_no,auth()->user()->Id,1]);

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
                        'details' => 'Unit Added Successfully !!',
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

    public function get_unit_list(Request $request){
        try {
            $sql = DB::select("SELECT UDF_GET_ORG_SCHEMA(?) as db;", [$request->org_id]);
            if (!$sql) {
                throw new Exception('Database schema not found.');
            }
        
            $org_schema = $sql[0]->db;
            $db = Config::get('database.connections.mysql');
            $db['database'] = $org_schema;
            config()->set('database.connections.coops', $db);
        
            // Get pagination and filter parameters from request
            $perPage = request()->get('limit', 10); // Default limit: 10
            $unitName = request()->get('keyword',''); // Filter by Unit_Name
        
            // Query Builder with optional filtering
            $query = DB::connection('coops')->table('mst_area_unit')
                ->select('Id', 'Unit_Name', 'Unit_No')
                ->orderBy('Id');
        
            // Apply filter if Unit_Name is provided
            if (!empty($unitName)) {
                $query->where('Unit_Name', 'like', "%$unitName%");
            }
        
            // Apply pagination
            $sql = $query->paginate($perPage);
        
            if ($sql->isEmpty()) {
                return response()->json([
                    'message' => 'No Data Found',
                    'details' => "No Data Found",
                ], 200);
            }
        
            return response()->json([
                'message' => 'Data Found',
                'data' => $sql,
            ], 200);
        
        } catch (Exception $ex) {
            $response = response()->json([
                'message' => 'Error Found',
                'details' => $ex->getMessage(),
            ], 400);
        
            throw new HttpResponseException($response);
        }
        
        
    }

    public function process_update_unit(Request $request){
        $validator = Validator::make($request->all(),[
            'unit_id' => 'required',
            'unit_name' => 'required',
            'unit_no' => 'required',
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

                $sql = DB::connection('coops')->statement("Call USP_ADD_EDIT_UNIT(?,?,?,?,?,@error,@message);",[$request->unit_id,$request->unit_name,$request->unit_no,auth()->user()->Id,2]);

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
                        'details' => 'Unit Updated Successfully !!',
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

    public function get_relation_type(){
        try {

            $sql = DB::select("Select Id,Option_Value From mst_org_product_parameater Where Module_Name=? And Option_Name=? And Is_Active=?;",['Member','Relation Type',1]);

            if (empty($sql)) {
                // Custom validation for no data found
                return response()->json([
                    'message' => 'No Data Found',
                    'details' => "No Data Found",
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

    public function get_gender_list(){
        try {

            $sql = DB::select("Select Id,Option_Value From mst_org_product_parameater Where Module_Name=? And Option_Name=? And Is_Active=?;",['Member','Gender',1]);

            if (empty($sql)) {
                // Custom validation for no data found
                return response()->json([
                    'message' => 'No Data Found',
                    'details' => "No Data Found",
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

    public function get_caste_list(){
        try {

            $sql = DB::select("Select Id,Option_Value From mst_org_product_parameater Where Module_Name=? And Option_Name=? And Is_Active=?;",['Member','Caste',1]);

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

    public function get_religion_list(){
        try {

            $sql = DB::select("Select Id,Option_Value From mst_org_product_parameater Where Module_Name=? And Option_Name=? And Is_Active=?;",['Member','Religion',1]);

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

    public function get_cash_denom(){
        try {

            $sql = DB::select("Select Id,Note_Lavel,Note_Value From mst_cash_denom Where Is_Active=? Order By Sl;",[1]);

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

    public function get_blkwise_village(Request $request){
        try {

            $sql = DB::select("Select UDF_GET_ORG_SCHEMA(?) as db;",[$request->org_id]);
            if(!$sql){
              throw new Exception;
            }
            $org_schema = $sql[0]->db;
            $db = Config::get('database.connections.mysql');
            $db['database'] = $org_schema;
            config()->set('database.connections.coops', $db);

            $sql = DB::connection('coops')->select("Select Id,Vill_Name From mst_area_village Where Block_Id=? And Is_Active=?;",[$request->block_id,1]);

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

    public function get_distwise_police(Request $request){
        try {

            $sql = DB::select("Select UDF_GET_ORG_SCHEMA(?) as db;",[$request->org_id]);
            if(!$sql){
              throw new Exception;
            }
            $org_schema = $sql[0]->db;
            $db = Config::get('database.connections.mysql');
            $db['database'] = $org_schema;
            config()->set('database.connections.coops', $db);

            $sql = DB::connection('coops')->select("Select Id,STation_Name From mst_area_policestation Where Dist_Id=? And Is_Active=?;",[$request->dist_id,1]);

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

    public function get_distwise_post(Request $request){
        try {

            $sql = DB::select("Select UDF_GET_ORG_SCHEMA(?) as db;",[$request->org_id]);
            if(!$sql){
              throw new Exception;
            }
            $org_schema = $sql[0]->db;
            $db = Config::get('database.connections.mysql');
            $db['database'] = $org_schema;
            config()->set('database.connections.coops', $db);

            $sql = DB::connection('coops')->select("Select Id,Post_Off_Name,Pin_Code From mst_area_post_office Where Dist_Id=? And Is_Active=?;",[$request->dist_id,1]);

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

    public function process_share_product(Request $request){
        $validator = Validator::make($request->all(),[
           'mem_type' => 'required',
           'adm_fees'=> 'required',
           'share_rate' => 'required',
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

                $sql = DB::connection('coops')->statement("Call USP_ADD_EDIT_SHARE_PRODUCT(?,?,?,?,?);",[$request->prod_id,$request->mem_type,$request->adm_fees,$request->share_rate,auth()->user()->Id]);

                if(!$sql){
                    throw new Exception;
                }

                
                    DB::connection('coops')->commit();
                    return response()->json([
                        'message' => 'Success',
                        'details' => 'Share Product Added Successfully !!',
                    ],200);
                


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

            $sql = DB::connection('coops')->select("Select Id,Adm_Fees,Share_Rate From config_share_product Where Mem_Type=? And Is_Active=?;",[$request->prod_id,1]);

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

    public function get_agent_paytype(){
        try {
           
            $sql = DB::select("Select Id,Option_Value From mst_org_product_parameater Where Module_Name=? And Option_Name=?",['Deposit','Agent Pay Out']);

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

    public function process_deposit_agent(Request $request){
        $validator = Validator::make($request->all(),[
            'agent_name' => 'required',
            'address'=> 'required',
            'mobile' => 'required',
            'email' => 'required',
            'deposit_amt' => 'required',
            'max_days' => 'required',
            'max_amt' => 'required',
            'paid_mode' => 'required',
            'paid_amt' => 'required',
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
 
                 $sql = DB::connection('coops')->statement("Call USP_ADD_DEPOSIT_AGENT(?,?,?,?,?,?,?,?,?,?,@error,@message);",[$request->agent_name,$request->address,$request->mobile,$request->email,$request->deposit_amt,$request->max_days,$request->max_amt,$request->paid_mode,$request->paid_amt,auth()->user()->Id]);
 
                 if(!$sql){
                     throw new Exception;
                 }
                 $result = DB::connection('coops')->select("Select @error As Error_No,@message As Message");
                 $error_no = $result[0]->Error_No;
                 $message = $result[0]->Message;

                 if($error_no<0){
                    DB::connection('coops')->rollBack();
                    return response()->json([
                        'message' => 'Error',
                        'details' => $message,
                    ],200);
                 }
                 else{
                    DB::connection('coops')->commit();
                    return response()->json([
                        'message' => 'Success',
                        'details' => 'Agent Successfully Added !!',
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

    public function get_deposit_agent(Request $request){
        try {

            $sql = DB::select("Select UDF_GET_ORG_SCHEMA(?) as db;",[$request->org_id]);
            if(!$sql){
              throw new Exception;
            }
            $org_schema = $sql[0]->db;
            $db = Config::get('database.connections.mysql');
            $db['database'] = $org_schema;
            config()->set('database.connections.coops', $db);

            $sql = DB::connection('coops')->select("Select Id,Agent_Name From mst_agent_master;");

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

    public function deposit_Intt_Slab(Request $request){
        $validator = Validator::make($request->all(),[
            'prod_id' => 'required',
            'slab_data' => 'required',
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
                 $drop_table = DB::connection('coops')->statement("Drop Temporary Table If Exists temp_intt_data;");
                 $create_table = DB::connection('coops')->statement("Create Temporary Table temp_intt_data
                                                (
                                                    Min_Dur			Int,
                                                    Max_Dur			Int,
                                                    Dur_Unit		Int,
                                                    Roi				Numeric(18,2),
                                                    Effec_Frm		Date,
                                                    Effec_To		Date
                                                );");
                if(is_array($request->slab_data)){
                    $slab_data = $this->convertToObject($request->slab_data);

                    foreach ($slab_data as $slab_details) {
                        $meter_insert =  DB::connection('coops')->statement("Insert Into temp_intt_data (Min_Dur,Max_Dur,Dur_Unit,Roi,Effec_Frm,Effec_To) Values (?,?,?,?,?,?);",[$slab_details->min_dur,$slab_details->max_dur,$slab_details->dur_unit,$slab_details->roi,$slab_details->effect_frm,$slab_details->eff_to]);
                    }
                }
                 $sql = DB::connection('coops')->statement("Call USP_ADD_DEPOSIT_INTT_SLAB(?,?,@error,@message);",[$request->prod_id,auth()->user()->Id]);
 
                 if(!$sql){
                     throw new Exception;
                 }
                 $result = DB::connection('coops')->select("Select @error As Error_No,@message As Message");
                 $error_no = $result[0]->Error_No;
                 $message = $result[0]->Message;

                 if($error_no<0){
                    DB::connection('coops')->rollBack();
                    return response()->json([
                        'message' => 'Error',
                        'details' => $message,
                    ],200);
                 }
                 else{
                    DB::connection('coops')->commit();
                    return response()->json([
                        'message' => 'Success',
                        'details' => 'Interest Slabl Added Successfully !!',
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

    public function get_active_module(Request $request){
        try {

            $sql = DB::select("Select m.Module_Id As Id,md.Module_Name From map_orgwise_module m Join mst_org_module md On md.Id=m.Module_Id Where m.Org_Id=? And m.Module_Id In(1,2,3);",[$request->org_id]);

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

    public function get_passbook_config(Request $request){
        try {

            $sql = DB::select("Select UDF_GET_ORG_SCHEMA(?) as db;",[$request->org_id]);
            if(!$sql){
              throw new Exception;
            }
            $org_schema = $sql[0]->db;
            $db = Config::get('database.connections.mysql');
            $db['database'] = $org_schema;
            config()->set('database.connections.coops', $db);

            $sql = DB::connection('coops')->select("Select Id,Module_Id,Page_Height,Page_Width,Fst_Page_Top,Line_No_Fst_Page,Line_No_Scnd_Page,Mid_Gap,Next_Page_Gap From mst_config_passbook Where Module_Id=?",[$request->module_id]);

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

    public function process_config_passbook(Request $request){
        $validator = Validator::make($request->all(),[
            'org_id' => 'required',
            'module_id' => 'required',
            'page_height' => 'required',
            'page_weidth' => 'required',
            'fst_top' => 'required',
            'fst_page_line' => 'required',
            'last_page_line' => 'required',
            'mid_gap' => 'required',
            'next_page_gap' => 'required'
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

                 $sql = DB::connection('coops')->statement("Call USP_CONFIG_PASSBOOK(?,?,?,?,?,?,?,?,?,?);",[$request->conf_id,$request->module_id,$request->page_height,$request->page_weidth,$request->fst_top,$request->fst_page_line,$request->last_page_line,$request->mid_gap,$request->next_page_gap,auth()->user()->Id]);
 
                 if(!$sql){
                     throw new Exception;
                 }
                
                    DB::connection('coops')->commit();
                    return response()->json([
                        'message' => 'Success',
                        'details' => 'Passbook Configured Successfully !!',
                    ],200);
 
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

}