<?php
require_once(dirname(__FILE__).'/../Framework/DataAccessBase.php');
/*
 説明：ユーザ-ユーザグループ紐付クラス
作成日：2014/1/5 TS谷
*/

/**
 * ユーザ-ユーザグループ紐付クラス
 */
class UserUserGroup extends DataAccessBase
{

	/**
	 * ユーザ-ユーザグループ紐付コンストラクタ
	 */
	function __construct(){
		parent::__construct();

		//接続先のテーブル名を設定してください
		$this->setTableName("user_usergroup");

		//接続先テーブルの主キーを設定してください（複数設定可）
		$this->setPrimaryKey("user_usergroup_id");
	}

}
