<?php
// アプリケーション共通クラス読込
require_once(dirname(__FILE__).'/../../ApplicationCommon/include.php');
require_once(dirname(__FILE__).'/../../CMSCommon/include.php');
require_once(dirname(__FILE__).'/../../DataAccess/AddInfoSelect.php'); 			//選択肢クラス
require_once(dirname(__FILE__).'/Common/LayoutManagerVoice.php');				//患者様の声レイアウトマネージャクラス
require_once(dirname(__FILE__).'/DataAccess/VoiceContent.php'); 			//患者様の声コンテンツクラス

//基本設定
$self = basename(__FILE__);				//自身のファイル名
$error = array();						//エラー情報格納用配列
$message = array();						//メッセージ情報格納用配列
$alert = array();						//アラート情報格納用配列
$redirect = "../../index.php";			//エラー時リダイレクト先
$restrict = array();					//表示・操作制限

$session = Session::get();

$session->loginCheckAndRedirect("../../login.php");

//権限取得
$ope_auth_publish = $session->user["ope_auth_page_publish"];
$ope_auth_edit = $session->user["ope_auth_page_edit"];
$ope_auth_delete = $session->user["ope_auth_page_delete"];

//コンテンツクラス読込
$VoiceContent = new VoiceContent();

//選択肢クラス
$AddInfoSelect = new AddInfoSelect();

//リクエストパラメータ取得
$message = isset($_REQUEST["message"]) ? $_REQUEST["message"] : $message;			//メッセージ
$action = isset($_REQUEST["action"]) ? $_REQUEST["action"] : "";				//実行アクション
$id = isset($_REQUEST["id"]) ? $_REQUEST["id"] : "";	//コンテンツID

//データ一覧取得
$where = array();
$where["del_flg"] = 0;
$order = array();
$order["date_year"] = "DESC";
$order["date_month"] = "DESC";
$VoiceContentList = $VoiceContent->getListByParameters($where,$order);


//選択肢一覧を取得
$where = array();
$where["selectname"] = "voice_age";
$order = array();
$order["optionvalue"] = "ASC";
$voiceAgeList = $AddInfoSelect->getListByParameters($where,$order);

//削除処理
if($action == "delete"){
	if($id){
		$where = array();
		$message = array();
		$count = count($id);
		$i = 0;
		DB::beginTransaction();
		//論理削除
		$param["del_flg"] = 1;
		foreach($id as $val){
			$where["id"] = $val;
			$result = $VoiceContent->update($where,$param);
			if($result){
				$i++;
			}
		}
		if($count == $i){
			DB::commit();
			$redirectParam["message[]"] = "コンテンツを削除しました。";
			Location::redirect($self,$redirectParam);
		}else{
			DB::rollBack();
			Logger::error("コンテンツを削除できませんでした。");
			$message[] = "コンテンツを削除できませんでした。";
		}
	}else{
		$message[] = "削除するコンテンツを選択してください。";
	}
}


//選択を公開
if($action == "open"){
	if($id){
		$where = array();
		$message = array();
		$count = count($id);
		$i = 0;
		DB::beginTransaction();

		$param["status"] = 0;
		foreach($id as $val){
			$where["id"] = $val;
			$result = $VoiceContent->update($where,$param);
			if($result){
				$i++;
			}
		}
		if($count == $i){
			DB::commit();
			Location::redirect($self,$redirectParam);
		}else{
			DB::rollBack();
			Logger::error("公開できません。");
			$message[] = "公開できません。";
		}
	}else{
		$message[] = "公開するコンテンツを選択してください。";
	}
}

//選択を非公開
if($action == "close"){
	if($id){
		$where = array();
		$message = array();
		$count = count($id);
		$i = 0;
		DB::beginTransaction();

		$param["status"] = 1;
		foreach($id as $val){
			$where["id"] = $val;
			$result = $VoiceContent->update($where,$param);
			if($result){
				$i++;
			}
		}
		if($count == $i){
			DB::commit();
			Location::redirect($self,$redirectParam);
		}else{
			DB::rollBack();
			Logger::error("非公開にできませんでした。");
			$message[] = "非公開にできませんでした。";
		}
	}else{
		$message[] = "非公開にするコンテンツを選択してください。";
	}
}




$LayoutManager = new LayoutManagerVoice();
$LayoutManager->setTitle("患者様の声管理");
$LayoutManager->setMessageList($message);
$LayoutManager->header();
?>

<!-- メッセージ出力開始 -->
<?php echo $LayoutManager->alert(); 	//アラート出力 ?>
<?php echo $LayoutManager->error(); 	//エラー出力 ?>
<?php echo $LayoutManager->message(); 	//メッセージ出力 ?>
<!-- メッセージ出力終了 -->

