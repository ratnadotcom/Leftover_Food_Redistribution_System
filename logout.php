<?php
require_once 'session_check.php';
if(is_logged_in()){
    session_unset();
    if(ini_get("session.use_cookies")){
        $p=session_get_cookie_params();
        setcookie(session_name(),'',time()-42000,$p['path'],$p['domain'],$p['secure'],$p['httponly']);
    }
    session_destroy();
}
header("Location:login.php?logged_out=1"); exit();
?>
