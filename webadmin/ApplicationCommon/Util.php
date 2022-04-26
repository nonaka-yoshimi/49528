<?php
require_once(dirname(__FILE__).'/../Framework/UtilBase.php');
/*
 説明：ユーティリティクラス
作成日：2013/05/19 TS谷
*/

/**
 * ユーティリティクラス
 * 各種便利機能を集めたクラス
 */
class Util extends UtilBase
{
	/**
	 * リクエストパラメータの値を拡張取得する処理<br>
	 * ・一度取得した値はセッション内に保存しておく機能がある。<br>
	 * ・指定キーワードがリクエストパラメータに含まれればリクエストパラメータの値を取得し、<br>
	 * 含まれなければ、セッション内の保存値を探し、存在する場合はセッション内の値を返却する。
	 * @param string $request_name リクエストキーワード
	 * @param string $session_name セッション名前空間
	 * @param string $default デフォルト値
	 * @return string 取得値
	 */
	static function globalReq($request_name,$session_name = 'default_savearea',$default = "")
	{
		if(isset($_REQUEST[$request_name])){
			$result = $_REQUEST[$request_name];
			$_SESSION[$session_name][$request_name] = $result;
		}else{
			if(isset($_SESSION[$session_name][$request_name])){
				$result = $_SESSION[$session_name][$request_name];
			}else{
				$result = $default;
			}
		}
		return $result;
	}

	/**
	 * globalReqパラメータに任意に値を設定する処理
	 * @param string $request_name リクエストキーワード
	 * @param string $value 設定する値
	 * @param string $session_name セッション名前空間
	 */
	static function setGlobalReq($request_name,$value,$session_name = 'default_savearea'){
		$_SESSION[$session_name][$request_name] = $value;
	}

	/**
	 * 拡張取得したリクエストを「名前空間」単位で削除する
	 * @param string $session_name セッション名前空間
	 */
	static function unsetGrobalReqAll($session_name = 'default_savearea'){
		unset($_SESSION[$session_name]);
	}

	/**
	 * 拡張取得したリクエストを「キーワード」「名前空間」単位で削除する
	 * @param string $request_name リクエストキーワード
	 * @param string $session_name セッション名前空間
	 */
	static function unsetGrobalReq($request_name,$session_name = 'default_savearea'){
		unset($_SESSION[$session_name][$request_name]);
	}

	/**
	 * 文字列がnull又は空欄又は空の配列(array())の場合、TRUEを返す
	 * @param string $param チェック対象文字列
	 * @return boolean
	 */
	static function IsNullOrEmpty($param)
	{
		if($param == null || $param == "" || $param == array())
		{
			return true;
		}
		return false;
	}

	/**
	 * パスワードにハッシュ処理を行い暗号化する
	 * @param string $param パスワード
	 * @return string ハッシュ済みパスワード
	 */
	static function makePasswordHashCode($param)
	{
		return hash('sha512',$param.Config::LOGIN_MAGIC_CODE);
	}

	/**
	 *
	 * @param string $mailaddress メールアドレス
	 * @param string $auth_code 認証コード
	 * @return string メールハッシュコード
	 */
	static function makeMailHashCode($mailaddress,$auth_code){
		$str = hash('sha512',$mailaddress.$auth_code);
		$str = base64_encode($str);
		$str = substr($str, 0,Config::MAIL_HASH_CODE_CHAR_NUM);
		return $str;
	}

	/**
	 * メールアドレスのフォーマットが正しいかチェックする
	 * @param string $mail メールアドレス
	 * @return boolean OK/NG
	 */
	static function checkMailAddressFormat($mail){
		//if (preg_match('/^[-+./w]+@[-a-z0-9]+(/.[-a-z0-9]+)*/.[a-z]{2,6}$/i', $mail)) {
		//if (preg_match('/^[a-zA-Z0-9_\.\-]+@[-a-z0-9]+(/.[-a-z0-9]+)*/.[a-z]{2,6}$/i', $mail)) {
		if (preg_match('/^[a-zA-Z0-9_\.\-]+?@[A-Za-z0-9_\.\-]+\.[a-z]{2,6}$/',$mail)) {
			return TRUE;
		} else {
			return FALSE;
		}
	}

