<?php
$options = Typecho_Widget::widget('Widget_Options');
$plug_url = $options->pluginUrl;
?>
<!--[if lt IE 9]>
<script src="http://libs.baidu.com/jquery/1.11.1/jquery.min.js"></script>
<script src="http://cdn.staticfile.org/modernizr/2.8.3/modernizr.js"></script>
<![endif]-->
<script src="http://cdn.amazeui.org/amazeui/2.7.2/js/amazeui.min.js" type="text/javascript"></script>
<script src="http://cdn.amazeui.org/amazeui/2.7.2/js/amazeui.ie8polyfill.min.js" type="text/javascript"></script>
<script src="http://cdn.amazeui.org/amazeui/2.7.2/js/amazeui.widgets.helper.min.js" type="text/javascript"></script>
<script src="<?=$plug_url?>/WeMedia/js/app.js"></script>
</body>
</html>