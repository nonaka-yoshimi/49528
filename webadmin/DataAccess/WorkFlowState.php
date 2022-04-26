<?php
require_once(dirname(__FILE__).'/../Framework/DataAccessBase.php');
/*
 説明：ワークフロー状態クラス
作成日：2014/1/8 TS谷
*/

/**
 * ワークフロー状態クラス
 */
class WorkFlowState extends DataAccessBase
{

	/**
	 * ワークフロー状態コンストラクタ
	 */
	function __construct(){
		parent::__construct();

		//接続先のテーブル名を設定してください
		$this->setTableName("workflowstate");

		//接続先テーブルの主キーを設定してください（複数設定可）
		$this->setPrimaryKey("workflowstate_id");
	}

	/**
	 * 表示用ワークフロー状態リストを取得する
	 * @return Ambigous <クエリ実行結果, boolean>
	 */
	function getListForSelect(){
		$sql = "SELECT ";
		$sql.= 		"workflowstate.updated, ";
		$sql.= 		"workflowstate.workflowstate_id, ";
		$sql.= 		"workflowstate.workflowstate_name ";
		$sql.= "FROM workflowstate ";
		$sql.= "WHERE workflowstate.active_flg = '1' ";
		$sql.= "ORDER BY workflowstate.sort_no ASC ";

		$result = $this->query($sql);
		return $result;
	}

	/**
	 * マスタ設定用ワークフロー状態リストを取得する
	 * @return Ambigous <クエリ実行結果, boolean>
	 */
	function getListForSetting(){
		$sql = "SELECT ";
		$sql.= 		"workflowstate.updated, ";
		$sql.= 		"workflowstate.workflowstate_id, ";
		$sql.= 		"workflowstate.workflowstate_name ";
		$sql.= "FROM workflowstate ";
		$sql.= "ORDER BY workflowstate.sort_no ASC ";

		$result = $this->query($sql);
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
