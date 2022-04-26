<?php
require_once(dirname(__FILE__).'/../Framework/CSVDownLoadManagerBase.php'); //ベースクラス読み込み
/*
説明：CSVダウンロードマネージャクラス
作成日：2013/11/13 TS谷
*/

/**
 * CSVダウンロードマネージャクラス
 */
class CSVDownLoadManager extends CSVDownLoadManagerBase {

	/**
	 * CSVダウンロードマネージャクラスコンストラクタ
	 */
	function __construct(){
		parent::__construct(); //親クラスのコンストラクタを呼び出し
	}

	/**
	 * CSVダウンロードの動作設定を行う
	 */
	protected function setConfig(){
		$this->csv_encode = "SJIS-win";
		$this->data_encode = "UTF-8";
		$this->filename = "csv_".date('YmdHis',time()).'.csv';
		$this->tmp_file_lifetime = 60;
		$this->tmp_filename_prefix = "csv_";
		$this->title_output_flg = true;
		$this->filepath = dirname(__FILE__)."/../".Config::CSV_DIR_PATH;
	}
}

?>