<?php
//アプリケーション共通クラス読込
require_once(dirname(__FILE__).'/../../ApplicationCommon/include.php');
require_once(dirname(__FILE__).'/../../DataAccess/OperationAuth.php');			//機能操作権限クラス

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

//対象データ設定
//権限
$auth_setting_list[] = array("name" => "ope_auth_page_view","display_name" => "ページ閲覧","type" => SPConst::RESTRICT_TYPE_ACTIVE_NONACTIVE);
$auth_setting_list[] = array("name" => "ope_auth_page_add","display_name" => "ページ作成","type" => SPConst::RESTRICT_TYPE_ACTIVE_NONACTIVE);
$auth_setting_list[] = array("name" => "ope_auth_page_edit","display_name" => "ページ編集","type" => SPConst::RESTRICT_TYPE_ACTIVE_NONACTIVE);
$auth_setting_list[] = array("name" => "ope_auth_page_delete","display_name" => "ページ削除","type" => SPConst::RESTRICT_TYPE_ACTIVE_NONACTIVE);
$auth_setting_list[] = array("name" => "ope_auth_page_workflow","display_name" => "ページワークフロー実行","type" => SPConst::RESTRICT_TYPE_ACTIVE_NONACTIVE);
$auth_setting_list[] = array("name" => "ope_auth_page_publish","display_name" => "ページ公開","type" => SPConst::RESTRICT_TYPE_ACTIVE_NONACTIVE);
$auth_setting_list[] = array("name" => "ope_auth_page_archive","display_name" => "ページアーカイブ","type" => SPConst::RESTRICT_TYPE_ACTIVE_NONACTIVE);
$auth_setting_list[] = array("name" => "ope_auth_element_view","display_name" => "部品閲覧","type" => SPConst::RESTRICT_TYPE_ACTIVE_NONACTIVE);
$auth_setting_list[] = array("name" => "ope_auth_element_add","display_name" => "部品作成","type" => SPConst::RESTRICT_TYPE_ACTIVE_NONACTIVE);
$auth_setting_list[] = array("name" => "ope_auth_element_edit","display_name" => "部品編集","type" => SPConst::RESTRICT_TYPE_ACTIVE_NONACTIVE);
$auth_setting_list[] = array("name" => "ope_auth_element_delete","display_name" => "部品削除","type" => SPConst::RESTRICT_TYPE_ACTIVE_NONACTIVE);
$auth_setting_list[] = array("name" => "ope_auth_element_workflow","display_name" => "部品ワークフロー実行","type" => SPConst::RESTRICT_TYPE_ACTIVE_NONACTIVE);
$auth_setting_list[] = array("name" => "ope_auth_element_publish","display_name" => "部品公開","type" => SPConst::RESTRICT_TYPE_ACTIVE_NONACTIVE);
$auth_setting_list[] = array("name" => "ope_auth_element_archive","display_name" => "部品アーカイブ","type" => SPConst::RESTRICT_TYPE_ACTIVE_NONACTIVE);
$auth_setting_list[] = array("name" => "ope_auth_image_view","display_name" => "イメージ閲覧","type" => SPConst::RESTRICT_TYPE_ACTIVE_NONACTIVE);
$auth_setting_list[] = array("name" => "ope_auth_image_add","display_name" => "イメージ作成","type" => SPConst::RESTRICT_TYPE_ACTIVE_NONACTIVE);
$auth_setting_list[] = array("name" => "ope_auth_image_edit","display_name" => "イメージ編集","type" => SPConst::RESTRICT_TYPE_ACTIVE_NONACTIVE);
$auth_setting_list[] = array("name" => "ope_auth_image_delete","display_name" => "イメージ削除","type" => SPConst::RESTRICT_TYPE_ACTIVE_NONACTIVE);
$auth_setting_list[] = array("name" => "ope_auth_image_workflow","display_name" => "イメージワークフロー実行","type" => SPConst::RESTRICT_TYPE_ACTIVE_NONACTIVE);
$auth_setting_list[] = array("name" => "ope_auth_image_publish","display_name" => "イメージ公開","type" => SPConst::RESTRICT_TYPE_ACTIVE_NONACTIVE);
$auth_setting_list[] = array("name" => "ope_auth_image_archive","display_name" => "イメージアーカイブ","type" => SPConst::RESTRICT_TYPE_ACTIVE_NONACTIVE);
$auth_setting_list[] = array("name" => "ope_auth_file_view","display_name" => "ファイル閲覧","type" => SPConst::RESTRICT_TYPE_ACTIVE_NONACTIVE);
$auth_setting_list[] = array("name" => "ope_auth_file_add","display_name" => "ファイル作成","type" => SPConst::RESTRICT_TYPE_ACTIVE_NONACTIVE);
$auth_setting_list[] = array("name" => "ope_auth_file_edit","display_name" => "ファイル編集","type" => SPConst::RESTRICT_TYPE_ACTIVE_NONACTIVE);
$auth_setting_list[] = array("name" => "ope_auth_file_delete","display_name" => "ファイル削除","type" => SPConst::RESTRICT_TYPE_ACTIVE_NONACTIVE);
$auth_setting_list[] = array("name" => "ope_auth_file_workflow","display_name" => "ファイルワークフロー実行","type" => SPConst::RESTRICT_TYPE_ACTIVE_NONACTIVE);
$auth_setting_list[] = array("name" => "ope_auth_file_publish","display_name" => "ファイル公開","type" => SPConst::RESTRICT_TYPE_ACTIVE_NONACTIVE);
$auth_setting_list[] = array("name" => "ope_auth_file_archive","display_name" => "ファイルアーカイブ","type" => SPConst::RESTRICT_TYPE_ACTIVE_NONACTIVE);
$auth_setting_list[] = array("name" => "ope_auth_template_view","display_name" => "テンプレート閲覧","type" => SPConst::RESTRICT_TYPE_ACTIVE_NONACTIVE);
$auth_setting_list[] = array("name" => "ope_auth_template_add","display_name" => "テンプレート作成","type" => SPConst::RESTRICT_TYPE_ACTIVE_NONACTIVE);
$auth_setting_list[] = array("name" => "ope_auth_template_edit","display_name" => "テンプレート編集","type" => SPConst::RESTRICT_TYPE_ACTIVE_NONACTIVE);
$auth_setting_list[] = array("name" => "ope_auth_template_delete","display_name" => "テンプレート削除","type" => SPConst::RESTRICT_TYPE_ACTIVE_NONACTIVE);
$auth_setting_list[] = array("name" => "ope_auth_template_workflow","display_name" => "テンプレートワークフロー実行","type" => SPConst::RESTRICT_TYPE_ACTIVE_NONACTIVE);
$auth_setting_list[] = array("name" => "ope_auth_template_publish","display_name" => "テンプレート公開","type" => SPConst::RESTRICT_TYPE_ACTIVE_NONACTIVE);
$auth_setting_list[] = array("name" => "ope_auth_template_archive","display_name" => "テンプレートアーカイブ","type" => SPConst::RESTRICT_TYPE_ACTIVE_NONACTIVE);
$auth_setting_list[] = array("name" => "ope_auth_stylesheet_view","display_name" => "スタイルシート閲覧","type" => SPConst::RESTRICT_TYPE_ACTIVE_NONACTIVE);
$auth_setting_list[] = array("name" => "ope_auth_stylesheet_add","display_name" => "スタイルシート作成","type" => SPConst::RESTRICT_TYPE_ACTIVE_NONACTIVE);
$auth_setting_list[] = array("name" => "ope_auth_stylesheet_edit","display_name" => "スタイルシート編集","type" => SPConst::RESTRICT_TYPE_ACTIVE_NONACTIVE);
$auth_setting_list[] = array("name" => "ope_auth_stylesheet_delete","display_name" => "スタイルシート削除","type" => SPConst::RESTRICT_TYPE_ACTIVE_NONACTIVE);
$auth_setting_list[] = array("name" => "ope_auth_stylesheet_workflow","display_name" => "スタイルシートワークフロー実行","type" => SPConst::RESTRICT_TYPE_ACTIVE_NONACTIVE);
$auth_setting_list[] = array("name" => "ope_auth_stylesheet_publish","display_name" => "スタイルシート公開","type" => SPConst::RESTRICT_TYPE_ACTIVE_NONACTIVE);
$auth_setting_list[] = array("name" => "ope_auth_stylesheet_archive","display_name" => "スタイルシートアーカイブ","type" => SPConst::RESTRICT_TYPE_ACTIVE_NONACTIVE);
$auth_setting_list[] = array("name" => "ope_auth_script_view","display_name" => "スクリプト閲覧","type" => SPConst::RESTRICT_TYPE_ACTIVE_NONACTIVE);
$auth_setting_list[] = array("name" => "ope_auth_script_add","display_name" => "スクリプト作成","type" => SPConst::RESTRICT_TYPE_ACTIVE_NONACTIVE);
$auth_setting_list[] = array("name" => "ope_auth_script_edit","display_name" => "スクリプト編集","type" => SPConst::RESTRICT_TYPE_ACTIVE_NONACTIVE);
$auth_setting_list[] = array("name" => "ope_auth_script_delete","display_name" => "スクリプト削除","type" => SPConst::RESTRICT_TYPE_ACTIVE_NONACTIVE);
$auth_setting_list[] = array("name" => "ope_auth_script_workflow","display_name" => "スクリプトワークフロー実行","type" => SPConst::RESTRICT_TYPE_ACTIVE_NONACTIVE);
$auth_setting_list[] = array("name" => "ope_auth_script_publish","display_name" => "スクリプト公開","type" => SPConst::RESTRICT_TYPE_ACTIVE_NONACTIVE);
$auth_setting_list[] = array("name" => "ope_auth_script_archive","display_name" => "スクリプトアーカイブ","type" => SPConst::RESTRICT_TYPE_ACTIVE_NONACTIVE);
$auth_setting_list[] = array("name" => "ope_auth_user_self","display_name" => "自部署ユーザ操作","type" => SPConst::RESTRICT_TYPE_ACTIVE_NONACTIVE);
$auth_setting_list[] = array("name" => "ope_auth_user_other","display_name" => "他部署ユーザ操作","type" => SPConst::RESTRICT_TYPE_ACTIVE_NONACTIVE);
$auth_setting_list[] = array("name" => "ope_auth_usergroup","display_name" => "ユーザグループ操作","type" => SPConst::RESTRICT_TYPE_ACTIVE_NONACTIVE);
$auth_setting_list[] = array("name" => "ope_auth_authsetting","display_name" => "権限マスタ操作","type" => SPConst::RESTRICT_TYPE_ACTIVE_NONACTIVE);
$auth_setting_list[] = array("name" => "ope_auth_workflowsetting","display_name" => "ワークフローマスタ操作","type" => SPConst::RESTRICT_TYPE_ACTIVE_NONACTIVE);
$auth_setting_list[] = array("name" => "ope_auth_website","display_name" => "ウェブサイト全体設定操作","type" => SPConst::RESTRICT_TYPE_ACTIVE_NONACTIVE);
$auth_setting_list[] = array("name" => "ope_auth_ext1","display_name" => "追加機能操作権限1","type" => SPConst::RESTRICT_TYPE_ACTIVE_NONACTIVE);
$auth_setting_list[] = array("name" => "ope_auth_ext2","display_name" => "追加機能操作権限2","type" => SPConst::RESTRICT_TYPE_ACTIVE_NONACTIVE);
$auth_setting_list[] = array("name" => "ope_auth_ext3","display_name" => "追加機能操作権限3","type" => SPConst::RESTRICT_TYPE_ACTIVE_NONACTIVE);
$auth_setting_list[] = array("name" => "ope_auth_ext4","display_name" => "追加機能操作権限4","type" => SPConst::RESTRICT_TYPE_ACTIVE_NONACTIVE);
$auth_setting_list[] = array("name" => "ope_auth_ext5","display_name" => "追加機能操作権限5","type" => SPConst::RESTRICT_TYPE_ACTIVE_NONACTIVE);
$auth_setting_list[] = array("name" => "ope_auth_ext6","display_name" => "追加機能操作権限6","type" => SPConst::RESTRICT_TYPE_ACTIVE_NONACTIVE);
$auth_setting_list[] = array("name" => "ope_auth_ext7","display_name" => "追加機能操作権限7","type" => SPConst::RESTRICT_TYPE_ACTIVE_NONACTIVE);
$auth_setting_list[] = array("name" => "ope_auth_ext8","display_name" => "追加機能操作権限8","type" => SPConst::RESTRICT_TYPE_ACTIVE_NONACTIVE);
$auth_setting_list[] = array("name" => "ope_auth_ext9","display_name" => "追加機能操作権限9","type" => SPConst::RESTRICT_TYPE_ACTIVE_NONACTIVE);
$auth_setting_list[] = array("name" => "ope_auth_ext10","display_name" => "追加機能操作権限10","type" => SPConst::RESTRICT_TYPE_ACTIVE_NONACTIVE);
$auth_setting_list[] = array("name" => "ope_auth_ext11","display_name" => "追加機能操作権限11","type" => SPConst::RESTRICT_TYPE_ACTIVE_NONACTIVE);
$auth_setting_list[] = array("name" => "ope_auth_ext12","display_name" => "追加機能操作権限12","type" => SPConst::RESTRICT_TYPE_ACTIVE_NONACTIVE);
$auth_setting_list[] = array("name" => "ope_auth_ext13","display_name" => "追加機能操作権限13","type" => SPConst::RESTRICT_TYPE_ACTIVE_NONACTIVE);
$auth_setting_list[] = array("name" => "ope_auth_ext14","display_name" => "追加機能操作権限14","type" => SPConst::RESTRICT_TYPE_ACTIVE_NONACTIVE);
$auth_setting_list[] = array("name" => "ope_auth_ext15","display_name" => "追加機能操作権限15","type" => SPConst::RESTRICT_TYPE_ACTIVE_NONACTIVE);
$auth_setting_list[] = array("name" => "ope_auth_ext16","display_name" => "追加機能操作権限16","type" => SPConst::RESTRICT_TYPE_ACTIVE_NONACTIVE);
$auth_setting_list[] = array("name" => "ope_auth_ext17","display_name" => "追加機能操作権限17","type" => SPConst::RESTRICT_TYPE_ACTIVE_NONACTIVE);
$auth_setting_list[] = array("name" => "ope_auth_ext18","display_name" => "追加機能操作権限18","type" => SPConst::RESTRICT_TYPE_ACTIVE_NONACTIVE);
$auth_setting_list[] = array("name" => "ope_auth_ext19","display_name" => "追加機能操作権限19","type" => SPConst::RESTRICT_TYPE_ACTIVE_NONACTIVE);
$auth_setting_list[] = array("name" => "ope_auth_ext20","display_name" => "追加機能操作権限20","type" => SPConst::RESTRICT_TYPE_ACTIVE_NONACTIVE);

