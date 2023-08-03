<?php

namespace App\Controllers;

class Products extends BaseController
{
    public function index()
    {
		echo 'Access denied!'; 
    }

	public function postRefresh() {
        // cek_session_login();
        $request = request();
        $db = db_connect();
        $datas = biller_get_products();
        
        $loop = -1;
        $insert = array();
        foreach($datas['data'] as $data) {
            $loop++;
            $insert[$loop] = $data;
            $insert[$loop]['description'] = $data['desc'];
            $insert[$loop]['product_type'] = $data['category'];

            unset($insert[$loop]['desc']);
            unset($insert[$loop]['category']);

            if (isset($data['admin'])) {
                $insert[$loop]['product_admin_fee'] = $data['admin'];
                unset($insert[$loop]['commission']);
            }
            if (isset($data['commission'])) {
                $insert[$loop]['merchant_fee'] = $data['commission'];
                unset($insert[$loop]['admin']);
            }

            // $insert[$loop]['partner_sku'] = $data['partner_sku'];
            // $insert[$loop]['product_name'] = $data['product_label'];
            // $insert[$loop]['description'] = $data['desc'];
            // $insert[$loop]['price'] = $data['price'];
            // if (isset($data['admin'])) {
            //     $insert[$loop]['product_admin_fee'] = $data['admin'];
            // }
            // if (isset($data['commission'])) {
            //     $insert[$loop]['merchant_fee'] = $data['commission'];
            // }
            // $insert[$loop]['id_category'] = 0;
            // if ($data['product']['category']['name'] === 'PD-Telkomsel') {
            //     $insert[$loop]['id_category'] = 18;
            // } elseif ($data['product']['category']['name'] === 'PD-XL') {
            //     $insert[$loop]['id_category'] = 20;
            // } elseif ($data['product']['category']['name'] === 'PD-Indosat') {
            //     $insert[$loop]['id_category'] = 19;
            // } elseif ($data['product']['category']['name'] === 'PD-Tri') {
            //     $insert[$loop]['id_category'] = 23;
            // } elseif ($data['product']['category']['name'] === 'PD-Axis') {
            //     $insert[$loop]['id_category'] = 32;
            // } elseif ($data['product']['category']['name'] === 'PD-Smartfren') {
            //     $insert[$loop]['id_category'] = 31;
            // } else {
            //     $insert[$loop]['id_category'] = 0;
            // }
            
        }
        print_r($insert);
        $db->table('products')->ignore()->upsertBatch($insert);
        $db->close();
        
		$res["status"] 	= "000";
		$res["error"] 	= "";
		$res["message"] = "Success add new products.";

		echo json_encode($res);
	}

	public function postView() {
        $id_user = cek_session_login();
        $db = db_connect();

        $data = $db->table('merchant_products')->where('id_user', $id_user)->orderBy('product_name', 'asc')->get()->getResult();
        $db->close();

		$res["status"] 	= "000";
		$res["error"] 	= "";
		$res["message"] = $data;

		echo json_encode($res);
	}

	public function postView_active() {
        $id_user = cek_session_login();
        $db = db_connect();

        $data = $db->table('merchant_products')->where('id_user', $id_user)->where('is_active', 1)->orderBy('product_name', 'asc')                
                ->orderBy('p.product_name', 'asc')->get()->getResult();
        $db->close();

		$res["status"] 	= "000";
		$res["error"] 	= "";
		$res["message"] = $data;

		echo json_encode($res);
	}

	public function postActivation() {
        $id_user = cek_session_login();
        $request = request();
        $db = db_connect();
        $data = $request->getJSON(true);

        $dt   = $db->table('products_pricing_merchant')->where('id_user', $id_user)->where('id_product', $data["id"])->get()->getRow();
        $is_active['is_active'] = !$dt->is_active;
        $data   = $db->table('products_pricing_merchant')->where('id_product', $data["id"])->where('id_user', $id_user)->update($is_active);
        $data = $db->table('merchant_products')->where('id_user', $id_user)->orderBy('product_name', 'asc')->get()->getResult();
        $db->close();
        
		$res["status"] 	= "000";
		$res["error"] 	= "";
		$res["message"] = "Success Update Product's Status.";
		$res["data"] = $data;

		echo json_encode($res);
	}

	public function postUpdate() {
        $id_user = cek_session_login();
        $request = request();
        $db = db_connect();
        $data = $request->getJSON(true);

        $dt['merchant_commission_amount'] = $data["merchant_commission_amount"];
        $dt['merchant_commission_percent'] = $data["merchant_commission_percent"];
        $data       	= $db->table('products_pricing_merchant')->where('id_user', $id_user)->where('id_product', $data["id"])->update($dt);
        $db->close();

		$res["status"] 	= "000";
		$res["error"] 	= "";
		$res["message"] = "Success Update Product's Status.";

		echo json_encode($res);
	}

	public function postUpdate_all() {
        $id_user = cek_session_login();
        $request = request();
        $db = db_connect();
        $data = $request->getJSON(true);
        
        $dt['merchant_commission_amount'] = $data["merchant_commission_amount"];
        $dt['merchant_commission_percent'] = $data["merchant_commission_percent"];
        $data       	= $db->table('products_pricing_merchant')->where('id_user', $id_user)->update($dt);
        $db->close();
        
		$res["status"] 	= "000";
		$res["error"] 	= "";
		$res["message"] = "Success Update Product's Status.";

		echo json_encode($res);
	}

	public function postView_product_images() {
        $id_user = cek_session_login();
        $db = db_connect();

        $data = $db->table('merchant_products')->select('DISTINCT(brand)')->where('id_user', $id_user)->where('is_active', 1)->orderBy('brand', 'asc')                
                ->get()->getResult();
        $data2 = $db->table('product_brand_images')->where('id_user', $id_user)->orderBy('brand_product', 'asc')                
                ->get()->getResult();

        $db->close();

		$res["status"] 	= "000";
		$res["error"] 	= "";
		$res["message"] 	= "";
		$res["data"]['brands'] = $data;
		$res["data"]['product_brand_images'] = $data2;

		echo json_encode($res);
	}

	public function postUpdate_product_images() {
        $id_user = cek_session_login();
        $request = request();
        $dataPost = $request->getPost();
        $db = db_connect();

        $row['image_url'] = upload_file($request);
        $row['id_user'] = $id_user;
		$row['brand_product'] = $dataPost['brand_product'];

        $isset = $db->table('product_brand_images')->where('id_user', $id_user)->where('brand_product', $dataPost['brand_product'])->get()->getRow();
        if ($isset) {
            $db->table('product_brand_images')->where('id_user', $id_user)->where('id_product_brand_images', $isset->id_product_brand_images)->ignore()->update($row);
        } else {
            $db->table('product_brand_images')->ignore()->insert($row);
        }

        $data = $db->table('merchant_products')->select('DISTINCT(brand)')->where('id_user', $id_user)->where('is_active', 1)->orderBy('brand', 'asc')                
                ->get()->getResult();
        $data2 = $db->table('product_brand_images')->where('id_user', $id_user)->orderBy('brand_product', 'asc')                
                ->get()->getResult();

        $db->close();
		
		$res["status"] 	= "000";
		$res["error"] 	= "";
		$res["message"] = "Image uploaded successfully.";
		$res["data"]['brands'] = $data;
		$res["data"]['product_brand_images'] = $data2;
		echo json_encode($res);
	}
    
}
