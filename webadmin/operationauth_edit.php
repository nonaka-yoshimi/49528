<?php
// アプリケーション共通クラス読込
require_once(dirname(__FILE__).'/ApplicationCommon/include.php');

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
$operationauth_id = isset($_REQUEST["operationauth_id"]) ? Util::encodeRequest($_REQUEST["operationauth_id"]) : "";			//機能操作権限ID
$operationauth_name = isset($_REQUEST["operationauth_name"]) ? Util::encodeRequest($_REQUEST["operationauth_name"]) : "";	//機能操作権限名
$ope_auth_page_view = isset($_REQUEST["ope_auth_page_view"]) ? $_REQUEST["ope_auth_page_view"] : "";						//ページ閲覧権限
$ope_auth_page_add = isset($_REQUEST["ope_auth_page_add"]) ? $_REQUEST["ope_auth_page_add"] : "";							//ページ追加権限
$ope_auth_page_edit = isset($_REQUEST["ope_auth_page_edit"]) ? $_REQUEST["ope_auth_page_edit"] : "";						//ページ編集権限
$ope_auth_page_delete = isset($_REQUEST["ope_auth_page_delete"]) ? $_REQUEST["ope_auth_page_delete"] : "";					//ページ削除権限
$ope_auth_page_workflow = isset($_REQUEST["ope_auth_page_workflow"]) ? $_REQUEST["ope_auth_page_workflow"] : "";			//ページワークフロー実行権限
$ope_auth_page_publish = isset($_REQUEST["ope_auth_page_publish"]) ? $_REQUEST["ope_auth_page_publish"] : "";				//ページ公開権限
$ope_auth_template_view = isset($_REQUEST["ope_auth_template_view"]) ? $_REQUEST["ope_auth_template_view"] : "";			//テンプレート閲覧権限
$ope_auth_template_add = isset($_REQUEST["ope_auth_template_add"]) ? $_REQUEST["ope_auth_template_add"] : "";				//テンプレート追加権限
$ope_auth_template_edit = isset($_REQUEST["ope_auth_template_edit"]) ? $_REQUEST["ope_auth_template_edit"] : "";			//テンプレート編集権限
$ope_auth_template_delete = isset($_REQUEST["ope_auth_template_delete"]) ? $_REQUEST["ope_auth_template_delete"] : "";		//テンプレート削除権限
$ope_auth_template_workflow = isset($_REQUEST["ope_auth_template_workflow"]) ? $_REQUEST["ope_auth_template_workflow"] : "";	//テンプレートワークフロー実行権限
$ope_auth_template_publish = isset($_REQUEST["ope_auth_template_publish"]) ? $_REQUEST["ope_auth_template_publish"] : "";	//テンプレート公開権限
$ope_auth_parts_view = isset($_REQUEST["ope_auth_parts_view"]) ? $_REQUEST["ope_auth_parts_view"] : "";						//部品閲覧権限
$ope_auth_parts_add = isset($_REQUEST["ope_auth_parts_add"]) ? $_REQUEST["ope_auth_parts_add"] : "";						//部品追加権限
$ope_auth_parts_edit = isset($_REQUEST["ope_auth_parts_edit"]) ? $_REQUEST["ope_auth_parts_edit"] : "";						//部品編集権限
$ope_auth_parts_delete = isset($_REQUEST["ope_auth_parts_delete"]) ? $_REQUEST["ope_auth_parts_delete"] : "";				//部品削除権限
$ope_auth_parts_workflow = isset($_REQUEST["ope_auth_parts_workflow"]) ? $_REQUEST["ope_auth_parts_workflow"] : "";			//部品ワークフロー実行権限
$ope_auth_parts_publish = isset($_REQUEST["ope_auth_parts_publish"]) ? $_REQUEST["ope_auth_parts_publish"] : "";			//部品公開権限
$ope_auth_stylesheet_view = isset($_REQUEST["ope_auth_stylesheet_view"]) ? $_REQUEST["ope_auth_stylesheet_view"] : "";		//スタイルシート閲覧権限
$ope_auth_stylesheet_add = isset($_REQUEST["ope_auth_stylesheet_add"]) ? $_REQUEST["ope_auth_stylesheet_add"] : "";			//スタイルシート追加権限
$ope_auth_stylesheet_edit = isset($_REQUEST["ope_auth_stylesheet_edit"]) ? $_REQUEST["ope_auth_stylesheet_edit"] : "";		//スタイルシート編集権限
$ope_auth_stylesheet_delete = isset($_REQUEST["ope_auth_stylesheet_delete"]) ? $_REQUEST["ope_auth_stylesheet_delete"] : "";	//スタイルシート削除権限
$ope_auth_stylesheet_workflow = isset($_REQUEST["ope_auth_stylesheet_workflow"]) ? $_REQUEST["ope_auth_stylesheet_workflow"] : "";	//スタイルシートワークフロー実行権限
$ope_auth_stylesheet_publish = isset($_REQUEST["ope_auth_stylesheet_publish"]) ? $_REQUEST["ope_auth_stylesheet_publish"] : "";	//スタイルシート公開権限
$ope_auth_script_view = isset($_REQUEST["ope_auth_script_view"]) ? $_REQUEST["ope_auth_script_view"] : "";					//スクリプト閲覧権限
$ope_auth_script_add = isset($_REQUEST["ope_auth_script_add"]) ? $_REQUEST["ope_auth_script_add"] : "";						//スクリプト追加権限
$ope_auth_script_edit = isset($_REQUEST["ope_auth_script_edit"]) ? $_REQUEST["ope_auth_script_edit"] : "";					//スクリプト編集権限
$ope_auth_script_delete = isset($_REQUEST["ope_auth_script_delete"]) ? $_REQUEST["ope_auth_script_delete"] : "";			//スクリプト削除権限
$ope_auth_script_workflow = isset($_REQUEST["ope_auth_script_workflow"]) ? $_REQUEST["ope_auth_script_workflow"] : "";		//スクリプトワークフロー実行権限
$ope_auth_script_publish = isset($_REQUEST["ope_auth_script_publish"]) ? $_REQUEST["ope_auth_script_publish"] : "";			//スクリプト公開権限
$ope_auth_file_admin = isset($_REQUEST["ope_auth_file_admin"]) ? $_REQUEST["ope_auth_file_admin"] : "";						//ファイル管理者権限
$ope_auth_user_admin = isset($_REQUEST["ope_auth_user_admin"]) ? $_REQUEST["ope_auth_user_admin"] : "";						//ユーザ管理者権限(仮)
$ope_auth_website = isset($_REQUEST["ope_auth_website"]) ? $_REQUEST["ope_auth_website"] : "";								//Webサイト全体設定権限
$ope_auth_ext1 = isset($_REQUEST["ope_auth_ext1"]) ? $_REQUEST["ope_auth_ext1"] : "";										//拡張機能１操作権限
$ope_auth_ext2 = isset($_REQUEST["ope_auth_ext2"]) ? $_REQUEST["ope_auth_ext2"] : "";										//拡張機能２操作権限
$ope_auth_ext3 = isset($_REQUEST["ope_auth_ext3"]) ? $_REQUEST["ope_auth_ext3"] : "";										//拡張機能３操作権限
$ope_auth_ext4 = isset($_REQUEST["ope_auth_ext4"]) ? $_REQUEST["ope_auth_ext4"] : "";										//拡張機能４操作権限
$ope_auth_ext5 = isset($_REQUEST["ope_auth_ext5"]) ? $_REQUEST["ope_auth_ext5"] : "";										//拡張機能５操作権限

