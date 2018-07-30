<?php
$options = Typecho_Widget::widget('Widget_Options');
$option=$options->plugin('WeMedia');
$plug_url = $options->pluginUrl;
if(strpos($this->permalink,'?')){
	$baseurl=substr($this->permalink,0,strpos($this->permalink,'?'));
}else{
	$baseurl=$this->permalink;
}

$queryUser= $this->db->select()->from('table.users')->where('uid = ?', Typecho_Cookie::get('__typecho_uid')); 
$rowUser = $this->db->fetchRow($queryUser);
$action = isset($_POST['action']) ? addslashes($_POST['action']) : '';
if($action=='updateinfo'){
	$screenName = isset($_POST['screenName']) ? addslashes($_POST['screenName']) : '';
	$email = isset($_POST['email']) ? addslashes($_POST['email']) : '';
	$url = isset($_POST['url']) ? addslashes($_POST['url']) : '';
	$realname = isset($_POST['realname']) ? addslashes($_POST['realname']) : '';
	$alipay = isset($_POST['alipay']) ? addslashes($_POST['alipay']) : '';
	$intro = isset($_POST['intro']) ? addslashes($_POST['intro']) : '';
	$code = isset($_POST['code']) ? addslashes($_POST['code']) : '';
	if($realname!=""&&$alipay!=""){
		if($rowUser["wemedia_isallow"]=="allow"){
			$wemedia_isallow="allow";
		}else{
			$wemedia_isallow="process";
		}
	}else{
		$wemedia_isallow="none";
	}
	if($rowUser["mail"]!=$email){
		$sessionCode = isset($_SESSION['code']) ? $_SESSION['code'] : '';
		if(strcasecmp($code,$sessionCode)!=0){
			$this->response->redirect($baseurl."?".$_SERVER['QUERY_STRING']."&error=code");exit;
		}
	}
	$userData=array(
		'screenName'=>$screenName,
		'mail'=>$email,
		'url'=>$url,
		'wemedia_realname'=>$realname,
		'wemedia_alipay'=>$alipay,
		'wemedia_info'=>$intro,
		'wemedia_isallow'=>$wemedia_isallow
	);
	$update = $this->db->update('table.users')->rows($userData)->where('uid=?',Typecho_Cookie::get('__typecho_uid'));
	$updateRows= $this->db->query($update);
	
	$randCode = '';
	$chars = 'abcdefghijkmnpqrstuvwxyzABCDEFGHIJKLMNPRSTUVWXYZ23456789';
	for ( $i = 0; $i < 5; $i++ ){
		$randCode .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
	}
	$_SESSION['code'] = strtoupper($randCode);
	
	$this->response->redirect($baseurl."?".$_SERVER['QUERY_STRING']);
}
$operation = isset($_GET['operation']) ? addslashes($_GET['operation']) : '';
if($operation=='syncpoint'){
	$queryComments= $this->db->select()->from('table.comments')->where('authorId = ?', Typecho_Cookie::get('__typecho_uid'))->where('type = ?', "comment")->where('status = ?', "approved"); 
	$rowComments = $this->db->fetchAll($queryComments);
	$queryPoint= $this->db->select('sum(pointnum) as sumpoint')->from('table.wemedia_point_cost')->where('pointuid = ?', Typecho_Cookie::get('__typecho_uid'))->where('pointstatus = ?', 1); 
	$rowPoint = $this->db->fetchRow($queryPoint);
	$wemedia_point=count($rowComments)*$option->point-$rowPoint["sumpoint"];
	$update = $this->db->update('table.users')->rows(array("wemedia_point"=>$wemedia_point))->where('uid=?',Typecho_Cookie::get('__typecho_uid'));
	$updateRows= $this->db->query($update);
	$this->response->redirect($baseurl."?page=info");
}
?>
<?php $this->need('templates/wemedia_user_header.php');?>
<div class="am-cf admin-main">
  <!-- sidebar start -->
  <?php $this->need('templates/wemedia_user_sidebar.php');?>
  <!-- sidebar end -->

  <!-- content start -->
  <div class="admin-content">
    <div class="admin-content-body">
      <div class="am-cf am-padding am-padding-bottom-0">
        <div class="am-fl am-cf">
			<strong class="am-text-primary am-text-lg">个人资料</strong> / 
			<small>
				<?php
				switch($rowUser["wemedia_isallow"]){
					case "none":echo "实名认证未审核";break;
					case "allow":echo '<font color="green">实名认证审核通过</font>';break;
					case "refuse":echo '<font color="red">实名认证审拒绝</font>';break;
					case "process":echo '<font color="green">实名认证审核中</font>';break;
				}
				?>
			</small>
		</div>
      </div>

      <hr/>

      <div class="am-g">
        <div class="am-u-sm-12 am-u-md-4 am-u-md-push-8">
          
		  <div class="am-panel am-panel-default">
            <div class="am-panel-bd">
              <div class="user-info">
                <p>积分信息</p>
                <div class="am-progress am-progress-sm">
                  <div class="am-progress-bar am-progress-bar-success" style="width: 80%"></div>
                </div>
                <p class="user-info-order">
					有效评论所得积分：<strong><?=$rowUser["wemedia_point"];?></strong>
					<strong>（<a href="?page=info&operation=syncpoint">同步</a>）</strong>
				</p>
              </div>
            </div>
          </div>
		  
        </div>

        <div class="am-u-sm-12 am-u-md-8 am-u-md-pull-4">
          <form id="infoForm" method="post" action="<?=$baseurl;?>?page=info" class="am-form am-form-horizontal">
            <div class="am-form-group">
              <label for="user-screenName" class="am-u-sm-3 am-form-label">昵称 / Nick</label>
              <div class="am-u-sm-9">
                <input type="text" name="screenName" id="user-screenName" value="<?=$rowUser['screenName'];?>" placeholder="昵称">
                <small>输入你的昵称，让我们记住你。</small>
              </div>
            </div>

            <div class="am-form-group">
              <label for="user-email" class="am-u-sm-3 am-form-label">电子邮件 / Email</label>
              <div class="am-u-sm-9">
                <input type="email" name="email" id="user-email" value="<?=$rowUser['mail'];?>" placeholder="输入你的电子邮件">
                <small id="emailmsg"><?php if(@$_GET["error"]==""){echo "邮箱你懂得...";}else if(@$_GET["error"]=="code"){echo '<font color="red">验证码错误</font>';}?></small>
              </div>
			  <div class="am-u-sm-9">
                <input type="text" name="code" id="user-code" value="" placeholder="(选填)如需修改邮箱，请填写验证码">
				<span id="sendmailmsg"></span><a id="sendmailcode" href="javascript:;">发送</a><br />
              </div>
            </div>

            <div class="am-form-group">
              <label for="user-url" class="am-u-sm-3 am-form-label">网址 / Url</label>
              <div class="am-u-sm-9">
                <input type="text" name="url" id="user-url" value="<?=$rowUser['url'];?>" placeholder="网址">
              </div>
            </div>

            <div class="am-form-group">
              <label for="user-realname" class="am-u-sm-3 am-form-label">真实姓名 / Name</label>
              <div class="am-u-sm-9">
                <input type="text" name="realname" id="user-realname" value="<?=$rowUser['wemedia_realname'];?>" placeholder="真实姓名用于提现实名认证，设置后不可修改" <?php if($rowUser['wemedia_isallow']=="allow"||$rowUser['wemedia_isallow']=="process"){?>readOnly<?php }?>>
              </div>
            </div>

            <div class="am-form-group">
              <label for="user-alipay" class="am-u-sm-3 am-form-label">支付宝 / Alipay</label>
              <div class="am-u-sm-9">
                <input type="text" name="alipay" id="user-alipay" value="<?=$rowUser['wemedia_alipay'];?>" placeholder="支付宝用于提现实名认证，设置后不可修改" <?php if($rowUser['wemedia_isallow']=="allow"||$rowUser['wemedia_isallow']=="process"){?>readOnly<?php }?>>
              </div>
            </div>

            <div class="am-form-group">
              <label for="user-intro" class="am-u-sm-3 am-form-label">简介 / Intro</label>
              <div class="am-u-sm-9">
                <textarea class="" name="intro" rows="5" id="user-intro" maxLength="100" placeholder="输入个人简介"><?=$rowUser['wemedia_info'];?></textarea>
                <small>100字以内写出你的一生...</small>
              </div>
            </div>

            <div class="am-form-group">
              <div class="am-u-sm-9 am-u-sm-push-3">
				<input type="hidden" name="action" value="updateinfo" />
				<input type="hidden" id="uid" value="<?=Typecho_Cookie::get('__typecho_uid');?>" />
                <button type="button" id="submitinfo" class="am-btn am-btn-primary">保存修改</button>
              </div>
            </div>
          </form>
        </div>
      </div>
    </div>

    <?php $this->need('templates/wemedia_user_footer.php');?>
  </div>
  <!-- content end -->

