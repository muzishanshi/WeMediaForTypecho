<!doctype html>
<html class="no-js fixed-layout">
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <title><?php if($this->user->group==NULL){echo "登录";}else{echo "用户中心";}?></title>
  <meta name="description" content="">
  <meta name="keywords" content="">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="format-detection" content="telephone=no">
  <meta http-equiv="Cache-Control" content="no-siteapp" />
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="renderer" content="webkit">
  <meta http-equiv="Cache-Control" content="no-siteapp" />
  <link rel="icon" type="image/png" href="https://ws3.sinaimg.cn/large/005V7SQ5ly1fykchvb7s5j300s00s744.jpg">
  <meta name="apple-mobile-web-app-title" content="Amaze UI" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/amazeui/2.7.2/css/amazeui.min.css"/>
  <script src="https://apps.bdimg.com/libs/jquery/1.7.1/jquery.min.js" type="text/javascript"></script>
</head>
<body>
<!--[if lt IE 9]>
    <script type="text/javascript">
        var str = '<p class="browsehappy">你正在使用<strong>过时</strong>的浏览器，此网站框架暂不支持。 请 <a href="https://browsehappy.com/" target="_blank">升级浏览器</a>以获得更好的体验！</p>';
        document.writeln("<pre style='text-align:center;color:#fff;background-color:#0cc; height:100%;border:0;position:fixed;top:0;left:0;width:100%;z-index:1234'>" +
                "<h2 style='padding-top:200px;margin:0'><strong>" + str + "<br/></strong></h2><h2 style='margin:0'><strong>如果你的使用的是双核浏览器,请切换到极速模式访问<br/></strong></h2></pre>");
        document.execCommand("Stop");
    </script>
<![endif]-->