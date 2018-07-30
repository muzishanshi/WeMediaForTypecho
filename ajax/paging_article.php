<?php
date_default_timezone_set('Asia/Shanghai');
include '../../../../config.inc.php';
$db = Typecho_Db::get();
$prefix = $db->getPrefix();

$searchtext = isset($_POST['searchtext']) ? addslashes($_POST['searchtext']) : '';
$page_now = isset($_POST['page_now']) ? addslashes($_POST['page_now']) : 1;
if($page_now<1){
	$page_now=1;
}
if($searchtext==''){
	$queryTotal= $db->select()->from('table.contents')->join('table.users', 'table.contents.authorId = table.users.uid',Typecho_Db::INNER_JOIN)->where('type = ?','post')->where('status = ?','publish')->order('table.contents.created',Typecho_Db::SORT_DESC);
}else{
	$queryTotal= $db->select()->from('table.contents')->join('table.users', 'table.contents.authorId = table.users.uid',Typecho_Db::INNER_JOIN)->where('type = ?','post')->where('status = ?','publish')->where('title like ? ', '%'.$searchtext.'%')->order('table.contents.created',Typecho_Db::SORT_DESC);
}
$resultTotal = $db->fetchAll($queryTotal);
$page_rec=1;
$totalrec=count($resultTotal);
$page=ceil($totalrec/$page_rec);

$arr['totalItem'] = $totalrec;
$arr['pageSize'] = $page_rec;
$arr['totalPage'] = $page;

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
if($searchtext==''){
	$query= $db->select()->from('table.contents')->join('table.users', 'table.contents.authorId = table.users.uid',Typecho_Db::INNER_JOIN)->where('type = ?','post')->where('status = ?','publish')->order('table.contents.created',Typecho_Db::SORT_DESC)->offset($i)->limit($page_rec);
}else{
	$query= $db->select()->from('table.contents')->join('table.users', 'table.contents.authorId = table.users.uid',Typecho_Db::INNER_JOIN)->where('type = ?','post')->where('status = ?','publish')->where('title like ? ', '%'.$searchtext.'%')->order('table.contents.created',Typecho_Db::SORT_DESC)->offset($i)->limit($page_rec);
}
$result = $db->fetchAll($query);
$divstr='';
foreach($result as $value){
	$querySort= $db->select()->from('table.metas')->join('table.relationships', 'table.metas.mid = table.relationships.mid',Typecho_Db::INNER_JOIN)->where('cid = ?', $value['cid']); 
	$rowSort = $db->fetchRow($querySort);
	$queryFeeItem= $db->select()->from('table.wemedia_fee_item')->where('feecid = ?', $value['cid'])->where('feeuid = ?', $value['uid'])->where('feestatus = ?', 1); 
	$rowFeeItem = $db->fetchAll($queryFeeItem);
	
	$arr['data_content']['commentsNum'] = $value['commentsNum'];
	$arr['data_content']['feeItemCount'] = count($rowFeeItem);
	$arr['data_content']['title'] = $value['title'];
	$arr['data_content']['author'] = $value['screenName']==""?$value['name']:$value['screenName'];
	$arr['data_content']['sortName'] = $rowSort['name'];
	$arr['data_content']['created'] = date('Y-m-d H:i:s',$value['created']);
	
	$arr['data_content']['wemedia_isFee'] = $value['wemedia_isFee'];
	$arr['data_content']['cid'] = $value['cid'];
	$arr['data_content']['wemedia_price'] = $value['wemedia_price'];
}
echo json_encode($arr);
?>