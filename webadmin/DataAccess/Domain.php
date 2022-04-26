<?php
require_once(dirname(__FILE__).'/../Framework/DataAccessBase.php');
/*
 説明：ドメインクラス
作成日：2013/12/2 TS谷
*/

/**
 * ドメインクラス
 */
class Domain extends DataAccessBase
{

	/**
	 * ドメインクラスコンストラクタ
	 */
	function __construct(){
		parent::__construct();

		//接続先のテーブル名を設定してください
		$this->setTableName("domain");

		//接続先テーブルの主キーを設定してください（複数設定可）
		$this->setPrimaryKey("domain_id");
	}

	/**
	 * 選択肢用ドメインリストを取得する
	 * @return Ambigous <クエリ実行結果, boolean>
	 */
	function getListForSelect(){
		$sql = "SELECT ";
		$sql.= 		"domain.domain_id, ";
		$sql.= 		"domain.domain_name, ";
		$sql.= 		"domain.domain, ";
		$sql.= 		"domain.base_dir_path, ";
		$sql.= 		"domain.default_doctype ";
		$sql.= "FROM domain ";
		$sql.= "WHERE domain.active_flg = '1' ";
		$sql.= "ORDER BY domain.sort_no ASC ";

		$result = $this->query($sql);
		return $result;
	}

	/**
	 * マスタ設定用ドメインリストを取得する
	 * @return Ambigous <クエリ実行結果, boolean>
	 */
	function getListForSetting(){
		$sql = "SELECT ";
		$sql.= 		"* ";
		$sql.= "FROM domain ";
		$sql.= "ORDER BY domain.sort_no ASC ";

		$result = $this->query($sql);
		return $result;
	}
}
