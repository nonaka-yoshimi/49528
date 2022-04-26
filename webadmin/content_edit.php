<?php
// アプリケーション共通クラス読込
require_once(dirname(__FILE__).'/ApplicationCommon/include.php');
require_once(dirname(__FILE__).'/CMSCommon/WorkFlowCommon.php');	//ワークフロー共通
require_once(dirname(__FILE__).'/DataAccess/Content.php'); 			//コンテンツクラス
require_once(dirname(__FILE__).'/DataAccess/Folder.php'); 			//フォルダクラス
require_once(dirname(__FILE__).'/DataAccess/WorkFlow.php'); 		//ワークフローアクションクラス
require_once(dirname(__FILE__).'/DataAccess/WorkFlowState.php'); 	//ワークフロー状態クラス

//基本設定
$self = basename(__FILE__);				//自身のファイル名
$error = array();						//エラー情報格納用配列
$message = array();						//メッセージ情報格納用配列
$alert = array();						//アラート情報格納用配列
$redirect = "index.php";				//エラー時リダイレクト先
$restrict = array();					//表示・操作制限
$now_timestamp = time();

//セッション取得
$session = Session::get();

//セッション認証
$session->loginCheckAndRedirect("login.php",array("admin_flg" => 1));

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
$folder_id = isset($_REQUEST["folder_id"]) ? $_REQUEST["folder_id"] : "";																	//フォルダID
$contentclass = isset($_REQUEST["contentclass"]) ? $_REQUEST["contentclass"] : "";															//コンテンツクラス

$search_title_flg = isset($_REQUEST["search_title_flg"]) ? $_REQUEST["search_title_flg"] : "";												//ページタイトル検索フラグ
$search_content_flg = isset($_REQUEST["search_content_flg"]) ? $_REQUEST["search_content_flg"] : "";										//コンテンツ検索フラグ
$search_keyword = isset($_REQUEST["search_keyword"]) ? $_REQUEST["search_keyword"] : "";													//検索キーワード

$workflow_id = isset($_POST["workflow_id"]) ? $_POST["workflow_id"] : "";																	//ワークフローID
$workflow_comment = isset($_POST["workflow_comment"]) ? Util::encodeRequest($_POST["workflow_comment"]) : "";								//ワークフローコメント
$workflowstate_id = isset($_POST["workflowstate_id"]) ? Util::encodeRequest($_POST["workflowstate_id"]) : "";								//ワークフロー状態ID
$workflowstate_name = isset($_POST["workflowstate_name"]) ? Util::encodeRequest($_POST["workflowstate_name"]) : "";							//ワークフロー状態名

$title = isset($_POST["title"]) ? Util::encodeRequest($_POST["title"]) : "";																//タイトル
$url = isset($_POST["url"]) ? Util::encodeRequest($_POST["url"]) : "";																		//URL
$keywords = isset($_POST["keywords"]) ? Util::encodeRequest($_POST["keywords"]) : "";														//keywords
$description = isset($_POST["description"]) ? Util::encodeRequest($_POST["description"]) : "";												//description
$author = isset($_POST["author"]) ? Util::encodeRequest($_POST["author"]) : "";																//author
$media = isset($_POST["media"]) ? Util::encodeRequest($_POST["media"]) : "";																//media(CSS)
$template_id = isset($_POST["template_id"]) ? $_POST["template_id"] : "";																	//テンプレートID
$stylesheet_id = isset($_POST["stylesheet_id"]) ? $_POST["stylesheet_id"] : array();														//スタイルシートID(複数選択)
$script_id = isset($_POST["script_id"]) ? $_POST["script_id"] : array();																	//スクリプトID(複数選択)
$content = isset($_POST["content"]) ? Util::encodeRequest($_POST["content"]) : "";															//コンテンツ
$html_attr = isset($_POST["html_attr"]) ? Util::encodeRequest($_POST["html_attr"]) : "";													//HTML属性
$head_attr = isset($_POST["head_attr"]) ? Util::encodeRequest($_POST["head_attr"]) : "";													//HEAD属性
$body_attr = isset($_POST["body_attr"]) ? Util::encodeRequest($_POST["body_attr"]) : "";													//BODY属性
$head_code = isset($_POST["head_code"]) ? Util::encodeRequest($_POST["head_code"]) : "";													//HEAD_CODE
$title_prefix = isset($_POST["title_prefix"]) ? Util::encodeRequest($_POST["title_prefix"]) : "";											//タイトルプレフィックス
$title_suffix = isset($_POST["title_suffix"]) ? Util::encodeRequest($_POST["title_suffix"]) : "";											//タイトルサフィックス
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

