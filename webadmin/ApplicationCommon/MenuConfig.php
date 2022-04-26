<?php
/*
 説明：メニュー設定クラス
作成日：2013/11/18 TS谷
*/

/**
 * メニュー設定クラス
 * アプリケーションのメニュー設定を取得する
 */
class MenuConfig
{
	/**
	 * メニュー設定格納用
	 */
	var $menu_config = array();

	/**
	 * メニュー設定取得
	 */
	function __construct(){
		//ワードライブラリ取得
		$wordlib = new Word();

		//トップメニュー設定
		$this->menu_config["top"]["menutitle"] = $wordlib->get("TOP");											//メニュータイトル設定
		$this->menu_config["top"]["url"] = "index.php";															//メニューURL設定

		//コンテンツ管理メニュー設定
		$this->menu_config["content_management"]["menutitle"] = $wordlib->get("CONTENT_MANAGEMENT");			//メニュータイトル設定
		$this->menu_config["content_management"]["url"] = "management/index.php";								//メニューURL設定

		//検索メニュー設定
		$this->menu_config["search"]["menutitle"] = $wordlib->get("CONTENT_SEARCH");							//メニュータイトル設定
		$this->menu_config["search"]["url"] = "management/search.php";											//メニューURL設定

		//ユーザ管理メニュー設定
		$this->menu_config["user"]["menutitle"] = $wordlib->get("USER_MANAGEMENT");								//メニュータイトル設定
		$this->menu_config["user"]["url"] = "user/index.php";													//メニューURL設定

		//個人アカウントメニュー設定
		$this->menu_config["account"]["menutitle"] = $wordlib->get("INDIVIDUAL_ACCOUNT");						//メニュータイトル設定
		$this->menu_config["account"]["url"] = "account/index.php";												//メニューURL設定

		//設定メニュー設定
		$this->menu_config["setting"]["menutitle"] = $wordlib->get("SETTING");									//メニュータイトル設定
		$this->menu_config["setting"]["url"] = "setting/index.php";												//メニューURL設定
	}

	function get(){
		return $this->menu_config;
	}
}