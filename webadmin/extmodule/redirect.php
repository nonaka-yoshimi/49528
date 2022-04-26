<?php
// アプリケーション共通クラス読込
require_once(dirname(__FILE__).'/../ApplicationCommon/include.php');

$redirect_url = isset($_POST["param"]) ? $_POST["param"] : "";
echo "[SPREDIRECT::/".Config::BASE_DIR_PATH.ltrim($redirect_url,"/")."::SPEND]";
?>