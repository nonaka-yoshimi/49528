<?php
//アプリケーション共通クラス読込
require_once(dirname(__FILE__).'/../../ApplicationCommon/include.php');
require_once(dirname(__FILE__).'/../../DataAccess/WorkFlow.php'); 				//ワークフローアクションクラス
require_once(dirname(__FILE__).'/../../DataAccess/WorkFlowState.php'); 			//ワークフロー状態クラス
require_once(dirname(__FILE__).'/../../DataAccess/UserType.php'); 				//ユーザ種別クラス
require_once(dirname(__FILE__).'/../../DataAccess/UserGroup.php'); 				//ユーザグループクラス
require_once(dirname(__FILE__).'/../../DataAccess/UserGroupType.php'); 			//ユーザグループ種別クラス
require_once(dirname(__FILE__).'/../../DataAccess/ContentAuth.php'); 			//コンテンツ操作権限クラス
require_once(dirname(__FILE__).'/../../DataAccess/OperationAuth.php'); 			//機能操作権限クラス
require_once(dirname(__FILE__).'/../../DataAccess/FolderType.php'); 			//フォルダ種別クラス

//基本設定
$self = basename(__FILE__);				//自身のファイル名
$error = array();						//エラー情報格納用配列
$message = array();						//メッセージ情報格納用配列
$alert = array();						//アラート情報格納用配列
$redirect = "../../index.php";			//エラー時リダイレクト先
$restrict = array();					//表示・操作制限

//セッション取得
$session = Session::get();

//セッション認証
$session->loginCheckAndRedirect("../../login.php?msg=session_error");

//リクエストパラメータ取得
$mode = isset($_REQUEST["mode"]) ? $_REQUEST["mode"] : "";																					//起動モード
$action = isset($_REQUEST["action"]) ? $_REQUEST["action"] : "";																			//実行アクション
$close = isset($_REQUEST["close"]) ? $_REQUEST["close"] : "";																				//閉じるフラグ
$message = isset($_REQUEST["message"]) ? $_REQUEST["message"] : $message;																	//メッセージ
$workflow_id = isset($_REQUEST["workflow_id"]) ? $_REQUEST["workflow_id"] : "";																//ワークフローアクションID
$workflow_name = isset($_REQUEST["workflow_name"]) ? Util::encodeRequest($_REQUEST["workflow_name"]) : "";									//ワークフローアクション名
$workflowstate_from_id = isset($_REQUEST["workflowstate_from_id"]) ? $_REQUEST["workflowstate_from_id"] : "";								//ワークフロー状態FROM
$workflowstate_to_id = isset($_REQUEST["workflowstate_to_id"]) ? $_REQUEST["workflowstate_to_id"] : "";										//ワークフロー状態TO
$userrestriction = isset($_REQUEST["userrestriction"]) ? $_REQUEST["userrestriction"] : "";													//実行可能ユーザ起案者区分
$usertype_id = isset($_REQUEST["usertype_id"]) ? $_REQUEST["usertype_id"] : "";																//実行可能ユーザ種別ID
$usergroup_id = isset($_REQUEST["usergroup_id"]) ? $_REQUEST["usergroup_id"] : "";															//実行可能ユーザグループID
$usergrouptype_id = isset($_REQUEST["usergrouptype_id"]) ? $_REQUEST["usergrouptype_id"] : "";												//実行可能ユーザグループ種別ID
$contentauth_id = isset($_REQUEST["contentauth_id"]) ? $_REQUEST["contentauth_id"] : "";													//実行可能コンテンツ操作権限ID
$operationauth_id = isset($_REQUEST["operationauth_id"]) ? $_REQUEST["operationauth_id"] : "";												//実行可能機能操作権限ID
$contentclass = isset($_REQUEST["contentclass"]) ? $_REQUEST["contentclass"] : "";															//実行可能コンテンツクラス
$folder_id = isset($_REQUEST["folder_id"]) ? $_REQUEST["folder_id"] : "";																	//実行可能フォルダID
$folder_name = isset($_REQUEST["folder_name"]) ? $_REQUEST["folder_name"] : "";																//実行可能フォルダ名
$foldertype_id = isset($_REQUEST["foldertype_id"]) ? $_REQUEST["foldertype_id"] : "";														//実行可能フォルダ種別ID
$mailcontent_id = isset($_REQUEST["mailcontent_id"]) ? $_REQUEST["mailcontent_id"] : "";													//通知メールコンテンツID
$mailcontent_name = isset($_REQUEST["mailcontent_name"]) ? $_REQUEST["mailcontent_name"] : "";												//通知メールコンテンツ名
$mailuserrestriction = isset($_REQUEST["mailuserrestriction"]) ? $_REQUEST["mailuserrestriction"] : "";										//通知先ユーザ起案者区分
$mailusertype_id = isset($_REQUEST["mailusertype_id"]) ? $_REQUEST["mailusertype_id"] : "";													//通知先ユーザ種別ID
$mailusergroup_id = isset($_REQUEST["mailusergroup_id"]) ? $_REQUEST["mailusergroup_id"] : "";												//通知先ユーザグループID
$mailusergrouptype_id = isset($_REQUEST["mailusergrouptype_id"]) ? $_REQUEST["mailusergrouptype_id"] : "";									//通知先ユーザグループ種別ID
$mailcontentauth_id = isset($_REQUEST["mailcontentauth_id"]) ? $_REQUEST["mailcontentauth_id"] : "";										//通知先コンテンツ操作権限ID
$mailoperationauth_id = isset($_REQUEST["mailoperationauth_id"]) ? $_REQUEST["mailoperationauth_id"] : "";									//通知先機能操作権限ID

