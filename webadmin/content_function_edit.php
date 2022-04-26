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
$mode = isset($_REQUEST["mode"]) ? $_REQUEST["mode"] : "edit";																	//起動モード
$action = isset($_REQUEST["action"]) ? $_REQUEST["action"] : "";																//実行アクション
$close = isset($_REQUEST["close"]) ? $_REQUEST["close"] : "";																	//閉じるフラグ
$close_url = isset($_REQUEST["close_url"]) ? $_REQUEST["close_url"] : "config_manager.php";										//戻り先URL
$message = isset($_REQUEST["message"]) ? $_REQUEST["message"] : $message;														//メッセージ
$config_content_new_mode = isset($_REQUEST["config_content_new_mode"]) ? $_REQUEST["config_content_new_mode"] : "";				//コンテンツ新規作成モード
$config_show_deleted_archive = isset($_REQUEST["config_show_deleted_archive"]) ? $_REQUEST["config_show_deleted_archive"] : "";	//削除済みアーカイブ表示
$config_static_stylesheet = isset($_REQUEST["config_static_stylesheet"]) ? $_REQUEST["config_static_stylesheet"] : "";			//静的スタイルシート配信
$config_static_script = isset($_REQUEST["config_static_script"]) ? $_REQUEST["config_static_script"] : "";						//静的スクリプト配信

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
	if(!$Config->set("content_new_mode", $config_content_new_mode)){
		DB::rollBack();
		Logger::error("コンテンツ新規作成モード更新に失敗しました。");
		Location::redirect($redirect);
	}
	//設定更新実行
	if(!$Config->set("show_deleted_archive", $config_show_deleted_archive)){
		DB::rollBack();
		Logger::error("削除済みコンテンツ表示設定更新に失敗しました。");
		Location::redirect($redirect);
	}
	//設定更新実行
	if(!$Config->set("static_stylesheet", $config_static_stylesheet)){
		DB::rollBack();
		Logger::error("スタイルシート静的配信機能設定更新に失敗しました。");
		Location::redirect($redirect);
	}
	//設定更新実行
	if(!$Config->set("static_script", $config_static_script)){
		DB::rollBack();
		Logger::error("スクリプト静的配信機能設定更新に失敗しました。");
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
		$config_content_new_mode = Config::get("content_new_mode");
		$config_show_deleted_archive = Config::get("show_deleted_archive");
		$config_static_stylesheet = Config::get("static_stylesheet");
		$config_static_script = Config::get("static_script");
	}
}

$LayoutManager = new LayoutManagerAdminMain();
$LayoutManager->setTitle("コンテンツ管理機能設定編集");
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
<table class="edit" cellspacing="0">
	<tr>
    <th class="w320">コンテンツ新規作成モード<span class="mark orange">必須</span></th>
    <td>
    <?php if($config_content_new_mode == "normal"){ $checked = "checked"; }else{ $checked = ""; } ?>
    <input type="radio" name="config_content_new_mode" value="normal" <?php echo $checked; ?> />新規追加のみ&nbsp;
    <?php if($config_content_new_mode == "copy_only"){ $checked = "checked"; }else{ $checked = ""; } ?>
    <input type="radio" name="config_content_new_mode" value="copy_only" <?php echo $checked; ?> />コピーして追加のみ&nbsp;
	<?php if($config_content_new_mode == "multi"){ $checked = "checked"; }else{ $checked = ""; } ?>
    <input type="radio" name="config_content_new_mode" value="multi" <?php echo $checked; ?> />新規追加＆コピー&nbsp;
    </td>
    </tr>
    <tr>
    <th>削除済みコンテンツ復元機能<span class="mark orange">必須</span></th>
    <td>
    <?php if($config_show_deleted_archive == "off"){ $checked = "checked"; }else{ $checked = ""; } ?>
    <input type="radio" name="config_show_deleted_archive" value="off" <?php echo $checked; ?> />利用しない&nbsp;
    <?php if($config_show_deleted_archive == "on"){ $checked = "checked"; }else{ $checked = ""; } ?>
    <input type="radio" name="config_show_deleted_archive" value="on" <?php echo $checked; ?> />利用する&nbsp;
    </td>
    </tr>
    <tr>
    <th>スタイルシート静的配信機能<span class="mark orange">必須</span></th>
    <td>
    <?php if($config_static_stylesheet == "off"){ $checked = "checked"; }else{ $checked = ""; } ?>
    <input type="radio" name="config_static_stylesheet" value="off" <?php echo $checked; ?> />利用しない(DB利用)&nbsp;
    <?php if($config_static_stylesheet == "on"){ $checked = "checked"; }else{ $checked = ""; } ?>
    <input type="radio" name="config_static_stylesheet" value="on" <?php echo $checked; ?> />利用する(静的ファイルを出力する)&nbsp;
    </td>
    </tr>
    <tr>
    <th>スクリプト静的配信機能<span class="mark orange">必須</span></th>
    <td>
    <?php if($config_static_script == "off"){ $checked = "checked"; }else{ $checked = ""; } ?>
    <input type="radio" name="config_static_script" value="off" <?php echo $checked; ?> />利用しない(DB利用)&nbsp;
    <?php if($config_static_script == "on"){ $checked = "checked"; }else{ $checked = ""; } ?>
    <input type="radio" name="config_static_script" value="on" <?php echo $checked; ?> />利用する(静的ファイルを出力する)&nbsp;
    </td>
    </tr>
</table>
</form>
<?php $LayoutManager->footer(); ?>
