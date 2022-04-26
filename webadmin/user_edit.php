<?php
// アプリケーション共通クラス読込
require_once(dirname(__FILE__).'/ApplicationCommon/include.php');

require_once(dirname(__FILE__).'/DataAccess/User.php'); 			//ユーザクラス
require_once(dirname(__FILE__).'/DataAccess/UserGroup.php'); 		//ユーザグループクラス
require_once(dirname(__FILE__).'/DataAccess/UserUserGroup.php'); 	//ユーザ-ユーザグループ紐付クラス

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
$mode = isset($_REQUEST["mode"]) ? $_REQUEST["mode"] : "";																					//起動モード
$action = isset($_REQUEST["action"]) ? $_REQUEST["action"] : "";																			//実行アクション
$close = isset($_REQUEST["close"]) ? $_REQUEST["close"] : "";																				//閉じるフラグ
$close_url = isset($_REQUEST["close_url"]) ? $_REQUEST["close_url"] : "";																	//戻り先URL
$message = isset($_REQUEST["message"]) ? $_REQUEST["message"] : $message;																	//メッセージ
$user_id = isset($_REQUEST["user_id"]) ? $_REQUEST["user_id"] : "";																			//ユーザID
$usergroup_id = isset($_REQUEST["usergroup_id"]) ? $_REQUEST["usergroup_id"] : "";															//ユーザグループID
$login_id = isset($_POST["login_id"]) ? Util::encodeRequest($_POST["login_id"]) : "";														//ログインID
$password = isset($_POST["password"]) ? Util::encodeRequest($_POST["password"]) : "";														//パスワード
$password_check = isset($_POST["password_check"]) ? Util::encodeRequest($_POST["password_check"]) : "";										//パスワード(確認用)
$name = isset($_POST["name"]) ? Util::encodeRequest($_POST["name"]) : "";																	//ユーザ名
$name_kana = isset($_POST["name_kana"]) ? Util::encodeRequest($_POST["name_kana"]) : "";													//ユーザ名(カナ)
$mail = isset($_POST["mail"]) ? Util::encodeRequest($_POST["mail"]) : "";																	//メールアドレス
$admin_flg = isset($_POST["admin_flg"]) ? $_POST["admin_flg"] : "";																			//管理者フラグ
$admintype = isset($_POST["admintype"]) ? $_POST["admintype"] : "";																			//管理者種別
$active_flg = isset($_POST["active_flg"]) ? $_POST["active_flg"] : "";																		//有効/無効

//起動モードが設定されていない場合
if(Util::IsNullOrEmpty($mode)){
	Logger::notice("起動モードが設定されていませんでした。");
	Location::redirect($redirect);
}

//ユーザクラス
$User = new User();


//入力値チェック
if(($mode == "new" || $mode == "edit") && $action == "save"){
	if($name == ""){
		$error[] = "ユーザ名を入力してください。";
	}
	if($login_id == ""){
		$error[] = "ログインIDを入力してください。";
	}else{
		$where = array();
		$where[] = array("login_id",$login_id);
		if($mode == "edit"){
			$where[] = array("user_id",$user_id,"!=");
		}
		$cnt = $User->getCountByParameters($where);
		if($cnt > 0){
			$error[] = "ログインIDが別のユーザと重複しています。";
		}
	}
	if($password != "" && $password_check != ""){
		if($password == ""){
			$error[] = "パスワードを入力してください。";
		}else if($password_check == ""){
			$error[] = "パスワード(確認)を入力してください。";
		}
	}
}else if($action == "delete"){
	$cnt = $User->getCountByParameters();
	if($cnt <= 1){
		$error[] = "全てのユーザを削除することはできません。";
	}else{
		$where = array();
		$where[] = array("user_id",$user_id,"<>");
		$where[] = array("admintype","1");
		$cnt = $User->getCountByParameters($where);
		if($cnt == 0){
			$error[] = "本ユーザを削除すると、システム管理者が0名となってしまうため、削除することはできません。";
		}
	}
}


