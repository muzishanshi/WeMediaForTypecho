<?php
/**
 * WeMedia（自媒体）Typecho用户中心付费阅读插件
 * @package WeMedia For Typecho
 * @author 二呆
 * @version 1.0.8
 * @link http://www.tongleer.com/
 * @date 2019-03-23
 */
class WeMedia_Plugin implements Typecho_Plugin_Interface{
    // 激活插件
    public static function activate(){
		Typecho_Plugin::factory('admin/write-post.php')->bottom = array('WeMedia_Plugin', 'tleWeMediaToolbar');
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
		self::alterColumn($db,$prefix.'wemedia_fee_item','feecookie','varchar(255) DEFAULT NULL');
		self::alterColumn($db,$prefix.'contents','wemedia_islogin','enum("y","n") DEFAULT "n"');
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
		$db = Typecho_Db::get();
		$prefix = $db->getPrefix();
		$options = Typecho_Widget::widget('Widget_Options');
		$plug_url = $options->pluginUrl;
		//版本检查
		$div=new Typecho_Widget_Helper_Layout();
		$div->html('<small>
			<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/amazeui/2.7.2/css/amazeui.min.css"/>
			<script src="https://apps.bdimg.com/libs/jquery/1.7.1/jquery.min.js" type="text/javascript"></script>
			<script src="https://cdnjs.cloudflare.com/ajax/libs/amazeui/2.7.2/js/amazeui.min.js" type="text/javascript"></script>
			<script>
				$.post("'.$plug_url.'/WeMedia/ajax/update.php",{version:8},function(data){
					$("#versionCode").html(data);
				});
			</script>
			版本检查：<span id="versionCode"></span>
			<h6>基础功能</h6>
			<span><p>第一步：配置下方各项参数；</p></span>
			<span>
				<p>
					第二步：在编写的原创文章中间通过点击编辑器的付费按钮（￥），插入以下代码，中间部分即为付费内容；<br />
					<font color="red">
						&lt;!--WeMedia start--><br />
						付费内容<br />
						&lt;!--WeMedia end--><br />
					</font>
				</p>
			</span>
			<span><p>第三步：在每位用户的原创文章列表都可以单独指定是否付费，管理员可以在本插件设置页面进行设置，比如下方的文章管理；</p></span>
			<span>
				<p>
					第四步：替换主题目录下post.php中输出内容的代码，如：<br />
					&lt;?php $this->content; ?>替换成<font color="red">&lt;?php echo WeMedia_Plugin::parseContent($this); ?></font><br />
					如果输出内容是其他代码，在不影响主题自有功能的情况下，页可替换成以上代码。<br />
					继续替换主题目录下archive.php或index.php中输出摘要或内容的代码，没有则不替换，如：<br />
					&lt;?php $this->excerpt(140, "..."); ?>替换成<font color="red">&lt;?php echo WeMedia_Plugin::parseExcerpt($this,140, "..."); ?></font>
				</p>
			</span>
			<h6>增值功能</h6>
			<span><p>第五步：等待其他用户或游客购买对应付费文章；</p></span>
			<span><p>第六步：有网站用户买家付款后即可查看付费内容，卖家可以到用户中心查看订单；</p></span>
			<span><p>第七步：在用户中心用户可以发表文章、回复评论、设置资料以及提现；</p></span>
			<span><p>第八步：归纳8步，祝每位有缘之人财源广进，另谢谢使用WeMedia插件，更多功能敬请期待……</p></span>
		</small>');
		$div->render();
		//付费文章管理
		$div = new Typecho_Widget_Helper_Layout();
		$divstr1='
			<div style="background-color:#fff;overflow:scroll; height:650px; width:100%; border: solid 0px #aaa; margin: 0 auto;">
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
				单价：<input type="text" id="price" value="" />
				免登录<input type="radio" name="wemedia_islogin" id="wemedia_islogin_n" value="n">
				需登录<input type="radio" name="wemedia_islogin" id="wemedia_islogin_y" value="y">
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
					if(array.wemedia_isFee=="y"){
						data_html = "<tr><td>"+array.commentsNum+"</td><td>"+array.feeItemCount+"</td><td>"+array.title+"</td><td>"+array.author+"</td><td>"+array.sortName+"</td><td>"+array.created+"</td><td><a href=\"javascript:cancelFee("+array.cid+");\">"+array.wemedia_price+"元付费中</a></td></tr>";
					}else{
						data_html = "<tr><td>"+array.commentsNum+"</td><td>"+array.feeItemCount+"</td><td>"+array.title+"</td><td>"+array.author+"</td><td>"+array.sortName+"</td><td>"+array.created+"</td><td><a href=\"javascript:confirmFee("+array.cid+","+array.wemedia_price+",\'"+array.wemedia_islogin+"\');\">免费中</a></td></tr>";
					}
					$("#data-area").append(data_html);
				  });
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
				if($("#price").val()<=0){
					alert("输入一个大于0的单价");
					return false;
				}
				$.post("'.$plug_url.'/WeMedia/ajax/change_fee.php",{action:"confirmFee",cid:$("#cid").val(),price:$("#price").val(),islogin:$("#feeForm input[name=\"wemedia_islogin\"]:checked").val()},function(data){
					location.href="plugins.php";
				});
				return false;
			});
			function confirmFee(cid,price,islogin){
				if($("#pricediv").css("display")=="block"){
					$("#pricediv").css("display","none");
				}else{
					$("#pricediv").css("display","block");
				}
				$("#price").val(price);
				$("#cid").val(cid);
				if(islogin=="y"){
					$("#wemedia_islogin_y").attr("checked","checked");
				}else{
					$("#wemedia_islogin_n").attr("checked","checked");
				}
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
		$wemedia_cookietime = new Typecho_Widget_Helper_Form_Element_Text('wemedia_cookietime', array('value'), 1, _t('免登录Cookie保存时间(天)'), _t('指定使用免登录付费后几天内可以查看隐藏内容，默认为1天，不会记录到买入订单中。'));
        $form->addInput($wemedia_cookietime);
		
		$spay_wxpay_id = new Typecho_Widget_Helper_Form_Element_Text('spay_wxpay_id', array('value'), "", _t('SPay微信支付合作身份者ID'), _t('SPay网站（主：http://spay.swapteam.cn/；副：http://spay.8889838.com）注册授权微信支付的合作身份者id。'));
        $form->addInput($spay_wxpay_id);
		$spay_wxpay_key = new Typecho_Widget_Helper_Form_Element_Text('spay_wxpay_key', array('value'), "", _t('SPay微信支付安全检验码'), _t('SPay网站（主：http://spay.swapteam.cn/；副：http://spay.8889838.com）注册授权微信支付的合作身份者id。'));
        $form->addInput($spay_wxpay_key);
		$spay_wxpay_notify_url = new Typecho_Widget_Helper_Form_Element_Text('spay_wxpay_notify_url', array('value'), $plug_url.'/WeMedia/notify_url.php', _t('SPay异步回调接口'), _t('支付完成后异步回调的接口地址'));
        $form->addInput($spay_wxpay_notify_url);
		$spay_wxpay_return_url = new Typecho_Widget_Helper_Form_Element_Text('spay_wxpay_return_url', array('value'), $plug_url.'/WeMedia/return_url.php', _t('SPay同步回调接口'), _t('支付完成后同步回调的接口地址'));
        $form->addInput($spay_wxpay_return_url);
		
		$mailsmtp = new Typecho_Widget_Helper_Form_Element_Text('mailsmtp', null, '', _t('smtp服务器(已验证QQ企业邮箱和126邮箱可成功发送)'), _t('用于用户中心发送邮箱验证码及其他邮件服务的smtp服务器地址，QQ企业邮箱：ssl://smtp.exmail.qq.com:465；126邮箱：smtp.126.com:25'));
        $form->addInput($mailsmtp);
		$mailport = new Typecho_Widget_Helper_Form_Element_Text('mailport', null, '', _t('smtp服务器端口'), _t('用于用户中心发送邮箱验证码及其他邮件服务的smtp服务器端口'));
        $form->addInput($mailport);
		$mailuser = new Typecho_Widget_Helper_Form_Element_Text('mailuser', null, '', _t('smtp服务器邮箱用户名'), _t('用于用户中心发送邮箱验证码及其他邮件服务的smtp服务器邮箱用户名'));
        $form->addInput($mailuser);
		$mailpass = new Typecho_Widget_Helper_Form_Element_Password('mailpass', null, '', _t('smtp服务器邮箱密码'), _t('用于用户中心发送邮箱验证码及其他邮件服务的smtp服务器邮箱密码'));
        $form->addInput($mailpass);
		
		$point = new Typecho_Widget_Helper_Form_Element_Text('point', array('value'), 1, _t('积分/评论'), _t('每个有效评论的积分数，默认为1，积分商城用户兑换商品。'));
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
		  `feetype` enum("alipay","wxpay","wx","WEIXIN_DAIXIAO","qqpay","bank_pc","tlepay") COLLATE utf8_general_ci DEFAULT "alipay",
		  `feestatus` smallint(2) DEFAULT "0" COMMENT "订单状态：0、未付款；1、付款成功；2、付款失败",
		  `feeinstime` datetime DEFAULT NULL,
		  PRIMARY KEY (`feeid`)
		) DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;');
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
		) DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;');
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
		) DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;');
	}
	
	/*创建提现订单数据表*/
	public static function createTableWemediaMoneyItem($db){
		$prefix = $db->getPrefix();
		//$db->query('DROP TABLE IF EXISTS '.$prefix.'weibofile_videoupload');
		$db->query('CREATE TABLE IF NOT EXISTS `'.$prefix.'wemedia_money_item` (
		  `moneyid` bigint(20) NOT NULL AUTO_INCREMENT,
		  `moneyuid` bigint(20) DEFAULT NULL,
		  `moneynum` double(10,2) DEFAULT NULL,
		  `moneytype` enum("alipay") COLLATE utf8_general_ci DEFAULT "alipay",
		  `moneystatus` smallint(2) DEFAULT "0" COMMENT "0：提现申请，1：提现成功，2：提现失败",
		  `moneyinstime` datetime DEFAULT NULL,
		  PRIMARY KEY (`moneyid`)
		) DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;');
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
	 * @param $codeLength   指定要生成的长度
	 * @param $codeCount    指定需要的个数
	 * @return array    生成字符串的集合
	 */
	public static function randomCode($codeLength, $codeCount){
		$str1 = '1234567890';
		$str2 = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
		$str3 = 'abcdefghijklmnopqrstuvwxyz';
		$arr = [$str1 , $str2 , $str3] ;
		$code_list = array();    // 接收随机数的数组
		// 生产制定个数
		for ($j = 1; $j <= $codeCount; $j++) {
			$code = "";
			for ($i = 1; $i <= $codeLength; $i++) {  // 生成指定位随机数
				$str = implode('',$arr);
				$code .= $str[mt_rand(0, strlen($str) - 1)];
			}
			if (!in_array($code, $code_list)) {
				$code_list[$j] = $code;
			} else {
				$j--;
			}
		}
		return $code_list;
	}
	
	/**
     * 后台编辑器添加付费阅读按钮
     * @access public
     * @return void
     */
	public static function tleWeMediaToolbar(){
		?>
		<script type="text/javascript">
			$(function(){
				if($('#wmd-button-row').length>0){
					$('#wmd-button-row').append('<li class="wmd-button" id="wmd-button-WeMedia" style="font-size:20px;float:left;color:#AAA;width:20px;" title=付费阅读><b>￥</b></li>');
				}else{
					$('#text').before('<a href="#" id="wmd-button-WeMedia" title="付费阅读"><b>￥</b></a>');
				}
				$(document).on('click', '#wmd-button-WeMedia', function(){
					$('#text').val($('#text').val()+'\r\n<!--WeMedia start-->\r\n\r\n<!--WeMedia end-->');
				});
				/*移除弹窗*/
				if(($('.wmd-prompt-dialog').length != 0) && e.keyCode == '27') {
					cancelAlert();
				}
			});
			function cancelAlert() {
				$('.wmd-prompt-dialog').remove()
			}
		</script>
		<?php
	}
	
	/**
     * 输出摘要
     * @access public
     * @return void
     */
    public static function parseExcerpt($obj,$length=140,$trim="..."){
		$excerpt=trim($obj->excerpt);
		if (preg_match_all('/&lt;!--wechatfans start--&gt;([\s\S]*?)&lt;!--wechatfans end--&gt;/i', $excerpt, $hide_words)){
			$excerpt = str_replace($hide_words[0], '', $excerpt);
		}
		if (preg_match_all('/&lt;!--WeMedia start--&gt;([\s\S]*?)&lt;!--WeMedia end--&gt;/i', $excerpt, $hide_words)){
			$excerpt = str_replace($hide_words[0], '', $excerpt);
		}
		$excerpt=Typecho_Common::subStr(strip_tags($excerpt), 0, $length, $trim);
		return $excerpt;
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
		$content=trim($obj->content);
		$query= $db->select()->from('table.contents')->where('cid = ?', $obj->cid); 
		$row = $db->fetchRow($query);
		if (preg_match_all('/&lt;!--WeMedia start--&gt;([\s\S]*?)&lt;!--WeMedia end--&gt;/i', $content, $hide_content)){
			if($row['wemedia_isFee']=='y'&&$row['authorId']!=Typecho_Cookie::get('__typecho_uid')){
				if($row["wemedia_islogin"]=="n"){
					if(!isset($_COOKIE["TypechoReadyPayCookie"])){
						$cookietime=$option->wemedia_cookietime==""?1:$option->wemedia_cookietime;
						$randomCode=self::randomCode(10,1)[1];
						setcookie("TypechoReadyPayCookie",$randomCode, time()+3600*24*$cookietime);
						$TypechoReadyPayCookie=$randomCode;
					}else{
						$TypechoReadyPayCookie=$_COOKIE["TypechoReadyPayCookie"];
					}
					$queryItem= $db->select()->from('table.wemedia_fee_item')->where('feecookie = ?', $TypechoReadyPayCookie)->where('feestatus = ?', 1)->where('feecid = ?', $obj->cid); 
					$rowItem = $db->fetchRow($queryItem);
					$queryUser= $db->select()->from('table.users')->where('uid = ?', $row['authorId']); 
					$rowUser = $db->fetchRow($queryUser);
					$wemedia_info=$rowUser["wemedia_info"]==''?'':'作者简介：'.$rowUser["wemedia_info"];
					if(count($rowItem)==0){
						$hide_notice='
						<div style="border:1px dashed #F60; padding:10px; margin:10px 0; line-height:200%; color:#F00; background-color:#FFF4FF; overflow:hidden; clear:both;">
							<span style="font-size:18px;">此处内容已经被作者隐藏，请付费后刷新页面查看内容</span>
							<form id="contentPayForm" method="post" style="margin:10px 0;" action="'.$plug_url.'/WeMedia/pay.php" target="_blank">
								<span class="yzts" style="font-size:18px;float:left;">方式：</span>
								<select name="feetype" style="border:none;float:left;width:160px; height:32px; line-height:30px; padding:0 5px; border:1px solid #FF6600;-moz-border-radius: 0px;  -webkit-border-radius: 0px;  border-radius:0px;">
									<!--
									<option value="alipay">支付宝支付</option>
									<option value="qqpay">QQ钱包支付</option>
									<option value="bank_pc">网银支付</option>
									-->
									<option value="wx">微信支付</option>
								</select>
								<div style="clear:left;"></div>
								<span class="yzts" style="font-size:18px;float:left;">价格：</span>
								<div style="border:none;float:left;width:80px; height:32px; line-height:30px; padding:0 5px; border:1px solid #FF6600;-moz-border-radius: 0px;  -webkit-border-radius: 0px;  border-radius:0px;">'.$row['wemedia_price'].'</div>
								<input id="verifybtn" style="border:none;float:left;width:80px; height:32px; line-height:32px; padding:0 5px; background-color:#F60; text-align:center; border:none; cursor:pointer; color:#FFF;-moz-border-radius: 0px; font-size:14px;  -webkit-border-radius: 0px;  border-radius:0px;" name="" type="submit" value="付款" />
								<input type="hidden" name="action" value="spaysubmit" />
								<input type="hidden" name="cid" value="'.urlencode($obj->cid).'" />
								<input type="hidden" name="feecookie" value="'.$TypechoReadyPayCookie.'" />
								<input type="hidden" name="returnurl" value="'.$obj->permalink.'" />
							</form>
							<div style="clear:left;"></div>
							<span style="color:#00BF30">点击付款支付后'.$option->wemedia_cookietime.'天内即可阅读隐藏内容。</span><div class="cl"></div>
							<span style="color:#00BF30">'.$wemedia_info.'</span>
						</div>
						';
						$content = str_replace($hide_content[0], $hide_notice, $content);
					}else{
						$content = str_replace($hide_content[0], '<div style="border:1px dashed #F60; padding:10px; margin:10px 0; line-height:200%;  background-color:#FFF4FF; overflow:hidden; clear:both;">'.$hide_content[1][0].'</div>', $content);
					}
				}else if($row["wemedia_islogin"]=="y"){
					$queryItem= $db->select()->from('table.wemedia_fee_item')->where('feecid = ?', $obj->cid)->where('feeuid = ?', Typecho_Cookie::get('__typecho_uid'))->where('feestatus = ?', 1); 
					$rowItem = $db->fetchRow($queryItem);
					$queryUser= $db->select()->from('table.users')->where('uid = ?', $row['authorId']); 
					$rowUser = $db->fetchRow($queryUser);
					$wemedia_info=$rowUser["wemedia_info"]==''?'':'作者简介：'.$rowUser["wemedia_info"];
					if(count($rowItem)==0){
						$hide_notice='
						<div style="border:1px dashed #F60; padding:10px; margin:10px 0; line-height:200%; color:#F00; background-color:#FFF4FF; overflow:hidden; clear:both;">
							<span style="font-size:18px;">此处内容已经被作者隐藏，请付费后刷新页面查看内容</span>
							<form id="contentPayForm" method="post" style="margin:10px 0;" action="'.$plug_url.'/WeMedia/pay.php" target="_blank">
								<span class="yzts" style="font-size:18px;float:left;">方式：</span>
								<select name="feetype" style="border:none;float:left;width:160px; height:32px; line-height:30px; padding:0 5px; border:1px solid #FF6600;-moz-border-radius: 0px;  -webkit-border-radius: 0px;  border-radius:0px;">
									<!--
									<option value="alipay">支付宝支付</option>
									<option value="qqpay">QQ钱包支付</option>
									<option value="bank_pc">网银支付</option>
									-->
									<option value="wx">微信支付</option>
								</select>
								<div style="clear:left;"></div>
								<span class="yzts" style="font-size:18px;float:left;">价格：</span>
								<div style="border:none;float:left;width:80px; height:32px; line-height:30px; padding:0 5px; border:1px solid #FF6600;-moz-border-radius: 0px;  -webkit-border-radius: 0px;  border-radius:0px;">'.$row['wemedia_price'].'</div>
								<input id="verifybtn" style="border:none;float:left;width:80px; height:32px; line-height:32px; padding:0 5px; background-color:#F60; text-align:center; border:none; cursor:pointer; color:#FFF;-moz-border-radius: 0px; font-size:14px;  -webkit-border-radius: 0px;  border-radius:0px;" name="" type="submit" value="付款" />
								<input type="hidden" name="action" value="spaysubmit" />
								<input type="hidden" name="cid" value="'.urlencode($obj->cid).'" />
								<input type="hidden" name="returnurl" value="'.$obj->permalink.'" />
								<input type="hidden" name="uid" id="uid" value="'.Typecho_Cookie::get('__typecho_uid').'" />
							</form>
							<div style="clear:left;"></div>
							<span style="color:#00BF30">登陆后点击付款支付后即可阅读隐藏内容。</span><div class="cl"></div>
							<span style="color:#00BF30">'.$wemedia_info.'</span>
							<script src="https://apps.bdimg.com/libs/jquery/1.7.1/jquery.min.js" type="text/javascript"></script>
							<script>
								$(function() {
									$("#contentPayForm").submit(function(){
										if($("#uid").val()==""){
											alert("请先登录");
											return false;
										}
									});
								});
							</script>
						</div>
						';
						$content = str_replace($hide_content[0], $hide_notice, $content);
					}else{
						$content = str_replace($hide_content[0], '<div style="border:1px dashed #F60; padding:10px; margin:10px 0; line-height:200%;  background-color:#FFF4FF; overflow:hidden; clear:both;">'.$hide_content[1][0].'</div>', $content);
					}
				}
			}else{
				$content = str_replace($hide_content[0], '<div style="border:1px dashed #F60; padding:10px; margin:10px 0; line-height:200%;  background-color:#FFF4FF; overflow:hidden; clear:both;">'.$hide_content[1][0].'</div>', $content);
			}
		}
		return $content;
	}
}