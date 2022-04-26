<?php
/*
 説明：コンテンツクラス
作成日：2013/12/1 TS谷
*/
require_once(dirname(__FILE__).'/../Framework/DataAccessBase.php');
require_once(dirname(__FILE__).'/ContentAddInfo.php');
require_once(dirname(__FILE__).'/ContentElement.php');
require_once(dirname(__FILE__).'/Folder.php');

/**
 * コンテンツクラス
 */
class Content extends DataAccessBase
{
	const TABLE_MANAGEMENT = "content";
	const TABLE_PUBLIC = "content_public";
	const TABLE_SCHEDULE = "content_schedule";
	const TABLE_ARCHIVE = "content_archive";

	protected $table_content = "";
	protected $table_element = "";
	protected $table_addinfo = "";

	protected $ContentAddInfo = null;
	protected $ContentElement = null;

	protected $now_timestamp;

	/**
	 * コンテンツクラスコンストラクタ
	 */
	function __construct($table){
		parent::__construct();

		if($table == self::TABLE_MANAGEMENT){
			$this->setTableName("content");
			$this->setPrimaryKey("content_id");
			$this->table_content = "content";
			$this->table_element = "content_element";
			$this->table_addinfo = "content_addinfo";
			$this->ContentAddInfo = new ContentAddInfo(ContentAddInfo::TABLE_MANAGEMENT);
			$this->ContentElement = new ContentAddInfo(ContentAddInfo::TABLE_MANAGEMENT);
		}elseif($table == self::TABLE_PUBLIC){
			$this->setTableName("content_public");
			$this->setPrimaryKey("content_id");
			$this->table_content = "content_public";
			$this->table_element = "content_public_element";
			$this->table_addinfo = "content_public_addinfo";
			$this->ContentAddInfo = new ContentAddInfo(ContentAddInfo::TABLE_PUBLIC);
			$this->ContentElement = new ContentAddInfo(ContentAddInfo::TABLE_PUBLIC);
		}elseif($table == self::TABLE_SCHEDULE){
			$this->setTableName("content_schedule");
			$this->setPrimaryKey("content_schedule_id");
			$this->table_content = "content_schedule";
			$this->table_element = "content_schedule_element";
			$this->table_addinfo = "content_schedule_addinfo";
			$this->ContentAddInfo = new ContentAddInfo(ContentAddInfo::TABLE_SCHEDULE);
			$this->ContentElement = new ContentAddInfo(ContentAddInfo::TABLE_SCHEDULE);
		}elseif($table == self::TABLE_ARCHIVE){
			$this->setTableName("content_archive");
			$this->setPrimaryKey("content_archive_id");
			$this->table_content = "content_archive";
			$this->table_element = "content_archive_element";
			$this->table_addinfo = "content_archive_addinfo";
			$this->ContentAddInfo = new ContentAddInfo(ContentAddInfo::TABLE_ARCHIVE);
			$this->ContentElement = new ContentAddInfo(ContentAddInfo::TABLE_ARCHIVE);
		}

		$this->now_timestamp = time();
	}

