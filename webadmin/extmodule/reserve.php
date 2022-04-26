<?php
//アプリケーション共通クラス読込
require_once(dirname(__FILE__).'/../addon/reserve/Common/includeReserve.php');

//データアクセスクラス読込
require_once(dirname(__FILE__).'/../addon/reserve/DataAccess/ReserveUser.php');		//ユーザマスタ
require_once(dirname(__FILE__).'/../addon/reserve/DataAccess/Shop.php');			//店舗マスタ
require_once(dirname(__FILE__).'/../addon/reserve/DataAccess/Type.php');			//相談種別マスタ
require_once(dirname(__FILE__).'/../addon/reserve/DataAccess/Reserve.php');			//予約トランザクション
require_once(dirname(__FILE__).'/../DataAccess/ContentAccess.php');		//コンテンツマスタ

//リクエストを取得
//$action = isset($_POST["post"]["action"]) ? $_POST["post"]["action"] : "";												//実行アクション
$shop_id = isset($_POST["post"]["shop_id"]) ? $_POST["post"]["shop_id"] : "";												//店舗ID
$reserve_datetime = isset($_POST["post"]["reserve_datetime"]) ? $_POST["post"]["reserve_datetime"] : "";					//予約日時
$lastname = isset($_POST["post"]["lastname"]) ? Util::encodeRequest($_POST["post"]["lastname"]) : "";						//姓
$firstname = isset($_POST["post"]["firstname"]) ? Util::encodeRequest($_POST["post"]["firstname"]) : "";					//名
$lastname_kana = isset($_POST["post"]["lastname_kana"]) ? Util::encodeRequest($_POST["post"]["lastname_kana"]) : "";		//姓（カナ）
$firstname_kana = isset($_POST["post"]["firstname_kana"]) ? Util::encodeRequest($_POST["post"]["firstname_kana"]) : "";		//名（カナ）
$tel1 = isset($_POST["post"]["tel1"]) ? Util::encodeRequest($_POST["post"]["tel1"]) : "";									//電話番号1
$tel2 = isset($_POST["post"]["tel2"]) ? Util::encodeRequest($_POST["post"]["tel2"]) : "";									//電話番号2
$tel3 = isset($_POST["post"]["tel3"]) ? Util::encodeRequest($_POST["post"]["tel3"]) : "";									//電話番号3
$mail = isset($_POST["post"]["mail"]) ? Util::encodeRequest($_POST["post"]["mail"]) : "";									//メールアドレス
$type_id = isset($_POST["post"]["type_id"]) ? $_POST["post"]["type_id"] : "";												//相談種別ID
$other = isset($_POST["post"]["other"]) ? Util::encodeRequest($_POST["post"]["other"]) : "";								//自由入力欄
$self = $_POST["get"]["url"];																								//呼び出しページアドレス
if($other == ReserveConfig::FORM_DEFAULT_NAME){
	$other = "";
}
$param = explode(',',$_POST["param"]);
//Debug::arrayCheck($_POST);
//Debug::arrayCheck($param);
$action = $param[0];
$inputPage = $param[1];
$checkPage = $param[2];
$finishPage = $param[3];

//予約時間が設定されている場合は、分解する
if($reserve_datetime != ""){
	$year = substr($reserve_datetime,0,4);
	$month = substr($reserve_datetime,4,2);
	$day = substr($reserve_datetime,6,2);
	$hour = substr($reserve_datetime,8,2);
	$minute = substr($reserve_datetime,10,2);
	$week = date("w",mktime($hour,$minute,0,$month,$day,$year));
	$week = Util::getWeekJPShort($week);
}

//電話番号を結合する
$tel = $tel1."-".$tel2."-".$tel3;

$error = array();	//エラー一覧

//店舗一覧を取得する
$shop = new Shop();
$shopList = $shop->getShopList();

//相談種別一覧を取得する
$type = new type();
$typeList = $type->getTypeList();

$dateList = array();	//日付一覧

