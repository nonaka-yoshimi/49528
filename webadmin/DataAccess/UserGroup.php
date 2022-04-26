<?php
/*
 説明：ユーザグループクラス
作成日：2013/12/2 TS谷
*/
require_once(dirname(__FILE__).'/../Framework/DataAccessBase.php');

/**
 * ユーザグループクラス
 */
class UserGroup extends DataAccessBase
{

	/**
	 * ユーザグループコンストラクタ
	 */
	function __construct(){
		parent::__construct();

		//接続先のテーブル名を設定してください
		$this->setTableName("usergroup");

		//接続先テーブルの主キーを設定してください（複数設定可）
		$this->setPrimaryKey("usergroup_id");
	}

	/**
	 * ユーザグループIDに基づき子グループ一覧を取得する
	 * @param string $usergroup_id ユーザグループID
	 * @return Ambigous <クエリ実行結果, boolean>
	 */
	function getChildGroupByUserGroupId($usergroup_id){
		$now_timestamp = time();

		$sql = "SELECT ";
		$sql.= 		"operationauth.*, ";
		$sql.= 		"usergroup.usergroup_id, ";
		$sql.= 		"usergroup.usergroup_name, ";
		$sql.= 		"usergrouptype.usergrouptype_id, ";
		$sql.= 		"usergrouptype.usergrouptype_name ";
		$sql.= "FROM usergroup ";
		$sql.= "LEFT JOIN usergrouptype ON usergroup.usergrouptype_id = usergrouptype.usergrouptype_id ";		//ユーザグループ種別
		$sql.= 		"AND usergrouptype.active_flg = '1' ";
		$sql.= "LEFT JOIN operationauth ON usergroup.operationauth_id = operationauth.operationauth_id ";		//機能操作権限
		$sql.= 		"AND operationauth.active_flg = '1' ";
		$sql.= "WHERE usergroup.parentgroup_id = ?";
		$param[] = $usergroup_id;																				//親グループ指定
		$sql.= 		"AND (usergroup.start_time <= ? OR usergroup.start_time IS NULL) ";
		$param[] = $now_timestamp;
		$sql.= 		"AND (usergroup.end_time > ? OR usergroup.end_time IS NULL) ";
		$param[] = $now_timestamp;
		$sql.= 		"AND usergroup.active_flg = '1' ";

		$result = $this->query($sql,$param);
		return $result;
	}

	/**
	 * ログインIDに基づきユーザ情報を1件取得する
	 * @param string $login_id ログインID
	 * @return Ambigous <クエリ実行結果, boolean>
	 */
	function getLoginUserData($login_id){
		$now_timestamp = time();

		$sql = "SELECT ";
		//$sql.= 		"operationauth.*, ";
		$sql.= 		"user.user_id, ";
		$sql.= 		"user.created, ";
		$sql.= 		"user.updated, ";
		$sql.= 		"user.created_by, ";
		$sql.= 		"user.updated_by, ";
		$sql.= 		"user.name, ";
		$sql.= 		"user.name_kana, ";
		$sql.= 		"user.login_id, ";
		$sql.= 		"user.password, ";
		$sql.= 		"user.mail, ";
		$sql.= 		"user.admin_flg, ";
		$sql.= 		"user.admintype, ";
		$sql.= 		"user.passwordchange_time, ";
		$sql.= 		"user.login_failed, ";
		$sql.= 		"user.firstauth_code, ";
		$sql.= 		"user.language, ";
		$sql.= 		"user.active_flg, ";
		$sql.= 		"usertype.usertype_id, ";
		$sql.= 		"usertype.usertype_name, ";
		$sql.= 		"usergroup.usergroup_id, ";
		$sql.= 		"usergroup.usergroup_name, ";
		$sql.= 		"usergrouptype.usergrouptype_id, ";
		$sql.= 		"usergrouptype.usergrouptype_name ";
		$sql.= "FROM user ";
		$sql.= "LEFT JOIN usertype ON user.usertype_id = usertype.usertype_id ";								//ユーザ種別
		$sql.= 		"AND usertype.active_flg = '1' ";
		$sql.= "LEFT JOIN user_usergroup ON user.user_id = user_usergroup.user_id ";							//ユーザ-ユーザグループ(主所属)紐付
		$sql.= 		"AND user_usergroup.maingroup_flg = '1' ";
		$sql.= 		"AND (user_usergroup.start_time <= ? OR user_usergroup.start_time IS NULL) ";
		$param[] = $now_timestamp;
		$sql.= 		"AND (user_usergroup.end_time > ? OR user_usergroup.end_time IS NULL) ";
		$param[] = $now_timestamp;
		$sql.= "LEFT JOIN usergroup ON user_usergroup.usergroup_id = usergroup.usergroup_id ";					//ユーザグループ
		$sql.= 		"AND (usergroup.start_time <= ? OR usergroup.start_time IS NULL) ";
		$param[] = $now_timestamp;
		$sql.= 		"AND (usergroup.end_time > ? OR usergroup.end_time IS NULL) ";
		$param[] = $now_timestamp;
		$sql.= "LEFT JOIN usergrouptype ON usergroup.usergrouptype_id = usergrouptype.usergrouptype_id ";		//ユーザグループ種別
		$sql.= 		"AND usergrouptype.active_flg = '1' ";
		//$sql.= "LEFT JOIN operationauth ON usergroup.operationauth_id = operationauth.operationauth_id ";		//機能操作権限
		//$sql.= 		"AND operationauth.active_flg = '1' ";
		$sql.= "WHERE user.login_id = ? ";

		$param[] = $login_id;

		$result = $this->query($sql,$param,DB::FETCH);
		return $result;
	}

