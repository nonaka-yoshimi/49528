<?php
/*
説明：定数定義クラス
作成日：2013/05/12 TS谷
*/

/**
 * 共通定数定義クラス
 */
class SPConst
{
	//ApplicationCommmon用共通定数 開始

	/**
	 * ログイン結果コード：ログイン成功
	 */
	const LOGIN_RESULT_OK = '1';

	/**
	 * ログイン結果コード：ログイン失敗(ID空欄)
	 */
	const LOGIN_RESULT_NG_ID_EMPTY = '2';

	/**
	 * ログイン結果コード：ログイン失敗(パスワード空欄)
	 */
	const LOGIN_RESULT_NG_PASSWORD_EMPTY = '3';

	/**
	 * ログイン結果コード：ログイン失敗(ID,パスワード不一致)
	 */
	const LOGIN_RESULT_NG_MISMATCH_IDPASS = '4';

	/**
	 * ログイン結果コード：ログイン失敗(ユーザ不在)
	 */
	const LOGIN_RESULT_NG_USER_NOT_EXIST = '5';

	/**
	 * ログイン結果コード：ログイン失敗(無効)
	 */
	const LOGIN_RESULT_NG_UNAVAILABLE = '6';

	/**
	 * ログイン結果コード：ログイン失敗(有効期限開始前)
	 */
	const LOGIN_RESULT_NG_BEFORE_AVAILABLE = '7';

	/**
	 * ログイン結果コード：ログイン失敗(有効期限開始後)
	 */
	const LOGIN_RESULT_NG_AFTER_AVAILABLE = '8';

	/**
	 * ログイン結果コード：ログイン失敗(所属組織未存在)
	 */
	const LOGIN_RESULT_NG_ORGANIZE_NOT_EXIST = '9';

	/**
	 * ログアウト結果コード：ログアウト成功
	 */
	const LOGOUT_RESULT_OK = '1';

	/**
	 * ログアウト結果コード：ログアウト失敗
	 */
	const LOGOUT_RESULT_NG = '2';

	/**
	 * ページ認証区分：通常(ログイン不要ページ)
	 */
	const PAGE_AUTH_TYPE_NO = '0';

	/**
	 * ページ認証区分：ログイン(ログイン有ページ)
	 */
	const PAGE_AUTH_TYPE_YES = '1';

	//ApplicationCommon用共通定数 終了

	//アプリケーション個別利用定数 開始
	//定数を追加する場合、こちらに記載してください

	/**
	 * 使用言語：日本語
	 */
	const LANGUAGE_JP = "日本語";

	/**
	 * 使用言語：英語
	 */
	const LANGUAGE_EN = "英語";

	/**
	 * コンテンツクラス：ページ
	 */
	const CONTENTCLASS_PAGE = "page";

	/**
	 * コンテンツクラス：部品
	 */
	const CONTENTCLASS_PARTS = "parts";

	/**
	 * コンテンツクラス：イメージ
	 */
	const CONTENTCLASS_IMAGE = "image";

	/**
	 * コンテンツクラス：ファイル
	 */
	const CONTENTCLASS_FILE = "file";

	/**
	 * コンテンツクラス：テンプレート
	 */
	const CONTENTCLASS_TEMPLATE = "template";

	/**
	 * コンテンツクラス：スタイルシート
	 */
	const CONTENTCLASS_STYLESHEET = "stylesheet";

	/**
	 * コンテンツクラス：スクリプト
	 */
	const CONTENTCLASS_SCRIPT = "script";

	/**
	 * 入力タイプ：１行テキスト(小）
	 */
	const INPUTTYPE_SHORT_TEXT = "1";

	/**
	 * 入力タイプ：１行テキスト(中）
	 */
	const INPUTTYPE_MIDDLE_TEXT = "2";

	/**
	 * 入力タイプ：１行テキスト(大）
	 */
	const INPUTTYPE_LONG_TEXT = "3";

	/**
	 * 入力タイプ：テキストエリア(小）
	 */
	const INPUTTYPE_SMALL_TEXTAREA = "4";

	/**
	 * 入力タイプ：テキストエリア(中）
	 */
	const INPUTTYPE_MIDDLE_TEXTAREA = "5";

	/**
	 * 入力タイプ：テキストエリア(大）
	 */
	const INPUTTYPE_LARGE_TEXTAREA = "6";

	/**
	 * 入力タイプ：CKEditor
	 */
	const INPUTTYPE_CKEDITOR = "7";

	/**
	 * 入力タイプ：日付
	 */
	const INPUTTYPE_DATE = "8";

	/**
	 * 入力タイプ：時間
	 */
	const INPUTTYPE_TIME = "9";

	/**
	 * 入力タイプ：日時
	 */
	const INPUTTYPE_DATETIME = "10";

