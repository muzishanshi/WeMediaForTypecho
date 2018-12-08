<?php
date_default_timezone_set('Asia/Shanghai');
include '../../../../config.inc.php';
$db = Typecho_Db::get();
$prefix = $db->getPrefix();

$action = isset($_POST['action']) ? addslashes($_POST['action']) : '';
$cid = isset($_POST['cid']) ? addslashes($_POST['cid']) : '';
if($action=='confirmFee'){
	$price = isset($_POST['price']) ? addslashes($_POST['price']) : '';
	$islogin = isset($_POST['islogin']) ? addslashes($_POST['islogin']) : '';
	$update = $db->update('table.contents')->rows(array('wemedia_isFee'=>'y','wemedia_price'=>$price,'wemedia_islogin'=>$islogin))->where('cid=?',$cid);
	$updateRows= $db->query($update);
}else if($action=='cancelFee'){
	$update = $db->update('table.contents')->rows(array('wemedia_isFee'=>'n'))->where('cid=?',$cid);
	$updateRows= $db->query($update);
}
?>