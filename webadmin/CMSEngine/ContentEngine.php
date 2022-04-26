<?php
require_once(dirname(__FILE__).'/../ApplicationCommon/Logger.php'); 	//ログ取得
require_once(dirname(__FILE__).'/../ApplicationCommon/Debug.php'); 		//デバッグ
require_once(dirname(__FILE__).'/../ApplicationCommon/Util.php'); 		//ユーティリティ
require_once(dirname(__FILE__).'/EngineCommon.php'); 					//エンジン共通

require_once(dirname(__FILE__).'/../DataAccess/ContentAccess.php'); 	//コンテンツアクセスクラス
/*
説明：コンテンツエンジンクラス
作成日：2013/11/30 TS谷
*/

/**
 * コンテンツエンジンクラス
 * コンテンツをレスポンスする
 */
class ContentEngine
{
	var $management_flg = false;

	var $device;

	var $contentTrn;

	var $requestReplaceArr = array();

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
		//Debug::sqlCheckStart();
		$this->management_flg = $management_flg;
		$this->device = $device;

		//リクエスト置換情報の格納
		$this->setRequestReplaceArr();

		//出力コンテンツ
		$output = "";

		//URLに基づきコンテンツ取得
		if($this->management_flg){
			$this->contentTrn = new ContentAccess("content");
		}else{
			$this->contentTrn = new ContentAccess("content_public");
		}
		//$this->contentTrn->loadAddinfoContent();
		$contentData = $this->contentTrn->getContentByUrl($url,$domain,$device);

		//取得できない場合はNOT FOUND
		if(!$contentData){
			return false;
		}

		//追加情報の取得
		$replace = array();
		$addinfoList = $this->contentTrn->getAddInfoListByContentId($contentData["content_id"]);
		foreach($addinfoList as $addinfoContent){
			if(!isset($replace[$addinfoContent["name"]])){
				if($addinfoContent["addinfo_content"] != null && $addinfoContent["addinfo_content"] != ""){
					$replace[$addinfoContent["name"]] = $addinfoContent["addinfo_content"];
					$replace["addinfo_".$addinfoContent["name"]] = $addinfoContent["addinfo_content"];
				}else if($addinfoContent["optionvalue"] != null && $addinfoContent["optionvalue"] != ""){
					$replace[$addinfoContent["name"]] = $addinfoContent["optionvalue"];
					$replace["addinfo_".$addinfoContent["name"]] = $addinfoContent["optionvalue"];
				}else{
					$replace[$addinfoContent["name"]] = "";
					$replace["addinfo_".$addinfoContent["name"]] = "";
				}
			}
		}

		//置換用に退避
		$replace["content_id"] = $contentData["content_id"];
		$replace["title"] = $contentData["title"];
		$replace["keywords"] = $contentData["keywords"];
		$replace["description"] = $contentData["description"];
		$replace["author"] = $contentData["author"];
		$replace["doctype"] = $contentData["doctype"];
		$replace["base_dir_path"] = $contentData["base_dir_path"];
		$replace["domain"] = $contentData["domain"];
		$replace["domain_name"] = $contentData["domain_name"];
		$replace["title_prefix"] = $contentData["title_prefix"];
		$replace["title_suffix"] = $contentData["title_suffix"];
		$replace["folder_title_prefix"] = $contentData["folder_title_prefix"];
		$replace["folder_title_suffix"] = $contentData["folder_title_suffix"];
		$replace["domain_title_prefix"] = $contentData["domain_default_title_prefix"];
		$replace["domain_title_suffix"] = $contentData["domain_default_title_suffix"];
		$replace["url"] = $url;

		//セッション情報格納
		if(isset($session->user) && isset($session->user["user_id"])){
			$replace["session.login"] = 1;
			foreach($session->user as $key => $value){
				if(!is_array($value)){
					$replace["session.".$key] = $value;
				}
			}
		}else{
			$replace["session.login"] = 0;
		}

		$replace["content_length"] = strlen($contentData["content"]);


		//ページ設定を取得
		$pageConfig["content_id"] = $contentData["content_id"];
		$pageConfig["title"] = $contentData["title"];
		$pageConfig["content"] = $contentData["content"];
		$pageConfig["keywords"] = $contentData["keywords"];
		$pageConfig["description"] = $contentData["description"];
		$pageConfig["author"] = $contentData["author"];
		$pageConfig["doctype"] = $contentData["doctype"];
		$pageConfig["base_dir_path"] = $contentData["base_dir_path"];
		$pageConfig["url"] = $url;
		$pageConfig["html_attr"] = $contentData["html_attr"];
		$pageConfig["head_attr"] = $contentData["head_attr"];
		$pageConfig["head_code"] = $contentData["head_code"];
		$pageConfig["body_attr"] = $contentData["body_attr"];
		$pageConfig["stylesheet_index"] = EngineCommon::csvToArray($contentData["stylesheet_index"]);
		$pageConfig["script_index"] = EngineCommon::csvToArray($contentData["script_index"]);
		$pageConfig["addinfo_index"] = EngineCommon::contentIndexToArray($contentData["addinfo_index"]);
		$pageConfig["element_index"] = EngineCommon::contentIndexToArray($contentData["element_index"]);
		$pageConfig["content_root"][] = $contentData["content_id"];
		$pageConfig["_root"][] = $contentData["content_id"];
		$pageConfig["base_dir_path"] = $contentData["base_dir_path"];
		$pageConfig["replace"] = $replace;

		//テンプレートインデックスを配列化
		$contentData["template_stylesheet_index"] = EngineCommon::csvToArray($contentData["template_stylesheet_index"]);
		$contentData["template_script_index"] = EngineCommon::csvToArray($contentData["template_script_index"]);
		$contentData["template_addinfo_index"] = EngineCommon::contentIndexToArray($contentData["template_addinfo_index"]);
		$contentData["template_element_index"] = EngineCommon::contentIndexToArray($contentData["template_element_index"]);

		//テンプレート合成
		$pageConfig = $this->templateCompose($pageConfig, $contentData);


		//テンプレートID再設定
		$pageConfig["template_id"] = $contentData["template_id"];

		//デフォルト設定反映
		if(!$pageConfig["doctype"]){
			$pageConfig["doctype"] = $contentData["domain_default_doctype"];
		}

		//部品合成
		$pageConfig = $this->elementCompose("content",$pageConfig, $replace);
		$pageConfig = $this->elementCompose("head_code",$pageConfig, $replace);

		//置換合成
		//Debug::arrayCheck($pageConfig);
		$pageConfig["content"] = $this->replaceCompose($pageConfig["content"], $replace);
		$pageConfig["head_code"] = $this->replaceCompose($pageConfig["head_code"], $replace);

		//リスト関数合成
		$pageConfig["content"] = $this->listFunctionCompose($pageConfig["content"],$pageConfig["addinfo_data"],$replace);

		//出力整形合成#1
		$pageConfig["content"] = $this->utilCompose($pageConfig["content"],$replace,true);
		$pageConfig["head_code"] = $this->utilCompose($pageConfig["head_code"],$replace,true);

		//条件分岐関数合成
		$pageConfig["content"] = $this->ifFunctionCompose($pageConfig["content"],$pageConfig["addinfo_data"],$replace);
		$pageConfig["head_code"] = $this->ifFunctionCompose($pageConfig["head_code"],$pageConfig["addinfo_data"],$replace);

		//部品合成#2
		$pageConfig = $this->elementCompose("content",$pageConfig, $replace);
		$pageConfig = $this->elementCompose("head_code",$pageConfig, $replace);

		//置換合成#2
		$pageConfig["content"] = $this->replaceCompose($pageConfig["content"], $replace);
		$pageConfig["head_code"] = $this->replaceCompose($pageConfig["head_code"], $replace);