	/**
	 * 入力タイプ：選択式
	 */
	const INPUTTYPE_SELECT = "11";

	/**
	 * 入力タイプ：チェックボックス
	 */
	const INPUTTYPE_CHECKBOX = "12";

	/**
	 * 入力タイプ：コンテンツ
	 */
	const INPUTTYPE_CONTENT = "13";

	/**
	 * 入力タイプ：ページ
	 */
	const INPUTTYPE_PAGE = "14";

	/**
	 * 入力タイプ：部品
	 */
	const INPUTTYPE_ELEMENT = "15";

	/**
	 * 入力タイプ：イメージ
	 */
	const INPUTTYPE_IMAGE = "16";

	/**
	 * 入力タイプ：ファイル
	 */
	const INPUTTYPE_FILE = "17";


	/**
	 * コンテンツ操作履歴区分：新規追加
	 */
	const OPERATION_HISTORY_NEW = "1";

	/**
	 * コンテンツ操作履歴区分：変更
	 */
	const OPERATION_HISTORY_EDIT = "2";

	/**
	 * コンテンツ操作履歴区分：削除
	 */
	const OPERATION_HISTORY_DELETE = "3";


	/**
	 * スケジュール区分：公開中のコンテンツと差替える
	 */
	const SCHEDULE_TYPE_REPLACE = "1";

	/**
	 * スケジュール区分：一時的に公開する
	 */
	const SCHEDULE_TYPE_TEMPORARY = "2";


	/**
	 * アーカイブ区分：手動
	 */
	const ARCHIVE_TYPE_MANUAL = "1";

	/**
	 * アーカイブ区分：公開時に自動
	 */
	const ARCHIVE_TYPE_PUBLISH = "2";

	/**
	 * アーカイブ区分：保存時に自動
	 */
	const ARCHIVE_TYPE_SAVE = "3";

	/**
	 * エディタモード：デフォルト
	 */
	const EDIT_MODE_DEFAULT = "0";

	/**
	 * エディタモード：テキストエディタ
	 */
	const EDIT_MODE_TEXTAREA = "1";

	/**
	 * エディタモード：CKEditor
	 */
	const EDIT_MODE_CKEDITOR = "2";


	/**
	 * 動的/静的ページ：動的ページとして作成
	 */
	const STATIC_MODE_DYNAMIC = "0";

	/**
	 * 動的/静的ページ：静的ページとして作成
	 */
	const STATIC_MODE_STATIC = "1";


	/**
	 * PHP動作モード：動作させない
	 */
	const PHP_MODE_NO = "0";

	/**
	 * PHP動作モード：動作させる
	 */
	const PHP_MODE_YES = "1";


	/**
	 * 管理者フラグ：非管理者
	 */
	const ADMIN_FLG_NO = "0";

	/**
	 * 管理者フラグ：管理者
	 */
	const ADMIN_FLG_YES = "1";

	/**
	 * 管理者フラグ：一般管理者
	 */
	const ADMINTYPE_NORMAL = "0";

	/**
	 * 管理者フラグ：システム管理者
	 */
	const ADMINTYPE_SUPERVISOR = "1";


	/**
	 * 制限種別：有効/無効
	 */
	const RESTRICT_TYPE_ACTIVE_NONACTIVE = "0";

	/**
	 * 制限種別：操作レベル
	 */
	const RESTRICT_TYPE_OPERATION_LEVEL = "1";

	/**
	 * 入力制限：非表示
	 */
	const RESTRICT_NONE = "0";

	/**
	 * 入力制限：読取専用
	 */
	const RESTRICT_READONLY = "1";

	/**
	 * 入力制限：入力可
	 */
	const RESTRICT_ENABLE = "2";

	/**
	 * 操作制限：無効
	 */
	const RESTRICT_NONACTIVE = "0";

	/**
	 * 操作制限：有効
	 */
	const RESTRICT_ACTIVE = "1";


	/**
	 * ユーザ有効状態：無効
	 */
	const USER_NONACTIVE = "0";

	/**
	 * ユーザ有効状態：無効
	 */
	const USER_ACTIVE = "1";

	/**
	 * ワークフロー実行可能ユーザ制限：全て
	 */
	const WORKFLOW_USER_RESTRICTION_ALL = "0";

	/**
	 * ワークフロー実行可能ユーザ制限：起案者のみ
	 */
	const WORKFLOW_USER_RESTRICTION_DRAFTER_ONLY = "1";

	/**
	 * ワークフロー実行可能ユーザ制限：起案者のみ
	 */
	const WORKFLOW_USER_RESTRICTION_EXCEPT_DRAFTER = "2";

	//アプリケーション個別利用定数 終了
}
?>