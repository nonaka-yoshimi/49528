<?php
// アプリケーション共通クラス読込
require_once(dirname(__FILE__).'/../../ApplicationCommon/include.php');
require_once(dirname(__FILE__).'/../../CMSCommon/include.php');
require_once(dirname(__FILE__).'/Common/LayoutManagerInfo.php');
require_once(dirname(__FILE__).'/DataAccess/InformationContent.php'); 	//お知らせコンテンツクラス
require_once(dirname(__FILE__).'/../../DataAccess/AddInfoSelect.php'); //選択肢クラス

//基本設定
$self = basename(__FILE__);				//自身のファイル名
$error = array();						//エラー情報格納用配列
$message = array();						//メッセージ情報格納用配列
$alert = array();						//アラート情報格納用配列
$redirect = "../../index.php";				//エラー時リダイレクト先
$restrict = array();					//表示・操作制限

$session = Session::get();

$session->loginCheckAndRedirect("../../login.php");

//権限取得
$ope_auth_publish = $session->user["ope_auth_page_publish"];
$ope_auth_edit = $session->user["ope_auth_page_edit"];
$ope_auth_delete = $session->user["ope_auth_page_delete"];

//お知らせコンテンツクラス
$Content = new InformationContent(InformationContent::TABLE_MANAGEMENT);

//選択肢クラス
$AddInfoSelect = new AddInfoSelect();

//リクエストパラメータ取得
$action = isset($_REQUEST["action"]) ? $_REQUEST["action"] : "";				//実行アクション
$content_id = isset($_REQUEST["content_id"]) ? $_REQUEST["content_id"] : "";	//コンテンツID
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

//検索条件設定
$where = array();
if($search_title_flg == "on"){
	$where["title"] = "%".$search_keyword."%";
}
if($search_content_flg == "on"){
	$where["content"] = "%".$search_keyword."%";
}
$contentList = $Content->getInformationList($where);

//削除済みコンテンツ一覧取得
if($search_flg != "on"){
	$deletedArchiveList = $Content->getDeletedArchiveListByParameters(array("contentclass" => "parts","folder_id" => 2));
}else{
	$deletedArchiveList = array();
}

//選択肢一覧を取得
/*
$where = array();
$where["selectname"] = "information_category";
$order = array();
$order["optionvalue"] = "ASC";
$informationCategoryList = $AddInfoSelect->getListByParameters($where,$order);
*/
/*
$where = array();
$where["selectname"] = "information_facility";
$order = array();
$order["optionvalue"] = "ASC";
$informationFacilityList = $AddInfoSelect->getListByParameters($where,$order);
*/
$LayoutManager = new LayoutManagerInfo();
$LayoutManager->setTitle("お知らせ管理");
$LayoutManager->header();

?>

<form action = "" id="control_values" style="margin-top: 10px;">
<input type="hidden" name="mode" />
<input type="hidden" name="close_url" />
<input type="hidden" name="contentclass" />
<input type="hidden" name="folder_id" />
<input type="hidden" name="search_title_flg" value="<?php echo htmlspecialchars($search_title_flg); ?>" />
<input type="hidden" name="search_content_flg" value="<?php echo htmlspecialchars($search_content_flg); ?>" />
<input type="hidden" name="search_keyword" value="<?php echo htmlspecialchars($search_keyword); ?>" />

	<div class="search">
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
		var action = "information_edit.php";
		$("*[name=mode]").val('new');
		$("*[name=close_url]").val('<?php echo $self; ?>');
		$('#control_values').attr({
		       'action':action,
		       'method':'get'
		     });
		$('#control_values').submit();
	});
	</script>
    </div>
</form>


<h3>お知らせ一覧</h3>

