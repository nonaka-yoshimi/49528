<?php
/*
説明：ファイルアップロードマネージャベースクラス
作成日：2013/11/7 TS谷
*/

/**
 * ファイルアップロードマネージャベースクラス
 *
 */
class FileUploadManagerBase {

	/**
	 * エラー：一時ファイル保管用ディレクトリ未存在
	 */
	const ERROR_TMP_DIR_NOT_EXIST = "0";

	/**
	 * エラー：最大ファイルサイズ超過
	 */
	const ERROR_FILE_SIZE_OVER = "1";



	/**
	 * エラー
	 */
	var $error;

	/**
	 * 要素名(inputタグのname属性)
	 */
	var $element_name;

	/**
	 * アップロードを許可する最大ファイルサイズ(byte)
	 */
	var $max_filesize;

	/**
	 * アップロードを許可するトータルの最大ファイルサイズ(byte)
	 */
	static $max_filesize_total;

	/**
	 * アップロードするトータルのファイルサイズ(byte)
	 */
	static $filesize_total;

	/**
	 * 一時ファイル名
	 */
	var $tmp_filename;

	/**
	 * 一時ファイル名プレフィックス
	 */
	var $tmp_filename_prefix;

	/**
	 * 一時ファイル格納ディレクトリパス
	 */
	var $tmp_file_dir;

	/**
	 * 一時ファイルの保存時間(秒)
	 */
	var $tmp_file_lifetime;

	/**
	 * アップロードファイル格納ディレクトリパス
	 */
	var $upload_file_dir;

	/**
	 * アップロードファイル名(実際のパス）
	 */
	var $upload_real_filename_body;

	/**
	 * アップロードファイル名(拡張子は含まない）
	 */
	var $upload_filename_body;

	/**
	 * アップロードファイル拡張子
	 */
	var $upload_filename_extension;

	/**
	 * デフォルト設定されたファイルの表示上のファイル名
	 */
	var $default_filename;

	/**
	 * デフォルト設定されたファイルの実際のファイル名
	 */
	var $default_real_filename;

	/**
	 * デフォルトファイルの削除フラグ
	 */
	var $default_delete_flg;

	/**
	 * リクエスト$_FILESから取得したファイル情報
	 */
	var $file;

	/**
	 * Hiddenフォームに表示する一時ファイル名サフィックス
	 */
	var $hidden_form_element_suffix;

	/**
	 * 一時ファイルクリーンアップ状態フラグ
	 */
	static $tmp_clean_flg = false;

	/**
	 * ファイルアップロードマネージャベースクラスコンストラクタ
	 * @param string $name 要素名(inputタグのname属性)
	 */
	function __construct($name = ""){
		$this->element_name = $name;
		$this->max_filesize = 1048576;
		$this->tmp_filename = "";
		$this->tmp_filename_prefix = "TEMP_";
		$this->upload_filename_body = "";
		$this->upload_filename_extension = "";
		$this->default_filename = "";
		$this->default_delete_flg = false;
		$this->error = null;
		$this->getRequest();
	}

	/**
	 * エラー有無を確認する
	 * @return boolean
	 */
	function IsError(){
		if($this->error){
			return true;
		}else{
			return false;
		}
	}

	/**
	 * アップロードファイルがあるか確認する
	 */
	function IsFile(){
		if($this->tmp_filename == null || $this->tmp_filename == ""){
			return false;
		}

		if(!file_exists($this->tmp_file_dir.$this->tmp_filename)){
			return false;
		}
		return true;
	}

	/**
	 * アップロードを許可する最大ファイルサイズを設定する(byte)
	 * @param int $size ファイルサイズ
	 * @return boolean true/false
	 */
	function setMaxFileSize($size){
		if($size == null || $size == "" || !is_numeric($size) || $size < 0){
			return false;
		}
		$this->max_filesize = $size;
		return true;
	}

	/**
	 * 一時ファイルのアップロード先ディレクトリを設定する
	 * @param string $dir
	 * @return boolean true/false
	 */
	function setTmpFileDir($dir){
		if($dir == null || $dir == ""){
			return false;
		}
		$this->tmp_file_dir = $dir;
		return true;
	}

	/**
	 * アップロードファイルの格納先ディレクトリを設定する
	 * @param string $dir
	 * @return boolean true/false
	 */
	function setUploadFileDir($dir){
		if($dir == null || $dir == ""){
			return false;
		}
		$this->upload_file_dir = $dir;
		return true;
	}

