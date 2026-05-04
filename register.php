<?php
require_once 'session_check.php';
require_once 'db_config.php';
if(is_logged_in()){ header("Location:dashboard.php"); exit(); }

$err=[]; $ok='';
if($_SERVER['REQUEST_METHOD']==='POST'){
    $name  = trim($_POST['name']??'');
    $email = trim($_POST['email']??'');
    $pass  = $_POST['password']??'';
    $role  = $_POST['role']??'';

    if(!$name)                                   $err[]="Name required.";
    if(!filter_var($email,FILTER_VALIDATE_EMAIL)) $err[]="Valid email required.";
    if(strlen($pass)<8)                           $err[]="Password min 8 chars.";
    if($pass!==($_POST['confirm']??''))           $err[]="Passwords don't match.";
    if(!in_array($role,['donor','receiver','admin'])) $err[]="Select a valid role.";

    if(!$err){
        $s=$conn->prepare("SELECT user_id FROM Users WHERE email=?");
        $s->bind_param("s",$email); $s->execute(); $s->store_result();
        if($s->num_rows) $err[]="Email already registered.";
        $s->close();
    }
    if(!$err){
        $hp=password_hash($pass,PASSWORD_BCRYPT);
        $s=$conn->prepare("INSERT INTO Users(name,email,password,role,created_at)VALUES(?,?,?,?,NOW())");
        $s->bind_param("ssss",$name,$email,$hp,$role);
        if($s->execute()){
            $id=$s->insert_id; $s->close();
            $tbl=$role==='donor'?'Donors':($role==='receiver'?'Receivers':null);
            if($tbl){ $s2=$conn->prepare("INSERT INTO $tbl(user_id,created_at)VALUES(?,NOW())"); $s2->bind_param("i",$id); $s2->execute(); $s2->close(); }
            $ok="Registered! <a href='login.php'>Login</a>";
        } else { $err[]="Registration failed."; $s->close(); }
    }
}
?><!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Register</title>
<style>*{box-sizing:border-box;margin:0;padding:0}body{font:14px Arial,sans-serif;background:#f5f5f5;display:flex;justify-content:center;align-items:center;min-height:100vh}.card{background:#fff;border-radius:8px;box-shadow:0 2px 10px rgba(0,0,0,.1);padding:2rem;width:100%;max-width:420px}h2{text-align:center;color:#2e7d32;margin-bottom:1.2rem}label{display:block;margin-bottom:3px;color:#333}input,select{width:100%;padding:9px 11px;border:1px solid #ccc;border-radius:5px;font-size:14px;margin-bottom:.9rem}input:focus,select:focus{outline:none;border-color:#2e7d32}.btn{width:100%;padding:10px;background:#2e7d32;color:#fff;border:none;border-radius:5px;font-size:15px;cursor:pointer}.btn:hover{background:#1b5e20}.msg{padding:10px;border-radius:5px;margin-bottom:1rem;font-size:13px}.err{background:#ffebee;color:#c62828}.suc{background:#e8f5e9;color:#2e7d32}.foot{text-align:center;margin-top:.9rem;color:#666}.foot a{color:#2e7d32}</style>
</head><body><div class="card">
<h2>&#127869; Register</h2>
<?php if($err){ echo "<div class='msg err'>".implode('<br>',$err)."</div>"; } ?>
<?php if($ok){  echo "<div class='msg suc'>$ok</div>"; } else: ?>
<form method="POST">
    <label>Full Name</label>
    <input type="text" name="name" value="<?=htmlspecialchars($_POST['name']??'')?>" required>
    <label>Email</label>
    <input type="email" name="email" value="<?=htmlspecialchars($_POST['email']??'')?>" required>
    <label>Role</label>
    <select name="role">
        <option value="">-- Select Role --</option>
        <option value="donor"    <?=($_POST['role']??'')==='donor'   ?'selected':''?>>Donor</option>
        <option value="receiver" <?=($_POST['role']??'')==='receiver'?'selected':''?>>Receiver</option>
        <option value="admin"    <?=($_POST['role']??'')==='admin'   ?'selected':''?>>Admin</option>
    </select>
    <label>Password</label>
    <input type="password" name="password" required>
    <label>Confirm Password</label>
    <input type="password" name="confirm" required>
    <button class="btn" type="submit">Register</button>
</form>
<?php endif; ?>
<div class="foot">Already registered? <a href="login.php">Login</a></div>
</div></body></html>