	/**
	 * 文字の長さが指定された範囲内かチェックする
	 * @param string $str チェック対象文字列
	 * @param int $min 最小の長さ
	 * @param int $max 最大の長さ
	 * @param string $encode エンコード（任意：標準はConfigのデフォルトエンコードを使用)
	 * @return boolean OK/NG
	 */
	static function checkStrLength($str,$min,$max,$encode = ''){
		if($encode == '') {$encode = Config::DEFAULT_ENCODE; }
		$strlen = mb_strlen($str, $encode);
		if($strlen >= $min && $strlen <= $max){
			return TRUE;
		}else{
			return FALSE;
		}
	}

	/**
	 * 文字の種類が正しいかチェックする(UTF-8の場合のみ使用可能)
	 * @param string $str チェック対象文字列
	 * @param array $allow 許可文字種類リスト(半角数字:num 全角数字:numZ 半角英字(小文字):alpha 半角英字(大文字):alphaL 全角英字(小文字):alphaZ 全角英字(大文字):alphaLZ 半角記号:kigou 全角記号:kigouZ ひらがな:hira カタカタ:kana 漢字:kanji 半角カタカナ：kana_han 半角スペース:space 全角スペース:spaceZ 改行:return ハイフン:- アンダーバー:_)
	 * @return boolean
	 */
	static function checkStrType($str,$allow){
		if($allow == array()){
			return FALSE;
		}

		mb_regex_encoding('UTF-8');
		mb_internal_encoding('UTF-8');

		$pattern = "";
		$pattern_last = "";
		$hankaku_kana_flg = FALSE;
		foreach($allow as $allow_one){
			if($allow_one == 'num'){ $pattern .= '0-9'; }
			if($allow_one == 'alpha'){ $pattern .= 'a-z'; }
			if($allow_one == 'alphaL'){ $pattern .= 'A-Z'; }
			if($allow_one == 'kigou'){ $pattern .= '\x21-\x2f\x3a-\x40\x5b-\x60\x7b-\x7e'; }
			if($allow_one == '_'){ $pattern .= '_'; }
			if($allow_one == '-'){ $pattern_last .= '-'; }
			if($allow_one == 'hira'){ $pattern .= 'ぁ-んー'; }
			//if($allow_one == 'kana'){ $pattern .= 'ァ-ヶー'; }
			if($allow_one == 'kana'){ $pattern .= 'ア-ン゛゜ァ-ォャ-ョヴー'; }
			if($allow_one == 'kanji'){ $pattern .= '一-龠'; }
			if($allow_one == 'numZ'){ $pattern .= '０-９'; }
			if($allow_one == 'alphaZ'){ $pattern .= 'ａ-ｚ'; }
			if($allow_one == 'alphaLZ'){ $pattern .= 'Ａ-Ｚ'; }
			if($allow_one == 'space'){ $pattern .= ' '; }
			if($allow_one == 'spaceZ'){ $pattern .= '　'; }
			if($allow_one == 'return'){ $pattern .= '\r\n'; }
			if($allow_one == 'kigouZ'){ $pattern .= '"！”＃＄％＆’（）＝～｜‘｛＋＊｝＜＞？＿－＾￥＠「；：」、。・'; }
			if($allow_one == 'kana_han'){ $hankaku_kana_flg = TRUE; }
		}
		$pattern = $pattern.$pattern_last;
		if(preg_match('/^['.$pattern.']+$/u',$str)){
			if($hankaku_kana_flg){
				return TRUE;
			}else{
				//if(mb_ereg('[ｱ-ﾝ]',$str)){]
				if(preg_match("/(?:\xEF\xBD[\xA1-\xBF]|\xEF\xBE[\x80-\x9F])/",$str)){
					return FALSE;
				}else{
					return TRUE;
				}
			}
		}else{
			return FALSE;
		}
	}

	/**
	 * 携帯端末からのアクセスかを判定する
	 * @return boolean TRUE:携帯端末 FALSE:携帯端末以外
	 */
	static function IsMobile(){

		$useragents = array(
				'iPhone', // Apple iPhone
				'iPod', // Apple iPod touch
				'Android', // 1.5+ Android
				'dream', // Pre 1.5 Android
				'CUPCAKE', // 1.5+ Android
				'blackberry9500', // Storm
				'blackberry9530', // Storm
				'blackberry9520', // Storm v2
				'blackberry9550', // Storm v2
				'blackberry9800', // Torch
				'webOS', // Palm Pre Experimental
				'incognito', // Other iPhone browser
				'webmate' // Other iPhone browser
		);
		$pattern = '/'.implode('|', $useragents).'/i';
		return preg_match($pattern, $_SERVER['HTTP_USER_AGENT']);
	}

