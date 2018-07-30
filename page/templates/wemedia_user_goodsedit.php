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
$query= $this->db->select()->from('table.wemedia_goods')->where('goodsuid = ?', Typecho_Cookie::get('__typecho_uid'))->where('goodsid = ? ', $id); 
$row = $this->db->fetchRow($query);
$action = isset($_POST['action']) ? addslashes($_POST['action']) : '';
if($action=='updategoods'){
	$goodsname = isset($_POST['goodsname']) ? addslashes($_POST['goodsname']) : '';
	$goodspoint = isset($_POST['goodspoint']) ? addslashes($_POST['goodspoint']) : '';
	$goodsdetail = isset($_POST['goodsdetail']) ? addslashes($_POST['goodsdetail']) : '';
	if($goodsname!=''&&$goodspoint!=''&&$goodsdetail!=''){
		$goodsData=array(
			'goodsname' => $goodsname,
			'goodspoint' => $goodspoint,
			'goodsinstime'=>date('Y-m-d H:i:s',$this->options->time),
			'goodsdetail'=>$goodsdetail
		);
		$update = $this->db->update('table.wemedia_goods')->rows($goodsData)->where('goodsid=?',$id);
		$updateRows= $this->db->query($update);
		$this->response->redirect($url.'?page=goods');
	}else{
		$this->response->redirect($url.'?page=goodsedit&id='.$id.'&error=null');
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
          <form id="editgoodsForm" method="post" action="" class="am-form am-form-horizontal">
            <div class="am-form-group">
                <input type="text" name="goodsname" id="goodsname" value="<?=$row['goodsname'];?>" placeholder="商品名称">
            </div>
            <div class="am-form-group">
              <input type="number" name="goodspoint" id="goodspoint" value="<?=$row['goodspoint'];?>" placeholder="花费积分">
            </div>
			
			<div class="am-form-group">
				<div id="editormd">
					<textarea style="display:none;" id="goodsdetail" name="goodsdetail"><?php echo $row["goodsdetail"];?></textarea>
				</div>
			</div>
			
			<div class="am-form-group">
				<input type="hidden" name="action" value="updategoods" />
                <button type="submit" id="submitinfo" class="am-btn am-btn-primary">保存修改</button>
				<small id="errormsg"><font color="red"><?php if(@$_GET['error']=='null'){echo '请填写完整';}?></font></small>
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
		$("#editgoodsForm").submit(function(){
			
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