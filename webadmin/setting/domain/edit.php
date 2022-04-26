<?php
//アプリケーション共通クラス読込
require_once(dirname(__FILE__).'/../../ApplicationCommon/include.php');
require_once(dirname(__FILE__).'/../../DataAccess/Domain.php'); 			//ドメインクラス
require_once(dirname(__FILE__).'/../../DataAccess/DomainUserGroup.php'); 	//フォルダ-ユーザグループ紐付クラス
require_once(dirname(__FILE__).'/../../DataAccess/DomainUser.php'); 		//フォルダ-ユーザ紐付クラス
require_once(dirname(__FILE__).'/../../DataAccess/ContentAuth.php'); 		//コンテンツ操作権限種別クラス

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
$domain_id = isset($_REQUEST["domain_id"]) ? $_REQUEST["domain_id"] : "";																	//ドメインID
$domain_name = isset($_POST["domain_name"]) ? Util::encodeRequest($_POST["domain_name"]) : "";												//ドメイン名
$domain = isset($_POST["domain"]) ? Util::encodeRequest($_POST["domain"]) : "";																//ドメイン
$base_dir_path = isset($_POST["base_dir_path"]) ? Util::encodeRequest($_POST["base_dir_path"]) : "";										//ベースディレクトリパス
$default_doctype = isset($_POST["default_doctype"]) ? Util::encodeRequest($_POST["default_doctype"]) : "";									//デフォルトDOCTYPE
$usergroup_contentauth = isset($_POST["usergroup_contentauth"]) ? Util::encodeRequest($_POST["usergroup_contentauth"]) : array();			//権限（ユーザグループ）
$user_contentauth = isset($_POST["user_contentauth"]) ? Util::encodeRequest($_POST["user_contentauth"]) : array();							//権限（ユーザ）

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

//コンテンツ操作権限種別一覧を取得する
$ContentAuth = new ContentAuth();
$contentauth_list = $ContentAuth->getListForSelect("domain");

//コンテンツ操作権限入力値を補完する
foreach($contentauth_list as $contentauth_one){
	if(!isset($usergroup_contentauth[$contentauth_one["contentauth_id"]])){
		$usergroup_contentauth[$contentauth_one["contentauth_id"]] = array();
	}
	if(!isset($user_contentauth[$contentauth_one["contentauth_id"]])){
		$user_contentauth[$contentauth_one["contentauth_id"]] = array();
	}
}

//入力値チェック
if(($mode == "new" || $mode == "edit") && $action == "save"){

}