	function getContentAndAuthListByParameters($parameters){

		$now_timestamp = time();

		$cont_tbl = $this->table_content;

		$sql = "SELECT ";
		$sql.= 		"cont.content_id,";
		$sql.= 		"cont.updated,";
		$sql.= 		"cont.title,";
		$sql.= 		"cont.url,";
		$sql.= 		"cont.contentclass, ";
		$sql.= 		"auth1.con_auth_page_view as con_auth_page_view1, ";
		$sql.= 		"auth1.con_auth_page_edit as con_auth_page_edit1, ";
		$sql.= 		"auth1.con_auth_page_delete as con_auth_page_delete1, ";
		$sql.= 		"auth1.con_auth_page_workflow as con_auth_page_workflow1, ";
		$sql.= 		"auth1.con_auth_page_publish as con_auth_page_publish1, ";
		$sql.= 		"auth1.con_auth_page_archive as con_auth_page_archive1, ";
		$sql.= 		"auth1.con_auth_element_view as con_auth_element_view1, ";
		$sql.= 		"auth1.con_auth_element_edit as con_auth_element_edit1, ";
		$sql.= 		"auth1.con_auth_element_delete as con_auth_element_delete1, ";
		$sql.= 		"auth1.con_auth_element_workflow as con_auth_element_workflow1, ";
		$sql.= 		"auth1.con_auth_element_publish as con_auth_element_publish1, ";
		$sql.= 		"auth1.con_auth_element_archive as con_auth_element_archive1, ";
		$sql.= 		"auth1.con_auth_image_view as con_auth_image_view1, ";
		$sql.= 		"auth1.con_auth_image_edit as con_auth_image_edit1, ";
		$sql.= 		"auth1.con_auth_image_delete as con_auth_image_delete1, ";
		$sql.= 		"auth1.con_auth_image_workflow as con_auth_image_workflow1, ";
		$sql.= 		"auth1.con_auth_image_publish as con_auth_image_publish1, ";
		$sql.= 		"auth1.con_auth_image_archive as con_auth_image_archive1, ";
		$sql.= 		"auth1.con_auth_file_view as con_auth_file_view1, ";
		$sql.= 		"auth1.con_auth_file_edit as con_auth_file_edit1, ";
		$sql.= 		"auth1.con_auth_file_delete as con_auth_file_delete1, ";
		$sql.= 		"auth1.con_auth_file_workflow as con_auth_file_workflow1, ";
		$sql.= 		"auth1.con_auth_file_publish as con_auth_file_publish1, ";
		$sql.= 		"auth1.con_auth_file_archive as con_auth_file_archive1, ";
		$sql.= 		"auth1.con_auth_template_view as con_auth_template_view1, ";
		$sql.= 		"auth1.con_auth_template_edit as con_auth_template_edit1, ";
		$sql.= 		"auth1.con_auth_template_delete as con_auth_template_delete1, ";
		$sql.= 		"auth1.con_auth_template_workflow as con_auth_template_workflow1, ";
		$sql.= 		"auth1.con_auth_template_publish as con_auth_template_publish1, ";
		$sql.= 		"auth1.con_auth_template_archive as con_auth_template_archive1, ";
		$sql.= 		"auth1.con_auth_stylesheet_view as con_auth_stylesheet_view1, ";
		$sql.= 		"auth1.con_auth_stylesheet_edit as con_auth_stylesheet_edit1, ";
		$sql.= 		"auth1.con_auth_stylesheet_delete as con_auth_stylesheet_delete1, ";
		$sql.= 		"auth1.con_auth_stylesheet_workflow as con_auth_stylesheet_workflow1, ";
		$sql.= 		"auth1.con_auth_stylesheet_publish as con_auth_stylesheet_publish1, ";
		$sql.= 		"auth1.con_auth_stylesheet_archive as con_auth_stylesheet_archive1, ";
		$sql.= 		"auth1.con_auth_script_view as con_auth_script_view1, ";
		$sql.= 		"auth1.con_auth_script_edit as con_auth_script_edit1, ";
		$sql.= 		"auth1.con_auth_script_delete as con_auth_script_delete1, ";
		$sql.= 		"auth1.con_auth_script_workflow as con_auth_script_workflow1, ";
		$sql.= 		"auth1.con_auth_script_publish as con_auth_script_publish1, ";
		$sql.= 		"auth1.con_auth_script_archive as con_auth_script_archive1, ";

		$sql.= 		"auth2.con_auth_page_view as con_auth_page_view2, ";
		$sql.= 		"auth2.con_auth_page_edit as con_auth_page_edit2, ";
		$sql.= 		"auth2.con_auth_page_delete as con_auth_page_delete2, ";
		$sql.= 		"auth2.con_auth_page_workflow as con_auth_page_workflow2, ";
		$sql.= 		"auth2.con_auth_page_publish as con_auth_page_publish2, ";
		$sql.= 		"auth2.con_auth_page_archive as con_auth_page_archive2, ";
		$sql.= 		"auth2.con_auth_element_view as con_auth_element_view2, ";
		$sql.= 		"auth2.con_auth_element_edit as con_auth_element_edit2, ";
		$sql.= 		"auth2.con_auth_element_delete as con_auth_element_delete2, ";
		$sql.= 		"auth2.con_auth_element_workflow as con_auth_element_workflow2, ";
		$sql.= 		"auth2.con_auth_element_publish as con_auth_element_publish2, ";
		$sql.= 		"auth2.con_auth_element_archive as con_auth_element_archive2, ";
		$sql.= 		"auth2.con_auth_image_view as con_auth_image_view2, ";
		$sql.= 		"auth2.con_auth_image_edit as con_auth_image_edit2, ";
		$sql.= 		"auth2.con_auth_image_delete as con_auth_image_delete2, ";
		$sql.= 		"auth2.con_auth_image_workflow as con_auth_image_workflow2, ";
		$sql.= 		"auth2.con_auth_image_publish as con_auth_image_publish2, ";
		$sql.= 		"auth2.con_auth_image_archive as con_auth_image_archive2, ";
		$sql.= 		"auth2.con_auth_file_view as con_auth_file_view2, ";
		$sql.= 		"auth2.con_auth_file_edit as con_auth_file_edit2, ";
		$sql.= 		"auth2.con_auth_file_delete as con_auth_file_delete2, ";
		$sql.= 		"auth2.con_auth_file_workflow as con_auth_file_workflow2, ";
		$sql.= 		"auth2.con_auth_file_publish as con_auth_file_publish2, ";
		$sql.= 		"auth2.con_auth_file_archive as con_auth_file_archive2, ";
		$sql.= 		"auth2.con_auth_template_view as con_auth_template_view2, ";
		$sql.= 		"auth2.con_auth_template_edit as con_auth_template_edit2, ";
		$sql.= 		"auth2.con_auth_template_delete as con_auth_template_delete2, ";
		$sql.= 		"auth2.con_auth_template_workflow as con_auth_template_workflow2, ";
		$sql.= 		"auth2.con_auth_template_publish as con_auth_template_publish2, ";
		$sql.= 		"auth2.con_auth_template_archive as con_auth_template_archive2, ";
		$sql.= 		"auth2.con_auth_stylesheet_view as con_auth_stylesheet_view2, ";
		$sql.= 		"auth2.con_auth_stylesheet_edit as con_auth_stylesheet_edit2, ";
		$sql.= 		"auth2.con_auth_stylesheet_delete as con_auth_stylesheet_delete2, ";
		$sql.= 		"auth2.con_auth_stylesheet_workflow as con_auth_stylesheet_workflow2, ";
		$sql.= 		"auth2.con_auth_stylesheet_publish as con_auth_stylesheet_publish2, ";
		$sql.= 		"auth2.con_auth_stylesheet_archive as con_auth_stylesheet_archive2, ";
		$sql.= 		"auth2.con_auth_script_view as con_auth_script_view2, ";
		$sql.= 		"auth2.con_auth_script_edit as con_auth_script_edit2, ";
		$sql.= 		"auth2.con_auth_script_delete as con_auth_script_delete2, ";
		$sql.= 		"auth2.con_auth_script_workflow as con_auth_script_workflow2, ";
		$sql.= 		"auth2.con_auth_script_publish as con_auth_script_publish2, ";
		$sql.= 		"auth2.con_auth_script_archive as con_auth_script_archive2, ";

		$sql.= 		"auth3.con_auth_page_view as con_auth_page_view3, ";
		$sql.= 		"auth3.con_auth_page_edit as con_auth_page_edit3, ";
		$sql.= 		"auth3.con_auth_page_delete as con_auth_page_delete3, ";
		$sql.= 		"auth3.con_auth_page_workflow as con_auth_page_workflow3, ";
		$sql.= 		"auth3.con_auth_page_publish as con_auth_page_publish3, ";
		$sql.= 		"auth3.con_auth_page_archive as con_auth_page_archive3, ";
		$sql.= 		"auth3.con_auth_element_view as con_auth_element_view3, ";
		$sql.= 		"auth3.con_auth_element_edit as con_auth_element_edit3, ";
		$sql.= 		"auth3.con_auth_element_delete as con_auth_element_delete3, ";
		$sql.= 		"auth3.con_auth_element_workflow as con_auth_element_workflow3, ";
		$sql.= 		"auth3.con_auth_element_publish as con_auth_element_publish3, ";
		$sql.= 		"auth3.con_auth_element_archive as con_auth_element_archive3, ";
		$sql.= 		"auth3.con_auth_image_view as con_auth_image_view3, ";
		$sql.= 		"auth3.con_auth_image_edit as con_auth_image_edit3, ";
		$sql.= 		"auth3.con_auth_image_delete as con_auth_image_delete3, ";
		$sql.= 		"auth3.con_auth_image_workflow as con_auth_image_workflow3, ";
		$sql.= 		"auth3.con_auth_image_publish as con_auth_image_publish3, ";
		$sql.= 		"auth3.con_auth_image_archive as con_auth_image_archive3, ";
		$sql.= 		"auth3.con_auth_file_view as con_auth_file_view3, ";
		$sql.= 		"auth3.con_auth_file_edit as con_auth_file_edit3, ";
		$sql.= 		"auth3.con_auth_file_delete as con_auth_file_delete3, ";
		$sql.= 		"auth3.con_auth_file_workflow as con_auth_file_workflow3, ";
		$sql.= 		"auth3.con_auth_file_publish as con_auth_file_publish3, ";
		$sql.= 		"auth3.con_auth_file_archive as con_auth_file_archive3, ";
		$sql.= 		"auth3.con_auth_template_view as con_auth_template_view3, ";
		$sql.= 		"auth3.con_auth_template_edit as con_auth_template_edit3, ";
		$sql.= 		"auth3.con_auth_template_delete as con_auth_template_delete3, ";
		$sql.= 		"auth3.con_auth_template_workflow as con_auth_template_workflow3, ";
		$sql.= 		"auth3.con_auth_template_publish as con_auth_template_publish3, ";
		$sql.= 		"auth3.con_auth_template_archive as con_auth_template_archive3, ";
		$sql.= 		"auth3.con_auth_stylesheet_view as con_auth_stylesheet_view3, ";
		$sql.= 		"auth3.con_auth_stylesheet_edit as con_auth_stylesheet_edit3, ";
		$sql.= 		"auth3.con_auth_stylesheet_delete as con_auth_stylesheet_delete3, ";
		$sql.= 		"auth3.con_auth_stylesheet_workflow as con_auth_stylesheet_workflow3, ";
		$sql.= 		"auth3.con_auth_stylesheet_publish as con_auth_stylesheet_publish3, ";
		$sql.= 		"auth3.con_auth_stylesheet_archive as con_auth_stylesheet_archive3, ";
		$sql.= 		"auth3.con_auth_script_view as con_auth_script_view3, ";
		$sql.= 		"auth3.con_auth_script_edit as con_auth_script_edit3, ";
		$sql.= 		"auth3.con_auth_script_delete as con_auth_script_delete3, ";
		$sql.= 		"auth3.con_auth_script_workflow as con_auth_script_workflow3, ";
		$sql.= 		"auth3.con_auth_script_publish as con_auth_script_publish3, ";
		$sql.= 		"auth3.con_auth_script_archive as con_auth_script_archive3, ";

		$sql.= 		"auth4.con_auth_page_view as con_auth_page_view4, ";
		$sql.= 		"auth4.con_auth_page_edit as con_auth_page_edit4, ";
		$sql.= 		"auth4.con_auth_page_delete as con_auth_page_delete4, ";
		$sql.= 		"auth4.con_auth_page_workflow as con_auth_page_workflow4, ";
		$sql.= 		"auth4.con_auth_page_publish as con_auth_page_publish4, ";
		$sql.= 		"auth4.con_auth_page_archive as con_auth_page_archive4, ";
		$sql.= 		"auth4.con_auth_element_view as con_auth_element_view4, ";
		$sql.= 		"auth4.con_auth_element_edit as con_auth_element_edit4, ";
		$sql.= 		"auth4.con_auth_element_delete as con_auth_element_delete4, ";
		$sql.= 		"auth4.con_auth_element_workflow as con_auth_element_workflow4, ";
		$sql.= 		"auth4.con_auth_element_publish as con_auth_element_publish4, ";
		$sql.= 		"auth4.con_auth_element_archive as con_auth_element_archive4, ";
		$sql.= 		"auth4.con_auth_image_view as con_auth_image_view4, ";
		$sql.= 		"auth4.con_auth_image_edit as con_auth_image_edit4, ";
		$sql.= 		"auth4.con_auth_image_delete as con_auth_image_delete4, ";
		$sql.= 		"auth4.con_auth_image_workflow as con_auth_image_workflow4, ";
		$sql.= 		"auth4.con_auth_image_publish as con_auth_image_publish4, ";
		$sql.= 		"auth4.con_auth_image_archive as con_auth_image_archive4, ";
		$sql.= 		"auth4.con_auth_file_view as con_auth_file_view4, ";
		$sql.= 		"auth4.con_auth_file_edit as con_auth_file_edit4, ";
		$sql.= 		"auth4.con_auth_file_delete as con_auth_file_delete4, ";
		$sql.= 		"auth4.con_auth_file_workflow as con_auth_file_workflow4, ";
		$sql.= 		"auth4.con_auth_file_publish as con_auth_file_publish4, ";
		$sql.= 		"auth4.con_auth_file_archive as con_auth_file_archive4, ";
		$sql.= 		"auth4.con_auth_template_view as con_auth_template_view4, ";
		$sql.= 		"auth4.con_auth_template_edit as con_auth_template_edit4, ";
		$sql.= 		"auth4.con_auth_template_delete as con_auth_template_delete4, ";
		$sql.= 		"auth4.con_auth_template_workflow as con_auth_template_workflow4, ";
		$sql.= 		"auth4.con_auth_template_publish as con_auth_template_publish4, ";
		$sql.= 		"auth4.con_auth_template_archive as con_auth_template_archive4, ";
		$sql.= 		"auth4.con_auth_stylesheet_view as con_auth_stylesheet_view4, ";
		$sql.= 		"auth4.con_auth_stylesheet_edit as con_auth_stylesheet_edit4, ";
		$sql.= 		"auth4.con_auth_stylesheet_delete as con_auth_stylesheet_delete4, ";
		$sql.= 		"auth4.con_auth_stylesheet_workflow as con_auth_stylesheet_workflow4, ";
		$sql.= 		"auth4.con_auth_stylesheet_publish as con_auth_stylesheet_publish4, ";
		$sql.= 		"auth4.con_auth_stylesheet_archive as con_auth_stylesheet_archive4, ";
		$sql.= 		"auth4.con_auth_script_view as con_auth_script_view4, ";
		$sql.= 		"auth4.con_auth_script_edit as con_auth_script_edit4, ";
		$sql.= 		"auth4.con_auth_script_delete as con_auth_script_delete4, ";
		$sql.= 		"auth4.con_auth_script_workflow as con_auth_script_workflow4, ";
		$sql.= 		"auth4.con_auth_script_publish as con_auth_script_publish4, ";
		$sql.= 		"auth4.con_auth_script_archive as con_auth_script_archive4, ";

		$sql.= 		"cont.contentclass ";
		$sql.= "FROM ".$cont_tbl." cont ";
		$sql.= 		"LEFT JOIN content_user ON cont.content_id = content_user.content_id ";							//コンテンツ-ユーザ紐付【特権】(1:1)
		$sql.= 			"AND content_user.user_id = ? ";
		$param[] = $parameters["user_id"];
		$sql.= 			"AND (content_user.start_time <= ? OR content_user.start_time IS NULL) ";
		$param[] = $now_timestamp;
		$sql.= 			"AND (content_user.end_time > ? OR content_user.end_time IS NULL) ";
		$param[] = $now_timestamp;
		$sql.= 		"LEFT JOIN contentauth as auth1 ON content_user.contentauth_id = auth1.contentauth_id ";		//操作権限種別【特権】(1:1:1)
		$sql.= 			"AND auth1.active_flg = '1' ";
		$sql.= 		"LEFT JOIN content_usergroup ON cont.content_id = content_usergroup.content_id ";				//コンテンツ-ユーザグループ紐付(1:N)
		if(count($parameters["usergroups"]) > 0){
			$sql.= "AND content_usergroup.usergroup_id IN (";
			for($i=0;$i<count($parameters["usergroups"]);$i++){
				if($i != ""){ $sql .= ","; }
				$sql .= "?";
				$param[] = $parameters["usergroups"][$i];
			}
			$sql .= ") ";
		}
		$sql.= 			"AND (content_usergroup.start_time <= ? OR content_usergroup.start_time IS NULL) ";
		$param[] = $now_timestamp;
		$sql.= 			"AND (content_usergroup.end_time > ? OR content_usergroup.end_time IS NULL) ";
		$param[] = $now_timestamp;
		$sql.= 		"LEFT JOIN contentauth as auth2 ON content_usergroup.contentauth_id = auth2.contentauth_id ";	//コンテンツ操作権限種別(1:N:N)
		$sql.= 			"AND auth2.active_flg = '1' ";
		$sql.= 		"LEFT JOIN folder ON cont.folder_id = folder.folder_id ";										//管理フォルダ(1:1)
		$sql.= 			"AND (folder.start_time <= ? OR folder.start_time IS NULL) ";
		$param[] = $now_timestamp;
		$sql.= 			"AND (folder.end_time > ? OR folder.end_time IS NULL) ";
		$param[] = $now_timestamp;
		$sql.= 			"AND folder.active_flg = '1' ";
		$sql.= 		"LEFT JOIN foldertype ON folder.foldertype_id = foldertype.foldertype_id ";						//管理フォルダタイプ(1:1:1)
		$sql.= 			"AND foldertype.active_flg = '1' ";
		$sql.= 		"LEFT JOIN folder_user ON folder.folder_id = folder_user.folder_id ";							//管理フォルダ-ユーザ紐付【特権】(1:1)
		$sql.= 			"AND folder_user.user_id = ? ";
		$param[] = $parameters["user_id"];
		$sql.= 			"AND (folder_user.start_time <= ? OR folder_user.start_time IS NULL) ";
		$param[] = $now_timestamp;
		$sql.= 			"AND (folder_user.end_time > ? OR folder_user.end_time IS NULL) ";
		$param[] = $now_timestamp;
		$sql.= 		"LEFT JOIN contentauth as auth3 ON folder_user.contentauth_id = auth3.contentauth_id ";			//操作権限種別(1:N:N)
		$sql.= 			"AND auth3.active_flg = '1' ";
		$sql.= 		"LEFT JOIN folder_usergroup ON folder.folder_id = folder_usergroup.folder_id ";					//管理フォルダ-ユーザグループ紐付(1:N)
		if(count($parameters["usergroups"]) > 0){
			$sql.= "AND folder_usergroup.usergroup_id IN (";
			for($i=0;$i<count($parameters["usergroups"]);$i++){
				if($i != ""){ $sql .= ","; }
				$sql .= "?";
				$param[] = $parameters["usergroups"][$i];
			}
			$sql .= ") ";
		}
		$sql.= 			"AND (folder_usergroup.start_time <= ? OR folder_usergroup.start_time IS NULL) ";
		$param[] = $now_timestamp;
		$sql.= 			"AND (folder_usergroup.end_time > ? OR folder_usergroup.end_time IS NULL) ";
		$param[] = $now_timestamp;
		$sql.= 		"LEFT JOIN contentauth as auth4 ON folder_usergroup.contentauth_id = auth4.contentauth_id ";	//操作権限種別(1:N:N)
		$sql.= 			"AND auth4.active_flg = '1' ";
		$sql.= "WHERE cont.folder_id = ? ";																			//フォルダID指定
		$param[] = $parameters["folder_id"];
		//検索対象：ページ
		if(isset($parameters["page"]) && $parameters["page"] == ""){
			$sql.= "AND cont.contentclass <> '".SPConst::CONTENTCLASS_PAGE."' ";
		}
		//検索対象：部品
		if(isset($parameters["element"]) && $parameters["element"] == ""){
			$sql.= "AND cont.contentclass <> '".SPConst::CONTENTCLASS_ELEMENT."' ";
		}
		//検索対象：イメージ
		if(isset($parameters["image"]) && $parameters["image"] == ""){
			$sql.= "AND cont.contentclass <> '".SPConst::CONTENTCLASS_IMAGE."' ";
		}
		//検索対象：ファイル
		if(isset($parameters["file"]) && $parameters["file"] == ""){
			$sql.= "AND cont.contentclass <> '".SPConst::CONTENTCLASS_FILE."' ";
		}
		//検索対象：テンプレート
		if(isset($parameters["template"]) && $parameters["template"] == ""){
			$sql.= "AND cont.contentclass <> '".SPConst::CONTENTCLASS_TEMPLATE."' ";
		}
		//検索対象：スタイルシート
		if(isset($parameters["stylesheet"]) && $parameters["stylesheet"] == ""){
			$sql.= "AND cont.contentclass <> '".SPConst::CONTENTCLASS_STYLESHEET."' ";
		}
		//検索対象：スクリプト
		if(isset($parameters["script"]) && $parameters["script"] == ""){
			$sql.= "AND cont.contentclass <> '".SPConst::CONTENTCLASS_SCRIPT."' ";
		}
		//検索条件：キーワード
		if(isset($parameters["keyword"]) && $parameters["keyword"] != ""){
			$sql.= "AND (cont.title LIKE ? OR cont.content LIKE ? ) ";
			$param[] = "%".$parameters["keyword"]."%";
			$param[] = "%".$parameters["keyword"]."%";
		}
		$sql.= "AND (auth1.contentauth_id IS NOT NULL OR auth2.contentauth_id IS NOT NULL ";						//単体コンテンツに対して権限が存在する場合
		$sql.= 		"OR content_usergroup.content_usergroup_id IS NULL ) ";											//	または、コンテンツにユーザグループ権限が何も設定されていない場合
		$sql.= "AND (auth3.contentauth_id IS NOT NULL OR auth4.contentauth_id IS NOT NULL) ";						//管理フォルダに対して権限が存在する場合
		$sql.= "ORDER BY cont.sort_no ASC ";																		//ソート順

		$result = $this->query($sql,$param);
		return $result;
	}