	/**
	 * ユーザIDに基づき直所属ユーザグループ・操作権限一覧取得する
	 * @param string $login_id ユーザID
	 * @return Ambigous <クエリ実行結果, boolean>
	 */
	function getUserGroupAndAuthListByUserId($user_id){
		$now_timestamp = time();

		$sql = "SELECT ";
		$sql.= 		"operationauth.*, ";
		$sql.= 		"usergroup.usergroup_id, ";
		$sql.= 		"usergroup.usergroup_name, ";
		$sql.= 		"usergrouptype.usergrouptype_id, ";
		$sql.= 		"usergrouptype.usergrouptype_name ";
		$sql.= "FROM user ";
		$sql.= "LEFT JOIN user_usergroup ON user.user_id = user_usergroup.user_id ";							//ユーザ-ユーザグループ紐付
		$sql.= 		"AND (user_usergroup.start_time <= ? OR user_usergroup.start_time IS NULL) ";
		$param[] = $now_timestamp;
		$sql.= 		"AND (user_usergroup.end_time > ? OR user_usergroup.end_time IS NULL) ";
		$param[] = $now_timestamp;
		$sql.= "LEFT JOIN usergroup ON user_usergroup.usergroup_id = usergroup.usergroup_id ";					//ユーザグループ
		$sql.= 		"AND (usergroup.start_time <= ? OR usergroup.start_time IS NULL) ";
		$param[] = $now_timestamp;
		$sql.= 		"AND (usergroup.end_time > ? OR usergroup.end_time IS NULL) ";
		$param[] = $now_timestamp;
		$sql.= "LEFT JOIN usergrouptype ON usergroup.usergrouptype_id = usergrouptype.usergrouptype_id ";		//ユーザグループ種別
		$sql.= 		"AND usergrouptype.active_flg = '1' ";
		$sql.= "LEFT JOIN operationauth ON usergroup.operationauth_id = operationauth.operationauth_id ";		//機能操作権限
		$sql.= 		"AND operationauth.active_flg = '1' ";
		$sql.= "WHERE user.user_id = ? ";
		$sql.= "ORDER BY user_usergroup.maingroup_flg DESC ";
		$param[] = $user_id;

		$result = $this->query($sql,$param);
		return $result;
	}

	/**
	 * ユーザIDに基づき直所属ユーザグループ一覧取得する
	 * @param string $login_id ユーザID
	 * @return Ambigous <クエリ実行結果, boolean>
	 */
	function getUserGroupListByUserId($user_id){
		$now_timestamp = time();

		$sql = "SELECT ";
		$sql.= 		"operationauth.*, ";
		$sql.= 		"usergroup.usergroup_id, ";
		$sql.= 		"usergroup.usergroup_name, ";
		$sql.= 		"usergrouptype.usergrouptype_id, ";
		$sql.= 		"usergrouptype.usergrouptype_name ";
		$sql.= "FROM user ";
		$sql.= "LEFT JOIN user_usergroup ON user.user_id = user_usergroup.user_id ";							//ユーザ-ユーザグループ紐付
		$sql.= 		"AND (user_usergroup.start_time <= ? OR user_usergroup.start_time IS NULL) ";
		$param[] = $now_timestamp;
		$sql.= 		"AND (user_usergroup.end_time > ? OR user_usergroup.end_time IS NULL) ";
		$param[] = $now_timestamp;
		$sql.= "LEFT JOIN usergroup ON user_usergroup.usergroup_id = usergroup.usergroup_id ";					//ユーザグループ
		$sql.= 		"AND (usergroup.start_time <= ? OR usergroup.start_time IS NULL) ";
		$param[] = $now_timestamp;
		$sql.= 		"AND (usergroup.end_time > ? OR usergroup.end_time IS NULL) ";
		$param[] = $now_timestamp;
		$sql.= "LEFT JOIN operationauth ON usergroup.operationauth_id = operationauth.operationauth_id ";		//機能操作権限
		$sql.= 		"AND operationauth.active_flg = '1' ";
		$sql.= "WHERE user.user_id = ? ";
		$sql.= "ORDER BY user_usergroup.maingroup_flg DESC ";
		$param[] = $user_id;

		$result = $this->query($sql,$param);
		return $result;
	}

