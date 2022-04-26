<?php
// アプリケーション共通クラス読込
require_once(dirname(__FILE__).'/../ApplicationCommon/include.php');

$session = Session::get();

$LayoutManager = new LayoutManagerAdminMain();
$LayoutManager->setTitle("ユーザ管理");
$LayoutManager->header();
?>
<style type="text/css">

.filelistcontrol{
	width:100%;
	clear:right;
}

.filelistcontrol table {
	margin:0;
	padding:0;
	font-size:12px;
	float:right;
	line-height:20px;
}

.filelist {
	margin:0;
	padding:0;
	clear:right;
}

.filelist th {
	margin:0;
	padding:2px;
	color: #333333;
	background: #eeeeee;
	text-align:left;
	font-size:12px;
	height:18px;
}

.filelist td {
	font-size:14px;
}

.filelist input {
	margin:0 5px 0 5px;
}

.filelist a {
	margin:0 5px 0 0;
}

.fileupload_table{
	border:1px dotted #000;
	width:60%;
	height:200px;
}

.new_menu li {
  position: relative;
  float: left;
  margin: 0;
  padding: 0 0 5px 0;
  width: 100px;
  height: 15px;
  border: solid 1px #ccc;
  font-weight: bold;
  list-style:none;
  font-size:15px;
  text-align:center;
}
.new_menu li:hover {
  color: #fff;
  background: #333;
}
.new_menu li ul {
  display: none;
  position: absolute;
  top: 20px;
  left: -1px;
  padding: 0 0 5px 0;
  width: 100px;
  background: #fff;
  border: solid 1px #ccc;
}
.new_menu li ul li {
  margin: 0;
  padding: 0 0 5px 0;
  width: 100px;
  border: none;
  text-align:left;
}
.new_menu li ul li a {
  display: inline-block;
  text-decoration:none;
  font-weight:normal;
  font-size:14px;
  width: 100px;
  height: 20px;
  color:#000;
}
.new_menu li ul li a:hover {
  background: #999;
  color: #fff;
}

</style>
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
		cancel: ".filelist_title",
		update: function(e, ui){
			//左カラムサイズ変更時処理
			$("#sort_fix").show();
		}
	});

	//ファイル一覧列のリサイズ可能化
	$(".filelist th").resizable({
		handles:"e",
		stop: function(e, ui){
			//左カラムサイズ変更時処理
			//alert(ui.size.width);
		}
	});

	$(".new_menu li").hover(function() {
		$(this).children('ul').show();
	}, function() {
		$(this).children('ul').hide();
	});

	//初期化処理

	//ディレクトリ情報取得処理
	get_usergroup_dir();

	//フォルダ選択処理
	//folder_select("folder-1");

});