//起動モードが設定されていない場合
if(Util::IsNullOrEmpty($mode)){
	Logger::notice("起動モードが設定されていませんでした。");
	Location::redirect($redirect);
}

//ユーザグループクラス
$OperationAuth = new OperationAuth();

//入力値チェック
if(($mode == "new" || $mode == "edit") && $action == "save"){
	if($operationauth_name == ""){
		$error[] = "機能操作権限名を入力してください。";
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
		$insertData["sort_no"] = $OperationAuth->getMaxByParameters("operationauth_id") + 1;
		$insertData["created"] = $now_timestamp;
		$insertData["created_by"] = $session->user["user_id"];
		if(!$OperationAuth->insert($insertData)){
			DB::rollBack();
			Logger::error("機能操作権限追加に失敗しました。",$insertData);
			Location::redirect($redirect);
		}
		//IDを取得
		$operationauth_id = $OperationAuth->last_insert_id();
	}

	//共通更新条件
	$where = array("operationauth_id" => $operationauth_id);				//更新条件

	//フォルダ更新データ設定
	$saveData = array();
	$saveData["operationauth_name"] = $operationauth_name;
	$saveData["ope_auth_page_view"] = $ope_auth_page_view;
	$saveData["ope_auth_page_add"] = $ope_auth_page_add;
	$saveData["ope_auth_page_edit"] = $ope_auth_page_edit;
	$saveData["ope_auth_page_delete"] = $ope_auth_page_delete;
	$saveData["ope_auth_page_workflow"] = $ope_auth_page_workflow;
	$saveData["ope_auth_page_publish"] = $ope_auth_page_publish;
	$saveData["ope_auth_template_view"] = $ope_auth_template_view;
	$saveData["ope_auth_template_add"] = $ope_auth_template_add;
	$saveData["ope_auth_template_edit"] = $ope_auth_template_edit;
	$saveData["ope_auth_template_delete"] = $ope_auth_template_delete;
	$saveData["ope_auth_template_workflow"] = $ope_auth_template_workflow;
	$saveData["ope_auth_template_publish"] = $ope_auth_template_publish;
	$saveData["ope_auth_parts_view"] = $ope_auth_parts_view;
	$saveData["ope_auth_parts_add"] = $ope_auth_parts_add;
	$saveData["ope_auth_parts_edit"] = $ope_auth_parts_edit;
	$saveData["ope_auth_parts_delete"] = $ope_auth_parts_delete;
	$saveData["ope_auth_parts_workflow"] = $ope_auth_parts_workflow;
	$saveData["ope_auth_parts_publish"] = $ope_auth_parts_publish;
	$saveData["ope_auth_stylesheet_view"] = $ope_auth_stylesheet_view;
	$saveData["ope_auth_stylesheet_add"] = $ope_auth_stylesheet_add;
	$saveData["ope_auth_stylesheet_edit"] = $ope_auth_stylesheet_edit;
	$saveData["ope_auth_stylesheet_delete"] = $ope_auth_stylesheet_delete;
	$saveData["ope_auth_stylesheet_workflow"] = $ope_auth_stylesheet_workflow;
	$saveData["ope_auth_stylesheet_publish"] = $ope_auth_stylesheet_publish;
	$saveData["ope_auth_script_view"] = $ope_auth_script_view;
	$saveData["ope_auth_script_add"] = $ope_auth_script_add;
	$saveData["ope_auth_script_edit"] = $ope_auth_script_edit;
	$saveData["ope_auth_script_delete"] = $ope_auth_script_delete;
	$saveData["ope_auth_script_workflow"] = $ope_auth_script_workflow;
	$saveData["ope_auth_script_publish"] = $ope_auth_script_publish;
	$saveData["ope_auth_file_admin"] = $ope_auth_file_admin;
	$saveData["ope_auth_user_self"] = $ope_auth_user_admin;		//仮
	$saveData["ope_auth_user_other"] = $ope_auth_user_admin;	//仮
	$saveData["ope_auth_website"] = $ope_auth_website;
	$saveData["ope_auth_ext1"] = $ope_auth_ext1;
	$saveData["ope_auth_ext2"] = $ope_auth_ext2;
	$saveData["ope_auth_ext3"] = $ope_auth_ext3;
	$saveData["ope_auth_ext4"] = $ope_auth_ext4;
	$saveData["ope_auth_ext5"] = $ope_auth_ext5;

	//ドメイン更新実行
	$saveData["updated"] = $now_timestamp;
	$saveData["updated_by"] = $session->user["user_id"];
	if(!$OperationAuth->update($where, $saveData)){
		DB::rollBack();
		Logger::error("機能操作権限更新に失敗しました。",$saveData);
		Location::redirect($redirect);
	}

	DB::commit();

	if($close == "on"){
		//一覧画面に遷移する
		$redirectParam["master_table"] = "operationauth";
		Location::redirect($close_url,$redirectParam);
	}else{
		//同画面に遷移する
		$redirectParam["operationauth_id"] = $operationauth_id;
		$redirectParam["close_url"] = $close_url;
		$redirectParam["mode"] = "edit";
		$redirectParam["message[]"] = "保存しました。";
		Location::redirect($self,$redirectParam);
	}
}

