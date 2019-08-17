<?php
date_default_timezone_set('Asia/Shanghai');
$options = Typecho_Widget::widget('Widget_Options');
$plug_url = $options->pluginUrl;
if(strpos($this->permalink,'?')){
	$url=substr($this->permalink,0,strpos($this->permalink,'?'));
}else{
	$url=$this->permalink;
}

$id = isset($_GET['id']) ? addslashes($_GET['id']) : '';
$query= $this->db->select()->from('table.contents')->join('table.relationships', 'table.contents.cid = table.relationships.cid',Typecho_Db::INNER_JOIN)->join('table.metas', 'table.metas.mid = table.relationships.mid',Typecho_Db::INNER_JOIN)->where('authorId = ?', Typecho_Cookie::get('__typecho_uid'))->where('table.contents.type = ?', 'post')->where('table.metas.type = ?', 'category')->where('table.contents.cid = ? ', $id); 
$row = $this->db->fetchRow($query);
$action = isset($_POST['action']) ? addslashes($_POST['action']) : '';
if($action=='updatearticle'){
	$title = isset($_POST['title']) ? addslashes($_POST['title']) : '';
	$cate = isset($_POST['cate']) ? addslashes($_POST['cate']) : '';
	$text = isset($_POST['text']) ? addslashes($_POST['text']) : '';
	if(strpos($text, '<!--markdown-->') !== 0){
		$text='<!--markdown-->'.$text;
	}
	if($title!=''){
		$contentData=array(
			'title' => $title,
			'text' => $text,
			'modified'=>$this->options->time,
			'type'=>'post',
			'status'=>'waiting'
		);
		$update = $this->db->update('table.contents')->rows($contentData)->where('cid=?',$id);
		$updateRows= $this->db->query($update);
		$this->response->redirect($url.'?page=article');
	}else{
		$this->response->redirect($url.'?page=articleedit&id='.$id.'&error=titlenull');
	}
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
        <div class="am-fl am-cf">
			<strong class="am-text-primary am-text-lg">编辑文章</strong> / 
			<small>
				<a href="javascript:history.go(-1);">返回</a>
			</small>
		</div>
      </div>

      <hr/>

      <div class="am-g">
        <div class="am-u-sm-12 am-u-md-4 am-u-md-push-8">
          
        </div>

        <div class="am-u-sm-12 am-u-md-8 am-u-md-pull-4">
          <form id="editarticleForm" method="post" action="" class="am-form am-form-horizontal">
            <div class="am-form-group">
                <input type="text" name="title" id="title" value="<?=$row['title'];?>" placeholder="标题">
				<small id="titlemsg"><font color="red"><?php if(@$_GET['error']=='titlenull'){echo '标题不能为空';}?></font></small>
            </div>
			<?php
			$queryCate= $this->db->select()->from('table.metas')->where('type = ?', 'category')->where('parent = ?', 0); 
			$rowCate = $this->db->fetchAll($queryCate);
			?>
            <div class="am-form-group">
              <select name="cate" id="cate">
				<?php foreach($rowCate as $value){?>
				<option value="<?=$value["mid"];?>" <?php if($row["mid"]==$value["mid"]){?>selected<?php }?>><?=$value["name"];?></option>
				<?php }?>
			  </select>
            </div>
			
			<div class="am-form-group">
				<div id="editormd">
					
					<textarea style="display:none;" id="text" name="text"><?php if(strpos($row["text"], '<!--markdown-->') === 0){echo substr($row["text"],15);}else{echo $row["text"];}?></textarea>
				</div>
			</div>
			
			<div class="am-form-group">
				<input type="hidden" name="action" value="updatearticle" />
                <button type="submit" id="submitinfo" class="am-btn am-btn-primary">保存修改</button>
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
		$("#editarticleForm").submit(function(){
			
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
	});
</script>