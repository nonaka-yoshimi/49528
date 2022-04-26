<?php
//診療日カレンダー契約
$enable = 1;//1:有効 0:無効

// アプリケーション共通クラス読込
require_once(dirname(__FILE__).'/../../ApplicationCommon/include.php');
require_once(dirname(__FILE__).'/../../CMSCommon/include.php');
require_once(dirname(__FILE__).'/Common/LayoutManagerCalendar.php');
require_once(dirname(__FILE__).'/DataAccess/CalendarFixedContent.php');//
require_once(dirname(__FILE__).'/DataAccess/CalendarFixedTextContent.php');//
require_once(dirname(__FILE__).'/DataAccess/CalendarFixedDetailTextContent.php');//
require_once(dirname(__FILE__).'/DataAccess/CalendarContent.php');//
require_once(dirname(__FILE__).'/DataAccess/CalendarHolidayContent.php');//
require_once(dirname(__FILE__).'/../../DataAccess/AddInfoSelect.php'); //選択肢クラス

$self = basename(__FILE__);				//自身のファイル名
$error = array();						//エラー情報格納用配列
$message = array();						//メッセージ情報格納用配列
$alert = array();						//アラート情報格納用配列
$redirect = "../../index.php";			//エラー時リダイレクト先
$restrict = array();					//表示・操作制限

$session = Session::get();

//セッション認証
$session->loginCheckAndRedirect("../../login.php",array("admin_flg" => 1));

//テーブル
$CalendarFixedContent			= new CalendarFixedContent();
$CalendarFixedTextContent 		= new CalendarFixedTextContent();
$CalendarFixedDetailTextContent = new CalendarFixedDetailTextContent();
$CalendarContent 				= new CalendarContent();
$CalendarHolidayContent			= new CalendarHolidayContent();

//選択肢クラス
$AddInfoSelect = new AddInfoSelect();


$type = isset($_POST["type"]) ? $_POST["type"] : "";

$text = isset($_POST["text"]) ? Util::encodeRequest($_POST["text"]) : "";

$id			= isset($_POST["id"]) ? $_POST["id"] : array();
$start 		= isset($_POST["start"]) ? $_POST["start"] : array();
$end 		= isset($_POST["end"]) ? $_POST["end"] : array();
$mon 		= isset($_POST["mon"]) ? $_POST["mon"] : array();
$tue 		= isset($_POST["tue"]) ? $_POST["tue"] : array();
$wed 		= isset($_POST["wed"]) ? $_POST["wed"] : array();
$thu 		= isset($_POST["thu"]) ? $_POST["thu"] : array();
$fri 		= isset($_POST["fri"]) ? $_POST["fri"] : array();
$sat 		= isset($_POST["sat"]) ? $_POST["sat"] : array();
$sun 		= isset($_POST["sun"]) ? $_POST["sun"] : array();
$holiday 	= isset($_POST["holiday"]) ? $_POST["holiday"] : array();


