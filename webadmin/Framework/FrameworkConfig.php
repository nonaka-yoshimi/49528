<?php
/*
 説明：フレームワーク設定クラス
作成日：2013/11/16 TS谷
*/

/**
 * 設定クラス<br>
 * フレームワークの各設定を取得する
 */
class FrameworkConfig
{
	/**
	 * ログファイル出力パス設定
	 */
	const LogOutPath = "../log/";

	/**
	 * デバッグログ出力設定
	 */
	const DebugLog = true;

	/**
	 * エラーログ出力設定
	 */
	const ErrorLog = true;

	/**
	 * 情報ログ出力設定
	 */
	const InformationLog = true;

	/**
	 * 注意ログ出力設定
	 */
	const NoticeLog = true;

	/**
	 * デバッグログトレース(詳細処理経路表示）設定
	 */
	const DebugTrace = false;

	/**
	 * エラーログトレース(詳細処理経路表示）設定
	 */
	const ErrorTrace = true;

	/**
	 * 注意ログトレース(詳細処理経路表示）設定
	 */
	const NoticeTrace = true;

}