<?php
require_once(dirname(__FILE__).'/DynamicConfig.php'); //動的(DB)設定クラス読み込み
/*
 説明：環境依存設定クラス
作成日：2013/11/22 TS谷
*/

/**
 * 環境依存設定クラス
 * 環境に依存するアプリケーションの各設定を行う
 */
class EnvConfig extends DynamicConfig
{
	//パス設定 開始

	/**
	 * デフォルトドメイン
	 */
	//const DEFAULT_DOMAIN = 'localhost';
	const DEFAULT_DOMAIN = '49528.local';
	/**
	 * サイトのベースドメイン
	 */
	//const SITE_BASE_DOMAIN = 'http://localhost/';
	const SITE_BASE_DOMAIN = 'http://www.49528.local/';
	/**
	 * サイトのベースドメイン(SSL)
	 */
	//const SITE_BASE_DOMAIN_SSL = 'https://localhost/';
	const SITE_BASE_DOMAIN_SSL = 'http://www.49528.local/';
	/**
	 * ローカルホストアクセス用URL
	 */
	const LOCAL_HOST_ADDRESS = 'http://www.49528.local/';

	/**
	 * ローカルホストアクセス時BASIC認証用設定
	 */
	const BASIC_AUTHORIZATION_USER = "";

	/**
	 * ローカルホストアクセス時BASIC認証用設定
	 */
	const BASIC_AUTHORIZATION_PASSWORD = "";

	/**
	 * サイトのベースディレクトリパス
	 */
	const BASE_DIR_PATH = '';

	/**
	 * 管理画面へのディレクトリパス
	 */
	const ADMIN_DIR_PATH = 'webadmin/';

	/**
	 * 添付ファイル格納フォルダへのパス
	 */
	const ATTACH_DIR_PATH = 'attach/';

	/**
	 * 一時ファイル格納フォルダへのパス
	 */
	const TMP_DIR_PATH = 'tmp/';

	/**
	 * CSVファイル格納フォルダへのパス
	 */
	const CSV_DIR_PATH = 'csv/';

	/**
	 * コンテンツ格納フォルダへのパス
	 */
	const CONTENT_DIR_PATH = 'content/';

	/**
	 * 拡張機能拡張フォルダへのパス
	 */
	const EXT_DIR_PATH = 'extmodule/';

	/**
	 * ローカルドメイン名
	 */
	const LOCAL_DOMAIN_NAME = 'local';

	/**
	 * NotFoundシステムファイル名
	 */
	const NOT_FOUND_FILE = 'notfound.php';

}