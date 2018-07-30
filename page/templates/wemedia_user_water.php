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
if($this->user->group=='administrator'){
	$status = isset($_GET['status']) ? addslashes($_GET['status']) : '';
	if($status=='ok'){
		$moneyid = isset($_GET['moneyid']) ? addslashes($_GET['moneyid']) : '';
		$update = $this->db->update('table.wemedia_money_item')->rows(array('moneystatus'=>1))->where('moneyid=?',$moneyid);
		$updateRows= $this->db->query($update);
		$this->response->redirect($url."?page=water");
	}else if($status=='refuse'){
		$moneyid = isset($_GET['moneyid']) ? addslashes($_GET['moneyid']) : '';
		$moneyuid = isset($_GET['moneyuid']) ? addslashes($_GET['moneyuid']) : '';
		$moneynum = isset($_GET['moneynum']) ? addslashes($_GET['moneynum']) : '';
		$update = $this->db->update('table.wemedia_money_item')->rows(array('moneystatus'=>2))->where('moneyid=?',$moneyid);
		$updateRows= $this->db->query($update);
		$queryUser= $this->db->select()->from('table.users')->where('uid = ?', $moneyuid); 
		$rowUser = $this->db->fetchRow($queryUser);
		$update = $this->db->update('table.users')->rows(array('wemedia_money'=>$rowUser["wemedia_money"]+$moneynum))->where('uid=?',$moneyuid);
		$updateRows= $this->db->query($update);
		$this->response->redirect($url."?page=water");
	}else if($status=='del'){
		$moneyid = isset($_GET['moneyid']) ? addslashes($_GET['moneyid']) : '';
		$delete = $this->db->delete('table.wemedia_money_item')->where('moneyid = ?', $moneyid);
		$deletedRows = $this->db->query($delete);
		$this->response->redirect($url."?page=water");
	}
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
	<div class="am-fl am-cf"><strong class="am-text-primary am-text-lg">提现流水</strong> / <small>记录</small></div>
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
	  <form class="am-form">
		<table class="am-table am-table-striped am-table-hover table-main">
		  <thead>
		  <tr>
			<?php if($this->user->group=='administrator'){?><th class="table-mail">用户</th><?php }?>
			<th class="table-moneynum">金额</th>
			<th class="table-moneytype">渠道</th>
			<th class="table-moneystatus">状态</th>
			<th class="table-moneyinstime">日期</th>
			<?php if($this->user->group=='administrator'){?><th class="table-set">操作</th><?php }?>
		  </tr>
		  </thead>
		  <tbody>
		  <?php
			if($this->user->group=='administrator'){
				$queryMoney= $this->db->select()->from('table.wemedia_money_item'); 
			}else{
				$queryMoney= $this->db->select()->from('table.wemedia_money_item')->where('moneyuid = ?', Typecho_Cookie::get('__typecho_uid')); 
			}
			$page_now = isset($_GET['page_now']) ? intval($_GET['page_now']) : 1;
			if($page_now<1){
				$page_now=1;
			}
			$resultTotal = $this->db->fetchAll($queryMoney);
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
				$queryMoney= $this->db->select()->from('table.wemedia_money_item')->join('table.users', 'table.wemedia_money_item.moneyuid = table.users.uid',Typecho_Db::INNER_JOIN)->offset($i)->limit($page_rec); 
			}else{
				$queryMoney= $this->db->select()->from('table.wemedia_money_item')->join('table.users', 'table.wemedia_money_item.moneyuid = table.users.uid',Typecho_Db::INNER_JOIN)->where('moneyuid = ?', Typecho_Cookie::get('__typecho_uid'))->offset($i)->limit($page_rec); 
			}
			$rowMoney = $this->db->fetchAll($queryMoney);
			foreach($rowMoney as $value){
		  ?>
		  <tr>
			<?php if($this->user->group=='administrator'){?><td><?=$value["mail"];?></td><?php }?>
			<td><?=$value["moneynum"];?></td>
			<td>
				<?php
				switch($value["moneytype"]){
					case "alipay":echo "支付宝";break;
				}
				?>
			</td>
			<td>
				<?php
				switch($value["moneystatus"]){
					case 0:echo "提现申请";break;
					case 1:echo "提现成功";break;
					case 2:echo "提现失败";break;
				}
				?>
			</td>
			<td><?=$value["moneyinstime"];?></td>
			<?php if($this->user->group=='administrator'){?>
			<td>
			  <div class="am-btn-toolbar">
				<div class="am-btn-group am-btn-group-xs">
				  <?php if($value["moneystatus"]==0){?>
				  <a href="?page=water&status=ok&moneyid=<?=$value["moneyid"];?>" class="am-btn am-btn-default am-btn-xs am-text-secondary"><span class="am-icon-lightbulb-o"></span> 确认</a>
				  <a href="?page=water&status=refuse&moneyid=<?=$value["moneyid"];?>&moneyuid=<?=$value["moneyuid"];?>&moneynum=<?=$value["moneynum"];?>" class="am-btn am-btn-default am-btn-xs am-hide-sm-only"><span class="am-icon-ban"></span> 拒绝</a>
				  <?php }else if($value["moneystatus"]==2){?>
				  <a href="?page=water&status=del&moneyid=<?=$value["moneyid"];?>" class="am-btn am-btn-default am-btn-xs am-text-danger am-hide-sm-only"><span class="am-icon-trash-o"></span> 删除</a>
				  <?php }?>
				</div>
			  </div>
			</td>
			<?php }?>
		  </tr>
		  <?php
		  }
		  ?>
		  </tbody>
		</table>
		<div class="am-cf">
		  共 <?=$totalrec;?> 条记录
		  <div class="am-fr">
			<ul class="am-pagination blog-pagination">
			  <?php if($page_now!=1){?>
				<li class="am-pagination-prev"><a href="<?=$url;?>?page=water&page_now=1">首页</a></li>
			  <?php }?>
			  <?php if($page_now>1){?>
				<li class="am-pagination-prev"><a href="<?=$url;?>?page=water&page_now=<?=$before_page;?>">&laquo; 上一页</a></li>
			  <?php }?>
			  <?php if($page_now<$page){?>
				<li class="am-pagination-next"><a href="<?=$url;?>?page=water&page_now=<?=$after_page;?>">下一页 &raquo;</a></li>
			  <?php }?>
			  <?php if($page_now!=$page){?>
				<li class="am-pagination-next"><a href="<?=$url;?>?page=water&page_now=<?=$page;?>">尾页</a></li>
			  <?php }?>
			</ul>
		  </div>
		</div>
		<hr />
		<p></p>
	  </form>
	</div>

  </div>
</div>

    <?php $this->need('templates/wemedia_user_footer.php');?>
  </div>
  <!-- content end -->

</div>

<a href="#" class="am-icon-btn am-icon-th-list am-show-sm-only admin-menu" data-am-offcanvas="{target: '#admin-offcanvas'}"></a>