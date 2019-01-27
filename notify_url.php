<?php
/*
 * SPay支付异步回调通知页面
 */
include '../../../config.inc.php';
require_once 'libs/spay.php';
date_default_timezone_set('Asia/Shanghai');

$db = Typecho_Db::get();
$options = Typecho_Widget::widget('Widget_Options');
$option=$options->plugin('WeMedia');

$id = isset($_POST['id']) ? addslashes($_POST['id']) : '';
$wxhao = isset($_POST['wxhao']) ? addslashes($_POST['wxhao']) : '';
$feetype="";
if($wxhao){
	$feetype="wx";
}
$is=spay_wpay_verify($id,$option->spay_wxpay_key,$feetype);
if($is!==false){
	$updateItem = $db->update('table.wemedia_fee_item')->rows(array('feestatus'=>1))->where('feeid=?',$is["orderNumber"]);
	$updateItemRows= $db->query($updateItem);
	
	$queryItem= $db->select()->from('table.wemedia_fee_item')->where('feeid = ?', $is["orderNumber"]); 
	$rowItem = $db->fetchRow($queryItem);
	$queryContents= $db->select()->from('table.contents')->where('cid = ?', $rowItem['feecid']); 
	$rowContents = $db->fetchRow($queryContents);
	$queryUser= $db->select()->from('table.users')->where('uid = ?', $rowContents['authorId']); 
	$rowUser = $db->fetchRow($queryUser);
	$updateUser = $db->update('table.users')->rows(array('wemedia_money'=>$rowUser['wemedia_money']+$rowItem['feeprice']))->where('uid=?',$rowContents['authorId']);
	$updateUserRows= $db->query($updateUser);
	
	echo 'success';
}else{
	echo 'fail';
}
?>