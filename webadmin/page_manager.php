<?php
// アプリケーション共通クラス読込
require_once(dirname(__FILE__).'/ApplicationCommon/include.php');
require_once(dirname(__FILE__).'/CMSCommon/include.php');

require_once(dirname(__FILE__).'/DataAccess/Content.php'); 			//コンテンツクラス
require_once(dirname(__FILE__).'/DataAccess/Folder.php'); 			//フォルダクラス

//基本設定
$self = basename(__FILE__);				//自身のファイル名
$error = array();						//エラー情報格納用配列
$message = array();						//メッセージ情報格納用配列
$alert = array();						//アラート情報格納用配列
$redirect = "index.php";				//エラー時リダイレクト先
$restrict = array();					//表示・操作制限

$session = Session::get();

$session->loginCheckAndRedirect("login.php",array("admin_flg" => 1));

//権限取得
$ope_auth_publish = $session->user["ope_auth_page_publish"];
$ope_auth_edit = $session->user["ope_auth_page_edit"];
$ope_auth_delete = $session->user["ope_auth_page_delete"];

//コンテンツクラス
$Content = new Content(Content::TABLE_MANAGEMENT);

//リクエストパラメータ取得
$action = isset($_REQUEST["action"]) ? $_REQUEST["action"] : "";				//実行アクション
$content_id = isset($_REQUEST["content_id"]) ? $_REQUEST["content_id"] : "";	//コンテンツID
$folder_id = isset($_REQUEST["folder_id"]) ? $_REQUEST["folder_id"] : 1;		//フォルダID
$sort = isset($_REQUEST["sort"]) ? $_REQUEST["sort"] : "";						//ソート方向
$search_action = isset($_REQUEST["search_action"]) ? $_REQUEST["search_action"] : "";						//検索アクション
$search_title_flg = isset($_REQUEST["search_title_flg"]) ? $_REQUEST["search_title_flg"] : "";				//ページタイトル検索フラグ
$search_content_flg = isset($_REQUEST["search_content_flg"]) ? $_REQUEST["search_content_flg"] : "";		//コンテンツ検索フラグ
$search_keyword = isset($_REQUEST["search_keyword"]) ? $_REQUEST["search_keyword"] : "";					//検索キーワード

//検索条件付きPOSTリダイレクト制御処理
if((isset($_REQUEST["search_title_flg"]) && !isset($_GET["search_title_flg"])) || (isset($_REQUEST["search_content_flg"]) && !isset($_GET["search_content_flg"]))){
	$redirectParam = array();
	$redirectParam["search_title_flg"] = $search_title_flg;
	$redirectParam["search_content_flg"] = $search_content_flg;
	$redirectParam["search_keyword"] = $search_keyword;
	Location::redirect($self,$redirectParam);
}

//検索フラグ判定
if(($search_title_flg == "on" || $search_content_flg == "on") && $search_keyword != ""){
	$search_flg = "on";
}else{
	$search_flg = "";
}


//ソート処理
if($action == "sort" && $sort != ""){
	if($sort == "up"){
		$Content->sortUp($content_id,$session->user["user_id"]);
	}else if($sort == "down"){
		$Content->sortDown($content_id,$session->user["user_id"]);
	}
	$redirectParam = array();
	$redirectParam["folder_id"] = $folder_id;
	Location::redirect($self,$redirectParam);
}

//コンテンツ一覧取得
$where = array();
$where["contentclass"] = "page";
$where["folder_id"] = $folder_id;
if($search_title_flg == "on"){
	$where["title"] = "%".$search_keyword."%";
}
if($search_content_flg == "on"){
	$where["content"] = "%".$search_keyword."%";
}
$contentList = $Content->getContentManagementListByParameters($where);

//削除済みコンテンツ一覧取得
if($search_flg != "on"){
	$deletedArchiveList = $Content->getDeletedArchiveListByParameters($where);
}else{
	$deletedArchiveList = array();
}

$LayoutManager = new LayoutManagerAdminMain();
$LayoutManager->setTitle("ページ情報管理");
$LayoutManager->header();

