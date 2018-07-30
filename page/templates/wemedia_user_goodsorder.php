<?php
if(strpos($this->permalink,'?')){
	$url=substr($this->permalink,0,strpos($this->permalink,'?'));
}else{
	$url=$this->permalink;
}
if(strpos($this->permalink,'?')){
	$url=substr($this->permalink,0,strpos($this->permalink,'?'));
}else{
	$url=$this->permalink;
}
?>
<?php $this->need('templates/wemedia_user_header.php');?>
<div class="am-cf admin-main">
  <!-- sidebar start -->
  <?php $this->need('templates/wemedia_user_sidebar.php');?>
  <!-- sidebar end -->

  <!-- content start -->
  <div class="admin-content">
    <div class="admin-content-body">
  <div class="am-cf am-padding am-padding-bottom-0">
	<div class="am-fl am-cf"><strong class="am-text-primary am-text-lg">商品订单</strong> / <small>记录</small></div>
  </div>

  <hr>

  <div class="am-g">
	<div class="am-u-sm-12 am-u-md-6">
	  <div class="am-btn-toolbar">
		<div class="am-btn-group am-btn-group-xs">
		  
		</div>
	  </div>
	</div>
	<div class="am-u-sm-12 am-u-md-3">
	  <div class="am-form-group">
		
	  </div>
	</div>
	<div class="am-u-sm-12 am-u-md-3">
	  <div class="am-input-group am-input-group-sm">
		
	  </div>
	</div>
  </div>

  <div class="am-g">
	<div class="am-u-sm-12">
		<div class="am-tabs" data-am-tabs>
		  <ul class="am-tabs-nav am-nav am-nav-tabs">
			<li class="am-active"><a href="#tab-4-1">买入订单</a></li>
		  </ul>
		  <div class="am-tabs-bd am-tabs-bd-ofv">
			<div class="am-tab-panel am-active" id="tab-4-1">
				<table class="am-table am-table-striped am-table-hover table-main">
				  <thead>
				  <tr>
					<th class="table-costname">商品名称</th>
					<th class="table-costseller">卖家</th>
					<th class="table-costpoint">花费积分</th>
					<th class="table-costinstime">时间</th>
				  </tr>
				  </thead>
				  <tbody>
				  <?php
					$queryOrder= $this->db->select()->from('table.wemedia_point_cost')->where('pointuid = ?', Typecho_Cookie::get('__typecho_uid')); 
					$page_now = isset($_GET['page_now']) ? intval($_GET['page_now']) : 1;
					if($page_now<1){
						$page_now=1;
					}
					$resultTotal = $this->db->fetchAll($queryOrder);
					$page_rec=10;
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
					$queryOrder= $this->db->select()->from('table.wemedia_point_cost')->join('table.users', 'table.wemedia_point_cost.pointuid = table.users.uid',Typecho_Db::INNER_JOIN)->where('pointuid = ?', Typecho_Cookie::get('__typecho_uid'))->order('table.wemedia_point_cost.pointinstime',Typecho_Db::SORT_DESC)->offset($i)->limit($page_rec); 
					$rowOrder = $this->db->fetchAll($queryOrder);
					foreach($rowOrder as $value){
					  $querySeller= $this->db->select()->from('table.wemedia_goods')->join('table.users', 'table.wemedia_goods.goodsuid = table.users.uid',Typecho_Db::INNER_JOIN)->where('goodsid = ?', $value["pointgid"]); 
					  $rowSeller = $this->db->fetchRow($querySeller);
					  ?>
					  <tr>
						<td><?=$rowSeller["goodsname"];?></td>
						<td><?php echo $rowSeller["screenName"].'（'.$rowSeller["mail"].'）';?></td>
						<td><?=$value["pointnum"];?></td>
						<td><?=$value["pointinstime"];?></td>
					  </tr>
					  <?php
				  }
				  ?>
				  </tbody>
				</table>
				<ul class="am-pagination blog-pagination">
				  <?php if($page_now!=1){?>
					<li class="am-pagination-prev"><a href="<?=$url;?>?page=goodsorder&page_now=1">首页</a></li>
				  <?php }?>
				  <?php if($page_now>1){?>
					<li class="am-pagination-prev"><a href="<?=$url;?>?page=goodsorder&page_now=<?=$before_page;?>">&laquo; 上一页</a></li>
				  <?php }?>
				  <?php if($page_now<$page){?>
					<li class="am-pagination-next"><a href="<?=$url;?>?page=goodsorder&page_now=<?=$after_page;?>">下一页 &raquo;</a></li>
				  <?php }?>
				  <?php if($page_now!=$page){?>
					<li class="am-pagination-next"><a href="<?=$url;?>?page=goodsorder&page_now=<?=$page;?>">尾页</a></li>
				  <?php }?>
				</ul>
			</div>
		  </div>
	  </div>
	  <p></p>
	  <div class="am-tabs" data-am-tabs>
		  <ul class="am-tabs-nav am-nav am-nav-tabs">
			<li class="am-active"><a href="#tab-4-1">卖出订单</a></li>
		  </ul>
		  <div class="am-tabs-bd am-tabs-bd-ofv">
			<div class="am-tab-panel am-active" id="tab-4-1">
				<table class="am-table am-table-striped am-table-hover table-main">
				  <thead>
				  <tr>
					<th class="table-costname">商品名称</th>
					<th class="table-costbuyer">买家</th>
					<th class="table-costpoint">花费积分</th>
					<th class="table-costinstime">时间</th>
				  </tr>
				  </thead>
				  <tbody>
				  <?php
					$queryOrder= $this->db->select()->from('table.wemedia_point_cost')->join('table.wemedia_goods', 'table.wemedia_point_cost.pointgid = table.wemedia_goods.goodsid',Typecho_Db::INNER_JOIN)->where('goodsuid = ?', Typecho_Cookie::get('__typecho_uid')); 
					$page_now_buy = isset($_GET['page_now_buy']) ? intval($_GET['page_now_buy']) : 1;
					if($page_now_buy<1){
						$page_now_buy=1;
					}
					$resultTotal = $this->db->fetchAll($queryOrder);
					$page_rec=10;
					$totalrec=count($resultTotal);
					$page=ceil($totalrec/$page_rec);
					if($page_now_buy>$page){
						$page_now_buy=$page;
					}
					if($page_now_buy<=1){
						$before_page=1;
						if($page>1){
							$after_page=$page_now_buy+1;
						}else{
							$after_page=1;
						}
					}else{
						$before_page=$page_now_buy-1;
						if($page_now_buy<$page){
							$after_page=$page_now_buy+1;
						}else{
							$after_page=$page;
						}
					}
					$i=($page_now_buy-1)*$page_rec<0?0:($page_now_buy-1)*$page_rec;
					$queryOrder= $this->db->select()->from('table.wemedia_point_cost')->join('table.wemedia_goods', 'table.wemedia_point_cost.pointgid = table.wemedia_goods.goodsid',Typecho_Db::INNER_JOIN)->where('goodsuid = ?', Typecho_Cookie::get('__typecho_uid'))->order('table.wemedia_point_cost.pointinstime',Typecho_Db::SORT_DESC)->offset($i)->limit($page_rec); 
					$rowOrder = $this->db->fetchAll($queryOrder);
					foreach($rowOrder as $value){
					  $queryBuyer= $this->db->select()->from('table.users')->where('uid = ?', $value["pointuid"]); 
					  $rowBuyer = $this->db->fetchRow($queryBuyer);
					  ?>
					  <tr>
						<td><?=$value["goodsname"];?></td>
						<td><?php echo $rowBuyer["screenName"].'（'.$rowBuyer["mail"].'）';?></td>
						<td><?=$value["pointnum"];?></td>
						<td><?=$value["pointinstime"];?></td>
					  </tr>
					  <?php
				  }
				  ?>
				  </tbody>
				</table>
				<ul class="am-pagination blog-pagination">
				  <?php if($page_now_buy!=1){?>
					<li class="am-pagination-prev"><a href="<?=$url;?>?page=goodsorder&page_now_buy=1">首页</a></li>
				  <?php }?>
				  <?php if($page_now_buy>1){?>
					<li class="am-pagination-prev"><a href="<?=$url;?>?page=goodsorder&page_now_buy=<?=$before_page;?>">&laquo; 上一页</a></li>
				  <?php }?>
				  <?php if($page_now_buy<$page){?>
					<li class="am-pagination-next"><a href="<?=$url;?>?page=goodsorder&page_now_buy=<?=$after_page;?>">下一页 &raquo;</a></li>
				  <?php }?>
				  <?php if($page_now_buy!=$page){?>
					<li class="am-pagination-next"><a href="<?=$url;?>?page=goodsorder&page_now_buy=<?=$page;?>">尾页</a></li>
				  <?php }?>
				</ul>
			</div>
		  </div>
	  </div>
	</div>

  </div>
</div>

    <?php $this->need('templates/wemedia_user_footer.php');?>
  </div>
  <!-- content end -->

</div>

<a href="#" class="am-icon-btn am-icon-th-list am-show-sm-only admin-menu" data-am-offcanvas="{target: '#admin-offcanvas'}"></a>