if($action == "check" || $action == "send")
{
	//選択された店舗情報を取得する
	$shop_name = "";
	if($shop_id != ""){
		$shopData = $shop->getDataByParameters(array("shop_id" => $shop_id));
		$shop_name = $shopData["shop_name"];
		$shop_tel = $shopData["tel"];
	}

	//選択された相談情報を取得する
	$type_name = "";
	if($type_id != ""){
		$typeData = $type->getDataByParameters(array("type_id" => $type_id));
		$type_name = $typeData["type_name"];
	}

	//ご希望のサロンチェック
	if(Util::IsNullOrEmpty($shop_id)){
		$error["shop"] = "ご希望の".ReserveConfig::SHOP_NAME."を選択してください。";
	}

	//ご予約希望日時チェック
	if(Util::IsNullOrEmpty($reserve_datetime)){
		$error["datetime"] = "ご予約希望日時を選択してください。";
	}elseif(!Common::checkReserveAllFacility($year, $month, $day, $hour, $minute, $shop_id, true)){
		$error["datetime"] = "この日程では予約することが出来ません。";
	}

	//お名前(漢字) 姓チェック
	if(Util::IsNullOrEmpty($lastname)){
		$error["lastname"] = "お名前(漢字)姓を入力してください。";
	}elseif(mb_strlen($lastname,Config::DEFAULT_ENCODE) > ReserveConfig::LASTNAME_MAX_CHAR_NUM){
		$error["lastname"] = "お名前(漢字)姓は".ReserveConfig::LASTNAME_MAX_CHAR_NUM."以内で入力してください。";
	}

	//お名前(漢字) 名チェック
	if(Util::IsNullOrEmpty($firstname)){
		$error["firstname"] = "お名前(漢字)名を入力してください。";
	}elseif(mb_strlen($firstname,Config::DEFAULT_ENCODE) > ReserveConfig::FIRSTNAME_MAX_CHAR_NUM){
		$error["firstname"] = "お名前(漢字)名は".ReserveConfig::FIRSTNAME_MAX_CHAR_NUM."以内で入力してください。";
	}

	//お名前(カナ) 姓チェック
	if(Util::IsNullOrEmpty($lastname_kana)){
		$error["lastname_kana"] = "お名前(カナ)姓を入力してください。";
	}elseif(mb_strlen($lastname_kana,Config::DEFAULT_ENCODE) > ReserveConfig::LASTNAME_KANA_MAX_CHAR_NUM){
		$error["lastname_kana"] = "お名前(カナ)姓は".ReserveConfig::LASTNAME_KANA_MAX_CHAR_NUM."以内で入力してください。";
	}elseif(!Util::checkStrType($lastname_kana, array("kana"))){
		$error["lastname_kana"] = "お名前(カナ)姓は全角カタカナで入力してください。";
	}

	//お名前(カナ) 名チェック
	if(Util::IsNullOrEmpty($firstname_kana)){
		$error["firstname_kana"] = "お名前(カナ)名を入力してください。";
	}elseif(mb_strlen($firstname_kana,Config::DEFAULT_ENCODE) > ReserveConfig::FIRSTNAME_KANA_MAX_CHAR_NUM){
		$error["firstname_kana"] = "お名前(カナ)名は".ReserveConfig::FIRSTNAME_KANA_MAX_CHAR_NUM."以内で入力してください。";
	}elseif(!Util::checkStrType($firstname_kana, array("kana"))){
		$error["firstname_kana"] = "お名前(カナ)名は全角カタカナで入力してください。";
	}

	//電話番号チェック
	if(Util::IsNullOrEmpty($tel1) || Util::IsNullOrEmpty($tel2) || Util::IsNullOrEmpty($tel3)){
		$error["tel"] = "電話番号を入力してください。";
	}else if(!Util::checkStrType($tel, array("num","-"))){	//電話番号チェック
		$error["tel"] = "電話番号は半角数字と、-(ハイフン)のみで入力して下さい。";
	}else if(mb_strlen($tel) < ReserveConfig::TEL_MIN_CHAR_NUM || mb_strlen($tel) > ReserveConfig::TEL_MAX_CHAR_NUM ){
		$error["tel"] = "電話番号は".ReserveConfig::TEL_MIN_CHAR_NUM."桁以上".ReserveConfig::TEL_MAX_CHAR_NUM."桁以内で入力してください。";
	}

	//メールアドレスチェック
	if(Util::IsNullOrEmpty($mail)){
		$error["mail"] = "メールアドレスを入力してください。";
	}else if(!Util::checkMailAddressFormat($mail)){				//メールフォーマットチェック
		$error["mail"] = "メールアドレスのフォーマットが不正です。";
	}else if(mb_strlen($mail) > ReserveConfig::MAIL_MAX_CHAR_NUM){
		$error["mail"] = "メールアドレスは".ReserveConfig::MAIL_MAX_CHAR_NUM."文字以内で入力してください。";
	}

	//ご相談内容チェック
	if(Util::IsNullOrEmpty($type_id)){
		$error["type"] = ReserveConfig::TYPE_NAME."を選択してください。";
	}

	//自由入力欄チェック
	if(mb_strlen($other,Config::DEFAULT_ENCODE) > ReserveConfig::FORM_OTHER_MAX){				//自由入力欄文字数チェック
		$error["other"] = "自由入力欄は".ReserveConfig::FORM_OTHER_MAX."文字以内で入力してください。";
	}
}

