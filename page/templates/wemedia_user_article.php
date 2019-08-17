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
if($action=='submitaddarticle'){
	$title = isset($_POST['title']) ? addslashes($_POST['title']) : '';
	$cate = isset($_POST['cate']) ? addslashes($_POST['cate']) : '';
	$articletags = isset($_POST['articletags']) ? addslashes(trim($_POST['articletags'])) : '';
	$text = isset($_POST['text']) ? addslashes($_POST['text']) : '';
	$text='<!--markdown-->'.$text;
	if($articletags!=''){
		$articletags=str_replace('  ',' ',$articletags);
		$tags=explode(" ",$articletags);
	}
	$contentData=array(
		'title' => $title,
		'text' => $text,
		'created'=>$this->options->time,
		'authorId'=>Typecho_Cookie::get('__typecho_uid'),
		'type'=>'post',
		'status'=>'waiting'
	);
	$insert = $this->db->insert('table.contents')->rows($contentData);
	$cid = $this->db->query($insert);
	$relationData=array(
		'cid' => $cid,
		'mid' => $cate
	);
	$insert = $this->db->insert('table.relationships')->rows($relationData);
	$insertId = $this->db->query($insert);
	if(isset($tags)){
		foreach($tags as $tag){
			$metasData=array(
				'name' => $tag,
				'type' => 'tag'
			);
			$insert = $this->db->insert('table.metas')->rows($metasData);
			$insertId = $this->db->query($insert);
			$relationData=array(
				'cid' => $cid,
				'mid' => $insertId
			);
			$insert = $this->db->insert('table.relationships')->rows($relationData);
			$insertId = $this->db->query($insert);
		}
	}
	$this->response->redirect($url.'?page=article');
}else if($action=='submitsearcharticle'){
	$articlewords = isset($_POST['articlewords']) ? addslashes($_POST['articlewords']) : '';
}else if($action=='submitfeearticle'){
	$feecid = isset($_POST['feecid']) ? addslashes($_POST['feecid']) : '';
	$wemedia_price = isset($_POST['wemedia_price']) ? addslashes($_POST['wemedia_price']) : '';
	$wemedia_islogin = isset($_POST['wemedia_islogin']) ? addslashes($_POST['wemedia_islogin']) : '';
	$update = $this->db->update('table.contents')->rows(array('wemedia_price'=>$wemedia_price,'wemedia_isFee'=>'y','wemedia_islogin'=>$wemedia_islogin))->where('cid=?',$feecid);
	$updateRows= $this->db->query($update);
	$this->response->redirect($url.'?page=article');
}
$goto = isset($_GET['goto']) ? addslashes($_GET['goto']) : '';
if($goto=='cancelFee'){
	$id = isset($_GET['id']) ? addslashes($_GET['id']) : '';
	$update = $this->db->update('table.contents')->rows(array('wemedia_isFee'=>'n'))->where('cid=?',$id);
	$updateRows= $this->db->query($update);
	$this->response->redirect($url.'?page=article');
}
?>
<?php $this->need('templates/wemedia_user_header.php');?>
<style>
.tags {
	background-color: #fff;
	border: 1px solid #d5d5d5;
	color: #777;
	padding: 4px 6px;
}
.tags:hover {
	border-color: #f59942;
	outline: 0 none;
}
.tags[class*="span"] {
	float: none;
	margin-left: 0;
}
.tags input[type="text"], .tags input[type="text"]:focus {
	border: 0 none;
	box-shadow: none;
	display: inline;
	line-height: 22px;
	margin: 0;
	outline: 0 none;
	padding: 4px 6px; 
}
.tags .tag {
	background-color: #91b8d0;
	color: #fff;
	display: inline-block;
	font-size: 12px;
	font-weight: normal;
	margin-bottom: 3px;
	margin-right: 3px;
	padding: 4px 22px 5px 9px;
	position: relative;
	text-shadow: 1px 1px 1px rgba(0, 0, 0, 0.15);
	transition: all 0.2s ease 0s;
	vertical-align: baseline;
	white-space: nowrap;
}
.tags .tag .close {
	bottom: 0;
	color: #fff;
	float: none;
	font-size: 12px;
	line-height: 20px;
	opacity: 1;
	position: absolute;
	right: 0;
	text-align: center;
	text-shadow: none;
	top: 0;
	width: 18px;
}
.tags .tag .close:hover {
	background-color: rgba(0, 0, 0, 0.2);
}
.close {
	color: #000;
	float: right;
	font-size: 21px;
	font-weight: bold;
	line-height: 1;
	opacity: 0.2;
	text-shadow: 0 1px 0 #fff;
}
.close:hover, .close:focus {
	color: #000;
	cursor: pointer;
	opacity: 0.5;
	text-decoration: none;
}
button.close {
	background: transparent none repeat scroll 0 0;
	border: 0 none;
	cursor: pointer;
	padding: 0;
}
.tags .tag-warning {
	background-color: #ffb752;
}
</style>
<link rel="stylesheet" href="<?=$plug_url;?>/WeMedia/libs/editor.md/css/editormd.min.css" />
<div class="am-cf admin-main">
  <!-- sidebar start -->
  <?php $this->need('templates/wemedia_user_sidebar.php');?>
  <!-- sidebar end -->

  <!-- content start -->
  <div class="admin-content">
    <div class="admin-content-body">
  <div class="am-cf am-padding am-padding-bottom-0">
	<div class="am-fl am-cf"><strong class="am-text-primary am-text-lg">文章</strong> / <small>管理</small></div>
  </div>

  <hr>
