<?php

namespace App\Controllers;

class Web_config extends BaseController
{
    public function index()
    {
		echo 'Access denied!'; 
    }

	public function postUpload_image() {
        $id_user = cek_session_login();
        $request = request();
        $dataPost = $request->getPost();
        $db = db_connect();

        $fileName = upload_file($request);
		$update["id_user"] = $id_user;
		$update["web_icon"] = $fileName;
		$update["web_name"] = $dataPost["web_name"];
		$update["web_title"] = $dataPost["web_title"];
		$update["whatsapp"] = $dataPost["whatsapp"];
		$update["telegram"] = $dataPost["telegram"];
		$update["facebook"] = $dataPost["facebook"];
		$update["instagram"] = $dataPost["instagram"];
		$update["tiktok"] = $dataPost["tiktok"];
		$update["address"] = $dataPost["address"];
		$update["maps_url"] = $dataPost["maps_url"];
		
		$isset = $db->table('web_config')->where('id_user', $id_user)->get()->getRow();
		if ($isset) {
			$db->table('web_config')->where('id_user', $id_user)->update($update);
		} else {
			$db->table('web_config')->ignore()->insert($update);
		}
		// echo (string)$db->getLastQuery();
        $db->close();
		
		$res["status"] 	= "000";
		$res["error"] 	= "";
		$res["message"] = "Success Update Data.";
		echo json_encode($res);
	}

	public function postView() {
        $id_user = cek_session_login();
        $db = db_connect();

        $data = $db->table('web_config')->where('id_user', $id_user)->get()->getRow();
        $db->close();

		$res["status"] 	= "000";
		$res["error"] 	= "";
		$res["message"] = $data;

		echo json_encode($res);
	}

	public function postSave() {
        $id_user = cek_session_login();
        $request = request();
        $db = db_connect();
        $data = $request->getJSON(true);
        
		$update["web_name"] = $data["web_name"];
		$update["web_title"] = $data["web_title"];
		$update["whatsapp"] = $data["whatsapp"];
		$update["telegram"] = $data["telegram"];
		$update["facebook"] = $data["facebook"];
		$update["instagram"] = $data["instagram"];
		$update["tiktok"] = $data["tiktok"];
		$update["address"] = $data["address"];
		$update["maps_url"] = $data["maps_url"];
        $data = $db->table('web_config')->where('id_user', $id_user)->update($update);
        $db->close();

		$res["status"] 	= "000";
		$res["error"] 	= "";
		$res["message"] = "Success Update Data.";

		echo json_encode($res);
	}

	public function postGet_current_template() {
        $id_user = cek_session_login();
        $db = db_connect();

        $data = $db->table('web_config')->get()->getRow();
        $db->close();

		$res["status"] 	= "000";
		$res["error"] 	= "";
		$res["message"] = $data->template;

		echo json_encode($res);
	}

	public function postChange_template() {
        $id_user = cek_session_login();
        $request = request();
        $db = db_connect();
        $data = $request->getJSON(true);
        
		$update["template"] = $data["template"];
        $data       	= $db->table('web_config')->where('id_user', $id_user)->update($update);
        $db->close();

		$res["status"] 	= "000";
		$res["error"] 	= "";
		$res["message"] = "Success Update Data.";

		echo json_encode($res);
	}
}