	/*
	function getContentAndAuthListForEditByParameters($parameters){

		$now_timestamp = time();

		$cont_tbl = "content_public";

		$sql = "SELECT ";
		$sql.= 		"cont.content_id,";
		$sql.= 		"cont.updated,";
		$sql.= 		"cont.title,";
		$sql.= 		"cont.url,";
		$sql.= 		"cont.content,";
		$sql.= 		"cont.contentclass, ";
		$sql.= 		"auth1.con_auth_page_view as con_auth_page_view1, ";
		$sql.= 		"auth1.con_auth_page_edit as con_auth_page_edit1, ";
		$sql.= 		"auth1.con_auth_page_delete as con_auth_page_delete1, ";
		$sql.= 		"auth1.con_auth_page_workflow as con_auth_page_workflow1, ";
		$sql.= 		"auth1.con_auth_page_publish as con_auth_page_publish1, ";
		$sql.= 		"auth1.con_auth_page_archive as con_auth_page_archive1, ";
		$sql.= 		"auth1.con_auth_element_view as con_auth_element_view1, ";
		$sql.= 		"auth1.con_auth_element_edit as con_auth_element_edit1, ";
		$sql.= 		"auth1.con_auth_element_delete as con_auth_element_delete1, ";
		$sql.= 		"auth1.con_auth_element_workflow as con_auth_element_workflow1, ";
		$sql.= 		"auth1.con_auth_element_publish as con_auth_element_publish1, ";
		$sql.= 		"auth1.con_auth_element_archive as con_auth_element_archive1, ";
		$sql.= 		"auth1.con_auth_image_view as con_auth_image_view1, ";
		$sql.= 		"auth1.con_auth_image_edit as con_auth_image_edit1, ";
		$sql.= 		"auth1.con_auth_image_delete as con_auth_image_delete1, ";
		$sql.= 		"auth1.con_auth_image_workflow as con_auth_image_workflow1, ";
		$sql.= 		"auth1.con_auth_image_publish as con_auth_image_publish1, ";
		$sql.= 		"auth1.con_auth_image_archive as con_auth_image_archive1, ";
		$sql.= 		"auth1.con_auth_file_view as con_auth_file_view1, ";
		$sql.= 		"auth1.con_auth_file_edit as con_auth_file_edit1, ";
		$sql.= 		"auth1.con_auth_file_delete as con_auth_file_delete1, ";
		$sql.= 		"auth1.con_auth_file_workflow as con_auth_file_workflow1, ";
		$sql.= 		"auth1.con_auth_file_publish as con_auth_file_publish1, ";
		$sql.= 		"auth1.con_auth_file_archive as con_auth_file_archive1, ";
		$sql.= 		"auth1.con_auth_template_view as con_auth_template_view1, ";
		$sql.= 		"auth1.con_auth_template_edit as con_auth_template_edit1, ";
		$sql.= 		"auth1.con_auth_template_delete as con_auth_template_delete1, ";
		$sql.= 		"auth1.con_auth_template_workflow as con_auth_template_workflow1, ";
		$sql.= 		"auth1.con_auth_template_publish as con_auth_template_publish1, ";
		$sql.= 		"auth1.con_auth_template_archive as con_auth_template_archive1, ";
		$sql.= 		"auth1.con_auth_stylesheet_view as con_auth_stylesheet_view1, ";
		$sql.= 		"auth1.con_auth_stylesheet_edit as con_auth_stylesheet_edit1, ";
		$sql.= 		"auth1.con_auth_stylesheet_delete as con_auth_stylesheet_delete1, ";
		$sql.= 		"auth1.con_auth_stylesheet_workflow as con_auth_stylesheet_workflow1, ";
		$sql.= 		"auth1.con_auth_stylesheet_publish as con_auth_stylesheet_publish1, ";
		$sql.= 		"auth1.con_auth_stylesheet_archive as con_auth_stylesheet_archive1, ";
		$sql.= 		"auth1.con_auth_script_view as con_auth_script_view1, ";
		$sql.= 		"auth1.con_auth_script_edit as con_auth_script_edit1, ";
		$sql.= 		"auth1.con_auth_script_delete as con_auth_script_delete1, ";
		$sql.= 		"auth1.con_auth_script_workflow as con_auth_script_workflow1, ";
		$sql.= 		"auth1.con_auth_script_publish as con_auth_script_publish1, ";
		$sql.= 		"auth1.con_auth_script_archive as con_auth_script_archive1, ";

		$sql.= 		"auth2.con_auth_page_view as con_auth_page_view2, ";
		$sql.= 		"auth2.con_auth_page_edit as con_auth_page_edit2, ";
		$sql.= 		"auth2.con_auth_page_delete as con_auth_page_delete2, ";
		$sql.= 		"auth2.con_auth_page_workflow as con_auth_page_workflow2, ";
		$sql.= 		"auth2.con_auth_page_publish as con_auth_page_publish2, ";
		$sql.= 		"auth2.con_auth_page_archive as con_auth_page_archive2, ";
		$sql.= 		"auth2.con_auth_element_view as con_auth_element_view2, ";
		$sql.= 		"auth2.con_auth_element_edit as con_auth_element_edit2, ";
		$sql.= 		"auth2.con_auth_element_delete as con_auth_element_delete2, ";
		$sql.= 		"auth2.con_auth_element_workflow as con_auth_element_workflow2, ";
		$sql.= 		"auth2.con_auth_element_publish as con_auth_element_publish2, ";
		$sql.= 		"auth2.con_auth_element_archive as con_auth_element_archive2, ";
		$sql.= 		"auth2.con_auth_image_view as con_auth_image_view2, ";
		$sql.= 		"auth2.con_auth_image_edit as con_auth_image_edit2, ";
		$sql.= 		"auth2.con_auth_image_delete as con_auth_image_delete2, ";
		$sql.= 		"auth2.con_auth_image_workflow as con_auth_image_workflow2, ";
		$sql.= 		"auth2.con_auth_image_publish as con_auth_image_publish2, ";
		$sql.= 		"auth2.con_auth_image_archive as con_auth_image_archive2, ";
		$sql.= 		"auth2.con_auth_file_view as con_auth_file_view2, ";
		$sql.= 		"auth2.con_auth_file_edit as con_auth_file_edit2, ";
		$sql.= 		"auth2.con_auth_file_delete as con_auth_file_delete2, ";
		$sql.= 		"auth2.con_auth_file_workflow as con_auth_file_workflow2, ";
		$sql.= 		"auth2.con_auth_file_publish as con_auth_file_publish2, ";
		$sql.= 		"auth2.con_auth_file_archive as con_auth_file_archive2, ";
		$sql.= 		"auth2.con_auth_template_view as con_auth_template_view2, ";
		$sql.= 		"auth2.con_auth_template_edit as con_auth_template_edit2, ";
		$sql.= 		"auth2.con_auth_template_delete as con_auth_template_delete2, ";
		$sql.= 		"auth2.con_auth_template_workflow as con_auth_template_workflow2, ";
		$sql.= 		"auth2.con_auth_template_publish as con_auth_template_publish2, ";
		$sql.= 		"auth2.con_auth_template_archive as con_auth_template_archive2, ";
		$sql.= 		"auth2.con_auth_stylesheet_view as con_auth_stylesheet_view2, ";
		$sql.= 		"auth2.con_auth_stylesheet_edit as con_auth_stylesheet_edit2, ";
		$sql.= 		"auth2.con_auth_stylesheet_delete as con_auth_stylesheet_delete2, ";
		$sql.= 		"auth2.con_auth_stylesheet_workflow as con_auth_stylesheet_workflow2, ";
		$sql.= 		"auth2.con_auth_stylesheet_publish as con_auth_stylesheet_publish2, ";
		$sql.= 		"auth2.con_auth_stylesheet_archive as con_auth_stylesheet_archive2, ";
		$sql.= 		"auth2.con_auth_script_view as con_auth_script_view2, ";
		$sql.= 		"auth2.con_auth_script_edit as con_auth_script_edit2, ";
		$sql.= 		"auth2.con_auth_script_delete as con_auth_script_delete2, ";
		$sql.= 		"auth2.con_auth_script_workflow as con_auth_script_workflow2, ";
		$sql.= 		"auth2.con_auth_script_publish as con_auth_script_publish2, ";
		$sql.= 		"auth2.con_auth_script_archive as con_auth_script_archive2, ";

		$sql.= 		"auth3.con_auth_page_view as con_auth_page_view3, ";
		$sql.= 		"auth3.con_auth_page_edit as con_auth_page_edit3, ";
		$sql.= 		"auth3.con_auth_page_delete as con_auth_page_delete3, ";
		$sql.= 		"auth3.con_auth_page_workflow as con_auth_page_workflow3, ";
		$sql.= 		"auth3.con_auth_page_publish as con_auth_page_publish3, ";
		$sql.= 		"auth3.con_auth_page_archive as con_auth_page_archive3, ";
		$sql.= 		"auth3.con_auth_element_view as con_auth_element_view3, ";
		$sql.= 		"auth3.con_auth_element_edit as con_auth_element_edit3, ";
		$sql.= 		"auth3.con_auth_element_delete as con_auth_element_delete3, ";
		$sql.= 		"auth3.con_auth_element_workflow as con_auth_element_workflow3, ";
		$sql.= 		"auth3.con_auth_element_publish as con_auth_element_publish3, ";
		$sql.= 		"auth3.con_auth_element_archive as con_auth_element_archive3, ";
		$sql.= 		"auth3.con_auth_image_view as con_auth_image_view3, ";
		$sql.= 		"auth3.con_auth_image_edit as con_auth_image_edit3, ";
		$sql.= 		"auth3.con_auth_image_delete as con_auth_image_delete3, ";
		$sql.= 		"auth3.con_auth_image_workflow as con_auth_image_workflow3, ";
		$sql.= 		"auth3.con_auth_image_publish as con_auth_image_publish3, ";
		$sql.= 		"auth3.con_auth_image_archive as con_auth_image_archive3, ";
		$sql.= 		"auth3.con_auth_file_view as con_auth_file_view3, ";
		$sql.= 		"auth3.con_auth_file_edit as con_auth_file_edit3, ";
		$sql.= 		"auth3.con_auth_file_delete as con_auth_file_delete3, ";
		$sql.= 		"auth3.con_auth_file_workflow as con_auth_file_workflow3, ";
		$sql.= 		"auth3.con_auth_file_publish as con_auth_file_publish3, ";
		$sql.= 		"auth3.con_auth_file_archive as con_auth_file_archive3, ";
		$sql.= 		"auth3.con_auth_template_view as con_auth_template_view3, ";
		$sql.= 		"auth3.con_auth_template_edit as con_auth_template_edit3, ";
		$sql.= 		"auth3.con_auth_template_delete as con_auth_template_delete3, ";
		$sql.= 		"auth3.con_auth_template_workflow as con_auth_template_workflow3, ";
		$sql.= 		"auth3.con_auth_template_publish as con_auth_template_publish3, ";
		$sql.= 		"auth3.con_auth_template_archive as con_auth_template_archive3, ";
		$sql.= 		"auth3.con_auth_stylesheet_view as con_auth_stylesheet_view3, ";
		$sql.= 		"auth3.con_auth_stylesheet_edit as con_auth_stylesheet_edit3, ";
		$sql.= 		"auth3.con_auth_stylesheet_delete as con_auth_stylesheet_delete3, ";
		$sql.= 		"auth3.con_auth_stylesheet_workflow as con_auth_stylesheet_workflow3, ";
		$sql.= 		"auth3.con_auth_stylesheet_publish as con_auth_stylesheet_publish3, ";
		$sql.= 		"auth3.con_auth_stylesheet_archive as con_auth_stylesheet_archive3, ";
		$sql.= 		"auth3.con_auth_script_view as con_auth_script_view3, ";
		$sql.= 		"auth3.con_auth_script_edit as con_auth_script_edit3, ";
		$sql.= 		"auth3.con_auth_script_delete as con_auth_script_delete3, ";
		$sql.= 		"auth3.con_auth_script_workflow as con_auth_script_workflow3, ";
		$sql.= 		"auth3.con_auth_script_publish as con_auth_script_publish3, ";
		$sql.= 		"auth3.con_auth_script_archive as con_auth_script_archive3, ";

		$sql.= 		"auth4.con_auth_page_view as con_auth_page_view4, ";
		$sql.= 		"auth4.con_auth_page_edit as con_auth_page_edit4, ";
		$sql.= 		"auth4.con_auth_page_delete as con_auth_page_delete4, ";
		$sql.= 		"auth4.con_auth_page_workflow as con_auth_page_workflow4, ";
		$sql.= 		"auth4.con_auth_page_publish as con_auth_page_publish4, ";
		$sql.= 		"auth4.con_auth_page_archive as con_auth_page_archive4, ";
		$sql.= 		"auth4.con_auth_element_view as con_auth_element_view4, ";
		$sql.= 		"auth4.con_auth_element_edit as con_auth_element_edit4, ";
		$sql.= 		"auth4.con_auth_element_delete as con_auth_element_delete4, ";
		$sql.= 		"auth4.con_auth_element_workflow as con_auth_element_workflow4, ";
		$sql.= 		"auth4.con_auth_element_publish as con_auth_element_publish4, ";
		$sql.= 		"auth4.con_auth_element_archive as con_auth_element_archive4, ";
		$sql.= 		"auth4.con_auth_image_view as con_auth_image_view4, ";
		$sql.= 		"auth4.con_auth_image_edit as con_auth_image_edit4, ";
		$sql.= 		"auth4.con_auth_image_delete as con_auth_image_delete4, ";
		$sql.= 		"auth4.con_auth_image_workflow as con_auth_image_workflow4, ";
		$sql.= 		"auth4.con_auth_image_publish as con_auth_image_publish4, ";
		$sql.= 		"auth4.con_auth_image_archive as con_auth_image_archive4, ";
		$sql.= 		"auth4.con_auth_file_view as con_auth_file_view4, ";
		$sql.= 		"auth4.con_auth_file_edit as con_auth_file_edit4, ";
		$sql.= 		"auth4.con_auth_file_delete as con_auth_file_delete4, ";
		$sql.= 		"auth4.con_auth_file_workflow as con_auth_file_workflow4, ";
		$sql.= 		"auth4.con_auth_file_publish as con_auth_file_publish4, ";
		$sql.= 		"auth4.con_auth_file_archive as con_auth_file_archive4, ";
		$sql.= 		"auth4.con_auth_template_view as con_auth_template_view4, ";
		$sql.= 		"auth4.con_auth_template_edit as con_auth_template_edit4, ";
		$sql.= 		"auth4.con_auth_template_delete as con_auth_template_delete4, ";
		$sql.= 		"auth4.con_auth_template_workflow as con_auth_template_workflow4, ";
		$sql.= 		"auth4.con_auth_template_publish as con_auth_template_publish4, ";
		$sql.= 		"auth4.con_auth_template_archive as con_auth_template_archive4, ";
		$sql.= 		"auth4.con_auth_stylesheet_view as con_auth_stylesheet_view4, ";
		$sql.= 		"auth4.con_auth_stylesheet_edit as con_auth_stylesheet_edit4, ";
		$sql.= 		"auth4.con_auth_stylesheet_delete as con_auth_stylesheet_delete4, ";
		$sql.= 		"auth4.con_auth_stylesheet_workflow as con_auth_stylesheet_workflow4, ";
		$sql.= 		"auth4.con_auth_stylesheet_publish as con_auth_stylesheet_publish4, ";
		$sql.= 		"auth4.con_auth_stylesheet_archive as con_auth_stylesheet_archive4, ";
		$sql.= 		"auth4.con_auth_script_view as con_auth_script_view4, ";
		$sql.= 		"auth4.con_auth_script_edit as con_auth_script_edit4, ";
		$sql.= 		"auth4.con_auth_script_delete as con_auth_script_delete4, ";
		$sql.= 		"auth4.con_auth_script_workflow as con_auth_script_workflow4, ";
		$sql.= 		"auth4.con_auth_script_publish as con_auth_script_publish4, ";
		$sql.= 		"auth4.con_auth_script_archive as con_auth_script_archive4, ";

		$sql.= 		"cont.contentclass ";
		$sql.= "FROM ".$cont_tbl." cont ";
		$sql.= 		"LEFT JOIN content_user ON cont.content_id = content_user.content_id ";							//コンテンツ-ユーザ紐付【特権】(1:1)
		$sql.= 			"AND content_user.user_id = ? ";
		$param[] = $parameters["user_id"];
		$sql.= 			"AND (content_user.start_time <= ? OR content_user.start_time IS NULL) ";
		$param[] = $now_timestamp;
		$sql.= 			"AND (content_user.end_time > ? OR content_user.end_time IS NULL) ";
		$param[] = $now_timestamp;
		$sql.= 		"LEFT JOIN contentauth as auth1 ON content_user.contentauth_id = auth1.contentauth_id ";		//操作権限種別【特権】(1:1:1)
		$sql.= 			"AND auth1.active_flg = '1' ";
		$sql.= 		"LEFT JOIN content_usergroup ON cont.content_id = content_usergroup.content_id ";				//コンテンツ-ユーザグループ紐付(1:N)
		if(count($parameters["usergroups"]) > 0){
			$sql.= "AND content_usergroup.usergroup_id IN (";
			for($i=0;$i<count($parameters["usergroups"]);$i++){
				if($i != ""){ $sql .= ","; }
				$sql .= "?";
				$param[] = $parameters["usergroups"][$i];
			}
			$sql .= ") ";
		}
		$sql.= 			"AND (content_usergroup.start_time <= ? OR content_usergroup.start_time IS NULL) ";
		$param[] = $now_timestamp;
		$sql.= 			"AND (content_usergroup.end_time > ? OR content_usergroup.end_time IS NULL) ";
		$param[] = $now_timestamp;
		$sql.= 		"LEFT JOIN contentauth as auth2 ON content_usergroup.contentauth_id = auth2.contentauth_id ";	//コンテンツ操作権限種別(1:N:N)
		$sql.= 			"AND auth2.active_flg = '1' ";
		$sql.= 		"LEFT JOIN folder ON cont.folder_id = folder.folder_id ";										//管理フォルダ(1:1)
		$sql.= 			"AND (folder.start_time <= ? OR folder.start_time IS NULL) ";
		$param[] = $now_timestamp;
		$sql.= 			"AND (folder.end_time > ? OR folder.end_time IS NULL) ";
		$param[] = $now_timestamp;
		$sql.= 			"AND folder.active_flg = '1' ";
		$sql.= 		"LEFT JOIN foldertype ON folder.foldertype_id = foldertype.foldertype_id ";						//管理フォルダタイプ(1:1:1)
		$sql.= 			"AND foldertype.active_flg = '1' ";
		$sql.= 		"LEFT JOIN folder_user ON folder.folder_id = folder_user.folder_id ";							//管理フォルダ-ユーザ紐付【特権】(1:1)
		$sql.= 			"AND folder_user.user_id = ? ";
		$param[] = $parameters["user_id"];
		$sql.= 			"AND (folder_user.start_time <= ? OR folder_user.start_time IS NULL) ";
		$param[] = $now_timestamp;
		$sql.= 			"AND (folder_user.end_time > ? OR folder_user.end_time IS NULL) ";
		$param[] = $now_timestamp;
		$sql.= 		"LEFT JOIN contentauth as auth3 ON folder_user.contentauth_id = auth3.contentauth_id ";			//操作権限種別(1:N:N)
		$sql.= 			"AND auth3.active_flg = '1' ";
		$sql.= 		"LEFT JOIN folder_usergroup ON folder.folder_id = folder_usergroup.folder_id ";					//管理フォルダ-ユーザグループ紐付(1:N)
		if(count($parameters["usergroups"]) > 0){
			$sql.= "AND folder_usergroup.usergroup_id IN (";
			for($i=0;$i<count($parameters["usergroups"]);$i++){
				if($i != ""){ $sql .= ","; }
				$sql .= "?";
				$param[] = $parameters["usergroups"][$i];
			}
			$sql .= ") ";
		}
		$sql.= 			"AND (folder_usergroup.start_time <= ? OR folder_usergroup.start_time IS NULL) ";
		$param[] = $now_timestamp;
		$sql.= 			"AND (folder_usergroup.end_time > ? OR folder_usergroup.end_time IS NULL) ";
		$param[] = $now_timestamp;
		$sql.= 		"LEFT JOIN contentauth as auth4 ON folder_usergroup.contentauth_id = auth4.contentauth_id ";	//操作権限種別(1:N:N)
		$sql.= 			"AND auth4.active_flg = '1' ";
		$sql.= "WHERE cont.content_id = ? ";																		//コンテンツID指定
		$param[] = $parameters["content_id"];
		$sql.= "AND (auth1.contentauth_id IS NOT NULL OR auth2.contentauth_id IS NOT NULL ";						//単体コンテンツに対して権限が存在する場合
		$sql.= 		"OR content_usergroup.content_usergroup_id IS NULL ) ";											//	または、コンテンツにユーザグループ権限が何も設定されていない場合
		$sql.= "AND (auth3.contentauth_id IS NOT NULL OR auth4.contentauth_id IS NOT NULL) ";						//管理フォルダに対して権限が存在する場合
		$sql.= "ORDER BY cont.sort_no ASC ";																		//ソート順

		$result = $this->query($sql,$param);
		return $result;
	}
	*/