//起動モードが設定されていない場合
if(Util::IsNullOrEmpty($mode)){
	Logger::notice("起動モードが設定されていませんでした。");
	Location::redirect($redirect);
}

//表示・操作制限・デフォルトメッセージ
if($mode == "delete"){
	//表示・操作制限
	$restrict["all"] = SPConst::RESTRICT_READONLY;
	//デフォルトメッセージ
	$alert[] = "一度削除すると元に戻せません。本当に削除しますか？";
}else{
	//表示・操作制限
	$restrict["all"] = SPConst::RESTRICT_ENABLE;
}

//ワークフロー状態一覧を取得する
$WorkFlowState = new WorkFlowState();
$workflowstate_list = $WorkFlowState->getListForSelect();

//ユーザ種別一覧を取得する
$UserType = new UserType();
$usertype_list = $UserType->getListForSelect();

//ユーザグループ一覧を取得する
$UserGroup = new UserGroup();
$usergroup_list = $UserGroup->getUserGroupList();

//ユーザグループ種別一覧を取得する
$UserGroupType = new UserGroupType();
$usergrouptype_list = $UserGroupType->getUserGroupTypeList();

//コンテンツ操作権限一覧を取得する
$ContentAuth = new ContentAuth();
$contentauth_list = $ContentAuth->getListForSelect();

//機能操作権限一覧を取得する
$OperationAuth = new OperationAuth();
$operationauth_list = $OperationAuth->getListForSelect();

//フォルダ種別一覧を取得する
$FolderType = new FolderType();
$foldertype_list = $FolderType->getListForSelect();

//入力値チェック
if(($mode == "new" || $mode == "edit") && $action == "save"){

}

