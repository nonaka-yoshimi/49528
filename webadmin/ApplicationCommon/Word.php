<?php
/*
 説明：言語クラス
作成日：2013/11/28 TS谷
*/

/**
 * 言語クラス
 */

class Word{

	const JAPANESE = "jp";
	const ENGLISH = "en";

	/**
	 * 使用言語設定
	 */
	var $language = "";

	/**
	 * 言語ライブラリ
	 */
	var $lib = null;

	/**
	 * メッセージクラスコンストラクタ
	 * @param string $language 言語設定
	 */
	function __construct($language = ""){
		if($language == ""){
			$this->language = Config::DEFAULT_LANGUAGE;	//デフォルト使用言語を設定
		}else{
			$this->language = $language;
		}
		$this->setLibrary();
	}

	/**
	 * 言語ライブラリのセット
	 */
	protected function setLibrary(){
		if($this->language == ""){
			$this->language = Config::DEFAULT_LANGUAGE;
		}
		if($this->language == self::ENGLISH){
			require_once(dirname(__FILE__).'/Language/Word.en.php'); //英語クラス読み込み
		}else{
			require_once(dirname(__FILE__).'/Language/Word.jp.php'); //日本語クラス読み込み
		}
		$this->lib = new WordLibrary();
	}

	/**
	 * メッセージ文字列の取得を行う
	 * 第一引数：メッセージ定義文字　第二引数以降：置換文字列
	 * @param string $define メッセージ定義文字
	 */
	function get($define){
		$args = func_get_args();
		$message = constant("WordLibrary::".$define);
		$replace_from = array();
		$replace_to = array();
		for($i=0;$i<count($args) - 1;$i++){
			$replace_from[] = "{".$i."}";
			$replace_to[] = $args[$i + 1];
		}
		$message = str_replace($replace_from, $replace_to, $message);
		return $message;
	}
}