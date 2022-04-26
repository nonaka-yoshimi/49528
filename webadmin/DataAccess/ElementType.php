<?php
require_once(dirname(__FILE__).'/../Framework/DataAccessBase.php');
/*
 説明：部品種別クラス
作成日：2013/12/2 TS谷
*/

/**
 * 部品種別クラス
 */
class ElementType extends DataAccessBase
{

	/**
	 * 部品種別コンストラクタ
	 */
	function __construct(){
		parent::__construct();

		//接続先のテーブル名を設定してください
		$this->setTableName("elementtype");

		//接続先テーブルの主キーを設定してください（複数設定可）
		$this->setPrimaryKey("elementtype_id");
	}

	/**
	 * 部品種別一覧を取得する
	 * @return Ambigous <クエリ実行結果, boolean>
	 */
	function getElementList(){
		$sql = "SELECT ";
		$sql.= 		"elementtype.elementtype_id, ";
		$sql.= 		"elementtype.elementtype_name ";
		$sql.= "FROM elementtype ";
		$sql.= "WHERE elementtype.active_flg = '1' ";
		$sql.= "ORDER BY elementtype.sort_no ASC ";

		$result = $this->query($sql);
		return $result;
	}

	/**
	 * マスタ設定用要素種別リストを取得する
	 * @return Ambigous <クエリ実行結果, boolean>
	 */
	function getListForSetting(){
		$sql = "SELECT ";
		$sql.= 		"elementtype.updated, ";
		$sql.= 		"elementtype.elementtype_id, ";
		$sql.= 		"elementtype.elementtype_name ";
		$sql.= "FROM elementtype ";
		$sql.= "ORDER BY elementtype.sort_no ASC ";

		$result = $this->query($sql);
		return $result;
	}
}