//保存処理
if(($mode == "new" || $mode == "edit") && $action == "save" && $error == array()){
	DB::beginTransaction();

	$WorkFlow = new WorkFlow();				//ワークフローアクションクラス

	//新規追加の場合データを追加
	if($mode == "new"){
		$insertData = array();
		$insertData["active_flg"] = "1";
		if(!$WorkFlow->insert($insertData)){
			DB::rollBack();
			Logger::error("ワークフローアクション新規追加に失敗しました。",$insertData);
			Location::redirect($redirect);
		}
		//ユーザ種別IDを取得
		$workflow_id = $WorkFlow->last_insert_id();
	}

	//共通更新条件
	$where = array("workflow_id" => $workflow_id);								//更新条件

	//更新データ設定
	$saveData = array();
	$saveData["workflow_name"] = $workflow_name;								//ワークフローアクション名
	$saveData["workflowstate_from_id"] = $workflowstate_from_id;				//ワークフロー状態FROM
	$saveData["workflowstate_to_id"] = $workflowstate_to_id;					//ワークフロー状態TO
	$saveData["userrestriction"] = $userrestriction;							//実行可能ユーザ起案者区分
	$saveData["usertype_id"] = $usertype_id;									//実行可能ユーザ種別ID
	$saveData["usergroup_id"] = $usergroup_id;									//実行可能ユーザグループID
	$saveData["usergrouptype_id"] = $usergrouptype_id;							//実行可能ユーザグループ種別ID
	$saveData["contentauth_id"] = $contentauth_id;								//実行可能コンテンツ操作権限ID
	$saveData["operationauth_id"] = $operationauth_id;							//実行可能機能操作権限ID
	$saveData["contentclass"] = $contentclass;									//実行可能コンテンツクラス
	$saveData["folder_id"] = $folder_id;										//実行可能フォルダID
	$saveData["foldertype_id"] = $foldertype_id;								//実行可能フォルダ種別ID
	$saveData["mailcontent_id"] = $mailcontent_id;								//通知メールコンテンツID
	$saveData["mailuserrestriction"] = $mailuserrestriction;					//通知先ユーザ起案者区分
	$saveData["mailusertype_id"] = $mailusertype_id;							//通知先ユーザ種別ID
	$saveData["mailusergroup_id"] = $mailusergroup_id;							//通知先ユーザグループID
	$saveData["mailusergrouptype_id"] = $mailusergrouptype_id;					//通知先ユーザグループ種別ID
	$saveData["mailcontentauth_id"] = $mailcontentauth_id;						//通知先コンテンツ操作権限ID
	$saveData["mailoperationauth_id"] = $mailoperationauth_id;					//通知先機能操作権限ID
	$saveData["active_flg"] = 1;												//有効

	//新規追加の場合は、IDをソート順として初期設定
	if($mode == "new"){
		$saveData["sort_no"] = $workflow_id;
		$saveData["created"] = time();
		$saveData["created_by"] = $session->user["user_id"];
	}

	$saveData["updated"] = time();
	$saveData["updated_by"] = $session->user["user_id"];

	//データ更新実行
	if(!$WorkFlow->update($where, $saveData)){
		DB::rollBack();
		Logger::error("ワークフローアクション更新に失敗しました。",$saveData);
		Location::redirect($redirect);
	}

	DB::commit();

	if($close == "on"){
		//一覧画面に遷移する
		Location::redirect("list.php");
	}else{
		//同画面に遷移する
		$redirectParam["workflow_id"] = $workflow_id;
		$redirectParam["mode"] = "edit";
		$redirectParam["message[]"] = "保存しました。";
		Location::redirect($self,$redirectParam);
	}

}elseif($mode == "delete" && $action == "delete" && $error == array()){		//削除処理
	DB::beginTransaction();

	$WorkFlow = new WorkFlow();									//ワークフローアクションクラス

	//共通削除条件
	$where = array("workflow_id" => $workflow_id);				//削除条件

	//フォルダ種別データ削除
	if(!$WorkFlow->delete($where)){
		DB::rollBack();
		Logger::error("ワークフローアクション削除に失敗しました。",$where);
		Location::redirect($redirect);
	}

	DB::commit();

	//一覧画面に遷移する
	Location::redirect("list.php");
}

