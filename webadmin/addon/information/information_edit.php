<?php
//アプリケーション共通クラス読込
require_once(dirname(__FILE__).'/../../ApplicationCommon/include.php');
require_once(dirname(__FILE__).'/../../CMSCommon/WorkFlowCommon.php');			//ワークフロー共通
require_once(dirname(__FILE__).'/../../DataAccess/AddInfoSelect.php'); 			//選択肢クラス
require_once(dirname(__FILE__).'/../../DataAccess/WorkFlow.php'); 				//ワークフローアクションクラス
require_once(dirname(__FILE__).'/../../DataAccess/WorkFlowState.php'); 			//ワークフロー状態クラス
require_once(dirname(__FILE__).'/Common/LayoutManagerInfo.php');				//お知らせ用レイアウトマネージャクラス
require_once(dirname(__FILE__).'/DataAccess/InformationContent.php'); 			//お知らせコンテンツクラス

//基本設定
$self = basename(__FILE__);				//自身のファイル名
$error = array();						//エラー情報格納用配列
$message = array();						//メッセージ情報格納用配列
$alert = array();						//アラート情報格納用配列
$redirect = "../../index.php";				//エラー時リダイレクト先
$restrict = array();					//表示・操作制限

//基本設定(アドオン) *アドオン機能用の条件（固定値）を記載します。
$contentclass = "parts";
$folder_id = 2;

//セッション取得
$session = Session::get();

//セッション認証
$session->loginCheckAndRedirect("../../login.php",array("admin_flg" => 1));

//お知らせコンテンツクラス
$Content = new InformationContent(InformationContent::TABLE_MANAGEMENT);
$ContentSchedule = new InformationContent(Content::TABLE_SCHEDULE);

//選択肢クラス
$AddInfoSelect = new AddInfoSelect();

//リクエストパラメータ取得
$mode = isset($_REQUEST["mode"]) ? $_REQUEST["mode"] : "";																					//起動モード
$action = isset($_REQUEST["action"]) ? $_REQUEST["action"] : "";																			//実行アクション
$close = isset($_REQUEST["close"]) ? $_REQUEST["close"] : "";																				//閉じるフラグ
$close_url = isset($_REQUEST["close_url"]) ? $_REQUEST["close_url"] : "";																	//戻り先URL
$archive = isset($_REQUEST["archive"]) ? $_REQUEST["archive"] : "";																			//アーカイブ実行フラグ
$publish = isset($_REQUEST["publish"]) ? $_REQUEST["publish"] : "";																			//公開実行フラグ
$message = isset($_REQUEST["message"]) ? $_REQUEST["message"] : $message;																	//メッセージ
$content_id = isset($_REQUEST["content_id"]) ? $_REQUEST["content_id"] : "";																//コンテンツID
$archive_id = isset($_REQUEST["archive_id"]) ? $_REQUEST["archive_id"] : "";																//アーカイブID
$copy_content_id = isset($_REQUEST["copy_content_id"]) ? $_REQUEST["copy_content_id"] : "";													//コピー元コンテンツID
//$folder_id = isset($_REQUEST["folder_id"]) ? $_REQUEST["folder_id"] : "";																	//フォルダID
//$contentclass = isset($_REQUEST["contentclass"]) ? $_REQUEST["contentclass"] : "";														//コンテンツクラス

$search_title_flg = isset($_REQUEST["search_title_flg"]) ? $_REQUEST["search_title_flg"] : "";												//ページタイトル検索フラグ
$search_content_flg = isset($_REQUEST["search_content_flg"]) ? $_REQUEST["search_content_flg"] : "";										//コンテンツ検索フラグ
$search_keyword = isset($_REQUEST["search_keyword"]) ? $_REQUEST["search_keyword"] : "";													//検索キーワード

$workflow_id = isset($_POST["workflow_id"]) ? $_POST["workflow_id"] : "";																	//ワークフローID
$workflow_comment = isset($_POST["workflow_comment"]) ? Util::encodeRequest($_POST["workflow_comment"]) : "";								//ワークフローコメント
$workflowstate_id = isset($_POST["workflowstate_id"]) ? Util::encodeRequest($_POST["workflowstate_id"]) : "";								//ワークフロー状態ID
$workflowstate_name = isset($_POST["workflowstate_name"]) ? Util::encodeRequest($_POST["workflowstate_name"]) : "";							//ワークフロー状態名

$information_category = isset($_POST["information_category"]) ? Util::encodeRequest($_POST["information_category"]) : "";					//カテゴリ
$information_type = isset($_POST["information_type"]) ? Util::encodeRequest($_POST["information_type"]) : "";								//配信先

