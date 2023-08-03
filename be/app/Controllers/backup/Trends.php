<?php

namespace App\Controllers;

class Trends extends BaseController
{
    public function index()
    {
		echo 'Access denied!'; 
    }

	public function postView_all_products() {
        $id_user = cek_session_login();
        $request = request();
        $dataPost = $request->getPost();
        $db = db_connect();

        $data = $db->table('merchant_products p')->select('p.*, c.category')
                ->join('categories c', 'c.id=p.id_category', 'left')
                ->where('p.id_user', $id_user)
                ->where('p.is_active', 1)
                ->orderBy('p.product_name', 'asc')->get()->getResult();

		$res["status"] 	= "000";
		$res["error"] 	= "";
		$res["message"] = $data;
        $db->close();

		echo json_encode($res);
	}

	public function postView() {
        $id_user = cek_session_login();
        $db = db_connect();

        $data = $db->table('merchant_trend_products')->where('id_user', $id_user)->get()->getResult();

		$res["status"] 	= "000";
		$res["error"] 	= "";
		$res["message"] = $data;
        $db->close();

		echo json_encode($res);
	}

	public function postView_trend_products() {
        $id_user = cek_session_login();
        $db = db_connect();

        $data = $db->table('merchant_trend_products t')->select('t.*, p.label, p.harga, p.merchant_fee, p.online_status, p.image, p.is_active, c.category')
                ->join('merchant_products p', 'p.id=t.id_product', 'left')
                ->join('categories c', 'c.id=p.id_category', 'left')                
                ->where('p.id_user', $id_user)
                ->where('p.is_active', 1)
                ->orderBy('p.label', 'asc')->get()->getResult();

		$res["status"] 	= "000";
		$res["error"] 	= "";
		$res["message"] = $data;
        $db->close();

		echo json_encode($res);
	}

	public function postUpdate() {
        $db = db_connect();
        $request = request();
        $id_user = cek_session_login();
        $data = $request->getJSON(true);
        $db->table('merchant_trend_products')->where('id_user', $id_user)->delete();
        $loop = -1;
        $insert = array();
        foreach ($data as $datax) {
            $loop++;
            $insert[$loop]['id_product'] = $datax;
            $insert[$loop]['is_active'] = 1;
            $insert[$loop]['id_user'] = $id_user;
        }
        $data       	= $db->table('merchant_trend_products')->insertBatch($insert);
		$res["status"] 	= "000";
		$res["error"] 	= "";
		$res["message"] = "Success Update Trend Products.";
        $db->close();

		echo json_encode($res);
	}
}
