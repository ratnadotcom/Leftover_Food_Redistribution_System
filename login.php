<?php
require_once 'session_check.php';
require_once 'db_config.php';
if(is_logged_in()){ redirect_by_role(); }

$err=''; $email='';
if(!isset($_SESSION['attempts'])){ $_SESSION['attempts']=0; $_SESSION['last_try']=time(); }
$locked=($_SESSION['attempts']>=5 && time()-$_SESSION['last_try']<300);

if($_SERVER['REQUEST_METHOD']==='POST' && !$locked){
    $email = trim($_POST['email']??'');
    $pass  = $_POST['password']??'';
    if(!$email||!$pass){
        $err="Email and password required.";
    } else {
        $s=$conn->prepare("SELECT user_id,name,email,password,role FROM Users WHERE email=? LIMIT 1");
        $s->bind_param("s",$email); $s->execute();
        $row=$s->get_result()->fetch_assoc(); $s->close();
        if($row && password_verify($pass,$row['password'])){
            $_SESSION['attempts']=0;
            session_regenerate_id(true);
            $_SESSION['user_id']=$row['user_id']; $_SESSION['user_name']=$row['name'];
            $_SESSION['user_email']=$row['email']; $_SESSION['user_role']=$row['role'];
            $_SESSION['logged_in']=true; $_SESSION['login_time']=time();
            redirect_by_role();
        } else {
            $err="Invalid email or password.";
            $_SESSION['attempts']++; $_SESSION['last_try']=time();
        }
    }
}
?><!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Login</title>
<style>*{box-sizing:border-box;margin:0;padding:0}body{font:14px Arial,sans-serif;background:#f5f5f5;display:flex;justify-content:center;align-items:center;min-height:100vh}.card{background:#fff;border-radius:8px;box-shadow:0 2px 10px rgba(0,0,0,.1);padding:2rem;width:100%;max-width:380px}h2{text-align:center;color:#2e7d32;margin-bottom:.3rem}.sub{text-align:center;color:#888;font-size:12px;margin-bottom:1.2rem}label{display:block;margin-bottom:3px}input{width:100%;padding:9px 11px;border:1px solid #ccc;border-radius:5px;font-size:14px;margin-bottom:.9rem}input:focus{outline:none;border-color:#2e7d32}.btn{width:100%;padding:10px;background:#2e7d32;color:#fff;border:none;border-radius:5px;font-size:15px;cursor:pointer}.btn:hover{background:#1b5e20}.btn:disabled{background:#aaa}.err{background:#ffebee;color:#c62828;padding:10px;border-radius:5px;margin-bottom:1rem;font-size:13px}.foot{text-align:center;margin-top:.9rem;color:#666}.foot a{color:#2e7d32}</style>
</head><body><div class="card">
<h2>&#127869; Login</h2>
<p class="sub">Food Redistribution System</p>
<?php if($err||$locked): ?>
<div class="err"><?= $locked ? "Too many attempts. Wait 5 min." : htmlspecialchars($err) ?></div>
<?php endif; ?>
<?php if(isset($_GET['timeout'])): ?><div class="err">Session expired. Please login again.</div><?php endif; ?>
<form method="POST">
    <label>Email</label>
    <input type="email" name="email" value="<?=htmlspecialchars($email)?>" <?=$locked?'disabled':''?> required autofocus>
    <label>Password</label>
    <input type="password" name="password" <?=$locked?'disabled':''?> required>
    <button class="btn" type="submit" <?=$locked?'disabled':''?>>Log In</button>
</form>
<div class="foot">No account? <a href="register.php">Register</a></div>
</div></body></html>
