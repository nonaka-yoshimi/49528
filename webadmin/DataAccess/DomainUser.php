<?php
require_once(dirname(__FILE__).'/../Framework/DataAccessBase.php');
/*
 説明：ドメイン-ユーザ紐付クラス
作成日：2014/1/8 TS谷
*/

/**
 * ドメイン-ユーザ紐付クラス
 */
class DomainUser extends DataAccessBase
{

	/**
	 * フォルダ-ユーザ紐付コンストラクタ
	 */
	function __construct(){
		parent::__construct();

		//接続先のテーブル名を設定してください
		$this->setTableName("domain_user");

		//接続先テーブルの主キーを設定してください（複数設定可）
		$this->setPrimaryKey("domain_user_id");
	}

	/**
	 * ドメイン-ユーザ紐付一覧を取得する
	 * @return Ambigous <クエリ実行結果, boolean>
	 */
	function getListByDomainId($domain_id){
		$sql = "SELECT ";
		$sql.= 		"user.user_id, ";
		$sql.= 		"user.name, ";
		$sql.= 		"contentauth.contentauth_id, ";
		$sql.= 		"domain_user.domain_user_id, ";
		$sql.= 		"domain_user.domain_id, ";
		$sql.= 		"domain_user.start_time, ";
		$sql.= 		"domain_user.end_time ";
		$sql.= "FROM domain_user ";
		$sql.= "INNER JOIN contentauth ON domain_user.contentauth_id = contentauth.contentauth_id ";		//コンテンツ操作権限(1:1)
		$sql.= 		"AND contentauth.active_flg = '1' ";
		$sql.= 		"AND contentauth.con_auth_domain = '1' ";
		$sql.= "INNER JOIN user ON domain_user.user_id = user.user_id ";										//ユーザ(1:1)
		$sql.= 		"AND user.active_flg = '1' ";
		$sql.= "WHERE domain_user.domain_id = ? ";
		$param[] = $domain_id;
		$sql.= "ORDER BY user.name ASC ";

		$result = $this->query($sql,$param);
		return $result;
	}
}
