<?php
//アプリケーション共通クラス読込
require_once(dirname(__FILE__).'/../../ApplicationCommon/include.php');
require_once(dirname(__FILE__).'/../../DataAccess/AddInfoSelect.php'); 			//選択肢クラス
require_once(dirname(__FILE__).'/Common/LayoutManagerVoice.php');				//患者様の声レイアウトマネージャクラス
require_once(dirname(__FILE__).'/DataAccess/VoiceContent.php'); 			//患者様の声コンテンツクラス

//基本設定
$self = basename(__FILE__);				//自身のファイル名
$error = array();						//エラー情報格納用配列
$message = array();						//メッセージ情報格納用配列
$alert = array();						//アラート情報格納用配列
$redirect = "../../index.php";				//エラー時リダイレクト先
$restrict = array();					//表示・操作制限

//セッション取得
$session = Session::get();

//セッション認証
$session->loginCheckAndRedirect("../../login.php",array("admin_flg" => 1));

//コンテンツクラス読込
$VoiceContent = new VoiceContent();

//選択肢クラス
$AddInfoSelect = new AddInfoSelect();

//リクエストパラメータ取得
$mode = isset($_REQUEST["mode"]) ? $_REQUEST["mode"] : "";							//起動モード
$action = isset($_REQUEST["action"]) ? $_REQUEST["action"] : "";					//実行アクション
$close = isset($_REQUEST["close"]) ? $_REQUEST["close"] : "";						//閉じるフラグ
$close_url = isset($_REQUEST["close_url"]) ? $_REQUEST["close_url"] : "";			//戻り先URL
$message = isset($_REQUEST["message"]) ? $_REQUEST["message"] : $message;			//メッセージ
$id = isset($_REQUEST["id"]) ? $_REQUEST["id"] : "";		//コンテンツID

$age = isset($_REQUEST["age"]) ? $_REQUEST["age"] : "";								//年代
$gender = isset($_REQUEST["gender"]) ? $_REQUEST["gender"] : "";					//性別
$date_year = isset($_REQUEST["date_year"]) ? $_REQUEST["date_year"] : "";			//表示用日付(年))
$date_month = isset($_REQUEST["date_month"]) ? $_REQUEST["date_month"] : "";		//表示用日付(月)
$comment = isset($_REQUEST["comment"]) ? $_REQUEST["comment"] : "";					//コンテンツ内容
$status = isset($_REQUEST["status"]) ? $_REQUEST["status"] : "0";					//ステータス


//起動モードが設定されていない場合
if(Util::IsNullOrEmpty($mode)){
	Logger::notice("起動モードが設定されていませんでした。");
	Location::redirect($redirect);
}

//エラーチェック
if(($mode == "new" || $mode == "edit") && $action == "save"){
	//年代チェック
	if(!ctype_digit($age)){
		$age = 0;
	}
	//性別
	if(!ctype_digit($gender)){
		$gender = 0;
	}
	//表示用日付チェック
	if($date_year != ""){
		$date_year = mb_convert_kana( $date_year , "a" , Config::DEFAULT_ENCODE);
		$date_year_check = sprintf('%04d', $date_year);
		if(!preg_match('/^[0-9]+$/', $date_year)){
			$error[] = "表示用日付年は数字で入力してください。";
		}else if(mb_strlen($date_year) != 4 || $date_year == "0000"){
			$error[] = "表示用日付年は4桁で入力してください。";
		}
	}else{
		$error[] = "表示用日付年を入力してください。";
	}

	if($date_month != ""){
		$date_month = mb_convert_kana( $date_month , "a" , Config::DEFAULT_ENCODE);
		$date_month_check = sprintf('%02d', $date_month);
		if(!preg_match('/^[0-9]+$/', $date_month)){
			$error[] = "表示用日付月は数字で入力してください。";
		}else if(mb_strlen($date_month_check) != 2 || $date_month_check == "00"){
			$error[] = "表示用日付月は1~2桁で且つ0以外を入力してください。";
		}
	}else{
		$error[] = "表示用日付月を入力してください。";
	}

	//ステータスチェック
	if(!ctype_digit($status)){
		$status = 1;
	}
}

//保存処理
if(($mode == "new" || $mode == "edit") && $action == "save" && $error == array()){
	DB::beginTransaction();
	//コンテンツ更新データ設定
	$saveData = array();
	$saveData["date_year"] = htmlspecialchars($date_year);
	$saveData["date_month"] = htmlspecialchars($date_month);
	$saveData["age"] = $age;
	$saveData["gender"] = $gender;
	$saveData["comment"] = htmlspecialchars($comment);
	$saveData["status"] = $status;
	$saveData["renewal"] = date('Y-m-d H-i-s');

	if($mode == "new"){
		$saveData["create"] = date('Y-m-d H-i-s');

		$result = $VoiceContent->insert($saveData);
		$id = $VoiceContent->LAST_INSERT_ID("id");
	}else{
		$where = array();
		$where["id"] = $id;
		$result = $VoiceContent->update($where, $saveData);
	}

	if(!$result){
		DB::rollBack();
		Logger::error("保存に失敗しました。",$saveData);
		Location::redirect($redirect);
	}
	DB::commit();

	if($close == "on"){
		//一覧画面に遷移する
		Location::redirect($close_url);
	}else{
		//同画面に遷移する
		$redirectParam["id"] = $id;
		$redirectParam["close_url"] = $close_url;
		$redirectParam["mode"] = "edit";
		$redirectParam["message[]"] = "保存しました。";
		Location::redirect($self,$redirectParam);
	}
}