if(($action == "check" || $action == "back") && $shop_id != ""){
	//20140219 Web制御不具合修正
	//予約可能な日付一覧を取得する
	//$dateList = Common::getReserveAvailableListForMonth($shop_id);
	$dateList = Common::getReserveAvailableListForMonth($shop_id,true);
}

if($action == "send" && $error == array()){
	//予約登録処理
	DB::beginTransaction();

	$reserveData = array();
	$reserveData["lastname"] = $lastname;				//姓
	$reserveData["firstname"] = $firstname;				//名
	$reserveData["lastname_kana"] = $lastname_kana;		//姓（カナ）
	$reserveData["firstname_kana"] = $firstname_kana;	//名（カナ）
	$reserveData["tel"] = $tel;							//電話番号
	$reserveData["mail"] = $mail;						//メールアドレス
	$reserveData["type_id"] = $type_id;					//相談種別ID
	$reserveData["free_text"] = $other;					//自由入力欄
	$result = Common::insertreserve($shop_id, $year, $month, $day, $hour, $minute, $reserveData);	//共通処理：予約挿入

	//メール送信先処理
	if($result){
		$mail_list = array();		//メール送信先一覧
		$user = new ReserveUser();

		//予約管理者一覧のメールアドレス取得
		$userWhere = array();
		$userWhere["active_flg"] = SPConst::USER_ACTIVE;
		//$userWhere["admin_flg"] = SPConst::ADMIN_FLG_YES;
		$userWhere["admin_type"] = ReserveConfig::ADMIN_TYPE_SUPERVISOR;
		$supervisorList = $user->getListByParametersReserve($userWhere);
		foreach($supervisorList as $suervisor){
			if(!Util::IsNullOrEmpty($suervisor["mail"])){
				$mail_list[] = $suervisor["mail"];
			}
		}

		//サロン全体管理者のメールアドレス一覧取得
		$userWhere["admin_type"] = array(ReserveConfig::ADMIN_TYPE_SALON,ReserveConfig::ADMIN_TYPE_NORMAL);
		$userWhere["shop_id"] = $shop_id;
		$salonUserList = $user->getListByParametersReserve($userWhere);
		foreach($salonUserList as $salonUser){
			if(!Util::IsNullOrEmpty($salonUser["mail"])){
				$mail_list[] = $salonUser["mail"];
			}
		}

		//管理者向けメール送信処理実行
		if(count($mail_list) > 0){
			//メールテンプレートを読み込み
			$mail_list_str = "";
			$mail_body = file_get_contents("../addon/reserve/mail/reserve_mail.txt");
			$mail_body = Util::deleteBom($mail_body);

			$Mail = new Mail();
			$Mail->setFrom(ReserveConfig::RESERVE_MAIL_FROM);				//FROMアドレス
			$Mail->setReturnPath(ReserveConfig::RESERVE_MAIL_RETURN_PATH);	//return-path
			$Mail->setFromName(ReserveConfig::RESERVE_MAIL_FROM_NAME);		//送信者名
			$Mail->setSubject(ReserveConfig::RESERVE_MAIL_TITLE);			//タイトル
			$Mail->setBody($mail_body);										//本文
			foreach($mail_list as $mail_address){							//メールアドレス追加
				$Mail->addTo($mail_address);
				if($mail_list_str != ""){ $mail_list_str .= ","; }
				$mail_list_str .= $mail_address;
			}
			//文字列自動置換設定
			$datetime = $year."年".$month."月".$day."日（".$week."） ".$hour."時".$minute."分～";
			$url = Config::SITE_BASE_DOMAIN_SSL.Config::ADMIN_DIR_PATH."reserve/list.php";
			$Mail->setReplace(array("%%%shop_name%%%" => $shop_name,"%%%datetime%%%" => $datetime,"%%%lastname%%%" => $lastname,"%%%firstname%%%" => $firstname,"%%%lastname_kana%%%" => $lastname_kana,"%%%firstname_kana%%%" => $firstname_kana,"%%%tel%%%" => $tel,"%%%mail%%%" => $mail,"%%%type_name%%%" => $type_name,"%%%other%%%" => $other,"%%%url%%%" => $url));

			if($Mail->send()){	//メール送信実行
				Logger::info("管理者向け予約通知メールを送信しました。shop_id=".$shop_id.",date=".$datetime.",name=".$lastname." ".$firstname.",mail_list=".$mail_list_str);
			}else{
				Logger::error("管理者向け予約通知メール送信に失敗しました。shop_id=".$shop_id.",date=".$datetime.",name=".$lastname." ".$firstname.",mail_list=".$mail_list_str);
			}
		}

		//顧客向け自動返信メール送信実行
		$mail_body = file_get_contents("../addon/reserve/mail/thanks_mail.txt");
		$mail_body = Util::deleteBom($mail_body);

		$Mail = new Mail();
		$Mail->setTo($mail);
		$Mail->setFrom(ReserveConfig::THANKS_MAIL_FROM);
		$Mail->setReturnPath(ReserveConfig::THANKS_MAIL_RETURN_PATH);
		$Mail->setFromName(ReserveConfig::THANKS_MAIL_FROM_NAME);
		$Mail->setSubject(ReserveConfig::THANKS_MAIL_TITLE);
		$Mail->setBody($mail_body);
		$Mail->setReplace(array("%%%shop_name%%%" => $shop_name,"%%%shop_tel%%%" => $shop_tel,"%%%datetime%%%" => $datetime,"%%%lastname%%%" => $lastname,"%%%firstname%%%" => $firstname,"%%%lastname_kana%%%" => $lastname_kana,"%%%firstname_kana%%%" => $firstname_kana,"%%%tel%%%" => $tel,"%%%mail%%%" => $mail,"%%%type_name%%%" => $type_name,"%%%other%%%" => $other));
		if($Mail->send()){	//メール送信実行
			Logger::info("顧客向け予約通知メールを送信しました。shop_id=".$shop_id.",date=".$datetime.",name=".$lastname." ".$firstname.",mail=".$mail);
		}else{
			Logger::error("顧客向け予約通知メール送信に失敗しました。shop_id=".$shop_id.",date=".$datetime.",name=".$lastname." ".$firstname.",mail=".$mail);
		}
	}
	if($result){
		DB::commit();
		Logger::info("予約を受け付け情報を登録しました。shop_id=".$shop_id.",date=".$datetime.",name=".$lastname." ".$firstname);
		echo "[SPREDIRECT::/".Config::BASE_DIR_PATH.$self."::SPEND]";
		//header("Location: reserve.php?action=finish");
	}else{
		DB::rollBack();
		Logger::error("予約を受け付け情報の登録に失敗しました。shop_id=".$shop_id.",date=".$datetime.",name=".$lastname." ".$firstname);
		$error[] = "エラーが発生しました。";
	}
}

