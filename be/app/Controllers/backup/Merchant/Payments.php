<?php

namespace App\Controllers\Merchant;

class Payments extends BaseController
{
    public function postAdd()
    {
        $id_user = cek_subdomain();
        $date = str_replace(' ', 'T', date('Y-m-d H:i:s',strtotime('+24 hours'))).'.000Z';
        $request = request();
        $datas = $request->getPost();
        $random = create_random_id();
        $id_external = date('ymd').$datas['id_user'].$random;
        $db = db_connect();
        // print_r($id_external); die();
        $getProduct = $db->table('merchant_products')
        ->where('id_product', $datas['id_product'])
        ->get()->getRow();

        // $getPricing = $db->table('merchant_pricing')
        // ->where('pricing_name', "user_payment")
        // ->get()->getRow()->pricing_amount;
        
        $billerPercen = $getProduct->biller_commission_percent / 100;
        $percenMerchant = $getProduct->merchant_commission_percent/100;
        $biller = ($billerPercen * $getProduct->price)+$getProduct->biller_commission_amount;
        $merchant = ($percenMerchant *$getProduct->final_price_merchant)+ $getProduct->merchant_commission_amount;
        $fee = $db->table('pg_list_setting')->where('id_user', $id_user)->where('is_used', '1')->get()->getRow()->fee_va;
        $ammount = $getProduct->price + $biller + $merchant + $fee;

        $sourceXenditVA = '{
            "external_id": "'.$id_external.'",
            "bank_code": "'.$datas['bank'].'",
            "name": "'.getenv('XENDIT_VA_NAME').'",
            "is_closed": true,
            "is_single_use": true,
            "expected_amount": "'.$ammount.'",
            "expiration_date": "'.$date.'"
        }';
        
        // $headersXendit = [
        //     'Accept: application/json',
        //     'Content-Type: application/json',
        //     'Authorization: '.getenv('XENDIT_AUTH'),
        // ];
        
        // $dataXendit1 = json_decode(curl(getenv('XENDIT_DOMAIN').'/callback_virtual_accounts', true, $sourceXenditVA, $headersXendit), true);
        $dataXendit1 = create_va($sourceXenditVA, false, true);
        // print_r($dataXendit1); die();
        
        
        
        $data = [
                "invoice_number" => $id_external,
                "invoice_number_retry" => $id_external,
                "id_user" => $datas['id_user'],
                "id_customer" => $request->getUserAgent(),
                "email" => $datas['email'],
                "sku_code" => $getProduct->buyer_sku_code,
                "id_product" => $datas['id_product'],
                "target_account_number" => $datas['target_account_number'],
                "ammount" => $ammount,
                "merchant_commission" => $merchant,
                "biller_commission" => $biller,
                "id_biller" => 1,
                "time_transaction" => date('Y-m-d H:i:s'),
                "time_transaction_success" => null,
                "time_transaction_failed" => null,
                "note_transaction" => null,
                "status_transaction" => 1,
                "id_payment_method" => 1,
                "trx_id" => $dataXendit1['id'],
                "external_id" => $id_external,
                "status_payment" => 1,
                "fee" => $dataXendit1['fee']
            ];
            
        $data = $db->table('transactions')->insert($data);

        $user = $db->table('users')->where('id_user', $datas['id_user'])->get()->getRow();
        $body = '<html>
        <h1>Dear '.$datas['email'].',</h1>
    
        <p>Your transaction has been created with Invoice Number <b>'.$id_external.'</b></p>
        <p>You have to pay <b>'.number_format((string)$ammount).'</b> to Virtual Account '.$datas['bank'].' Number <b>'.chunk_split($dataXendit1['account_number'], 4, ' ').'</b></p>
    
        <p>&nbsp;</p>
    
        <p><a href="https://'.$user->subdomain.'.'.getenv('APP_DOMAIN_URL').'transaction" style="font-size: 15px;line-height: 15px;color: #fff;background: #00a2db;text-decoration: none;padding: 12px 28px;margin: 18px 0;" target="_blank"> My History </a></p>
    
        <p>&nbsp;</p>
    
        <p>For further information, please contact us at&nbsp;<a href="mailto:'.getenv('APP_MAIL').'" target="_blank">'.getenv('APP_MAIL').'</a></p>
    
        <p>thanks,<br />
        '.getenv('APP_NAME').'</p>
    
        </html>';
        sendMail($datas['email'], "Payment Instructions for ".$id_external, $body);

        $res["status"] 	= $data ? "000" : "001";
		$res["error"] 	= $data ? "" : "Error DB when create transaction!";
		$res["message"] = $dataXendit1['id'];
		$res["data"] = $data;
        $db->close();

		echo json_encode($res);
    }

    public function postDetail()
    {
        $id_user = cek_subdomain();
        $request = request();
        $datas = $request->getPost();
        $id = $datas['id'];

        // $api_key = $db->table('pg_list_setting')->where('id_user', $id_user)->get()->getRow()->api_key;
        
        // $headersXendit = [
        //     'Accept: application/json',
        //     'Content-Type: application/json',
        //     'Authorization: Basic '.base64_encode($api_key.':'),
        // ];
        
        // $dataXendit1 = json_decode(curl(getenv('XENDIT_DOMAIN').'/callback_virtual_accounts/'.$id, false, false, $headersXendit), true);
        $dataXendit1 = get_va($id, false, true);
        // print_r($dataXendit1); 
        // die();
        $res["status"] 	= "000";
		$res["error"] 	= "";
		$res["message"] = $dataXendit1;
        
        echo json_encode($res);
    }

    public function postHistory()
    {
        $id_user = cek_subdomain();
        $request = request();
        $datas = $request->getPost();

        $db = db_connect();

        $data = $db->table('transactions p')
                ->join('merchant_products c', 'c.id_product=p.id_product', 'left')
                ->where('p.external_id', $datas['search'])
                ->orWhere('p.target_account_number', $datas['search'])
                ->groupBy('p.id_transaction')
                ->get()->getResult();
        $db->close();
        
        $res["status"] 	= "000";
		$res["error"] 	= "";
		$res["message"] = $data;
        
        echo json_encode($res);
    }
}