//リクエストパラメータ取得
$mode = isset($_REQUEST["mode"]) ? $_REQUEST["mode"] : "";																					//起動モード
$action = isset($_REQUEST["action"]) ? $_REQUEST["action"] : "";																			//実行アクション
$close = isset($_REQUEST["close"]) ? $_REQUEST["close"] : "";																				//閉じるフラグ
$message = isset($_REQUEST["message"]) ? $_REQUEST["message"] : $message;																	//メッセージ
$operationauth_id = isset($_REQUEST["operationauth_id"]) ? $_REQUEST["operationauth_id"] : "";												//機能操作権限ID
$operationauth_name = isset($_REQUEST["operationauth_name"]) ? $_REQUEST["operationauth_name"] : "";										//機能操作権限名

//権限設定リクエストパラメータ取得
$auth_setting = array();
for($i=0;$i<count($auth_setting_list);$i++){
	$auth_setting[$auth_setting_list[$i]["name"]] = isset($_REQUEST[$auth_setting_list[$i]["name"]]) ? $_REQUEST[$auth_setting_list[$i]["name"]] : "";
}

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

//入力値チェック
if(($mode == "new" || $mode == "edit") && $action == "save"){

}

//保存処理
if(($mode == "new" || $mode == "edit") && $action == "save" && $error == array()){
	DB::beginTransaction();

	$OperationAuth = new OperationAuth();						//機能操作権限クラス

	//新規追加の場合データを追加
	if($mode == "new"){
		$insertData = array();
		$insertData["active_flg"] = "1";
		if(!$OperationAuth->insert($insertData)){
			DB::rollBack();
			Logger::error("機能操作権限新規追加に失敗しました。",$insertData);
			Location::redirect($redirect);
		}
		//機能操作権限IDを取得
		$operationauth_id = $OperationAuth->last_insert_id();
	}

	//共通更新条件
	$where = array("operationauth_id" => $operationauth_id);					//更新条件

	//更新データ設定
	$saveData = array();
	$saveData["operationauth_name"] = $operationauth_name;						//機能操作権限名

	//権限設定データ設定
	for($i=0;$i<count($auth_setting_list);$i++){
		$saveData[$auth_setting_list[$i]["name"]] = $auth_setting[$auth_setting_list[$i]["name"]];
	}

	$saveData["active_flg"] = 1;												//有効

	//新規追加の場合は、IDをソート順として初期設定
	if($mode == "new"){
		$saveData["sort_no"] = $operationauth_id;
		$saveData["created"] = time();
		$saveData["created_by"] = $session->user["user_id"];
	}

	$saveData["updated"] = time();
	$saveData["updated_by"] = $session->user["user_id"];

	//データ更新実行
	if(!$OperationAuth->update($where, $saveData)){
		DB::rollBack();
		Logger::error("機能操作権限更新に失敗しました。",$saveData);
		Location::redirect($redirect);
	}

	DB::commit();

	if($close == "on"){
		//一覧画面に遷移する
		Location::redirect("list.php");
	}else{
		//同画面に遷移する
		$redirectParam["operationauth_id"] = $operationauth_id;
		$redirectParam["mode"] = "edit";
		$redirectParam["message[]"] = "保存しました。";
		Location::redirect($self,$redirectParam);
	}
}elseif($mode == "delete" && $action == "delete" && $error == array()){		//削除処理
	DB::beginTransaction();

	$OperationAuth = new OperationAuth();							//コンテンツ操作権限クラス

	//共通削除条件
	$where = array("operationauth_id" => $operationauth_id);		//削除条件

	//機能操作権限データ削除
	if(!$OperationAuth->delete($where)){
		DB::rollBack();
		Logger::error("機能操作権限削除に失敗しました。",$where);
		Location::redirect($redirect);
	}

	DB::commit();

	//一覧画面に遷移する
	Location::redirect("list.php");
}

