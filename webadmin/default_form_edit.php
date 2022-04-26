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
$mode = isset($_REQUEST["mode"]) ? $_REQUEST["mode"] : "edit";																													//起動モード
$action = isset($_REQUEST["action"]) ? $_REQUEST["action"] : "";																												//実行アクション
$close = isset($_REQUEST["close"]) ? $_REQUEST["close"] : "";																													//閉じるフラグ
$close_url = isset($_REQUEST["close_url"]) ? $_REQUEST["close_url"] : "config_manager.php";																						//戻り先URL
$message = isset($_REQUEST["message"]) ? $_REQUEST["message"] : $message;																										//メッセージ
$config_form_admin_mail_to = isset($_REQUEST["config_form_admin_mail_to"]) ? Util::encodeRequest($_REQUEST["config_form_admin_mail_to"]) : "";									//管理者向け通知メール宛先アドレス
$config_form_admin_mail_from = isset($_REQUEST["config_form_admin_mail_from"]) ? Util::encodeRequest($_REQUEST["config_form_admin_mail_from"]) : "";							//管理者向け通知メール送信元アドレス
$config_form_admin_mail_from_name = isset($_REQUEST["config_form_admin_mail_from_name"]) ? Util::encodeRequest($_REQUEST["config_form_admin_mail_from_name"]) : "";				//管理者向け通知メール送信者名
$config_form_admin_mail_subject = isset($_REQUEST["config_form_admin_mail_subject"]) ? Util::encodeRequest($_REQUEST["config_form_admin_mail_subject"]) : "";					//管理者向け通知メール題名
$config_form_admin_mail_template = isset($_REQUEST["config_form_admin_mail_template"]) ? Util::encodeRequest($_REQUEST["config_form_admin_mail_template"]) : "";				//管理者向け通知メールテンプレート
$config_form_thanks_mail_from = isset($_REQUEST["config_form_thanks_mail_from"]) ? Util::encodeRequest($_REQUEST["config_form_thanks_mail_from"]) : "";							//サンクスメール送信元アドレス
$config_form_thanks_mail_from_name = isset($_REQUEST["config_form_thanks_mail_from_name"]) ? Util::encodeRequest($_REQUEST["config_form_thanks_mail_from_name"]) : "";			//サンクスメール送信者名
$config_form_thanks_mail_subject = isset($_REQUEST["config_form_thanks_mail_subject"]) ? Util::encodeRequest($_REQUEST["config_form_thanks_mail_subject"]) : "";				//サンクスメール題名
$config_form_thanks_mail_template = isset($_REQUEST["config_form_thanks_mail_template"]) ? Util::encodeRequest($_REQUEST["config_form_thanks_mail_template"]) : "";				//サンクスメールテンプレート

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
	if(!$Config->set("form_admin_mail_to", $config_form_admin_mail_to)){
		DB::rollBack();
		Logger::error("管理者向け通知メール宛先アドレス設定更新に失敗しました。");
		Location::redirect($redirect);
	}
	if(!$Config->set("form_admin_mail_from", $config_form_admin_mail_from)){
		DB::rollBack();
		Logger::error("管理者向け通知メール送信元アドレス設定更新に失敗しました。");
		Location::redirect($redirect);
	}
	if(!$Config->set("form_admin_mail_from_name", $config_form_admin_mail_from_name)){
		DB::rollBack();
		Logger::error("管理者向け通知メール送信者名設定更新に失敗しました。");
		Location::redirect($redirect);
	}
	if(!$Config->set("form_admin_mail_subject", $config_form_admin_mail_subject)){
		DB::rollBack();
		Logger::error("管理者向け通知メール題名設定更新に失敗しました。");
		Location::redirect($redirect);
	}
	if(!$Config->set("form_admin_mail_template", $config_form_admin_mail_template)){
		DB::rollBack();
		Logger::error("管理者向け通知メールテンプレートID設定更新に失敗しました。");
		Location::redirect($redirect);
	}
	if(!$Config->set("form_thanks_mail_from", $config_form_thanks_mail_from)){
		DB::rollBack();
		Logger::error("サンクスメール送信元アドレス設定更新に失敗しました。");
		Location::redirect($redirect);
	}
	if(!$Config->set("form_thanks_mail_from_name", $config_form_thanks_mail_from_name)){
		DB::rollBack();
		Logger::error("サンクスメール送信者名設定更新に失敗しました。");
		Location::redirect($redirect);
	}
	if(!$Config->set("form_thanks_mail_subject", $config_form_thanks_mail_subject)){
		DB::rollBack();
		Logger::error("サンクスメール題名設定更新に失敗しました。");
		Location::redirect($redirect);
	}
	if(!$Config->set("form_thanks_mail_template", $config_form_thanks_mail_template)){
		DB::rollBack();
		Logger::error("サンクスメールテンプレートID設定更新に失敗しました。");
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
		$config_form_admin_mail_to = Config::get("form_admin_mail_to");
		$config_form_admin_mail_from = Config::get("form_admin_mail_from");
		$config_form_admin_mail_from_name = Config::get("form_admin_mail_from_name");
		$config_form_admin_mail_subject = Config::get("form_admin_mail_subject");
		$config_form_admin_mail_template = Config::get("form_admin_mail_template");
		$config_form_thanks_mail_from = Config::get("form_thanks_mail_from");
		$config_form_thanks_mail_from_name = Config::get("form_thanks_mail_from_name");
		$config_form_thanks_mail_subject = Config::get("form_thanks_mail_subject");
		$config_form_thanks_mail_template = Config::get("form_thanks_mail_template");
	}
}

