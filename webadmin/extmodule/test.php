<?php
require_once(dirname(__FILE__)."/../ApplicationCommon/include.php");
echo "拡張モジュールからの出力 SESSION：<br>";
print_r($_POST["session"]);

echo "<br><br>拡張モジュールからの出力 REQUEST：<br>";
print_r($_POST["request"]);

echo "<br><br>拡張モジュールからの出力 POST：<br>";
print_r($_POST["post"]);

echo "<br><br>拡張モジュールからの出力 GET：<br>";
print_r($_POST["get"]);

echo "<br><br>拡張モジュールからの出力 FILES：<br>";
print_r($_POST["files"]);

echo "<br><br>拡張モジュールからの出力 PARAM：<br>";
print_r($_POST["param"]);

print_r($data);
?>