//生成したディレクトリに機能を追加する
function usergroup_function_bind(){
	//ディレクトリのドラッグドロップ可能化
	$(".dirtree").sortable({
		update: function(e, ui){
			//左カラムサイズ変更時処理
			$("#sort_fix").show();
		}
	});

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

	//フォルダ右クリック挙動設定
	$('.domain').contextMenu('domainMenu', {
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

	//フォルダ右クリック挙動設定
	$('.folder').contextMenu('folderMenu', {
        //選択したメニューよってアラートを出します。
        bindings: {
        	'new': function(t) {
            	var folder_id = t.id.replace(/usergroup\-/,"");
            	window.location.href = 'usergroup.php?usergroup_id=' + folder_id + '&mode=new';
            },
            'edit': function(t) {
            	var folder_id = t.id.replace(/usergroup\-/,"");
            	window.location.href = 'usergroup.php?usergroup_id=' + folder_id + '&mode=edit';
            },
            'delete': function(t) {
            	var folder_id = t.id.replace(/usergroup\-/,"");
            	window.location.href = 'usergroup.php?usergroup_id=' + folder_id + '&mode=delete';
            }
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
	if(folder_id.match(/usergroup\-/)){
		folder_id = folder_id.replace(/usergroup\-/,"");
		//選択中のフォルダIDをhiddenタグに保存
		$("#userlist_usergroup_id").val(folder_id);

		var url = "../api/userlist.php";
		var param = "usergroup_id="+folder_id;

		$.ajax({
			type: "GET",
			url: url,
			data: param,
			success: function(res){
				var list = res.list;
				$(".filelist_body").empty();
				setFileListTitle();
				for(var i=0;i<list.length;i++){
					$(".filelist_body").append('<tr class="content" id="user-'+list[i]["user_id"]+'"><td><input type="checkbox" /></td><td><img src="../img/folder_mini.png" /></td><td><a href="edit.php?mode=edit&user_id='+list[i]["user_id"]+'&usergroup_id='+folder_id+'">'+escapeHTML(list[i]["name"])+"</a></td><td>"+list[i]["updated"]+"</td></tr>");
				}

				//高さの自動揃え
				$("#pageleft,#pageright").autoHeight({column:2});

				//新規追加ボタン押下時挙動設定
				$("#useradd_button").click(function(){
					var usergroup_id = $("#userlist_usergroup_id").val();
					window.location.href = 'edit.php?mode=new&usergroup_id='+usergroup_id;
				});

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
			            'edit': function(t) {
			            	var user_id = t.id.replace(/user\-/,"");
			            	var usergroup_id = $("#userlist_usergroup_id").val();
			            	window.location.href = 'edit.php?user_id=' + user_id + '&mode=edit&usergroup_id='+usergroup_id;
			            },
			            'delete': function(t) {
			            	var user_id = t.id.replace(/user\-/,"");
			            	var usergroup_id = $("#userlist_usergroup_id").val();
			            	window.location.href = 'edit.php?user_id=' + user_id + '&mode=delete&usergroup_id='+usergroup_id;
			            }
			      }
			    });

			}
		});
	}
}


function setFileListTitle(){
	$(".filelist_body").append('<tr class="filelist_title"><th style="width:20px;">&nbsp</th><th style="width:17px;">&nbsp;</th><th style="width:200px;">タイトル</th><th>更新日時</th></tr>');
}


// -->
</script>
<div id="pageleft">

<!--
<ul class="dirtree" id="domain1">
	<li class="directory" id="folder-1"><img src="../img/folder_plus.png" class="plus" />○○株式会社公式ホームページ</li>
	<li class="directory" class="" id="folder-2"><img src="../img/folder_minus.png" class="minus" />要素2
		<ul class="dirtree">
			<li class="directory" id="folder-3"><img src="../img/folder_plus.png" class="plus" /><a href="">配下1</a></li>
			<li class="directory" id="folder-4"><img src="../img/folder_plus.png" class="plus" />配下2</li>
		</ul>
	</li>
	<li class="directory" class="" id="folder-5"><img src="../img/folder_minus.png" class="minus" />要素3
		<ul class="dirtree">
		<li class="directory" id="folder-6"><img src="../img/folder_plus.png" class="plus" />配下1</li>
		<li class="directory" id="folder-7"><img src="../img/folder_minus.png" class="minus" />配下2
			<ul class="dirtree">
				<li class="directory" id="folder-8"><img src="../img/folder_plus.png" class="plus" />配下1</li>
				<li class="directory" id="folder-9"><img src="../img/folder_empty.png" class="empty" />配下2</li>
			</ul>
		</li>
		</ul>
	</li>
</ul>
<input type="button" value="並び順を確定" id="sort_fix"  name="domain1" />
-->
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
<input type="button" id="useradd_button" value="新規追加" />
<input type="hidden" id="userlist_usergroup_id" value="" />
<table class="filelist">
<tbody class="filelist_body">
<tr class="filelist_title">
<th>タイトル</th>
<th>更新日時</th>
<th>種類</th>
<th>サイズ</th>
</tr>
<tr>
<td>ああああああ</td>
<td>2013/12/03 14:13</td>
<td>ページ</td>
<td>124kb</td>
</tr>
<tr>
<td>いいいいいい</td>
<td>2013/12/03 14:13</td>
<td>ページ</td>
<td>124kb</td>
</tr>
</tbody>
</table>


</div>




<div class='contextMenu' id='folderMenu'>
    <ul>
    	<li id='new'><img src="../img/setting_mini.png" />新規グループ追加</li>
        <li id='edit'><img src="../img/setting_mini.png" />グループ設定</li>
        <li id='delete'><img src="../img/delete_mini.png" />削除</li>
    </ul>
</div>
<div class='contextMenu' id='domainMenu'>
    <ul>
        <li id='setting'><img src="../img/folder_mini.png" />ドメイン設定</li>
    </ul>
</div>
<div class='contextMenu' id='filelisttitleMenu'>
    <ul>
        <li id='setting'><img src="../img/folder_mini.png" />表示設定</li>
    </ul>
</div>
<div class='contextMenu' id='contentMenu'>
    <ul>
        <li id='edit'><img src="../img/folder_mini.png" />ユーザ編集</li>
        <li id='delete'><img src="../img/folder_mini.png" />ユーザ削除</li>
    </ul>
</div>


<?php $LayoutManager->footer(); ?>
