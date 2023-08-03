<?php

namespace App\Controllers;

class Finance extends BaseController
{
    public function index()
    {
		echo 'Access denied!'; 
    }

    public function postGet_user_saldo()
    {
        $id_user = cek_session_login();
        $db = db_connect();

        $data = $db->table('saldo_user')->where('id_user', $id_user)->get()->getRow();
        $notif = $db->table('app_notifications')->where('id_user', $id_user)->orWhere('id_user', -1)->get()->getResult();

		$res["status"] 	= "000";
		$res["error"] 	= "";
		$res["message"] = "";
		$res["data"] = $data;
		$res["credit"] = $data;
		$res["notif"] = $notif;
        $db->close();

		echo json_encode($res);
    }

    public function postPricing()
    {
        $id_user = cek_session_login();
        $db = db_connect();

        $data = $db->table('merchant_pricing')->get()->getResult();

		$res["status"] 	= "000";
		$res["error"] 	= "";
		$res["message"] = "";
		$res["data"] = $data;
        $db->close();

		echo json_encode($res);
    }

    public function postGet_active_topup()
    {
        $id_user = cek_session_login();
        $request = request();
        $dataPost = $request->getPost();
        $db = db_connect();

        $data = $db->table('history_va_xendit')
        ->where('id_user', $id_user)
        ->where('type_transaction ', 'TOPUP')
        ->where('status', 'PENDING')
        ->where('expiration_date > NOW()')
        ->get()->getRow();

        $banks = $db->table('bank_names')
        ->orderBy('label', 'asc')
        ->where('is_active ', '1')
        ->get()->getResult();
        $db->close();

		$res['va'] = $data;
		$res['banks'] = $banks;

		return ($res);
    }

    public function postTopup()
    {
        $id_user = cek_session_login();
        $request = request();
        $dataPost = $request->getPost();
        $db = db_connect();

        $expDate = str_replace(' ', 'T', date('Y-m-d H:i:s',strtotime('+1 hours'))).'.000Z'; 
        $sourceXenditVA['external_id'] = 'TOPUP-'.$id_user.'-'.date('YmdHis').'-'.create_random_captcha();
        $sourceXenditVA['name'] = 'TOPUP '.$db->table('profiles')->where('id_user', $id_user)->get()->getRow()->bussiness_name;
        $sourceXenditVA['expected_amount'] = $dataPost['amount'];
        // $sourceXenditVA['suggested_amount'] = $dataPost['amount'];
        $sourceXenditVA['bank_code'] = $dataPost['bank'];
        $sourceXenditVA['is_closed'] = true;
        $sourceXenditVA['is_single_use'] = true;
        $sourceXenditVA['expiration_date'] = $expDate;
        $dataXendit = create_va(json_encode($sourceXenditVA), false, true); 

        $data = $db->table('topup_history')->where('topup_id_user', $id_user)->orderBy('topup_transaction_updated', 'desc')->get()->getResult();
        $db->close();
        
        $this->postHistory_topup();
    }

    public function postTopup_from_credit()
    {
        $id_user = cek_session_login();
        $request = request();
        $dataPost = $request->getPost();
        $db = db_connect();

        $external_id_credit = $db->table('users')->where('id_user', $id_user)->get()->getRow()->external_id_credit;
        $insert['id'] = '-';
        $insert['external_id'] = $external_id_credit;
        $insert['merchant_code'] = '-';
        $insert['amount'] = $dataPost['amount'];
        $insert['bank_code'] = '-';
        $insert['account_number'] = '';
        $insert['owner_id'] = '-';
        $insert['callback_virtual_account_id'] = '-';
        $insert['payment_id'] = '-';
        $db->table('pg_va_callback')->ignore()->insert($insert);

        $data = $db->table('topup_history')->where('topup_id_user', $id_user)->orderBy('topup_transaction_updated', 'desc')->get()->getResult();

		$res["status"] 	= "000";
		$res["error"] 	= "";
		$res["message"] = "Topup from Credit Successfully Added.";
		$res["data"] = $data;
        $db->close();

		echo json_encode($res);
    }