	/**
	 * ユーザID及び所属ユーザグループ配列、フォルダIDに基づき、対象コンテンツの権限一覧（非ユニーク）を取得する
	 * @param int $user_id ユーザID
	 * @param array $usergroupList ユーザグループ配列
	 * @param int $content_id コンテンツID
	 * @param array $auth_columns 権限カラム一覧
	 * @return Ambigous <クエリ実行結果, boolean>
	 */
	function getContentAuthList($user_id,$usergroupList,$content_id,$auth_columns){
		$now_timestamp = time();

		$sql = "SELECT ";
		$sql.= 		"cont.content_id,";
		$sql.= 		"cont.folder_id,";
		$sql.= self::make_auth_column_str("auth1", "1", $auth_columns);
		$sql.= 		",";
		$sql.= self::make_auth_column_str("auth2", "2", $auth_columns);
		$sql.= " ";
		$sql.= "FROM content cont ";
		$sql.= "LEFT JOIN contenttype ON cont.contenttype_id = contenttype.contenttype_id ";						//コンテンツ種別(1:1) TODO 使用用途確認
		$sql.= 		"AND contenttype.active_flg = '1' ";
		$sql.= "LEFT JOIN content_user ON cont.folder_id = content_user.content_id ";								//コンテンツ-ユーザ紐付(1:1)
		$sql.= 		"AND content_user.content_id = ? ";
		$param[] = $content_id;
		$sql.= 		"AND content_user.user_id = ? ";
		$param[] = $user_id;
		$sql.= 		"AND (content_user.start_time <= ? OR content_user.start_time IS NULL) ";
		$param[] = $now_timestamp;
		$sql.= 		"AND (content_user.end_time > ? OR content_user.end_time IS NULL) ";
		$param[] = $now_timestamp;
		$sql.= "LEFT JOIN contentauth as auth1 ON content_user.contentauth_id = auth1.contentauth_id ";				//コンテンツ操作権限種別(1:1:1)
		$sql.= 		"AND auth1.active_flg = '1' ";
		$sql.= "LEFT JOIN content_usergroup ON cont.content_id = content_usergroup.content_id ";					//コンテンツ-ユーザグループ紐付(1:N)
		$sql.= 		"AND content_usergroup.content_id = ? ";
		$param[] = $content_id;
		if(count($usergroupList) > 0){
			$sql.= "AND (content_usergroup.usergroup_id IN (";
			for($i=0;$i<count($usergroupList);$i++){
				if($i != ""){ $sql .= ","; }
				$sql .= "?";
				$param[] = $usergroupList[$i];
			}
			$sql .= ") ";
		}
		$sql.= "OR content_usergroup.usergroup_id = '0') ";															//全てのユーザグループに権限がある場合
		$sql.= 		"AND (content_usergroup.start_time <= ? OR content_usergroup.start_time IS NULL) ";
		$param[] = $now_timestamp;
		$sql.= 		"AND (content_usergroup.end_time > ? OR content_usergroup.end_time IS NULL) ";
		$param[] = $now_timestamp;
		$sql.= "LEFT JOIN contentauth as auth2 ON content_usergroup.contentauth_id = auth2.contentauth_id ";		//コンテンツ操作権限種別(1:N:N)
		$sql.= 		"AND auth2.active_flg = '1' ";
		$sql.= "WHERE cont.content_id = ? ";																		//コンテンツID指定
		$param[] = $content_id;
		$sql.= "AND auth1.contentauth_id IS NOT NULL OR auth2.contentauth_id IS NOT NULL ";							//何らかの権限が存在する場合

		$result = $this->query($sql,$param);
		return $result;
	}

