<?php
/*
説明：デバッグベースクラス
作成日：2013/10/16 TS谷
*/

/**
 * デバッグベースクラス
 * @author Tani
 *
 */
class DebugBase
{
	/**
	 * コンストラクタ
	 */
	function __construct()
	{

	}

	/**
	 * SQLチェックを開始する<br>
	 * 以降の処理について、実行SQLを画面上に出力する。
	 */
	static function sqlCheckStart(){
		global $sql_check;
		$sql_check = true;
	}

	/**
	 * SQLチェックを終了する<br>
	 * 以降の処理について、実行SQLが画面上に出力されなくなる
	 */
	static function sqlCheckEnd(){
		global $sql_check;
		$sql_check = false;
	}

	/**
	 * デバッグモードを開始する<br>
	 * 以降の処理について、Frameworkのデバッグログを画面上に出力する。
	 */
	static function debugModeStart(){
		global $debug_mode;
		$debug_mode = true;
	}

	/**
	 * デバッグモードを終了する<br>
	 * 以降の処理について、Frameworkのデバッグログが画面上に出力されなくなる。
	 */
	static function debugModeEnd(){
		global $debug_mode;
		$debug_mode = false;
	}

	/**
	 * 配列を表に整形して出力する。
	 */
	static function arrayCheck($array){
		echo '<table style="border-width:1px; border-style:solid; background-color:#fff;">'."\n";
		foreach($array as $index => $value){
			echo '<tr>'."\n";
			if(is_array($value)){
				$yajirushi = "==>";
				$color = "red";
			}else{
				$yajirushi = "=>";
				$color = "black";
			}

			echo '<td style="border-width:1px; border-style:solid; vertical-align:top; color:'.$color.';">'.htmlspecialchars($index).'</td>'."\n".'<td style="border-width:1px; border-style:solid; vertical-align:top; color:'.$color.';">'.$yajirushi.'</td>'."\n";

			if(is_array($value)){
				echo '<td style="border-width:1px; border-style:solid; vertical-align:top;">'."\n";
				echo DebugBase::arrayCheck($value);
				echo '</td>'."\n";
			}else{
				echo '<td style="border-width:1px; border-style:solid; vertical-align:top;">'.htmlspecialchars($value).'&nbsp;</td>'."\n";
			}

			echo '</tr>'."\n";
		}
		echo '</table>'."\n";
	}

	/**
	 * セッション内容を表にして出力する
	 */
	static function sessionCheck(){
		self::arrayCheck($_SESSION);
	}

	/**
	 * クッキー内容を表にして出力する
	 */
	static function cookieCheck(){
		self::arrayCheck($_COOKIE);
	}

	/**
	 * リクエスト内容を表にして出力する
	 */
	static function requestCheck(){
		self::arrayCheck($_REQUEST);
	}

	/**
	 * ファイルリクエスト内容を表にして出力する
	 */
	static function filesRequestCheck(){
		self::arrayCheck($_FILES);
	}

	/**
	 * マイクロ秒でタイムスタンプを取得する
	 */
	static function printMicroTimestamp($message = "",$br = true){
		if($br){
			echo $message.(float)microtime(true)."<br>";
		}else{
			echo $message.(float)microtime(true);
		}

	}
}

?>