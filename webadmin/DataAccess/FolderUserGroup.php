<?php
require_once(dirname(__FILE__).'/../Framework/DataAccessBase.php');
/*
 説明：フォルダ-ユーザグループ紐付クラス
作成日：2013/12/2 TS谷
*/

/**
 * フォルダ-ユーザグループ紐付クラス
 */
class FolderUserGroup extends DataAccessBase
{

	/**
	 * フォルダ-ユーザグループ紐付コンストラクタ
	 */
	function __construct(){
		parent::__construct();

		//接続先のテーブル名を設定してください
		$this->setTableName("folder_usergroup");

		//接続先テーブルの主キーを設定してください（複数設定可）
		$this->setPrimaryKey("folder_usergroup_id");
	}

	/**
	 * フォルダ-ユーザグループ紐付一覧を取得する
	 * @return Ambigous <クエリ実行結果, boolean>
	 */
	function getListByFolderId($folder_id){
		$sql = "SELECT ";
		$sql.= 		"usergroup.usergroup_id, ";
		$sql.= 		"usergroup.usergroup_name, ";
		$sql.= 		"contentauth.contentauth_id, ";
		$sql.= 		"folder_usergroup.folder_usergroup_id, ";
		$sql.= 		"folder_usergroup.folder_id, ";
		$sql.= 		"folder_usergroup.start_time, ";
		$sql.= 		"folder_usergroup.end_time ";
		$sql.= "FROM folder_usergroup ";
		$sql.= "INNER JOIN contentauth ON folder_usergroup.contentauth_id = contentauth.contentauth_id ";		//コンテンツ操作権限(1:1)
		$sql.= 		"AND contentauth.active_flg = '1' ";
		$sql.= 		"AND contentauth.con_auth_folder = '1' ";
		$sql.= "INNER JOIN usergroup ON folder_usergroup.usergroup_id = usergroup.usergroup_id ";				//ユーザグループ(1:1)
		$sql.= 		"AND usergroup.active_flg = '1' ";
		$sql.= "WHERE folder_usergroup.folder_id = ? ";
		$param[] = $folder_id;
		$sql.= "ORDER BY usergroup.usergroup_name ASC ";

		$result = $this->query($sql,$param);
		return $result;
	}
}