$title = isset($_POST["title"]) ? Util::encodeRequest($_POST["title"]) : "";																//タイトル(管理用)
$date = isset($_POST["date"]) ? Util::encodeRequest($_POST["date"]) : "";																	//お知らせ日付
$hour = isset($_POST["hour"]) ? Util::encodeRequest($_POST["hour"]) : "";																	//お知らせ時
$minute = isset($_POST["minute"]) ? Util::encodeRequest($_POST["minute"]) : "";															//お知らせ分

$content = isset($_POST["content"]) ? Util::encodeRequest($_POST["content"]) : "";															//コンテンツ
$editmode = isset($_POST["editmode"]) ? $_POST["editmode"] : "";																			//編集モード
$schedule_type = isset($_POST["schedule_type"]) ? $_POST["schedule_type"] : "0";															//スケジュール区分
$schedule_publish_date = isset($_POST["schedule_publish_date"]) ? Util::encodeRequest($_POST["schedule_publish_date"]) : "";				//スケジュール開始年月日
$schedule_publish_hour = isset($_POST["schedule_publish_hour"]) ? $_POST["schedule_publish_hour"] : "";										//スケジュール開始時
$schedule_publish_minute = isset($_POST["schedule_publish_minute"]) ? $_POST["schedule_publish_minute"] : "";								//スケジュール開始分
$schedule_publish_no_check = isset($_POST["schedule_publish_no_check"]) ? $_POST["schedule_publish_no_check"] : "";							//スケジュール開始を指定しない
$schedule_unpublish_date = isset($_POST["schedule_unpublish_date"]) ? Util::encodeRequest($_POST["schedule_unpublish_date"]) : "";			//スケジュール終了年月日
$schedule_unpublish_hour = isset($_POST["schedule_unpublish_hour"]) ? $_POST["schedule_unpublish_hour"] : "";								//スケジュール終了時
$schedule_unpublish_minute = isset($_POST["schedule_unpublish_minute"]) ? $_POST["schedule_unpublish_minute"] : "";							//スケジュール終了分
$schedule_unpublish_no_check = isset($_POST["schedule_unpublish_no_check"]) ? $_POST["schedule_unpublish_no_check"] : "";					//スケジュール終了を指定しない
$delete_schedule_id = isset($_POST["delete_schedule_id"]) ? $_POST["delete_schedule_id"] : "";												//削除対象スケジュールID

//起動モードが設定されていない場合
if(Util::IsNullOrEmpty($mode)){
	Logger::notice("起動モードが設定されていませんでした。");
	Location::redirect($redirect);
}
//権限制御
if($session->user["admintype"] == SPConst::ADMINTYPE_SUPERVISOR){
	$ope_auth_edit = "1";
	$ope_auth_publish = "1";
	$ope_auth_delete = "1";
	//ワークフロー状態判定
	if(Config::get("workflow_mode") == "on" && WorkFlowCommon::checkContentEditAvailable($content_id, $session->user["usergroups"],$session->user["admintype"])){
		$ope_auth_workflow = "1";
	}else{
		$ope_auth_workflow = "0";
	}
}else{
	//ワークフロー状態判定
	if(Config::get("workflow_mode") == "on" && WorkFlowCommon::checkContentEditAvailable($content_id, $session->user["usergroups"],$session->user["admintype"])){
		$ope_auth_edit = $session->user["ope_auth_".$contentclass."_edit"];
		$ope_auth_publish = $session->user["ope_auth_".$contentclass."_publish"];
		$ope_auth_delete = $session->user["ope_auth_".$contentclass."_delete"];
		$ope_auth_workflow = $session->user["ope_auth_".$contentclass."_workflow"];
	}else{
		$ope_auth_edit = "0";
		$ope_auth_publish = "0";
		$ope_auth_delete = "0";
		$ope_auth_workflow = "0";
	}
}
//入力値チェック
if(($mode == "new" || $mode == "edit") && $action == "save"){
	if($title == ""){
		$error[] = "タイトルを入力してください。";
	}
	if($date == ""){
		$error[] = "お知らせ日付を入力してください。";
	}
	if($hour == ""){
		$error[] = "お知らせ時を入力してください。";
	}
	if($minute == ""){
		$error[] = "お知らせ分を入力してください。";
	}

	if($information_category == ""){
		$error[] = "お知らせカテゴリを入力してください。";
	}
	if($information_type == ""){
		$error[] = "お知らせカテゴリを入力してください。";
	}

	if($content == ""){
		$error[] = "お知らせ内容を入力してください。";
	}
	$schedule_publish = "";
	$schedule_unpublish = "";
	if($schedule_publish_date == ""){
	}else{
		if(Util::checkDateFormat($schedule_publish_date)){
			$schedule_publish = Util::convInputDateTimeToTimestamp($schedule_publish_date,$schedule_publish_hour,$schedule_publish_minute);				//公開開始
		}else{
			$error[] = "公開開始日のフォーマットが不正です。";
		}
	}
	if($schedule_unpublish_date == ""){
	}else{
		if(Util::checkDateFormat($schedule_unpublish_date)){
			$schedule_unpublish = Util::convInputDateTimeToTimestamp($schedule_unpublish_date,$schedule_unpublish_hour,$schedule_unpublish_minute);		//公開終了
		}else{
			$error[] = "公開終了日のフォーマットが不正です。";
		}
	}
	if(!$schedule_publish_no_check && !$schedule_unpublish_no_check && $schedule_publish >= $schedule_unpublish ){
		$error[] = "公開終了日は公開開始日以降である必要があります。";
	}
	if($schedule_type > 0){
		if(Util::IsNullOrEmpty($schedule_publish)){
			$error[] = "スケジュール登録する場合、公開開始日は入力必須です。";
		}elseif($schedule_publish <= $now_timestamp){
			$error[] = "スケジュール登録する場合、公開開始日は現在日時以降である必要があります。";
		}
	}


}elseif($mode == "edit" && $action == "restore"){
	if($archive_id == ""){
		$error[] = "復元対象アーカイブを選択してください。";
	}
}