	/**
	 * 時間（時分）の前後関係をチェックする。
	 * @param int $start_hour 開始時間（時）
	 * @param int $start_minute 開始時間（分）
	 * @param int $end_hour 終了時間（時）
	 * @param int $end_minute 終了時間（分）
	 * @return boolean true/false
	 */
	static function checkTimeFromTo($start_hour,$start_minute,$end_hour,$end_minute)
	{
		$start_hour = intval($start_hour);
		$start_minute = intval($start_minute);
		$end_hour = intval($end_hour);
		$end_minute = intval($end_minute);

		if($start_hour > $end_hour)
		{
			return false;
		}

		if($start_hour == $end_hour)
		{
			if($start_minute > $end_minute)
			{
				return false;
			}
		}
		return true;
	}

	/**
	 * 日付のフォーマットがYYYY/MM/DDであるかチェックする。
	 * @param string $date
	 */
	static function checkDateFormat($date){
		if($date == null || $date == ""){
			return false;
		}
		$date_arr = explode("/",$date);
		if(count($date_arr) < 3){
			return false;
		}
		$year = ltrim($date_arr[0], "0");
		$month = ltrim($date_arr[1], "0");
		$day = ltrim($date_arr[2], "0");
		if(!is_numeric($year)){
			return false;
		}
		if(!is_numeric($month)){
			return false;
		}
		if(!is_numeric($day)){
			return false;
		}
		if(!checkdate($month,$day,$year)){
			return false;
		}
		$time = mktime(0,0,0,$month,$day,$year);
		if($time <= 0){
			return false;
		}
		return true;
	}

	/**
	 * POST/GETリクエストのエンコード変換処理を行う
	 * @param string 対象文字列
	 * @return string 変換後文字列
	 */
	static function encodeRequest($str){
		if(!is_array($str)){
			if(get_magic_quotes_gpc()){
				$str = stripcslashes($str); //エスケープ処理の出コード
			}
			$str = mb_convert_encoding($str, Config::DEFAULT_ENCODE,Config::DEFAULT_ENCODE_FROM); //エンコードの変換
		}else{
			foreach($str as $key => $value){
				$str[$key] = self::encodeRequest($value);
			}
		}
		return $str;
	}

	/**
	 * HTMLで入力された値をPHPで使用可能な形式に変換
	 * @param 入力値
	 * @param Stripslashesをかけるか設定（boolean)
	 * @param MbConvertEncodingをかけるか設定(boolean)
	 */
	static function convHtmlValToPHPVal($value,$isStripslashes = true,$isMbConvertEncoding = true){
        if(null == $value) return null;
        if(is_array($value)) return $value;

        if($isStripslashes){
            $value = stripslashes($value);
        }

        if($isMbConvertEncoding){
            $value = mb_convert_encoding($value,'UTF-8','UTF-8,EUC-JP,SJIS');
        }

        return $value;
	}

	/**
	 * MYSQLのDATETIME型をPHPのSTRING型に変換する
	 * @param unknown_type $value
	 */
	static function convMySqlDateTimeToString($value,$format = "Y-m-d"){
        if(null == $value) return null;

        if(!strtotime($value)){
        	return "";
        }

        return date($format,strtotime($value));
	}

	/**
	 * 8桁の数値(文字列)をデータベースのDATE型に変換する
	 * @param int/string $value 8桁の数値(文字列)
	 * @return string データベースDate型
	 */
	static function convDate8ToDBDate($value){
		if(!is_numeric($value) || strlen($value) != 8){
			return false;
		}

		$y = intval(substr($value,0,4));
		$m = intval(substr($value,4,2));
		$d = intval(substr($value,6,2));

		if($y < 0 || $y > 9999){ return false; }
		if($m < 0 || $m > 12){ return false; }
		if($d < 0 || $d > date("t",mktime(0,0,0,$m,1,$y))){
			return false;
		}

		return Util::lPad4($y)."-".Util::lPad2($m)."-".Util::lPad2($d);
	}

	/**
	 * 年/月/日をデータベースのDATETIME型に変換する
	 * @param int/string $y 年
	 * @param int/string $m 月
	 * @param int/string $d 日
	 * @return string データベースDateTime型
	 */
	static function convYmdToMySqlDateTime($y,$m,$d){

		if(!is_numeric($y) || !is_numeric($m) || !is_numeric($d)){ return false; }

		$y = intval($y);
		$m = intval($m);
		$d = intval($d);

		if($y < 0 || $y > 9999){ return false; }
		if($m < 0 || $m > 12){ return false; }
		if($d < 0 || $d > date("t",mktime(0,0,0,$m,1,$y))){
			return false;
		}

		$str = date("Y-m-d H:i:s",mktime(0,0,0,$m,$d,$y));

		return $str;
	}

