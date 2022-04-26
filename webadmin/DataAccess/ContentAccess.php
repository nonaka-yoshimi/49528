<?php
/*
 説明：コンテンツアクセスクラス
作成日：2013/12/1 TS谷
*/
require_once(dirname(__FILE__).'/../Framework/DataAccessBase.php');

/**
 * コンテンツアクセスクラス
 */
class ContentAccess extends DataAccessBase
{
	/**
	 * テーブル一覧
	 */
	var $tables = array();

	/**
	 * 公開コンテンツアクセスクラスコンストラクタ
	 */
	function __construct($table = "content_public"){
		parent::__construct();

		//接続先のテーブル名を設定してください
		$this->setTableName($table);

		//接続先テーブルの主キーを設定してください（複数設定可）
		$this->setPrimaryKey("content_id");
	}

	function setTables(){
		if(!$this->tables){
			$results = $this->query("SHOW TABLES");
			for($i=0;$i<count($results);$i++){
				foreach($results[$i] as $value){
					$this->tables[] = $value;
					break;
				}
			}
		}
	}

	function getContentByUrl($url,$domain,$device = ""){

		$now_timestamp = time();

		$cont_tbl = $this->tablename;

		$sql = "SELECT ";
		$sql.= 		"cont.content_id,";
		$sql.= 		"cont.title,";
		$sql.= 		"cont.content,";
		$sql.= 		"cont.keywords,";
		$sql.= 		"cont.description,";
		$sql.= 		"cont.author,";
		$sql.= 		"cont.doctype,";
		$sql.= 		"cont.html_attr,";
		$sql.= 		"cont.head_attr,";
		$sql.= 		"cont.head_code,";
		$sql.= 		"cont.body_attr,";
		$sql.= 		"cont.title_prefix,";
		$sql.= 		"cont.title_suffix,";
		$sql.= 		"cont.template_id,";
		$sql.= 		"cont.stylesheet_index,";
		$sql.= 		"cont.script_index,";
		$sql.= 		"cont.addinfo_index,";
		$sql.= 		"cont.element_index,";
		$sql.= 		"folder.title_prefix as folder_title_prefix,";
		$sql.= 		"folder.title_suffix as folder_title_suffix,";
		$sql.= 		"folder.template_id as folder_template_id,";
		$sql.= 		"domain.domain as domain,";
		$sql.= 		"domain.domain_name as domain_name,";
		$sql.= 		"domain.base_dir_path as base_dir_path,";
		$sql.= 		"domain.default_doctype as domain_default_doctype, ";
		$sql.= 		"domain.default_title_prefix as domain_default_title_prefix, ";
		$sql.= 		"domain.default_title_suffix as domain_default_title_suffix ";
		$sql.= "FROM ".$cont_tbl." cont ";
		$sql.= 		"INNER JOIN folder ON cont.folder_id = folder.folder_id ";
		$sql.= 			"AND (folder.start_time <= ? OR folder.start_time IS NULL) ";
		$param[] = $now_timestamp;
		$sql.= 			"AND (folder.end_time > ? OR folder.end_time IS NULL) ";
		$param[] = $now_timestamp;
		$sql.= 			"AND folder.active_flg = '1' ";
		$sql.= 		"INNER JOIN domain ON folder.domain_id = domain.domain_id ";
		$sql.= 			"AND domain.active_flg = '1' ";
		$sql.= "WHERE cont.url = ? ";
		$param[] = $url;
		$sql.= "AND domain.domain = ? ";
		$param[] = $domain;
		$sql.= "AND (cont.device = ? OR cont.device = '' ) ";
		$param[] = $device;
		$sql.= "AND cont.contentclass = 'page' ";
		$sql.= "AND cont.active_flg = '1' ";
		if($cont_tbl == "content_public"){
			$sql.= "AND (cont.schedule_publish <= ? OR cont.schedule_publish IS NULL) ";
			$param[] = $now_timestamp;
			$sql.= "AND (cont.schedule_unpublish > ? OR cont.schedule_unpublish IS NULL) ";
			$param[] = $now_timestamp;
		}
		$sql.= "ORDER BY cont.device DESC ";
		$sql.= "LIMIT 1 ";
		$result = $this->query($sql,$param,DB::FETCH);

		if(!$result){
			return null;
		}

		//空テンプレート設定
		$result["template_content"] = "";
		$result["template_keywords"] = "";
		$result["template_description"] = "";
		$result["template_author"] = "";
		$result["template_doctype"] = "";
		$result["template_html_attr"] = "";
		$result["template_head_attr"] = "";
		$result["template_head_code"] = "";
		$result["template_body_attr"] = "";
		$result["parent_template_id"] = "";
		$result["template_stylesheet_index"] = "";
		$result["template_script_index"] = "";
		$result["template_addinfo_index"] = "";
		$result["template_element_index"] = "";
		$result["template_device"] = "";
		$result["template_device_index"] = "";

		if($result["template_id"]){
			//コンテンツテンプレートを参照
			$templateData = $this->getTemplateById($result["template_id"],$device);
			if($templateData){
				$result = array_merge($result,$templateData);
			}
		}elseif($result["folder_template_id"]){
			//フォルダテンプレートを参照
			$templateData = $this->getTemplateById($result["folder_template_id"],$device);
			if($templateData){
				$result = array_merge($result,$templateData);
			}
		}

		return $result;
	}

