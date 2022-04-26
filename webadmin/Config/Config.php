<?php
require_once(dirname(__FILE__).'/EnvConfig.php'); //環境依存設定クラス読み込み
/*
 説明：設定クラス
作成日：2013/10/17 TS谷
*/

/**
 * 設定クラス
 * アプリケーションの各設定を取得する
 */
class Config extends EnvConfig
{
	/**
	 * extmodule読込方法
	 */
	//const EXTMODULE_METHOD = "file_get_contents";
	const EXTMODULE_METHOD = "include";

	/**
	 * 静的ファイル管理設定
	 */
	const FILE_MANAGEMENT = false;

	//静的ファイルブラウザキャッシュ設定 開始
	/**
	 * 静的ファイルブラウザキャッシュ設定
	 */
	const STATIC_FILE_BROWSER_CACHE = false;

	//静的ファイルブラウザキャッシュ設定 終了



	//置換識別子設定 開始

	/**
	 * データ置換識別子設定
	 */
	//const REPLACE_MARK = "";

	/**
	 * データ置換識別子設定(開始)
	 */
	const REPLACE_MARK_START = "{{{";

	/**
	 * データ置換識別子設定(開始)
	 */
	const REPLACE_MARK_START_ESCAPE = "\{\{\{";

	/**
	 * データ置換識別子設定(終了)
	 */
	const REPLACE_MARK_END = "}}}";

	/**
	 * データ置換識別子設定(終了)
	 */
	const REPLACE_MARK_END_ESCAPE = "\}\}\}";

	/**
	 * パラメータ置換識別子設定
	 */
	//const REQUEST_MARK = "###";

	/**
	 * パラメータ置換識別子設定(開始)
	 */
	const REQUEST_MARK_START = "###";

	/**
	 * パラメータ置換識別子設定(開始)
	 */
	const REQUEST_MARK_START_ESCAPE = "###";

	/**
	 * パラメータ置換識別子設定(終了)
	 */
	const REQUEST_MARK_END = "###";

	/**
	 * パラメータ置換識別子設定(終了)
	 */
	const REQUEST_MARK_END_ESCAPE = "###";

	/**
	 * ヘッダ一時置換識別子設定(開始)
	 */
	const HEAD_CODE_REPLACE_MARK_START = "@@@";

	/**
	 * ヘッダ一時置換識別子設定(終了)
	 */
	const HEAD_CODE_REPLACE_MARK_END = "@@@";

	//置換識別子設定 終了

	//デフォルト言語設定 開始

	/**
	 * デフォルト言語設定
	 */
	const DEFAULT_LANGUAGE = "jp";

	//デフォルト言語設定 終了

	//パス設定 終了

	//ログイン認証関連設定 開始

	/**
	 * ログイン認証用MAGIC CODE(DB格納パスワード生成用)
	 */
	const LOGIN_MAGIC_CODE = 'login_magic';

	//ログイン認証関連設定 終了

	//セッション認証関連設定 開始

	/**
	 * セッション有効時間（秒）
	 */
	const SESSION_LIFETIME = 3600;

	/**
	 * セッションクッキー有効時間（秒）
	 */
	const SESSION_COOKIE_LIFETIME = 31536000;

	/**
	 * アクセス識別子用セッション名称
	 */
	const SESSION_ACCESS_IDENTCODE = 'accessidentcode';

	/**
	 * アクセス識別子生成用MAGIC CODE
	 */
	const SESSION_ACCESS_IDENTCODE_MAGIC_CODE = 'magic_code';

	/**
	 * ユーザ認証コード格納先セッション名称
	 */
	const SESSION_USER_AUTH_NAME = 'session_user_auth';

	/**
	 * ユーザログインID格納先セッション名称
	 */
	const SESSION_USER_LOGIN_ID_NAME = 'session_user_login_id';

	/**
	 * ログイン認証コード用マジックコード
	 */
	const SESSION_LOGIN_AUTH_MAGIC_CODE = 'magic_code';

	/**
	 * 最終操作時間セッション名称
	 */
	const SESSION_LAST_OPERATION_TIME_NAME = 'session_last_operation_time';

	//セッション認証関連設定 終了

	//ファイル関連設定 開始

	/**
	 * 一時ファイルの保持時間(秒）
	 */
	const TMP_FILE_LIFETIME = 86400;

	/**
	 * アップロード可能な最大ファイル容量(1件)（byte)
	 */
	const UPLOAD_FILE_MAX_SIZE = 10485760;

	/**
	 * アップロード可能な最大ファイル容量(トータル)（byte)
	 */
	const UPLOAD_FILE_TOTAL_MAX_SIZE = 15728640;

	//ファイル関連設定 終了

	//文字コード関連設定 開始

	/**
	 * システム標準使用エンコード
	 */
	const DEFAULT_ENCODE = 'UTF-8';

	/**
	 * システム標準変換元エンコード
	 */
	const DEFAULT_ENCODE_FROM = 'UTF-8,EUC-JP,SJIS';

	//文字コード関連設定 終了

	//ページ制御設定 開始

	/**
	 * 標準リスト件数
	 */
	const DEFAULT_PAGE_LIST_NUM = 20;

	//ページ制御設定 終了

	//入力制御設定 開始

	/**
	 * ログインID最小文字数
	 */
	const LOGIN_ID_MIN_CHAR_NUM = 3;

	/**
	 * ログインID最大文字数
	 */
	const LOGIN_ID_MAX_CHAR_NUM = 20;

	/**
	 * パスワード最大文字数
	 */
	const PASSWORD_MIN_CHAR_NUM = 3;

	/**
	 * パスワード最大文字数
	 */
	const PASSWORD_MAX_CHAR_NUM = 20;

	//入力制御設定 終了

	//アドオン設定 開始

	/**
	 * アドオン機能設定可能数
	 */
	const ADDON_MODULE_NUM = "20";

	//アドオン設定 終了

}