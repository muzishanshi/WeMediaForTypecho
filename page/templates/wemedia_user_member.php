<?php if (!$this->user->pass('administrator')) exit;?>
<?php
if(strpos($this->permalink,'?')){
	$url=substr($this->permalink,0,strpos($this->permalink,'?'));
}else{
	$url=$this->permalink;
}
$action = isset($_POST['action']) ? addslashes($_POST['action']) : '';
if($action=='submitsearchuser'){
	$searchuser = isset($_POST['searchuser']) ? addslashes($_POST['searchuser']) : '';
	
}
$isallow = isset($_GET['isallow']) ? addslashes($_GET['isallow']) : '';
if($isallow=='allow'){
	$mail = isset($_GET['mail']) ? addslashes($_GET['mail']) : '';
	$update = $this->db->update('table.users')->rows(array('wemedia_isallow'=>'allow'))->where('mail=?',$mail);
	$updateRows= $this->db->query($update);
	$this->response->redirect($url."?page=member");
}else if($isallow=='refuse'){
	$mail = isset($_GET['mail']) ? addslashes($_GET['mail']) : '';
	$update = $this->db->update('table.users')->rows(array('wemedia_isallow'=>'refuse'))->where('mail=?',$mail);
	$updateRows= $this->db->query($update);
	$this->response->redirect($url."?page=member");
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
	<div class="am-fl am-cf"><strong class="am-text-primary am-text-lg">用户</strong> / <small>管理</small></div>
  </div>

  <hr>
  <form class="am-form" method="post" action="?page=member">
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
		<input type="text" name="searchuser" id="searchuser" placeholder="输入邮箱" class="am-form-field">
		<span class="am-input-group-btn">
			<input type="hidden" name="action" value="submitsearchuser" />
			<button class="am-btn am-btn-default" type="submit">搜索</button>
		</span>
	  </div>
	</div>
  </div>

  <div class="am-g">
	<div class="am-u-sm-12">
	  
		<div class="am-scrollable-horizontal">
			<table class="am-table am-table-bordered am-table-striped am-text-nowrap">
			  <thead>
			  <tr>
				<th class="table-id">ID</th>
				<th class="table-name">用户名</th>
				<th class="table-mail">邮箱</th>
				<th class="table-screenName">昵称</th>
				<th class="table-group">用户组</th>
				<th class="table-money">账户</th>
				<th class="table-realname">姓名</th>
				<th class="table-alipay">支付宝</th>
				<th class="table-isallow">实名认证</th>
				<th class="table-info">简介</th>
				<th class="table-created">注册时间</th>
				<th class="table-activated">激活时间</th>
				<th class="table-logged">登录时间</th>
				<th class="table-operation">操作</th>
			  </tr>
			  </thead>
			  <tbody>
			  <?php
				if(@$searchuser!=''){
					$queryUsers= $this->db->select()->from('table.users')->where('mail = ?', $searchuser); 
				}else{
					$queryUsers= $this->db->select()->from('table.users'); 
				}
				$page_now = isset($_GET['page_now']) ? intval($_GET['page_now']) : 1;
				if($page_now<1){
					$page_now=1;
				}
				$resultTotal = $this->db->fetchAll($queryUsers);
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
				if(@$searchuser!=''){
					$queryUsers= $this->db->select()->from('table.users')->where('mail = ?', $searchuser)->offset($i)->limit($page_rec); 
				}else{
					$queryUsers= $this->db->select()->from('table.users')->offset($i)->limit($page_rec); 
				}
				$rowUsers = $this->db->fetchAll($queryUsers);
				foreach($rowUsers as $value){
			  ?>
			  <tr>
				<td><?=$value["uid"];?></td>
				<td><?=$value["name"];?></td>
				<td><?=$value["mail"];?></td>
				<td><?=$value["screenName"];?></td>
				<td><?=$value["group"];?></td>
				<td><?=$value["wemedia_money"];?></td>
				<td><?=$value["wemedia_realname"];?></td>
				<td><?=$value["wemedia_alipay"];?></td>
				<td>
					<?php
					switch($value["wemedia_isallow"]){
						case "none":echo "未认证";break;
						case "allow":echo "认证成功";break;
						case "refuse":echo "认证失败";break;
						case "process":echo "认证中";break;
					}
					?>
				</td>
				<td>
					<div class="am-dropdown" data-am-dropdown>
					  <button class="am-btn am-btn-success am-dropdown-toggle">简介<span class="am-icon-caret-down"></span></button>
					  <div class="am-dropdown-content">
						<?=$value["wemedia_info"];?>
					  </div>
					</div>
				</td>
				<td><?=date("Y-m-d H:i:s",$value["created"]);?></td>
				<td><?=date("Y-m-d H:i:s",$value["activated"]);?></td>
				<td><?=date("Y-m-d H:i:s",$value["logged"]);?></td>
				<td>
				  <a href="?page=member&isallow=allow&mail=<?=$value["mail"];?>" class="am-btn am-btn-default am-btn-xs am-text-secondary"><span class="am-icon-lightbulb-o"></span> 通过</a>
				  <?php if($value["url"]!=""){?>
				  <a class="am-btn am-btn-default am-btn-xs" href="<?=$value["url"];?>" target="_blank"><span class="am-icon-copy"></span> 网址</a>
				  <?php }?>
				  <a href="?page=member&isallow=refuse&mail=<?=$value["mail"];?>" class="am-btn am-btn-default am-btn-xs am-text-danger"><span class="am-icon-ban"></span> 拒绝</a>
				</td>
			  </tr>
			  <?php
				}
			  ?>
			  </tbody>
			</table>
		</div>
		<div class="am-cf">
		  共 <?=$totalrec;?> 条记录
		  <div class="am-fr">
			<ul class="am-pagination blog-pagination">
			  <?php if($page_now!=1){?>
				<li class="am-pagination-prev"><a href="<?=$url;?>?page=member&page_now=1">首页</a></li>
			  <?php }?>
			  <?php if($page_now>1){?>
				<li class="am-pagination-prev"><a href="<?=$url;?>?page=member&page_now=<?=$before_page;?>">&laquo; 上一页</a></li>
			  <?php }?>
			  <?php if($page_now<$page){?>
				<li class="am-pagination-next"><a href="<?=$url;?>?page=member&page_now=<?=$after_page;?>">下一页 &raquo;</a></li>
			  <?php }?>
			  <?php if($page_now!=$page){?>
				<li class="am-pagination-next"><a href="<?=$url;?>?page=member&page_now=<?=$page;?>">尾页</a></li>
			  <?php }?>
			</ul>
		  </div>
		</div>
		<hr />
		<p></p>
	  
	</div>

  </div>
  </form>
</div>

    <?php $this->need('templates/wemedia_user_footer.php');?>
  </div>
  <!-- content end -->

</div>

<a href="#" class="am-icon-btn am-icon-th-list am-show-sm-only admin-menu" data-am-offcanvas="{target: '#admin-offcanvas'}"></a>