if($type == 1){

	//バリデート
	for($i = 0;$i <= count($id);$i++){

		$dispNo = $i + 1;
		//最終行
		if($i == count($id)){
			if($start[$i] != "" && $end[$i] != ""){
				//新規登録
				if(strlen($start[$i]) != 5){
					$error[] = "開始時刻".$dispNo."を正しく入力してください";
				}
				if(strlen($end[$i]) != 5){
					$error[] = "終了時刻".$dispNo."を正しく入力してください";
				}
				$del[$i] = 0;
			}else{
				$del[$i] = 1;
			}
		}else{
			if($start[$i] == "" && $end[$i] == ""){
				//削除
				$del[$i] = 1;
			}else{
				if(strlen($start[$i]) != 5){
					$error[] = "開始時刻".$dispNo."を正しく入力してください";
				}
				if(strlen($end[$i]) != 5){
					$error[] = "終了時刻".$dispNo."を正しく入力してください";
				}
				$del[$i] = 0;
			}
		}
		if($mon[$i] == ""){
			$error[] = "月曜日".$dispNo."を正しく入力してください";
		}
		if($tue[$i] == ""){
			$error[] = "火曜日".$dispNo."を正しく入力してください";
		}
		if($wed[$i] == ""){
			$error[] = "水曜日".$dispNo."を正しく入力してください";
		}
		if($thu[$i] == ""){
			$error[] = "木曜日".$dispNo."を正しく入力してください";
		}
		if($fri[$i] == ""){
			$error[] = "金曜日".$dispNo."を正しく入力してください";
		}
		if($sat[$i] == ""){
			$error[] = "土曜日".$dispNo."を正しく入力してください";
		}
		if($sun[$i] == ""){
			$error[] = "日曜日".$dispNo."を正しく入力してください";
		}
		if($holiday[$i] == ""){
			$error[] = "祝日".$dispNo."を正しく入力してください";
		}
	}

	if($error == array()){
		//削除
		for($i = 0;$i < count($id);$i++){
			$CalendarFixedContent->delete($id[$i]);
		}
		//登録
		for($i = 0;$i <= count($id);$i++){
			if($del[$i] == 0){
				$saveData = array();
				$saveData["start"] 		= $start[$i];
				$saveData["end"] 		= $end[$i];
				$saveData["mon"] 		= $mon[$i];
				$saveData["tue"] 		= $tue[$i];
				$saveData["wed"] 		= $wed[$i];
				$saveData["thu"] 		= $thu[$i];
				$saveData["fri"] 		= $fri[$i];
				$saveData["sat"] 		= $sat[$i];
				$saveData["sun"] 		= $sun[$i];
				$saveData["holiday"] 	= $holiday[$i];
				$CalendarFixedContent->insert($saveData);
			}
		}
		Location::redirect("/".Config::BASE_DIR_PATH."webadmin/addon/calendar/calendar_manager.php?info=true");
	}
}else if($type == 2){

	if($text == ""){
		$error[] = "休診日を入力してください。";
	}
	//保存処理
	if($error == array()){
		$saveData = array();
		$saveData["text"] = $text;
		$CalendarFixedTextContent->update(0,$saveData);
		Location::redirect("/".Config::BASE_DIR_PATH."webadmin/addon/calendar/calendar_manager.php?info=true");
	}

}else if($type == 3){

	$calendar = array();

	for($t = 0;$t < 3;$t++){

		//カレンダー表示
		$year = date('Y',strtotime(date('Y')."/".date('m')."/1 +".$t." month"));
		$month = date('n',strtotime(date('Y')."/".date('m')."/1 +".$t." month"));
		$yearDisplay[] = date('Y',strtotime(date('Y')."/".date('m')."/1 +".$t." month"));
		$monthDisplay[] = date('n',strtotime(date('Y')."/".date('m')."/1 +".$t." month"));

		// 月末日を取得
		$last_day = date('j', mktime(0, 0, 0, $month + 1, 0, $year));
		// 月末日までループ
		for ($i = 1; $i < $last_day + 1; $i++) {
			for($h = 0; $h < 10; $h++) {
				if(!isset($_POST["day_".$year."_".sprintf('%02d',$month)."_".sprintf('%02d',$i)."_".$h])){
					break;
				}

				$dayCheck[$year][$month][$i][$h] = isset($_POST["day_".$year."_".sprintf('%02d',$month)."_".sprintf('%02d',$i)."_".$h]) ? $_POST["day_".$year."_".sprintf('%02d',$month)."_".sprintf('%02d',$i)."_".$h]: "";
				if($dayCheck[$year][$month][$i][$h] == ""){
					$error[] = "個別情報（".$year."-".sprintf('%02d',$month)."-".sprintf('%02d',$i)."）を正しく入力してください。";
				}

				//SELECT
				//Debug::sqlCheckStart();
				$saveData = array();
				$saveData["date"] 		= $year."-".sprintf('%02d',$month)."-".sprintf('%02d',$i);
				$saveData["fixed"] 		= $h;
				$saveData["type"] 		= $dayCheck[$year][$month][$i][$h];
				$saveData["text"] 		= NULL;
				$saveData["register"] 	= date("Y-m-d H:i:s");

				//echo count($calender_content);
				if($CalendarContent->getListByParameters(array("date"=>$year."-".sprintf('%02d',$month)."-".sprintf('%02d',$i),"fixed"=>$h))){
					//UPDATE
					$CalendarContent->update(array("date"=>$year."-".sprintf('%02d',$month)."-".sprintf('%02d',$i),"fixed"=>$h),$saveData);
				}else{
					//INSERT
					$CalendarContent->insert($saveData);
				}
				//Debug::sqlCheckEnd();
			}
		}
	}
	if($error == array()){
		Location::redirect("/".Config::BASE_DIR_PATH."webadmin/addon/calendar/calendar_manager.php?info=true");
	}

}else if($type == 4){

	if($text == ""){
		//$error[] = "休診日を入力してください。";
	}
	//保存処理
	if($error == array()){
		$saveData = array();
		$saveData["text"] = $text;
		$CalendarFixedDetailTextContent->update(0,$saveData);
		Location::redirect("/".Config::BASE_DIR_PATH."webadmin/addon/calendar/calendar_manager.php?info=true");
	}

}