	/**
	 * 年/月/日をデータベースのDATE型に変換する
	 * @param int/string $y 年
	 * @param int/string $m 月
	 * @param int/string $d 日
	 * @return string データベースDate型
	 */
	static function convYmdToMySqlDate($y,$m,$d){

		if(!is_numeric($y) || !is_numeric($m) || !is_numeric($d)){ return false; }

		$y = intval($y);
		$m = intval($m);
		$d = intval($d);

		if($y < 0 || $y > 9999){ return false; }
		if($m < 0 || $m > 12){ return false; }
		if($d < 0 || $d > date("t",mktime(0,0,0,$m,1,$y))){
			return false;
		}

		return Util::lPad4($y)."-".Util::lPad2($m)."-".Util::lPad2($d);
	}

	/**
	 * 4文字の時分を、任意のフォーマットに変換(デフォルトH:i)
	 * @param string $value 数字4文字の時間
	 * @param string $format フォーマット（デフォルトH:i)
	 * @return string	フォーマットに合わせ整形した時分
	 */
	static function convTime4ToString($value,$format = "H:i"){
		$h = intval(substr($value,0,2));
		$m = intval(substr($value,2,2));
		$mk = mktime($h,$m);
		return date($format,$mk);
	}

	/**
	 * 数字4文字の時間（9時30分なら0900)を00:00からの分数に変換する
	 * @param int $time4 数字4文字の時分
	 * @return int 00:00からの分数
	 */
	static function convTime4ToMinute($time4){
		return intval(substr($time4,0,2)) * 60 + intval(substr($time4,2,2));
	}

	/**
	 * 00:00からの分数から数字4文字の時間（9時30分なら0900)に変換する
	 * @param int $minute 00:00からの分数
	 * @return int 数字4文字の時間
	 */
	static function convMinuteToTime4($minute){
		$end_hour = Util::lPad2(intval($minute / 60));
		$end_minute = Util::lPad2($minute % 60);
		return $end_hour.$end_minute;
	}

	/**
	 * 入力された日付(Y/m/d)及び時間、分をtimestampに変換する
	 * @param string $date 日付(Y/m/d)
	 * @param string $hour 時
	 * @param string $minute 分
	 * @param string $second 秒
	 */
	static function convInputDateTimeToTimestamp($date,$hour = 0,$minute = 0,$second = 0){
		if($date == ""){
			return null;
		}
		$date_arr = explode("/",$date);
		if(count($date_arr) < 3){
			return null;
		}
		$year = intval($date_arr[0]);
		$month = intval($date_arr[1]);
		$day = intval($date_arr[2]);

		if($hour == ""){
			$hour = "0";
		}
		if($minute == ""){
			$minute = "0";
		}
		if($second == ""){
			$second = "0";
		}
		$hour = intval($hour);
		$minute = intval($minute);
		$second = intval($second);

		//存在しない日付の場合
		if(!checkdate($month,$day,$year)){
			return null;
		}

		//存在しない時間の場合
		if($hour < 0 || $hour > 23){
			return null;
		}

		//存在しない分の場合
		if($minute < 0 || $minute > 59){
			return null;
		}

		//存在しない秒の場合
		if($second < 0 || $second > 59){
			return null;
		}
		return mktime($hour,$minute,$second,$month,$day,$year);
	}

	/**
	 * 8桁日付を1日進めた値を取得する
	 * @param string $date 8桁日付
	 * @return boolean|string 8桁日付（1日後)
	 */
	static function getNextDate8($date){
		if(!is_numeric($date) || strlen($date) != 8){
			return false;
		}

		$y = intval(substr($date,0,4));
		$m = intval(substr($date,4,2));
		$d = intval(substr($date,6,2));

		//該当月の最終月を取得する
		$last_day = date("t",mktime(0,0,0,$m,$d,$y));

		$dd = $d + 1; //1日進める
		$yy = $y;
		$mm = $m;
		if($dd > $last_day){ //月繰上げ
			$dd = 1;
			$mm = $mm + 1;
			if($mm > 12){ //年繰り上げ
				$mm = 1;
				$yy = $yy + 1;
			}
		}
		return Util::lPad4($yy).Util::lPad2($mm).Util::lPad2($dd);
	}

