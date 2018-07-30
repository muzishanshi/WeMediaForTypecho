<?php
include '../../../config.inc.php';
require_once 'libs/ispay/lib/Ispay.class.php';
$db = Typecho_Db::get();
$prefix = $db->getPrefix();

$action = isset($_POST['action']) ? addslashes($_POST['action']) : '';
if($action=='feepay'){
	$feetype = isset($_POST['feetype']) ? addslashes($_POST['feetype']) : '';
	$feeprice = isset($_POST['feeprice']) ? addslashes($_POST['feeprice']) : '';
	$cid = isset($_POST['cid']) ? intval($_POST['cid']) : '';
	$uid = isset($_POST['uid']) ? intval($_POST['uid']) : '';
	$returnurl = isset($_POST['returnurl']) ? addslashes($_POST['returnurl']) : '';
	
	$options = Typecho_Widget::widget('Widget_Options');
	$option=$options->plugin('WeMedia');
	$plug_url = $options->pluginUrl;
	
	$Ispay = new ispayService($option->ispayid, $option->ispaykey);
	//设置时区
	date_default_timezone_set('Asia/Shanghai');
	//商户编号
	$Request=array();
	$Request['payId'] = $option->ispayid;
	//支付通道
	$Request['payChannel'] = $feetype;
	//订单标题
	$Request['Subject'] = "WeMediaForTypecho插件";
	//交易金额（单位分）
	$Request['Money'] = $feeprice*100;
	//随机生成订单号
	$Request['orderNumber'] = date("YmdHis") . rand(100000, 999999);
	//附加数据（没有可不填）
	$Request['attachData'] = $returnurl;
	//异步通知地址
	$Request['Notify_url'] = $plug_url."/WeMedia/notify_url.php";;
	//客户端同步跳转通知地址
	$Request['Return_url'] = $plug_url."/WeMedia/return_url.php";;
	//签名（加密算法详见开发文档）
	$Request['Sign'] = $Ispay -> Sign($Request);
	
	if($feetype!='tlepay'&&($Request['orderNumber']==''||$Request['payChannel']==''||$Request['Money']=='')){
		header("location:http://127.0.0.1");
		exit;
	}
	
	$data = array(
		'feeid'   =>  $Request['orderNumber'],
		'feecid'   =>  $cid,
		'feeuid'     =>  $uid,
		'feeprice'=>$Request['Money']/100,
		'feetype'     =>  $feetype,
		'feestatus'=>0,
		'feeinstime'=>date('Y-m-d H:i:s',Typecho_Date::time())
	);
	$insert = $db->insert('table.wemedia_fee_item')->rows($data);
	$insertId = $db->query($insert);
	
	switch($feetype){
		case "alipay":
		case "wxpay":
		case "qqpay":
		case "bank_pc":
			echo '
				<link rel="stylesheet" href="http://cdn.amazeui.org/amazeui/2.7.2/css/amazeui.min.css"/>
				<script src="http://apps.bdimg.com/libs/jquery/1.7.1/jquery.min.js" type="text/javascript"></script>
				<script src="http://cdn.amazeui.org/amazeui/2.7.2/js/amazeui.min.js" type="text/javascript"></script>
				<link rel="alternate icon" type="image/png" href="http://www.tongleer.com/wp-content/themes/D8/img/favicon.png">
				<div class="am-modal am-modal-loading am-modal-no-btn" tabindex="-1" id="my-modal-loading">
				  <div class="am-modal-dialog">
					<div class="am-modal-hd">正在付款中...</div>
					<div class="am-modal-bd">
					  <span class="am-icon-spinner am-icon-spin"></span>
					</div>
				  </div>
				</div>
				<form id="orderform" method="post" action="https://pay.ispay.cn/core/api/request/pay/">
					<input type="hidden" name="payChannel" value="'.$Request['payChannel'].'" />
					<input type="hidden" name="payId" value="'.$Request['payId'].'" />
					<input type="hidden" name="Subject" value="'.$Request['Subject'].'">
					<input type="hidden" name="attachData" value="'.$Request['attachData'].'">
					<input type="hidden" name="Money" value="'.$Request['Money'].'">
					<input type="hidden" name="orderNumber" value="'.$Request['orderNumber'].'">
					<input type="hidden" name="Notify_url" value="'.$Request['Notify_url'].'">
					<input type="hidden" name="Return_url" value="'.$Request['Return_url'].'">
					<input type="hidden" name="Sign" value="'.$Request['Sign'].'">
				</form>
				<script>
					$(function() {
						$("#my-modal-loading").modal();
						$("#orderform").submit();
					});
				</script>
			';
			break;
		case "tlepay":
			
			break;
	}
}
?>