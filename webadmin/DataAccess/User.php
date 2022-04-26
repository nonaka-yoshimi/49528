<?php
/*
 説明：ユーザマスタクラス
作成日：2013/10/17 TS谷
*/
require_once(dirname(__FILE__).'/../Framework/DataAccessBase.php');

/**
 * ユーザマスタクラス
 */
class User extends DataAccessBase
{

	/**
	 * 有効フラグ：無効
	 */
	const NONACTIVE = "0";

	/**
	 * 有効フラグ：有効
	 */
	const ACTIVE = "1";

	/**
	 * ユーザマスタコンストラクタ
	 */
	function __construct(){
		parent::__construct();

		//接続先のテーブル名を設定してください
		$this->setTableName("user");

		//接続先テーブルの主キーを設定してください（複数設定可）
		$this->setPrimaryKey("user_id");
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
		$sql.= 		"user.user_id, ";
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

	function getUserListByParameters($parameters){
		$now_timestamp = time();

		$sql = "SELECT ";
		$sql.= 		"user.user_id, ";
		$sql.= 		"user.updated, ";
		$sql.= 		"user.name ";
		$sql.= "FROM user ";
		$sql.= "INNER JOIN user_usergroup ON user.user_id = user_usergroup.user_id ";
		$sql.= 		"AND (user_usergroup.start_time <= ? OR user_usergroup.start_time IS NULL) ";
		$param[] = $now_timestamp;
		$sql.= 		"AND (user_usergroup.end_time > ? OR user_usergroup.end_time IS NULL) ";
		$param[] = $now_timestamp;
		$sql.= "WHERE user_usergroup.usergroup_id = ? ";
		$param[] = $parameters["usergroup_id"];
		$sql.= "GROUP BY user.user_id ";
		$sql.= "ORDER BY user.sort_no ASC ";

		$result = $this->query($sql,$param);
		return $result;
	}

	function getUserDataForEdit($user_id){
		$sql = "SELECT ";
		$sql.= 		"user.user_id, ";																		//ユーザID
		$sql.= 		"user.start_time, ";																	//利用開始日時
		$sql.= 		"user.end_time, ";																		//利用終了日時
		$sql.= 		"user.name, ";																			//氏名
		$sql.= 		"user.name_kana, ";																		//氏名（カナ）
		$sql.= 		"user.login_id, ";																		//ログインID
		$sql.= 		"user.password, ";																		//パスワード
		$sql.= 		"user.mail, ";																			//メールアドレス
		$sql.= 		"user.admin_flg, ";																		//管理者フラグ
		$sql.= 		"user.admintype, ";																		//管理者種別
		$sql.= 		"user.firstauth_code, ";																//初回認証コード
		$sql.= 		"user.language, ";																		//使用言語
		$sql.= 		"user.active_flg, ";																	//有効・無効
		$sql.= 		"usertype.usertype_id, ";																//ユーザ種別ID
		$sql.= 		"usertype.usertype_name ";																//ユーザ種別名
		$sql.= "FROM user ";
		$sql.= "LEFT JOIN usertype ON user.usertype_id = usertype.usertype_id ";							//ユーザ種別
		$sql.= 		"AND usertype.active_flg = '1' ";
		$sql.= "WHERE user.user_id = ? ";
		$param[] = $user_id;
		$sql.= "ORDER BY user.sort_no ASC ";

		$result = $this->query($sql,$param,DB::FETCH);
		return $result;
	}

	function getUserListForEdit(){
		$now_timestamp = time();

		$sql = "SELECT ";
		$sql.= 		"user.user_id, ";																		//ユーザID
		$sql.= 		"user.start_time, ";																	//利用開始日時
		$sql.= 		"user.end_time, ";																		//利用終了日時
		$sql.= 		"user.name, ";																			//氏名
		$sql.= 		"user.name_kana, ";																		//氏名（カナ）
		$sql.= 		"user.login_id, ";																		//ログインID
		$sql.= 		"user.password, ";																		//パスワード
		$sql.= 		"user.mail, ";																			//メールアドレス
		$sql.= 		"user.admin_flg, ";																		//管理者フラグ
		$sql.= 		"user.admintype, ";																		//管理者種別
		$sql.= 		"user.firstauth_code, ";																//初回認証コード
		$sql.= 		"user.language, ";																		//使用言語
		$sql.= 		"user.created, ";																		//作成日時
		$sql.= 		"user.updated, ";																		//更新日時
		$sql.= 		"user.active_flg, ";																	//状態
		$sql.= 		"usertype.usertype_id, ";																//ユーザ種別ID
		$sql.= 		"usertype.usertype_name, ";																//ユーザ種別名
		$sql.= 		"usergroup.usergroup_name ";															//ユーザグループ名
		$sql.= "FROM user ";
		$sql.= "LEFT JOIN usertype ON user.usertype_id = usertype.usertype_id ";							//ユーザ種別
		$sql.= 		"AND usertype.active_flg = '1' ";
		$sql.= "LEFT JOIN user_usergroup ON user.user_id = user_usergroup.user_id ";						//ユーザ-ユーザグループ紐付
		$sql.= 		"AND maingroup_flg = '1' ";																//主所属のみ
		$sql.= 		"AND (user_usergroup.start_time <= ? OR user_usergroup.start_time IS NULL) ";
		$param[] = $now_timestamp;
		$sql.= 		"AND (user_usergroup.end_time > ? OR user_usergroup.end_time IS NULL) ";
		$param[] = $now_timestamp;
		$sql.= "LEFT JOIN usergroup ON user_usergroup.usergroup_id = usergroup.usergroup_id ";				//ユーザグループ
		$sql.= 		"AND (usergroup.start_time <= ? OR usergroup.start_time IS NULL) ";
		$param[] = $now_timestamp;
		$sql.= 		"AND (usergroup.end_time > ? OR usergroup.end_time IS NULL) ";
		$param[] = $now_timestamp;
		$sql.= "GROUP BY user.user_id ";
		$sql.= "ORDER BY user.sort_no ASC ";

		$result = $this->query($sql,$param);
		return $result;
	}

	function getUserListInUserGroupList($usergroup_list){

		$now_timestamp = time();

		$sql = "SELECT ";
		$sql.= 		"user.user_id, ";																		//ユーザID
		$sql.= 		"user.start_time, ";																	//利用開始日時
		$sql.= 		"user.end_time, ";																		//利用終了日時
		$sql.= 		"user.name, ";																			//氏名
		$sql.= 		"user.name_kana, ";																		//氏名（カナ）
		$sql.= 		"user.login_id, ";																		//ログインID
		$sql.= 		"user.password, ";																		//パスワード
		$sql.= 		"user.mail, ";																			//メールアドレス
		$sql.= 		"user.admin_flg, ";																		//管理者フラグ
		$sql.= 		"user.admintype, ";																		//管理者種別
		$sql.= 		"user.firstauth_code, ";																//初回認証コード
		$sql.= 		"user.language, ";																		//使用言語
		$sql.= 		"user.created, ";																		//作成日時
		$sql.= 		"user.updated, ";																		//更新日時
		$sql.= 		"user.active_flg, ";																	//状態
		$sql.= 		"usertype.usertype_id, ";																//ユーザ種別ID
		$sql.= 		"usertype.usertype_name, ";																//ユーザ種別名
		$sql.= 		"usergroup.usergroup_name ";															//ユーザグループ名
		$sql.= "FROM user ";
		$sql.= "LEFT JOIN usertype ON user.usertype_id = usertype.usertype_id ";							//ユーザ種別
		$sql.= 		"AND usertype.active_flg = '1' ";
		$sql.= "INNER JOIN user_usergroup ON user.user_id = user_usergroup.user_id ";						//ユーザ-ユーザグループ紐付
		$sql.= 		"AND (user_usergroup.start_time <= ? OR user_usergroup.start_time IS NULL) ";
		$param[] = $now_timestamp;
		$sql.= 		"AND (user_usergroup.end_time > ? OR user_usergroup.end_time IS NULL) ";
		$param[] = $now_timestamp;
		$sql.= "INNER JOIN usergroup ON user_usergroup.usergroup_id = usergroup.usergroup_id ";				//ユーザグループ
		$sql.= 		"AND (usergroup.start_time <= ? OR usergroup.start_time IS NULL) ";
		$param[] = $now_timestamp;
		$sql.= 		"AND (usergroup.end_time > ? OR usergroup.end_time IS NULL) ";
		$param[] = $now_timestamp;
		$sql.= 		"AND usergroup.active_flg = '1' ";
		$sql.= "WHERE user.active_flg = '1' ";
		$sql.= 		"AND (user.start_time <= ? OR user.start_time IS NULL) ";
		$param[] = $now_timestamp;
		$sql.= 		"AND (user.end_time > ? OR user.end_time IS NULL) ";
		$param[] = $now_timestamp;
		if($usergroup_list){
			$sql.= 		"AND usergroup.usergroup_id IN(";
			for($i=0;$i<count($usergroup_list);$i++){
				if($i>0){$sql.= ",";}
				$sql.= "?";
				$param[] = $usergroup_list[$i];
			}
			$sql.= 		") ";
		}
		$sql.= "GROUP BY user.user_id ";
		$sql.= "ORDER BY user.sort_no ASC ";
		Logger::debug($sql,$param);
		$result = $this->query($sql,$param);
		return $result;
	}

	function sortDown($user_id,$operation_user_id){
		$now_timestamp = time();

		$user = $this->getDataByPrimaryKey($user_id);
		if(!$user){
			return false;
		}

		$sql = "SELECT min(sort_no) as min FROM user ";
		$sql.= "WHERE sort_no > ? ";
		$params[] = $user["sort_no"];

		$result = $this->query($sql,$params,DB::FETCH);
		$target_sort = $result["min"];
		if(!$target_sort || $target_sort == 0){
			return true;
		}

		$where = array();
		$where["sort_no"] = $target_sort;
		$target_user = $this->getDataByParameters($where);
		if(!$target_user){
			return false;
		}

		$this->update(array("user_id" => $user_id), array("sort_no" => $target_sort,"updated" => $now_timestamp,"updated_by" => $operation_user_id));
		$this->update(array("user_id" => $target_user["user_id"]), array("sort_no" => $user["sort_no"],"updated" => $now_timestamp,"updated_by" => $operation_user_id));
		return true;
	}

	function sortUp($user_id,$operation_user_id){
		$now_timestamp = time();

		$user = $this->getDataByPrimaryKey($user_id);
		if(!$user){
			return false;
		}

		$sql = "SELECT max(sort_no) as max FROM user ";
		$sql.= "WHERE sort_no < ? ";
		$params[] = $user["sort_no"];

		$result = $this->query($sql,$params,DB::FETCH);
		$target_sort = $result["max"];
		if(!$target_sort || $target_sort == 0){
			return true;
		}

		$where = array();
		$where["sort_no"] = $target_sort;
		$target_user = $this->getDataByParameters($where);
		if(!$target_user){
			return false;
		}

		$this->update(array("user_id" => $user_id), array("sort_no" => $target_sort,"updated" => $now_timestamp,"updated_by" => $operation_user_id));
		$this->update(array("user_id" => $target_user["user_id"]), array("sort_no" => $user["sort_no"],"updated" => $now_timestamp,"updated_by" => $operation_user_id));
		return true;
	}

	function getMaxSort(){
		$sql = "SELECT max(sort_no) as max FROM user ";

		$result = $this->query($sql,array(),DB::FETCH);
		$max = $result["max"];

		if(!$max || $max == 0){
			$sort = 0;
		}else{
			$sort = $max + 1;
		}

		return $sort;
	}

	function deleteUser($user_id){
		require_once(dirname(__FILE__).'/UserUserGroup.php');
		require_once(dirname(__FILE__).'/FolderUser.php');
		require_once(dirname(__FILE__).'/ContentUser.php');
		require_once(dirname(__FILE__).'/DomainUser.php');

		$user = $this->getDataByPrimaryKey($user_id);
		if(!$user){
			return false;
		}

		$this->delete(array("user_id" => $user_id));

		$userUserGroup = new UserUserGroup();
		$userUserGroup->delete(array("user_id" => $user_id));

		$folderUser = new FolderUser();
		$folderUser->delete(array("user_id" => $user_id));
		$contentUser = new ContentUser();
		$contentUser->delete(array("user_id" => $user_id));
		$domainUser = new DomainUser();
		$domainUser->delete(array("user_id" => $user_id));

		return true;
	}
}
