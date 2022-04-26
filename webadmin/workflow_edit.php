<?php
// アプリケーション共通クラス読込
require_once(dirname(__FILE__).'/ApplicationCommon/include.php');

require_once(dirname(__FILE__).'/DataAccess/WorkFlow.php'); 		//ワークフローアクションクラス
require_once(dirname(__FILE__).'/DataAccess/WorkFlowState.php'); 	//ワークフロー状態クラス
require_once(dirname(__FILE__).'/DataAccess/Folder.php'); 			//フォルダクラス
require_once(dirname(__FILE__).'/DataAccess/UserGroup.php'); 		//ユーザグループクラス

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
$mode = isset($_REQUEST["mode"]) ? $_REQUEST["mode"] : "";																			//起動モード
$action = isset($_REQUEST["action"]) ? $_REQUEST["action"] : "";																	//実行アクション
$close = isset($_REQUEST["close"]) ? $_REQUEST["close"] : "";																		//閉じるフラグ
$close_url = isset($_REQUEST["close_url"]) ? $_REQUEST["close_url"] : "";															//戻り先URL
$message = isset($_REQUEST["message"]) ? $_REQUEST["message"] : $message;															//メッセージ
$workflow_id = isset($_REQUEST["workflow_id"]) ? Util::encodeRequest($_REQUEST["workflow_id"]) : "";								//ワークフローアクションID
$workflow_name = isset($_REQUEST["workflow_name"]) ? Util::encodeRequest($_REQUEST["workflow_name"]) : "";							//ワークフローアクション名
$workflowstate_from_id = isset($_REQUEST["workflowstate_from_id"]) ? Util::encodeRequest($_REQUEST["workflowstate_from_id"]) : "";	//ワークフロー状態FROM
$workflowstate_to_id = isset($_REQUEST["workflowstate_to_id"]) ? Util::encodeRequest($_REQUEST["workflowstate_to_id"]) : "";		//ワークフロー状態TO
$changes = isset($_REQUEST["changes"]) ? Util::encodeRequest($_REQUEST["changes"]) : array();										//ワークフロー実行時の自動アクション
$contentclass = isset($_REQUEST["contentclass"]) ? Util::encodeRequest($_REQUEST["contentclass"]) : array();						//実行可能コンテンツ
$folder_id = isset($_REQUEST["folder_id"]) ? Util::encodeRequest($_REQUEST["folder_id"]) : array();									//実行可能フォルダID
$usergroup_id = isset($_REQUEST["usergroup_id"]) ? Util::encodeRequest($_REQUEST["usergroup_id"]) : array();						//実行可能ユーザグループID
$mailcontent_id = isset($_REQUEST["mailcontent_id"]) ? Util::encodeRequest($_REQUEST["mailcontent_id"]) : "";						//通知メールコンテンツID
$mailsuperadmin = isset($_REQUEST["mailsuperadmin"]) ? Util::encodeRequest($_REQUEST["mailsuperadmin"]) : 0;						//通知メールシステム管理者

//全てを選択の場合、他のデータ削除
if(in_array("all",$folder_id)){
	$folder_id = array("all");
}
if(in_array("all",$usergroup_id)){
	$usergroup_id = array("all");
}
if(in_array("all",$usergroup_id)){
	$usergroup_id = array("all");
}

//起動モードが設定されていない場合
if(Util::IsNullOrEmpty($mode)){
	Logger::notice("起動モードが設定されていませんでした。");
	Location::redirect($redirect);
}

//ワークフローアクションクラス
$WorkFlow = new WorkFlow();


//入力値チェック
if(($mode == "new" || $mode == "edit") && $action == "save"){
	if($workflow_name == ""){
		$error[] = "ワークフローアクション名を入力してください。";
	}
	if($mailcontent_id != "" && !is_numeric($mailcontent_id)){
		$error[] = "通知メールコンテンツIDは数値で入力してください。";
	}
}