$LayoutManager = new LayoutManagerAdminMain();
$LayoutManager->setTitle("お問い合わせフォーム設定");
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
    <th class="w240">管理者向け通知メール宛先アドレス</th>
    <td>
    <input type="text" name="config_form_admin_mail_to" value="<?php echo htmlspecialchars($config_form_admin_mail_to);?>" size="100" />
    </td>
    </tr>

    <tr>
    <th class="w240">管理者向け通知メール送信元アドレス</th>
    <td>
    <input type="text" name="config_form_admin_mail_from" value="<?php echo htmlspecialchars($config_form_admin_mail_from);?>" size="100" />
    </td>
    </tr>

    <tr>
    <th class="w240">管理者向け通知メール送信者名</th>
    <td>
    <input type="text" name="config_form_admin_mail_from_name" value="<?php echo htmlspecialchars($config_form_admin_mail_from_name);?>" size="100" />
    </td>
    </tr>

    <tr>
    <th class="w240">管理者向け通知メール題名</th>
    <td>
    <input type="text" name="config_form_admin_mail_subject" value="<?php echo htmlspecialchars($config_form_admin_mail_subject);?>" size="100" />
    </td>
    </tr>

    <tr>
    <th class="w240">管理者向け通知メールテンプレート</th>
    <td>
    <textarea style="width:100%; height:500px;" name="config_form_admin_mail_template"><?php echo htmlspecialchars($config_form_admin_mail_template)?></textarea>
    </td>
    </tr>

    <tr>
    <th class="w240">サンクスメール送信元アドレス</th>
    <td>
    <input type="text" name="config_form_thanks_mail_from" value="<?php echo htmlspecialchars($config_form_thanks_mail_from);?>" size="100" />
    </td>
    </tr>

    <tr>
    <th class="w240">サンクスメール送信者名</th>
    <td>
    <input type="text" name="config_form_thanks_mail_from_name" value="<?php echo htmlspecialchars($config_form_thanks_mail_from_name);?>" size="100" />
    </td>
    </tr>

    <tr>
    <th class="w240">サンクスメール題名</th>
    <td>
    <input type="text" name="config_form_thanks_mail_subject" value="<?php echo htmlspecialchars($config_form_thanks_mail_subject);?>" size="100" />
    </td>
    </tr>

    <tr>
    <th class="w240">サンクスメールテンプレート</th>
    <td>
    <textarea style="width:100%; height:500px;" name="config_form_thanks_mail_template"><?php echo htmlspecialchars($config_form_thanks_mail_template)?></textarea>
    </td>
    </tr>

</table>
</form>
<?php $LayoutManager->footer(); ?>