	/**
	 * アップロードファイルのファイル名（サーバ上に格納する名前）を設定する
	 * @param string $filename
	 * @return boolean true/false
	 */
	function setUploadRealFileNameBody($filename){
		if($filename == null || $filename == ""){
			return false;
		}
		$this->upload_real_filename_body = $filename;
		return true;
	}

	/**
	 * デフォルトのファイル名（サーバ上に格納する名前）を設定する
	 * @param string $filename
	 * @return boolean true/false
	 */
	function setDefaultRealFileName($filename){
		if($filename == null || $filename == ""){
			return false;
		}
		$this->default_real_filename = $filename;
		return true;
	}

	/**
	 * デフォルトのファイル名（表示上の名前）を設定する
	 * @param string $filename
	 * @return boolean true/false
	 */
	function setDefaultFileName($filename){
		if($filename == null || $filename == ""){
			return false;
		}
		$this->default_filename = $filename;
		return true;
	}

	/**
	 * デフォルトファイル削除フラグを確認する
	 * @return boolean true/false
	 */
	function checkDefaultDelete(){
		return $this->default_delete_flg;
	}

	/**
	 * 一時ファイルの保持時間を設定する
	 * @param string $time 一時ファイルの保持時間
	 * @return boolean true/false
	 */
	function setTmpFileLifetime($time){
		if($time == null || $time == "" || !is_numeric($time) || $time < 0){
			return false;
		}
		$this->tmp_file_lifetime = $time;
		return true;
	}

	/**
	 * Hiddenフォームに表示する一時ファイル名サフィックスを設定する
	 * @param string $dir
	 * @return boolean true/false
	 */
	function setHiddenFormElementSuffix($suffix){
		if($suffix == null || $suffix == ""){
			return false;
		}
		$this->hidden_form_element_suffix = $suffix;
		return true;
	}

	/**
	 * 作成した一時ファイル名を取得する
	 * @return string 一時ファイル名
	 */
	function getTmpFileName(){
		return $this->tmp_filename;
	}

	/**
	 * リクエストからファイル情報を取得する
	 */
	function getRequest(){
		$this->file = isset($_FILES[$this->element_name]) ? $_FILES[$this->element_name] : "";
		$this->tmp_filename = isset($_REQUEST[$this->element_name.$this->hidden_form_element_suffix]) ? $_REQUEST[$this->element_name.$this->hidden_form_element_suffix] : "";

		//ファイル名取得処理
		if(isset($this->file["name"]) && $this->file["name"] != ""){
			$filename_body = $this->getFileNameBody($this->file["name"]);
			if($filename_body){
				$this->upload_filename_body = $filename_body;
			}
			$filename_extension = $this->getFileNameExtension($this->file["name"]);
			if($filename_extension){
				$this->upload_filename_extension = $filename_extension;
			}
		}

		//削除フラグを取得
		$delete_flg = isset($_REQUEST[$this->element_name.$this->hidden_form_element_suffix."_delete"]) ? $_REQUEST[$this->element_name.$this->hidden_form_element_suffix."_delete"] : "";
		if($delete_flg == "checked"){
			echo 'delete';
			//一時ファイルを削除
			$this->tmpFileDelete();
			echo $this->tmp_filename;
		}

		//デフォルトファイル削除フラグを取得
		$default_delete_flg = isset($_REQUEST[$this->element_name.$this->hidden_form_element_suffix."_default_delete"]) ? $_REQUEST[$this->element_name.$this->hidden_form_element_suffix."_default_delete"] : "";
		if($default_delete_flg == "checked"){
			$this->default_delete_flg = true;
		}

		//アップロードファイル名を取得
		$upload_filename = isset($_REQUEST[$this->element_name.$this->hidden_form_element_suffix."_upload_filename"]) ? $_REQUEST[$this->element_name.$this->hidden_form_element_suffix."_upload_filename"] : "";
		if($upload_filename != ""){
			//アップロードファイル名を設定
			$this->setUploadFileName($upload_filename);
		}

		//デフォルトファイル名(実ファイル名)を取得
		$default_real_filename = isset($_REQUEST[$this->element_name.$this->hidden_form_element_suffix."_default_real_filename"]) ? $_REQUEST[$this->element_name.$this->hidden_form_element_suffix."_default_real_filename"] : "";
		if($default_real_filename != ""){
			//デフォルトファイル名(実ファイル名)を設定
			$this->setDefaultRealFileName($default_real_filename);
		}

		//デフォルトファイル名(表示上のファイル名)を取得
		$default_filename = isset($_REQUEST[$this->element_name.$this->hidden_form_element_suffix."_default_filename"]) ? $_REQUEST[$this->element_name.$this->hidden_form_element_suffix."_default_filename"] : "";
		if($default_filename != ""){
			//デフォルトファイル名(表示上のファイル名)を設定
			$this->setDefaultFileName($default_filename);
		}

	}

