<?php

    function biller_get_products($source=false) {
        $db = db_connect();
        
        $id_biller = $db->table('biller_list_setting')->where('is_used', '1')->get()->getRow()->id_biller;
        
        $db->close();
        
        if ($id_biller === '1' || $id_biller === 1) {
            return digiflazz_get_products($source);
        }
    }

    function biller_get_balance($source=false) {
        $db = db_connect();
        
        $id_biller = $db->table('biller_list_setting')->where('is_used', '1')->get()->getRow()->id_biller;
        
        $db->close();
        
        if ($id_biller === '1' || $id_biller === 1) {
            return digiflazz_get_balance($source);
        }
    }

    function biller_topup($source=false) {
        $db = db_connect();
        
        $id_biller = $db->table('biller_list_setting')->where('is_used', '1')->get()->getRow()->id_biller;
        
        $db->close();
        
        if ($id_biller === '1' || $id_biller === 1) {
            return digiflazz_topup($source);
        }
    }
    
?>