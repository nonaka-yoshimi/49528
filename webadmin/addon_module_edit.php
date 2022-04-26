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
for($i = 0;$i < Config::ADDON_MODULE_NUM;$i++){
	$no = $i + 1;
	$config_addon_module_active[$i] = isset($_REQUEST["config_addon_module".$no."_active"]) ? $_REQUEST["config_addon_module".$no."_active"] : "";	//拡張機能利用
	$config_addon_module_name[$i] = isset($_REQUEST["config_addon_module".$no."_name"]) ? $_REQUEST["config_addon_module".$no."_name"] : "";		//拡張機能名称
	$config_addon_module_path[$i] = isset($_REQUEST["config_addon_module".$no."_path"]) ? $_REQUEST["config_addon_module".$no."_path"] : "";		//拡張機能URLパス
	$config_addon_module_code[$i] = isset($_REQUEST["config_addon_module".$no."_code"]) ? $_REQUEST["config_addon_module".$no."_code"] : "";		//拡張機能コード
	$config_addon_module_description[$i] = isset($_REQUEST["config_addon_module".$no."_description"]) ? $_REQUEST["config_addon_module".$no."_description"] : "";		//拡張機能説明文
}

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
	for($i = 0;$i < Config::ADDON_MODULE_NUM;$i++){
		$no = $i + 1;

		if(!$Config->set("addon_module".$no."_active", $config_addon_module_active[$i])){
			DB::rollBack();
			Logger::error("拡張機能".$no."利用設定更新に失敗しました。");
			Location::redirect($redirect);
		}
		if(!$Config->set("addon_module".$no."_name", $config_addon_module_name[$i])){
			DB::rollBack();
			Logger::error("拡張機能".$i."名称設定更新に失敗しました。");
			Location::redirect($redirect);
		}
		if(!$Config->set("addon_module".$no."_path", $config_addon_module_path[$i])){
			DB::rollBack();
			Logger::error("拡張機能".$no."URL設定更新に失敗しました。");
			Location::redirect($redirect);
		}
		if(!$Config->set("addon_module".$no."_code", $config_addon_module_code[$i])){
			DB::rollBack();
			Logger::error("拡張機能".$no."コード更新に失敗しました。");
			Location::redirect($redirect);
		}
		if(!$Config->set("addon_module".$no."_description", $config_addon_module_description[$i])){
			DB::rollBack();
			Logger::error("拡張機能".$no."説明文更新に失敗しました。");
			Location::redirect($redirect);
		}
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
			for($i = 0;$i < Config::ADDON_MODULE_NUM;$i++){
			$no = $i + 1;
			$config_addon_module_active[$i] = Config::get("addon_module".$no."_active");
			$config_addon_module_name[$i] = Config::get("addon_module".$no."_name");
			$config_addon_module_path[$i] = Config::get("addon_module".$no."_path");
			$config_addon_module_code[$i] = Config::get("addon_module".$no."_code");
			$config_addon_module_description[$i] = Config::get("addon_module".$no."_description");
		}
	}
}

$LayoutManager = new LayoutManagerAdminMain();
$LayoutManager->setTitle("拡張機能利用設定編集");
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
<?php for($i = 0;$i < Config::ADDON_MODULE_NUM;$i++):$no = $i + 1;?>
	<tr>
    <th class="w240">拡張機能<?php echo $no;?>利用<span class="mark orange">必須</span></th>
    <td>
    <?php if($config_addon_module_active[$i] == "1"){ $checked0 = ""; $checked1 = "checked"; } else { $checked0 = "checked"; $checked1 = ""; } ?>
    <input type="radio" name="config_addon_module<?php echo $no;?>_active" value="0" <?php echo $checked0; ?> />無効&nbsp;
    <input type="radio" name="config_addon_module<?php echo $no;?>_active" value="1" <?php echo $checked1; ?> />有効
    </td>
    </tr>

    <tr>
    <th>拡張機能<?php echo $no;?>名称</th>
    <td>
    <input type="text" name="config_addon_module<?php echo $no;?>_name" value="<?php echo htmlspecialchars($config_addon_module_name[$i]);?>" size="70" />
    </td>
    </tr>

    <tr>
    <th>拡張機能<?php echo $no;?>URL</th>
    <td>
    <input type="text" name="config_addon_module<?php echo $no;?>_path" value="<?php echo htmlspecialchars($config_addon_module_path[$i]);?>" size="70" />
    </td>
    </tr>

    <tr>
    <th>拡張機能<?php echo $no;?>コード</th>
    <td>
    <input type="text" name="config_addon_module<?php echo $no;?>_code" value="<?php echo htmlspecialchars($config_addon_module_code[$i]);?>" size="70" />
    </td>
    </tr>

    <tr>
    <th>拡張機能<?php echo $no;?>説明文</th>
    <td>
    <input type="text" name="config_addon_module<?php echo $no;?>_description" value="<?php echo htmlspecialchars($config_addon_module_description[$i]);?>" size="70" />
    </td>
    </tr>
<?php endfor;?>
</table>
</form>
<?php $LayoutManager->footer(); ?>