//初期表示情報取得
if(($mode == "edit" || $mode == "delete") && $action == ""){		//編集モードで初期表示の場合

	//初期表示データ取得
	$WorkFlow = new WorkFlow();
	$initData = $WorkFlow->getDataForEdit($workflow_id);

	if(!$initData){
		//初期表示データが取得できない場合
		Logger::error("ワークフローアクション初期表示データ取得に失敗しました。",array("workflow_id" => $workflow_id));
		Location::redirect("../../index.php");
	}

	$workflow_name = $initData["workflow_name"];								//ワークフローアクション名
	$workflowstate_from_id = $initData["workflowstate_from_id"];				//ワークフロー状態FROM
	$workflowstate_to_id = $initData["workflowstate_to_id"];					//ワークフロー状態TO
	$userrestriction = $initData["userrestriction"];							//実行可能ユーザ起案者区分
	$usertype_id = $initData["usertype_id"];									//実行可能ユーザ種別ID
	$usergroup_id = $initData["usergroup_id"];									//実行可能ユーザグループID
	$usergrouptype_id = $initData["usergrouptype_id"];							//実行可能ユーザグループ種別ID
	$contentauth_id = $initData["contentauth_id"];								//実行可能コンテンツ操作権限ID
	$operationauth_id = $initData["operationauth_id"];							//実行可能機能操作権限ID
	$contentclass = $initData["contentclass"];									//実行可能コンテンツクラス
	$folder_id = $initData["folder_id"];										//実行可能フォルダID
	$folder_name = $initData["folder_name"];									//実行可能フォルダ名
	$foldertype_id = $initData["foldertype_id"];								//実行可能フォルダ種別ID
	$mailcontent_id = $initData["mailcontent_id"];								//通知メールコンテンツID
	$mailcontent_name = $initData["mailcontent_name"];							//通知メールコンテンツ名
	$mailuserrestriction = $initData["mailuserrestriction"];					//通知先ユーザ起案者区分
	$mailusertype_id = $initData["mailusertype_id"];							//通知先ユーザ種別ID
	$mailusergroup_id = $initData["mailusergroup_id"];							//通知先ユーザグループID
	$mailusergrouptype_id = $initData["mailusergrouptype_id"];					//通知先ユーザグループ種別ID
	$mailcontentauth_id = $initData["mailcontentauth_id"];						//通知先ユーザコンテンツ操作権限ID
	$mailoperationauth_id = $initData["mailoperationauth_id"];					//通知先ユーザ機能操作権限ID

}elseif($mode == "new" && $action == ""){		//新規追加モードで初期表示の場合
	//処理なし
}

$LayoutManager = new LayoutManagerAdminMain();
$LayoutManager->setTitle("ワークフローアクション編集");
$LayoutManager->setErrorList($error);
$LayoutManager->setMessageList($message);
$LayoutManager->setAlertList($alert);
$LayoutManager->header();

?>
<script>
$(function(){
	$("#tabs").tabs({
		selected: 1 //コンテンツタブをデフォルトにする
	});

	//保存するボタン設定
	$("#action_save").click(function(){
		$("*[name=action]").val('save');
		$('#values').attr({
		       'action':'edit.php',
		       'method':'post'
		     });
		$('#values').submit();
	});

	//保存して閉じるボタン設定
	$("#action_save_close").click(function(){
		$("*[name=action]").val('save');
		$("*[name=close]").val('on');
		$('#values').attr({
		       'action':'edit.php',
		       'method':'post'
		     });
		$('#values').submit();
	});

	//削除ボタン設定
	$("#action_delete").click(function(){
		$("*[name=action]").val('delete');
		$('#values').attr({
		       'action':'edit.php',
		       'method':'post'
		     });
		$('#values').submit();
	});

	//一覧に戻るボタン設定
	$("#back_to_list").click(function(){
		$('#values').attr({
		       'action':'list.php',
		       'method':'post'
		     });
		$('#values').submit();
	});
});
</script>

<form action="/" method="post" id="values" enctype="multipart/form-data">
<input type="hidden" name="workflow_id" value="<?php echo htmlspecialchars($workflow_id); //ワークフローアクションID ?>" />
<input type="hidden" name="mode" value="<?php echo htmlspecialchars($mode); //動作モード ?>" />
<input type="hidden" name="action" value="<?php //javascriptから設定 ?>" />
<input type="hidden" name="close" value="<?php //javascriptから設定 ?>" />

<div id="tabs">
<table class="edit_control">
<tr>
<td>
<input type="button" value="一覧に戻る" id="back_to_list" />
</td>
<?php if($mode == "new" || $mode == "edit"): ?>
	<td>
	<input type="button" value="保存する" id="action_save"  />
	</td>
	<td>
	<input type="button" value="保存して閉じる" id="action_save_close"  />
	</td>
<?php elseif($mode == "delete"): ?>
	<td>
	<input type="button" value="削除する" id="action_delete" />
	</td>
<?php endif; ?>
</tr>
</table>
<br>

