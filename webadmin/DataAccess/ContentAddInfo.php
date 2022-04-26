<?php
require_once(dirname(__FILE__).'/../Framework/DataAccessBase.php');
/*
 説明：コンテンツ追加情報クラス
作成日：2013/12/10 TS谷
*/

/**
 * コンテンツ追加情報クラス
 */
class ContentAddInfo extends DataAccessBase
{
	const TABLE_MANAGEMENT = "content_addinfo";
	const TABLE_PUBLIC = "content_public_addinfo";
	const TABLE_SCHEDULE = "content_schedule_addinfo";
	const TABLE_ARCHIVE = "content_archive_addinfo";

	private $table_content = "";
	private $table_element = "";
	private $table_addinfo = "";

	/**
	 * コンテンツ追加情報コンストラクタ
	 */
	function __construct($table = self::TABLE_PUBLIC){
		parent::__construct();

		if($table == self::TABLE_MANAGEMENT){
			$this->setTableName("content_addinfo");
			$this->setPrimaryKey("content_addinfo_id");
			$this->table_content = "content";
			$this->table_element = "content_element";
			$this->table_addinfo = "content_addinfo";
		}elseif($table == self::TABLE_PUBLIC){
			$this->setTableName("content_public_addinfo");
			$this->setPrimaryKey("content_addinfo_id");
			$this->table_content = "content_public";
			$this->table_element = "content_public_element";
			$this->table_addinfo = "content_public_addinfo";
		}elseif($table == self::TABLE_SCHEDULE){
			$this->setTableName("content_schedule_addinfo");
			$this->setPrimaryKey("content_schedule_addinfo_id");
			$this->table_content = "content_schedule";
			$this->table_element = "content_schedule_element";
			$this->table_addinfo = "content_schedule_addinfo";
		}elseif($table == self::TABLE_ARCHIVE){
			$this->setTableName("content_archive_addinfo");
			$this->setPrimaryKey("content_archive_addinfo_id");
			$this->table_content = "content_archive";
			$this->table_element = "content_archive_element";
			$this->table_addinfo = "content_archive_addinfo";
		}

		//接続先のテーブル名を設定してください
		//$this->setTableName("content_addinfo");

		//接続先テーブルの主キーを設定してください（複数設定可）
		//$this->setPrimaryKey("content_addinfo_id");
	}

	/**
	 * コンテンツIDに基づき追加情報一覧を取得する
	 * @param int $content_id コンテンツID
	 * @return Ambigous <クエリ実行結果, boolean>
	 */
	function getListByContentId($content_id){

		$sql = "SELECT ";
		$sql.= 		"addinfo.name, ";
		$sql.= 		"addinfo.display_name, ";
		$sql.= 		"addinfo.inputtype, ";
		$sql.= 		"addinfo.selectname, ";
		$sql.= 		"addinfo.optionvalue, ";
		$sql.= 		"addinfo.checkboxname, ";
		$sql.= 		"addinfo.addinfo_content, ";
		$sql.= 		"content.title as addinfo_content_name ";
		$sql.= "FROM ".$this->table_addinfo." addinfo ";
		$sql.= 		"LEFT JOIN ".$this->table_content." content ";
		$sql.= 			"ON addinfo.addinfo_content = content.content_id ";
		$sql.= 			"AND (addinfo.inputtype = '".SPConst::INPUTTYPE_CONTENT."' ";
		$sql.= 				"OR (addinfo.inputtype = '".SPConst::INPUTTYPE_PAGE."' AND content.contentclass = '".SPConst::CONTENTCLASS_PAGE."') ";
		$sql.= 				"OR (addinfo.inputtype = '".SPConst::INPUTTYPE_ELEMENT."' AND content.contentclass = '".SPConst::CONTENTCLASS_ELEMENT."') ";
		$sql.= 				"OR (addinfo.inputtype = '".SPConst::INPUTTYPE_IMAGE."' AND content.contentclass = '".SPConst::CONTENTCLASS_IMAGE."') ";
		$sql.= 				"OR (addinfo.inputtype = '".SPConst::INPUTTYPE_FILE."' AND content.contentclass = '".SPConst::CONTENTCLASS_FILE."') ) ";
		$sql.= "WHERE addinfo.content_id = ? ";
		$param[] = $content_id;
		$sql.= "AND addinfo.active_flg = '1' ";
		$sql.= "ORDER BY addinfo.sort_no ASC ";

		$result = $this->query($sql,$param);
		return $result;
	}
}
