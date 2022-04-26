<?php
// アプリケーション共通クラス読込
require_once(dirname(__FILE__).'/ApplicationCommon/include.php');

require_once(dirname(__FILE__).'/DataAccess/UserGroup.php'); 		//ユーザグループクラス
require_once(dirname(__FILE__).'/DataAccess/OperationAuth.php'); 	//機能操作権限クラス

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
$usergroup_id = isset($_REQUEST["usergroup_id"]) ? Util::encodeRequest($_REQUEST["usergroup_id"]) : "";						//ユーザグループID
$usergroup_name = isset($_REQUEST["usergroup_name"]) ? Util::encodeRequest($_REQUEST["usergroup_name"]) : "";				//ユーザグループ名
$operationauth_id = isset($_REQUEST["operationauth_id"]) ? Util::encodeRequest($_REQUEST["operationauth_id"]) : "";			//機能操作権限種別ID

//起動モードが設定されていない場合
if(Util::IsNullOrEmpty($mode)){
	Logger::notice("起動モードが設定されていませんでした。");
	Location::redirect($redirect);
}

//ユーザグループクラス
$UserGroup = new UserGroup();

//入力値チェック
if(($mode == "new" || $mode == "edit") && $action == "save"){
	if($usergroup_name == ""){
		$error[] = "ユーザグループ名を入力してください。";
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
		$insertData["sort_no"] = $UserGroup->getMaxByParameters("usergroup_id") + 1;
		$insertData["created"] = $now_timestamp;
		$insertData["created_by"] = $session->user["user_id"];
		if(!$UserGroup->insert($insertData)){
			DB::rollBack();
			Logger::error("ユーザグループ追加に失敗しました。",$insertData);
			Location::redirect($redirect);
		}
		//IDを取得
		$usergroup_id = $UserGroup->last_insert_id();
	}

	//共通更新条件
	$where = array("usergroup_id" => $usergroup_id);				//更新条件

	//フォルダ更新データ設定
	$saveData = array();
	$saveData["usergroup_name"] = $usergroup_name;
	$saveData["operationauth_id"] = $operationauth_id;

	//ドメイン更新実行
	$saveData["updated"] = $now_timestamp;
	$saveData["updated_by"] = $session->user["user_id"];
	if(!$UserGroup->update($where, $saveData)){
		DB::rollBack();
		Logger::error("フォルダ更新に失敗しました。",$saveData);
		Location::redirect($redirect);
	}

	DB::commit();

	if($close == "on"){
		//一覧画面に遷移する
		$redirectParam["master_table"] = "usergroup";
		Location::redirect($close_url,$redirectParam);
	}else{
		//同画面に遷移する
		$redirectParam["usergroup_id"] = $usergroup_id;
		$redirectParam["close_url"] = $close_url;
		$redirectParam["mode"] = "edit";
		$redirectParam["message[]"] = "保存しました。";
		Location::redirect($self,$redirectParam);
	}
}

//削除処理
if($mode == "edit" && $action == "delete" && $error == array()){
	DB::beginTransaction();
	$UserGroup->delete(array("usergroup_id" => $usergroup_id));
	DB::commit();

	//一覧画面に遷移する
	$redirectParam["master_table"] = "usergroup";
	Location::redirect($close_url,$redirectParam);
}

//初期表示
if($action == ""){
	if($mode == "edit"){
		//ドメインデータを取得
		$usergroupData = $UserGroup->getDataByPrimaryKey($usergroup_id);
		$usergroup_name = $usergroupData["usergroup_name"];
		$operationauth_id = $usergroupData["operationauth_id"];
	}
}

//機能操作権限一覧
$OperationAuth = new OperationAuth();
$operationAuthList = $OperationAuth->getListByParameters();

$LayoutManager = new LayoutManagerAdminMain();
$LayoutManager->setTitle("ユーザグループ編集");
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
<input type="hidden" name="master_table" value="<?php echo "usergroup"; //マスタ選択画面区分 ?>" />
<input type="hidden" name="usergroup_id" value="<?php echo $usergroup_id; //ID ?>" />
<input type="hidden" name="action" value="<?php //javascriptから設定 ?>" />
<input type="hidden" name="close" value="<?php //javascriptから設定 ?>" />
<table class="edit" cellspacing="0">
    <tr>
    <th class="w240">ユーザグループ名<span class="mark orange">必須</span></th>
    <td>
    <input type="text" name="usergroup_name" value="<?php echo htmlspecialchars($usergroup_name);?>" />
    </td>
    </tr>

    <tr>
    <th>機能操作権限種別</th>
    <td>
    <select name="operationauth_id">
    <option value=""></option>
	<?php
	foreach($operationAuthList as $operationAuthData){
		if($operationAuthData["operationauth_id"] == $operationauth_id){ $selected = "selected"; }else{ $selected = ""; }
		echo '<option value="'.$operationAuthData["operationauth_id"].'" '.$selected.'>'.$operationAuthData["operationauth_name"].'</option>';
	}
	?>
    </select>
    </td>
    </tr>

</table>
</form>
<?php $LayoutManager->footer(); ?>
