<?php

namespace App\Controllers;

class Slideshow extends BaseController
{
    public function index()
    {
		echo 'Access denied!'; 
    }

	public function postUpload() {
        $id_user = cek_session_login();
        $request = request();
        $dataPost = $request->getPost();
        $db = db_connect();

        $row['image'] = upload_file($request);
        $row['id_user'] = $id_user;
		$row['external_url'] = $dataPost['external_url'];
		$db->table('merchant_slideshow')->insert($row);

		$update["width"] = $dataPost["width"];
		$update["height"] = $dataPost["height"];
        $db->table('merchant_slideshow')->where('id_user', $id_user)->update($update);
        $db->close();
		
		$res["status"] 	= "000";
		$res["error"] 	= "";
		$res["message"] = "Image uploaded successfully.";
		echo json_encode($res);
	}

	public function postView() {
        $id_user = cek_session_login();
        $request = request();
        $dataPost = $request->getPost();
        $db = db_connect();

        $dimension = $db->table('merchant_slideshow')->where('id_user', $id_user)->get()->getRow();
        $images = $db->table('merchant_slideshow')->where('id_user', $id_user)->get()->getResult();
        $db->close();

		$data['dimension'] = $dimension;
		$data['images'] = $images;

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
        
		$update["width"] = $data["width"];
		$update["height"] = $data["height"];
        $data       	= $db->table('merchant_slideshow')->where('id_user', $id_user)->update($update);
        $db->close();

		$res["status"] 	= "000";
		$res["error"] 	= "";
		$res["message"] = "Success Update Data.";

		echo json_encode($res);
	}

	public function postDelete() {
        $id_user = cek_session_login();
        $request = request();
        $db = db_connect();
        $data = $request->getJSON(true);

        $image       	= $db->table('merchant_slideshow')->where('id_user', $id_user)->where('id', $data["id"])->get()->getRow();
        $data       	= $db->table('merchant_slideshow')->where('id_user', $id_user)->where('id', $data["id"])->delete();
        $db->close();
		unlink(FCPATH.$image->image);

		$res["status"] 	= "000";
		$res["error"] 	= "";
		$res["message"] = "Success Delete Image.";

		echo json_encode($res);
	}

	public function postGet_images() {
        $id_user = cek_session_login();
        $request = request();
        $db = db_connect();
        $data = $request->getJSON(true);

        $dimension      = $db->table('merchant_slideshow')->where('id_user', $id_user)->where('id', '1')->get()->getRow();
        $images       	= $db->table('merchant_slideshow')->where('id_user', $id_user)->where('id >', '1')->get()->getResult();
        $db->close();
		$data['dimension'] = $dimension;
		$data['images'] = $images;

		$res["status"] 	= "000";
		$res["error"] 	= "";
		$res["message"] = $data;

		echo json_encode($res);
	}

}
