<?php
require_once(dirname(__FILE__).'/../Framework/DataAccessBase.php');
/*
 説明：フォルダ-ユーザ紐付クラス
作成日：2013/12/2 TS谷
*/

/**
 * フォルダ-ユーザ紐付クラス
 */
class FolderUser extends DataAccessBase
{

	/**
	 * フォルダ-ユーザ紐付コンストラクタ
	 */
	function __construct(){
		parent::__construct();

		//接続先のテーブル名を設定してください
		$this->setTableName("folder_user");

		//接続先テーブルの主キーを設定してください（複数設定可）
		$this->setPrimaryKey("folder_user_id");
	}

	/**
	 * フォルダ-ユーザ紐付一覧を取得する
	 * @return Ambigous <クエリ実行結果, boolean>
	 */
	function getListByFolderId($folder_id){
		$sql = "SELECT ";
		$sql.= 		"user.user_id, ";
		$sql.= 		"user.name, ";
		$sql.= 		"contentauth.contentauth_id, ";
		$sql.= 		"folder_user.folder_user_id, ";
		$sql.= 		"folder_user.folder_id, ";
		$sql.= 		"folder_user.start_time, ";
		$sql.= 		"folder_user.end_time ";
		$sql.= "FROM folder_user ";
		$sql.= "INNER JOIN contentauth ON folder_user.contentauth_id = contentauth.contentauth_id ";		//コンテンツ操作権限(1:1)
		$sql.= 		"AND contentauth.active_flg = '1' ";
		$sql.= 		"AND contentauth.con_auth_folder = '1' ";
		$sql.= "INNER JOIN user ON folder_user.user_id = user.user_id ";										//ユーザ(1:1)
		$sql.= 		"AND user.active_flg = '1' ";
		$sql.= "WHERE folder_user.folder_id = ? ";
		$param[] = $folder_id;
		$sql.= "ORDER BY user.name ASC ";

		$result = $this->query($sql,$param);
		return $result;
	}
}
