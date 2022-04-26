<?php
require_once(dirname(__FILE__).'/FrameworkConfig.php');  //フレームワーク設定クラス
/*
 説明：ログ取得ベースクラス
作成日：2013/11/16 TS谷
*/

/**
 * ログ出力ベースクラス
 *
 */
class LoggerBase {

	/**
	 * ログタイプ：情報
	 */
	const INFORMATION = "1";

	/**
	 * ログタイプ：エラー
	 */
	const ERROR = "2";

	/**
	 * ログタイプ：注意
	 */
	const NOTICE = "3";

	/**
	 * ログタイプ：デバッグ
	 */
	const DEBUG = "4";

	/**
	 * ログ出力クラスコンストラクタ
	 * @param string $filename 出力元ファイル名　
	 */
	function  __construct($file = "")
	{

	}

	private static function getFilePath(){
		$dirpath = dirname(__FILE__).'/'.FrameworkConfig::LogOutPath;
		$filename = date("Y-m-d").".log";
		return $dirpath.$filename;
	}

	/**
	 * デバッグログを記録する
	 * @param string $message メッセージ
	 * @param array $key_value 保存パラメータ（連想配列）
	 */
	static final function debug($message,$key_value = array()){
		if(FrameworkConfig::DebugLog){
			$message .= self::make_key_value_str($key_value);
			if(FrameworkConfig::DebugTrace){
				$message .= self::debug_trace_str(debug_backtrace());
			}
			self::out(self::DEBUG,$message,$key_value);
		}
	}

	/**
	 * エラーログを記録する
	 * @param string $message メッセージ
	 * @param array $key_value 保存パラメータ（連想配列）
	 */
	static final function error($message,$key_value = array()){
		if(FrameworkConfig::ErrorLog){
			$message .= self::make_key_value_str($key_value);
			if(FrameworkConfig::ErrorTrace){
				$message .= self::debug_trace_str(debug_backtrace());
			}
			self::out(self::ERROR,$message,$key_value);
		}
	}

	/**
	 * 情報ログを記録する
	 * @param string $message メッセージ
	 * @param array $key_value 保存パラメータ（連想配列）
	 */
	static final function info($message,$key_value = array()){
		if(FrameworkConfig::InformationLog){
			$message .= self::make_key_value_str($key_value);
			self::out(self::INFORMATION,$message,$key_value);
		}
	}

	/**
	 * 注意ログを記録する
	 * @param string $message メッセージ
	 * @param array $key_value 保存パラメータ（連想配列）
	 */
	static final function notice($message,$key_value = array()){
		if(FrameworkConfig::NoticeLog){
			$message .= self::make_key_value_str($key_value);
			if(FrameworkConfig::NoticeTrace){
				$message .= self::debug_trace_str(debug_backtrace());
			}
			self::out(self::NOTICE,$message,$key_value);
		}
	}

	static private function out($type,$content,$key_value = array()){

		$str = self::make_loghead($type);
		$str .= " ".$content;
		$str .= "\r\n";
		$filepath = self::getFilePath();
		$fp = fopen($filepath, "a");
		fwrite($fp, $str);
		fclose($fp);
	}

	static private function make_loghead($type){
		$str = date("Y-m-d H:i:s")." ";
		if($type == self::INFORMATION){
			$str .= "[Information]";
		}elseif($type == self::ERROR){
			$str .= "[Error]";
		}elseif($type == self::DEBUG){
			$str .= "[Debug]";
		}elseif($type == self::NOTICE){
			$str .= "[Notice]";
		}
		return $str;
	}

	static private function make_key_value_str($key_value = array()){
		$str = "";
		if($key_value == ""){
			return "";
		}
		foreach($key_value as $key => $value){
			if($str != ""){ $str .= ","; } else { $str .= " "; }
			if(!is_array($value)){
				$str .= $key."=".$value;
			}else{
				$str .= $key."=array()";
			}
		}
		return $str;
	}

	static private function debug_trace_str($debug_trace){
		$str = "";
		for($i=0;$i<count($debug_trace);$i++){
			$debug_trace_data = $debug_trace[$i];
			$function = "";
			if($i > 0){
				if(isset($debug_trace_data["class"])){
					$function = " (".$debug_trace_data["class"]."->".$debug_trace_data["function"]."())";
				}elseif(isset($debug_trace_data["function"])){
					$function = " (".$debug_trace_data["function"].")";
				}
			}
			$str .= "\r\n\t".$debug_trace_data["file"]." line ".$debug_trace_data["line"].$function;
		}
		return $str;
	}

}


?>