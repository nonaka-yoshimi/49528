<?php
require_once(dirname(__FILE__).'/../Framework/DataAccessBase.php');
/*
 説明：フォルダ種別クラス
作成日：2013/12/2 TS谷
*/

/**
 * フォルダ種別クラス
 */
class FolderType extends DataAccessBase
{

	/**
	 * フォルダ種別コンストラクタ
	 */
	function __construct(){
		parent::__construct();

		//接続先のテーブル名を設定してください
		$this->setTableName("foldertype");

		//接続先テーブルの主キーを設定してください（複数設定可）
		$this->setPrimaryKey("foldertype_id");
	}

	/**
	 * 選択肢用フォルダ種別リストを取得する
	 * @return Ambigous <クエリ実行結果, boolean>
	 */
	function getListForSelect(){
		$sql = "SELECT ";
		$sql.= 		"foldertype.foldertype_id, ";
		$sql.= 		"foldertype.foldertype_name ";
		$sql.= "FROM foldertype ";
		$sql.= "WHERE foldertype.active_flg = '1' ";
		$sql.= "ORDER BY foldertype.sort_no ASC ";

		$result = $this->query($sql);
		return $result;
	}

	/**
	 * マスタ設定用フォルダ種別リストを取得する
	 * @return Ambigous <クエリ実行結果, boolean>
	 */
	function getListForSetting(){
		$sql = "SELECT ";
		$sql.= 		"foldertype.updated, ";
		$sql.= 		"foldertype.foldertype_id, ";
		$sql.= 		"foldertype.foldertype_name ";
		$sql.= "FROM foldertype ";
		$sql.= "ORDER BY foldertype.sort_no ASC ";

		$result = $this->query($sql);
		return $result;
	}
}
