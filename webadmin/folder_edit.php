<?php
// アプリケーション共通クラス読込
require_once(dirname(__FILE__).'/ApplicationCommon/include.php');

require_once(dirname(__FILE__).'/DataAccess/Content.php'); 	//コンテンツクラス
require_once(dirname(__FILE__).'/DataAccess/Folder.php'); 	//フォルダクラス
require_once(dirname(__FILE__).'/DataAccess/Domain.php'); 	//ドメインクラス

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
$folder_id = isset($_REQUEST["folder_id"]) ? Util::encodeRequest($_REQUEST["folder_id"]) : "";								//フォルダID
$domain_id = isset($_REQUEST["domain_id"]) ? Util::encodeRequest($_REQUEST["domain_id"]) : "";								//ドメインID
$folder_name = isset($_REQUEST["folder_name"]) ? Util::encodeRequest($_REQUEST["folder_name"]) : "";						//フォルダ管理名
$folder_code = isset($_REQUEST["folder_code"]) ? Util::encodeRequest($_REQUEST["folder_code"]) : "";						//フォルダ識別子
$template_id = isset($_REQUEST["template_id"]) ? Util::encodeRequest($_REQUEST["template_id"]) : "";						//テンプレートID
$title_prefix = isset($_REQUEST["title_prefix"]) ? Util::encodeRequest($_REQUEST["title_prefix"]) : "";						//タイトルプレフィックス
$title_suffix = isset($_REQUEST["title_suffix"]) ? Util::encodeRequest($_REQUEST["title_suffix"]) : "";						//タイトルサフィックス
$default_dir_path = isset($_REQUEST["default_dir_path"]) ? Util::encodeRequest($_REQUEST["default_dir_path"]) : "";			//デフォルトディレクトリパス
$default_title = isset($_REQUEST["default_title"]) ? Util::encodeRequest($_REQUEST["default_title"]) : "";					//デフォルトタイトル
$list_display_flg = isset($_REQUEST["list_display_flg"]) ? $_REQUEST["list_display_flg"] : "";								//一覧表示フラグ

//起動モードが設定されていない場合
if(Util::IsNullOrEmpty($mode)){
	Logger::notice("起動モードが設定されていませんでした。");
	Location::redirect($redirect);
}

//フォルダクラス
$Folder = new Folder();

//入力値チェック
if(($mode == "new" || $mode == "edit") && $action == "save"){
	if($folder_name == ""){
		$error[] = "フォルダ名(管理用)を入力してください。";
	}
	if($folder_code== ""){
		$error[] = "フォルダ識別子を入力してください。";
	}else{
		if($mode == "new"){
			$cnt = $Folder->getCountByParameters(array("folder_code" => $folder_code));
			if($cnt > 0){
				$error[] = "フォルダ識別子は既に登録されています。別の名前を入力してください。";
			}
		}else if($mode == "edit"){
			$where = array();
			$where[] = array("folder_code",$folder_code);
			$where[] = array("folder_id",$folder_id,"<>");
			$cnt = $Folder->getCountByParameters($where);
			if($cnt > 0){
				$error[] = "フォルダ識別子は既に登録されています。別の名前を入力してください。";
			}
		}
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
		$insertData["sort_no"] = $Folder->getMaxByParameters("folder_id") + 1;
		$insertData["created"] = $now_timestamp;
		$insertData["created_by"] = $session->user["user_id"];
		if(!$Folder->insert($insertData)){
			DB::rollBack();
			Logger::error("フォルダ追加に失敗しました。",$insertData);
			Location::redirect($redirect);
		}
		//IDを取得
		$folder_id = $Folder->last_insert_id();
	}

	//共通更新条件
	$where = array("folder_id" => $folder_id);				//更新条件

	//フォルダ更新データ設定
	$saveData = array();
	$saveData["domain_id"] = $domain_id;
	$saveData["folder_name"] = $folder_name;
	$saveData["folder_code"] = $folder_code;
	$saveData["template_id"] = $template_id;
	$saveData["title_prefix"] = $title_prefix;
	$saveData["title_suffix"] = $title_suffix;
	$saveData["default_dir_path"] = $default_dir_path;
	$saveData["default_title"] = $default_title;
	$saveData["list_display_flg"] = $list_display_flg;

	//ドメイン更新実行
	$saveData["updated"] = $now_timestamp;
	$saveData["updated_by"] = $session->user["user_id"];
	if(!$Folder->update($where, $saveData)){
		DB::rollBack();
		Logger::error("フォルダ更新に失敗しました。",$saveData);
		Location::redirect($redirect);
	}

	DB::commit();

	if($close == "on"){
		//一覧画面に遷移する
		$redirectParam["master_table"] = "folder";
		Location::redirect($close_url,$redirectParam);
	}else{
		//同画面に遷移する
		$redirectParam["folder_id"] = $folder_id;
		$redirectParam["close_url"] = $close_url;
		$redirectParam["mode"] = "edit";
		$redirectParam["message[]"] = "保存しました。";
		Location::redirect($self,$redirectParam);
	}
}

