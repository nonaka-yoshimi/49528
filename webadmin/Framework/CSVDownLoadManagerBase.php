<?php
/*
説明：CSVダウンロードマネージャベースクラス
作成日：2013/11/13 TS谷
*/

/**
 * CSVダウンロードマネージャベースクラス
 *
 */
abstract class CSVDownLoadManagerBase {

	/**
	 * カラム名配列
	 */
	var $column_arr;

	/**
	 * レコード配列
	 */
	var $record_arr;

	/**
	 * CSV出力エンコード
	 */
	var $csv_encode;

	/**
	 * データエンコード
	 */
	var $data_encode;

	/**
	 * 出力ファイル名
	 */
	var $filename;

	/**
	 * CSV格納ファイルパス
	 */
	var $filepath;

	/**
	 * タイトル出力フラグ
	 */
	var $title_output_flg;

	/**
	 * 一時ファイル名プレフィックス
	 */
	var $tmp_filename_prefix;

	/**
	 * 一時ファイルの保存時間(秒)
	 */
	var $tmp_file_lifetime;

	abstract protected function setConfig();

	/**
	 * CSVダウンロードマネージャベースクラスコンストラクタ
	 */
	function __construct(){
		$this->column_arr = array();
		$this->record_arr = array();
		$this->setConfig();
	}

	/**
	 * カラム名(タイトル)配列を設定する
	 * @param array $column_arr カラム名配列
	 * @return boolean true/false
	 */
	function setColumn($column_arr){
		if($column_arr == null || $column_arr == "" || !is_array($column_arr)){
			return false;
		}
		$this->column_arr = $column_arr;
		return true;
	}

	/**
	 * レコード配列を1件設定する
	 * @param array $record_arr レコード配列
	 * @return boolean true/false
	 */
	function addRecord($record_arr){
		if($record_arr == null || $record_arr == "" || !is_array($record_arr)){
			return false;
		}
		$this->record_arr[] = $record_arr;
	}

	/**
	 * 出力ファイル名を設定する
	 * @param string $filename 出力ファイル名
	 * @return boolean true/false
	 */
	function setFileName($filename){
		if($filename == null || $filename == ""){
			return false;
		}
		$this->filename = $filename;
		return true;
	}

	/**
	 * CSVファイルパスを設定する
	 * @param string $filepath CSVファイルパス
	 * @return boolean true/false
	 */
	function setFilePath($filepath){
		if($filepath == null || $filepath == ""){
			return false;
		}
		$this->filepath = $filepath;
		return true;
	}

	/**
	 * 出力元データエンコードを指定する
	 * @param string $encode 出力データエンコード
	 * @return boolean true/false
	 */
	function setDataEncode($encode){
		if($encode == null || $encode == ""){
			return false;
		}
		$this->data_encode = $encode;
		return true;
	}

	/**
	 * CSVファイルを出力する
	 */
	function output(){
		$column_arr = $this->column_arr;
		$record_arr = $this->record_arr;

		$output_arr = array();
		$counter = 0;
		if($this->title_output_flg){
			//カラム名エンコード変換処理
			for($i=0;$i<count($column_arr);$i++){
				$output_arr[$counter][$i] = mb_convert_encoding($column_arr[$i],$this->csv_encode,$this->data_encode);
			}
			$counter++;
		}
		//データレコードエンコード変換処理
		for($i=0;$i<count($record_arr);$i++){
			$record_one = $record_arr[$i];
			for($ii=0;$ii<count($record_one);$ii++){
				$output_arr[$counter][$ii] = mb_convert_encoding($record_arr[$i][$ii],$this->csv_encode,$this->data_encode);
			}
			$counter++;
		}

		$csvFile = $this->filepath.$this->filename;
		$fp = fopen($csvFile,'w');
		for($i=0;$i<count($output_arr);$i++){
			fputcsv($fp,$output_arr[$i]);
		}
		fclose($fp);

		if(file_exists($csvFile)) {
			$filename = basename($csvFile);
			$filesize = filesize($csvFile);
			//ダウンロード開始
			if(isset($_SERVER['HTTP_USER_AGENT']) && strpos($_SERVER['HTTP_USER_AGENT'],'MSIE') && $_SERVER['SERVER_PORT']==443){
				header('Pragma:');
			}
			header('Content-type: application/octet-stream');
			header('Content-Disposition: attachment; filename='.$filename);
			header('Content-Length: '.$filesize);

			//出力バッファをオフ
			if (ob_get_level() > 0 && ob_get_length() > 0) {
    			while (ob_end_clean());
			}
			readfile($csvFile);

			$this->cleanTmpFiles(); //古い一時ファイルを削除
			exit;
		}
	}

	/**
	 * 一時ファイルをクリーンアップする
	 * @return boolean true/false
	 */
	function cleanTmpFiles(){

		if($this->filepath == null || $this->filepath == ""){
			return false;
		}
		//削除設定
		$dir_path = $this->filepath;
		$delete_time = time() - $this->tmp_file_lifetime;
		$file_type = array("csv");
		$file_prefix = $this->tmp_filename_prefix;

		//削除機能呼び出し
		if($this->dirCleanUpByTime($dir_path,$delete_time,$file_type,$file_prefix)){
			return true;
		}else{
			return false;
		}
	}

	/**
	 * 指定時間以前のフォルダ内ファイルをタイムスタンプに基づき削除する
	 * @param string $dir_path 対象ディレクトリパス
	 * @param time $delete_time 基準時間（デフォルトは現在時間）
	 * @param array $file_type ファイル拡張子（配列で指定、デフォルトは指定なし）
	 * @param strin $file_prefix 削除対象とするファイルプレフィックス（デフォルトは指定なし）
	 * @return boolean true/false
	 */
	static function dirCleanUpByTime($dir_path,$delete_time = "",$file_type = array(),$file_prefix = "")
	{
		global $debug_mode;
		if($dir_path == null || $dir_path == "" || !is_array($file_type)){
			return FALSE;
		}
		if($delete_time == ""){
			$delete_time = time();
		}
		$res_dir = opendir($dir_path);
		while($file_one = readdir($res_dir)){
			$file_path = $dir_path.$file_one;
			if(file_exists($file_path)){
				$file_time = filemtime($file_path);
				if($file_time != NULL && $file_time != "" && $file_time != 0 && $file_time <= $delete_time){
					$delete_flg = TRUE;
					//拡張子タイプチェック
					if(count($file_type) > 0){
						$extension = pathinfo($file_path, PATHINFO_EXTENSION);
						$delete_flg2 = FALSE;
						foreach($file_type as $file_type_one){
							if($file_type_one == $extension){
								$delete_flg2 = TRUE;
							}
						}
						if(!$delete_flg2){
							$delete_flg = FALSE;
							if($debug_mode){ echo "拡張子条件で除外：".$file_path."<br>"; }

						}
					}

					//システムファイル除外
					if($delete_flg && ($file_one == '..' || $file_one == '.' || substr($file_one,0,1) == ".")){
						$delete_flg = FALSE;
						if($debug_mode){ echo "システムファイル条件で除外：".$file_path."<br>"; }
					}

					//プレフィックスチェック
					if($delete_flg && $file_prefix != ""){
						if(!preg_match("/^".$file_prefix."/", $file_one)){
							$delete_flg = FALSE;
							if($debug_mode){ echo "プレフィックス条件で除外：".$file_path."<br>"; }
						}
					}

					//削除処理実行
					if($delete_flg){
						unlink($file_path);
						if($debug_mode){ echo "ファイル削除：".$file_path."<br>"; }
					}
				}
			}
		}
		return true;
	}
}

?>