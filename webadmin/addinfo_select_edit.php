<?php
// アプリケーション共通クラス読込
require_once(dirname(__FILE__).'/ApplicationCommon/include.php');

require_once(dirname(__FILE__).'/DataAccess/AddInfoSelect.php'); 	//追加情報選択肢クラス

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
$selectname = isset($_REQUEST["selectname"]) ? Util::encodeRequest($_REQUEST["selectname"]) : "";							//追加情報選択肢名
$selectname_display = isset($_REQUEST["selectname_display"]) ? Util::encodeRequest($_REQUEST["selectname_display"]) : "";	//追加情報選択表示名
$options_text = isset($_REQUEST["options_text"]) ? Util::encodeRequest($_REQUEST["options_text"]) : "";						//選択肢テキスト
$options = array();


//起動モードが設定されていない場合
if(Util::IsNullOrEmpty($mode)){
	Logger::notice("起動モードが設定されていませんでした。");
	Location::redirect($redirect);
}

//追加情報選択肢クラス
$AddInfoSelect = new AddInfoSelect();

//入力値チェック
if(($mode == "new" || $mode == "edit") && $action == "save"){
	if($selectname_display == ""){
		$error[] = "選択肢名(管理用)を入力してください。";
	}
	if($selectname == ""){
		$error[] = "選択肢定義名を入力してください。";
	}else{
		if($mode == "new"){
			$cnt = $AddInfoSelect->getCountByParameters(array("selectname" => $selectname));
			if($cnt > 0){
				$error[] = "選択肢定義名は既に登録されています。別の名前を入力してください。";
			}
		}
	}
	if($options_text == ""){
		$error[] = "選択肢一覧を入力してください。";
	}else{
		//選択肢テキスト分解
		$options_str = explode("\n", $options_text);
		$options_str = array_map('trim', $options_str);
		$options_str = array_filter($options_str, 'strlen');
		$options_str = array_values($options_str);

		foreach($options_str as $key => $value){
			$options_arr = explode("=",$value);
			if(count($options_arr) == 2){
				$options[$options_arr[0]] = $options_arr[1];
			}else{
				$error[] = "選択肢一覧のフォーマットが不正です。";
			}
		}
		if(count($options) < 1){
			$error[] = "選択肢一覧のフォーマットが不正です。";
		}
	}
}

//保存処理
if(($mode == "new" || $mode == "edit") && $action == "save" && $error == array()){
	$now_timestamp = time();

	DB::beginTransaction();

	if($mode == "new"){
		//新規追加処理
		$saveData = array();
		$saveData["selectname"] = $selectname;
		$saveData["selectname_display"] = $selectname_display;
		$saveData["active_flg"] = 1;
		$saveData["updated"] = $now_timestamp;
		$saveData["updated_by"] = $session->user["user_id"];
		$saveData["created"] = $now_timestamp;
		$saveData["created_by"] = $session->user["user_id"];
		$count = 0;
		foreach($options as $key => $value){
			$saveData["optionvalue"] = $key;
			$saveData["optionvalue_name"] = $value;
			$saveData["sort_no"] = $count;
			if(!$AddInfoSelect->insert($saveData)){
				DB::rollBack();
				Logger::error("追加情報選択肢追加に失敗しました。",$saveData);
				Location::redirect($redirect);
			}
			$count++;
		}
	}else{
		//既存情報の取得
		$where = array();
		$where["selectname"] = $selectname;
		$addInfoListOld = $AddInfoSelect->getListByParameters($where);
		$addInfoListDelete = $addInfoListOld;

		//更新処理
		$saveData = array();
		$saveData["selectname"] = $selectname;
		$saveData["selectname_display"] = $selectname_display;
		$saveData["active_flg"] = 1;
		$saveData["updated"] = $now_timestamp;
		$saveData["updated_by"] = $session->user["user_id"];
		$count = 0;
		foreach($options as $key => $value){
			$saveData["optionvalue"] = $key;
			$saveData["optionvalue_name"] = $value;
			$saveData["sort_no"] = $count;
			$where = array();
			$where["selectname"] = $selectname;
			$where["optionvalue"] = $key;
			$cnt = $AddInfoSelect->getCountByParameters($where);
			if($cnt > 0){
				if(!$AddInfoSelect->update($where, $saveData)){
					DB::rollBack();
					Logger::error("追加情報選択肢更新に失敗しました。",$saveData);
					Location::redirect($redirect);
				}
				foreach($addInfoListDelete as $deleteKey => $deleteValue){
					if($key == $deleteValue["optionvalue"]){
						unset($addInfoListDelete[$deleteKey]);
						break;
					}
				}
			}else{
				$saveData["created"] = $now_timestamp;
				$saveData["created_by"] = $session->user["user_id"];
				if(!$AddInfoSelect->insert($saveData)){
					DB::rollBack();
					Logger::error("追加情報選択肢追加に失敗しました。",$saveData);
					Location::redirect($redirect);
				}
			}
			$count++;
		}
		//削除処理
		foreach($addInfoListDelete as $value){
			if(!$AddInfoSelect->delete($value)){
				DB::rollBack();
				Logger::error("追加情報選択肢削除に失敗しました。",$value);
				Location::redirect($redirect);
			}
		}
	}

	DB::commit();

	if($close == "on"){
		//一覧画面に遷移する
		$redirectParam["master_table"] = "addinfo_select";
		Location::redirect($close_url,$redirectParam);
	}else{
		//同画面に遷移する
		$redirectParam["selectname"] = $selectname;
		$redirectParam["close_url"] = $close_url;
		$redirectParam["mode"] = "edit";
		$redirectParam["message[]"] = "保存しました。";
		Location::redirect($self,$redirectParam);
	}
}

