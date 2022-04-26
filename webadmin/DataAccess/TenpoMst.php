<?php
/*
 説明：店舗情報アクセスクラス
作成日：2013/10/29 TS谷
*/
require_once(dirname(__FILE__).'/../Framework/DataAccessBase.php');

/**
 * 店舗マスタクラス
 *
 */
class TenpoMst extends DataAccessBase
{
	/**
	 * 店舗IDカラム名
	 * @var string
	 */
	const TENPO_IDColumn = 'tenpo_id';

	/**
	 * ユーザマスタコンストラクタ
	 */
	function __construct(){
		parent::__construct();

		//接続先のテーブル名を設定してください
		$this->setTableName("tenpo_mst");

		//接続先テーブルの主キーを設定してください（複数設定可）
		$this->setPrimaryKey("tenpo_id");
	}

	/**
	 * 店舗一覧を取得する
	 * @return array 店舗一覧:
	 */
	function getTenpoList(){

		$where = array("active_flg" =>1);
		$order = array("sort_no" => "ASC");
		$result = $this->getListByParameters($where,$order);
		return $result;
	}

	function getTenpoDetailList($start = "",$num = ""){

		$sql  = "SELECT ";
		$sql .= "tenpo.`tenpo_id`,";
		$sql .= "tenpo.`tenpo_name`,";
		$sql .= "tenpo.`tenpo_name_sub`,";
		$sql .= "tenpo.`postal_code`,";
		$sql .= "tenpo.`address`,";
		$sql .= "tenpo.`tel`,";
		$sql .= "tenpo.`fax`,";
		$sql .= "tenpo.`mail`,";
		$sql .= "tenpo.`opentime_monday`,";
		$sql .= "tenpo.`opentime_tuesday`,";
		$sql .= "tenpo.`opentime_wednesday`,";
		$sql .= "tenpo.`opentime_thursday`,";
		$sql .= "tenpo.`opentime_friday`,";
		$sql .= "tenpo.`opentime_saturday`,";
		$sql .= "tenpo.`opentime_sunday`,";
		$sql .= "tenpo.`opentime_holiday`,";
		$sql .= "tenpo.`closetime_monday`,";
		$sql .= "tenpo.`closetime_tuesday`,";
		$sql .= "tenpo.`closetime_wednesday`,";
		$sql .= "tenpo.`closetime_thursday`,";
		$sql .= "tenpo.`closetime_friday`,";
		$sql .= "tenpo.`closetime_saturday`,";
		$sql .= "tenpo.`closetime_sunday`,";
		$sql .= "tenpo.`closetime_holiday`,";
		$sql .= "tenpo.`reserve_term`,";
		$sql .= "tenpo.`reserve_default_time`,";
		$sql .= "tenpo.`create_datetime`,";
		$sql .= "tenpo.`update_datetime`,";
		$sql .= "tenpo.`sort_no`,";
		$sql .= "tenpo.`active_flg`,";
		$sql .= "COUNT(tenpo.`tenpo_id`) booth_count ";
		$sql .= "FROM `tenpo_mst` tenpo ";
		$sql .= "LEFT JOIN `booth_mst` booth ON tenpo.`tenpo_id` = booth.`tenpo_id` ";
		//$sql .= "WHERE tenpo.`active_flg` = 1 ";
		$sql .= "GROUP BY tenpo.`tenpo_id` ";
		$sql .= "ORDER BY tenpo.`sort_no` ASC,tenpo.`tenpo_id` ASC ";
		if($start !== "" && $num !== ""){
			$sql .= "LIMIT ".$start.",".$num;
		}
		$result = $this->query($sql);
		return $result;
	}

}