//保存処理
if(($mode == "new" || $mode == "edit") && $action == "save" && $error == array()){
	$now_timestamp = time();

	DB::beginTransaction();

	//ユーザグループ紐付クラス
	$UserUserGroup = new UserUserGroup();

	//新規追加の場合データを追加
	if($mode == "new"){
		$insertData = array();
		$insertData["admin_flg"] = 1;
		$insertData["language"] = "jp";
		$insertData["sort_no"] = $User->getMaxSort();
		$insertData["created"] = $now_timestamp;
		$insertData["created_by"] = $session->user["user_id"];
		if(!$User->insert($insertData)){
			DB::rollBack();
			Logger::error("ユーザ追加に失敗しました。",$insertData);
			Location::redirect($redirect);
		}

		//ユーザIDを取得
		$user_id = $User->last_insert_id();

		//ユーザグループ紐付作成
		$insertData2 = array();
		$insertData2["user_id"] = $user_id;
		$insertData2["usergroup_id"] = 0;
		$insertData2["maingroup_flg"] = 1;
		$insertData2["created"] = $now_timestamp;
		$insertData2["created_by"] = $session->user["user_id"];
		if(!$UserUserGroup->insert($insertData2)){
			DB::rollBack();
			Logger::error("ユーザグループ紐付け追加に失敗しました。",$insertData2);
			Location::redirect($redirect);
		}
	}

	//共通更新条件
	$where = array("user_id" => $user_id);							//更新条件

	//ユーザ更新データ設定
	$saveData = array();
	$saveData["login_id"] = $login_id;
	if($password != ""){
		$saveData["password"] = Util::makePasswordHashCode($password);
	}

	$saveData["name"] = $name;
	$saveData["name_kana"] = $name_kana;
	$saveData["mail"] = $mail;
	$saveData["admin_flg"] = $admin_flg;
	$saveData["admintype"] = $admintype;
	$saveData["active_flg"] = $active_flg;

	//ユーザ更新実行
	$saveData["updated"] = $now_timestamp;
	$saveData["updated_by"] = $session->user["user_id"];
	if(!$User->update($where, $saveData)){
		DB::rollBack();
		Logger::error("ユーザ更新に失敗しました。",$saveData);
		Location::redirect($redirect);
	}

	//ユーザグループ紐付更新
	$userUserGroupData = array();
	$userUserGroupData["usergroup_id"] = $usergroup_id;
	$userUserGroupData["maingroup_flg"] = 1;
	$userUserGroupData["updated"] = $now_timestamp;
	$userUserGroupData["updated_by"] = $session->user["user_id"];
	if(!$UserUserGroup->update(array("user_id" => $user_id,"maingroup_flg" => 1), $userUserGroupData)){
		DB::rollBack();
		Logger::error("ユーザグループ紐付け更新に失敗しました。",$saveData);
		Location::redirect($redirect);
	}

	DB::commit();

	if($close == "on"){
		//一覧画面に遷移する
		Location::redirect($close_url);
	}else{
		//同画面に遷移する
		$redirectParam["user_id"] = $user_id;
		$redirectParam["close_url"] = $close_url;
		$redirectParam["mode"] = "edit";
		$redirectParam["message[]"] = "保存しました。";
		Location::redirect($self,$redirectParam);
	}
}

//削除処理
if($mode == "edit" && $action == "delete" && $error == array()){
	DB::beginTransaction();
	$User->deleteUser($user_id);
	DB::commit();

	//一覧画面に遷移する
	Location::redirect($close_url);
}

//初期表示
if($action == ""){
	if($mode == "edit"){
		//ユーザデータを取得
		$userData = $User->getUserDataForEdit($user_id);

		$login_id = $userData["login_id"];
		$name = $userData["name"];
		$name_kana = $userData["name_kana"];
		$mail = $userData["mail"];
		$admintype = $userData["admintype"];
		$admin_flg = $userData["admin_flg"];
		$active_flg = $userData["active_flg"];

		//ユーザグループ紐付クラス
		$UserUserGroup = new UserUserGroup();
		$userUserGroupData = $UserUserGroup->getDataByParameters(array("user_id" => $user_id,"maingroup_flg" => 1));
		$usergroup_id = $userUserGroupData["usergroup_id"];

	}else if($mode == "new"){
		$admin_flg = 0;
		$admintype = 0;
		$active_flg = 1;
	}
}

//ユーザグループ一覧
$UserGroup = new UserGroup();
$userGroupList = $UserGroup->getUserGroupList();


