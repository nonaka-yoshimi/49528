<?php
//アプリケーション共通クラス読込
require_once(dirname(__FILE__).'/../../ApplicationCommon/include.php');
require_once(dirname(__FILE__).'/../../DataAccess/UserType.php'); 				//ユーザ種別クラス

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
$session->loginCheckAndRedirect("../login.php?msg=session_error");

//リクエストパラメータ取得
$mode = isset($_REQUEST["mode"]) ? $_REQUEST["mode"] : "";																					//起動モード
$action = isset($_REQUEST["action"]) ? $_REQUEST["action"] : "";																			//実行アクション
$close = isset($_REQUEST["close"]) ? $_REQUEST["close"] : "";																				//閉じるフラグ
$message = isset($_REQUEST["message"]) ? $_REQUEST["message"] : $message;																	//メッセージ
$usertype_id = isset($_REQUEST["usertype_id"]) ? $_REQUEST["usertype_id"] : "";																//ユーザ種別ID
$usertype_name = isset($_REQUEST["usertype_name"]) ? $_REQUEST["usertype_name"] : "";														//ユーザ種別名

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

	$UserType = new UserType();						//ユーザ種別クラス

	//新規追加の場合データを追加
	if($mode == "new"){
		$insertData = array();
		$insertData["active_flg"] = "1";
		if(!$UserType->insert($insertData)){
			DB::rollBack();
			Logger::error("ユーザ種別新規追加に失敗しました。",$insertData);
			Location::redirect($redirect);
		}
		//ユーザ種別IDを取得
		$usertype_id = $UserType->last_insert_id();
	}

	//共通更新条件
	$where = array("usertype_id" => $usertype_id);								//更新条件

	//更新データ設定
	$saveData = array();
	$saveData["usertype_name"] = $usertype_name;								//ユーザ種別名
	$saveData["active_flg"] = 1;												//有効

	//新規追加の場合は、IDをソート順として初期設定
	if($mode == "new"){
		$saveData["sort_no"] = $usertype_id;
		$saveData["created"] = time();
		$saveData["created_by"] = $session->user["user_id"];
	}

	$saveData["updated"] = time();
	$saveData["updated_by"] = $session->user["user_id"];

	//データ更新実行
	if(!$UserType->update($where, $saveData)){
		DB::rollBack();
		Logger::error("ユーザ種別更新に失敗しました。",$saveData);
		Location::redirect($redirect);
	}

	DB::commit();

	if($close == "on"){
		//一覧画面に遷移する
		Location::redirect("list.php");
	}else{
		//同画面に遷移する
		$redirectParam["usertype_id"] = $usertype_id;
		$redirectParam["mode"] = "edit";
		$redirectParam["message[]"] = "保存しました。";
		Location::redirect($self,$redirectParam);
	}

}elseif($mode == "delete" && $action == "delete" && $error == array()){		//削除処理
	DB::beginTransaction();

	$UserType = new UserType();						//ユーザ種別クラス

	//共通削除条件
	$where = array("usertype_id" => $usertype_id);	//削除条件

	//ユーザデータ削除
	if(!$UserType->delete($where)){
		DB::rollBack();
		Logger::error("ユーザ種別削除に失敗しました。",$where);
		Location::redirect($redirect);
	}

	DB::commit();

	//一覧画面に遷移する
	Location::redirect("list.php");
}

//初期表示情報取得
if(($mode == "edit" || $mode == "delete") && $action == ""){		//編集モードで初期表示の場合

	//初期表示データ取得
	$UserType = new UserType();
	$initData = $UserType->getDataByParameters(array("usertype_id" => $usertype_id));

	if(!$initData){
		//初期表示データが取得できない場合
		Logger::error("ユーザ種別初期表示データ取得に失敗しました。",array("usertype_id" => $usertype_id));
		Location::redirect("../index.php");
	}

	$usertype_name = $initData["usertype_name"];							//ユーザ種別名

}elseif($mode == "new" && $action == ""){		//新規追加モードで初期表示の場合
	//処理なし
}

$LayoutManager = new LayoutManagerAdminMain();
$LayoutManager->setTitle("ユーザ種別編集");
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
<input type="hidden" name="usertype_id" value="<?php echo htmlspecialchars($usertype_id); //ユーザ種別ID ?>" />
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
<li><a href="#tabs-1" class="tabmenu">ユーザ種別設定</a></li>
</ul>

<!-- コンテンツタブ領域開始 -->
<div id="tabs-1" class="tab_area">
<h1>ユーザ種別設定</h1>
<table class="content_input_table">
<?php if($restrict["all"] >= SPConst::RESTRICT_READONLY): ?>
	<tr>
	<th>ユーザ種別名</th>
	<td>
	<?php echo UIParts::middleText("usertype_name",$usertype_name,$restrict["all"]); ?>
	</td>
	</tr>
<?php endif; ?>
</table>
</div>
<!-- コンテンツタブ領域終了 -->

</div>
</form>
<?php echo $LayoutManager->footer(); ?>