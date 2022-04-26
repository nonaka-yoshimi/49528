<?php
/*
 説明：エンジン共通クラス
作成日：2013/11/30 TS谷
*/

/**
 * エンジン共通クラス
 */
class EngineCommon
{
	/**
	 * HTTPヘッダ日付をtimestampに変換する
	 * @param string $string_date HTTPヘッダ日付
	 * @return int タイムスタンプ
	 */
	static function parse_http_date( $string_date ) {

		// 月の名前と数字を定義
		$define_month = array(
				"01" => "Jan", "02" => "Feb", "03" => "Mar",
				"04" => "Apr", "05" => "May", "06" => "Jun",
				"07" => "Jul", "08" => "Aug", "09" => "Sep",
				"10" => "Oct", "11" => "Nov", "12" => "Dec"
		);

		if( preg_match( "/^(Mon|Tue|Wed|Thu|Fri|Sat|Sun), ([0-3][0-9]) (Jan|Feb|Mar|Apr|May|Jun|Jul|Aug|Sep|Oct|Nov|Dec) ([0-9]{4}) ([0-2][0-9]):([0-5][0-9]):([0-5][0-9]) GMT$/", $string_date, $temp_date ) ) {
			$date["hour"] = $temp_date[5];
			$date["minute"] = $temp_date[6];
			$date["second"] = $temp_date[7];
			// 定義済みの月の名前を数字に変換する
			$date["month"] = array_search( $temp_date[3], $define_month );
			$date["day"] = $temp_date[2];
			$date["year"] = $temp_date[4];
		} elseif( preg_match( "/^(Monday|Tuesday|Wednesday|Thursday|Friday|Saturday|Sunday), ([0-3][0-9])-(Jan|Feb|Mar|Apr|May|Jun|Jul|Aug|Sep|Oct|Nov|Dec)-([0-9]{2}) ([0-2][0-9]):([0-5][0-9]):([0-5][0-9]) GMT$/", $string_date, $temp_date ) ) {

			$date["hour"] = $temp_date[5];
			$date["minute"] = $temp_date[6];
			$date["second"] = $temp_date[7];
			// 定義済みの月の名前を数字に変換する
			$date["month"] = array_search( $temp_date[3], $define_month );
			// 年が2桁しかないので1900を足して4桁に
			$date["day"] = $temp_date[2];
			$date["year"] = 1900 + $temp_date[4];
		} elseif( preg_match( "/^(Mon|Tue|Wed|Thu|Fri|Sat|Sun) (Jan|Feb|Mar|Apr|May|Jun|Jul|Aug|Sep|Oct|Nov|Dec) ([0-3 ][0-9]) ([0-2][0-9]):([0-5][0-9]):([0-5][0-9]) ([0-9]{4})$/", $string_date, $temp_date ) ) {
			$date["hour"] = $temp_date[4];
			$date["minute"] = $temp_date[5];
			$date["second"] = $temp_date[6];
			$date["month"] = array_search( $temp_date[2], $define_month );
			// 日が1桁の場合先、半角スペースを0に置換
			$date["day"] = str_replace( " ", 0, $temp_date[3] );
			// 定義済みの月の名前を数字に変換する
			$date["year"] = $temp_date[7];
		} else {
			return 0;
		}

		// UNIXタイムスタンプを生成 GMTなのに注意
		$timestamp = gmmktime( $date["hour"], $date["minute"], $date["second"], $date["month"], $date["day"], $date["year"] );

		return $timestamp;
	}

	static function contentIndexToArray($contentIndex){
		if($contentIndex == ""){
			return array();
		}
		$array = array();
		$contentIndexArr = explode(",",$contentIndex);
		for($i=0;$i<count($contentIndexArr);$i++){
			$contIndex = explode("=",$contentIndexArr[$i]);
			$index = str_replace(array("[","]"), array("",""),$contIndex[0]);
			$array[$index] = $contIndex[1];
		}
		return $array;
	}

	static function csvToArray($csv){
		if($csv == ""){
			return array();
		}

		$array = explode(",",$csv);
		return $array;
	}
}
?>