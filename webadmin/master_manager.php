<?php
// アプリケーション共通クラス読込
require_once(dirname(__FILE__).'/ApplicationCommon/include.php');

require_once(dirname(__FILE__).'/DataAccess/Domain.php'); 			//ドメインクラス
require_once(dirname(__FILE__).'/DataAccess/Folder.php'); 			//フォルダクラス
require_once(dirname(__FILE__).'/DataAccess/AddInfoSelect.php'); 	//追加情報選択肢クラス
require_once(dirname(__FILE__).'/DataAccess/UserGroup.php'); 		//ユーザグループクラス
require_once(dirname(__FILE__).'/DataAccess/OperationAuth.php'); 	//機能操作権限クラス
require_once(dirname(__FILE__).'/DataAccess/WorkFlowState.php'); 	//ワークフロー状態クラス
require_once(dirname(__FILE__).'/DataAccess/WorkFlow.php'); 		//ワークフローアクションクラス

//基本設定
$self = basename(__FILE__);				//自身のファイル名
$error = array();						//エラー情報格納用配列
$message = array();						//メッセージ情報格納用配列
$alert = array();						//アラート情報格納用配列
$redirect = "index.php";				//エラー時リダイレクト先
$restrict = array();					//表示・操作制限

$session = Session::get();

$session->loginCheckAndRedirect("login.php",array("admin_flg" => 1));


//リクエストパラメータ取得
$action = isset($_REQUEST["action"]) ? $_REQUEST["action"] : "";									//実行アクション
$master_table = isset($_REQUEST["master_table"]) ? $_REQUEST["master_table"] : "";					//編集対象マスタ
$id = isset($_REQUEST["id"]) ? $_REQUEST["id"] : "";												//対象データID
$selectname = isset($_REQUEST["selectname"]) ? Util::encodeRequest($_REQUEST["selectname"]) : "";	//対象データ選択名
$sort = isset($_REQUEST["sort"]) ? $_REQUEST["sort"] : "";											//ソート方向

//マスタセレクト一覧
$masterList = array();
$masterList["addinfo_select"] = "選択肢マスタ";
$masterList["domain"] = "ドメインマスタ";
$masterList["folder"] = "フォルダマスタ";
$masterList["usergroup"] = "ユーザグループマスタ";
$masterList["operationauth"] = "機能操作権限マスタ";
$masterList["workflowstate"] = "ワークフロー状態マスタ";
$masterList["workflow"] = "ワークフローアクションマスタ";

if($master_table == ""){

}elseif($master_table == "addinfo_select"){

}elseif($master_table == "folder"){
	//ソート処理
	$Folder = new Folder();
	if($action == "sort" && $sort != ""){
		if($sort == "up"){
			$Folder->sortUp($id,$session->user["user_id"]);
		}else if($sort == "down"){
			$Folder->sortDown($id,$session->user["user_id"]);
		}
	}
}elseif($master_table == "usergroup"){
	//ソート処理
	$UserGroup = new UserGroup();
	if($action == "sort" && $sort != ""){
		if($sort == "up"){
			$UserGroup->sortUp($id,$session->user["user_id"]);
		}else if($sort == "down"){
			$UserGroup->sortDown($id,$session->user["user_id"]);
		}
	}
}elseif($master_table == "workflowstate"){
	//ソート処理
	$WorkFlowState = new WorkFlowState();
	if($action == "sort" && $sort != ""){
		if($sort == "up"){
			$WorkFlowState->sortUp($id,$session->user["user_id"]);
		}else if($sort == "down"){
			$WorkFlowState->sortDown($id,$session->user["user_id"]);
		}
	}
}elseif($master_table == "workflow"){
	//ソート処理
	$WorkFlow = new WorkFlow();
	if($action == "sort" && $sort != ""){
		if($sort == "up"){
			$WorkFlow->sortUp($id,$session->user["user_id"]);
		}else if($sort == "down"){
			$WorkFlow->sortDown($id,$session->user["user_id"]);
		}
	}
}

