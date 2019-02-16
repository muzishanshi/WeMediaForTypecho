<?php
//版本检测
$version = isset($_POST['version']) ? addslashes($_POST['version']) : '';
$version=file_get_contents('https://www.tongleer.com/api/interface/WeMedia.php?action=update&version='.$version);
echo $version;
?>