//削除処理
if($mode == "edit" && $action == "delete" && $error == array()){
	DB::beginTransaction();
	$Folder->delete(array("folder_id" => $folder_id));
	DB::commit();

	//一覧画面に遷移する
	$redirectParam["master_table"] = "folder";
	Location::redirect($close_url,$redirectParam);
}

//初期表示
if($action == ""){
	if($mode == "edit"){
		//ドメインデータを取得
		$folderData = $Folder->getDataByPrimaryKey($folder_id);
		$domain_id = $folderData["domain_id"];
		$folder_name = $folderData["folder_name"];
		$folder_code = $folderData["folder_code"];
		$template_id = $folderData["template_id"];
		$title_prefix = $folderData["title_prefix"];
		$title_suffix = $folderData["title_suffix"];
		$default_dir_path = $folderData["default_dir_path"];
		$default_title = $folderData["default_title"];
		$list_display_flg = $folderData["list_display_flg"];
	}else if($mode == "new"){
		$list_display_flg = "1";
	}
}

//ドメイン一覧
$Domain = new Domain();
$domainList = $Domain->getListByParameters();

//テンプレート名
$Content = new Content(Content::TABLE_MANAGEMENT);
$where = array();
$where["contentclass"] = "template";
$templateList = $Content->getListByParameters($where);

$LayoutManager = new LayoutManagerAdminMain();
$LayoutManager->setTitle("フォルダ編集");
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
<input type="hidden" name="master_table" value="<?php echo "folder"; //選択肢名 ?>" />
<input type="hidden" name="folder_id" value="<?php echo $folder_id; //ID ?>" />
<input type="hidden" name="action" value="<?php //javascriptから設定 ?>" />
<input type="hidden" name="close" value="<?php //javascriptから設定 ?>" />
<table class="edit" cellspacing="0">
    <tr>
    <th class="w240">ドメイン<span class="mark orange">必須</span></th>
    <td>
    <select name="domain_id">
	<?php
	foreach($domainList as $domainData){
		if($domainData["domain_id"] == $domain_id){ $selected = "selected"; }else{ $selected = ""; }
		echo '<option value="'.$domainData["domain_id"].'" '.$selected.'>'.$domainData["domain_name"].'</option>';
	}
	?>
    </select>
    </td>
    </tr>

    <tr>
    <th>フォルダ名（管理用）<span class="mark orange">必須</span></th>
    <td>
    <input type="text" name="folder_name" value="<?php echo htmlspecialchars($folder_name);?>" />
    </td>
    </tr>

    <tr>
    <th>フォルダ識別名<span class="mark orange">必須</span></th>
    <td>
    <input type="text" name="folder_code" value="<?php echo htmlspecialchars($folder_code);?>" />
    </td>
    </tr>

    <tr>
    <th>デフォルトテンプレート</th>
    <td>
    <select name="template_id">
    <option value=""></option>
	<?php
	foreach($templateList as $templateData){
		if($templateData["content_id"] == $template_id){ $selected = "selected"; }else{ $selected = ""; }
		echo '<option value="'.$templateData["content_id"].'" '.$selected	.'>'.$templateData["title"].'</option>';
	}
	?>
    </select>
    </td>
    </tr>

    <tr>
    <th>タイトルプレフィックス<span class="mark orange">必須</span></th>
    <td>
    <input type="text" name="title_prefix" value="<?php echo htmlspecialchars($title_prefix);?>" />
    </td>
    </tr>

    <tr>
    <th>タイトルサフィクス<span class="mark orange">必須</span></th>
    <td>
    <input type="text" name="title_suffix" value="<?php echo htmlspecialchars($title_suffix);?>" />
    </td>
    </tr>

    <tr>
    <th>デフォルトディレクトリパス</th>
    <td>
    <input type="text" name="default_dir_path" value="<?php echo htmlspecialchars($default_dir_path);?>" />
    </td>
    </tr>

    <tr>
    <th>デフォルトタイトル</th>
    <td>
    <input type="text" name="default_title" value="<?php echo htmlspecialchars($default_title);?>" />
    </td>
    </tr>

	<tr>
    <th>フォルダ一覧への表示<span class="mark orange">必須</span></th>
    <td>
    <?php if($list_display_flg == "0"){$checked = "checked"; }else{$checked = ""; }?>
    <input type="radio" name="list_display_flg" value="0" <?php echo $checked; ?>>非表示&nbsp;
    <?php if($list_display_flg == "1"){$checked = "checked"; }else{$checked = ""; }?>
    <input type="radio" name="list_display_flg" value="1" <?php echo $checked; ?>>表示&nbsp;
    </td>
    </tr>

</table>
</form>
<?php $LayoutManager->footer(); ?>