	var $getTempateByIdLoopCheck = array();
	function getTemplateById($content_id,$device = ""){
		$now_timestamp = time();

		$cont_tbl = $this->tablename;

		$sql = "SELECT ";
		$sql.= 		"template.content as template_content,";
		$sql.= 		"template.keywords as template_keywords,";
		$sql.= 		"template.description as template_description,";
		$sql.= 		"template.author as template_author,";
		$sql.= 		"template.doctype as template_doctype,";
		$sql.= 		"template.html_attr as template_html_attr,";
		$sql.= 		"template.head_attr as template_head_attr,";
		$sql.= 		"template.head_code as template_head_code,";
		$sql.= 		"template.body_attr as template_body_attr,";
		$sql.= 		"template.content_id as template_id,";
		$sql.= 		"template.template_id as parent_template_id,";
		$sql.= 		"template.stylesheet_index as template_stylesheet_index,";
		$sql.= 		"template.script_index as template_script_index,";
		$sql.= 		"template.addinfo_index as template_addinfo_index,";
		$sql.= 		"template.element_index as template_element_index,";
		$sql.= 		"template.device as template_device, ";
		$sql.= 		"template.device_index as template_device_index ";
		$sql.= "FROM ".$cont_tbl." template ";
		$sql.= "WHERE template.content_id = ? ";
		$param[] = $content_id;
		$sql.= 		"AND template.contentclass = 'template' ";
		$sql.= 		"AND template.active_flg = '1' ";
		if($cont_tbl == "content_public"){
			$sql.= "AND (template.schedule_publish <= ? OR template.schedule_publish IS NULL) ";
			$param[] = $now_timestamp;
			$sql.= "AND (template.schedule_unpublish > ? OR template.schedule_unpublish IS NULL) ";
			$param[] = $now_timestamp;
		}
		$result = $this->query($sql,$param,DB::FETCH);
		if(!$result){
			return null;
		}

		//別デバイスコンテンツが存在する場合には結果セットを再取得する
		if($result["template_device"] != $device && $result["template_device_index"]){
			$device_index = explode(",",$result["template_device_index"]);
			for($i=0;$i<count($device_index);$i++){
				$device_dic = explode("=",$device_index[$i]);
				if($device_dic[0] && $device_dic[0] == $device){
					if(in_array($device_dic[1],$this->getTempateByIdLoopCheck)){
						break;
					}else{
						$this->getTempateByIdLoopCheck[] = $device_dic[1];
						$result = $this->getTempateById($device_dic[1],$device);
						break;
					}
				}
			}
		}

		return $result;
	}

	function getStylesheetByUrl($url,$domain,$device = ""){
		$now_timestamp = time();

		$cont_tbl = $this->tablename;

		$sql = "SELECT ";
		$sql.= 		"cont.content_id,";
		$sql.= 		"cont.content,";
		$sql.= 		"cont.addinfo_index,";
		$sql.= 		"domain.base_dir_path as base_dir_path ";
		$sql.= "FROM ".$cont_tbl." cont ";
		$sql.= 		"INNER JOIN folder ON cont.folder_id = folder.folder_id ";
		$sql.= 			"AND (folder.start_time <= ? OR folder.start_time IS NULL) ";
		$param[] = $now_timestamp;
		$sql.= 			"AND (folder.end_time > ? OR folder.end_time IS NULL) ";
		$param[] = $now_timestamp;
		$sql.= 			"AND folder.active_flg = '1' ";
		$sql.= 		"INNER JOIN domain ON folder.domain_id = domain.domain_id ";
		$sql.= 			"AND domain.active_flg = '1' ";
		$sql.= "WHERE cont.url = ? ";
		$param[] = $url;
		$sql.= "AND domain.domain = ? ";
		$param[] = $domain;
		$sql.= "AND (cont.device = ? OR cont.device = '') ";
		$param[] = $device;
		$sql.= "AND cont.contentclass = 'stylesheet' ";
		$sql.= "AND cont.active_flg = '1' ";
		if($cont_tbl == "content_public"){
			$sql.= "AND (cont.schedule_publish <= ? OR cont.schedule_publish IS NULL) ";
			$param[] = $now_timestamp;
			$sql.= "AND (cont.schedule_unpublish > ? OR cont.schedule_unpublish IS NULL) ";
			$param[] = $now_timestamp;
		}
		$sql.= "ORDER BY cont.device DESC ";
		$sql.= "LIMIT 1 ";

		$result = $this->query($sql,$param,DB::FETCH);
		return $result;
	}

