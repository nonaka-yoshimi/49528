<?php
// アプリケーション共通クラス読込
require_once(dirname(__FILE__).'/ApplicationCommon/include.php');

$session = Session::get();

$session->loginCheckAndRedirect("login.php",array("admin_flg" => 1));

$LayoutManager = new LayoutManagerAdminMain();
$LayoutManager->setTitle("管理者ポータル");
$LayoutManager->header();
?>
<h3>メニュー</h3>

<table class="list">
<?php
for($i=1;$i<=20;$i++){
	if(Config::get("addon_module".$i."_active") && Config::get("addon_module".$i."_path") && ($session->user["admintype"] == SPConst::ADMINTYPE_SUPERVISOR || $session->user["ope_auth_ext".$i])){
		?>
	  <tr>
	    <th class="w240"><a href="/<?php echo Config::BASE_DIR_PATH.Config::ADMIN_DIR_PATH.Config::get("addon_module".$i."_path");?>"><?php echo Config::get("addon_module".$i."_name"); ?></a></th>
	    <td><?php echo htmlspecialchars(Config::get("addon_module".$i."_description"))?></td>
	  </tr>
	<?php
	}
}
?>
  <?php if($session->user["admintype"] == SPConst::ADMINTYPE_SUPERVISOR || $session->user["ope_auth_page_view"]): ?>
  <tr>
    <th><a href="page_manager.php">ページ情報管理</a></th>
    <td>Webサイトの各ページコンテンツの登録・更新を行います。</td>
  </tr>
  <?php endif; ?>
  <?php if($session->user["admintype"] == SPConst::ADMINTYPE_SUPERVISOR || $session->user["ope_auth_template_view"]): ?>
  <tr>
    <th><a href="template_manager.php">テンプレート管理</a></th>
    <td>各ページの共通部分（テンプレート）の登録・更新を行います。(管理者向け)</td>
  </tr>
  <?php endif; ?>
  <?php if($session->user["admintype"] == SPConst::ADMINTYPE_SUPERVISOR || $session->user["ope_auth_parts_view"]): ?>
  <tr>
    <th><a href="parts_manager.php">部品管理</a></th>
    <td>各ページや動的出力機能から呼び出して使用する部品の登録・更新を行います。(管理者向け)</td>
  </tr>
  <?php endif; ?>
  <?php if($session->user["admintype"] == SPConst::ADMINTYPE_SUPERVISOR || $session->user["ope_auth_stylesheet_view"]): ?>
  <tr>
    <th><a href="stylesheet_manager.php">スタイルシート管理</a></th>
    <td>各ページやテンプレートのレイアウトを定義するスタイルシート(CSS)の登録・更新を行います。(管理者向け)</td>
  </tr>
  <?php endif; ?>
  <?php if($session->user["admintype"] == SPConst::ADMINTYPE_SUPERVISOR || $session->user["ope_auth_script_view"]): ?>
  <tr>
    <th><a href="script_manager.php">スクリプト管理</a></th>
    <td>各ページやテンプレートに組み込むスクリプトプログラム(JavaScript等)の登録・更新を行います。(管理者向け)</td>
  </tr>
  <?php endif; ?>
  <?php if($session->user["admintype"] == SPConst::ADMINTYPE_SUPERVISOR || $session->user["ope_auth_file_admin"]): ?>
  <tr>
    <th><a href="" onClick="open_ckeditor(); return false;">ファイル管理</a>
    <?php
    	echo '<script src="/'.Config::BASE_DIR_PATH.Config::ADMIN_DIR_PATH.'ckfinder/ckfinder.js"></script>'."\n";
		echo '<script>'."\n";
		echo '	function open_ckeditor(){'."\n";
		echo '		var finder = new CKFinder();'."\n";
		echo '		finder.basePath = "/'.Config::BASE_DIR_PATH.'";'."\n";
		echo '		finder.popup();'."\n";
		echo '	}'."\n";
		echo '</script>'."\n";
    ?>
    </th>
    <td>各ページで使用する画像ファイル等の登録・更新を行います。（別ウィンドウが開きます）</td>
  </tr>
  <?php endif; ?>
  <?php if($session->user["admintype"] == SPConst::ADMINTYPE_SUPERVISOR || ($session->user["ope_auth_user_self"] && $session->user["ope_auth_user_other"])): ?>
  <tr>
    <th><a href="user_manager.php">ユーザ管理</a></th>
    <td>システムを利用するユーザの登録・更新を行います。</td>
  </tr>
  <?php endif; ?>
  <?php if($session->user["admintype"] == SPConst::ADMINTYPE_SUPERVISOR || $session->user["ope_auth_website"]): ?>
  <tr>
    <th><a href="master_manager.php">マスタ設定</a></th>
    <td>システム全体の動作に関わる、マスタ情報の登録・更新を行います。(開発者向け)</td>
  </tr>
  <tr>
    <th><a href="config_manager.php">システム設定</a></th>
    <td>システム全体の動作設定を行います。(開発者向け)</td>
  </tr>
  <?php endif; ?>
</table>
<?php $LayoutManager->footer(); ?>