<?php 
	$queryCate= $this->db->select()->from('table.metas')->where('type = ?', 'category')->where('parent = ?', 0); 
	$rowCate = $this->db->fetchAll($queryCate);
	
	if(isset($articlewords)){
		$articlewords='%'.$articlewords.'%';
	}else{
		$articlewords='%';
	}
	$mid = isset($_GET['mid']) ? addslashes($_GET['mid']) : '';
	
	if($mid!=''){
		$queryArticle= $this->db->select('*,table.metas.name as mname,table.users.name as uname,table.contents.password as cpassword,table.relationships.cid as rcid,table.metas.mid as mmid')->from('table.contents')->join('table.users', 'table.contents.authorId = table.users.uid',Typecho_Db::INNER_JOIN)->join('table.relationships', 'table.contents.cid = table.relationships.cid',Typecho_Db::INNER_JOIN)->join('table.metas', 'table.metas.mid = table.relationships.mid',Typecho_Db::INNER_JOIN)->where('authorId = ?', Typecho_Cookie::get('__typecho_uid'))->where('table.metas.mid = ?', $mid)->where('table.contents.type = ?', 'post')->where('table.metas.type = ?', 'category')->where('title like ? ', $articlewords)->orWhere('text like ? ', $articlewords); 
	}else{
		$queryArticle= $this->db->select('*,table.metas.name as mname,table.users.name as uname,table.contents.password as cpassword,table.relationships.cid as rcid,table.metas.mid as mmid')->from('table.contents')->join('table.users', 'table.contents.authorId = table.users.uid',Typecho_Db::INNER_JOIN)->join('table.relationships', 'table.contents.cid = table.relationships.cid',Typecho_Db::INNER_JOIN)->join('table.metas', 'table.metas.mid = table.relationships.mid',Typecho_Db::INNER_JOIN)->where('authorId = ?', Typecho_Cookie::get('__typecho_uid'))->where('table.contents.type = ?', 'post')->where('table.metas.type = ?', 'category')->where('title like ? ', $articlewords)->orWhere('text like ? ', $articlewords); 
	}
	
	$page_now = isset($_GET['page_now']) ? intval($_GET['page_now']) : 1;
	if($page_now<1){
		$page_now=1;
	}
	$resultTotal = $this->db->fetchAll($queryArticle);
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
	
	if($mid!=''){
		$queryArticle= $this->db->select('*,table.metas.name as mname,table.users.name as uname,table.contents.password as cpassword,table.relationships.cid as rcid,table.metas.mid as mmid')->from('table.contents')->join('table.users', 'table.contents.authorId = table.users.uid',Typecho_Db::INNER_JOIN)->join('table.relationships', 'table.contents.cid = table.relationships.cid',Typecho_Db::INNER_JOIN)->join('table.metas', 'table.metas.mid = table.relationships.mid',Typecho_Db::INNER_JOIN)->where('authorId = ?', Typecho_Cookie::get('__typecho_uid'))->where('table.metas.mid = ?', $mid)->where('table.contents.type = ?', 'post')->where('table.metas.type = ?', 'category')->where('title like ? ', $articlewords)->orWhere('text = ? ', $articlewords)->order('table.contents.created',Typecho_Db::SORT_DESC)->offset($i)->limit($page_rec); 
	}else{
		$queryArticle= $this->db->select('*,table.metas.name as mname,table.users.name as uname,table.contents.password as cpassword,table.relationships.cid as rcid,table.metas.mid as mmid')->from('table.contents')->join('table.users', 'table.contents.authorId = table.users.uid',Typecho_Db::INNER_JOIN)->join('table.relationships', 'table.contents.cid = table.relationships.cid',Typecho_Db::INNER_JOIN)->join('table.metas', 'table.metas.mid = table.relationships.mid',Typecho_Db::INNER_JOIN)->where('authorId = ?', Typecho_Cookie::get('__typecho_uid'))->where('table.contents.type = ?', 'post')->where('table.metas.type = ?', 'category')->where('title like ? ', $articlewords)->orWhere('text = ? ', $articlewords)->order('table.contents.created',Typecho_Db::SORT_DESC)->offset($i)->limit($page_rec); 
	}
	$rowArticle = $this->db->fetchAll($queryArticle);
	
