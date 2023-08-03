<?php

namespace App\Controllers\Callbacks;

class Xendit extends BaseController
{
    public function index()
    {
		echo 'Access denied!'; 
    }

    public function postPaid_va()
    {
        $request = request();
        $res = $request->getJSON(true);
        $db = db_connect();
        print_r('----- RES Callback Xendit ----------');
        print_r($res);

        $message = ""; 
        $dataTransaction = $db->table('transactions')->where('external_id', $res['external_id'])->get()->getRow();
        if ($dataTransaction) {
            $user = $db->table('users')->where('id_user', $dataTransaction->id_user)->get()->getRow();
            $updateTransaction['status_payment'] = 2;
            $db->table('transactions')->where('external_id', $res['external_id'])->ignore()->update($updateTransaction);
            $res['fee'] = $db->table('merchant_pricing')->where('pricing_name ', 'user_payment')->get()->getRow()->pricing_amount;
            $message = "Transaction is Paid";

            $source = '{
                "ref_id": "'.$res['external_id'].'",
                "buyer_sku_code": "'.$dataTransaction->sku_code.'",
                "customer_no": "'.$dataTransaction->target_account_number.'"
            }';
    
            $resX = biller_topup($source, true);
            print_r('----- RES TUPOP DIGIFLAZZ ----------');
            print_r($resX);

            $body = '<html>
            <h1>Dear '.$user->email.',</h1>
        
            <p>Your transaction has been PAID with Invoice Number <b>'.$res['external_id'].'</b></p>
        
            <p>&nbsp;</p>
        
            <p><a href="https://'.$user->subdomain.'.'.getenv('APP_DOMAIN_URL').'transaction" style="font-size: 15px;line-height: 15px;color: #fff;background: #00a2db;text-decoration: none;padding: 12px 28px;margin: 18px 0;" target="_blank"> My History </a></p>
        
            <p>&nbsp;</p>
        
            <p>For further information, please contact us at&nbsp;<a href="mailto:'.getenv('APP_MAIL').'" target="_blank">'.getenv('APP_MAIL').'</a></p>
        
            <p>thanks,<br />
            '.getenv('APP_NAME').'</p>
        
            </html>';
            sendMail($user->email, "Transaction Paid ".$res['external_id'], $body);
            
        } else {
            $message = "No data.";
        }

        $res['transaction_created'] = $res['created'];
        $res['transaction_updated'] = $res['updated'];
        unset($res['created']);
        unset($res['updated']);
        $db->table('pg_va_callback')->ignore()->insert($res);

        echo $message;
        echo "\n\n";
        print_r($res);

        $db->close();
    }

    public function postPaid_retail()
    {
        $request = request();
        $res = $request->getJSON(true);
        $db = db_connect();

        $dataTransaction = $db->table('transactions')->where('external_id', $res['external_id'])->get()->getRow();
        if ($dataTransaction) {
            $updateTransaction['status_payment'] = 2;
            $db->table('transactions')->where('external_id', $res['external_id'])->ignore()->update($updateTransaction);
            $message = "Transaction is Paid";
        } else {
            $message = "No data.";
        }

        $res['fee'] = $db->table('merchant_pricing')->where('pricing_name ', 'user_payment')->get()->getRow()->pricing_amount;
        $db->table('pg_retail_callback')->ignore()->insert($res);

        echo $message;
        echo "\n\n";
        print_r($res);

        $db->close();
    }

    public function postSent_disbursement()
    {
        // $id_user = cek_session_login();
        $request = request();
        $res = $request->getJSON(true);
        $db = db_connect();
        
        $res['fee'] = $db->table('merchant_pricing')->where('pricing_name ', 'withdraw')->get()->getRow()->pricing_amount;
        
        $dataWdBiller = $db->table('withdraw_biller')->where('external_id ', $res['external_id'])->get()->getRow();
        if ($dataWdBiller) {
            $update['status'] = 'SUCCEEDED';
            $db->table('withdraw_biller')->where('external_id', $res['external_id'])->ignore()->update($update);
        } else {
            $dataWdMerchant = $db->table('withdraw_merchant')->where('external_id ', $res['external_id'])->get()->getRow();
            if ($dataWdMerchant) {
                $update['status'] = 'SUCCEEDED';
                $db->table('withdraw_merchant')->where('external_id', $res['external_id'])->ignore()->update($update);
            }
        }

        unset($res['email_to']);
        unset($res['email_cc']);
        unset($res['email_bcc']);
        $db->table('pg_disbursement_callback')->ignore()->insert($res);

        $db->close();
    }

    public function postStatus_va()
    {
        // $id_user = cek_session_login();
        $request = request();
        $res = $request->getJSON(true);
        $db = db_connect();
        
        $dataTransaction = $db->table('transactions')->where('external_id', $res['external_id'])->get()->getRow();
        if ($dataTransaction) {
            if ($res['status'] == 'PENDING') {
                $updateTransaction['status_payment'] = 1;
            } elseif ($res['status'] == 'INACTIVE') {
                $updateTransaction['status_payment'] = 2;
            }
            $db->table('transactions')->where('external_id', $res['external_id'])->ignore()->update($updateTransaction);
            // $res['fee'] = $db->table('merchant_pricing')->where('pricing_name ', 'user_payment')->get()->getRow()->pricing_amount;
            $message = "Transaction is Paid";
        } else {
            $message = "No data.";
        }

        $db->close();
    }

    public function postStatus_retail()
    {
        // $id_user = cek_session_login();
        $request = request();
        $res = $request->getJSON(true);
        print_r($res);
        $db = db_connect();

        $db->close();
    }

    public function postStatus_disbursement()
    {
        $request = request();
        $res = $request->getJSON(true);
        $db = db_connect();



        $db->close();
    }
}