	/**
	 * 8桁日付を1日遡った値を取得する
	 * @param string $date 8桁日付
	 * @return boolean|string 8桁日付（1日前)
	 */
	static function getPrevDate8($date){
		if(!is_numeric($date) || strlen($date) != 8){
			return false;
		}

		$y = intval(substr($date,0,4));
		$m = intval(substr($date,4,2));
		$d = intval(substr($date,6,2));

		//該当月の最終月を取得する
		$last_day = date("t",mktime(0,0,0,$m,$d,$y));

		$dd = $d - 1; //1日進める
		$yy = $y;
		$mm = $m;
		if($dd < 1){ //月繰下げ
			$mm = $mm - 1;
			if($mm < 1){ //年繰下げ
				$mm = 12;
				$yy = $yy - 1;
			}
			$dd = date("t",mktime(0,0,0,$mm,1,$yy));
		}

		return Util::lPad4($yy).Util::lPad2($mm).Util::lPad2($dd);
	}


	/**
	 * STR_PAD($value,2,"0",STR_PAD_LEFT)をかける
	 * @param string $value
	 */
	static function lPad2($value){
	    return STR_PAD($value,2,"0",STR_PAD_LEFT);
	}

	/**
	 * STR_PAD($value,4,"0",STR_PAD_LEFT)をかける
	 * @param string $value
	 */
	static function lPad4($value){
		return STR_PAD($value,4,"0",STR_PAD_LEFT);
	}

	/**
	 * 引数で指定したActionがActionListに含まれるかチェック
	 * @param string アクション
	 * @param array アクションリスト
	 * @return true=含まれる false=含まれない
	 */
	static function existsAction($action,$actionList){

	    if(array_keys($actionList,$action) == null ){
	        return false;
	    }else{
	        return true;
	    }

	}

	/**
	 * 日付から年を取得する
	 * @param string Date型,DateTime型
	 * @return string 年
	 */
	static function getYearFromDate($date){
		$time = strtotime($date);
		$y = intval(date("Y", $time));
		return $y;
	}

	/**
	 * 日付から月を取得する
	 * @param string Date型,DateTime型
	 * @return string 月
	 */
	static function getMonthFromDate($date){
		$time = strtotime($date);
		$m = intval(date("m", $time));
		return $m;
	}

	/**
	 * 日付から日を取得する
	 * @param string Date型,DateTime型
	 * @return string 日
	 */
	static function getDayFromDate($date){
		$time = strtotime($date);
		$d = intval(date("d", $time));
		return $d;
	}

	/**
	 * 日付から時を取得する
	 * @param string DateTime型
	 * @return string 時
	 */
	static function getHourFromDate($date){
		$time = strtotime($date);
		$h = intval(date("H", $time));
		return $h;
	}

	/**
	 * 日付から分を取得する
	 * @param string DateTime型
	 * @return string 分
	 */
	static function getMinuteFromDate($date){
		$time = strtotime($date);
		$i = date("i", $time);
		return $i;
	}

	/**
	 * 日付から秒を取得する
	 * @param string DateTime型
	 * @return string 秒
	 */
	static function getSecondFromDate($date){
		$time = strtotime($date);
		$s = date("s", $time);
		return $i;
	}


	/**
	 * 日付から曜日を取得する
	 * @param string Date型,DateTime型
	 * @return string 日本語曜日表記（1文字）
	 */
    static function getWeekFromDate($date){
        $week = array("日", "月", "火", "水", "木", "金", "土");
        $time = strtotime($date);
        $w = date("w", $time);

        return $week[$w];
    }

    /**
     * 曜日を日本語短縮表記で取得する<br>
     * 0:日 1:月 2:火 3:水 4:木 5:金 6:土
     * @param int $week 曜日番号[0～6]
     * @return string 曜日表記（1文字）
     */
    static function getWeekJPShort($week){
    	if(!is_numeric($week) || $week < 0 || $week > 6){
    		return "";
    	}

    	$weekarr = array("日", "月", "火", "水", "木", "金", "土");
    	return $weekarr[$week];
    }

    /**
     * UTF8のBOMを削除する
     * @param string UTF8テキスト
     * @return string BOMを除去したテキスト
     */
    static function deleteBom($str){
    	if (($str == NULL) || (mb_strlen($str) == 0)) {
    		return $str;
    	}
    	if (ord($str{0}) == 0xef && ord($str{1}) == 0xbb && ord($str{2}) == 0xbf){
    		$str = substr($str, 3);
    	}
    	return $str;
    }

