<?php
$str = "";

//DOCTYPE宣言
$str.= $pageConfig["doctype"]."\n";

//HTML開始タグ
if($pageConfig["html_attr"]){
	$str.= '<html '.$pageConfig["html_attr"].">\n";
}else{
	$str.= "<html>\n";
}

//HEAD開始タグ
if($pageConfig["head_attr"]){
	$str.= '<head '.$pageConfig["head_attr"].">\n";
}else{
	$str.= '<head>'."\n";
}

//エンコード宣言
$encode_str = "";
if($pageConfig["doctype"] == "<!DOCTYPE html>"){
	//HTML5
	$encode_str.= '<meta charset="'.Config::DEFAULT_ENCODE.'" />'."\n";
}else{
	//HTML5以外
	$encode_str.= '<meta http-equiv="Content-Type" content="text/html; charset='.Config::DEFAULT_ENCODE.'" />'."\n";
}

//タイトル
$title_str = "";
$title_str.= '<title>'.htmlspecialchars(trim($pageConfig["title"])).'</title>'."\n";

//作成者
$author_str = "";
if($pageConfig["author"] != null && $pageConfig["author"] != ""){
	$author_str.= '<meta name="author" content="'.htmlspecialchars($pageConfig["author"]).'" />'."\n";
}

//ディスクリプション
$description_str = "";
if($pageConfig["description"] != null && $pageConfig["description"] != ""){
	$description_str.= '<meta name="description" content="'.htmlspecialchars($pageConfig["description"]).'" />'."\n";
}

//キーワード
$keyword_str = "";
if($pageConfig["keywords"] != null && $pageConfig["keywords"] != ""){
	$keyword_str.= '<meta name="keywords" content="'.htmlspecialchars($pageConfig["keywords"]).'" />'."\n";
}

//スタイルシート
$style_str = "";
for($i=0;$i<count($pageConfig["stylesheet"]);$i++){
	if($pageConfig["stylesheet"][$i]["media"]){
		$media = 'media="'.htmlspecialchars($pageConfig["stylesheet"][$i]["media"]).'"';
	}else{
		$media = "";
	}
	$style_str.= '<link rel="stylesheet" type="text/css" href="/'.$pageConfig["base_dir_path"].$pageConfig["stylesheet"][$i]["url"].'" '.$media.' />'."\n";
}

//スクリプト
$script_str = "";
for($i=0;$i<count($pageConfig["script"]);$i++){
	$script_str.= '<script type="text/javascript" src="/'.$pageConfig["base_dir_path"].$pageConfig["script"][$i]["url"].'" ></script>'."\n";
}

//タイトル組み込み
$replace = Config::HEAD_CODE_REPLACE_MARK_START."head_title".Config::HEAD_CODE_REPLACE_MARK_END;
$pattern = "/".$replace."/i";
if(preg_match($pattern,$pageConfig["head_code"])){
	$pageConfig["head_code"] = str_replace($replace, $title_str, $pageConfig["head_code"]);
	$title_str = "";
}

//作成者組み込み
$replace = Config::HEAD_CODE_REPLACE_MARK_START."head_author".Config::HEAD_CODE_REPLACE_MARK_END;
$pattern = "/".$replace."/i";
if(preg_match($pattern,$pageConfig["head_code"])){
	$pageConfig["head_code"] = str_replace($replace, $author_str, $pageConfig["head_code"]);
	$author_str = "";
}

//ディスクリプション組み込み
$replace = Config::HEAD_CODE_REPLACE_MARK_START."head_description".Config::HEAD_CODE_REPLACE_MARK_END;
$pattern = "/".$replace."/i";
if(preg_match($pattern,$pageConfig["head_code"])){
	$pageConfig["head_code"] = str_replace($replace, $description_str, $pageConfig["head_code"]);
	$description_str = "";
}

//キーワード組み込み
$replace = Config::HEAD_CODE_REPLACE_MARK_START."head_keywords".Config::HEAD_CODE_REPLACE_MARK_END;
$pattern = "/".$replace."/i";
if(preg_match($pattern,$pageConfig["head_code"])){
	$pageConfig["head_code"] = str_replace($replace, $keyword_str, $pageConfig["head_code"]);
	$keyword_str = "";
}

//スタイルシート組み込み
$replace = Config::HEAD_CODE_REPLACE_MARK_START."head_stylesheet".Config::HEAD_CODE_REPLACE_MARK_END;
$pattern = "/".$replace."/i";
if(preg_match($pattern,$pageConfig["head_code"])){
	$pageConfig["head_code"] = str_replace($replace, $style_str, $pageConfig["head_code"]);
	$style_str = "";
}

//スクリプト組み込み
$replace = Config::HEAD_CODE_REPLACE_MARK_START."head_script".Config::HEAD_CODE_REPLACE_MARK_END;
$pattern = "/".$replace."/i";
if(preg_match($pattern,$pageConfig["head_code"])){
	$pageConfig["head_code"] = str_replace($replace, $script_str, $pageConfig["head_code"]);
	$style_str = "";
}

//カスタムHEADコード
$custom_head_code_str = $encode_str.$title_str.$author_str.$description_str.$keyword_str.$style_str.$script_str;
$custom_head_code_str.= $pageConfig["head_code"];
$str.= $custom_head_code_str;
//HEAD終了タグ
$str.= "\n</head>\n";

//BODY開始タグ
if($pageConfig["body_attr"]){
	$str.= '<body '.$pageConfig["body_attr"].">\n";
}else{
	$str.= '<body>'."\n";
}

//コンテンツ
$str.= $pageConfig["content"];

//BODY終了タグ
$str.= "\n</body>\n";

//HTML終了タグ
$str.= "</html>";
?>