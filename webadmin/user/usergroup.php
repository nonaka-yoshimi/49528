<?php
//アプリケーション共通クラス読込
require_once(dirname(__FILE__).'/../ApplicationCommon/include.php');
require_once(dirname(__FILE__).'/../DataAccess/UserGroup.php');
require_once(dirname(__FILE__).'/../DataAccess/UserGroupType.php');
require_once(dirname(__FILE__).'/../DataAccess/OperationAuth.php');

//基本設定
$self = basename(__FILE__);				//自身のファイル名
$error = array();						//エラー情報格納用配列
$message = array();						//メッセージ情報格納用配列
$alert = array();						//アラート情報格納用配列
$redirect = "../index.php";				//エラー時リダイレクト先
$restrict = array();					//表示・操作制限

//セッション取得
$session = Session::get();

//セッション認証
$session->loginCheckAndRedirect("../login.php?msg=session_error");

//リクエストパラメータ取得
$mode = isset($_REQUEST["mode"]) ? $_REQUEST["mode"] : "";																					//起動モード
$action = isset($_REQUEST["action"]) ? $_REQUEST["action"] : "";																			//実行アクション
$close = isset($_REQUEST["close"]) ? $_REQUEST["close"] : "";																				//閉じるフラグ
$message = isset($_REQUEST["message"]) ? $_REQUEST["message"] : $message;																	//メッセージ
$usergroup_id = isset($_REQUEST["usergroup_id"]) ? $_REQUEST["usergroup_id"] : "";															//ユーザグループID
$usergroup_name = isset($_POST["usergroup_name"]) ? Util::encodeRequest($_POST["usergroup_name"]) : "";										//ユーザグループ名
$parentgroup_id = isset($_POST["parentgroup_id"]) ? $_POST["parentgroup_id"] : "";															//親グループID
$parentgroup_name = isset($_POST["parentgroup_name"]) ? Util::encodeRequest($_POST["parentgroup_name"]) : "";								//親グループ名
$usergrouptype_id = isset($_POST["usergrouptype_id"]) ? $_POST["usergrouptype_id"] : "";													//ユーザグループ種別ID
$usergrouptype_name = isset($_POST["usergrouptype_name"]) ? Util::encodeRequest($_POST["usergrouptype_name"]) : "";							//ユーザグループ種別名
$operationauth_id = isset($_POST["operationauth_id"]) ? $_POST["operationauth_id"] : "";													//機能操作権限種別ID
$start_date = isset($_POST["start_date"]) ? Util::encodeRequest($_POST["start_date"]) : "";													//利用開始（年月日）
$start_hour = isset($_POST["start_hour"]) ? $_POST["start_hour"] : "";																		//利用開始（時）
$start_minute = isset($_POST["start_minute"]) ? $_POST["start_minute"] : "";																//利用開始（分）
$start_time_no_check = isset($_POST["start_time_no_check"]) ? $_POST["start_time_no_check"] : "";											//利用開始を指定しない
$end_date = isset($_POST["end_date"]) ? Util::encodeRequest($_POST["end_date"]) : "";														//利用終了（年月日）
$end_hour = isset($_POST["end_hour"]) ? $_POST["end_hour"] : "";																			//利用終了（時）
$end_minute = isset($_POST["end_minute"]) ? $_POST["end_minute"] : "";																		//利用終了（分）
$end_time_no_check = isset($_POST["end_time_no_check"]) ? $_POST["end_time_no_check"] : "";													//利用終了を指定しない

//起動モードが設定されていない場合
if(Util::IsNullOrEmpty($mode)){
	Logger::notice("起動モードが設定されていませんでした。");
	Location::redirect($redirect);
}

//表示・操作制限・デフォルトメッセージ
if($mode == "delete"){
	//表示・操作制限
	$restrict["usergroup_name"] = SPConst::RESTRICT_READONLY;
	$restrict["parentgroup_id"] = SPConst::RESTRICT_READONLY;
	$restrict["usergrouptype_id"] = SPConst::RESTRICT_READONLY;
	$restrict["operationauth_id"] = SPConst::RESTRICT_READONLY;
	$restrict["start_time"] = SPConst::RESTRICT_READONLY;
	$restrict["end_time"] = SPConst::RESTRICT_READONLY;
	//デフォルトメッセージ
	$alert[] = "一度削除すると元に戻せません。本当に削除しますか？";
}else{
	//表示・操作制限
	$restrict["usergroup_name"] = SPConst::RESTRICT_ENABLE;
	$restrict["parentgroup_id"] = SPConst::RESTRICT_ENABLE;
	$restrict["usergrouptype_id"] = SPConst::RESTRICT_ENABLE;
	$restrict["operationauth_id"] = SPConst::RESTRICT_ENABLE;
	$restrict["start_time"] = SPConst::RESTRICT_ENABLE;
	$restrict["end_time"] = SPConst::RESTRICT_ENABLE;
}