	function getScriptByUrl($url,$domain,$device = ""){
		$now_timestamp = time();

		$cont_tbl = $this->tablename;

		$sql = "SELECT ";
		$sql.= 		"cont.content_id,";
		$sql.= 		"cont.content,";
		$sql.= 		"cont.addinfo_index,";
		$sql.= 		"domain.base_dir_path as base_dir_path ";
		$sql.= "FROM ".$cont_tbl." cont ";
		$sql.= 		"INNER JOIN folder ON cont.folder_id = folder.folder_id ";
		$sql.= 			"AND (folder.start_time <= ? OR folder.start_time IS NULL) ";
		$param[] = $now_timestamp;
		$sql.= 			"AND (folder.end_time > ? OR folder.end_time IS NULL) ";
		$param[] = $now_timestamp;
		$sql.= 			"AND folder.active_flg = '1' ";
		$sql.= 		"INNER JOIN domain ON folder.domain_id = domain.domain_id ";
		$sql.= 			"AND domain.active_flg = '1' ";
		$sql.= "WHERE cont.url = ? ";
		$param[] = $url;
		$sql.= "AND domain.domain = ? ";
		$param[] = $domain;
		$sql.= "AND (cont.device = ? OR cont.device = '') ";
		$param[] = $device;
		$sql.= "AND cont.contentclass = 'script' ";
		$sql.= "AND cont.active_flg = '1' ";
		if($cont_tbl == "content_public"){
			$sql.= "AND (cont.schedule_publish <= ? OR cont.schedule_publish IS NULL) ";
			$param[] = $now_timestamp;
			$sql.= "AND (cont.schedule_unpublish > ? OR cont.schedule_unpublish IS NULL) ";
			$param[] = $now_timestamp;
		}
		$sql.= "ORDER BY cont.device DESC ";
		$sql.= "LIMIT 1 ";

		$result = $this->query($sql,$param,DB::FETCH);



		return $result;
	}

	var $getTemplateByTemplateIdLoopCheck = array();
	function getTemplateByTemplateId($template_id,$device = ""){
		$now_timestamp = time();

		$cont_tbl = $this->tablename;

		$sql = "SELECT ";
		$sql.= 		"template.content as template_content,";
		$sql.= 		"template.keywords as template_keywords,";
		$sql.= 		"template.description as template_description,";
		$sql.= 		"template.author as template_author,";
		$sql.= 		"template.doctype as template_doctype,";
		$sql.= 		"template.html_attr as template_html_attr,";
		$sql.= 		"template.head_attr as template_head_attr,";
		$sql.= 		"template.head_code as template_head_code,";
		$sql.= 		"template.body_attr as template_body_attr,";
		$sql.= 		"template.content_id as template_id,";
		$sql.= 		"template.template_id as parent_template_id,";
		$sql.= 		"template.stylesheet_index as template_stylesheet_index,";
		$sql.= 		"template.script_index as template_script_index,";
		$sql.= 		"template.addinfo_index as template_addinfo_index,";
		$sql.= 		"template.element_index as template_element_index, ";
		$sql.= 		"template.device as template_device, ";
		$sql.= 		"template.device_index  as template_device_index ";
		$sql.= "FROM ".$cont_tbl." cont ";
		$sql.= 		"LEFT JOIN ".$cont_tbl." template ON cont.template_id = template.content_id ";
		$sql.= 			"AND template.contentclass = 'template' ";
		$sql.= 			"AND template.active_flg = '1' ";
		$sql.= "WHERE cont.content_id = ? ";
		$param[] = $template_id;
		$sql.= "AND cont.contentclass = 'template' ";
		$sql.= "AND cont.active_flg = '1' ";
		if($cont_tbl == "content_public"){
			$sql.= "AND (cont.schedule_publish <= ? OR cont.schedule_publish IS NULL) ";
			$param[] = $now_timestamp;
			$sql.= "AND (cont.schedule_unpublish > ? OR cont.schedule_unpublish IS NULL) ";
			$param[] = $now_timestamp;
		}
		$result = $this->query($sql,$param,DB::FETCH);

		//別デバイスコンテンツが存在する場合には結果セットを再取得する
		if($result["template_device"] != $device && $result["template_device_index"]){
			$device_index = explode(",",$result["template_device_index"]);
			for($i=0;$i<count($device_index);$i++){
				$device_dic = explode("=",$device_index[$i]);
				if($device_dic[0] && $device_dic[0] == $device){
					if(in_array($device_dic[1],$this->getTemplateByTemplateIdLoopCheck)){
						break;
					}else{
						$this->getTemplateByTemplateIdLoopCheck[] = $device_dic[1];
						$result = $this->getTemplateById($device_dic[1],$device);
						break;
					}
				}
			}
		}

		return $result;
	}

