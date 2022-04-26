<?php
require_once(dirname(__FILE__).'/../ApplicationCommon/Session.php'); 	//セッションクラス
require_once(dirname(__FILE__).'/../Config/Config.php'); 				//設定クラス
require_once(dirname(__FILE__).'/../ApplicationCommon/Debug.php'); 		//デバッグクラス
require_once(dirname(__FILE__).'/Mobile_Detect.php'); 					//モバイル判定クラス
/*
説明：CMSエンジンクラス
作成日：2013/11/28 TS谷
*/

/**
 * CMSエンジンクラス
 * サーバアクセスに基づきページ/ファイルをレスポンスする。
 */
class CMSEngine
{
	/**
	 * ドメイン
	 */
	var $domain = "";

	/**
	 * アクセスURL
	 */
	var $url = "";

	/**
	 * 動作モード
	 */
	var $mode = "";

	/**
	 * 管理時アクセスURL
	 */
	var $management_url = "";

	/**
	 * 拡張子
	 */
	var $extension = "";

	/**
	 * セッション
	 */
	var $session = null;

	/**
	 * デバイス
	 */
	var $device = null;

	/**
	 * ファイル設定
	 */
	var $config = array();

	/**
	 * CMSエンジンコンストラクタ
	 */
	function __construct($domain){
		//URL/拡張子取得処理
		$this->parseUrl();
		//デバイス情報取得
		$this->parseDevice();


		//GETリクエスト再格納
		$this->convGetRequest();
		//セッション設定
		$this->session = Session::get();

		//プレビューモード取得
		$this->parseMode();
		//ドメイン設定
		/*
		if($this->session->webadmin_login_state){
			$this->domain = $this->session->domain;
		}else{
			$this->domain = $domain;
		}
		*/
		$this->domain = $domain;
		//ファイル設定取得
		$this->setCMSConfig();
	}

	/**
	 * URL/拡張子取得処理
	 */
	protected function parseUrl(){
		//URL設定
		$this->url = isset($_GET["url"]) ? $_GET["url"] : "";
		if($this->url == ""){
			$this->url = "index.html";
		}
		if(preg_match("/\/$/",$this->url)){
			$this->url = $this->url."index.html";
		}

		//ファイル名分割
		$filename_arr = explode(".",$this->url);
		if(count($filename_arr) < 2){ return; }

		//拡張子設定
		$this->extension =  strtolower($filename_arr[count($filename_arr) - 1]);
	}

	/**
	 * デバイス取得処理
	 */
	protected function parseDevice(){
		$detect = new Mobile_Detect;
		if($detect->isMobile()){
			$this->device = "mobile";
		}elseif($detect->isTablet()){
			$this->device = "tablet";
		}else{
			$this->device = "pc";
		}
	}

	/**
	 * プレビューモード取得処理
	 */
	protected function parseMode(){
		if(!$this->session->webadmin_login_state){
			return;
		}
		//動作モード設定
		if(isset($_GET["mode"])){
			if($_GET["mode"] == "preview"){
				$this->session->setMode($_GET["mode"]);
				$this->mode = $this->session->getMode();
			}elseif($_GET["mode"] == "onetime_preview"){
				$this->mode = "preview";
			}else{
				$this->session->setMode("");
				$this->mode = "";
			}
		}else{
			$this->mode = $this->session->getMode();
		}
	}

	/**
	 * GETリクエスト再格納
	 */
	protected function convGetRequest(){
		//リクエストURIを取得
		$request_uri = $_SERVER['REQUEST_URI'];

		//?で分割
		$exploded_uri = explode("?", $request_uri);
		if(count($exploded_uri) < 2){
			return;
		}

		//パラメータ部取得
		$param_str = "";
		if(count($exploded_uri) > 2){
			for($i=1;$i<count($exploded_uri);$i++){
				if($param_str != ""){ $param_str .= "?"; }
				$param_str .= $exploded_uri[$i];
			}
		}else{
			$param_str = $exploded_uri[1];
		}

		//&で分割
		$exploded_param = explode("&", $param_str);
		foreach($exploded_param as $key_value){
			//=で分割
			$exploded_key_value = explode("=", $key_value);
			if(count($exploded_key_value) == 2){
				//GETパラメータ再格納
				$_GET[$exploded_key_value[0]] = urldecode($exploded_key_value[1]);
				$_REQUEST[$exploded_key_value[0]] = $_GET[$exploded_key_value[0]];
			}
		}
	}