?>

  <div class="am-g">
	<div class="am-u-sm-12 am-u-md-6">
	  <div class="am-btn-toolbar">
		<div class="am-btn-group am-btn-group-xs">
		  <button type="button" class="am-btn am-btn-default" id="btnAddArticle"><span class="am-icon-plus"></span> 新增</button>
		</div>
	  </div>
	</div>
	<div class="am-u-sm-12 am-u-md-3">
	  <div class="am-form-group">
		  <select id="selectcate">
			<option value="">所有类别</option>
			<?php foreach($rowCate as $value){?>
			<option value="<?=$value["mid"];?>" <?php if($mid==$value["mid"]){?>selected<?php }?>><?=$value["name"];?></option>
			<?php }?>
		  </select>
		  <span class="am-form-caret"></span>
      </div>
	</div>
	<div class="am-u-sm-12 am-u-md-3">
	<form id="searchArticleForm" class="am-form" method="post" action="">
	  <div class="am-input-group am-input-group-sm">
		<input type="text" name="articlewords" id="articlewords" placeholder="输入关键词" class="am-form-field">
		<span class="am-input-group-btn">
			<input type="hidden" name="action" value="submitsearcharticle" />
			<button class="am-btn am-btn-default" id="searcharticle" type="button">搜索</button>
		</span>
	  </div>
	</form>
	</div>
  </div>

  <div class="am-g">
	<div class="am-u-sm-12">
	  
		<table class="am-table am-table-striped am-table-hover table-main">
		  <thead>
		  <tr>
			<th class="table-commentsNum">评论</th>
			<th class="table-orderNum">订单</th>
			<th class="table-title">标题</th>
			<th class="table-author">作者</th>
			<th class="table-cate">分类</th>
			<th class="table-time">时间</th>
			<th class="table-set">操作</th>
		  </tr>
		  </thead>
		  <tbody>
		  <?php
		  foreach($rowArticle as $value){
		  ?>
		  <tr>
			<td><?=$value["commentsNum"];?></td>
			<td>
				<?php
				$queryOrder= $this->db->select()->from('table.contents')->join('table.wemedia_fee_item', 'table.contents.cid = table.wemedia_fee_item.feecid',Typecho_Db::INNER_JOIN)->where('authorId = ?', Typecho_Cookie::get('__typecho_uid'))->where('cid = ?', $value["cid"]); 
				$rowOrder = $this->db->fetchAll($queryOrder);
				echo count($rowOrder);
				?>
			</td>
			<td>
				<a href="?page=articleedit&id=<?=$value["cid"];?>"><?=$value["title"];?></span></a>
				<small>
				<?php
				switch($value["status"]){
					case "waiting":echo "待审中";break;
					case "hidden":echo "隐藏中";break;
					case "private":echo "私密";break;
					default:if(isset($value["cpassword"])){echo "密码保护";}
				}
				?>
				</small>
			</td>
			<td><?php if($value["screenName"]!=''){echo $value["screenName"];}else{echo $value["name"];}?></td>
			<td><?=$value["mname"];?></td>
			<td><?=date("Y-m-d H:i:s",$value["created"]);?></td>
			<td>
			  <div class="am-btn-toolbar">
				<div class="am-btn-group am-btn-group-xs">
				  <?php if($value["wemedia_isFee"]=='y'){?>
				  <a id="cancelFee<?=$value["cid"];?>" data-price="<?=$value["wemedia_price"];?>" data-cid="<?=$value["cid"];?>" class="am-btn am-btn-default am-btn-xs am-text-secondary cancelFee"><span class="am-icon-money"></span> <font color="red">付费中</font></a>
				  <?php }else{?>
				  <a id="confirmFee<?=$value["cid"];?>" data-price="<?=$value["wemedia_price"];?>" data-cid="<?=$value["cid"];?>" class="am-btn am-btn-default am-btn-xs am-text-secondary confirmFee"><span class="am-icon-gift"></span> <font color="green">免费中</font></a>
				  <?php }?>
				</div>
			  </div>
			</td>
		  </tr>
		  <?php
		  }
		  ?>
		  </tbody>
		</table>
		<form id="feeArticleForm" class="am-form" method="post" action="">
		<div class="am-modal am-modal-prompt" tabindex="-1" id="fee-prompt">
		  <div class="am-modal-dialog">
			<div class="am-modal-hd">设置单价</div>
			<div class="am-modal-bd">
			  <input type="text" name="wemedia_price" id="wemedia_price" class="am-modal-prompt-input">
			  免登录<input type="radio" name="wemedia_islogin" value="n">
			  需登录<input type="radio" name="wemedia_islogin" value="y">
			</div>
			<div class="am-modal-footer">
			  <input type="hidden" name="action" value="submitfeearticle" />
			  <input type="hidden" name="feecid" id="feecid" value="" />
			  <span class="am-modal-btn" data-am-modal-cancel>取消</span>
			  <span class="am-modal-btn" data-am-modal-confirm>提交</span>
			</div>
		  </div>
		</div>
		</form>
		<div class="am-cf">
		  共 <?=$totalrec;?> 条记录
		  <div class="am-fr">
			<ul class="am-pagination blog-pagination">
			  <?php if($page_now!=1){?>
				<li class="am-pagination-prev"><a href="<?=$url;?>?page=article&page_now=1">首页</a></li>
			  <?php }?>
			  <?php if($page_now>1){?>
				<li class="am-pagination-prev"><a href="<?=$url;?>?page=article&page_now=<?=$before_page;?>">&laquo; 上一页</a></li>
			  <?php }?>
			  <?php if($page_now<$page){?>
				<li class="am-pagination-next"><a href="<?=$url;?>?page=article&page_now=<?=$after_page;?>">下一页 &raquo;</a></li>
			  <?php }?>
			  <?php if($page_now!=$page){?>
				<li class="am-pagination-next"><a href="<?=$url;?>?page=article&page_now=<?=$page;?>">尾页</a></li>
			  <?php }?>
			</ul>
		  </div>
		</div>
		<hr />
		<p></p>
		<?php
		
		?>
		<form id="addArticleForm" class="am-form" method="post" action="">
			<div class="am-popup-bd">
				<div class="am-modal-actions" id="articlepopup">
				  <div class="am-modal-actions-group">
					<div style="background-color:#fff;overflow:scroll; height:400px; width:100%; border: solid 0px #aaa; margin: 0 auto;">
						<input type="text" id="title" name="title" placeholder="标题" maxLength="30" />
						<select name="cate" id="cate">
							<?php 
							foreach($rowCate as $value){
							?>
							<option value="<?=$value['mid'];?>">分类：<?=$value['name'];?></option>
							<?php 
							}
							?>
						</select>
						<div class="tags" id="tags" tabindex="1" style="display:none;"> 
							<input id="form-field-tags" type="text" value="" name="tags" style="display: none;">
							<input type="text" id="tags_enter" placeholder="请输入标签，每个标签用空格分隔" class="tags_enter" autocomplete="off">
						</div>
						<div id="editormd">
							<textarea style="display:none;" id="text" name="text"></textarea>
						</div>
					</div>
				  </div>
				  <div class="am-modal-actions-group">
					<input type="hidden" name="action" id="action" value="submitaddarticle" />
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
		$("#wemedia_price").keyup( function () {
			/*先把非数字的都替换掉，除了数字和.*/
			$(this).val($(this).val().replace(/[^\d.]/g,""));
			/*保证只有出现一个.而没有多个.*/
			$(this).val($(this).val().replace(/\.{2,}/g,"."));
			/*必须保证第一个为数字而不是.*/
			$(this).val($(this).val().replace(/^\./g,""));
			/*保证.只出现一次，而不能出现两次以上*/
			$(this).val($(this).val().replace(".","$#$").replace(/\./g,"").replace("$#$","."));
			/*只能输入两个小数*/
			$(this).val($(this).val().replace(/^(\-)*(\d+)\.(\d\d).*$/,"$1$2.$3"));
		});
		$(".confirmFee").each(function(){
			var id=$(this).attr("id")
			$("#"+id).click( function () {
				$("#feecid").val($(this).attr("data-cid"));
				$("#wemedia_price").val($(this).attr("data-price"));
				$('#fee-prompt').modal({
				  relatedTarget: this,
				  onConfirm: function(e) {
					  if(e.data<0.01){
						  alert("最小单价为0.01元");
						  return;
					  }
					  $("#feeArticleForm").submit();
				  },
				  onCancel: function(e) {
				  }
				});
			});
		});
		$(".cancelFee").each(function(){
			var id=$(this).attr("id")
			$("#"+id).click( function () {
				location.href='?page=article&goto=cancelFee&id='+$(this).attr('data-cid');
			});
		});
		$("#searcharticle").click(function(){
			if($("#articlewords").val()==''){
				alert("请输入关键词");
				return;
			}
			$("#searchArticleForm").submit();
		});
		$("#selectcate").change(function(){
			location.href='?page=article&mid='+$(this).val();
		});
		$("#addArticleForm").submit(function(){
			if($("#title").val()==""){
				alert("请输入文章标题");
				return false;
			}
		});
		$("#btnAddArticle").click(function(){
			$('#articlepopup').modal();
		});
		var editor = editormd("editormd", {
			path : "<?=$plug_url;?>/WeMedia/libs/editor.md/lib/",
			height: 350,
			toolbarIcons : function() {
				return ["undo", "redo", "|", "bold","italic","link","quote","code","image","hr","wemedia_fee", "|", "watch"]
			},
			toolbarIconTexts : {
				wemedia_fee : "<div id='wemedia_fee'>付</div>"
			},
			toolbarHandlers : {
				wemedia_fee : function(cm, icon, cursor, selection) {
					cm.replaceSelection("\r\n<!--WeMedia start-->\r\n\r\n<!--WeMedia end-->\r\n");
				}
			}
		});
		$(".tags_enter").blur(function() { /*焦点失去触发 */
			var txtvalue=$(this).val().trim();
			if(txtvalue!=''){
				addTag($(this));
				$(this).parents(".tags").css({"border-color": "#d5d5d5"})
			}
		}).keydown(function(event) {
			var key_code = event.keyCode;
			var txtvalue=$(this).val().trim(); 
			if (key_code == 13&& txtvalue != '') { /*enter*/
				addTag($(this));
			}
			if (key_code == 32 && txtvalue!='') { /*space*/
				addTag($(this));
			}
		});
		$(".close").live("click", function() {
			$(this).parent(".tag").remove();
		});
		$(".tags").click(function() {
			$(this).css({"border-color": "#f59942"})
		}).blur(function() {
			$(this).css({"border-color": "#d5d5d5"})
		});
	});
	var articletags='';
	function addTag(obj) {
		var tag = obj.val();
		if (tag != '') {
			var i = 0;
			$(".tag").each(function() {
				if ($(this).text() == tag + "×") {
					$(this).addClass("tag-warning");
					setTimeout("removeWarning()", 400);
					i++;
				}
			})
			obj.val('');
			if (i > 0) { /*说明有重复*/
				return false;
			}
			articletags=articletags+' '+tag;
			$("#form-field-tags").before("<span class='tag'>" + tag + "<input type='hidden' name='articletags' value='"+articletags+"' /><button class='close' type='button'>×</button></span>"); /*添加标签*/
		}
	}
	function removeWarning() {
		$(".tag-warning").removeClass("tag-warning");
	}
</script>