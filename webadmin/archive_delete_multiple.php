<?php
// アプリケーション共通クラス読込
require_once(dirname(__FILE__).'/ApplicationCommon/include.php');

require_once(dirname(__FILE__).'/DataAccess/Content.php'); 			//コンテンツクラス

//基本設定
$self = basename(__FILE__);				//自身のファイル名
$error = array();						//エラー情報格納用配列
$message = array();						//メッセージ情報格納用配列
$alert = array();						//アラート情報格納用配列
$redirect = "index.php";				//エラー時リダイレクト先
$restrict = array();					//表示・操作制限

$session = Session::get();
$session->loginCheckAndRedirect("login.php",array("admin_flg" => 1));

//パラメータ取得
$action = isset($_POST["action"]) ? $_POST["action"] : "";
$redirect_url = isset($_POST["redirect_url"]) ? $_POST["redirect_url"] : "";
$content_id_list = isset($_POST["content_id"]) ? $_POST["content_id"] : "";

if(!$content_id_list){
	Location::redirect($redirect_url);
}

//コンテンツクラス
$Content = new Content(Content::TABLE_ARCHIVE);
$where = array();
$where[] = array("content_archive_id",$content_id_list);
$contentList = $Content->getListByParameters($where,array("archive_time" => "desc"));

if($action == "send"){
	DB::beginTransaction();
	foreach($content_id_list as $content_archive_id){

		//コンテンツID取得
		$contentData = $Content->getDataByParameters(array("content_archive_id" => $content_archive_id));
		$content_id = $contentData["content_id"];
		if(!$content_id){
			continue;
		}

		//削除実行処理
		if(!$Content->deleteArchiveAll($content_id)){
			DB::rollBack();
			Logger::error("アーカイブ削除処理に失敗しました。content_id:".$content_id);
			Location::redirect($redirect);
		}else{
			$logparam = array();
			$logparam["content_id"] = $content_id;
			$logparam["content_archive_id"] = $content_archive_id;
			$logparam["user_id"] = $session->user["user_id"];
			$logparam["name"] = $session->user["name"];
			Logger::info("アーカイブ削除処理を行いました。",$logparam);
		}
	}
	DB::commit();
	Location::redirect($redirect_url);
}


$LayoutManager = new LayoutManagerAdminMain();
$LayoutManager->setTitle("アーカイブ削除（確認）");
$LayoutManager->setAlertList(array("一旦削除すると元に戻せません。本当に削除しますか？"));
$LayoutManager->header();
?>

<form action="<?php echo $redirect_url;?>" method="post">
<div class="search">
<input class="btn btn_small" type="submit" value="一覧に戻る"/>
</div>
</form>

<?php $LayoutManager->alert(); ?>

<table class="list">
<tr>
	<th class="w80">ID</th>
	<th>タイトル</th>
	<th>URL</th>
</tr>
<?php
foreach($contentList as $key => $value){
	echo '<tr>'."\n";
	echo '<td>'.$value["content_id"].'</td>'."\n";
	echo '<td>'.$value["title"].'</td>'."\n";
	echo '<td>'.$value["url"].'</td>'."\n";
	echo '</tr>'."\n";
}
?>
</table>

<form action="<?php echo $self;?>" method="post">
<input type="hidden" name="action" value="send" />
<input type="hidden" name="redirect_url" value="<?php echo $redirect_url; ?>" />
<?php
foreach($contentList as $key => $value){
	echo '<input type="hidden" name="content_id[]" value="'.$value["content_archive_id"].'">'."\n";
}
?>
<div class="search">
<input class="btn red btn_small" type="submit" value="削除する"/>
</div>
</form>


<?php $LayoutManager->footer(); ?>
