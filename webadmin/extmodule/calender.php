<?php
//診療日カレンダー契約
$enable = 1;//1:有効 0:無効

require_once(dirname(__FILE__).'/../ApplicationCommon/include.php');
//require_once(dirname(__FILE__).'/../extmodule/include.php');

require_once(dirname(__FILE__).'/../addon/calendar/DataAccess/CalendarFixedContent.php');//コンテンツクラス
require_once(dirname(__FILE__).'/../addon/calendar/DataAccess/CalendarFixedTextContent.php');//コンテンツクラス
require_once(dirname(__FILE__).'/../addon/calendar/DataAccess/CalendarFixedDetailTextContent.php');//コンテンツクラス
require_once(dirname(__FILE__).'/../addon/calendar/DataAccess/CalendarContent.php');//コンテンツクラス
require_once(dirname(__FILE__).'/../addon/calendar/DataAccess/CalendarHolidayContent.php');//コンテンツクラス

$CalendarFixedContent			= new CalendarFixedContent();
$CalendarFixedTextContent 		= new CalendarFixedTextContent();
$CalendarFixedDetailTextContent = new CalendarFixedDetailTextContent();
$CalendarContent 				= new CalendarContent();
$CalendarHolidayContent			= new CalendarHolidayContent();

//基本情報
$calender_fixd_content = $CalendarFixedContent->getListByParameters(array(),array("sort"=>"ASC"));
//print_r($calender_fixd_content);
//基本情報テキスト
$calender_fixd_text_content = $CalendarFixedTextContent->getDataByParameters(0);
//print_r($calender_fixd_text_content);
//診療日カレンダー
$calender_content = $CalendarContent->getListByParameters(array(),array("date"=>"ASC"));
//print_r($calender_content);
$calenderArray = array();
for($i = 0;$i < count($calender_content);$i++){
	$calenderArray[$calender_content[$i]["date"]][$calender_content[$i]["fixed"]] = $calender_content[$i]["type"];
}
//祝日
$calender_holiday_content = $CalendarHolidayContent->getListByParameters(array(),array("date"=>"ASC"));
//print_r($calender_holiday_content);
$calenderHolidayArray = array();
for($i = 0;$i < count($calender_holiday_content);$i++){
	$calenderHolidayArray[$calender_holiday_content[$i]["date"]] = $calender_holiday_content[$i]["text"];
}
//詳細情報テキスト
$calender_fixd_detail_text_content = $CalendarFixedDetailTextContent->getDataByParameters(0);

//曜日配列
$weekArray = array("sun","mon","tue","wed","thu","fri","sat");

$calendar = array();

for($t = 0;$t < 3;$t++){
	//カレンダー表示
	$year = date('Y',strtotime(date('Y')."/".date('m')."/1 +".$t." month"));
	$month = date('n',strtotime(date('Y')."/".date('m')."/1 +".$t." month"));
	$yearDisplay[] = date('Y',strtotime(date('Y')."/".date('m')."/1 +".$t." month"));
	$monthDisplay[] = date('n',strtotime(date('Y')."/".date('m')."/1 +".$t." month"));

	// 月末日を取得
	$last_day = date('j', mktime(0, 0, 0, $month + 1, 0, $year));

	$j = 0;
	// 月末日までループ
	for ($i = 1; $i < $last_day + 1; $i++) {
		// 曜日を取得
		$week = date('w', mktime(0, 0, 0, $month, $i, $year));
		// 1日の場合
		if ($i == 1) {
			if($week != 1){
				// 1日目の曜日までをループ
				for ($s = 2; $s <= $week; $s++) {
					// 前半に空文字をセット
					$calendar[$t][$j]['day'] = '';
					$calendar[$t][$j]['end'] = '';
					$j++;
				}
			}else if($week == 0){
				// 1日目の曜日までをループ
				for ($s = 2; $s <= 7; $s++) {
					// 前半に空文字をセット
					$calendar[$t][$j]['day'] = '';
					$calendar[$t][$j]['end'] = '';
					$j++;
				}
			}
		}
		// 配列に日付をセット
		$calendar[$t][$j]['day'] = $i;
		if($calendar[$t][$j]['day'] != ''){
			for($n = 0;$n < count($calender_fixd_content);$n++){
				if($calender_fixd_content[$n][$weekArray[$week]] == 1){
					$calendar[$t][$j]['display'][$n]['mark'] = "◯";
				}else if($calender_fixd_content[$n][$weekArray[$week]] == 2){
					$calendar[$t][$j]['display'][$n]['mark'] = "△";
				}else if($calender_fixd_content[$n][$weekArray[$week]]== 3){
					$calendar[$t][$j]['display'][$n]['mark'] = "☆";
				}else{
					$calendar[$t][$j]['display'][$n]['mark'] = "－";
				}
				//個別指定参照
				if(isset($calenderArray[$year."-".sprintf('%02d',$month)."-".sprintf('%02d',$i)][$n])){
					if($calenderArray[$year."-".sprintf('%02d',$month)."-".sprintf('%02d',$i)][$n] == 1){
						$calendar[$t][$j]['display'][$n]['mark'] = "◯";
					}else if($calenderArray[$year."-".sprintf('%02d',$month)."-".sprintf('%02d',$i)][$n] == 2){
						$calendar[$t][$j]['display'][$n]['mark'] = "△";
					}else if($calenderArray[$year."-".sprintf('%02d',$month)."-".sprintf('%02d',$i)][$n] == 3){
						$calendar[$t][$j]['display'][$n]['mark'] = "☆";
					}else{
						$calendar[$t][$j]['display'][$n]['mark'] = "－";
					}
				}
			}
		}
		$calendar[$t][$j]['week'] = $week;
		//特定日指定（祝日）
		if(isset($calenderHolidayArray[$year."-".sprintf('%02d',$month)."-".sprintf('%02d',$i)][$n])){
			$calendar[$t][$j]['week'] = 7;
		}
		$calendar[$t][$j]['end'] = '';
		$j++;
		// 月末日の場合
		if ($i == $last_day) {
			if($week != 0){
				// 月末日から残りをループ
				for ($e = 1; $e <= 7 - $week; $e++) {
					// 後半に空文字をセット
					$calendar[$t][$j]['day'] = '';
					$calendar[$t][$j]['end'] = 1;
					$j++;
				}
			}
			$calendar[$t][$j]['end'] = 1;
		}
	}
}