<form action = "" id="control_values" style="margin-top: 10px;">
<input type="hidden" name="mode" />
<input type="hidden" name="close_url" />
	<div class="search">
	<input class="btn btn_small" type="button" value="新規追加" id="action_new" />

	<script>
	$(function () {
		$("#action_new").click(function(){
			var action = "voice_edit.php";
			$("*[name=mode]").val('new');
			$("*[name=close_url]").val('<?php echo $self; ?>');
			$('#control_values').attr({
			'action':action,
			'method':'get'
		});
			$('#control_values').submit();
		});

		$("#action_delete").click(function(){
			var res = confirm("選択を削除しますか？");
			// 選択結果で分岐
			if( res == true ) {
			var self = "<?php echo $self; ?>";
			$("*[name=action]").val('delete');
			$("#content_values").append("<input type='hidden' name='action' value='delete' />");
			$('#content_values').attr({
			'action':self,
			'method':'post'
			});
				$('#content_values').submit();
			}else{
				alert("キャンセルしました");
			}
		});
		
		$("#action_open").click(function(){
			var res = confirm("選択を公開しますか？");//ダイアログ
			// 選択結果で分岐
			if( res == true ) {
			var self = "<?php echo $self; ?>";//自ファイル名
			$("*[name=action]").val('open');
			$("#content_values").append("<input type='hidden' name='action' value='open' />");
			$('#content_values').attr({
			'action':self,
			'method':'post'
			});
				$('#content_values').submit();
			}else{
				alert("キャンセルしました");
			}
		});

		$("#action_close").click(function(){
			var res = confirm("選択を非公開にしますか？");//ダイアログ
			// 選択結果で分岐
			if( res == true ) {
			var self = "<?php echo $self; ?>";//自ファイル名
			$("*[name=action]").val('close');
			$("#content_values").append("<input type='hidden' name='action' value='close' />");
			$('#content_values').attr({
			'action':self,
			'method':'post'
			});
				$('#content_values').submit();
			}else{
				alert("キャンセルしました");
			}
		});
	});
	</script>
	</div>
</form>

<h3>患者様の声一覧</h3>

<div class="search">
<div class="content_left">
<a class="btn btn_small" href="#" onclick="list_check_all('list_checkbox'); return false;">一括選択</a> / <a class="btn btn_small" href="#" onclick="list_clear_all('list_checkbox'); return false;">一括解除</a>
</div>
<div class="content_right">
<input class="btn btn_small" type="button" value="選択を公開" id="action_open" />
<input class="btn btn_small" type="button" value="選択を非公開" id="action_close" />
<input class="btn red btn_small" type="button" value="選択を削除" id="action_delete" />
</div>
<div class="clear"></div>
</div>

<form action = "" id="content_values" style="margin-top: 10px;">
<table class="list">
<tr>
	<th>選択</th>
	<th>ID</th>
	<th class="w400">コメント内容</th>
	<th class="w160">コメント日付</th>
	<th>年代</th>
	<th>性別</th>
	<th>作成日／更新日</th>
	<th>状態</th>
</tr>
<?php
for($i=0;$i < count($VoiceContentList);$i++){
	echo '<tr>'."\n";
	echo '<td><input type="checkbox" name="id[]" class="list_checkbox" value="'.$VoiceContentList[$i]["id"].'" ></td>'."\n";
	echo '<td>'.$VoiceContentList[$i]["id"].'</td>'."\n";
	echo '<td><a href="voice_edit.php?mode=edit&id='.$VoiceContentList[$i]["id"].'&close_url='.$self.'">'.$VoiceContentList[$i]["comment"].'</a></td>'."\n";
	echo '<td>'.$VoiceContentList[$i]["date_year"].'年'.$VoiceContentList[$i]["date_month"].'月</td>'."\n";
	foreach ($voiceAgeList as $val) {
		if($VoiceContentList[$i]["age"] == $val["optionvalue"]){
			echo '<td>'.$val["optionvalue_name"].'代</td>'."\n";
		}
	}
	if($VoiceContentList[$i]["gender"] == 0){
		echo '<td>'."男性".'</td>'."\n";
	}else{
		echo '<td>'."女性".'</td>'."\n";
	}
	//echo '<td>'.date("Y/m/d",$VoiceContentList[$i]["create"])."<br>".date("Y/m/d",$VoiceContentList[$i]["renewal"]).'</td>'."\n";
	echo '<td>'.$VoiceContentList[$i]["create"]."<br>".$VoiceContentList[$i]["renewal"].'</td>'."\n";
	if($VoiceContentList[$i]["status"] == 0){
		echo '<td>'."公開中".'</td>'."\n";
	}else{
		echo '<td>'."非公開".'</td>'."\n";
	}
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
<input class="btn btn_small" type="button" value="選択を公開" id="action_open" />
<input class="btn btn_small" type="button" value="選択を非公開" id="action_close" />
<input class="btn red btn_small" type="button" value="選択を削除" id="action_delete" />
</div>
<div class="clear"></div>
</div>


<?php $LayoutManager->footer(); ?>
