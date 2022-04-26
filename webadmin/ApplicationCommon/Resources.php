<?php
/*
 説明：文字リソースクラス
作成日：2014/2/26 TS谷
*/

/**
 * 文字リソースクラス
 */

class Resources{

	//拡張リソースライブラリ定義 開始
	const SAMPLE = "sample";
	//拡張リソースライブラリ定義 終了

	/**
	 * 使用リソースタイプ
	 */
	var $type = null;

	/**
	 * 言語ライブラリ
	 */
	var $lib = null;

	/**
	 * ライブラリファイル
	 */
	var $lib_file = null;

	/**
	 * ライブラリクラス
	 */
	var $lib_class = null;

	/**
	 * メッセージクラスコンストラクタ
	 * @param string $language 言語設定
	 */
	function __construct($type = ""){
		if($this->type == null || $type != ""){
			$this->type = $type;
		}
		$this->setLibrary();
	}

	/**
	 * 言語ライブラリのセット
	 */
	protected function setLibrary(){
		if($this->type == null || $this->type == ""){
			$this->lib_file = "Resources.common.php";	//共通リソースクラス読み込み
			$this->lib_class = "ResourceLibrary";
		}else{
			$this->lib_file = "Resources.".$this->type.".php";		//拡張リソースクラス読み込み
			$this->lib_class = "ResourceLibrary_".$this->type;
		}

		require_once(dirname(__FILE__).'/../Resources/'.$this->lib_file); //リソースクラス読み込み
		$this->lib = new ResourceLibrary();
	}

	/**
	 * リソース文字列の取得を行う
	 * 第一引数：リソース定義文字　第二引数以降：置換文字列
	 * @param string $define メッセージ定義文字
	 */
	function get($define){
		if(!defined($this->lib_class."::".$define)){
			return $define;
		}

		$args = func_get_args();
		$message = constant($this->lib_class."::".$define);
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