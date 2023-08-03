<?php

namespace App\Controllers\Biller;

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

        $data = $db->query('SELECT 
        sum(t.biller_commission) as saldo
        from transactions t
        left join status_payment sp on sp.id_status_payment = t.status_payment
        where sp.id_status_payment = 2
        order by t.id_transaction desc;')->getRow();
        $notif = $db->table('app_notifications')->where('id_user', 0)->get()->getResult();
        $db->close();

		$res["status"] 	= "000";
		$res["error"] 	= "";
		$res["message"] = "";
		$res["data"] = $data;
		$res["notif"] = $notif;
		echo json_encode($res);
    }

    public function postSet_pricing()
    {
        $id_user = cek_session_login();
        $request = request();
        $dataPost = $request->getJSON(true);
        $db = db_connect();

        $data = $db->table('merchant_pricing')->ignore()->upsert($dataPost);
        $data = $db->table('merchant_pricing')->get()->getResult();

		$res["status"] 	= "000";
		$res["error"] 	= "";
		$res["message"] = "";
		$res["data"] = $data;
        $db->close();

		echo json_encode($res);
    }

    public function postMy_history_withdraw()
    {
        $id_user = cek_session_login();
        $db = db_connect();

        $data = $db->table('withdraw_biller')->orderBy('id', 'desc')->get()->getResult();

		$res["status"] 	= "000";
		$res["error"] 	= "";
		$res["message"] = "";
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
        ->where('topup_transaction_updated BETWEEN \''.$dataPost['start_date'].'\' AND \''.$dataPost['end_date'].'\'')
        ->orderBy('topup_transaction_updated', 'DESC');
        
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
        ->where("history_va_xendit.status <> 'PAID'")
        ->where('history_va_xendit.created_date BETWEEN \''.$dataPost['start_date'].'\' AND \''.$dataPost['end_date'].'\'')
        ->orderBy('history_va_xendit.created_date', 'DESC');
        
        // echo $db->getLastQuery();
        // die();

        $data = $union->union($builder)
        ->groupBy('external_id')
        ->orderBy('topup_transaction_updated', 'DESC')
        ->ignore()->get()->getResult();

		$res["status"] 	= "000";
		$res["error"] 	= "";
		$res["message"] = "";
		$res["data"] = $data;
        $db->close();

		echo json_encode($res);
    }

    public function postHistory_withdraw()
    {
        $id_user = cek_session_login();
        $db = db_connect();

        $data = $db->table('withdraw_history')->orderBy('id', 'desc')->get()->getResult();

		$res["status"] 	= "000";
		$res["error"] 	= "";
		$res["message"] = "";
		$res["data"] = $data;
        $db->close();

		echo json_encode($res);
    }

    public function postMy_withdraw()
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
        
        $fee = $db->table('merchant_pricing')->where('pricing_name ', 'withdraw')->get()->getRow()->pricing_amount;

        $dataXendit = create_disbursement($sourceXenditDisburst, true);
        $dataXendit['unique_id'] = $dataXendit['id'];
        $dataXendit['id_user'] = $id_user;
        $dataXendit['fee'] = $fee;
        unset($dataXendit['user_id']);
        unset($dataXendit['id']);
        $db->table('withdraw_biller')->ignore()->insert($dataXendit);

        $data = $db->table('withdraw_biller')->orderBy('id', 'desc')->get()->getResult();

		$res["status"] 	= "000";
		$res["error"] 	= "";
		$res["message"] = "Withdraw on process.";
		$res["data"] = $data;
        $db->close();

		echo json_encode($res);
    }
}