//出力用リスト取得
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


//レイアウトマネージャー
$LayoutManager = new LayoutManagerCalendar();
$LayoutManager->setTitle("診療日カレンダー編集");
$LayoutManager->setErrorList($error);
$LayoutManager->setMessageList($message);
$LayoutManager->setAlertList($alert);
$LayoutManager->header();

//print_r($_POST);
?>

<!-- メッセージ出力開始 -->
<?php echo $LayoutManager->alert(); 	//アラート出力 ?>
<?php echo $LayoutManager->error(); 	//エラー出力 ?>
<?php echo $LayoutManager->message(); 	//メッセージ出力 ?>
<!-- メッセージ出力終了 -->

<?php
if($_GET["info"] == "true"){
	echo '<ul class="message">'."\n";
	echo '<li>登録が完了しました</li>'."\n";
	echo '</ul>'."\n";
}
?>

<h3>基本情報</h3>
<form action="./calendar_manager.php" method="post">
<input type="hidden" name="type" value="1" />
<?php
if(isset($calender_fixd_content)){
	echo '<table class="list">'."\n";
	echo '<tbody>'."\n";
	echo '<tr>'."\n";
	echo '<th>診療時間</th>'."\n";
	echo '<th>月</th>'."\n";
	echo '<th>火</th>'."\n";
	echo '<th>水</th>'."\n";
	echo '<th>木</th>'."\n";
	echo '<th>金</th>'."\n";
	echo '<th>土</th>'."\n";
	echo '<th>日</th>'."\n";
	echo '<th>祝</th>'."\n";
	echo '</tr>'."\n";
	for($i = 0;$i < count($calender_fixd_content);$i++){
		echo '<tr>'."\n";
		echo '<input type="hidden" name="id[]" value="'.$calender_fixd_content[$i]["id"].'" />'."\n";
		echo '<td><input type="text" name="start[]" value="'.$calender_fixd_content[$i]["start"].'" style="width:60px;" />～<input type="text" name="end[]" value="'.$calender_fixd_content[$i]["end"].'" style="width:60px;" /></td>'."\n";
		echo '<td>'."\n";
		echo '<select name="mon[]">'."\n";
		echo '<option value="0">－</option>'."\n";
		if($calender_fixd_content[$i]["mon"] == 1){
			echo '<option value="1" selected="selected">〇</option>'."\n";
		}else{
			echo '<option value="1">◯</option>'."\n";
		}
		if($calender_fixd_content[$i]["mon"] == 2){
			echo '<option value="2" selected="selected">△</option>'."\n";
		}else{
			echo '<option value="2">△</option>'."\n";
		}
		if($calender_fixd_content[$i]["mon"] == 3){
			echo '<option value="3" selected="selected">☆</option>'."\n";
		}else{
			echo '<option value="3">☆</option>'."\n";
		}
		echo '</select>'."\n";
		echo '</td>'."\n";
		echo '<td>'."\n";
		echo '<select name="tue[]">'."\n";
		echo '<option value="0">－</option>'."\n";
		if($calender_fixd_content[$i]["tue"] == 1){
			echo '<option value="1" selected="selected">〇</option>'."\n";
		}else{
			echo '<option value="1">◯</option>'."\n";
		}
		if($calender_fixd_content[$i]["tue"] == 2){
			echo '<option value="2" selected="selected">△</option>'."\n";
		}else{
			echo '<option value="2">△</option>'."\n";
		}
		if($calender_fixd_content[$i]["tue"] == 3){
			echo '<option value="3" selected="selected">☆</option>'."\n";
		}else{
			echo '<option value="3">☆</option>'."\n";
		}
		echo '</select>'."\n";
		echo '</td>'."\n";
		echo '<td>'."\n";
		echo '<select name="wed[]">'."\n";
		echo '<option value="0">－</option>'."\n";
		if($calender_fixd_content[$i]["wed"] == 1){
			echo '<option value="1" selected="selected">〇</option>'."\n";
		}else{
			echo '<option value="1">◯</option>'."\n";
		}
		if($calender_fixd_content[$i]["wed"] == 2){
			echo '<option value="2" selected="selected">△</option>'."\n";
		}else{
			echo '<option value="2">△</option>'."\n";
		}
		if($calender_fixd_content[$i]["wed"] == 3){
			echo '<option value="3" selected="selected">☆</option>'."\n";
		}else{
			echo '<option value="3">☆</option>'."\n";
		}
		echo '</select>'."\n";
		echo '</td>'."\n";
		echo '<td>'."\n";
		echo '<select name="thu[]">'."\n";
		echo '<option value="0">－</option>'."\n";
		if($calender_fixd_content[$i]["thu"] == 1){
			echo '<option value="1" selected="selected">〇</option>'."\n";
		}else{
			echo '<option value="1">◯</option>'."\n";
		}
		if($calender_fixd_content[$i]["thu"] == 2){
			echo '<option value="2" selected="selected">△</option>'."\n";
		}else{
			echo '<option value="2">△</option>'."\n";
		}
		if($calender_fixd_content[$i]["thu"] == 3){
			echo '<option value="3" selected="selected">☆</option>'."\n";
		}else{
			echo '<option value="3">☆</option>'."\n";
		}
		echo '</select>'."\n";
		echo '</td>'."\n";
		echo '<td>'."\n";
		echo '<select name="fri[]">'."\n";
		echo '<option value="0">－</option>'."\n";
		if($calender_fixd_content[$i]["fri"] == 1){
			echo '<option value="1" selected="selected">〇</option>'."\n";
		}else{
			echo '<option value="1">◯</option>'."\n";
		}
		if($calender_fixd_content[$i]["fri"] == 2){
			echo '<option value="2" selected="selected">△</option>'."\n";
		}else{
			echo '<option value="2">△</option>'."\n";
		}
		if($calender_fixd_content[$i]["fri"] == 3){
			echo '<option value="3" selected="selected">☆</option>'."\n";
		}else{
			echo '<option value="3">☆</option>'."\n";
		}
		echo '</select>'."\n";
		echo '</td>'."\n";
		echo '<td>'."\n";
		echo '<select name="sat[]">'."\n";
		echo '<option value="0">－</option>'."\n";
		if($calender_fixd_content[$i]["sat"] == 1){
			echo '<option value="1" selected="selected">〇</option>'."\n";
		}else{
			echo '<option value="1">◯</option>'."\n";
		}
		if($calender_fixd_content[$i]["sat"] == 2){
			echo '<option value="2" selected="selected">△</option>'."\n";
		}else{
			echo '<option value="2">△</option>'."\n";
		}
		if($calender_fixd_content[$i]["sat"] == 3){
			echo '<option value="3" selected="selected">☆</option>'."\n";
		}else{
			echo '<option value="3">☆</option>'."\n";
		}
		echo '</select>'."\n";
		echo '</td>'."\n";
		echo '<td>'."\n";
		echo '<select name="sun[]">'."\n";
		echo '<option value="0">－</option>'."\n";
		if($calender_fixd_content[$i]["sun"] == 1){
			echo '<option value="1" selected="selected">〇</option>'."\n";
		}else{
			echo '<option value="1">◯</option>'."\n";
		}
		if($calender_fixd_content[$i]["sun"] == 2){
			echo '<option value="2" selected="selected">△</option>'."\n";
		}else{
			echo '<option value="2">△</option>'."\n";
		}
		if($calender_fixd_content[$i]["sun"] == 3){
			echo '<option value="3" selected="selected">☆</option>'."\n";
		}else{
			echo '<option value="3">☆</option>'."\n";
		}
		echo '</select>'."\n";
		echo '</td>'."\n";
		echo '<td>'."\n";
		echo '<select name="holiday[]">'."\n";
		echo '<option value="0">－</option>'."\n";
		if($calender_fixd_content[$i]["holiday"] == 1){
			echo '<option value="1" selected="selected">〇</option>'."\n";
		}else{
			echo '<option value="1">◯</option>'."\n";
		}
		if($calender_fixd_content[$i]["holiday"] == 2){
			echo '<option value="2" selected="selected">△</option>'."\n";
		}else{
			echo '<option value="2">△</option>'."\n";
		}
		if($calender_fixd_content[$i]["holiday"] == 3){
			echo '<option value="3" selected="selected">☆</option>'."\n";
		}else{
			echo '<option value="3">☆</option>'."\n";
		}
		echo '</select>'."\n";
		echo '</td>'."\n";
		echo '</tr>'."\n";
	}
	echo '<tr>'."\n";
	echo '<td><input type="text" name="start[]" value="" style="width:60px;" />～<input type="text" name="end[]" value="" style="width:60px;" /><br /></td>'."\n";
	echo '<td>'."\n";
	echo '<select name="mon[]">'."\n";
	echo '<option value="0">－</option>'."\n";
	echo '<option value="1">◯</option>'."\n";
	echo '<option value="2">△</option>'."\n";
	echo '<option value="3">☆</option>'."\n";
	echo '</select>'."\n";
	echo '</td>'."\n";
	echo '<td>'."\n";
	echo '<select name="tue[]">'."\n";
	echo '<option value="0">－</option>'."\n";
	echo '<option value="1">◯</option>'."\n";
	echo '<option value="2">△</option>'."\n";
	echo '<option value="3">☆</option>'."\n";
	echo '</select>'."\n";
	echo '</td>'."\n";
	echo '<td>'."\n";
	echo '<select name="wed[]">'."\n";
	echo '<option value="0">－</option>'."\n";
	echo '<option value="1">◯</option>'."\n";
	echo '<option value="2">△</option>'."\n";
	echo '<option value="3">☆</option>'."\n";
	echo '</select>'."\n";
	echo '</td>'."\n";
	echo '<td>'."\n";
	echo '<select name="thu[]">'."\n";
	echo '<option value="0">－</option>'."\n";
	echo '<option value="1">◯</option>'."\n";
	echo '<option value="2">△</option>'."\n";
	echo '<option value="3">☆</option>'."\n";
	echo '</select>'."\n";
	echo '</td>'."\n";
	echo '<td>'."\n";
	echo '<select name="fri[]">'."\n";
	echo '<option value="0">－</option>'."\n";
	echo '<option value="1">◯</option>'."\n";
	echo '<option value="2">△</option>'."\n";
	echo '<option value="3">☆</option>'."\n";
	echo '</select>'."\n";
	echo '</td>'."\n";
	echo '<td>'."\n";
	echo '<select name="sat[]">'."\n";
	echo '<option value="0">－</option>'."\n";
	echo '<option value="1">◯</option>'."\n";
	echo '<option value="2">△</option>'."\n";
	echo '<option value="3">☆</option>'."\n";
	echo '</select>'."\n";
	echo '</td>'."\n";
	echo '<td>'."\n";
	echo '<select name="sun[]">'."\n";
	echo '<option value="0">－</option>'."\n";
	echo '<option value="1">◯</option>'."\n";
	echo '<option value="2">△</option>'."\n";
	echo '<option value="3">☆</option>'."\n";
	echo '</select>'."\n";
	echo '</td>'."\n";
	echo '<td>'."\n";
	echo '<select name="holiday[]">'."\n";
	echo '<option value="0">－</option>'."\n";
	echo '<option value="1">◯</option>'."\n";
	echo '<option value="2">△</option>'."\n";
	echo '<option value="3">☆</option>'."\n";
	echo '</select>'."\n";
	echo '</td>'."\n";
	echo '</tr>'."\n";
	echo '</tbody>'."\n";
	echo '</table>'."\n";
}
?>
<input class="btn btn_small" type="submit" value="編集登録する" />
<p>※診療時間は 00:00 形式で入力ください。</p>
</form>

