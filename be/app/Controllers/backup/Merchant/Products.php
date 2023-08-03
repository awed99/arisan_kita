<?php

namespace App\Controllers\Merchant;

class Products extends BaseController
{

    public function postView()
    {
        $request = request();
        $id_user = cek_subdomain();
        $datas = $request->getPost();
        $db = db_connect();
        // print_r($datas['category']);
        if($datas['search'] != ""){
            $search = $datas['search'];
            $like = "(product_name like '%$search%' or product_type like '%$search%' or brand like '%$search%' or type like '%$search%')"; 
            $data = $db->table('merchant_products')
                        ->where('id_user', $id_user)
                        ->where($like)
                        ->orderBy('product_name', 'asc')->get()->getResult();
        }else{
            $data = $db->table('merchant_products')->where('id_user', $id_user)->orderBy('product_name', 'asc')->get()->getResult();
        }

        $data2 = $db->table('merchant_trend_products p')->select('c.*')
                ->join('merchant_products c', 'c.id_product=p.id_product', 'left')
                ->where('c.id_user', $id_user)
                ->where('p.is_active', 1)
                ->orderBy('c.product_name', 'asc')
                ->get()->getResult();
                
        $db->close();
        
        $array = array('trend'=> $data2, 'product'=> $data);
        $res["status"] 	= "000";
		$res["error"] 	= "";
		$res["message"] = $array;

		echo json_encode($res);
    }

    public function postDetails()
    {
        $request = request();
        $id_user = cek_subdomain();
        $datas = $request->getPost();
        $db = db_connect();
        
        $data = $db->table('merchant_products')
                ->where('id_product', $datas['id_product'])
                ->where('id_user', $id_user)
                ->get()->getRow();
        $db->close();

        $res["status"] 	= "000";
		$res["error"] 	= "";
		$res["message"] = $data;

		echo json_encode($res);
    }

    public function postCategory()
    {
        $id_user = cek_subdomain();
        $db = db_connect();

        $data = $db->table('categories')
                ->get()->getResult();
        $db->close();

        $res["status"] 	= "000";
		$res["error"] 	= "";
		$res["message"] = $data;

		echo json_encode($res);
    }

    public function postPromo()
    {
        $id_user = cek_subdomain();
        $db = db_connect();

        $data = $db->table('merchant_promo')
                ->get()->getResult();
        $db->close();

        $res["status"] 	= "000";
		$res["error"] 	= "";
		$res["message"] = $data;

		echo json_encode($res);
    }
}