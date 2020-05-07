<?php
/**
 * WeMediaForTypecho自媒体付费阅读插件<div class="WeMediaUpdateSet"><br /><a href="javascript:;" title="插件因兴趣于闲暇时间所写，故会有代码不规范、不专业和bug的情况，但完美主义促使代码还说得过去，如有bug或使用问题进行反馈即可。">鼠标轻触查看备注</a>&nbsp;<a href="http://club.tongleer.com" target="_blank">论坛</a>&nbsp;<a href="https://www.tongleer.com/api/web/pay.png" target="_blank">打赏</a>&nbsp;<a href="http://mail.qq.com/cgi-bin/qm_share?t=qm_mailme&email=diamond0422@qq.com" target="_blank">反馈</a></div><style>.WeMediaUpdateSet a{background: #4DABFF;padding: 5px;color: #fff;}</style>
 * @package WeMedia For Typecho
 * @author 二呆
 * @version 1.0.16<br /><span id="WeMediaUpdateInfo"></span><script>WeMediaXmlHttp=new XMLHttpRequest();WeMediaXmlHttp.open("GET","https://www.tongleer.com/api/interface/WeMedia.php?action=update&version=16",true);WeMediaXmlHttp.send(null);WeMediaXmlHttp.onreadystatechange=function () {if (WeMediaXmlHttp.readyState ==4 && WeMediaXmlHttp.status ==200){document.getElementById("WeMediaUpdateInfo").innerHTML=WeMediaXmlHttp.responseText;}}</script>
 * @link http://www.tongleer.com/
 * @date 2020-03-07
 */
class WeMedia_Plugin implements Typecho_Plugin_Interface{
	/** @var string 提交路由前缀 */
    public static $action = 'manage_wemedia';
    /** @var string 控制菜单链接 */
    public static $panel  = 'WeMedia/templates/manage_wemedia.php';
    // 激活插件
    public static function activate(){
		WeMedia_Plugin::Judge_database();
		Typecho_Plugin::factory('admin/write-post.php')->bottom = array('WeMedia_Plugin', 'tleWeMediaToolbar');
		Typecho_Plugin::factory('Widget_Archive')->header = array('WeMedia_Plugin', 'header');
		Typecho_Plugin::factory('Widget_Abstract_Contents')->contentEx = array('WeMedia_Plugin', 'contentEx');
		Typecho_Plugin::factory('Widget_Abstract_Contents')->excerptEx = array('WeMedia_Plugin', 'excerptEx');
		$db = Typecho_Db::get();
		$prefix = $db->getPrefix();
		$dbconfig=$db->getConfig();
		self::alterColumn($db,$dbconfig[0]->database,$prefix.'contents','wemedia_isFee','enum("y","n") DEFAULT "n"');
		self::alterColumn($db,$dbconfig[0]->database,$prefix.'contents','wemedia_price','double(10,2) DEFAULT 0');
		self::alterColumn($db,$dbconfig[0]->database,$prefix.'users','wemedia_money','double(10,2) DEFAULT 0');
		self::alterColumn($db,$dbconfig[0]->database,$prefix.'users','wemedia_point','int(11) DEFAULT 0');
		self::alterColumn($db,$dbconfig[0]->database,$prefix.'users','wemedia_realname','varchar(32) DEFAULT NULL');
		self::alterColumn($db,$dbconfig[0]->database,$prefix.'users','wemedia_alipay','varchar(32) DEFAULT 0');
		self::alterColumn($db,$dbconfig[0]->database,$prefix.'users','wemedia_isallow','enum("none","allow","refuse","process") DEFAULT "none"');
		self::alterColumn($db,$dbconfig[0]->database,$prefix.'users','wemedia_info','varchar(255) DEFAULT NULL');
		self::createTableWemediaFeeItem($db);
		self::createTableWemediaMoneyItem($db);
		self::createTableWemediaPointCost($db);
		self::createTableWemediaGoods($db);
		self::alterColumn($db,$dbconfig[0]->database,$prefix.'wemedia_fee_item','feecookie','varchar(255) DEFAULT NULL');
		self::alterColumn($db,$dbconfig[0]->database,$prefix.'contents','wemedia_islogin','enum("y","n") DEFAULT "n"');
		self::alterColumn($db,$dbconfig[0]->database,$prefix.'wemedia_fee_item','feeitemtype','varchar(11) DEFAULT NULL COMMENT "保存订单类型：默认空为cookie；mail为邮箱保存。"');
		self::alterColumn($db,$dbconfig[0]->database,$prefix.'wemedia_fee_item','feemail','varchar(64) DEFAULT NULL COMMENT "付款邮箱"');
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
		//创建菜单
		$versions=explode("/",Typecho_Widget::widget('Widget_Options')->Version);
		if($versions[1]>="19.10.15"){
			self::$panel='WeMedia/templates/manage_wemedia2.php';
		}
		if($versions[1]>="19.10.20"){
			self::$panel='WeMedia/templates/manage_wemedia3.php';
		}
		Helper::addPanel(3, self::$panel, '付费阅读', 'WeMedia付费阅读', 'administrator');
		Helper::addAction(self::$action, 'WeMedia_Action');
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
		//删除菜单
		$versions=explode("/",Typecho_Widget::widget('Widget_Options')->Version);
		if($versions[1]>="19.10.15"){
			self::$panel='WeMedia/templates/manage_wemedia2.php';
		}
		if($versions[1]>="19.10.20"){
			self::$panel='WeMedia/templates/manage_wemedia3.php';
		}
		Helper::removeAction(self::$action);
		Helper::removePanel(3, self::$panel);
        return _t('插件已被禁用');
    }
	
