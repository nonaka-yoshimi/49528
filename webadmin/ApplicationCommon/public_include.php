<?php

//フレームワーク
require_once(dirname(__FILE__).'/../Framework/include.php');

//設定クラス
require_once(dirname(__FILE__).'/../Config/Config.php');

//デバッグクラス
require_once(dirname(__FILE__).'/Debug.php');

//定数定義クラス
require_once(dirname(__FILE__).'/SPConst.php');

//初期化処理クラス
require_once(dirname(__FILE__).'/Init.php');

//汎用DBクラス
require_once(dirname(__FILE__).'/DB.php');

//ユーティリティクラス
require_once(dirname(__FILE__).'/Util.php');

//メール送信処理クラス
require_once(dirname(__FILE__).'/Mail.php');

//ページャ管理クラス
require_once(dirname(__FILE__).'/Pager.php');

//業務共通処理クラス
require_once(dirname(__FILE__).'/Common.php');

?>