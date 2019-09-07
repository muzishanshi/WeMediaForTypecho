<?php
session_start();
include '../../../config.inc.php';
require_once 'libs/spay.php';
require_once 'libs/payjs.php';
$db = Typecho_Db::get();
$prefix = $db->getPrefix();
date_default_timezone_set('Asia/Shanghai');

//防止CC攻击，频繁F5刷新
$timestamp = time();
$ll_nowtime = $timestamp ; //判断session是否存在如果存在从session取值，如果不存在进行初始化赋值
if ($_SESSION){
	$ll_lasttime = $_SESSION['ll_lasttime'];
	$ll_times = $_SESSION['ll_times'] + 1;
	$_SESSION['ll_times'] = $ll_times;
}else{
	$ll_lasttime = $ll_nowtime;
	$ll_times = 1;
	$_SESSION['ll_times'] = $ll_times;
	$_SESSION['ll_lasttime'] = $ll_lasttime;
}
if(($ll_nowtime - $ll_lasttime) < 3){//现在时间-开始登录时间 来进行判断 如果登录频繁 跳转 否则对session进行赋值
	if ($ll_times>=5){
		header("location:http://127.0.0.1");exit;//可以换成其他链接，比如站内的404错误显示页面(千万不要用动态页面)
	}
}else{
	$ll_times = 0; $_SESSION['ll_lasttime'] = $ll_nowtime; $_SESSION['ll_times'] = $ll_times;
}

