<?php
date_default_timezone_set('Asia/Shanghai');
include '../../../../config.inc.php';
$db = Typecho_Db::get();
$prefix = $db->getPrefix();
$queryAllow= $db->select('value')->from('table.options')->where('name = ?', 'allowRegister'); 
$rowAllow = $db->fetchRow($queryAllow);

$action = isset($_POST['action']) ? addslashes($_POST['action']) : '';
if($action=='checkemail'){
	$email = isset($_POST['email']) ? addslashes($_POST['email']) : '';
	$uid = isset($_POST['uid']) ? addslashes($_POST['uid']) : '';
	if($uid==""){
		$query= $db->select()->from('table.users')->where('mail = ?', $email); 
	}else{
		$query= $db->select()->from('table.users')->where('mail = ?', $email)->where('uid != ?', $uid); 
	}
	$row = $db->fetchRow($query);
	$check="/\w+([-+.']\w+)*@\w+([-.]\w+)*\.\w+([-.]\w+)*/";
	if(preg_match($check,$email)){
		if(count($row)>0){
			echo '{"status":"mailrepeat"}';
		}else if(count($row)==0){
			echo '{"status":"mailok"}';
		}
	}else if(!preg_match($check,$email)){
		echo '{"status":"mailerror"}';
	}
}else if($action=='sendmail'){
	session_start();
	$randCode = '';
	$chars = 'abcdefghijkmnpqrstuvwxyzABCDEFGHIJKLMNPRSTUVWXYZ23456789';
	for ( $i = 0; $i < 5; $i++ ){
		$randCode .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
	}
	$_SESSION['code'] = strtoupper($randCode);

	$email = isset($_POST['email']) ? addslashes($_POST['email']) : '';
	$queryPlugins= $db->select('value')->from('table.options')->where('name = ?', 'title'); 
	$rowPlugins = $db->fetchRow($queryPlugins);
	WeMedia_Plugin::sendMail($email,'【'.$rowPlugins["value"].'】验证码','欢迎使用【'.$rowPlugins["value"].'】验证码服务，您的验证码是：'.$_SESSION['code']);
	echo $_SESSION['code'];
}else if($action=='submit'){
	session_start();
	$email = isset($_POST['email']) ? addslashes($_POST['email']) : '';
	$password = isset($_POST['password']) ? addslashes($_POST['password']) : '';
	$code = isset($_POST['code']) ? addslashes($_POST['code']) : '';
	
	if($rowAllow["value"]==1){
		$query= $db->select()->from('table.users')->where('mail = ?', $email); 
		$userData = $db->fetchRow($query);
		$hasher = new PasswordHash(8, true);
		if(count($userData)>0){
			if($hasher->CheckPassword($password,$userData["password"])){
				echo '{"status":"login"}';
			}else{
				echo '{"status":"passworderror"}';
			}
		}else if(count($userData)==0){
			if($code!=""){
				$sessionCode = isset($_SESSION['code']) ? $_SESSION['code'] : '';
				if(strcasecmp($code,$sessionCode)==0){
					$query= $db->select()->from('table.users')->where('mail = ?', $email); 
					$row = $db->fetchRow($query);
					$check="/\w+([-+.']\w+)*@\w+([-.]\w+)*\.\w+([-.]\w+)*/";
					if(preg_match($check,$email)){
						if(count($row)>0){
							echo '{"status":"mailrepeat"}';
						}else if(count($row)==0){
							if($password!=""){
								echo '{"status":"reg"}';
							}else{
								echo '{"status":"passwordnull"}';
							}
						}
					}else if(!preg_match($check,$email)){
						echo '{"status":"mailerror"}';
					}
				}else{
					echo '{"status":"codeerror"}';
				}
			}else{
				echo '{"status":"codenull"}';
			}
		}
	}else if($rowAllow["value"]==0){
		echo '{"status":"NotAllowReg"}';
	}
}else if($action=='forget'){
	session_start();
	$email = isset($_POST['email']) ? addslashes($_POST['email']) : '';
	$password = isset($_POST['password']) ? addslashes($_POST['password']) : '';
	$code = isset($_POST['code']) ? addslashes($_POST['code']) : '';
	$query= $db->select()->from('table.users')->where('mail = ?', $email); 
	$userData = $db->fetchRow($query);
	if(count($userData)>0){
		$sessionCode = isset($_SESSION['code']) ? $_SESSION['code'] : '';
		if(strcasecmp($code,$sessionCode)==0){
			echo '{"status":"forget"}';
		}else{
			echo '{"status":"codeerror"}';
		}
	}else{
		echo '{"status":"mailnull"}';
	}
}
?>