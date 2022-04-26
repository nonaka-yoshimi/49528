<?php
require_once(dirname(__FILE__).'/../ApplicationCommon/include.php');
require_once(dirname(__FILE__).'/../addon/event/DataAccess/EventContent.php');

//----------------------
// パラメタ取得
//----------------------
//{{{ext:content_next(【prev/next】,【現在のコンテンツID】,【エントリID/カラム名】,【URL必須】)

$user_id = isset($_REQUEST["user_id"]) ? $_REQUEST["user_id"] : null;
$paramArr = explode(",", isset($_REQUEST["param"]) ? $_REQUEST["param"] : "");

//prev or next
$direction = isset($paramArr[0]) ? $paramArr[0] : null;
if ( $direction == null ) return; //引数不正

//現在のコンテンツID
$content_id = isset($paramArr[1]) ? $paramArr[1] : null;
if ( $content_id == null ) return; //引数不正

//エントリID or カラム名
$entry_id = isset($paramArr[2]) ? $paramArr[2] : null;
$column = null;
if ( $entry_id == null ) return; //引数不正;
if ( !is_numeric($entry_id) ) {
	//カラム指定のケース
	$column = $entry_id;
	$entry_id = null;
}

//URL必須
$isRequiredUrl = isset($paramArr[3]) ? $paramArr[3] : null;
if ( $isRequiredUrl == null ) {
	$isRequiredUrl = false; //引数省略時は必須チェックなし
}

//----------------------
// データ取得
//----------------------
$eventContent = new EventContent( $user_id ? Content::TABLE_MANAGEMENT : Content::TABLE_PUBLIC);
$dataList = $eventContent->getFrontList();
if ( !isset($dataList[0]) ) return;

//----------------------
// 前or次のイベント
//----------------------

$event = null;
$isNext = false;

//先頭・末尾に達した場合ループさせるため、データを２倍にしておく
$dataList = array_merge($dataList, $dataList);

if ( $direction == "prev" ) {
	//--------------------
	// 前のイベント
	//--------------------
	//データの末尾から検索
	for ( $i=count($dataList)-1; $i>=0; $i-- ) {
		if ( $isNext ) {
			if ( $isRequiredUrl && !$dataList[$i]["url"] ) continue; //URL必須指定の場合、URLのないページはスキップ
			$event = $dataList[$i];
			break;
		}
		if ( $dataList[$i]["content_id"] == $content_id ) {
			$isNext = true;
		}
	}

} else {
	//--------------------
	// 次のイベント
	//--------------------
	//データの先頭から検索
	foreach ($dataList as $data) {
		if ( $isNext ) {
			if ( $isRequiredUrl && !$data["url"] ) continue; //URL必須指定の場合、URLのないページはスキップ
			$event = $data;
			break;
		}
		if ( $data["content_id"] == $content_id ) {
			$isNext = true;
		}
	}
}
if ( $event == null ) return;

//----------------------
// HTML生成・返却
//----------------------
if ( $entry_id ) {

	//動作未確認 必要に応じて調整してください。
	$Content = new Content( $user_id ? Content::TABLE_MANAGEMENT : Content::TABLE_PUBLIC);
	$content = $Content->getContentDataByContentId($entry_id);
	$html = isset($content["content"]) ? $content["content"] : null;
	if ( $html == null ) return;

	$html = str_replace("{{{url}}}",         isset($event["url"])         ? $event["url"]         : "", $html);
	$html = str_replace("{{{title}}}",       isset($event["title"])       ? $event["title"]       : "", $html);
	$html = str_replace("{{{event_year}}}",  isset($event["event_year"])  ? $event["event_year"]  : "", $html);
	$html = str_replace("{{{event_month}}}", isset($event["event_month"]) ? $event["event_month"] : "", $html);
	$html = str_replace("{{{event_image}}}", isset($event["event_image"]) ? $event["event_image"] : "", $html);

	echo $html;

} else {
	echo isset($event[$column]) ? $event[$column] : "";
}

?>