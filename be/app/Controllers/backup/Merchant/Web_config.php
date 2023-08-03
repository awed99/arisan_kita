<?php

namespace App\Controllers\Merchant;

class Web_config extends BaseController
{
    public function postView() {
        $id_user = cek_subdomain();
        $db = db_connect();

        $web_config = $db->table('web_config')->select('count(*) as total')->where('id_user', $id_user)->where('web_name IS NOT NULL')->where('web_title IS NOT NULL')->where('web_icon IS NOT NULL')->get()->getRow()->total;
        $pg = $db->table('pg_list_setting')->select('count(*) as total')->where('id_user', $id_user)->where('is_used', 1)->get()->getRow()->total;
        $products = $db->table('merchant_products')->select('count(*) as total')->where('id_user', $id_user)->where('(merchant_commission_amount > 0 OR merchant_commission_percent > 0)')->get()->getRow()->total;

        $data = $db->table('web_config')->where('id_user', $id_user)->get()->getRow();
        $db->close();

        $res["status"]   = "000";
        $res["error"]   = "";
        $res["message"] = $data;
        $res["web_config"] = $web_config;
        $res["pg"] = $pg;
        $res["product"] = $products;

        echo json_encode($res);
    }

    public function postSlideshow() {
        $id_user = cek_subdomain();
        $db = db_connect();

        $data = $db->table('merchant_slideshow')->where('id_user', $id_user)->get()->getResult();
        $db->close();

        $res["status"]   = "000";
        $res["error"]   = "";
        $res["message"] = $data;

        echo json_encode($res);
    }
}