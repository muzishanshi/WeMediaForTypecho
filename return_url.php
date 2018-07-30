<?php
/*
 * 同步回调通知页面 (需商户在下单请求中传递Return_url)
 * 2017-08-06
 * https://www.ispay.cn
 */
include '../../../config.inc.php';
require_once 'libs/ispay/lib/Ispay.class.php';
$db = Typecho_Db::get();
$prefix = $db->getPrefix();

$options = Typecho_Widget::widget('Widget_Options');
$option=$options->plugin('WeMedia');
$plug_url = $options->pluginUrl;
$Ispay = new ispayService($option->ispayid, $option->ispaykey);
//设置页头
header("Content-type: text/html; charset=utf-8");
//设置时区
date_default_timezone_set('Asia/Shanghai');
//接受ISPAY通知返回的支付渠道
$Array['payChannel'] = @$_POST['payChannel']; //(支付通道)
//接受ISPAY通知返回的支付金额
$Array['Money'] = @$_POST['Money'];  //(单位分)
//接受ISPAY通知返回的订单号
$Array['orderNumber'] = @$_POST['orderNumber'];  //(商户订单号)
//接受ISPAY通知返回的附加数据
$Array['attachData'] = @$_POST['attachData'];  //(商户自定义附加数据)
//接受ISPAY通知返回的回调签名
$Array['callbackSign'] = @$_POST['callbackSign'];  //(详情查看ISPAY开发文档)
?>
<?php
//回调签名校验
if(!$Ispay->callbackSignCheck($Array)){
	echo "付款失败";
}else{
	$updateItem = $db->update('table.wemedia_fee_item')->rows(array('feestatus'=>1))->where('feeid=?',@$Array['orderNumber']);
	$updateItemRows= $db->query($updateItem);
	$queryItem= $db->select()->from('table.wemedia_fee_item')->where('feeid = ?', $Array['orderNumber']); 
	$rowItem = $db->fetchRow($queryItem);
	$queryContents= $db->select()->from('table.contents')->where('cid = ?', $rowItem['feecid']); 
	$rowContents = $db->fetchRow($queryContents);
	$queryUser= $db->select()->from('table.users')->where('uid = ?', $rowContents['authorId']); 
	$rowUser = $db->fetchRow($queryUser);
	$updateUser = $db->update('table.users')->rows(array('wemedia_money'=>$rowUser['wemedia_money']+@$Array['Money']/100))->where('uid=?',$rowContents['authorId']);
	$updateUserRows= $db->query($updateUser);
	if($updateUserRows){
		echo "<script>location.href='".$Array['attachData']."';</script>";
		?>
		<div>
			<h2>付款<?php echo @$Array['Money']/100; ?> 元成功！</h2>
		</div>
		
		<?php
	}
}
?>