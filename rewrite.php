<?php
//ini_set('display_errors', "On");
require_once(dirname(__FILE__)."/webadmin/CMSEngine/CMSEngine.php");
require_once(dirname(__FILE__)."/webadmin/ApplicationCommon/Logger.php");
require_once(dirname(__FILE__)."/webadmin/Config/Config.php");

global $rewrite_count;
if(!$rewrite_count){ $rewrite_count = 1; }else{ $rewrite_count = $rewrite_count+1; }

$url = isset($_GET["url"]) ? $_GET["url"] : "";

//ドメイン設定
$domain = Config::DEFAULT_DOMAIN;
$CMS = new CMSEngine($domain);

//出力処理
$CMS->output();

//print_r($_REQUEST);
?>