<?php
require_once(dirname(__FILE__).'/../ApplicationCommon/Util.php'); 		//ユーティリティ
require_once(dirname(__FILE__).'/../ApplicationCommon/Session.php'); 	//セッション
require_once(dirname(__FILE__).'/../ApplicationCommon/SPConst.php'); 	//定数
require_once(dirname(__FILE__).'/../ApplicationCommon/Mail.php'); 		//メール
require_once(dirname(__FILE__).'/../DataAccess/Content.php'); 			//コンテンツ
require_once(dirname(__FILE__).'/../DataAccess/WorkFlow.php'); 			//ワークフローアクション
require_once(dirname(__FILE__).'/../DataAccess/WorkFlowState.php'); 	//ワークフロー状態
require_once(dirname(__FILE__).'/../DataAccess/User.php'); 				//ユーザ
/*
説明：ワークフロー関連共通機能クラス
作成日：2014/8/31 TS谷
*/

/**
 * ワークフロー関連共通機能クラス
 */
class WorkFlowCommon{
	static function checkContentEditAvailable($content_id,$usergroups,$admintype,$workflowstate_id = ""){

		//コンテンツ取得
		if($content_id){
			$Content = new Content(Content::TABLE_MANAGEMENT); //コンテンツクラス
			$contentData = $Content->getContentDataByContentId($content_id);
			if(!$contentData){
				return true;
			}
		}else{
			return true;
		}

		//対象ワークフローアクションID
		if(!$workflowstate_id){
			$workflowstate_id = $contentData["workflowstate_id"];
		}
		if(!$workflowstate_id){
			return true;
		}
		//利用可能なワークフローアクション存在チェック
		$WorkFlow = new WorkFlow();
		$where = array();
		$where["workflowstate_from_id"] = $workflowstate_id;
		$where["active_flg"] = 1;
		$order = array();
		$order["sort_no"] = "asc";
		$workFlowListSrc = $WorkFlow->getListByParameters($where,$order);

		//実行可能なアクションに絞込み
		$workFlowList = array();
		foreach($workFlowListSrc as $key => $value){
			$wf_contentclass = explode(",",$value["contentclass"]);
			$wf_folder_id = explode(",",$value["folder_id"]);
			$wf_usergroup_id = explode(",",$value["usergroup_id"]);

			//実行可能コンテンツチェック
			if(!in_array($contentData["contentclass"],$wf_contentclass)){
				continue;
			}

			//実行可能フォルダチェック
			if(!in_array("all",$wf_folder_id) && !in_array($contentData["folder_id"],$wf_folder_id)){
				continue;
			}

			//実行可能ユーザグループチェック
			if(!$admintype == SPConst::ADMINTYPE_SUPERVISOR && !in_array("all",$wf_usergroup_id)){
				$check = false;
				foreach($usergroups as $usergroup_id){
					if(in_array($usergroup_id,$wf_usergroup_id)){
						$check = true;
					}
				}
				if(!$check){
					continue;
				}
			}
			$workFlowList[] = $value;
		}
		if(count($workFlowList) > 0){
			return true;
		}else{
			return false;
		}
	}

	static function getWorkFlowListAvailable($content_id,$contentclass,$folder_id,$usergroups,$admintype,$workflowstate_id = ""){
		//コンテンツ取得
		if($content_id){
			$Content = new Content(Content::TABLE_MANAGEMENT); //コンテンツクラス
			$contentData = $Content->getContentDataByContentId($content_id);
			$mode = "edit";
		}else{
			$mode = "new";
		}

		//対象ワークフローアクションID
		if(!$workflowstate_id && $mode == "edit"){
			$workflowstate_id = $contentData["workflowstate_id"];
		}
		if(!$workflowstate_id){
			$workflowstate_id = 0;
		}
		//利用可能なワークフローアクション存在チェック
		$WorkFlow = new WorkFlow();
		$where = array();
		$where["workflowstate_from_id"] = $workflowstate_id;
		$where["active_flg"] = 1;
		$order = array();
		$order["sort_no"] = "asc";
		$workFlowListSrc = $WorkFlow->getListByParameters($where,$order);
		//実行可能なアクションに絞込み
		$workFlowList = array();
		foreach($workFlowListSrc as $key => $value){
			$wf_contentclass = explode(",",$value["contentclass"]);
			$wf_folder_id = explode(",",$value["folder_id"]);
			$wf_usergroup_id = explode(",",$value["usergroup_id"]);

			//実行可能コンテンツチェック
			if(!in_array($contentclass,$wf_contentclass)){
				continue;
			}

			//実行可能フォルダチェック
			if(!in_array("all",$wf_folder_id) && !in_array($folder_id,$wf_folder_id)){
				continue;
			}

			//実行可能ユーザグループチェック
			if(!$admintype == SPConst::ADMINTYPE_SUPERVISOR && !in_array("all",$wf_usergroup_id)){
				$check = false;
				foreach($usergroups as $usergroup_id){
					if(in_array($usergroup_id,$wf_usergroup_id)){
						$check = true;
					}
				}
				if(!$check){
					continue;
				}
			}
			$workFlowList[] = $value;
		}
		return $workFlowList;
	}

