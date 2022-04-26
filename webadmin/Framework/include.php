<?php

//フレームワーク設定クラス
require_once(dirname(__FILE__).'/FrameworkConfig.php');

//データアクセス用ベースクラス
require_once(dirname(__FILE__).'/DataAccessBase.php');

//セッション管理用ベースクラス
require_once(dirname(__FILE__).'/SessionBase.php');

//選択肢ベースクラス
require_once(dirname(__FILE__).'/OptionsBase.php');

//メール送信ベースクラス
require_once(dirname(__FILE__).'/MailBase.php');

//エディタベースクラス
require_once(dirname(__FILE__).'/EditorBase.php');

//ファイルアップロードマネージャベースクラス
require_once(dirname(__FILE__).'/FileUploadManagerBase.php');

//ユーティリティベースクラス
require_once(dirname(__FILE__).'/UtilBase.php');

//ログ取得クラス
require_once(dirname(__FILE__).'/LoggerBase.php');

?>