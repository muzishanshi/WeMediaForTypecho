<?php
/**
 * 积分商城页面
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
$options = Typecho_Widget::widget('Widget_Options');
$option=$options->plugin('WeMedia');
$plug_url = $options->pluginUrl;
if(strpos($this->permalink,'?')){
	$url=substr($this->permalink,0,strpos($this->permalink,'?'));
}else{
	$url=$this->permalink;
}

$id = isset($_GET['id']) ? addslashes($_GET['id']) : '';
?>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/amazeui/2.7.2/css/amazeui.min.css"/>
<script src="https://apps.bdimg.com/libs/jquery/1.7.1/jquery.min.js" type="text/javascript"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/amazeui/2.7.2/js/amazeui.min.js" type="text/javascript"></script>
<script src="https://libs.baidu.com/jquery/1.11.1/jquery.min.js"></script>
<style>
.page-main{
	background-color:#fff;
	width:960px;
	margin:0px auto 0px auto;
}
@media screen and (max-width: 960px) {
	.page-main {width: 100%;}
}
</style>
<!-- content section -->
<section class="page-main" style="word-wrap:break-word;">
	<?php
	if($id==""){
	?>
	<ul class="am-gallery am-avg-sm-2 am-avg-md-3 am-avg-lg-4 am-gallery-overlay" >
	  <?php
		$queryTotal= $this->db->select()->from('table.wemedia_goods');
		$page_now = isset($_GET['page_now']) ? intval($_GET['page_now']) : 1;
		if($page_now<1){
			$page_now=1;
		}
		$resultTotal = $this->db->fetchAll($queryTotal);
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
		$query= $this->db->select()->from('table.wemedia_goods')->order('goodsinstime',Typecho_Db::SORT_DESC)->offset($i)->limit($page_rec);
		$result = $this->db->fetchAll($query);
		$temi=1;
		?>
		<?php
		foreach($result as $value){
			$match_str = "/((http)+.*?((.gif)|(.jpg)|(.bmp)|(.png)|(.GIF)|(.JPG)|(.PNG)|(.BMP)))/";
			preg_match_all ($match_str,$value['goodsdetail'],$matches,PREG_PATTERN_ORDER);
			$imgsrc =$plug_url."/WeMedia/images/random/".rand(1,20).".jpg";
			?>
			<li>
			<div class="am-gallery-item">
				<?php if(count($matches[1])!=0){?>
				<a href="<?=$url;?>?id=<?=$value["goodsid"];?>">
				  <img src="<?=$matches[1][0];?>" alt="<?=$value["goodsname"];?>（￥<?=$value["goodspoint"];?>积分）" />
				  <h3 class="am-gallery-title"><?=$value["goodsname"];?>（￥<?=$value["goodspoint"];?>积分）</h3>
				</a>
				<?php }else{?>
				<a href="<?=$url;?>?id=<?=$value["goodsid"];?>">
				  <img src="<?=$imgsrc;?>" alt="<?=$value["goodsname"];?>（￥<?=$value["goodspoint"];?>积分）" />
				  <h3 class="am-gallery-title"><?=$value["goodsname"];?>（￥<?=$value["goodspoint"];?>积分）</h3>
				</a>
				<?php }?>
			</div>
			</li>
			<?php
			$temi++;
		}
		?>
	</ul>
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
	<?php
	}else if($id!=""){
		$queryGoods= $this->db->select()->from('table.wemedia_goods')->join('table.users', 'table.wemedia_goods.goodsuid = table.users.uid',Typecho_Db::INNER_JOIN)->where('goodsid = ?', $id); 
		$rowGoods = $this->db->fetchRow($queryGoods);
		
		$queryUser= $this->db->select()->from('table.users')->where('uid = ?', Typecho_Cookie::get('__typecho_uid')); 
		$rowUser = $this->db->fetchRow($queryUser);
		
		$goto = isset($_GET['goto']) ? addslashes($_GET['goto']) : '';
		if($goto=='buy'){
			$costData=array(
				'pointgid' => $id,
				'pointuid' => Typecho_Cookie::get('__typecho_uid'),
				'pointinstime'=>date('Y-m-d H:i:s',$this->options->time),
				'pointnum'=>$rowGoods["goodspoint"],
				'pointstatus'=>1
			);
			$insert = $this->db->insert('table.wemedia_point_cost')->rows($costData);
			$this->db->query($insert);
			$update = $this->db->update('table.users')->rows(array("wemedia_point"=>$rowUser["wemedia_point"]-$rowGoods["goodspoint"]))->where('uid=?',Typecho_Cookie::get('__typecho_uid'));
			$updateRows= $this->db->query($update);
			$this->response->redirect($url.'?id='.$id);
		}
		?>
		<ol class="am-breadcrumb">
			<li><a href="<?=$this->options ->siteUrl();?>" class="am-icon-home">首页</a></li>
			<li class="am-active"><?php $this->title(); ?></li>
		</ol>
		<article class="am-article am-paragraph am-paragraph-default" data-am-widget="paragraph" data-am-paragraph="{ tableScrollable: true, pureview: true }">
		  <div class="am-article-hd">
			<h1 class="am-article-title"><?php echo $rowGoods["goodsname"]; ?></h1>
			<p class="am-article-meta">
				<div>
					<small>
						<input type="hidden" id="userpoint" value="<?=$rowUser["wemedia_point"];?>">
						<input type="hidden" id="goods-login" data-login="<?=$this->user->hasLogin();?>">
						<a href="<?=$rowGoods["url"]!=""?$rowGoods["url"]:'javascript:;';?>" target="_blank" rel="nofollow"><?=$rowGoods["screenName"];?></a> 发布 | <?=$rowGoods["goodsinstime"];?> | 卖家邮箱：<?=$rowGoods["mail"];?> 
						<?php if($rowGoods["uid"]!=Typecho_Cookie::get('__typecho_uid')){?>
						| <a id="buygoods" data-point="<?=$rowGoods["goodspoint"];?>" href="javascript:;">花费（<?=$rowGoods["goodspoint"];?>）积分购买</a>
						<?php }?>
					</small>
				</div>
			</p>
		  </div>
		  <div class="am-article-bd">
			<?php echo Markdown::convert($rowGoods["goodsdetail"]); ?>
		  </div>
		</article>
		<script>
		$("#buygoods").click( function () {
			if($('#goods-login').attr('data-login')!=1){
				alert('登陆后方可购买');
				return;
			}
			if($('#userpoint').val()<$(this).attr('data-point')){
				alert('您的积分不够，可以在用户中心同步积分或者多多评论文章可提高积分，快去评论吧！');
				return;
			}
			if(confirm("您确定要花费"+$(this).attr('data-point')+"积分购买此商品吗？")){
				location.href='<?=$url;?>?id=<?=$id;?>&goto=buy';
			}
		});
		</script>
		<?php
	}
	?>
</section>
<!-- end content section -->
