<?php
require_once(dirname(__FILE__).'/../Framework/DataAccessBase.php');
/*
 説明：機能操作権限種別クラス
作成日：2013/12/27 TS谷
*/

/**
 * 機能操作権限種別クラス
 */
class OperationAuth extends DataAccessBase
{

	/**
	 * 機能操作権限種別コンストラクタ
	 */
	function __construct(){
		parent::__construct();

		//接続先のテーブル名を設定してください
		$this->setTableName("operationauth");

		//接続先テーブルの主キーを設定してください（複数設定可）
		$this->setPrimaryKey("operationauth_id");
	}

	/**
	 * 選択肢用機能操作権限種別リストを取得する
	 * @return Ambigous <クエリ実行結果, boolean>
	 */
	function getListForSelect(){
		$sql = "SELECT ";
		$sql.= 		"operationauth.operationauth_id, ";
		$sql.= 		"operationauth.operationauth_name ";
		$sql.= "FROM operationauth ";
		$sql.= "WHERE operationauth.active_flg = '1' ";
		$sql.= "ORDER BY operationauth.sort_no ASC ";

		$result = $this->query($sql);
		return $result;
	}

	/**
	 * マスタ設定用要素種別リストを取得する
	 * @return Ambigous <クエリ実行結果, boolean>
	 */
	function getListForSetting(){
		$sql = "SELECT ";
		$sql.= 		"* ";
		$sql.= "FROM operationauth ";
		$sql.= "ORDER BY operationauth.sort_no ASC ";

		$result = $this->query($sql);
		return $result;
	}
}
