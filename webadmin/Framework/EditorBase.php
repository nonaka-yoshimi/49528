<?php
/*
 説明：エディタベースクラス
作成日：2013/10/17 TS谷
*/

/**
 * エディタベースクラス
 *
 */
abstract class EditorBase
{

	/**
	 * CKEditorベースパス
	 */
	var $ckeditor_basepath;

	/**
	 * CKFinderベースパス
	 */
	var $ckfinder_basepath;

	/**
	 * エディタの表示コンテンツ
	 */
	var $content;

	/**
	 * エディタの横幅設定
	 */
	var $width;

	/**
	 * エディタの縦幅設定
	 */
	var $height;

	/**
	 * エディタID設定
	 */
	var $editor_id;

	/**
	 * フォームのname属性設定
	 */
	var $formName;

	/**
	 * メディアファイルのベースパス
	 */
	var $media_basepath;

	/**
	 * メディアファイルのディレクトリパス
	 */
	var $media_dir_path;

	/**
	 * メディアファイルのサムネイル格納場所
	 */
	var $media_thumbs_path;

	/**
	 * スタイルシート設定配列
	 */
	var $css;

	abstract protected function setConfig();

	/**
	 * エディタベースクラスコンストラクタ
	 */
	protected function  __construct()
	{
		$this->setConfig();
	}

	/**
	 * CKEditorベースパス設定処理
	 * @param string CKEditorベースパス(デフォルト：/ckeditor/)
	 * @return boolean true:成功/false:失敗
	 */
	function setCKEditorBasePath($dirpath)
	{
		if($dirpath == null || $dirpath == ""){
			return false;
		}

		//CKEditorベースパスを設定する
		$this->ckeditor_basepath = $dirpath;

		return true;
	}

	/**
	 * CKFinderベースパス設定処理
	 * @param string CKFinderベースパス(デフォルト：/ckfinder/)
	 * @return boolean true:成功/false:失敗
	 */
	function setCKFinderBasePath($dirpath)
	{
		if($dirpath == null || $dirpath == ""){
			return false;
		}

		//CKFinderベースパスを設定する
		$this->ckfinder_basepath = $dirpath;

		return true;
	}

	/**
	 * エディタ表示コンテンツ設定処理
	 * @param string 表示コンテンツ
	 * @return boolean true:成功/false:失敗
	 */
	function setContent($content)
	{
		if($content == null || $content == ""){
			return false;
		}

		//エディタ表示コンテンツを設定する
		$this->content = $content;

		return true;
	}

	/**
	 * CKEditorの横幅設定
	 * @param string 横幅設定(例：80%、200px など)
	 * @return boolean true:成功/false:失敗
	 */
	function setCKEditorWidth($size)
	{
		if($size == null || $size == ""){
			return false;
		}

		//CKFinderベースパスを設定する
		$this->width = $size;

		return true;
	}

	/**
	 * CKEditorの縦幅設定
	 * @param string 縦幅設定(例：80%、200px など)
	 * @return boolean true:成功/false:失敗
	 */
	function setCKEditorHeight($size)
	{
		if($size == null || $size == ""){
			return false;
		}

		//CKFinderベースパスを設定する
		$this->height = $size;

		return true;
	}

	/**
	 * フォームのname属性設定
	 * @param string name属性
	 * @return boolean true:成功/false:失敗
	 */
	function setFormName($name)
	{
		if($name == null || $name == ""){
			return false;
		}

		//フォームのname属性を設定する
		$this->formName = $name;

		return true;
	}

	/**
	 * エディタID設定
	 * @param string エディタID
	 * @return boolean true:成功/false:失敗
	 */
	function setEditorId($id)
	{
		if($id == null || $id == ""){
			return false;
		}

		//フォームのname属性を設定する
		$this->editor_id = $id;

		return true;
	}

	/**
	 * メディアベースパス設定
	 * @param string ベースパス
	 * @return boolean true:成功/false:失敗
	 */
	function setMediaBasePath($path)
	{
		if($path == null || $path == ""){
			return false;
		}

		//メディアベースパスを設定する
		$this->media_basepath = $path;

		return true;
	}