    /**
     * パスワードを隠して表示する(例：***)
     * @param string $password 変換元パスワード
     * @param string $mark 変換用文字(デフォルトは*)
     * @return string 変換後パスワード
     */
    static function passwordHidden($password,$mark = "*"){
    	$num = strlen($password);
    	$str = "";
    	for($i=0;$i<$num;$i++){
    		$str .= $mark;
    	}
    	return $str;
    }

    /**
     * ファイル名から拡張子部分を取得する
     * @param string $filename ファイル名
     * @return string 拡張子（.は含まない)
     */
    static function getExtensionFromFileName($filename){
    	if($filename == null || $filename == ""){
    		return false;
    	}

    	//ファイル名分割
    	$filename_arr = explode(".",$filename);

    	if(count($filename_arr) < 2){ return false; }

    	return $filename_arr[count($filename_arr) - 1];
    }

    /**
     * データリスト(2次元配列)からカラム名を指定して1次元配列を取り出す
     * @param array $list 2次元配列
     * @param sring $name カラム名
     * @return array 1次元配列
     */
    static function getArrayByDataListAndName($list,$name){
    	$result = array();
    	for($i=0;$i<count($list);$i++){
    		if(isset($list[$i][$name])){
    			$result[] = $list[$i][$name];
    		}
    	}
    	return $result;
    }


    /**
     * データリスト(2次元配列)からキーカラム名、値カラム名を指定して連想配列を取り出す
     * @param array $array 配列
     * @param string $key_column キーカラム名
     * @param string $value_column 値カラム名
     * @return array 連想配列
     */
    static function getAssocFromMultiArrayByKeyValue($array,$key_column,$value_column){
    	$result = array();
    	foreach($array as $data){
    		$result[$data[$key_column]] = $data[$value_column];
    	}
    	return $result;
    }

    /**
     * データリスト(2次元配列)からカラム名を指定してユニークなリスト配列を取り出す(重複データは2回目以降のデータを無視する)
     * @param array $list 2次元配列
     * @param sring $name カラム名
     * @return array 2次元配列
     */
    static function getUniqueDataByDataListAndName($list,$name){
    	//重複排除
    	$resultList = array();
    	$mem = array();
    	for($i=0;$i<count($list);$i++){
    		if(!in_array($list[$i][$name],$mem)){
    			$resultList[] = $list[$i];
    			$mem[] = $list[$i][$name];
    		}
    	}
    	return $resultList;
    }

    /**
     * データリスト(2次元配列)から特定カラムをキーとした連想配列+配列の辞書を作成する
     * @param unknown $array
     * @param unknown $column
     * @return Ambigous <multitype:, unknown>
     */
    static function getDicArrayByIndexColumn($array,$column){
    	$result = array();
    	for($i=0;$i<count($array);$i++){
    		$result[$array[$i][$column]][] = $array[$i];
    	}
    	return $result;
    }

    /**
     * データリスト(2次元配列)から特定カラムをキーとした連想配列の辞書を作成する
     * @param unknown $array
     * @param unknown $column
     * @return Ambigous <multitype:, unknown>
     */
    static function getDicByIndexColumn($array,$column){
    	$result = array();
    	for($i=0;$i<count($array);$i++){
    		$result[$array[$i][$column]] = $array[$i];
    	}
    	return $result;
    }


    /**
     * 二次元配列を(AND)条件検索する
     * @param array $search 検索条件（連想配列）
     * @param array $array 検索対象配列
     * @return array 検索後配列
     */
	static function array_search_multi($search,$array){
		$result = array();
		for($i=0;$i<count($array);$i++){
			$check = true;
			foreach($search as $key => $value){
				if(is_array($value)){
					$check2 = false;
					foreach($value as $key2 => $value2){
						if(is_array($value2) || $array[$i][$key] == $value2){
							$check2 = true;
						}
					}
					$check = $check2;
				}else if(!is_array($value) && $array[$i][$key] != $value){
					$check = false;
				}
			}
			if($check){
				$result[] = $array[$i];
			}
		}
		return $result;
	}