//保存処理
if(($mode == "new" || $mode == "edit") && $action == "save" && $error == array()){

	DB::beginTransaction();

	//コンテンツ更新データ設定
	$saveData = array();
	$saveData["title"] = $title;
	$saveData["content"] = Util::decodeHTMLBasePath($content);
	$saveData["editmode"] = $editmode;
	$saveData["addinfo"]["date"] = $date;
	$saveData["addinfo"]["hour"] = $hour;
	$saveData["addinfo"]["minute"] = $minute;
	$saveData["addinfo"]["information_category"] = $information_category;
	$saveData["addinfo"]["information_type"] = $information_type;



	$saveData["schedule_type"] = $schedule_type;
	if(!$schedule_publish_no_check){
		$saveData["schedule_publish"] = Util::convInputDateTimeToTimestamp($schedule_publish_date,$schedule_publish_hour,$schedule_publish_minute);				//公開開始
	}else{
		$saveData["schedule_publish"] = null;							//公開開始を指定しない
	}
	if(!$schedule_unpublish_no_check){
		$saveData["schedule_unpublish"] = Util::convInputDateTimeToTimestamp($schedule_unpublish_date,$schedule_unpublish_hour,$schedule_unpublish_minute);		//公開終了
	}else{
		$saveData["schedule_unpublish"] = null;							//公開終了を指定しない
	}

	if($mode == "new"){
		$saveData["folder_id"] = $folder_id;
		$saveData["contentclass"] = $contentclass;
		$saveData["sort_no"] = $Content->getMaxSort($contentclass, $folder_id);
		$saveData["active_flg"] = "1";
		$content_id = $Content->insertContent($saveData,$session->user["user_id"]);
		if(!$content_id){
			DB::rollBack();
			Logger::error("お知らせ新規追加に失敗しました。",$saveData);
			Location::redirect($redirect);
		}
	}else{
		if(!$Content->updateContent($content_id, $saveData, $session->user["user_id"])){
			DB::rollBack();
			Logger::error("お知らせ更新に失敗しました。",$saveData);
			Location::redirect($redirect);
		}
	}

	//アーカイブ実行処理
	if($archive == "on"){
		if(!$Content->archiveContent($content_id)){
			DB::rollBack();
			Logger::error("アーカイブ処理に失敗しました。",$saveData);
			Location::redirect($redirect);
		}
	}

	//スケジュール削除処理
	if(Config::get("schedule_mode") == "on" && $delete_schedule_id){
		foreach($delete_schedule_id as $delete_schedule_id_one){
			if(!$ContentSchedule->deleteSchedule($delete_schedule_id_one)){
				DB::rollBack();
				Logger::error("スケジュール削除処理に失敗しました。".$delete_schedule_id_one);
				Location::redirect($redirect);
			}
		}
	}

	if($publish == "on"){
		if(Config::get("schedule_mode") == "on" && $schedule_type != "0" && $saveData["schedule_publish"]){
			//スケジュール設定処理
			if(!$Content->scheduleContent($content_id)){
				DB::rollBack();
				Logger::error("スケジュール処理に失敗しました。",$saveData);
				Location::redirect($redirect);
			}else{
				$logparam = array();
				$logparam["content_id"] = $content_id;
				$logparam["user_id"] = $session->user["user_id"];
				$logparam["name"] = $session->user["name"];
				Logger::info("コンテンツをスケジュール設定しました。",$logparam);
			}
		}else{
			//公開実行処理
			if(!$Content->publishContent($content_id)){
				DB::rollBack();
				Logger::error("公開処理に失敗しました。",$saveData);
				Location::redirect($redirect);
			}else{
				$logparam = array();
				$logparam["content_id"] = $content_id;
				$logparam["user_id"] = $session->user["user_id"];
				$logparam["name"] = $session->user["name"];
				Logger::info("コンテンツを公開しました。",$logparam);
			}
		}
	}

	//ワークフロー実行処理
	if($workflow_id){
		if(!WorkFlowCommon::executeWorkFlow($content_id, $workflow_id,$workflow_comment, $session->user["user_id"])){
			DB::rollBack();
			Logger::error("ワークフロー実行処理に失敗しました。",$saveData);
			Location::redirect($redirect);
		}else{
			$logparam = array();
			$logparam["content_id"] = $content_id;
			$logparam["workflow_id"] = $workflow_id;
			$logparam["user_id"] = $session->user["user_id"];
			$logparam["name"] = $session->user["name"];
			Logger::info("ワークフロー処理を実行しました。",$logparam);
		}
	}

	DB::commit();







	if($close == "on"){
		//一覧画面に遷移する
		$redirectParam["search_title_flg"] = $search_title_flg;
		$redirectParam["search_content_flg"] = $search_content_flg;
		$redirectParam["search_keyword"] = $search_keyword;
		Location::redirect($close_url,$redirectParam);
	}else{
		//同画面に遷移する
		$redirectParam["content_id"] = $content_id;
		$redirectParam["close_url"] = $close_url;
		$redirectParam["mode"] = "edit";
		$redirectParam["search_title_flg"] = $search_title_flg;
		$redirectParam["search_content_flg"] = $search_content_flg;
		$redirectParam["search_keyword"] = $search_keyword;
		$redirectParam["message[]"] = "保存しました。";
		Location::redirect($self,$redirectParam);
	}
}

