<?php

namespace App\Controllers;

class Promo_products extends BaseController
{
    public function index()
    {
		echo 'Access denied!'; 
    }

	public function postView() {
        $id_user = cek_session_login();
        $request = request();
        $dataPost = $request->getPost();
        $db = db_connect();

        $data = $db->table('merchant_promo_products')->where('id_user', $id_user)->get()->getResult();
        $data2 = $db->table('merchant_promo_products')
        ->select('DISTINCT(id_promo), id_products')->where('id_user', $id_user)
        ->get()->getResult();
        $data3 = $db->table('merchant_products p')->select('p.*, c.category')
                ->join('categories c', 'c.id=p.id_category', 'left')
                ->where('p.id_user', $id_user)
                ->where('p.is_active', 1)
                ->orderBy('p.product_name', 'asc')->get()->getResult();
        $db->close();

        $dt['data'] = $data;
        $dt['parent'] = $data2;
        $dt['products'] = $data3;

		$res["status"] 	= "000";
		$res["error"] 	= "";
		$res["message"] = $dt;

		echo json_encode($res);
	}

	public function postView_promo_products() {
        $id_user = cek_session_login();
        $request = request();
        $dataPost = $request->getPost();
        $db = db_connect();

        $data = $db->table('merchant_promo_products')->select('pp.*, p.product_name, p.final_price_merchant, p.merchant_commission_amount, p.merchant_commission_percent, p.is_active, c.product_type')
                ->join('merchant_products p', 'p.id=pp.id_product', 'left')
                ->where('p.id_user', $id_user)
                ->where('p.is_active', 1)
                ->orderBy('p.product_name', 'asc')->get()->getResult();
        $db->close();

		$res["status"] 	= "000";
		$res["error"] 	= "";
		$res["message"] = $data;

		echo json_encode($res);
	}

	public function postUpdate() {
        $id_user = cek_session_login();
        $request = request();
        $db = db_connect();

        $data = $request->getJSON(true);
        $db->table('merchant_promo_products')->where('id_user', $id_user)->delete();
        if ($data) {
            $loop = -1;
            $insert = array();
            foreach (array_keys($data) as $datax) {
                $loop++;
                $insert[$loop]['id_user'] = $id_user;
                $insert[$loop]['id_promo'] = $datax;
                $insert[$loop]['id_products'] = json_encode($data[$datax]);
                $insert[$loop]['is_active'] = 1;
            }
            $db->table('merchant_promo_products')->insertBatch($insert);
        }
        $db->close();

		$res["status"] 	= "000";
		$res["error"] 	= "";
		$res["message"] = "Success Update Promo Products.";

		echo json_encode($res);
	}
}