	/**
	 * 二次元配列内のデータ存在チェックを行う
	 * @param array $search 検索条件（連想配列）
	 * @param array $array 検索対象配列
	 * @return boolean true|false
	 */
	static function array_exist_multi($search,$array){
		$result = false;
		for($i=0;$i<count($array);$i++){
			$check = true;
			foreach($search as $key => $value){
				if(is_array($value)){
					$check2 = false;
					foreach($value as $key2 => $value2){
						if(is_array($value2) || $array[$i][$key] == $value2){
							$check2 = true;
						}
					}
					$check = $check2;
				}else if(!is_array($value) && $array[$i][$key] != $value){
					$check = false;
				}
			}
			if($check){
				$result = true;
				break;
			}
		}
		return $result;
	}

	/**
	 * 指定カラムを対象に、配列１に存在するが、配列２に存在しないデータ一覧を検索し抽出する
	 * @param array $array1 配列１
	 * @param array $array2 配列２
	 * @param array $columns 検索カラム一覧
	 * @return multitype:unknown 配列１-配列２結果一覧
	 */
	static function array_def_multi($array1,$array2,$columns){
		$result = array();
		foreach($array1 as $key1 => $value1){
			$check = false;
			foreach($array2 as $key2 => $value2){
				$check2 = true;
				foreach($columns as $column){
					if($value1[$column] != $value2[$column]){
						$check2 = false;
						break;
					}
				}
				if($check2){
					$check = true;
					break;
				}
			}
			if(!$check){
				$result[] = $value1;
			}
		}
		return $result;
	}

	static function encodeHTMLBasePath($content){
		if(Config::BASE_DIR_PATH == ""){
			return $content;
		}else{
			$base = str_replace("/", "\/", Config::BASE_DIR_PATH);
			$base = str_replace("-", "\-", $base);
		}
		//変換
		$pattern = '/((href|src|ref|action|data\-anystretch)=[\"|\']\/)([^\/])([^\"|^\']*[\"|\'])/i';
		$pattern2 = '/^'.$base.'/i';
		preg_match_all($pattern, $content , $match);
		if($match[0]){
			//Debug::arrayCheck($match);
			for($i=0;$i<count($match[0]);$i++){
				if(!preg_match($pattern2,$match[3][$i].$match[4][$i])){
					$str = $match[1][$i].Config::BASE_DIR_PATH.$match[3][$i].$match[4][$i];
					$content = str_replace($match[0][$i], $str, $content);
				}
			}
		}

		return $content;
	}

	static function encodeURLBasePath($content){
		if(Config::BASE_DIR_PATH == ""){
			return $content;
		}else{
			$base = str_replace("/", "\/", Config::BASE_DIR_PATH);
		}
		//変換
		$pattern = '/^(\/)([\S\s]*)/i';
		$pattern2 = '/^'.$base.'\//i';
		preg_match_all($pattern, $content , $match);
		if($match[0]){
			for($i=0;$i<count($match[0]);$i++){
				if(!preg_match($pattern2,$match[2][$i])){
					$str = $match[1][$i].Config::BASE_DIR_PATH.$match[2][$i];
					$content = str_replace($match[0][$i], $str, $content);
				}
			}
		}

		return $content;
	}

	static function decodeHTMLBasePath($content){
		if(Config::BASE_DIR_PATH == ""){
			return $content;
		}else{
			$base = str_replace("/", "\/", Config::BASE_DIR_PATH);
		}
		//変換
		$pattern = '/((href|src|ref|action|data\-anystretch)=[\"|\']\/)([^\/])('.$base.'([^\"|^\']*[\"|\']))/i';
		preg_match_all($pattern, $content , $match);
		if($match[0]){
			//Debug::arrayCheck($match);
			for($i=0;$i<count($match[0]);$i++){
				$str = $match[1][$i].$match[3][$i].$match[4][$i];
				$content = str_replace($match[0][$i], $str, $content);
			}
		}

		return $content;
	}

	static function decodeURLBasePath($content){
		if(Config::BASE_DIR_PATH == ""){
			return $content;
		}else{
			$base = str_replace("/", "\/", Config::BASE_DIR_PATH);
		}
		//変換
		$pattern = '/^(\/)('.$base.'([\S\s]*))/i';
		preg_match_all($pattern, $content , $match);
		if($match[0]){
			//Debug::arrayCheck($match);
			for($i=0;$i<count($match[0]);$i++){
				$str = $match[1][$i].$match[3][$i];
				$content = str_replace($match[0][$i], $str, $content);
			}
		}

		return $content;
	}

