<?php
require_once(dirname(__FILE__).'/../Framework/DataAccessBase.php');
/*
 説明：コンテンツ-部品紐付クラス
作成日：2013/12/10 TS谷
*/

/**
 * コンテンツ-部品紐付クラス
 */
class ContentElement extends DataAccessBase
{
	const TABLE_MANAGEMENT = "content_element";
	const TABLE_PUBLIC = "content_public_element";
	const TABLE_SCHEDULE = "content_schedule_element";
	const TABLE_ARCHIVE = "content_archive_element";

	private $table_content = "";
	private $table_element = "";
	private $table_addinfo = "";

	/**
	 * コンテンツ-部品紐付コンストラクタ
	 */
	function __construct($table = self::TABLE_PUBLIC){
		parent::__construct();

		if($table == self::TABLE_MANAGEMENT){
			$this->setTableName("content_element");
			$this->setPrimaryKey("content_element_id");
			$this->table_content = "content";
			$this->table_element = "content_element";
			$this->table_addinfo = "content_addinfo";
		}elseif($table == self::TABLE_PUBLIC){
			$this->setTableName("content_public_element");
			$this->setPrimaryKey("content_element_id");
			$this->table_content = "content_public";
			$this->table_element = "content_public_element";
			$this->table_addinfo = "content_public_addinfo";
		}elseif($table == self::TABLE_SCHEDULE){
			$this->setTableName("content_schedule_element");
			$this->setPrimaryKey("content_schedule_element_id");
			$this->table_content = "content_schedule";
			$this->table_element = "content_schedule_element";
			$this->table_addinfo = "content_schedule_addinfo";
		}elseif($table == self::TABLE_ARCHIVE){
			$this->setTableName("content_archive_element");
			$this->setPrimaryKey("content_archive_element_id");
			$this->table_content = "content_archive";
			$this->table_element = "content_archive_element";
			$this->table_addinfo = "content_archive_addinfo";
		}
	}

	/**
	 * コンテンツIDに基づき部品情報一覧を取得する
	 * @param int $content_id コンテンツID
	 * @return Ambigous <クエリ実行結果, boolean>
	 */
	function getElementListByContentId($content_id){

		$sql = "SELECT ";
		$sql.= 		"con_ele.content_element_id, ";
		$sql.= 		"con_ele.elementtype_id, ";
		$sql.= 		"element.content_id as element_id, ";
		$sql.= 		"element.title ";
		$sql.= "FROM ".$this->table_element." con_ele ";
		$sql.= 		"LEFT JOIN ".$this->table_content." element ON con_ele.element_id = element.content_id ";
		$sql.= 			"AND element.active_flg = '1' ";
		$sql.= "WHERE con_ele.content_id = ? ";
		$param[] = $content_id;
		$sql.= "AND con_ele.contentclass = 'element' ";
		$sql.= "AND con_ele.active_flg = '1' ";

		$result = $this->query($sql,$param);
		return $result;
	}

	/**
	 * コンテンツIDに基づきスタイルシート情報一覧を取得する
	 * @param int $content_id コンテンツID
	 * @return Ambigous <クエリ実行結果, boolean>
	 */
	function getStylesheetListByContentId($content_id){

		$sql = "SELECT ";
		$sql.= 		"con_ele.content_element_id, ";
		$sql.= 		"con_ele.elementtype_id, ";
		$sql.= 		"element.content_id as element_id, ";
		$sql.= 		"element.title, ";
		$sql.= 		"element.url, ";
		$sql.= 		"element.media ";
		$sql.= "FROM ".$this->table_element." con_ele ";
		$sql.= 		"INNER JOIN ".$this->table_content." element ON con_ele.element_id = element.content_id ";
		$sql.= 			"AND element.active_flg = '1' ";
		$sql.= "WHERE con_ele.content_id = ? ";
		$param[] = $content_id;
		$sql.= "AND con_ele.contentclass = 'stylesheet' ";
		$sql.= "AND con_ele.active_flg = '1' ";
		$sql.= "ORDER BY con_ele.sort_no ASC ";

		$result = $this->query($sql,$param);
		return $result;
	}

	/**
	 * コンテンツIDに基づきスクリプト情報一覧を取得する
	 * @param int $content_id コンテンツID
	 * @return Ambigous <クエリ実行結果, boolean>
	 */
	function getScriptListByContentId($content_id){

		$sql = "SELECT ";
		$sql.= 		"con_ele.content_element_id, ";
		$sql.= 		"con_ele.elementtype_id, ";
		$sql.= 		"element.content_id as element_id, ";
		$sql.= 		"element.title, ";
		$sql.= 		"element.url ";
		$sql.= "FROM ".$this->table_element." con_ele ";
		$sql.= 		"INNER JOIN ".$this->table_content." element ON con_ele.element_id = element.content_id ";
		$sql.= 			"AND element.active_flg = '1' ";
		$sql.= "WHERE con_ele.content_id = ? ";
		$param[] = $content_id;
		$sql.= "AND con_ele.contentclass = 'script' ";
		$sql.= "AND con_ele.active_flg = '1' ";
		$sql.= "ORDER BY con_ele.sort_no ASC ";

		$result = $this->query($sql,$param);
		return $result;
	}
}