	/**
	 * アウトプット
	 */
	function output(){
		//拡張子設定取得
		require_once(dirname(__FILE__).'/FileExtensionConfig.php');
		$pageExtensionList = FileExtensionConfig::getPageExtention();		//ページ拡張子一覧
		$imageExtensionList = FileExtensionConfig::getImageExtention();		//イメージ拡張子一覧
		$fileExtensionList = FileExtensionConfig::getFileExtention();		//ファイル拡張子一覧
		$cssExtensionList = FileExtensionConfig::getCssExtention();			//スタイルシート拡張子一覧
		$scriptExtensionList = FileExtensionConfig::getScriptExtention();	//スクリプト拡張子一覧

		if(!$this->mode){
			//未ログインの場合
			if(array_key_exists($this->extension, $pageExtensionList)){
				//ページの場合
				$extensionInfo = $pageExtensionList[$this->extension];			//拡張子情報取得
				if($this->outputPublicFile($extensionInfo)){ 					//公開ファイル出力
				}elseif($this->outputPublicDBContent($extensionInfo)){			//公開DBコンテンツ出力
				}else{ $this->outputNotFound(); }								//NOT FOUNDページ出力
			}elseif(array_key_exists($this->extension, $imageExtensionList)){
				//イメージの場合
				$extensionInfo = $imageExtensionList[$this->extension];			//拡張子情報取得
				if($this->outputPublicFile($extensionInfo)){ 					//公開ファイル出力
				}else{ $this->outputNotFound(); }								//NOT FOUNDページ出力
			}elseif(array_key_exists($this->extension, $fileExtensionList)){
				//ファイルの場合
				$extensionInfo = $fileExtensionList[$this->extension];			//拡張子情報取得
				if($this->outputPublicFile($extensionInfo)){ 					//公開ファイル出力
				}else{ $this->outputNotFound(); }								//NOT FOUNDページ出力
			}elseif(array_key_exists($this->extension, $cssExtensionList )){
				//スタイルシートの場合
				$extensionInfo = $cssExtensionList[$this->extension];			//拡張子情報取得
				if($this->outputPublicFile($extensionInfo)){ 					//公開ファイル出力
				}elseif($this->outputPublicDBCss($extensionInfo)){				//公開DBスタイルシート出力
				}else{ $this->outputNotFound(); }								//NOT FOUNDページ出力
			}elseif(array_key_exists($this->extension, $scriptExtensionList )){
				//スクリプトの場合
				$extensionInfo = $scriptExtensionList[$this->extension];		//拡張子情報取得
				if($this->outputPublicFile($extensionInfo)){ 					//公開ファイル出力
				}elseif($this->outputPublicDBScript($extensionInfo)){			//公開DBコンテンツ出力
				}else{ $this->outputNotFound(); }								//NOT FOUNDページ出力
			}else{
				$this->outputNotFound(); //NOT FOUNDページ出力
			}
		}else{
			//ログイン済みの場合
			//管理時URLを格納する
			/*
			if(Config::FILE_MANAGEMENT){
				$this->management_url = Config::ADMIN_DIR_PATH.Config::CONTENT_DIR_PATH.$this->domain."/".$this->url;
			}else{
				$this->management_url = $this->url;
			}
			*/
			$this->management_url = $this->url;
			if(array_key_exists($this->extension, $pageExtensionList)){
				//ページの場合
				$extensionInfo = $pageExtensionList[$this->extension];			//拡張子情報取得
				if($this->outputManagementFile($extensionInfo)){ 				//管理ファイル出力
				}elseif($this->outputManagementDBContent($extensionInfo)){		//管理DBコンテンツ出力
				}else{ $this->outputNotFound(); }								//NOT FOUNDページ出力
			}elseif(array_key_exists($this->extension, $imageExtensionList)){
				//イメージの場合
				$extensionInfo = $imageExtensionList[$this->extension];			//拡張子情報取得
				if($this->outputManagementFile($extensionInfo)){ 				//管理イメージ出力
				}else{ $this->outputNotFound(); }								//NOT FOUNDページ出力
			}elseif(array_key_exists($this->extension, $fileExtensionList)){
				//ファイルの場合
				$extensionInfo = $fileExtensionList[$this->extension];			//拡張子情報取得
				if($this->outputManagementFile($extensionInfo)){ 				//管理ファイル出力
				}else{ $this->outputNotFound(); }								//NOT FOUNDページ出力
			}elseif(array_key_exists($this->extension, $cssExtensionList)){
				//スタイルシートの場合
				$extensionInfo = $cssExtensionList[$this->extension];			//拡張子情報取得
				if($this->outputManagementFile($extensionInfo)){ 				//管理ファイル出力
				}elseif($this->outputManagementDBCss($extensionInfo)){		//管理DBコンテンツ出力
				}else{ $this->outputNotFound(); }								//NOT FOUNDページ出力
			}elseif(array_key_exists($this->extension, $scriptExtensionList)){
				//スクリプトの場合
				$extensionInfo = $scriptExtensionList[$this->extension];		//拡張子情報取得
				if($this->outputManagementFile($extensionInfo)){ 				//管理ファイル出力
				}elseif($this->outputManagementDBScript($extensionInfo)){		//管理DBコンテンツ出力
				}else{ $this->outputNotFound(); }								//NOT FOUNDページ出力
			}else{
				$this->outputNotFound(); //NOT FOUNDページ出力
			}
		}
	}