$LayoutManager = new LayoutManagerAdminMain();
$LayoutManager->setTitle("ユーザ編集");
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
<input type="hidden" name="user_id" value="<?php echo htmlspecialchars($user_id); //ユーザID ?>" />
<input type="hidden" name="mode" value="<?php echo htmlspecialchars($mode); //動作モード ?>" />
<input type="hidden" name="close_url" value="<?php echo htmlspecialchars($close_url); //戻り先URL ?>" />
<input type="hidden" name="action" value="<?php //javascriptから設定 ?>" />
<input type="hidden" name="close" value="<?php //javascriptから設定 ?>" />
<table class="edit" cellspacing="0">
    <tr>
    <th class="w240">ユーザ名<span class="mark orange">必須</span></th>
    <td>
    <input type="text" name="name" value="<?php echo htmlspecialchars($name);?>" />
    </td>
    </tr>

    <tr>
    <th>ユーザ名(カナ)</th>
    <td>
    <input type="text" name="name_kana" value="<?php echo htmlspecialchars($name_kana);?>" />
    </td>
    </tr>

    <tr>
    <th>ログインID<span class="mark orange">必須</span></th>
    <td>
    <input type="text" name="login_id" value="<?php echo htmlspecialchars($login_id);?>" />
    </td>
    </tr>

    <tr>
    <th>パスワード</th>
    <td>
    <input type="text" name="password" value="<?php echo htmlspecialchars($password);?>" autocomplete="off" />
    <?php if($mode == "edit"): ?>
    	<br /><span class="info">変更する場合のみ入力</span>
    <?php endif;?>
    </td>
    </tr>

    <tr>
    <th>パスワード(確認)</th>
    <td>
    <input type="text" name="password_check" value="<?php echo htmlspecialchars($password_check);?>" autocomplete="off"  />
    <?php if($mode == "edit"): ?>
    	<br /><span class="info">変更する場合のみ入力</span>
    <?php endif;?>
    </td>
    </tr>

    <tr>
    <th>ユーザグループ<span class="mark orange">必須</span></th>
    <td>
    <select name="usergroup_id">
    <?php

    for($i=0;$i<count($userGroupList);$i++){
		if($usergroup_id == $userGroupList[$i]["usergroup_id"]){
			$selected = "selected";
		}else{
			$selected = "";
		}
		echo '<option value="'.$userGroupList[$i]["usergroup_id"].'" '.$selected.'>'.$userGroupList[$i]["usergroup_name"]."</option>";
	}
    ?>
    </select>
    </td>
    </tr>

    <tr>
    <th>メールアドレス<span class="mark orange">必須</span></th>
    <td>
    <input type="text" name="mail" value="<?php echo htmlspecialchars($mail);?>" />
    </td>
    </tr>

    <tr>
    <th>一般/管理者区分<span class="mark orange">必須</span></th>
    <td>
    <?php
    if($admin_flg == "0"){ $checked = "checked"; }else { $checked = ""; }
    echo '<input type="radio" name="admin_flg" value="0" '.$checked.' onchange="changeAdminFlg($(this).val());">一般(管理画面ログイン不可)&nbsp;';
    if($admin_flg == "1"){ $checked = "checked"; }else { $checked = ""; }
    echo '<input type="radio" name="admin_flg" value="1" '.$checked.' onchange="changeAdminFlg($(this).val());">管理者(管理画面ログイン可)';
    ?>
    <script>
	function changeAdminFlg(state){
		if(state == "0"){
			$("#admintype_tr").css("display","none");
		}else{
			$("#admintype_tr").css("display","");
		}
	}
    </script>
    </td>
    </tr>

    <tr id="admintype_tr">
    <th>管理者種別<span class="mark orange">必須</span></th>
    <td>
    <?php
    if($admintype == "1"){ $checked = "checked"; }else { $checked = ""; }
    echo '<input type="radio" name="admintype" value="1" '.$checked.'>システム管理者&nbsp;';
    if($admintype == "0"){ $checked = "checked"; }else { $checked = ""; }
    echo '<input type="radio" name="admintype" value="0" '.$checked.'>一般管理者';
    ?>
    <script>
	if($("input[name=admin_flg]:checked").val() == "0"){
		$("#admintype_tr").css("display","none");
    }
    </script>
    </td>
    </tr>

    <tr>
    <th>状態<span class="mark orange">必須</span></th>
    <td>
    <?php
    if($active_flg == "1"){ $checked = "checked"; }else { $checked = ""; }
    echo '<input type="radio" name="active_flg" value="1" '.$checked.'>有効&nbsp;';
    if($active_flg == "0"){ $checked = "checked"; }else { $checked = ""; }
    echo '<input type="radio" name="active_flg" value="0" '.$checked.'>無効';
    ?>
    </td>
    </tr>

</table>
</form>
<?php $LayoutManager->footer(); ?>
