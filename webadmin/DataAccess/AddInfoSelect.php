<?php
require_once(dirname(__FILE__).'/../Framework/DataAccessBase.php');
/*
 説明：追加情報選択肢クラス
作成日：2013/12/10 TS谷
*/

/**
 * 追加情報選択肢クラス
 */
class AddInfoSelect extends DataAccessBase
{

	/**
	 * 追加情報選択肢コンストラクタ
	 */
	function __construct(){
		parent::__construct();

		//接続先のテーブル名を設定してください
		$this->setTableName("addinfo_select");

		//接続先テーブルの主キーを設定してください（複数設定可）
		$this->setPrimaryKey("addinfo_select_id");
	}

	/**
	 * 選択肢名に基づき追加情報選択肢一覧を取得する
	 * @param int $selectname 選択肢名
	 * @return Ambigous <クエリ実行結果, boolean>
	 */
	function getListBySelectName($selectname){

		$sql = "SELECT ";
		$sql.= 		"addinfo_select.addinfo_select_id, ";
		$sql.= 		"addinfo_select.selectname, ";
		$sql.= 		"addinfo_select.optionvalue, ";
		$sql.= 		"addinfo_select.optionvalue_name ";
		$sql.= "FROM addinfo_select ";
		$sql.= "WHERE addinfo_select.selectname = ? ";
		$param[] = $selectname;
		$sql.= "AND addinfo_select.active_flg = '1' ";
		$sql.= "ORDER BY addinfo_select.sort_no ASC ";

		$result = $this->query($sql,$param);
		return $result;
	}

	/**
	 * 追加情報選択肢名一覧を取得する
	 * @return Ambigous <クエリ実行結果, boolean>
	 */
	function getSelectNameList(){
		$sql = "SELECT ";
		$sql.= 		"addinfo_select.selectname, ";
		$sql.= 		"addinfo_select.selectname_display, ";
		$sql.= 		"count(addinfo_select.optionvalue) as num, ";
		$sql.= 		"addinfo_select.created, ";
		$sql.= 		"addinfo_select.updated ";
		$sql.= "FROM addinfo_select ";
		$sql.= "WHERE addinfo_select.active_flg = '1' ";
		$sql.= "GROUP BY addinfo_select.selectname ";
		$sql.= "ORDER BY addinfo_select.addinfo_select_id ";

		$result = $this->query($sql);
		return $result;
	}

	/**
	 * マスタ設定用要素種別リストを取得する
	 * @return Ambigous <クエリ実行結果, boolean>
	 */
	function getListForSetting(){
		$sql = "SELECT ";
		$sql.= 		"addinfo_select.updated, ";
		$sql.= 		"addinfo_select.addinfo_select_id, ";
		$sql.= 		"addinfo_select.selectname, ";
		$sql.= 		"addinfo_select.optionvalue, ";
		$sql.= 		"addinfo_select.optionvalue_name ";
		$sql.= "FROM addinfo_select ";
		$sql.= "ORDER BY addinfo_select.sort_no ASC ";

		$result = $this->query($sql);
		return $result;
	}
}
