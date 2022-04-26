<?php
//アプリケーション共通クラス読込
require_once(dirname(__FILE__).'/../ApplicationCommon/include.php');
require_once(dirname(__FILE__).'/../DataAccess/User.php'); 					//ユーザクラス
require_once(dirname(__FILE__).'/../DataAccess/UserType.php'); 				//ユーザ種別クラス
require_once(dirname(__FILE__).'/../DataAccess/UserGroup.php'); 			//ユーザグループクラス
require_once(dirname(__FILE__).'/../DataAccess/UserUserGroup.php'); 		//ユーザ-ユーザグループ紐付クラス

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
$user_id = isset($_REQUEST["user_id"]) ? $_REQUEST["user_id"] : "";																			//ユーザID
$usergroup_id = isset($_REQUEST["usergroup_id"]) ? $_REQUEST["usergroup_id"] : "";															//呼出ユーザグループID
$usergroup = isset($_REQUEST["usergroup"]) ? $_REQUEST["usergroup"] : "";																	//所属ユーザグループ一覧
$name = isset($_POST["name"]) ? Util::encodeRequest($_POST["name"]) : "";																	//氏名
$name_kana = isset($_POST["name_kana"]) ? Util::encodeRequest($_POST["name_kana"]) : "";													//氏名（カナ）
$login_id = isset($_POST["login_id"]) ? Util::encodeRequest($_POST["login_id"]) : "";														//ログインID
$password = isset($_POST["password"]) ? Util::encodeRequest($_POST["password"]) : "";														//パスワード
$mail = isset($_POST["mail"]) ? Util::encodeRequest($_POST["mail"]) : "";																	//メールアドレス
$usertype_id = isset($_POST["usertype_id"]) ? $_POST["usertype_id"] : "";																	//ユーザ種別ID
$admin_flg = isset($_POST["admin_flg"]) ? $_POST["admin_flg"] : "";																			//管理者フラグ
$admintype = isset($_POST["admintype"]) ? $_POST["admintype"] : "";																			//管理者タイプ
$firstauth_code = isset($_POST["firstauth_code"]) ? Util::encodeRequest($_POST["firstauth_code"]) : "";										//初回認証コード
$language = isset($_POST["language"]) ? $_POST["language"] : "";																			//使用言語
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
	$restrict["name"] = SPConst::RESTRICT_READONLY;
	$restrict["name_kana"] = SPConst::RESTRICT_READONLY;
	$restrict["login_id"] = SPConst::RESTRICT_READONLY;
	$restrict["password"] = SPConst::RESTRICT_READONLY;
	$restrict["mail"] = SPConst::RESTRICT_READONLY;
	$restrict["usertype_id"] = SPConst::RESTRICT_READONLY;
	$restrict["admin_flg"] = SPConst::RESTRICT_READONLY;
	$restrict["admintype"] = SPConst::RESTRICT_READONLY;
	$restrict["usergroup"] = SPConst::RESTRICT_READONLY;
	$restrict["firstauth_code"] = SPConst::RESTRICT_READONLY;
	$restrict["language"] = SPConst::RESTRICT_READONLY;
	$restrict["start_time"] = SPConst::RESTRICT_READONLY;
	$restrict["end_time"] = SPConst::RESTRICT_READONLY;
	//デフォルトメッセージ
	$alert[] = "一度削除すると元に戻せません。本当に削除しますか？";
}else{
	//表示・操作制限
	$restrict["name"] = SPConst::RESTRICT_ENABLE;
	$restrict["name_kana"] = SPConst::RESTRICT_ENABLE;
	$restrict["login_id"] = SPConst::RESTRICT_ENABLE;
	$restrict["password"] = SPConst::RESTRICT_ENABLE;
	$restrict["mail"] = SPConst::RESTRICT_ENABLE;
	$restrict["usertype_id"] = SPConst::RESTRICT_ENABLE;
	$restrict["admin_flg"] = SPConst::RESTRICT_ENABLE;
	$restrict["admintype"] = SPConst::RESTRICT_ENABLE;
	$restrict["usergroup"] = SPConst::RESTRICT_ENABLE;
	$restrict["firstauth_code"] = SPConst::RESTRICT_ENABLE;
	$restrict["language"] = SPConst::RESTRICT_ENABLE;
	$restrict["start_time"] = SPConst::RESTRICT_ENABLE;
	$restrict["end_time"] = SPConst::RESTRICT_ENABLE;
}

//ユーザ種別一覧を取得する
$UserType = new UserType();
$usertype_list = $UserType->getListForSelect();

