<?php
// アプリケーション共通クラス読込
require_once(dirname(__FILE__).'/ApplicationCommon/include.php');

//基本設定
$self = basename(__FILE__);				//自身のファイル名
$error = array();						//エラー情報格納用配列
$message = array();						//メッセージ情報格納用配列
$alert = array();						//アラート情報格納用配列
$redirect = "index.php";				//エラー時リダイレクト先
$restrict = array();					//表示・操作制限

//セッション取得
$session = Session::get();

//セッション認証
$session->loginCheckAndRedirect("login.php",array("admin_flg" => 1));

//リクエストパラメータ取得
$mode = isset($_REQUEST["mode"]) ? $_REQUEST["mode"] : "edit";																					//起動モード
$action = isset($_REQUEST["action"]) ? $_REQUEST["action"] : "";																				//実行アクション
$close = isset($_REQUEST["close"]) ? $_REQUEST["close"] : "";																					//閉じるフラグ
$close_url = isset($_REQUEST["close_url"]) ? $_REQUEST["close_url"] : "config_manager.php";														//戻り先URL
$message = isset($_REQUEST["message"]) ? $_REQUEST["message"] : $message;																		//メッセージ
$config_notfound_url = isset($_REQUEST["config_notfound_url"]) ? Util::encodeRequest($_REQUEST["config_notfound_url"]) : "";					//NotFoundURL
$config_forbidden_url = isset($_REQUEST["config_forbidden_url"]) ? Util::encodeRequest($_REQUEST["config_forbidden_url"]) : "";					//ForbiddenURL
$config_unauthorized_url = isset($_REQUEST["config_unauthorized_url"]) ? Util::encodeRequest($_REQUEST["config_unauthorized_url"]) : "";		//UnauthorizedURL

//起動モードが設定されていない場合
if(Util::IsNullOrEmpty($mode)){
	Logger::notice("起動モードが設定されていませんでした。");
	Location::redirect($redirect);
}

//設定クラス
$Config = new Config();

//入力値チェック
if(($mode == "new" || $mode == "edit") && $action == "save"){

}

//保存処理
if(($mode == "new" || $mode == "edit") && $action == "save" && $error == array()){
	$now_timestamp = time();

	DB::beginTransaction();
	//設定更新実行
	if(!$Config->set("notfound_url", $config_notfound_url)){
		DB::rollBack();
		Logger::error("NotFoundページ設定更新に失敗しました。");
		Location::redirect($redirect);
	}
	if(!$Config->set("forbidden_url", $config_forbidden_url)){
		DB::rollBack();
		Logger::error("Forbiddenページ設定更新に失敗しました。");
		Location::redirect($redirect);
	}
	if(!$Config->set("unauthorized_url", $config_unauthorized_url)){
		DB::rollBack();
		Logger::error("Unauthorizedページ設定更新に失敗しました。");
		Location::redirect($redirect);
	}

	DB::commit();

	if($close == "on"){
		//一覧画面に遷移する
		Location::redirect($close_url);
	}else{
		//同画面に遷移する
		$redirectParam["close_url"] = $close_url;
		$redirectParam["mode"] = "edit";
		$redirectParam["message[]"] = "保存しました。";
		Location::redirect($self,$redirectParam);
	}
}

//初期表示
if($action == ""){
	if($mode == "edit"){
		//設定データ取得
		$config_notfound_url = Config::get("notfound_url");
		$config_forbidden_url = Config::get("forbidden_url");
		$config_unauthorized_url = Config::get("unauthorized_url");
	}
}

$LayoutManager = new LayoutManagerAdminMain();
$LayoutManager->setTitle("特殊ページ編集");
$LayoutManager->setErrorList($error);
$LayoutManager->setMessageList($message);
$LayoutManager->setAlertList($alert);
$LayoutManager->header();

?>
<script>
$(function(){
	//保存するボタン設定
	$("#action_save").click(function(){
		$("*[name=action]").val('save');
		$('#values').attr({
		       'action':'<?php echo $self; ?>',
		       'method':'post'
		     });
		$('#values').submit();
	});

	//保存して閉じるボタン設定
	$("#action_save_close").click(function(){
		$("*[name=action]").val('save');
		$("*[name=close]").val('on');
		$('#values').attr({
		       'action':'<?php echo $self; ?>',
		       'method':'post'
		     });
		$('#values').submit();
	});

	//一覧に戻るボタン設定
	$("#back_to_list").click(function(){
		$('#values').attr({
		       'action':'<?php echo $close_url; ?>',
		       'method':'post'
		     });
		$('#values').submit();
	});

});
</script>

<div class="search">
<input class="btn btn_small" type="button" value="一覧に戻る" id="back_to_list" />
<input class="btn btn_small" type="button" value="保存する" id="action_save"  />
<input class="btn btn_small" type="button" value="保存して閉じる" id="action_save_close"  />
</div>

<!-- メッセージ出力開始 -->
<?php echo $LayoutManager->alert(); 	//アラート出力 ?>
<?php echo $LayoutManager->error(); 	//エラー出力 ?>
<?php echo $LayoutManager->message(); 	//メッセージ出力 ?>
<!-- メッセージ出力終了 -->


<form action="/" method="post" id="values" enctype="multipart/form-data">
<input type="hidden" name="mode" value="<?php echo htmlspecialchars($mode); //動作モード ?>" />
<input type="hidden" name="close_url" value="<?php echo htmlspecialchars($close_url); //戻り先URL ?>" />
<input type="hidden" name="action" value="<?php //javascriptから設定 ?>" />
<input type="hidden" name="close" value="<?php //javascriptから設定 ?>" />
<table class="edit"  cellspacing="0">

    <tr>
    <th class="w240">NotFoundページURL</th>
    <td>
    <input type="text" name="config_notfound_url" value="<?php echo htmlspecialchars($config_notfound_url);?>" />
    </td>
    </tr>

    <tr>
    <th valign="w240">ForbiddenページURL</th>
    <td>
    <input type="text" name="config_forbidden_url" value="<?php echo htmlspecialchars($config_forbidden_url);?>" />
    </td>
    </tr>

    <tr>
    <th valign="w240">UnauthorizedページURL</th>
    <td>
    <input type="text" name="config_unauthorized_url" value="<?php echo htmlspecialchars($config_unauthorized_url);?>" />
    </td>
    </tr>

</table>
</form>
<?php $LayoutManager->footer(); ?>
