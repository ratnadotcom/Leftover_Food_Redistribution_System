<?php
if(session_status()===PHP_SESSION_NONE){
    ini_set('session.cookie_httponly',1);
    ini_set('session.gc_maxlifetime',1800);
    session_start();
}
if(isset($_SESSION['logged_in']) && time()-$_SESSION['login_time']>1800){
    session_unset(); session_destroy();
    header("Location:login.php?timeout=1"); exit();
}
if(isset($_SESSION['logged_in'])) $_SESSION['login_time']=time();

function is_logged_in(){ return !empty($_SESSION['logged_in']); }
function require_login(){ if(!is_logged_in()){ header("Location:login.php"); exit(); } }
function require_role($roles){
    require_login();
    $r=is_array($roles)?$roles:[$roles];
    if(!in_array($_SESSION['user_role']??'',$r)){ header("Location:access_denied.php"); exit(); }
}
function get_user_id(){ return $_SESSION['user_id']??null; }
function get_user_role(){ return $_SESSION['user_role']??''; }
function get_user_name(){ return $_SESSION['user_name']??''; }
function redirect_by_role(){
    $map=['admin'=>'admin/dashboard.php','donor'=>'donor/dashboard.php','receiver'=>'receiver/dashboard.php'];
    header("Location:".($map[$_SESSION['user_role']??'']??'dashboard.php')); exit();
}
function set_flash($t,$m){ $_SESSION['flash']=[$t,$m]; }
function show_flash(){
    if(!isset($_SESSION['flash'])) return;
    [$t,$m]=$_SESSION['flash']; unset($_SESSION['flash']);
    $c=['success'=>'#2e7d32','error'=>'#c62828','warning'=>'#e65100','info'=>'#1565c0'];
    echo "<div style='padding:10px;border-radius:4px;margin:8px 0;color:".($c[$t]??'#333')."'>".htmlspecialchars($m)."</div>";
}
?>