//入力値チェック
if(($mode == "new" || $mode == "edit") && $action == "save"){

}

//保存処理
if(($mode == "new" || $mode == "edit") && $action == "save" && $error == array()){
	DB::beginTransaction();

	$User = new User();								//ユーザクラス
	$UserGroup = new UserGroup();					//ユーザグループクラス
	$UserUserGroup = new UserUserGroup();			//ユーザ-ユーザグループ紐付クラス

	//新規追加の場合データを追加
	if($mode == "new"){
		$insertData = array();
		$insertData["active_flg"] = "1";
		if(!$User->insert($insertData)){
			DB::rollBack();
			Logger::error("ユーザ新規追加に失敗しました。",$insertData);
			Location::redirect($redirect);
		}
		//フォルダIDを取得
		$user_id = $User->last_insert_id();
	}

	//共通更新条件
	$where = array("user_id" => $user_id);										//更新条件

	//ユーザ更新データ設定
	$saveData = array();
	$saveData["name"] = $name;													//氏名
	$saveData["name_kana"] = $name_kana;										//氏名(カナ)
	$saveData["mail"] = $mail;													//メールアドレス
	$saveData["login_id"] = $login_id;											//ログインID
	if(!Util::IsNullOrEmpty($password)){
		$saveData["password"] = Util::makePasswordHashCode($password);			//パスワード
	}
	$saveData["usertype_id"] = $usertype_id;									//ユーザ種別ID
	$saveData["admin_flg"] = $admin_flg;										//権限種別
	$saveData["admintype"] = $admintype;										//管理者種別

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
	$saveData["firstauth_code"] = $firstauth_code;								//初回認証コード
	$saveData["language"] = $language;											//使用言語
	$saveData["active_flg"] = 1;												//有効

	//新規追加の場合は、ユーザIDをソート順として初期設定
	if($mode == "new"){
		$saveData["sort_no"] = $user_id;
		$saveData["created"] = time();
		$saveData["created_by"] = $session->user["user_id"];
	}

	$saveData["updated"] = time();
	$saveData["updated_by"] = $session->user["user_id"];

	//ユーザ更新実行
	if(!$User->update($where, $saveData)){
		DB::rollBack();
		Logger::error("ユーザ更新に失敗しました。",$saveData);
		Location::redirect($redirect);
	}

	//既存の所属ユーザグループ紐付一覧を取得する
	$usergroup_db = $User->getUserGroupListByUserId($user_id);

	//削除対象データ(DBデータ-入力データ)を抽出
	$deleteUsergroup = Util::array_def_multi($usergroup_db, $usergroup, array("usergroup_id"));

	//ユーザ-ユーザグループ紐付データ削除処理
	foreach($deleteUsergroup as $key => $value){
		//ユーザ-ユーザグループ紐付データを作成
		$userUsergroupData = array();
		$userUsergroupData["user_id"] = $user_id;						//ユーザID
		$userUsergroupData["usergroup_id"] = $value["usergroup_id"];	//ユーザグループID
		//削除処理実行
		if(!$UserUserGroup->delete($userUsergroupData)){
			DB::rollBack();
			Logger::error("ユーザ-ユーザグループ紐付データ削除に失敗しました。",$userUsergroupData);
			Location::redirect($redirect);
		}
	}

	//データ追加処理
	foreach($usergroup as $key => $value){
		//データを作成
		$userUsergroupData = array();
		$userUsergroupData["user_id"] =  $user_id;						//ユーザID
		$userUsergroupData["usergroup_id"] = $value["usergroup_id"];	//ユーザグループID
		if(Util::array_exist_multi($userUsergroupData, $usergroup_db)){
			//既にデータベースに存在するデータの場合
			//何もしない
		}else{
			//存在しないデータの場合
			$userUsergroupData["created"] = time();
			$userUsergroupData["created_by"] = $session->user["user_id"];
			$userUsergroupData["updated"] = time();
			$userUsergroupData["updated_by"] = $session->user["user_id"];
			//新規登録実行
			if(!$UserUserGroup->insert($userUsergroupData)){
				DB::rollBack();
				Logger::error("ユーザ-ユーザグループ紐付データ新規登録に失敗しました。",$userUsergroupData);
				Location::redirect($redirect);
			}
		}
	}

	DB::commit();

	if($close == "on"){
		//一覧画面に遷移する
		Location::redirect("index.php");
	}else{
		//同画面に遷移する
		$redirectParam["user_id"] = $user_id;
		$redirectParam["mode"] = "edit";
		$redirectParam["message[]"] = "保存しました。";
		Location::redirect($self,$redirectParam);
	}

}elseif($mode == "delete" && $action == "delete" && $error == array()){		//削除処理
	DB::beginTransaction();

	$User = new User();								//ユーザクラス
	$UserUserGroup = new UserUserGroup();			//ユーザ-ユーザグループ紐付クラス

	//共通削除条件
	$where = array("user_id" => $user_id);			//削除条件

	//ユーザデータ削除
	if(!$User->delete($where)){
		DB::rollBack();
		Logger::error("ユーザ削除に失敗しました。",$where);
		Location::redirect($redirect);
	}

	//ユーザ-ユーザグループ紐付データ削除
	if(!$UserUserGroup->delete($where)){
		DB::rollBack();
		Logger::error("ユーザ-ユーザグループ紐付削除に失敗しました。",$where);
		Location::redirect($redirect);
	}

	DB::commit();

	//一覧画面に遷移する
	Location::redirect("index.php");
}

