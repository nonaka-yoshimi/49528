<?php
require_once(dirname(__FILE__).'/../ApplicationCommon/include.php');
require_once(dirname(__FILE__).'/../DataAccess/Content.php');
$value = "";
try {

	//引数：変換文字列, エントリID（リンク用）[, エントリID（メール用）]
	//※
	//引数の文字列中から対象表記（URL、メールアドレス）を抽出し、
	//エントリに従って内容を置き換える。

	//----------------------
	// パラメタ取得
	//----------------------

	$user_id = isset($_REQUEST["user_id"]) ? $_REQUEST["user_id"] : null;
	$Content = new Content( $user_id ? Content::TABLE_MANAGEMENT : Content::TABLE_PUBLIC);

	$paramArr = explode(",", isset($_REQUEST["param"]) ? $_REQUEST["param"] : "");

	$i = 0;
	//対象文字列
	$value = isset($paramArr[$i]) ? $paramArr[$i] : null;
	if ( $value == null ) throw new Exception('引数不正');

	$i++;
	//エントリID（リンク）
	$entry_link_id = isset($paramArr[$i]) ? $paramArr[$i] : null;
	if ( $entry_link_id ) {
		$content = $Content->getContentDataByContentId($entry_link_id);
		$entry_link = isset($content["content"]) ? $content["content"] : null;
		if ( $entry_link == null ) throw new Exception('コンテンツ取得失敗');
	}

	$i++;
	//エントリID（メール）
	$entry_mail_id = isset($paramArr[$i]) ? $paramArr[$i] : null;
	if ( $entry_mail_id ) {
		$content = $Content->getContentDataByContentId($entry_mail_id);
		$entry_mail = isset($content["content"]) ? $content["content"] : null;
		if ( $entry_mail == null ) throw new Exception('コンテンツ取得失敗');
	}

	//----------------------
	// HTML生成・返却
	//----------------------

	$html = $value;

	//リンク
	if ( $entry_link_id && preg_match_all("(https?://[-_.!~*\'()a-zA-Z0-9;/?:@&=+$,%#]+)", $html, $matches) > 0 ) {
		$links = array_unique($matches[0]);
		foreach ( $links as $link ) {
			$linkFormatted = str_replace("{{{link}}}", $link, $entry_link);
			$html = str_replace($link, $linkFormatted, $html);
		}
	}

	//メール
	if ( $entry_mail_id && preg_match_all("([A-Za-z0-9\-\.\_]+@[A-Za-z0-9\-\_]+\.[A-Za-z0-9\-\.\_]+)", $html, $matches) > 0 ) {
		$mails = array_unique($matches[0]);
		foreach ( $mails as $mail ) {
			$mailFormatted = str_replace("{{{mail}}}", $mail, $entry_mail);
			$html = str_replace($mail, $mailFormatted, $html);
		}
	}

	echo $html;

} catch ( Exception $e ) {
  return $value;
}
?>