//保存処理
if(($mode == "new" || $mode == "edit") && $action == "save" && $error == array()){
	DB::beginTransaction();

	$Domain = new Domain();							//ドメインクラス
	$DomainUserGroup = new DomainUserGroup();		//ドメイン-ユーザグループ紐付クラス
	$DomainUser = new DomainUser();					//ドメイン-ユーザ紐付クラス

	//新規追加の場合データを追加
	if($mode == "new"){
		$insertData = array();
		$insertData["active_flg"] = 1;
		if(!$Domain->insert($insertData)){
			DB::rollBack();
			Logger::error("ドメイン新規追加に失敗しました。",$insertData);
			Location::redirect($redirect);
		}
		//ドメインIDを取得
		$domain_id = $Domain->last_insert_id();
	}

	//共通更新条件
	$where = array("domain_id" => $domain_id);									//更新条件

	//ドメイン更新データ設定
	$saveData = array();
	$saveData["domain_name"] = $domain_name;									//ドメイン名
	$saveData["domain"] = $domain;												//ドメイン
	$saveData["base_dir_path"] = $base_dir_path;								//ベースディレクトリパス
	$saveData["default_doctype"] = $default_doctype;							//デフォルトDOCTYPE
	$saveData["active_flg"] = 1;												//有効

	//新規追加の場合は、フォルダIDをソート順として初期設定
	if($mode == "new"){
		$saveData["sort_no"] = $domain_id;
		$saveData["created"] = time();
		$saveData["created_by"] = $session->user["user_id"];
	}

	$saveData["updated"] = time();
	$saveData["updated_by"] = $session->user["user_id"];

	//ドメイン更新実行
	if(!$Domain->update($where, $saveData)){
		DB::rollBack();
		Logger::error("ドメイン更新に失敗しました。",$saveData);
		Location::redirect($redirect);
	}

	//既存の権限（ユーザグループ）紐付一覧を取得する
	$DomainUserGroup = new DomainUserGroup();
	$domain_usergroup_list = $DomainUserGroup->getListByDomainId($domain_id);

	//権限（ユーザグループ）更新実行
	foreach($usergroup_contentauth as $contentauth_id => $dataArr){
		//紐付一覧をコンテンツ権限種別で検索
		$domain_usergroup_list_target = Util::array_search_multi(array("contentauth_id" => $contentauth_id), $domain_usergroup_list);

		//削除対象データ(DBデータ-入力データ)を抽出
		$deleteAuthData = Util::array_def_multi($domain_usergroup_list_target, $dataArr, array("usergroup_id"));

		//データ削除処理
		foreach($deleteAuthData as $key => $value){
			//権限紐付データを作成
			$authData = array();
			$authData["domain_usergroup_id"] = $value["domain_usergroup_id"];		//コンテンツ操作権限種別ID
			//削除処理実行
			if(!$DomainUserGroup->delete($authData)){
				DB::rollBack();
				Logger::error("権限（ユーザグループ）紐付データ削除に失敗しました。",$authData);
				Location::redirect($redirect);
			}
		}

		//データ追加処理
		$exist_flg = false;
		foreach($dataArr as $key => $value){
			//権限紐付データを作成
			$authData = array();
			$authData["contentauth_id"] = $contentauth_id;				//コンテンツ操作権限種別ID
			$authData["domain_id"] = $domain_id;						//ドメインID
			$authData["usergroup_id"] = $value["usergroup_id"];			//ユーザグループID

			if(Util::array_exist_multi($authData, $domain_usergroup_list)){
				//既にデータベースに存在するデータの場合
				//何もしない
			}else{
				//存在しないデータの場合
				$authData["created"] = time();
				$authData["created_by"] = $session->user["user_id"];
				$authData["updated"] = time();
				$authData["updated_by"] = $session->user["user_id"];
				//新規登録実行
				if(!$DomainUserGroup->insert($authData)){
					DB::rollBack();
					Logger::error("権限（ユーザグループ）紐付データ新規登録に失敗しました。",$authData);
					Location::redirect($redirect);
				}
			}
			$exist_flg = true;
		}

		if($exist_flg){
			//全グループ許可データの削除
			$authData = array();
			$authData["contentauth_id"] = $contentauth_id;				//コンテンツ操作権限種別ID
			$authData["domain_id"] = $domain_id;						//ドメインID
			$authData["usergroup_id"] = 0;								//ユーザグループID
			//削除処理実行
			if(!$DomainUserGroup->delete($authData)){
				DB::rollBack();
				Logger::error("権限（ユーザグループ）紐付全グループ許可データの削除に失敗しました。",$authData);
				Location::redirect($redirect);
			}
		}else{
			//全グループ許可データの追加
			$authData = array();
			$authData["contentauth_id"] = $contentauth_id;				//コンテンツ操作権限種別ID
			$authData["domain_id"] = $domain_id;						//ドメインID
			$authData["usergroup_id"] = 0;								//ユーザグループID

			//既存件数取得
			$authDataCount = $DomainUserGroup->getCountByParameters($authData);
			if($authDataCount < 1){
				$authData["created"] = time();
				$authData["created_by"] = $session->user["user_id"];
				$authData["updated"] = time();
				$authData["updated_by"] = $session->user["user_id"];
				//新規登録実行
				if(!$DomainUserGroup->insert($authData)){
					DB::rollBack();
					Logger::error("権限（ユーザグループ）紐付全グループ許可データ新規登録に失敗しました。",$authData);
					Location::redirect($redirect);
				}
			}
		}
	}

	//既存の権限（ユーザ）紐付一覧を取得する
	$DomainUser = new DomainUser();
	$domain_user_list = $DomainUser->getListByDomainId($domain_id);

	//権限（ユーザ）更新実行
	foreach($user_contentauth as $contentauth_id => $dataArr){
		//紐付一覧をコンテンツ権限種別で検索
		$domain_user_list_target = Util::array_search_multi(array("contentauth_id" => $contentauth_id), $domain_user_list);

		//削除対象データ(DBデータ-入力データ)を抽出
		$deleteAuthData = Util::array_def_multi($domain_user_list_target, $dataArr, array("user_id"));

		//データ削除処理
		foreach($deleteAuthData as $key => $value){
			//権限紐付データを作成
			$authData = array();
			$authData["domain_user_id"] = $value["domain_user_id"];		//コンテンツ操作権限種別ID
			//削除処理実行
			if(!$DomainUser->delete($authData)){
				DB::rollBack();
				Logger::error("権限（ユーザ）紐付データ削除に失敗しました。",$authData);
				Location::redirect($redirect);
			}
		}

		//データ追加処理
		foreach($dataArr as $key => $value){
			//権限紐付データを作成
			$authData = array();
			$authData["contentauth_id"] = $contentauth_id;				//コンテンツ操作権限種別ID
			$authData["domain_id"] = $domain_id;						//ドメインID
			$authData["user_id"] = $value["user_id"];					//ユーザID

			if(Util::array_exist_multi($authData, $domain_user_list)){
				//既にデータベースに存在するデータの場合
				//何もしない
			}else{
				//存在しないデータの場合
				$authData["created"] = time();
				$authData["created_by"] = $session->user["user_id"];
				$authData["updated"] = time();
				$authData["updated_by"] = $session->user["user_id"];
				//新規登録実行
				if(!$DomainUser->insert($authData)){
					DB::rollBack();
					Logger::error("権限（ユーザ）紐付データ新規登録に失敗しました。",$authData);
					Location::redirect($redirect);
				}
			}
		}
	}

	DB::commit();

	if($close == "on"){
		//一覧画面に遷移する
		Location::redirect("list.php");
	}else{
		//同画面に遷移する
		$redirectParam["domain_id"] = $domain_id;
		$redirectParam["mode"] = "edit";
		$redirectParam["message[]"] = "保存しました。";
		Location::redirect($self,$redirectParam);
	}
}elseif($mode == "delete" && $action == "delete" && $error == array()){		//削除処理
	DB::beginTransaction();

	$Domain = new Domain();							//ドメインクラス
	$DomainUserGroup = new DomainUserGroup();		//ドメイン-ユーザグループ紐付クラス
	$DomainUser = new DomainUser();					//ドメイン-ユーザ紐付クラス

	//共通削除条件
	$where = array("domain_id" => $domain_id);		//削除条件

	//ドメインデータ削除
	if(!$Domain->delete($where)){
		DB::rollBack();
		Logger::error("ドメイン削除に失敗しました。",$where);
		Location::redirect($redirect);
	}

	//フォルダ-ユーザグループ紐付データ削除
	if(!$DomainUserGroup->delete($where)){
		DB::rollBack();
		Logger::error("ドメイン-ユーザグループ紐付削除に失敗しました。",$where);
		Location::redirect($redirect);
	}

	//フォルダ-ユーザ紐付データ削除
	if(!$DomainUser->delete($where)){
		DB::rollBack();
		Logger::error("ドメイン-ユーザ紐付削除に失敗しました。",$where);
		Location::redirect($redirect);
	}

	DB::commit();

	//一覧画面に遷移する
	Location::redirect("index.php");
}

