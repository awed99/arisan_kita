<?php

namespace App\Controllers\Biller;

class Products extends BaseController
{
    public function index()
    {
		echo 'Access denied!'; 
    }

	public function postAdd() {
        cek_session_login();
        $request = request();
        $db = db_connect();
        $datas = $request->getJSON(true);
        
        $loop = -1;
        $insert = array();
        foreach($datas as $data) {
            $loop++;
            $insert[$loop]['code_product'] = $data['partner_sku'];
            $insert[$loop]['product_name'] = $data['product_label'];
            $insert[$loop]['description'] = $data['product']['description'];
            $insert[$loop]['harga'] = $data['price_excl_tax'];
            $insert[$loop]['admin_fee'] = $data['admin_fee'];
            $insert[$loop]['merchant_fee'] = 0;
            if ($data['product']['category']['name'] === 'PD-Telkomsel') {
                $insert[$loop]['id_category'] = 18;
            } elseif ($data['product']['category']['name'] === 'PD-XL') {
                $insert[$loop]['id_category'] = 20;
            } elseif ($data['product']['category']['name'] === 'PD-Indosat') {
                $insert[$loop]['id_category'] = 19;
            } elseif ($data['product']['category']['name'] === 'PD-Tri') {
                $insert[$loop]['id_category'] = 23;
            } elseif ($data['product']['category']['name'] === 'PD-Axis') {
                $insert[$loop]['id_category'] = 32;
            } elseif ($data['product']['category']['name'] === 'PD-Smartfren') {
                $insert[$loop]['id_category'] = 31;
            } else {
                $insert[$loop]['id_category'] = 0;
            }
            
        }
        // print_r($insert);
        $db->table('products')->ignore()->insertBatch($insert);
        $db->close();
        
		$res["status"] 	= "000";
		$res["error"] 	= "";
		$res["message"] = "Success add new products.";

		echo json_encode($res);
	}

	public function postView() {
        // $id_user = cek_session_login();
        $db = db_connect();

        $data = $db->table('products')->orderBy('product_name', 'asc')->get()->getResult();
        $db->close();

		$res["status"] 	= "000";
		$res["error"] 	= "";
		$res["message"] = $data;

		echo json_encode($res);
	}

	public function postView_active() {
        $id_user = cek_session_login();
        $db = db_connect();

        $data = $db->table('merchant_products')->where('is_active', 1)->orderBy('product_name', 'asc')                
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

        $dt   = $db->table('products_pricing_merchant')->where('id_product', $data["id"])->get()->getRow();
        $is_active['is_active'] = !$dt->is_active;
        $data   = $db->table('products_pricing_merchant')->where('id_product', $data["id"])->update($is_active);
        $data = $db->table('merchant_products')->orderBy('product_name', 'asc')->get()->getResult();
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

        $dt['biller_commission_amount'] = $data["biller_commission_amount"];
        $dt['biller_commission_percent'] = $data["biller_commission_percent"];
        $data       	= $db->table('products')->where('id_product', $data["id"])->update($dt);
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
        
        $dt['biller_commission_amount'] = $data["biller_commission_amount"];
        $dt['biller_commission_percent'] = $data["biller_commission_percent"];
        $data       	= $db->table('products')->update($dt);
        $db->close();
        
		$res["status"] 	= "000";
		$res["error"] 	= "";
		$res["message"] = "Success Update Product's Status.";

		echo json_encode($res);
	}

	public function postView_product_images() {
        $id_user = cek_session_login();
        $db = db_connect();

        $data = $db->table('merchant_products')->select('DISTINCT(brand)')->where('is_active', 1)->orderBy('brand', 'asc')                
                ->get()->getResult();
        $data2 = $db->table('product_brand_images')->where('id_user', 0)->orderBy('brand_product', 'asc')                
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
        $row['id_user'] = 0;
		$row['brand_product'] = $dataPost['brand_product'];

        $isset = $db->table('product_brand_images')->where('id_user', 0)->where('brand_product', $dataPost['brand_product'])->get()->getRow();
        if ($isset) {
            $db->table('product_brand_images')->where('id_user', 0)->where('id_product_brand_images', $isset->id_product_brand_images)->ignore()->update($row);
        } else {
            $db->table('product_brand_images')->ignore()->insert($row);
        }

        $data = $db->table('merchant_products')->select('DISTINCT(brand)')->where('is_active', 1)->orderBy('brand', 'asc')                
                ->get()->getResult();
        $data2 = $db->table('product_brand_images')->where('id_user', 0)->orderBy('brand_product', 'asc')                
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
