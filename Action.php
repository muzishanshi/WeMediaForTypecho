<?php
/**
 * WeMedia_Action
 * 自媒体付费阅读行为
 *
 */
class WeMedia_Action extends Typecho_Widget implements Widget_Interface_Do
{
    /** @var  数据操作对象 */
    private $_db;

    /** @var  插件配置信息 */
    private $_cfg;
    
    /** @var  系统配置信息 */
    private $_options;

    /**
     * 初始化
     * @return $this
     */
    public function init(){
        $this->_db = Typecho_Db::get();
        $this->_options = $this->widget('Widget_Options');
        $this->_cfg = Helper::options()->plugin('WeMedia');
    }

    /**
     * action 入口
     *
     * @access public
     * @return void
     */
    public function action(){
		$this->on($this->request->is('do=delItem'))->delItem();
    }
	/**
     * 删除订单方法
     */
    public function delItem(){
        $this->init();
		
		$feeid = isset($_GET['feeid']) ? addslashes(trim($_GET['feeid'])) : 0;
		if($feeid){
			$delete = $this->_db->delete('table.wemedia_fee_item')->where('feeid = ?', $feeid);
			$deletedRows = $this->_db->query($delete);
		}
		
		$result = true;
		/** 提示信息 */
		$this->widget('Widget_Notice')->set(true === $result ? _t('删除成功') : _t('删除失败'), true === $result ? 'success' : 'notice');
		
        /** 转向原页 */
        $this->response->goBack();
    }
}