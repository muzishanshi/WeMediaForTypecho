<?php
/**
 * WeMedia（自媒体）Typecho用户中心付费阅读插件
 * @package WeMedia For Typecho
 * @author 二呆
 * @version 1.0.4
 * @link http://www.tongleer.com/
 * @date 2018-09-21
 */
class WeMedia_Plugin implements Typecho_Plugin_Interface{
    // 激活插件
    public static function activate(){
		$db = Typecho_Db::get();
		$prefix = $db->getPrefix();
		self::alterColumn($db,$prefix.'contents','wemedia_isFee','enum("y","n") DEFAULT "n"');
		self::alterColumn($db,$prefix.'contents','wemedia_price','double(10,2) DEFAULT 0');
		self::alterColumn($db,$prefix.'users','wemedia_money','double(10,2) DEFAULT 0');
		self::alterColumn($db,$prefix.'users','wemedia_point','int(11) DEFAULT 0');
		self::alterColumn($db,$prefix.'users','wemedia_realname','varchar(32) DEFAULT NULL');
		self::alterColumn($db,$prefix.'users','wemedia_alipay','varchar(32) DEFAULT 0');
		self::alterColumn($db,$prefix.'users','wemedia_isallow','enum("none","allow","refuse","process") DEFAULT "none"');
		self::alterColumn($db,$prefix.'users','wemedia_info','varchar(255) DEFAULT NULL');
		self::createTableWemediaFeeItem($db);
		self::createTableWemediaMoneyItem($db);
		self::createTableWemediaPointCost($db);
		self::createTableWemediaGoods($db);
		//判断目录权限，并将插件文件写入主题目录
		self::funWriteThemePage($db,'page_wemedia_goods.php');
		self::funWriteThemePage($db,'page_wemedia_user.php');
		self::funWriteThemePage($db,'templates/wemedia_footer.php');
		self::funWriteThemePage($db,'templates/wemedia_header.php');
		self::funWriteThemePage($db,'templates/wemedia_login.php');
		self::funWriteThemePage($db,'templates/wemedia_user_404.php');
		self::funWriteThemePage($db,'templates/wemedia_user_article.php');
		self::funWriteThemePage($db,'templates/wemedia_user_articleedit.php');
		self::funWriteThemePage($db,'templates/wemedia_user_comment.php');
		self::funWriteThemePage($db,'templates/wemedia_user_footer.php');
		self::funWriteThemePage($db,'templates/wemedia_user_goods.php');
		self::funWriteThemePage($db,'templates/wemedia_user_goodsedit.php');
		self::funWriteThemePage($db,'templates/wemedia_user_goodsorder.php');
		self::funWriteThemePage($db,'templates/wemedia_user_header.php');
		self::funWriteThemePage($db,'templates/wemedia_user_index.php');
		self::funWriteThemePage($db,'templates/wemedia_user_info.php');
		self::funWriteThemePage($db,'templates/wemedia_user_member.php');
		self::funWriteThemePage($db,'templates/wemedia_user_money.php');
		self::funWriteThemePage($db,'templates/wemedia_user_sidebar.php');
		self::funWriteThemePage($db,'templates/wemedia_user_water.php');
		//如果数据表没有添加页面就插入
		self::funWriteDataPage($db,'积分商城','wemedia_goods','page_wemedia_goods.php','publish');
		self::funWriteDataPage($db,'用户中心','wemedia_user','page_wemedia_user.php','publish');
        return _t('插件已经激活，需先配置插件信息！');
    }

