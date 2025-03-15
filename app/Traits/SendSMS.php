<?php

namespace App\Traits;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

trait SendSMS
{
    public function check_sms_config($org_id){
        try {
            $sql = DB::select("Select Count(*) As SMS From mst_org_config Where Is_SMS=1 And Org_Id=?",[$org_id]);

            if(!$sql){
                return false;
            }
            $sms = $sql[0]->SMS;
            if($sms<>0){
                return true;
            }
            else{
                return false;
            }

        } catch (Exception $ex) {
           return $ex->getMessage();
        }
    }

    public function check_balance($org_id){
        try {
            $sms_qur = DB::select("Select SMS_Qnty From mst_org_sms_refill Where Org_Id=? And Valid_Till>=?",[$org_id,Carbon::now()->toDateString()]);

            if(!$sms_qur){
                return false;
            }
            $sql = DB::select("Select UDF_GET_ORG_SCHEMA(?) as db;",[$org_id]);
            if(!$sql){
                return false;
            }
            $org_schema = $sql[0]->db;
            $db = Config::get('database.connections.mysql');
            $db['database'] = $org_schema;
            config()->set('database.connections.coops', $db);
            $send_data =DB::connection('coops')->select("Select Count(*) As Send From mst_sms_log;");
            $already_send = $send_data[0]->Send;
            $sms = $sms_qur[0]->SMS_Qnty;
            if($sms>=$already_send){
                return true;
            }
            else{
                return false;
            }

        } catch (Exception $ex) {
           return $ex->getMessage();
        }
    }

    public function push_sms_log($org_id,$message,$phone){
        try {
            
            $sql = DB::select("Select UDF_GET_ORG_SCHEMA(?) as db;",[$org_id]);
            if(!$sql){
                return false;
            }
            $org_schema = $sql[0]->db;
            $db = Config::get('database.connections.mysql');
            $db['database'] = $org_schema;
            config()->set('database.connections.coops', $db);
            DB::connection('coops')->beginTransaction();
            DB::connection('coops')->statement("Insert Into mst_sms_log (Sms_Type,Message_Body,Mobile_No,Send_Date,Created_By) Values (?,?,?,?,?);",['Welcome Member',$message,$phone,Carbon::now()->toDateString(),auth()->user()->Id]);
            DB::connection('coops')->commit();
        } catch (Exception $ex) {
           return $ex->getMessage();
        }
    }

    public function send_welcome_member($org_id,$member_name,$member_code,$phone){
        try {
            if($this->check_sms_config($org_id)===true){
                if($this->check_balance($org_id)===true){
                    $apiUrl = env('SMS_URL');
                    $message = "Dear ".$member_name.", Thank you for joining Samabay Samity. Your Customer Code is ".$member_code.". Kindly note this for future reference. Regards SAMABAY";
                    $params = [
                        'username'   => env('USER_NAME'),
                        'message'    => $message,
                        'sendername' => env('SENDER_NAME'),
                        'smstype'    => env('SMS_TYPE'),
                        'numbers'    => $phone,
                        'apikey'     => env('SMS_APIKEY'),
                    ];
                    $this->push_sms_log($org_id,$message,$phone);
                    return Http::get($apiUrl, $params)->json();
                }
            }
        } catch (Exception $ex) {
            return $ex->getMessage();
        }
    }

    public function send_deposit_account_open($org_id,$member_name,$acct_no,$phone,$open_date){
        try {
            if($this->check_sms_config($org_id)===true){
                if($this->check_balance($org_id)===true){
                    $apiUrl = env('SMS_URL');
                    $message = "Dear ".$member_name.", You have opened a Savings account on ".Carbon::parse($open_date)->format('d-m-Y').". Account Number is ".$acct_no.". Please keep this number while transacting with us. Regards SAMABAY";
                    $params = [
                        'username'   => env('USER_NAME'),
                        'message'    => $message,
                        'sendername' => env('SENDER_NAME'),
                        'smstype'    => env('SMS_TYPE'),
                        'numbers'    => $phone,
                        'apikey'     => env('SMS_APIKEY'),
                    ];
                    $this->push_sms_log($org_id,$message,$phone);
                    return Http::get($apiUrl, $params)->json();
                }
            }
        } catch (Exception $ex) {
            return $ex->getMessage();
        }
    }

