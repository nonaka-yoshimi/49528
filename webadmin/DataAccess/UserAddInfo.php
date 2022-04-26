<?php
/*
 説明：ユーザ追加情報クラス
作成日：2014/91/2 TS半田
*/
require_once(dirname(__FILE__).'/../Framework/DataAccessBase.php');

/**
 * ユーザグループクラス
 */
class UserAddInfo extends DataAccessBase
{

	/**
	 * ユーザグループコンストラクタ
	 */
	function __construct(){
		parent::__construct();

		//接続先のテーブル名を設定してください
		$this->setTableName("user_addinfo");

		//接続先テーブルの主キーを設定してください（複数設定可）
		$this->setPrimaryKey("user_addinfo_id");
	}

}