//初期表示情報取得
if(($mode == "edit" || $mode == "delete") && $action == ""){		//編集モードで初期表示の場合

	//ユーザ情報取得
	$User = new User();
	$userData = $User->getUserDataForEdit($user_id);

	if(!$userData){
		//ユーザデータが取得できない場合
		Location::redirect("../index.php");
	}

	$start_time = $userData["start_time"];									//利用開始(timestamp)
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
	$end_time = $userData["end_time"];										//利用終了(timestamp)
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

	$name = $userData["name"];												//氏名
	$name_kana = $userData["name_kana"];									//氏名（カナ）
	$login_id = $userData["login_id"];										//ログインID
	$password = "";															//パスワード
	$mail = $userData["mail"];												//メールアドレス
	$usertype_id = $userData["usertype_id"];								//ユーザ種別
	$admin_flg = $userData["admin_flg"];									//管理者フラグ
	$admintype = $userData["admintype"];									//管理者タイプ
	$firstauth_code = $userData["firstauth_code"];							//初回認証コード
	$language = $userData["language"];										//使用言語

	//所属ユーザグループ一覧情報を取得
	$usergroup = $User->getUserGroupListByUserId($user_id);



}elseif($mode == "new" && $action == ""){		//新規追加モードで初期表示の場合
	//呼出元ユーザグループ情報を取得
	$UserGroup = new UserGroup();
	$usergroup = array();
	$usergroup[] = $UserGroup->getUserGroupDataForEdit($usergroup_id);
}

//ユーザグループ一覧を補完する
$UserGroup = new UserGroup();
$usergroup_all = $UserGroup->getUserGroupList();
for($i=0;$i<count($usergroup_all);$i++){
	$search_result = Util::array_search_multi(array("usergroup_id" => $usergroup_all[$i]["usergroup_id"]), $usergroup);
	if($search_result){
		$usergroup_all[$i]["checked"] = "checked";
	}else{
		$usergroup_all[$i]["checked"] = "";
	}
}

$LayoutManager = new LayoutManagerAdminMain();
$LayoutManager->setTitle("ユーザ編集");
$LayoutManager->setErrorList($error);
$LayoutManager->setMessageList($message);
$LayoutManager->setAlertList($alert);
$LayoutManager->header();

//Debug::arrayCheck($restrict);
?>
<script>
$(function(){
	$("#tabs").tabs({
		selected: 1 //コンテンツタブをデフォルトにする
	});

	//ファイル一覧のドラッグドロップ可能化
	$(".sortable").sortable({
		cancel: ".filelist_title,.add_button_area,.add_stylesheet_button_area,.add_script_button_area",
		update: function(e, ui){
			//
		}
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
		       'action':'index.php',
		       'method':'post'
		     });
		$('#values').submit();
	});
});

function window_open(url,width,height){
	window.open(url, 'child', 'toolbar=no, location=no, directories=no, status=no, menubar=no, scrollbars=no, resizable=no, width=' + width + ', height=' + height);
}
</script>

<form action="/" method="post" id="values" enctype="multipart/form-data">
<input type="hidden" name="user_id" value="<?php echo htmlspecialchars($user_id); //ユーザID ?>" />
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
<li><a href="#tabs-1" class="tabmenu">ユーザ設定（基本）</a></li>
<li><a href="#tabs-2" class="tabmenu">属性・権限</a></li>
<li><a href="#tabs-3" class="tabmenu">システム設定</a></li>
</ul>