//初期表示情報取得
if(($mode == "edit" || $mode == "delete") && $action == ""){		//編集モードで初期表示の場合

	//初期表示データ取得
	$OperationAuth = new OperationAuth();
	$initData = $OperationAuth->getDataByParameters(array("operationauth_id" => $operationauth_id));

	if(!$initData){
		//初期表示データが取得できない場合
		Logger::error("機能操作権限初期表示データ取得に失敗しました。",array("operationauth_id" => $operationauth_id));
		Location::redirect("../../index.php");
	}

	$operationauth_name = $initData["operationauth_name"];			//機能操作権限名

	//権限設定データ取得
	$auth_setting = array();
	for($i=0;$i<count($auth_setting_list);$i++){
		$auth_setting[$auth_setting_list[$i]["name"]] = $initData[$auth_setting_list[$i]["name"]];
	}

}elseif($mode == "new" && $action == ""){		//新規追加モードで初期表示の場合
	//処理なし
}
//Debug::arrayCheck($auth_setting_list);
//Debug::arrayCheck($auth_setting);

//表示用配列設定
$restrict_active_nonactive = Options::restrict_active_nonactive();
$restrict_level = Options::restrict_level();

$LayoutManager = new LayoutManagerAdminMain();
$LayoutManager->setTitle("機能操作権限編集");
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
<input type="hidden" name="operationauth_id" value="<?php echo htmlspecialchars($operationauth_id); //コンテンツ操作権限ID ?>" />
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
<li><a href="#tabs-1" class="tabmenu">機能操作権限設定</a></li>
</ul>

