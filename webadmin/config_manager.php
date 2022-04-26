<?php
// アプリケーション共通クラス読込
require_once(dirname(__FILE__).'/ApplicationCommon/include.php');

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

$LayoutManager = new LayoutManagerAdminMain();
$LayoutManager->setTitle("システム設定");
$LayoutManager->header();

?>

<h3>システム設定一覧</h3>

<table class="list">
<tr>
	<th>設定項目</th>
	<th>説明</th>
</tr>
<tr>
<td>
<a href="content_function_edit.php">コンテンツ管理機能設定</a>
</td>
<td>
コンテンツ管理機能の動作設定を行います。
</td>
</tr>
<tr>
<td>
<a href="folder_function_edit.php">フォルダ管理機能設定</a>
</td>
<td>
フォルダ管理機能の動作設定を行います。
</td>
</tr>
<tr>
<td>
<a href="schedule_function_edit.php">スケジュール機能設定</a>
</td>
<td>
スケジュール機能の動作設定を行います。
</td>
</tr>
<tr>
<td>
<a href="archive_function_edit.php">アーカイブ機能設定</a>
</td>
<td>
アーカイブ機能の動作設定を行います。
</td>
</tr>
<tr>
<td>
<a href="preview_function_edit.php">プレビュー機能設定</a>
</td>
<td>
プレビュー機能の動作設定を行います。
</td>
</tr>
<tr>
<td>
<a href="workflow_function_edit.php">ワークフロー機能設定</a>
</td>
<td>
ワークフロー機能の動作設定を行います。
</td>
</tr>
<tr>
<td>
<a href="editor_customize.php">エディタカスタマイズ設定</a>
</td>
<td>
エディタ設定を行います。
</td>
</tr>
<tr>
<td>
<a href="special_page_edit.php">特殊ページ設定</a>
</td>
<td>
NotFoundページなど特殊ページの設定を行います。
</td>
</tr>
<tr>
<td>
<a href="addon_module_edit.php">拡張機能利用設定</a>
</td>
<td>
システム拡張機能の利用有無、名前、管理画面URLの設定を行います。
</td>
</tr>
<tr>
<td>
<a href="default_form_edit.php">お問い合わせフォーム設定</a>
</td>
<td>
お問い合わせフォームのメール通知などの設定を行います。
</td>
</tr>
</table>

<?php $LayoutManager->footer(); ?>