    public function postHistory_topup()
    {
        $id_user = cek_session_login();
        $request = request();
        $dataPost = $request->getPost();
        $db = db_connect();

        $update['status'] = 'EXPIRED';
        $db->table('history_va_xendit')->where('expiration_date IS NOT NULL and NOW() > expiration_date')->where('status', 'PENDING')->ignore()->update($update);

        $union = $db->table('topup_history')
        ->select("*, 'PAID' as topup_status")
        ->where('topup_id_user', $id_user)
        ->where('topup_transaction_updated BETWEEN \''.$dataPost['start_date'].'\' AND \''.$dataPost['end_date'].'\'')
        ->orderBy('topup_transaction_updated', 'desc');
        
        $builder = $db->table('history_va_xendit')
        ->select("history_va_xendit.id,
        history_va_xendit.external_id,
        history_va_xendit.id_user as topup_id_user,
        users.email as topup_email,
        profiles.owner_name as topup_owner_name,
        profiles.bussiness_name as topup_bussiness_name,
        history_va_xendit.expected_amount as topup_amount,
        history_va_xendit.fee as topup_fee,
        (history_va_xendit.expected_amount - history_va_xendit.fee) as topup_final_amount,
        history_va_xendit.bank_code as topup_va_bank,
        history_va_xendit.bank_code as topup_bank_transfer,
        '' as topup_bank_merchant_code,
        history_va_xendit.account_number as topup_bank_account_number,
        '-' as topup_payment_id,
        history_va_xendit.created_date as topup_transaction_timestamp,
        history_va_xendit.created_date as topup_transaction_created,
        history_va_xendit.created_date as topup_transaction_updated,
        history_va_xendit.status as topup_status")
        ->join("users", "users.id_user = history_va_xendit.id_user", "left")
        ->join("profiles", "profiles.id_user = users.id_user", "left")
        ->where('history_va_xendit.id_user', $id_user)
        ->where("history_va_xendit.status <> 'PAID'")
        ->where('history_va_xendit.created_date BETWEEN \''.$dataPost['start_date'].'\' AND \''.$dataPost['end_date'].'\'')
        ->orderBy('history_va_xendit.created_date', 'desc');
        
        // echo $db->getLastQuery();
        // die();

        $data = $union->union($builder)
        // ->where('NOT EXISTS (SELECT 1 FROM history_va_xendit hvax WHERE external_id = hvax.external_id)')
        // ->select('*')
        ->groupBy('external_id')
        ->ignore()->get()->getResult();

        // echo $db->getLastQuery();
        // die();
        
        $va = $this->postGet_active_topup();
        
        $db->close();

		$res["status"] 	= "000";
		$res["error"] 	= "";
		$res["message"] = "";
		$res["data"] = $data;
		$res["topup"] = $va;

		echo json_encode($res);
    }

    public function postHistory_withdraw()
    {
        $id_user = cek_session_login();
        $db = db_connect();

        $data = $db->table('withdraw_history')->where('id_user', $id_user)->orderBy('id', 'desc')->get()->getResult();

		$res["status"] 	= "000";
		$res["error"] 	= "";
		$res["message"] = "";
		$res["data"] = $data;
        $db->close();

		echo json_encode($res);
    }

    public function postWithdraw()
    {
        $request = request();
        $dataPost = $request->getPost();
        $id_user = cek_session_login();
        $db = db_connect();

        $sourceXenditDisburst = '{
            "external_id": "Disbursement-'.$id_user.'-'.date('Y-m-d H:i:s').'",
            "amount": '.$dataPost['amount'].',
            "bank_code": "'.$dataPost['user_bank_name'].'",
            "account_holder_name": "'.$dataPost['user_bank_account_name'].'",
            "account_number": "'.$dataPost['user_bank_account_number'].'",
            "description": "Withdraw Merchant '.$dataPost['user_bank_account_name'].'"
         }';

        $dataXendit = create_disbursement($sourceXenditDisburst);
        $dataXendit['unique_id'] = $dataXendit['id'];
        $dataXendit['id_user'] = $id_user;
        $dataXendit['fee'] = $dataXendit['fee'];
        unset($dataXendit['user_id']);
        unset($dataXendit['id']);
        $db->table('withdraw_merchant')->ignore()->insert($dataXendit);

        $data = $db->table('withdraw_history')->where('id_user', $id_user)->orderBy('id', 'desc')->get()->getResult();

		$res["status"] 	= "000";
		$res["error"] 	= "";
		$res["message"] = "Withdraw on process.";
		$res["data"] = $data;
        $db->close();

		echo json_encode($res);
    }
}