$LayoutManager = new LayoutManagerAdminMain();
if($master_table == ""){
	$LayoutManager->setTitle("マスタ設定");
}elseif($master_table == "addinfo_select"){
	$LayoutManager->setTitle("マスタ設定|選択肢管理");
}elseif($master_table == "domain"){
	$LayoutManager->setTitle("マスタ設定|ドメイン管理");
}elseif($master_table == "folder"){
	$LayoutManager->setTitle("マスタ設定|フォルダ管理");
}elseif($master_table == "usergroup"){
	$LayoutManager->setTitle("マスタ設定|ユーザグループ管理");
}elseif($master_table == "operationauth"){
	$LayoutManager->setTitle("マスタ設定|機能操作権限管理");
}elseif($master_table == "workflowstate"){
	$LayoutManager->setTitle("マスタ設定|ワークフロー状態");
}elseif($master_table == "workflow"){
	$LayoutManager->setTitle("マスタ設定|ワークフローアクション");
}
$LayoutManager->header();

?>
<form action="" id="values">
<select name="master_table" id="master_table_select" onChange="changeMasterTable($(this).val());">
<option value="" >--編集対象マスタを選択してください--</option>
<?php
foreach($masterList as $key => $value){
	if($key == $master_table){
		echo '<option value='.$key.' selected>'.$value.'</option>'."\n";
	}else{
		echo '<option value='.$key.'>'.$value.'</option>'."\n";
	}
}
?>
</select>
</form>
<script>
function changeMasterTable(value){
	$('#values').attr({
	       'action':'<?php echo $self; ?>',
	       'method':'get'
	     });
	$('#values').submit();
}
</script>

<?php if($master_table == "addinfo_select"): ?>
	<?php
	$AddInfoSelect = new AddInfoSelect();
	$addInfoSelectList = $AddInfoSelect->getSelectNameList();
	?>

	
	<form action = "" id="control_values" style="margin-top: 10px;">
	<input type="hidden" name="mode" />
	<input type="hidden" name="close_url" />
    <div class="search">
	<input class="btn btn_small" type="button" value="新規追加" id="action_new" />
    </div>
	</form>
	<script>
	$("#action_new").click(function(){
		var action = "addinfo_select_edit.php";
		$("*[name=mode]").val('new');
		$("*[name=close_url]").val('<?php echo $self; ?>');
		$('#control_values').attr({
		       'action':action,
		       'method':'get'
		     });
		$('#control_values').submit();
	});
	</script>
    

	<h3>選択肢カテゴリ一覧</h3>

	<table class="list">
	<tr>
		<th>選択肢カテゴリ名/定義名</th>
		<th>選択肢数</th>
		<th>作成 / 更新</th>
	</tr>
	<?php
	for($i=0;$i<count($addInfoSelectList);$i++){
		echo '<tr>';
		echo '<td><a href="addinfo_select_edit.php?mode=edit&close_url='.$self.'&selectname='.$addInfoSelectList[$i]["selectname"].'">'.$addInfoSelectList[$i]["selectname_display"].'</a><br>';
		echo $addInfoSelectList[$i]["selectname"].'<br></td>';
		echo '<td>'.$addInfoSelectList[$i]["num"].'</td>';
		echo '<td>'.date("Y/m/d",$addInfoSelectList[$i]["created"]).'<br>'."\n";
		echo date("Y/m/d",$addInfoSelectList[$i]["updated"]).'</td>'."\n";
		echo '</tr>';
	}
	?>
	</table>
