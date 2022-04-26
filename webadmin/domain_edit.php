<?php
// アプリケーション共通クラス読込
require_once(dirname(__FILE__).'/ApplicationCommon/include.php');

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
$domain_id = isset($_REQUEST["domain_id"]) ? Util::encodeRequest($_REQUEST["domain_id"]) : "";								//ドメインID
$domain_name = isset($_REQUEST["domain_name"]) ? Util::encodeRequest($_REQUEST["domain_name"]) : "";						//ドメイン管理名
$domain = isset($_REQUEST["domain"]) ? Util::encodeRequest($_REQUEST["domain"]) : "";										//ドメインURL
$base_dir_path = isset($_REQUEST["base_dir_path"]) ? Util::encodeRequest($_REQUEST["base_dir_path"]) : "";					//ベースディレクトリパス
$default_doctype = isset($_REQUEST["default_doctype"]) ? Util::encodeRequest($_REQUEST["default_doctype"]) : "";			//デフォルトDOCTYPE
$default_title_prefix = isset($_REQUEST["default_title_prefix"]) ? Util::encodeRequest($_REQUEST["default_title_prefix"]) : "";			//デフォルトタイトルプレフィックス
$default_title_suffix = isset($_REQUEST["default_title_suffix"]) ? Util::encodeRequest($_REQUEST["default_title_suffix"]) : "";			//デフォルトタイトルサフィックス
$options = array();

//起動モードが設定されていない場合
if(Util::IsNullOrEmpty($mode)){
	Logger::notice("起動モードが設定されていませんでした。");
	Location::redirect($redirect);
}

//追加情報選択肢クラス
$Domain = new Domain();

//入力値チェック
if(($mode == "new" || $mode == "edit") && $action == "save"){
	if($domain_name == ""){
		$error[] = "ドメイン名(管理用)を入力してください。";
	}
	if($domain == ""){
		$error[] = "ドメインURLを入力してください。";
	}else{
		if($mode == "new"){
			$cnt = $Domain->getCountByParameters(array("domain" => $domain));
			if($cnt > 0){
				$error[] = "ドメインURLは既に登録されています。別の名前を入力してください。";
			}
		}else if($mode == "edit"){
			$where = array();
			$where[] = array("domain",$domain);
			$where[] = array("domain_id",$domain_id,"<>");
			$cnt = $Domain->getCountByParameters($where);
			if($cnt > 0){
				$error[] = "ドメインURLは既に登録されています。別の名前を入力してください。";
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
		$insertData["sort_no"] = $Domain->getMaxSort();
		$insertData["created"] = $now_timestamp;
		$insertData["created_by"] = $session->user["user_id"];
		if(!$Domain->insert($insertData)){
			DB::rollBack();
			Logger::error("ドメイン追加に失敗しました。",$insertData);
			Location::redirect($redirect);
		}
		//IDを取得
		$domain_id = $User->last_insert_id();
	}

	//共通更新条件
	$where = array("domain_id" => $domain_id);				//更新条件

	//ドメイン更新データ設定
	$saveData = array();
	$saveData["domain_name"] = $domain_name;
	$saveData["domain"] = $domain;
	$saveData["base_dir_path"] = $base_dir_path;
	$saveData["default_doctype"] = $default_doctype;
	$saveData["default_title_prefix"] = $default_title_prefix;
	$saveData["default_title_suffix"] = $default_title_suffix;

	//ドメイン更新実行
	$saveData["updated"] = $now_timestamp;
	$saveData["updated_by"] = $session->user["user_id"];
	if(!$Domain->update($where, $saveData)){
		DB::rollBack();
		Logger::error("ドメイン更新に失敗しました。",$saveData);
		Location::redirect($redirect);
	}

	DB::commit();

	if($close == "on"){
		//一覧画面に遷移する
		$redirectParam["master_table"] = "domain";
		Location::redirect($close_url,$redirectParam);
	}else{
		//同画面に遷移する
		$redirectParam["domain_id"] = $domain_id;
		$redirectParam["close_url"] = $close_url;
		$redirectParam["mode"] = "edit";
		$redirectParam["message[]"] = "保存しました。";
		Location::redirect($self,$redirectParam);
	}
}

//削除処理
if($mode == "edit" && $action == "delete" && $error == array()){
	DB::beginTransaction();
	$Domain->delete(array("domain_id" => $domain_id));
	DB::commit();

	//一覧画面に遷移する
	$redirectParam["master_table"] = "domain";
	Location::redirect($close_url,$redirectParam);
}

//初期表示
if($action == ""){
	if($mode == "edit"){
		//ドメインデータを取得
		$domainData = $Domain->getDataByPrimaryKey($domain_id);

		$domain_name = $domainData["domain_name"];
		$domain = $domainData["domain"];
		$base_dir_path = $domainData["base_dir_path"];
		$default_doctype = $domainData["default_doctype"];
		$default_title_prefix = $domainData["default_title_prefix"];
		$default_title_suffix = $domainData["default_title_suffix"];
	}
}

$LayoutManager = new LayoutManagerAdminMain();
$LayoutManager->setTitle("ドメイン編集");
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
	<!--
	<input class="btn red btn_small" type="button" value="削除"  id="action_delete"  />
	-->
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
<input type="hidden" name="master_table" value="<?php echo "domain"; //選択肢名 ?>" />
<input type="hidden" name="domain_id" value="<?php echo $domain_id; //ID ?>" />
<input type="hidden" name="action" value="<?php //javascriptから設定 ?>" />
<input type="hidden" name="close" value="<?php //javascriptから設定 ?>" />
<table class="edit" cellspacing="0">
    <tr>
    <th class="w240">ドメイン名（管理用）<span class="mark orange">必須</span></th>
    <td>
    <input type="text" name="domain_name" value="<?php echo htmlspecialchars($domain_name);?>" />
    </td>
    </tr>

    <tr>
    <th>ドメインURL<span class="mark orange">必須</span></th>
    <td>
    <input type="text" name="domain" value="<?php echo htmlspecialchars($domain);?>" />
    </td>
    </tr>

    <tr>
    <th>ベースディレクトリパス<span class="mark orange">必須</span></th>
    <td>
    <input type="text" name="base_dir_path" value="<?php echo htmlspecialchars($base_dir_path);?>" />
    </td>
    </tr>

    <tr>
    <th>デフォルトDOCTYPE<span class="mark orange">必須</span></th>
    <td>
    <input type="text" name="default_doctype" value="<?php echo htmlspecialchars($default_doctype);?>" style="width:90%" />
    </td>
    </tr>

    <tr>
    <th>タイトルプレフィックス</th>
    <td>
    <input type="text" name="default_title_prefix" value="<?php echo htmlspecialchars($default_title_prefix);?>" style="width:90%" />
    </td>
    </tr>

    <tr>
    <th>タイトルサフィックス</th>
    <td>
    <input type="text" name="default_title_suffix" value="<?php echo htmlspecialchars($default_title_suffix);?>" style="width:90%" />
    </td>
    </tr>

</table>
</form>
<?php $LayoutManager->footer(); ?>
