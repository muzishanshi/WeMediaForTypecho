<?php
include 'header.php';
include 'menu.php';

$options=Typecho_Widget::widget('Widget_Options');
$option=$options->plugin('WeMedia');
$plug_url = $options->pluginUrl;
$versions=explode("/",Typecho_Widget::widget('Widget_Options')->Version);
if($versions[1]>="19.10.15"){
	WeMedia_Plugin::$panel='WeMedia/templates/manage_wemedia2.php';
}
?>
    <div class="admin-content-body typecho-list row typecho-page-main">
      <div class="am-cf am-padding typecho-page-title">
			<div class="am-fl am-cf">
				<?php include 'page-title.php'; ?>
			</div>
	  </div>
      <div class="am-g typecho-list-operate clearfix">
		<form method="get">
			<div class="operate am-u-sm-12 am-u-md-6">
			  <div class="am-btn-toolbar typecho-option-tabs">
				<div class="am-btn-group am-btn-group-xs">
				  <a href="https://www.tongleer.com" target="_blank" title="同乐儿" class="am-btn am-btn-default"><?php _e('官网'); ?></a>
				</div>
			  </div>
			</div>
		</form>
      </div>

      <div class="am-g">
		<form method="post" name="manage_posts" class="am-form">
			<div class="typecho-table-wrap am-u-sm-12">
				<table class="typecho-list-table">
					<thead>
						<tr>
							<th><?php _e('ID'); ?></th>
							<th><?php _e('文章'); ?></th>
							<th><?php _e('单价'); ?></th>
							<th><?php _e('渠道'); ?></th>
							<th><?php _e('状态'); ?></th>
							<th><?php _e('时间'); ?></th>
							<th><?php _e('付款邮箱'); ?></th>
							<th><?php _e('cookie'); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php
						$queryTotal= $db->select()->from('table.wemedia_fee_item')->join('table.contents', 'table.wemedia_fee_item.feecid = table.contents.cid',Typecho_Db::INNER_JOIN);
						$page_now = isset($_GET['page_now']) ? intval($_GET['page_now']) : 1;
						if($page_now<1){
							$page_now=1;
						}
						$resultTotal = $db->fetchAll($queryTotal);
						$page_rec=20;
						$totalrec=count($resultTotal);
						$page=ceil($totalrec/$page_rec);
						if($page_now>$page){
							$page_now=$page;
						}
						if($page_now<=1){
							$before_page=1;
							if($page>1){
								$after_page=$page_now+1;
							}else{
								$after_page=1;
							}
						}else{
							$before_page=$page_now-1;
							if($page_now<$page){
								$after_page=$page_now+1;
							}else{
								$after_page=$page;
							}
						}
						$i=($page_now-1)*$page_rec<0?0:($page_now-1)*$page_rec;
						$query= $db->select()->from('table.wemedia_fee_item')->join('table.contents', 'table.wemedia_fee_item.feecid = table.contents.cid',Typecho_Db::INNER_JOIN)->order('feeinstime',Typecho_Db::SORT_DESC)->offset($i)->limit($page_rec);
						$result = $db->fetchAll($query);
						if($result){
							foreach($result as $value){
								?>
								<tr>
									<td><?=$value["feeid"];?></td>
									<td><a href="<?php $options->adminUrl('write-post.php?cid=' . $value["feecid"]); ?>"><?=mb_strimwidth($value["title"],0,25,"...");?></a></td>
									<td><?=$value["feeprice"];?></td>
									<td>
										<?php if($value["feetype"]=="alipay"){echo "支付宝";}else if($value["feetype"]=="wx"){echo "微信";}else if($value["feetype"]=="qqpay"){echo "QQ";}?>
									</td>
									<td>
										<?php
										if($value["feestatus"]==0){
											?>
											未付款&nbsp;<a href="<?php $security->index('/action/manage_wemedia?do=delItem&feeid='.$value["feeid"]); ?>">删除</a>
											<?php
										}else if($value["feestatus"]==1){
											echo "<font color='green'>付款成功</font>";
										}else if($value["feestatus"]==2){
											echo "<font color='red'>付款失败</font>";
										}
										?>
									</td>
									<td><?=$value["feeinstime"];?></td>
									<td><?=$value["feemail"];?></td>
									<td><?=$value["feecookie"];?></td>
								</tr>
								<?php
							}
						}else{
							?>
							<tr>
								<td colspan="8"><h6 class="typecho-list-table-title"><?php _e('没有订单'); ?></h6></td>
							</tr>
							<?php
						}
						?>
					</tbody>
				</table>
				<div class="am-cf">
				  
				  <div class="am-fr">
					<ul class="am-pagination typecho-pager">
						<?php if($page_now!=1){?>
							<li class="am-pagination-prev"><a href="<?php $options->adminUrl('extending.php?panel=' . WeMedia_Plugin::$panel . '&page_now=1'); ?>">首页</a></li>
						  <?php }?>
						  <?php if($page_now>1){?>
							<li class="am-pagination-prev"><a href="<?php $options->adminUrl('extending.php?panel=' . WeMedia_Plugin::$panel . '&page_now='.$before_page); ?>">&laquo; 上一页</a></li>
						  <?php }?>
						  <?php if($page_now<$page){?>
							<li class="am-pagination-next"><a href="<?php $options->adminUrl('extending.php?panel=' . WeMedia_Plugin::$panel . '&page_now='.$after_page); ?>">下一页 &raquo;</a></li>
						  <?php }?>
						  <?php if($page_now!=$page){?>
							<li class="am-pagination-next"><a href="<?php $options->adminUrl('extending.php?panel=' . WeMedia_Plugin::$panel . '&page_now='.$page); ?>">尾页</a></li>
						<?php }?>
					</ul>
				  </div>
				  
				</div>
				<hr />
				<p>共 <?=$totalrec;?> 条记录</p>
			</div>
		</form>
      </div>
    </div>
	<footer class="admin-content-footer">
	<?php include 'copyright.php';?>
	</footer>
<?php
include 'copyright.php';
include 'common-js.php';
include 'table-js.php';
include 'footer.php';
?>