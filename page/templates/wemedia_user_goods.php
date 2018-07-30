<?php
date_default_timezone_set('Asia/Shanghai');
$options = Typecho_Widget::widget('Widget_Options');
$plug_url = $options->pluginUrl;
if(strpos($this->permalink,'?')){
	$url=substr($this->permalink,0,strpos($this->permalink,'?'));
}else{
	$url=$this->permalink;
}

$action = isset($_POST['action']) ? addslashes($_POST['action']) : '';
if($action=='submitaddgoods'){
	$goodsname = isset($_POST['goodsname']) ? addslashes($_POST['goodsname']) : '';
	$goodsdetail = isset($_POST['goodsdetail']) ? addslashes(trim($_POST['goodsdetail'])) : '';
	$goodspoint = isset($_POST['goodspoint']) ? addslashes($_POST['goodspoint']) : '';
	
	$goodsData=array(
		'goodsname' => $goodsname,
		'goodsdetail' => $goodsdetail,
		'goodsinstime'=>date('Y-m-d H:i:s',$this->options->time),
		'goodsuid'=>Typecho_Cookie::get('__typecho_uid'),
		'goodspoint'=>$goodspoint
	);
	$insert = $this->db->insert('table.wemedia_goods')->rows($goodsData);
	$insertId = $this->db->query($insert);
	
	$this->response->redirect($url.'?page=goods');
}
$goto = isset($_GET['goto']) ? addslashes($_GET['goto']) : '';
if($goto=='delgoods'){
	$id = isset($_GET['id']) ? addslashes($_GET['id']) : '';
	$delete = $this->db->delete('table.wemedia_goods')->where('goodsid = ?', $id);
	$deletedRows = $this->db->query($delete);
	$this->response->redirect($url.'?page=goods');
}
?>
<?php $this->need('templates/wemedia_user_header.php');?>
<link rel="stylesheet" href="<?=$plug_url;?>/WeMedia/libs/editor.md/css/editormd.min.css" />
<div class="am-cf admin-main">
  <!-- sidebar start -->
  <?php $this->need('templates/wemedia_user_sidebar.php');?>
  <!-- sidebar end -->

  <!-- content start -->
  <div class="admin-content">
    <div class="admin-content-body">
  <div class="am-cf am-padding am-padding-bottom-0">
	<div class="am-fl am-cf"><strong class="am-text-primary am-text-lg">商品</strong> / <small>管理</small></div>
  </div>

  <hr>
<?php 
	$queryGoods= $this->db->select()->from('table.wemedia_goods')->where('goodsuid = ?', Typecho_Cookie::get('__typecho_uid')); 
	$page_now = isset($_GET['page_now']) ? intval($_GET['page_now']) : 1;
	if($page_now<1){
		$page_now=1;
	}
	$resultTotal = $this->db->fetchAll($queryGoods);
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
	$queryGoods= $this->db->select()->from('table.wemedia_goods')->join('table.users', 'table.wemedia_goods.goodsuid = table.users.uid',Typecho_Db::INNER_JOIN)->where('goodsuid = ?', Typecho_Cookie::get('__typecho_uid'))->order('table.wemedia_goods.goodsinstime',Typecho_Db::SORT_DESC)->offset($i)->limit($page_rec); 
	$rowGoods = $this->db->fetchAll($queryGoods);
