<?php
require_once(dirname(__FILE__)."/webadmin/ApplicationCommon/include.php");
require_once(dirname(__FILE__)."/webadmin/DataAccess/Content.php");
$error = array();

// 既定パラメータ取得
$post_redirect = isset($_REQUEST["post_redirect"]) ? $_REQUEST["post_redirect"] : "";									// post処理が成功したのちの遷移先URL
$post_redirect_error = isset($_REQUEST["post_redirect_error"]) ? $_REQUEST["post_redirect_error"] : "";					// post処理が失敗したのちの遷移先URL
$mail_address = isset($_REQUEST["mail_address"]) ? $_REQUEST["mail_address"] : "";										// お問い合わせ者のメールアドレス（ユーザ入力）
$exclude_param = array("post_redirect","post_redirect_error","mail_address");

// システム設定より値を取得
$form_admin_mail_to = Config::get("form_admin_mail_to");					// 管理者向け通知メール宛先アドレス
$form_admin_mail_from = Config::get("form_admin_mail_from");				// 管理者向け通知メール送信元アドレス
$form_admin_mail_from_name = Config::get("form_admin_mail_from_name");		// 管理者向け通知メール送信者名
$form_admin_mail_subject = Config::get("form_admin_mail_subject");			// 管理者向け通知メール題名
$form_admin_mail_template = Config::get("form_admin_mail_template");		// 管理者向け通知メールテンプレート
$form_thanks_mail_from = Config::get("form_thanks_mail_from");				// サンクスメール送信元アドレス
$form_thanks_mail_from_name = Config::get("form_thanks_mail_from_name");	// サンクスメール送信者名
$form_thanks_mail_subject = Config::get("form_thanks_mail_subject");		// サンクスメール題名
$form_thanks_mail_template = Config::get("form_thanks_mail_template");		// サンクスメールテンプレート

// 置換パラメータ生成
$replace_param = array();
foreach($_REQUEST as $key => $value){
	//if(!in_array($key,$exclude_param)){
		$replace_param[Config::REPLACE_MARK_START.$key.Config::REPLACE_MARK_END] = $value;
	//}
}

//管理者向け通知メール送信
if($form_admin_mail_to && $form_admin_mail_template){
	if($form_admin_mail_from){
		//メールクラス取得
		$Mail = new Mail();
		//TO設定
		$Mail->setTo($form_admin_mail_to);
		//FROM設定
		$Mail->setFrom($form_admin_mail_from);
		//FROM名称設定
		if($form_admin_mail_from_name){
			$Mail->setFromName($form_admin_mail_from_name);
		}
		//文字列置換処理
		$mail_body = str_replace(array_keys($replace_param), array_values($replace_param), $form_admin_mail_template);
		$mail_subject =  str_replace(array_keys($replace_param), array_values($replace_param), $form_admin_mail_subject);

		//タイトル設定
		$Mail->setSubject($mail_subject);

		//本文設定
		$Mail->setBody($mail_body);
		if($Mail->send()){
			Logger::info("default_form_post.phpより管理者向け通知メールを送信しました。",$_REQUEST);
		}else{
			Logger::info("default_form_post.phpからの管理者向け通知メール送信に失敗しました。",$_REQUEST);
		}
	}
}

//サンクスメール送信
if($mail_address && $form_admin_mail_template){
	if($form_thanks_mail_from){
		//メールクラス取得
		$Mail = new Mail();
		//TO設定
		$Mail->setTo($mail_address);
		//FROM設定
		$Mail->setFrom($form_thanks_mail_from);
		//FROM名称設定
		if($form_thanks_mail_from_name){
			$Mail->setFromName($form_thanks_mail_from_name);
		}
		//文字列置換処理
		$mail_body = str_replace(array_keys($replace_param), array_values($replace_param), $form_thanks_mail_template);
		$mail_subject =  str_replace(array_keys($replace_param), array_values($replace_param), $form_thanks_mail_subject);

		//タイトル設定
		$Mail->setSubject($mail_subject);

		//本文設定
		$Mail->setBody($mail_body);
		if($Mail->send()){
			Logger::info("default_form_post.phpよりサンクスメールを送信しました。",$_REQUEST);
		}else{
			Logger::info("default_form_post.phpからのサンクスメール送信に失敗しました。",$_REQUEST);
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