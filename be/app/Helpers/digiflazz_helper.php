<?php

    function digiflazz_get_products($source=false) {
        $db = db_connect();
        
        $dt = $db->table('biller_list_setting')->where('id_biller', '1')->get()->getRow();
        // print_r($dt);
        $db->close();

        // $__source = json_decode($source, true);
        $__source['cmd'] = 'deposit';
        $__source['username'] = $dt->username;
        $__source['sign'] = md5($dt->username.$dt->api_key."pricelist");
        $_source = json_encode($__source);

        $data = json_decode(curl(getenv('DIGIFLAZZ_API_ENDPOINT').'price-list', true, $_source), true);
        // print_r($data);
        return $data;
    }

    function digiflazz_get_balance($source=false) {
        $db = db_connect();
        
        $dt = $db->table('biller_list_setting')->where('id_biller', '1')->get()->getRow();
        $db->close();

        // $__source = json_decode($source, true);
        $__source['cmd'] = 'deposit';
        $__source['username'] = $dt->username;
        $__source['sign'] = md5($dt->username.$dt->api_key."depo");
        $_source = json_encode($__source);

        $data = json_decode(curl(getenv('DIGIFLAZZ_API_ENDPOINT').'cek-saldo', true, $_source), true);
        // print_r($data);
        return $data;
    }

    function digiflazz_topup($source=false) {
        $db = db_connect();
        
        $dt = $db->table('biller_list_setting')->where('id_biller', '1')->get()->getRow();
        $db->close();

        $__source = json_decode($source, true);
        $__source['username'] = $dt->username;
        $__source['sign'] = md5($dt->username.$dt->api_key.$__source['ref_id']);
        $_source = json_encode($__source);

        $data = json_decode(curl(getenv('DIGIFLAZZ_API_ENDPOINT').'transaction', true, $_source), true);
        return $data;
    }

?>