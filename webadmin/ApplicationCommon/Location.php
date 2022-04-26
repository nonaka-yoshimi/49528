<?php
/*
 説明：ロケーションクラス
作成日：2013/11/28 TS谷
*/

/**
 * ロケーションクラス
*/
class Location
{
	/**
	 * リダイレクト処理を行う
	 * @param string $url リダイレクト先URL
	 * @param array $param 送信パラメータ(連想配列)
	 * @return void
	 */
	static function redirect($url,$param = array()){
		$param_str = "";
		foreach($param as $key => $value){
			if($param_str == ""){ $param_str .= "?"; }else{ $param_str .= "&"; }
			$param_str .= $key."=".htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
		}
		header("Location: ".$url.$param_str);
		exit;
	}
}
