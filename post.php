<?php
require_once(dirname(__FILE__)."/webadmin/ApplicationCommon/include.php");
require_once(dirname(__FILE__)."/webadmin/DataAccess/Content.php");
$error = array();

//パラメータ取得
$post_redirect = isset($_REQUEST["post_redirect"]) ? $_REQUEST["post_redirect"] : "";
$post_redirect_error = isset($_REQUEST["post_redirect_error"]) ? $_REQUEST["post_redirect_error"] : "";
$post_mail_to = isset($_REQUEST["post_mail_to"]) ? $_REQUEST["post_mail_to"] : "";
$post_mail_to1 = isset($_REQUEST["post_mail_to1"]) ? $_REQUEST["post_mail_to1"] : "";
$post_mail_to2 = isset($_REQUEST["post_mail_to2"]) ? $_REQUEST["post_mail_to2"] : "";
$post_mail_cc = isset($_REQUEST["post_mail_cc"]) ? $_REQUEST["post_mail_cc"] : "";
$post_mail_cc1 = isset($_REQUEST["post_mail_cc1"]) ? $_REQUEST["post_mail_cc1"] : "";
$post_mail_cc2 = isset($_REQUEST["post_mail_cc2"]) ? $_REQUEST["post_mail_cc2"] : "";
$post_mail_from = isset($_REQUEST["post_mail_from"]) ? $_REQUEST["post_mail_from"] : "";
$post_mail_from1 = isset($_REQUEST["post_mail_from1"]) ? $_REQUEST["post_mail_from1"] : "";
$post_mail_from2 = isset($_REQUEST["post_mail_from2"]) ? $_REQUEST["post_mail_from2"] : "";
$post_mail_from_name = isset($_REQUEST["post_mail_from_name"]) ? $_REQUEST["post_mail_from_name"] : "";
$post_mail_from_name1 = isset($_REQUEST["post_mail_from_name1"]) ? $_REQUEST["post_mail_from_name1"] : "";
$post_mail_from_name2 = isset($_REQUEST["post_mail_from_name2"]) ? $_REQUEST["post_mail_from_name2"] : "";
$post_mail_subject = isset($_REQUEST["post_mail_subject"]) ? $_REQUEST["post_mail_subject"] : "";
$post_mail_subject1 = isset($_REQUEST["post_mail_subject1"]) ? $_REQUEST["post_mail_subject1"] : "";
$post_mail_subject2 = isset($_REQUEST["post_mail_subject2"]) ? $_REQUEST["post_mail_subject2"] : "";
$post_mail_content = isset($_REQUEST["post_mail_content"]) ? $_REQUEST["post_mail_content"] : "";
$post_mail_content1 = isset($_REQUEST["post_mail_content1"]) ? $_REQUEST["post_mail_content1"] : "";
$post_mail_content2 = isset($_REQUEST["post_mail_content2"]) ? $_REQUEST["post_mail_content2"] : "";
$post_mail_content_id = isset($_REQUEST["post_mail_content_id"]) ? $_REQUEST["post_mail_content_id"] : "";
$post_mail_content_id1 = isset($_REQUEST["post_mail_content_id1"]) ? $_REQUEST["post_mail_content_id1"] : "";
$post_mail_content_id2 = isset($_REQUEST["post_mail_content_id2"]) ? $_REQUEST["post_mail_content_id2"] : "";
$exclude_param = array("post_redirect","post_mail_to","post_mail_to1","post_mail_to2","post_mail_from","post_mail_from1","post_mail_from2");

//メールテンプレート用コンテンツデータを取得
$ContentPublic = new Content(Content::TABLE_PUBLIC);
$post_mail_content = "";
$post_mail_content1 = "";
$post_mail_content2 = "";
if($post_mail_content_id){
	$post_mail_content_data = $ContentPublic->getDataByPrimaryKey($post_mail_content_id);
	if($post_mail_content_data){
		if(!$post_mail_subject){
			$post_mail_subject = $post_mail_content_data["title"];
		}
		if(!$post_mail_content){
			$post_mail_content = $post_mail_content_data["content"];
		}
	}
}
if($post_mail_content_id1){
	$post_mail_content_data1 = $ContentPublic->getDataByPrimaryKey($post_mail_content_id1);
	if($post_mail_content_data1){
		if(!$post_mail_subject1){
			$post_mail_subject1 = $post_mail_content_data1["title"];
		}
		if(!$post_mail_content1){
			$post_mail_content1 = $post_mail_content_data1["content"];
		}
	}
}
if($post_mail_content_id2){
	$post_mail_content_data2 = $ContentPublic->getDataByPrimaryKey($post_mail_content_id2);
	if($post_mail_content_data2){
		if(!$post_mail_subject2){
			$post_mail_subject2 = $post_mail_content_data2["title"];
		}
		if(!$post_mail_content2){
			$post_mail_content2 = $post_mail_content_data2["content"];
		}
	}
}