$action = isset($_POST['action']) ? addslashes($_POST['action']) : '';
if($action=='paysubmit'){
	$feetype = isset($_POST['feetype']) ? addslashes($_POST['feetype']) : '';
	$feecookie = isset($_POST['feecookie']) ? addslashes($_POST['feecookie']) : '';
	$feecid = isset($_POST['feecid']) ? intval(urldecode($_POST['feecid'])) : '';
	$feeuid = isset($_POST['feeuid']) ? intval($_POST['feeuid']) : 0;
	
	$options = Typecho_Widget::widget('Widget_Options');
	$option=$options->plugin('WeMedia');
	$plug_url = $options->pluginUrl;
	
	$queryContent= $db->select()->from('table.contents')->where('cid = ?', $feecid); 
	$rowContent = $db->fetchRow($queryContent);
	
	switch($option->wemedia_paytype){
		case "spay":
			$time=time();
			$orderNumber=date("YmdHis",$time) . rand(100000, 999999);
			if($feetype=="wx"){
				$pdata['orderNumber']=$orderNumber;
				$pdata['Money']=$rowContent["wemedia_price"];
				$pdata['Notify_url']=$option->spay_pay_notify_url;
				$pdata['Return_url']=$option->spay_pay_return_url;
				$pdata['SPayId']=$option->spay_wxpay_id;
				$ret=spay_pay_pay($pdata,$option->spay_wxpay_key,$feetype);
				$Money=$pdata['Money'];
			}else if($feetype=="alipay"){
				$data['total_fee'] = $rowContent["wemedia_price"];
				$data['partner']= $option->spay_alipay_id;
				$data['notify_url']= $option->spay_pay_notify_url;
				$data['return_url']= $option->spay_pay_return_url;
				$data['out_trade_no']= $orderNumber;
				$ret=spay_pay_pay($data,$option->spay_alipay_key);
				$Money=$data['total_fee'];
			}
			$url=$ret['url'];
			if($url!=''){
				$data = array(
					'feeid'   =>  $orderNumber,
					'feecid'   =>  $feecid,
					'feeuid'     =>  $feeuid,
					'feeprice'=>$Money,
					'feetype'     =>  $feetype,
					'feestatus'=>0,
					'feeinstime'=>date('Y-m-d H:i:s',$time),
					'feecookie'=>$feecookie
				);
				$insert = $db->insert('table.wemedia_fee_item')->rows($data);
				$insertId = $db->query($insert);
				$json=json_encode(array("status"=>"ok","type"=>"spay","channel"=>$feetype,"qrcode"=>$url));
				echo $json;
				exit;
			}
			break;
		case "payjs":
			switch($option->wemedia_payjstype){
				case "native":
					$time=time();
					$arr = [
						'body' => $options->title,               // 订单标题
						'out_trade_no' => date("YmdHis",$time) . rand(100000, 999999),       // 订单号
						'total_fee' => $rowContent["wemedia_price"]*100,             // 金额,单位:分
						'attach'=>$rowContent["wemedia_price"]// 自定义数据
					];
					$payjs = new Payjs($arr,$option->payjs_wxpay_mchid,$option->payjs_wxpay_key,$option->payjs_wxpay_notify_url);
					$res = $payjs->pay();
					$rst=json_decode($res,true);
					if($rst["return_code"]==1){
						$data = array(
							'feeid'   =>  $arr['out_trade_no'],
							'feecid'   =>  $feecid,
							'feeuid'     =>  $feeuid,
							'feeprice'=>$rowContent['wemedia_price'],
							'feetype'     =>  $feetype,
							'feestatus'=>0,
							'feeinstime'=>date('Y-m-d H:i:s',$time),
							'feecookie'=>$feecookie
						);
						$insert = $db->insert('table.wemedia_fee_item')->rows($data);
						$insertId = $db->query($insert);
						$json=json_encode(array("status"=>"ok","type"=>"payjsnative","channel"=>"wx","qrcode"=>$rst["qrcode"]));
						echo $json;
						exit;
						
					}
					break;
				case "cashier":
					$json=json_encode(array("status"=>"ok","type"=>"payjscashier"));
					echo $json;
					exit;
					break;
			}
			break;
	}
	$json=json_encode(array("status"=>"fail"));
	echo $json;
	exit;
}else{
	$feetype = isset($_GET['feetype']) ? addslashes($_GET['feetype']) : '';
	$feecookie = isset($_GET['feecookie']) ? addslashes($_GET['feecookie']) : '';
	$feecid = isset($_GET['feecid']) ? intval(urldecode($_GET['feecid'])) : '';
	$feeuid = isset($_GET['feeuid']) ? intval($_GET['feeuid']) : 0;
	
	$options = Typecho_Widget::widget('Widget_Options');
	$option=$options->plugin('WeMedia');
	$plug_url = $options->pluginUrl;
	
	$queryContent= $db->select()->from('table.contents')->where('cid = ?', $feecid); 
	$rowContent = $db->fetchRow($queryContent);
	
	switch($option->wemedia_paytype){
		case "payjs":
			switch($option->wemedia_payjstype){
				case "cashier":
					$cashierapi="https://payjs.cn/api/cashier";
					$time=time();
					$arr = [
						'body' => $options->title,               // 订单标题
						'out_trade_no' => date("YmdHis",$time) . rand(100000, 999999),       // 订单号
						'total_fee' => $rowContent["wemedia_price"]*100,             // 金额,单位:分
						'attach' => $rowContent["wemedia_price"]// 自定义数据
					];
					$payjs = new Payjs($arr,$option->payjs_wxpay_mchid,$option->payjs_wxpay_key,$option->payjs_wxpay_notify_url,$cashierapi);
					$data = $arr;
					$data['mchid'] = $option->payjs_wxpay_mchid;
					$data['notify_url'] = $option->payjs_wxpay_notify_url;
					$data['auto'] = 1;
					$data['hide'] = 1;
					$sign = $payjs->sign($data);
					$data = array(
						'feeid'   =>  $arr['out_trade_no'],
						'feecid'   =>  $feecid,
						'feeuid'     =>  $feeuid,
						'feeprice'=>$rowContent['wemedia_price'],
						'feetype'     =>  $feetype,
						'feestatus'=>0,
						'feeinstime'=>date('Y-m-d H:i:s',$time),
						'feecookie'=>$feecookie
					);
					$insert = $db->insert('table.wemedia_fee_item')->rows($data);
					$insertId = $db->query($insert);
					echo '
						<form id="payform" action="'.$cashierapi.'" method="post">
							<input type="hidden" name="mchid" value="'.$option->payjs_wxpay_mchid.'" />
							<input type="hidden" name="total_fee" value="'.$arr["total_fee"].'" />
							<input type="hidden" name="out_trade_no" value="'.$arr["out_trade_no"].'" />
							<input type="hidden" name="body" value="'.$arr["body"].'" />
							<input type="hidden" name="attach" value="'.$arr["attach"].'" />
							<input type="hidden" name="notify_url" value="'.$option->payjs_wxpay_notify_url.'" />
							<input type="hidden" name="auto" value="1" />
							<input type="hidden" name="hide" value="1" />
							<input type="hidden" name="sign" value="'.$sign.'" />
						</form>
						<script>document.getElementById("payform").submit();</script>
					';
					break;
			}
			break;
	}
}
?>