	function getStyleScriptListInArray($array){
		$now_timestamp = time();

		$cont_tbl = $this->tablename;

		$sql = "SELECT ";
		$sql.= 		"cont.contentclass,";
		$sql.= 		"cont.media,";
		$sql.= 		"cont.url ";
		$sql.= "FROM ".$cont_tbl." cont ";
		$sql.= "WHERE cont.active_flg = '1' ";
		$sql.= "AND (cont.contentclass = 'stylesheet' OR cont.contentclass = 'script') ";
		if(count($array) > 0){
			$sql.= "AND cont.content_id IN (";
			for($i=0;$i<count($array);$i++){
				if($i > 0){ $sql.= ","; }
				$sql.= "?";
				$param[] = $array[$i];
			}
			$sql.= ") ";
		}
		if($cont_tbl == "content_public"){
			$sql.= "AND (cont.schedule_publish <= ? OR cont.schedule_publish IS NULL) ";
			$param[] = $now_timestamp;
			$sql.= "AND (cont.schedule_unpublish > ? OR cont.schedule_unpublish IS NULL) ";
			$param[] = $now_timestamp;
		}
		$sql.= "ORDER BY cont.sort_no ASC ";


		$result = $this->query($sql,$param);
		return $result;
	}

	function getAddInfoList($array){
		$cont_tbl = $this->tablename."_addinfo";

		$sql = "SELECT ";
		$sql.= 		"addinfo.content_addinfo_id,";
		$sql.= 		"addinfo.name,";
		$sql.= 		"addinfo.addinfo_content ";
		$sql.= "FROM ".$cont_tbl." addinfo ";
		$sql.= 		"LEFT JOIN addinfo_select sel ON addinfo.selectname = sel.selectname ";
		$sql.= 			"AND addinfo.optionvalue = sel.optionvalue ";
		$sql.= 			"AND sel.active_flg = '1' ";
		$sql.= "WHERE addinfo.active_flg = '1' ";
		if(count($array) > 0){
			$sql.= "AND addinfo.content_addinfo_id IN (";
			for($i=0;$i<count($array);$i++){
				if($i > 0){ $sql .= ","; }
				$sql.= "?";
				$param[] = $array[$i];
			}
			$sql.= ") ";
		}

		$result = $this->query($sql,$param);
		return $result;
	}

	var $addinfoListMap = array();
	function getAddInfoListByContentId($content_id){
		if(isset($this->addinfoListMap[$content_id])){
			return $this->addinfoListMap[$content_id];
		}
		$cont_tbl = $this->tablename."_addinfo";

		$sql = "SELECT ";
		$sql.= 		"addinfo.content_addinfo_id,";
		$sql.= 		"addinfo.name,";
		$sql.= 		"addinfo.addinfo_content, ";
		$sql.= 		"addinfo.inputtype, ";
		$sql.= 		"sel.optionvalue ";
		$sql.= "FROM ".$cont_tbl." addinfo ";
		$sql.= 		"LEFT JOIN addinfo_select sel ON addinfo.selectname = sel.selectname ";
		$sql.= 			"AND addinfo.optionvalue = sel.optionvalue ";
		$sql.= 			"AND sel.active_flg = '1' ";
		$sql.= "WHERE addinfo.active_flg = '1' ";
		$sql.= "AND addinfo.content_id = ? ";
		$param[] = $content_id;
		$result = $this->query($sql,$param);
		$this->addinfoListMap[$content_id] = $result;

		return $result;
	}

	function loadAddinfoContent(){
		$cont_tbl = $this->tablename."_addinfo";

		$sql = "SELECT ";
		$sql.= 		"addinfo.content_id,";
		$sql.= 		"addinfo.content_addinfo_id,";
		$sql.= 		"addinfo.name,";
		$sql.= 		"addinfo.addinfo_content, ";
		$sql.= 		"addinfo.inputtype ";
		$sql.= "FROM ".$cont_tbl." addinfo ";
		$sql.= "WHERE addinfo.active_flg = '1' ";
		//$param[] = $content_id;
		$result = $this->query($sql);
		for($i=0;$i<count($result);$i++){
			if(!isset($this->addinfoListMap[$result[$i]["content_id"]])){
				$this->addinfoListMap[$result[$i]["content_id"]] = array();
			}

			$this->addinfoListMap[$result[$i]["content_id"]][] = $result[$i];
		}
	}



