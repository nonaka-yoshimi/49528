<?php

//フレームワーク
require_once(dirname(__FILE__).'/../Framework/include.php');

//設定クラス
require_once(dirname(__FILE__).'/../Config/Config.php');

//文字リソースクラス
require_once(dirname(__FILE__).'/Resources.php');

//メッセージ(言語)クラス
//require_once(dirname(__FILE__).'/Message.php');

//ワード(言語)クラス
//require_once(dirname(__FILE__).'/Word.php');

//メニュー設定クラス
require_once(dirname(__FILE__).'/MenuConfig.php');

//デバッグクラス
require_once(dirname(__FILE__).'/Debug.php');

//文字ソースクラス
//require_once(dirname(__FILE__).'/WordLibrary.jp.php');

//定数定義クラス
require_once(dirname(__FILE__).'/SPConst.php');

//選択肢クラス
require_once(dirname(__FILE__).'/Options.php');

//セッション管理クラス
require_once(dirname(__FILE__).'/Session.php');

//ユーティリティクラス
require_once(dirname(__FILE__).'/Util.php');

//メール送信処理クラス
require_once(dirname(__FILE__).'/Mail.php');

//エディタクラス
require_once(dirname(__FILE__).'/Editor.php');

//ファイルアップロードマネージャクラス
//require_once(dirname(__FILE__).'/FileUploadManager.php');

//CSVダウンロードマネージャクラス
require_once(dirname(__FILE__).'/CSVDownLoadManager.php');

//管理者画面用レイアウト管理クラス
require_once(dirname(__FILE__).'/LayoutManagerAdminMain.php');

//子画面用レイアウト管理クラス
require_once(dirname(__FILE__).'/LayoutManagerChild.php');

//設定画面用レイアウト管理クラス
require_once(dirname(__FILE__).'/LayoutManagerSetting.php');

//ページャ管理クラス
require_once(dirname(__FILE__).'/Pager.php');

//業務共通処理クラス
//require_once(dirname(__FILE__).'/Common.php');

//ログ出力クラス
require_once(dirname(__FILE__).'/Logger.php');

//ロケーションクラス
require_once(dirname(__FILE__).'/Location.php');

//ユーザインターフェース部品クラス
require_once(dirname(__FILE__).'/UIParts.php');

//DB接続
require_once(dirname(__FILE__).'/dbconnect.php');

?>