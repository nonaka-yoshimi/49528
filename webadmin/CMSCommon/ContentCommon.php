<?php
require_once(dirname(__FILE__).'/../ApplicationCommon/Util.php'); 	//ユーティリティ
require_once(dirname(__FILE__).'/../DataAccess/Content.php'); 		//コンテンツクラス
require_once(dirname(__FILE__).'/../DataAccess/Folder.php'); 		//フォルダクラス
require_once(dirname(__FILE__).'/../CMSCommon/UserAuth.php'); 		//ユーザ権限関連処理
/*
説明：コンテンツ関連共通機能クラス
作成日：2013/12/2 TS谷
*/

/**
 * コンテンツ関連共通機能クラス
 */
class ContentCommon{

	static function getContentListByParameters($parameters){
		$Content = new Content();

		//コンテンツ（非ユニーク）・権限一覧を取得する。
		$contentAuthList = $Content->getContentAndAuthListByParameters($parameters);

		$columns[] = "con_auth_page_view";
		$columns[] = "con_auth_page_edit";
		$columns[] = "con_auth_page_delete";
		$columns[] = "con_auth_page_workflow";
		$columns[] = "con_auth_page_publish";
		$columns[] = "con_auth_page_archive";
		$columns[] = "con_auth_element_view";
		$columns[] = "con_auth_element_edit";
		$columns[] = "con_auth_element_delete";
		$columns[] = "con_auth_element_workflow";
		$columns[] = "con_auth_element_publish";
		$columns[] = "con_auth_element_archive";
		$columns[] = "con_auth_image_view";
		$columns[] = "con_auth_image_edit";
		$columns[] = "con_auth_image_delete";
		$columns[] = "con_auth_image_workflow";
		$columns[] = "con_auth_image_publish";
		$columns[] = "con_auth_image_archive";
		$columns[] = "con_auth_file_view";
		$columns[] = "con_auth_file_edit";
		$columns[] = "con_auth_file_delete";
		$columns[] = "con_auth_file_workflow";
		$columns[] = "con_auth_file_publish";
		$columns[] = "con_auth_file_archive";
		$columns[] = "con_auth_template_view";
		$columns[] = "con_auth_template_edit";
		$columns[] = "con_auth_template_delete";
		$columns[] = "con_auth_template_workflow";
		$columns[] = "con_auth_template_publish";
		$columns[] = "con_auth_template_archive";
		$columns[] = "con_auth_stylesheet_view";
		$columns[] = "con_auth_stylesheet_edit";
		$columns[] = "con_auth_stylesheet_delete";
		$columns[] = "con_auth_stylesheet_workflow";
		$columns[] = "con_auth_stylesheet_publish";
		$columns[] = "con_auth_stylesheet_archive";
		$columns[] = "con_auth_script_view";
		$columns[] = "con_auth_script_edit";
		$columns[] = "con_auth_script_delete";
		$columns[] = "con_auth_script_workflow";
		$columns[] = "con_auth_script_publish";
		$columns[] = "con_auth_script_archive";

		//コンテンツ（非ユニーク）・権限カラム統合一覧(コンテンツ紐付）を取得する。
		$result_con = UserAuth::mergeMaxNumColumnWithNumIndex($contentAuthList, $columns, 0,1,2,3,4);

		//コンテンツ（ユニーク）・統合後権限一覧(コンテンツ紐付）を取得する。
		$result_con = UserAuth::mergeRecordWithMaxNumColumn($result_con, "content_id", $columns);

		//コンテンツ（非ユニーク）・権限カラム統合一覧(フォルダ紐付）を取得する。
		$result_fol = UserAuth::mergeMaxNumColumnWithNumIndex($contentAuthList, $columns, 0,3,4,1,2);

		//コンテンツ（ユニーク）・統合後権限一覧(コンテンツ紐付）を取得する。
		$result_fol = UserAuth::mergeRecordWithMaxNumColumn($result_fol, "content_id", $columns);

		//権限結合
		$result = array_merge($result_con,$result_fol);

		//権限をコンテンツ紐付・フォルダ紐付で最小値化する。
		$result = UserAuth::mergeRecordWithMaxNumColumn($result, "content_id", $columns);

		return $result;
	}