//ユーザグループ種別一覧を取得する
$UserGroupType = new UserGroupType();
$usergrouptype_list = $UserGroupType->getUserGroupTypeList();

//機能操作権限種別一覧を取得する
$OperationAuth = new OperationAuth();
$operationauth_list = $OperationAuth->getListForSelect();

//入力値チェック
if(($mode == "new" || $mode == "edit") && $action == "save"){

}

//保存処理
if(($mode == "new" || $mode == "edit") && $action == "save" && $error == array()){
	DB::beginTransaction();

	$UserGroup = new UserGroup();					//ユーザグループクラス

	//新規追加の場合データを追加
	if($mode == "new"){
		$insertData = array();
		$insertData["parentgroup_id"] = $parentgroup_id;
		if(!$UserGroup->insert($insertData)){
			DB::rollBack();
			Logger::error("ユーザグループ新規追加に失敗しました。",$insertData);
			Location::redirect($redirect);
		}
		//ユーザグループIDを取得
		$usergroup_id = $UserGroup->last_insert_id();
	}

	//共通更新条件
	$where = array("usergroup_id" => $usergroup_id);							//更新条件

	//ユーザグループ更新データ設定
	$saveData = array();
	$saveData["usergroup_name"] = $usergroup_name;								//ユーザグループ名
	$saveData["parentgroup_id"] = $parentgroup_id;								//親ユーザグループID
	$saveData["usergrouptype_id"] = $usergrouptype_id;							//ユーザグループ種別ID
	$saveData["operationauth_id"] = $operationauth_id;							//機能操作権限種別ID
	if(!$start_time_no_check){
		$saveData["start_time"] = Util::convInputDateTimeToTimestamp($start_date,$start_hour,$start_minute);	//利用開始
	}else{
		$saveData["start_time"] = null;											//利用開始を指定しない
	}
	if(!$end_time_no_check){
		$saveData["end_time"] = Util::convInputDateTimeToTimestamp($end_date,$end_hour,$end_minute);	//利用終了
	}else{
		$saveData["end_time"] = null;											//利用終了を指定しない
	}
	$saveData["active_flg"] = 1;												//有効

	//新規追加の場合は、ユーザグループIDをソート順として初期設定
	if($mode == "new"){
		$saveData["sort_no"] = $usergroup_id;
		$saveData["created"] = time();
		$saveData["created_by"] = $session->user["user_id"];
	}

	$saveData["updated"] = time();
	$saveData["updated_by"] = $session->user["user_id"];

	//ユーザグループ更新実行
	if(!$UserGroup->update($where, $saveData)){
		DB::rollBack();
		Logger::error("ユーザグループ更新に失敗しました。",$saveData);
		Location::redirect($redirect);
	}

	DB::commit();

	if($close == "on"){
		//一覧画面に遷移する
		Location::redirect("index.php");
	}else{
		//同画面に遷移する
		$redirectParam["usergroup_id"] = $usergroup_id;
		$redirectParam["mode"] = "edit";
		$redirectParam["message[]"] = "保存しました。";
		Location::redirect($self,$redirectParam);
	}
}elseif($mode == "delete" && $action == "delete" && $error == array()){		//削除処理
	DB::beginTransaction();

	$UserGroup = new UserGroup();						//ユーザグループクラス

	//共通削除条件
	$where = array("usergroup_id" => $usergroup_id);	//削除条件

	//ユーザグループデータ削除
	if(!$UserGroup->delete($where)){
		DB::rollBack();
		Logger::error("ユーザグループ削除に失敗しました。",$where);
		Location::redirect($redirect);
	}

	DB::commit();

	//一覧画面に遷移する
	Location::redirect("index.php");
}