	function getContentDataByContentId($content_id){
		require_once(dirname(__FILE__).'/AddInfoSelect.php');
		$now_timestamp = time();

		$cont_tbl = $this->tablename;
		$sql = "SELECT ";
		$sql.= 		"cont.content_id,";
		$sql.= 		"cont.updated,";
		$sql.= 		"cont.published,";
		$sql.= 		"cont.unpublished,";
		$sql.= 		"cont.schedule_publish,";
		$sql.= 		"cont.schedule_unpublish,";
		$sql.= 		"cont.revision,";
		$sql.= 		"cont.contentclass, ";
		$sql.= 		"cont.title,";
		$sql.= 		"cont.url,";
		$sql.= 		"cont.content,";
		$sql.= 		"cont.stylesheet_index,";
		$sql.= 		"cont.script_index,";
		$sql.= 		"cont.comment,";
		$sql.= 		"cont.keywords,";
		$sql.= 		"cont.description,";
		$sql.= 		"cont.author,";
		$sql.= 		"cont.media,";
		$sql.= 		"cont.editmode,";
		$sql.= 		"cont.doctype,";
		$sql.= 		"cont.html_attr,";
		$sql.= 		"cont.head_attr,";
		$sql.= 		"cont.head_code,";
		$sql.= 		"cont.body_attr,";
		$sql.= 		"cont.title_prefix,";
		$sql.= 		"cont.title_suffix,";
		$sql.= 		"cont.doctype,";
		$sql.= 		"cont.static_mode,";
		$sql.= 		"cont.php_mode,";
		$sql.= 		"cont.schedule_type,";
		$sql.= 		"cont.sort_no,";
		$sql.= 		"folder.folder_id,";
		$sql.= 		"folder.folder_name,";
		$sql.= 		"elementtype.elementtype_id, ";
		$sql.= 		"elementtype.elementtype_name, ";
		$sql.= 		"contenttype.contenttype_id, ";
		$sql.= 		"contenttype.contenttype_name, ";
		$sql.= 		"template.content_id as template_id, ";
		$sql.= 		"template.title as template_name, ";
		$sql.= 		"checkout.user_id as checkout_user_id, ";
		$sql.= 		"checkout.name as checkout_user_name, ";
		$sql.= 		"workflow.workflowstate_id, ";
		$sql.= 		"workflow.workflowstate_name ";
		$sql.= "FROM ".$cont_tbl." cont ";
		$sql.= 		"LEFT JOIN folder ON cont.folder_id = folder.folder_id ";										//管理フォルダ(1:1)
		$sql.= 			"AND (folder.start_time <= ? OR folder.start_time IS NULL) ";
		$param[] = $now_timestamp;
		$sql.= 			"AND (folder.end_time > ? OR folder.end_time IS NULL) ";
		$param[] = $now_timestamp;
		$sql.= 			"AND folder.active_flg = '1' ";
		$sql.= 		"LEFT JOIN elementtype ON cont.elementtype_id = elementtype.elementtype_id ";					//部品種別(1:1)
		$sql.= 			"AND elementtype.active_flg = '1' ";
		$sql.= 		"LEFT JOIN contenttype ON cont.contenttype_id = contenttype.contenttype_id ";					//コンテンツ種別(1:1)
		$sql.= 			"AND contenttype.active_flg = '1' ";
		$sql.= 		"LEFT JOIN content template ON cont.template_id = template.content_id ";						//テンプレート(1:1)
		$sql.= 			"AND template.active_flg = '1' ";
		$sql.= 		"LEFT JOIN user checkout ON cont.checkout_user_id = checkout.user_id ";								//チェックアウトユーザ(1:1)
		$sql.= 			"AND (checkout.start_time <= ? OR checkout.start_time IS NULL) ";
		$param[] = $now_timestamp;
		$sql.= 			"AND (checkout.end_time > ? OR checkout.end_time IS NULL) ";
		$param[] = $now_timestamp;
		$sql.= 			"AND checkout.active_flg = '1' ";
		$sql.= 		"LEFT JOIN workflowstate workflow ON cont.workflowstate_id = workflow.workflowstate_id ";		//ワークフロー状態(1:1)
		$sql.= 			"AND workflow.active_flg = '1' ";
		$sql.= "WHERE cont.content_id = ? ";																		//コンテンツID指定
		$param[] = $content_id;

		$result = $this->query($sql,$param,DB::FETCH);
		if(!$result){
			return $result;
		}

		//追加情報取得
		$addinfoList = $this->ContentAddInfo->getListByParameters(array("content_id" => $content_id),array("sort_no" => "ASC"));
		foreach($addinfoList as $addinfo){
			if(!isset($result[$addinfo["name"]])){
				if($addinfo["selectname"] != null && $addinfo["selectname"] != ""){
					//選択肢型
					$result[$addinfo["name"]] = $addinfo["optionvalue"];

				}else{
					//入力型
					$result[$addinfo["name"]] = $addinfo["addinfo_content"];
				}
			}
			$result["addinfo"][$addinfo["name"]] = $addinfo;
		}

		return $result;
	}

	function getContentDataByUrl($url){
		require_once(dirname(__FILE__).'/AddInfoSelect.php');
		$now_timestamp = time();

		$cont_tbl = $this->tablename;
		$sql = "SELECT ";
		$sql.= 		"cont.content_id,";
		$sql.= 		"cont.updated,";
		$sql.= 		"cont.published,";
		$sql.= 		"cont.unpublished,";
		$sql.= 		"cont.schedule_publish,";
		$sql.= 		"cont.schedule_unpublish,";
		$sql.= 		"cont.revision,";
		$sql.= 		"cont.contentclass, ";
		$sql.= 		"cont.title,";
		$sql.= 		"cont.url,";
		$sql.= 		"cont.content,";
		$sql.= 		"cont.stylesheet_index,";
		$sql.= 		"cont.script_index,";
		$sql.= 		"cont.comment,";
		$sql.= 		"cont.keywords,";
		$sql.= 		"cont.description,";
		$sql.= 		"cont.author,";
		$sql.= 		"cont.media,";
		$sql.= 		"cont.editmode,";
		$sql.= 		"cont.doctype,";
		$sql.= 		"cont.html_attr,";
		$sql.= 		"cont.head_attr,";
		$sql.= 		"cont.head_code,";
		$sql.= 		"cont.body_attr,";
		$sql.= 		"cont.title_prefix,";
		$sql.= 		"cont.title_suffix,";
		$sql.= 		"cont.doctype,";
		$sql.= 		"cont.static_mode,";
		$sql.= 		"cont.php_mode,";
		$sql.= 		"cont.schedule_type,";
		$sql.= 		"cont.sort_no,";
		$sql.= 		"folder.folder_id,";
		$sql.= 		"folder.folder_name,";
		$sql.= 		"elementtype.elementtype_id, ";
		$sql.= 		"elementtype.elementtype_name, ";
		$sql.= 		"contenttype.contenttype_id, ";
		$sql.= 		"contenttype.contenttype_name, ";
		$sql.= 		"template.content_id as template_id, ";
		$sql.= 		"template.title as template_name, ";
		$sql.= 		"checkout.user_id as checkout_user_id, ";
		$sql.= 		"checkout.name as checkout_user_name, ";
		$sql.= 		"workflow.workflowstate_id, ";
		$sql.= 		"workflow.workflowstate_name ";
		$sql.= "FROM ".$cont_tbl." cont ";
		$sql.= 		"LEFT JOIN folder ON cont.folder_id = folder.folder_id ";										//管理フォルダ(1:1)
		$sql.= 			"AND (folder.start_time <= ? OR folder.start_time IS NULL) ";
		$param[] = $now_timestamp;
		$sql.= 			"AND (folder.end_time > ? OR folder.end_time IS NULL) ";
		$param[] = $now_timestamp;
		$sql.= 			"AND folder.active_flg = '1' ";
		$sql.= 		"LEFT JOIN elementtype ON cont.elementtype_id = elementtype.elementtype_id ";					//部品種別(1:1)
		$sql.= 			"AND elementtype.active_flg = '1' ";
		$sql.= 		"LEFT JOIN contenttype ON cont.contenttype_id = contenttype.contenttype_id ";					//コンテンツ種別(1:1)
		$sql.= 			"AND contenttype.active_flg = '1' ";
		$sql.= 		"LEFT JOIN content template ON cont.template_id = template.content_id ";						//テンプレート(1:1)
		$sql.= 			"AND template.active_flg = '1' ";
		$sql.= 		"LEFT JOIN user checkout ON cont.checkout_user_id = checkout.user_id ";								//チェックアウトユーザ(1:1)
		$sql.= 			"AND (checkout.start_time <= ? OR checkout.start_time IS NULL) ";
		$param[] = $now_timestamp;
		$sql.= 			"AND (checkout.end_time > ? OR checkout.end_time IS NULL) ";
		$param[] = $now_timestamp;
		$sql.= 			"AND checkout.active_flg = '1' ";
		$sql.= 		"LEFT JOIN workflowstate workflow ON cont.workflowstate_id = workflow.workflowstate_id ";		//ワークフロー状態(1:1)
		$sql.= 			"AND workflow.active_flg = '1' ";
		$sql.= "WHERE cont.url = ? ";																		//コンテンツID指定
		$param[] = $url;

		$result = $this->query($sql,$param,DB::FETCH);
		if(!$result){
			return $result;
		}

		//追加情報取得
		$addinfoList = $this->ContentAddInfo->getListByParameters(array("content_id" => $result["content_id"]),array("sort_no" => "ASC"));
		foreach($addinfoList as $addinfo){
			if(!isset($result[$addinfo["name"]])){
				if($addinfo["selectname"] != null && $addinfo["selectname"] != ""){
					//選択肢型
					$result[$addinfo["name"]] = $addinfo["optionvalue"];

				}else{
					//入力型
					$result[$addinfo["name"]] = $addinfo["addinfo_content"];
				}
			}
			$result["addinfo"][$addinfo["name"]] = $addinfo;
		}

		return $result;
	}