?>

  <div class="am-g">
	<div class="am-u-sm-12 am-u-md-6">
	  <div class="am-btn-toolbar">
		<div class="am-btn-group am-btn-group-xs">
		  <button type="button" class="am-btn am-btn-default" id="btnAddGoods"><span class="am-icon-plus"></span> 新增</button>
		</div>
	  </div>
	</div>
	<div class="am-u-sm-12 am-u-md-3">
	  <div class="am-form-group">
		  
      </div>
	</div>
	<div class="am-u-sm-12 am-u-md-3">
	
	</div>
  </div>

  <div class="am-g">
	<div class="am-u-sm-12">
	  
		<table class="am-table am-table-striped am-table-hover table-main">
		  <thead>
		  <tr>
			<th class="table-name">名称</th>
			<th class="table-point">积分</th>
			<th class="table-time">时间</th>
			<th class="table-set">操作</th>
		  </tr>
		  </thead>
		  <tbody>
		  <?php
		  foreach($rowGoods as $value){
		  ?>
		  <tr>
			<td><a href="?page=goodsedit&id=<?=$value["goodsid"];?>"><?=$value["goodsname"];?></span></a></td>
			<td><?=$value["goodspoint"];?></td>
			<td><?=$value["goodsinstime"];?></td>
			<td>
			  <a href="?page=goods&goto=delgoods&id=<?=$value["goodsid"];?>" class="am-btn am-btn-default am-btn-xs am-text-secondary">删除</a>
			</td>
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
				<li class="am-pagination-prev"><a href="<?=$url;?>?page=goods&page_now=1">首页</a></li>
			  <?php }?>
			  <?php if($page_now>1){?>
				<li class="am-pagination-prev"><a href="<?=$url;?>?page=goods&page_now=<?=$before_page;?>">&laquo; 上一页</a></li>
			  <?php }?>
			  <?php if($page_now<$page){?>
				<li class="am-pagination-next"><a href="<?=$url;?>?page=goods&page_now=<?=$after_page;?>">下一页 &raquo;</a></li>
			  <?php }?>
			  <?php if($page_now!=$page){?>
				<li class="am-pagination-next"><a href="<?=$url;?>?page=goods&page_now=<?=$page;?>">尾页</a></li>
			  <?php }?>
			</ul>
		  </div>
		</div>
		<hr />
		<p></p>
		<?php
		
		?>
		<form id="addGoodsForm" class="am-form" method="post" action="">
			<div class="am-popup-bd">
				<div class="am-modal-actions" id="goodspopup">
				  <div class="am-modal-actions-group">
					<div style="background-color:#fff;overflow:scroll; height:400px; width:100%; border: solid 0px #aaa; margin: 0 auto;">
						<input type="text" id="goodsname" name="goodsname" placeholder="商品名称" maxLength="30" />
						<input type="number" id="goodspoint" name="goodspoint" placeholder="消费积分">
						<div id="editormd">
							<textarea style="display:none;" id="goodsdetail" name="goodsdetail"></textarea>
						</div>
					</div>
				  </div>
				  <div class="am-modal-actions-group">
					<input type="hidden" name="action" id="action" value="submitaddgoods" />
					<button class="am-btn am-btn-secondary am-btn-block">发布</button>
					<button class="am-btn am-btn-secondary am-btn-block" data-am-modal-close>取消</button>
				  </div>
				</div>
			</div>
		</form>
	  
	</div>

  </div>

</div>

    <?php $this->need('templates/wemedia_user_footer.php');?>
  </div>
  <!-- content end -->

</div>

<a href="#" class="am-icon-btn am-icon-th-list am-show-sm-only admin-menu" data-am-offcanvas="{target: '#admin-offcanvas'}"></a>
<script src="<?=$plug_url;?>/WeMedia/libs/editor.md/editormd.min.js"></script>
<script type="text/javascript">
	$(function() {
		$("#addGoodsForm").submit(function(){
			if($("#goodsname").val()==""||$("#goodsdetail").val()==""||$("#goodspoint").val()==""){
				alert("请填写完整");
				return false;
			}
		});
		$("#btnAddGoods").click(function(){
			$('#goodspopup').modal();
		});
		var editor = editormd("editormd", {
			path : "<?=$plug_url;?>/WeMedia/libs/editor.md/lib/",
			height: 350,
			toolbarIcons : function() {
				return ["undo", "redo", "|", "bold","italic","link","quote","code","image","hr", "|", "watch"]
			}
		});
	});
</script>