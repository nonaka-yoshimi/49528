<?php
/*
説明：データベース接続設定クラス
作成日：2013/05/12 TS谷
*/

/**
 * データベース接続設定クラス
 */
class DBConfig
{
	//データベース接続設定（本番環境） 開始

	/**
	 * データベースDSN
	 */
	//const DSN = "mysql:host=127.0.0.1; dbname=speedcms";
	const DSN = "mysql:host=127.0.0.1; dbname=49528";
	/**
	 * データベース：ユーザ名
	 */
	const USER = "root";

	/**
	 * データベース：パスワード
	 */
	const PASSWORD = "";

	/**
	 * 使用データベース種別 1:MySQL 2:Oracle 3:SQLServer
	 */
	const DATABASE = "1";

	//データベース接続設定 終了
}

?>