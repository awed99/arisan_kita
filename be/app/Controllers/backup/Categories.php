<?php

namespace App\Controllers;

class Categories extends BaseController
{
    public function index()
    {
		echo 'Access denied!'; 
    }

    public function postCreate()
    {
        $id_user = cek_session_login();
        $request = request();
        $dataPost = $request->getPost();
        $db = db_connect();
        
        $isset = $db->table('categories')->where('id_user', $id_user)->where('category', $dataPost['category'])->get()->getRow();
        if (isset($isset)) {
            $res["status"] 	= "001";
            $res["error"] 	= "Can't Duplicate Category Name!";
            $res["message"] = "Can't Duplicate Category Name!";
            echo json_encode($res);
            return false;
        }

        $row['icon'] = upload_file($request);
		$row['id_parent'] = $dataPost['id_parent'];
		$row['id_user'] = 0;
		$row['category'] = $dataPost['category'];
        $db->table('categories')->insert($row);
        $db->close();
		
		$res["status"] 	= "000";
		$res["error"] 	= "";
		$res["message"] = "New Category has been created.";
		echo json_encode($res);
    }

	public function postView() {
        $id_user = cek_session_login();
        $request = request();
        $dataPost = $request->getPost();
        $db = db_connect();
        $data = $db->table('categories')->where('id_user', 0)->orderBy('category, icon', 'asc')->get()->getResult();

		$res["status"] 	= "000";
		$res["error"] 	= "";
		$res["message"] = $data;
        $db->close();

		echo json_encode($res);
	}

	public function postView_active() {
        $id_user = cek_session_login();
        $request = request();
        $dataPost = $request->getPost();
        $db = db_connect();
        $data = $db->table('categories')->where('id_user', 0)->where('is_active', 1)->getResult();
        $db->close();

		$res["status"] 	= "000";
		$res["error"] 	= "";
		$res["message"] = $data;

		echo json_encode($res);
	}

	public function postView_parent() {
        $id_user = cek_session_login();
        $request = request();
        $dataPost = $request->getPost();
        $db = db_connect();
        $data = $db->table('categories')->where('id_user', 0)->where('id_parent', 0)->getResult();
        $db->close();

		$res["status"] 	= "000";
		$res["error"] 	= "";
		$res["message"] = $data;

		echo json_encode($res);
	}

	public function postView_parent_active() {
        $id_user = cek_session_login();
        $request = request();
        $dataPost = $request->getPost();
        $db = db_connect();
        $data = $db->table('categories')->where('id_user', 0)->where('id_parent', 0)->where('is_active', 1)->get()->getResult();
        $db->close();

		$res["status"] 	= "000";
		$res["error"] 	= "";
		$res["message"] = $data;

		echo json_encode($res);
	}

	public function postDelete() {
        $id_user = cek_session_login();
        $request = request();
        $db = db_connect();
        $data = $request->getJSON(true);
        $image       	= $db->table('categories')->where('id_user', 0)->where('id', $data["id"])->get()->getRow();
        $data       	= $db->table('categories')->where('id_user', 0)->where('id', $data["id"])->delete();
        
        if (isset($isset->icon)) {
            unlink(FCPATH.$isset->icon);
        }
        $db->close();

		$res["status"] 	= "000";
		$res["error"] 	= "";
		$res["message"] = "Success Delete Category ".$image->category.".";

		echo json_encode($res);
	}

	public function postActivation() {
        $id_user = cek_session_login();
        $request = request();
        $db = db_connect();
        $data = $request->getJSON(true);
        $image = $db->table('categories')->where('id_user', 0)->where('id', $data["id"])->get()->getRow();
        $is_active['is_active'] = !$image->is_active;
        $data       	= $db->table('categories')->where('id_user', 0)->where('id', $data["id"])->update($is_active);
        $db->close();
        
		$res["status"] 	= "000";
		$res["error"] 	= "";
		$res["message"] = "Success Update Category ".$image->category.".";

		echo json_encode($res);
	}

	public function postUpdate() {
        $id_user = cek_session_login();
        $request = request();
        $dataPost = $request->getPost();
        $db = db_connect();

        $isset = $db->table('categories')->where('id_user', 0)->where('category', $dataPost['category'])->where('id <>', $dataPost['id'])->get()->getRow();
        
        if (isset($isset)) {
            $res["status"] 	= "001";
            $res["error"] 	= "Can't Duplicate Category Name!";
            $res["message"] = "Can't Duplicate Category Name!";
            echo json_encode($res);
            return false;
        }

        $file = $request->getFile('userfile');
        if (isset($file)) {
            $row['icon'] = upload_file($request);
            if (isset($isset->icon)) {
                unlink(FCPATH.$isset->icon);
            }
        }

		$row['id_parent'] = $dataPost['id_parent'];
		$row['category'] = $dataPost['category'];
		$db->table('categories')->where('id_user', 0)->where('id', $dataPost['id'])->update($row);
        $db->close();
		
		$res["status"] 	= "000";
		$res["error"] 	= "";
		$res["message"] = "Category has been updated.";
		echo json_encode($res);
	}
}
