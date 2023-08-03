<?php

namespace App\Controllers\Biller;

class Biller extends BaseController
{
    public function index()
    {
		echo 'Access denied!'; 
    }

    public function postGet_biller_list()
    {
        $id_user = cek_session_login();
        $db = db_connect();

        $biller = $db->table('biller_list')->get()->getResult();
        $data = $db->table('biller_list_setting')->get()->getResult();

        $bal = array();
        $_data = array();
        foreach ($data as $dt) {
          if ($dt->id_biller === '1' || $dt->id_biller === 1) {
            $headersXendit = [
              'Accept: application/json',
              'Content-Type: application/json',
              'Authorization: Basic '.base64_encode($dt->api_key.':'),
            ];
            
            $dataBiller = biller_get_balance();
            // print_r($dataBiller);
            $dt->balance = $dataBiller['data']["deposit"] ?? 'Wrong API Key or Secret Key!';
          } else {
            $dt->balance = 0;
          }
          $_data[] = $dt;
        }

        $res["status"] 	= "000";
        $res["error"] 	= "";
        $res["message"] = "";
        $res["data"]['list'] = $biller;
        $res["data"]['biller_setting'] = $_data;
        $db->close();

		echo json_encode($res);
    }

    public function postSet_biller_list()
    {
        $id_user = cek_session_login();
        $request = request();
        $dataPost = $request->getJSON(true);
        $db = db_connect();

        foreach ($dataPost as $dtPost) {
            if ($dtPost['api_key']) {
              // $update['id_user'] = 0;
              $update['api_key'] = $dtPost['api_key'];
              $update['username'] = $dtPost['username'];
              $update['is_used'] = $dtPost['is_used'] ?? 1;

              $_data = $db->table('biller_list_setting')->get()->getRow();
              
              if ($_data) {
                $updateX = $db->table('biller_list_setting')->ignore()->update($update);
              } else {
                $db->table('biller_list_setting')->ignore()->insert($update);
              }
            }
        }
        // echo (string) $db->getLastQuery();

        $biller = $db->table('biller_list')->get()->getResult();
        $data = $db->table('biller_list_setting')->get()->getResult();

        $bal = array();
        $_data = array();
        foreach ($data as $dt) {
          if ($dt->id_biller === '1' || $dt->id_biller === 1) {
            $headersXendit = [
              'Accept: application/json',
              'Content-Type: application/json',
              'Authorization: Basic '.base64_encode($dt->api_key.':'),
            ];
            
            $dataBiller = biller_get_balance();
            // print_r($dataBiller);
            $dt->balance = isset($dataBiller['data']["rc"]) ? 'Wrong API Key or Secret Key!' : $dataBiller['data']["deposit"];
          } else {
            $dt->balance = 0;
          }
          $_data[] = $dt;
        }

        $res["status"] 	= "000";
        $res["error"] 	= "";
        $res["message"] = "Payment Gateway Setting is successfully updated.";
        $res["data"]['list'] = $biller;
        $res["data"]['biller_setting'] = $_data;
        $db->close();

        echo json_encode($res);
    }
}