//削除処理
if($mode == "edit" && $action == "delete" && $error == array()){
	DB::beginTransaction();
	$AddInfoSelect->delete(array("selectname" => $selectname));
	DB::commit();

	//一覧画面に遷移する
	$redirectParam["master_table"] = "addinfo_select";
	Location::redirect($close_url,$redirectParam);
}

//初期表示
if($action == ""){
	if($mode == "edit"){
		//追加情報選択肢データを取得
		$where = array();
		$where["selectname"] = $selectname;
		$order = array();
		$order["sort_no"] = "ASC";
		$addInfoSelectList = $AddInfoSelect->getListByParameters($where,$order);
		$selectname_display = $addInfoSelectList[0]["selectname_display"];

		$options_text = "";
		for($i=0;$i<count($addInfoSelectList);$i++){
			$options_text.= $addInfoSelectList[$i]["optionvalue"]."=".$addInfoSelectList[$i]["optionvalue_name"]."\r\n";
		}
	}
}

$LayoutManager = new LayoutManagerAdminMain();
$LayoutManager->setTitle("追加情報選択肢編集");
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
<input type="hidden" name="master_table" value="<?php echo "addinfo_select"; //選択肢名 ?>" />
<input type="hidden" name="action" value="<?php //javascriptから設定 ?>" />
<input type="hidden" name="close" value="<?php //javascriptから設定 ?>" />
<table class="edit" cellspacing="0">
    <tr>
    <th class="w240">選択肢名（管理用）<span class="mark orange">必須</span></th>
    <td>
    <input type="text" name="selectname_display" value="<?php echo htmlspecialchars($selectname_display);?>" />
    </td>
    </tr>

    <tr>
    <th>選択肢定義名<span class="mark orange">必須</span></th>
    <td>
    <?php if($mode == "edit"): ?>
    	<input type="text" value="<?php echo htmlspecialchars($selectname);?>" disabled /><br/>
        <span class="info">システムからの参照時に利用します。半角英数字のみ利用可。</span>
    	<input type="hidden"  name="selectname" value="<?php echo htmlspecialchars($selectname);?>"/>
    <?php else: ?>
    	<input type="text" name="selectname" value="<?php echo htmlspecialchars($selectname);?>" /><br/>
        <span class="info">システムからの参照時に利用します。半角英数字のみ利用可。</span>
    <?php endif; ?>
    </td>
    </tr>

    <tr>
    <th>選択肢一覧<span class="mark orange">必須</span></th>
    <td>
    <textarea rows="5" cols="30" name="options_text"><?php echo $options_text; ?></textarea><br/>
    <span class="info">値=表示名 のフォーマットで複数行入力可。</span>
    </td>
    </tr>

</table>
</form>
<?php $LayoutManager->footer(); ?>