//初期表示
if($action == "" && $mode == "edit"){
	//コンテンツの取得
	$where = array();
	$where["id"] = $id;
	$VoiceContentData = $VoiceContent->getDataByParameters($where);

	//表示変数
	$age = $VoiceContentData["age"];
	$gender = $VoiceContentData["gender"];
	$date_year = $VoiceContentData["date_year"];
	$date_month =$VoiceContentData["date_month"];
	$comment = $VoiceContentData["comment"];
	$status = $VoiceContentData["status"];
}

//選択肢一覧を取得
$where = array();
$where["selectname"] = "voice_age";
$order = array();
$order["optionvalue"] = "ASC";
$voiceYearList = $AddInfoSelect->getListByParameters($where,$order);


$LayoutManager = new LayoutManagerVoice();
$LayoutManager->setTitle("お知らせ編集");
$LayoutManager->setErrorList($error);
$LayoutManager->setMessageList($message);
$LayoutManager->setAlertList($alert);
$LayoutManager->header();
?>
<script>
//編集判定用
var editflg = false;
$(function(){
	$("form").change(function(){
		editflg = true;
	});

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
		if(editflg){
			if (!confirm('編集内容が破棄されますがよろしいですか？')) {
		        return false;
		    }
		}
		$('#values').attr({
		       'action':'<?php echo $close_url; ?>',
		       'method':'post'
		     });
		$('#values').submit();
	});
});
</script>


<form action="/" method="post" id="values" enctype="multipart/form-data">

<div class="search">
<input class="btn btn_small" type="button" value="一覧に戻る" id="back_to_list" />
<input class="btn btn_small" type="button" value="保存する" id="action_save" />
<input class="btn btn_small" type="button" value="保存して閉じる" id="action_save_close" />
</div>

<!-- メッセージ出力開始 -->
<?php echo $LayoutManager->alert(); 	//アラート出力 ?>
<?php echo $LayoutManager->error(); 	//エラー出力 ?>
<?php echo $LayoutManager->message(); 	//メッセージ出力 ?>
<!-- メッセージ出力終了 -->

<h3>コンテンツ編集</h3>
<input type="hidden" name="id" value="<?php echo htmlspecialchars($id); //コンテンツID ?>" />
<input type="hidden" name="mode" value="<?php echo htmlspecialchars($mode); //動作モード ?>" />
<input type="hidden" name="close_url" value="<?php echo htmlspecialchars($close_url); //戻り先URL ?>" />
<input type="hidden" name="action" value="<?php //javascriptから設定 ?>" />
<input type="hidden" name="close" value="<?php //javascriptから設定 ?>" />

<table class="edit" cellspacing="0">
    <tr>
    <th>年代</th>
    <td>
	<select name="age">
	<?php
	foreach($voiceYearList as $val){
		$selected = "";
		if($val["optionvalue"] == $age){
			$selected = "selected";
		}
		echo '<option value="'.$val["optionvalue"].'" '.$selected.'>'.$val["optionvalue_name"]."代</option>";
	} ?>
	</select>
    </td>
    </tr>
    <tr>
    <th>性別</th>
    <td>
    <input type="radio" name="gender" value="0" <?php if($gender == 0) echo 'checked' ?> /> 男性
	<input type="radio" name="gender" value="1" <?php if($gender == 1) echo 'checked' ?> /> 女性
    </td>
    </tr>
	<tr>
    <th>表示用日付<span class="mark orange">必須</span></th>
    <td>
    <input type="text" name="date_year" value="<?php echo htmlspecialchars($date_year); ?>" />年
    <input type="text" name="date_month" value="<?php echo htmlspecialchars($date_month); ?>" />月
    </td>
    </tr>
	<tr>
    <th>内容<span class="mark orange">必須</span></th>
    <td>
	<textarea name="comment" rows="4" cols="40"><?php echo htmlspecialchars($comment);?></textarea>
	</td>
    </tr>
	<tr>
	<?php if($mode == "edit"){ ?>
    <th>ステータス</th>
    <td>
	<input type="radio" name="status" value="0" <?php if($status == 0) echo 'checked' ?> />公開
	<input type="radio" name="status" value="1" <?php if($status == 1) echo 'checked' ?> />非公開
	</td>
	<?php } ?>
	</tr>
	</table>
</form>
<?php $LayoutManager->footer(); ?>
