<?php
/*
 * 付费阅读异步回调
 */
include '../../../config.inc.php';
require_once 'libs/spay.php';
require_once 'libs/payjs.php';
date_default_timezone_set('Asia/Shanghai');

$db = Typecho_Db::get();
$options = Typecho_Widget::widget('Widget_Options');
$option=$options->plugin('WeMedia');

switch($option->wemedia_paytype){
	case "spay":
		$id = isset($_POST['id']) ? addslashes($_POST['id']) : '';
		$wxhao = isset($_POST['wxhao']) ? addslashes($_POST['wxhao']) : '';
		$feetype="";
		if($wxhao){
			$feetype="wx";
			$is=spay_pay_verify($option->spay_wxpay_key,$id,$feetype);
			if($is!==false){
				$updateItem = $db->update('table.wemedia_fee_item')->rows(array('feestatus'=>1))->where('feeid=?',$is["orderNumber"]);
				$updateItemRows= $db->query($updateItem);
				
				$queryItem= $db->select()->from('table.wemedia_fee_item')->where('feeid = ?', $is["orderNumber"]); 
				$rowItem = $db->fetchRow($queryItem);
				if($rowItem['feestatus']==1){
					$queryContents= $db->select()->from('table.contents')->where('cid = ?', $rowItem['feecid']); 
					$rowContents = $db->fetchRow($queryContents);
					$queryUser= $db->select()->from('table.users')->where('uid = ?', $rowContents['authorId']); 
					$rowUser = $db->fetchRow($queryUser);
					$updateUser = $db->update('table.users')->rows(array('wemedia_money'=>$rowUser['wemedia_money']+$rowItem['feeprice']))->where('uid=?',$rowContents['authorId']);
					$updateUserRows= $db->query($updateUser);
				}
				echo 'success';
			}else{
				echo 'fail';
			}
		}else{
			$feetype="alipay";
			if(spay_pay_verify($option->spay_alipay_key)){
				$ts = $_POST['trade_status'];    
				if ($ts == 'TRADE_FINISHED' || $ts == 'TRADE_SUCCESS'){
					$updateItem = $db->update('table.wemedia_fee_item')->rows(array('feestatus'=>1))->where('feeid=?',$_POST["out_trade_no"]);
					$updateItemRows= $db->query($updateItem);
					echo 'success';    
				}else{
					echo 'fail';    
				}
			}else{
				echo 'fail';    
			}
		}
		break;
	case "payjs":
		$data = $_POST;
		if($data['return_code'] == 1){
			$payjs = new Payjs("","",$option->payjs_wxpay_key,"");
			$sign_verify = $data['sign'];
			unset($data['sign']);
			if($payjs->sign($data) == $sign_verify&&$data['total_fee']==$data['attach']*100){
				$updateItem = $db->update('table.wemedia_fee_item')->rows(array('feestatus'=>1))->where('feeid=?',$data["out_trade_no"]);
				$updateItemRows= $db->query($updateItem);
				
				$queryItem= $db->select()->from('table.wemedia_fee_item')->where('feeid = ?', $data["out_trade_no"]); 
				$rowItem = $db->fetchRow($queryItem);
				if($rowItem['feestatus']==1){
					$queryContents= $db->select()->from('table.contents')->where('cid = ?', $rowItem['feecid']); 
					$rowContents = $db->fetchRow($queryContents);
					$queryUser= $db->select()->from('table.users')->where('uid = ?', $rowContents['authorId']); 
					$rowUser = $db->fetchRow($queryUser);
					$updateUser = $db->update('table.users')->rows(array('wemedia_money'=>$rowUser['wemedia_money']+$rowItem['feeprice']))->where('uid=?',$rowContents['authorId']);
					$updateUserRows= $db->query($updateUser);
				}
				echo 'success';
			}
		}
		break;
}
?>