?>
<div class="search">
<?php if(Config::get("folder_use_flg") == "on"): ?>
	<?php
	$Folder = new Folder();
	$where = array();
	$where["active_flg"] = 1;
	$where["list_display_flg"] = 1;
	$order = array();
	$order["sort_no"] = "asc";
	$folderList = $Folder->getListByParameters($where,$order);
	?>
	<form id="folder_values" action="">
	フォルダ選択：<select name="folder_id" onchange="changeFolder();" style="padding:2px;">
	<?php
	$code_display = Config::get("folder_code_display_flg");
	for($i=0;$i<count($folderList);$i++){
		if($folderList[$i]["folder_id"] == $folder_id){ $selected = "selected"; }else{ $selected = ""; }
		if($code_display == "on" && $folderList[$i]["folder_code"] != ""){ $code = "【".$folderList[$i]["folder_code"]."】"; }else{ $code = ""; }
		echo '<option value="'.$folderList[$i]["folder_id"].'" '.$selected.'>'.$folderList[$i]["folder_name"].$code."</option>";
	}
	?>
	</select>
	</form>
	<script>
	function changeFolder(){
		$('#folder_values').attr({
		       'action':'<?php echo $self; ?>',
		       'method':'get'
		     });
		$('#folder_values').submit();
	}
	</script>
<?php endif; ?>
<?php if($session->user["admintype"] == SPConst::ADMINTYPE_SUPERVISOR || $session->user["ope_auth_page_add"]): ?>
	<form action = "" id="control_values" style="margin-top: 10px;">
	<input type="hidden" name="mode" />
	<input type="hidden" name="close_url" />
	<input type="hidden" name="contentclass" />
	<input type="hidden" name="folder_id" />
	<input type="hidden" name="search_title_flg" value="<?php echo htmlspecialchars($search_title_flg); ?>" />
	<input type="hidden" name="search_content_flg" value="<?php echo htmlspecialchars($search_content_flg); ?>" />
	<input type="hidden" name="search_keyword" value="<?php echo htmlspecialchars($search_keyword); ?>" />

	<input class="btn btn_small" type="button" value="新規追加" id="action_new" />
	<?php if(Config::get("content_new_mode") != "" && Config::get("content_new_mode") != "normal"): ?>
		<select name="copy_content_id" style="padding:2px;">
		<?php if(Config::get("content_new_mode") == "multi"): ?>
			<option value="">--コピー元選択--</option>
		<?php endif; ?>
		<?php
		for($i=0;$i < count($contentList);$i++){
			echo "<option value='".$contentList[$i]["content_id"]."'>".$contentList[$i]["title"]."</option>\n";
		}
		?>
		</select>
	<?php endif; ?>
	<script>
	$("#action_new").click(function(){
		var action = "content_edit.php";
		$("*[name=mode]").val('new');
		$("*[name=contentclass]").val('page');
		$("*[name=folder_id]").val('<?php echo $folder_id; ?>');
		$("*[name=close_url]").val('<?php echo $self; ?>');
		$('#control_values').attr({
		       'action':action,
		       'method':'get'
		     });
		$('#control_values').submit();
	});
	</script>
	</form>
<?php endif; ?>
</div>

<h3>ページ情報一覧</h3>


<div class="search">
	<form action="<?php echo $self; ?>" id="search_values">
	<input type="hidden" name="search_action" value="" />
	<input type="hidden" name="folder_id" value="<?php echo htmlspecialchars($folder_id); ?>" />
	<input type="checkbox" name="search_title_flg" <?php if($search_title_flg == "on"){echo "checked"; }?> />ページタイトル
	<input type="checkbox" name="search_content_flg" <?php if($search_content_flg == "on"){echo "checked"; }?> />コンテンツ
	<input type="text" name="search_keyword" value="<?php echo htmlspecialchars($search_keyword); ?>" />
	<input class="btn btn_small" type="button" value="検索" id="search_btn" /> /
	<input class="btn btn_small" type="button" value="解除" id="search_clear_btn" />
	</form>
