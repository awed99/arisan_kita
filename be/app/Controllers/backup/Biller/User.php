<?php

namespace App\Controllers\Biller;

class User extends BaseController
{
    public function index()
    {
        return view('welcome_message');
    }

    public function postGet_forbiden_subdomains()
    {
        $db = db_connect();

        $data = $db->table('forbiden_subdomain')->get()->getRow();

		$res["status"] 	= "000";
		$res["error"] 	= "";
		$res["message"] = "";
		$res["data"] = $data;
        $db->close();

		echo json_encode($res);
    }

    public function postSet_forbiden_subdomains()
    {
        $id_user = cek_session_login();
        $request = request();
        $dataPost['subdomains'] = json_encode($request->getJSON());
        
        $db = db_connect();

        $data = $db->table('forbiden_subdomain')->where('id', '1')->ignore()->update($dataPost);
        $data = $db->table('forbiden_subdomain')->get()->getRow();

		$res["status"] 	= "000";
		$res["error"] 	= "";
		$res["message"] = "";
		$res["data"] = $data;
        $db->close();

		echo json_encode($res);
    }

    public function postLogin()
    {
        $request = request();
        $dataPost = $request->getPost();
        $db = db_connect();

        $update['token'] = hash('sha256', hash('sha256', $dataPost['email'].date('Y-m-d H:i:s')));
        $db->table('users u')->where('email', $dataPost['email'])->update($update);

        if (isset($dataPost['password'])) {
            $dataPost['password'] = hash('sha256', hash('sha256', $dataPost['password']));
        }
        $data = $db->table('users u')->select('*')
        ->join('profiles p', 'p.id_user = u.id_user', 'left')
        ->where($dataPost)->get()->getRow();

		$res["status"] 	= $data ? "000" : "001";
		$res["error"] 	= $data ? "" : "Email or Password incorrect!";
		$res["message"] = "";
		$res["data"] = $data;
        $db->close();

		echo json_encode($res);
    }

    public function postGet_merchants()
    {
        $id_user = cek_session_login();
        $db = db_connect();

        $data = $db->table('users u')->select('*')
        ->join('profiles p', 'p.id_user = u.id_user', 'left')
        ->join('user_status us', 'us.id_user_status = u.user_status', 'left')
        ->where('u.user_role', 1)->orderBy('u.id_user', 'desc')->get()->getResult();

        $db->close();

		$res["status"] 	= $data ? "000" : "001";
		$res["error"] 	= $data ? "" : "No Merchants!";
		$res["message"] = "";
		$res["data"] = $data;

		echo json_encode($res);
    }

    public function postGet_merchant()
    {
        $id_user = cek_session_login();
        $db = db_connect();
        $request = request();
        $dataPost = $request->getPost();

        $data = $db->table('users u')->select('*')
        ->join('profiles p', 'p.id_user = u.id_user', 'left')
        ->join('user_status us', 'us.id_user_status = u.user_status', 'left')
        ->where('u.user_role', 1)->where('u.id_user', $dataPost['id_user'])->get()->getRow();

        
        $data2 = $db->table('user_status')->get()->getResult();

        $db->close();

		$res["status"] 	= $data ? "000" : "001";
		$res["error"] 	= $data ? "" : "Merchant is not finished register process!";
		$res["message"] = "";
		$res["data"] = $data;
		$res["user_status"] = $data2;

		echo json_encode($res);
    }

    public function postGet_user()
    {
        $id_user = cek_session_login();
        $db = db_connect();

        $data = $db->table('users u')->select('*')
        ->join('profiles p', 'p.id_user = u.id_user', 'left')
        ->where('u.id_user', $id_user)->get()->getRow();

        $db->close();

		$res["status"] 	= $data ? "000" : "001";
		$res["error"] 	= $data ? "" : "Error DB when Change Password Merchant!";
		$res["message"] = "";
		$res["data"] = $data;

		echo json_encode($res);
    }