	static function make_auth_column_str($name,$index,$columns){
		$str = "";
		foreach($columns as $column){
			if($str != ""){ $str.= ","; }
			$str.= $name.".".$column." as ".$column.$index;
		}
		return $str;
	}

	function getContentArchiveListByContentId($content_id,$start = 0,$num = 20){
		$sql = "SELECT ";
		$sql.= 		"cont.content_id,";
		$sql.= 		"cont.content_archive_id,";
		$sql.= 		"cont.updated, ";
		$sql.= 		"cont.archive_type,";
		$sql.= 		"cont.archive_time, ";
		$sql.= 		"cre_user.name as created_by_name, ";
		$sql.= 		"upd_user.name as updated_by_name ";
		$sql.= "FROM ".$this->table_content." cont ";
		$sql.= 		"LEFT JOIN  user cre_user ";
		$sql.= 			"ON  cont.created_by = cre_user.user_id ";
		$sql.= 		"LEFT JOIN  user upd_user ";
		$sql.= 			"ON  cont.updated_by = upd_user.user_id ";
		$sql.= "WHERE cont.content_id = ? ";
		$param[] = $content_id;
		$sql.= "AND cont.active_flg = '1' ";
		$sql.= "ORDER BY cont.archive_time DESC,cont.updated DESC ";
		$sql.= "LIMIT ".$start.",".$num;

		$result = $this->query($sql,$param);
		return $result;
	}

	function getContentScheduleListByContentId($content_id){
		$now_timestamp = time();

		$sql = "SELECT ";
		$sql.= 		"cont.content_id,";
		$sql.= 		"cont.content_schedule_id,";
		$sql.= 		"cont.schedule_publish,";
		$sql.= 		"cont.schedule_unpublish,";
		$sql.= 		"cont.schedule_type ";
		$sql.= "FROM ".$this->table_content." cont ";
		$sql.= "WHERE cont.content_id = ? ";
		$param[] = $content_id;
		$sql.= "AND active_flg = '1' ";
		$sql.= "AND (schedule_publish >= ? OR schedule_unpublish >= ? OR schedule_unpublish IS NULL ) ";
		$param[] = $now_timestamp;
		$param[] = $now_timestamp;
		$sql.= "ORDER BY cont.schedule_time DESC ";

		$result = $this->query($sql,$param);
		return $result;
	}

	function getContentManagementListByParameters($parameters){
		$sql = "SELECT cont.*,count(sche.content_schedule_id) as sche_cnt,pub.content_id as content_public_id,pub.updated as content_public_updated,domain.domain,domain.base_dir_path,workflow.workflowstate_name FROM content cont ";
		$sql.= "LEFT JOIN content_public pub ";
		$sql.= 		"ON  cont.content_id = pub.content_id ";
		$sql.= "LEFT JOIN content_schedule sche ";
		$sql.= 		"ON  cont.content_id = sche.content_id ";
		$sql.= "INNER JOIN folder ";
		$sql.= 		"ON  cont.folder_id = folder.folder_id ";
		$sql.= "INNER JOIN domain ";
		$sql.= 		"ON  folder.domain_id = domain.domain_id ";
		$sql.= "LEFT JOIN workflowstate workflow ON cont.workflowstate_id = workflow.workflowstate_id ";
		$sql.= 		"AND workflow.active_flg = '1' ";
		$sql.= "WHERE cont.contentclass = ? " ;
		$params[] = $parameters["contentclass"];
		if(isset($parameters["folder_id"])){
			$sql.= "AND cont.folder_id = ? " ;
			$params[] = $parameters["folder_id"];
		}
		if(isset($parameters["title"]) && isset($parameters["content"])){
			$sql.= "AND (cont.title LIKE ? OR cont.content LIKE ? ) " ;
			$params[] = $parameters["title"];
			$params[] = $parameters["content"];
		}else{
			if(isset($parameters["title"])){
				$sql.= "AND cont.title LIKE ? " ;
				$params[] = $parameters["title"];
			}
			if(isset($parameters["content"])){
				$sql.= "AND cont.content LIKE ? " ;
				$params[] = $parameters["content"];
			}
		}
		$sql.= "GROUP BY cont.content_id ";
		$sql.= "ORDER BY cont.sort_no ASC " ;

		$result = $this->query($sql,$params);
		return $result;
	}

	function getDeletedArchiveListByParameters($parameters){
		$sql = "SELECT * FROM (SELECT cont.*,domain.domain,domain.base_dir_path FROM content_archive cont ";
		$sql.= "LEFT JOIN content delcont ";
		$sql.= 		"ON  cont.content_id = delcont.content_id ";
		$sql.= "INNER JOIN folder ";
		$sql.= 		"ON  cont.folder_id = folder.folder_id ";
		$sql.= "INNER JOIN domain ";
		$sql.= 		"ON  folder.domain_id = domain.domain_id ";
		$sql.= "WHERE cont.contentclass = ? " ;
		$params[] = $parameters["contentclass"];
		$sql.= "AND delcont.content_id IS NULL " ;
		if(isset($parameters["folder_id"])){
			$sql.= "AND cont.folder_id = ? " ;
			$params[] = $parameters["folder_id"];
		}
		$sql.= "ORDER BY cont.archive_time DESC ) as tmp " ;
		$sql.= "GROUP BY content_id " ;

		$result = $this->query($sql,$params);
		return $result;
	}


	function insertContent($dataSet,$user_id){
		$Content = new Content(Content::TABLE_MANAGEMENT);
		//追加情報退避

		$insertSet["created"] = $this->now_timestamp;
		$insertSet["created_by"] = $user_id;

		//コンテンツの作成
		if(!$Content->insert($insertSet)){
			Logger::error("コンテンツの作成に失敗しました。",$insertSet);
			return $result;
		}

		$dataSet["content_id"] = $Content->last_insert_id();
		if(!$this->updateContent($dataSet["content_id"], $dataSet, $user_id)){
			return false;
		}else{
			return $dataSet["content_id"];
		}
	}

	function updateContent($content_id,$dataSet,$user_id){
		require_once(dirname(__FILE__).'/../ApplicationCommon/Logger.php');
		$Content = new Content(Content::TABLE_MANAGEMENT);
		$ContentAddInfo = new ContentAddInfo(ContentAddInfo::TABLE_MANAGEMENT);
		$ContentElement = new ContentElement(ContentElement::TABLE_MANAGEMENT);

		//追加情報退避
		if(isset($dataSet["addinfo"])){
			$addinfo = $dataSet["addinfo"];
			unset($dataSet["addinfo"]);
		}

		//コンテンツの更新
		$where = array("content_id" => $content_id);
		$dataSet["updated"] = $this->now_timestamp;
		$dataSet["updated_by"] = $user_id;
		$result = $Content->update($where, $dataSet);

		if(!$result){
			Logger::error("コンテンツの更新に失敗しました。",$where + $dataSet);
			return $result;
		}

		//スタイルシート情報の管理コンテンツ部品テーブルへの格納
		if(isset($dataSet["stylesheet_index"])){
			//既存情報の取得
			$where = array("content_id" => $content_id,"contentclass" => "stylesheet");
			$stylesheetOld = $ContentElement->getListByParameters($where);
			$stylesheetDelete = $stylesheetOld;

			$stylesheet = explode(",",$dataSet["stylesheet_index"]);
			if($stylesheet && $stylesheet[0] != ""){
				foreach($stylesheet as $key => $value){
					$where = array();
					$where["contentclass"] = "stylesheet";
					$where["content_id"] = $content_id;
					$where["element_id"] = $value;
					$saveData = $where;
					$saveData["sort_no"] = $key;
					$saveData["active_flg"] = 1;

					$cnt = $ContentElement->getCountByParameters($where);
					if($cnt > 0){
						$result2 = $ContentElement->update($where, $saveData);
						foreach($stylesheetDelete as $deleteKey => $deleteValue){
							if($value == $deleteValue["element_id"]){
								unset($stylesheetDelete[$deleteKey]);
								break;
							}
						}
					}else{
						$result2 = $ContentElement->insert($saveData);
					}
					if(!$result2){
						Logger::error("スタイルシート情報の管理コンテンツ部品テーブルへの格納に失敗しました。",$where);
						return $result2;
					}
				}
			}
			//削除処理
			foreach($stylesheetDelete as $value){
				if(!$ContentElement->delete($value)){
					Logger::error("スタイルシート情報の管理コンテンツ部品テーブル削除に失敗しました。",$value);
					return false;
				}
			}
		}

		//スクリプト情報の管理コンテンツ部品テーブルへの格納
		if(isset($dataSet["script_index"])){
			//既存情報の取得
			$where = array("content_id" => $content_id,"contentclass" => "script");
			$scriptOld = $ContentElement->getListByParameters($where);
			$scriptDelete = $scriptOld;

			$script = explode(",",$dataSet["script_index"]);
			if($script && $script[0] != ""){
				foreach($script as $key => $value){
					$where = array();
					$where["contentclass"] = "script";
					$where["content_id"] = $content_id;
					$where["element_id"] = $value;
					$saveData = $where;
					$saveData["sort_no"] = $key;
					$saveData["active_flg"] = 1;

					$cnt = $ContentElement->getCountByParameters($where);
					if($cnt > 0){
						$result2 = $ContentElement->update($where, $saveData);
						foreach($scriptDelete as $deleteKey => $deleteValue){
							if($value == $deleteValue["element_id"]){
								unset($scriptDelete[$deleteKey]);
								break;
							}
						}
					}else{
						$result2 = $ContentElement->insert($saveData);
					}
					if(!$result2){
						Logger::error("スクリプト情報の管理コンテンツ部品テーブルへの格納に失敗しました。",$where);
						return $result2;
					}
				}
			}
			//削除処理
			foreach($scriptDelete as $value){
				if(!$ContentElement->delete($value)){
					Logger::error("スクリプト情報の管理コンテンツ部品テーブル削除に失敗しました。",$value);
					return false;
				}
			}
		}

		//追加情報の格納
		if(isset($addinfo)){
			//追加情報インデックス用辞書
			$addinfo_index_dic = array();

			//既存情報の取得
			$where = array("content_id" => $content_id);
			$addinfoOld = $ContentAddInfo->getListByParameters($where);
			$addinfoDelete = $addinfoOld;

			$count = 0;
			foreach($addinfo as $key => $saveData){

				$where = array();
				$where["content_id"] = $content_id;
				$where["name"] = $key;

				if(is_array($saveData)){
					if(isset($saveData["content"]) && !isset($saveData["addinfo_content"])){
						$saveData["addinfo_content"] = $saveData["content"];
						unset($saveData["content"]);
					}
					if(!isset($saveData["display_name"])){
						$saveData["display_name"] = $key;
					}
					if(!isset($saveData["inputtype"])){
						$saveData["inputtype"] = 1;
					}
					if(!isset($saveData["active_flg"])){
						$saveData["active_flg"] = 1;
					}
				}else{
					$content = $saveData;
					$saveData = array();
					$saveData["addinfo_content"] = $content;
					$saveData["display_name"] = $key;
					$saveData["inputtype"] = 1;
					$saveData["active_flg"] = 1;
				}
				$saveData["content_id"] = $content_id;
				$saveData["name"] = $key;
				$saveData["sort_no"] = $count;

				$cnt = $ContentAddInfo->getCountByParameters($where);
				if($cnt > 0){
					$result2 = $ContentAddInfo->update($where, $saveData);
					foreach($addinfoDelete as $deleteKey => $deleteValue){
						if($key == $deleteValue["name"]){
							unset($addinfoDelete[$deleteKey]);
							break;
						}
					}
					$content_addinfo_data = $ContentAddInfo->getDataByParameters($where);
					$addinfo_index_dic[$key] = $content_addinfo_data["content_addinfo_id"];
				}else{
					$result2 = $ContentAddInfo->insert($saveData);
					$addinfo_index_dic[$key] = $ContentAddInfo->last_insert_id();
				}
				if(!$result2){
					Logger::error("追加情報の格納に失敗しました。",$where);
					return $result2;
				}

				$count++;
			}
			//削除処理
			foreach($addinfoDelete as $value){
				if(!$ContentAddInfo->delete($value)){
					Logger::error("追加情報削除に失敗しました。",$value);
					return false;
				}
			}
			//インデックス作成処理
			$addinfo_index = "";
			foreach($addinfo_index_dic as $key => $value){
				if($addinfo_index != ""){ $addinfo_index .= ","; }
				$addinfo_index.= "[".$key."]=".$value;
			}

			if(!$Content->update(array("content_id" => $content_id), array("addinfo_index" => $addinfo_index))){
				Logger::error("追加情報インデックス作成に失敗しました。",$value);
				return false;
			}
		}

		return true;
	}

