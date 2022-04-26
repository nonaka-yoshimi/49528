<?php
// アプリケーション共通クラス読込
require_once(dirname(__FILE__).'/ApplicationCommon/include.php');

require_once(dirname(__FILE__).'/DataAccess/User.php'); 			//ユーザクラス

//基本設定
$self = basename(__FILE__);				//自身のファイル名
$error = array();						//エラー情報格納用配列
$message = array();						//メッセージ情報格納用配列
$alert = array();						//アラート情報格納用配列
$redirect = "index.php";				//エラー時リダイレクト先
$restrict = array();					//表示・操作制限

$session = Session::get();

$session->loginCheckAndRedirect("login.php",array("admin_flg" => 1));

$User = new User();

//リクエストパラメータ取得
$action = isset($_REQUEST["action"]) ? $_REQUEST["action"] : "";				//実行アクション
$user_id = isset($_REQUEST["user_id"]) ? $_REQUEST["user_id"] : "";				//ユーザID
$sort = isset($_REQUEST["sort"]) ? $_REQUEST["sort"] : "";						//ソート方向

//ソート処理
if($action == "sort" && $sort != ""){
	if($sort == "up"){
		$User->sortUp($user_id,$session->user["user_id"]);
	}else if($sort == "down"){
		$User->sortDown($user_id,$session->user["user_id"]);
	}
}


$userList = $User->getUserListForEdit();

$LayoutManager = new LayoutManagerAdminMain();
$LayoutManager->setTitle("ユーザ管理");
$LayoutManager->header();

?>

<form action = "" id="control_values" style="margin-top: 10px;">
<input type="hidden" name="mode" />
<input type="hidden" name="close_url" />
<input type="hidden" name="contentclass" />
<input type="hidden" name="folder_id" />
<div class="search">
<input class="btn btn_small" type="button" value="新規追加" id="action_new" />
</div>
</form>
<script>
$("#action_new").click(function(){
	var action = "user_edit.php";
	$("*[name=mode]").val('new');
	$("*[name=close_url]").val('<?php echo $self; ?>');
	$('#control_values').attr({
	       'action':action,
	       'method':'get'
	     });
	$('#control_values').submit();
});
</script>

<h3>ユーザ一覧</h3>

<table class="list">
<tr>
	<th>氏名 / カナ</th>
	<th>ユーザグループ</th>
	<th>ログインID</th>
	<th>作成 / 更新</th>
	<th>状態</th>
	<th class="w80">並び順</th>
</tr>

<?php
for($i=0;$i < count($userList);$i++){
	echo '<tr>'."\n";
	echo '<td><a href="user_edit.php?mode=edit&user_id='.$userList[$i]["user_id"].'&close_url='.$self.'">'.htmlspecialchars($userList[$i]["name"]).'</a><br>'."\n";
	echo $userList[$i]["name_kana"]."\n";
	echo '</td>'."\n";

	echo '<td>'.htmlspecialchars($userList[$i]["usergroup_name"])."\n";
	echo '<td>'.htmlspecialchars($userList[$i]["login_id"])."\n";

	echo '<td>'.date("Y/m/d",$userList[$i]["created"]).'<br>'."\n";
	echo date("Y/m/d",$userList[$i]["updated"]).'</td>'."\n";

	if($userList[$i]["active_flg"] == "1"){
		echo '<td><span class="mark green">有効</span></td>'."\n";
	}else{
		echo '<td><span class="mark red">無効</span></td>'."\n";
	}

	echo '<td>';
	echo '<a href="'.$self.'?user_id='.$userList[$i]["user_id"].'&action=sort&sort=up">▲</a>';
	echo '<br>';
	echo '<a href="'.$self.'?user_id='.$userList[$i]["user_id"].'&action=sort&sort=down">▼</a>';
	echo '</td>';
	echo '</tr>'."\n";
}

?>

</table>
<?php $LayoutManager->footer(); ?>
