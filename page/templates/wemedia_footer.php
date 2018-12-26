<?php
$options = Typecho_Widget::widget('Widget_Options');
$plug_url = $options->pluginUrl;
?>
<!--[if lt IE 9]>
<script src="https://libs.baidu.com/jquery/1.11.1/jquery.min.js"></script>
<script src="https://cdn.staticfile.org/modernizr/2.8.3/modernizr.js"></script>
<![endif]-->
<script src="https://cdnjs.cloudflare.com/ajax/libs/amazeui/2.7.2/js/amazeui.min.js" type="text/javascript"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/amazeui/2.7.2/js/amazeui.ie8polyfill.min.js" type="text/javascript"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/amazeui/2.7.2/js/amazeui.widgets.helper.min.js" type="text/javascript"></script>
<script src="<?=$plug_url?>/WeMedia/js/app.js"></script>
</body>
</html>