<?php
/*
説明：ファイルアップロードマネージャクラス
作成日：2013/11/7 TS谷
*/

/**
 * ファイルアップロードマネージャクラス
 *
 */
class FileUploadManager extends FileUploadManagerBase {


	/**
	 * 入力したファイル名称の使用有無
	 */
	var $use_link_name_flg = false;

	/**
	 * リンク名
	 */
	var $link_name;

	/**
	 * リンク名サフィックス
	 */
	var $link_name_suffix;

	/**
	 * 添付ファイル一覧出力フラグ
	 */
	static $list_output_flg = false;

	/**
	 * ファイルアップロードマネージャベースクラスコンストラクタ
	 * @param string $name 要素名(inputタグのname属性)
	 * @param boolean $use_link_name リンク名欄の仕様有無（デフォルトは無し）
	 */
	function __construct($name = "",$use_link_name = false){
		$this->setHiddenFormElementSuffix("_tempfilename");							//hiddenタグで使用するプレフィックスを設定
		$this->setTmpFileDir(dirname(__FILE__)."/../".Config::TMP_DIR_PATH);		//添付ファイルを保管するディレクトリを設定
		$this->setUploadFileDir(dirname(__FILE__)."/../".Config::ATTACH_DIR_PATH); 	//アップロードファイルを保管するディレクトリを設定
		$this->setTmpFileLifetime(Config::TMP_FILE_LIFETIME);						//一時ファイル保持時間を設定
		$this->link_name_suffix = "_linkname";										//リンク名サフィックスを設定
		if($use_link_name){
			$this->use_link_name_flg = $use_link_name;								//リンク名使用フラグを設定
		}

		//親クラスのコンストラクタを呼び出し
		parent::__construct($name);

		if($use_link_name){
			//リクエストからリンク名を取得する
			$this->getLinkNameRequest();
		}
		$this->setMaxFileSize(Config::UPLOAD_FILE_MAX_SIZE);	//アップロード可能な最大ファイルサイズ設定

		$this->makeTmpFile();	//一時ファイルを作成
		$this->cleanTmpFiles(); //不要な一時ファイルを削除

	}

	/**
	 * リンク名のリクエストを取得する
	 */
	function getLinkNameRequest(){
		$this->link_name = isset($_REQUEST[$this->element_name.$this->link_name_suffix]) ? $_REQUEST[$this->element_name.$this->link_name_suffix] : "";

	}

	/**
	 * リンク名を設定する
	 * @param string $filename リンク名
	 * @return boolean true/false
	 */
	function setLinkName($filename){
		if($filename == null || $filename == "")
		{
			return false;
		}
		$this->link_name = $filename;
		$this->setDefaultFileName($filename);
		return true;
	}

	/**
	 * ファイル入力フォームを出力する
	 * @return boolean true/false
	 */
	function makeInputForm(){
		if($this->element_name == null || $this->element_name == "")
		{
			return false;
		}

		echo '<input type="file" name="'.$this->element_name.'" />';
		if($this->use_link_name_flg){
			//リンク名が設定されている場合
			echo '　リンク名<input name="'.$this->element_name.$this->link_name_suffix.'" type="text" value="'.htmlspecialchars($this->getLinkName()).'"><br />';
		}else{
			//リンク名が設定されていない場合
			echo '<br />';
		}
		if($this->tmp_filename != null && $this->tmp_filename != ""){
			//一時ファイルが設定されている場合
			echo '<input type="hidden" name="'.$this->element_name.$this->hidden_form_element_suffix.'" value="'.$this->tmp_filename.'" />';
			echo '<input type="hidden" name="'.$this->element_name.$this->hidden_form_element_suffix.'_upload_filename" value="'.htmlspecialchars($this->getUploadFileName()).'" />';
			echo '┗アップロードファイル：<a href="/'.Config::BASE_DIR_PATH.Config::ADMIN_DIR_PATH.'download.php?type=tmp&filename='.$this->tmp_filename.'" target="_blank" >'.htmlspecialchars($this->getUploadFileName()).'</a> ('.$this->getTmpFileSize().")";
			echo '&nbsp;&nbsp;<input type="checkbox" value="checked" name="'.$this->element_name.$this->hidden_form_element_suffix.'_delete" >削除<span class="small r">（上書きの場合チェック不要）</span><br />';

		}else{
			//一時ファイルが設定されていない場合
			if($this->default_real_filename != null && $this->default_real_filename != ""){
				$checked = "";
				if($this->default_delete_flg){
					$checked = "checked=checked";
				}
				echo '┗登録済みファイル：<a href="/'.Config::BASE_DIR_PATH.Config::ADMIN_DIR_PATH.'download.php?type=attach&filename='.$this->default_real_filename.'" target="_blank" >'.htmlspecialchars($this->default_filename).'</a> ('.$this->getDefaultFileSize().")";
				echo '&nbsp;&nbsp;<input type="checkbox" value="checked" name="'.$this->element_name.$this->hidden_form_element_suffix.'_default_delete" '.$checked.'>削除<span class="small r">（上書きの場合チェック不要）</span><br />';
			}
		}
		echo '<input type="hidden" name="'.$this->element_name.$this->hidden_form_element_suffix.'_default_filename" value="'.$this->default_filename.'" />';
		echo '<input type="hidden" name="'.$this->element_name.$this->hidden_form_element_suffix.'_default_real_filename" value="'.$this->default_real_filename.'" />';
	}