</div>
<script>
$("#search_btn").click(function(){
	var action = "<?php echo $self; ?>";
	$("*[name=search_action]").val('search');
	$('#search_values').attr({
	       'action':action,
	       'method':'get'
	     });
	$('#search_values').submit();
});
$("#search_clear_btn").click(function(){
	var action = "<?php echo $self; ?>";
	$("*[name=search_title_flg]").attr("checked",false);
	$("*[name=search_content_flg]").attr("checked",false);
	$("*[name=search_keyword]").val("");
	$("*[name=search_action]").val('clear');
	$('#search_values').attr({
	       'action':action,
	       'method':'get'
	     });
	$('#search_values').submit();
});
</script>

<form action = "" id="content_values" style="margin-top: 10px;">
<table class="list">
<tr>
    <th class="w80">選択</th>
	<th class="w80">ID</th>
	<th>タイトル / URL</th>
	<th>公開 / 期日</th>
	<th>作成 / 更新</th>
	<th>状態</th>
	<th class="w80">プレビュー</th>
	<?php if($search_flg != "on"):?>
		<th class="w80">並び順</th>
	<?php endif; ?>
</tr>

<?php
for($i=0;$i < count($contentList);$i++){
	echo '<tr>'."\n";
	echo '<td><input type="checkbox" name="content_id[]" class="list_checkbox" value="'.$contentList[$i]["content_id"].'" ></td>'."\n";
	echo '<td>'.$contentList[$i]["content_id"].'</td>'."\n";
	echo '<td><a href="content_edit.php?contentclass='.$contentList[$i]["contentclass"].'&mode=edit&content_id='.$contentList[$i]["content_id"].'&folder_id=1&close_url='.$self.'&search_title_flg='.htmlspecialchars($search_title_flg).'&search_content_flg='.htmlspecialchars($search_content_flg).'&search_keyword='.htmlspecialchars($search_keyword).'">'.htmlspecialchars($contentList[$i]["title"]).'</a><br>'."\n";
	echo $contentList[$i]["url"]."\n";
	echo '</td>'."\n";
	if(Util::IsNullOrEmpty($contentList[$i]["schedule_publish"])){
		echo '<td>指定なし<br>'."\n";
	}else{
		echo '<td>'.date("Y/m/d",$contentList[$i]["schedule_publish"]).'<br>'."\n";
	}
	if(Util::IsNullOrEmpty($contentList[$i]["schedule_unpublish"])){
		echo '指定なし</td>'."\n";
	}else{
		echo date("Y/m/d",$contentList[$i]["schedule_unpublish"]).'</td>'."\n";
	}

	echo '<td>'.date("Y/m/d",$contentList[$i]["created"]).'<br>'."\n";
	echo date("Y/m/d",$contentList[$i]["updated"]).'</td>'."\n";

	echo '<td>'.ContentCommon::getContentStatusHTML($contentList[$i])."</td>\n";
	echo '<td>';
	$mode = Config::get("preview_mode");
	if(!$mode){ $mode = "preview"; }
	echo '<a href="http://'.$contentList[$i]["domain"].'/'.$contentList[$i]["base_dir_path"].$contentList[$i]["url"].'?mode='.$mode.'" target="_blank"><img src="img/screen_icon.png"></a>';
	echo '</td>';
	if($search_flg != "on"){
		echo '<td>';
		echo '<a href="'.$self.'?content_id='.$contentList[$i]["content_id"].'&action=sort&sort=up&folder_id='.$folder_id.'">▲</a>';
		echo '<br>';
		echo '<a href="'.$self.'?content_id='.$contentList[$i]["content_id"].'&action=sort&sort=down&folder_id='.$folder_id.'">▼</a>';
		echo '</td>';
	}
	echo '</tr>'."\n";
}
?>
</table>


