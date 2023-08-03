<?php

namespace App\Controllers\Biller\Callback;

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
        // print_r($res);

        $message = "";
        $dataUser = $db->table('users')->where('external_id_va_bca', $res['external_id'])->orWhere('external_id_va_other ', $res['external_id'])->get()->getRow();
        if (!$dataUser) {
            $dataTransaction = $db->table('transactions')->where('external_id', $res['external_id'])->get()->getRow();
            if ($dataTransaction) {
                $updateTransaction['status_payment'] = 2;
                $db->table('transactions')->where('external_id', $res['external_id'])->ignore()->update($updateTransaction);
                $updateStatusVA['status'] = 'PAID';
                $db->table('history_va_xendit')->where('external_id', $res['external_id'])->ignore()->update($updateStatusVA);
                $res['fee'] = $db->table('merchant_pricing')->where('pricing_name ', 'user_payment')->get()->getRow()->pricing_amount;
                $message = "Transaction is Paid";
            } else {
                $message = "No data.";
            }
        } else {
            $res['fee'] = $db->table('merchant_pricing')->where('pricing_name ', 'topup')->get()->getRow()->pricing_amount;
            $message = "Topup Merchant is Paid.";
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