	/**
	 * ファイル名から拡張子を除いた部分を取得する
	 * @param string $filename ファイル名
	 * @return boolean|string ファイル名から拡張子を除いた部分
	 */
	protected function getFileNameBody($filename){
		if($filename == null || $filename == ""){
			return false;
		}

		//ファイル名分割
		$filename_arr = explode(".",$filename);

		if(count($filename_arr) < 2){ return false; }

		$filename_body = "";
		//最後の一つを除いて結合
		for($i=0;$i<count($filename_arr) - 1;$i++)
		{
			$filename_body .= $filename_arr[$i];
		}
		return $filename_body;
	}

	/**
	 * アップロードファイル名の本体（拡張子を除く）を取得する
	 * @return string アップロードファイル名の本体（拡張子を除く）
	 */
	function getUploadFileNameBody(){
		return $this->upload_filename_body;
	}

	/**
	 * アップロードファイル名の拡張子部分を取得する
	 * @return string アップロードファイル拡張子
	 */
	function getUploadFileNameExtension(){
		return $this->upload_filename_extension;
	}

	/**
	 * ファイル名から拡張子を取得する
	 * @param string $filename ファイル名
	 * @return boolean|string 拡張子
	 */
	protected function getFileNameExtension($filename){
		if($filename == null || $filename == ""){
			return false;
		}

		//ファイル名分割
		$filename_arr = explode(".",$filename);

		if(count($filename_arr) < 2){ return false; }

		return $filename_arr[count($filename_arr) - 1];
	}

	/**
	 * アップロードファイル名を設定する
	 * @param string $filename アップロードファイル名
	 * @return boolean true/false
	 */
	function setUploadFileName($filename){
		if($filename == null || $filename == ""){
			return false;
		}
		$this->upload_filename_body = $this->getFileNameBody($filename);
		$this->upload_filename_extension = $this->getFileNameExtension($filename);
		return true;
	}

	/**
	 * アップロードファイル名を取得する
	 * @return string アップロードファイル名
	 */
	function getUploadFileName(){


		if($this->upload_filename_body == null || $this->upload_filename_body == ""){
			return "";
		}

		if($this->upload_filename_extension == null  || $this->upload_filename_extension == ""){
			return $this->upload_filename_body;
		}else{
			return $this->upload_filename_body.".".$this->upload_filename_extension;
		}
	}


	/**
	 * 一時ファイルの削除処理を実行
	 * @return true/false
	 */
	function tmpFileDelete(){
		if($this->tmp_file_dir != null && $this->tmp_file_dir != "" && $this->tmp_filename != null && $this->tmp_filename != ""
				&& file_exists($this->tmp_file_dir.$this->tmp_filename)){
			unlink($this->tmp_file_dir.$this->tmp_filename);
			$this->clearFileData(); //ファイルデータクリア
			return true;
		}else{
			$this->clearFileData(); //ファイルデータクリア
		}
		return false;
	}

	/**
	 * デフォルトファイルの削除処理を実行
	 * @return true/false
	 */
	function defaultFileDelete(){
		if($this->upload_file_dir != null && $this->upload_file_dir != "" && $this->default_real_filename != null && $this->default_real_filename != ""
				&& file_exists($this->upload_file_dir.$this->default_real_filename)){
			unlink($this->upload_file_dir.$this->default_real_filename);
			return true;
		}
		$this->clearDefaultFileData(); //ファイルデータクリア
		return false;
	}

	/**
	 * ファイルデータ一式をクリアする
	 */
	private function clearFileData(){
		$this->file = "";
		$this->tmp_filename = "";
		$this->upload_filename_body = "";
		$this->upload_filename_extension = "";
	}

	/**
	 * デフォルトファイルデータ一式をクリアする
	 */
	private function clearDefaultFileData(){
		$this->default_filename = "";
		$this->default_real_filename = "";
	}