	var $getElementByIdLoopCheck = array();
	var $elementMap = array();
	function getElementById($element_id,$device = ""){
		if(isset($this->elementMap[$element_id])){
			return $this->elementMap[$element_id];
		}
		$now_timestamp = time();

		$cont_tbl = $this->tablename;

		$sql = "SELECT ";
		$sql.= 		"cont.content_id,";
		$sql.= 		"cont.content,";
		$sql.= 		"cont.title,";
		$sql.= 		"cont.addinfo_index,";
		$sql.= 		"cont.element_index, ";
		$sql.= 		"cont.device, ";
		$sql.= 		"cont.device_index ";
		$sql.= "FROM ".$cont_tbl." cont ";
		$sql.= "WHERE cont.content_id = ? ";
		$param[] = $element_id;
		$sql.= "AND cont.active_flg = '1' ";
		//$sql.= "AND (cont.contentclass = 'page' OR cont.contentclass = 'element') ";
		if($cont_tbl == "content_public"){
			$sql.= "AND (cont.schedule_publish <= ? OR cont.schedule_publish IS NULL) ";
			$param[] = $now_timestamp;
			$sql.= "AND (cont.schedule_unpublish > ? OR cont.schedule_unpublish IS NULL) ";
			$param[] = $now_timestamp;
		}

		$result = $this->query($sql,$param,DB::FETCH);

		//別デバイスコンテンツが存在する場合には結果セットを再取得する
		if($result["device"] != $device && $result["device_index"]){
			$device_index = explode(",",$result["device_index"]);
			for($i=0;$i<count($device_index);$i++){
				$device_dic = explode("=",$device_index[$i]);
				if($device_dic[0] && $device_dic[0] == $device){
					if(in_array($device_dic[1],$this->getElementByIdLoopCheck)){
						break;
					}else{
						$this->getElementByIdLoopCheck[] = $device_dic[1];
						$result = $this->getElementById($device_dic[1],$device);
						break;
					}
				}
			}
		}

		$this->elementMap[$element_id] = $result;

		return $result;
	}