	function publishContent($content_id){

		$Content = new Content(Content::TABLE_MANAGEMENT);
		$ContentAddInfo = new ContentAddInfo(ContentAddInfo::TABLE_MANAGEMENT);
		$ContentElement = new ContentElement(ContentElement::TABLE_MANAGEMENT);
		$ContentPublic = new Content(Content::TABLE_PUBLIC);
		$ContentPublicAddInfo = new ContentAddInfo(ContentAddinfo::TABLE_PUBLIC);
		$ContentPublicElement = new ContentElement(ContentElement::TABLE_PUBLIC);

		$content = $Content->getDataByPrimaryKey($content_id);
		if(!$content){
			return false;
		}

		$pubCount = $ContentPublic->getCountByParameters(array("content_id" => $content_id));
		if($pubCount > 0){
			$result = $ContentPublic->update(array("content_id" => $content_id), $content);
		}else{
			$result = $ContentPublic->insert($content);
		}

		if(!$result){
			return false;
		}

		$ContentPublicAddInfo->delete(array("content_id" => $content_id));
		$addinfoList = $ContentAddInfo->getListByParameters(array("content_id" => $content_id));
		$ContentPublicAddInfo->insert($addinfoList);

		$ContentPublicElement->delete(array("content_id" => $content_id));
		$elementList = $ContentElement->getListByParameters(array("content_id" => $content_id));
		$ContentPublicElement->insert($elementList);

		if($result){
			return true;
		}else{
			return false;
		}
	}

	function archiveContent($content_id){
		$now_timestamp = time();
		$Content = new Content(Content::TABLE_MANAGEMENT);
		$ContentAddInfo = new ContentAddInfo(ContentAddInfo::TABLE_MANAGEMENT);
		$ContentElement = new ContentElement(ContentElement::TABLE_MANAGEMENT);
		$ContentArchive = new Content(Content::TABLE_ARCHIVE);
		$ContentArchiveAddInfo = new ContentAddInfo(ContentAddinfo::TABLE_ARCHIVE);
		$ContentArchiveElement = new ContentElement(ContentElement::TABLE_ARCHIVE);

		$content = $Content->getDataByPrimaryKey($content_id);
		$content["archive_time"] = $now_timestamp;
		$result = $ContentArchive->insert($content);
		if(!$result){
			return false;
		}
		$archive_id = $ContentArchive->last_insert_id();

		$addinfoList = $ContentAddInfo->getListByParameters(array("content_id" => $content_id));
		for($i=0;$i < count($addinfoList);$i++){
			$addinfoList[$i]["content_archive_id"] =  $archive_id;
		}
		$ContentArchiveAddInfo->insert($addinfoList);

		$elementList = $ContentElement->getListByParameters(array("content_id" => $content_id));
		for($i=0;$i < count($elementList);$i++){
			$elementList[$i]["content_archive_id"] =  $archive_id;
		}
		$ContentArchiveElement->insert($elementList);

		$this->cleanArchive($content_id);

		if($result){
			return true;
		}else{
			return false;
		}
	}

	/**
	 * 設定数以上まはた設定日数以上経過したアーカイブを削除する
	 * @param unknown $content_id
	 */
	function cleanArchive($content_id){
		$archive_num_limit = Config::get("archive_num_limit");
		$archive_day_limit = Config::get("archive_day_limit");
		if(!$archive_num_limit && !$archive_day_limit){
			return true;
		}
		$now_timestamp = time();
		$ContentArchive = new Content(Content::TABLE_ARCHIVE);

		//設定数以上のアーカイブを削除する
		if($archive_num_limit){
			$archiveList = $ContentArchive->getListByParameters(array("content_id" => $content_id),array("archive_time" => "desc"));
			foreach($archiveList as $key => $archiveData){
				if($key >= $archive_num_limit){
					$ContentArchive->deleteArchive($archiveData["content_id"], $archiveData["content_archive_id"]);
				}
			}
		}

		//設定日数以上経過したアーカイブを削除する
		if($archive_day_limit){
			$basedaytime = $now_timestamp - 60 * 60 * 24 * $archive_day_limit;
			$basedaytime = mktime(0,0,0,date("m",$basedaytime),date("d",$basedaytime),date("Y",$basedaytime));
			$where = array();
			$where[] = array("content_id",$content_id);
			$where[] = array("archive_time",$basedaytime,"<=");
			$archiveList = $ContentArchive->getListByParameters($where,array("archive_time" => "desc"));
			foreach($archiveList as $key => $archiveData){
				$ContentArchive->deleteArchive($archiveData["content_id"], $archiveData["content_archive_id"]);
			}
		}
		return true;
	}

	function restoreContent($content_id,$archive_id){
		$Content = new Content(Content::TABLE_MANAGEMENT);
		$ContentAddInfo = new ContentAddInfo(ContentAddInfo::TABLE_MANAGEMENT);
		$ContentElement = new ContentElement(ContentElement::TABLE_MANAGEMENT);
		$ContentArchive = new Content(Content::TABLE_ARCHIVE);
		$ContentArchiveAddInfo = new ContentAddInfo(ContentAddinfo::TABLE_ARCHIVE);
		$ContentArchiveElement = new ContentElement(ContentElement::TABLE_ARCHIVE);

		$archiveContent = $ContentArchive->getDataByParameters(array("content_archive_id" => $archive_id));
		if(!$archiveContent){
			Logger::error("アーカイブコンテンツの取得に失敗しました。ARCHIVE_ID:".$archive_id);
			return false;
		}
		$content = $Content->getDataByPrimaryKey($content_id);

		unset($archiveContent["content_archive_id"]);

		if(!$content){
			$archiveContent["sort_no"] = $Content->getMaxSort($archiveContent["contentclass"], $archiveContent["folder_id"]);
			if(!$Content->insert($archiveContent)){
				Logger::error("コンテンツの追加に失敗しました。CONTENT_ID:".$content_id,$archiveContent);
				return false;
			}
		}else{
			$archiveContent["content_id"] = $content_id;
			if(!$Content->update(array("content_id" => $content_id),$archiveContent)){
				Logger::error("コンテンツの更新に失敗しました。CONTENT_ID:".$content_id,$archiveContent);
				return false;
			}
		}

		//重複URLの調整
		$ajust_count = 1;
		$ajust_flg = false;
		$url = $archiveContent["url"];
		if($url){
			for(;;){
				if($ajust_count >= 1000){
					break;
				}
				$where = array();
				$where[] = array("url",$url);
				$where[] = array("content_id",$content_id,"<>");
				$cnt = $Content->getCountByParameters($where);
				if($cnt>0){
					$url = $archiveContent["url"]."(".$ajust_count.")";
					$ajust_flg = true;
				}else{
					if($ajust_flg){
						if(!$Content->update(array("content_id" => $content_id),array("url" => $url))){
							Logger::error("コンテンツの更新(URL調整)に失敗しました。CONTENT_ID:".$content_id,$archiveContent);
							return false;
						}
					}
					break;
				}
				$ajust_count++;
			}
		}

		$ContentAddInfo->delete(array("content_id" => $content_id));
		$addinfoList = $ContentArchiveAddInfo->getListByParameters(array("content_id" => $content_id,"content_archive_id" => $archive_id));
		for($i=0;$i < count($addinfoList);$i++){
			unset($addinfoList[$i]["content_archive_id"]);
			unset($addinfoList[$i]["content_addinfo_id"]);
		}
		$ContentAddInfo->insert($addinfoList);

		$ContentElement->delete(array("content_id" => $content_id));
		$elementList = $ContentArchiveElement->getListByParameters(array("content_id" => $content_id,"content_archive_id" => $archive_id));
		for($i=0;$i < count($elementList);$i++){
			unset($elementList[$i]["content_archive_id"]);
			unset($addinfoList[$i]["content_element_id"]);
		}
		$ContentElement->insert($elementList);

		return true;
	}

	function scheduleContent($content_id){
		$now_timestamp = time();

		$Content = new Content(Content::TABLE_MANAGEMENT);
		$ContentAddInfo = new ContentAddInfo(ContentAddInfo::TABLE_MANAGEMENT);
		$ContentElement = new ContentElement(ContentElement::TABLE_MANAGEMENT);
		$ContentSchedule = new Content(Content::TABLE_SCHEDULE);
		$ContentScheduleAddInfo = new ContentAddInfo(ContentAddinfo::TABLE_SCHEDULE);
		$ContentScheduleElement = new ContentElement(ContentElement::TABLE_SCHEDULE);

		//コンテンツ取得
		$content = $Content->getDataByPrimaryKey($content_id);
		$content["schedule_time"] = $now_timestamp;

		//同一時間のスケジュールコンテンツを検索
		$where = array();
		$where[] = array("content_id",$content["content_id"]);
		$where[] = array("schedule_publish",$content["schedule_publish"]);
		$scheduleData = $ContentSchedule->getDataByParameters($where);
		if($scheduleData){
			//存在する場合、更新
			$result = $ContentSchedule->update(array("content_id" => $content_id,"content_schedule_id" => $scheduleData["content_schedule_id"]), $content);
			$schedule_id = $scheduleData["content_schedule_id"];
		}else{
			//存在しない場合、追加
			$result = $ContentSchedule->insert($content);
			$schedule_id = $ContentSchedule->last_insert_id();
		}
		if(!$result){
			Logger::error("スケジュール設定にに失敗しました。CONTENT_ID:".$content_id,$content);
			return false;
		}

		$ContentScheduleAddInfo->delete(array("content_schedule_id" => $schedule_id));
		$addinfoList = $ContentAddInfo->getListByParameters(array("content_id" => $content_id));
		for($i=0;$i < count($addinfoList);$i++){
			$addinfoList[$i]["content_schedule_id"] =  $schedule_id;
		}
		$ContentScheduleAddInfo->insert($addinfoList);

		$ContentScheduleElement->delete(array("content_schedule_id" => $schedule_id));
		$elementList = $ContentElement->getListByParameters(array("content_id" => $content_id));
		for($i=0;$i < count($elementList);$i++){
			$elementList[$i]["content_schedule_id"] =  $schedule_id;
		}
		$ContentScheduleElement->insert($elementList);

		if($result){
			return true;
		}else{
			return false;
		}
	}

