<?php
// アプリケーション共通クラス読込
require_once("/webadmin/ApplicationCommon/include.php");

// セッションを取得する
$session = Session::get();

//エラー・メッセージ格納用配列
$error = array();
$message = array();

//REQUESTの取得
$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : "";							//実行アクション
$login_id = isset($_POST['login_id']) ? $_POST['login_id'] : "";							//ログインID
$password = isset($_POST['password']) ? $_POST['password'] : "";							//パスワード
$redirect = isset($_REQUEST['redirect']) ? $_REQUEST['redirect'] : "";						//成功時リダイレクト先
$redirect_error = isset($_REQUEST['redirect_error']) ? $_REQUEST['redirect_error'] : "";	//エラー時リダイレクト先

//言語ライブラリ取得
$lib = new Resources();

//ログイン処理の実行
if($action == 'login'){
	//空欄チェック
	if($login_id == ''){
		$error[] = $lib->get("LOGIN_ID_EMPTY");
	}
	if($password == ''){
		$error[] = $lib->get("PASSWORD_EMPTY");
	}

	//ログイン処理
	if($error == array()){
		$result = $session->login($login_id,$password);

		if($result != '1'){
			$error[] = $lib->get("IDPASSWORD_ERROR");
		}else{
			//成功：業務画面にリダイレクト
			Location::redirect($redirect);
		}
	}
	Location::redirect($redirect_error);
}elseif($action == 'logout'){
	//セッションログアウト
	$session->logout();
	//リダイレクト処理
	Location::redirect($redirect);
}