if($error != array()){
	$action = "back";
}

$content = new ContentAccess();
//メインコンテンツ描画処理
//確認画面
if($action == "check"){
	$pageData = $content->getDataByPrimaryKey($checkPage);
	$pageData = $pageData["content"];

	//置き換え処理 開始
	$pageData = str_replace("{{{shop_id}}}", $shop_id, $pageData);	//店舗id
	$pageData = str_replace("{{{reserve_datetime}}}", $reserve_datetime, $pageData);	//店舗id
	$pageData = str_replace("{{{lastname}}}", $lastname, $pageData);	//姓
	$pageData = str_replace("{{{firstname}}}", $firstname, $pageData);	//名
	$pageData = str_replace("{{{lastname_kana}}}", $lastname_kana, $pageData);	//姓（カナ）
	$pageData = str_replace("{{{firstname_kana}}}", $firstname_kana, $pageData);	//名（カナ）
	$pageData = str_replace("{{{tel}}}", $tel, $pageData);	//電話番号
	$pageData = str_replace("{{{tel1}}}", $tel1, $pageData);	//電話番号①
	$pageData = str_replace("{{{tel2}}}", $tel2, $pageData);	//電話番号②
	$pageData = str_replace("{{{tel3}}}", $tel3, $pageData);	//電話番号③
	$pageData = str_replace("{{{mail}}}", $mail, $pageData);	//メールアドレス
	$pageData = str_replace("{{{type_id}}}", $type_id, $pageData);	//種別
	$pageData = str_replace("{{{other}}}", $other, $pageData);	//その他入力欄
	$pageData = str_replace("{{{shop_name}}}", $shop_name, $pageData);	//店舗名
	$pageData = str_replace("{{{type_name}}}", $type_name, $pageData);	//種別名

	$datetimeValue = "";
	if($reserve_datetime != "") { $datetimeValue = $year."年".$month."月".$day."日（".$week."） ".$hour.":".$minute."～"; }
	$pageData = str_replace("{{{datetime}}}", $datetimeValue, $pageData);	//年月日書き換え

	//置き換え処理 終了


}else if($action == "finish"){
	$pageData = $content->getDataByPrimaryKey($finishPage);
	$pageData = $pageData["content"];
//入力画面
}else{
	$pageData = $content->getDataByPrimaryKey($inputPage);
	$pageData = $pageData["content"];

	//置き換え処理 開始

	//店舗一覧
	$shopValue = "";
	for($i=0;$i<count($shopList);$i++){
	if($shop_id == $shopList[$i]["shop_id"]){
			$selected = "checked=checked";
    	}else{
	$selected = "";
	}
		$shopValue .= '<input name="shop_id" type="radio" value="'.$shopList[$i]["shop_id"].'" '.$selected.' />'.htmlspecialchars($shopList[$i]["shop_name"]).'<br />'."\n";
	}
	$pageData = str_replace("{{{shop_select}}}", $shopValue, $pageData);

	//選択可能日付一覧
	$selectDayValue = "";
	for($i=0;$i<count($dateList);$i++){
	$value = Util::lPad4($dateList[$i]["year"]).Util::lPad2($dateList[$i]["month"]).Util::lPad2($dateList[$i]["day"]).Util::lPad2($dateList[$i]["hour"]).Util::lPad2($dateList[$i]["minute"]);
			if($value == $reserve_datetime){
			$selected = "selected=selected";
		}else{
			$selected = "";
			}
			$selectDayValue .= '<option value="'.$value.'" '.$selected.'>'.$dateList[$i]["month"].'月'.$dateList[$i]["day"].'日（'.$dateList[$i]["week"].'） '.$dateList[$i]["hour"].':'.Util::lPad2($dateList[$i]["minute"]).'～</option>'."\n";
	}
	$pageData = str_replace("{{{select_day}}}", $selectDayValue, $pageData);


	//名前関連
	$pageData = str_replace("{{{lastname}}}", $lastname , $pageData);				//姓
	$pageData = str_replace("{{{firstname}}}", $firstname , $pageData);				//名
	$pageData = str_replace("{{{lastname_kana}}}", $lastname_kana , $pageData);		//姓（カナ）
	$pageData = str_replace("{{{firstname_kana}}}", $firstname_kana , $pageData);	//名（カナ）

	//電話関連
	$pageData = str_replace("{{{tel1}}}", $tel1 , $pageData);						//電話番号①
	$pageData = str_replace("{{{tel2}}}", $tel2 , $pageData);						//電話番号②
	$pageData = str_replace("{{{tel3}}}", $tel3 , $pageData);						//電話番号③

	//メールアドレス
	$pageData = str_replace("{{{mail}}}", $mail , $pageData);						//メールアドレス

	//種別リスト
	$typeValue = "";
	for($i=0;$i<count($typeList);$i++){
		if($type_id == $typeList[$i]["type_id"]){
				$selected = "selected=selected";
	    	}else{
		$selected = "";
	}
		$typeValue .= '<option value="'.$typeList[$i]["type_id"].'" '.$selected.'>'.$typeList[$i]["type_name"].'</option>'."\n";
	}
	$pageData = str_replace("{{{type}}}", $typeValue , $pageData);						//メールアドレス

	//その他カラー
	if(!$other){$pageData = str_replace("{{{othercolor}}}", "color: #969696;" , $pageData);}

	if($other){$pageData = str_replace("{{{other}}}", $other, $pageData);}
	else{$pageData = str_replace("{{{other}}}", ReserveConfig::FORM_DEFAULT_NAME, $pageData);}

	//置き換え処理 終了
}