	function deleteContent($content_id){

		$Content = new Content(Content::TABLE_MANAGEMENT);
		$ContentAddInfo = new ContentAddInfo(ContentAddInfo::TABLE_MANAGEMENT);
		$ContentElement = new ContentElement(ContentElement::TABLE_MANAGEMENT);
		$ContentSchedule = new Content(Content::TABLE_SCHEDULE);
		$ContentScheduleAddInfo = new ContentAddInfo(ContentAddinfo::TABLE_SCHEDULE);
		$ContentScheduleElement = new ContentElement(ContentElement::TABLE_SCHEDULE);
		$ContentPublic = new Content(Content::TABLE_PUBLIC);
		$ContentPublicAddInfo = new ContentAddInfo(ContentAddinfo::TABLE_PUBLIC);
		$ContentPublicElement = new ContentElement(ContentElement::TABLE_PUBLIC);

		$content = $Content->getDataByPrimaryKey($content_id);
		if(!$content){
			return false;
		}

		if(Config::get("show_deleted_archive") == "on"){
			Logger::info("削除時に自動アーカイブしました。content_id=".$content_id);
			$Content->archiveContent($content_id);
		}

		$ContentPublic->delete(array("content_id" => $content_id));
		$ContentPublicAddInfo->delete(array("content_id" => $content_id));
		$ContentPublicElement->delete(array("content_id" => $content_id));
		$ContentSchedule->delete(array("content_id" => $content_id));
		$ContentScheduleAddInfo->delete(array("content_id" => $content_id));
		$ContentScheduleElement->delete(array("content_id" => $content_id));
		$Content->delete(array("content_id" => $content_id));
		$ContentAddInfo->delete(array("content_id" => $content_id));
		$ContentElement->delete(array("content_id" => $content_id));

		return true;
	}

	function deleteArchive($content_id,$content_archive_id){
		if(!$content_id || !$content_archive_id){
			return false;
		}
		$ContentArchive = new Content(Content::TABLE_ARCHIVE);
		$ContentArchiveAddInfo = new ContentAddInfo(ContentAddinfo::TABLE_ARCHIVE);
		$ContentArchiveElement = new ContentElement(ContentElement::TABLE_ARCHIVE);

		$ContentArchive->delete(array("content_id" => $content_id,"content_archive_id" => $content_archive_id));
		$ContentArchiveAddInfo->delete(array("content_id" => $content_id,"content_archive_id" => $content_archive_id));
		$ContentArchiveElement->delete(array("content_id" => $content_id,"content_archive_id" => $content_archive_id));
		return true;
	}

	function deleteArchiveAll($content_id){
		if(!$content_id){
			return false;
		}
		$ContentArchive = new Content(Content::TABLE_ARCHIVE);
		$ContentArchiveAddInfo = new ContentAddInfo(ContentAddinfo::TABLE_ARCHIVE);
		$ContentArchiveElement = new ContentElement(ContentElement::TABLE_ARCHIVE);

		$ContentArchive->delete(array("content_id" => $content_id));
		$ContentArchiveAddInfo->delete(array("content_id" => $content_id));
		$ContentArchiveElement->delete(array("content_id" => $content_id));
		return true;
	}

	function unpublishContent($content_id){

		$Content = new Content(Content::TABLE_MANAGEMENT);
		$ContentPublic = new Content(Content::TABLE_PUBLIC);
		$ContentPublicAddInfo = new ContentAddInfo(ContentAddinfo::TABLE_PUBLIC);
		$ContentPublicElement = new ContentElement(ContentElement::TABLE_PUBLIC);

		$content = $Content->getDataByPrimaryKey($content_id);
		if(!$content){
			return false;
		}

		$ContentPublic->delete(array("content_id" => $content_id));
		$ContentPublicAddInfo->delete(array("content_id" => $content_id));
		$ContentPublicElement->delete(array("content_id" => $content_id));

		return true;
	}


	function sortDown($content_id,$user_id){
		$now_timestamp = time();
		$Content = new Content(Content::TABLE_MANAGEMENT);
		$content = $Content->getDataByPrimaryKey($content_id);
		if(!$content){
			return false;
		}

		$sql = "SELECT min(sort_no) as min FROM content ";
		$sql.= "WHERE contentclass = ? ";
		$params[] = $content["contentclass"];
		$sql.= "AND folder_id = ? ";
		$params[] = $content["folder_id"];
		$sql.= "AND sort_no > ? ";
		$params[] = $content["sort_no"];

		$result = $this->query($sql,$params,DB::FETCH);
		$target_sort = $result["min"];
		if(!$target_sort || $target_sort == 0){
			return true;
		}

		$where = array();
		$where["contentclass"] = $content["contentclass"];
		$where["folder_id"] = $content["folder_id"];
		$where["sort_no"] = $target_sort;
		$target_content = $Content->getDataByParameters($where);
		if(!$target_content){
			return false;
		}

		$Content->update(array("content_id" => $content_id), array("sort_no" => $target_sort,"updated" => $now_timestamp,"updated_by" => $user_id));
		$Content->update(array("content_id" => $target_content["content_id"]), array("sort_no" => $content["sort_no"],"updated" => $now_timestamp,"updated_by" => $user_id));
		return true;
	}

	function sortUp($content_id,$user_id){
		$now_timestamp = time();
		$Content = new Content(Content::TABLE_MANAGEMENT);
		$content = $Content->getDataByPrimaryKey($content_id);
		if(!$content){
			return false;
		}

		$sql = "SELECT max(sort_no) as max FROM content ";
		$sql.= "WHERE contentclass = ? ";
		$params[] = $content["contentclass"];
		$sql.= "AND folder_id = ? ";
		$params[] = $content["folder_id"];
		$sql.= "AND sort_no < ? ";
		$params[] = $content["sort_no"];

		$result = $this->query($sql,$params,DB::FETCH);
		$target_sort = $result["max"];
		if(!$target_sort || $target_sort == 0){
			return true;
		}

		$where = array();
		$where["contentclass"] = $content["contentclass"];
		$where["folder_id"] = $content["folder_id"];
		$where["sort_no"] = $target_sort;
		$target_content = $Content->getDataByParameters($where);
		if(!$target_content){
			return false;
		}

		$Content->update(array("content_id" => $content_id), array("sort_no" => $target_sort,"updated" => $now_timestamp,"updated_by" => $user_id));
		$Content->update(array("content_id" => $target_content["content_id"]), array("sort_no" => $content["sort_no"],"updated" => $now_timestamp,"updated_by" => $user_id));
		return true;
	}

	function getMaxSort($contentclass,$folder_id){
		$sql = "SELECT max(sort_no) as max FROM content ";
		$sql.= "WHERE contentclass = ? ";
		$params[] = $contentclass;
		$sql.= "AND folder_id = ? ";
		$params[] = $folder_id;

		$result = $this->query($sql,$params,DB::FETCH);
		$max = $result["max"];

		if(!$max){
			$sort = 1;
		}else{
			$sort = $max + 1;
		}

		return $sort;
	}

	function getStylesheetIndex($content_id,$check_arr = array()){
		if(in_array($content_id,$check_arr)){
			return "";
		}else{
			$check_arr[] = $content_id;
		}
		$content = $this->getDataByPrimaryKey($content_id);

		if(!$content){
			return "";
		}

		$stylesheet_index = $content["stylesheet_index"];
		if($stylesheet_index == null){
			$stylesheet_index = "";
		}

		$template_stylesheet_index = "";
		if($content["template_id"] != null && $content["template_id"] != "" && $content["template_id"] != 0){
			$template_stylesheet_index = $this->getStylesheetIndex($content["template_id"],$check_arr);
		}else{
			if(count($check_arr) <= 1){
				$Folder = new Folder();
				$folder = $Folder->getDataByPrimaryKey($content["folder_id"]);
				if($folder["template_id"] != null && $folder["template_id"] != ""){
					$template_stylesheet_index = $this->getStylesheetIndex($folder["template_id"],$check_arr);
				}
			}else{
				$template_stylesheet_index = "";
			}
		}

		if($stylesheet_index != ""){
			if($template_stylesheet_index != ""){
				$stylesheet_index = $template_stylesheet_index.",".$stylesheet_index;
			}
		}else{
			$stylesheet_index = $template_stylesheet_index;
		}
		return $stylesheet_index;
	}

	function getScheduleActionContentList(){
		$now_timestamp = time();

		$sql = "SELECT sche.content_schedule_id,sche.content_id,sche.schedule_type FROM content_schedule sche ";
		$sql.= "INNER JOIN content cont ON sche.content_id = cont.content_id ";
		$sql.= "WHERE sche.schedule_publish <= ? ";
		$params[] = $now_timestamp;
		$sql.= "ORDER BY sche.schedule_publish asc ";

		$result = $this->query($sql,$params);
		return $result;
	}

	function executeReplaceSchedule($content_id,$content_schedule_id){
		$now_timestamp = time();
		$ContentPublic = new Content(Content::TABLE_PUBLIC);
		$ContentPublicAddInfo = new ContentAddInfo(ContentAddInfo::TABLE_PUBLIC);
		$ContentPublicElement = new ContentElement(ContentElement::TABLE_PUBLIC);
		$ContentSchedule = new Content(Content::TABLE_SCHEDULE);
		$ContentScheduleAddInfo = new ContentAddInfo(ContentAddinfo::TABLE_SCHEDULE);
		$ContentScheduleElement = new ContentElement(ContentElement::TABLE_SCHEDULE);

		//スケジュールコンテンツの取得
		$content = $ContentSchedule->getDataByParameters(array("content_id" => $content_id,"content_schedule_id" => $content_schedule_id));
		if(!$content){
			return false;
		}
		unset($content["content_schedule_id"]);

		//公開コンテンツの更新
		$pubCount = $ContentPublic->getCountByParameters(array("content_id" => $content_id));
		if($pubCount > 0){
			$result = $ContentPublic->update(array("content_id" => $content_id), $content);
		}else{
			$result = $ContentPublic->insert($content);
		}

		if(!$result){
			return false;
		}

		//公開コンテンツ追加情報・要素の更新
		$ContentPublicAddInfo->delete(array("content_id" => $content_id));
		$addinfoList = $ContentScheduleAddInfo->getListByParameters(array("content_id" => $content_id,"content_schedule_id" => $content_schedule_id));
		for($i=0;$i<count($addinfoList);$i++){
			unset($addinfoList[$i]["content_schedule_id"]);
		}
		$ContentPublicAddInfo->insert($addinfoList);

		$ContentPublicElement->delete(array("content_id" => $content_id));
		$elementList = $ContentScheduleElement->getListByParameters(array("content_id" => $content_id,"content_schedule_id" => $content_schedule_id));
		for($i=0;$i<count($elementList);$i++){
			unset($elementList[$i]["content_schedule_id"]);
		}
		$ContentPublicElement->insert($elementList);

		//スケジュールコンテンツの削除
		$ContentSchedule->delete(array("content_id" => $content_id,"content_schedule_id" => $content_schedule_id));
		$ContentScheduleAddInfo->delete(array("content_id" => $content_id,"content_schedule_id" => $content_schedule_id));
		$ContentScheduleElement->delete(array("content_id" => $content_id,"content_schedule_id" => $content_schedule_id));

		if($result){
			return true;
		}else{
			return false;
		}
	}

	function deleteSchedule($content_schedule_id){
		if(!$content_schedule_id){
			return false;
		}
		$ContentSchedule = new Content(Content::TABLE_SCHEDULE);
		$ContentScheduleAddInfo = new ContentAddInfo(ContentAddinfo::TABLE_SCHEDULE);
		$ContentScheduleElement = new ContentElement(ContentElement::TABLE_SCHEDULE);

		$ContentSchedule->delete(array("content_schedule_id" => $content_schedule_id));
		$ContentScheduleAddInfo->delete(array("content_schedule_id" => $content_schedule_id));
		$ContentScheduleElement->delete(array("content_schedule_id" => $content_schedule_id));
		return true;
	}
}
