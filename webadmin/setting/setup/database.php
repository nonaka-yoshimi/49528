<?php
// アプリケーション共通クラス読込
require_once(dirname(__FILE__).'/../../ApplicationCommon/include.php');

$session = Session::get();

$LayoutManager = new LayoutManagerSetting();
$LayoutManager->setTitle("ユーザ管理");
$LayoutManager->header();
?>
<style type="text/css">
#pagebody #pageleft{
	width:250px;
	border-right:none;
}
#pagebody #pageright{
	margin: 0 0 0 250px;
}
</style>
<script>
<!--
$(function(){

	$("#tabs").tabs({
		selected: 1 //コンテンツタブをデフォルトにする
	});

	//高さの自動揃え
	//$("#pageleft,#pageright").autoHeight({column:2});

	$(".new_menu li").hover(function() {
		$(this).children('ul').show();
	}, function() {
		$(this).children('ul').hide();
	});

	//初期化処理

	//ディレクトリ情報取得処理
	//get_dir();

	//フォルダ選択処理
	//folder_select("folder-1");

});

//生成したディレクトリに機能を追加する
function dir_function_bind(){
	//[+][-]ボタンクリック時の挙動設定
	$("img").click(function(){
		if($(this).attr("class") == "plus"){
			$(this).attr("src","../img/folder_minus.png");
			$(this).attr("class","minus");
			$(this).parent("li").children("ul").css("display", "block");
		}else if($(this).attr("class") == "minus"){
			$(this).attr("src","../img/folder_plus.png");
			$(this).attr("class","plus");
			$(this).parent("li").children("ul").css("display", "none");
		}
	});

	//フォルダ名クリックイベント設定
	$(".folder > a").click(function(){
		var folder_id = $(this).parent("li").attr("id");
		folder_select(folder_id);
		return false;
	});
}

//フォルダ選択時処理
function folder_select(folder_id){
	if(folder_id.match(/folder\-/)){
		folder_id = folder_id.replace(/folder\-/,"");
		var url = "../api/filelist.php";
		var param = "folder_id="+folder_id;

		$.ajax({
			type: "GET",
			url: url,
			data: param,
			success: function(res){
				var list = res.list;
				$(".filelist_body").empty();
				setFileListTitle();
				for(var i=0;i<list.length;i++){
					$(".filelist_body").append('<tr class="content"><td><input type="checkbox" /></td><td><img src="../img/folder_mini.png" /></td><td><a href="edit.php?content_id='+list[i]["content_id"]+'">'+escapeHTML(list[i]["title"])+"</a></td><td>"+list[i]["updated"]+"</td><td>"+list[i]["contentclass"]+"</td></tr>");
				}

				//高さの自動揃え
				$("#pageleft,#pageright").autoHeight({column:2});

				//右クリック挙動設定
				$('.filelist_title').contextMenu('filelisttitleMenu', {
			        //選択したメニューよってアラートを出します。
			        bindings: {
			            'setting': function(t) {
			            alert('idは'+t.id+'です。\nOpenしました。');
			            },
			            'delete': function(t) {
			            alert('idは'+t.id+'です。\nEmailを起動。');
			            },
			            'save': function(t) {
			            alert('idは'+t.id+'です。\nSave（保存）。');
			            },
			            'delete': function(t) {
			            alert('idは'+t.id+'です。\n（閉じる）。');
			            }
			      }
			    });

				//ファイル行右クリック挙動設定
				$('.content').contextMenu('contentMenu', {
			        //選択したメニューよってアラートを出します。
			        bindings: {
			            'setting': function(t) {
			            alert('idは'+t.id+'です。\nOpenしました。');
			            },
			            'delete': function(t) {
			            alert('idは'+t.id+'です。\nEmailを起動。');
			            },
			            'save': function(t) {
			            alert('idは'+t.id+'です。\nSave（保存）。');
			            },
			            'delete': function(t) {
			            alert('idは'+t.id+'です。\n（閉じる）。');
			            }
			      }
			    });

			}
		});
	}
}


function setFileListTitle(){
	$(".filelist_body").append('<tr class="filelist_title"><th style="width:20px;">&nbsp</th><th style="width:17px;">&nbsp;</th><th style="width:200px;">タイトル</th><th>更新日時</th><th>種類</th></tr>');
}


// -->
</script>
<div id="pageleft">
<?php $LayoutManager->sideMenu(); ?>

