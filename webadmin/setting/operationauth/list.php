<?php
//アプリケーション共通クラス読込
require_once(dirname(__FILE__).'/../../ApplicationCommon/include.php');
require_once(dirname(__FILE__).'/../../DataAccess/OperationAuth.php'); 				//機能操作権限クラス

$session = Session::get();

//要素種別一覧を取得
$OperationAuth = new OperationAuth();
$operationauth_list = $OperationAuth->getListForSetting();

$LayoutManager = new LayoutManagerSetting();
$LayoutManager->setTitle("機能操作権限一覧");
$LayoutManager->header();
?>
<script>
<!--
$(function(){
	//高さの自動揃え
	$("#pageleft,#pageright").autoHeight({column:2});

	//左カラムのリサイズ可能化
	$("#pageleft").resizable({
		handles:"e,se",
		minWidth:200,
		stop: function(e, ui){
			//左カラムサイズ変更時処理
			//alert(ui.size.width);
		}
	});

	//ファイル一覧のドラッグドロップ可能化
	$(".filelist_body").sortable({
		items: '> tr:not(.filelist_title)',
		update: function(e, ui){
			//並び順変更時処理
			//行ID配列を取得
			var record_arr = $(".filelist_body").sortable('toArray');
			//接頭語を除去してID配列を作成
			for(var i = 0; i < record_arr.length ; i++){
				record_arr[i] = record_arr[i].replace(/operationauth\-/,"");
			}
			//AJAXで並び替え処理を呼び出し
			var url = "sort.php";
			var param = {id_list:record_arr};
			$.ajax({
				type: "POST",
				url: url,
				data: param,
				success: function(res){

				}
			});
		}
	});

	//新規追加ボタン押下時挙動設定
	$("#addnew_button").click(function(){
		window.location.href = 'edit.php?mode=new';
	});

	//ファイル行右クリック挙動設定
	$('.content').contextMenu('contentMenu', {
        //選択したメニューよってアラートを出します。
        bindings: {
            'edit': function(t) {
            	var operationauth_id = t.id.replace(/operationauth\-/,"");
            	window.location.href = 'edit.php?operationauth_id=' + operationauth_id + '&mode=edit';
            },
            'delete': function(t) {
            	var operationauth_id = t.id.replace(/operationauth\-/,"");
            	window.location.href = 'edit.php?operationauth_id=' + operationauth_id + '&mode=delete';
            }
        }
    });
});
// -->
</script>
<div id="pageleft">
<?php $LayoutManager->sideMenu(); ?>
<br style="clear:both;" />
</div>
<div id="pageright">
<input type="button" id="addnew_button" value="新規追加" />
<table class="filelist">
<tbody class="filelist_body">
<tr class="filelist_title">
<th>タイトル</th>
<th>更新日時</th>
</tr>
<?php
foreach($operationauth_list as $key => $value){
	echo '<tr class="content" id="operationauth-'.$value["operationauth_id"].'">';
	echo '<td><a href="edit.php?mode=edit&operationauth_id='.$value["operationauth_id"].'">'.htmlspecialchars($value["operationauth_name"]).'</a></td>';
	echo '<td>'.date("Y/m/d H:i",$value["updated"]).'</td>';
	echo '</tr>';
}
?>
</tbody>
</table>
</div>

<div class='contextMenu' id='contentMenu'>
    <ul>
        <li id='edit'><img src="/<?php echo Config::BASE_DIR_PATH.Config::ADMIN_DIR_PATH; ?>img/folder_mini.png" />編集</li>
        <li id='delete'><img src="/<?php echo Config::BASE_DIR_PATH.Config::ADMIN_DIR_PATH; ?>img/folder_mini.png" />削除</li>
    </ul>
</div>
<?php $LayoutManager->footer(); ?>