<!-- 基本設定タブ領域開始 -->
<div id="tabs-1" class="tab_area">
<h1>基本設定</h1>
<table class="content_input_table">
<?php if($restrict["all"] >= SPConst::RESTRICT_READONLY): ?>
	<tr>
	<th>機能操作権限名</th>
	<td>
	<?php echo UIParts::middleText("operationauth_name",$operationauth_name,$restrict["all"]); ?>
	</td>
	</tr>
	<?php
	for($i=0;$i<count($auth_setting_list);$i++){
		echo '<tr>';
		echo '<th>'.$auth_setting_list[$i]["display_name"].'</th>';
		echo '<td>';
		if($auth_setting_list[$i]["type"] == SPConst::RESTRICT_TYPE_ACTIVE_NONACTIVE){
			$list = $restrict_active_nonactive;
		}elseif($auth_setting_list[$i]["type"] == SPConst::RESTRICT_TYPE_OPERATION_LEVEL){
			$list = $restrict_level;
		}
		echo UIParts::radio($auth_setting_list[$i]["name"], $list, $auth_setting[$auth_setting_list[$i]["name"]],$restrict["all"]);
		echo '</td>';
		echo '</tr>';
	}
	?>
<?php endif; ?>
</table>
</div>
<!-- 基本設定タブ領域終了 -->

</div>
</form>
<?php echo $LayoutManager->footer(); ?>