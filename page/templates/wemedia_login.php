<?php
@session_start();
date_default_timezone_set('Asia/Shanghai');
$options = Typecho_Widget::widget('Widget_Options');
$plug_url = $options->pluginUrl;
if(strpos($this->permalink,'?')){
	$url=substr($this->permalink,0,strpos($this->permalink,'?'));
}else{
	$url=$this->permalink;
}
$action = isset($_POST['action']) ? addslashes($_POST['action']) : '';
if($action=='submitlogin'){
	$mail = isset($_POST['mail']) ? addslashes($_POST['mail']) : '';
	$password = isset($_POST['password']) ? addslashes($_POST['password']) : '';
	$query= $this->db->select()->from('table.users')->where('mail = ?', $mail); 
	$userData = $this->db->fetchRow($query);
	$user = Typecho_Widget::widget('Widget_User');
	$user->login($userData["name"],$password);
	$this->response->redirect($url);
}else if($action=='submitreg'){
	$hasher = new PasswordHash(8, true);
	$dataStruct = array(
        'name' => $this->request->mail,
        'mail' => $this->request->mail,
        'screenName' => $this->request->mail,
        'password' => $hasher->HashPassword($this->request->password),
        'created' => $this->options->time,
        'group' => 'contributor'
    );
    $register = $this->widget('Widget_Register');
    $insertId = $register->insert($dataStruct);
    $register->db->fetchRow($register->select()->where('uid = ?', $insertId)->limit(1), array($register, 'push'));
    $this->user->login($this->request->mail, $this->request->password);
	
	$randCode = '';
	$chars = 'abcdefghijkmnpqrstuvwxyzABCDEFGHIJKLMNPRSTUVWXYZ23456789';
	for ( $i = 0; $i < 5; $i++ ){
		$randCode .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
	}
	$_SESSION['code'] = strtoupper($randCode);
	
    $this->response->redirect($url);
}else if($action=='submitforget'){
	$hasher = new PasswordHash(8, true);
	$update = $this->db->update('table.users')->rows(array('password'=>$hasher->HashPassword($this->request->password)))->where('mail=?',$this->request->mail);
	$updateRows= $this->db->query($update);
	
	$query= $this->db->select()->from('table.users')->where('mail = ?', $this->request->mail); 
	$userData = $this->db->fetchRow($query);
	$user = Typecho_Widget::widget('Widget_User');
	$user->login($userData["name"], $this->request->password);
	
	$randCode = '';
	$chars = 'abcdefghijkmnpqrstuvwxyzABCDEFGHIJKLMNPRSTUVWXYZ23456789';
	for ( $i = 0; $i < 5; $i++ ){
		$randCode .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
	}
	$_SESSION['code'] = strtoupper($randCode);
	
    $this->response->redirect($url);
}
?>
<style>
    .header {
      text-align: center;
    }
    .header h1 {
      font-size: 200%;
      color: #333;
      margin-top: 30px;
    }
    .header p {
      font-size: 14px;
    }
</style>
<div class="header">
  <div class="am-g">
    <h1>用户登陆</h1>
    <p><a href="<?=$this->options ->siteUrl();?>"><?php $this->options->title(); ?></a></p>
  </div>
  <hr />
</div>
<div class="am-g">
  <div class="am-u-lg-6 am-u-md-8 am-u-sm-centered">
    <h3>登录</h3>
    <hr>
    <div class="am-btn-group">
    </div>
	<br />

    <form id="wemediaLoginForm" method="post" class="am-form" action="">
      <label for="email">邮箱:</label>
      <input type="email" name="mail" id="email" value="" placeholder="您的电子邮箱">
	  <small id="emailmsg"></small>
      <br>
      <label for="password">密码:</label>
      <input type="password" name="password" id="password" value="" placeholder="您的登录密码">
	  <small id="passwordmsg"></small>
      <br>
	  <label for="code">邮箱验证码(<span id="sendmailmsg"></span><a id="sendmailcode" href="javascript:;">发送</a>):</label>
      <input type="text" name="code" id="code" value="" placeholder="(选填)第一次登录和忘记密码时填写">
	  <small id="codemsg"></small>
      <br>
      <div class="am-cf">
		<input type="hidden" name="action" id="action" value="submit" />
        <input type="button" id="btnLogin" name="" value="登 录" class="am-btn am-btn-primary am-btn-sm am-fl">
        <input type="button" id="btnForget" name="" value="忘记密码 ^_^? " class="am-btn am-btn-default am-btn-sm am-fr">
      </div>
    </form>
    <?php $this->need('templates/wemedia_user_footer.php');?>
  </div>
