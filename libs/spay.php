<?php
function spay_wpay_verify($id,$key,$type) {
   //异步验证函数 用于验证签名是否正确
   //返回数组 分别是商户订单号和支付金额
   //如果验证前面没通过则返回false
	$sign_data=$type;//QQ钱包接口的话这里wx写qq
	$sign_data.=$id;
	$sign_data.=$_REQUEST['hao'];
	$sign_data.=$_REQUEST['money'];
    if($_REQUEST['sign'] != md5(md5($sign_data).$key)) return false;
    $ret['orderNumber']=$_REQUEST['hao'];
    $ret['Money']=$_REQUEST['money'];
    return $ret;   
}
function spay_wpay_pay($pdata,$key,$type) {//提交支付函数 
	//返回数组 
	//       array(
	//              '二维码'=>'二维码的base64格式的图片',
	//              '跳转地址'=>'跳转过去支付的地址 有跳转地址则不会返回二维码图片',
	//              '最晚支付时间'=>'支付的有效期',
	//              '错误信息'=>'如果没出现错误则为空'
	//        )
    $data=array();
    $data['service'] = $type;//QQ钱包接口的话这里wx写qq
    $data['id']=  $pdata['SPayId'];
    $data['hao']= $pdata['orderNumber'];
    $data['money'] =  $pdata['Money'];
    $data['notify_url']= $pdata['Notify_url'];
    $data['return_url']= $pdata['Return_url'];
    $sign_data=$type;
    $sign_data.= $pdata['SPayId'];
    $sign_data.=$pdata['orderNumber'];
    $sign_data.=$pdata['Money'];
    $sign_data.=$pdata['Notify_url'];
    $data['sign']= md5(md5($sign_data).$key);
    $ret=$pdata;
    //QQ钱包接口的话下面地址里面的  wxpay.php 改成qqpay.php
    $ret['qrcode'] = file_get_contents("http://www.dayyun.com/pay/pay/wxpay.php?".http_build_query($data));
    $retd=$ret['qrcode'];
    $ret['LatestPayTime'] = date("Y-m-d H:i:s",time()+5*60);
    //支付时间为5分钟  如果超过5分钟支付 有一定机率不会到账
     
    $ret['error'] = substr($retd,
                        strlen('msg:')+strpos($retd, 'msg:'),
                        (strlen($retd) - strpos($retd, '-->'))*(-1)
                       );
    $ret['url'] = substr($retd,
                        strlen('<!-- url:')+strpos($retd, '<!-- url:'),
                        (strlen($retd) - strpos($retd, '-->'))*(-1)
                       );
     
    if(!empty($ret['error'])) $ret['qrcode']="";
    return $ret;  
}
?>