	private static function Judge_database(){
        $db= Typecho_Db::get();
        $getAdapterName = $db->getAdapterName();
        if(preg_match('/^M|m?ysql$/',$getAdapterName)){
            return true;
        }else{
            throw new Typecho_Plugin_Exception(_t('对不起，使用了不支持的数据库，无法使用此功能，仅支持mysql数据库。'));
        }
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
			<script src="https://apps.bdimg.com/libs/jquery/1.7.1/jquery.min.js" type="text/javascript"></script>
			<script src="https://cdnjs.cloudflare.com/ajax/libs/amazeui/2.7.2/js/amazeui.min.js" type="text/javascript"></script>
			<h6>基础功能</h6>
			<span><p>第一步：配置下方各项参数；</p></span>
			<span>
				<p>
					第二步：在编写的原创文章中间通过点击编辑器的付费按钮（￥），插入以下代码，中间部分即为付费内容；<br />
					<font color="blue">
						&lt;!--WeMedia start--><br />
						付费内容<br />
						&lt;!--WeMedia end--><br />
					</font>
				</p>
			</span>
			<span><p>第三步：在每位用户的原创文章列表都可以单独指定是否付费，管理员可以在本插件设置页面进行设置，比如下方的文章管理；</p></span>
			<span>
				<p>
					第四步：自动匹配隐藏内容（&lt;?php $this->content; ?>）标签规则。<br />
				</p>
			</span>
			<span><p>第五步：等待其他用户或游客购买对应付费文章；</p></span>
			<h6><font color="red">关于用户中心和积分商城独立页面模块未经测试仅供参考</font></h6>
			<span><p>第六步：有网站用户买家付款后即可查看付费内容，卖家可以到用户中心查看订单；</p></span>
			<span><p>第七步：在用户中心用户可以发表文章、回复评论、设置资料以及提现；</p></span>
			<span><p>第八步：归纳8步，祝每位有缘之人财源广进，另谢谢使用WeMedia插件，更多功能敬请期待……</p></span>
		</small>');
		$div->render();
		//付费文章管理
		$div = new Typecho_Widget_Helper_Layout();
		$divstr1='
			<div style="background-color:#fff;overflow:scroll; height:650px; width:100%; border: solid 0px #aaa; margin: 0 auto;color:#00AAAA;background-color: rgba(0, 0, 0, 0);">
			  <table width="100%" border="1" style="border-color:#ddd;" class="am-table am-table-bordered am-table-striped am-text-nowrap">
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
		$isEnableJQuery = new Typecho_Widget_Helper_Form_Element_Radio('isEnableJQuery', array(
            'y'=>_t('是'),
            'n'=>_t('否')
        ), 'y', _t('是否加载JQuery'), _t("用于解决jquery冲突的问题，如果主题head中自带jquery，需要选择否；如果主题中未加载jquery，则需要选择是。"));
		$form->addInput($isEnableJQuery->addRule('enum', _t(''), array('y', 'n')));
		
		$wemedia_default_price = new Typecho_Widget_Helper_Form_Element_Text('wemedia_default_price', array('value'), 1, _t('默认阅读单价'), _t('为文章设置默认付费金额'));
        $form->addInput($wemedia_default_price);
		
		$wemedia_default_title = new Typecho_Widget_Helper_Form_Element_Text('wemedia_default_title', array('value'), "此处内容已经被作者隐藏，请付费后刷新页面查看内容", _t('默认付款表单标题'), _t('为付款表单设置默认标题'));
        $form->addInput($wemedia_default_title);
		
		$wemedia_itemtype = new Typecho_Widget_Helper_Form_Element_Radio('wemedia_itemtype', array(
            'mail'=>_t('邮箱'),
            ''=>_t('cookie(不推荐,受浏览器cookie等影响.)')
        ), 'mail', _t('以何种方式保存订单'), _t("选择以何种方式保存订单。"));
		$form->addInput($wemedia_itemtype->addRule('enum', _t(''), array('mail', '')));
		
		$wemedia_cookietime = new Typecho_Widget_Helper_Form_Element_Text('wemedia_cookietime', array('value'), 1, _t('免登录Cookie保存时间(天)'), _t('指定使用免登录付费后几天内可以查看隐藏内容，默认为1天。'));
        $form->addInput($wemedia_cookietime);
		
		$wemedia_paytype = new Typecho_Widget_Helper_Form_Element_Radio('wemedia_paytype', array(
            'spay'=>_t('spay'),
            'payjs'=>_t('payjs')
        ), 'payjs', _t('支付渠道'), _t("选择支付渠道，spay和payjs以下配置二选一即可。关于spay支付作者集成后已不再续费，推荐选择payjs支付，payjs是可以直接打款到微信的，比较安全快捷，相比官方微信支付还是可以的。"));
		$form->addInput($wemedia_paytype->addRule('enum', _t(''), array('spay', 'payjs')));
		
		$payjs_wxpay_mchid = new Typecho_Widget_Helper_Form_Element_Text('payjs_wxpay_mchid', array('value'), "", _t('payjs商户号'), _t('在<a href="https://payjs.cn/" target="_blank">payjs官网</a>注册的商户号。'));
        $form->addInput($payjs_wxpay_mchid);
		$payjs_wxpay_key = new Typecho_Widget_Helper_Form_Element_Password('payjs_wxpay_key', array('value'), "", _t('payjs通信密钥'), _t('在<a href="https://payjs.cn/" target="_blank">payjs官网</a>注册的通信密钥。'));
        $form->addInput($payjs_wxpay_key);
		$payjs_wxpay_notify_url = new Typecho_Widget_Helper_Form_Element_Text('payjs_wxpay_notify_url', array('value'), $plug_url.'/WeMedia/notify_url.php', _t('payjs异步回调接口'), _t('支付完成后异步回调的接口地址。'));
        $form->addInput($payjs_wxpay_notify_url);
		$payjs_wxpay_return_url = new Typecho_Widget_Helper_Form_Element_Text('payjs_wxpay_return_url', array('value'), $plug_url.'/WeMedia/return_url.php', _t('payjs同步回调接口'), _t('支付完成后同步回调的接口地址。'));
        $form->addInput($payjs_wxpay_return_url);
		
		$spay_wxpay_id = new Typecho_Widget_Helper_Form_Element_Text('spay_wxpay_id', array('value'), "", _t('SPay微信(QQ)支付合作身份者ID'), _t('SPay网站（主：http://spay.swapteam.cn/；副：http://spay.8889838.com）注册授权微信支付的合作身份者id。'));
        $form->addInput($spay_wxpay_id);
		$spay_wxpay_key = new Typecho_Widget_Helper_Form_Element_Password('spay_wxpay_key', array('value'), "", _t('SPay微信(QQ)支付安全检验码'), _t('SPay网站（主：http://spay.swapteam.cn/；副：http://spay.8889838.com）注册授权微信支付的安全校验码key。'));
        $form->addInput($spay_wxpay_key);
		$spay_alipay_id = new Typecho_Widget_Helper_Form_Element_Text('spay_alipay_id', array('value'), "", _t('SPay支付宝支付合作身份者ID'), _t('SPay网站（主：http://spay.swapteam.cn/；副：http://spay.8889838.com）注册授权支付宝支付的合作身份者id。<font color="red">注：支付宝最低单价为0.8元。</font>'));
        $form->addInput($spay_alipay_id);
		$spay_alipay_key = new Typecho_Widget_Helper_Form_Element_Password('spay_alipay_key', array('value'), "", _t('SPay支付宝支付安全检验码'), _t('SPay网站（主：http://spay.swapteam.cn/；副：http://spay.8889838.com）注册授权支付宝支付的安全校验码key。'));
        $form->addInput($spay_alipay_key);
		$spay_pay_notify_url = new Typecho_Widget_Helper_Form_Element_Text('spay_pay_notify_url', array('value'), $plug_url.'/WeMedia/notify_url.php', _t('SPay异步回调接口'), _t('支付完成后异步回调的接口地址'));
        $form->addInput($spay_pay_notify_url);
		$spay_pay_return_url = new Typecho_Widget_Helper_Form_Element_Text('spay_pay_return_url', array('value'), $plug_url.'/WeMedia/return_url.php', _t('SPay同步回调接口'), _t('支付完成后同步回调的接口地址'));
        $form->addInput($spay_pay_return_url);
		
		$mailsmtp = new Typecho_Widget_Helper_Form_Element_Text('mailsmtp', null, '', _t('smtp服务器'), _t('用于以邮箱保存payjs订单时所需或者用户中心发送邮箱验证码时的smtp服务器地址，QQ企业邮箱：smtp.exmail.qq.com:465；126邮箱：smtp.126.com:25'));
        $form->addInput($mailsmtp);
		$mailport = new Typecho_Widget_Helper_Form_Element_Text('mailport', null, '', _t('smtp服务器端口'), _t('用于以邮箱保存payjs订单时所需或者用户中心发送邮箱验证码时的smtp服务器端口'));
        $form->addInput($mailport);
		$mailuser = new Typecho_Widget_Helper_Form_Element_Text('mailuser', null, '', _t('smtp服务器邮箱用户名'), _t('用于以邮箱保存payjs订单时所需或者用户中心发送邮箱验证码时的smtp服务器邮箱用户名'));
        $form->addInput($mailuser);
		$mailpass = new Typecho_Widget_Helper_Form_Element_Password('mailpass', null, '', _t('smtp服务器邮箱密码'), _t('用于以邮箱保存payjs订单时所需或者用户中心发送邮箱验证码时的smtp服务器邮箱密码'));
        $form->addInput($mailpass);
		$mailsecure = new Typecho_Widget_Helper_Form_Element_Select('mailsecure',array(
            'ssl' => _t('SSL'),
            'tls' => _t('TLS'),
            'none' => _t('无')
        ), "ssl", _t('安全类型'));
		$form->addInput($mailsecure);
		
		$point = new Typecho_Widget_Helper_Form_Element_Text('point', array('value'), 1, _t('积分/评论'), _t('每个有效评论的积分数，默认为1，积分商城用户兑换商品。'));
        $form->addInput($point->addRule('required', _t('需要输入一个积分数')));
		
		$notice = new Typecho_Widget_Helper_Form_Element_Textarea('notice', null, '', _t('公告'), _t('公告可在用户中心显示'));
        $form->addInput($notice);
		
		$wemedia_ad_return = new Typecho_Widget_Helper_Form_Element_Textarea('wemedia_ad_return', array("value"), '广告位', _t('手机端同步回调页广告位'), _t('手机端同步回调页广告位广告代码'));
        $form->addInput($wemedia_ad_return);
    }

