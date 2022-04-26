<?php
// アプリケーション共通クラス読込
require_once("ApplicationCommon/include.php");

// セッションを取得する
$session = Session::get();

//エラー・メッセージ格納用配列
$error = array();
$message = array();

//REQUESTの取得
$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : "";			//実行アクション
$login_id = isset($_POST['login_id']) ? $_POST['login_id'] : "";			//ログインID
$password = isset($_POST['password']) ? $_POST['password'] : "";			//パスワード
$msg = isset($_REQUEST['msg']) ? $_REQUEST['msg'] : "";						//メッセージ
$redirect = isset($_REQUEST['redirect']) ? $_REQUEST['redirect'] : "";		//リダイレクト先

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
			//リダイレクト設定
			if(Util::IsNullOrEmpty($redirect)){
				$filename = "index.php";
			}else{
				$filename = base64_decode($redirect);
			}
			//成功：業務画面にリダイレクト
			Location::redirect($filename);
		}
	}
}

//ログアウト処理の実行
if($action == 'logout'){
	//セッションログアウト
	$session->logout();

	//リダイレクト処理
	Location::redirect("login.php",array("msg" => "logout"));
}

//メッセージ作成処理
if($msg != ""){
	if($msg == 'logout'){
		$message[] = $lib->get("DO_LOGOUT");
	}elseif($msg == 'session_error'){
		$message[] = $lib->get("SESSION_TIMEOUT");
	}
}

$LayoutManager = new LayoutManagerAdminMain();
$LayoutManager->setTitle("ログイン画面");
$LayoutManager->header_login();
?>

<?php if($error != array()): //エラーの表示 ?>
	<ul class='error'>
	<?php foreach($error as $alert_one): ?>
	<?php echo '<li>'.$alert_one.'</li>'; ?>
	<?php endforeach; ?>
	</ul>
<?php endif; ?>
<?php if($message != array()): //メッセージの表示  ?>
	<ul class='message'>
	<?php foreach($message as $alert_one): ?>
		<?php echo '<li>'.$alert_one.'</li>'; ?>
	<?php endforeach; ?>
	</ul>
<?php endif; ?>

<form action="login.php?redirect=" method="post">
<input type="hidden" name="action" value="login" />
<input type="hidden" name="redirect"  value="<?php echo $redirect; ?>" />
<table class="edit" cellspacing="0">
	<tr>
		<th class="w240">ログインＩＤ</th>
		<td><input type="text" name="login_id" /><br />
		<span class="info">半角英数字5文字以上16文字まで</span></td>
	</tr>
	<tr>
		<th>パスワード</th>
		<td><input type="password" name="password" /><br />
		<span class="info">半角英数字5文字以上16文字まで</span></td>
	</tr>
</table>
<input class="margin_f btn w240" type="submit" value="ログイン" /><input class="margin_s btn red" type="reset" value="リセット" />
</form>




<?php $LayoutManager->footer_login(); ?>