<!-- メッセージ出力開始 -->
<?php echo $LayoutManager->alert(); 	//アラート出力 ?>
<?php echo $LayoutManager->error(); 	//エラー出力 ?>
<?php echo $LayoutManager->message(); 	//メッセージ出力 ?>
<!-- メッセージ出力終了 -->

<ul>
<li><a href="#tabs-1" class="tabmenu">ワークフローアクション設定</a></li>
</ul>

<!-- コンテンツタブ領域開始 -->
<div id="tabs-1" class="tab_area">
<h1>ワークフローアクション設定</h1>
<table class="content_input_table">
<?php if($restrict["all"] >= SPConst::RESTRICT_READONLY): ?>
	<tr>
	<th>ワークフローアクション名</th>
	<td>
	<?php echo UIParts::middleText("workflow_name",$workflow_name,$restrict["all"]); ?>
	</td>
	</tr>
	<tr>
	<th>ワークフロー状態FROM</th>
	<td>
	<?php
		$select_list = Util::getAssocFromMultiArrayByKeyValue($workflowstate_list, "workflowstate_id", "workflowstate_name");
		$select_list = array("" => "--ワークフロー状態FROM--") + $select_list;
		echo UIParts::select("workflowstate_from_id", $select_list,$workflowstate_from_id,$restrict["all"]);
	?>
	</td>
	</tr>
	<tr>
	<th>ワークフロー状態TO</th>
	<td>
	<?php
		$select_list = Util::getAssocFromMultiArrayByKeyValue($workflowstate_list, "workflowstate_id", "workflowstate_name");
		$select_list = array("" => "--ワークフロー状態TO--") + $select_list;
		echo UIParts::select("workflowstate_to_id", $select_list,$workflowstate_to_id,$restrict["all"]);
	?>
	</td>
	</tr>
	<tr>
	<th>実行可能ユーザ起案者区分</th>
	<td>
	<?php
		$select_list = Options::workflow_user_restriction();
		echo UIParts::select("userrestriction", $select_list,$userrestriction,$restrict["all"]);
	?>
	</td>
	</tr>
	<tr>
	<th>実行可能ユーザ種別制限</th>
	<td>
	<?php
		$select_list = Util::getAssocFromMultiArrayByKeyValue($usertype_list, "usertype_id", "usertype_name");
		$select_list = array("" => "--実行可能ユーザ種別--") + $select_list;
		echo UIParts::select("usertype_id", $select_list,$usertype_id,$restrict["all"]);
	?>
	</td>
	</tr>
	<tr>
	<th>実行可能ユーザグループ制限</th>
	<td>
	<?php
		$select_list = Util::getAssocFromMultiArrayByKeyValue($usergroup_list, "usergroup_id", "usergroup_name");
		$select_list = array("" => "--実行可能ユーザグループ--") + $select_list;
		echo UIParts::select("usergroup_id", $select_list,$usergroup_id,$restrict["all"]);
	?>
	</td>
	</tr>
	<tr>
	<th>実行可能ユーザグループ種別制限</th>
	<td>
	<?php
		$select_list = Util::getAssocFromMultiArrayByKeyValue($usergrouptype_list, "usergrouptype_id", "usergrouptype_name");
		$select_list = array("" => "--実行可能ユーザグループ種別--") + $select_list;
		echo UIParts::select("usergrouptype_id", $select_list,$usergrouptype_id,$restrict["all"]);
	?>
	</td>
	</tr>
	<tr>
	<th>実行可能コンテンツ操作権限制限</th>
	<td>
	<?php
		$select_list = Util::getAssocFromMultiArrayByKeyValue($contentauth_list, "contentauth_id", "contentauth_name");
		$select_list = array("" => "--実行可能コンテンツ操作権限--") + $select_list;
		echo UIParts::select("contentauth_id", $select_list,$contentauth_id,$restrict["all"]);
	?>
	</td>
	</tr>
	<tr>
	<th>実行可能機能操作権限制限</th>
	<td>
	<?php
		$select_list = Util::getAssocFromMultiArrayByKeyValue($operationauth_list, "operationauth_id", "operationauth_name");
		$select_list = array("" => "--実行可能機能操作権限--") + $select_list;
		echo UIParts::select("operationauth_id", $select_list,$operationauth_id,$restrict["all"]);
	?>
	</td>
	</tr>
	<tr>
	<th>実行可能コンテンツ種別制限</th>
	<td>
	<?php
		$select_list = Options::contentclass();
		$select_list = array("" => "--実行可能コンテンツ種別--") + $select_list;
		echo UIParts::select("contentclass", $select_list,$contentclass,$restrict["all"]);
	?>
	</td>
	</tr>
	<tr>
	<th>実行可能フォルダ制限</th>
	<td>
	<?php echo UIParts::shortReference("folder","folder_id", "folder_name", $folder_id,$folder_name,$restrict["all"]); ?>
	</td>
	</tr>
	<tr>
	<th>実行可能フォルダ種別制限</th>
	<td>
	<?php
		$select_list = Util::getAssocFromMultiArrayByKeyValue($foldertype_list, "foldertype_id", "foldertype_name");
		$select_list = array("" => "--フォルダ種別--") + $select_list;
		echo UIParts::select("foldertype_id", $select_list,$foldertype_id,$restrict["all"]);
	?>
	</td>
	</tr>
	<tr>
	<th>通知メール</th>
	<td>
	<?php echo UIParts::shortReference("page","mailcontent_id", "mailcontent_name", $mailcontent_id,$mailcontent_name,$restrict["all"]); ?>
	</td>
	</tr>
	<tr>
	<th>通知先ユーザ起案者区分</th>
	<td>
	<?php
		$select_list = Options::workflow_user_restriction();
		echo UIParts::select("mailuserrestriction", $select_list,$mailuserrestriction,$restrict["all"]);
	?>
	</td>
	</tr>
	<tr>
	<th>通知先ユーザ種別制限</th>
	<td>
	<?php
		$select_list = Util::getAssocFromMultiArrayByKeyValue($usertype_list, "usertype_id", "usertype_name");
		$select_list = array("" => "--通知先ユーザ種別--") + $select_list;
		echo UIParts::select("mailusertype_id", $select_list,$mailusertype_id,$restrict["all"]);
	?>
	</td>
	</tr>
	<tr>
	<th>通知先ユーザグループ制限</th>
	<td>
	<?php
		$select_list = Util::getAssocFromMultiArrayByKeyValue($usergroup_list, "usergroup_id", "usergroup_name");
		$select_list = array("" => "--通知先ユーザグループ--") + $select_list;
		echo UIParts::select("mailusergroup_id", $select_list,$mailusergroup_id,$restrict["all"]);
	?>
	</td>
	</tr>
	<tr>
	<th>通知先ユーザグループ種別制限</th>
	<td>
	<?php
		$select_list = Util::getAssocFromMultiArrayByKeyValue($usergrouptype_list, "usergrouptype_id", "usergrouptype_name");
		$select_list = array("" => "--通知先ユーザグループ種別--") + $select_list;
		echo UIParts::select("mailusergrouptype_id", $select_list,$mailusergrouptype_id,$restrict["all"]);
	?>
	</td>
	</tr>
	<tr>
	<th>通知先コンテンツ操作権限制限</th>
	<td>
	<?php
		$select_list = Util::getAssocFromMultiArrayByKeyValue($contentauth_list, "contentauth_id", "contentauth_name");
		$select_list = array("" => "--通知先コンテンツ操作権限--") + $select_list;
		echo UIParts::select("mailcontentauth_id", $select_list,$mailcontentauth_id,$restrict["all"]);
	?>
	</td>
	</tr>
	<tr>
	<th>通知先機能操作権限制限</th>
	<td>
	<?php
		$select_list = Util::getAssocFromMultiArrayByKeyValue($operationauth_list, "operationauth_id", "operationauth_name");
		$select_list = array("" => "--通知先機能操作権限--") + $select_list;
		echo UIParts::select("mailoperationauth_id", $select_list,$mailoperationauth_id,$restrict["all"]);
	?>
	</td>
	</tr>
<?php endif; ?>
</table>
</div>
<!-- コンテンツタブ領域終了 -->

</div>
</form>
<?php echo $LayoutManager->footer(); ?>