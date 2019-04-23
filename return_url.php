<?php
/*
 * 付费阅读同步回调
 */
include '../../../config.inc.php';
require_once 'libs/spay.php';
require_once 'libs/payjs.php';
$db = Typecho_Db::get();
$prefix = $db->getPrefix();
date_default_timezone_set('Asia/Shanghai');

$options = Typecho_Widget::widget('Widget_Options');
$option=$options->plugin('WeMedia');
$plug_url = $options->pluginUrl;

switch($option->wemedia_paytype){
	case "spay":
		$id = isset($_GET['id']) ? addslashes($_GET['id']) : '';
		$wxhao = isset($_GET['wxhao']) ? addslashes($_GET['wxhao']) : '';
		$feetype="";
		if($wxhao){
			$feetype="wx";
			$is=spay_pay_verify($option->spay_wxpay_key,$id,$feetype);
			if($is!==false){
				echo "付款成功";
			}else{
				echo "付款失败";
			}
		}else{
			$feetype="alipay";
			if(spay_pay_verify($option->spay_alipay_key)){
				$ts = $_GET['trade_status'];    
				if ($ts == 'TRADE_FINISHED' || $ts == 'TRADE_SUCCESS'){
					echo '付款成功';    
				}else{
					echo '付款失败';    
				}
			}else{
				echo '签名验证失败';    
			}
		}
		break;
	case "payjs":
		
		break;
}
?>