<div class="search">
	<form action="<?php echo $self; ?>" id="search_values">
	<input type="hidden" name="search_action" value="" />
	<input type="checkbox" name="search_title_flg" <?php if($search_title_flg == "on"){echo "checked"; }?> />タイトル
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
	<th>タイトル/区分</th>
	<th>お知らせ日付</th>
	<th>公開 / 期日</th>
	<th>作成 / 更新</th>
	<th>状態</th>
	<!--<th class="w80">プレビュー</th>-->
</tr>

<?php
for($i=0;$i < count($contentList);$i++){
	echo '<tr>'."\n";
	echo '<td><input type="checkbox" name="content_id[]" class="list_checkbox" value="'.$contentList[$i]["content_id"].'" ></td>'."\n";
	echo '<td>'.$contentList[$i]["content_id"].'</td>'."\n";
	echo '<td><a href="information_edit.php?mode=edit&content_id='.$contentList[$i]["content_id"].'&close_url='.$self.'&search_title_flg='.htmlspecialchars($search_title_flg).'&search_content_flg='.htmlspecialchars($search_content_flg).'&search_keyword='.htmlspecialchars($search_keyword).'">'.htmlspecialchars($contentList[$i]["title"]).'</a><br>'."\n";
	echo $contentList[$i]["information_type"]."\n：";
	echo $contentList[$i]["information_category"]."\n";
	echo '</td>'."\n";
	echo '<td style="white-space:nowrap">'.$contentList[$i]["date"].'</td>'."\n";
	if(Util::IsNullOrEmpty($contentList[$i]["schedule_publish"])){
		echo '<td style="white-space:nowrap">指定なし<br>'."\n";
	}else{
		echo '<td style="white-space:nowrap">'.date("Y/m/d",$contentList[$i]["schedule_publish"]).'<br>'."\n";
	}
	if(Util::IsNullOrEmpty($contentList[$i]["schedule_unpublish"])){
		echo '指定なし</td>'."\n";
	}else{
		echo date("Y/m/d",$contentList[$i]["schedule_unpublish"]).'</td>'."\n";
	}

	echo '<td style="white-space:nowrap">'.date("Y/m/d",$contentList[$i]["created"]).'<br>'."\n";
	echo date("Y/m/d",$contentList[$i]["updated"]).'</td>'."\n";

	echo '<td style="white-space:nowrap">'.ContentCommon::getContentStatusHTML($contentList[$i])."</td>\n";
	//echo '<td>';
	//$mode = Config::get("preview_mode");
	//if(!$mode){ $mode = "preview"; }
	//echo '<a href="/'.Config::BASE_DIR_PATH.'?mode='.$mode.'" target="_blank"><img src="../../img/screen_icon.png"></a>';
	//echo '</td>';
	echo '</tr>'."\n";
}
?>
</table>
</form>


<div class="search">
<div class="content_left">
<a class="btn btn_small" href="#" onclick="list_check_all('list_checkbox'); return false;">一括選択</a> / <a class="btn btn_small" href="#" onclick="list_clear_all('list_checkbox'); return false;">一括解除</a>
</div>
<div class="content_right">
<!--
<?php if(!$ope_auth_publish){ $disabled = "disabled"; }else{ $disabled = "";}?>
<input class="btn btn_small" type="button" value="選択を公開" onclick="action_publish_multiple('content_values'); return false;" <?php echo $disabled; ?>>
<?php if(!$ope_auth_publish){ $disabled = "disabled"; }else{ $disabled = "";}?>
<input class="btn btn_small" type="button" value="選択を非公開" onclick="action_unpublish_multiple('content_values'); return false;" <?php echo $disabled; ?>>
 -->
<?php if(!$ope_auth_delete){ $disabled = "disabled"; }else{ $disabled = "";}?>
<input class="btn red btn_small" type="button" value="選択を削除" onclick="action_delete_multiple('content_values'); return false;" <?php echo $disabled; ?>>
</div>
<div class="clear"></div>
</div>

<?php /*
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
*/ ?>

<?php $LayoutManager->footer(); ?>
