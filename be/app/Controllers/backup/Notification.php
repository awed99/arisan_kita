<?php

namespace App\Controllers;

class Notification extends BaseController
{
    public function index()
    {
		echo 'Access denied!'; 
    }

	public function postGet_my_notif() {
        $id_user = cek_session_login();
        $request = request();
        $dataPost = $request->getPost();
        $db = db_connect();

        $notif = $db->table('app_notifications')->where('id_user', $id_user)->orWhere('id_user', -1)->orderBy('id', 'desc')->get()->getResult();
        $db->close();

		$res["status"] 	= "000";
		$res["error"] 	= "";
		$res["message"] 	= "";
		$res["data"] = $notif;

		echo json_encode($res);
	}

	public function postCreate() {
        $id_user = cek_session_login();
        $request = request();
        $db = db_connect();
        $dataPost = $request->getPost(true);
        

        $notif['id_user'] = $id_user;
        $notif['notif_code'] = 'U';
        $notif['notif_title'] = $dataPost['notif_title'];
        $notif['notif_info'] = $dataPost['notif_info'];
        $data = $db->table('app_notifications')->ignore()->insert($notif);
        $db->close();

		$res["status"] 	= "000";
		$res["error"] 	= "";
		$res["message"] = "Success Update Data.";

		echo json_encode($res);
	}

	public function postRead() {
        $id_user = cek_session_login();
        $request = request();
        $db = db_connect();
        $dataPost = $request->getPost();
        
        $notif['is_read'] = 1;
        $notif['read_date'] = date('Y-m-d H:i:s');
        $db->table('app_notifications')->where('id_user', $id_user)->where('id', $dataPost['id'])->ignore()->update($notif);
        $notif = $db->table('app_notifications')->where('id_user', $id_user)->orWhere('id_user', -1)->orderBy('id', 'desc')->get()->getResult();
        $db->close();

		$res["status"] 	= "000";
		$res["error"] 	= "";
		$res["message"] = "Notif read.";
		$res["data"] = $notif;

		echo json_encode($res);
	}

	public function postRead_all() {
        $id_user = cek_session_login();
        $request = request();
        $db = db_connect();
        $dataPost = $request->getPost();
        
        $notif['is_read'] = 1;
        $notif['read_date'] = date('Y-m-d H:i:s');
        $db->table('app_notifications')->where('id_user', $id_user)->ignore()->update($notif);
        $notif = $db->table('app_notifications')->where('id_user', $id_user)->orWhere('id_user', -1)->orderBy('id', 'desc')->get()->getResult();
        $db->close();

		$res["status"] 	= "000";
		$res["error"] 	= "";
		$res["message"] = "Notif read.";
		$res["data"] = $notif;

		echo json_encode($res);
	}

}
