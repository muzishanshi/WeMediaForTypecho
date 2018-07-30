<?php
$options = Typecho_Widget::widget('Widget_Options');
$plug_url = $options->pluginUrl;
$option=$options->plugin('WeMedia');
if(strpos($this->permalink,'?')){
	$url=substr($this->permalink,0,strpos($this->permalink,'?'));
}else{
	$url=$this->permalink;
}
?>
<div class="admin-sidebar am-offcanvas" id="admin-offcanvas">
    <div class="am-offcanvas-bar admin-offcanvas-bar">
      <ul class="am-list admin-sidebar-list">
        <li><a href="<?=$url;?>"><span class="am-icon-home"></span> 首页</a></li>
		<li><a href="<?=$url;?>?page=article"><span class="am-icon-pencil-square-o"></span> 文章</a></li>
        <li><a href="<?=$url;?>?page=comment"><span class="am-icon-table"></span> 评论</a></li>
		<li class="admin-parent">
          <a class="am-cf" data-am-collapse="{target: '#collapse-nav'}"><span class="am-icon-file"></span> 电商 <span class="am-icon-angle-right am-fr am-margin-right"></span></a>
          <ul class="am-list am-collapse admin-sidebar-sub am-in" id="collapse-nav">
            <li><a href="<?=$url;?>?page=goods"><span class="am-icon-shopping-bag"></span> 商品</a></li>
            <li><a href="<?=$url;?>?page=goodsorder"><span class="am-icon-wpforms"></span> 订单</a></li>
          </ul>
        </li>
		
		<?php if($this->user->group=='administrator'){?>
		<li><a href="<?=$url;?>?page=member"><span class="am-icon-user"></span> 用户</a></li>
		<?php }?>
        <li class="admin-parent">
          <a class="am-cf" data-am-collapse="{target: '#collapse-nav'}"><span class="am-icon-file"></span> 账户 <span class="am-icon-angle-right am-fr am-margin-right"></span></a>
          <ul class="am-list am-collapse admin-sidebar-sub am-in" id="collapse-nav">
            <li><a href="<?=$url;?>?page=info" class="am-cf"><span class="am-icon-check"></span> 资料<span class="am-icon-star am-fr am-margin-right admin-icon-yellow"></span></a></li>
            <li><a href="<?=$url;?>?page=money"><span class="am-icon-puzzle-piece"></span> 提现</a></li>
            <li><a href="<?=$url;?>?page=water"><span class="am-icon-th"></span> 流水</a></li>
          </ul>
        </li>
        <li><a href="<?php $options->logoutUrl(); ?>"><span class="am-icon-sign-out"></span> 注销</a></li>
      </ul>

      <div class="am-panel am-panel-default admin-sidebar-panel">
        <div class="am-panel-bd">
          <p><span class="am-icon-bookmark"></span> 公告</p>
          <p><?php echo $option->notice;?></p>
        </div>
      </div>

    </div>
</div>