	/**
	 * 戻るボタンなどで使用するHiddenタグを出力する
	 */
	function makeHiddenForm(){
		if($this->tmp_filename != null && $this->tmp_filename != ""){
			echo '<input type="hidden" name="'.$this->element_name.$this->hidden_form_element_suffix.'" value="'.$this->tmp_filename.'" />';

			echo '<input type="hidden" name="'.$this->element_name.$this->hidden_form_element_suffix.'_upload_filename" value="'.htmlspecialchars($this->getUploadFileName()).'" />';

		}
		if($this->use_link_name_flg){
			echo '<input type="hidden" name="'.$this->element_name.$this->link_name_suffix.'" value="'.htmlspecialchars($this->link_name).'" />';

		}
		echo '<input type="hidden" name="'.$this->element_name.$this->hidden_form_element_suffix.'_default_real_filename" value="'.$this->default_real_filename.'" />';
		echo '<input type="hidden" name="'.$this->element_name.$this->hidden_form_element_suffix.'_default_filename" value="'.$this->default_filename.'" />';
		if($this->default_delete_flg){
			echo '<input type="hidden" name="'.$this->element_name.$this->hidden_form_element_suffix.'_default_delete" value="checked" />';
		}
	}

	/**
	 * 一時ファイルへのリンクを出力する
	 */
	function makeTmpFileLink(){
		if($this->tmp_filename != null && $this->tmp_filename != ""){
			//一時ファイルが設定されている場合

			echo '<input type="hidden" name="'.$this->element_name.$this->hidden_form_element_suffix.'" value="'.$this->tmp_filename.'" />';
			if(FileUploadManager::$list_output_flg){
				echo "<br>";
			}
			if($this->use_link_name_flg && !Util::IsNullOrEmpty($this->link_name) ){
				//リンク名が設定されている場合
				$link_name = $this->link_name;
				echo '<a href="/'.Config::BASE_DIR_PATH.Config::ADMIN_DIR_PATH.'download.php?type=tmp&filename='.$this->tmp_filename.'" target="_blank" >'.htmlspecialchars($link_name).'</a> ('.$this->getTmpFileSize().")";
			}else{
				//リンク名が設定されていない場合
				echo '<a href="/'.Config::BASE_DIR_PATH.Config::ADMIN_DIR_PATH.'download.php?type=tmp&filename='.$this->tmp_filename.'" target="_blank" >'.htmlspecialchars($this->getLinkName()).'</a> ('.$this->getTmpFileSize().")";
			}
			echo '<input type="hidden" name="'.$this->element_name.$this->hidden_form_element_suffix.'_upload_filename" value="'.htmlspecialchars($this->getUploadFileName()).'" />';
			if($this->use_link_name_flg){
				echo '<input type="hidden" name="'.$this->element_name.$this->link_name_suffix.'" value="'.htmlspecialchars($this->link_name).'" />';
			}
			FileUploadManager::$list_output_flg = true;
		}else{
			//一時ファイルが設定されていない場合
			if($this->default_delete_flg){
				//削除フラグが設定されている場合は表示しない
			}elseif($this->default_real_filename != null && $this->default_real_filename != ""){
				if(FileUploadManager::$list_output_flg){
					echo "<br>";
				}
				if($this->use_link_name_flg && !Util::IsNullOrEmpty($this->link_name) ){
					//リンク名が設定されている場合
					$link_name = $this->link_name;
					echo '<a href="/'.Config::BASE_DIR_PATH.Config::ADMIN_DIR_PATH.'download.php?type=attach&filename='.$this->default_real_filename.'" target="_blank" >'.htmlspecialchars($link_name).'</a> ('.$this->getDefaultFileSize().")";
				}else{
					//リンク名が設定されていない場合
					echo '<a href="/'.Config::BASE_DIR_PATH.Config::ADMIN_DIR_PATH.'download.php?type=attach&filename='.$this->default_real_filename.'" target="_blank" >'.htmlspecialchars($this->default_real_filename).'</a> ('.$this->getDefaultFileSize().")";
				}
				FileUploadManager::$list_output_flg = true;
			}
		}
		echo '<input type="hidden" name="'.$this->element_name.$this->hidden_form_element_suffix.'_default_real_filename" value="'.$this->default_real_filename.'" />';
		echo '<input type="hidden" name="'.$this->element_name.$this->hidden_form_element_suffix.'_default_filename" value="'.$this->default_filename.'" />';
		if($this->default_delete_flg){
			echo '<input type="hidden" name="'.$this->element_name.$this->hidden_form_element_suffix.'_default_delete" value="checked" />';
		}
	}

	/**
	 * リンク名を取得する
	 * @return リンク名
	 */
	function getLinkName(){
		if($this->use_link_name_flg && !Util::IsNullOrEmpty($this->link_name) ){
			return $this->link_name;
		}else{
			return $this->getFileNameBody($this->getUploadFileName());
		}
	}

	/**
	 * エラーメッセージを取得する
	 * @return string エラーメッセージ
	 */
	function getErrorMessage(){
		if(!$this->error){
			return "";
		}
		if($this->error == self::ERROR_FILE_SIZE_OVER){
			return $this->getUploadFileName()."のファイルサイズが大きすぎます。(最大".$this->calcFileSizeDisplay(Config::UPLOAD_FILE_MAX_SIZE)."まで）";
		}elseif($this->error == self::ERROR_TMP_DIR_NOT_EXIST){
			return "一時ファイルアップロード用のディレクトリが存在しません。";
		}
	}
}

?>