//置換パラメータ生成
$replace_param = array();
foreach($_REQUEST as $key => $value){
	if(!in_array($key,$exclude_param)){
		$replace_param[Config::REPLACE_MARK_START.$key.Config::REPLACE_MARK_END] = $value;
	}
}

//メールデフォルト送信
if($post_mail_to && $post_mail_content){
	if($post_mail_from){
		//メールクラス取得
		$Mail = new Mail();
		//TO設定
		$Mail->setTo($post_mail_to);
		//CC設定
		$Mail->setCc($post_mail_cc);
		//FROM設定
		$Mail->setFrom($post_mail_from);
		//FROM名称設定
		if($post_mail_from_name){
			$Mail->setFromName($post_mail_from_name);
		}
		//文字列置換処理
		$mail_body = str_replace(array_keys($replace_param), array_values($replace_param), $post_mail_content);
		$mail_subject =  str_replace(array_keys($replace_param), array_values($replace_param), $post_mail_subject);

		//タイトル設定
		$Mail->setSubject($mail_subject);

		//本文設定
		$Mail->setBody($mail_body);
		if($Mail->send()){
			Logger::info("post.phpよりメールを送信しました。",$_REQUEST);
		}else{
			Logger::info("post.phpからのメール送信に失敗しました。",$_REQUEST);
		}
	}
}

//メール1送信
if($post_mail_to1 && $post_mail_content1){
	if($post_mail_from1){
		//メールクラス取得
		$Mail = new Mail();
		//TO設定
		$Mail->setTo($post_mail_to1);
		//CC設定
		$Mail->setCc($post_mail_cc1);
		//FROM設定
		$Mail->setFrom($post_mail_from1);
		//FROM名称設定
		if($post_mail_from_name1){
			$Mail->setFromName($post_mail_from_name1);
		}
		//文字列置換処理
		$mail_body = str_replace(array_keys($replace_param), array_values($replace_param), $post_mail_content1);
		$mail_subject =  str_replace(array_keys($replace_param), array_values($replace_param), $post_mail_subject1);

		//タイトル設定
		$Mail->setSubject($mail_subject);

		//本文設定
		$Mail->setBody($mail_body);
		if($Mail->send()){
			Logger::info("post.phpよりメールを送信しました。",$_REQUEST);
		}else{
			Logger::info("post.phpからのメール送信に失敗しました。",$_REQUEST);
		}
	}
}

//メール2送信
if($post_mail_to2 && $post_mail_content2){
	if($post_mail_from2){
		//メールクラス取得
		$Mail = new Mail();
		//TO設定
		$Mail->setTo($post_mail_to2);
		//CC設定
		$Mail->setCc($post_mail_cc2);
		//FROM設定
		$Mail->setFrom($post_mail_from2);
		//FROM名称設定
		if($post_mail_from_name2){
			$Mail->setFromName($post_mail_from_name2);
		}
		//文字列置換処理
		$mail_body = str_replace(array_keys($replace_param), array_values($replace_param), $post_mail_content2);
		$mail_subject =  str_replace(array_keys($replace_param), array_values($replace_param), $post_mail_subject2);

		//タイトル設定
		$Mail->setSubject($mail_subject);

		//本文設定
		$Mail->setBody($mail_body);
		if($Mail->send()){
			Logger::info("post.phpよりメールを送信しました。",$_REQUEST);
		}else{
			Logger::info("post.phpからのメール送信に失敗しました。",$_REQUEST);
		}
	}
}

//リダイレクト処理
if($error == array()){
	$post_redirect = ltrim($post_redirect, '/');
	Location::redirect("/".Config::BASE_DIR_PATH.$post_redirect);
}else{
	if($post_redirect_error){
		$post_redirect_error = ltrim($post_redirect_error, '/');
		Location::redirect("/".Config::BASE_DIR_PATH.$post_redirect_error);
	}else{
		$post_redirect = ltrim($post_redirect, '/');
		Location::redirect("/".Config::BASE_DIR_PATH.$post_redirect);
	}
}
?>