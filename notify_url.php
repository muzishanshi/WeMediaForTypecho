<?php
/*
 * 有赞支付异步回调通知页面
 */
include '../../../config.inc.php';
date_default_timezone_set('Asia/Shanghai');

$db = Typecho_Db::get();
$options = Typecho_Widget::widget('Widget_Options');
$option=$options->plugin('WeMedia');

/*有赞回调*/
$json = file_get_contents('php://input'); 
$data = json_decode($json, true);
/**
 * 判断消息是否合法，若合法则返回成功标识
 */
$msg = $data['msg'];
$sign_string = $option->wemedia_yz_client_id."".$msg."".$option->wemedia_yz_client_secret;
$sign = md5($sign_string);
if($sign != $data['sign']){
	exit();
}else{
	$result = array("code"=>0,"msg"=>"success") ;
}
/**
 * msg内容经过 urlencode 编码，需进行解码
 */
$msg = json_decode(urldecode($msg),true);
/**
 * 根据 type 来识别消息事件类型，具体的 type 值以文档为准，此处仅是示例
 */
if($data['type'] == "trade_TradePaid"){
	$qrNameArr=explode("|",$msg["qr_info"]["qr_name"]);
	$data = array(
		'feeid'   =>  $data['id'],
		'feecid'   =>  $qrNameArr[1],
		'feeuid'     =>  $qrNameArr[2],
		'feeprice'=>$qrNameArr[3],
		'feetype'     =>  $msg["full_order_info"]["order_info"]["pay_type_str"],
		'feestatus'=>1,
		'feeinstime'=>date('Y-m-d H:i:s',time())
	);
	$insert = $db->insert('table.wemedia_fee_item')->rows($data);
	$insertId = $db->query($insert);
	
	$queryItem= $db->select()->from('table.wemedia_fee_item')->where('feeid = ?', $data['id']); 
	$rowItem = $db->fetchRow($queryItem);
	$queryContents= $db->select()->from('table.contents')->where('cid = ?', $rowItem['feecid']); 
	$rowContents = $db->fetchRow($queryContents);
	$queryUser= $db->select()->from('table.users')->where('uid = ?', $rowContents['authorId']); 
	$rowUser = $db->fetchRow($queryUser);
	$updateUser = $db->update('table.users')->rows(array('wemedia_money'=>$rowUser['wemedia_money']+$qrNameArr[3]))->where('uid=?',$rowContents['authorId']);
	$updateUserRows= $db->query($updateUser);
}
?>