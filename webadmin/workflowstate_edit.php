<?php
// アプリケーション共通クラス読込
require_once(dirname(__FILE__).'/ApplicationCommon/include.php');

require_once(dirname(__FILE__).'/DataAccess/WorkFlowState.php'); 		//ユーザグループクラス

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
$mode = isset($_REQUEST["mode"]) ? $_REQUEST["mode"] : "";																	//起動モード
$action = isset($_REQUEST["action"]) ? $_REQUEST["action"] : "";															//実行アクション
$close = isset($_REQUEST["close"]) ? $_REQUEST["close"] : "";																//閉じるフラグ
$close_url = isset($_REQUEST["close_url"]) ? $_REQUEST["close_url"] : "";													//戻り先URL
$message = isset($_REQUEST["message"]) ? $_REQUEST["message"] : $message;													//メッセージ
$workflowstate_id = isset($_REQUEST["workflowstate_id"]) ? Util::encodeRequest($_REQUEST["workflowstate_id"]) : "";			//ワークフロー状態ID
$workflowstate_name = isset($_REQUEST["workflowstate_name"]) ? Util::encodeRequest($_REQUEST["workflowstate_name"]) : "";	//ワークフロー状態名

//起動モードが設定されていない場合
if(Util::IsNullOrEmpty($mode)){
	Logger::notice("起動モードが設定されていませんでした。");
	Location::redirect($redirect);
}

//ワークフロー状態クラス
$WorkFlowState = new WorkFlowState();

//入力値チェック
if(($mode == "new" || $mode == "edit") && $action == "save"){
	if($workflowstate_name == ""){
		$error[] = "ワークフロー状態名を入力してください。";
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
		$insertData["sort_no"] = $WorkFlowState->getMaxByParameters("workflowstate_id") + 1;
		$insertData["created"] = $now_timestamp;
		$insertData["created_by"] = $session->user["user_id"];
		if(!$WorkFlowState->insert($insertData)){
			DB::rollBack();
			Logger::error("ワークフロー状態追加に失敗しました。",$insertData);
			Location::redirect($redirect);
		}
		//IDを取得
		$workflowstate_id = $WorkFlowState->last_insert_id();
	}

	//共通更新条件
	$where = array("workflowstate_id" => $workflowstate_id);				//更新条件

	//フォルダ更新データ設定
	$saveData = array();
	$saveData["workflowstate_name"] = $workflowstate_name;

	//ドメイン更新実行
	$saveData["updated"] = $now_timestamp;
	$saveData["updated_by"] = $session->user["user_id"];
	if(!$WorkFlowState->update($where, $saveData)){
		DB::rollBack();
		Logger::error("ワークフロー状態更新に失敗しました。",$saveData);
		Location::redirect($redirect);
	}

	DB::commit();

	if($close == "on"){
		//一覧画面に遷移する
		$redirectParam["master_table"] = "workflowstate";
		Location::redirect($close_url,$redirectParam);
	}else{
		//同画面に遷移する
		$redirectParam["workflowstate_id"] = $workflowstate_id;
		$redirectParam["close_url"] = $close_url;
		$redirectParam["mode"] = "edit";
		$redirectParam["message[]"] = "保存しました。";
		Location::redirect($self,$redirectParam);
	}
}

//削除処理
if($mode == "edit" && $action == "delete" && $error == array()){
	DB::beginTransaction();
	$WorkFlowState->delete(array("workflowstate_id" => $workflowstate_id));
	DB::commit();

	//一覧画面に遷移する
	$redirectParam["master_table"] = "workflowstate";
	Location::redirect($close_url,$redirectParam);
}

//初期表示
if($action == ""){
	if($mode == "edit"){
		//初期データを取得
		$workflowstateData = $WorkFlowState->getDataByPrimaryKey($workflowstate_id);
		$workflowstate_name = $workflowstateData["workflowstate_name"];
	}
}

$LayoutManager = new LayoutManagerAdminMain();
$LayoutManager->setTitle("ワークフロー状態編集");
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
<input type="hidden" name="master_table" value="<?php echo "workflowstate"; //マスタ選択画面区分 ?>" />
<input type="hidden" name="workflowstate_id" value="<?php echo $workflowstate_id; //ID ?>" />
<input type="hidden" name="action" value="<?php //javascriptから設定 ?>" />
<input type="hidden" name="close" value="<?php //javascriptから設定 ?>" />
<table class="edit" cellspacing="0">
    <tr>
    <th class="w240">ワークフロー状態名<span class="mark orange">必須</span></th>
    <td>
    <input type="text" name="workflowstate_name" value="<?php echo htmlspecialchars($workflowstate_name);?>" />
    </td>
    </tr>

</table>
</form>
<?php $LayoutManager->footer(); ?>