</div>

<a href="#" class="am-icon-btn am-icon-th-list am-show-sm-only admin-menu" data-am-offcanvas="{target: '#admin-offcanvas'}"></a>
<script>
$("#submitinfo").click(function(){
	var reg = /^([a-zA-Z0-9]+[_|\_|\.]?)*[a-zA-Z0-9]+@([a-zA-Z0-9]+[_|\_|\.]?)*[a-zA-Z0-9]+\.[a-zA-Z]{2,3}$/;
	if(!reg.test($("#user-email").val())){
　　　　$("#emailmsg").html('<font color="red">请先输入合法邮箱</font>');
　　　　return;
　　}
	$.post("<?=$plug_url;?>/WeMedia/ajax/user.php",{action:"checkemail",uid:$("#uid").val(),email:$('#user-email').val()},function(data){
		var data=JSON.parse(data);
		if(data.status=="mailok"){
			$("#infoForm").submit();
		}else if(data.status=="mailrepeat"){
			$("#emailmsg").html('<font color="red">此邮箱已注册</font>');
		}else if(data.status=="mailerror"){
			$("#emailmsg").html('<font color="red">此邮箱格式错误</font>');
		}
	});
});
$("#sendmailcode").click(function(){
	var reg = /^([a-zA-Z0-9]+[_|\_|\.]?)*[a-zA-Z0-9]+@([a-zA-Z0-9]+[_|\_|\.]?)*[a-zA-Z0-9]+\.[a-zA-Z]{2,3}$/;
	if(!reg.test($("#user-email").val())){
　　　　$("#emailmsg").html('<font color="red">请先输入合法邮箱</font>');
　　　　return false;
　　}
	settime();
	$.post("<?=$plug_url;?>/WeMedia/ajax/user.php",{action:"sendmail",email:$('#user-email').val()},function(data){
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
</script>