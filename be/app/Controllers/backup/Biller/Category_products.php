<?php

namespace App\Controllers\Biller;

class Category_products extends BaseController
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

        $data = $db->table('categories_products')->where('id_user', 0)->get()->getResult();
        $data2 = $db->table('categories_products')
        ->select('DISTINCT(id_category), id_products')->where('id_user', 0)
        ->get()->getResult();
        $data3 = $db->table('products p')->select('p.*, c.category')
                ->join('categories c', 'c.id=p.id_category', 'left')
                ->orderBy('p.product_name', 'asc')
                ->groupBy('p.id_product', 'asc')->get()->getResult();
        $db->close();

        $dt['data'] = $data;
        $dt['parent'] = $data2;
        $dt['products'] = $data3;

		$res["status"] 	= "000";
		$res["error"] 	= "";
		$res["message"] = $dt;

		echo json_encode($res);
	}

	public function postUpdate() {
        $id_user = cek_session_login();
        $request = request();
        $db = db_connect();

        $data = $request->getJSON(true);
        $db->table('categories_products')->where('id_user', 0)->delete();
        if ($data) {
            $loop = -1;
            $insert = array();
            foreach (array_keys($data) as $datax) {
                $loop++;
                $insert[$loop]['id_user'] = 0;
                $insert[$loop]['id_category'] = $datax;
                $insert[$loop]['id_products'] = json_encode($data[$datax]);
                $insert[$loop]['is_active'] = 1;
            }
            $db->table('categories_products')->insertBatch($insert);
        }
        $db->close();

		$res["status"] 	= "000";
		$res["error"] 	= "";
		$res["message"] = "Success Update Category Products.";

		echo json_encode($res);
	}
}
