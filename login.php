<?php
require __DIR__.'/config.php';
if(current_user())go('index.php');
$error='';
if($_SERVER['REQUEST_METHOD']==='POST'){
 csrf_check();
 $attempts=$_SESSION['login_attempts']??0;
 if($attempts>=8){$error='Muitas tentativas. Feche o navegador e tente novamente mais tarde.';}
 else{
  $s=db()->prepare("SELECT * FROM users WHERE email=? AND active=1");$s->execute([trim($_POST['email'])]);$u=$s->fetch();
  if($u&&password_verify($_POST['password'],$u['password_hash'])){
   session_regenerate_id(true);$_SESSION['user']=['id'=>$u['id'],'name'=>$u['name'],'email'=>$u['email'],'role'=>$u['role']];$_SESSION['login_attempts']=0;audit('login','users',(int)$u['id']);go('index.php');
  } else {$_SESSION['login_attempts']=$attempts+1;$error='E-mail ou senha inválidos.';}
 }
}
?><!doctype html><html lang="pt-BR"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width"><link rel="stylesheet" href="style.css"><title>Login</title></head><body class="login-page"><form method="post" class="login-card"><?=csrf_field()?><div class="logo">FloraGestor Ultimate 1.0</div><p>Gestão completa para flores, cestas e presentes personalizados</p><?php if($error):?><div class="flash error"><?=e($error)?></div><?php endif;?><label>E-mail</label><input type="email" name="email" required><label>Senha</label><input type="password" name="password" required><button class="primary">Entrar</button></form></body></html>