    public function send_deposit_credit($org_id,$acct_no,$phone,$trans_date,$amount,$balance){
        try {
            if($this->check_sms_config($org_id)===true){
                if($this->check_balance($org_id)===true){
                    $apiUrl = env('SMS_URL');
                    $message = "Your SB A/c XX".substr($acct_no,-4)." is credited INR ".$amount." On ".Carbon::parse($trans_date)->format('d-m-Y').". Avl Bal-".$balance.". Regards SAMABAY";
                    $params = [
                        'username'   => env('USER_NAME'),
                        'message'    => $message,
                        'sendername' => env('SENDER_NAME'),
                        'smstype'    => env('SMS_TYPE'),
                        'numbers'    => $phone,
                        'apikey'     => env('SMS_APIKEY'),
                    ];
                    $this->push_sms_log($org_id,$message,$phone);
                    return Http::get($apiUrl, $params)->json();
                }
            }
        } catch (Exception $ex) {
            return $ex->getMessage();
        }
    }

    public function send_deposit_account_debit($org_id,$acct_no,$phone,$trans_date,$amount,$balance){
        try {
            if($this->check_sms_config($org_id)===true){
                if($this->check_balance($org_id)===true){
                    $apiUrl = env('SMS_URL');
                    $message = "Your SB A/c XX".substr($acct_no,-4)." is debited INR ".$amount." only On ".Carbon::parse($trans_date)->format('d-m-Y').". Avl Bal-".$balance.". Regards SAMABAY";
                    $params = [
                        'username'   => env('USER_NAME'),
                        'message'    => $message,
                        'sendername' => env('SENDER_NAME'),
                        'smstype'    => env('SMS_TYPE'),
                        'numbers'    => $phone,
                        'apikey'     => env('SMS_APIKEY'),
                    ];
                    $this->push_sms_log($org_id,$message,$phone);
                    return Http::get($apiUrl, $params)->json();
                }
            }
        } catch (Exception $ex) {
            return $ex->getMessage();
        }
    }

    public function send_on_loan_disburse($org_id,$acct_no,$amount,$disb_date,$phone){
        try {
            if($this->check_sms_config($org_id)===true){
                if($this->check_balance($org_id)===true){
                    $apiUrl = env('SMS_URL');
                    $message = "Your Loan A/c ".$acct_no." of INR-".$amount." have been disbursed on ".Carbon::parse($disb_date)->format('d-m-Y').". Regards SAMABAY";
                    $params = [
                        'username'   => env('USER_NAME'),
                        'message'    => $message,
                        'sendername' => env('SENDER_NAME'),
                        'smstype'    => env('SMS_TYPE'),
                        'numbers'    => $phone,
                        'apikey'     => env('SMS_APIKEY'),
                    ];
                    $this->push_sms_log($org_id,$message,$phone);
                    return Http::get($apiUrl, $params)->json();
                }
            }
        } catch (Exception $ex) {
            return $ex->getMessage();
        }
    }

    public function send_on_loan_repayment($org_id,$acct_no,$prn_amt,$intt_amt,$repay_date,$outs_bal,$phone){
        try {
            if($this->check_sms_config($org_id)===true){
                if($this->check_balance($org_id)===true){
                    $apiUrl = env('SMS_URL');
                    $message = "Repayment done on your Loan A/c ".$acct_no." of INR-".number_format(($prn_amt+$intt_amt),2,'.','')." (Prn.".$prn_amt."+ Int.".$intt_amt.") on ".Carbon::parse($repay_date)->format('d-m-Y').". Cur Outs-".$outs_bal.". Regards SAMABAY";
                    $params = [
                        'username'   => env('USER_NAME'),
                        'message'    => $message,
                        'sendername' => env('SENDER_NAME'),
                        'smstype'    => env('SMS_TYPE'),
                        'numbers'    => $phone,
                        'apikey'     => env('SMS_APIKEY'),
                    ];
                    $this->push_sms_log($org_id,$message,$phone);
                    return Http::get($apiUrl, $params)->json();
                }
            }
        } catch (Exception $ex) {
            return $ex->getMessage();
        }
    }
}