    public function postValidation()
    {
        $id_user = cek_session_login();
        $request = request();
        $dataPost = $request->getPost();
        $db = db_connect();

        if (isset($dataPost['password'])) {
            $dataPost['password'] = hash('sha256', hash('sha256', $dataPost['password']));
        }
        $data = $db->table('users')->where($dataPost)->get()->getRow();

		$res["status"] 	= $data ? "000" : "001";
		$res["error"] 	= $data ? "Data found." : "Data is not found!";
		$res["message"] = "";
		$res["data"] = $data;
        $db->close();

		echo json_encode($res);
    }

    public function postValidation_no_token()
    {
        $request = request();
        $dataPost = $request->getPost();
        $db = db_connect();

        $action = $dataPost['action'] ?? null;
        $type = $dataPost['type'] ?? null;
        unset($dataPost['action']);
        unset($dataPost['type']);

        if (isset($dataPost['password'])) {
            $dataPost['password'] = hash('sha256', hash('sha256', $dataPost['password']));
        }
        $data = $db->table('users')->where($dataPost)->get()->getRow();

        $appName = ($type == 'biller') ? getenv('APP_NAME') : getenv('APP_BILLER_NAME');
        $appURL = ($type == 'biller') ? getenv('APP_ADMIN_URL') : getenv('APP_BILLER_URL');
        $appMailTitle = ($type == 'biller') ? "Change Password Biller" : "Change Password Merchant";
        
        if ($data && $action && $action === 'cp') {
            $body = '<html>
            <h1>Dear '.$dataPost['email'].',</h1>
        
            <p>Welcome to&nbsp;'.$appName.' - Administration Portal. To change password your account, please click the following link:</p>
        
            <p>&nbsp;</p>
        
            <p><a href="'.$appURL.'auth/change-password/new-password?token='.base64_encode($dataPost['email']).'" style="font-size: 15px;line-height: 15px;color: #fff;background: #00a2db;text-decoration: none;padding: 12px 28px;margin: 18px 0;" target="_blank"> Change Password </a></p>
        
            <p>&nbsp;</p>
        
            <p>If you did not request a change password, please contact us at&nbsp;<a href="mailto:'.getenv('APP_MAIL').'" target="_blank">'.getenv('APP_MAIL').'</a></p>
        
            <p>thanks,<br />
            '.$appName.'</p>
        
            </html>';
            sendMail($dataPost['email'], $appMailTitle, $body);
        }

		$res["status"] 	= $data ? "000" : "001";
		$res["error"] 	= $data ? "" : "Data is not found!";
		$res["message"] = $data ? "Data found." : "";
		$res["data"] = $data;
        $db->close();

		echo json_encode($res);
    }

    public function postRegister()
    {
        $request = request();
        $dataPost = $request->getPost();
        $db = db_connect();

        if (isset($dataPost['password'])) {
            $dataPost['password'] = hash('sha256', hash('sha256', $dataPost['password']));
        }

        $sourceXenditBCA = '{
            "external_id": "va-biller-bca-'.$dataPost['email'].'",
            "bank_code": "BCA",
            "name": "'.getenv('XENDIT_VA_NAME').'"
        }';

        $dataXendit1 = create_va($sourceXenditBCA, true);
        $va_bca_number = $dataXendit1['account_number'];