//共通置き換え処理 開始

//エラー置き換え
$errorValue = "";
if($error != array()){
	//全体置き換え
	foreach($error as $error_one){
		$errorValue .= '<span class="attention">&nbsp;&nbsp;※&nbsp;'.$error_one.'</span><br />'."\n";
	}
	$errorValue .= "<br />\n";
	$pageData = str_replace("{{{error}}}", $errorValue , $pageData);

	//個別置き換え
	if(isset($error["shop"])){$pageData = str_replace("{{{shop_error}}}", "<br />".$error["shop"] , $pageData);}								//店舗エラー置き換え
	if(isset($error["datetime"])){$pageData = str_replace("{{{datetime_error}}}", "<br />".$error["datetime"] , $pageData);}					//日付エラー置き換え
	if(isset($error["lastname"])){$pageData = str_replace("{{{lastname_error}}}", "<br />".$error["lastname"] , $pageData);}					//姓エラー置き換え
	if(isset($error["firstname"])){$pageData = str_replace("{{{firstname_error}}}", "<br />".$error["firstname"] , $pageData);}					//名エラー置き換え
	if(isset($error["lastname_kana"])){$pageData = str_replace("{{{lastname_kana_error}}}", "<br />".$error["lastname_kana"] , $pageData);}		//姓（カナ）エラー置き換え
	if(isset($error["firstname_kana"])){$pageData = str_replace("{{{firstname_kana_error}}}", "<br />".$error["firstname_kana"] , $pageData);}	//名（カナ）エラー置き換え
	if(isset($error["tel"])){$pageData = str_replace("{{{tel_error}}}", "<br />".$error["tel"] , $pageData);}									//電話エラー置き換え
	if(isset($error["mail"])){$pageData = str_replace("{{{mail_error}}}", "<br />".$error["mail"] , $pageData);}								//メールエラー置き換え
	if(isset($error["type"])){$pageData = str_replace("{{{type_error}}}", "<br />".$error["type"] , $pageData);}								//種別エラー置き換え
	if(isset($error["other"])){$pageData = str_replace("{{{other_error}}}", "<br />".$error["other"] , $pageData);}								//他入力欄エラー置き換え
}