<?php elseif($master_table == "domain"): ?>
	<?php
	$Domain = new Domain();
	$configList = $Domain->getListByParameters();
	?>
	<h3>ドメイン一覧</h3>
	<table class="list">
	<tr>
		<th>ドメイン管理名/ドメインURL</th>
		<th>作成 / 更新</th>
	</tr>
	<?php
	for($i=0;$i<count($configList);$i++){
		echo '<tr>';
		echo '<td><a href="domain_edit.php?mode=edit&close_url='.$self.'&domain_id='.$configList[$i]["domain_id"].'">'.$configList[$i]["domain_name"].'</a><br>';
		echo $configList[$i]["domain"].'<br></td>';
		echo '<td>'.date("Y/m/d",$configList[$i]["created"]).'<br>'."\n";
		echo date("Y/m/d",$configList[$i]["updated"]).'</td>'."\n";
		echo '</tr>';
	}
	?>
	</table>

<?php elseif($master_table == "folder"): ?>
	<?php
	$Folder = new Folder();
	$configList = $Folder->getListByParameters(array(),array("sort_no" => "asc"));
	?>

	<form action = "" id="control_values" style="margin-top: 10px;">
	<input type="hidden" name="mode" />
	<input type="hidden" name="close_url" />
    <div class="search">
	<input class="btn btn_small" type="button" value="新規追加" id="action_new" />
    </div>
	</form>
	<script>
	$("#action_new").click(function(){
		var action = "folder_edit.php";
		$("*[name=mode]").val('new');
		$("*[name=close_url]").val('<?php echo $self; ?>');
		$('#control_values').attr({
		       'action':action,
		       'method':'get'
		     });
		$('#control_values').submit();
	});
	</script>

	<h3>フォルダ一覧</h3>
	<table class="list">
	<tr>
		<th class="w80">ID</th>
		<th>フォルダ名/識別名</th>
		<th>一覧表示</th>
		<th>作成 / 更新</th>
		<th class="w80">並び順</th>
	</tr>
	<?php
	for($i=0;$i<count($configList);$i++){
		echo '<tr>';
		echo '<td>'.$configList[$i]["folder_id"]."</td>"."\n";
		echo '<td><a href="folder_edit.php?mode=edit&close_url='.$self.'&folder_id='.$configList[$i]["folder_id"].'">'.$configList[$i]["folder_name"].'</a><br>';
		echo $configList[$i]["folder_code"].'<br></td>';
		if($configList[$i]["list_display_flg"] == "1"){
			echo '<td>表示する</td>';
		}else{
			echo '<td>表示しない</td>';
		}
		echo '<td>'.date("Y/m/d",$configList[$i]["created"]).'<br>'."\n";
		echo date("Y/m/d",$configList[$i]["updated"]).'</td>'."\n";
		echo '<td>';
		echo '<a href="'.$self.'?master_table=folder&id='.$configList[$i]["folder_id"].'&action=sort&sort=up">▲</a>';
		echo '<br>';
		echo '<a href="'.$self.'?master_table=folder&id='.$configList[$i]["folder_id"].'&action=sort&sort=down">▼</a>';
		echo '</td>';
		echo '</tr>';
	}
	?>
	</table>

