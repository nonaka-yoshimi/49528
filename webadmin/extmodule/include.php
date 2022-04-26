<?php
require_once("../ApplicationCommon/include.php");
/**
 * extmodule用の共通インクルードファイル
 */

$tmp = $_POST;
$_SESSION = $tmp["session"];
$_REQUEST = $tmp["request"];
$_POST = $tmp["post"];
$_GET = $tmp["get"];
$_FILES = $tmp["files"];
$_PARAM = $tmp["param"];
$_META = $tmp["meta"];
unset($tmp);



?>