//初期表示情報取得
if(($mode == "edit" || $mode == "delete") && $action == ""){		//編集モードで初期表示の場合
	//ユーザグループ情報取得
	$UserGroup = new UserGroup();
	$userGroupData = $UserGroup->getUserGroupDataForEdit($usergroup_id);

	if(!$userGroupData){
		//ユーザグループデータが取得できない場合
		Location::redirect("../index.php");
	}

	$start_time = $userGroupData["start_time"];								//利用開始(timestamp)
	if(!Util::IsNullOrEmpty($start_time)){
		$start_date = date("Y/m/d",$start_time);							//利用開始(年月日)
		$start_hour = date("H",$start_time);								//利用開始(時)
		$start_minute = date("i",$start_time);								//利用開始(分)
	}else{
		$start_date = "";													//利用開始(年月日)
		$start_hour = "";													//利用開始(時)
		$start_minute = "";													//利用開始(分)
		$start_time_no_check = "checked";									//利用開始を指定しない
	}
	$end_time = $userGroupData["end_time"];									//利用終了(timestamp)
	if(!Util::IsNullOrEmpty($end_time)){
		$end_date = date("Y/m/d",$end_time);								//利用終了(年月日)
		$end_hour = date("H",$end_time);									//利用終了(時)
		$end_minute = date("i",$end_time);									//利用終了(分)
	}else{
		$end_date = "";														//利用終了(年月日)
		$end_hour = "";														//利用終了(時)
		$end_minute = "";													//利用終了(分)
		$end_time_no_check = "checked";										//利用終了を指定しない
	}

	$parentgroup_id = $userGroupData["parentgroup_id"];						//親グループID
	$parentgroup_name = $userGroupData["parentgroup_name"];					//親グループ名
	$usergroup_name = $userGroupData["usergroup_name"];						//ユーザグループ名
	$operationauth_id = $userGroupData["operationauth_id"];					//機能操作権限種別ID
	$usergrouptype_id = $userGroupData["usergrouptype_id"];					//ユーザグループ種別ID

}elseif($mode == "new" && $action == ""){		//新規追加モードで初期表示の場合
	$parentgroup_id = $usergroup_id;			//ユーザグループIDパラメータを親グループIDに差し替え

	//親ユーザグループ情報取得
	$UserGroup = new UserGroup();
	$parentGroupData = $UserGroup->getUserGroupDataForEdit($parentgroup_id);
	$parentgroup_id = $parentGroupData["usergroup_id"];
	$parentgroup_name = $parentGroupData["usergroup_name"];
}

//レイアウトマネージャ設定・ヘッダ出力
$LayoutManager = new LayoutManagerAdminMain();
$LayoutManager->setTitle("ユーザグループ設定");
$LayoutManager->setErrorList($error);
$LayoutManager->setMessageList($message);
$LayoutManager->setAlertList($alert);
$LayoutManager->header();
?>
<script>
$(function(){
	//タブレイアウト設定
	$("#tabs").tabs({
		selected: 1 //タブのデフォルト設定
	});

	//コンテンツ一覧に戻るボタン設定
	$("#back_to_list").click(function(){
		$('#values').attr({
		       'action':'index.php',
		       'method':'get'
		     });
		$('#values').submit();
	});

	//保存するボタン設定
	$("#action_save").click(function(){
		$("*[name=action]").val('save');
		$('#values').attr({
		       'action':'usergroup.php',
		       'method':'post'
		     });
		$('#values').submit();
	});

	//保存して閉じるボタン設定
	$("#action_save_close").click(function(){
		$("*[name=action]").val('save');
		$("*[name=close]").val('on');
		$('#values').attr({
		       'action':'usergroup.php',
		       'method':'post'
		     });
		$('#values').submit();
	});

	//削除ボタン設定
	$("#action_delete").click(function(){
		$("*[name=action]").val('delete');
		$('#values').attr({
		       'action':'usergroup.php',
		       'method':'post'
		     });
		$('#values').submit();
	});
});
</script>

<form action="/" method="post" id="values">
<input type="hidden" name="usergroup_id" value="<?php echo htmlspecialchars($usergroup_id); //ユーザグループID ?>" />
<input type="hidden" name="mode" value="<?php echo htmlspecialchars($mode); //動作モード ?>" />
<input type="hidden" name="action" value="<?php //javascriptから設定 ?>" />
<input type="hidden" name="close" value="<?php //javascriptから設定 ?>" />

<div id="tabs">

<!-- アクションボタン開始 -->
<table class="edit_control">
<tr>
<td>
<input type="button" value="ユーザ一覧に戻る"  id="back_to_list" />
</td>
<?php if($mode == "new" || $mode == "edit"): ?>
	<td>
	<input type="button" value="保存する" id="action_save" />
	</td>
	<td>
	<input type="button" value="保存して閉じる" id="action_save_close" />
	</td>
<?php elseif($mode == "delete"): ?>
	<td>
	<input type="button" value="削除する" id="action_delete" />
	</td>
<?php endif; ?>
</tr>
</table><!-- edit_control -->
<!-- アクションボタン終了 -->
<br />