<!-- コンテンツタブ領域開始 -->
<div id="tabs-1" class="tab_area">
<h1>ユーザ設定（基本）</h1>
<table class="content_input_table">
<?php if($restrict["name"] >= SPConst::RESTRICT_READONLY): ?>
	<tr>
	<th>氏名</th>
	<td>
	<?php echo UIParts::middleText("name",$name,$restrict["name"]); ?>
	</td>
	</tr>
<?php endif; ?>
<?php if($restrict["name_kana"] >= SPConst::RESTRICT_READONLY): ?>
	<tr>
	<th>氏名（カナ）</th>
	<td>
	<?php echo UIParts::middleText("name_kana",$name_kana,$restrict["name_kana"]); ?>
	</td>
	</tr>
<?php endif; ?>
<?php if($restrict["mail"] >= SPConst::RESTRICT_READONLY): ?>
	<tr>
	<th>メールアドレス</th>
	<td>
	<?php echo UIParts::middleText("mail",$mail,$restrict["mail"]); ?>
	</td>
	</tr>
<?php endif; ?>
<?php if($restrict["login_id"] >= SPConst::RESTRICT_READONLY): ?>
	<tr>
	<th>ログインID</th>
	<td>
	<?php echo UIParts::shortText("login_id",$login_id,$restrict["login_id"]); ?>
	</td>
	</tr>
<?php endif; ?>
<?php if($restrict["password"] >= SPConst::RESTRICT_READONLY): ?>
	<tr>
	<th>パスワード</th>
	<td>
	<?php echo UIParts::shortPassword("password",$password,$restrict["password"]); ?>
	</td>
	</tr>
<?php endif; ?>
</table>
</div>
<!-- コンテンツタブ領域終了 -->

<!-- 属性・権限タブ開始 -->
<div id="tabs-2" class="tab_area">
<h1>属性</h1>
<table class="content_input_table">
<?php if($restrict["usertype_id"] >= SPConst::RESTRICT_READONLY): ?>
	<tr>
	<th>ユーザ種別&nbsp;</th>
	<td>
	<?php
		$select_list = Util::getAssocFromMultiArrayByKeyValue($usertype_list, "usertype_id", "usertype_name");
		$select_list = array("" => "--ユーザ種別--") + $select_list;
		echo UIParts::select("usertype_id", $select_list,$usertype_id,$restrict["usertype_id"]);
	?>
	</td>
	</tr>
<?php endif; ?>
<?php if($restrict["admin_flg"] >= SPConst::RESTRICT_READONLY): ?>
	<tr>
	<th>権限種別&nbsp;</th>
	<td>
	<?php
	$select_list = Options::admin_flg();
	echo UIParts::radio("admin_flg", $select_list, $admin_flg,$restrict["admin_flg"]);
	?>
	</td>
	</tr>
<?php endif; ?>
<?php if($restrict["admintype"] >= SPConst::RESTRICT_READONLY): ?>
	<tr>
	<th>管理者種別&nbsp;</th>
	<td>
	<?php
	$select_list = Options::admintype();
	echo UIParts::radio("admintype", $select_list, $admintype,$restrict["admintype"]);
	?>
	</td>
	</tr>
<?php endif; ?>
<?php if($restrict["usergroup"] >= SPConst::RESTRICT_READONLY): ?>
	<tr>
	<th>所属ユーザグループ&nbsp;</th>
	<td>
	<?php
	for($i=0;$i<count($usergroup_all);$i++){
		echo UIParts::checkbox("usergroup[][usergroup_id]", $usergroup_all[$i]["usergroup_id"], $usergroup_all[$i]["checked"],$restrict["usergroup"])." ".$usergroup_all[$i]["usergroup_name"]."<br />";
	}
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
<?php if($restrict["firstauth_code"] >= SPConst::RESTRICT_READONLY): ?>
	<tr>
	<th>初回認証コード&nbsp;</th>
	<td>
	<?php echo UIParts::middleText("firstauth_code",$firstauth_code,$restrict["firstauth_code"]); ?>
	</td>
	</tr>
<?php endif; ?>
<?php if($restrict["language"] >= SPConst::RESTRICT_READONLY): ?>
	<tr>
	<th>使用言語&nbsp;</th>
	<td>
	<?php
	$select_list = Options::language();
	echo UIParts::select("language", $select_list,$language,$restrict["language"]);
	?>
	</td>
	</tr>
<?php endif; ?>
</table>
</div>
<!-- システム設定タブ終了 -->

</div>
</form>
<?php //Debug::arrayCheck($result); ?>
<?php echo $LayoutManager->footer(); ?>
<?php //Debug::arrayCheck($contentData);?>