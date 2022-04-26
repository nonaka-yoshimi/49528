<?php
require_once(dirname(__FILE__).'/../Framework/DataAccessBase.php');
/*
 説明：コンテンツ-ユーザ紐付クラス
作成日：2013/12/10 TS谷
*/

/**
 * コンテンツ-ユーザ紐付クラス
 */
class ContentUser extends DataAccessBase
{

	/**
	 * コンテンツ-ユーザ紐付コンストラクタ
	 */
	function __construct(){
		parent::__construct();

		//接続先のテーブル名を設定してください
		$this->setTableName("content_user");

		//接続先テーブルの主キーを設定してください（複数設定可）
		$this->setPrimaryKey("content_user_id");
	}

	/**
	 * コンテンツ-ユーザ紐付一覧を取得する
	 * @return Ambigous <クエリ実行結果, boolean>
	 */
	function getListByContentId($content_id,$contentclass){
		$sql = "SELECT ";
		$sql.= 		"user.user_id, ";
		$sql.= 		"user.name, ";
		$sql.= 		"contentauth.contentauth_id, ";
		$sql.= 		"content_user.content_user_id, ";
		$sql.= 		"content_user.content_id, ";
		$sql.= 		"content_user.start_time, ";
		$sql.= 		"content_user.end_time ";
		$sql.= "FROM content_user ";
		$sql.= "INNER JOIN contentauth ON content_user.contentauth_id = contentauth.contentauth_id ";		//コンテンツ操作権限(1:1)
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
		$sql.= "INNER JOIN user ON content_user.user_id = user.user_id ";									//ユーザ(1:1)
		$sql.= 		"AND user.active_flg = '1' ";
		$sql.= "WHERE content_user.content_id = ? ";
		$param[] = $content_id;
		$sql.= "ORDER BY user.name ASC ";

		$result = $this->query($sql,$param);
		return $result;
	}
}