<!--
<br><br>
<ul class="dirtree" id="domain1">
	<li class="" id="folder-1"><img src="../img/folder_plus.png" class="plus" />要素1</li>
	<li class="" id="folder-2"><img src="../img/folder_minus.png" class="minus" />要素2
		<ul class="dirtree">
			<li id="folder-3"><img src="../img/folder_plus.png" class="plus" /><a href="">配下1</a></li>
			<li id="folder-4"><img src="../img/folder_plus.png" class="plus" />配下2</li>
		</ul>
	</li>
	<li class="" id="folder-5"><img src="../img/folder_minus.png" class="minus" />要素3
		<ul class="dirtree">
		<li id="folder-6"><img src="../img/folder_plus.png" class="plus" />配下1</li>
		<li id="folder-7"><img src="../img/folder_minus.png" class="minus" />配下2
			<ul class="dirtree">
				<li id="folder-8"><img src="../img/folder_plus.png" class="plus" />配下1</li>
				<li id="folder-9"><img src="../img/folder_empty.png" class="empty" />配下2</li>
			</ul>
		</li>
		</ul>
	</li>
</ul>
<input type="button" value="並び順を確定" id="sort_fix"  name="domain1" />
<br><br>
<ul class="dirtree" id="domain1">
	<li class="" id="folder-1"><img src="../img/folder_plus.png" class="plus" />要素1</li>
	<li class="" id="folder-2"><img src="../img/folder_minus.png" class="minus" />要素2
		<ul class="dirtree">
			<li id="folder-3"><img src="../img/folder_plus.png" class="plus" /><a href="">配下1</a></li>
			<li id="folder-4"><img src="../img/folder_plus.png" class="plus" />配下2</li>
		</ul>
	</li>
	<li class="" id="folder-5"><img src="../img/folder_minus.png" class="minus" />要素3
		<ul class="dirtree">
		<li id="folder-6"><img src="../img/folder_plus.png" class="plus" />配下1</li>
		<li id="folder-7"><img src="../img/folder_minus.png" class="minus" />配下2
			<ul class="dirtree">
				<li id="folder-8"><img src="../img/folder_plus.png" class="plus" />配下1</li>
				<li id="folder-9"><img src="../img/folder_empty.png" class="empty" />配下2</li>
			</ul>
		</li>
		</ul>
	</li>
</ul>
<input type="button" value="並び順を確定" id="sort_fix"  name="domain1" />
-->
<br style="clear:both;" />

</div>
<div id="pageright">
<div id="tabs">
<!--
<table class="edit_control">
<tr>
<td>
<input type="button" value="設定を保存する" id="back_to_list" />
</td>
</tr>
</table>
<br />
-->

<ul style="height:34px">
	<li><a href="#tabs-1" class="tabmenu">データベース初期化</a></li>
</ul>

<!--
<div id="tabs-1" class="tab_area">
<h1>データベース接続</h1>
<div class="status">
あああ
</div>
<table class="setting_table">
<tr>
<th>データベース</th>
<td>&nbsp;<input type="radio" name="editmode" />MySQL&nbsp;<input type="radio" name="editmode" />Oracle&nbsp;<input type="radio" name="editmode" />SQLServer</td>
</tr>
<tr>
<th>DSN</th>
<td><input type="text" name="" class="input_long" /></td>
</tr>
<tr>
<th>ユーザ名</th>
<td><input type="text" name="" /></td>
</tr>
<tr>
<th>パスワード</th>
<td><input type="text" name="" /></td>
</tr>
</table>
<br />
<input type="button" value="データベース接続"  />
</div>
-->

<div id="tabs-1" class="tab_area">
<h1>データベース初期化</h1>
<input type="button" value="データベース初期化実行"  />
</div>

<!--
<div id="tabs-3" class="tab_area">
<h1>データベース設定操作ユーザ</h1>
<table class="setting_table">
<tr>
<th>有効/無効</th>
<td>&nbsp;<input type="radio" name="editmode" />有効&nbsp;<input type="radio" name="editmode" />無効</td>
</tr>
<tr>
<th>ユーザ名</th>
<td><input type="text" name="" /></td>
</tr>
<tr>
<th>パスワード</th>
<td><input type="text" name="" /></td>
</tr>
</table>
</div>
-->

</div>



</div>

<?php $LayoutManager->footer(); ?>