//削除処理
if($mode == "edit" && $action == "delete" && $error == array()){
	DB::beginTransaction();
	$OperationAuth->delete(array("operationauth_id" => $operationauth_id));
	DB::commit();

	//一覧画面に遷移する
	$redirectParam["master_table"] = "operationauth";
	Location::redirect($close_url,$redirectParam);
}

//初期表示
if($action == ""){
	if($mode == "edit"){
		//ドメインデータを取得
		$operationauthData = $OperationAuth->getDataByPrimaryKey($operationauth_id);
		$operationauth_name = $operationauthData["operationauth_name"];
		$ope_auth_page_view = $operationauthData["ope_auth_page_view"];
		$ope_auth_page_add = $operationauthData["ope_auth_page_add"];
		$ope_auth_page_edit = $operationauthData["ope_auth_page_edit"];
		$ope_auth_page_delete = $operationauthData["ope_auth_page_delete"];
		$ope_auth_page_workflow = $operationauthData["ope_auth_page_workflow"];
		$ope_auth_page_publish = $operationauthData["ope_auth_page_publish"];
		$ope_auth_template_view = $operationauthData["ope_auth_template_view"];
		$ope_auth_template_add = $operationauthData["ope_auth_template_add"];
		$ope_auth_template_edit = $operationauthData["ope_auth_template_edit"];
		$ope_auth_template_delete = $operationauthData["ope_auth_template_delete"];
		$ope_auth_template_workflow = $operationauthData["ope_auth_template_workflow"];
		$ope_auth_template_publish = $operationauthData["ope_auth_template_publish"];
		$ope_auth_parts_view = $operationauthData["ope_auth_parts_view"];
		$ope_auth_parts_add = $operationauthData["ope_auth_parts_add"];
		$ope_auth_parts_edit = $operationauthData["ope_auth_parts_edit"];
		$ope_auth_parts_delete = $operationauthData["ope_auth_parts_delete"];
		$ope_auth_parts_workflow = $operationauthData["ope_auth_parts_workflow"];
		$ope_auth_parts_publish = $operationauthData["ope_auth_parts_publish"];
		$ope_auth_stylesheet_view = $operationauthData["ope_auth_stylesheet_view"];
		$ope_auth_stylesheet_add = $operationauthData["ope_auth_stylesheet_add"];
		$ope_auth_stylesheet_edit = $operationauthData["ope_auth_stylesheet_edit"];
		$ope_auth_stylesheet_delete = $operationauthData["ope_auth_stylesheet_delete"];
		$ope_auth_stylesheet_workflow = $operationauthData["ope_auth_stylesheet_workflow"];
		$ope_auth_stylesheet_publish = $operationauthData["ope_auth_stylesheet_publish"];
		$ope_auth_script_view = $operationauthData["ope_auth_script_view"];
		$ope_auth_script_add = $operationauthData["ope_auth_script_add"];
		$ope_auth_script_edit = $operationauthData["ope_auth_script_edit"];
		$ope_auth_script_delete = $operationauthData["ope_auth_script_delete"];
		$ope_auth_script_workflow = $operationauthData["ope_auth_script_workflow"];
		$ope_auth_script_publish = $operationauthData["ope_auth_script_publish"];
		$ope_auth_file_admin = $operationauthData["ope_auth_file_admin"];
		$ope_auth_user_admin = $operationauthData["ope_auth_user_self"];	//仮
		$ope_auth_website = $operationauthData["ope_auth_website"];
		$ope_auth_ext1 = $operationauthData["ope_auth_ext1"];
		$ope_auth_ext2 = $operationauthData["ope_auth_ext2"];
		$ope_auth_ext3 = $operationauthData["ope_auth_ext3"];
		$ope_auth_ext4 = $operationauthData["ope_auth_ext4"];
		$ope_auth_ext5 = $operationauthData["ope_auth_ext5"];
	}
}

