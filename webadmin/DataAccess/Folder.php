<?php
require_once(dirname(__FILE__).'/../Framework/DataAccessBase.php');
/*
 説明：フォルダークラス
作成日：2013/12/2 TS谷
*/

/**
 * フォルダクラス
 */
class Folder extends DataAccessBase
{

	/**
	 * フォルダクラスコンストラクタ
	 */
	function __construct(){
		parent::__construct();

		//接続先のテーブル名を設定してください
		$this->setTableName("folder");

		//接続先テーブルの主キーを設定してください（複数設定可）
		$this->setPrimaryKey("folder_id");
	}

	/**
	 * ユーザID及び所属ユーザグループ配列に基づき、フォルダ・権限一覧（非ユニーク）を取得する
	 * @param int $user_id ユーザID
	 * @param array $usergroupList ユーザグループ配列
	 * @return Ambigous <クエリ実行結果, boolean>
	 */
	function getFolderAndAuthListByUserId($user_id,$usergroupList){
		$now_timestamp = time();

		$sql = "SELECT ";
		$sql.= 		"folder.folder_id, ";
		$sql.= 		"folder.domain_id, ";
		$sql.= 		"folder.parentfolder_id, ";
		$sql.= 		"folder.folder_name, ";
		$sql.= 		"foldertype.foldertype_id, ";
		$sql.= 		"foldertype.foldertype_name, ";
		$sql.= 		"domain.domain_name, ";
		$sql.= 		"auth1.con_auth_dir_view as con_auth_dir_view1, ";
		$sql.= 		"auth1.con_auth_dir_add as con_auth_dir_add1, ";
		$sql.= 		"auth1.con_auth_dir_edit as con_auth_dir_edit1, ";
		$sql.= 		"auth1.con_auth_dir_delete as con_auth_dir_delete1, ";
		$sql.= 		"auth1.con_auth_dir_sort as con_auth_dir_sort1, ";
		$sql.= 		"auth2.con_auth_dir_view as con_auth_dir_view2, ";
		$sql.= 		"auth2.con_auth_dir_add as con_auth_dir_add2, ";
		$sql.= 		"auth2.con_auth_dir_edit as con_auth_dir_edit2, ";
		$sql.= 		"auth2.con_auth_dir_delete as con_auth_dir_delete2, ";
		$sql.= 		"auth2.con_auth_dir_sort as con_auth_dir_sort2 ";
		$sql.= "FROM folder ";
		$sql.= "LEFT JOIN foldertype ON folder.foldertype_id = foldertype.foldertype_id ";						//フォルダ種別(1:1)
		$sql.= 		"AND foldertype.active_flg = '1' ";
		$sql.= "LEFT JOIN domain ON folder.domain_id = domain.domain_id ";										//ドメイン(1:1)
		$sql.= 		"AND domain.active_flg = '1' ";
		$sql.= "LEFT JOIN folder_user ON folder.folder_id = folder_user.folder_id ";							//ユーザ-フォルダ紐付(1:1)
		$sql.= 		"AND folder_user.user_id = ? ";
		$param[] = $user_id;
		$sql.= 		"AND (folder_user.start_time <= ? OR folder_user.start_time IS NULL) ";
		$param[] = $now_timestamp;
		$sql.= 		"AND (folder_user.end_time > ? OR folder_user.end_time IS NULL) ";
		$param[] = $now_timestamp;
		$sql.= "LEFT JOIN contentauth as auth1 ON folder_user.contentauth_id = auth1.contentauth_id ";			//コンテンツ操作権限種別(1:1:1)
		$sql.= 		"AND auth1.active_flg = '1' ";
		$sql.= "LEFT JOIN folder_usergroup ON folder.folder_id = folder_usergroup.folder_id ";					//ユーザグループ-フォルダ紐付(1:N)
		if(count($usergroupList) > 0){
			$sql.= "AND (folder_usergroup.usergroup_id IN (";
			for($i=0;$i<count($usergroupList);$i++){
				if($i != ""){ $sql .= ","; }
				$sql .= "?";
				$param[] = $usergroupList[$i];
			}
			$sql .= ") ";
		}
		$sql.= "OR folder_usergroup.usergroup_id = '0') ";														//全てのユーザグループに権限がある場合
		$sql.= 		"AND (folder_usergroup.start_time <= ? OR folder_usergroup.start_time IS NULL) ";
		$param[] = $now_timestamp;
		$sql.= 		"AND (folder_usergroup.end_time > ? OR folder_usergroup.end_time IS NULL) ";
		$param[] = $now_timestamp;
		$sql.= "LEFT JOIN contentauth as auth2 ON folder_usergroup.contentauth_id = auth2.contentauth_id ";		//コンテンツ操作権限種別(1:N:N)
		$sql.= 		"AND auth2.active_flg = '1' ";
		$sql.= "WHERE (auth1.contentauth_id IS NOT NULL OR auth2.contentauth_id IS NOT NULL) ";					//何らかの権限が存在する場合
		$sql.= "ORDER BY folder.sort_no ASC ";																	//ソート順

		$result = $this->query($sql,$param);
		return $result;
	}