	/**
	 * ディレクトリ上の設定ファイルから、ファイル挙動に関する設定を取得する
	 * @param string URL
	 */
	protected function setCMSConfig(){
		$pathinfo = pathinfo($this->url);

		if(file_exists($pathinfo["dirname"]."/.cmsconfig")){
			$configList = include($pathinfo["dirname"]."/.cmsconfig");
			if(isset($configList[$pathinfo["basename"]])){
				$config = $configList[$pathinfo["basename"]];			//個別ファイル設定
			}
		}else{
			$config = array();
		}
		$config["filename"] = $pathinfo["basename"];					//ファイル名
		$config["dirname"] = $pathinfo["dirname"];						//ディレクトリ名
		$this->config = $config;
	}

	/**
	 * 公開ファイルを出力
	 */
	protected function outputPublicFile($extensionInfo){
		if(file_exists(dirname(__FILE__)."/../../".$this->url)){ 	//ファイルが実在する場合実在
			require_once(dirname(__FILE__).'/FileEngine.php');									//ファイルエンジン
			$fileEngine = new FileEngine();
			$fileEngine->outputFile(dirname(__FILE__)."/../../".$this->url, $this->session, $this->domain, $this->device, $extensionInfo,$this->config);	//ファイル出力
			return true;
		}else{
			return false;
		}
	}

	/**
	 * 公開DBコンテンツを出力
	 */
	protected function outputPublicDBContent($extensionInfo){
		require_once(dirname(__FILE__).'/ContentEngine.php');												//コンテンツエンジン
		require_once(dirname(__FILE__).'/../ApplicationCommon/dbconnect.php');								//DB接続																						//DB接続
		$contentEngine = new ContentEngine();
		$result = $contentEngine->outputContent($this->url, $this->session, $this->domain, $this->device, $extensionInfo,$this->config);	//コンテンツ出力
		return $result;
	}

	/**
	 * 公開DBスタイルシートを出力
	 */
	protected function outputPublicDBCss($extensionInfo){
		require_once(dirname(__FILE__).'/CSSEngine.php');													//スタイルシートエンジン
		require_once(dirname(__FILE__).'/../ApplicationCommon/dbconnect.php');								//DB接続																						//DB接続
		$cssEngine = new CSSEngine();
		$result = $cssEngine->outputContent($this->url, $this->session, $this->domain, $this->device, $extensionInfo,$this->config);		//コンテンツ出力
		return $result;
	}

	/**
	 * 公開DBスクリプトを出力
	 */
	protected function outputPublicDBScript($extensionInfo){
		require_once(dirname(__FILE__).'/ScriptEngine.php');												//スクリプトエンジン
		require_once(dirname(__FILE__).'/../ApplicationCommon/dbconnect.php');								//DB接続																						//DB接続
		$scriptEngine = new ScriptEngine();
		$result = $scriptEngine->outputContent($this->url, $this->session, $this->domain, $this->device, $extensionInfo,$this->config);		//コンテンツ出力
		return $result;
	}

	/**
	 * 管理ファイルを出力
	 */
	protected function outputManagementFile($extensionInfo){
		if(file_exists(dirname(__FILE__)."/../../".$this->management_url)){ 	//ファイルが実在する場合
			require_once(dirname(__FILE__).'/FileEngine.php');												//ファイルエンジン
			$fileEngine = new FileEngine();
			$fileEngine->outputFile(dirname(__FILE__)."/../../".$this->management_url, $this->session, $this->domain, $this->device, $extensionInfo,$this->config);	//ファイル出力
			return true;
		}else{
			return false;
		}
	}

	/**
	 * 管理DBコンテンツを出力
	 */
	protected function outputManagementDBContent($extensionInfo){
		require_once(dirname(__FILE__).'/ContentEngine.php');												//コンテンツエンジン
		require_once(dirname(__FILE__).'/../ApplicationCommon/dbconnect.php');								//DB接続
		$contentEngine = new ContentEngine();
		$result = $contentEngine->outputContent($this->url, $this->session, $this->domain, $this->device, $extensionInfo,$this->config,true);	//コンテンツ出力
		return $result;
	}

	/**
	 * 管理DBスタイルシートを出力
	 */
	protected function outputManagementDBCss($extensionInfo){
		require_once(dirname(__FILE__).'/CSSEngine.php');													//スタイルシートエンジン
		require_once(dirname(__FILE__).'/../ApplicationCommon/dbconnect.php');								//DB接続
		$cssEngine = new CSSEngine();
		$result = $cssEngine->outputContent($this->url, $this->session, $this->domain, $this->device, $extensionInfo,$this->config,true);	//コンテンツ出力
		return $result;
	}

	/**
	 * 管理DBスクリプトを出力
	 */
	protected function outputManagementDBScript($extensionInfo){
		require_once(dirname(__FILE__).'/ScriptEngine.php');												//スクリプトエンジン
		require_once(dirname(__FILE__).'/../ApplicationCommon/dbconnect.php');								//DB接続
		$scriptEngine = new ScriptEngine();

		$result = $scriptEngine->outputContent($this->url, $this->session, $this->domain, $this->device, $extensionInfo,$this->config,true);	//コンテンツ出力
		return $result;
	}

	/**
	 * NotFoundページ出力
	 */
	protected function outputNotFound(){
		include(dirname(__FILE__)."/../../".Config::NOT_FOUND_FILE);
	}
}
?>