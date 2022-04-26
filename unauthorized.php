<?php
require_once(dirname(__FILE__)."/webadmin/CMSEngine/CMSEngine.php");
require_once(dirname(__FILE__)."/webadmin/ApplicationCommon/Logger.php");
require_once(dirname(__FILE__)."/webadmin/Config/Config.php");
header("HTTP/1.0 401 Unauthorized");
$_GET["url"] = Config::get("unauthorized_url");
if($_GET["url"]){
	include("rewrite.php");
	exit;
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html>
<head >
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<title>401 Unauthorized</title>
<meta name="author" content="" />
<meta name="description" content="" />
<meta name="keywords" content="" />
</head>
<body style="text-align:center;">
<br /><br /><br /><br /><br /><br /><br /><br /><br />
<h2>ページの表示には、ユーザの認証が必要です。</h2>
<h3>(Unauthorized)</h3>
</body>