	function getUserGroupList(){
		$sql = "SELECT ";
		$sql.= 		"usergroup.usergroup_id, ";
		$sql.= 		"usergroup.parentgroup_id, ";
		$sql.= 		"usergroup.usergroup_name ";
		$sql.= "FROM usergroup ";
		$sql.= "ORDER BY usergroup.sort_no ASC ";

		$result = $this->query($sql);
		return $result;
	}

	function getUserGroupDataForEdit($usergroup_id){
		$sql = "SELECT ";
		$sql.= 		"usergroup.usergroup_id, ";
		$sql.= 		"usergroup.start_time, ";
		$sql.= 		"usergroup.end_time, ";
		$sql.= 		"usergroup.usergroup_name, ";
		$sql.= 		"usergroup.operationauth_id, ";
		$sql.= 		"usergroup.usergrouptype_id, ";
		$sql.= 		"parentgroup.usergroup_id as parentgroup_id, ";
		$sql.= 		"parentgroup.usergroup_name as parentgroup_name ";
		$sql.= "FROM usergroup ";
		$sql.= "LEFT JOIN usergroup parentgroup ON usergroup.parentgroup_id = parentgroup.usergroup_id ";
		$sql.= 		"AND parentgroup.active_flg = '1' ";
		$sql.= "WHERE usergroup.usergroup_id = ? ";
		$param[] = $usergroup_id;

		$result = $this->query($sql,$param,DB::FETCH);
		return $result;
	}

	function sortDown($id,$user_id){
		$now_timestamp = time();
		$data = $this->getDataByPrimaryKey($id);
		if(!$data){
			return false;
		}

		$sql = "SELECT min(sort_no) as min FROM ".$this->tablename." ";
		$sql.= "WHERE sort_no > ? ";
		$params[] = $data["sort_no"];

		$result = $this->query($sql,$params,DB::FETCH);
		$target_sort = $result["min"];
		if(!$target_sort || $target_sort == 0){
			return true;
		}

		$where = array();
		$where["sort_no"] = $target_sort;
		$target_data = $this->getDataByParameters($where);
		if(!$target_data){
			return false;
		}

		$this->update(array($this->primaryKeys[0] => $id), array("sort_no" => $target_sort,"updated" => $now_timestamp,"updated_by" => $user_id));
		$this->update(array($this->primaryKeys[0] => $target_data[$this->primaryKeys[0]]), array("sort_no" => $data["sort_no"],"updated" => $now_timestamp,"updated_by" => $user_id));
		return true;
	}

	function sortUp($id,$user_id){
		$now_timestamp = time();
		$data = $this->getDataByPrimaryKey($id);
		if(!$data){
			return false;
		}

		$sql = "SELECT max(sort_no) as max FROM ".$this->tablename." ";
		$sql.= "WHERE sort_no < ? ";
		$params[] = $data["sort_no"];

		$result = $this->query($sql,$params,DB::FETCH);
		$target_sort = $result["max"];
		if(!$target_sort || $target_sort == 0){
			return true;
		}

		$where = array();
		$where["sort_no"] = $target_sort;
		$target_data = $this->getDataByParameters($where);
		if(!$target_data){
			return false;
		}

		$this->update(array($this->primaryKeys[0] => $id), array("sort_no" => $target_sort,"updated" => $now_timestamp,"updated_by" => $user_id));
		$this->update(array($this->primaryKeys[0] => $target_data[$this->primaryKeys[0]]), array("sort_no" => $data["sort_no"],"updated" => $now_timestamp,"updated_by" => $user_id));
		return true;
	}

	function getMaxSort(){
		$sql = "SELECT max(sort_no) as max FROM ".$this->tablename." ";
		$result = $this->query($sql,array(),DB::FETCH);
		$max = $result["max"];

		if(!$max || $max == 0){
			$sort = 0;
		}else{
			$sort = $max + 1;
		}

		return $sort;
	}
}
