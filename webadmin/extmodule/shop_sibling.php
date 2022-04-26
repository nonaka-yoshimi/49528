<?php
require_once(dirname(__FILE__).'/../ApplicationCommon/include.php');
require_once(dirname(__FILE__).'/../addon/shop/DataAccess/ShopContent.php');

//----------------------
// パラメタ取得
//----------------------
//{{{ext:content_next(【prev/next】,【現在のコンテンツID】,【エントリID/カラム名】)

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

//----------------------
// データ取得
//----------------------
$shopContent = new ShopContent( $user_id ? Content::TABLE_MANAGEMENT : Content::TABLE_PUBLIC);
$dataList = $shopContent->getFrontList(true);
if ( !isset($dataList[0]) ) return;

//----------------------
// 前or次の店舗
//----------------------

$shop = null;
$isNext = false;

//先頭・末尾に達した場合ループさせるため、データを２倍にしておく
$dataList = array_merge($dataList, $dataList);

if ( $direction == "prev" ) {
	//--------------------
	// 前の店舗
	//--------------------
	//データの末尾から検索
	for ( $i=count($dataList)-1; $i>=0; $i-- ) {
		if ( $isNext ) {
			$shop = $dataList[$i];
			break;
		}
		if ( $dataList[$i]["content_id"] == $content_id ) {
			$isNext = true;
		}
	}

} else {
	//--------------------
	// 次の店舗
	//--------------------
	//データの先頭から検索
	foreach ($dataList as $data) {
		if ( $isNext ) {
			$shop = $data;
			break;
		}
		if ( $data["content_id"] == $content_id ) {
			$isNext = true;
		}
	}
}
if ( $shop == null ) return;

//----------------------
// HTML生成・返却
//----------------------
if ( $entry_id ) {

	//動作未確認 必要に応じて調整してください。
	$Content = new Content( $user_id ? Content::TABLE_MANAGEMENT : Content::TABLE_PUBLIC);
	$content = $Content->getContentDataByContentId($entry_id);
	$html = isset($content["content"]) ? $content["content"] : null;
	if ( $html == null ) return;

	$html = str_replace("{{{url}}}",        isset($shop["url"])        ? $shop["url"]        : "", $html);
	$html = str_replace("{{{title}}}",      isset($shop["title"])      ? $shop["title"]      : "", $html);
	$html = str_replace("{{{shop_year}}}",  isset($shop["shop_year"])  ? $shop["shop_year"]  : "", $html);
	$html = str_replace("{{{shop_month}}}", isset($shop["shop_month"]) ? $shop["shop_month"] : "", $html);
	$html = str_replace("{{{shop_image}}}", isset($shop["shop_image"]) ? $shop["shop_image"] : "", $html);

	echo $html;

} else {
	echo isset($shop[$column]) ? $shop[$column] : "";
}

?>