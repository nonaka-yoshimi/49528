<?php
// アプリケーション共通クラス読込
require_once(dirname(__FILE__).'/../ApplicationCommon/include.php');

$testfile = $_POST["files"]["testfile"];

Debug::arrayCheck($testfile);

echo $testfile["tmp_name"];
if (file_exists($testfile["tmp_name"])) {
	if (copy($testfile["tmp_name"], "../../filetest/" . $testfile["name"])) {
		echo $testfile["name"] . "をアップロードしました。";
	} else {
		echo "ファイルをアップロードできません。";
	}
}else{
	echo "一時ファイルがありません。";
}


?>