<?php elseif($master_table == "usergroup"): ?>
	<?php
	$UserGroup = new UserGroup();
	$configList = $UserGroup->getListByParameters(array(),array("sort_no" => "asc"));
	?>

	<form action = "" id="control_values" style="margin-top: 10px;">
	<input type="hidden" name="mode" />
	<input type="hidden" name="close_url" />
    <div class="search">
	<input class="btn btn_small" type="button" value="新規追加" id="action_new" />
    </div>
	</form>
	<script>
	$("#action_new").click(function(){
		var action = "usergroup_edit.php";
		$("*[name=mode]").val('new');
		$("*[name=close_url]").val('<?php echo $self; ?>');
		$('#control_values').attr({
		       'action':action,
		       'method':'get'
		     });
		$('#control_values').submit();
	});
	</script>

	<h3>ユーザグループ一覧</h3>
	<table class="list">
	<tr>
		<th class="w80">ID</th>
		<th>ユーザグループ名</th>
		<th>作成 / 更新</th>
		<th class="w80">並び順</th>
	</tr>
	<?php
	for($i=0;$i<count($configList);$i++){
		echo '<tr>';
		echo '<td>'.$configList[$i]["usergroup_id"]."</td>"."\n";
		echo '<td><a href="usergroup_edit.php?mode=edit&close_url='.$self.'&usergroup_id='.$configList[$i]["usergroup_id"].'">'.$configList[$i]["usergroup_name"].'</a></td>';
		echo '<td>'.date("Y/m/d",$configList[$i]["created"]).'<br>'."\n";
		echo date("Y/m/d",$configList[$i]["updated"]).'</td>'."\n";
		echo '<td>';
		echo '<a href="'.$self.'?master_table=usergroup&id='.$configList[$i]["usergroup_id"].'&action=sort&sort=up">▲</a>';
		echo '<br>';
		echo '<a href="'.$self.'?master_table=usergroup&id='.$configList[$i]["usergroup_id"].'&action=sort&sort=down">▼</a>';
		echo '</td>';
		echo '</tr>';
	}
	?>
	</table>
<?php elseif($master_table == "operationauth"): ?>
	<?php
	$OperationAuth = new OperationAuth();
	$configList = $OperationAuth->getListByParameters();
	?>

	<form action = "" id="control_values" style="margin-top: 10px;">
	<input type="hidden" name="mode" />
	<input type="hidden" name="close_url" />
    <div class="search">
	<input class="btn btn_small" type="button" value="新規追加" id="action_new" />
    </div>
	</form>
	<script>
	$("#action_new").click(function(){
		var action = "operationauth_edit.php";
		$("*[name=mode]").val('new');
		$("*[name=close_url]").val('<?php echo $self; ?>');
		$('#control_values').attr({
		       'action':action,
		       'method':'get'
		     });
		$('#control_values').submit();
	});
	</script>

	<h3>機能操作権限一覧</h3>
	<table class="list">
	<tr>
		<th class="w80">ID</th>
		<th>機能操作権限名</th>
		<th>作成 / 更新</th>
	</tr>
	<?php
	for($i=0;$i<count($configList);$i++){
		echo '<tr>';
		echo '<td>'.$configList[$i]["operationauth_id"]."</td>"."\n";
		echo '<td><a href="operationauth_edit.php?mode=edit&close_url='.$self.'&operationauth_id='.$configList[$i]["operationauth_id"].'">'.$configList[$i]["operationauth_name"].'</a></td>';
		echo '<td>'.date("Y/m/d",$configList[$i]["created"]).'<br>'."\n";
		echo date("Y/m/d",$configList[$i]["updated"]).'</td>'."\n";
		echo '</tr>';
	}
	?>
	</table>
<?php elseif($master_table == "workflowstate"): ?>
	<?php
	$WorkFlowState = new WorkFlowState();
	$configList = $WorkFlowState->getListByParameters(array(),array("sort_no" => "asc"));
	?>

	<form action = "" id="control_values" style="margin-top: 10px;">
	<input type="hidden" name="mode" />
	<input type="hidden" name="close_url" />
    <div class="search">
	<input class="btn btn_small" type="button" value="新規追加" id="action_new" />
    </div>
	</form>
	<script>
	$("#action_new").click(function(){
		var action = "workflowstate_edit.php";
		$("*[name=mode]").val('new');
		$("*[name=close_url]").val('<?php echo $self; ?>');
		$('#control_values').attr({
		       'action':action,
		       'method':'get'
		     });
		$('#control_values').submit();
	});
	</script>

	<h3>ワークフロー状態一覧</h3>
	<table class="list">
	<tr>
		<th class="w80">ID</th>
		<th>ワークフロー状態名</th>
		<th>作成 / 更新</th>
		<th class="w80">並び順</th>
	</tr>
	<?php
	for($i=0;$i<count($configList);$i++){
		echo '<tr>';
		echo '<td>'.$configList[$i]["workflowstate_id"]."</td>"."\n";
		echo '<td><a href="workflowstate_edit.php?mode=edit&close_url='.$self.'&workflowstate_id='.$configList[$i]["workflowstate_id"].'">'.$configList[$i]["workflowstate_name"].'</a></td>';
		echo '<td>'.date("Y/m/d",$configList[$i]["created"]).'<br>'."\n";
		echo date("Y/m/d",$configList[$i]["updated"]).'</td>'."\n";
		echo '<td>';
		echo '<a href="'.$self.'?master_table=workflowstate&id='.$configList[$i]["workflowstate_id"].'&action=sort&sort=up">▲</a>';
		echo '<br>';
		echo '<a href="'.$self.'?master_table=workflowstate&id='.$configList[$i]["workflowstate_id"].'&action=sort&sort=down">▼</a>';
		echo '</td>';
		echo '</tr>';
	}
	?>
	</table>