	static function executeWorkFlow($content_id,$workflow_id,$workflow_comment,$user_id){
		$WorkFlow = new WorkFlow();
		$Content = new Content(Content::TABLE_MANAGEMENT);
		$ContentPublic = new Content(Content::TABLE_PUBLIC);
		$contentData = $Content->getContentDataByContentId($content_id);
		if(!$contentData){
			Logger::error("ワークフローエラー：コンテンツが取得できません。ID:".$content_id);
			return false;
		}
		$workFlowData = $WorkFlow->getDataByPrimaryKey($workflow_id);
		if(!$workFlowData){
			Logger::error("ワークフローエラー：ワークフローアクションが取得できません。ID:".$workflow_id);
			return false;
		}

		$workflowstate_from_id = $workFlowData["workflowstate_from_id"];
		if($workflowstate_from_id == 0){
			$workflowstate_from_id = null;
		}
		$workflowstate_to_id = $workFlowData["workflowstate_to_id"];
		if($workflowstate_to_id == 0){
			$workflowstate_to_id = null;
		}

		if($workflowstate_from_id != $contentData["workflowstate_id"]){
			Logger::error("ワークフローエラー：ワークフロー状態とアクションFROMが不一致です。workflowstate_id=".$contentData["workflowstate_id"]." workflowstate_from_id=".$workflowstate_from_id);
			return false;
		}

		//ワークフロー状態更新
		$saveData = array();
		$saveData["workflowstate_id"] = $workflowstate_to_id;
		if(!$Content->update(array("content_id" => $content_id), $saveData)){
			Logger::error("ワークフローエラー：ワークフロー状態の更新に失敗しました。ID:".$content_id);
			return false;
		}

		//ワークフロー自動実行処理
		$changes = explode(",",$workFlowData["changes"]);
		if(in_array("publish", $changes)){
			//自動公開
			$Content->publishContent($content_id);
		}

		//ワークフローメール送信
		if($workFlowData["mailcontent_id"]){
			//メールテンプレート取得
			$mailContentData = $ContentPublic->getDataByPrimaryKey($workFlowData["mailcontent_id"]);
			if($mailContentData){
				//操作ユーザ情報取得
				$User = new User();
				$userData = $User->getDataByPrimaryKey($user_id);

				//対象ユーザ一覧取得
				$userList = self::getWorkFlowMailUserList($workflowstate_to_id, $user_id);
				if(!$userList){
					return true;
				}
				//メール内容設定
				$replace = array();
				$replace[Config::REPLACE_MARK_START."workflow_comment".Config::REPLACE_MARK_END] = $workflow_comment;
				foreach($contentData as $key => $value){
					$replace[Config::REPLACE_MARK_START.$key.Config::REPLACE_MARK_END] = $value;
				}

				$subject = $mailContentData["title"];
				$body = $mailContentData["content"];
				$from = $userData["mail"];

				$Mail = new Mail();
				$Mail->setSubject($subject);
				$Mail->setBody($body);
				$Mail->setFrom($from);
				$Mail->setReplace($replace);
				foreach($userList as $user){
					if($user["mail"]){
						$Mail->addTo($user["mail"]);
					}
				}
				if($Mail->send()){
					Logger::info("ワークフローメールを送信しました。user_id=".$user_id."&comment=".$workflow_comment);
				}else{
					Logger::error("ワークフローメールを送信に失敗しました。");
				}
			}
		}

		return true;
	}

	static function getWorkFlowMailUserList($workflowstate_to_id,$user_id){
		//自アクションを実行可能なユーザグループ検索
		$WorkFlow = new WorkFlow();
		$all_group_flg = false;
		$usergroup_list = array();
		if($workflowstate_to_id){
			$where = array();
			$where["workflowstate_from_id"] = $workflowstate_to_id;
			$where["active_flg"] = 1;
			$workFlowList = $WorkFlow->getListByParameters($where);
			foreach($workFlowList as $workFlowData){
				$usergroup_id_one = explode(",",$workFlowData["usergroup_id"]);
				foreach($usergroup_id_one as $key => $value){
					if($value){
						if($value == "all"){
							$all_group_flg = true;
						}else{
							$usergroup_list[] = $value;
							Logger::debug("setgroup".$value);
						}
					}
				}
			}
		}else{
			$all_group_flg = true;
		}
		if(!$all_group_flg && count($usergroup_list) == 0){
			return array();
		}elseif($all_group_flg){
			$usergroup_list = array();
		}
		//メール送信対象のユーザ検索
		$User = new User();
		$userListSrc = $User->getUserListInUserGroupList($usergroup_list);
		$userList = array();
		foreach($userListSrc as $key => $value){
			if($value["user_id"] != $user_id){
				$userList[] = $value;
			}
		}

		return $userList;
	}
}