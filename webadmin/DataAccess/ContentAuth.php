<?php
require_once(dirname(__FILE__).'/../Framework/DataAccessBase.php');
/*
 説明：コンテンツ操作権限種別クラス
作成日：2013/12/2 TS谷
*/

/**
 * コンテンツ操作権限種別クラス
 */
class ContentAuth extends DataAccessBase
{

	/**
	 * コンテンツ操作権限種別コンストラクタ
	 */
	function __construct(){
		parent::__construct();

		//接続先のテーブル名を設定してください
		$this->setTableName("contentauth");

		//接続先テーブルの主キーを設定してください（複数設定可）
		$this->setPrimaryKey("contentauth_id");
	}

	/**
	 * 選択肢用フォルダ種別リストを取得する
	 * @param string $display_target 表示先区分 ページ=page 部品=element イメージ=image ファイル=file テンプレート=template スタイルシート=stylesheet スクリプト=script フォルダ=folder ドメイン=domain
	 * @return Ambigous <クエリ実行結果, boolean>
	 */
	function getListForSelect($display_target = ""){
		$sql = "SELECT ";
		$sql.= 		"contentauth.contentauth_id, ";
		$sql.= 		"contentauth.contentauth_name ";
		$sql.= "FROM contentauth ";
		$sql.= "WHERE contentauth.active_flg = '1' ";

		if($display_target == "page"){
			$sql.= "AND contentauth.con_auth_page = '1' ";
		}elseif($display_target == "element"){
			$sql.= "AND contentauth.con_auth_element = '1' ";
		}elseif($display_target == "image"){
			$sql.= "AND contentauth.con_auth_image = '1' ";
		}elseif($display_target == "file"){
			$sql.= "AND contentauth.con_auth_file = '1' ";
		}elseif($display_target == "template"){
			$sql.= "AND contentauth.con_auth_template = '1' ";
		}elseif($display_target == "stylesheet"){
			$sql.= "AND contentauth.con_auth_stylesheet = '1' ";
		}elseif($display_target == "script"){
			$sql.= "AND contentauth.con_auth_script = '1' ";
		}elseif($display_target == "folder"){
			$sql.= "AND contentauth.con_auth_folder = '1' ";
		}elseif($display_target == "domain"){
			$sql.= "AND contentauth.con_auth_domain = '1' ";
		}

		$sql.= "ORDER BY contentauth.sort_no ASC ";

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
		$sql.= "FROM contentauth ";
		$sql.= "ORDER BY contentauth.sort_no ASC ";

		$result = $this->query($sql);
		return $result;
	}
}