<div class="search">
<div class="content_left">
<a class="btn btn_small" href="#" onclick="list_check_all('list_checkbox'); return false;">一括選択</a> / <a class="btn btn_small" href="#" onclick="list_clear_all('list_checkbox'); return false;">一括解除</a>
</div>
<div class="content_right">
<?php if(!$ope_auth_publish){ $disabled = "disabled"; }else{ $disabled = "";}?>
<input class="btn btn_small" type="button" value="選択を公開" onclick="action_publish_multiple('content_values'); return false;" <?php echo $disabled; ?>>
<?php if(!$ope_auth_publish){ $disabled = "disabled"; }else{ $disabled = "";}?>
<input class="btn btn_small" type="button" value="選択を非公開" onclick="action_unpublish_multiple('content_values'); return false;" <?php echo $disabled; ?>>
<?php if(!$ope_auth_delete){ $disabled = "disabled"; }else{ $disabled = "";}?>
<input class="btn red btn_small" type="button" value="選択を削除" onclick="action_delete_multiple('content_values'); return false;" <?php echo $disabled; ?>>
<?php if(Config::get("folder_use_flg") == "on"): ?>
	<?php if(!$ope_auth_edit){ $disabled = "disabled"; }else{ $disabled = "";}?>
	<input class="btn btn_small" type="button" value="選択を移動" onclick="action_move_multiple('content_values'); return false;" <?php echo $disabled; ?>>
	&nbsp;移動先:
	<select name="folder_id" style="padding:2px;">
	<?php
	for($i=0;$i<count($folderList);$i++){
		if($folderList[$i]["folder_id"] == $folder_id){ $selected = "selected"; }else{ $selected = ""; }
		echo '<option value="'.$folderList[$i]["folder_id"].'" '.$selected.'>'.$folderList[$i]["folder_name"]."</option>";
	}
	?>
	</select>
<?php endif; ?>
</form>
</div>
<div class="clear"></div>
</div>

<?php if(Config::get("show_deleted_archive") == "on" && count($deletedArchiveList) > 0): ?>
	<h3>削除済みコンテンツ</h3>
	<form action = "" id="archive_values" style="margin-top: 10px;">
	<table class="list">
	<tr>
	    <th class="w80">選択</th>
		<th class="w80">ID</th>
		<th>タイトル / URL</th>
		<th>最終アーカイブ日時</th>
	</tr>
	<?php
	for($i=0;$i < count($deletedArchiveList);$i++){
		echo '<tr>'."\n";
		echo '<td><input type="checkbox" name="content_id[]" class="list_checkbox_deleted_archive" value="'.$deletedArchiveList[$i]["content_archive_id"].'" ></td>'."\n";
		echo '<td>'.$deletedArchiveList[$i]["content_id"].'</td>'."\n";
		echo '<td>'.htmlspecialchars($deletedArchiveList[$i]["title"]).'<br>'."\n";
		echo $deletedArchiveList[$i]["url"]."\n";

		echo '<td>'.date("Y/m/d H:i",$deletedArchiveList[$i]["archive_time"]).'</td>'."\n";
		echo '</td>'."\n";
		echo '</tr>'."\n";
	}
	?>
	</table>
	</form>

    <div class="search">
    <div class="content_left">
	<a class="btn btn_small" href="#" onclick="list_check_all('list_checkbox_deleted_archive'); return false;">一括選択</a> / <a class="btn btn_small" href="#" onclick="list_clear_all('list_checkbox_deleted_archive'); return false;">一括解除</a>
    </div>
    <div class="content_right">
    <?php if(!$ope_auth_edit){ $disabled = "disabled"; }else{ $disabled = "";}?>
	<input class="btn btn_small" type="button" value="選択を復元" onclick="action_restore_multiple('archive_values'); return false;" <?php echo $disabled; ?>>
	<?php if(!$ope_auth_delete){ $disabled = "disabled"; }else{ $disabled = "";}?>
	<input class="btn red btn_small" type="button" value="完全に削除" onclick="action_archive_delete_multiple('archive_values'); return false;" <?php echo $disabled; ?>>
	</form>
    </div>
    <div class="clear"></div>
    </div>


<?php endif;  ?>

<?php $LayoutManager->footer(); ?>
