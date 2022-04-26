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
$folder_id = isset($_POST["folder_id"]) ? $_POST["folder_id"] : "";

if(!$content_id_list || !$folder_id){
	Location::redirect($redirect_url);
}

$Content = new Content(Content::TABLE_MANAGEMENT);		//コンテンツクラス

foreach($content_id_list as $content_id){
	DB::beginTransaction();

	$saveData = array();
	$saveData["folder_id"] = $folder_id;

	//更新処理
	$contentData = $Content->getDataByPrimaryKey($content_id);
	if($contentData["folder_id"] != $folder_id){
		$saveData["sort_no"] = $Content->getMaxSort($contentData["contentclass"], $saveData["folder_id"]);
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

	DB::commit();
}

Location::redirect($redirect_url);
?>