<h3>休診日</h3>
<form action="./calendar_manager.php" method="post">
<input type="hidden" name="type" value="2" />
<?php
echo '<textarea name="text">'.$calender_fixd_text_content["text"].'</textarea>'."\n";
?>
<br />
<input class="btn btn_small" type="submit" value="編集登録する" />
</form>

<h3>個別情報</h3>
<form action="./calendar_manager.php" method="post">
<input type="hidden" name="type" value="3" />
<?php
if($enable == 1){

	for($t = 0;$t < 3;$t++){

		echo '<p>'.$yearDisplay[$t].'年'.$monthDisplay[$t].'月</p>'."\n";

		echo '<table class="list">'."\n";
		echo '<thead>'."\n";
		echo '<tr>'."\n";
		echo '<th>月</th>'."\n";
		echo '<th>火</th>'."\n";
		echo '<th>水</th>'."\n";
		echo '<th>木</th>'."\n";
		echo '<th>金</th>'."\n";
		echo '<th>土</th>'."\n";
		echo '<th>日</th>'."\n";
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
				echo '<td>'."\n";
				echo '－'."\n";
				echo '</td>'."\n";
			}else{
				if($value['week'] == 6){
					echo '<td class="calendar-sat">'."\n";
				}else if($value['week'] == 0 || $value['week'] == 7){
					echo '<td class="calendar-holiday">'."\n";
				}else{
					echo '<td>'."\n";
				}
				echo $value['day'].'日<br />'."\n";
				for($n = 0;$n < count($value["display"]);$n++){
					if($n > 0){
						echo " / ";
					}
					//echo $value["display"][$n]["mark"];
					echo '<select name="day_'.$yearDisplay[$t].'_'.sprintf('%02d',$monthDisplay[$t]).'_'.sprintf('%02d',$value['day']).'_'.$n.'">'."\n";
					if($value["display"][$n]["mark"] == "－"){
						echo '<option value="0" selected="selected">－</option>'."\n";
					}else{
						echo '<option value="0">－</option>'."\n";
					}
					if($value["display"][$n]["mark"] == "◯"){
						echo '<option value="1" selected="selected">◯</option>'."\n";
					}else{
						echo '<option value="1">◯</option>'."\n";
					}
					if($value["display"][$n]["mark"] == "△"){
						echo '<option value="2" selected="selected">△</option>'."\n";
					}else{
						echo '<option value="2">△</option>'."\n";
					}
					if($value["display"][$n]["mark"] == "☆"){
						echo '<option value="3" selected="selected">☆</option>'."\n";
					}else{
						echo '<option value="3">☆</option>'."\n";
					}
					echo '</select>'."\n";
				}
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
	}
}
?>
<input class="btn btn_small" type="submit" value="編集登録する" />
</form>

<h3>個別情報</h3>
<form action="./calendar_manager.php" method="post">
<input type="hidden" name="type" value="4" />
<?php
echo '<textarea name="text">'.$calender_fixd_detail_text_content["text"].'</textarea>'."\n";
?>
<br />
<input class="btn btn_small" type="submit" value="編集登録する" />
</form>
<?php $LayoutManager->footer(); ?>