	/**
	 * ユーザID及び所属ユーザグループ配列、フォルダIDに基づき、フォルダ・権限一覧（非ユニーク）を取得する
	 * @param int $user_id ユーザID
	 * @param array $usergroupList ユーザグループ配列
	 * @param string $folder_id 親フォルダID
	 * @param string $keyword キーワード
	 * @return Ambigous <クエリ実行結果, boolean>
	 */
	function getFolderAndAuthListForFileListByUserId($user_id,$usergroupList,$folder_id = "",$keyword = ""){
		$now_timestamp = time();

		$sql = "SELECT ";
		$sql.= 		"folder.folder_id, ";
		$sql.= 		"folder.domain_id, ";
		$sql.= 		"folder.parentfolder_id, ";
		$sql.= 		"folder.folder_name, ";
		$sql.= 		"foldertype.foldertype_id, ";
		$sql.= 		"foldertype.foldertype_name, ";
		$sql.= 		"domain.domain_name, ";
		$sql.= 		"auth1.con_auth_dir_view as con_auth_dir_view1, ";
		$sql.= 		"auth1.con_auth_dir_add as con_auth_dir_add1, ";
		$sql.= 		"auth1.con_auth_dir_edit as con_auth_dir_edit1, ";
		$sql.= 		"auth1.con_auth_dir_delete as con_auth_dir_delete1, ";
		$sql.= 		"auth1.con_auth_dir_sort as con_auth_dir_sort1, ";
		$sql.= 		"auth2.con_auth_dir_view as con_auth_dir_view2, ";
		$sql.= 		"auth2.con_auth_dir_add as con_auth_dir_add2, ";
		$sql.= 		"auth2.con_auth_dir_edit as con_auth_dir_edit2, ";
		$sql.= 		"auth2.con_auth_dir_delete as con_auth_dir_delete2, ";
		$sql.= 		"auth2.con_auth_dir_sort as con_auth_dir_sort2 ";
		$sql.= "FROM folder ";
		$sql.= "LEFT JOIN foldertype ON folder.foldertype_id = foldertype.foldertype_id ";						//フォルダ種別(1:1)
		$sql.= 		"AND foldertype.active_flg = '1' ";
		$sql.= "LEFT JOIN domain ON folder.domain_id = domain.domain_id ";										//ドメイン(1:1)
		$sql.= 		"AND domain.active_flg = '1' ";
		$sql.= "LEFT JOIN folder_user ON folder.folder_id = folder_user.folder_id ";							//ユーザ-フォルダ紐付(1:1)
		$sql.= 		"AND folder_user.user_id = ? ";
		$param[] = $user_id;
		$sql.= 		"AND (folder_user.start_time <= ? OR folder_user.start_time IS NULL) ";
		$param[] = $now_timestamp;
		$sql.= 		"AND (folder_user.end_time > ? OR folder_user.end_time IS NULL) ";
		$param[] = $now_timestamp;
		$sql.= "LEFT JOIN contentauth as auth1 ON folder_user.contentauth_id = auth1.contentauth_id ";			//コンテンツ操作権限種別(1:1:1)
		$sql.= 		"AND auth1.active_flg = '1' ";
		$sql.= "LEFT JOIN folder_usergroup ON folder.folder_id = folder_usergroup.folder_id ";					//ユーザグループ-フォルダ紐付(1:N)
		if(count($usergroupList) > 0){
			$sql.= "AND (folder_usergroup.usergroup_id IN (";
			for($i=0;$i<count($usergroupList);$i++){
				if($i != ""){ $sql .= ","; }
				$sql .= "?";
				$param[] = $usergroupList[$i];
			}
			$sql .= ") ";
		}
		$sql.= "OR folder_usergroup.usergroup_id = '0') ";														//全てのユーザグループに権限がある場合
		$sql.= 		"AND (folder_usergroup.start_time <= ? OR folder_usergroup.start_time IS NULL) ";
		$param[] = $now_timestamp;
		$sql.= 		"AND (folder_usergroup.end_time > ? OR folder_usergroup.end_time IS NULL) ";
		$param[] = $now_timestamp;
		$sql.= "LEFT JOIN contentauth as auth2 ON folder_usergroup.contentauth_id = auth2.contentauth_id ";		//コンテンツ操作権限種別(1:N:N)
		$sql.= 		"AND auth2.active_flg = '1' ";
		$sql.= "WHERE (auth1.contentauth_id IS NOT NULL OR auth2.contentauth_id IS NOT NULL) ";					//何らかの権限が存在する場合

		//検索条件：親フォルダID
		if($folder_id != ""){
			$sql.= "AND folder.parentfolder_id = ? ";
			$param[] = $folder_id;
		}else{
			$sql.= "AND folder.parentfolder_id = ? ";
			$param[] = 0;
		}

		//検索条件：キーワード
		if($keyword != ""){
			$sql.= "AND (folder.folder_name LIKE ? OR folder.folder_name LIKE ? ) ";
			$param[] = "%".$keyword."%";
			$param[] = "%".$keyword."%";
		}
		$sql.= "ORDER BY folder.sort_no ASC ";																	//ソート順

		$result = $this->query($sql,$param);
		return $result;
	}



