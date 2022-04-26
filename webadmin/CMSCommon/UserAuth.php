<?php
require_once(dirname(__FILE__).'/../ApplicationCommon/Util.php'); 	//ユーティリティ
require_once(dirname(__FILE__).'/../DataAccess/User.php'); //ユーザクラス
require_once(dirname(__FILE__).'/../DataAccess/UserGroup.php'); //ユーザグループクラス
/*
説明：ユーザ権限関連機能クラス
作成日：2013/12/2 TS谷
*/

/**
 * ユーザ権限関連機能クラス
 */
class UserAuth{


	/**
	 * ユーザIDに基づき、権限を保有する部署一覧を取得する
	 * @param string $user_id ユーザID
	 */
	static function getAuthUserGroupListByUserId($user_id){
		//ユーザの所属グループ一覧を取得する
		$User = new User();
		$userGroupList = $User->getUserGroupListByUserId($user_id);

		//所属グループ毎に子グループ一覧を取得する
		$resultList = array();
		foreach($userGroupList as $userGroup ){
			$groupList = self::getChildGroupByUserGroupId($userGroup["usergroup_id"]);
			$userGroupList = array_merge($userGroupList,$groupList);
		}

		//重複排除
		$resultList = Util::getUniqueDataByDataListAndName($userGroupList, "usergroup_id");
		return $resultList;
	}

	/**
	 * ユーザグループIDに基づき子グループ一覧を取得する(ループ処理)
	 * @param string $usergroup_id ユーザグループID
	 * @param array $mem メモリ
	 * @return multitype:|Ambigous <multitype:, Ambigous, クエリ実行結果, boolean>
	 */
	static function getChildGroupByUserGroupId($usergroup_id,$mem = array()){
		if(in_array($usergroup_id,$mem)){
			return array();
		}else{
			$mem[] = $usergroup_id;
		}

		//自身の子グループ一覧を取得する
		$UserGroup = new UserGroup();
		$userGroupList = $UserGroup->getChildGroupByUserGroupId($usergroup_id);

		//孫グループ以下一覧を取得する
		$childGroupList = array();
		for($i=0;$i<count($userGroupList);$i++){
			$childGroupList = self::getChildGroupByUserGroupId($userGroupList[$i]["usergroup_id"],$mem);
		}
		//取得データ結合
		if(count($childGroupList) > 0){
			$userGroupList = array_merge($userGroupList,$childGroupList);
		}

		return $userGroupList;
	}

	/**
	 * 機能操作権限一覧リストに基づき、対象ユーザの権限辞書を作成する
	 * @param string $user_id ユーザID
	 */
	static function getOperationAuthDicByAuthList($operationAuthList){
		if(!is_array($operationAuthList)){
			return array();
		}
		$authDic = array();
		foreach($operationAuthList as $operationAuth){
			foreach($operationAuth as $key => $value){
				if(preg_match("/^ope_auth/i",$key)){
					if(!isset($authDic[$key]) || $authDic[$key] < $value){
						$authDic[$key] = $value;
					}
				}
			}
		}
		return $authDic;
	}

	/**
	 * カラム名の後ろに数値インデックスの付いた二次元配列の指定カラムを最大値で統合する
	 * @param array $list 二次元配列
	 * @param unknown $columns マージ対象カラム名配列
	 * @param unknown $index_num マージ対象カラム名数値インデックス最大値(１～設定値）
	 * @param number $default デフォルト数値
	 * @param number $delete_start 削除開始インデックス
	 * @param number $delete_end 削除終了インデックス
	 * @return array マージ後の二次元配列
	 */
	static function mergeMaxNumColumnWithNumIndex($list,$columns,$default = 0,$merge_start = 0,$merge_end = 0,$delete_start = 0,$delete_end = 0){
		for($i=0;$i<count($list);$i++){
			for($ii=0;$ii<count($columns);$ii++){
				$list[$i][$columns[$ii]] = $default;
			}
		}

		for($i=0;$i<count($list);$i++){
			$data = 0;
			for($ii=0;$ii<count($columns);$ii++){
				for($iii=$merge_start;$iii<=$merge_end;$iii++){
					$index = $columns[$ii].$iii;
					if($list[$i][$index] > $list[$i][$columns[$ii]]){
						$list[$i][$columns[$ii]] = $list[$i][$index];
					}
					unset($list[$i][$index]);
				}
				for($iii=$delete_start;$iii<=$delete_end;$iii++){
					$index = $columns[$ii].$iii;
					unset($list[$i][$index]);
				}
			}
		}

		return $list;
	}

	/**
	 * キーを指定し、指定カラムの値を最大値統合した配列を取得する
	 * @param unknown $list
	 * @param unknown $key
	 * @param unknown $columns
	 * @param number $default
	 * @return multitype:unknown
	 */
	static function mergeRecordWithMaxNumColumn($list,$key,$columns,$default = 0){
		$result_dic = array();
		$mem_data = array();
		for($i=0;$i<count($columns);$i++){
			$mem_data[$columns[$i]] = $default;
		}

		for($i=0;$i<count($list);$i++){
			if(!array_key_exists($list[$i][$key], $result_dic)){
				$result_dic[$list[$i][$key]] = $list[$i];
			}else{
				for($ii=0;$ii<count($columns);$ii++){
					if($result_dic[$list[$i][$key]][$columns[$ii]] < $list[$i][$columns[$ii]]){
						$result_dic[$list[$i][$key]][$columns[$ii]] = $list[$i][$columns[$ii]];
					}
				}
			}
		}
		$result_arr = array();
		foreach($result_dic as $record){
			$result_arr[] = $record;
		}
		return $result_arr;
	}

	/**
	 * キーを指定し、指定カラムの値を最小値統合した配列を取得する
	 * @param unknown $list
	 * @param unknown $key
	 * @param unknown $columns
	 * @param number $default
	 * @return multitype:unknown
	 */
	static function mergeRecordWithMinNumColumn($list,$key,$columns,$default = 0){
		$result_dic = array();
		$mem_data = array();
		for($i=0;$i<count($columns);$i++){
			$mem_data[$columns[$i]] = $default;
		}

		for($i=0;$i<count($list);$i++){
			if(!array_key_exists($list[$i][$key], $result_dic)){
				$result_dic[$list[$i][$key]] = $list[$i];
			}else{
				for($ii=0;$ii<count($columns);$ii++){
					if($result_dic[$list[$i][$key]][$columns[$ii]] > $list[$i][$columns[$ii]]){
						$result_dic[$list[$i][$key]][$columns[$ii]] = $list[$i][$columns[$ii]];
					}
				}
			}
		}
		$result_arr = array();
		foreach($result_dic as $record){
			$result_arr[] = $record;
		}
		return $result_arr;
	}




}