	/*
	static function getContentDataForEditByParameters($parameters){
		$Content = new Content();

		//コンテンツ（非ユニーク）・権限一覧を取得する。
		$contentAuthList = $Content->getContentAndAuthListForEditByParameters($parameters);

		$columns[] = "con_auth_page_view";
		$columns[] = "con_auth_page_edit";
		$columns[] = "con_auth_page_delete";
		$columns[] = "con_auth_page_workflow";
		$columns[] = "con_auth_page_publish";
		$columns[] = "con_auth_page_archive";
		$columns[] = "con_auth_element_view";
		$columns[] = "con_auth_element_edit";
		$columns[] = "con_auth_element_delete";
		$columns[] = "con_auth_element_workflow";
		$columns[] = "con_auth_element_publish";
		$columns[] = "con_auth_element_archive";
		$columns[] = "con_auth_image_view";
		$columns[] = "con_auth_image_edit";
		$columns[] = "con_auth_image_delete";
		$columns[] = "con_auth_image_workflow";
		$columns[] = "con_auth_image_publish";
		$columns[] = "con_auth_image_archive";
		$columns[] = "con_auth_file_view";
		$columns[] = "con_auth_file_edit";
		$columns[] = "con_auth_file_delete";
		$columns[] = "con_auth_file_workflow";
		$columns[] = "con_auth_file_publish";
		$columns[] = "con_auth_file_archive";
		$columns[] = "con_auth_template_view";
		$columns[] = "con_auth_template_edit";
		$columns[] = "con_auth_template_delete";
		$columns[] = "con_auth_template_workflow";
		$columns[] = "con_auth_template_publish";
		$columns[] = "con_auth_template_archive";
		$columns[] = "con_auth_stylesheet_view";
		$columns[] = "con_auth_stylesheet_edit";
		$columns[] = "con_auth_stylesheet_delete";
		$columns[] = "con_auth_stylesheet_workflow";
		$columns[] = "con_auth_stylesheet_publish";
		$columns[] = "con_auth_stylesheet_archive";
		$columns[] = "con_auth_script_view";
		$columns[] = "con_auth_script_edit";
		$columns[] = "con_auth_script_delete";
		$columns[] = "con_auth_script_workflow";
		$columns[] = "con_auth_script_publish";
		$columns[] = "con_auth_script_archive";

		//コンテンツ（非ユニーク）・権限カラム統合一覧(コンテンツ紐付）を取得する。
		$result_con = UserAuth::mergeMaxNumColumnWithNumIndex($contentAuthList, $columns, 0,1,2,3,4);

		//コンテンツ（ユニーク）・統合後権限一覧(コンテンツ紐付）を取得する。
		$result_con = UserAuth::mergeRecordWithMaxNumColumn($result_con, "content_id", $columns);

		//コンテンツ（非ユニーク）・権限カラム統合一覧(フォルダ紐付）を取得する。
		$result_fol = UserAuth::mergeMaxNumColumnWithNumIndex($contentAuthList, $columns, 0,3,4,1,2);

		//コンテンツ（ユニーク）・統合後権限一覧(コンテンツ紐付）を取得する。
		$result_fol = UserAuth::mergeRecordWithMaxNumColumn($result_fol, "content_id", $columns);

		//権限結合
		$result = array_merge($result_con,$result_fol);

		//権限をコンテンツ紐付・フォルダ紐付で最小値化する。
		$result = UserAuth::mergeRecordWithMaxNumColumn($result, "content_id", $columns);

		return $result[0];
	}
	*/

	/**
	 * コンテンツIDに基づき、コンテンツ権限一覧を返却する
	 * @param string $content_id コンテンツID
	 */
	static function getContentAuth($content_id){
		$Content = new Content();						//コンテンツクラス
		$Folder = new Folder();							//フォルダクラス
		$session = Session::get();						//セッションクラス

		$user_id = $session->user["user_id"];			//セッション：ユーザID取得
		$usergroups = $session->user["usergroups"];		//セッション：ユーザグループ一覧取得

		//権限カラム一覧を取得する
		$columns = self::getConAuthColumns();

		//対象コンテンツの権限一覧を取得する。
		$contentAuthList = $Content->getContentAuthList($user_id, $usergroups, $content_id,$columns);

		//権限一覧が取得できない場合処理終了
		if(!$contentAuthList){ return false; }

		//コンテンツ権限一覧が取得できない場合処理終了
		if(!$contentAuthList){ return false; }

		//Debug::arrayCheck($contentAuthList);

		//コンテンツ権限カラム統合一覧を取得する。

		$content_result = UserAuth::mergeMaxNumColumnWithNumIndex($contentAuthList, $columns, 0, 1 ,2);

		//コンテンツ権限レコード統合一覧を取得する。
		$content_result = UserAuth::mergeRecordWithMaxNumColumn($content_result, "content_id", $columns);

		//対象フォルダの権限一覧を取得する。
		$folderAuthList = $Folder->getFolderAuthList($user_id, $usergroups, $contentAuthList[0]["folder_id"],$columns);

		//権限一覧が取得できない場合処理終了
		if(!$folderAuthList){ return false; }

		//フォルダ権限カラム統合一覧を取得する。
		$folder_result = UserAuth::mergeMaxNumColumnWithNumIndex($folderAuthList, $columns, 0, 1 ,2);

		//フォルダ権限レコード統合一覧を取得する。
		$folder_result = UserAuth::mergeRecordWithMaxNumColumn($folder_result, "folder_id", $columns);

		//コンテンツ・フォルダ権限レコード統合一覧を取得する。
		$result = array_merge($content_result,$folder_result);
		$result = UserAuth::mergeRecordWithMinNumColumn($result, "folder_id", $columns);

		return $result[0];
	}

