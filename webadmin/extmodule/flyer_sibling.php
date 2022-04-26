<?php
require_once(dirname(__FILE__).'/../ApplicationCommon/include.php');
require_once(dirname(__FILE__).'/../addon/flyer/DataAccess/FlyerContent.php');

//----------------------
// パラメタ取得
//----------------------
//引数：prev/next, 現在のコンテンツID, エントリID（通常）, エントリID（リンク先なし）, [,一覧表示件数]

$user_id = isset($_REQUEST["user_id"]) ? $_REQUEST["user_id"] : null;
$paramArr = explode(",", isset($_REQUEST["param"]) ? $_REQUEST["param"] : "");
$i = 0;

//prev or next
$direction = isset($paramArr[$i]) ? $paramArr[$i] : null;
if ( $direction == null ) return; //引数不正
$i++;

//現在のコンテンツID
$content_id = isset($paramArr[$i]) ? $paramArr[$i] : null;
if ( $content_id == null ) return; //引数不正
$i++;

//エントリID（通常）
$entry_id_enabled = isset($paramArr[$i]) ? $paramArr[$i] : null;
if ( $entry_id_enabled == null ) return; //引数不正;
$i++;

//エントリID（リンク先なし）
$entry_id_disabled = isset($paramArr[$i]) ? $paramArr[$i] : null;
if ( $entry_id_disabled == null ) return; //引数不正;
$i++;

//一覧表示件数
$count = isset($paramArr[$i]) ? $paramArr[$i] : null;
$i++;

//----------------------
// データ取得
//----------------------
$flyerContent = new FlyerContent( $user_id ? Content::TABLE_MANAGEMENT : Content::TABLE_PUBLIC);
$dataList = $flyerContent->getFrontList($count);

//----------------------
// 前・次 URL
//----------------------
$flyer = null;
$isNext = false;

if ( isset($dataList[0]) ) {
	//データがあれば隣接を検索

	if ( $direction == "prev" ) {
		//--------------------
		// 前のチラシ
		//--------------------
		//データの末尾から検索
		for ( $i=count($dataList)-1; $i>=0; $i-- ) {
			if ( $isNext ) {
				$flyer = $dataList[$i];
				break;
			}
			if ( $dataList[$i]["content_id"] == $content_id ) {
				$isNext = true;
			}
		}

	} else {
		//--------------------
		// 次のチラシ
		//--------------------
		//データの先頭から検索
		foreach ($dataList as $data) {
			if ( $isNext ) {
				$flyer = $data;
				break;
			}
			if ( $data["content_id"] == $content_id ) {
				$isNext = true;
			}
		}
	}
}

//----------------------
// HTML生成・返却
//----------------------
$Content = new Content( $user_id ? Content::TABLE_MANAGEMENT : Content::TABLE_PUBLIC);


if ( $flyer != null ) {
	//リンク有効
	$content = $Content->getContentDataByContentId($entry_id_enabled);
	$html = isset($content["content"]) ? $content["content"] : null;
	if ( $html == null ) return;

	$html = str_replace("{{{url}}}", isset($flyer["url"]) ? $flyer["url"] : "", $html);

} else {
	//リンク無効
	$content = $Content->getContentDataByContentId($entry_id_disabled);
	$html = isset($content["content"]) ? $content["content"] : null;

}

echo $html;

?>