    // 个人用户配置面板
    public static function personalConfig(Typecho_Widget_Helper_Form $form){
    }

    // 获得插件配置信息
    public static function getConfig(){
        return Typecho_Widget::widget('Widget_Options')->plugin('WeMedia');
    }
	
	/*修改数据表字段*/
	public static function alterColumn($db,$dbname,$table,$column,$define){
		$prefix = $db->getPrefix();
		$query= "select * from information_schema.columns WHERE TABLE_SCHEMA='".$dbname."' and table_name = '".$table."' AND column_name = '".$column."'";
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
		require_once dirname(__FILE__).'/libs/PHPMailer/PHPMailerAutoload.php';
		$phpMailer = new PHPMailer();
		$options = Typecho_Widget::widget('Widget_Options');
		$option=$options->plugin('WeMedia');
		$phpMailer->isSMTP();
		$phpMailer->SMTPAuth = true;
		$phpMailer->Host = $option->mailsmtp;
		$phpMailer->Port = $option->mailport;
		$phpMailer->Username = $option->mailuser;
		$phpMailer->Password = $option->mailpass;
		$phpMailer->isHTML(true);
		if ('none' != $option->mailsecure) {
			$phpMailer->SMTPSecure = $option->mailsecure;
		}
		$phpMailer->setFrom($option->mailuser, $title);
		$phpMailer->addAddress($email, $email);
		$phpMailer->Subject = $title;
		$phpMailer->Body    = $content;
		if(!$phpMailer->send()) {
			return false;
		} else {
			return true;
		}
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
	
	public static function str_replace_once_for_wemedia($needle, $replace, $haystack) {
    $pos = strpos($haystack, $needle);
    if ($pos === false) {
        return $haystack;
    }
    return substr_replace($haystack, $replace, $pos, strlen($needle));
}
	
	/**
     * 自动输出摘要
     * @access public
     * @return void
     */
    public static function excerptEx($html, $widget, $lastResult){
		$wechatfansRule='/<!--wechatfans start-->([\s\S]*?)<!--wechatfans end-->/i';
		preg_match_all($wechatfansRule, $html, $hide_words);
		if(!$hide_words[0]){
			$wechatfansRule='/&lt;!--wechatfans start--&gt;([\s\S]*?)&lt;!--wechatfans end--&gt;/i';
		}
		$WeMediaRule='/<!--WeMedia start-->([\s\S]*?)<!--WeMedia end-->/i';
		preg_match_all($WeMediaRule, $html, $hide_words);
		if(!$hide_words[0]){
			$WeMediaRule='/&lt;!--WeMedia start--&gt;([\s\S]*?)&lt;!--WeMedia end--&gt;/i';
		}
		$html=trim($html);
		if (preg_match_all($wechatfansRule, $html, $hide_words)){
			$html = str_replace($hide_words[0], '', $html);
		}
		if (preg_match_all($WeMediaRule, $html, $hide_words)){
			$html = str_replace($hide_words[0], '', $html);
		}
		$WeMedia2Rule='/\[WeMedia\]([\s\S]*?)\[\/WeMedia\]/i';
		if (preg_match_all($WeMedia2Rule, $html, $hide_words)){
			$html = str_replace($hide_words[0], '', $html);
		}
		$html=Typecho_Common::subStr(strip_tags($html), 0, 140, "...");
		return $html;
	}
	
	/**
     * 自动输出内容
     * @access public
     * @return void
     */
    public static function contentEx($html, $widget, $lastResult){
		if(!isset($_SESSION)){session_start();}
		if (preg_match_all('/<!--WeMedia start-->([\s\S]*?)<!--WeMedia end-->/i', $html, $matches)){
			$hide_content=$matches;
		}else if(preg_match_all('/&lt;!--WeMedia start--&gt;([\s\S]*?)&lt;!--WeMedia end--&gt;/i', $html, $matches)){
			$hide_content=$matches;
		}else if(preg_match_all('/\[WeMedia\]([\s\S]*?)\[\/WeMedia\]/i', $html, $matches)){
			$hide_content=$matches;
		}else{
			$hide_content="";
		}
		$html = empty( $lastResult ) ? $html : $lastResult;
		$db = Typecho_Db::get();
		$options = Typecho_Widget::widget('Widget_Options');
		$option=$options->plugin('WeMedia');
		$plug_url = $options->pluginUrl;
		$html=trim($html);
		$query= $db->select()->from('table.contents')->where('cid = ?', $widget->cid); 
		$row = $db->fetchRow($query);
		$wemedia_price=$row['wemedia_price']?$row['wemedia_price']:($option->wemedia_default_price?$option->wemedia_default_price:0);
		if ($hide_content&&$wemedia_price){
			if($option->wemedia_paytype=="spay"){
				$wemedia_paytype='
					<option value="wx">微信支付</option>
					<option value="alipay">支付宝支付</option>
					<!--
					<option value="qqpay">QQ钱包支付</option>
					<option value="bank_pc">网银支付</option>
					-->
				';
			}else if($option->wemedia_paytype=="payjs"){
				$wemedia_paytype='
					<option value="wx">微信支付</option>
				';
			}
			if($row['wemedia_isFee']=='y'&&$row['authorId']!=Typecho_Cookie::get('__typecho_uid')){
				if($row["wemedia_islogin"]=="n"){
					$isPay=false;
					if($option->wemedia_itemtype==""){
						if(!isset($_COOKIE["TypechoReadyPayCookie"])){
							$cookietime=$option->wemedia_cookietime==""?1:$option->wemedia_cookietime;
							$randomCode=self::randomCode(10,1)[1];
							setcookie("TypechoReadyPayCookie",$randomCode, time()+3600*24*$cookietime);
							$TypechoReadyPayCookie=$randomCode;
						}else{
							$TypechoReadyPayCookie=$_COOKIE["TypechoReadyPayCookie"];
						}
						$queryItem= $db->select()->from('table.wemedia_fee_item')->where('feecookie = ?', $TypechoReadyPayCookie)->where('feestatus = ?', 1)->where('feecid = ?', $widget->cid); 
						$rowItem = $db->fetchRow($queryItem);
						if($rowItem){
							$isPay=true;
						}
					}else if($option->wemedia_itemtype=="mail"){
						$TypechoReadyPayMail = isset($_GET['TypechoReadyPayMail']) ? addslashes(trim($_GET['TypechoReadyPayMail'])) : '';
						if($TypechoReadyPayMail&&$TypechoReadyPayMail==$_SESSION["new".$options->title]){
							$queryItem= $db->select()->from('table.wemedia_fee_item')->where('feemail = ?', $TypechoReadyPayMail)->where('feestatus = ?', 1)->where('feecid = ?', $widget->cid); 
							$rowItem = $db->fetchRow($queryItem);
							if($rowItem){
								$isPay=true;
							}
						}
					}
					$queryUser= $db->select()->from('table.users')->where('uid = ?', $row['authorId']); 
					$rowUser = $db->fetchRow($queryUser);
					$wemedia_info=$rowUser["wemedia_info"]==''?'':'作者简介：'.$rowUser["wemedia_info"];
					if(!$isPay){
						foreach ($hide_content[0] as $k => $m) {
							if ($k == 0) {
								$hide_notice='
								<div class="wemedia-box wemedia-center">
									<!--<div class="wemedia-mask"></div>-->
									<div class="wemedia-lock"><span class="icon-lock-m"></span></div>
									<div style="border:1px dashed #F60; padding:10px; margin:10px 0; line-height:200%; color:#F00; background-color:#FFF4FF; overflow:hidden; clear:both;">
										<form id="wemediaPayPost" method="post" style="margin:10px 0;" action="" target="_blank">
											<span class="yzts" style="font-size:18px;float:left;"></span>
											<span style="font-size:18px;width:30%; height:32px; line-height:30px; padding:0 5px;">'.($option->wemedia_default_title?$option->wemedia_default_title:"此处内容已经被作者隐藏，请付费后刷新页面查看内容").'</span>
											<div style="clear:left;"></div>
											<select id="feetype" name="feetype" style="width:30%; height:32px; line-height:30px; padding:0 5px; border:1px solid #FF6600;-moz-border-radius: 0px;  -webkit-border-radius: 0px;  border-radius:0px;">
												'.$wemedia_paytype.'
											</select>
											<div style="clear:left;"></div>
											'.($option->wemedia_itemtype==""?'':'<input style="border:none;width:30%; height:32px; line-height:30px;border:0.1px solid #FF6600;-moz-border-radius: 0px;  -webkit-border-radius: 0px;  border-radius:0px;" type="email" id="feemail" name="feemail" placeholder="输入个人邮箱" /><div style="clear:left;"></div>
											<input style="width:30%;height:32px; line-height:30px;border:0.1px solid #FF6600;-moz-border-radius: 0px;  -webkit-border-radius: 0px;  border-radius:0px;" type="text" id="feemailcode" name="feemailcode" placeholder="邮箱验证码" />
											<div style="clear:left;"></div>
											<input id="btnSendCode" style="width:30%; height:32px; line-height:30px; padding:0 5px; background-color:#00BF30; border:none; cursor:pointer; color:#FFF;-moz-border-radius: 0px; font-size:14px;  -webkit-border-radius: 0px;  border-radius:0px;" name="" type="button" value="发送" />
											<div style="clear:left;"></div>').'
											<input id="verifybtn" style="width:30%; height:32px; line-height:30px; padding:0 5px; background-color:#F60; text-align:center; border:none; cursor:pointer; color:#FFF;-moz-border-radius: 0px; font-size:14px;  -webkit-border-radius: 0px;  border-radius:0px;" name="" type="submit" value="付款('.$wemedia_price.')元" />
											<input type="hidden" name="action" value="paysubmit" />
											<input type="hidden" id="feecid" name="feecid" value="'.urlencode($widget->cid).'" />
											<input type="hidden" id="feepermalink" name="feepermalink" value="'.$widget->permalink.'" />
											<input type="hidden" id="feecookie" name="feecookie" value="'.@$TypechoReadyPayCookie.'" />
										</form>
										<div style="clear:left;"></div>
										'.($option->wemedia_itemtype==""?'<span style="color:#00BF30">点击付款支付后'.$option->wemedia_cookietime.'天内即可阅读隐藏内容。</span>':'<a style="color:#00BF30" id="wemediaPayQuery" href="javascript:;" onClick="return false;">已付款？点击查看(可能会有几秒延迟)</a>').'
										<div class="cl"></div>
										<span style="color:#00BF30">'.$wemedia_info.'</span>
										<span id="wemedia_islogin" style="display:none;">y</span>
										<div style="display:none;" id="wemedia_itemtype">'.$option->wemedia_itemtype.'</div>
									</div>
								</div>
								';
							}else{
								$hide_notice='
									<div class="wemedia-box wemedia-center">
										<div style="border:1px dashed #F60; padding:10px; margin:10px 0; line-height:200%; color:#F00; background-color:#FFF4FF; overflow:hidden; clear:both;">
											<span style="font-size:18px;">'.($option->wemedia_default_title?$option->wemedia_default_title:"此处内容已经被作者隐藏，请付费后刷新页面查看内容").'</span>
										</div>
									</div>
								';
							}
							$html = WeMedia_Plugin::str_replace_once_for_wemedia($m, $hide_notice, $html);
							//$html = str_replace($hide_content[0], $hide_notice, $html);
						}
					}else{
						/*
						$html = str_replace($hide_content[0], '<div style="border:1px dashed #F60; padding:10px; margin:10px 0; line-height:200%;  background-color:#FFF4FF; overflow:hidden; clear:both;">'.$hide_content[1][0].'</div>', $html);
						$html = str_replace("&lt;","<", $html);
						$html = str_replace("&gt;",">", $html);
						*/
						$html = preg_replace('/<!--WeMedia start-->([\s\S]*?)<!--WeMedia end-->/i', '<div style="border:1px dashed #F60; padding:10px; margin:10px 0; line-height:200%;  background-color:#FFF4FF; overflow:hidden; clear:both;">'.($wemedia_price?'<font color="red">本部分为付费内容，您已获得阅读权限</font><br />':'').'$1</div>', $html);
						$html = preg_replace('/&lt;!--WeMedia start--&gt;([\s\S]*?)&lt;!--WeMedia end--&gt;/i', '<div style="border:1px dashed #F60; padding:10px; margin:10px 0; line-height:200%;  background-color:#FFF4FF; overflow:hidden; clear:both;">'.($wemedia_price?'<font color="red">本部分为付费内容，您已获得阅读权限</font><br />':'').'$1</div>', $html);
						$html = preg_replace('/\[WeMedia\]([\s\S]*?)\[\/WeMedia\]/i', '<div style="border:1px dashed #F60; padding:10px; margin:10px 0; line-height:200%;  background-color:#FFF4FF; overflow:hidden; clear:both;">'.($wemedia_price?'<font color="red">本部分为付费内容，您已获得阅读权限</font><br />':'').'$1</div>', $html);
					}
				}else if($row["wemedia_islogin"]=="y"){
					$queryItem= $db->select()->from('table.wemedia_fee_item')->where('feecid = ?', $widget->cid)->where('feeuid = ?', Typecho_Cookie::get('__typecho_uid'))->where('feestatus = ?', 1); 
					$rowItem = $db->fetchRow($queryItem);
					$queryUser= $db->select()->from('table.users')->where('uid = ?', $row['authorId']); 
					$rowUser = $db->fetchRow($queryUser);
					$wemedia_info=$rowUser["wemedia_info"]==''?'':'作者简介：'.$rowUser["wemedia_info"];
					if(Typecho_Cookie::get('__typecho_uid')==0||count($rowItem)==0){
						foreach ($hide_content[0] as $k => $m) {
							if ($k == 0) {
								$hide_notice='
								<div style="border:1px dashed #F60; padding:10px; margin:10px 0; line-height:200%; color:#F00; background-color:#FFF4FF; overflow:hidden; clear:both;">
									<span style="font-size:18px;">'.($option->wemedia_default_title?$option->wemedia_default_title:"此处内容已经被作者隐藏，请付费后刷新页面查看内容").'</span>
									<form id="wemediaPayPost" method="post" style="margin:10px 0;" action="" target="_blank">
										<span class="yzts" style="font-size:18px;float:left;">方式：</span>
										<select id="feetype" name="feetype" style="border:none;float:left;width:160px; height:32px; line-height:30px; padding:0 5px; border:1px solid #FF6600;-moz-border-radius: 0px;  -webkit-border-radius: 0px;  border-radius:0px;">
											'.$wemedia_paytype.'
										</select>
										<div style="clear:left;"></div>
										<span class="yzts" style="font-size:18px;float:left;">价格：</span>
										<div style="border:none;float:left;width:80px; height:32px; line-height:30px; padding:0 5px; border:1px solid #FF6600;-moz-border-radius: 0px;  -webkit-border-radius: 0px;  border-radius:0px;">'.$wemedia_price.'</div>
										<input id="verifybtn" style="border:none;float:left;width:68px; height:34px; line-height:32px; padding:0 5px; background-color:#F60; text-align:center; border:none; cursor:pointer; color:#FFF;-moz-border-radius: 0px; font-size:14px;  -webkit-border-radius: 0px;  border-radius:0px;" name="" type="submit" value="付款" />
										<input type="hidden" name="action" value="paysubmit" />
										<input type="hidden" id="feecid" name="feecid" value="'.urlencode($widget->cid).'" />
										<input type="hidden" id="feepermalink" name="feepermalink" value="'.$widget->permalink.'" />
										<input type="hidden" id="feeuid" name="feeuid" value="'.Typecho_Cookie::get('__typecho_uid').'" />
									</form>
									<div style="clear:left;"></div>
									<span style="color:#00BF30">登陆后点击付款支付后即可阅读隐藏内容。</span><div class="cl"></div>
									<span style="color:#00BF30">'.$wemedia_info.'</span>
									<span id="wemedia_islogin" style="display:none;">y</span>
								</div>
								';
								//$html = str_replace($hide_content[0], $hide_notice, $html);
							}else{
								$hide_notice='
									<div class="wemedia-box wemedia-center">
										<div style="border:1px dashed #F60; padding:10px; margin:10px 0; line-height:200%; color:#F00; background-color:#FFF4FF; overflow:hidden; clear:both;">
											<span style="font-size:18px;">'.($option->wemedia_default_title?$option->wemedia_default_title:"此处内容已经被作者隐藏，请付费后刷新页面查看内容").'</span>
										</div>
									</div>
								';
							}
							$html = WeMedia_Plugin::str_replace_once_for_wemedia($m, $hide_notice, $html);
						}
					}else{
						$html = preg_replace('/<!--WeMedia start-->([\s\S]*?)<!--WeMedia end-->/i', '<div style="border:1px dashed #F60; padding:10px; margin:10px 0; line-height:200%;  background-color:#FFF4FF; overflow:hidden; clear:both;">'.($wemedia_price?'<font color="red">本部分为付费内容，您已获得阅读权限</font><br />':'').'$1</div>', $html);
						$html = preg_replace('/&lt;!--WeMedia start--&gt;([\s\S]*?)&lt;!--WeMedia end--&gt;/i', '<div style="border:1px dashed #F60; padding:10px; margin:10px 0; line-height:200%;  background-color:#FFF4FF; overflow:hidden; clear:both;">'.($wemedia_price?'<font color="red">本部分为付费内容，您已获得阅读权限</font><br />':'').'$1</div>', $html);
						$html = preg_replace('/\[WeMedia\]([\s\S]*?)\[\/WeMedia\]/i', '<div style="border:1px dashed #F60; padding:10px; margin:10px 0; line-height:200%;  background-color:#FFF4FF; overflow:hidden; clear:both;">'.($wemedia_price?'<font color="red">本部分为付费内容，您已获得阅读权限</font><br />':'').'$1</div>', $html);
					}
				}
				$html .= self::script();
			}else{
				$html = preg_replace('/<!--WeMedia start-->([\s\S]*?)<!--WeMedia end-->/i', '<div style="border:1px dashed #F60; padding:10px; margin:10px 0; line-height:200%;  background-color:#FFF4FF; overflow:hidden; clear:both;">'.($wemedia_price?'<font color="red">本部分为付费内容，您已获得阅读权限</font><br />':'').'$1</div>', $html);
				$html = preg_replace('/&lt;!--WeMedia start--&gt;([\s\S]*?)&lt;!--WeMedia end--&gt;/i', '<div style="border:1px dashed #F60; padding:10px; margin:10px 0; line-height:200%;  background-color:#FFF4FF; overflow:hidden; clear:both;">'.($wemedia_price?'<font color="red">本部分为付费内容，您已获得阅读权限</font><br />':'').'$1</div>', $html);
				$html = preg_replace('/\[WeMedia\]([\s\S]*?)\[\/WeMedia\]/i', '<div style="border:1px dashed #F60; padding:10px; margin:10px 0; line-height:200%;  background-color:#FFF4FF; overflow:hidden; clear:both;">'.($wemedia_price?'<font color="red">本部分为付费内容，您已获得阅读权限</font><br />':'').'$1</div>', $html);
			}
		}
		return $html;
	}
	
	public static function header(){
		$options = Typecho_Widget::widget('Widget_Options');
		$option=$options->plugin('WeMedia');
		$plug_url = $options->pluginUrl;
		echo '<link rel="stylesheet" href="'.$plug_url.'/WeMedia/css/wemedia.min.css"/>';
		if($option->isEnableJQuery=="y"){
			echo '<script src="https://libs.baidu.com/jquery/1.11.1/jquery.min.js"></script>';
		}
		echo '<script src="https://cdnjs.cloudflare.com/ajax/libs/layer/2.3/layer.js"></script>';
		echo '<script type="text/javascript" src="'.$plug_url.'/WeMedia/js/jquery.cookie.js"></script>';
	}
	
	public static function script(){
		$options = Typecho_Widget::widget('Widget_Options');
		$plug_url = $options->pluginUrl;
		$option=$options->plugin('WeMedia');
		return "
			<script>
				".($option->wemedia_itemtype=="mail"?"
					$(\"#feemail\").focus();
					$(\"#wemediaPayQuery\").click(function(){
						if($(\"#feemail\").val()==\"\"){
							layer.msg(\"必须要输入个人邮箱\");
							return;
						}
						if($(\"#feemailcode\").val()==\"\"){
							layer.msg(\"邮箱验证码不能为空\");
							return false;
						}
						$.ajax({
							type : \"POST\",
							url : \"".$plug_url."/WeMedia/pay.php\",
							data : {action:\"wemediaPayQuery\",feemail:$(\"#feemail\").val(),feemailcode:$(\"#feemailcode\").val(),feecid:$(\"#feecid\").val()},
							dataType : \"text\",
							success : function(data) {
								var data=JSON.parse(data);
								if(data.status==\"ok\"){
									location.href=$(\"#feepermalink\").val()+($(\"#feepermalink\").val().indexOf(\"?\")!=-1?\"&\":\"?\")+\"TypechoReadyPayMail=\"+$(\"#feemail\").val();
								}else{
									layer.msg(data.msg);
								}
							}
						});
					});
					if($.cookie(\"mailCodeCookie\")){
						var count=$.cookie(\"mailCodeCookie\");
						$(\"#btnSendCode\").attr(\"disabled\",true);
						$(\"#btnSendCode\").val(count+\"秒\");
						var resend = setInterval(function(){
							count--;
							if (count > 0){
								$(\"#btnSendCode\").val(count+\"秒\");
								$.cookie(\"mailCodeCookie\", count, {path: \"/\", expires: (1/86400)*count});
							}else {
								$(\"#btnSendCode\").attr(\"disabled\", false);
								clearInterval(resend);
								$(\"#btnSendCode\").val(\"发送\");
							}
						},1000);
					}
					$(\"#btnSendCode\").click(function(){
						if($(\"#feemail\").val()==\"\"){
							layer.msg(\"必须要输入个人邮箱\");
							return false;
						}
						if($(\"#btnSendCode\").val()!=\"发送\"){
							return;
						}
						$(\"#btnSendCode\").val(\"发送中...\");
						$.post(\"".$plug_url."/WeMedia/pay.php\",{action:\"sendMailCode\",feemail:$(\"#feemail\").val()},function(data){
							var data=JSON.parse(data);
							if(data.code==0){
								layer.msg(data.msg);
								var count = 60;
								var inl = setInterval(function () {
									$(\"#btnSendCode\").attr(\"disabled\", true);
									count -= 1;
									var text = count + \" 秒\";
									$.cookie(\"mailCodeCookie\", count, {path: \"/\", expires: (1/86400)*count}); 
									$(\"#btnSendCode\").val(text);
									if (count <= 0) {
										clearInterval(inl);
										$(\"#btnSendCode\").attr(\"disabled\", false); 
										$(\"#btnSendCode\").val(\"发送\");
									}
								}, 1000);
							}else{
								layer.msg(data.msg);
								$(\"#btnSendCode\").val(\"发送\");
							}
						});
					});
				":"")."
				$(\"#wemediaPayPost\").submit(function(){
					if($(\"#wemedia_islogin\").html()==\"y\"&&$(\"#feeuid\").val()==\"\"){
						layer.msg(\"需要先登录\");
						return false;
					}
					if($(\"#wemedia_itemtype\").text()==\"mail\"){
						if($(\"#feemail\").val()==\"\"){
							layer.msg(\"必须要输入个人邮箱\");
							return false;
						}
						if($(\"#feemailcode\").val()==\"\"){
							layer.msg(\"邮箱验证码不能为空\");
							return false;
						}
					}
					var str = \"确认要付款购买吗？\";
					layer.confirm(str, {
						btn: [\"付款\",\"算了\"]
					}, function(){
						var ii = layer.load(2, {shade:[0.1,\"#fff\"]});
						var wemedia_payjstype=\"native\";
						if(isWemediaWeiXin()){
							wemedia_payjstype=\"cashier\";
						}
						$.ajax({
							type : \"POST\",
							url : \"".$plug_url."/WeMedia/pay.php\",
							data : {\"action\":\"paysubmit\",\"wemedia_payjstype\":wemedia_payjstype,\"feetype\":$(\"#feetype\").val(),\"feepermalink\":$(\"#feepermalink\").val(),\"feecid\":$(\"#feecid\").val(),\"feeuid\":$(\"#feeuid\").val(),\"feecookie\":$(\"#feecookie\").val(),\"feemail\":$(\"#feemail\").val(),\"feemailcode\":$(\"#feemailcode\").val()},
							dataType : \"json\",
							success : function(data) {
								layer.close(ii);
								if(data.status==\"ok\"){
									if(data.type==\"spay\"){
										if(data.channel==\"wx\"){
											str=\"<center><div>支持微信付款</div><div><img src='https://www.tongleer.com/api/web/?action=qrcode&url='\"+data.qrcode+\" width='200' /></div><div><a href=\"+data.qrcode+\" target='_blank'>跳转支付链接</a></div></center>\";
										}else if(data.channel==\"alipay\"){
											str=\"<center><div>支持支付宝付款</div><div><a href=\"+data.qrcode+\" target='_blank'>跳转支付链接</a></div></center>\";
										}
									}else if(data.type==\"native\"){
										str=\"<center><div>支持微信付款</div><div><img src='\"+data.qrcode+\"' width='200' /></div></center>\";
									}else if(data.type==\"cashier\"){
										open(\"".$plug_url."/WeMedia/pay.php?wemedia_payjstype=\"+wemedia_payjstype+\"&feetype=\"+$('#feetype').val()+\"&feepermalink=\"+$('#feepermalink').val()+\"&feecid=\"+$('#feecid').val()+\"&feeuid=\"+$('#feeuid').val()+\"&feecookie=\"+$('#feecookie').val()+\"&feemail=\"+$('#feemail').val()+\"&feemailcode=\"+$('#feemailcode').val());
										return false;
									}
									layer.confirm(str, {
										btn: [\"已付款\",\"算了\"]
									},function(index){
										location.href=$(\"#feepermalink\").val()+($(\"#feepermalink\").val().indexOf(\"?\")!=-1?\"&\":\"?\")+\"TypechoReadyPayMail=\"+$(\"#feemail\").val();
										layer.close(index);
									});
								}else{
									layer.msg(data.msg);
								}
							},error:function(data){
								layer.close(ii);
								layer.msg(\"服务器错误\");
								return false;
							}
						});
					});
					return false;
				});
				function isWemediaWeiXin(){
					var ua = window.navigator.userAgent.toLowerCase();
					if(ua.match(/MicroMessenger/i) == \"micromessenger\"){
						return true;
					}else{
						return false;
					}
				}
			</script>
		";
	}
}