	/**
	 * ユーザID及び所属ユーザグループ配列、フォルダIDに基づき、対象フォルダの権限一覧（非ユニーク）を取得する
	 * @param int $user_id ユーザID
	 * @param array $usergroupList ユーザグループ配列
	 * @param array $auth_columns 権限カラム一覧
	 * @return Ambigous <クエリ実行結果, boolean>
	 */
	function getFolderAuthList($user_id,$usergroupList,$folder_id,$auth_columns){
		$now_timestamp = time();

		$sql = "SELECT ";
		$sql.= 		"folder.folder_id, ";																		//フォルダID
		$sql.= self::make_auth_column_str("auth1", "1", $auth_columns);
		$sql.= 		",";
		$sql.= self::make_auth_column_str("auth2", "2", $auth_columns);
		$sql.= " ";
		$sql.= "FROM folder ";
		$sql.= "LEFT JOIN foldertype ON folder.foldertype_id = foldertype.foldertype_id ";						//フォルダ種別(1:1) TODO 使用用途確認
		$sql.= 		"AND foldertype.active_flg = '1' ";
		$sql.= "LEFT JOIN domain ON folder.domain_id = domain.domain_id ";										//ドメイン(1:1)
		$sql.= 		"AND domain.active_flg = '1' ";
		$sql.= "LEFT JOIN folder_user ON folder.folder_id = folder_user.folder_id ";							//ユーザ-フォルダ紐付(1:1)
		$sql.= 		"AND folder_user.folder_id = ? ";
		$param[] = $folder_id;
		$sql.= 		"AND folder_user.user_id = ? ";
		$param[] = $user_id;
		$sql.= 		"AND (folder_user.start_time <= ? OR folder_user.start_time IS NULL) ";
		$param[] = $now_timestamp;
		$sql.= 		"AND (folder_user.end_time > ? OR folder_user.end_time IS NULL) ";
		$param[] = $now_timestamp;
		$sql.= "LEFT JOIN contentauth as auth1 ON folder_user.contentauth_id = auth1.contentauth_id ";			//コンテンツ操作権限種別(1:1:1)
		$sql.= 		"AND auth1.active_flg = '1' ";
		$sql.= "LEFT JOIN folder_usergroup ON folder.folder_id = folder_usergroup.folder_id ";					//ユーザグループ-フォルダ紐付(1:N)
		$sql.= 		"AND folder_usergroup.folder_id = ? ";
		$param[] = $folder_id;
		if(count($usergroupList) > 0){
			$sql.= "AND (folder_usergroup.usergroup_id IN (";
			for($i=0;$i<count($usergroupList);$i++){
				if($i != ""){ $sql .= ","; }
				$sql .= "?";
				$param[] = $usergroupList[$i];
			}
			$sql .= ") ";
		}
		$sql.= "OR folder_usergroup.usergroup_id = '0') ";														//全てのユーザグループに権限がある場合
		$sql.= 		"AND (folder_usergroup.start_time <= ? OR folder_usergroup.start_time IS NULL) ";
		$param[] = $now_timestamp;
		$sql.= 		"AND (folder_usergroup.end_time > ? OR folder_usergroup.end_time IS NULL) ";
		$param[] = $now_timestamp;
		$sql.= "LEFT JOIN contentauth as auth2 ON folder_usergroup.contentauth_id = auth2.contentauth_id ";		//コンテンツ操作権限種別(1:N:N)
		$sql.= 		"AND auth2.active_flg = '1' ";
		$sql.= "WHERE folder.folder_id = ? ";																	//フォルダID指定
		$param[] = $folder_id;
		$sql.= "AND domain.domain_id IS NOT NULL ";																//ドメイン有効チェック
		$sql.= "AND auth1.contentauth_id IS NOT NULL OR auth2.contentauth_id IS NOT NULL ";						//何らかの権限が存在する場合

		$result = $this->query($sql,$param);
		return $result;
	}

