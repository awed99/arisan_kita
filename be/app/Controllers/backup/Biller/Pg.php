<?php

namespace App\Controllers\Biller;

class Pg extends BaseController
{
    public function index()
    {
		echo 'Access denied!'; 
    }

    public function postGet_pg_list()
    {
        // $id_user = cek_session_login();
        $db = db_connect();

        $pg = $db->table('pg_list')->get()->getResult();
        $data = $db->table('pg_list_setting')->where('id_user', '0')->get()->getResult();

        $bal = array();
        $_data = array();
        foreach ($data as $dt) {
          if ($dt->id_pg === '1' || $dt->id_pg === 1) {
            $headersXendit = [
              'Accept: application/json',
              'Content-Type: application/json',
              'Authorization: Basic '.base64_encode($dt->api_key.':'),
            ];
            
            $dataXendit = json_decode(curl(getenv('XENDIT_DOMAIN').'/balance', false, false, $headersXendit), true);
            $dt->balance = $dataXendit['balance'] ?? 'Wrong API Key or Secret Key!';
          } else {
            $dt->balance = 0;
          }
          $_data[] = $dt;
        }

        $res["status"] 	= "000";
        $res["error"] 	= "";
        $res["message"] = "";
        $res["data"]['list'] = $pg;
        $res["data"]['pg_setting'] = $_data;
        $db->close();

		echo json_encode($res);
    }

    public function postSet_pg_list()
    {
        $id_user = cek_session_login();
        $request = request();
        $dataPost = $request->getJSON(true);
        $db = db_connect();

        foreach ($dataPost as $dtPost) {
            if ($dtPost['api_key']) {
              $update['id_user'] = 0;
              $update['api_key'] = $dtPost['api_key'];
              $update['secret_key'] = $dtPost['secret_key'];
              $update['is_used'] = $dtPost['is_used'] ?? 1;
              $update['fee_va'] = $dtPost['fee_va'] ?? 0;
              $update['fee_retail'] = $dtPost['fee_retail'] ?? 0;
              $update['fee_qris'] = $dtPost['fee_qris'] ?? 0;
              $update['fee_disbursement'] = $dtPost['fee_disbursement'] ?? 0;

              $_data = $db->table('pg_list_setting')->where('id_user', 0)->get()->getRow();
              
              if ($_data) {
                $updateX = $db->table('pg_list_setting')->where('id_user', 0)->ignore()->update($update);
              } else {
                $db->table('pg_list_setting')->ignore()->insert($update);
              }
            }
        }
        // echo (string) $db->getLastQuery();

        $pg = $db->table('pg_list')->get()->getResult();
        $data = $db->table('pg_list_setting')->where('id_user', 0)->get()->getResult();

        $bal = array();
        $_data = array();
        foreach ($data as $dt) {
          if ($dt->id_pg === '1' || $dt->id_pg === 1) {
            $headersXendit = [
              'Accept: application/json',
              'Content-Type: application/json',
              'Authorization: Basic '.base64_encode($dt->api_key.':'),
            ];
            
            $dataXendit = json_decode(curl(getenv('XENDIT_DOMAIN').'/balance', false, false, $headersXendit), true);
            $dt->balance = $dataXendit['balance'] ?? 'Wrong API Key or Secret Key!';
          } else {
            $dt->balance = 0;
          }
          $_data[] = $dt;
        }

        $res["status"] 	= "000";
        $res["error"] 	= "";
        $res["message"] = "Payment Gateway Setting is successfully updated.";
        $res["data"]['list'] = $pg;
        $res["data"]['pg_setting'] = $_data;
        $db->close();

        echo json_encode($res);
    }
}