//削除処理
if($mode == "edit" && $action == "delete" && $error == array()){

	$contentData = $Content->getContentDataByContentId($content_id);


	DB::beginTransaction();
	$Content->deleteContent($content_id);
	DB::commit();

	//一覧画面に遷移する
	$redirectParam = array();
	$redirectParam["search_title_flg"] = $search_title_flg;
	$redirectParam["search_content_flg"] = $search_content_flg;
	$redirectParam["search_keyword"] = $search_keyword;
	Location::redirect($close_url,$redirectParam);
}

//非公開処理
if($mode == "edit" && $action == "unpublish" && $error == array()){
	DB::beginTransaction();
	$Content->unpublishContent($content_id);
	DB::commit();

	//一覧画面に遷移する
	$redirectParam = array();
	$redirectParam["search_title_flg"] = $search_title_flg;
	$redirectParam["search_content_flg"] = $search_content_flg;
	$redirectParam["search_keyword"] = $search_keyword;
	Location::redirect($close_url,$redirectParam);
}

//アーカイブ復元処理
if($mode == "edit" && $action == "restore" && $error == array()){
	DB::beginTransaction();
	$Content = new Content(Content::TABLE_MANAGEMENT);
	if(!$Content->restoreContent($content_id, $archive_id)){
		DB::rollBack();
		Logger::error("アーカイブ復元処理に失敗しました。ID:".$content_id."ARCHIVE_ID:".$archive_id);
		Location::redirect($redirect);
	}
	DB::commit();

	//同画面に遷移する
	$redirectParam["folder_id"] = $folder_id;
	$redirectParam["content_id"] = $content_id;
	$redirectParam["contentclass"] = $contentclass;
	$redirectParam["close_url"] = $close_url;
	$redirectParam["mode"] = "edit";
	$redirectParam["search_title_flg"] = $search_title_flg;
	$redirectParam["search_content_flg"] = $search_content_flg;
	$redirectParam["search_keyword"] = $search_keyword;
	$redirectParam["message[]"] = "アーカイブから復元しました。";
	Location::redirect($self,$redirectParam);
}