</div>
<script>
$(function(){
	var isInputEmail=false;
	$("#email").change(function(){
		$.post("<?=$plug_url;?>/WeMedia/ajax/user.php",{action:"checkemail",email:$('#email').val()},function(data){
			if(isInputEmail){
				var data=JSON.parse(data);
				if(data.status=="mailok"){
					$("#emailmsg").html('<font color="green">此邮箱可以注册</font>');
				}else if(data.status=="mailrepeat"){
					$("#emailmsg").html('<font color="red">此邮箱已注册</font>');
				}else if(data.status=="mailerror"){
					$("#emailmsg").html('<font color="red">此邮箱格式错误</font>');
				}
			}
			isInputEmail=true;
		});
	});
	$("#sendmailcode").click(function(){
		var reg = /^([a-zA-Z0-9]+[_|\_|\.]?)*[a-zA-Z0-9]+@([a-zA-Z0-9]+[_|\_|\.]?)*[a-zA-Z0-9]+\.[a-zA-Z]{2,3}$/;
		if(!reg.test($("#email").val())){
	　　　　$("#emailmsg").html('<font color="red">请先输入合法邮箱</font>');
	　　　　return false;
	　　}
		settime();
		$.post("<?=$plug_url;?>/WeMedia/ajax/user.php",{action:"sendmail",email:$('#email').val()},function(data){
		});
	});
	var timer;
	var countdown=60;
	function settime() {
		if (countdown == 0) {
			countdown = 60;
			clearTimeout(timer);
			$("#sendmailcode").css("display","inline");
			$("#sendmailmsg").css("display","none");
			return;
		} else {
			$("#sendmailcode").css("display","none");
			$("#sendmailmsg").html(countdown+'秒后可重新发送');
			$("#sendmailmsg").css("display","inline");
			countdown--; 
		} 
		timer=setTimeout(function() { 
			settime() 
		},1000) 
	}
	$("#btnLogin").click(function(){
		if($("#email").val()==""){
			$("#emailmsg").html('<font color="red">请填写邮箱</font>');
			return;
		}
		if($("#password").val()==""){
			$("#passwordmsg").html('<font color="red">请填写密码</font>');
			return;
		}
		$.post("<?=$plug_url;?>/WeMedia/ajax/user.php",{action:"submit",email:$('#email').val(),password:$('#password').val(),code:$('#code').val()},function(data){
			var data=JSON.parse(data);
			if(data.status=="login"){
				$("#action").val("submitlogin");
				$("#wemediaLoginForm").submit();
			}else if(data.status=="reg"){
				$("#action").val("submitreg");
				$("#wemediaLoginForm").submit();
			}else if(data.status=="passworderror"){
				$("#passwordmsg").html('<font color="red">用户密码不正确</font>');
			}else if(data.status=="codenull"){
				$("#codemsg").html('<font color="red">请填写验证码</font>');
			}else if(data.status=="codeerror"){
				$("#codemsg").html('<font color="red">验证码错误</font>');
			}else if(data.status=="mailerror"){
				$("#emailmsg").html('<font color="red">此邮箱格式错误</font>');
			}else if(data.status=="mailrepeat"){
				$("#emailmsg").html('<font color="red">此邮箱已注册</font>');
			}else if(data.status=="passwordnull"){
				$("#passwordmsg").html('<font color="red">请填写密码</font>');
			}
		});
	});
	$("#btnForget").click(function(){
		if($("#email").val()==""){
			$("#emailmsg").html('<font color="red">请填写邮箱</font>');
			return;
		}
		if($("#password").val()==""){
			$("#passwordmsg").html('<font color="red">请填写密码</font>');
			return;
		}
		if($("#code").val()==""){
			$("#codemsg").html('<font color="red">请填写验证码</font>');
			return;
		}
		if(confirm("确定使用当前密码设为新密码并登录吗？")){
			$.post("<?=$plug_url;?>/WeMedia/ajax/user.php",{action:"forget",email:$('#email').val(),password:$('#password').val(),code:$('#code').val()},function(data){
				var data=JSON.parse(data);
				if(data.status=="forget"){
					$("#action").val("submitforget");
					$("#wemediaLoginForm").submit();
				}else if(data.status=="mailnull"){
					$("#emailmsg").html('<font color="red">账号不存在</font>');
				}else if(data.status=="codeerror"){
					$("#codemsg").html('<font color="red">验证码错误</font>');
				}
			});
		}
	});
})
</script>