	function getListFunctionContent($parameter,$count_flag = false){
		$now_timestamp = time();
		//Debug::arrayCheck($parameter);
		$cont_tbl = $this->tablename;
		$cont_addinfo_tbl = $cont_tbl."_addinfo";

		//追加情報を参照する項目リスト作成
		$default_column = array("content_id","title","subtitle","content","url","contentclass","contentclass","contenttype_id","sort_no","folder_id","updated","created");
		$addinfo_search = array();
		$ext_order_column = array();
		foreach($parameter as $key => $value){
			if(preg_match("/^search_ext_([a-z0-9-_\.]+)$/",$key,$matches)){
				$data = array();
				$data["key"] = $matches[1];
				$data["value"] = $value;
				$addinfo_search[] = $data;
			}elseif($key == "search_orderby"){
				foreach($value as $key2 => $value2){
					if(!in_array($value2["value"], $default_column)){
						$ext_order_column[] = $value2["value"];
					}
				}
			}
		}

		$param = array();

		$sql = "SELECT ";

		if($count_flag){
			$sql.= 		"COUNT(*) as cnt ";
		}else{
			$sql.= 		"cont.content_id,";
			$sql.= 		"cont.title,";
			$sql.= 		"cont.content,";
			$sql.= 		"cont.url,";
			$sql.= 		"cont.contentclass,";
			$sql.= 		"cont.contenttype_id,";
			$sql.= 		"cont.folder_id,";
			$sql.= 		"cont.updated,";
			$sql.= 		"cont.created,";

			$cnt = 0;
			for($i=0;$i<count($addinfo_search);$i++){
				$sql.= 		"col".$cnt.".addinfo_content as ".$addinfo_search[$i]["key"].",";
				$cnt++;
			}
			for($i=0;$i<count($ext_order_column);$i++){
				$sql.= 		"col".$cnt.".addinfo_content as ".$ext_order_column[$i].",";
				$cnt++;
			}

			$sql.= 		"cont.sort_no ";
		}

		$sql.= "FROM ".$cont_tbl." cont ";
		$sql.= 		"LEFT JOIN folder ";
		$sql.= 		"ON cont.folder_id = folder.folder_id ";
		$sql.= 		"LEFT JOIN contenttype conttype ";
		$sql.= 		"ON cont.contenttype_id = conttype.contenttype_id ";

		$cnt = 0;
		for($i=0;$i<count($addinfo_search);$i++){
			$sql.= 	"LEFT JOIN ".$cont_addinfo_tbl." as col".$cnt." ON cont.content_id = col".$cnt.".content_id ";
			$sql.=	"AND col".$cnt.".name = ? ";
			$param[] = $addinfo_search[$i]["key"];
			$cnt++;
		}
		for($i=0;$i<count($ext_order_column);$i++){
			$sql.= 	"LEFT JOIN ".$cont_addinfo_tbl." as col".$cnt." ON cont.content_id = col".$cnt.".content_id ";
			$sql.=	"AND col".$cnt.".name = ? ";
			$param[] = $ext_order_column[$i];
			$cnt++;
		}

		$sql.= "WHERE cont.active_flg = '1' ";

		//コンテンツID検索
		if(isset($parameter["search_content_id"])){
			if(isset($parameter["search_content_id"][0])){
				$or_str = "";
				foreach($parameter["search_content_id"] as $value){
					if($value["value"] == ""){
					}else{
						if($or_str == ""){ $or_str = "AND ("; }else{ $or_str .= " OR "; }
						if($value["condition"] == "="){
							$or_str.= "cont.content_id LIKE ? ";
						}else{
							$or_str.= "cont.content_id ".$value["condition"]." ? ";
						}
						$param[] = $value["value"];
					}
				}
				if($or_str != ""){ $or_str .= " ) "; }
				$sql.= $or_str;
			}else{
				$value = $parameter["search_content_id"];
				if($value["value"] == ""){
				}else{
					if($value["condition"] == "="){
						$sql.= "AND cont.content_id LIKE ? ";
					}else{
						$sql.= "AND cont.content_id ".$value["condition"]." ? ";
					}
					$param[] = $value["value"];
				}
			}
		}

		//タイトル検索
		if(isset($parameter["search_title"])){
			if(isset($parameter["search_title"][0])){
				$or_str = "";
				foreach($parameter["search_title"] as $value){
					if($value["value"] == ""){
					}else{
						if($or_str == ""){ $or_str = "AND ("; }else{ $or_str .= " OR "; }
						if($value["condition"] == "="){
							$or_str.= "cont.title LIKE ? ";
						}else{
							$or_str.= "cont.title ".$value["condition"]." ? ";
						}
						$param[] = $value["value"];
					}
				}
				if($or_str != ""){ $or_str .= " ) "; }
				$sql.= $or_str;
			}else{
				$value = $parameter["search_title"];
				if($value["value"] == ""){
				}else{
					if($value["condition"] == "="){
						$sql.= "AND cont.title LIKE ? ";
					}else{
						$sql.= "AND cont.title ".$value["condition"]." ? ";
					}
					$param[] = $value["value"];
				}
			}
		}

		//コンテンツ検索
		if(isset($parameter["search_content"])){
			if(isset($parameter["search_content"][0])){
				$or_str = "";
				foreach($parameter["search_content"] as $value){
					if($value["value"] == ""){
					}else{
						if($or_str == ""){ $or_str = "AND ("; }else{ $or_str .= " OR "; }
						if($value["condition"] == "="){
							$or_str.= "cont.content LIKE ? ";
						}else{
							$or_str.= "cont.content ".$value["condition"]." ? ";
						}
						$param[] = $value["value"];
					}
				}
				if($or_str != ""){ $or_str .= " ) "; }
				$sql.= $or_str;
			}else{
				$value = $parameter["search_content"];
				if($value["value"] == ""){
				}else{
					if($value["condition"] == "="){
						$sql.= "AND cont.content LIKE ? ";
					}else{
						$sql.= "AND cont.content ".$value["condition"]." ? ";
					}
					$param[] = $value["value"];
				}
			}
		}

		//URL検索
		if(isset($parameter["search_url"])){
			if(isset($parameter["search_url"][0])){
				$or_str = "";
				foreach($parameter["search_url"] as $value){
					if($value["value"] == ""){
					}else{
						if($or_str == ""){ $or_str = "AND ("; }else{ $or_str .= " OR "; }
						if($value["condition"] == "="){
							$or_str.= "cont.url LIKE ? ";
						}else{
							$or_str.= "cont.url ".$value["condition"]." ? ";
						}
						$param[] = $value["value"];
					}
				}
				if($or_str != ""){ $or_str .= " ) "; }
				$sql.= $or_str;
			}else{
				$value = $parameter["search_url"];
				if($value["value"] == ""){
				}else{
					if($value["condition"] == "="){
						$sql.= "AND cont.url LIKE ? ";
					}else{
						$sql.= "AND cont.url ".$value["condition"]." ? ";
					}
					$param[] = $value["value"];
				}
			}
		}

		//フォルダ検索
		if(isset($parameter["search_folder"])){
			if(isset($parameter["search_folder"][0])){
				$or_str = "";
				foreach($parameter["search_folder"] as $value){
					if($value["value"] == ""){
					}elseif(is_numeric($value["value"])){
						if($or_str == ""){ $or_str = "AND ("; }else{ $or_str .= " OR "; }
						$or_str.= "cont.folder_id ".$value["condition"]." ? ";
						$param[] = $value["value"];
					}else{
						if($or_str == ""){ $or_str = "AND ("; }else{ $or_str .= " OR "; }
						if($value["condition"] == "="){
							$or_str.= "folder.folder_code LIKE ? ";
						}else{
							$or_str.= "folder.folder_code ".$value["condition"]." ? ";
						}
						$param[] = $value["value"];
					}
				}
				if($or_str != ""){ $or_str .= " ) "; }
				$sql.= $or_str;
			}else{
				$value = $parameter["search_folder"];
				if($value["value"] == ""){
				}elseif(is_numeric($value["value"])){
					$sql.= "AND cont.folder_id ".$value["condition"]." ? ";
					$param[] = $value["value"];
				}else{
					if($value["condition"] == "="){
						$sql.= "AND folder.folder_code LIKE ? ";
					}else{
						$sql.= "AND folder.folder_code ".$value["condition"]." ? ";
					}
					$param[] = $value["value"];
				}
			}
		}

		//コンテンツクラス検索
		if(isset($parameter["search_contentclass"])){
			if(isset($parameter["search_contentclass"][0])){
				$or_str = "";
				foreach($parameter["search_contentclass"] as $value){
					if($value["value"] == ""){
					}else{
						if($or_str == ""){ $or_str = "AND ("; }else{ $or_str .= " OR "; }
						$or_str.= "cont.contentclass ".$value["condition"]." ? ";
						$param[] = $value["value"];
					}
				}
				if($or_str != ""){ $or_str .= " ) "; }
				$sql.= $or_str;
			}else{
				$value = $parameter["search_contentclass"];
				if($value["value"] == ""){
				}else{
					$sql.= "AND cont.contentclass ".$value["condition"]." ? ";
					$param[] = $value["value"];
				}
			}
		}

		//コンテンツタイプ検索
		if(isset($parameter["search_contenttype"])){
			if(isset($parameter["search_contenttype"][0])){
				$or_str = "";
				foreach($parameter["search_contenttype"] as $value){
					if($value["value"] == ""){
					}elseif(is_numeric($value["value"])){
						if($or_str == ""){ $or_str = "AND ("; }else{ $or_str .= " OR "; }
						$or_str.= "cont.contenttype_id ".$value["condition"]." ? ";
						$param[] = $value["value"];
					}else{
						if($or_str == ""){ $or_str = "AND ("; }else{ $or_str .= " OR "; }
						if($value["condition"] == "="){
							$or_str.= "conttype.contenttype_name LIKE ? ";
						}else{
							$or_str.= "conttype.contenttype_name ".$value["condition"]." ? ";
						}
						$param[] = $value["value"];
					}
				}
				if($or_str != ""){ $or_str .= " ) "; }
				$sql.= $or_str;
			}else{
				$value = $parameter["search_contenttype"];
				if($value["value"] == ""){
				}elseif(is_numeric($value["value"])){
					$sql.= "AND cont.contenttype_id ".$value["condition"]." ? ";
					$param[] = $value["value"];
				}else{
					if($value["condition"] == "="){
						$sql.= "AND conttype.contenttype_name LIKE ? ";
					}else{
						$sql.= "AND conttype.contenttype_name ".$value["condition"]." ? ";
					}

					$param[] = $value["value"];
				}
			}
		}

		//追加情報検索
		for($i=0;$i<count($addinfo_search);$i++){
			if(isset($addinfo_search[$i]["value"][0])){
				$or_str = "";
				foreach($addinfo_search[$i]["value"] as $value){
					if($value["value"] == ""){
					}else{
						if($or_str == ""){ $or_str = "AND ("; }else{ $or_str .= " OR "; }
						if($value["condition"] == "="){
							$or_str.= "col".$i.".addinfo_content LIKE ? ";
						}else{
							$or_str.= "col".$i.".addinfo_content ".$value["condition"]." ? ";
						}
						$param[] = $value["value"];
					}
				}
				if($or_str != ""){ $or_str .= " ) "; }
				$sql.= $or_str;
			}else{
				$value = $addinfo_search[$i]["value"];
				if($value["value"] == ""){
				}else{
					if($value["condition"] == "="){
						$sql.= "AND col".$i.".addinfo_content LIKE ? ";
					}else{
						$sql.= "AND col".$i.".addinfo_content ".$value["condition"]." ? ";
					}
					$param[] = $value["value"];
				}
			}
		}

		if($cont_tbl == "content_public"){
			$sql.= "AND (cont.schedule_publish <= ? OR cont.schedule_publish IS NULL) ";
			$param[] = $now_timestamp;
			$sql.= "AND (cont.schedule_unpublish > ? OR cont.schedule_unpublish IS NULL) ";
			$param[] = $now_timestamp;
		}

		//並び順制御
		if(!$count_flag){
			if(isset($parameter["search_orderby"])){
				$count = 0;
				$order_str = "";
				foreach($parameter["search_orderby"] as $orderby_one){
					if($orderby_one["value"] == ""){
					}else{
						if($order_str == ""){ $order_str = "ORDER BY "; }else{ $order_str .= ","; }
						$order_str.= $orderby_one["value"]." ";
						if(isset($parameter["search_ordertype"][$count])){
							if($parameter["search_ordertype"][$count]["value"] == ""){
							}else{
								if($parameter["search_ordertype"][$count]["value"] == "DESC" || $parameter["search_ordertype"][$count]["value"] == "desc"){
									$order_str.= "DESC ";
								}else{
									$order_str.= "ASC ";
								}
							}
						}
					}
					$count++;
				}
				$sql.= $order_str;
			}

			//開始番号
			$start = 0;
			if(isset($parameter["search_start"]) && is_numeric($parameter["search_start"]["value"])){
				$start = $parameter["search_start"]["value"];
			}

			//件数
			$limit = 100000;
			if(isset($parameter["search_limit"]) && is_numeric($parameter["search_limit"]["value"])){
				$limit = $parameter["search_limit"]["value"];
			}

			$sql.= " LIMIT ".$start.",".$limit." ";
		}
		//Debug::sqlCheckStart();
		$result = $this->query($sql,$param);
		//Debug::sqlCheckEnd();
		if($count_flag){
			return $result[0]["cnt"];
		}else{
			return $result;
		}
	}