//保存処理
if(($mode == "new" || $mode == "edit") && $action == "save" && $error == array()){
	$now_timestamp = time();

	DB::beginTransaction();

	//新規追加の場合データを追加
	if($mode == "new"){
		$insertData = array();
		$insertData["active_flg"] = 1;
		$insertData["sort_no"] = $WorkFlow->getMaxByParameters("sort_no") + 1;
		$insertData["created"] = $now_timestamp;
		$insertData["created_by"] = $session->user["user_id"];
		if(!$WorkFlow->insert($insertData)){
			DB::rollBack();
			Logger::error("ワークフローアクション追加に失敗しました。",$insertData);
			Location::redirect($redirect);
		}
		//IDを取得
		$workflow_id = $WorkFlow->last_insert_id();
	}

	//共通更新条件
	$where = array("workflow_id" => $workflow_id);				//更新条件

	//フォルダ更新データ設定
	$saveData = array();
	$saveData["workflow_name"] = $workflow_name;
	$saveData["workflowstate_from_id"] = $workflowstate_from_id;
	$saveData["workflowstate_to_id"] = $workflowstate_to_id;
	$saveData["changes"] = implode(",", $changes);
	$saveData["contentclass"] = implode(",", $contentclass);
	$saveData["folder_id"] = implode(",", $folder_id);
	$saveData["usergroup_id"] = implode(",", $usergroup_id);
	$saveData["mailcontent_id"] = $mailcontent_id;
	$saveData["mailsuperadmin"] = $mailsuperadmin;

	//ドメイン更新実行
	$saveData["updated"] = $now_timestamp;
	$saveData["updated_by"] = $session->user["user_id"];
	if(!$WorkFlow->update($where, $saveData)){
		DB::rollBack();
		Logger::error("ワークフローアクション更新に失敗しました。",$saveData);
		Location::redirect($redirect);
	}

	DB::commit();

	if($close == "on"){
		//一覧画面に遷移する
		$redirectParam["master_table"] = "workflow";
		Location::redirect($close_url,$redirectParam);
	}else{
		//同画面に遷移する
		$redirectParam["workflow_id"] = $workflow_id;
		$redirectParam["close_url"] = $close_url;
		$redirectParam["mode"] = "edit";
		$redirectParam["message[]"] = "保存しました。";
		Location::redirect($self,$redirectParam);
	}
}

//削除処理
if($mode == "edit" && $action == "delete" && $error == array()){
	DB::beginTransaction();
	$WorkFlow->delete(array("workflow_id" => $workflow_id));
	DB::commit();

	//一覧画面に遷移する
	$redirectParam["master_table"] = "workflow";
	Location::redirect($close_url,$redirectParam);
}

//初期表示
if($action == ""){
	if($mode == "edit"){
		//初期データを取得
		$workflowData = $WorkFlow->getDataByPrimaryKey($workflow_id);
		$workflow_name = $workflowData["workflow_name"];
		$workflowstate_from_id = $workflowData["workflowstate_from_id"];
		$workflowstate_to_id = $workflowData["workflowstate_to_id"];

		$changes = explode(",",$workflowData["changes"]);
		$contentclass = explode(",",$workflowData["contentclass"]);
		$folder_id = explode(",",$workflowData["folder_id"]);
		$usergroup_id = explode(",",$workflowData["usergroup_id"]);
		$mailcontent_id = $workflowData["mailcontent_id"];
		$mailsuperadmin = $workflowData["mailsuperadmin"];
	}else{
		$contentclass = array("page","parts","template","stylesheet","script");
		$folder_id = array("all");
		$usergroup_id = array("all");
		$mailsuperadmin = 0;
	}
}

//ワークフロー状態リスト取得
$WorkFlowState = new WorkFlowState();
$workFlowStateList = $WorkFlowState->getListByParameters(array("active_flg" => 1),array("sort_no" => "asc"));

//フォルダリスト取得
$Folder = new Folder();
$folderList = $Folder->getListByParameters(array("active_flg" => 1),array("sort_no" => "asc"));

//ユーザグループリスト取得
$UserGroup = new UserGroup();
$usergroupList = $UserGroup->getListByParameters(array("active_flg" => 1),array("sort_no" => "asc"));

