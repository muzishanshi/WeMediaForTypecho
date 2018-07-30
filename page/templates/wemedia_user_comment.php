<?php
date_default_timezone_set('Asia/Shanghai');
include __TYPECHO_ROOT_DIR__.__TYPECHO_PLUGIN_DIR__."/WeMedia/include/function.php";
if(strpos($this->permalink,'?')){
	$url=substr($this->permalink,0,strpos($this->permalink,'?'));
}else{
	$url=$this->permalink;
}

$operation = isset($_GET['operation']) ? addslashes(trim($_GET['operation'])) : '';
switch($operation){
	case "approved":
		$id = isset($_GET['id']) ? addslashes(trim($_GET['id'])) : '';
		$update = $this->db->update('table.comments')->rows(array('status'=>'approved'))->where('coid=?',$id);
		$updateRows= $this->db->query($update);
		$this->response->redirect($url.'?page=comment');
		break;
	case "waiting":
		$id = isset($_GET['id']) ? addslashes(trim($_GET['id'])) : '';
		$update = $this->db->update('table.comments')->rows(array('status'=>'waiting'))->where('coid=?',$id);
		$updateRows= $this->db->query($update);
		$this->response->redirect($url.'?page=comment');
		break;
	case "spam":
		$id = isset($_GET['id']) ? addslashes(trim($_GET['id'])) : '';
		$update = $this->db->update('table.comments')->rows(array('status'=>'spam'))->where('coid=?',$id);
		$updateRows= $this->db->query($update);
		$this->response->redirect($url.'?page=comment');
		break;
	case "del":
		$id = isset($_GET['id']) ? addslashes(trim($_GET['id'])) : '';
		$queryComment= $this->db->select()->from('table.comments')->where('coid = ?', $id); 
		$rowComment = $this->db->fetchRow($queryComment);
		$queryContent= $this->db->select()->from('table.contents')->where('cid = ?', $rowComment["cid"]); 
		$rowContent = $this->db->fetchRow($queryContent);
		$update = $this->db->update('table.contents')->rows(array('commentsNum'=>$rowContent["commentsNum"]-1))->where('cid=?',$rowContent["cid"]);
		$updateRows= $this->db->query($update);
		$delete = $this->db->delete('table.comments')->where('coid = ?', $id);
		$deletedRows = $this->db->query($delete);
		$this->response->redirect($url.'?page=comment');
		break;
}
$action = isset($_POST['action']) ? addslashes(trim($_POST['action'])) : '';
switch($action){
	case "submitEditComment":
		$coid = isset($_POST['coid']) ? addslashes(trim($_POST['coid'])) : '';
		$commentname = isset($_POST['commentname']) ? addslashes(trim($_POST['commentname'])) : '';
		$commentmail = isset($_POST['commentmail']) ? addslashes(trim($_POST['commentmail'])) : '';
		$commenturl = isset($_POST['commenturl']) ? addslashes(trim($_POST['commenturl'])) : '';
		$commenttext = isset($_POST['commenttext']) ? addslashes(trim($_POST['commenttext'])) : '';
		$commentData=array(
			'author'=>$commentname,
			'mail'=>$commentmail,
			'url'=>$commenturl,
			'text'=>$commenttext
		);
		$update = $this->db->update('table.comments')->rows($commentData)->where('coid=?',$coid);
		$updateRows= $this->db->query($update);
		$this->response->redirect($url.'?page=comment');
		break;
	case "submitReplyComment":
		$coid = isset($_POST['coid']) ? addslashes(trim($_POST['coid'])) : '';
		$cid = isset($_POST['contentid']) ? addslashes(trim($_POST['contentid'])) : '';
		$commentmail = isset($_POST['commentmail']) ? addslashes(trim($_POST['commentmail'])) : '';
		$commenturl = isset($_POST['commenturl']) ? addslashes(trim($_POST['commenturl'])) : '';
		$commenttext = isset($_POST['commenttext']) ? addslashes(trim($_POST['commenttext'])) : '';
		$queryUser= $this->db->select()->from('table.users')->where('uid = ?', Typecho_Cookie::get('__typecho_uid')); 
		$rowUser = $this->db->fetchRow($queryUser);
		$commentData=array(
			"cid"=>$cid,
			"created"=>$this->options->time,
			"author"=>$rowUser["screenName"],
			"authorId"=>Typecho_Cookie::get('__typecho_uid'),
			"ownerId"=>Typecho_Cookie::get('__typecho_uid'),
			"mail"=>$commentmail,
			"url"=>$commenturl,
			"ip"=>getIP(),
			"agent"=>$_SERVER['HTTP_USER_AGENT'],
			"text"=>$commenttext,
			"type"=>'comment',
			"status"=>'approved',
			"parent"=>$coid,
		);
		$insert = $this->db->insert('table.comments')->rows($commentData);
		$insertId = $this->db->query($insert);
		$queryContent= $this->db->select()->from('table.contents')->where('cid = ?', $cid); 
		$rowContent = $this->db->fetchRow($queryContent);
		$update = $this->db->update('table.contents')->rows(array('commentsNum'=>$rowContent["commentsNum"]+1))->where('cid=?',$cid);
		$updateRows= $this->db->query($update);
		$this->response->redirect($url.'?page=comment');
		break;
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
	<div class="am-fl am-cf"><strong class="am-text-primary am-text-lg">评论</strong> / <small>管理</small></div>
  </div>

  <hr>

  <div class="am-g">
	<div class="am-u-sm-12 am-u-md-6">
	  <div class="am-btn-toolbar">
		<div class="am-btn-group am-btn-group-xs">
		  <a href="?page=article" type="button" class="am-btn am-btn-default">文章</a>
		</div>
	  </div>
	</div>
	<div class="am-u-sm-12 am-u-md-3">
	  <div class="am-form-group">
		<select id="commentstatus">
		  <option value="approved" <?php if(@$_GET["status"]=='approved'){?>selected<?php }?>>已通过</option>
		  <option value="waiting" <?php if(@$_GET["status"]=='waiting'){?>selected<?php }?>>待审核</option>
		  <option value="spam" <?php if(@$_GET["status"]=='spam'){?>selected<?php }?>>垃圾</option>
		</select>
	  </div>
	</div>
	<div class="am-u-sm-12 am-u-md-3">
	  <div class="am-input-group am-input-group-sm">
		<input type="text" id="searchComment" class="am-form-field" placeholder="输入内容">
	  <span class="am-input-group-btn">
		<button id="btnSearchComment" class="am-btn am-btn-default" type="button">搜索</button>
	  </span>
	  </div>
	</div>
  </div>

  <div class="am-g">
	<div class="am-u-sm-12">
	  <form id="commentForm" class="am-form" method="post" action="">
		<table class="am-table am-table-striped am-table-hover table-main">
		  <thead>
		  <tr>
			<th class="table-author">作者</th>
			<th class="table-content">内容</th>
		  </tr>
		  </thead>
		  <tbody>
		  <?php
			$status = isset($_GET['status']) ? addslashes(trim($_GET['status'])) : 'approved';
			$text = isset($_GET['text']) ? addslashes(trim($_GET['text'])) : '';
			
			if($text!=''){
				$queryComment= $this->db->select('*,table.comments.mail as cmail,table.comments.url as curl,table.comments.created as ccreated,table.comments.text as ctext,table.comments.status as cstatus,table.comments.cid as ccid')->from('table.comments')->join('table.contents', 'table.contents.cid = table.comments.cid',Typecho_Db::INNER_JOIN)->join('table.users', 'table.comments.authorId = table.users.uid',Typecho_Db::INNER_JOIN)->where('table.comments.ownerId = ?', Typecho_Cookie::get('__typecho_uid'))->where('table.comments.type = ?', 'comment')->where('table.comments.status like ?', $status)->where('table.comments.text like ?', '%'.$text.'%'); 
			}else{
				$queryComment= $this->db->select('*,table.comments.mail as cmail,table.comments.url as curl,table.comments.created as ccreated,table.comments.text as ctext,table.comments.status as cstatus,table.comments.cid as ccid')->from('table.comments')->join('table.contents', 'table.contents.cid = table.comments.cid',Typecho_Db::INNER_JOIN)->join('table.users', 'table.comments.authorId = table.users.uid',Typecho_Db::INNER_JOIN)->where('table.comments.ownerId = ?', Typecho_Cookie::get('__typecho_uid'))->where('table.comments.type = ?', 'comment')->where('table.comments.status like ?', $status); 
			}
			
			$page_now = isset($_GET['page_now']) ? intval($_GET['page_now']) : 1;
			if($page_now<1){
				$page_now=1;
			}
			$resultTotal = $this->db->fetchAll($queryComment);
			$page_rec=$this->parameter->pageSize;
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
			
			if($text!=''){
				$queryComment= $this->db->select('*,table.comments.mail as cmail,table.comments.url as curl,table.comments.created as ccreated,table.comments.text as ctext,table.comments.status as cstatus,table.comments.cid as ccid')->from('table.comments')->join('table.contents', 'table.contents.cid = table.comments.cid',Typecho_Db::INNER_JOIN)->join('table.users', 'table.comments.authorId = table.users.uid',Typecho_Db::INNER_JOIN)->where('table.comments.ownerId = ?', Typecho_Cookie::get('__typecho_uid'))->where('table.comments.type = ?', 'comment')->where('table.comments.status like ?', $status)->where('table.comments.text like ?', '%'.$text.'%')->order('table.comments.created',Typecho_Db::SORT_DESC)->offset($i)->limit($page_rec); 
			}else{
				$queryComment= $this->db->select('*,table.comments.mail as cmail,table.comments.url as curl,table.comments.created as ccreated,table.comments.text as ctext,table.comments.status as cstatus,table.comments.cid as ccid')->from('table.comments')->join('table.contents', 'table.contents.cid = table.comments.cid',Typecho_Db::INNER_JOIN)->join('table.users', 'table.comments.authorId = table.users.uid',Typecho_Db::INNER_JOIN)->where('table.comments.ownerId = ?', Typecho_Cookie::get('__typecho_uid'))->where('table.comments.type = ?', 'comment')->where('table.comments.status like ?', $status)->order('table.comments.created',Typecho_Db::SORT_DESC)->offset($i)->limit($page_rec); 
			}
			$rowComment = $this->db->fetchAll($queryComment);
			foreach($rowComment as $value){
		  ?>
		  <tr>
			<td>
				<a href="<?php if($value["curl"]!=''){echo $value["curl"];}else{echo 'javascript:;';}?>" target="_blank">
					<?=$value["author"];?>
				</a><br />
				<a href="mailto:<?=$value["cmail"];?>" target="_blank">
					<?=$value["cmail"];?>
				</a><br />
				<?=$value["ip"];?>
			</td>
			<td>
				<?=date('Y-m-d H:i:s',$value["ccreated"]);?>于<a href="javascript:;" target="_blank"><?=$value["title"];?></a><br />
				<div style="width:300px;word-wrap:break-word;"><?=$value["ctext"];?></div><br />
				<div class="am-btn-toolbar">
					<div class="am-btn-group am-btn-group-xs">
					<?php if($value["cstatus"]!='approved'){?>
					  <a href="?page=comment&operation=approved&id=<?=$value["coid"];?>" class="am-btn am-btn-default am-btn-xs am-text-secondary"><span class="am-icon-check"></span> 通过</a>
					<?php }?>
					<?php if($value["cstatus"]!='waiting'){?>
					  <a href="?page=comment&operation=waiting&id=<?=$value["coid"];?>" class="am-btn am-btn-default am-btn-xs"><span class="am-icon-calendar-check-o"></span> 待审核</a>
					<?php }?>
					<?php if($value["cstatus"]!='spam'){?>
					  <a href="?page=comment&operation=spam&id=<?=$value["coid"];?>" class="am-btn am-btn-default am-btn-xs am-text-danger"><span class="am-icon-trash-o"></span> 垃圾</a>
					<?php }?>
					  <a id="editComment<?=$value["coid"];?>" data-coid="<?=$value["coid"];?>" data-name="<?=$value["author"];?>" data-mail="<?=$value["cmail"];?>" data-url="<?=$value["curl"];?>" data-text="<?=$value["ctext"];?>" class="am-btn am-btn-default am-btn-xs am-text-secondary editComment"><span class="am-icon-pencil-square-o"></span> 编辑</a>
					  <a id="replyComment<?=$value["coid"];?>" data-coid="<?=$value["coid"];?>" data-cid="<?=$value["cid"];?>" data-text="<?=$value["ctext"];?>" class="am-btn am-btn-default am-btn-xs replyComment"><span class="am-icon-mail-reply"></span> 回复</a>
					  <a id="delComment<?=$value["coid"];?>" data-coid="<?=$value["coid"];?>" class="am-btn am-btn-default am-btn-xs am-text-danger delComment"><span class="am-icon-trash"></span> 删除</a>
					</div>
				</div>
			</td>
		  </tr>
		  <?php
			}
		  ?>
		  </tbody>
		</table>
		<!--编辑/回复弹窗-->
		<div class="am-modal-actions" id="comment-actions">
		  <div class="am-modal-actions-group">
			<ul class="am-list">
			  <li id="commentnameli" class="am-modal-actions-header">
				<input type="text" id="commentname" name="commentname" placeholder="用户名">
			  </li>
			  <li id="commentmailli" class="am-modal-actions-header">
				<input type="email" id="commentmail" name="commentmail" placeholder="电子邮箱">
			  </li>
			  <li id="commenturlli" class="am-modal-actions-header">
				<input type="text" id="commenturl" name="commenturl" placeholder="个人主页">
			  </li>
			  <li id="commentnameli" class="am-modal-actions-header">
				<textarea id="commenttext" name="commenttext" placeholder="内容"></textarea>
			  </li>
			</ul>
		  </div>
		  <div class="am-modal-actions-group">
			<input type="hidden" name="contentid" id="contentid" value="">
			<input type="hidden" name="coid" id="coid" value="">
			<input type="hidden" name="action" id="action" value="">
			<a id="btnComment" class="am-btn am-btn-secondary am-btn-block">提交</a>
			<button class="am-btn am-btn-secondary am-btn-block" data-am-modal-close>取消</button>
		  </div>
		</div>
		<div class="am-cf">
		  共 <?=$totalrec;?> 条记录
		  <div class="am-fr">
			<ul class="am-pagination blog-pagination">
			  <?php if($page_now!=1){?>
				<li class="am-pagination-prev"><a href="<?=$url;?>?page=comment&page_now=1">首页</a></li>
			  <?php }?>
			  <?php if($page_now>1){?>
				<li class="am-pagination-prev"><a href="<?=$url;?>?page=comment&page_now=<?=$before_page;?>">&laquo; 上一页</a></li>
			  <?php }?>
			  <?php if($page_now<$page){?>
				<li class="am-pagination-next"><a href="<?=$url;?>?page=comment&page_now=<?=$after_page;?>">下一页 &raquo;</a></li>
			  <?php }?>
			  <?php if($page_now!=$page){?>
				<li class="am-pagination-next"><a href="<?=$url;?>?page=comment&page_now=<?=$page;?>">尾页</a></li>
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
<script>
$(function() {
	$("#commentstatus").change( function () {
		location.href='?page=comment&status='+$(this).val();
	});
	$("#btnSearchComment").click( function () {
		if($("#searchComment").val()!=''){
			location.href='?page=comment&text='+$("#searchComment").val();
		}
	});
	$(".editComment").each(function(){
		var id=$(this).attr("id")
		$("#"+id).click( function () {
			$("#commentnameli").css("display","block");
			$("#commentmailli").css("display","block");
			$("#commenturlli").css("display","block");
			$("#action").val("submitEditComment");
			$("#coid").val($(this).attr("data-coid"));
			$("#commentname").val($(this).attr("data-name"));
			$("#commentmail").val($(this).attr("data-mail"));
			$("#commenturl").val($(this).attr("data-url"));
			$("#commenttext").val($(this).attr("data-text"));
			$('#comment-actions').modal();
		});
	});
	$(".replyComment").each(function(){
		var id=$(this).attr("id")
		$("#"+id).click( function () {
			$("#commentnameli").css("display","none");
			$("#commentmailli").css("display","none");
			$("#commenturlli").css("display","none");
			$("#action").val("submitReplyComment");
			$("#coid").val($(this).attr("data-coid"));
			$("#contentid").val($(this).attr("data-cid"));
			$('#comment-actions').modal();
		});
	});
	$("#btnComment").click( function () {
		$("#commentForm").submit();
	});
	$(".delComment").each(function(){
		var id=$(this).attr("id")
		$("#"+id).click( function () {
			if(confirm("确认要删除此评论吗？")){
				location.href='?page=comment&operation=del&id='+$(this).attr("data-coid");
			}
		});
	});
});
</script>