	/**
	 * メディアディレクトリパス設定
	 * @param string ベースパス
	 * @return boolean true:成功/false:失敗
	 */
	function setMediaDirPath($path)
	{
		if($path == null || $path == ""){
			return false;
		}

		//メディアディレクトリパスを設定する
		$this->media_dir_path = $path;

		return true;
	}

	/**
	 * メディアサムネイル格納場所設定
	 * @param string サムネイル格納場所
	 * @return boolean true:成功/false:失敗
	 */
	function setMediaThumbsPath($path)
	{
		if($path == null || $path == ""){
			return false;
		}

		//メディアサムネイル格納場所を設定する
		$this->media_thumbs_path = $path;

		return true;
	}

	/**
	 * スタイルシート追加設定
	 * @param string スタイルシートパス
	 * @return boolean true:成功/false:失敗
	 */
	function addCss($path)
	{
		if($path == null || $path == ""){
			return false;
		}

		//メディアサムネイル格納場所を設定する
		$this->css[] = $path;

		return true;
	}

	/**
	 * エディタ表示処理
	 */
	function display()
	{

		$_SESSION['ckeditor_media_basepath'] = $this->media_basepath;	//メディアベースパスを設定
		$_SESSION['ckeditor_media_dirpath'] = $this->media_dir_path;	//メディアディレクトリパスを設定
		$_SESSION['ckeditor_media_thumbs_path'] = $this->media_thumbs_path;	//メディアサムネイル格納場所設定

		echo "\n";
		echo "<script type=\"text/javascript\" src=\"".$this->ckeditor_basepath."ckeditor.js\"></script>\n"; //CKEditor読み込み
		echo "<script type=\"text/javascript\" src=\"".$this->ckfinder_basepath."ckfinder.js\"></script>\n"; //CKFinder読み込み
		echo "<script type=\"text/javascript\">\n";
		echo "CKEDITOR.config.width = '".$this->width."';\n"; //横幅設定
		echo "CKEDITOR.config.height = '".$this->height."';\n"; //縦幅設定

		//ツールバー設定
		echo "CKEDITOR.config.toolbar = [\n";
		echo "['Source','-','NewPage','Preview','-','Templates']\n";
		echo ",['Cut','Copy','Paste','PasteText','PasteFromWord','-','Print','SpellChecker']\n";
		echo ",['Undo','Redo','-','Find','Replace','-','SelectAll','RemoveFormat']\n";
		//echo ",['Form','Checkbox','Radio','TextField','Textarea','Select','Button','ImageButton','HiddenField']\n";
		echo ",'/'\n";
		echo ",['Bold','Italic','Underline','Strike','-','Subscript','Superscript']\n";
		echo ",['NumberedList','BulletedList','-','Outdent','Indent','Blockquote']\n";
		echo ",['JustifyLeft','JustifyCenter','JustifyRight','JustifyBlock']\n";
		echo ",['Link','Unlink','Anchor']\n";
		echo ",['Image','Flash','Table','HorizontalRule','Smiley','SpecialChar','PageBreak']\n";
		echo ",'/'\n";
		echo ",['Styles','Format','Font','FontSize']\n";
		echo ",['TextColor','BGColor']\n";
		echo ",['ShowBlocks']\n";
		echo "];\n";

		//CSS読み込み設定
		echo "CKEDITOR.config.contentsCss = [";
		for($i=0;$i<count($this->css);$i++){
			if($i > 0){ echo ","; }
			echo "'".$this->css[$i]."'";
		}
		echo "];\n";

		echo "CKEDITOR.config.bodyId = 'contents';\n";

		echo "CKEDITOR.config.bodyClass = '';\n";



		echo "CKFinder.setupCKEditor(CKEDITOR,'".$this->ckfinder_basepath."');\n";
		echo "</script>\n";
		echo "<textarea class=\"ckeditor\" id=\"".$this->editor_id."\" name=\"".$this->formName."\">".$this->content."</textarea>\n";
		echo "\n";
	}
}

?>