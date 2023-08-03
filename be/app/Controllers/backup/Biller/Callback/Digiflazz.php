<?php

namespace App\Controllers\Biller\Callback;

class Digiflazz extends BaseController
{
    public function index()
    {
		echo 'Access denied!'; 
    }

    public function postInbound()
    {
        $request = request();
        $res = $request->getJSON(true);
        $db = db_connect();

        $message = "No data.";
        $post_data = file_get_contents('php://input');
        $secret=getenv('DIGIFLAZZ_SECRET');
        $signature = hash_hmac('sha1', $post_data, $secret);

        if ($request->header('X-Hub-Signature')->getValue() === 'sha1='.$signature) {
            $data = $res['data'];
            $isUpdate = $request->header('X-Digiflazz-Event')->getValue() ?? '';
            if ($isUpdate == 'update') {
                $data['updated_date'] = date('Y-m-d H:i:s');
            }
            $db->table('biller_digiflazz_callback')->ignore()->upsert($data);
            
            if ($data['status'] == 'Sukses' || $data['status'] == 'sukses') {
                $updateTransaction['status_transaction'] = 1;
                
                $transaction = $db->table('transactions')->where('invoice_number', $data['ref_id'])->orWhere('invoice_number_retry', $data['ref_id'])->get()->getRow();
                $user = $db->table('users')->where('id_user', $transaction->id_user)->get()->getRow();
                $body = '<html>
                <h1>Dear '.$user->email.',</h1>
            
                <p>Your transaction has been SUCCESSFUL with Invoice Number <b>'.$transaction->invoice_number.'</b></p>
            
                <p>&nbsp;</p>
            
                <p><a href="https://'.$user->subdomain.'.'.getenv('APP_DOMAIN_URL').'transaction" style="font-size: 15px;line-height: 15px;color: #fff;background: #00a2db;text-decoration: none;padding: 12px 28px;margin: 18px 0;" target="_blank"> My History </a></p>
            
                <p>&nbsp;</p>
            
                <p>For further information, please contact us at&nbsp;<a href="mailto:'.getenv('APP_MAIL').'" target="_blank">'.getenv('APP_MAIL').'</a></p>
            
                <p>thanks,<br />
                '.getenv('APP_NAME').'</p>
            
                </html>';
                sendMail($user->email, "Transaction SUCCESSFUL ".$transaction->invoice_number, $body);
            
            } elseif ($data['status'] == 'Pending' || $data['status'] == 'pending') {
                $updateTransaction['status_transaction'] = 0;
            } else {
                $updateTransaction['status_transaction'] = 2;
            }
            $db->table('transactions')->where('invoice_number', $data['ref_id'])->orWhere('invoice_number_retry', $data['ref_id'])->ignore()->update($updateTransaction);
        }

        $db->close();
        
        echo $message;
        echo "\n\n";
        print_r($res);

    }
}
