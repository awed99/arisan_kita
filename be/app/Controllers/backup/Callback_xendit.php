<?php

namespace App\Controllers;

class Callback_xendit extends BaseController
{
    public function index()
    {
		echo 'Access denied!'; 
    }

    public function postPaid_va()
    {
        // $id_user = cek_session_login();
        $request = request();
        $data = $request->getJSON(true);
        print_r($data);
        $db = db_connect();

        $db->close();
    }

    public function postPaid_retail()
    {
        // $id_user = cek_session_login();
        $request = request();
        $data = $request->getJSON(true);
        print_r($data);
        $db = db_connect();

        $db->close();
    }

    public function postSent_disbursement()
    {
        // $id_user = cek_session_login();
        $request = request();
        $data = $request->getJSON(true);
        print_r($data);
        $db = db_connect();

        $db->close();
    }
}