	function getListFunctionDatabase($parameter,$count_flag = false){
		//存在するテーブル名をセット
		$this->setTables();

		//テーブル名存在チェック
		if(!in_array($parameter["database"]["value"],$this->tables)){
			echo "error";
			return;
		}

		$params = array();
		$sql = "SELECT ";
		if($count_flag){
			$sql.= 		"COUNT(*) as cnt ";
		}else{
			if(isset($parameter["column"]["value"])){
				if(isset($parameter["column"][0])){
					foreach($parameter["column"] as $key => $column["value"]){
						if($key != 0){ $sql.= ","; }
						$sql.= $column["value"];
					}
				}else{
					$sql.= $parameter["column"]["value"];
				}
				$sql.= " ";
			}else{
				$sql.= 		"* ";
			}
		}
		$sql.= "FROM ".$parameter["database"]["value"]." ";
		if(isset($parameter["search"]) && $parameter["search"]){
			$sql.= "WHERE ";
			$counter = 0;
			foreach($parameter["search"] as $key => $value){
				if($counter != 0){ $sql.= " AND ";}
				if(isset($value[0])){
					$sql.= "(";
					for($i=0;$i<count($value);$i++){
						if($i!=0){ $sql.= " OR ";}
						if($value[$i]["condition"] == "="){
							if(is_numeric($value[$i]["value"])){
								$sql.= $key." = ? ";
							}else{
								$sql.= $key." LIKE ? ";
							}
						}else{
							$sql.= $key." ".$value["condition"]." ? ";
						}
						$param[] = $value[$i]["value"];
					}
					if($i>0){ $sql.= ") ";}
				}else{
					if($value["condition"] == "="){
						if(is_numeric($value["value"])){
							$sql.= $key." = ? ";
						}else{
							$sql.= $key." LIKE ? ";
						}
					}else{
						$sql.= $key." ".$value["condition"]." ? ";
					}
					$param[] = $value["value"];
				}
				$counter++;
			}
		}

		//並び順制御
		if(!$count_flag){
			if(isset($parameter["search_orderby"])){
				$count = 0;
				$order_str = "";
				foreach($parameter["search_orderby"] as $orderby_one){
					if($orderby_one["value"] == ""){
					}else{
						if($order_str == ""){ $order_str = "ORDER BY "; }else{ $order_str .= ","; }
						$order_str.= $orderby_one["value"]." ";
						if(isset($parameter["search_ordertype"][$count])){
							if($parameter["search_ordertype"][$count]["value"] == ""){
							}else{
								if($parameter["search_ordertype"][$count]["value"] == "DESC" || $parameter["search_ordertype"][$count]["value"] == "desc"){
									$order_str.= "DESC ";
								}else{
									$order_str.= "ASC ";
								}
							}
						}
					}
					$count++;
				}
				$sql.= $order_str;
			}

			//開始番号
			$start = 0;
			if(isset($parameter["search_start"]) && is_numeric($parameter["search_start"]["value"])){
				$start = $parameter["search_start"]["value"];
			}

			//件数
			$limit = 100000;
			if(isset($parameter["search_limit"]) && is_numeric($parameter["search_limit"]["value"])){
				$limit = $parameter["search_limit"]["value"];
			}

			$sql.= " LIMIT ".$start.",".$limit." ";
		}
		//Debug::sqlCheckStart();
		$result = $this->query($sql,$param);
		//Debug::sqlCheckEnd();
		if($count_flag){
			return $result[0]["cnt"];
		}else{
			return $result;
		}
	}
}
