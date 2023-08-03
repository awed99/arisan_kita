<?php

namespace App\Controllers\Biller;

class Dashboard extends BaseController
{
    public function index()
    {
		echo 'Access denied!'; 
    }

    public function postGet_data()
    {
        $id_user = cek_session_login();
        $db = db_connect();

        $dataMonthly = $db->query('SELECT 
            COALESCE((SELECT (SUM(ammount)-SUM(fee)-SUM(merchant_commission)) from transactions where status_payment = 2 and YEAR(time_transaction) = YEAR(NOW()) AND MONTH(time_transaction) = MONTH(NOW())), 0) as sales,
            COALESCE((SELECT (SUM(ammount)-SUM(fee)-SUM(merchant_commission)) from transactions where status_payment <> \'2\' and YEAR(time_transaction) = YEAR(NOW()) AND MONTH(time_transaction) = MONTH(NOW())), 0) as unpaid_sales,
            COALESCE((SELECT (SUM(biller_commission)) from transactions where status_payment = 2 and YEAR(time_transaction) = YEAR(NOW()) AND MONTH(time_transaction) = MONTH(NOW())), 0) as revenue,
            COALESCE((SELECT (COUNT(*)) from transactions where status_payment = 2 and YEAR(time_transaction) = YEAR(NOW()) AND MONTH(time_transaction) = MONTH(NOW())), 0) as transactions
        ;')->getRow();

        $dataSummary = $db->query('SELECT 
            COALESCE((SELECT (SUM(ammount)-SUM(fee)-SUM(merchant_commission)) from transactions where status_payment = 2), 0) as sales,
            COALESCE((SELECT (SUM(ammount)-SUM(fee)-SUM(merchant_commission)) from transactions where status_payment <> \'2\'), 0) as unpaid_sales,
            COALESCE((SELECT (SUM(biller_commission)) from transactions where status_payment = 2), 0) as revenue,
            COALESCE((SELECT (COUNT(*)) from transactions where status_payment = 2), 0) as transactions
        ;')->getRow();

        $dataTopup = $db->table('topup_history')->select('SUM(topup_final_amount) as amount')->get()->getRow()->amount;
        $dataBalance = $db->table('saldo_user')->get()->getRow()->saldo;
        $dataXendit = xendit_get_balance();
        $dataDuitku = 0;
        
        $userApproved = $db->table('users')->select('count(*) as total')->where('user_role', '1')->where('user_status', '1')->get()->getRow()->total;
        $userPending = $db->table('users')->select('count(*) as total')->where('user_role', '1')->where('user_status', '0')->get()->getRow()->total;
        $userUnderVerif = $db->table('users')->select('count(*) as total')->where('user_role', '1')->where('user_status', '4')->get()->getRow()->total;
        $userBanned = $db->table('users')->select('count(*) as total')->where('user_role', '1')->where('user_status', '3')->get()->getRow()->total;

		$res["status"] 	= "000";
		$res["error"] 	= "";
		$res["message"] = "";
		$res["data"]['monthly'] = $dataMonthly;
		$res["data"]['summary'] = $dataSummary;
		$res["data"]['total_topup'] = $dataTopup;
		$res["data"]['balance_app'] = $dataBalance;
		$res["data"]['balance_xendit'] = $dataXendit;
		$res["data"]['balance_duitku'] = $dataDuitku;
		$res["data"]['user_approved'] = $userApproved;
		$res["data"]['user_pending'] = $userPending;
		$res["data"]['user_under_verif'] = $userUnderVerif;
		$res["data"]['user_banned'] = $userBanned;
        $db->close();

		echo json_encode($res);
    }
}