    // 禁用插件
    public static function deactivate(){
		//删除页面模板
		$db = Typecho_Db::get();
		$queryTheme= $db->select('value')->from('table.options')->where('name = ?', 'theme'); 
		$rowTheme = $db->fetchRow($queryTheme);
		@unlink(dirname(__FILE__).'/../../themes/'.$rowTheme['value'].'/page_wemedia_goods.php');
		@unlink(dirname(__FILE__).'/../../themes/'.$rowTheme['value'].'/page_wemedia_user.php');
		@unlink(dirname(__FILE__).'/../../themes/'.$rowTheme['value'].'/templates/wemedia_footer.php');
		@unlink(dirname(__FILE__).'/../../themes/'.$rowTheme['value'].'/templates/wemedia_header.php');
		@unlink(dirname(__FILE__).'/../../themes/'.$rowTheme['value'].'/templates/wemedia_login.php');
		@unlink(dirname(__FILE__).'/../../themes/'.$rowTheme['value'].'/templates/wemedia_user_404.php');
		@unlink(dirname(__FILE__).'/../../themes/'.$rowTheme['value'].'/templates/wemedia_user_article.php');
		@unlink(dirname(__FILE__).'/../../themes/'.$rowTheme['value'].'/templates/wemedia_user_articleedit.php');
		@unlink(dirname(__FILE__).'/../../themes/'.$rowTheme['value'].'/templates/wemedia_user_comment.php');
		@unlink(dirname(__FILE__).'/../../themes/'.$rowTheme['value'].'/templates/wemedia_user_footer.php');
		@unlink(dirname(__FILE__).'/../../themes/'.$rowTheme['value'].'/templates/wemedia_user_goods.php');
		@unlink(dirname(__FILE__).'/../../themes/'.$rowTheme['value'].'/templates/wemedia_user_goodsedit.php');
		@unlink(dirname(__FILE__).'/../../themes/'.$rowTheme['value'].'/templates/wemedia_user_goodsorder.php');
		@unlink(dirname(__FILE__).'/../../themes/'.$rowTheme['value'].'/templates/wemedia_user_header.php');
		@unlink(dirname(__FILE__).'/../../themes/'.$rowTheme['value'].'/templates/wemedia_user_index.php');
		@unlink(dirname(__FILE__).'/../../themes/'.$rowTheme['value'].'/templates/wemedia_user_info.php');
		@unlink(dirname(__FILE__).'/../../themes/'.$rowTheme['value'].'/templates/wemedia_user_member.php');
		@unlink(dirname(__FILE__).'/../../themes/'.$rowTheme['value'].'/templates/wemedia_user_money.php');
		@unlink(dirname(__FILE__).'/../../themes/'.$rowTheme['value'].'/templates/wemedia_user_sidebar.php');
		@unlink(dirname(__FILE__).'/../../themes/'.$rowTheme['value'].'/templates/wemedia_user_water.php');
        return _t('插件已被禁用');
    }