	function getFolderDataByFolderId($folder_id){
		$sql = "SELECT ";
		$sql.= 		"folder.folder_id, ";																		//フォルダID
		$sql.= 		"folder.start_time, ";																		//利用開始日時
		$sql.= 		"folder.end_time, ";																		//利用終了日時
		$sql.= 		"folder.folder_name, ";																		//フォルダ名
		$sql.= 		"folder.folder_code, ";																		//フォルダ識別名
		$sql.= 		"folder.title_prefix, ";																	//タイトルプレフィックス
		$sql.= 		"folder.title_suffix, ";																	//タイトルサフィックス
		$sql.= 		"folder.default_dir_path, ";																//デフォルトディレクトリパス
		$sql.= 		"folder.default_title, ";																	//デフォルトタイトル
		$sql.= 		"folder.default_page_content_id, ";															//デフォルトタイトル
		$sql.= 		"folder.default_static_mode, ";																//デフォルト静的モード
		$sql.= 		"template.content_id as template_id, ";														//テンプレートID
		$sql.= 		"template.title as template_name ";															//テンプレート名
		$sql.= "FROM folder ";
		$sql.= "LEFT JOIN content template ON folder.template_id = template.content_id ";						//テンプレート(1:1)
		$sql.= 		"AND template.active_flg = '1' ";
		$sql.= "WHERE folder.folder_id = ? ";																	//フォルダID指定
		$param[] = $folder_id;
		$sql.= "AND folder.active_flg = '1' ";

		$result = $this->query($sql,$param,DB::FETCH);
		return $result;
	}