	/**
	 * コンテンツ操作権限カラム一覧を取得する
	 * @return array コンテンツ操作権限カラム一覧
	 */
	static function getConAuthColumns(){
		$columns[] = "con_auth_page_view";
		$columns[] = "con_auth_page_add";
		$columns[] = "con_auth_page_edit";
		$columns[] = "con_auth_page_delete";
		$columns[] = "con_auth_page_workflow";
		$columns[] = "con_auth_page_publish";
		$columns[] = "con_auth_page_archive";
		$columns[] = "con_auth_page_schedule";
		$columns[] = "con_auth_page_title";
		$columns[] = "con_auth_page_url";
		$columns[] = "con_auth_page_content";
		$columns[] = "con_auth_page_comment";
		$columns[] = "con_auth_page_keywords";
		$columns[] = "con_auth_page_description";
		$columns[] = "con_auth_page_author";
		$columns[] = "con_auth_page_addinfo";
		$columns[] = "con_auth_page_addinfocode";
		$columns[] = "con_auth_page_addcolumn";
		$columns[] = "con_auth_page_element";
		$columns[] = "con_auth_page_template";
		$columns[] = "con_auth_page_stylesheet";
		$columns[] = "con_auth_page_script";
		$columns[] = "con_auth_page_editor";
		$columns[] = "con_auth_page_doctype";
		$columns[] = "con_auth_page_head_attr";
		$columns[] = "con_auth_page_head_code";
		$columns[] = "con_auth_page_body_attr";
		$columns[] = "con_auth_page_folder";
		$columns[] = "con_auth_page_auth";
		$columns[] = "con_auth_page_history";
		$columns[] = "con_auth_page_static_mode";
		$columns[] = "con_auth_page_php_mode";
		$columns[] = "con_auth_element_view";
		$columns[] = "con_auth_element_add";
		$columns[] = "con_auth_element_edit";
		$columns[] = "con_auth_element_delete";
		$columns[] = "con_auth_element_workflow";
		$columns[] = "con_auth_element_publish";
		$columns[] = "con_auth_element_archive";
		$columns[] = "con_auth_element_schedule";
		$columns[] = "con_auth_element_title";
		$columns[] = "con_auth_element_content";
		$columns[] = "con_auth_element_comment";
		$columns[] = "con_auth_element_addinfo";
		$columns[] = "con_auth_element_addinfocode";
		$columns[] = "con_auth_element_addcolumn";
		$columns[] = "con_auth_element_element";
		$columns[] = "con_auth_element_template";
		$columns[] = "con_auth_element_stylesheet";
		$columns[] = "con_auth_element_script";
		$columns[] = "con_auth_element_editor";
		$columns[] = "con_auth_element_folder";
		$columns[] = "con_auth_element_elementtype";
		$columns[] = "con_auth_element_auth";
		$columns[] = "con_auth_element_history";
		$columns[] = "con_auth_image_view";
		$columns[] = "con_auth_image_add";
		$columns[] = "con_auth_image_edit";
		$columns[] = "con_auth_image_delete";
		$columns[] = "con_auth_image_workflow";
		$columns[] = "con_auth_image_publish";
		$columns[] = "con_auth_image_archive";
		$columns[] = "con_auth_image_schedule";
		$columns[] = "con_auth_image_title";
		$columns[] = "con_auth_image_url";
		$columns[] = "con_auth_image_content";
		$columns[] = "con_auth_image_addinfo";
		$columns[] = "con_auth_image_addinfocode";
		$columns[] = "con_auth_image_addcolumn";
		$columns[] = "con_auth_image_folder";
		$columns[] = "con_auth_image_auth";
		$columns[] = "con_auth_image_history";
		$columns[] = "con_auth_file_view";
		$columns[] = "con_auth_file_add";
		$columns[] = "con_auth_file_edit";
		$columns[] = "con_auth_file_delete";
		$columns[] = "con_auth_file_workflow";
		$columns[] = "con_auth_file_publish";
		$columns[] = "con_auth_file_archive";
		$columns[] = "con_auth_file_schedule";
		$columns[] = "con_auth_file_title";
		$columns[] = "con_auth_file_url";
		$columns[] = "con_auth_file_content";
		$columns[] = "con_auth_file_addinfo";
		$columns[] = "con_auth_file_addinfocode";
		$columns[] = "con_auth_file_addcolumn";
		$columns[] = "con_auth_file_folder";
		$columns[] = "con_auth_file_auth";
		$columns[] = "con_auth_file_history";
		$columns[] = "con_auth_template_view";
		$columns[] = "con_auth_template_add";
		$columns[] = "con_auth_template_edit";
		$columns[] = "con_auth_template_delete";
		$columns[] = "con_auth_template_workflow";
		$columns[] = "con_auth_template_publish";
		$columns[] = "con_auth_template_archive";
		$columns[] = "con_auth_template_schedule";
		$columns[] = "con_auth_template_title";
		$columns[] = "con_auth_template_content";
		$columns[] = "con_auth_template_keywords";
		$columns[] = "con_auth_template_description";
		$columns[] = "con_auth_template_author";
		$columns[] = "con_auth_template_addinfo";
		$columns[] = "con_auth_template_addinfocode";
		$columns[] = "con_auth_template_addcolumn";
		$columns[] = "con_auth_template_element";
		$columns[] = "con_auth_template_template";
		$columns[] = "con_auth_template_stylesheet";
		$columns[] = "con_auth_template_script";
		$columns[] = "con_auth_template_doctype";
		$columns[] = "con_auth_template_head_attr";
		$columns[] = "con_auth_template_head_code";
		$columns[] = "con_auth_template_body_attr";
		$columns[] = "con_auth_template_editor";
		$columns[] = "con_auth_template_folder";
		$columns[] = "con_auth_template_auth";
		$columns[] = "con_auth_template_history";
		$columns[] = "con_auth_stylesheet_view";
		$columns[] = "con_auth_stylesheet_add";
		$columns[] = "con_auth_stylesheet_edit";
		$columns[] = "con_auth_stylesheet_delete";
		$columns[] = "con_auth_stylesheet_workflow";
		$columns[] = "con_auth_stylesheet_publish";
		$columns[] = "con_auth_stylesheet_archive";
		$columns[] = "con_auth_stylesheet_schedule";
		$columns[] = "con_auth_stylesheet_title";
		$columns[] = "con_auth_stylesheet_url";
		$columns[] = "con_auth_stylesheet_content";
		$columns[] = "con_auth_stylesheet_folder";
		$columns[] = "con_auth_stylesheet_auth";
		$columns[] = "con_auth_stylesheet_history";
		$columns[] = "con_auth_script_view";
		$columns[] = "con_auth_script_add";
		$columns[] = "con_auth_script_edit";
		$columns[] = "con_auth_script_delete";
		$columns[] = "con_auth_script_workflow";
		$columns[] = "con_auth_script_publish";
		$columns[] = "con_auth_script_archive";
		$columns[] = "con_auth_script_schedule";
		$columns[] = "con_auth_script_title";
		$columns[] = "con_auth_script_url";
		$columns[] = "con_auth_script_content";
		$columns[] = "con_auth_script_folder";
		$columns[] = "con_auth_script_auth";
		$columns[] = "con_auth_script_history";
		$columns[] = "con_auth_file_sort";
		$columns[] = "con_auth_dir_view";
		$columns[] = "con_auth_dir_add";
		$columns[] = "con_auth_dir_edit";
		$columns[] = "con_auth_dir_delete";
		$columns[] = "con_auth_dir_sort";
		return $columns;
	}

	static function getContentStatusHTML($contentData){
		$str = "";
		if($contentData["content_public_id"] != NULL){
			if($contentData["content_public_updated"] == $contentData["updated"]){
				if($contentData["schedule_publish"] != null && $contentData["schedule_publish"] > time()){
					$str.= '<span class="mark info">公開予定</span>';
				}else if($contentData["schedule_unpublish"] != null && $contentData["schedule_unpublish"] < time()){
					$str.= '<span class="mark red">公開終了</span>';
				}else{
					$str.= '<span class="mark green">公開済</span>';
				}
			}else{
				$str.= '<span class="mark orange">編集中</span>';
			}
		}else{
			$str.= '<span class="mark red">非公開</span>';
		}
		if($contentData["sche_cnt"] > 0){
			$str.= '<br /><span class="mark info">スケジュール済</span>';
		}
		if($contentData["workflowstate_name"]){
			$str.= '<br /><span class="mark info">'.htmlspecialchars($contentData["workflowstate_name"]).'</span>';
		}
		return $str;
	}
}