<!-- メッセージ出力開始 -->
<?php echo $LayoutManager->alert(); 	//アラート出力 ?>
<?php echo $LayoutManager->error(); 	//エラー出力 ?>
<?php echo $LayoutManager->message(); 	//メッセージ出力 ?>
<!-- メッセージ出力終了 -->

<!-- タブメニュー設定開始 -->
<ul>
<li><a href="#tabs-1" class="tabmenu">ユーザグループ設定(基本)</a></li>
<li><a href="#tabs-2" class="tabmenu">属性・権限</a></li>
<li><a href="#tabs-3" class="tabmenu">システム設定</a></li>
</ul>
<!-- タブメニュー設定終了 -->

<!-- フォルダ設定（基本）タブ開始 -->
<div id="tabs-1" class="tab_area">
<h1>フォルダ設定(基本)</h1>
<table class="content_input_table">
<?php if($restrict["usergroup_name"] >= SPConst::RESTRICT_READONLY): ?>
	<tr>
	<th>ユーザグループ名&nbsp;</th>
	<td>
	<?php echo UIParts::middleText("usergroup_name", $usergroup_name, $restrict["usergroup_name"]); ?>
	</td>
	</tr>
<?php endif; ?>
</table>
</div>
<!-- フォルダ設定（基本）タブ終了 -->

<!-- 属性・権限タブ開始 -->
<div id="tabs-2" class="tab_area">
<h1>属性</h1>
<table class="content_input_table">
<?php if($restrict["parentgroup_id"] >= SPConst::RESTRICT_READONLY): ?>
	<tr>
	<th>親グループ&nbsp;</th>
	<td>
	<?php echo UIParts::shortReference("usergroup","parentgroup_id", "parentgroup_name", $parentgroup_id,$parentgroup_name,$restrict["parentgroup_id"]); ?>
	</td>
	</tr>
<?php endif; ?>
<?php if($restrict["usergrouptype_id"] >= SPConst::RESTRICT_READONLY): ?>
	<tr>
	<th>ユーザグループ種別&nbsp;</th>
	<td>
	<?php
		$select_list = Util::getAssocFromMultiArrayByKeyValue($usergrouptype_list, "usergrouptype_id", "usergrouptype_name");
		$select_list = array("" => "--ユーザグループ種別--") + $select_list;
		echo UIParts::select("usergrouptype_id", $select_list,$usergrouptype_id,$restrict["usergrouptype_id"]);
	?>
	</td>
	</tr>
<?php endif; ?>
<?php if($restrict["operationauth_id"] >= SPConst::RESTRICT_READONLY): ?>
	<tr>
	<th>機能操作権限種別&nbsp;</th>
	<td>
	<?php
		$select_list = Util::getAssocFromMultiArrayByKeyValue($operationauth_list, "operationauth_id", "operationauth_name");
		$select_list = array("" => "--機能操作権限種別--") + $select_list;
		echo UIParts::select("operationauth_id", $select_list,$operationauth_id,$restrict["operationauth_id"]);
	?>
	</td>
	</tr>
<?php endif; ?>
</table>
</div>
<!-- 属性・権限タブ終了 -->

<!-- システム設定開始 -->
<div id="tabs-3" class="tab_area">
<h1>システム設定</h1>
<table class="content_input_table">
<?php if($restrict["start_time"] >= SPConst::RESTRICT_READONLY): ?>
	<tr>
	<th>利用開始&nbsp;</th>
	<td>
	<?php echo UIParts::dateTimePicker("start_date", "start_hour", "start_minute", $start_date, $start_hour, $start_minute,$restrict["start_time"]); ?>
	<?php if($restrict["start_time"] >= SPConst::RESTRICT_ENABLE): ?>
		<input type="checkbox" name="start_time_no_check" value="checked" <?php if($start_time_no_check){ echo "checked"; }?>>指定しない
	<?php endif; ?>
	</td>
	</tr>
<?php endif; ?>
<?php if($restrict["end_time"] >= SPConst::RESTRICT_READONLY): ?>
	<tr>
	<th>利用終了&nbsp;</th>
	<td>
	<?php echo UIParts::dateTimePicker("end_date", "end_hour", "end_minute", $end_date, $end_hour, $end_minute,$restrict["end_time"]); ?>
	<?php if($restrict["end_time"] >= SPConst::RESTRICT_ENABLE): ?>
		<input type="checkbox" name="end_time_no_check" value="checked" <?php if($end_time_no_check){ echo "checked"; }?>>指定しない
	<?php endif; ?>
	</td>
	</tr>
<?php endif; ?>
</table>
</div>
<!-- システム設定タブ終了 -->

</div><!-- tabs -->
</form><!-- values -->
<?php echo $LayoutManager->footer(); ?>