$LayoutManager = new LayoutManagerAdminMain();
$LayoutManager->setTitle("ワークフローアクション編集");
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

	//削除ボタン設定
	$("#action_delete").click(function(){
		if(!window.confirm('一度削除すると元に戻せません。本当に削除しますか？')){
			return false;
		}
		$("*[name=action]").val('delete');
		$("*[name=close]").val('on');
		$('#values').attr({
		       'action':'<?php echo $self; ?>',
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
<?php if($mode == "edit"): ?>
	<input class="btn red btn_small" type="button" value="削除"  id="action_delete"  />
<?php endif; ?>
</div>

<!-- メッセージ出力開始 -->
<?php echo $LayoutManager->alert(); 	//アラート出力 ?>
<?php echo $LayoutManager->error(); 	//エラー出力 ?>
<?php echo $LayoutManager->message(); 	//メッセージ出力 ?>
<!-- メッセージ出力終了 -->


<form action="/" method="post" id="values" enctype="multipart/form-data">
<input type="hidden" name="mode" value="<?php echo htmlspecialchars($mode); //動作モード ?>" />
<input type="hidden" name="close_url" value="<?php echo htmlspecialchars($close_url); //戻り先URL ?>" />
<input type="hidden" name="master_table" value="<?php echo "workflow"; //マスタ選択画面区分 ?>" />
<input type="hidden" name="workflow_id" value="<?php echo $workflow_id; //ID ?>" />
<input type="hidden" name="action" value="<?php //javascriptから設定 ?>" />
<input type="hidden" name="close" value="<?php //javascriptから設定 ?>" />
<table class="edit" cellspacing="0">
    <tr>
    <th class="w320">ワークフローアクション名<span class="mark orange">必須</span></th>
    <td>
    <span class="mark_1">必須</span>
    <input type="text" name="workflow_name" value="<?php echo htmlspecialchars($workflow_name);?>" />
    </td>
    </tr>

    <tr>
    <th>ワークフロー状態FROM</th>
    <td>
    <select name="workflowstate_from_id">
    <option value=""></option>
    <?php
	foreach($workFlowStateList as $workFlowStateData){
		if($workFlowStateData["workflowstate_id"] == $workflowstate_from_id){ $selected = "selected"; }else{ $selected = ""; }
		echo '<option value="'.$workFlowStateData["workflowstate_id"].'" '.$selected	.'>'.$workFlowStateData["workflowstate_name"].'</option>';
	}
	?>
    </select>
    </td>
    </tr>

    <tr>
    <th>ワークフロー状態TO</th>
    <td>
    <select name="workflowstate_to_id">
    <option value=""></option>
    <?php
	foreach($workFlowStateList as $workFlowStateData){
		if($workFlowStateData["workflowstate_id"] == $workflowstate_to_id){ $selected = "selected"; }else{ $selected = ""; }
		echo '<option value="'.$workFlowStateData["workflowstate_id"].'" '.$selected	.'>'.$workFlowStateData["workflowstate_name"].'</option>';
	}
	?>
    </select>
    </td>
    </tr>

    <tr>
    <th>ワークフロー実行時の自動アクション</th>
    <td>
    <input type="checkbox" name="changes[]" value="publish" <?php if(in_array("publish",$changes)){ echo "checked=checked"; }?>>公開
    </td>
    </tr>

    <tr>
    <th>実行可能コンテンツ</th>
    <td>
    <input type="checkbox" name="contentclass[]" value="page" <?php if(in_array("page",$contentclass)){ echo "checked=checked"; }?>>ページ<br>
    <input type="checkbox" name="contentclass[]" value="parts" <?php if(in_array("parts",$contentclass)){ echo "checked=checked"; }?>>部品<br>
    <input type="checkbox" name="contentclass[]" value="template" <?php if(in_array("template",$contentclass)){ echo "checked=checked"; }?>>テンプレート<br>
    <input type="checkbox" name="contentclass[]" value="stylesheet" <?php if(in_array("stylesheet",$contentclass)){ echo "checked=checked"; }?>>スタイルシート<br>
    <input type="checkbox" name="contentclass[]" value="script" <?php if(in_array("script",$contentclass)){ echo "checked=checked"; }?>>スクリプト<br>
    </td>
    </tr>

    <tr>
    <th>実行可能フォルダ</th>
    <td>
    <input type="checkbox" name="folder_id[]" value="all" <?php if(in_array("all",$folder_id)){ echo "checked=checked"; }?>>全てのフォルダ<br>
	<?php
	foreach($folderList as $folderData){
		if(in_array($folderData["folder_id"],$folder_id)){ $checked = "checked=checked"; }else{ $checked = ""; }
		echo '<input type="checkbox" name="folder_id[]" value="'.$folderData["folder_id"].'" '.$checked.'>'.$folderData["folder_name"].'<br>';
	}
	?>
    </td>
    </tr>

    <tr>
    <th>実行可能ユーザグループ</th>
    <td>
    <input type="checkbox" name="usergroup_id[]" value="all" <?php if(in_array("all",$usergroup_id)){ echo "checked=checked"; }?>>全てのユーザグループ<br>
	<?php
	foreach($usergroupList as $usergroupData){
		if(in_array($usergroupData["usergroup_id"],$usergroup_id)){ $checked = "checked=checked"; }else{ $checked = ""; }
		echo '<input type="checkbox" name="usergroup_id[]" value="'.$usergroupData["usergroup_id"].'" '.$checked.'>'.$usergroupData["usergroup_name"].'<br>';
	}
	?>
    </td>
    </tr>

    <tr>
    <th>通知メールコンテンツID</th>
    <td>
    <input type="text" name="mailcontent_id" value="<?php echo htmlspecialchars($mailcontent_id); ?>" />
    </td>
    </tr>

    <tr>
    <th>システム管理者宛に自動通知</th>
    <td>
    <input type="radio" name="mailsuperadmin" value="0" <?php if($mailsuperadmin == 0){echo "checked"; }?> >自動通知しない<br>
    <input type="radio" name="mailsuperadmin" value="1" <?php if($mailsuperadmin == 1){echo "checked"; }?> >自動通知する<br>
    </td>
    </tr>

</table>
</form>
<?php $LayoutManager->footer(); ?>