	/**
	 * フォルダIDに基づき、フォルダデータ1件(編集画面用)を取得する
	 * @param int $folder_id フォルダID
	 * @return Ambigous <クエリ実行結果, boolean>
	 */
	function getFolderDataForEdit($folder_id){
		$now_timestamp = time();

		$sql = "SELECT ";
		$sql.= 		"folder.folder_id, ";																		//フォルダID
		$sql.= 		"folder.start_time, ";																		//利用開始日時
		$sql.= 		"folder.end_time, ";																		//利用終了日時
		$sql.= 		"folder.folder_name, ";																		//フォルダ名
		$sql.= 		"folder.folder_code, ";																		//フォルダ識別名
		$sql.= 		"folder.title_prefix, ";																	//タイトルプレフィックス
		$sql.= 		"folder.title_suffix, ";																	//タイトルサフィックス
		$sql.= 		"folder.default_dir_path, ";																//デフォルトディレクトリパス
		$sql.= 		"folder.default_title, ";																	//デフォルトタイトル
		$sql.= 		"folder.default_static_mode, ";																//デフォルト静的モード
		$sql.= 		"domain.domain_id, ";																		//ドメインID
		$sql.= 		"domain.domain_name, ";																		//ドメイン名
		$sql.= 		"parent.folder_id as parentfolder_id, ";													//親フォルダID
		$sql.= 		"parent.folder_name as parentfolder_name, ";												//親フォルダ名
		$sql.= 		"template.content_id as template_id, ";														//テンプレート(コンテンツ)ID
		$sql.= 		"template.title as template_name, ";														//テンプレート(コンテンツ)名
		$sql.= 		"def.content_id as default_page_content_id, ";												//デフォルトページ(コンテンツ)ID
		$sql.= 		"def.title as default_page_content_name, ";													//デフォルトページ(コンテンツ)タイトル
		$sql.= 		"foldertype.foldertype_id, ";																//フォルダ種別ID
		$sql.= 		"foldertype.foldertype_name ";																//フォルダ種別名
		$sql.= "FROM folder ";
		$sql.= "INNER JOIN domain ON folder.domain_id = domain.domain_id ";										//ドメイン(1:1)
		$sql.= 		"AND domain.active_flg = '1' ";
		$sql.= "LEFT JOIN folder parent ON folder.parentfolder_id = parent.folder_id ";							//親フォルダ(1:1)
		$sql.= 		"AND parent.active_flg = '1' ";
		$sql.= 		"AND (parent.start_time <= ? OR parent.start_time IS NULL) ";
		$param[] = $now_timestamp;
		$sql.= 		"AND (parent.end_time > ? OR parent.end_time IS NULL) ";
		$param[] = $now_timestamp;
		$sql.= "LEFT JOIN content template ON folder.template_id = template.content_id ";						//テンプレート(1:1)
		$sql.= 		"AND template.active_flg = '1' ";
		$sql.= "LEFT JOIN content def ON folder.default_page_content_id = def.content_id ";						//デフォルトページ(1:1)
		$sql.= 		"AND def.active_flg = '1' ";
		$sql.= "LEFT JOIN foldertype ON folder.foldertype_id = foldertype.foldertype_id ";						//フォルダ種別(1:1)
		$sql.= 		"AND foldertype.active_flg = '1' ";
		$sql.= "WHERE folder.folder_id = ? ";																	//フォルダID指定
		$param[] = $folder_id;

		$result = $this->query($sql,$param,DB::FETCH);
		return $result;
	}


	function getDomainDataByFolderId($folder_id){
		$sql = "SELECT ";
		$sql.= 		"domain.domain_id, ";																		//ドメインID
		$sql.= 		"domain.domain_name, ";																		//ドメイン名
		$sql.= 		"domain.domain ";																			//ドメイン
		$sql.= "FROM folder ";
		$sql.= "INNER JOIN domain ON folder.domain_id = domain.domain_id ";										//ドメイン(1:1)
		$sql.= 		"AND domain.active_flg = '1' ";
		$sql.= "WHERE folder.folder_id = ? ";																	//フォルダID指定
		$param[] = $folder_id;

		$result = $this->query($sql,$param,DB::FETCH);
		return $result;
	}

	static function make_auth_column_str($name,$index,$columns){
		$str = "";
		foreach($columns as $column){
			if($str != ""){ $str.= ","; }
			$str.= $name.".".$column." as ".$column.$index;
		}
		return $str;
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
