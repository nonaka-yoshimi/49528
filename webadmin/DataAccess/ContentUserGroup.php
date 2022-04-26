<?php
require_once(dirname(__FILE__).'/../Framework/DataAccessBase.php');
/*
 説明：コンテンツ-ユーザグループ紐付クラス
作成日：2013/12/9 TS谷
*/

/**
 * コンテンツ-ユーザグループ紐付クラス
 */
class ContentUserGroup extends DataAccessBase
{

	/**
	 * コンテンツ-ユーザグループ紐付コンストラクタ
	 */
	function __construct(){
		parent::__construct();

		//接続先のテーブル名を設定してください
		$this->setTableName("content_usergroup");

		//接続先テーブルの主キーを設定してください（複数設定可）
		$this->setPrimaryKey("content_usergroup_id");
	}

	/**
	 * コンテンツ-ユーザグループ紐付一覧を取得する
	 * @param int $content_id コンテンツID
	 * @param string $contentclass コンテンツクラス
	 * @return Ambigous <クエリ実行結果, boolean>
	 */
	function getListByContentId($content_id,$contentclass){
		$sql = "SELECT ";
		$sql.= 		"usergroup.usergroup_id, ";
		$sql.= 		"usergroup.usergroup_name, ";
		$sql.= 		"contentauth.contentauth_id, ";
		$sql.= 		"content_usergroup.content_usergroup_id, ";
		$sql.= 		"content_usergroup.content_id, ";
		$sql.= 		"content_usergroup.start_time, ";
		$sql.= 		"content_usergroup.end_time ";
		$sql.= "FROM content_usergroup ";
		$sql.= "INNER JOIN contentauth ON content_usergroup.contentauth_id = contentauth.contentauth_id ";		//コンテンツ操作権限(1:1)
		$sql.= 		"AND contentauth.active_flg = '1' ";
		if($contentclass == SPConst::CONTENTCLASS_PAGE){
			$sql.= 		"AND contentauth.con_auth_page = '1' ";
		}elseif($contentclass == SPConst::CONTENTCLASS_ELEMENT){
			$sql.= 		"AND contentauth.con_auth_element = '1' ";
		}elseif($contentclass == SPConst::CONTENTCLASS_IMAGE){
			$sql.= 		"AND contentauth.con_auth_image = '1' ";
		}elseif($contentclass == SPConst::CONTENTCLASS_FILE){
			$sql.= 		"AND contentauth.con_auth_file = '1' ";
		}elseif($contentclass == SPConst::CONTENTCLASS_TEMPLATE){
			$sql.= 		"AND contentauth.con_auth_template = '1' ";
		}elseif($contentclass == SPConst::CONTENTCLASS_STYLESHEET){
			$sql.= 		"AND contentauth.con_auth_stylesheet = '1' ";
		}elseif($contentclass == SPConst::CONTENTCLASS_SCRIPT){
			$sql.= 		"AND contentauth.con_auth_script = '1' ";
		}
		$sql.= "INNER JOIN usergroup ON content_usergroup.usergroup_id = usergroup.usergroup_id ";				//ユーザグループ(1:1)
		$sql.= 		"AND usergroup.active_flg = '1' ";
		$sql.= "WHERE content_usergroup.content_id = ? ";
		$param[] = $content_id;
		$sql.= "ORDER BY usergroup.usergroup_name ASC ";

		$result = $this->query($sql,$param);
		return $result;
	}
}