echo '<section class="index_width cntWrap">'."\n";
if(isset($calender_fixd_content)){
	echo '<table class="calendar">'."\n";
	echo '<tbody>'."\n";
	echo '<tr>'."\n";
	echo '<th class="borderNone"><span>診療時間</span></th>'."\n";
	echo '<td>月</td>'."\n";
	echo '<td>火</td>'."\n";
	echo '<td>水</td>'."\n";
	echo '<td>木</td>'."\n";
	echo '<td>金</td>'."\n";
	echo '<td>土</td>'."\n";
	echo '<td>日</td>'."\n";
	echo '<td>祝</td>'."\n";
	echo '</tr>'."\n";
	for($i = 0;$i < count($calender_fixd_content);$i++){
		echo '<tr class="lineHeight">'."\n";
		echo '<th><span>'.$calender_fixd_content[$i]["start"].'～'.$calender_fixd_content[$i]["end"].'</span></th>'."\n";
		if($calender_fixd_content[$i]["mon"] == 1){
			echo '<td>◯</td>'."\n";
		}else if($calender_fixd_content[$i]["mon"] == 2){
			echo '<td>△</td>'."\n";
		}else if($calender_fixd_content[$i]["mon"] == 3){
			echo '<td>☆</td>'."\n";
		}else{
			echo '<td>－</td>'."\n";
		}
		if($calender_fixd_content[$i]["tue"] == 1){
			echo '<td>◯</td>'."\n";
		}else if($calender_fixd_content[$i]["tue"] == 2){
			echo '<td>△</td>'."\n";
		}else if($calender_fixd_content[$i]["tue"] == 3){
			echo '<td>☆</td>'."\n";
		}else{
			echo '<td>－</td>'."\n";
		}
		if($calender_fixd_content[$i]["wed"] == 1){
			echo '<td>◯</td>'."\n";
		}else if($calender_fixd_content[$i]["wed"] == 2){
			echo '<td>△</td>'."\n";
		}else if($calender_fixd_content[$i]["wed"] == 3){
			echo '<td>☆</td>'."\n";
		}else{
			echo '<td>－</td>'."\n";
		}
		if($calender_fixd_content[$i]["thu"] == 1){
			echo '<td>◯</td>'."\n";
		}else if($calender_fixd_content[$i]["thu"] == 2){
			echo '<td>△</td>'."\n";
		}else if($calender_fixd_content[$i]["thu"] == 3){
			echo '<td>☆</td>'."\n";
		}else{
			echo '<td>－</td>'."\n";
		}
		if($calender_fixd_content[$i]["fri"] == 1){
			echo '<td>◯</td>'."\n";
		}else if($calender_fixd_content[$i]["fri"] == 2){
			echo '<td>△</td>'."\n";
		}else if($calender_fixd_content[$i]["fri"] == 3){
			echo '<td>☆</td>'."\n";
		}else{
			echo '<td>－</td>'."\n";
		}
		if($calender_fixd_content[$i]["sat"] == 1){
			echo '<td>◯</td>'."\n";
		}else if($calender_fixd_content[$i]["sat"] == 2){
			echo '<td>△</td>'."\n";
		}else if($calender_fixd_content[$i]["sat"] == 3){
			echo '<td>☆</td>'."\n";
		}else{
			echo '<td>－</td>'."\n";
		}
		if($calender_fixd_content[$i]["sun"] == 1){
			echo '<td>◯</td>'."\n";
		}else if($calender_fixd_content[$i]["sun"] == 2){
			echo '<td>△</td>'."\n";
		}else if($calender_fixd_content[$i]["sun"] == 3){
			echo '<td>☆</td>'."\n";
		}else{
			echo '<td>－</td>'."\n";
		}
		if($calender_fixd_content[$i]["holiday"] == 1){
			echo '<td>◯</td>'."\n";
		}else if($calender_fixd_content[$i]["holiday"] == 2){
			echo '<td>△</td>'."\n";
		}else if($calender_fixd_content[$i]["holiday"] == 3){
			echo '<td>☆</td>'."\n";
		}else{
			echo '<td>－</td>'."\n";
		}
		echo '</tr>'."\n";
	}
	echo '</tbody>'."\n";
	echo '</table>'."\n";
}

