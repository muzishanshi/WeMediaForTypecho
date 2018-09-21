<?php
if(strpos($this->permalink,'?')){
	$url=substr($this->permalink,0,strpos($this->permalink,'?'));
}else{
	$url=$this->permalink;
}
$action = isset($_GET['action']) ? addslashes($_GET['action']) : '';
if($action=='delFeeItem'){
	$feeid = isset($_GET['feeid']) ? addslashes($_GET['feeid']) : '';
	$delete = $this->db->delete('table.wemedia_fee_item')->where('feeid = ?', $feeid);
	$deletedRows = $this->db->query($delete);
	$this->response->redirect($url);
}

$queryUser= $this->db->select()->from('table.users')->where('uid = ?', Typecho_Cookie::get('__typecho_uid')); 
$rowUser = $this->db->fetchRow($queryUser);
$queryContent= $this->db->select()->from('table.contents')->where('authorId = ?', Typecho_Cookie::get('__typecho_uid'))->where('type = ?', 'post')->where('status = ?', 'publish'); 
$rowContent = $this->db->fetchAll($queryContent);
$queryComments= $this->db->select()->from('table.comments')->where('authorId = ?', Typecho_Cookie::get('__typecho_uid'))->where('type = ?', "comment")->where('status = ?', "approved"); 
$rowComments = $this->db->fetchAll($queryComments);

