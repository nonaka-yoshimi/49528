<?php
require_once(dirname(__FILE__).'/../ApplicationCommon/Logger.php'); 	//ログ取得
require_once(dirname(__FILE__).'/../ApplicationCommon/Debug.php'); 		//デバッグ
require_once(dirname(__FILE__).'/EngineCommon.php'); 					//エンジン共通

require_once(dirname(__FILE__).'/../DataAccess/ContentAccess.php'); 	//コンテンツアクセスクラス
/*
説明：スクリプトエンジンクラス
作成日：2013/11/30 TS谷
*/

/**
 * スクリプトエンジンクラス
 * スクリプトをレスポンスする
 */
class ScriptEngine
{
	var $management_flg = false;

	var $device;

	/**
	 * コンテンツを出力する
	 * @param string $url URL
	 * @param array $session セッション
	 * @param array $domain ドメイン
	 * @param array $device デバイス
	 * @param array $extensionInfo 拡張子情報
	 * @param array $config ファイル出力設定
	 * @param boolean $management_flg 管理コンテンツフラグ
	 */
	function outputContent($url,$session,$domain,$device,$extensionInfo,$config,$management_flg = false){

		$this->management_flg = $management_flg;

		$this->device = $device;

		if(!isset($extensionInfo["mime"])){
			return false;
		}
		//Debug::sqlCheckStart();

		//出力コンテンツ
		$output = "";

		//URLに基づきコンテンツ取得
		if($management_flg){
			$contentTrn = new ContentAccess("content");
		}else{
			$contentTrn = new ContentAccess("content_public");
		}
		$contentData = $contentTrn->getScriptByUrl($url,$domain,$device);

		//取得できない場合はNOT FOUND
		if(!$contentData){
			return false;
		}

		//ページ設定を取得
		$pageConfig["content"] = $contentData["content"];
		$pageConfig["base_dir_path"] = $contentData["base_dir_path"];
		$pageConfig["addinfo_index"] = EngineCommon::contentIndexToArray($contentData["addinfo_index"]);

		//部品合成
		$pageConfig["_root"] = array();
		$pageConfig["element_index"] = array();
		$pageConfig = $this->elementCompose($pageConfig);

		//置換合成
		$replace = array();
		$pageConfig = $this->replaceCompose($pageConfig, $replace);

		//非置換削除
		$pageConfig = $this->deleteCompose($pageConfig);

		//スクリプト構築
		$output = $this->buildScript($pageConfig);

		header( "Content-Type: ".$extensionInfo["mime"]);	//mime typeの出力
		print($output);
		exit;
	}

	function getIncludeFunctionDic($content){
		$functionDic = array();
		$pattern = '/'.Config::REPLACE_MARK_START.'(include:([a-z0-9]+)\.([a-z0-9]+))'.Config::REPLACE_MARK_END.'/i';
		preg_match_all($pattern, $content , $match);
		if($match){
			for($i=0;$i<count($match[0]);$i++){
				$functionDic[$match[0][$i]]["function_full"] = $match[0][$i];
				$functionDic[$match[0][$i]]["function_text"] = $match[1][$i];
				$functionDic[$match[0][$i]]["type"] = "element";
				$functionDic[$match[0][$i]]["id"] = $match[2][$i];
				$functionDic[$match[0][$i]]["target"] = $match[3][$i];
			}
		}
		return $functionDic;
	}

	function getAddInfoFunctionDic($content,$addinfo_index){
		$functionDic = array();
		$pattern = '/'.Config::REPLACE_MARK_START.'([a-z0-9]+)'.Config::REPLACE_MARK_END.'/i';
		preg_match_all($pattern, $content , $match);
		if($match){
			for($i=0;$i<count($match[0]);$i++){
				$functionDic[$match[0][$i]]["function_full"] = $match[0][$i];
				$functionDic[$match[0][$i]]["function_text"] = $match[1][$i];
				$functionDic[$match[0][$i]]["type"] = "element";
				$functionDic[$match[0][$i]]["name"] = $match[1][$i];

				if(isset($addinfo_index[$match[1][$i]])){
					$functionDic[$match[0][$i]]["id"] = $addinfo_index[$match[1][$i]];
				}else{
					$functionDic[$match[0][$i]]["id"] = "";
				}
			}
		}
		return $functionDic;
	}

