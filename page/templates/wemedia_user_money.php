<?php
date_default_timezone_set('Asia/Shanghai');
if(strpos($this->permalink,'?')){
	$url=substr($this->permalink,0,strpos($this->permalink,'?'));
}else{
	$url=$this->permalink;
}
$queryUser= $this->db->select()->from('table.users')->where('uid = ?', Typecho_Cookie::get('__typecho_uid')); 
$rowUser = $this->db->fetchRow($queryUser);
$action = isset($_POST['action']) ? addslashes($_POST['action']) : '';
if($action=='submitmoney'){
	$moneynum = isset($_POST['moneynum']) ? addslashes($_POST['moneynum']) : '';
	$moneytype = isset($_POST['moneytype']) ? addslashes($_POST['moneytype']) : '';
	$moneyData=array(
		"moneyuid"=>Typecho_Cookie::get('__typecho_uid'),
		"moneynum"=>$moneynum,
		"moneytype"=>$moneytype,
		"moneystatus"=>0,
		"moneyinstime"=>date("Y-m-d H:i:s",time()),
	);
	$insert = $this->db->insert('table.wemedia_money_item')->rows($moneyData);
	$insertId = $this->db->query($insert);
	$update = $this->db->update('table.users')->rows(array("wemedia_money"=>$rowUser["wemedia_money"]-$moneynum))->where('uid=?',Typecho_Cookie::get('__typecho_uid'));
	$updateRows= $this->db->query($update);
	$this->response->redirect($url.'?page=money');
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
        <strong class="am-text-primary am-text-lg">提现</strong> /
        <small>无最低限额</small>
      </div>
    </div>

    <hr>
	
	<form id="moneyForm" method="post" action="?page=money">
    <div class="am-tabs am-margin" data-am-tabs>
      <ul class="am-tabs-nav am-nav am-nav-tabs">
        <li class="am-active"><a href="#tab1">支付宝</a></li>
      </ul>

      <div class="am-tabs-bd">
        <div class="am-tab-panel am-fade am-in am-active" id="tab1">
          <div class="am-g am-margin-top">
            <div class="am-u-sm-4 am-u-md-2 am-text-right">账户余额</div>
            <div class="am-u-sm-8 am-u-md-10">
				<span id="moneybalance"><?=$rowUser["wemedia_money"];?></span>
            </div>
          </div>
		  
		  <div class="am-g am-margin-top">
            <div class="am-u-sm-4 am-u-md-2 am-text-right">提现渠道</div>
            <div class="am-u-sm-8 am-u-md-10">
              <select name="moneytype" data-am-selected="{btnSize: 'sm'}">
                <option value="alipay">支付宝</option>
              </select>
            </div>
          </div>

          <div class="am-g am-margin-top">
            <div class="am-u-sm-4 am-u-md-2 am-text-right">提现金额</div>
            <div class="am-u-sm-8 am-u-md-10">
				<input type="text" name="moneynum" size="4" maxLength="4" id="moneynum" value="" placeholder="">
				<small id="moneynummsg"></small>
            </div>
          </div>

        </div>

      </div>
    </div>

    <div class="am-margin">
	  <input type="hidden" name="action" value="submitmoney" />
      <button type="submit" class="am-btn am-btn-primary am-btn-xs">提现</button>
    </div>
  </div>

    <?php $this->need('templates/wemedia_user_footer.php');?>
  </div>
  <!-- content end -->

</div>

<a href="#" class="am-icon-btn am-icon-th-list am-show-sm-only admin-menu" data-am-offcanvas="{target: '#admin-offcanvas'}"></a>
<script>
$("#moneyForm").submit(function(){
	if($("#moneynum").val()>$("#moneybalance").text()){
		$("#moneynummsg").html('<font color="red">提现金额不能超过账户余额</font>');
		return false;
	}
});
$("#moneynum").keyup(function(){
	/*先把非数字的都替换掉，除了数字和.*/
	$("#moneynum").val($("#moneynum").val().replace(/[^\d.]/g,""));
	/*保证只有出现一个.而没有多个.*/
	$("#moneynum").val($("#moneynum").val().replace(/\.{2,}/g,"."));
	/*必须保证第一个为数字而不是.*/
	$("#moneynum").val($("#moneynum").val().replace(/^\./g,""));
	/*保证.只出现一次，而不能出现两次以上*/
	$("#moneynum").val($("#moneynum").val().replace(".","$#$").replace(/\./g,"").replace("$#$","."));
	/*只能输入两个小数*/
	$("#moneynum").val($("#moneynum").val().replace(/^(\-)*(\d+)\.(\d\d).*$/,"$1$2.$3"));
});
</script>