//初期表示情報取得
if(($mode == "edit" || $mode == "delete") && $action == ""){		//編集モードで初期表示の場合
	//フォルダ情報取得
	$Domain = new Domain();
	$domainData = $Domain->getDataByPrimaryKey($domain_id);

	if(!$domainData){
		//ドメインデータが取得できない場合
		Location::redirect("../../index.php");
	}

	$domain_name = $domainData["domain_name"];								//ドメイン名
	$domain = $domainData["domain"];										//ドメイン
	$base_dir_path = $domainData["base_dir_path"];							//ベースディレクトリパス
	$default_doctype = $domainData["default_doctype"];						//デフォルトDOCTYPE

	//権限（ユーザグループ）紐付一覧を取得する
	$DomainUserGroup = new DomainUserGroup();
	$domain_usergroup_list = $DomainUserGroup->getListByDomainId($domain_id);

	//権限（ユーザグループ）紐付をコンテンツ操作権限種別ごとに分類する
	$usergroup_contentauth = array();
	for($i=0;$i<count($domain_usergroup_list);$i++){
		$usergroup_contentauth[$domain_usergroup_list[$i]["contentauth_id"]][$domain_usergroup_list[$i]["usergroup_id"]]["usergroup_id"] = $domain_usergroup_list[$i]["usergroup_id"];
		$usergroup_contentauth[$domain_usergroup_list[$i]["contentauth_id"]][$domain_usergroup_list[$i]["usergroup_id"]]["usergroup_name"] = $domain_usergroup_list[$i]["usergroup_name"];
	}

	//権限（ユーザ）紐付一覧を取得する
	$DomainUser = new DomainUser();
	$domain_user_list = $DomainUser->getListByDomainId($domain_id);

	//権限（ユーザ）紐付をコンテンツ操作権限種別ごとに分類する
	$user_contentauth = array();
	for($i=0;$i<count($domain_user_list);$i++){
		$user_contentauth[$domain_user_list[$i]["contentauth_id"]][$domain_user_list[$i]["user_id"]]["user_id"] = $domain_user_list[$i]["user_id"];
		$user_contentauth[$domain_user_list[$i]["contentauth_id"]][$domain_user_list[$i]["user_id"]]["name"] = $domain_user_list[$i]["name"];
	}
}elseif($mode == "new" && $action == ""){		//新規追加モードで初期表示の場合

}