    // 插件配置面板
    public static function config(Typecho_Widget_Helper_Form $form){
		//版本检查
		$version=file_get_contents('http://api.tongleer.com/interface/WeMedia.php?action=update&version=4');
		$div=new Typecho_Widget_Helper_Layout();
		$div->html('版本检查：'.$version.'
			<h6>使用方法</h6>
			<span><p>第一步：配置下方各项参数；</p></span>
			<span><p>第二步：在编写的原创文章中间通过点击编辑器摘要按钮，插入<!--more-->代码，下方文字即为付费内容；</p></span>
			<span><p>第三步：在每位用户的原创文章列表都可以单独指定是否付费，管理员可以在本插件设置页面进行设置，比如下方的文章管理；</p></span>
			<span>
				第四步：将以下代码放到主题目录下post.php中输出内容的位置进行替换（如：parseContent($this)）即可；
				<pre>&lt;?php WeMedia_Plugin::parseContent($this); ?></pre>
			</span>
			<span><p>第五步：等待其他用户购买对应付费文章；</p></span>
			<span><p>第六步：有买家付款后即可查看付费内容，卖家可以到用户中心查看订单；</p></span>
			<span><p>第七步：在用户中心用户可以发表文章、回复评论、设置资料以及提现；</p></span>
			<span><p>第八步：归纳8步，祝每位有缘之人财源广进，另谢谢使用WeMedia插件，更多功能敬请期待……</p></span>
		');
		$div->render();
		$db = Typecho_Db::get();
		$prefix = $db->getPrefix();
		$options = Typecho_Widget::widget('Widget_Options');
		$plug_url = $options->pluginUrl;
		//付费文章管理
		$div = new Typecho_Widget_Helper_Layout();
		$divstr1='
			<link rel="stylesheet" href="http://cdn.amazeui.org/amazeui/2.7.2/css/amazeui.min.css"/>
			<script src="http://apps.bdimg.com/libs/jquery/1.7.1/jquery.min.js" type="text/javascript"></script>
			<script src="http://cdn.amazeui.org/amazeui/2.7.2/js/amazeui.min.js" type="text/javascript"></script>
			<div style="background-color:#fff;overflow:scroll; height:260px; width:100%; border: solid 0px #aaa; margin: 0 auto;">
			  <table class="am-table am-table-bordered am-table-striped am-text-nowrap">
				<caption>
					<h4>文章管理</h4>
					<form id="searchForm">
						<input type="text" id="searchtext" value="" placeholder="搜索标题，回车提交" />
					</form>
				</caption>
				<thead>
					<tr>
						<th>评论数</th>
						<th>订单数</th>
						<th>标题</th>
						<th>作者</th>
						<th>分类</th>
						<th>日期</th>
						<th>状态</th>
					</tr>
				</thead>
				<tbody id="data-area">
				
		';
		$divstr2='';
		$divstr3='
				</tbody>
			</table>
			<div id="pageBar"><!--这里添加分页按钮栏--></div>
		</div>
		<div class="am-g" id="pricediv" style="display:none;">
		  <div class="am-u-md-8 am-u-sm-centered">
			<form class="am-form" id="feeForm">
			  <fieldset class="am-form-set">
				<input type="text" id="price" value="" />
			  </fieldset>
			  <input type="hidden" id="cid" value="" />
			  <button type="submit" class="am-btn am-btn-primary am-btn-block">确认付费</button>
			</form>
		  </div>
		</div>
		<script>
			var curPage;/*当前页数*/
			var totalItem;/*总记录数*/
			var pageSize;/*每一页记录数*/
			var totalPage;/*总页数*/
			 
			//获取分页数据
			function turnPage(page,search=""){
			  $.ajax({
				type: "POST",
				url: "'.$plug_url.'/WeMedia/ajax/paging_article.php",/*这里是请求的后台地址，自己定义*/
				data: {"page_now":page,"searchtext":search},
				dataType: "json",
				beforeSend: function() {
				  $("#data-area").append("加载中...");
				},
				success: function(json) {
				  $("#data-area").empty();/*移除原来的分页数据*/
				  totalItem = json.totalItem;
				  pageSize = json.pageSize;
				  curPage = page;
				  totalPage = json.totalPage;
				  var data_content = json.data_content;
				  var data_html = "";
				  $.each(data_content,function(index,array) {
					/*添加新的分页数据（数据的显示样式根据自己页面来设置，这里只是一个简单的列表）*/
					if(data_content.wemedia_isFee=="y"){
						data_html = "<tr><td>"+data_content.commentsNum+"</td><td>"+data_content.feeItemCount+"</td><td>"+data_content.title+"</td><td>"+data_content.author+"</td><td>"+data_content.sortName+"</td><td>"+data_content.created+"</td><td><a href=\"javascript:cancelFee("+data_content.cid+");\">"+data_content.wemedia_price+"元付费中</a></td></tr>";
					}else{
						data_html = "<tr><td>"+data_content.commentsNum+"</td><td>"+data_content.feeItemCount+"</td><td>"+data_content.title+"</td><td>"+data_content.author+"</td><td>"+data_content.sortName+"</td><td>"+data_content.created+"</td><td><a href=\"javascript:confirmFee("+data_content.cid+","+data_content.wemedia_price+");\">免费中</a></td></tr>";
					}
				  });
			 
				  $("#data-area").append(data_html);
				},
				complete: function() {    /*添加分页按钮栏*/
				  getPageBar();
				},
				error: function() {
				  alert("数据加载失败");
				}
			  });
			}
			/*获取分页条（分页按钮栏的规则和样式根据自己的需要来设置）*/
			function getPageBar(){
			  if(curPage > totalPage) {
				curPage = totalPage;
			  }
			  if(curPage < 1) {
				curPage = 1;
			  }
			 
			  pageBar = "<ul class=\"am-pagination blog-pagination\">";
			 
			  /*如果不是第一页*/
			  if(curPage != 1){
				pageBar += "<li class=\"am-pagination-prev\"><a href=\"javascript:turnPage(1)\">首页</a></li>";
				pageBar += "<li class=\"am-pagination-prev\"><a href=\"javascript:turnPage("+(curPage-1)+")\"><<</a></li>";
			  }
			 
			  /*显示的页码按钮(5个)*/
			  var start,end;
			  if(totalPage <= 5) {
				start = 1;
				end = totalPage;
			  } else {
				if(curPage-2 <= 0) {
					start = 1;
					end = 5;
				} else {
					if(totalPage-curPage < 2) {
						start = totalPage - 4;
						end = totalPage;
					} else {
						start = curPage - 2;
						end = curPage + 2;
					}
				}
			  }
			 
			  for(var i=start;i<=end;i++) {
				if(i == curPage) {
					pageBar += "<li class=\"am-active\"><a href=\"javascript:turnPage("+i+")\">"+i+"</a></li>";
				} else {
					pageBar += "<li><a href=\"javascript:turnPage("+i+")\">"+i+"</a></li>";
				}
			  }
			   
			  /*如果不是最后页*/
			  if(curPage != totalPage){
				pageBar += "<li class=\"am-pagination-next\"><a href=\"javascript:turnPage("+(parseInt(curPage)+1)+")\">>></a></li>";
				pageBar += "<li class=\"am-pagination-next\"><a href=\"javascript:turnPage("+totalPage+")\">尾页</a></li>";
			  }
				pageBar += "</ul>"; 
			  $("#pageBar").html(pageBar);
			}
			 
			/*页面加载时初始化分页*/
			$(function() {
			  turnPage(1);
			});
			$("#searchForm").submit(function(){
				turnPage(1,$("#searchtext").val());
				return false;
			});
			$("#price").keyup(function(){
				/*先把非数字的都替换掉，除了数字和.*/
				$("#price").val($("#price").val().replace(/[^\d.]/g,""));
				/*保证只有出现一个.而没有多个.*/
				$("#price").val($("#price").val().replace(/\.{2,}/g,"."));
				/*必须保证第一个为数字而不是.*/
				$("#price").val($("#price").val().replace(/^\./g,""));
				/*保证.只出现一次，而不能出现两次以上*/
				$("#price").val($("#price").val().replace(".","$#$").replace(/\./g,"").replace("$#$","."));
				/*只能输入两个小数*/
				$("#price").val($("#price").val().replace(/^(\-)*(\d+)\.(\d\d).*$/,"$1$2.$3"));
			});
			$("#feeForm").submit(function(){
				$.post("'.$plug_url.'/WeMedia/ajax/change_fee.php",{action:"confirmFee",cid:$("#cid").val(),price:$("#price").val()},function(data){
					location.href="plugins.php";
				});
				return false;
			});
			function confirmFee(cid,price){
				if($("#pricediv").css("display")=="block"){
					$("#pricediv").css("display","none");
				}else{
					$("#pricediv").css("display","block");
				}
				$("#price").val(price);
				$("#cid").val(cid);
			}
			function cancelFee(cid){
				$.post("'.$plug_url.'/WeMedia/ajax/change_fee.php",{action:"cancelFee",cid:cid},function(data){
					location.href="plugins.php";
				});
			}
		</script>
		';
		$div->html($divstr1.$divstr2.$divstr3);
		$div->render();
		//配置信息
		/*
		$ispayid = new Typecho_Widget_Helper_Form_Element_Text('ispayid', null, '', _t('ispayid'), _t('在<a href="https://www.ispay.cn/" target="_blank">ispay官网</a>注册的payId'));
        $form->addInput($ispayid->addRule('required', _t('ispayid不能为空！')));
		$ispaykey = new Typecho_Widget_Helper_Form_Element_Text('ispaykey', null, '', _t('ispaykey'), _t('在<a href="https://www.ispay.cn/" target="_blank">ispay官网</a>注册的payKey'));
        $form->addInput($ispaykey->addRule('required', _t('ispayid不能为空！')));
		*/
		$wemedia_yz_client_id = new Typecho_Widget_Helper_Form_Element_Text('wemedia_yz_client_id', null, '', _t('有赞client_id'), _t('在<a href="https://www.youzanyun.com/" target="_blank">有赞云官网</a>授权绑定有赞微小店APP的店铺后注册的client_id'));
        $form->addInput($wemedia_yz_client_id);
		$wemedia_yz_client_secret = new Typecho_Widget_Helper_Form_Element_Text('wemedia_yz_client_secret', null, '', _t('有赞client_secret'), _t('在<a href="https://www.youzanyun.com/" target="_blank">有赞云官网</a>授权绑定有赞微小店APP的店铺后注册的client_secret'));
        $form->addInput($wemedia_yz_client_secret);
		$wemedia_yz_shop_id = new Typecho_Widget_Helper_Form_Element_Text('wemedia_yz_shop_id', null, '', _t('有赞授权店铺id'), _t('在<a href="https://www.youzanyun.com/" target="_blank">有赞云官网</a>授权绑定有赞微小店APP的店铺后注册的授权店铺id'));
        $form->addInput($wemedia_yz_shop_id);
		$wemedia_yz_redirect_url = new Typecho_Widget_Helper_Form_Element_Text('wemedia_yz_redirect_url', array("value"), $plug_url.'/WeMedia/notify_url.php', _t('有赞消息推送网址'), _t('在<a href="https://www.youzanyun.com/" target="_blank">有赞云官网</a>授权绑定有赞微小店APP的店铺后注册的消息推送网址'));
        $form->addInput($wemedia_yz_redirect_url);
		$wemedia_yz_shoptype = new Typecho_Widget_Helper_Form_Element_Radio('wemedia_yz_shoptype', array(
            'oauth'=>_t('工具型'),
            'self'=>_t('自用型')
        ), 'self', _t('自用型'), _t("店铺应用种类"));
        $form->addInput($wemedia_yz_shoptype->addRule('enum', _t(''), array('oauth', 'self')));
		
		$mailsmtp = new Typecho_Widget_Helper_Form_Element_Text('mailsmtp', null, '', _t('smtp服务器(已验证QQ企业邮箱和126邮箱可成功发送)'), _t('用于发送邮箱验证码及其他邮件服务的smtp服务器地址'));
        $form->addInput($mailsmtp->addRule('required', _t('smtp服务器不能为空！')));
		$mailport = new Typecho_Widget_Helper_Form_Element_Text('mailport', null, '', _t('smtp服务器端口'), _t('用于发送邮箱验证码及其他邮件服务的smtp服务器端口'));
        $form->addInput($mailport->addRule('required', _t('smtp服务器端口不能为空！')));
		$mailuser = new Typecho_Widget_Helper_Form_Element_Text('mailuser', null, '', _t('smtp服务器邮箱用户名'), _t('用于发送邮箱验证码及其他邮件服务的smtp服务器邮箱用户名'));
        $form->addInput($mailuser->addRule('required', _t('smtp服务器邮箱用户名不能为空！')));
		$mailpass = new Typecho_Widget_Helper_Form_Element_Password('mailpass', null, '', _t('smtp服务器邮箱密码'), _t('用于发送邮箱验证码及其他邮件服务的smtp服务器邮箱密码'));
        $form->addInput($mailpass->addRule('required', _t('smtp服务器邮箱密码不能为空！')));
		
		$point = new Typecho_Widget_Helper_Form_Element_Text('point', array('value'), 1, _t('积分/评论'), _t('每个有效评论的积分数，默认为1，积分用户兑换商品。'));
        $form->addInput($point->addRule('required', _t('需要输入一个积分数')));
		
		$notice = new Typecho_Widget_Helper_Form_Element_Textarea('notice', null, '', _t('公告'), _t('公告可在用户中心显示'));
        $form->addInput($notice);
    }

    // 个人用户配置面板
    public static function personalConfig(Typecho_Widget_Helper_Form $form){
    }

    // 获得插件配置信息
    public static function getConfig(){
        return Typecho_Widget::widget('Widget_Options')->plugin('WeMedia');
    }
	
	/*修改数据表字段*/
	public static function alterColumn($db,$table,$column,$define){
		$prefix = $db->getPrefix();
		$query= "select * from information_schema.columns WHERE table_name = '".$table."' AND column_name = '".$column."'";
		$row = $db->fetchRow($query);
		if(count($row)==0){
			$db->query('ALTER TABLE `'.$table.'` ADD COLUMN `'.$column.'` '.$define.';');
		}
	}
	
	/*创建支付订单数据表*/
	public static function createTableWemediaFeeItem($db){
		$prefix = $db->getPrefix();
		//$db->query('DROP TABLE IF EXISTS '.$prefix.'weibofile_videoupload');
		$db->query('CREATE TABLE IF NOT EXISTS `'.$prefix.'wemedia_fee_item` (
		  `feeid` varchar(64) COLLATE utf8_general_ci NOT NULL,
		  `feecid` bigint(20) DEFAULT NULL,
		  `feeuid` bigint(20) DEFAULT NULL,
		  `feeprice` double(10,2) DEFAULT NULL,
		  `feetype` enum("alipay","ALIPAY","wxpay","WEIXIN_DAIXIAO","qqpay","bank_pc","tlepay") COLLATE utf8_general_ci DEFAULT "alipay",
		  `feestatus` smallint(2) DEFAULT "0" COMMENT "订单状态：0、未付款；1、付款成功；2、付款失败",
		  `feeinstime` datetime DEFAULT NULL,
		  PRIMARY KEY (`feeid`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;');
	}
	
	/*创建商品数据表*/
	public static function createTableWemediaGoods($db){
		$prefix = $db->getPrefix();
		//$db->query('DROP TABLE IF EXISTS '.$prefix.'weibofile_videoupload');
		$db->query('CREATE TABLE IF NOT EXISTS `'.$prefix.'wemedia_goods` (
		  `goodsid` bigint(20) NOT NULL AUTO_INCREMENT,
		  `goodsuid` bigint(20) DEFAULT NULL,
		  `goodsname` varchar(64) DEFAULT NULL,
		  `goodsdetail` longtext DEFAULT NULL,
		  `goodspoint` int(11) DEFAULT 0 COMMENT "消费积分",
		  `goodsinstime` datetime DEFAULT NULL,
		  PRIMARY KEY (`goodsid`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;');
	}
	
	/*创建积分消费数据表*/
	public static function createTableWemediaPointCost($db){
		$prefix = $db->getPrefix();
		//$db->query('DROP TABLE IF EXISTS '.$prefix.'weibofile_videoupload');
		$db->query('CREATE TABLE IF NOT EXISTS `'.$prefix.'wemedia_point_cost` (
		  `pointid` bigint(20) NOT NULL AUTO_INCREMENT,
		  `pointgid` bigint(20) DEFAULT NULL,
		  `pointuid` bigint(20) DEFAULT NULL,
		  `pointnum` int(11) DEFAULT NULL,
		  `pointstatus` smallint(2) DEFAULT "0" COMMENT "0：兑换申请，1：兑换成功，2：兑换失败",
		  `pointinstime` datetime DEFAULT NULL,
		  PRIMARY KEY (`pointid`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;');
	}
	
	/*创建提现订单数据表*/
	public static function createTableWemediaMoneyItem($db){
		$prefix = $db->getPrefix();
		//$db->query('DROP TABLE IF EXISTS '.$prefix.'weibofile_videoupload');
		$db->query('CREATE TABLE IF NOT EXISTS `'.$prefix.'wemedia_money_item` (
		  `moneyid` bigint(20) NOT NULL AUTO_INCREMENT,
		  `moneyuid` bigint(20) DEFAULT NULL,
		  `moneynum` double(10,2) DEFAULT NULL,
		  `moneytype` enum("alipay") COLLATE utf8_bin DEFAULT "alipay",
		  `moneystatus` smallint(2) DEFAULT "0" COMMENT "0：提现申请，1：提现成功，2：提现失败",
		  `moneyinstime` datetime DEFAULT NULL,
		  PRIMARY KEY (`moneyid`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;');
	}
	
	/*公共方法：将页面写入数据库*/
	public static function funWriteDataPage($db,$title,$slug,$template,$status="hidden"){
		date_default_timezone_set('Asia/Shanghai');
		$query= $db->select('slug')->from('table.contents')->where('template = ?', $template); 
		$row = $db->fetchRow($query);
		if(count($row)==0){
			$contents = array(
				'title'      =>  $title,
				'slug'      =>  $slug,
				'created'   =>  time(),
				'text'=>  '<!--markdown-->',
				'password'  =>  '',
				'authorId'     =>  Typecho_Cookie::get('__typecho_uid'),
				'template'     =>  $template,
				'type'     =>  'page',
				'status'     =>  $status,
			);
			$insert = $db->insert('table.contents')->rows($contents);
			$insertId = $db->query($insert);
			$slug=$contents['slug'];
		}else{
			$slug=$row['slug'];
		}
	}
	
	/*公共方法：将页面写入主题目录*/
	public static function funWriteThemePage($db,$filename){
		$queryTheme= $db->select('value')->from('table.options')->where('name = ?', 'theme'); 
		$rowTheme = $db->fetchRow($queryTheme);
		$path=dirname(__FILE__).'/../../themes/'.$rowTheme['value'];
		if(!is_dir($path."/templates/")){
			mkdir ($path."/templates/", 0777, true );
		}
		if(!is_writable($path)){
			Typecho_Widget::widget('Widget_Notice')->set(_t('主题目录不可写，请更改目录权限。'.__TYPECHO_THEME_DIR__.'/'.$rowTheme['value']), 'success');
		}
		if(!file_exists($path."/".$filename)){
			$regfile = @fopen(dirname(__FILE__)."/page/".$filename, "r") or die("不能读取".$filename."文件");
			$regtext=fread($regfile,filesize(dirname(__FILE__)."/page/".$filename));
			fclose($regfile);
			$regpage = fopen($path."/".$filename, "w") or die("不能写入".$filename."文件");
			fwrite($regpage, $regtext);
			fclose($regpage);
		}
	}
	
	/*公共方法：将页面写入主题目录*/
	public static function removeDir($dirName) { 
		if(! is_dir($dirName)) { 
			return false; 
		} 
		$handle = @opendir($dirName); 
		while(($file = @readdir($handle)) !== false) { 
			if($file != '.' && $file != '..') { 
				$dir = $dirName . '/' . $file; 
				is_dir($dir) ? removeDir($dir) : @unlink($dir); 
			} 
		} 
		closedir($handle); 
		return rmdir($dirName) ; 
	}
	
	/*发送给管理员邮件通知*/
	public static function sendMail($email,$title,$content){
		require __DIR__ . '/libs/email.class.php';
		$options = Typecho_Widget::widget('Widget_Options');
		$option=$options->plugin('WeMedia');
		$smtpserverport =$option->mailport;//SMTP服务器端口//企业QQ:465、126:25
		$smtpserver = $option->mailsmtp;//SMTP服务器//QQ:ssl://smtp.qq.com、126:smtp.126.com
		$smtpusermail = $option->mailuser;//SMTP服务器的用户邮箱
		$smtpemailto = $email;//发送给谁
		$smtpuser = $option->mailuser;//SMTP服务器的用户帐号
		$smtppass = $option->mailpass;//SMTP服务器的用户密码
		$mailtitle = $title;//邮件主题
		$mailcontent = $content;//邮件内容
		$mailtype = "HTML";//邮件格式（HTML/TXT）,TXT为文本邮件
		//************************ 配置信息 ****************************
		$smtp = new smtp($smtpserver,$smtpserverport,true,$smtpuser,$smtppass);//这里面的一个true是表示使用身份验证,否则不使用身份验证.
		$smtp->debug = false;//是否显示发送的调试信息
		$state = $smtp->sendmail($smtpemailto, $smtpusermail, $mailtitle, $mailcontent, $mailtype);
		return $state;
	}
	
	/**
     * 获得有赞支付Token
     * @access public
     * @return void
     */
    public static function getYouzanPayToken($client_id,$client_secret,$shop_id,$redirect_url,$shoptype){
		require_once dirname(__FILE__).'/libs/youzan/YZGetTokenClient.php';
		require_once dirname(__FILE__).'/libs/youzan/YZTokenClient.php';
		date_default_timezone_set('Asia/Shanghai');
		$youzan_config=@unserialize(ltrim(file_get_contents(dirname(__FILE__).'/config/youzan_config.php'),'<?php die; ?>'));
		$day = floor((time()-strtotime($youzan_config["instime"]))/3600/24);
		if($day<7){
			return $youzan_config["access_token"];
		}else{
			$token = new YZGetTokenClient( $client_id , $client_secret );
			$keys['kdt_id'] = $shop_id;
			$keys['redirect_uri'] = $redirect_url;
			$token=$token->get_token( $shoptype , $keys );
			file_put_contents(dirname(__FILE__).'/config/youzan_config.php','<?php die; ?>'.serialize(array(
				'access_token'=>$token['access_token'],
				'expires_in'=>$token['expires_in'],
				'scope'=>$token['scope'],
				'instime'=>date('Y-m-d H:i:s',time())
			)));
			return $token['access_token'];
		}
	}
	
	/**
     * 获得有赞支付二维码
     * @access public
     */
    public static function getYouzanPayQR($client_id,$client_secret,$shop_id,$redirect_url,$shoptype,$cid,$uid,$title,$price){
		require_once dirname(__FILE__).'/libs/youzan/YZGetTokenClient.php';
		require_once dirname(__FILE__).'/libs/youzan/YZTokenClient.php';
		$token=self::getYouzanPayToken($client_id,$client_secret,$shop_id,$redirect_url,$shoptype);
		$client = new YZTokenClient($token);
		$method = 'youzan.pay.qrcode.create';
		$api_version = '3.0.0';
		$my_params = [
			'qr_name' => str_replace('|','',$title).'|'.$cid.'|'.$uid.'|'.$price,
			'qr_price' => $price*100,
			'qr_type' => "QR_TYPE_DYNAMIC",
		];
		$my_files = [];
		$payqrcode=$client->post($method, $api_version, $my_params, $my_files);
		return $payqrcode;
	}
	
	/**
     * 输出内容
     * @access public
     * @return void
     */
    public static function parseContent($obj){
		$db = Typecho_Db::get();
		$options = Typecho_Widget::widget('Widget_Options');
		$option=$options->plugin('WeMedia');
		$plug_url = $options->pluginUrl;
		if (!empty($options->src_add) && !empty($options->cdn_add)) {
			$obj->content = str_ireplace($options->src_add, $options->cdn_add, $obj->content);
		}
		$content=trim($obj->content);
		$query= $db->select()->from('table.contents')->where('cid = ?', $obj->cid); 
		$row = $db->fetchRow($query);
		$queryItem= $db->select()->from('table.wemedia_fee_item')->where('feecid = ?', $obj->cid)->where('feeuid = ?', Typecho_Cookie::get('__typecho_uid'))->where('feestatus = ?', 1); 
		$rowItem = $db->fetchRow($queryItem);
		$queryUser= $db->select()->from('table.users')->where('uid = ?', $row['authorId']); 
		$rowUser = $db->fetchRow($queryUser);
		$wemedia_info=$rowUser["wemedia_info"]==''?'':'作者简介：'.$rowUser["wemedia_info"];
		if($row['wemedia_isFee']=='y'&&count($rowItem)==0&&$row['authorId']!=Typecho_Cookie::get('__typecho_uid')){
			$content=explode('<!--more-->',$content)[0];
			$payqrcode=self::getYouzanPayQR($option->wemedia_yz_client_id,$option->wemedia_yz_client_secret,$option->wemedia_yz_shop_id,$option->wemedia_yz_redirect_url,$option->wemedia_yz_shoptype,$obj->cid,Typecho_Cookie::get('__typecho_uid'),$obj->title,$row['wemedia_price']);
			$qrimg="";
			if(Typecho_Cookie::get('__typecho_uid')!=""){
				$qrimg='<img class="wxpic" align="right" src="'.$payqrcode["response"]['qr_code'].'" style="width:150px;height:150px;margin-left:20px;display:inline;border:none" width="150" height="150"  alt="'.$obj->title.'" />';
			}
			$content.='
			<div style="border:1px dashed #F60; padding:10px; margin:10px 0; line-height:200%; color:#F00; background-color:#FFF4FF; overflow:hidden; clear:both;">
				'.$qrimg.'
				<span style="font-size:18px;">此处内容已经被作者隐藏，请付费后并刷新页面查看内容</span>
				<form id="contentPayForm" method="post" style="margin:10px 0;" action="'.$plug_url.'/WeMedia/pay.php">
					<!--
					<span class="yzts" style="font-size:18px;float:left;">方式：</span>
					<select name="feetype" style="border:none;float:left;width:160px; height:32px; line-height:30px; padding:0 5px; border:1px solid #FF6600;-moz-border-radius: 0px;  -webkit-border-radius: 0px;  border-radius:0px;">
						<option value="alipay">支付宝支付</option>
						<option value="wxpay">微信支付</option>
						<option value="qqpay">QQ钱包支付</option>
						<option value="bank_pc">网银支付</option>
					</select>
					-->
					<div style="clear:left;"></div>
					<span class="yzts" style="font-size:18px;float:left;">价格：</span>
					<div style="border:none;float:left;width:80px; height:32px; line-height:30px; padding:0 5px; border:1px solid #FF6600;-moz-border-radius: 0px;  -webkit-border-radius: 0px;  border-radius:0px;">'.$row['wemedia_price'].'</div>
					<input id="verifybtn" style="border:none;float:left;width:80px; height:32px; line-height:32px; padding:0 5px; background-color:#F60; text-align:center; border:none; cursor:pointer; color:#FFF;-moz-border-radius: 0px; font-size:14px;  -webkit-border-radius: 0px;  border-radius:0px;" name="" type="submit" value="付款" />
					<input type="hidden" name="action" value="feepay" />
					<input type="hidden" name="cid" value="'.$obj->cid.'" />
					<input type="hidden" name="returnurl" value="'.$obj->permalink.'" />
					<input type="hidden" name="uid" id="uid" value="'.Typecho_Cookie::get('__typecho_uid').'" />
				</form>
				<div style="clear:left;"></div>
				<span style="color:#00BF30">点击付款或扫描右侧二维码支付后即可阅读隐藏内容。</span><div class="cl"></div>
				<span style="color:#00BF30">'.$wemedia_info.'</span>
				<script src="http://apps.bdimg.com/libs/jquery/1.7.1/jquery.min.js" type="text/javascript"></script>
				<script>
					$(function() {
						$("#contentPayForm").submit(function(){
							if($("#uid").val()==""){
								alert("请先登录");
								return false;
							}
							window.open("'.$payqrcode["response"]['qr_url'].'");
							return false;
						});
					});
				</script>
			</div>
			';
		}
		echo $content;
	}
}