	/**
	 * 一時ファイルを作成する
	 * @return boolean true/false
	 */
	function makeTmpFile(){
		global $debug_mode;
		if($this->file == null || $this->file == "" || $this->file == array())
		{
			if($debug_mode){ echo "FILES情報取得エラー<br>"; }
			return false;
		}

		if(!isset($this->file["tmp_name"]) || $this->file["tmp_name"] == null || $this->file["tmp_name"] == "")
		{
			if($debug_mode){ echo "ファイル未選択エラー<br>"; }
			return false;
		}

		if($this->element_name == null || $this->element_name == "")
		{
			if($debug_mode){ echo "name属性情報取得エラー<br>"; }
			return false;
		}

		//一時ファイル存在チェック
		if (!is_uploaded_file($this->file['tmp_name'])){
			if($debug_mode){ echo "一時ファイル存在チェックエラー<br>"; }
			return false;
		}

		//ファイル容量チェック
		if ($this->file['size'] > $this->max_filesize){
			if($debug_mode){ echo "ファイル容量チェックエラー<br>"; }
			$this->error = self::ERROR_FILE_SIZE_OVER;
			return false;
		}

		//一時ファイルアップロード先ディレクトリ存在チェック
		if(!file_exists($this->tmp_file_dir) || !is_dir($this->tmp_file_dir)){
			if($debug_mode){ echo "一時ファイルアップロード先ディレクトリ存在チェックエラー<br>"; }
			return false;
		}

		//アップロード先ファイルパス作成

		//ファイルの拡張子を取得する
		$filename = isset($this->file['name']) ? $this->file['name'] : "";
		$extension = pathinfo($filename, PATHINFO_EXTENSION);

		//一時ファイル名を生成する
		$tmp_filename_body = sha1(session_id().microtime().$this->element_name);
		$this->tmp_filename = $this->tmp_filename_prefix.$tmp_filename_body.".".$extension;

		$tmp_filepath = $this->tmp_file_dir.$this->tmp_filename;

		//ファイルアップロード処理実行
		if(!move_uploaded_file($this->file['tmp_name'], $tmp_filepath)){
			if($debug_mode){ echo "アップロード処理実行エラー<br>"; }
			return false;
		}

		return true;
	}

	/**
	 * アップロード処理（一時ファイル⇒保管）を実行する
	 */
	function upload(){
		global $debug_mode;

		if($this->tmp_file_dir == null || $this->tmp_file_dir == "" || $this->upload_file_dir == null || $this->upload_file_dir == "" )
		{
			if($debug_mode){
				echo "一時ファイルディレクトリ情報・アップロードファイルディレクトリ情報エラー<br>";
			}
			return false;
		}
		if($this->upload_filename_extension == null || $this->upload_filename_extension == "" || $this->upload_real_filename_body == null || $this->upload_real_filename_body == "")
		{
			if($debug_mode){
				echo "アップロードファイル名情報エラー<br>";
			}
			return false;
		}

		$result = copy($this->tmp_file_dir.$this->tmp_filename,$this->upload_file_dir.$this->upload_real_filename_body.".".$this->upload_filename_extension);
		if($result){
			unlink($this->tmp_file_dir.$this->tmp_filename);
			return true;
		}else{
			if($debug_mode){
				echo "コピー処理失敗エラー<br>";
			}
			return false;
		}

	}

