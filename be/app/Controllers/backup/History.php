<?php

namespace App\Controllers;

class History extends BaseController
{
    public function index()
    {
		echo 'Access denied!'; 
    }

    public function postGet_history_transactions()
    {
        $id_user = cek_session_login();
        $request = request();
        $dataPost = $request->getPost();
        $db = db_connect();

        $data = $db->query('SELECT 
        DISTINCT(t.id_transaction) id_trx,
        COALESCE(pg.amount, pg2.amount) amount, 
        COALESCE(pg.fee, pg2.fee) fee,
        COALESCE((pg.amount - pg.fee), (pg2.amount - pg2.fee)) as final_amount,
        t.*,
        mp.product_name, mp.product_type, mp.brand, mp.description,
        sp.name_status_payment as status_payment
        from transactions t
        left join merchant_products mp on t.id_product = mp.id_product
        left join pg_va_callback pg on t.external_id = pg.external_id
        left join pg_retail_callback pg2 on t.external_id = pg2.external_id
        left join users u on u.id_user = t.id_user
        left join status_payment sp on sp.id_status_payment = t.status_payment
        where u.id_user = \''.$id_user.'\'
        AND (time_transaction BETWEEN \''.$dataPost['start_date'].'\' AND \''.$dataPost['end_date'].'\')
        order by t.id_transaction desc;')->getResult();

		$res["status"] 	= "000";
		$res["error"] 	= "";
		$res["message"] = "";
		$res["data"] = $data;
        $db->close();

		echo json_encode($res);
    }

    public function postGet_history_cash_flow()
    {
        $id_user = cek_session_login();
        $db = db_connect();

        $data = $db->table('transactions t')->select('t.*, mp.product_name, mp.product_name, mp.product_type, mp.brand, mp.description')
        ->join('merchant_products mp', 'mp.id_product = t.id_product', 'left')
        ->where('t.id_user', $id_user)->orderBy('t.id_transaction ', 'desc')->get()->getResult();

		$res["status"] 	= "000";
		$res["error"] 	= "";
		$res["message"] = "";
		$res["data"] = $data;
        $db->close();

		echo json_encode($res);
    }

    public function postGet_history_margin()
    {
        $id_user = cek_session_login();
        $request = request();
        $dataPost = $request->getPost();
        $db = db_connect();

        $data = $db->query('SELECT 
        DISTINCT(t.id_transaction) id_trx,
        COALESCE(pg.amount, pg2.amount) amount, 
        COALESCE(pg.fee, pg2.fee) fee,
        COALESCE((pg.amount - pg.fee), (pg2.amount - pg2.fee)) as final_amount,
        t.*,
        mp.product_name, mp.product_type, mp.brand, mp.description,
        sp.name_status_payment as status_payment
        from transactions t
        left join merchant_products mp on t.id_product = mp.id_product
        left join pg_va_callback pg on t.external_id = pg.external_id
        left join pg_retail_callback pg2 on t.external_id = pg2.external_id
        left join users u on u.id_user = t.id_user
        left join status_payment sp on sp.id_status_payment = t.status_payment
        where u.id_user = \''.$id_user.'\' and sp.id_status_payment = 2
        AND (time_transaction BETWEEN \''.$dataPost['start_date'].'\' AND \''.$dataPost['end_date'].'\')
        order by t.id_transaction desc;')->getResult();

		$res["status"] 	= "000";
		$res["error"] 	= "";
		$res["message"] = "";
		$res["data"] = $data;
        $db->close();

		echo json_encode($res);
    }

    public function postHistory_journal()
    {
        $id_user = cek_session_login();
        $request = request();
        $dataPost = $request->getPost();
        $db = db_connect();

        $data = $db->table('journal_history')
        ->where('id_user', $id_user)
        ->where('trx_date BETWEEN \''.$dataPost['start_date'].'\' AND \''.$dataPost['end_date'].'\'')
        ->orderBy('trx_date', 'desc')->orderBy('description', 'asc')->get()->getResult();

		$res["status"] 	= "000";
		$res["error"] 	= "";
		$res["message"] = "";
		$res["data"] = $data;
        $db->close();

		echo json_encode($res);
    }
}