<?php elseif($master_table == "workflow"): ?>
	<?php
	$WorkFlow = new WorkFlow();
	$configList = $WorkFlow->getListByParameters(array("active_flg" => 1),array("sort_no" => "asc"));
	$WorkFlowState = new WorkFlowState();
	$workFlowStateList = $WorkFlowState->getListByParameters(array("active_flg" => 1));
	$workFlowStateDic = Util::getDicByIndexColumn($workFlowStateList, "workflowstate_id");
	?>

	<form action = "" id="control_values" style="margin-top: 10px;">
	<input type="hidden" name="mode" />
	<input type="hidden" name="close_url" />
    <div class="search">
	<input class="btn btn_small" type="button" value="新規追加" id="action_new" />
    </div>
	</form>
	<script>
	$("#action_new").click(function(){
		var action = "workflow_edit.php";
		$("*[name=mode]").val('new');
		$("*[name=close_url]").val('<?php echo $self; ?>');
		$('#control_values').attr({
		       'action':action,
		       'method':'get'
		     });
		$('#control_values').submit();
	});
	</script>

	<h3>ワークフローアクション一覧</h3>
	<table class="list">
	<tr>
		<th class="w80">ID</th>
		<th>アクション名</th>
		<th>状態FROM</th>
		<th>状態TO</th>
		<th>作成 / 更新</th>
		<th class="w80">並び順</th>
	</tr>
	<?php
	for($i=0;$i<count($configList);$i++){
		echo '<tr>';
		echo '<td>'.$configList[$i]["workflow_id"]."</td>"."\n";
		echo '<td><a href="workflow_edit.php?mode=edit&close_url='.$self.'&workflow_id='.$configList[$i]["workflow_id"].'">'.$configList[$i]["workflow_name"].'</a></td>';
		echo '<td>'."\n";
		echo isset($workFlowStateDic[$configList[$i]["workflowstate_from_id"]]) ? $workFlowStateDic[$configList[$i]["workflowstate_from_id"]]["workflowstate_name"] : "";
		echo '</td>'."\n";
		echo '<td>'."\n";
		echo isset($workFlowStateDic[$configList[$i]["workflowstate_to_id"]]) ? $workFlowStateDic[$configList[$i]["workflowstate_to_id"]]["workflowstate_name"] : "";
		echo '</td>'."\n";
		echo '<td>'.date("Y/m/d",$configList[$i]["created"]).'<br>'."\n";
		echo date("Y/m/d",$configList[$i]["updated"]).'</td>'."\n";
		echo '<td>';
		echo '<a href="'.$self.'?master_table=workflow&id='.$configList[$i]["workflow_id"].'&action=sort&sort=up">▲</a>';
		echo '<br>';
		echo '<a href="'.$self.'?master_table=workflow&id='.$configList[$i]["workflow_id"].'&action=sort&sort=down">▼</a>';
		echo '</td>';
		echo '</tr>';
	}
	?>
	</table>

<?php endif; ?>

<?php $LayoutManager->footer(); ?>