		//リダイレクト識別子エスケープ
		if(strpos($pageConfig["content"], "[SPREDIRECT::") !== false){
			$pattern = "/\[SPREDIRECT::([a-z0-9=_\-<>!\|\/\.:\#]+)::SPEND\]/i";
			preg_match_all($pattern, $pageConfig["content"] , $match);
			if($match){
				for($i=0;$i<count($match[0]);$i++){
					$pageConfig["content"] = str_replace($match[0][0], "", $pageConfig["content"]);
				}
			}
		}
		if(strpos($pageConfig["head_code"], "[SPREDIRECT::") !== false){
			$pattern = "/\[SPREDIRECT::([a-z0-9=_\-<>!\|\/\.:\#]+)::SPEND\]/i";
			preg_match_all($pattern, $pageConfig["head_code"] , $match);
			if($match){
				for($i=0;$i<count($match[0]);$i++){
					$pageConfig["head_code"] = str_replace($match[0][0], "", $pageConfig["head_code"]);
				}
			}
		}

		//拡張機能合成
		$pageConfig["content"] = $this->extensionCompose($pageConfig,$pageConfig["content"],$session);
		$pageConfig["head_code"] = $this->extensionCompose($pageConfig,$pageConfig["head_code"],$session);

		//リダイレクト処理
		if(strpos($pageConfig["content"], "[SPREDIRECT::") !== false){
			$pattern = "/\[SPREDIRECT::([a-z0-9=_\-<>!\|\/\.:\#]+)::SPEND\]/i";
			preg_match_all($pattern, $pageConfig["content"] , $match);
			if(isset($match[0][0])){
				$url = $match[1][0];
				header("Location: ".$url);
			}
		}
		if(strpos($pageConfig["head_code"], "[SPREDIRECT::") !== false){
			preg_match_all($pattern, $pageConfig["head_code"] , $match);
			if(isset($match[0][0])){
				$url = $match[1][0];
				header("Location: ".$url);
			}
		}

		//リクエストパラメータ合成
		$pageConfig["content"] = $this->requestCompose($pageConfig["content"]);
		$pageConfig["head_code"] = $this->requestCompose($pageConfig["head_code"]);

		//出力整形合成#2
		$pageConfig["content"] = $this->utilCompose($pageConfig["content"],$replace,true);
		$pageConfig["head_code"] = $this->utilCompose($pageConfig["head_code"],$replace,true);

		//HEAD置換子変換
		$pageConfig["head_code"] = $this->headReplaceMarkReplace($pageConfig["head_code"]);

		//非置換削除
		$pageConfig["content"] = $this->deleteCompose($pageConfig["content"]);
		$pageConfig["head_code"] = $this->deleteCompose($pageConfig["head_code"]);

		//タイトルプレフィックス追加
		if($contentData["title_prefix"]){
			$pageConfig["title"] = $contentData["title_prefix"].$pageConfig["title"];
		}elseif($contentData["folder_title_prefix"]){
			$pageConfig["title"] = $contentData["folder_title_prefix"].$pageConfig["title"];
		}else{
			$pageConfig["title"] = $contentData["domain_default_title_prefix"].$pageConfig["title"];
		}
		//タイトルサフィックス追加
		if($contentData["title_suffix"]){
			$pageConfig["title"] = $pageConfig["title"].$contentData["title_suffix"];
		}elseif($contentData["folder_title_suffix"]){
			$pageConfig["title"] = $pageConfig["title"].$contentData["domain_default_title_suffix"];
		}else{
			$pageConfig["title"] = $pageConfig["title"].$contentData["domain_default_title_suffix"];
		}
		//タイトル整形
		$pageConfig["title"] = trim($pageConfig["title"]);

		//スタイルシート・スクリプト一覧を取得
		//一覧配列作成
		$refArr = array(); //参照先ID一覧
		if($pageConfig["stylesheet_index"] && $pageConfig["script_index"]){
			$refArr = array_merge($pageConfig["stylesheet_index"],$pageConfig["script_index"]);		//配列マージ
		}elseif($pageConfig["stylesheet_index"]){
			$refArr = $pageConfig["stylesheet_index"];
		}elseif($pageConfig["script_index"]){
			$refArr = $pageConfig["script_index"];
		}
		//参照先のデータ一覧を取得
		if(count($refArr) > 0){
			$refList = $this->contentTrn->getStyleScriptListInArray($refArr);
		}else{
			$refList = array();
		}
		//参照先情報を設定
		$pageConfig["stylesheet"] = $this->getCssListFromRefList($refList);
		$pageConfig["script"] = $this->getScriptListFromRefList($refList);

		//Debug::arrayCheck($refList);
		//Debug::arrayCheck($pageConfig);

		//HTML生成
		$output = $this->buildHTMLPage($pageConfig);

		//階層補正
		$output = Util::encodeHTMLBasePath($output);

