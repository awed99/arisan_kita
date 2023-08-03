<?php

    function xendit_get_balance($source=false, $is_admin=false, $is_user=false, $id_user=0) {
        $db = db_connect();
        
        $dt = $db->table('pg_list_setting')->where('id_user', $id_user)->where('id_pg', '1')->get()->getRow();
        $headers = [
            'Accept: application/json',
            'Content-Type: application/json',
            'Authorization: Basic '.base64_encode($dt->api_key.':'),
        ];
        $db->close();

        $data = json_decode(curl(getenv('XENDIT_DOMAIN').'/balance', false, false, $headers), true)['balance'] ?? 0;
        return $data;
    }

    function xendit_create_va($source, $is_admin=false, $is_user=false, $id_user=0) {
        $db = db_connect();
        
        $dt = $db->table('pg_list_setting')->where('id_user', $id_user)->where('id_pg', '1')->get()->getRow();
        $headers = [
            'Accept: application/json',
            'Content-Type: application/json',
            'Authorization: Basic '.base64_encode($dt->api_key.':'),
        ];
        $db->close();

        $data = json_decode(curl(getenv('XENDIT_DOMAIN').'/callback_virtual_accounts', true, $source, $headers), true);
        $data['fee'] = $dt->fee_va;

        $insert = $data; 
        $insert['id_user'] = $id_user;
        $insert['type_transaction'] = explode("-", $insert['external_id'])[0] === 'TOPUP' ? 'TOPUP' : 'PAYMENT';
        $db->table('history_va_xendit')->ignore()->insert($insert);
        
        return $data;
    }

    function xendit_create_retail($source, $is_admin=false, $is_user=false, $id_user=0) {
        $db = db_connect();
        
        $dt = $db->table('pg_list_setting')->where('id_user', $id_user)->where('id_pg', '1')->get()->getRow();
        $headers = [
            'Accept: application/json',
            'Content-Type: application/json',
            'Authorization: Basic '.base64_encode($dt->api_key.':'),
        ];
        $db->close();

        $data = json_decode(curl(getenv('XENDIT_DOMAIN').'/fixed_payment_code', true, $source, $headers), true);
        $data['fee'] = $dt->fee_retail;
        return $data;
    }

    function xendit_create_disbursement($source, $is_admin=false, $is_user=false, $id_user=0) {
        $db = db_connect();
        
        $dt = $db->table('pg_list_setting')->where('id_user', $id_user)->where('id_pg', '1')->get()->getRow();
        $headers = [
            'Accept: application/json',
            'Content-Type: application/json',
            'Authorization: Basic '.base64_encode($dt->api_key.':'),
        ];
        $db->close();

        $data = json_decode(curl(getenv('XENDIT_DOMAIN').'/disbursements', true, $source, $headers), true);
        $data['fee'] = $dt->fee_disbursement;
        return $data;
    }

    function xendit_get_va($source, $is_admin=false, $is_user=false, $id_user=0) {
        $db = db_connect();

        $dt = $db->table('pg_list_setting')->where('id_user', $id_user)->where('id_pg', '1')->get()->getRow();
        $headers = [
            'Accept: application/json',
            'Content-Type: application/json',
            'Authorization: Basic '.base64_encode($dt->api_key.':'),
        ];
        $db->close();

        $data = json_decode(curl(getenv('XENDIT_DOMAIN')."/callback_virtual_accounts/".$source, false, false, $headers), true);
        $data['fee'] = $dt->fee_va;
        // print_r($data); 
        // die();
        return $data;
    }

?>