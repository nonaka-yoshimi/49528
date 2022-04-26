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
$editor_enable = isset($_REQUEST["editor_enable"]) ? $_REQUEST["editor_enable"] : "";											//エディタ有効/無効
$editor_default = isset($_REQUEST["editor_default"]) ? $_REQUEST["editor_default"] : "";										//エディタデフォルト設定
$editor_custom_script = isset($_REQUEST["editor_custom_script"]) ? $_REQUEST["editor_custom_script"] : "";						//カスタムスクリプト
$editor_custom_css = isset($_REQUEST["editor_custom_css"]) ? $_REQUEST["editor_custom_css"] : "";								//カスタムCSS

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
	if(!$Config->set("editor_enable", $editor_enable)){
		DB::rollBack();
		Logger::error("エディタ有効/無効更新に失敗しました。");
		Location::redirect($redirect);
	}
	if(!$Config->set("editor_default", $editor_default)){
		DB::rollBack();
		Logger::error("エディタデフォルト設定更新に失敗しました。");
		Location::redirect($redirect);
	}
	if(!$Config->set("editor_custom_script", $editor_custom_script)){
		DB::rollBack();
		Logger::error("エディタカスタムスクリプト更新に失敗しました。");
		Location::redirect($redirect);
	}
	if(!$Config->set("editor_custom_script", $editor_custom_script)){
		DB::rollBack();
		Logger::error("エディタカスタムスクリプト更新に失敗しました。");
		Location::redirect($redirect);
	}
	if(!$Config->set("editor_custom_css", $editor_custom_css)){
		DB::rollBack();
		Logger::error("エディタカスタムCSS更新に失敗しました。");
		Location::redirect($redirect);
	}
	//カスタムCSS書出し処理
	if($editor_custom_css){
		$str = $editor_custom_css;
		$fp = fopen(dirname(__FILE__)."/ckeditor_css/editor_custom.css", "w");
		fwrite($fp, $str);
		fclose($fp);
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
		$editor_enable = Config::get("editor_enable");
		$editor_default = Config::get("editor_default");
		$editor_custom_script = Config::get("editor_custom_script");
		$editor_custom_css = Config::get("editor_custom_css");
	}
}

$LayoutManager = new LayoutManagerAdminMain();
$LayoutManager->setTitle("エディタカスタマイズ設定編集");
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
    <th class="w240">エディタ有効/無効<span class="mark orange">必須</span></th>
    <td>
    <?php if($editor_enable == "" || $editor_enable == "0"){ $checked = "checked"; }else{ $checked = ""; } ?>
    <input type="radio" name="editor_enable" value="0" <?php echo $checked; ?> />無効&nbsp;
    <?php if($editor_enable == "1"){ $checked = "checked"; }else{ $checked = ""; } ?>
    <input type="radio" name="editor_enable" value="1" <?php echo $checked; ?> />有効<br />
    </td>
    </tr>
    <tr>
    <th class="w240">エディタデフォルト設定<span class="mark orange">必須</span></th>
    <td>
    <?php if($editor_default == "" || $editor_default == "1"){ $checked = "checked"; }else{ $checked = ""; } ?>
    <input type="radio" name="editor_default" value="1" <?php echo $checked; ?> />テキストエリア&nbsp;
    <?php if($editor_default == "2"){ $checked = "checked"; }else{ $checked = ""; } ?>
    <input type="radio" name="editor_default" value="2" <?php echo $checked; ?> />Webエディタ<br />
    </td>
    </tr>
</table>

<h3>カスタマイズスクリプト設定</h3>
<textarea style="width:100%; height:150px;" name="editor_custom_script"><?php echo htmlspecialchars($editor_custom_script)?></textarea>

<h3>カスタマイズCSS設定</h3>
<textarea style="width:100%; height:150px;" name="editor_custom_css"><?php echo htmlspecialchars($editor_custom_css)?></textarea>

</form>
<?php $LayoutManager->footer(); ?>
