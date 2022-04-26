<?php
// アプリケーション共通クラス読込
require_once(dirname(__FILE__).'/ApplicationCommon/include.php');
require_once(dirname(__FILE__).'/DataAccess/Content.php'); 			//コンテンツクラス

//セッション取得
$session = Session::get();

//セッション認証
$session->loginCheckAndRedirect("login.php",array("admin_flg" => 1));

//パラメータ取得
$redirect_url = isset($_POST["redirect_url"]) ? $_POST["redirect_url"] : "";
$content_id_list = isset($_POST["content_id"]) ? $_POST["content_id"] : "";

if(!$content_id_list){
	Location::redirect($redirect_url);
}

$Content = new Content(Content::TABLE_ARCHIVE);		//コンテンツクラス

DB::beginTransaction();
foreach($content_id_list as $content_archive_id){

	//コンテンツID取得
	$contentData = $Content->getDataByPrimaryKey($content_archive_id);
	$content_id = $contentData["content_id"];
	if(!$content_id){
		continue;
	}
	//リストア実行処理
	if(!$Content->restoreContent($content_id, $content_archive_id)){
		DB::rollBack();
		Logger::error("リストア処理に失敗しました。content_archive_id:".$content_archive_id);
		Location::redirect($redirect);
	}else{
		$logparam = array();
		$logparam["content_id"] = $content_id;
		$logparam["content_archive_id"] = $content_archive_id;
		$logparam["user_id"] = $session->user["user_id"];
		$logparam["name"] = $session->user["name"];
		Logger::info("リストア処理を行いました。",$logparam);
	}
}
DB::commit();

Location::redirect($redirect_url);
?>