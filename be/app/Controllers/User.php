<?php

namespace App\Controllers;

class User extends BaseController
{
    public function index()
    {
		  echo 'Access denied!'; 
    }

    public function postRegister()
    {
        $request = request();
        $dataPost = $request->getPost();
        $db = db_connect();

        if (isset($dataPost['password'])) {
            $dataPost['password'] = hash('sha256', hash('sha256', $dataPost['password']));
        }

        $payloads = [
          "title" => "Topup Arisan Kita",
          "type" => "MULTIPLE",
          // "amount" => 0,
          // "redirect_url" => getenv('APP_URL'),
          // "step" => 2,
          "sender_name" => $dataPost['username'],
          "sender_email" => $dataPost['email'],
      ];
        $resFlip = curlFlip('pwf/bill', $payloads, getenv('MODE'));
        $dataPost['topup_link_id'] = $resFlip['link_id'];
        $dataPost['topup_link_url'] = $resFlip['link_url'];

        // print_r($dataPost);
        $dataUser0 = $db->table('users')->where('username', $dataPost['username'])->where('email', $dataPost['email'])->get()->getRow();
        if ($dataUser0) {
          $db->close();
          $res["status"] 	= "001";
          $res["message"] = "Username or Email is already registered!";
          $res["data"] = null;
      
          echo json_encode($res);
          die();
        }

        $data = $db->table('users')->ignore()->insert($dataPost);
        // sleep(1);
        // $dataUser = $db->table('users')->orderBy('id_user', 'desc')->get()->getRow();

        $body = '<html>
        <h1>Dear '.$dataPost['email'].',</h1>
    
        <p>Welcome to&nbsp;'.getenv('APP_NAME').' Indonesia. To activate your account, please click the following link:</p>
    
        <p>&nbsp;</p>
    
        <p><a href="'.getenv('APP_URL').'auth/login?token='.base64_encode($dataPost['email']).'" style="font-size: 15px;line-height: 15px;color: #fff;background: #00a2db;text-decoration: none;padding: 12px 28px;margin: 18px 0;" target="_blank"> Activate My Account </a></p>
    
        <p>&nbsp;</p>
    
        <p>For further information, please contact us at&nbsp;<a href="mailto:'.getenv('APP_MAIL').'" target="_blank">'.getenv('APP_MAIL').'</a></p>
    
        <p>thanks,<br />
        '.getenv('APP_NAME').'</p>
    
        </html>';
        sendMail($dataPost['email'], "Confirmation Instructions", $body);

        $db->close();
        $res["status"] 	= "000";
        $res["message"] = "Check your email for activation.";
        $res["data"] = null;

        echo json_encode($res);
    }

    public function postActivation()
    {
      $request = request();
      $dataPost = $request->getPost();
      $db = db_connect();

      $update['is_active'] = 1;
      $db->table('users')->where('email', base64_decode($dataPost['token']))->ignore()->update($update);
      sleep(1);
      $data = $db->table('users')->where('email', base64_decode($dataPost['token']))->get()->getRow();

      $db->close();

      $res["status"] 	= "000";
      $res["message"] = "Your account has been activated.";
      $res["data"] = $data;

      echo json_encode($res);
    }

    public function postValidation_no_token()
    {
        $request = request();
        $dataPost = $request->getPost();
        $db = db_connect();

        $action = $dataPost['action'] ?? null;
        unset($dataPost['action']);

        if (isset($dataPost['password'])) {
            $dataPost['password'] = hash('sha256', hash('sha256', $dataPost['password']));
        }
        $data = $db->table('users')->where($dataPost)->get()->getRow();
        $db->close();

        $appName = getenv('APP_NAME');
        $appURL = getenv('APP_URL');
        $appMailTitle = "Change Password Account";
        
        if ($data && $action && $action === 'cp') {
            $body = '<html>
            <h1>Dear '.$dataPost['email'].',</h1>
        
            <p>To change password your account, please click the following link:</p>
        
            <p>&nbsp;</p>
        
            <p><a href="'.$appURL.'auth/reset-password?token='.base64_encode($dataPost['email']).'" style="font-size: 15px;line-height: 15px;color: #fff;background: #00a2db;text-decoration: none;padding: 12px 28px;margin: 18px 0;" target="_blank"> Change Password </a></p>
        
            <p>&nbsp;</p>
        
            <p>If you did not request a change password, please contact us at&nbsp;<a href="mailto:'.getenv('APP_MAIL').'" target="_blank">'.getenv('APP_MAIL').'</a></p>
        
            <p>thanks,<br />
            '.$appName.'</p>
        
            </html>';
            sendMail($dataPost['email'], $appMailTitle, $body);
        }

      $res["status"] 	= $data ? "000" : "001";
      $res["message"] = $data ? "Check your email to follow instruction." : "Data is not found!";
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
        $res["message"] = $data ? "Successfuly changed your password" : "Error DB when change your password!";
        $res["data"] = $data;

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
        $data = $db->table('users')->where($dataPost)->get()->getRow();
        $db->close();

        $res["status"] 	= $data ? "000" : "001";
        $res["message"] 	= $data ? "Login successfull." : "Email or Password incorrect!";
        $res["data"] = $data;

        echo json_encode($res);
    }

    public function postGet_data()
    {
        $request = request();
        $dataPost = $request->getPost();
        $db = db_connect();
        $id_user = cek_session_login();

        $data = $db->table('users')->where('id_user', $id_user)->get()->getRow();

        $banks = $db->table('bank_names')->select('LOWER(label) as value, bank_name as label')
        ->orderBy('label', 'asc')
        ->where('is_active ', '1')
        ->get()->getResult();

        $db->close();

        $res["status"] 	= $data ? "000" : "001";
        $res["message"] 	= $data ? "" : "Error DB!";
        $res["data"] = $data;
        $res['banks'] = $banks;

        echo json_encode($res);
    }

    public function postUpdate()
    {
        $request = request();
        $dataPost = $request->getPost();
        $db = db_connect();
        $id_user = cek_session_login();

        if (isset($dataPost['password']) && strlen($dataPost['password']) > 0) {
          $dataPost['password'] = hash('sha256', hash('sha256', $dataPost['password']));
        }

        $data = $db->table('users')->where('id_user', $id_user)->ignore()->update($dataPost);
        $db->close();

        $res["status"] 	= $data ? "000" : "001";
        $res["message"] 	= $data ? "" : "Error DB!";
        $res["data"] = $data;

        echo json_encode($res);
    }

    public function postGet_bank_account()
    {
        $request = request();
        $dataPost = $request->getPost();
        $db = db_connect();
        $id_user = cek_session_login();

        $payloads = [
          "account_number" => $dataPost['user_bank_account_number'],
          "bank_code" => $dataPost['user_bank_name'],
        ];
        $resFlip = curlFlip('disbursement/bank-account-inquiry', $payloads, getenv('MODE'));
        // print_r($resFlip);

        $dataPost['user_bank_account_name'] = $resFlip['account_holder'];
        $db->table('users')->where('id_user', $id_user)->ignore()->update($dataPost);
        sleep(1);
        $data = $db->table('users')->where('id_user', $id_user)->get()->getRow();
        $db->close();

        $res["status"] 	= ($resFlip['account_holder'] !== '') ? "000" : "001";
        $res["message"] 	= ($resFlip['account_holder'] !== '') ? "Bank Account is Valid." : "Bank Account is not Valid!";
        $res["data"] = $data;

        echo json_encode($res);
    }
}
