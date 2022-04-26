<?php
require_once(dirname(__FILE__).'/../Framework/MailBase.php'); //ベースクラス読み込み

/*
説明：メール送信処理クラス
作成日：2013/10/16 TS谷
*/


/**
 * メール送信処理クラス<br>
 * ・複数宛先/添付ファイル/文字列置換などに対応
 * @author Tani
 *
 */
class Mail extends MailBase
{

	/**
	 * メール送信処理クラスのコンストラクタ
	 */
	function __construct()
	{
		//親クラスのコンストラクタ呼び出し
		parent::__construct();

		//メール送信処理の初期設定値を記載します
		$this->setFrom("");
		$this->setFromName("");

	}

}

?>