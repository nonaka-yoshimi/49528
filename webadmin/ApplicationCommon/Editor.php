<?php
require_once(dirname(__FILE__).'/../Framework/EditorBase.php'); //ベースクラス読み込み

/*
説明：エディタクラス
作成日：2013/10/17 TS谷
*/

/**
 * エディタ作成クラス<br>
 * @author Tani
 *
 */
class Editor extends EditorBase{

	/**
	 * エディタ作成クラスコンストラクタ
	 */
	function  __construct()
	{
		//親クラスのコンストラクタ呼び出し
		parent::__construct();
	}

	/**
	 * エディタ動作設定
	 */
	protected function setConfig(){
		$this->ckeditor_basepath = "/".Config::BASE_DIR_PATH.Config::ADMIN_DIR_PATH."ckeditor/";		//CKEditorのベースパス設定
		$this->ckfinder_basepath = "/".Config::BASE_DIR_PATH.Config::ADMIN_DIR_PATH."ckfinder/";		//CKFinderのベースパス設定
		$this->media_basepath = "/".Config::BASE_DIR_PATH."file/";										//メディアベースパスを設定
		$this->media_thumbs_path = "../".Config::ADMIN_DIR_PATH."_thumbs";								//メディアサムネイル格納場所設定
		$this->content = "";																			//デフォルトのエディタコンテンツ設定
		$this->width = "100%"; 																			//エディタの横幅設定
		$this->height = "300px"; 																		//エディタの横幅設定
		$this->formName = "editor1"; 																	//フォームのname属性設定
		$this->editor_id = "editor1"; 																	//エディタID設定
		$this->media_dir_path = "";																		//メディアファイルのディレクトリパス設定
		$this->css = array(); 																			//スタイルシート設定

		$this->addCss("/".Config::BASE_DIR_PATH."webadmin/css/editor.css"); 							//スタイルシート設定
	}

	/**
	 * 店舗IDをに基づき、使用するディレクトリを設定する
	 * @param string $tenpo_id 店舗ID
	 */
	function setTenpoId($tenpo_id){
		$this->setMediaDirPath($tenpo_id."/");
	}
}
?>