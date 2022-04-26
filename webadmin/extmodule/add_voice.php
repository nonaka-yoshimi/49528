<?php
require_once(dirname(__FILE__).'/../ApplicationCommon/include.php');
require_once(dirname(__FILE__).'/../CMSCommon/include.php');
require_once(dirname(__FILE__).'/../addon/voice/DataAccess/VoiceContent.php');
require_once(dirname(__FILE__).'/../DataAccess/AddInfoSelect.php');

//----------------------
// パラメタ取得
//----------------------
//{{{ext:add_voice(【エントリID/カラム名】)}}}

$user_id = isset($_REQUEST["user_id"]) ? $_REQUEST["user_id"] : null;
$paramArr = explode(",", isset($_REQUEST["param"]) ? $_REQUEST["param"] : "");

//エントリID or カラム名
$entry_id = isset($paramArr[0]) ? $paramArr[0] : null;
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
$VoiceContent = new VoiceContent();
$AddInfoSelect = new AddInfoSelect();

$where = array();
$where["status"] = 0;
$where["del_flg"] = 0;
$order = array();
$order["date_year"] = "DESC";
$order["date_month"] = "DESC";
$VoiceContentList = $VoiceContent->getListByParameters($where,$order);

//性別によってスタイルのクラスを出しわける
for($i = 0; $i < count($VoiceContentList); $i++){
	if($VoiceContentList[$i]["gender"] == 0){
		$VoiceContentList[$i]["class"] = "man";
	}else{
		$VoiceContentList[$i]["class"] = "woman";
	}
}

//選択肢一覧を取得
$where = array();
$where["selectname"] = "voice_age";
$order = array();
$order["optionvalue"] = "ASC";
$voiceYearList = $AddInfoSelect->getListByParameters($where,$order);

if ( count($VoiceContentList) == 0 ) return;

//----------------------
// HTML生成・返却
//----------------------
if ( $entry_id) {
	$html = "";
	$voice_list = "";

	//動作未確認 必要に応じて調整してください。
	$Content = new Content( $user_id ? Content::TABLE_MANAGEMENT : Content::TABLE_PUBLIC);
	$content = $Content->getContentDataByContentId($entry_id);
	$html = isset($content["content"]) ? $content["content"] : null;
	if ( $html == null ) return;

	foreach($VoiceContentList as $val){
		if($val["gender"] == 0){
			$gender = "男性";
		}else{
			$gender = "女性";
		}

		foreach($voiceYearList as $voice_year) {
			if($voice_year["optionvalue"] == $val["age"]){
				$voice_year_disp = $voice_year["optionvalue_name"];
				break;
			}
		}

		$voice_list .='
		<section class="voiceArea">
		<div class="people">
		<div class="voice '.$val["class"].'">
		<p>'.$voice_year_disp.'代<br />
		<span>'.$gender.'</span></p>
		</div>
		</div>
		
		<div class="description">
		<p class="text"><time>'.$val["date_year"].'年'.$val["date_month"].'月</time> '.$val["comment"].'</p>
		</div>
		</section>';
	}
	$html = str_replace("{{{voice_list}}}", $voice_list, $html);

	echo $html;
}
?>