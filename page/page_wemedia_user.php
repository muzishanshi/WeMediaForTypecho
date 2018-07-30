<?php
/**
 * 用户中心页面
 *
 * @package custom
 */
date_default_timezone_set('Asia/Shanghai');
if (!defined('__TYPECHO_ROOT_DIR__')) exit;
$queryPlugins= $this->db->select('value')->from('table.options')->where('name = ?', 'plugins'); 
$rowPlugins = $this->db->fetchRow($queryPlugins);
$plugins=@unserialize($rowPlugins['value']);
if(!isset($plugins['activated']['WeMedia'])){
	die('未启用wemedia插件');
}
$page = isset($_GET['page']) ? addslashes($_GET['page']) : '';
?>
<?php $this->need('templates/wemedia_header.php');?>
<?php
if($this->user->group==NULL){
	$this->need('templates/wemedia_login.php');
}else if($this->user->group!=NULL){
	if($page==''){
		$this->need('templates/wemedia_user_index.php');
	}else if($page=='article'){
		$this->need('templates/wemedia_user_article.php');
	}else if($page=='articleedit'){
		$this->need('templates/wemedia_user_articleedit.php');
	}else if($page=='comment'){
		$this->need('templates/wemedia_user_comment.php');
	}else if($page=='goods'){
		$this->need('templates/wemedia_user_goods.php');
	}else if($page=='goodsedit'){
		$this->need('templates/wemedia_user_goodsedit.php');
	}else if($page=='goodsorder'){
		$this->need('templates/wemedia_user_goodsorder.php');
	}else if($page=='money'){
		$this->need('templates/wemedia_user_money.php');
	}else if($page=='water'){
		$this->need('templates/wemedia_user_water.php');
	}else if($page=='info'){
		$this->need('templates/wemedia_user_info.php');
	}else if($page=='member'){
		$this->need('templates/wemedia_user_member.php');
	}else{
		$this->need('templates/wemedia_user_404.php');
	}
}
?>
<?php $this->need('templates/wemedia_footer.php');?>