$queryItem= $this->db->select()->from('table.wemedia_fee_item')->join('table.contents', 'table.wemedia_fee_item.feecid = table.contents.cid',Typecho_Db::INNER_JOIN)->where('authorId = ?', Typecho_Cookie::get('__typecho_uid')); 
$rowItem = $this->db->fetchAll($queryItem);
?>
<?php $this->need('templates/wemedia_user_header.php');?>
<div class="am-cf admin-main">
  <!-- sidebar start -->
  <?php $this->need('templates/wemedia_user_sidebar.php');?>
  <!-- sidebar end -->

  <!-- content start -->
  <div class="admin-content">
    <div class="admin-content-body">
      <div class="am-cf am-padding">
        <div class="am-fl am-cf"><strong class="am-text-primary am-text-lg">首页</strong> / <small>Anything is possible.</small></div>
      </div>

      <ul class="am-avg-sm-1 am-avg-md-4 am-margin am-padding am-text-center admin-content-list ">
        <li><a href="#" class="am-text-success"><span class="am-icon-btn am-icon-file-text"></span><br/>账户余额<br/><?=$rowUser["wemedia_money"];?></a></li>
        <li><a href="#" class="am-text-warning"><span class="am-icon-btn am-icon-briefcase"></span><br/>文章数<br/><?=count($rowContent);?></a></li>
        <li><a href="#" class="am-text-danger"><span class="am-icon-btn am-icon-recycle"></span><br/>评论数<br/><?=count($rowComments);?></a></li>
        <li><a href="#" class="am-text-secondary"><span class="am-icon-btn am-icon-user-md"></span><br/>订单数<br/><?=count($rowItem);?></a></li>
      </ul>
      <div class="am-g">
        <div class="am-u-sm-12">
		<?php
		if($this->user->group=='administrator'){
			$queryBuyItem= $this->db->select()->from('table.wemedia_fee_item'); 
		}else if($this->user->group!=NULL&&$this->user->group!='administrator'){
			$queryBuyItem= $this->db->select()->from('table.wemedia_fee_item')->where('feeuid = ?', Typecho_Cookie::get('__typecho_uid')); 
		}
		$page_now_buy = isset($_GET['page_now_buy']) ? intval($_GET['page_now_buy']) : 1;
		if($page_now_buy<1){
			$page_now_buy=1;
		}
		$resultTotal = $this->db->fetchAll($queryBuyItem);
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
		if($this->user->group=='administrator'){
			$queryBuyItem= $this->db->select()->from('table.wemedia_fee_item')->join('table.contents', 'table.wemedia_fee_item.feecid = table.contents.cid',Typecho_Db::INNER_JOIN)->join('table.users', 'table.wemedia_fee_item.feeuid = table.users.uid',Typecho_Db::INNER_JOIN)->offset($i)->limit($page_rec); 
		}else if($this->user->group!=NULL&&$this->user->group!='administrator'){
			$queryBuyItem= $this->db->select()->from('table.wemedia_fee_item')->join('table.contents', 'table.wemedia_fee_item.feecid = table.contents.cid',Typecho_Db::INNER_JOIN)->where('feeuid = ?', Typecho_Cookie::get('__typecho_uid'))->offset($i)->limit($page_rec); 
		}
		$rowBuyItem = $this->db->fetchAll($queryBuyItem);
		?>
		<?php if(count($rowBuyItem)>0){?>
          <div class="am-tabs" data-am-tabs>
			  <ul class="am-tabs-nav am-nav am-nav-tabs">
				<li class="am-active"><a href="#tab-4-1">买入订单</a></li>
			  </ul>
			  <div class="am-tabs-bd am-tabs-bd-ofv">
				<div class="am-tab-panel am-active" id="tab-4-1">
				   <table class="am-table am-table-bd am-table-striped admin-content-table">
						<thead>
						<tr>
						  <th>订单</th>
						  <th>文章</th>
						  <th>卖家</th>
						  <?php if($this->user->group=='administrator'){?><th>买家</th><?php }?>
						  <th>价格</th>
						  <th>方式</th>
						  <th>状态</th>
						  <th>管理</th>
						</tr>
						</thead>
						<tbody>
						<?php
						foreach($rowBuyItem as $value){
						?>
						<tr>
							<td><?=$value["feeid"];?></td>
							<td><a href="javascript:;"><?=$value["title"];?></a></td>
							<td>
								<?php
								$queryAuthor= $this->db->select()->from('table.users')->where('uid = ?', $value["authorId"]); 
								$rowAuthor = $this->db->fetchRow($queryAuthor);
								echo $rowAuthor["mail"];
								?>
							</td>
							<?php if($this->user->group=='administrator'){?>
							<td><?=$value["mail"];?></td>
							<?php }?>
							<td><span class="am-badge am-badge-success"><?=$value["feeprice"];?></span></td>
							<td>
								<?php
								switch($value["feetype"]){
									case "alipay":
									case "ALIPAY":
										echo "支付宝支付";break;
									case "wxpay":
									case "WEIXIN_DAIXIAO":
										echo "微信支付";break;
									case "qqpay":echo "QQ钱包支付";break;
									case "bank_pc":echo "网银支付";break;
									case "tlepay":echo "同乐支付";break;
									default:echo "其他";break;
								}
								?>
							</td>
							<td>
								<?php
								switch($value["feestatus"]){
									case 0:echo "未付款";break;
									case 1:echo "付款成功";break;
									case 2:echo "付款失败";break;
								}
								?>
							</td>
							<td>
								<?php if($value["feestatus"]!=1){?>
								<div class="am-dropdown" data-am-dropdown>
								  <button class="am-btn am-btn-default am-btn-xs am-dropdown-toggle" data-am-dropdown-toggle><span class="am-icon-cog"></span> <span class="am-icon-caret-down"></span></button>
								  <ul class="am-dropdown-content">
									<?php if($value["feeuid"]==Typecho_Cookie::get('__typecho_uid')){?>
									<li><!--<a href="javascript:;">支付</a>--></li>
									<?php }?>
									<li><a href="javascript:delFeeItem('<?=$value["feeid"]?>');">删除</a></li>
								  </ul>
								</div>
								<?php }?>
							</td>
						</tr>
						<?php
						}
						?>
						</tbody>
					</table>
					<ul class="am-pagination blog-pagination">
					  <?php if($page_now_buy!=1){?>
						<li class="am-pagination-prev"><a href="<?=$url;?>?page_now_buy=1">首页</a></li>
					  <?php }?>
					  <?php if($page_now_buy>1){?>
						<li class="am-pagination-prev"><a href="<?=$url;?>?page_now_buy=<?=$before_page;?>">&laquo; 上一页</a></li>
					  <?php }?>
					  <?php if($page_now_buy<$page){?>
						<li class="am-pagination-next"><a href="<?=$url;?>?page_now_buy=<?=$after_page;?>">下一页 &raquo;</a></li>
					  <?php }?>
					  <?php if($page_now_buy!=$page){?>
						<li class="am-pagination-next"><a href="<?=$url;?>?page_now_buy=<?=$page;?>">尾页</a></li>
					  <?php }?>
					</ul>
				</div>
			  </div>
		  </div>
		  <p></p>
		  <?php }?>
		  <?php
			if($this->user->group=='administrator'){
				$querySellItem= $this->db->select()->from('table.wemedia_fee_item'); 
			}else if($this->user->group!=NULL&&$this->user->group!='administrator'){
				$querySellItem= $this->db->select()->from('table.wemedia_fee_item')->join('table.contents', 'table.wemedia_fee_item.feecid = table.contents.cid',Typecho_Db::INNER_JOIN)->where('authorId = ?', Typecho_Cookie::get('__typecho_uid')); 
			}
			$page_now = isset($_GET['page_now']) ? intval($_GET['page_now']) : 1;
			if($page_now<1){
				$page_now=1;
			}
			$resultTotal = $this->db->fetchAll($querySellItem);
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
			if($this->user->group=='administrator'){
				$querySellItem= $this->db->select()->from('table.wemedia_fee_item')->join('table.contents', 'table.wemedia_fee_item.feecid = table.contents.cid',Typecho_Db::INNER_JOIN)->offset($i)->limit($page_rec); 
			}else if($this->user->group!=NULL&&$this->user->group!='administrator'){
				$querySellItem= $this->db->select()->from('table.wemedia_fee_item')->join('table.contents', 'table.wemedia_fee_item.feecid = table.contents.cid',Typecho_Db::INNER_JOIN)->where('authorId = ?', Typecho_Cookie::get('__typecho_uid'))->offset($i)->limit($page_rec); 
			}
			$rowSellItem = $this->db->fetchAll($querySellItem);
		  ?>
		  <?php if(count($rowSellItem)>0){?>
		  <div class="am-tabs" data-am-tabs>
			  <ul class="am-tabs-nav am-nav am-nav-tabs">
				<li class="am-active"><a href="#tab-4-1">卖出订单</a></li>
			  </ul>
			  <div class="am-tabs-bd am-tabs-bd-ofv">
				<div class="am-tab-panel am-active" id="tab-4-1">
				   <table class="am-table am-table-bd am-table-striped admin-content-table">
						<thead>
						<tr>
						  <th>订单</th>
						  <th>文章</th>
						  <?php if($this->user->group=='administrator'){?><th>卖家</th><?php }?>
						  <th>买家</th>
						  <th>价格</th>
						  <th>方式</th>
						  <th>状态</th>
						  <th>管理</th>
						</tr>
						</thead>
						<tbody>
						<?php
						foreach($rowSellItem as $value){
						?>
						<tr>
							<td><?=$value["feeid"];?></td>
							<td><a href="javascript:;"><?=$value["title"];?></a></td>
							<?php
							if($this->user->group=='administrator'){
								$queryAuthor= $this->db->select()->from('table.users')->where('uid = ?', $value["authorId"]); 
								$rowAuthor = $this->db->fetchRow($queryAuthor);
								?>
								<td><?=$rowAuthor["mail"];?></td>
								<?php
							}
							?>
							<td>
								<?php
								$queryBuyer= $this->db->select()->from('table.users')->where('uid = ?', $value["feeuid"]); 
								$rowBuyer = $this->db->fetchRow($queryBuyer);
								echo $rowBuyer["mail"];
								?>
							</td>
							<td><span class="am-badge am-badge-success"><?=$value["feeprice"];?></span></td>
							<td>
								<?php
								switch($value["feetype"]){
									case "alipay":
									case "ALIPAY":
										echo "支付宝支付";break;
									case "wxpay":
									case "WEIXIN_DAIXIAO":
										echo "微信支付";break;
									case "qqpay":echo "QQ钱包支付";break;
									case "bank_pc":echo "网银支付";break;
									case "tlepay":echo "同乐支付";break;
									default:echo "其他";break;
								}
								?>
							</td>
							<td>
								<?php
								switch($value["feestatus"]){
									case 0:echo "未付款";break;
									case 1:echo "付款成功";break;
									case 2:echo "付款失败";break;
								}
								?>
							</td>
							<td>
								<?php if($value["feestatus"]!=1){?>
								<div class="am-dropdown" data-am-dropdown>
								  <button class="am-btn am-btn-default am-btn-xs am-dropdown-toggle" data-am-dropdown-toggle><span class="am-icon-cog"></span> <span class="am-icon-caret-down"></span></button>
								  <ul class="am-dropdown-content">
									<li><a href="javascript:delFeeItem('<?=$value["feeid"]?>');">删除</a></li>
								  </ul>
								</div>
								<?php }?>
							</td>
						</tr>
						<?php
						}
						?>
						</tbody>
					</table>
					<ul class="am-pagination blog-pagination">
					  <?php if($page_now!=1){?>
						<li class="am-pagination-prev"><a href="<?=$url;?>?page_now=1">首页</a></li>
					  <?php }?>
					  <?php if($page_now>1){?>
						<li class="am-pagination-prev"><a href="<?=$url;?>?page_now=<?=$before_page;?>">&laquo; 上一页</a></li>
					  <?php }?>
					  <?php if($page_now<$page){?>
						<li class="am-pagination-next"><a href="<?=$url;?>?page_now=<?=$after_page;?>">下一页 &raquo;</a></li>
					  <?php }?>
					  <?php if($page_now!=$page){?>
						<li class="am-pagination-next"><a href="<?=$url;?>?page_now=<?=$page;?>">尾页</a></li>
					  <?php }?>
					</ul>
				</div>
			  </div>
		  </div>
		  <?php }?>
        </div>
      </div>

    </div>

    <?php $this->need('templates/wemedia_user_footer.php');?>
  </div>
  <!-- content end -->

</div>

<a href="#" class="am-icon-btn am-icon-th-list am-show-sm-only admin-menu" data-am-offcanvas="{target: '#admin-offcanvas'}"></a>
<script>
function delFeeItem(id){
	if(confirm("确定要删除此订单吗？")){
		location.href="?action=delFeeItem&feeid="+id;
	}
}
</script>