<?php
require_once(dirname(__FILE__).'/../ApplicationCommon/include.php');
require_once(dirname(__FILE__).'/../addon/event/DataAccess/EventContent.php');

//----------------------
// パラメタ取得
//----------------------
$user_id = isset($_REQUEST["user_id"]) ? $_REQUEST["user_id"] : null;
$paramArr = explode(",", isset($_REQUEST["param"]) ? $_REQUEST["param"] : "");
$entry_id = isset($paramArr[0]) ? $paramArr[0] : null;
if ( $entry_id == null ) return;

//----------------------
// データ取得
//----------------------
$eventContent = new EventContent( $user_id ? Content::TABLE_MANAGEMENT : Content::TABLE_PUBLIC);
$dataList = $eventContent->getFrontListPickuped();
if ( !isset($dataList[0]) ) return;

//----------------------
//今月以降直近のイベント
//----------------------
$event = null;
$date = new DateTime();
$year = $date->format("Y");
$month = $date->format("m");

//今年分として検索
foreach ($dataList as $data) {
	if ( $month <= $data["event_month"] ) {
		$event = $data;
		$event["event_year"] = $year;
		break;
	}
}

//今年分になければ、翌年扱い
if ( $event == null ) {
	$event = $dataList[0];
	$event["event_year"] = ++$year;
}

//----------------------
// HTML生成・返却
//----------------------
$Content = new Content( $user_id ? Content::TABLE_MANAGEMENT : Content::TABLE_PUBLIC);
$content = $Content->getContentDataByContentId($entry_id);
$html = isset($content["content"]) ? $content["content"] : null;
if ( $html == null ) return;

$html = str_replace("{{{url}}}", $event["url"], $html);
$html = str_replace("{{{title}}}", $event["title"], $html);
$html = str_replace("{{{event_year}}}", $event["event_year"], $html);
$html = str_replace("{{{event_month}}}", $event["event_month"], $html);
$html = str_replace("{{{event_image}}}", $event["event_image"], $html);

echo $html;

?>