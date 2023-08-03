<?php

    function get_balance($source=false, $is_admin=false, $is_user=false) {
        $db = db_connect();
        if ($is_user) {
            $id_user_x = cek_subdomain();
        } else if (!$is_user && !$is_admin){            
            $id_user_x = cek_session_login();
        }
        $id_user = $is_admin ? '0' : $id_user_x;
        
        $id_pg = $db->table('pg_list_setting')->where('id_user', $id_user)->where('is_used', '1')->get()->getRow()->id_pg;
        
        $db->close();
        
        if ($id_pg === '1' || $id_pg === 1) {
            return xendit_get_balance($source, $is_admin, $is_user, $id_user);
        }
    }

    function create_va($source, $is_admin=false, $is_user=false) {
        $db = db_connect();
        if ($is_user) {
            $id_user_x = cek_subdomain();
        } else if (!$is_user && !$is_admin){            
            $id_user_x = cek_session_login();
        }
        $id_user = $is_admin ? '0' : $id_user_x;
        
        $id_pg = $db->table('pg_list_setting')->where('id_user', $id_user)->where('is_used', '1')->get()->getRow()->id_pg;
        
        $db->close();
        
        if ($id_pg === '1' || $id_pg === 1) {
            return xendit_create_va($source, $is_admin, $is_user, $id_user);
        }
    }

    function create_retail($source, $is_admin=false, $is_user=false) {
        $db = db_connect();

        if ($is_user) {
            $id_user_x = cek_subdomain();
        } else if (!$is_user && !$is_admin){            
            $id_user_x = cek_session_login();
        }
        
        $id_user = $is_admin ? '0' : $id_user_x;
        
        $id_pg = $db->table('pg_list_setting')->where('id_user', $id_user)->where('is_used', '1')->get()->getRow()->id_pg;
        
        $db->close();
        
        if ($id_pg === '1' || $id_pg === 1) {
            return xendit_create_retail($source, $is_admin, $is_user, $id_user);
        }
    }

    function create_disbursement($source, $is_admin=false, $is_user=false) {
        $db = db_connect();

        if ($is_user) {
            $id_user_x = cek_subdomain();
        } else if (!$is_user && !$is_admin){            
            $id_user_x = cek_session_login();
        }

        $id_user = $is_admin ? '0' : $id_user_x;
        
        $id_pg = $db->table('pg_list_setting')->where('id_user', $id_user)->where('is_used', '1')->get()->getRow()->id_pg;
        
        $db->close();
        
        if ($id_pg === '1' || $id_pg === 1) {
            return xendit_create_disbursement($source, $is_admin, $is_user, $id_user);
        }
    }

    function get_va($source, $is_admin=false, $is_user=false) {
        $db = db_connect();
        if ($is_user) {
            $id_user_x = cek_subdomain();
        } else if (!$is_user && !$is_admin){            
            $id_user_x = cek_session_login();
        }
        $id_user = $is_admin ? '0' : $id_user_x;
        
        $id_pg = $db->table('pg_list_setting')->where('id_user', $id_user)->where('is_used', '1')->get()->getRow()->id_pg;
        
        $db->close();
        
        if ($id_pg === '1' || $id_pg === 1) {
            return xendit_get_va($source, $is_admin, $is_user, $id_user);
        }
    }
    
?>