$Content = new Content(Content::TABLE_MANAGEMENT);
$ContentSchedule = new Content(Content::TABLE_SCHEDULE);

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
	}elseif(Config::get("workflow_mode") == "off" || Config::get("workflow_mode") == ""){
		$ope_auth_edit = "1";
		$ope_auth_publish = "1";
		$ope_auth_delete = "1";
		$ope_auth_workflow = "1";
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
	if($contentclass == "page" || $contentclass == "stylesheet" || $contentclass == "script"){
		if($url == ""){
			$error[] = "URLを入力してください。";
		}else{
			//URL重複チェック
			$Content = new Content(Content::TABLE_MANAGEMENT);						//コンテンツクラス
			$ContentPublic = new Content(Content::TABLE_PUBLIC);					//公開コンテンツクラス

			$where = array();
			$where[] = array("url",$url);
			$where[] = array("content_id",$content_id,"<>");
			if($Content->getCountByParameters($where) > 0){
				$error[] = "URLが重複しています。異なるURLを設定してください。";
			}else if($ContentPublic->getCountByParameters($where) > 0){
				$error[] = "URLが他の公開コンテンツと重複しています。異なるURLを設定してください。";
			}
		}
	}
	$schedule_publish = Util::convInputDateTimeToTimestamp($schedule_publish_date,$schedule_publish_hour,$schedule_publish_minute);				//公開開始
	$schedule_unpublish = Util::convInputDateTimeToTimestamp($schedule_unpublish_date,$schedule_unpublish_hour,$schedule_unpublish_minute);		//公開終了
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

	$Content = new Content(Content::TABLE_MANAGEMENT);						//コンテンツクラス

	//コンテンツ更新データ設定
	$saveData = array();
	$saveData["title"] = $title;
	$saveData["url"] = $url;
	$saveData["keywords"] = $keywords;
	$saveData["description"] = $description;
	$saveData["author"] = $author;
	$saveData["media"] = $media;
	$saveData["content"] = Util::decodeHTMLBasePath($content);
	$saveData["template_id"] = $template_id;
	$saveData["stylesheet_index"] = implode(",",$stylesheet_id);
	$saveData["script_index"] = implode(",",$script_id);
	$saveData["editmode"] = $editmode;
	$saveData["html_attr"] = $html_attr;
	$saveData["head_attr"] = $head_attr;
	$saveData["body_attr"] = $body_attr;
	$saveData["head_code"] = Util::decodeHTMLBasePath($head_code);
	$saveData["title_prefix"] = $title_prefix;
	$saveData["title_suffix"] = $title_suffix;
	$saveData["folder_id"] = $folder_id;
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
		$saveData["contentclass"] = $contentclass;
		$saveData["sort_no"] = $Content->getMaxSort($saveData["contentclass"], $saveData["folder_id"]);
		$saveData["active_flg"] = "1";
		$content_id = $Content->insertContent($saveData,$session->user["user_id"]);
		if(!$content_id){
			DB::rollBack();
			Logger::error("コンテンツ新規追加に失敗しました。",$saveData);
			Location::redirect($redirect);
		}else{
			$logparam = array();
			$logparam["content_id"] = $content_id;
			$logparam["user_id"] = $session->user["user_id"];
			$logparam["name"] = $session->user["name"];
			Logger::info("コンテンツを新規追加しました。",$logparam);
		}
	}else{
		$contentData = $Content->getDataByPrimaryKey($content_id);
		if($contentData["folder_id"] != $folder_id){
			$saveData["sort_no"] = $Content->getMaxSort($contentclass, $saveData["folder_id"]);
		}
		if(!$Content->updateContent($content_id, $saveData, $session->user["user_id"])){
			DB::rollBack();
			Logger::error("コンテンツ更新に失敗しました。",$saveData);
			Location::redirect($redirect);
		}else{
			$logparam = array();
			$logparam["content_id"] = $content_id;
			$logparam["user_id"] = $session->user["user_id"];
			$logparam["name"] = $session->user["name"];
			Logger::info("コンテンツを更新しました。",$logparam);
		}
	}

	//アーカイブ実行処理
	if($archive == "on"){
		if(!$Content->archiveContent($content_id)){
			DB::rollBack();
			Logger::error("アーカイブ処理に失敗しました。",$saveData);
			Location::redirect($redirect);
		}else{
			$logparam = array();
			$logparam["content_id"] = $content_id;
			$logparam["user_id"] = $session->user["user_id"];
			$logparam["name"] = $session->user["name"];
			Logger::info("コンテンツをアーカイブしました。",$logparam);
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

	// ファイル公開処理
	if($publish == "on" && $url != ""){
		if(($contentclass == "stylesheet" && Config::get("static_stylesheet") == "on")
			|| ($contentclass == "script" && Config::get("static_script") == "on")){
			// 物理ファイルを配信する
			Util::deployPhysicalFile(dirname(__FILE__)."/../".$url , $content);
		}
	}

	DB::commit();

	if($close == "on"){
		//一覧画面に遷移する
		$redirectParam = array();
		$redirectParam["folder_id"] = $folder_id;
		$redirectParam["search_title_flg"] = $search_title_flg;
		$redirectParam["search_content_flg"] = $search_content_flg;
		$redirectParam["search_keyword"] = $search_keyword;
		Location::redirect($close_url,$redirectParam);
	}else{
		//同画面に遷移する
		$redirectParam["folder_id"] = $folder_id;
		$redirectParam["content_id"] = $content_id;
		$redirectParam["contentclass"] = $contentclass;
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
	DB::beginTransaction();
	$Content = new Content(Content::TABLE_MANAGEMENT);
	$Content->deleteContent($content_id);

	// ファイル削除処理
	if(($contentclass == "stylesheet" && Config::get("static_stylesheet") == "on")
		|| ($contentclass == "script" && Config::get("static_script") == "on")){
		// 物理ファイルを削除する
		Util::deletePhysicalFile(dirname(__FILE__)."/../",$url);
	}

	DB::commit();

	//一覧画面に遷移する
	$redirectParam = array();
	$redirectParam["folder_id"] = $folder_id;
	$redirectParam["search_title_flg"] = $search_title_flg;
	$redirectParam["search_content_flg"] = $search_content_flg;
	$redirectParam["search_keyword"] = $search_keyword;
	Location::redirect($close_url,$redirectParam);
}

//非公開処理
if($mode == "edit" && $action == "unpublish" && $error == array()){
	DB::beginTransaction();
	$Content = new Content(Content::TABLE_MANAGEMENT);
	$Content->unpublishContent($content_id);
	DB::commit();

	// ファイル削除処理
	if(($contentclass == "stylesheet" && Config::get("static_stylesheet") == "on")
			|| ($contentclass == "script" && Config::get("static_script") == "on")){
		// 物理ファイルを削除する
		Util::deletePhysicalFile(dirname(__FILE__)."/../",$url);
	}

	//一覧画面に遷移する
	$redirectParam = array();
	$redirectParam["folder_id"] = $folder_id;
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

$Content = new Content(Content::TABLE_MANAGEMENT);

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
		//編集orコピーして新規作成
		//変数データ設定
		$contentclass = $contentData["contentclass"];
		$folder_id = $contentData["folder_id"];
		$title = $contentData["title"];
		$url = $contentData["url"];

		$keywords = $contentData["keywords"];
		$description = $contentData["description"];
		$author = $contentData["author"];
		$media = $contentData["media"];
		$template_id = $contentData["template_id"];
		$stylesheet_id = explode(",",$contentData["stylesheet_index"]);
		$script_id = explode(",",$contentData["script_index"]);
		$content = Util::encodeHTMLBasePath($contentData["content"]);
		$editmode = $contentData["editmode"];
		$html_attr = $contentData["html_attr"];
		$head_attr = $contentData["head_attr"];
		$body_attr = $contentData["body_attr"];
		$head_code = Util::encodeHTMLBasePath($contentData["head_code"]);
		$title_prefix = $contentData["title_prefix"];
		$title_suffix = $contentData["title_suffix"];
		if($mode == "edit"){
			$workflowstate_id = $contentData["workflowstate_id"];
			$workflowstate_name = $contentData["workflowstate_name"];
		}

		if($contentData["schedule_publish"] > time()){
			$schedule_type = $contentData["schedule_type"];
		}

		if($contentData["schedule_publish"] == NULL || $contentData["schedule_publish"] <= time()){
			$schedule_publish_no_check = "checked";
		}else{
			$schedule_publish_date = date("Y/m/d",$contentData["schedule_publish"]);
			$schedule_publish_hour = date("H",$contentData["schedule_publish"]);
			$schedule_publish_minute = date("i",$contentData["schedule_publish"]);
		}

		if($contentData["schedule_unpublish"] == NULL || $contentData["schedule_unpublish"] <= time()){
			$schedule_unpublish_no_check = "checked";
		}else{
			$schedule_unpublish_date = date("Y/m/d",$contentData["schedule_unpublish"]);
			$schedule_unpublish_hour = date("H",$contentData["schedule_unpublish"]);
			$schedule_unpublish_minute = date("i",$contentData["schedule_unpublish"]);
		}
	}else{
		//新規作成
		if($contentclass == "page"){
			$editmode = Config::get("editor_default");
		}else{
			$editmode = "1";
		}
		$schedule_publish_no_check = "checked";
		$schedule_unpublish_no_check = "checked";
	}
}

//フォルダ一覧取得
$Folder = new Folder();
$where = array();
$where["active_flg"] = 1;
$where["list_display_flg"] = 1;
$order = array();
$order["sort_no"] = "asc";
$folderList = $Folder->getListByParameters($where,$order);

//テンプレート一覧取得
$where = array();
$where[] = array("contentclass","template");
$where[] = array("content_id",$content_id,"<>");
$order = array();
$order["sort_no"] = "ASC";
$templateList = $Content->getListByParameters($where,$order);

//スタイルシート一覧取得
$where = array();
$where[] = array("contentclass","stylesheet");
$where[] = array("content_id",$content_id,"<>");
$order = array();
$order["sort_no"] = "ASC";
$stylesheetList = $Content->getListByParameters($where,$order);

//スクリプト一覧取得
$where = array();
$where[] = array("contentclass","script");
$where[] = array("content_id",$content_id,"<>");
$order = array();
$order["sort_no"] = "ASC";
$scriptList = $Content->getListByParameters($where,$order);

//適用スタイルシートインデックス取得
if($mode == "edit" && $content_id != ""){
	$stylesheetIndex = $Content->getStylesheetIndex($content_id);
	$stylesheetIdList = explode(",",$stylesheetIndex);
	$stylesheetUrlList = array();
	for($i=0;$i<count($stylesheetIdList);$i++){
		$stylesheetContent = $Content->getDataByPrimaryKey($stylesheetIdList[$i]);
		if($stylesheetContent){
			$stylesheetUrlList[] = $stylesheetContent["url"];
		}
	}
}else{
	$stylesheetUrlList = array();
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

//物理ファイル存在チェック
if(!Util::IsNullOrEmpty($url)){
	if(($contentclass == "stylesheet" && Config::get("static_stylesheet") == "on")
			|| ($contentclass == "script" && Config::get("static_script") == "on")){
		//静的配信の場合
		$alert[] = "コンテンツを公開すると物理ファイルが更新(配信)されます。";
	}else if(file_exists("../".$url)){
		$alert[] = "同名の物理ファイルが既にサーバ上にアップロードされています。このコンテンツは表示されませんので注意してください。";
	}
}



$content_name = "";
$contentclassName = Options::contentclass();

$LayoutManager = new LayoutManagerAdminMain();
$LayoutManager->setTitle($contentclassName[$contentclass]."編集");
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
		<?php if(Config::get("archive_mode") == "save_auto" || Config::get("archive_mode") == "publish_auto"): ?>
		$("*[name=archive]").val('on');
		<?php endif; ?>
		$("*[name=close]").val('on');
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
<?php if(!$ope_auth_edit){ $disabled = "disabled"; }else{ $disabled = "";}?>
<input class="btn btn_small" type="button" value="保存する" id="action_save"  <?php echo $disabled; ?> />
<input class="btn btn_small" type="button" value="保存して閉じる" id="action_save_close"  <?php echo $disabled; ?> />
<?php if(!$ope_auth_publish){ $disabled = "disabled"; }else{ $disabled = "";}?>
<input class="btn btn_small" type="button" value="公開実行"  id="action_publish" <?php echo $disabled; ?>  />
<?php if($mode == "edit"): ?>
	<?php if(!$ope_auth_publish){ $disabled = "disabled"; }else{ $disabled = "";}?>
	<input class="btn red btn_small" type="button" value="非公開"  id="action_unpublish"  <?php echo $disabled; ?> />
	<?php if(!$ope_auth_delete){ $disabled = "disabled"; }else{ $disabled = "";}?>
	<input class="btn red btn_small" type="button" value="削除"  id="action_delete"  <?php echo $disabled; ?> />
<?php endif; ?>
</div>
<div class="content_right">
<?php if($mode == "edit"): ?>
	<?php if(!$ope_auth_edit){ $disabled = "disabled"; }else{ $disabled = "";}?>
	<br />
	<select name="archive_id" style="float:left; padding:2px; margin-right:10px;" <?php echo $disabled; ?> >
	<option value="">--アーカイブ--</option>
	<?php
	foreach($archiveList as $value){
		echo "<option value='".$value["content_archive_id"]."' >".date("Y/m/d H:i",$value["updated"])." ".$value["updated_by_name"]."</option>";
	}
	?>
	</select>
	<input class="btn btn_small" type="button" value="復元"  id="action_restore"  <?php echo $disabled; ?> />
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
			<select name="workflow_id">
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
<input type="hidden" name="folder_id" value="<?php echo htmlspecialchars($folder_id); //フォルダID ?>" />
<input type="hidden" name="contentclass" value="<?php echo htmlspecialchars($contentclass); //コンテンツクラス ?>" />
<input type="hidden" name="mode" value="<?php echo htmlspecialchars($mode); //動作モード ?>" />
<input type="hidden" name="close_url" value="<?php echo htmlspecialchars($close_url); //戻り先URL ?>" />
<input type="hidden" name="action" value="<?php //javascriptから設定 ?>" />
<input type="hidden" name="close" value="<?php //javascriptから設定 ?>" />
<input type="hidden" name="publish" value="<?php //javascriptから設定 ?>" />
<input type="hidden" name="archive" value="<?php //javascriptから設定 ?>" />
<input type="hidden" name="workflowstate_id" value="<?php echo htmlspecialchars($workflowstate_id); //ワークフロー状態ID ?>" />
<input type="hidden" name="workflowstate_name" value="<?php echo htmlspecialchars($workflowstate_name); //ワークフロー状態名 ?>" />
<input type="hidden" name="search_title_flg" value="<?php echo htmlspecialchars($search_title_flg); //タイトル検索フラグ ?>" />
<input type="hidden" name="search_content_flg" value="<?php echo htmlspecialchars($search_content_flg); //コンテンツ検索フラグ ?>" />
<input type="hidden" name="search_keyword" value="<?php echo htmlspecialchars($search_keyword); //検索キーワード ?>" />

<table class="edit" cellspacing="0">
    <tr>
    <th class="w240">ページタイトル<span class="mark orange">必須</span></th>
    <td>
    <input type="text" name="title" value="<?php echo htmlspecialchars($title);?>" size="100" />
    </td>
    </tr>

    <?php if($contentclass == "page" || $contentclass == "stylesheet" || $contentclass == "script"): ?>
	    <tr>
	    <th>URL<span class="mark orange">必須</span></th>
	    <td>
	    <input type="text" name="url" value="<?php echo htmlspecialchars($url);?>" size="100" />
	    </td>
	    </tr>
	<?php endif; ?>
	<?php if($contentclass == "page" || $contentclass == "template"): ?>
	    <tr>
	    <th>キーワード</th>
	    <td>
	    <input type="text" name="keywords" value="<?php echo htmlspecialchars($keywords);?>" size="100" /><br />
	    <span class="info">html meta情報(検索エンジン向け情報)となります</span>
	    </td>
	    </tr>

	    <tr>
	    <th>説明文</th>
	    <td>
	    <input type="text" name="description" value="<?php echo htmlspecialchars($description);?>"  size="100" /><br />
	    <span class="info">html meta情報(検索エンジン向け情報)となります</span>
	    </td>
	    </tr>

	    <tr>
	    <th>作成者</th>
	    <td>
	    <input type="text" name="author" value="<?php echo htmlspecialchars($author);?>"  size="100" /><br />
	    <span class="info">html meta情報(検索エンジン向け情報)となります</span>
	    </td>
	    </tr>
	<?php endif; ?>
	<?php if(Config::get("folder_use_flg") == "on"): ?>
		<tr>
	    <th>フォルダ<span class="mark orange">必須</span></th>
	    <td>
	    <select name="folder_id">
	    <?php
	    for($i=0;$i<count($folderList);$i++){
			if($folder_id == $folderList[$i]["folder_id"]){
				$selected = "selected";
			}else{
				$selected = "";
			}
			echo '<option value="'.$folderList[$i]["folder_id"].'" '.$selected.'>'.$folderList[$i]["folder_name"]."</option>";
		}
	    ?>
	    </select>
	    </td>
	    </tr>
	<?php endif; ?>
	<?php if($contentclass == "page" || $contentclass == "template" || $contentclass == "parts"): ?>

	    <tr>
	    <th>テンプレート</th>
	    <td>
	    <select name="template_id">
	    <option value=""></option>
	    <?php

	    for($i=0;$i<count($templateList);$i++){
			if($template_id == $templateList[$i]["content_id"]){
				$selected = "selected";
			}else{
				$selected = "";
			}
			echo '<option value="'.$templateList[$i]["content_id"].'" '.$selected.'>'.$templateList[$i]["title"]."</option>";
		}
	    ?>
	    </select>
	    </td>
	    </tr>
	<?php elseif($contentclass == "stylesheet"): ?>
		<tr>
	    <th>media</th>
	    <td>
	    <input type="text" name="media" value="<?php echo htmlspecialchars($media);?>" /><br />
	    <span class="info">CSSの適用対象を設定します</span>
	    </td>
	    </tr>
	<?php endif; ?>

	<tr>
    <th style="border-bottom:0px;">コンテンツ<span class="mark orange">必須</span></th>
    <td style="border-bottom:0px;"></td>
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
	    echo "CKEDITOR.config.height = '300px';\n"; //縦幅設定

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
	    if(count($stylesheetUrlList) > 0){
		    echo "CKEDITOR.config.contentsCss = [";
		    for($i=0;$i<count($stylesheetUrlList);$i++){
		    	if($i > 0){ echo ","; }
		    	echo "'/".Config::BASE_DIR_PATH.$stylesheetUrlList[$i]."'";
		    }
		    if(Config::get("editor_custom_css")){
		    	if($i > 0){ echo ","; }
		    	echo "'/".Config::BASE_DIR_PATH.Config::ADMIN_DIR_PATH."ckeditor_css/editor_custom.css'";
			}
		    echo "];\n";
	    }

	    //
	    echo "CKEDITOR.config.bodyId = 'contents';\n";

	    echo "CKEDITOR.config.bodyClass = '';\n";

	    echo "CKEDITOR.config.allowedContent = true;\n";

	    echo "CKEDITOR.config.resize_dir = 'both';\n";
	    echo "CKEDITOR.config.resize_enabled = true;\n";

	    echo "\n\n";
	    echo "//カスタムスクリプト 開始\n";
	    echo Config::get("editor_custom_script");
	    echo "\n//カスタムスクリプト 終了\n";
	    echo "\n\n";

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
	    echo "<textarea class=\"ckeditor\" id=\"content\" name=\"content\">".htmlspecialchars($content)."</textarea>\n";
	}else{
		echo "<textarea class=\"ckeditor\" id=\"content\" name=\"content\" style=\"width:100%; height:300px;\">".htmlspecialchars($content)."</textarea>\n";
	}
    ?>
	<table class="edit" cellspacing="0">
	<?php if($contentclass == "page" || $contentclass == "template" || $contentclass == "parts"): ?>
	    <tr>
	    <th class="w240">エディタ設定</th>
	    <td>
	    <?php
	    if($editmode == "1" || $editmode == "0"){ $checked = "checked"; }else { $checked = ""; }
	    echo '<input type="radio" name="editmode" value="1" '.$checked.'>テキストエリア&nbsp;';
	    if($editmode == "2"){ $checked = "checked"; }else { $checked = ""; }
	    echo '<input type="radio" name="editmode" value="2" '.$checked.'>Webエディタ';
	    ?>
	    </td>
	    </tr>
	<?php endif; ?>
    <?php if($contentclass == "template" || $contentclass == "page"  || $contentclass == "parts" ): ?>
		<tr>
	    <th>スタイルシート <a href="#" id="open_stylesheet">▼</a>
	    <script>
			$("#open_stylesheet").click(function(){
				$("#stylesheet_area").toggle();
				if($("#open_stylesheet").text() == "▼"){
					$("#open_stylesheet").text("▲");
				}else{
					$("#open_stylesheet").text("▼");
				}
				return false;
			});
	    </script>
	    </th>
	    <td>
	    <div id="stylesheet_area" style="display:none;">
	    <?php
	    for($i=0;$i<count($stylesheetList);$i++){
	    	if(in_array($stylesheetList[$i]["content_id"],$stylesheet_id)){
	    		$checked = "checked=checked";
	    	}else{
	    		$checked = "";
	    	}
	    	echo '<input type="checkbox" name="stylesheet_id[]" value='.$stylesheetList[$i]["content_id"].' '.$checked.'>&nbsp;'.$stylesheetList[$i]["title"]."【".$stylesheetList[$i]["url"]."】<br>";
	    }
	    ?>
	    </div>
	    </td>
	    </tr>
	<?php endif; ?>
    <?php if($contentclass == "template" || $contentclass == "page" ): ?>
	    <tr>
	    <th>スクリプト <a href="#" id="open_script">▼</a>
	    <script>
			$("#open_script").click(function(){
				$("#script_area").toggle();
				if($("#open_script").text() == "▼"){
					$("#open_script").text("▲");
				}else{
					$("#open_script").text("▼");
				}
				return false;
			});
	    </script>
	    </th>
	    <td>
	    <div id="script_area" style="display:none;">
	    <?php
	    for($i=0;$i<count($scriptList);$i++){
	    	if(in_array($scriptList[$i]["content_id"],$script_id)){
	    		$checked = "checked=checked";
	    	}else{
	    		$checked = "";
	    	}
	    	echo '<input type="checkbox" name="script_id[]" value='.$scriptList[$i]["content_id"].' '.$checked.'>&nbsp;'.$scriptList[$i]["title"]."【".$stylesheetList[$i]["url"]."】<br>";
	    }
	    ?>
	    </div>
	    </td>
	    </tr>

	<?php endif; ?>

	<?php if($contentclass == "page" || $contentclass == "template"): ?>

	    <tr>
	    <th>HTML HEADコード <a href="#" id="open_head_code">▼</a>
	    <script>
			$("#open_head_code").click(function(){
				$("*[class=head_code]").toggle();
				if($("#open_head_code").text() == "▼"){
					$("#open_head_code").text("▲");
				}else{
					$("#open_head_code").text("▼");
				}
				return false;
			});
	    </script>
	    </th>
	    <td></td>
	    </tr>
	    <tr class="head_code" style="display:none;">
		<td>　HTML属性</td>
	    <td>
	    <input type="text" name="html_attr" value="<?php echo htmlspecialchars($html_attr);?>" />
	    </td>
	    </tr>
	    <tr class="head_code" style="display:none;">
		<td>　HEAD属性</td>
	    <td>
	    <input type="text" name="head_attr" value="<?php echo htmlspecialchars($head_attr);?>" />
	    </td>
	    </tr>
	    <tr class="head_code" style="display:none;">
		<td>　BODY属性</td>
	    <td>
	    <input type="text" name="body_attr" value="<?php echo htmlspecialchars($body_attr);?>" />
	    </td>
	    </tr>
		<tr class="head_code" style="display:none;">
		<td>　HEADコード</td>
	    <td></td>
	    </tr>
	    <tr class="head_code" style="display:none;">
	    <td colspan="2">
		<textarea name="head_code" style="width:99%; height:300px;"><?php echo $head_code; ?></textarea>
	    </td>
	    </tr>
	<?php endif; ?>
    <?php if($contentclass == "page"): ?>
    	<tr>
	    <th colspan="2">プレフィックス・サフィックス <a href="#" id="open_prefix_suffix">▼</a>
	    <script>
			$("#open_prefix_suffix").click(function(){
				$("*[class=prefix_suffix]").toggle();
				if($("#open_prefix_suffix").text() == "▼"){
					$("#open_prefix_suffix").text("▲");
				}else{
					$("#open_prefix_suffix").text("▼");
				}
				return false;
			});
	    </script>
	    </th>
	    <td></td>
	    </tr>

	    <tr class="prefix_suffix" style="display:none;">
	    <td>　プレフィックス</td>
	    <td>
	    <input type="text" name="title_prefix" value="<?php echo htmlspecialchars($title_prefix);?>" /><br />
	    </td>
	    </tr>

	    <tr class="prefix_suffix" style="display:none;">
	    <td>　サフィックス</td>
	    <td>

	    <input type="text" name="title_suffix" value="<?php echo htmlspecialchars($title_suffix);?>" /><br />
	    </td>
	    </tr>

    <?php endif; ?>
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
    <th>公開開始<span class="mark orange">必須</span></th>
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
    <th>公開終了<span class="mark orange">必須</span></th>
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
			if($ope_auth_publish == "1"){
				echo '<td style="border-bottom:0px"><input type="checkbox" name="delete_schedule_id[]" value="'.$scheduleList[$i]["content_schedule_id"].'" />削除	</td>'."\n";
			}
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