		print($output);
		exit;
	}

	function setRequestReplaceArr(){
		foreach($_REQUEST as $key => $value){
			$this->requestReplaceArr[Config::REQUEST_MARK_START.$key.Config::REQUEST_MARK_END] = $value;
		}
	}

	function templateCompose($pageConfig,$contentData){
		//テンプレートIDを更新
		$pageConfig["template_id"] = $contentData["template_id"];

		//テンプレートコンテンツ置換
		if($contentData["template_content"]){
			$pageConfig["content"] = str_replace(array(Config::REPLACE_MARK_START."content".Config::REPLACE_MARK_END), array($pageConfig["content"]), $contentData["template_content"]);
		}

		//キーワード合成
		if($contentData["template_keywords"]){
			$pageKeywordsArr = explode(",",$pageConfig["keywords"]);				//ページのキーワードをカンマで分割
			$templateKeywordsArr = explode(",",$contentData["template_keywords"]);	//テンプレートのキーワードをカンマで分割
			$keyworsArr = array_merge($pageKeywordsArr,$templateKeywordsArr);		//配列マージ
			$keyworsArr = array_unique($keyworsArr);								//一意化
			//再格納
			$keywords_str = "";
			foreach($keyworsArr as $keyword){
				if($keywords_str != ""){ $keywords_str .= ","; }
				$keywords_str .= $keyword;
			}
			$pageConfig["keywords"] = $keywords_str;
		}

		//ディスクリプションデフォルト
		if(!$pageConfig["description"]){
			$pageConfig["description"] = $contentData["template_description"];
		}

		//作成者デフォルト
		if(!$pageConfig["author"]){
			$pageConfig["author"] = $contentData["template_author"];
		}

		//DOCTYPEデフォルト
		if(!$pageConfig["doctype"]){
			$pageConfig["doctype"] = $contentData["template_doctype"];
		}

		//HTML属性合成
		if($contentData["template_html_attr"]){
			$pageHtmlAttrArr = explode(" ",$pageConfig["html_attr"]);				//ページのHTML属性を空白で分割
			$templateHtmlAttrArr = explode(" ",$contentData["template_html_attr"]);	//テンプレートのHTML属性を空白で分割
			$HtmlAttrArr = array_merge($pageHtmlAttrArr,$templateHtmlAttrArr);		//配列マージ
			$HtmlAttrArr = array_unique($HtmlAttrArr);								//一意化

			//同一属性削除,再格納
			$htmlattr_str = "";
			$attr_memory = array();
			foreach($HtmlAttrArr as $htmlattr){
				$attrArr = explode("=",$htmlattr);
				if(!in_array($attrArr[0], $attr_memory)){
					if($htmlattr_str != ""){ $htmlattr_str .= " "; }
					$htmlattr_str .= $htmlattr;
					$attr_memory[] = $attrArr[0];
				}
			}
			$pageConfig["html_attr"] = $htmlattr_str;
		}

		//HEAD属性合成
		if($contentData["template_head_attr"]){
			$pageHeadAttrArr = explode(" ",$pageConfig["head_attr"]);				//ページのHEAD属性を空白で分割
			$templateHeadAttrArr = explode(" ",$contentData["template_head_attr"]);	//テンプレートのHEAD属性を空白で分割
			$HeadAttrArr = array_merge($pageHeadAttrArr,$templateHeadAttrArr);		//配列マージ
			$HeadAttrArr = array_unique($HeadAttrArr);								//一意化

			//同一属性削除,再格納
			$headattr_str = "";
			$attr_memory = array();
			foreach($HeadAttrArr as $headattr){
				$attrArr = explode("=",$headattr);
				if(!in_array($attrArr[0], $attr_memory)){
					if($headattr_str != ""){ $headattr_str .= " "; }
					$headattr_str .= $headattr;
					$attr_memory[] = $attrArr[0];
				}
			}
			$pageConfig["head_attr"] = $headattr_str;
		}

		//HEADコード追加
		if($contentData["template_head_code"]){
			$pageConfig["head_code"] = $contentData["template_head_code"]."\n".$pageConfig["head_code"];
		}

		//BODY属性合成
		if($contentData["template_body_attr"]){
			$pageBodyAttrArr = explode(" ",$pageConfig["body_attr"]);				//ページのHEAD属性を空白で分割
			$templateBodyAttrArr = explode(" ",$contentData["template_body_attr"]);	//テンプレートのHEAD属性を空白で分割
			$BodyAttrArr = array_merge($pageBodyAttrArr,$templateBodyAttrArr);		//配列マージ
			$BodyAttrArr = array_unique($BodyAttrArr);								//一意化

			//同一属性削除,再格納
			$bodyattr_str = "";
			$attr_memory = array();
			foreach($BodyAttrArr as $bodyattr){
				$attrArr = explode("=",$bodyattr);
				if(!in_array($attrArr[0], $attr_memory)){
					if($bodyattr_str != ""){ $bodyattr_str .= " "; }
					$bodyattr_str .= $bodyattr;
					$attr_memory[] = $attrArr[0];
				}
			}
			$pageConfig["body_attr"] = $bodyattr_str;
		}

		//スタイルシート索引合成
		if($contentData["template_stylesheet_index"]){
			$cssArr = array_merge($pageConfig["stylesheet_index"],$contentData["template_stylesheet_index"]);	//配列マージ
			$cssArr = array_unique($cssArr);																	//一意化
			$pageConfig["stylesheet_index"] = $cssArr;															//再格納
		}

		//スクリプト索引合成
		if($contentData["template_script_index"]){
			$scriptArr = array_merge($pageConfig["script_index"],$contentData["template_script_index"]);		//配列マージ
			$scriptArr = array_unique($scriptArr);																//一意化
			$pageConfig["script_index"] = $scriptArr;															//再格納
		}

		//追加情報索引合成
		if($contentData["template_addinfo_index"]){
			$AddInfoArr = array_merge($contentData["template_addinfo_index"],$pageConfig["addinfo_index"]);		//配列マージ
			$pageConfig["addinfo_index"] = $AddInfoArr;															//再格納
		}

		//要素索引合成
		if($contentData["template_element_index"]){
			$ElementArr = array_merge($contentData["template_element_index"],$pageConfig["element_index"]);		//配列マージ
			$pageConfig["element_index"] = $ElementArr;															//再格納
		}

		//親テンプレート合成
		if($pageConfig["template_id"] && $contentData["parent_template_id"] && !in_array($pageConfig["template_id"],$pageConfig["content_root"])){
			//テンプレートIDに基づき親テンプレート取得
			$contentData = $this->contentTrn->getTemplateByTemplateId($pageConfig["template_id"],$this->device);
			$contentData["template_stylesheet_index"] = EngineCommon::csvToArray($contentData["template_stylesheet_index"]);
			$contentData["template_script_index"] = EngineCommon::csvToArray($contentData["template_script_index"]);
			$contentData["template_addinfo_index"] = EngineCommon::contentIndexToArray($contentData["template_addinfo_index"]);
			$contentData["template_element_index"] = EngineCommon::contentIndexToArray($contentData["template_element_index"]);

			//取得できない場合はリターン
			if(!$contentData){
				return $pageConfig;
			}

			//コンテンツルートに追加
			$pageConfig["content_root"][] = $pageConfig["template_id"];

			//テンプレート合成
			$pageConfig = $this->templateCompose($pageConfig, $contentData);
		}
		return $pageConfig;
	}

	function contentFunctionAnalyze($content){
		$functionDic = array();
		$pattern = '/'.Config::REPLACE_MARK_START.'([a-z0-9=\-_\.:]+)'.Config::REPLACE_MARK_END.'/i';
		preg_match_all($pattern, $content , $match);
		//Debug::arrayCheck($match);
		if($match){
			for($i=0;$i<count($match[0]);$i++){
				$functionDic[$i]["function_full"] = $match[0][$i];
				$functionDic[$i]["function_text"] = $match[1][$i];
				if(preg_match('/^[a-z0-9]+$/i',$functionDic[$i]["function_text"])){	//パターン追加情報
					$functionDic[$i]["type"] = "addinfo";
					$functionDic[$i]["name"] = $functionDic[$i]["function_text"];
				}elseif(preg_match('/^([a-z0-9]+)\.([a-z0-9]+)$/i',$functionDic[$i]["function_text"],$match2)){	//パターン要素
					$functionDic[$i]["type"] = "element";
					$functionDic[$i]["name"] = $match2[1];
					$functionDic[$i]["target"] = $match2[2];
					print_r($match2);
				}elseif(preg_match('/^include:([0-9]+)\.([a-z0-9]+)$/i',$functionDic[$i]["function_text"],$match2)){	//パターンインクルード
					$functionDic[$i]["type"] = "include";
					$functionDic[$i]["id"] = $match2[1];
					$functionDic[$i]["target"] = $match2[2];
				}elseif(preg_match('/^list:([a-z0-9]+):([a-z0-9=\-_\.:]+)$/i',$functionDic[$i]["function_text"],$match2)){	//パターンリスト
					//echo 'hit';
				}
			}
		}

		$resultArr = array();
		$function_memory = array();
		$counter = 0;
		for($i=0;$i<count($functionDic);$i++){
			if(!in_array($functionDic[$i]["function_text"], $function_memory)){	//重複排除
				$resultArr[$counter] = $functionDic[$i];
				$function_memory[] = $functionDic[$i]["function_text"];
				$counter++;
			}
		}

		return $resultArr;
	}

	function getElementFunctionDic($content,$element_index){
		$functionDic = array();
		$pattern = '/'.Config::REPLACE_MARK_START.'(([a-z0-9]+)\.([a-z0-9]+))'.Config::REPLACE_MARK_END.'/i';
		preg_match_all($pattern, $content , $match);
		if($match){
			for($i=0;$i<count($match[0]);$i++){
				if($match[2][$i] == "session" || $match[2][$i] == "SESSION"){
					//セッション要素は除外
					continue;
				}
				$functionDic[$match[0][$i]]["function_full"] = $match[0][$i];
				$functionDic[$match[0][$i]]["function_text"] = $match[1][$i];
				$functionDic[$match[0][$i]]["type"] = "element";
				$functionDic[$match[0][$i]]["name"] = $match[2][$i];
				$functionDic[$match[0][$i]]["target"] = $match[3][$i];

				if(isset($element_index[$match[2][$i]])){
					$functionDic[$match[0][$i]]["id"] = $element_index[$match[2][$i]];
				}else{
					$functionDic[$match[0][$i]]["id"] = "";
				}
			}
		}
		return $functionDic;
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
		$pattern = '/'.Config::REPLACE_MARK_START.'([a-z0-9_-]+)'.Config::REPLACE_MARK_END.'/i';
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

	function getListFunctionDic($content){
		$functionDic = array();
		//$pattern = '/'.Config::REPLACE_MARK_START.'(list:([a-z0-9]+):([a-z0-9=-_<>!\|\/\.:]+))'.Config::REPLACE_MARK_END.'/i';
		//$pattern = '/'.Config::REPLACE_MARK_START.'((list|select|count):([a-z0-9]+):([^'.Config::REPLACE_MARK.']+))'.Config::REPLACE_MARK_END.'/i';
		$pattern = '/'.Config::REPLACE_MARK_START.'((list|select|count):([a-z0-9_-]+):([^'.Config::REPLACE_MARK_START.Config::REPLACE_MARK_END.']+))'.Config::REPLACE_MARK_END.'/i';
		preg_match_all($pattern, $content , $match);
		if($match){
			for($i=0;$i<count($match[0]);$i++){
				$functionDic[$match[0][$i]]["function_full"] = $match[0][$i];
				$functionDic[$match[0][$i]]["function_text"] = $match[1][$i];
				if($match[2][$i] == "count" || $match[2][$i] == "COUNT"){
					$functionDic[$match[0][$i]]["type"] = "count";
				}else{
					$functionDic[$match[0][$i]]["type"] = "list";
				}
				$functionDic[$match[0][$i]]["list_index"] = $match[3][$i];
				$functionDic[$match[0][$i]]["list_main"] = $match[4][$i];

				$list_condition_arr = explode(":", $functionDic[$match[0][$i]]["list_main"]);

				foreach($list_condition_arr as $list_condition_one){
					if(preg_match("/\|/",$list_condition_one)){
						//OR条件の場合
						$list_condition_arr_or = explode("|",$list_condition_one);
						foreach($list_condition_arr_or as $list_condition_or_one){
							$functionDic[$match[0][$i]] = $this->getListConditionDic($functionDic[$match[0][$i]],$list_condition_or_one,"or");
						}
					}else{
						//OR条件ではない場合
						$functionDic[$match[0][$i]] = $this->getListConditionDic($functionDic[$match[0][$i]],$list_condition_one,"and");
					}
				}
			}
		}
		return $functionDic;
	}

function getListConditionDic($functionDic,$list_condition_one,$type = "and"){
		//if(preg_match('/^([\s\S]*)([=<>!]+|&lt;|&gt;|&lt;=|&gt;=)([\s\S]*)$/i',$list_condition_one,$list_one_match)){
		//if(preg_match('/^([\s\S]*)([=<>!]+|&lt;|&gt;|&lt;=|&gt;=)([\s\S]*)$/i',$list_condition_one,$list_one_match)){
		//if(preg_match('/^([\s\S]*)([=<>!]+)([\s\S]*)$/i',$list_condition_one,$list_one_match)){
		$list_condition_one = str_replace(array("&lt;","&gt;"), array("<",">"), $list_condition_one);
		if(preg_match('/^([^=<>!]*)([=<>!]+)([^=<>!]*[\s\S]*)$/i',$list_condition_one,$list_one_match)){
			$condition_target = $list_one_match[1];
			$condition = $list_one_match[2];
			$condition_value = $list_one_match[3];

			if($condition_target == "database"){
				$condition_dic = array();
				$condition_dic["value"] = $this->requestCompose($condition_value);
				$condition_dic["condition"] = $condition;
				$functionDic["database"] = $condition_dic;
			}elseif($condition_target == "content_id"){
				$condition_dic = array();
				$condition_dic["value"] = $this->requestCompose($condition_value);
				$condition_dic["condition"] = $condition;
				if($type == "or"){
					$functionDic["search_content_id"][] = $condition_dic;
					$functionDic["search"]["content_id"][] = $condition_dic;
				}else{
					$functionDic["search_content_id"] = $condition_dic;
					$functionDic["search"]["content_id"] = $condition_dic;
				}
			}elseif($condition_target == "title"){
				$condition_dic = array();
				$condition_dic["value"] = $this->requestCompose($condition_value);
				$condition_dic["condition"] = $condition;
				if($type == "or"){
					$functionDic["search_title"][] = $condition_dic;
					$functionDic["search"]["title"][] = $condition_dic;
				}else{
					$functionDic["search_title"] = $condition_dic;
					$functionDic["search"]["title"] = $condition_dic;
				}
			}elseif($condition_target == "content"){
				$condition_dic = array();
				$condition_dic["value"] = $this->requestCompose($condition_value);
				$condition_dic["condition"] = $condition;
				if($type == "or"){
					$functionDic["search_content"][] = $condition_dic;
					$functionDic["search"]["content"][] = $condition_dic;
				}else{
					$functionDic["search_content"] = $condition_dic;
					$functionDic["search"]["content"] = $condition_dic;
				}
			}elseif($condition_target == "url"){
				$condition_dic = array();
				$condition_dic["value"] = $this->requestCompose($condition_value);
				$condition_dic["condition"] = $condition;
				if($type == "or"){
					$functionDic["search_url"][] = $condition_dic;
					$functionDic["search"]["url"][] = $condition_dic;
				}else{
					$functionDic["search_url"] = $condition_dic;
					$functionDic["search"]["url"] = $condition_dic;
				}
			}else if($condition_target == "folder"){
				$condition_dic = array();
				$condition_dic["value"] = $this->requestCompose($condition_value);
				$condition_dic["condition"] = $condition;
				if($type == "or"){
					$functionDic["search_folder"][] = $condition_dic;
					$functionDic["search"]["folder"][] = $condition_dic;
				}else{
					$functionDic["search_folder"] = $condition_dic;
					$functionDic["search"]["folder"] = $condition_dic;
				}
			}else if($condition_target == "contentclass"){
				$condition_dic = array();
				$condition_dic["value"] = $this->requestCompose($condition_value);
				$condition_dic["condition"] = $condition;
				if($type == "or"){
					$functionDic["search_contentclass"][] = $condition_dic;
					$functionDic["search"]["contentclass"][] = $condition_dic;
				}else{
					$functionDic["search_contentclass"] = $condition_dic;
					$functionDic["search"]["contentclass"] = $condition_dic;
				}
			}else if($condition_target == "contenttype"){
				$condition_dic = array();
				$condition_dic["value"] = $this->requestCompose($condition_value);
				$condition_dic["condition"] = $condition;
				if($type == "or"){
					$functionDic["search_contenttype"][] = $condition_dic;
					$functionDic["search"]["contenttype"][] = $condition_dic;
				}else{
					$functionDic["search_contenttype"] = $condition_dic;
					$functionDic["search"]["contenttype"] = $condition_dic;
				}
			}else if($condition_target == "orderby"){
				$condition_dic = array();
				$condition_dic["value"] = $this->requestCompose($condition_value);
				$condition_dic["condition"] = $condition;
				$functionDic["search_orderby"][] = $condition_dic;
			}else if($condition_target == "ordertype"){
				$condition_dic = array();
				$condition_dic["value"] = $this->requestCompose($condition_value);
				$condition_dic["condition"] = $condition;
				$functionDic["search_ordertype"][] = $condition_dic;
			}else if($condition_target == "start"){
				$condition_dic = array();
				$condition_dic["value"] = $this->requestCompose($condition_value);
				$condition_dic["condition"] = $condition;
				$functionDic["search_start"] = $condition_dic;
			}else if($condition_target == "limit"){
				$condition_dic = array();
				$condition_dic["value"] = $this->requestCompose($condition_value);
				$condition_dic["condition"] = $condition;
				$functionDic["search_limit"] = $condition_dic;
			}else if($condition_target == "parts"){
				$condition_dic = array();
				$condition_dic["value"] = $this->requestCompose($condition_value);
				$condition_dic["condition"] = $condition;
				$functionDic["parts"] = $condition_dic;
			}else if($condition_target == "column"){
				$condition_dic = array();
				$condition_dic["value"] = $this->requestCompose($condition_value);
				$condition_dic["condition"] = $condition;
				if($type == "or"){
					$functionDic["column"][] = $condition_dic;
				}else{
					$functionDic["column"] = $condition_dic;
				}
			}else{
				$condition_dic = array();
				$condition_dic["value"] = $this->requestCompose($condition_value);
				$condition_dic["condition"] = $condition;
				if($type == "or"){
					$functionDic["search_ext_".$condition_target][] = $condition_dic;
					$functionDic["search"][$condition_target][] = $condition_dic;
				}else{
					$functionDic["search_ext_".$condition_target] = $condition_dic;
					$functionDic["search"][$condition_target] = $condition_dic;
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

	function replaceCompose($content,$replace,$mark_flg = true){

		$replaceArr = array();
		foreach($replace as $key => $value){
			if($mark_flg){
				$replaceArr[Config::REPLACE_MARK_START.$key.Config::REPLACE_MARK_END] = $value;
				$replaceArr[Config::REPLACE_MARK_START.$key.".length".Config::REPLACE_MARK_END] = strlen($value);
			}else{
				$replaceArr[$key] = $value;
				$replaceArr[$key.".length"] = strlen($value);
			}
		}
		$content = str_replace(array_keys($replaceArr), array_values($replaceArr), $content);
		return $content;
	}

	function cleanVar($content){
		$pattern = '/'.Config::REPLACE_MARK_START.'[a-z0-9_-]+'.Config::REPLACE_MARK_END.'/i';
	}

	function addinfoReplaceCompose($content,$addinfo_data,$mark_flg = true){

		$replaceArr = array();
		foreach($addinfo_data as $key => $value){
			if($mark_flg){
				$replaceArr[Config::REPLACE_MARK_START.$value["name"].Config::REPLACE_MARK_END] = $value["addinfo_content"];
			}else{
				$replaceArr[$value["name"]] = $value["addinfo_content"];
			}
		}

		$content = str_replace(array_keys($replaceArr), array_values($replaceArr), $content);
		return $content;
	}

	function listFunctionCompose($content,$addinfo_data,$replace){
		//リスト関数情報を取得
		$pattern = '/'.Config::REPLACE_MARK_START.'((list|select|count):([a-z0-9_-]+):([^'.Config::REPLACE_MARK_START.Config::REPLACE_MARK_END.']+))'.Config::REPLACE_MARK_END.'/i';
		$listFunctionDic = $this->getListFunctionDic($content);
		$content_old = $content;
		try{
			$replace_base = $replace;
			foreach($listFunctionDic as $key => $value){
				//Debug::arrayCheck($value);
				$content_old2 = $content;
				try{
					if($value["type"] == "list"){
						//一覧情報取得
						if(!isset($value["database"]["value"]) || $value["database"]["value"] == "content"){
							$contentList = $this->contentTrn->getListFunctionContent($value);
							$targetDatabase = "content";
						}else{
							$contentList = $this->contentTrn->getListFunctionDatabase($value);
							$targetDatabase = $value["database"]["value"];
						}

						//部品コンテンツ取得
						$listIncludeType = "";
						if(isset($value["parts"]["value"]) && is_numeric($value["parts"]["value"])){
							$entryContent = $this->contentTrn->getElementById($value["parts"]["value"],$this->device);
							$listIncludeType = "parts";
						}elseif(isset($value["column"]["value"])){
							$listIncludeType = "column";
						}else{
							continue;
						}

						//SELECTした結果回数分ループ
						$listContent = "";
						for($i=0;$i<count($contentList);$i++){
							$replace = $replace_base;
							$replace = array_merge($replace,$contentList[$i]);
							if($targetDatabase == "content"){
								$replace["id"] = $contentList[$i]["content_id"];
							}
							$replace["list_count"] = count($contentList);
							$replace["list_index"] = $i;
							$replace["list_num"] = $i+1;
							if($targetDatabase == "content"){
								//追加情報の取得
								$addinfoList = $this->contentTrn->getAddInfoListByContentId($contentList[$i]["content_id"]);
								foreach($addinfoList as $addinfoContent){
									//if(!isset($replace[$addinfoContent["name"]])){
										if($addinfoContent["addinfo_content"] != null && $addinfoContent["addinfo_content"] != ""){
											$replace[$addinfoContent["name"]] = $addinfoContent["addinfo_content"];
										}else if($addinfoContent["optionvalue"] != null && $addinfoContent["optionvalue"] != ""){
											$replace[$addinfoContent["name"]] = $addinfoContent["optionvalue"];
										}else{
											$replace[$addinfoContent["name"]] = "";
										}
									//}
								}
							}
							//リストコンテンツ1件の取得
							$listContentOne = "";

							if($listIncludeType == "parts"){
								//出力整形合成#1
								$listContentOne = $this->ifFunctionCompose($this->listFunctionCompose($this->utilCompose($this->replaceCompose($entryContent["content"], $replace),$replace,true),$addinfo_data,$replace) ,array(),$replace);

							}else{
								if(isset($replace[$value["column"]["value"]])){
									$listContentOne = $replace[$value["column"]["value"]];
								}
							}
							//入れ子のリスト処理
							if(preg_match($pattern,$listContentOne)){
								$listContentOne = $this->listFunctionCompose($listContentOne, $addinfo_data, $replace);
							}
							$listContent.= $listContentOne;
						}
						$content = str_replace($key, $listContent, $content);
					}elseif($value["type"] == "count"){
						//一覧カウント取得
						if(!isset($value["database"]["value"]) || $value["database"]["value"] == "content"){
							$contentCnt = $this->contentTrn->getListFunctionContent($value,true);
						}else{
							$contentCnt = $this->contentTrn->getListFunctionDatabase($value,true);
						}
						$content = str_replace($key, $contentCnt, $content);
					}
				}catch (Exception $e){
					//echo $e->getMessage();
					$content = $content_old2;
				}
			}
		}catch (Exception $e){
			//echo $e->getMessage();
			$content = $content_old;
		}
		return $content;
	}

	function ifFunctionCompose($content,$addinfo_data,$replace){
		// 必ず半角英数字の場合は、strpos,strlenを使用
		// マルチバイトの可能性がある場合は、mb_strpos,mb_strlenを使用
		// 正規表現を使用せずやや長い処理記載になるが性能対策のため。
		if($content == ""){
			return $content;
		}

		$ifResutContent = "";

		// 処理用変数
		$condition_dic = array();			// 条件格納用
		$pos_function_start = 0;			// IF関数の開始位置
		$pos_function_if_start_end = 0;		// IF関数の開始記載終了位置
		$pos_function_end = 0;				// IF関数の終了位置
		$pos_function_if_end_start = 0;		// IF関数の終了記載開始位置
		$if_function_name = "";				// IF関数の名称
		$if_function_first_condition = "";	// IF関数の最初の検索条件

		$before_if_str = "";				// IF関数が開始される前の文字列
		$in_if_str = "";					// 対象のIF関数処理部全体の文字列
		$after_if_str = "";					// IF関数が開始された後の文字列

		// 現在地格納用変数
		$pos_total = 0;

		// IF開始関数開始文字検索
		// {{{if:
		{
			$pattern1 = Config::REPLACE_MARK_START."if:";
			$pos_function_start = mb_strpos($content,$pattern1);
			if($pos_function_start === false){
				// IF関数がない場合
				return $content;
			}
			// 終了位置保存
			$pos_total = $pos_function_start + strlen($pattern1);
		}
		// IF関数定義名処理
		// name:
		{
			$pos1 = mb_strpos($content,":",$pos_total);
			if($pos1 === false){
				// 関数名がない場合
				return $content;
			}
			$pos1_end = $pos1 + strlen(":");
			$if_function_name = mb_substr($content, $pos_total,$pos1 - $pos_total);
			// 終了位置保存
			$pos_total = $pos1 + strlen(":");
		}
		// IF開始関数終了記載有無検索
		// }}}
		{
			$pos1 = mb_strpos($content,Config::REPLACE_MARK_END,$pos_total);
			if($pos1 === false){
				// IF開始関数終了文字がない場合
				return $content;
			}
			// IF関数の最初の検索条件を保存
			$if_function_first_condition = mb_substr($content, $pos_total,$pos1 - $pos_total);

			// IF開始関数終了位置を保存
			$pos_function_if_start_end = $pos1 + mb_strlen(Config::REPLACE_MARK_END);
			// 終了位置保存
			$pos_total = $pos_function_if_start_end;
		}
		// IF終了関数検索(関数名指定検索)
		{
			$pattern1 = Config::REPLACE_MARK_START."endif:".$if_function_name.Config::REPLACE_MARK_END;
			$pos1 = mb_strpos($content,$pattern1,$pos_total);
			if($pos1 === false){
				// IF終了関数文字がない場合
				return $content;
			}
			// IF終了関数開始位置を保存
			$pos_function_if_end_start = $pos1;
			// 終了位置保存
			$pos_function_end = $pos1 + strlen($pattern1);
			$pos_total = $pos_function_end;
		}
		// 文字列をIF処理前・処理中・処理後に3分割
		{
			$before_if_str = mb_substr($content, 0,$pos_function_start);
			$in_if_str = mb_substr($content, $pos_function_start,$pos_function_end - $pos_function_start);
			$after_if_str = mb_substr($content, $pos_function_end);

			// IF内部処理のため、IF開始関数終了位置・IF終了関数開始位置を減算
			$pos_function_if_start_end -= $pos_function_start;
			$pos_function_if_end_start -= $pos_function_start;
		}

		// 条件辞書初期化
		$condition_dic["conditions"] = array();
		$condition_dic["else_content"] = "";
		{
			$is_next = true;
			$next_condition = $if_function_first_condition;	// 最初の検索条件
			$pos = $pos_function_if_start_end;				// 最初の検索条件の終了位置
			while($is_next){
				$condition = array();
				$condition["condition"] = $next_condition;					// 検索条件格納
				$condition["name"] = $if_function_name;

				// コンテンツの終了位置を検索
				$pattern1 = Config::REPLACE_MARK_START."elseif:".$if_function_name.":";					// elseif検索
				$pattern2 = Config::REPLACE_MARK_START."else:".$if_function_name.Config::REPLACE_MARK_END;		// else検索
				$pattern3 = Config::REPLACE_MARK_START."endif:".$if_function_name.Config::REPLACE_MARK_END;		// endif検索(必ずヒットする)

				// elseif検索
				$pos1 = mb_strpos($in_if_str, $pattern1,$pos);
				if($pos1 !== false){
					// コンテンツを保存
					$condition["content"] = mb_substr($in_if_str, $pos, $pos1 - $pos);
					$condition_dic["conditions"][] = $condition;
					// 次の検索条件を取得
					$pos_next_condition_start = $pos1 + strlen($pattern1);
					$pos_next_condition_end = mb_strpos($in_if_str, Config::REPLACE_MARK_END,$pos_next_condition_start);
					$next_condition = mb_substr($in_if_str, $pos_next_condition_start, $pos_next_condition_end - $pos_next_condition_start);
					$pos = $pos_next_condition_end + strlen(Config::REPLACE_MARK_END);
				}else{
					// else検索
					$pos2 = mb_strpos($in_if_str, $pattern2,$pos);
					if($pos2 !== false){
						// コンテンツを保存
						$condition["content"] = mb_substr($in_if_str, $pos, $pos2 - $pos);
						$condition_dic["conditions"][] = $condition;
						// elseコンテンツを保存
						$pos_else_content_start = $pos2 + strlen($pattern2);
						$pos_else_content_end = mb_strpos($in_if_str, $pattern3,$pos_else_content_start);
						$condition_dic["else_content"] = mb_substr($in_if_str, $pos_else_content_start, $pos_else_content_end - $pos_else_content_start);
						// ループ処理終了
						break;
					}else{
						// endif検索
						$pos3 = mb_strpos($in_if_str, $pattern3,$pos);
						// コンテンツを保存
						$condition["content"] = mb_substr($in_if_str, $pos, $pos3 - $pos);
						$condition_dic["conditions"][] = $condition;
						// ループ処理終了
						break;
					}
				}
			}
		}
		//Debug::arrayCheck($condition_dic);

		$conditions = $condition_dic["conditions"];
		$match_flg = false;
		for($i=0;$i<count($conditions);$i++){
			$condition_one = $conditions[$i];
			$target_condition = $condition_one["condition"];
			$match_flg = true;
			$and_list = explode(":",$target_condition);
			foreach($and_list as $cnt2 => $andvalue){
				$match_flg2 = false;
				$or_list = explode("|",$andvalue);
				foreach($or_list as $k => $v){
					$v = str_replace(array("&lt;","&gt;"), array("<",">"), $v);
					if(preg_match("/([\S\s]*)!=([\S\s]*)/i",$v,$matches) || preg_match("/([\S\s]*)\<\>([\S\s]*)/i",$v,$matches)){
						//ノットイコール処理
						$param1 = $this->deleteCompose($this->requestCompose($this->addinfoReplaceCompose($this->replaceCompose($matches[1], $replace,false),$addinfo_data,false)));
						$param2 = $this->deleteCompose($this->requestCompose($this->addinfoReplaceCompose($this->replaceCompose($matches[2], $replace,false),$addinfo_data,false)));
						if($param1!=$param2){
							$match_flg2 = true;
						}
					}elseif(preg_match("/([\S\s]*)\<=([\S\s]*)/i",$v,$matches)){
						//以上処理
						$param1 = $this->deleteCompose($this->requestCompose($this->addinfoReplaceCompose($this->replaceCompose($matches[1], $replace,false),$addinfo_data,false)));
						$param2 = $this->deleteCompose($this->requestCompose($this->addinfoReplaceCompose($this->replaceCompose($matches[2], $replace,false),$addinfo_data,false)));
						if($param1<=$param2){
							$match_flg2 = true;
						}
					}elseif(preg_match("/([\S\s]*)\>=([\S\s]*)/i",$v,$matches)){
						//以下処理
						$param1 = $this->deleteCompose($this->requestCompose($this->addinfoReplaceCompose($this->replaceCompose($matches[1], $replace,false),$addinfo_data,false)));
						$param2 = $this->deleteCompose($this->requestCompose($this->addinfoReplaceCompose($this->replaceCompose($matches[2], $replace,false),$addinfo_data,false)));
						if($param1>=$param2){
							$match_flg2 = true;
						}
					}elseif(preg_match("/([\S\s]*)\<([\S\s]*)/i",$v,$matches)){
						//大なり小なり
						$param1 = $this->deleteCompose($this->requestCompose($this->addinfoReplaceCompose($this->replaceCompose($matches[1], $replace,false),$addinfo_data,false)));
						$param2 = $this->deleteCompose($this->requestCompose($this->addinfoReplaceCompose($this->replaceCompose($matches[2], $replace,false),$addinfo_data,false)));
						if($param1<$param2){
							$match_flg2 = true;
						}
					}elseif(preg_match("/([\S\s]*)\>([\S\s]*)/i",$v,$matches)){
						//大なり小なり
						$param1 = $this->deleteCompose($this->requestCompose($this->addinfoReplaceCompose($this->replaceCompose($matches[1], $replace,false),$addinfo_data,false)));
						$param2 = $this->deleteCompose($this->requestCompose($this->addinfoReplaceCompose($this->replaceCompose($matches[2], $replace,false),$addinfo_data,false)));
						if($param1>$param2){
							$match_flg2 = true;
						}
					}elseif(preg_match("/([\S\s]*)==([\S\s]*)/i",$v,$matches) || preg_match("/([\S\s]*)=([\S\s]*)/i",$v,$matches)){
						//イコール処理
						$param1 = $this->deleteCompose($this->requestCompose($this->addinfoReplaceCompose($this->replaceCompose($matches[1], $replace,false),$addinfo_data,false)));
						$param2 = $this->deleteCompose($this->requestCompose($this->addinfoReplaceCompose($this->replaceCompose($matches[2], $replace,false),$addinfo_data,false)));
						if($param1==$param2){
							$match_flg2 = true;
						}
					}
				}
				if(!$match_flg2){
					$match_flg = false;
					break;
				}
			}
			if($match_flg == true){
				$ifResutContent = $condition_one["content"];
				break;
			}
		}
		if($match_flg == false && isset($condition_dic["else_content"])){
			$ifResutContent = $condition_dic["else_content"];
		}

		// 再帰呼び出し処理
		$content  = $before_if_str.$ifResutContent.$after_if_str;
		{
			$pattern1 = Config::REPLACE_MARK_START."if:";
			$pos_function_start = mb_strpos($content,$pattern1);
			if($pos_function_start !== false){
				$content = $this->ifFunctionCompose($this->utilCompose($content,$replace,true), $addinfo_data, $replace);
			}
		}

		return $content;
	}

	function requestCompose($content){
		$replaceArr = array();
		$content = str_replace(array_keys($this->requestReplaceArr), array_values($this->requestReplaceArr), $content);
		return $content;
	}

	function utilCompose($content,$replace,$mark = false){
		//replace処理合成
		if($mark){
			$pattern = '/'.Config::REPLACE_MARK_START.'replace\(([^\)]*),([^\)]*),([^\)]*)\)'.Config::REPLACE_MARK_END.'/i';
		}else{
			$pattern = '/replace\(([^\)]*),([^\)]*),([^\)]*)\)/i';
		}
		preg_match_all($pattern, $content , $match);
		if($match){
			for($i=0;$i<count($match[0]);$i++){
				$str = str_replace($match[1][$i], $match[2][$i], $match[3][$i]);
				$content = str_replace($match[0][$i], $str, $content);
			}
		}

		//date処理合成#1
		if($mark){
			$pattern = '/'.Config::REPLACE_MARK_START.'date\(([^\)]*),([^\)]*)\)'.Config::REPLACE_MARK_END.'/i';
		}else{
			$pattern = '/date\(([^\)]*),([^\)]*)\)/i';
		}
		preg_match_all($pattern, $content , $match);
		if($match){
			for($i=0;$i<count($match[0]);$i++){
				if($match[2][$i] != ""){
					$str = date($match[1][$i], $match[2][$i]);
				}else{
					$str = date($match[1][$i], time());
				}
				$content = str_replace($match[0][$i], $str, $content);
			}
		}

		//date処理合成#2
		if($mark){
			$pattern = '/'.Config::REPLACE_MARK_START.'date\(([^\)]*)\)'.Config::REPLACE_MARK_END.'/i';
		}else{
			$pattern = '/date\(([^\)]*)\)/i';
		}
		preg_match_all($pattern, $content , $match);
		if($match){
			for($i=0;$i<count($match[0]);$i++){
				$str = date($match[1][$i], time());
				$content = str_replace($match[0][$i], $str, $content);
			}
		}

		//intval合成処理
		if($mark){
			$pattern = '/'.Config::REPLACE_MARK_START.'intval\(([^\)]*)\)'.Config::REPLACE_MARK_END.'/i';
		}else{
			$pattern = '/intval\(([^\)]*)\)/i';
		}
		preg_match_all($pattern, $content , $match);
		if($match){
			for($i=0;$i<count($match[0]);$i++){
				$str = intval($this->replaceCompose($match[1][$i],$replace,true));
				$content = str_replace($match[0][$i], $str, $content);
			}
		}

		//strlen合成処理
		if($mark){
			$pattern = '/'.Config::REPLACE_MARK_START.'strlen\(([^\)]*)\)'.Config::REPLACE_MARK_END.'/i';
		}else{
			$pattern = '/strlen\(([^\)]*)\)/i';
		}
		preg_match_all($pattern, $content , $match);
		if($match){
			for($i=0;$i<count($match[0]);$i++){
				$str = mb_strlen($this->replaceCompose($match[1][$i],$replace,true));
				$content = str_replace($match[0][$i], $str, $content);
			}
		}

		//nl2br+htmlspecialchars合成処理
		if($mark){
			$pattern = '/'.Config::REPLACE_MARK_START.'nl2br\('.Config::REPLACE_MARK_START.'htmlspecialchars\(([^\)]*)\)'.Config::REPLACE_MARK_END.'\)'.Config::REPLACE_MARK_END.'/i';
		}else{
			$pattern = '/nl2br\(htmlspecialchars\(([^\)]*)\)\)/i';
		}
		preg_match_all($pattern, $content , $match);
		if($match){
			for($i=0;$i<count($match[0]);$i++){
				$str = nl2br(htmlspecialchars($this->requestCompose($this->replaceCompose($match[1][$i],$replace,true))));
				$content = str_replace($match[0][$i], $str, $content);
			}
		}

		//htmlspecialchars合成処理
		if($mark){
			$pattern = '/'.Config::REPLACE_MARK_START.'htmlspecialchars\(([^\)]*)\)'.Config::REPLACE_MARK_END.'/i';
		}else{
			$pattern = '/htmlspecialchars\(([^\)]*)\)/i';
		}
		preg_match_all($pattern, $content , $match);
		if($match){
			for($i=0;$i<count($match[0]);$i++){
				$str = htmlspecialchars($this->requestCompose($this->replaceCompose($match[1][$i],$replace,true)));
				$content = str_replace($match[0][$i], $str, $content);
			}
		}

		//nl2br合成処理
		if($mark){
			$pattern = '/'.Config::REPLACE_MARK_START.'nl2br\(([^\)]*)\)'.Config::REPLACE_MARK_END.'/i';
		}else{
			$pattern = '/nl2br\(([^\)]*)\)/i';
		}
		preg_match_all($pattern, $content , $match);
		if($match){
			for($i=0;$i<count($match[0]);$i++){
				$str = nl2br($this->requestCompose($this->replaceCompose($match[1][$i],$replace,true)));
				$content = str_replace($match[0][$i], $str, $content);
			}
		}
		return $content;
	}

	function getExtensionDic($content){
		$functionDic = array();
		$pattern = '/'.Config::REPLACE_MARK_START.'(ext:([a-z0-9_-]+)\(([^\)]*)\))'.Config::REPLACE_MARK_END.'/i';
		preg_match_all($pattern, $content , $match);
		if($match){
			for($i=0;$i<count($match[0]);$i++){
				$functionDic[$match[0][$i]]["function_full"] = $match[0][$i];
				$functionDic[$match[0][$i]]["function_text"] = $match[1][$i];
				$functionDic[$match[0][$i]]["type"] = "extension";
				$functionDic[$match[0][$i]]["name"] = $match[2][$i];
				$functionDic[$match[0][$i]]["param"] = $match[3][$i];
			}
		}
		return $functionDic;
	}

	function extensionCompose($pageConfig,$page_content,$session){
		//拡張子情報一覧を取得
		$extensionDic = $this->getExtensionDic($page_content);
		foreach($extensionDic as $key => $value){
			$data = array();
			$data["session"] = $_SESSION;
			$data["request"] = $_REQUEST;
			$data["post"] = $_POST;
			$data["get"] = $_GET;
			$data["files"] = $_FILES;
			//ファイルを一時領域に退避
			/*
			if($_FILES){
				foreach($_FILES as $fkey => $fvalue){
					if (is_uploaded_file($fvalue["tmp_name"])) {
						if (!move_uploaded_file($fvalue["tmp_name"], dirname(__FILE__)."/../tmp/" . basename($fvalue["tmp_name"]))) {
							unset($data["files"][$fkey]);
						}else{
							$data["files"][$fkey]["tmp_name"] = "../tmp/".basename($fvalue["tmp_name"]);
						}
					}
				}
			}
			*/

			$data["param"] = $this->requestCompose($value["param"]);
			$_REQUEST["param"] = $data["param"];
			$_POST["param"] = $data["param"];

			$data["meta"] = array();
			$data["meta"]["url"] = $data["get"]["url"];
			$data["meta"]["content_id"] = $pageConfig["content_id"];

			unset($data["request"]["url"]);
			unset($data["get"]["url"]);

			if($session && isset($session->user["user_id"])){
				$data["user_id"] = $session->user["user_id"];
				$_REQUEST["user_id"] = $data["user_id"];
				$_POST["user_id"] = $data["user_id"];
			}

			if(Config::EXTMODULE_METHOD == "file_get_contents"){
				$data = http_build_query($data, "", "&");

				$header = array(
						"Content-Type: application/x-www-form-urlencoded",
						"Content-Length: ".strlen($data)
				);

				if(Config::BASIC_AUTHORIZATION_USER && Config::BASIC_AUTHORIZATION_PASSWORD){
					$header[] = "Authorization: Basic ".base64_encode(Config::BASIC_AUTHORIZATION_USER.":".Config::BASIC_AUTHORIZATION_PASSWORD);
				}

				$context = array(
						"http" => array(
								"method"  => "POST",
								"header"  => implode("\r\n", $header),
								"content" => $data
						)
				);
				$url = Config::LOCAL_HOST_ADDRESS.Config::BASE_DIR_PATH.Config::ADMIN_DIR_PATH.Config::EXT_DIR_PATH.$value["name"].".php";

				//コンテンツ置換
				$content = @file_get_contents($url, false, stream_context_create($context));
				$page_content = str_replace($key, $content, $page_content);
			}else{
				//コンテンツ置換
				$content = $this->get_include_contents(dirname(__FILE__)."/../".Config::EXT_DIR_PATH.$value["name"].".php");
				$page_content = str_replace($key, $content, $page_content);
			}
		}
		return $page_content;
	}

	function get_include_contents($filename) {
		if (is_file($filename)) {
			ob_start();
			include $filename;
			return ob_get_clean();
		}
		return false;
	}

	function deleteCompose($content){
		//$pattern = '/'.Config::REPLACE_MARK_START.'([a-z0-9=-_<>!\|\/\.:]+)'.Config::REPLACE_MARK_END.'/i';
		$pattern = '/'.Config::REPLACE_MARK_START.'([^'.Config::REPLACE_MARK_START.'|'.Config::REPLACE_MARK_END.']+)'.Config::REPLACE_MARK_END.'/i';
		preg_match_all($pattern, $content , $match);
		$replace = array();
		if($match){
			for($i=0;$i<count($match[0]);$i++){
				$replace[$match[0][$i]] = "";
			}
			$content = str_replace(array_keys($replace), array_values($replace), $content);
		}
		//$pattern = '/'.Config::REQUEST_MARK_START.'([a-z0-9=-_<>!\|\/\.:]+)'.Config::REQUEST_MARK_END.'/i';
		$pattern = '/'.Config::REQUEST_MARK_START.'([^'.Config::REQUEST_MARK_START.'|'.Config::REQUEST_MARK_END.']+)'.Config::REQUEST_MARK_END.'/i';
		preg_match_all($pattern, $content , $match);
		$replace = array();
		if($match){
			for($i=0;$i<count($match[0]);$i++){
				$replace[$match[0][$i]] = "";
			}
			$content = str_replace(array_keys($replace), array_values($replace), $content);
		}
		return $content;
	}

	function elementCompose($key_column,$pageConfig, $replace){
		//ファンクション-要素一覧を取得
		$elementFunctionDic = $this->getElementFunctionDic($pageConfig[$key_column],$pageConfig["element_index"]);

		//要素組み込みを実施
		foreach($elementFunctionDic as $key => $value){
			if(in_array($value["id"], $pageConfig["_root"])){
				continue;
			}elseif($value["id"]){

				//要素データを取得
				$elementData = $this->contentTrn->getElementById($value["id"],$this->device);
				if(!$elementData){
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
					$configNext = $this->elementCompose("content",$configNext, $replace);

					//コンテンツ置換
					$pageConfig[$key_column] = str_replace($key, $configNext["content"], $pageConfig[$key_column]);
				}elseif($value["target"] == "title"){
					//タイトル置換
					$pageConfig[$key_column] = str_replace($key, $elementData["title"], $pageConfig[$key_column]);
				}
			}else{
				$pageConfig[$key_column] = str_replace($key, "", $pageConfig[$key_column]);
			}
		}

		$indexFunctionDic = $this->getIncludeFunctionDic($pageConfig[$key_column]);

		//インクルード組み込みを実施
		foreach($indexFunctionDic as $key => $value){
			if(in_array($value["id"], $pageConfig["_root"])){
				continue;
			}elseif($value["id"]){
				//要素データを取得
				$elementData = $this->contentTrn->getElementById($value["id"],$this->device);
				//データがない場合は終了
				if(!$elementData){
					$pageConfig[$key_column] = str_replace($key, "", $pageConfig[$key_column]);
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
					$configNext = $this->elementCompose("content",$configNext, $replace);

					//コンテンツ置換
					$pageConfig[$key_column] = str_replace($key, $configNext["content"], $pageConfig[$key_column]);
				}elseif($value["target"] == "title"){
					//タイトル置換
					$pageConfig[$key_column] = str_replace($key, $elementData["title"], $pageConfig[$key_column]);
				}
			}
		}

		// 追加情報組み込み
		$pageConfig = $this->addinfoCompose($key_column, $pageConfig, $replace);
		return $pageConfig;
	}

	function addinfoCompose($key_column,$pageConfig, $replace){
		$addinfoFunctionDic = $this->getAddInfoFunctionDic($pageConfig[$key_column],$pageConfig["addinfo_index"]);
		$addinfoArr = $this->getAddInfoArr($addinfoFunctionDic);

		//追加情報組み込みを実施
		$pageConfig["addinfo_data"] = array();
		if(count($addinfoArr) > 0){
			//追加情報取得
			$addinfoList = $this->contentTrn->getAddInfoList($addinfoArr);
			$replace_arr_withmark = array();
			$replace_arr = array();
			foreach($addinfoList as $addinfo){
				$index = Config::REPLACE_MARK_START.$addinfo["name"].Config::REPLACE_MARK_END;
				if(isset($addinfoFunctionDic[$index])){
					$replace_arr_withmark[Config::REPLACE_MARK_START.$addinfo["name"].Config::REPLACE_MARK_END] = $addinfo["addinfo_content"];
					$replace_arr_withmark[Config::REPLACE_MARK_START."addinfo_".$addinfo["name"].Config::REPLACE_MARK_END] = $addinfo["addinfo_content"];
					$replace_arr[$addinfo["name"]] = $addinfo["addinfo_content"];
					$replace_arr["addinfo_".$addinfo["name"]] = $addinfo["addinfo_content"];
				}
			}
			//追加情報置換
			$pageConfig["addinfo_data"] = $addinfoList;

			//IF合成
			//$pageConfig["content"] = $this->ifFunctionCompose($pageConfig["content"], $pageConfig["addinfo_data"], $replace);

			$pageConfig[$key_column] = $this->utilCompose($pageConfig[$key_column], $replace_arr,true);

			$pageConfig[$key_column] = str_replace(array_keys($replace_arr_withmark), array_values($replace_arr_withmark), $pageConfig[$key_column]);
		}

		return $pageConfig;
	}

	function getCssListFromRefList($refList){
		$result = array();
		for($i=0;$i<count($refList);$i++){
			if($refList[$i]["contentclass"] == "stylesheet"){
				$result[] = $refList[$i];
			}
		}
		return $result;
	}

	function getScriptListFromRefList($refList){
		$result = array();
		for($i=0;$i<count($refList);$i++){
			if($refList[$i]["contentclass"] == "script"){
				$result[] = $refList[$i];
			}
		}
		return $result;
	}

	function headReplaceMarkReplace($head_code){
		$replace_arr = array();
		$replace_arr[Config::REPLACE_MARK_START."head_title".Config::REPLACE_MARK_END] = Config::HEAD_CODE_REPLACE_MARK_START."head_title".Config::HEAD_CODE_REPLACE_MARK_END;
		$replace_arr[Config::REPLACE_MARK_START."head_description".Config::REPLACE_MARK_END] = Config::HEAD_CODE_REPLACE_MARK_START."head_description".Config::HEAD_CODE_REPLACE_MARK_END;
		$replace_arr[Config::REPLACE_MARK_START."head_keywords".Config::REPLACE_MARK_END] = Config::HEAD_CODE_REPLACE_MARK_START."head_keywords".Config::HEAD_CODE_REPLACE_MARK_END;
		$replace_arr[Config::REPLACE_MARK_START."head_author".Config::REPLACE_MARK_END] = Config::HEAD_CODE_REPLACE_MARK_START."head_author".Config::HEAD_CODE_REPLACE_MARK_END;
		$replace_arr[Config::REPLACE_MARK_START."head_stylesheet".Config::REPLACE_MARK_END] = Config::HEAD_CODE_REPLACE_MARK_START."head_stylesheet".Config::HEAD_CODE_REPLACE_MARK_END;
		$replace_arr[Config::REPLACE_MARK_START."head_script".Config::REPLACE_MARK_END] = Config::HEAD_CODE_REPLACE_MARK_START."head_script".Config::HEAD_CODE_REPLACE_MARK_END;
		return str_replace(array_keys($replace_arr), array_values($replace_arr), $head_code);
	}

	function buildHTMLPage($pageConfig){
		$str = "";
		include 'page_template.php';
		return $str;
	}
}
?>