	function getAddInfoArr($addInfoFunctionDic){
		$array = array();
		foreach($addInfoFunctionDic as $addInfoFunction){
			if($addInfoFunction["id"]){
				$array[] = $addInfoFunction["id"];
			}
		}
		return $array;
	}

	function replaceCompose($pageConfig,$replace){
		/*
		$replaceArr[Config::REPLACE_MARK_START."title".Config::REPLACE_MARK_END] = $replace["title"];
		$replaceArr[Config::REPLACE_MARK_START."keywords".Config::REPLACE_MARK_END] = $replace["keywords"];
		$replaceArr[Config::REPLACE_MARK_START."description".Config::REPLACE_MARK_END] = $replace["description"];
		$replaceArr[Config::REPLACE_MARK_START."author".Config::REPLACE_MARK_END] = $replace["author"];
		$replaceArr[Config::REPLACE_MARK_START."doctype".Config::REPLACE_MARK_END] = $replace["doctype"];
		$replaceArr[Config::REPLACE_MARK_START."url".Config::REPLACE_MARK_END] = $replace["url"];

		$pageConfig["content"] = str_replace(array_keys($replaceArr), array_values($replaceArr), $pageConfig["content"]);
		*/
		return $pageConfig;
	}

	function deleteCompose($pageConfig){
		$pattern = '/'.Config::REPLACE_MARK_START.'([a-z0-9\.:]+)'.Config::REPLACE_MARK_END.'/i';
		preg_match_all($pattern, $pageConfig["content"] , $match);
		$replace = array();
		if($match){
			for($i=0;$i<count($match[0]);$i++){
				$replace[$match[0][$i]] = "";
			}
			$pageConfig["content"] = str_replace(array_keys($replace), array_values($replace), $pageConfig["content"]);
		}
		return $pageConfig;
	}

	function elementCompose($pageConfig){
		//URLに基づきコンテンツ取得
		if($this->management_flg){
			$contentTrn = new ContentAccess("content");
		}else{
			$contentTrn = new ContentAccess("content_public");
		}

		$indexFunctionDic = $this->getIncludeFunctionDic($pageConfig["content"]);

		//インクルード組み込みを実施
		foreach($indexFunctionDic as $key => $value){
			if(in_array($value["id"], $pageConfig["_root"])){
				continue;
			}elseif($value["id"]){

				//要素データを取得
				$elementData = $contentTrn->getElementById($value["id"]);
				//データがない場合は終了
				if(!$elementData){
					$pageConfig["content"] = str_replace($key, "", $pageConfig["content"]);
					continue;
				}

				if($value["target"] == "content"){
					//要素設定統合
					$configNext = $pageConfig;

					$configNext["content"] = $elementData["content"];

					//設定統合
					$element_index = EngineCommon::contentIndexToArray($elementData["element_index"]);
					$configNext["element_index"] = array_merge($element_index,$pageConfig["element_index"]);

					//コンテンツルートに追加
					$configNext["_root"][] = $elementData["content_id"];

					//部品の部品合成
					$configNext = $this->elementCompose($configNext);

					//コンテンツ置換
					$pageConfig["content"] = str_replace($key, $configNext["content"], $pageConfig["content"]);
				}elseif($value["target"] == "title"){
					//タイトル置換
					$pageConfig["content"] = str_replace($key, $elementData["title"], $pageConfig["content"]);
				}
			}
		}

		$addinfoFunctionDic = $this->getAddInfoFunctionDic($pageConfig["content"],$pageConfig["addinfo_index"]);
		$addinfoArr = $this->getAddInfoArr($addinfoFunctionDic);

		//追加情報組み込みを実施
		if(count($addinfoArr) > 0){
			//追加情報取得
			$addinfoList = $contentTrn->getAddInfoList($addinfoArr);
			$replace_arr = array();
			foreach($addinfoList as $addinfo){
				$index = Config::REPLACE_MARK_START.$addinfo["name"].Config::REPLACE_MARK_END;
				if(isset($addinfoFunctionDic[$index])){
					$replace_arr[$index] = $addinfo["addinfo_content"];
				}
			}
			//追加情報置換
			$pageConfig["content"] = str_replace(array_keys($replace_arr), array_values($replace_arr), $pageConfig["content"]);
		}

		return $pageConfig;
	}

	function buildScript($pageConfig){
		$str = "";
		$str .= $pageConfig["content"];
		return $str;
	}
}
?>