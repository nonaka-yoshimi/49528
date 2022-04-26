<?php
require_once(dirname(__FILE__).'/../Framework/DataAccessBase.php');
/*
 説明：コンテンツ操作履歴クラス
作成日：2013/12/10 TS谷
*/

/**
 * コンテンツ操作履歴クラス
 */
class ContentHistory extends DataAccessBase
{

	/**
	 * コンテンツ操作履歴コンストラクタ
	 */
	function __construct(){
		parent::__construct();

		//接続先のテーブル名を設定してください
		$this->setTableName("contenthistory");

		//接続先テーブルの主キーを設定してください（複数設定可）
		$this->setPrimaryKey("contenthistory_id");
	}

	/**
	 * コンテンツIDに基づき公開履歴一覧を取得する
	 * @param int $content_id コンテンツID
	 * @return Ambigous <クエリ実行結果, boolean>
	 */
	function getListByContentId($content_id,$start = 0,$num = 20){
		$sql = "SELECT ";
		$sql.= 		"contenthistory.contenthistory_id, ";
		$sql.= 		"contenthistory.operation, ";
		$sql.= 		"contenthistory.time, ";
		$sql.= 		"content.content_schedule_id, ";
		$sql.= 		"content.content_id, ";
		$sql.= 		"content.title, ";
		$sql.= 		"user.name ";
		$sql.= "FROM contenthistory ";
		$sql.= 		"LEFT JOIN user ON contenthistory.user_id = user.user_id ";
		$sql.= 			"AND user.active_flg = '1' ";
		$sql.= 		"INNER JOIN ";
		$sql.= 			"(SELECT '0' as content_schedule_id,content_id,title FROM content_public ";
		$sql.= 				"WHERE content_id = ? ";
		$param[] = $content_id;
		$sql.= 				"UNION SELECT content_schedule_id,content_id,title FROM content_schedule ";
		$sql.= 				"WHERE content_id = ?) as content ";
		$param[] = $content_id;
		$sql.= 		"ON contenthistory.content_id = content.content_id ";
		$sql.= 		"AND contenthistory.content_schedule_id = content.content_schedule_id ";
		$sql.= "WHERE contenthistory.content_id = ? ";
		$param[] = $content_id;
		$sql.= "ORDER BY contenthistory.time ASC ";
		$sql.= "LIMIT ".$start.",".$num;

		$result = $this->query($sql,$param);
		return $result;
	}
}