//共通置き換え処理 終了

echo $pageData;

?>
<!-- メインコンテンツ表示枠 -->
<div id="wrapper_contents" style="width: 650px;">

<?php /* 後程{{{error}}}から書き換えますか！ if($action == "" || $action == "back"): ?>
  <?php
	if($error != array()){
		foreach($error as $error_one){
			echo '<span class="attention">&nbsp;&nbsp;※&nbsp;'.$error_one.'</span><br />';
		}
		echo '<br />';
	}
*/  ?>
<!-- お申し込みフォーム -->

<?php /* elseif($action == "check"): ?>

<img src="./img/add_subtitle.gif" class="subtitle" alt="お近くのコンシェルジュ・サロンでお待ちしております" />
<div id="wrapper_contents_sub" style="width: 620px; margin-left:30px;">

  <div class="txt_box2">
  </div>


<img src="./img/add_comment1.gif" class="comment" alt="ご入力内容をご確認いただき、よろしければ「この情報で予約する」ボタンを押してください" />


<form action="<?php echo $self;?>" method="POST" id="form1" name="form1">
<input type="hidden" name="action" value="" id="action" />
<input type="hidden" name="reserve_datetime" value="<?php echo htmlspecialchars($reserve_datetime); ?>" />
<input type="hidden" name="shop_id" value="<?php echo htmlspecialchars($shop_id); ?>" />
<input type="hidden" name="lastname" value="<?php echo htmlspecialchars($lastname); ?>" />
<input type="hidden" name="firstname" value="<?php echo htmlspecialchars($firstname); ?>" />
<input type="hidden" name="lastname_kana" value="<?php echo htmlspecialchars($lastname_kana); ?>" />
<input type="hidden" name="firstname_kana" value="<?php echo htmlspecialchars($firstname_kana); ?>" />
<input type="hidden" name="tel" value="<?php echo htmlspecialchars($tel); ?>" />
<input type="hidden" name="tel1" value="<?php echo htmlspecialchars($tel1); ?>" />
<input type="hidden" name="tel2" value="<?php echo htmlspecialchars($tel2); ?>" />
<input type="hidden" name="tel3" value="<?php echo htmlspecialchars($tel3); ?>" />
<input type="hidden" name="mail" value="<?php echo htmlspecialchars($mail); ?>" />
<input type="hidden" name="type_id" value="<?php echo htmlspecialchars($type_id); ?>" />
<input type="hidden" name="other" value="<?php echo htmlspecialchars($other); ?>" />
<table class="form" width="620" border="0" cellspacing="0" cellpadding="0" style="margin-top:20px;">
  <tr>
    <th style="width:160px;">ご希望のサロン</th>
    <td><?php echo htmlspecialchars($shop_name); ?>&nbsp;</td>
  </tr>
  <tr>
    <th>ご予約希望日時</th>
    <td><?php if($reserve_datetime != "") { echo $year."年".$month."月".$day."日（".$week."） ".$hour.":".$minute."～"; } ?>&nbsp;</td>
  </tr>
  <tr>
    <th>お名前（漢字）</th>
    <td><?php echo htmlspecialchars($lastname); ?>　<?php echo htmlspecialchars($firstname); ?>&nbsp;</td>
  </tr>
  <tr>
    <th>お名前（カナ）</th>
    <td><?php echo htmlspecialchars($lastname_kana); ?>　<?php echo htmlspecialchars($firstname_kana); ?>&nbsp;</td>
  </tr>
  <tr>
    <th>電話番号</th>
    <td><?php echo htmlspecialchars($tel); ?>&nbsp;</td>
  </tr>
  <tr>
    <th>メールアドレス</th>
    <td><?php echo htmlspecialchars($mail); ?>&nbsp;</td>
  </tr>
  <tr>
    <th>ご相談内容</th>
    <td><?php echo htmlspecialchars($type_name); ?>&nbsp;</td>
  </tr>
  <tr>
  	<th>自由入力欄</th>
  	<td><?php echo nl2br(htmlspecialchars($other));?></td>
  </tr>
</table>
</form>
<br /><br />

  <div id="form_bt_box">
    <div id="form_button2"><a href="#" onclick="javascript:document.getElementById('action').value='back';document.form1.submit();return false;">入力画面に戻る</a></div>
    <div id="form_button2_rig"><a href="#" onclick="javascript:document.getElementById('action').value='send';document.form1.submit();return false;">この情報で送信する</a></div>
  </div>

<?php endif; */?>

</div>
</div>
<br style="clear: both;" />

</body>
</html>