	/**
	 * 一時ファイルをクリーンアップする
	 * @return boolean true/false
	 */
	function cleanTmpFiles(){
		if(FileUploadManagerBase::$tmp_clean_flg){
			return true;
		}

		if($this->tmp_file_dir == null || $this->tmp_file_dir == ""){
			return false;
		}
		//削除設定
		$dir_path = $this->tmp_file_dir;
		$delete_time = time() - $this->tmp_file_lifetime;
		$file_type = array();
		$file_prefix = $this->tmp_filename_prefix;

		//削除機能呼び出し
		if($this->dirCleanUpByTime($dir_path,$delete_time,$file_type,$file_prefix)){
			FileUploadManagerBase::$tmp_clean_flg = true;
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

	/**
	 * ファイルサイズ(byte)をGB,MB,KB,Byte表記に変換する
	 * @param int $filesize ファイルサイズ(byte)
	 * @return string ファイルサイズ(GB,MB,KB,Byte);
	 */
	static function calcFileSizeDisplay($filesize){
		if($filesize == null || $filesize == "" || !is_numeric($filesize) || $filesize < 0)
		{
			return "0Byte";
		}

		$filesize_g = $filesize / (1024 * 1024 * 1024);
		$filesize_m = $filesize / (1024 * 1024);
		$filesize_k = $filesize / 1024;

		$filesize_str = "";
		if($filesize_g >= 1){
			$filesize_str .= number_format($filesize_g,2)."GB";
		}elseif($filesize_m >= 1){
			$filesize_str .= number_format($filesize_m,1)."MB";
		}elseif($filesize_k >= 1){
			$filesize_str .= number_format($filesize_k,1)."KB";
		}else{
			$filesize_str .= number_format($filesize)."Byte";
		}
		return $filesize_str;

	}

	/**
	 * 一時ファイルのサイズ(単位表記)を取得する
	 * @return number
	 */
	function getTmpFileSize(){
		$filesize = $this->getTmpFileByte();
		return $this->calcFileSizeDisplay($filesize);
	}

	/**
	 * 一時ファイルのサイズ(byte)を取得する
	 * @return number
	 */
	function getTmpFileByte(){
		if(!Util::IsNullOrEmpty($this->tmp_filename)){
			if(file_exists($this->tmp_file_dir.$this->tmp_filename)){
				$filesize = filesize($this->tmp_file_dir.$this->tmp_filename);
			}else{
				$filesize = 0;
			}
		}else{
			$filesize = 0;
		}
		return $filesize;
	}

	/**
	 * デフォルトファイルのサイズを取得する
	 * @return number
	 */
	function getDefaultFileSize(){
		if(!Util::IsNullOrEmpty($this->default_real_filename)){
			if(file_exists($this->upload_file_dir.$this->default_real_filename)){
				$filesize = filesize($this->upload_file_dir.$this->default_real_filename);
			}else{
				$filesize = 0;
			}
		}else{
			$filesize = 0;
		}
		return $this->calcFileSizeDisplay($filesize);
	}


	function uploadFile($files,$upload_filepath){

		if($files == null || $files == "" || $files == array())
		{
			if($debug_mode){ echo "FILES情報取得エラー<br>"; }
			Logger::debug("FILES情報取得エラー");
			return false;
		}

		if(!isset($files["tmp_name"]) || $files["tmp_name"] == null || $files["tmp_name"] == "")
		{
			if($debug_mode){ echo "ファイル未選択エラー<br>"; }
			Logger::debug("ファイル未選択エラー");
			return false;
		}

		//一時ファイル存在チェック
		if (!is_uploaded_file($files['tmp_name'])){
			if($debug_mode){ echo "一時ファイル存在チェックエラー<br>"; }
			Logger::debug("一時ファイル存在チェックエラー");
			return false;
		}

		//ファイル容量チェック
		if ($files['size'] > $this->max_filesize){
			if($debug_mode){ echo "ファイル容量チェックエラー<br>"; }
			Logger::debug("ファイル容量チェックエラー",array("size" => $files['size']));
			$this->error = self::ERROR_FILE_SIZE_OVER;
			return false;
		}

		//一時ファイルアップロード先ディレクトリ存在チェック
		//if(!file_exists($this->tmp_file_dir) || !is_dir($this->tmp_file_dir)){
		//	if($debug_mode){ echo "一時ファイルアップロード先ディレクトリ存在チェックエラー<br>"; }
		//	return false;
		//}

		//アップロード先ファイルパス作成

		//ファイルの拡張子を取得する
		//$filename = isset($files['name']) ? $files['name'] : "";
		//$extension = pathinfo($filename, PATHINFO_EXTENSION);

		//一時ファイル名を生成する
		//$tmp_filename_body = sha1(session_id().microtime().$this->element_name);
		//$this->tmp_filename = $this->tmp_filename_prefix.$tmp_filename_body.".".$extension;

		//$tmp_filepath = $this->tmp_file_dir.$this->tmp_filename;

		//ファイルアップロード処理実行
		if(!move_uploaded_file($files['tmp_name'], $upload_filepath)){
			if($debug_mode){ echo "アップロード処理実行エラー<br>"; }
			Logger::debug("アップロード処理実行エラー",array("path" => $upload_filepath));
			return false;
		}else{
			return true;
		}
	}
}

?>