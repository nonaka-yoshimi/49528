<?php
require_once(dirname(__FILE__).'/../ApplicationCommon/Util.php'); 		//ユーティリティ
require_once(dirname(__FILE__).'/../ApplicationCommon/Session.php'); 	//セッションクラス
require_once(dirname(__FILE__).'/../CMSCommon/UserAuth.php'); 			//ユーザ認証共通処理クラス
require_once(dirname(__FILE__).'/../CMSCommon/ContentCommon.php'); 		//コンテンツ共通クラス
require_once(dirname(__FILE__).'/../DataAccess/Folder.php'); 			//フォルダクラス
/*
説明：フォルダ関連共通機能クラス
作成日：2013/12/2 TS谷
*/

/**
 * フォルダ関連共通機能クラス
 */
class FolderCommon{

	static function getFolderListByUserIdAndUserGroup($user_id,$usergroupList){
		$Folder = new Folder();

		//フォルダ（非ユニーク）・権限一覧を取得する。
		$folderAndAuthList = $Folder->getFolderAndAuthListByUserId($user_id, $usergroupList);

		//フォルダ（非ユニーク）・権限カラム統合一覧を取得する。
		$columns = array("con_auth_dir_view","con_auth_dir_add","con_auth_dir_edit","con_auth_dir_delete","con_auth_dir_sort");
		$result = UserAuth::mergeMaxNumColumnWithNumIndex($folderAndAuthList, $columns, 0, 1 ,2);

		//フォルダ（ユニーク）・統合後権限一覧を取得する。
		$result = UserAuth::mergeRecordWithMaxNumColumn($result, "folder_id", $columns);

		//表示権限があるデータのみを抽出する
		$result = Util::array_search_multi(array("con_auth_dir_view" => "1"), $result);

		return $result;
	}

	static function getFolderDataForFileListByParameters($parameters){
		$Folder = new Folder();

		//フォルダ（非ユニーク）・権限一覧を取得する。
		$folderAndAuthList = $Folder->getFolderAndAuthListForFileListByUserId($parameters["user_id"], $parameters["usergroups"],$parameters["folder_id"],$parameters["keyword"]);

		//フォルダ（非ユニーク）・権限カラム統合一覧を取得する。
		$columns = array("con_auth_dir_view","con_auth_dir_add","con_auth_dir_edit","con_auth_dir_delete","con_auth_dir_sort");
		$result = UserAuth::mergeMaxNumColumnWithNumIndex($folderAndAuthList, $columns, 0, 1 ,2);

		//フォルダ（ユニーク）・統合後権限一覧を取得する。
		$result = UserAuth::mergeRecordWithMaxNumColumn($result, "folder_id", $columns);

		return $result;
	}

	/**
	 * フォルダIDに基づき、フォルダ権限一覧を返却する
	 * @param string $folder_id フォルダID
	 */
	static function getFolderAuth($folder_id){
		$Folder = new Folder();							//フォルダクラス
		$session = Session::get();						//セッションクラス

		$user_id = $session->user["user_id"];			//セッション：ユーザID取得
		$usergroups = $session->user["usergroups"];		//セッション：ユーザグループ一覧取得

		//権限カラム一覧を取得する
		$columns = ContentCommon::getConAuthColumns();

		//対象フォルダの権限一覧を取得する。
		$folderAuthList = $Folder->getFolderAuthList($user_id, $usergroups, $folder_id,$columns);

		//権限一覧が取得できない場合処理終了
		if(!$folderAuthList){ return false; }

		//フォルダ権限カラム統合一覧を取得する。
		$result = UserAuth::mergeMaxNumColumnWithNumIndex($folderAuthList, $columns, 0, 1 ,2);

		//フォルダ権限レコード統合一覧を取得する。
		$result = UserAuth::mergeRecordWithMaxNumColumn($result, "folder_id", $columns);

		return $result[0];
	}
}