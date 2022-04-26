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

$Content = new Content(Content::TABLE_MANAGEMENT);		//コンテンツクラス
$ContentPublic = new Content(Content::TABLE_PUBLIC);	//公開コンテンツクラス

$deploy_file_flg = false;
if(Config::get("static_stylesheet") == "on" || Config::get("static_script") == "on"){
	$deploy_file_flg = true;
}

DB::beginTransaction();
foreach($content_id_list as $content_id){
	//更新日更新
	if(!$Content->updateContent($content_id, array(), $session->user["user_id"])){
		DB::rollBack();
		Logger::error("コンテンツ更新に失敗しました。",$saveData);
		Location::redirect($redirect);
	}
	//非公開実行処理
	if(!$Content->unpublishContent($content_id)){
		DB::rollBack();
		Logger::error("非公開処理に失敗しました。content_id:".$content_id);
		Location::redirect($redirect);
	}else{
		// ファイル非公開処理
		if($deploy_file_flg){
			$contentData = $Content->getDataByPrimaryKey($content_id);
			if($contentData){
				if(($contentData["contentclass"] == "stylesheet" && Config::get("static_stylesheet") == "on")
						|| ($contentData["contentclass"] == "script" && Config::get("static_script") == "on")){
					// 物理ファイルを削除する
					Util::deletePhysicalFile(dirname(__FILE__)."/../",$contentData["url"]);
				}
			}
		}

		$logparam = array();
		$logparam["content_id"] = $content_id;
		$logparam["user_id"] = $session->user["user_id"];
		$logparam["name"] = $session->user["name"];
		Logger::info("非公開処理を行いました。",$logparam);
	}
}
DB::commit();

Location::redirect($redirect_url);
?>