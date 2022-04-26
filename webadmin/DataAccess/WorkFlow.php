<?php
require_once(dirname(__FILE__).'/../Framework/DataAccessBase.php');
/*
 説明：ワークフローアクションクラス
作成日：2014/1/10 TS谷
*/

/**
 * ワークフローアクションクラス
 */
class WorkFlow extends DataAccessBase
{

	/**
	 * ワークフローアクションコンストラクタ
	 */
	function __construct(){
		parent::__construct();

		//接続先のテーブル名を設定してください
		$this->setTableName("workflow");

		//接続先テーブルの主キーを設定してください（複数設定可）
		$this->setPrimaryKey("workflow_id");
	}

	/**
	 * マスタ設定用ワークフローアクションリストを取得する
	 * @return Ambigous <クエリ実行結果, boolean>
	 */
	function getListForSetting(){
		$sql = "SELECT ";
		$sql.= 		"workflow.updated, ";
		$sql.= 		"workflow.workflow_id, ";
		$sql.= 		"workflow.workflow_name ";
		$sql.= "FROM workflow ";
		$sql.= "ORDER BY workflow.sort_no ASC ";

		$result = $this->query($sql);
		return $result;
	}

	/**
	 * マスタ編集用ワークフローアクションデータを取得する
	 * @return Ambigous <クエリ実行結果, boolean>
	 */
	function getDataForEdit($workflow_id){
		$now_timestamp = time();

		$sql = "SELECT ";
		$sql.= 		"workflow.updated, ";
		$sql.= 		"workflow.workflow_id, ";
		$sql.= 		"workflow.workflow_name, ";
		$sql.= 		"workflow.workflowstate_from_id, ";
		$sql.= 		"workflow.workflowstate_to_id, ";
		$sql.= 		"workflow.userrestriction, ";
		$sql.= 		"workflow.usergroup_id, ";
		$sql.= 		"workflow.usergrouptype_id, ";
		$sql.= 		"workflow.contentauth_id, ";
		$sql.= 		"workflow.operationauth_id, ";
		$sql.= 		"workflow.contentclass, ";
		$sql.= 		"workflow.usertype_id, ";
		$sql.= 		"workflow.foldertype_id, ";
		$sql.= 		"workflow.mailuserrestriction, ";
		$sql.= 		"workflow.mailusertype_id, ";
		$sql.= 		"workflow.mailusergroup_id, ";
		$sql.= 		"workflow.mailusergrouptype_id, ";
		$sql.= 		"workflow.mailcontentauth_id, ";
		$sql.= 		"workflow.mailoperationauth_id, ";
		$sql.= 		"mailcontent.content_id as mailcontent_id, ";
		$sql.= 		"mailcontent.title as mailcontent_name, ";
		$sql.= 		"folder.folder_id as folder_id, ";
		$sql.= 		"folder.folder_name as folder_name ";
		$sql.= "FROM workflow ";
		$sql.= 		"LEFT JOIN content mailcontent ON workflow.mailcontent_id = mailcontent.content_id ";		//メールコンテンツ(1:1)
		$sql.= 			"AND mailcontent.active_flg = '1' ";
		$sql.= 		"LEFT JOIN folder ON workflow.folder_id = folder.folder_id ";								//管理フォルダ(1:1)
		$sql.= 			"AND (folder.start_time <= ? OR folder.start_time IS NULL) ";
		$param[] = $now_timestamp;
		$sql.= 			"AND (folder.end_time > ? OR folder.end_time IS NULL) ";
		$param[] = $now_timestamp;
		$sql.= 			"AND folder.active_flg = '1' ";
		$sql.= "WHERE workflow.workflow_id = ? ";
		$param[] = $workflow_id;

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