        $sourceXenditBNI = '{
            "external_id": "va-biller-other-'.$dataPost['email'].'",
            "bank_code": "BNI",
            "name": "'.getenv('XENDIT_VA_NAME').'"
        }';

        $dataXendit2 = create_va($sourceXenditBNI, true);
        $va_other_number = $dataXendit2['account_number'];
        
        $dataPost['token'] = hash('sha256', hash('sha256', $dataPost['email']));
        $dataPost['va_bca_number'] = $va_bca_number;
        $dataPost['va_other_number'] = $va_other_number;
        $dataPost['external_id_va_bca'] = "va-biller-bca-".$dataPost['email'];
        $dataPost['external_id_va_other'] = "va-biller-other-".$dataPost['email'];
        $dataPost['external_id_credit'] = "va-biller-credit-".$dataPost['email'];
        // print_r($dataPost);

        $data = $db->table('users')->insert($dataPost);
        $id_user = $db->table('users')->where($dataPost)->orderBy('id_user', 'desc')->get()->getRow()->id_user;

        $db->query('INSERT INTO products_pricing_merchant (id_product, id_user) SELECT id_product, '.$id_user.' FROM products;');

        $body = '<html>
        <h1>Dear '.$dataPost['email'].',</h1>
    
        <p>Welcome to&nbsp;'.getenv('APP_NAME').' Administration Portal. To activate your account, please click the following link:</p>
    
        <p>&nbsp;</p>
    
        <p><a href="'.getenv('APP_ADMIN_URL').'auth/login?token='.base64_encode($dataPost['email']).'" style="font-size: 15px;line-height: 15px;color: #fff;background: #00a2db;text-decoration: none;padding: 12px 28px;margin: 18px 0;" target="_blank"> Activate My Account </a></p>
    
        <p>&nbsp;</p>
    
        <p>For further information, please contact us at&nbsp;<a href="mailto:'.getenv('APP_MAIL').'" target="_blank">'.getenv('APP_MAIL').'</a></p>
    
        <p>thanks,<br />
        '.getenv('APP_NAME').'</p>
    
        </html>';
        sendMail($dataPost['email'], "Confirmation Instructions", $body);

        $db->close();
		$res["status"] 	= $data ? "000" : "001";
		$res["error"] 	= $data ? "" : "Error DB when create new User merchant!";
		$res["message"] = "";
		$res["data"] = $data;

		echo json_encode($res);
    }

    public function postRegister_profile()
    {
        $request = request();
        $dataPost = $request->getPost();
        $db = db_connect();

        $data = $db->table('profiles')->insert($dataPost);

		$res["status"] 	= $data ? "000" : "001";
		$res["error"] 	= $data ? "" : "Error DB when create new Profile merchant!";
		$res["message"] = "";
		$res["data"] = $data;
        $db->close();

		echo json_encode($res);
    }

    public function postActivation()
    {
        // print_r($email);
        $request = request();
        $dataPost = $request->getPost();
        $db = db_connect();

        $update['is_active'] = 1;
        $db->table('users')->where('email', base64_decode($dataPost['token']))->ignore()->update($update);

        $data = $db->table('users u')->select('*')
        ->join('profiles p', 'p.id_user = u.id_user', 'left')
        ->where('email', base64_decode($dataPost['token']))->get()->getRow();

        $db->close();

		$res["status"] 	= "000";
		$res["error"] 	= "";
		$res["message"] = "";
		$res["data"] = $data;

		echo json_encode($res);
    }

    public function postCreate_profile()
    {
        $request = request();
        $dataPost = $request->getPost();
        $db = db_connect();

        $insert = $dataPost;

        $ktpCardFileName = upload_file_custom($request, 'image_id_card');
        $npwpCardFileName = upload_file_custom($request, 'image_npwp');

        if ($request->getFile('image_bussiness_npwp')) {
            $bussinessNpwpFileName = upload_file_custom($request, 'image_bussiness_npwp');
            $bussinessAktaFileName = upload_file_custom($request, 'image_bussiness_akta');
            $bussinessNibFileName = upload_file_custom($request, 'image_bussiness_nib');
            $bussinessSkFileName = upload_file_custom($request, 'image_bussiness_sk');
            $insert['image_bussiness_npwp'] = $bussinessNpwpFileName;
            $insert['image_bussiness_akta'] = $bussinessAktaFileName;
            $insert['image_bussiness_nib'] = $bussinessNibFileName;
            $insert['image_bussiness_sk'] = $bussinessSkFileName;
            $update['user_category'] = 2;
        }

        $id_user = $db->table('users')->where('email', $dataPost['email'])->get()->getRow()->id_user;
        $insert['id_user'] = $id_user;
        $insert['image_id_card'] = $ktpCardFileName;
        $insert['image_npwp'] = $npwpCardFileName;
        unset($insert['email']);

        $db->table('profiles')->ignore()->upsert($insert);

        $update['token'] = hash('sha256', hash('sha256', $dataPost['email'].date('Y-m-d H:i:s')));
        $db->table('users u')->where('email', $dataPost['email'])->update($update);

        $data = $db->table('users u')->select('*')
        ->join('profiles p', 'p.id_user = u.id_user', 'left')
        ->where($dataPost)->where('u.email', $dataPost['email'])->get()->getRow();

        $db->close();

		$res["status"] 	= "000";
		$res["error"] 	= "";
		$res["message"] = "";
		$res["data"] = $data;

		echo json_encode($res);
    }

    public function postChange_password()
    {
        // print_r($email);
        $request = request();
        $dataPost = $request->getPost();
        $db = db_connect();

        if (isset($dataPost['password'])) {
            $update['password'] = hash('sha256', hash('sha256', $dataPost['password']));
        }
        if (isset($dataPost['token'])) {
            $db->table('users')->where('email', base64_decode($dataPost['token']))->ignore()->update($update);

            $data = $db->table('users u')->select('*')
            ->join('profiles p', 'p.id_user = u.id_user', 'left')
            ->where('u.email', base64_decode($dataPost['token']))->get()->getRow();
        } else {
            $id_user = cek_session_login();
            $db->table('users')->where('id_user', $id_user)->ignore()->update($update);

            $data = $db->table('users u')->select('*')
            ->join('profiles p', 'p.id_user = u.id_user', 'left')
            ->where('u.id_user', $id_user)->get()->getRow();
        }

        $db->close();

		$res["status"] 	= $data ? "000" : "001";
		$res["error"] 	= $data ? "" : "Error DB when Change Password Merchant!";
		$res["message"] = $data ? "Successfuly changed your password" : "";
		$res["data"] = $data;

		echo json_encode($res);
    }

    public function postGet_bank_account()
    {
        $id_user = cek_session_login();
        $db = db_connect();

        $data = $db->table('users u')->select('*')
        ->join('profiles p', 'p.id_user = u.id_user', 'left')
        ->where('u.id_user', $id_user)->get()->getRow();

        $data2 = $db->table('bank_names')->where('is_active', '1')->get()->getResult();

        $db->close();

		$res["status"] 	= ($data && $data2) ? "000" : "001";
		$res["error"] 	= ($data && $data2) ? "" : "Error DB when Change Password Merchant!";
		$res["message"] = "";
		$res["data"]['user'] = $data;
		$res["data"]['banks'] = $data2;

		echo json_encode($res);
    }

    public function postChange_bank_account()
    {
        $request = request();
        $id_user = cek_session_login();
        $dataPost = $request->getPost();
        $db = db_connect();
        
        $db->table('users')->where('id_user', $id_user)->ignore()->update($dataPost);

        $data = $db->table('users u')->select('*')
        ->join('profiles p', 'p.id_user = u.id_user', 'left')
        ->where('u.id_user', $id_user)->get()->getRow();

        $data2 = $db->table('bank_names')->where('is_active', '1')->get()->getResult();

        $db->close();

		$res["status"] 	= ($data && $data2) ? "000" : "001";
		$res["error"] 	= ($data && $data2) ? "" : "Error DB when Change Password Merchant!";
		$res["message"] = ($data && $data2) ? "Bank Account has been updated." : "";
		$res["data"]['user'] = $data;
		$res["data"]['banks'] = $data2;

		echo json_encode($res);
    }

    public function postGet_user_status()
    {
        $request = request();
        $db = db_connect();

        $data = $db->table('user_status')->get()->getResult();
        $db->close();

		$res["status"] 	= $data ? "000" : "001";
		$res["error"] 	= $data ? "" : "Error DB!";
		$res["message"] = "";
		$res["data"] = $data;

		echo json_encode($res);
    }

    public function postUpdate_status_merchant()
    {
        $id_user = cek_session_login();
        $request = request();
        $dataPost = $request->getPost();
        $db = db_connect();
        
        $db->table('users')->where('id_user', $dataPost['id_user'])->ignore()->update($dataPost);

        $db->close();

		$res["status"] 	= "000";
		$res["error"] 	= "";
		$res["message"] = "Merchant Status has been updated.";

		echo json_encode($res);
    }
}
