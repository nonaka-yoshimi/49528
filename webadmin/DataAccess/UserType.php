<?php
require_once(dirname(__FILE__).'/../Framework/DataAccessBase.php');
/*
 説明：ユーザ種別クラス
作成日：2013/12/30 TS谷
*/

/**
 * ユーザ種別クラス
 */
class UserType extends DataAccessBase
{

	/**
	 * ユーザ種別コンストラクタ
	 */
	function __construct(){
		parent::__construct();

		//接続先のテーブル名を設定してください
		$this->setTableName("usertype");

		//接続先テーブルの主キーを設定してください（複数設定可）
		$this->setPrimaryKey("usertype_id");
	}

	/**
	 * 選択肢用ユーザ種別リストを取得する
	 * @return Ambigous <クエリ実行結果, boolean>
	 */
	function getListForSelect(){
		$sql = "SELECT ";
		$sql.= 		"usertype.usertype_id, ";
		$sql.= 		"usertype.usertype_name ";
		$sql.= "FROM usertype ";
		$sql.= "WHERE usertype.active_flg = '1' ";
		$sql.= "ORDER BY usertype.sort_no ASC ";

		$result = $this->query($sql);
		return $result;
	}

	/**
	 * マスタ設定用ユーザ種別リストを取得する
	 * @return Ambigous <クエリ実行結果, boolean>
	 */
	function getListForSetting(){
		$sql = "SELECT ";
		$sql.= 		"usertype.updated, ";
		$sql.= 		"usertype.usertype_id, ";
		$sql.= 		"usertype.usertype_name ";
		$sql.= "FROM usertype ";
		$sql.= "ORDER BY usertype.sort_no ASC ";

		$result = $this->query($sql);
		return $result;
	}
}
