<?php 

    use PHPMailer\PHPMailer\PHPMailer;
    use PHPMailer\PHPMailer\SMTP;
    use PHPMailer\PHPMailer\Exception;

    //Load Composer's autoloader
    require '../vendor/autoload.php';

    date_default_timezone_set("Asia/Bangkok");

    function cek_session_login() {        
        $request = request();
        $session = session();

        if($request->hasHeader('Authorization')) {
            $db = db_connect();
            $tokenLogin = $request->header('Authorization')->getValue();
            // $subdo = explode('.', explode('://', $_SERVER['HTTP_ORIGIN'])[1])[0];
            $builder = $db->table('users')->where('token', $tokenLogin);
            $dataUser = $builder->get()->getRow();
            $db->close();
            if ($dataUser) {
                return $dataUser->id_user;
                // $user = $dataUser;
                // $session->set('login', $user);
                // $session->set('token_login', $user->token_login);
                // $session->set('token_api', $user->token_api);
            } else {
                echo '{
                    "code": 1,
                    "error": "Token is not valid!",
                    "message": "Token is not valid!",
                    "data": null
                }';
                exit();
            }
        } else {
            echo '{
                "code": 1,
                "error": "Token is not valid!",
                "message": "Token is not valid!",
                "data": null
            }';
            exit();
        }
    }

    function cek_subdomain() {        
        $request = request();
        $session = session();

        return 37;
        die();
        // print_r($request->header('Origin')->getValue());
        // die();
        if($request->header('Origin')->getValue()) {
            $db = db_connect();
            // $tokenLogin = $request->header('Authorization')->getValue();
            $subdo = explode('.', explode('://', $request->header('Origin')->getValue())[1])[0];
            $builder = $db->table('users')->where('subdomain', $subdo);
            $dataUser = $builder->get()->getRow();
            $db->close();
            if ($dataUser) {
                return $dataUser->id_user;
                // $user = $dataUser;
                // $session->set('login', $user);
                // $session->set('token_login', $user->token_login);
                // $session->set('token_api', $user->token_api);
            } else {
                echo '{
                    "code": 1,
                    "error": "Token is not valid!",
                    "message": "Token is not valid!",
                    "data": null
                }';
                exit();
            }
        } else {
            echo '{
                "code": 1,
                "error": "Token is not valid!",
                "message": "Token is not valid!",
                "data": null
            }';
            exit();
        }
    }

    function cek_session_user() {        
        $request = request();
        $session = session();

        if($request->hasHeader('Authorization')) {
            $db = db_connect();
            // $tokenLogin = $request->header('Authorization')->getValue();
            $subdo = explode('.', explode('://', $_SERVER['HTTP_ORIGIN'])[1])[0];
            $builder = $db->table('users')->where('subdomain', $subdo);
            $dataUser = $builder->get()->getRow();
            $db->close();
            if ($dataUser) {
                return $dataUser->id_user;
                // $user = $dataUser;
                // $session->set('login', $user);
                // $session->set('token_login', $user->token_login);
                // $session->set('token_api', $user->token_api);
            } else {
                echo '{
                    "code": 1,
                    "error": "Token is not valid!",
                    "message": "Token is not valid!",
                    "data": null
                }';
                exit();
            }
        } else {
            echo '{
                "code": 1,
                "error": "Token is not valid!",
                "message": "Token is not valid!",
                "data": null
            }';
            exit();
        }
    }

    function format_rupiah($angka) {
        $rupiah=number_format($angka,0,',','.');
        return $rupiah;
    }

    function curl($url, $isPost=false, $postFields=false, $headers=false) {
        set_time_limit(15);
        ignore_user_abort(false);
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, $isPost);
        if ($isPost) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $postFields);
        }

        // In real life you should use something like:
        // curl_setopt($ch, CURLOPT_POSTFIELDS, 
        //          http_build_query(array('postvar1' => 'value1')));
        if ($headers) {
            // print_r($headers);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        }

        // Receive server response ...
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 0); 
        curl_setopt($ch, CURLOPT_NOSIGNAL, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10); //timeout in seconds
        // curl_setopt($ch, CURLOPT_TIMEOUT_MS, 200);

        curl_setopt($ch, CURLINFO_HEADER_OUT, true); // enable tracking
        $server_output = curl_exec($ch);

        // $headerSent = curl_getinfo($ch, CURLINFO_HEADER_OUT );
        // print_r($headerSent);
        // $header_size = curl_getinfo($curl, CURLINFO_HEADER_SIZE);
        // print_r($header_size);

        curl_close($ch);

        return $server_output;
        // Further processing ...
        // if ($server_output == "OK") { ... } else { ... }
    }

    function curlFlip($_url, $postArray=false, $isSandBox=false) {
        $url = $isSandBox ? 'https://bigflip.id/big_sandbox_api/v2/'.$_url : 'https://bigflip.id/api/v2/'.$_url;

        set_time_limit(15);
        ignore_user_abort(false);
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        if ($postArray) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postArray));
        }

        
        // Add header in your disbursement request
        $secret_key = getenv('FLIP_SECRET_KEY');
        $headers = ["Content-Type: application/x-www-form-urlencoded"];
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_USERPWD, $secret_key.":");

        // Receive server response ...
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 0); 
        curl_setopt($ch, CURLOPT_NOSIGNAL, 1);
        curl_setopt($ch, CURLOPT_HEADER, FALSE);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10); //timeout in seconds
        // curl_setopt($ch, CURLOPT_TIMEOUT_MS, 200);

        // curl_setopt($ch, CURLINFO_HEADER_OUT, true); // enable tracking
        $server_output = curl_exec($ch);

        // $headerSent = curl_getinfo($ch, CURLINFO_HEADER_OUT );
        // print_r($headerSent);
        // $header_size = curl_getinfo($curl, CURLINFO_HEADER_SIZE);
        // print_r($header_size);

        curl_close($ch);

        return json_decode($server_output, true);
        // Further processing ...
        // if ($server_output == "OK") { ... } else { ... }
    }

    function upload_file($_request)
    {   
        $file = $_request->getFile('userfile');
        $validationRule = [
            'userfile' => [
                'label' => 'Image File',
                'rules' => [
                    'uploaded[userfile]',
                    'is_image[userfile]',
                    'mime_in[userfile,image/jpg,image/jpeg,image/gif,image/png,image/webp]',
                    'max_size[userfile,100]',
                    'max_dims[userfile,1024,768]',
                ],
            ],
        ];
        if ($file->getSizeByUnit('mb') > 2) {
            return ['errors' => "File size must < 2mb!"];
        }
        if (
            $file->getMimeType() !== 'image/jpg' &&
            $file->getMimeType() !== 'image/jpeg' &&
            $file->getMimeType() !== 'image/png' &&
            $file->getMimeType() !== 'image/webp'
            ) {
            return ['errors' => "File type must an image!"];
        }

        $newName = $file->getRandomName();
        $x = $file->move(ROOTPATH  . 'public/images', $newName);
       
        $data = ['name' => '/images/'.$newName];
        return $data;
        // return view('upload_form', $data);
    }

    function upload_file_custom($_request, $fileXname)
    {   
        $file = $_request->getFile($fileXname);
        $validationRule = [
            $fileXname => [
                'label' => 'Image File',
                'rules' => [
                    'uploaded['.$fileXname.']',
                    'is_image['.$fileXname.']',
                    'mime_in['.$fileXname.',image/jpg,image/jpeg,image/gif,image/png,image/webp]',
                    'max_size['.$fileXname.',100]',
                    'max_dims['.$fileXname.',1024,768]',
                ],
            ],
        ];
        if ($file->getSizeByUnit('mb') > 2) {
            return ['errors' => "File size must < 2mb!"];
        }
        if (
            $file->getMimeType() !== 'image/jpg' &&
            $file->getMimeType() !== 'image/jpeg' &&
            $file->getMimeType() !== 'image/png' &&
            $file->getMimeType() !== 'image/webp'
            ) {
            return ['errors' => "File type must an image!"];
        }

        $newName = $file->getRandomName();
        $x = $file->move(ROOTPATH  . 'public/images', $newName);
       
        $data = '/images/'.$newName;
        return $data;
        // return view('upload_form', $data);
    }

    function create_random_captcha() {
        $seed = str_split('abcdefghijklmnopqrstuvwxyz'
                 .'ABCDEFGHIJKLMNOPQRSTUVWXYZ'
                 .'0123456789'); // and any other characters
        shuffle($seed); // probably optional since array_is randomized; this may be redundant
        $rand = '';
        foreach (array_rand($seed, 10) as $k) $rand .= $seed[$k];
        return strtoupper($rand);
    }

    function create_random_id() {
        $seed = str_split('abcdefghijklmnopqrstuvwxyz'
                 .'ABCDEFGHIJKLMNOPQRSTUVWXYZ'
                 .'0123456789'); // and any other characters
        shuffle($seed); // probably optional since array_is randomized; this may be redundant
        $rand = '';
        foreach (array_rand($seed, 3) as $k) $rand .= $seed[$k];
        return strtoupper($rand);
    }
    
    function getUserIP()
    {
        // Get real visitor IP behind CloudFlare network
        if (isset($_SERVER["HTTP_CF_CONNECTING_IP"])) {
                $_SERVER['REMOTE_ADDR'] = $_SERVER["HTTP_CF_CONNECTING_IP"];
                $_SERVER['HTTP_CLIENT_IP'] = $_SERVER["HTTP_CF_CONNECTING_IP"];
        }
        $client  = @$_SERVER['HTTP_CLIENT_IP'];
        $forward = @$_SERVER['HTTP_X_FORWARDED_FOR'];
        $remote  = $_SERVER['REMOTE_ADDR'];

        if(filter_var($client, FILTER_VALIDATE_IP))
        {
            $ip = $client;
        }
        elseif(filter_var($forward, FILTER_VALIDATE_IP))
        {
            $ip = $forward;
        }
        else
        {
            $ip = $remote;
        }

        return $ip;
    }

    
    function sendMail0($to=false, $subject, $message) {         
        $email = \Config\Services::email();
        if ($to) {
            $email->setTo($to);
        } else {
            $email->setTo(getenv('SMTP_USER'));
        }
        $email->setFrom(getenv('SMTP_USER'), getenv('SMTP_NAME'));
        
        $email->setSubject($subject);
        $email->setMessage($message);
        if ($email->send()) 
		{
            echo 'Email successfully sent';
        } 
		else 
		{
            $data = $email->printDebugger(['headers']);
            print_r($data);
        }
    }

    
    function sendMail($to=false, $subject, $message) {   
        //Create an instance; passing `true` enables exceptions
        $mail = new PHPMailer(true);
        
        try {
            //Server settings
            // $mail->SMTPDebug = SMTP::DEBUG_SERVER;                      //Enable verbose debug output
            $mail->isSMTP();                                            //Send using SMTP
            $mail->Host       = getenv('SMTP_HOST');                     //Set the SMTP server to send through
            $mail->SMTPAuth   = true;                                   //Enable SMTP authentication
            $mail->Username   = getenv('SMTP_USER');                     //SMTP username
            $mail->Password   = getenv('SMTP_PASS');                               //SMTP password
            // $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;            //Enable implicit TLS encryption
            $mail->SMTPSecure = getenv('SMTP_TLS');            //Enable implicit TLS encryption
            $mail->Port       = getenv('SMTP_PORT');                                    //TCP port to connect to; use 587 if you have set `SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS`
        
            //Recipients
            $mail->setFrom(getenv('SMTP_USER'), getenv('SMTP_NAME'));
            $mail->addReplyTo(getenv('SMTP_USER'), getenv('SMTP_NAME'));

            if ($to) {
                $mail->addAddress($to, 'Merchant User');     //Add a recipient
            } else {
                $mail->addAddress(getenv('SMTP_USER'), 'Merchant User');     //Add a recipient
            }
            // $mail->addAddress('ellen@example.com');               //Name is optional

            // $mail->addCC('cc@example.com');
            // $mail->addBCC('bcc@example.com');
        
            //Attachments
            // $mail->addAttachment('/var/tmp/file.tar.gz');         //Add attachments
            // $mail->addAttachment('/tmp/image.jpg', 'new.jpg');    //Optional name
        
            //Content
            $mail->isHTML(true);                                  //Set email format to HTML
            $mail->Subject = $subject;
            $mail->Body    = $message;
            // $mail->AltBody = $message;
        
            $mail->send();
            // echo 'Message has been sent';
        } catch (Exception $e) {
            // echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
        }
    }


?>