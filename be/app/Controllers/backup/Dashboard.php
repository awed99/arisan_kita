<?php

namespace App\Controllers;

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
            COALESCE((SELECT (SUM(ammount)-SUM(fee)) from transactions where status_payment = 2 and id_user = '.$id_user.' and YEAR(time_transaction) = YEAR(NOW()) AND MONTH(time_transaction) = MONTH(NOW())), 0) as sales,
            COALESCE((SELECT (SUM(ammount)-SUM(fee)) from transactions where status_payment <> \'2\' and id_user = '.$id_user.' and YEAR(time_transaction) = YEAR(NOW()) AND MONTH(time_transaction) = MONTH(NOW())), 0) as unpaid_sales,
            COALESCE((SELECT (SUM(merchant_commission)) from transactions where status_payment = 2 and id_user = '.$id_user.' and YEAR(time_transaction) = YEAR(NOW()) AND MONTH(time_transaction) = MONTH(NOW())), 0) as revenue,
            COALESCE((SELECT (COUNT(*)) from transactions where status_payment = 2 and id_user = '.$id_user.' and YEAR(time_transaction) = YEAR(NOW()) AND MONTH(time_transaction) = MONTH(NOW())), 0) as transactions
        ;')->getRow();

        $dataSummary = $db->query('SELECT 
            COALESCE((SELECT (SUM(ammount)-SUM(fee)) from transactions where status_payment = 2 and id_user = '.$id_user.'), 0) as sales,
            COALESCE((SELECT (SUM(ammount)-SUM(fee)) from transactions where status_payment <> \'2\' and id_user = '.$id_user.'), 0) as unpaid_sales,
            COALESCE((SELECT (SUM(merchant_commission)) from transactions where status_payment = 2 and id_user = '.$id_user.'), 0) as revenue,
            COALESCE((SELECT (COUNT(*)) from transactions where status_payment = 2 and id_user = '.$id_user.'), 0) as transactions
        ;')->getRow();

        
        // $data = $db->table('pg_list_setting')->where('id_user', $id_user)->where('id_pg', '1')->get()->getRow()->api_key;

        $dataTopup = $db->table('topup_history')->select('SUM(topup_final_amount) as amount')->where('topup_id_user', $id_user)->get()->getRow()->amount;
        $dataBalance = $db->table('saldo_user')->where('id_user', $id_user)->get()->getRow()->saldo;
        $dataXendit = xendit_get_balance();
        $dataDuitku = 0;

        $web_config = $db->table('web_config')->select('count(*) as total')->where('id_user', $id_user)->where('web_name IS NOT NULL')->where('web_title IS NOT NULL')->where('web_icon IS NOT NULL')->get()->getRow()->total;
        $pg = $db->table('pg_list_setting')->select('count(*) as total')->where('id_user', $id_user)->where('is_used', 1)->get()->getRow()->total;
        $products = $db->table('merchant_products')->select('count(*) as total')->where('id_user', $id_user)->where('(merchant_commission_amount > 0 OR merchant_commission_percent > 0)')->get()->getRow()->total;

		$res["status"] 	= "000";
		$res["error"] 	= "";
		$res["message"] = "";
		$res["data"]['monthly'] = $dataMonthly;
		$res["data"]['summary'] = $dataSummary;
		$res["data"]['total_topup'] = $dataTopup;
		$res["data"]['balance_app'] = $dataBalance;
		$res["data"]['balance_xendit'] = $dataXendit;
		$res["data"]['balance_duitku'] = $dataDuitku;
		$res["data"]['setup']['web_config'] = $web_config;
		$res["data"]['setup']['pg'] = $pg;
		$res["data"]['setup']['products'] = $products;
        $db->close();

		echo json_encode($res);
    }
}