//初期表示
if($action == ""){
	//データロード
	$contentData = null;
	if($mode == "edit"){
		//編集対象コンテンツ取得
		$contentData = $Content->getContentDataByContentId($content_id);
	}else if($mode == "new"){
		//コピー元指定で新規作成
		if($copy_content_id){
			$contentData = $Content->getContentDataByContentId($copy_content_id);
		}
	}

	if($contentData){
		$title = $contentData["title"];
		$url = $contentData["url"];
		$content = Util::encodeHTMLBasePath($contentData["content"]);
		$editmode = $contentData["editmode"];
		$date = $contentData["date"];
		$hour = $contentData["hour"];
		$minute = $contentData["minute"];
		$information_category = isset($contentData["information_category"])? $contentData["information_category"] : "";
		$information_type = isset($contentData["information_type"])? $contentData["information_type"] : "";


		if($mode == "edit"){
			$workflowstate_id = $contentData["workflowstate_id"];
			$workflowstate_name = $contentData["workflowstate_name"];
		}

		if($contentData["schedule_publish"] > time()){
			$schedule_type = $contentData["schedule_type"];
		}

		if($contentData["schedule_publish"] == NULL){
			$schedule_publish_no_check = "checked";
		}else{
			$schedule_publish_date = date("Y/m/d",$contentData["schedule_publish"]);
			$schedule_publish_hour = date("H",$contentData["schedule_publish"]);
			$schedule_publish_minute = date("i",$contentData["schedule_publish"]);
		}

		if($contentData["schedule_unpublish"] == NULL){
			$schedule_unpublish_no_check = "checked";
		}else{
			$schedule_unpublish_date = date("Y/m/d",$contentData["schedule_unpublish"]);
			$schedule_unpublish_hour = date("H",$contentData["schedule_unpublish"]);
			$schedule_unpublish_minute = date("i",$contentData["schedule_unpublish"]);
		}
	}else{
		//新規作成
		$editmode = Config::get("editor_default");
		$schedule_publish_no_check = "checked";
		$schedule_unpublish_no_check = "checked";

		//141224追加
		$date = date("Y/m/d");;
		$hour = "00";
		$minute = "00";
	}
}

//アーカイブ一覧取得
if($mode == "edit"){
	$ContentArchive = new Content(Content::TABLE_ARCHIVE);
	$archiveList = $ContentArchive->getContentArchiveListByContentId($content_id);
}else{
	$archiveList = array();
}

//スケジュール済み一覧取得
if($mode == "edit"){
	$scheduleList = $ContentSchedule->getListByParameters(array("content_id" => $content_id),array("schedule_publish" => "desc"));
}else{
	$scheduleList = array();
}

//選択肢一覧を取得
$where = array();
$where["selectname"] = "information_category";
$order = array();
$order["optionvalue"] = "ASC";
$informationCategoryList = $AddInfoSelect->getListByParameters($where,$order);

$where = array();
$where["selectname"] = "information_type";
$order = array();
$order["optionvalue"] = "ASC";
$informationTypeList = $AddInfoSelect->getListByParameters($where,$order);

