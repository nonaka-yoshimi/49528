<?php
require_once(dirname(__FILE__).'/../Framework/DataAccessBase.php');
/*
 説明：ユーザグループ種別クラス
作成日：2013/12/27 TS谷
*/

/**
 * ユーザグループ種別クラス
 */
class UserGroupType extends DataAccessBase
{

	/**
	 * ユーザグループ種別コンストラクタ
	 */
	function __construct(){
		parent::__construct();

		//接続先のテーブル名を設定してください
		$this->setTableName("usergrouptype");

		//接続先テーブルの主キーを設定してください（複数設定可）
		$this->setPrimaryKey("usergrouptype_id");
	}

	/**
	 * ユーザグループ種別一覧を取得する
	 * @return Ambigous <クエリ実行結果, boolean>
	 */
	function getUserGroupTypeList(){
		$sql = "SELECT ";
		$sql.= 		"usergrouptype.usergrouptype_id, ";
		$sql.= 		"usergrouptype.usergrouptype_name ";
		$sql.= "FROM usergrouptype ";
		$sql.= "WHERE usergrouptype.active_flg = '1' ";
		$sql.= "ORDER BY usergrouptype.sort_no ASC ";

		$result = $this->query($sql);
		return $result;
	}

	/**
	 * マスタ設定用ユーザグループ種別リストを取得する
	 * @return Ambigous <クエリ実行結果, boolean>
	 */
	function getListForSetting(){
		$sql = "SELECT ";
		$sql.= 		"usergrouptype.updated, ";
		$sql.= 		"usergrouptype.usergrouptype_id, ";
		$sql.= 		"usergrouptype.usergrouptype_name ";
		$sql.= "FROM usergrouptype ";
		$sql.= "ORDER BY usergrouptype.sort_no ASC ";

		$result = $this->query($sql);
		return $result;
	}
}