	static function deployPhysicalFile($filepath,$content){
		$paths = explode("/", $filepath);

		// 経路ディレクトリ作成
		$pathstr = "";
		for($i=0;$i<count($paths);$i++){
			if($i > 0){
				$pathstr .= "/";
			}
			$pathstr .= $paths[$i];
			if(file_exists($pathstr)){
				// 既にディレクトリあり
			}else{
				if($i<count($paths)-1){
					mkdir($pathstr, 0777);
					chmod($pathstr, 0777);
				}
			}
		}

		// ファイル作成
		$fh = fopen($filepath, 'w+');
		fwrite($fh, $content);
		fclose($fh);
		chmod();
	}

	static function deletePhysicalFile($basepath,$filepath){
		// ファイル削除
		unlink($basepath.$filepath);

		// 経路ディレクトリが空の場合削除
		$path_arr = array();
		$paths = explode("/", $filepath);
		$pathstr = "";
		for($i=0;$i<count($paths)-1;$i++){
			if($i > 0){
				$pathstr .= "/";
			}
			$pathstr .= $paths[$i];
			$path_arr[] = $pathstr;
		}
		$path_arr = array_reverse($path_arr);

		for($i=0;$i<count($path_arr);$i++){
			$path = $path_arr[$i];
			if(file_exists($basepath.$path) && is_dir($basepath.$path)){
				$filelist = scandir($basepath.$path);
				$cnt = 0;
				for($ii=0;$ii<count($filelist);$ii++){
					if(!$filelist[$ii] == "." && !$filelist[$ii] == ".."){
						$cnt++;
					}
				}
				if($cnt == 0){
					rmdir($basepath.$path);
				}
			}
		}
	}



	// 画像の方向を正す
	static function orientationFixedImage($output,$input){
		$image = ImageCreateFromJPEG($input);
		$exif_datas = @exif_read_data($input);
		if(isset($exif_datas['Orientation'])){
			$orientation = $exif_datas['Orientation'];

			if($image){
				// 未定義
				if($orientation == 0){
					// 通常
				}else if($orientation == 1){
					// 左右反転
				}else if($orientation == 2){
					$image = Util::image_flop($image);
					// 180°回転
				}else if($orientation == 3){
					$image = Util::image_rotate($image,180, 0);
					// 上下反転
				}else if($orientation == 4){
					$image = Util::image_Flip($image);
					// 反時計回りに90°回転 上下反転
				}else if($orientation == 5){
					$image = Util::image_rotate($image,270, 0);
					$image = Util::image_flip($image);
					// 時計回りに90°回転
				}else if($orientation == 6){
					//$image = image_rotate($image,90, 0);
					$image = Util::image_rotate($image,270, 0);
					// 時計回りに90°回転 上下反転
				}else if($orientation == 7){
					$image = Util::image_rotate($image,90, 0);
					$image = image_flip($image);
					// 反時計回りに90°回転
				}else if($orientation == 8){
					$image = Util::image_rotate($image,90, 0);
				}
			}
		}
		// 画像の書き出し
		ImageJPEG($image ,$output);
		return false;
	}
	// 画像の左右反転
	static function image_flop($image){
		// 画像の幅を取得
		echo $w = imagesx($image);
		// 画像の高さを取得
		echo $h = imagesy($image);
		// 変換後の画像の生成（元の画像と同じサイズ）
		$destImage = @imagecreatetruecolor($w,$h);
		// 逆側から色を取得
		for($i=($w-1);$i>=0;$i--){
			for($j=0;$j<$h;$j++){
				$color_index = imagecolorat($image,$i,$j);
				$colors = imagecolorsforindex($image,$color_index);
				imagesetpixel($destImage,abs($i-$w+1),$j,imagecolorallocate($destImage,$colors["red"],$colors["green"],$colors["blue"]));
			}
		}
		return $destImage;
	}
	// 上下反転
	static function image_flip($image){
		// 画像の幅を取得
		echo $w = imagesx($image);
		// 画像の高さを取得
		echo $h = imagesy($image);
		// 変換後の画像の生成（元の画像と同じサイズ）
		$destImage = @imagecreatetruecolor($w,$h);
		// 逆側から色を取得
		for($i=0;$i<$w;$i++){
			for($j=($h-1);$j>=0;$j--){
				$color_index = imagecolorat($image,$i,$j);
				$colors = imagecolorsforindex($image,$color_index);
				imagesetpixel($destImage,$i,abs($j-$h+1),imagecolorallocate($destImage,$colors["red"],$colors["green"],$colors["blue"]));
			}
		}
		return $destImage;
	}
	// 画像を回転
	static function image_rotate($image, $angle, $bgd_color){
		return imagerotate($image, $angle, $bgd_color, 0);
	}

}

?>