$LayoutManager = new LayoutManagerInfo();
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
		<?php if(Config::get("archive_mode") == "save_auto"): ?>
		$("*[name=archive]").val('on');
		<?php endif; ?>
		$('#values').attr({
		       'action':'<?php echo $self; ?>',
		       'method':'post'
		     });
		$('#values').submit();
	});

	//保存して閉じるボタン設定
	$("#action_save_close").click(function(){
		$("*[name=action]").val('save');
		<?php if(Config::get("archive_mode") == "save_auto"): ?>
		$("*[name=archive]").val('on');
		<?php endif; ?>
		$("*[name=close]").val('on');
		$('#values').attr({
		       'action':'<?php echo $self; ?>',
		       'method':'post'
		     });
		$('#values').submit();
	});

	//公開実行ボタン設定
	$("#action_publish").click(function(){
		$("*[name=action]").val('save');
		$("*[name=close]").val('on');
		<?php if(Config::get("archive_mode") == "save_auto" || Config::get("archive_mode") == "publish_auto"): ?>
		$("*[name=archive]").val('on');
		<?php endif; ?>
		$("*[name=publish]").val('on');
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

	//削除ボタン設定
	$("#action_delete").click(function(){
		if(!window.confirm('本当に削除しますか？')){
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

	//非公開ボタン設定
	$("#action_unpublish").click(function(){

		$("*[name=action]").val('unpublish');
		$("*[name=close]").val('on');
		$('#values').attr({
		       'action':'<?php echo $self; ?>',
		       'method':'post'
		     });
		$('#values').submit();
	});

	//復元ボタン設定
	$("#action_restore").click(function(){
		if($("*[name=archive_id]").val() == ""){
			alert("アーカイブデータが選択されていません。");
			return false;
		}else if(!window.confirm('復元すると現在編集中のコンテンツは上書きされます。本当に復元しますか？')){
			return false;
		}

		$("*[name=action]").val('restore');
		$("*[name=close]").val('on');
		$('#values').attr({
		       'action':'<?php echo $self; ?>',
		       'method':'post'
		     });
		$('#values').submit();
	});

});
</script>





<form action="/" method="post" id="values" enctype="multipart/form-data">

<div class="search">
<div class="content_left">
<input class="btn btn_small" type="button" value="一覧に戻る" id="back_to_list" />
<!--
<input class="btn btn_small" type="button" value="保存する" id="action_save"  />
<input class="btn btn_small" type="button" value="保存して閉じる" id="action_save_close"  />
 -->
<input class="btn btn_small" type="button" value="公開実行"  id="action_publish"  />

<?php if($mode == "edit"): ?>
	<!-- <input class="btn btn_small" type="button" value="非公開"  id="action_unpublish"  /> -->
	<input class="btn red btn_small" type="button" value="削除"  id="action_delete"  />
<?php endif; ?>
</div>
<div class="content_right">
<?php if($mode == "edit"): ?>
	<select name="archive_id" style="padding:2px;">
	<option value="">--アーカイブ--</option>
	<?php
	foreach($archiveList as $value){
		echo "<option value='".$value["content_archive_id"]."' >".date("Y/m/d H:i",$value["updated"])." ".$value["updated_by_name"]."</option>";
	}
	?>
	</select>
	<input class="btn btn_small" type="button" value="復元"  id="action_restore"  />
<?php endif; ?>
</div>
<div class="clear"></div>
</div>

<!-- メッセージ出力開始 -->
<?php echo $LayoutManager->alert(); 	//アラート出力 ?>
<?php echo $LayoutManager->error(); 	//エラー出力 ?>
<?php echo $LayoutManager->message(); 	//メッセージ出力 ?>
<!-- メッセージ出力終了 -->
<?php if(Config::get("workflow_mode") == "on" && $ope_auth_workflow): ?>
	<?php
	$workFlowList = WorkFlowCommon::getWorkFlowListAvailable($content_id, $contentclass, $folder_id, $session->user["usergroups"], $session->user["admintype"]);
	?>
	<?php if(count($workFlowList) > 0): ?>
		<!--<div style="width:100%; border:1px #999999 solid; margin:5px 5px 5px 0;">-->
		<h3>ワークフロー
		<?php
		if($workflowstate_id){
			echo "(現在の状態：".$workflowstate_name.")";
		}
		?>
		</h3>
		<table class="edit" cellspacing="0">
			<tr>
		    <th class="w240">アクション</th>
		    <td>
			<select name="workflow_id" style="padding:2px;">
				<option value="">--ワークフローアクションを選択--</option>
				<?php
				foreach($workFlowList as $key => $value){
					if($workflow_id == $value["workflow_id"]){ $selected = "selected"; }else{ $selected = ""; }
					echo '<option value="'.$value["workflow_id"].'" '.$selected.'>'.$value["workflow_name"].'</option>';
				}
				?>
			</select>
		    </td>
		    </tr>
		    <tr>
		    <th>連絡コメント</th>
		    <td>
			<textarea style="width:500px; height:100px;" name="workflow_comment"><?php echo htmlspecialchars($workflow_comment); ?></textarea>
		    </td>
		    </tr>
		</table>
		<!--</div>-->
	<?php endif; ?>
<?php endif; ?>
<h3>コンテンツ編集</h3>


<input type="hidden" name="content_id" value="<?php echo htmlspecialchars($content_id); //コンテンツID ?>" />
<input type="hidden" name="mode" value="<?php echo htmlspecialchars($mode); //動作モード ?>" />
<input type="hidden" name="close_url" value="<?php echo htmlspecialchars($close_url); //戻り先URL ?>" />
<input type="hidden" name="action" value="<?php //javascriptから設定 ?>" />
<input type="hidden" name="close" value="<?php //javascriptから設定 ?>" />
<input type="hidden" name="publish" value="<?php //javascriptから設定 ?>" />
<input type="hidden" name="archive" value="<?php //javascriptから設定 ?>" />
<input type="hidden" name="search_title_flg" value="<?php echo htmlspecialchars($search_title_flg); //タイトル検索フラグ ?>" />
<input type="hidden" name="search_content_flg" value="<?php echo htmlspecialchars($search_content_flg); //コンテンツ検索フラグ ?>" />
<input type="hidden" name="search_keyword" value="<?php echo htmlspecialchars($search_keyword); //検索キーワード ?>" />
<table class="edit" cellspacing="0">
    <tr>
    <th class="w240">配信先<span class="mark orange">必須</span></th>
    <td>
    <?php if($information_type == "01"){ ?>
    お知らせ＆NEWS
    <input type="hidden" name="information_type" value="01" />
    <?php }else{ ?>
    <select name="information_type">
    <?php
    foreach($informationTypeList as $iType){
    	if($iType["optionvalue"] == $information_type){ $selected = "selected"; }else{ $selected = ""; }
    	echo '<option value="'.$iType["optionvalue"].'" '.$selected.'>'.$iType["optionvalue_name"]."</option>";
	}
    ?>
    </select>
    <?php } ?>
    </td>
    </tr>
    <tr>
    <th class="w240">カテゴリ<span class="mark orange">必須</span></th>
    <td>
    <select name="information_category">
    <?php
    foreach($informationCategoryList as $category){
		if($category["optionvalue"] == $information_category){ $selected = "selected"; }else{ $selected = ""; }
		echo '<option value="'.$category["optionvalue"].'" '.$selected.'>'.$category["optionvalue_name"]."</option>";
	}
    ?>
    </select>
    </td>
    </tr>
    <tr>
    <th>タイトル<span class="mark orange">必須</span></th>
    <td>
    <input type="text" name="title" value="<?php echo htmlspecialchars($title);?>" />
    </td>
    </tr>
	<tr>
    <th>お知らせ日付<span class="mark orange">必須</span></th>
    <td>
    <?php //echo UIParts::datePicker("date", $date); ?>
    <?php echo UIParts::dateTimePicker("date","hour","minute",$date,$hour,$minute); ?>
    </td>
    </tr>
	<tr>
    <th>お知らせ内容<span class="mark orange">必須</span></th>
    <td></td>
    </tr>
	</table>
    <?php
	if($editmode == "2" && Config::get("editor_enable") == "1"){
	    $_SESSION['ckeditor_media_basepath'] = "";	//メディアベースパスを設定
	    $_SESSION['ckeditor_media_dirpath'] = "";	//メディアディレクトリパスを設定
	    $_SESSION['ckeditor_media_thumbs_path'] = "";	//メディアサムネイル格納場所設定


	    echo "<script type=\"text/javascript\" src=\"/".Config::BASE_DIR_PATH.Config::ADMIN_DIR_PATH."ckeditor/ckeditor.js\"></script>\n"; //CKEditor読み込み
	    echo "<script type=\"text/javascript\" src=\"/".Config::BASE_DIR_PATH.Config::ADMIN_DIR_PATH."ckfinder/ckfinder.js\"></script>\n"; //CKFinder読み込み
	    echo "<script type=\"text/javascript\">\n";
	    echo "CKEDITOR.config.width = '100%';\n"; //横幅設定
	    echo "CKEDITOR.config.height = '3d00';\n"; //縦幅設定

	    //ツールバー設定
	    echo "CKEDITOR.config.toolbar = [\n";
	    echo "['Source','-','NewPage','Preview','-','Templates']\n";
	    echo ",['Cut','Copy','Paste','PasteText','PasteFromWord','-','Print','SpellChecker']\n";
	    echo ",['Undo','Redo','-','Find','Replace','-','SelectAll','RemoveFormat']\n";
	    //echo ",['Form','Checkbox','Radio','TextField','Textarea','Select','Button','ImageButton','HiddenField']\n";
	    echo ",'/'\n";
	    echo ",['Bold','Italic','Underline','Strike','-','Subscript','Superscript']\n";
	    echo ",['NumberedList','BulletedList','-','Outdent','Indent','Blockquote']\n";
	    echo ",['JustifyLeft','JustifyCenter','JustifyRight','JustifyBlock']\n";
	    echo ",['Link','Unlink','Anchor']\n";
	    echo ",['Image','Flash','Table','HorizontalRule','Smiley','SpecialChar','PageBreak']\n";
	    echo ",'/'\n";
	    echo ",['Styles','Format','Font','FontSize']\n";
	    echo ",['TextColor','BGColor']\n";
	    echo ",['ShowBlocks']\n";
	    echo "];\n";

	    //CSS読み込み設定
		/*
	    if(count($stylesheetUrlList) > 0){

		    echo "CKEDITOR.config.contentsCss = [";
		    for($i=0;$i<count($stylesheetUrlList);$i++){
		    	if($i > 0){ echo ","; }
		    	echo "'/".Config::BASE_DIR_PATH.$stylesheetUrlList[$i]."'";
		    }
		    echo "];\n";
	    }
	    */

	    echo "CKEDITOR.config.bodyId = 'contents';\n";
		echo "CKEDITOR.config.enterMode = 2;\n";
		echo "CKEDITOR.config.shiftEnterMode = 1;\n";
	    echo "CKEDITOR.config.bodyClass = '';\n";

	    echo "CKEDITOR.config.allowedContent = true;\n";

	    echo "CKFinder.setupCKEditor(CKEDITOR,'/".Config::BASE_DIR_PATH.Config::ADMIN_DIR_PATH."ckfinder/');\n";

	    //編集チェック機能
	    echo "function myCheckDirty () {\n";
	    echo "	var inst = CKEDITOR.instances.content;\n";
	    echo "	if (inst && inst.checkDirty()) {\n";
	    echo "		var data = inst.getData(), orig_data = data;\n";
	    echo "		editflg = true;\n";
	    echo "		if (data != orig_data){\n";
	    echo "			inst.setData(data, function () { inst.resetDirty(); });\n";
	    echo "		}\n";
	    echo "	}\n";
	    echo "	setTimeout (myCheckDirty, 250);\n";
	    echo "}\n";
	    echo "myCheckDirty();\n";

	    echo "</script>\n";
	    echo "<textarea class=\"ckeditor\" id=\"content\" name=\"content\">".$content."</textarea>\n";
	}else{
		echo "<textarea class=\"ckeditor\" id=\"content\" name=\"content\" style=\"width:100%; height:300px;\">".$content."</textarea>\n";
	}
    ?>
	<table class="edit" cellspacing="0">
    <tr>
    <th>エディタ設定</th>
    <td>
    <?php
    if($editmode == "1" || $editmode == "0"){ $checked = "checked"; }else { $checked = ""; }
    echo '<input type="radio" name="editmode" value="1" '.$checked.'>テキストエリア&nbsp;';
    if($editmode == "2"){ $checked = "checked"; }else { $checked = ""; }
    echo '<input type="radio" name="editmode" value="2" '.$checked.'>Webエディタ';
    ?>
    </td>
    </tr>
	<?php if(Config::get("schedule_mode") == "on" && $mode == "edit"): ?>
		<tr>
	    <th>スケジュール</th>
	    <td>
	    <?php
	    if($schedule_type == "0"){ $checked = "checked"; }else { $checked = ""; }
	    echo '<input type="radio" name="schedule_type" value="0" '.$checked.'>現在のコンテンツを差替える<br>';
	    if($schedule_type == "1"){ $checked = "checked"; }else { $checked = ""; }
	    echo '<input type="radio" name="schedule_type" value="1" '.$checked.'>スケジュールに登録する<br>';
	    //if($schedule_type == "2"){ $checked = "checked"; }else { $checked = ""; }
	    //echo '<input type="radio" name="schedule_type" value="2" '.$checked.'>スケジュールに登録する(一時的なコンテンツ)<br>';
	    ?>
	    </td>
	    </tr>
	<?php endif; ?>
    <tr>
    <th>公開開始</th>
    <td>
    <?php echo UIParts::dateTimePicker("schedule_publish_date", "schedule_publish_hour", "schedule_publish_minute", $schedule_publish_date, $schedule_publish_hour, $schedule_publish_minute); ?>
    <br/><input type="checkbox" name="schedule_publish_no_check" value="checked" <?php if($schedule_publish_no_check){ echo "checked"; }?>>指定しない
    <script>
		$("*[name=schedule_publish_date]").change(function() {
			$("*[name=schedule_publish_no_check]").attr("checked", false);
		});
		$("*[name=schedule_publish_hour]").change(function() {
			$("*[name=schedule_publish_no_check]").attr("checked", false);
		});
		$("*[name=schedule_publish_minute]").change(function() {
			$("*[name=schedule_publish_no_check]").attr("checked", false);
		});
    </script>
    </td>
    </tr>

    <tr>
    <th>公開終了</th>
    <td>
    <?php echo UIParts::dateTimePicker("schedule_unpublish_date", "schedule_unpublish_hour", "schedule_unpublish_minute", $schedule_unpublish_date, $schedule_unpublish_hour, $schedule_unpublish_minute); ?>
    <br/><input type="checkbox" name="schedule_unpublish_no_check" value="checked" <?php if($schedule_unpublish_no_check){ echo "checked"; }?>>指定しない
    <script>
		$("*[name=schedule_unpublish_date]").change(function() {
			$("*[name=schedule_unpublish_no_check]").attr("checked", false);
		});
		$("*[name=schedule_unpublish_hour]").change(function() {
			$("*[name=schedule_unpublish_no_check]").attr("checked", false);
		});
		$("*[name=schedule_unpublish_minute]").change(function() {
			$("*[name=schedule_unpublish_no_check]").attr("checked", false);
		});
    </script>
    </td>
    </tr>
	<?php if(Config::get("schedule_mode") == "on" && count($scheduleList) > 0): ?>
		<tr>
	    <th>スケジュール済み一覧</th>
	    <td>
	    <?php
		echo '<table>'."\n";
	    for($i=0;$i<count($scheduleList);$i++){
			echo '<tr>';
			echo '<td style="border-bottom:0px">'.date("Y/m/d H:i",$scheduleList[$i]["schedule_publish"]).'</td>'."\n";
			echo '<td style="border-bottom:0px"><input type="checkbox" name="delete_schedule_id[]" value="'.$scheduleList[$i]["content_schedule_id"].'" />削除	</td>'."\n";

			echo '</tr>';
		}
		echo '</table>'."\n";
	    ?>
	    </td>
	    </tr>
	<?php endif; ?>
</table>
</form>
<?php $LayoutManager->footer(); ?>