//レイアウトマネージャ設定・ヘッダ出力
$LayoutManager = new LayoutManagerAdminMain();
$LayoutManager->setTitle("ドメイン設定");
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
		       'action':'list.php',
		       'method':'get'
		     });
		$('#values').submit();
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
});
</script>

<form action="/" method="post" id="values">
<input type="hidden" name="domain_id" value="<?php echo htmlspecialchars($domain_id); //ドメインID ?>" />
<input type="hidden" name="mode" value="<?php echo htmlspecialchars($mode); //動作モード ?>" />
<input type="hidden" name="action" value="<?php //javascriptから設定 ?>" />
<input type="hidden" name="close" value="<?php //javascriptから設定 ?>" />

<div id="tabs">

<!-- アクションボタン開始 -->
<table class="edit_control">
<tr>
<td>
<input type="button" value="一覧に戻る"  id="back_to_list" />
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
<li><a href="#tabs-1" class="tabmenu">ドメイン設定(基本)</a></li>
<li><a href="#tabs-2" class="tabmenu">属性・権限</a></li>
</ul>
<!-- タブメニュー設定終了 -->

<!-- ドメイン設定（基本）タブ開始 -->
<div id="tabs-1" class="tab_area">
<h1>ドメイン設定(基本)</h1>
<table class="content_input_table">
<?php if($restrict["all"] >= SPConst::RESTRICT_READONLY): ?>
	<tr>
	<th>表示名&nbsp;</th>
	<td>
	<?php echo UIParts::middleText("domain_name", $domain_name, $restrict["all"]); ?>
	</td>
	</tr>
	<tr>
	<th>ドメイン&nbsp;</th>
	<td>
	<?php echo UIParts::middleText("domain", $domain, $restrict["all"]); ?>
	</td>
	</tr>
	<tr>
	<th>ベースディレクトリパス&nbsp;</th>
	<td>
	<?php echo UIParts::middleText("base_dir_path", $base_dir_path, $restrict["all"]); ?>
	</td>
	</tr>
	<tr>
	<th>デフォルトDOCTYPE&nbsp;</th>
	<td>
	<?php echo UIParts::middleText("default_doctype", $default_doctype, $restrict["all"]); ?>
	</td>
	</tr>
<?php endif; ?>
</table>
</div>
<!-- フォルダ設定（基本）タブ終了 -->

<!-- 属性・権限タブ開始 -->
<div id="tabs-2" class="tab_area">
<h1>属性</h1>
<?php if($restrict["all"] >= SPConst::RESTRICT_READONLY): ?>
	<br />
	<h1>権限(ユーザグループ)&nbsp;</h1>
	<?php echo UIParts::authPicker("page", "usergroup_contentauth", "ユーザグループ", $contentauth_list, $usergroup_contentauth, "contentauth_id", "contentauth_name", "usergroup_id", "usergroup_name",$restrict["all"]); ?>
<?php endif; ?>
<?php if($restrict["all"] >= SPConst::RESTRICT_READONLY): ?>
	<br />
	<h1>権限(ユーザ)&nbsp;</h1>
	<?php echo UIParts::authPicker("page", "user_contentauth", "ユーザ", $contentauth_list, $user_contentauth, "contentauth_id", "contentauth_name", "user_id", "name",$restrict["all"]); ?>
<?php endif; ?>
</div>
<!-- 属性・権限タブ終了 -->

</div><!-- tabs -->
</form><!-- values -->

<?php //Debug::arrayCheck($result); ?>
<?php echo $LayoutManager->footer(); ?>
<?php //Debug::arrayCheck($contentData);?>