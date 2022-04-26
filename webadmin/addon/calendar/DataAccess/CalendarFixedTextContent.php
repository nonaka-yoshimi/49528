<?php
/*
説明：お知らせコンテンツクラス(アドオン拡張)
作成日：2013/12/1 TS谷
*/
require_once(dirname(__FILE__).'/../../../Framework/DataAccessBase.php');

/**
 * コンテンツクラス
 */
class CalendarFixedTextContent extends DataAccessBase
{

	/**
	 * 追加情報選択肢コンストラクタ
	 */
	function __construct(){
		parent::__construct();

		//接続先のテーブル名を設定してください
		$this->setTableName("add_calendarfixdtext");

		//接続先テーブルの主キーを設定してください（複数設定可）
		$this->setPrimaryKey("id");
	}

}