$LayoutManager = new LayoutManagerAdminMain();
$LayoutManager->setTitle("機能操作権限編集");
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
<input type="hidden" name="master_table" value="<?php echo "operationauth"; //マスタ選択画面区分 ?>" />
<input type="hidden" name="operationauth_id" value="<?php echo $operationauth_id; //ID ?>" />
<input type="hidden" name="action" value="<?php //javascriptから設定 ?>" />
<input type="hidden" name="close" value="<?php //javascriptから設定 ?>" />
<table class="edit" cellspacing="0">
    <tr>
    <th class="w400">機能操作権限名<span class="mark orange">必須</span></th>
    <td>
    <input type="text" name="operationauth_name" value="<?php echo htmlspecialchars($operationauth_name);?>" />
    </td>
    </tr>

    <tr>
    <th>ページ閲覧権限<span class="mark orange">必須</span></th>
    <td>
    <?php if($ope_auth_page_view == "1"){ $checked0 = ""; $checked1 = "checked"; } else { $checked0 = "checked"; $checked1 = ""; } ?>
    <input type="radio" name="ope_auth_page_view" value="0" <?php echo $checked0; ?> />無効&nbsp;
    <input type="radio" name="ope_auth_page_view" value="1" <?php echo $checked1; ?> />有効
    </td>
    </tr>

    <tr>
    <th>ページ作成権限<span class="mark orange">必須</span></th>
    <td>
    <?php if($ope_auth_page_add == "1"){ $checked0 = ""; $checked1 = "checked"; } else { $checked0 = "checked"; $checked1 = ""; } ?>
    <input type="radio" name="ope_auth_page_add" value="0" <?php echo $checked0; ?> />無効&nbsp;
    <input type="radio" name="ope_auth_page_add" value="1" <?php echo $checked1; ?> />有効
    </td>
    </tr>

    <tr>
    <th>ページ編集権限<span class="mark orange">必須</span></th>
    <td>
    <?php if($ope_auth_page_edit == "1"){ $checked0 = ""; $checked1 = "checked"; } else { $checked0 = "checked"; $checked1 = ""; } ?>
    <input type="radio" name="ope_auth_page_edit" value="0" <?php echo $checked0; ?> />無効&nbsp;
    <input type="radio" name="ope_auth_page_edit" value="1" <?php echo $checked1; ?> />有効
    </td>
    </tr>

    <tr>
    <th>ページ削除権限<span class="mark orange">必須</span></th>
    <td>
    <?php if($ope_auth_page_delete == "1"){ $checked0 = ""; $checked1 = "checked"; } else { $checked0 = "checked"; $checked1 = ""; } ?>
    <input type="radio" name="ope_auth_page_delete" value="0" <?php echo $checked0; ?> />無効&nbsp;
    <input type="radio" name="ope_auth_page_delete" value="1" <?php echo $checked1; ?> />有効
    </td>
    </tr>

    <tr>
    <th>ページワークフロー実行権限<span class="mark orange">必須</span></th>
    <td>
    <?php if($ope_auth_page_workflow == "1"){ $checked0 = ""; $checked1 = "checked"; } else { $checked0 = "checked"; $checked1 = ""; } ?>
    <input type="radio" name="ope_auth_page_workflow" value="0" <?php echo $checked0; ?> />無効&nbsp;
    <input type="radio" name="ope_auth_page_workflow" value="1" <?php echo $checked1; ?> />有効
    </td>
    </tr>

    <tr>
    <th>ページ公開権限<span class="mark orange">必須</span></th>
    <td>
    <?php if($ope_auth_page_publish == "1"){ $checked0 = ""; $checked1 = "checked"; } else { $checked0 = "checked"; $checked1 = ""; } ?>
    <input type="radio" name="ope_auth_page_publish" value="0" <?php echo $checked0; ?> />無効&nbsp;
    <input type="radio" name="ope_auth_page_publish" value="1" <?php echo $checked1; ?> />有効
    </td>
    </tr>

    <tr>
    <th>テンプレート閲覧権限<span class="mark orange">必須</span></th>
    <td>
    <?php if($ope_auth_template_view == "1"){ $checked0 = ""; $checked1 = "checked"; } else { $checked0 = "checked"; $checked1 = ""; } ?>
    <input type="radio" name="ope_auth_template_view" value="0" <?php echo $checked0; ?> />無効&nbsp;
    <input type="radio" name="ope_auth_template_view" value="1" <?php echo $checked1; ?> />有効
    </td>
    </tr>

    <tr>
    <th>テンプレート作成権限<span class="mark orange">必須</span></th>
    <td>
    <?php if($ope_auth_template_add == "1"){ $checked0 = ""; $checked1 = "checked"; } else { $checked0 = "checked"; $checked1 = ""; } ?>
    <input type="radio" name="ope_auth_template_add" value="0" <?php echo $checked0; ?> />無効&nbsp;
    <input type="radio" name="ope_auth_template_add" value="1" <?php echo $checked1; ?> />有効
    </td>
    </tr>

    <tr>
    <th>テンプレート編集権限<span class="mark orange">必須</span></th>
    <td>
    <?php if($ope_auth_template_edit == "1"){ $checked0 = ""; $checked1 = "checked"; } else { $checked0 = "checked"; $checked1 = ""; } ?>
    <input type="radio" name="ope_auth_template_edit" value="0" <?php echo $checked0; ?> />無効&nbsp;
    <input type="radio" name="ope_auth_template_edit" value="1" <?php echo $checked1; ?> />有効
    </td>
    </tr>

    <tr>
    <th>テンプレート削除権限<span class="mark orange">必須</span></th>
    <td>
    <?php if($ope_auth_template_delete == "1"){ $checked0 = ""; $checked1 = "checked"; } else { $checked0 = "checked"; $checked1 = ""; } ?>
    <input type="radio" name="ope_auth_template_delete" value="0" <?php echo $checked0; ?> />無効&nbsp;
    <input type="radio" name="ope_auth_template_delete" value="1" <?php echo $checked1; ?> />有効
    </td>
    </tr>

    <tr>
    <th>テンプレートワークフロー実行権限<span class="mark orange">必須</span></th>
    <td>
    <?php if($ope_auth_template_workflow == "1"){ $checked0 = ""; $checked1 = "checked"; } else { $checked0 = "checked"; $checked1 = ""; } ?>
    <input type="radio" name="ope_auth_template_workflow" value="0" <?php echo $checked0; ?> />無効&nbsp;
    <input type="radio" name="ope_auth_template_workflow" value="1" <?php echo $checked1; ?> />有効
    </td>
    </tr>

    <tr>
    <th>テンプレート公開権限<span class="mark orange">必須</span></th>
    <td>
    <?php if($ope_auth_template_publish == "1"){ $checked0 = ""; $checked1 = "checked"; } else { $checked0 = "checked"; $checked1 = ""; } ?>
    <input type="radio" name="ope_auth_template_publish" value="0" <?php echo $checked0; ?> />無効&nbsp;
    <input type="radio" name="ope_auth_template_publish" value="1" <?php echo $checked1; ?> />有効
    </td>
    </tr>

    <tr>
    <th>部品閲覧権限<span class="mark orange">必須</span></th>
    <td>
    <?php if($ope_auth_parts_view == "1"){ $checked0 = ""; $checked1 = "checked"; } else { $checked0 = "checked"; $checked1 = ""; } ?>
    <input type="radio" name="ope_auth_parts_view" value="0" <?php echo $checked0; ?> />無効&nbsp;
    <input type="radio" name="ope_auth_parts_view" value="1" <?php echo $checked1; ?> />有効
    </td>
    </tr>

    <tr>
    <th>部品作成権限<span class="mark orange">必須</span></th>
    <td>
    <?php if($ope_auth_parts_add == "1"){ $checked0 = ""; $checked1 = "checked"; } else { $checked0 = "checked"; $checked1 = ""; } ?>
    <input type="radio" name="ope_auth_parts_add" value="0" <?php echo $checked0; ?> />無効&nbsp;
    <input type="radio" name="ope_auth_parts_add" value="1" <?php echo $checked1; ?> />有効
    </td>
    </tr>

    <tr>
    <th>部品編集権限<span class="mark orange">必須</span></th>
    <td>
    <?php if($ope_auth_parts_edit == "1"){ $checked0 = ""; $checked1 = "checked"; } else { $checked0 = "checked"; $checked1 = ""; } ?>
    <input type="radio" name="ope_auth_parts_edit" value="0" <?php echo $checked0; ?> />無効&nbsp;
    <input type="radio" name="ope_auth_parts_edit" value="1" <?php echo $checked1; ?> />有効
    </td>
    </tr>

    <tr>
    <th>部品削除権限<span class="mark orange">必須</span></th>
    <td>
    <?php if($ope_auth_parts_delete == "1"){ $checked0 = ""; $checked1 = "checked"; } else { $checked0 = "checked"; $checked1 = ""; } ?>
    <input type="radio" name="ope_auth_parts_delete" value="0" <?php echo $checked0; ?> />無効&nbsp;
    <input type="radio" name="ope_auth_parts_delete" value="1" <?php echo $checked1; ?> />有効
    </td>
    </tr>

    <tr>
    <th>部品ワークフロー実行権限<span class="mark orange">必須</span></th>
    <td>
    <?php if($ope_auth_parts_workflow == "1"){ $checked0 = ""; $checked1 = "checked"; } else { $checked0 = "checked"; $checked1 = ""; } ?>
    <input type="radio" name="ope_auth_parts_workflow" value="0" <?php echo $checked0; ?> />無効&nbsp;
    <input type="radio" name="ope_auth_parts_workflow" value="1" <?php echo $checked1; ?> />有効
    </td>
    </tr>

    <tr>
    <th>部品公開権限<span class="mark orange">必須</span></th>
    <td>
    <?php if($ope_auth_parts_publish == "1"){ $checked0 = ""; $checked1 = "checked"; } else { $checked0 = "checked"; $checked1 = ""; } ?>
    <input type="radio" name="ope_auth_parts_publish" value="0" <?php echo $checked0; ?> />無効&nbsp;
    <input type="radio" name="ope_auth_parts_publish" value="1" <?php echo $checked1; ?> />有効
    </td>
    </tr>

    <tr>
    <th>スタイルシート閲覧権限<span class="mark orange">必須</span></th>
    <td>
    <?php if($ope_auth_stylesheet_view == "1"){ $checked0 = ""; $checked1 = "checked"; } else { $checked0 = "checked"; $checked1 = ""; } ?>
    <input type="radio" name="ope_auth_stylesheet_view" value="0" <?php echo $checked0; ?> />無効&nbsp;
    <input type="radio" name="ope_auth_stylesheet_view" value="1" <?php echo $checked1; ?> />有効
    </td>
    </tr>

    <tr>
    <th>スタイルシート作成権限<span class="mark orange">必須</span></th>
    <td>
    <?php if($ope_auth_stylesheet_add == "1"){ $checked0 = ""; $checked1 = "checked"; } else { $checked0 = "checked"; $checked1 = ""; } ?>
    <input type="radio" name="ope_auth_stylesheet_add" value="0" <?php echo $checked0; ?> />無効&nbsp;
    <input type="radio" name="ope_auth_stylesheet_add" value="1" <?php echo $checked1; ?> />有効
    </td>
    </tr>

    <tr>
    <th>スタイルシート編集権限<span class="mark orange">必須</span></th>
    <td>
    <?php if($ope_auth_stylesheet_edit == "1"){ $checked0 = ""; $checked1 = "checked"; } else { $checked0 = "checked"; $checked1 = ""; } ?>
    <input type="radio" name="ope_auth_stylesheet_edit" value="0" <?php echo $checked0; ?> />無効&nbsp;
    <input type="radio" name="ope_auth_stylesheet_edit" value="1" <?php echo $checked1; ?> />有効
    </td>
    </tr>

    <tr>
    <th>スタイルシート削除権限<span class="mark orange">必須</span></th>
    <td>
    <?php if($ope_auth_stylesheet_delete == "1"){ $checked0 = ""; $checked1 = "checked"; } else { $checked0 = "checked"; $checked1 = ""; } ?>
    <input type="radio" name="ope_auth_stylesheet_delete" value="0" <?php echo $checked0; ?> />無効&nbsp;
    <input type="radio" name="ope_auth_stylesheet_delete" value="1" <?php echo $checked1; ?> />有効
    </td>
    </tr>

    <tr>
    <th>スタイルシートワークフロー実行権限<span class="mark orange">必須</span></th>
    <td>
    <?php if($ope_auth_stylesheet_workflow == "1"){ $checked0 = ""; $checked1 = "checked"; } else { $checked0 = "checked"; $checked1 = ""; } ?>
    <input type="radio" name="ope_auth_stylesheet_workflow" value="0" <?php echo $checked0; ?> />無効&nbsp;
    <input type="radio" name="ope_auth_stylesheet_workflow" value="1" <?php echo $checked1; ?> />有効
    </td>
    </tr>

    <tr>
    <th>スタイルシート公開権限<span class="mark orange">必須</span></th>
    <td>
    <?php if($ope_auth_stylesheet_publish == "1"){ $checked0 = ""; $checked1 = "checked"; } else { $checked0 = "checked"; $checked1 = ""; } ?>
    <input type="radio" name="ope_auth_stylesheet_publish" value="0" <?php echo $checked0; ?> />無効&nbsp;
    <input type="radio" name="ope_auth_stylesheet_publish" value="1" <?php echo $checked1; ?> />有効
    </td>
    </tr>

    <tr>
    <th>スクリプト閲覧権限<span class="mark orange">必須</span></th>
    <td>
    <?php if($ope_auth_script_view == "1"){ $checked0 = ""; $checked1 = "checked"; } else { $checked0 = "checked"; $checked1 = ""; } ?>
    <input type="radio" name="ope_auth_script_view" value="0" <?php echo $checked0; ?> />無効&nbsp;
    <input type="radio" name="ope_auth_script_view" value="1" <?php echo $checked1; ?> />有効
    </td>
    </tr>

    <tr>
    <th>スクリプト作成権限<span class="mark orange">必須</span></th>
    <td>
    <?php if($ope_auth_script_add == "1"){ $checked0 = ""; $checked1 = "checked"; } else { $checked0 = "checked"; $checked1 = ""; } ?>
    <input type="radio" name="ope_auth_script_add" value="0" <?php echo $checked0; ?> />無効&nbsp;
    <input type="radio" name="ope_auth_script_add" value="1" <?php echo $checked1; ?> />有効
    </td>
    </tr>

    <tr>
    <th>スクリプト編集権限<span class="mark orange">必須</span></th>
    <td>
    <?php if($ope_auth_script_edit == "1"){ $checked0 = ""; $checked1 = "checked"; } else { $checked0 = "checked"; $checked1 = ""; } ?>
    <input type="radio" name="ope_auth_script_edit" value="0" <?php echo $checked0; ?> />無効&nbsp;
    <input type="radio" name="ope_auth_script_edit" value="1" <?php echo $checked1; ?> />有効
    </td>
    </tr>

    <tr>
    <th>スクリプト削除権限<span class="mark orange">必須</span></th>
    <td>
    <?php if($ope_auth_script_delete == "1"){ $checked0 = ""; $checked1 = "checked"; } else { $checked0 = "checked"; $checked1 = ""; } ?>
    <input type="radio" name="ope_auth_script_delete" value="0" <?php echo $checked0; ?> />無効&nbsp;
    <input type="radio" name="ope_auth_script_delete" value="1" <?php echo $checked1; ?> />有効
    </td>
    </tr>

    <tr>
    <th>スクリプトワークフロー実行権限<span class="mark orange">必須</span></th>
    <td>
    <?php if($ope_auth_script_workflow == "1"){ $checked0 = ""; $checked1 = "checked"; } else { $checked0 = "checked"; $checked1 = ""; } ?>
    <input type="radio" name="ope_auth_script_workflow" value="0" <?php echo $checked0; ?> />無効&nbsp;
    <input type="radio" name="ope_auth_script_workflow" value="1" <?php echo $checked1; ?> />有効
    </td>
    </tr>

    <tr>
    <th>スクリプト公開権限<span class="mark orange">必須</span></th>
    <td>
    <?php if($ope_auth_script_publish == "1"){ $checked0 = ""; $checked1 = "checked"; } else { $checked0 = "checked"; $checked1 = ""; } ?>
    <input type="radio" name="ope_auth_script_publish" value="0" <?php echo $checked0; ?> />無効&nbsp;
    <input type="radio" name="ope_auth_script_publish" value="1" <?php echo $checked1; ?> />有効
    </td>
    </tr>

    <tr>
    <th>ファイル管理者権限<span class="mark orange">必須</span></th>
    <td>
    <?php if($ope_auth_file_admin == "1"){ $checked0 = ""; $checked1 = "checked"; } else { $checked0 = "checked"; $checked1 = ""; } ?>
    <input type="radio" name="ope_auth_file_admin" value="0" <?php echo $checked0; ?> />無効&nbsp;
    <input type="radio" name="ope_auth_file_admin" value="1" <?php echo $checked1; ?> />有効
    </td>
    </tr>

    <tr>
    <th>ユーザ管理者権限<span class="mark orange">必須</span></th>
    <td>
    <?php if($ope_auth_user_admin == "1"){ $checked0 = ""; $checked1 = "checked"; } else { $checked0 = "checked"; $checked1 = ""; } ?>
    <input type="radio" name="ope_auth_user_admin" value="0" <?php echo $checked0; ?> />無効&nbsp;
    <input type="radio" name="ope_auth_user_admin" value="1" <?php echo $checked1; ?> />有効
    </td>
    </tr

    ><tr>
    <th>Webサイト全体設定権限<span class="mark orange">必須</span></th>
    <td>
    <?php if($ope_auth_website == "1"){ $checked0 = ""; $checked1 = "checked"; } else { $checked0 = "checked"; $checked1 = ""; } ?>
    <input type="radio" name="ope_auth_website" value="0" <?php echo $checked0; ?> />無効&nbsp;
    <input type="radio" name="ope_auth_website" value="1" <?php echo $checked1; ?> />有効
    </td>
    </tr>

    <?php if(Config::get("addon_module1_active")): ?>
	    <tr>
	    <th><?php echo Config::get("addon_module1_name");?>操作権限<span class="mark orange">必須</span></th>
	    <td>
	    <?php if($ope_auth_ext1 == "1"){ $checked0 = ""; $checked1 = "checked"; } else { $checked0 = "checked"; $checked1 = ""; } ?>
	    <input type="radio" name="ope_auth_ext1" value="0" <?php echo $checked0; ?> />無効&nbsp;
	    <input type="radio" name="ope_auth_ext1" value="1" <?php echo $checked1; ?> />有効
	    </td>
	    </tr>
	<?php endif; ?>

	<?php if(Config::get("addon_module2_active")): ?>
	    <tr>
	    <th><?php echo Config::get("addon_module2_name");?>操作権限<span class="mark orange">必須</span></th>
	    <td>
	    <?php if($ope_auth_ext2 == "1"){ $checked0 = ""; $checked1 = "checked"; } else { $checked0 = "checked"; $checked1 = ""; } ?>
	    <input type="radio" name="ope_auth_ext2" value="0" <?php echo $checked0; ?> />無効&nbsp;
	    <input type="radio" name="ope_auth_ext2" value="1" <?php echo $checked1; ?> />有効
	    </td>
	    </tr>
	<?php endif; ?>

	<?php if(Config::get("addon_module3_active")): ?>
	    <tr>
	    <th><?php echo Config::get("addon_module3_name");?>操作権限<span class="mark orange">必須</span></th>
	    <td>
	    <?php if($ope_auth_ext3 == "1"){ $checked0 = ""; $checked1 = "checked"; } else { $checked0 = "checked"; $checked1 = ""; } ?>
	    <input type="radio" name="ope_auth_ext3" value="0" <?php echo $checked0; ?> />無効&nbsp;
	    <input type="radio" name="ope_auth_ext3" value="1" <?php echo $checked1; ?> />有効
	    </td>
	    </tr>
	<?php endif; ?>

	<?php if(Config::get("addon_module4_active")): ?>
	    <tr>
	    <th><?php echo Config::get("addon_module4_name");?>操作権限<span class="mark orange">必須</span></th>
	    <td>
	    <?php if($ope_auth_ext4 == "1"){ $checked0 = ""; $checked1 = "checked"; } else { $checked0 = "checked"; $checked1 = ""; } ?>
	    <input type="radio" name="ope_auth_ext4" value="0" <?php echo $checked0; ?> />無効&nbsp;
	    <input type="radio" name="ope_auth_ext4" value="1" <?php echo $checked1; ?> />有効
	    </td>
	    </tr>
	<?php endif; ?>

	<?php if(Config::get("addon_module5_active")): ?>
	    <tr>
	    <th><?php echo Config::get("addon_module5_name");?>操作権限<span class="mark orange">必須</span></th>
	    <td>
	    <?php if($ope_auth_ext5 == "1"){ $checked0 = ""; $checked1 = "checked"; } else { $checked0 = "checked"; $checked1 = ""; } ?>
	    <input type="radio" name="ope_auth_ext5" value="0" <?php echo $checked0; ?> />無効&nbsp;
	    <input type="radio" name="ope_auth_ext5" value="1" <?php echo $checked1; ?> />有効
	    </td>
	    </tr>
	<?php endif; ?>

</table>
</form>
<?php $LayoutManager->footer(); ?>