if($calender_fixd_text_content["text"]!= ""){
	echo '<div class="holiday">'."\n";
	echo '<p>休診日</p>'."\n";
	echo nl2br($calender_fixd_text_content["text"])."\n";
	echo '</div>'."\n";
}

echo '</section>'."\n";


if($enable == 1){

	echo '<section class="index_width cntWrap">'."\n";
	echo '<div class="calendar_images index_width">'."\n";
	echo '<h3>診療カレンダー</h3>'."\n";

	echo '<div class="swiper-container-wrapper index_width"><!-- Slider main container -->'."\n";
	echo '<div class="swiper-container swiper2"><!-- Additional required wrapper -->'."\n";
	echo '<div class="swiper-wrapper"><!-- Slides -->'."\n";

	for($t = 0;$t < 3;$t++){

		echo '<div class="swiper-slide">'."\n";
		echo '<div class="mini-calendar">'."\n";
		echo '<div class="calendar-head">'."\n";
		echo '<p class="calendar-year-month">'.$yearDisplay[$t].'年'.$monthDisplay[$t].'月</p>'."\n";
		echo '</div>'."\n";

		echo '<table>'."\n";
		echo '<thead>'."\n";
		echo '<tr>'."\n";
		echo '<th>月</th>'."\n";
		echo '<th>火</th>'."\n";
		echo '<th>水</th>'."\n";
		echo '<th>木</th>'."\n";
		echo '<th>金</th>'."\n";
		echo '<th class="calendar-sat">土</th>'."\n";
		echo '<th class="calendar-sun">日</th>'."\n";
		echo '</tr>'."\n";
		echo '</thead>'."\n";

		echo '<tbody>'."\n";
		//echo '<tr>'."\n";
		$cnt = 0;
		$roop = 0;
		foreach ($calendar[$t] as $key => $value){
			if ($cnt == 0){
				echo '<tr>'."\n";
			}
			if($value['day'] == ""){
				echo '<td class="calendar-day-number">'."\n";
				echo '<div class="calendar-labels"></div>'."\n";
				echo '</td>'."\n";
			}else{
				if($value['week'] == 6){
					echo '<td class="calendar-sat">'."\n";
				}else if($value['week'] == 0 || $value['week'] == 7){
					echo '<td class="calendar-holiday">'."\n";
				}else{
					echo '<td>'."\n";
				}
				echo '<i class="calendar-day-number">'.$value['day'].'</i>'."\n";
				echo '<div class="calendar-labels"><span class="calender-label-blue">'."\n";
				for($n = 0;$n < count($value["display"]);$n++){
					if($n > 0){
						echo " / ";
					}
					echo $value["display"][$n]["mark"];
				}
				echo '</span></div>'."\n";
				echo '</td>'."\n";
			}
			$cnt++;
			if ($cnt == 7){
				echo '</tr>'."\n";
				$cnt = 0;
				$roop++;
				if($roop == 5){
					break;
				}
			}
		}
		echo '</tbody>'."\n";

		echo '</table>'."\n";
		echo '</div>'."\n";
		echo '</div>'."\n";
		echo '<!--swiper-slide-->'."\n";

	}

	echo '</div>'."\n";
	echo '<!-- If we need navigation buttons -->'."\n";

	echo '<div class="swiper-button-prev button-prev02"></div>'."\n";

	echo '<div class="swiper-button-next button-prev02"></div>'."\n";
	echo '</div>'."\n";


	if($calender_fixd_detail_text_content["text"]!= ""){
		echo '<div class="holiday">'."\n";
		echo '<p>ご案内</p>'."\n";
		echo nl2br($calender_fixd_detail_text_content["text"])."\n";
		echo '</div>'."\n";
	}

	echo '</div>'."\n";

	echo '<!-- swiper-container-wrapper --></div>'."\n";

	echo '</section>'."\n";

}
?>