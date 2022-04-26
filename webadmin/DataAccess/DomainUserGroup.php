<?php
require_once(dirname(__FILE__).'/../Framework/DataAccessBase.php');
/*
 説明：ドメイン-ユーザグループ紐付クラス
作成日：2014/1/8 TS谷
*/

/**
 * ドメイン-ユーザグループ紐付クラス
 */
class DomainUserGroup extends DataAccessBase
{

	/**
	 * ドメイン-ユーザグループ紐付コンストラクタ
	 */
	function __construct(){
		parent::__construct();

		//接続先のテーブル名を設定してください
		$this->setTableName("domain_usergroup");

		//接続先テーブルの主キーを設定してください（複数設定可）
		$this->setPrimaryKey("domain_usergroup_id");
	}

	/**
	 * ドメイン-ユーザグループ紐付一覧を取得する
	 * @return Ambigous <クエリ実行結果, boolean>
	 */
	function getListByDomainId($domain_id){
		$sql = "SELECT ";
		$sql.= 		"usergroup.usergroup_id, ";
		$sql.= 		"usergroup.usergroup_name, ";
		$sql.= 		"contentauth.contentauth_id, ";
		$sql.= 		"domain_usergroup.domain_usergroup_id, ";
		$sql.= 		"domain_usergroup.domain_id, ";
		$sql.= 		"domain_usergroup.start_time, ";
		$sql.= 		"domain_usergroup.end_time ";
		$sql.= "FROM domain_usergroup ";
		$sql.= "INNER JOIN contentauth ON domain_usergroup.contentauth_id = contentauth.contentauth_id ";		//コンテンツ操作権限(1:1)
		$sql.= 		"AND contentauth.active_flg = '1' ";
		$sql.= 		"AND contentauth.con_auth_domain = '1' ";
		$sql.= "INNER JOIN usergroup ON domain_usergroup.usergroup_id = usergroup.usergroup_id ";				//ユーザグループ(1:1)
		$sql.= 		"AND usergroup.active_flg = '1' ";
		$sql.= "WHERE domain_usergroup.domain_id = ? ";
		$param[] = $domain_id;
		$sql.= "ORDER BY usergroup.usergroup_name ASC ";

		$result = $this->query($sql,$param);
		return $result;
	}
}
