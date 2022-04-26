<?php
require_once("include.php");

require_once("../DataAccess/Content.php");

//インデックスページ定義
$indexPageName = "index.html";

//実行ページのコンテンツIDとURLを取得
$content_id = $_META["content_id"];
$url = $_META["url"];

//対象ページのコンテンツデータを取得
$Content = new Content(Content::TABLE_PUBLIC);
$contentData = $Content->getContentDataByContentId($content_id);
if($contentData){

	$url_params1 = explode("?",$url);
	$base_url = $url_params1[0];
	$url_params2 = explode("/",$base_url);

	$BreadCrumbsCreater = new BreadCrumbsCreater();
	$BreadCrumbsCreater->setIndexPageName($indexPageName);
	$directoryList = $BreadCrumbsCreater->getBreadCrumbsData($url_params2,true);

	//以下パンくず出力処理
	//必要に応じて修正してください
	$output = "";
	for($i=0;$i<count($directoryList);$i++){
		$data = $directoryList[$i];
		if($data["content_id"]){
			if($output){
				$output.= ">";
			}

			$output.= "<a href=\"/".$data["url"]."\">".$data["title"]."</a>";
		}
	}
	echo $output;
	//以下パンくず出力処理 終了
}

class BreadCrumbsCreater{

	var $indexPageName = "";

	function setIndexPageName($name){
		$this->indexPageName = $name;
	}

	function getBreadCrumbsData($url_params,$top_flg = false){
		$data = array();
		//ファイル名を取得
		$filename = $url_params[count($url_params) - 1];
		//URLを取得
		$url = implode("/", $url_params);
		if(!$top_flg && $filename != $this->indexPageName){
			$url.= "/".$this->indexPageName;
		}
		//コンテンツデータを取得
		$Content = new Content(Content::TABLE_PUBLIC);
		$contentData = $Content->getContentDataByUrl($url);

		//ディレクトリトップ判定
		if($filename ==  $this->indexPageName){
			//ディレクトリトップの場合
			$data["directory_top"] = true;
		}else{
			//ディレクトリトップ以外の場合
			$data["directory_top"] = false;
		}
		//IDを取得
		$data["content_id"] = $contentData["content_id"];
		//タイトルを取得
		$data["title"] = $contentData["title"];
		//URLを取得
		$data["url"] = $url;

		if($data["directory_top"]){
			//パラメータから1件除去
			unset($url_params[count($url_params) - 1]);
			if(count($url_params) > 0){
				$url_params[count($url_params) - 1] = $this->indexPageName;
			}
		}else{
			//ディレクトリトップに補正
			$url_params[count($url_params) - 1] = $this->indexPageName;
		}

		if(count($url_params) > 0 || $filename != $this->indexPageName){
			//上位階層がある場合
			if($filename == $this->indexPageName){
				$upper_list = $this->getBreadCrumbsData($url_params,true);
			}else{
				$upper_list = $this->getBreadCrumbsData($url_params);
			}
		}else{
			$upper_list = array();
		}

		$upper_list[] = $data;

		return $upper_list;
	}
}
?>