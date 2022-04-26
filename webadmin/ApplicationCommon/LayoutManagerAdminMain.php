<?php
require_once(dirname(__FILE__).'/MenuConfig.php'); //ベースクラス読み込み
/*
説明：管理者画面用レイアウト管理クラス
作成日：2013/10/21 TS谷
*/

/**
 * 管理者画面用レイアウト管理クラス
 */
class LayoutManagerAdminMain
{

	/**
	 * セッション情報
	 */
	var $session;

	/**
	 * メニュー設定
	 */
	var $menu;

	/**
	 * ページタイトル(メイン)
	 */
	var $title;

	/**
	 * ページタイトル(プレフィックス)
	 */
	var $title_prefix;

	/**
	 * ページタイトル(サフィックス)
	 */
	var $title_suffix;

	/**
	 * 選択中の画面上部メニュー
	 */
	var $selected_headmenu;

	/**
	 * 選択中のサイドメニュー
	 */
	var $selected_sidemenu;

	/**
	 * エラー一覧
	 */
	var $error;

	/**
	 * メッセージ一覧
	 */
	var $message;

	/**
	 * アラート一覧
	 */
	var $alert;

	/**
	 * 管理者画面用レイアウト管理クラスのコンストラクタ
	 */
	function __construct()
	{
		//ユーザ情報を格納(セッションからコピー)
		$this->session = Session::get();

		//デフォルト設定
		$lib = new Resources();
		$this->title = $lib->get("TITLE_NOT_SET");
		$this->title_suffix = " | ".$lib->get("PRODUCT_NAME");
		$this->title_prefix = "";
		$this->error = array();
		$this->message = array();
	}

	/**
	 * ページタイトルを設定
	 * @param ページタイトル
	 * @return true/false
	 */
	function setTitle($title){
		if($title == null || $title == ""){
			return false;
		}

		//ページタイトルを設定する
		$this->title = $title;

		return true;
	}

	/**
	 * 選択中画面上部メニューを設定
	 * @param string 選択中メニュー
	 * @return true/false
	 */
	function setSelectedHeadmenu($menu){
		if($menu == null || $menu == ""){
			return false;
		}

		//選択中画面上部メニューを設定する
		$this->selected_headmenu = $menu;

		return true;
	}

	/**
	 * 選択中サイドメニューを設定
	 * @param string 選択中メニュー
	 * @return true/false
	 */
	function setSelectedSidemenu($menu){
		if($menu == null || $menu == ""){
			return false;
		}

		//選択中サイドメニューを設定する
		$this->selected_sidemenu = $menu;

		return true;
	}

	/**
	 * エラーメッセージリストを設定
	 * @param array エラーメッセージリスト
	 * @return true/false
	 */
	function setErrorList($error){
		if($error == null || $error == ""){
			return false;
		}

		//エラーメッセージを設定する
		$this->error = $error;

		return true;
	}

	/**
	 * メッセージリストを設定
	 * @param array メッセージリスト
	 * @return true/false
	 */
	function setMessageList($message){
		if($message == null || $message == ""){
			return false;
		}

		//エラーメッセージを設定する
		$this->message = $message;

		return true;
	}

	/**
	 * アラートリストを設定
	 * @param array アラートリスト
	 * @return true/false
	 */
	function setAlertList($alert){
		if($alert == null || $alert == ""){
			return false;
		}

		//アラートメッセージを設定する
		$this->alert = $alert;

		return true;
	}

	/**
	 * エラーメッセージ一覧出力
	 */
	function error(){
		if(!is_array($this->error) || $this->error == array()){
			return "";
		}

		echo '<ul class="error">'."\n";
		foreach($this->error as $error_one){
			echo '<li>'.htmlspecialchars($error_one).'</li>'."\n";
		}
		echo '</ul>'."\n";
	}

	/**
	 * メッセージ一覧出力
	 */
	function message(){
		if(!is_array($this->message) || $this->message == array()){
			return "";
		}

		echo '<ul class="message">'."\n";
		foreach($this->message as $message_one){
			echo '<li>'.htmlspecialchars($message_one).'</li>'."\n";
		}
		echo '</ul>'."\n";
	}

	/**
	 * アラート一覧出力
	 */
	function alert(){
		if(!is_array($this->alert) || $this->alert == array()){
			return "";
		}

		echo '<ul class="alert">'."\n";
		foreach($this->alert as $alert_one){
			echo '<li>'.htmlspecialchars($alert_one).'</li>'."\n";
		}
		echo '</ul>'."\n";
	}

	/**
	 * ヘッダ部全体出力処理
	 */
	function header(){
		echo '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN""http://www.w3.org/TR/html4/loose.dtd">'."\n";
		echo '<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="ja" lang="ja">'."\n";
		echo '<head>'."\n";
		echo '<meta http-equiv="X-UA-Compatible" content="IE=EmulateIE7" />'."\n";
		echo '<meta http-equiv="Pragma" content="no-cache">'."\n";
		echo '<meta http-equiv="Expires" content="-1">'."\n";
		echo '<meta http-equiv="Content-Type" content="text/html; charset=utf-8">'."\n";
		echo '<meta http-equiv="Content-Language" content="ja">'."\n";
		echo '<meta http-equiv="Content-Style-Type" content="text/css" />'."\n";
		echo '<meta http-equiv="Content-Script-Type" content="text/javascript" />'."\n";
		echo '<title>'.htmlspecialchars($this->title_prefix).htmlspecialchars($this->title).htmlspecialchars($this->title_suffix).'</title>'."\n";
		echo '<link href="'.$this->admin_path().'css/jquery-ui-1.10.3.custom.css" rel="stylesheet" type="text/css">'."\n";
		//echo '<link href="'.$this->admin_path().'css/common.css" rel="stylesheet" type="text/css">'."\n";
		//echo '<link href="'.$this->admin_path().'css/main.css" rel="stylesheet" type="text/css">'."\n";
		echo '<script type="text/javascript" src="'.$this->admin_path().'js/jquery-1.10.2.min.js"></script>'."\n";
		echo '<script type="text/javascript" src="'.$this->admin_path().'js/jquery-ui-1.10.3.custom.min.js"></script>'."\n";
		echo '<script type="text/javascript" src="'.$this->admin_path().'js/jquery.ui.datepicker-ja.js"></script>'."\n";
		//echo '<script type="text/javascript" src="'.$this->admin_path().'js/jQueryAutoHeight.js"></script>'."\n";
		//echo '<script type="text/javascript" src="'.$this->admin_path().'js/jquery.contextmenu.r2.js"></script>'."\n";
		//echo '<script type="text/javascript" src="'.$this->admin_path().'js/common.js"></script>'."\n";
		echo '<script type="text/javascript" src="'.$this->admin_path().'js/admin_common.js"></script>'."\n";
		//echo '<link rel="stylesheet" href="//netdna.bootstrapcdn.com/bootstrap/3.0.0/css/bootstrap.min.css">';
		//echo '<link rel="stylesheet" href="'.$this->admin_path().'js/fileupload/css/style.css">';
		echo '<link rel="stylesheet" href="'.$this->admin_path().'css/admin.css">';
		echo '<script type="text/javascript">'."\n";
		echo '	var admin_path = "'.$this->admin_path().'";'."\n";
		echo '	var base_dir_path = "'.Config::BASE_DIR_PATH.'";'."\n";
		echo '	var admin_dir_path = "'.Config::ADMIN_DIR_PATH.'";'."\n";
		echo '</script>'."\n";
		echo '</head>'."\n";
		echo '<body>'."\n";
		echo '<div id="container">'."\n";
        
		echo '<div id="header">'."\n";
		//echo '<h1 id="brand" class="content_left"><a href="'.$this->admin_path().'"><img src="'.$this->admin_path().'img/logo_mini.png" alt="ロゴ" /></a></h1>'."\n";
        echo '<h1 id="brand" class="content_left">歯科CMS 管理サイト</h1>'."\n";
		echo '<ul class="content_right">'."\n";
		echo '<li><a href="'.$this->admin_path().'login.php?action=logout">ログアウト</a></li>'."\n";
        echo '<div class="clear"></div>'."\n";
		echo '</ul>'."\n";
		echo '<div class="clear"></div>'."\n";
		echo '</div>'."\n";
        
		echo '<div id="body">'."\n";
		echo '<div id="content">'."\n";
		echo '<div id="content_inner">'."\n";
		/*
		echo '<div id="list">'."\n";
		if($this->title == "管理者ポータル"){
			echo $this->title."\n";
		}else{
			echo "<a href='".$this->admin_path()."'>管理者ポータル</a> > ".$this->title."\n";
		}
		echo '</div>'."\n";
		*/
		echo '<h2>'.$this->title.'</h2>'."\n";

	}

	/**
	 * ヘッダ部全体出力処理
	 */
	function header_login(){
		echo '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN""http://www.w3.org/TR/html4/loose.dtd">'."\n";
		echo '<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="ja" lang="ja">'."\n";
		echo '<head>'."\n";
		echo '<meta http-equiv="X-UA-Compatible" content="IE=EmulateIE7" />'."\n";
		echo '<meta http-equiv="Pragma" content="no-cache">'."\n";
		echo '<meta http-equiv="Expires" content="-1">'."\n";
		echo '<meta http-equiv="Content-Type" content="text/html; charset=utf-8">'."\n";
		echo '<meta http-equiv="Content-Language" content="ja">'."\n";
		echo '<meta http-equiv="Content-Style-Type" content="text/css" />'."\n";
		echo '<meta http-equiv="Content-Script-Type" content="text/javascript" />'."\n";
		echo '<title>'.htmlspecialchars($this->title_prefix).htmlspecialchars($this->title).htmlspecialchars($this->title_suffix).'</title>'."\n";
		//echo '<link href="'.$this->admin_path().'css/jquery-ui-1.10.3.custom.css" rel="stylesheet" type="text/css">'."\n";
		//echo '<link href="'.$this->admin_path().'css/common.css" rel="stylesheet" type="text/css">'."\n";
		//echo '<link href="'.$this->admin_path().'css/main.css" rel="stylesheet" type="text/css">'."\n";
		//echo '<script type="text/javascript" src="'.$this->admin_path().'js/jquery-1.10.2.min.js"></script>'."\n";
		//echo '<script type="text/javascript" src="'.$this->admin_path().'js/jquery-ui-1.10.3.custom.min.js"></script>'."\n";
		//echo '<script type="text/javascript" src="'.$this->admin_path().'js/jquery.ui.datepicker-ja.js"></script>'."\n";
		//echo '<script type="text/javascript" src="'.$this->admin_path().'js/jQueryAutoHeight.js"></script>'."\n";
		//echo '<script type="text/javascript" src="'.$this->admin_path().'js/jquery.contextmenu.r2.js"></script>'."\n";
		//echo '<script type="text/javascript" src="'.$this->admin_path().'js/common.js"></script>'."\n";

		//echo '<link rel="stylesheet" href="//netdna.bootstrapcdn.com/bootstrap/3.0.0/css/bootstrap.min.css">';
		//echo '<link rel="stylesheet" href="'.$this->admin_path().'js/fileupload/css/style.css">';
		echo '<link rel="stylesheet" href="'.$this->admin_path().'css/admin.css">';

		echo '</head>'."\n";
		echo '<body>'."\n";
		echo '<div id="container">'."\n";
        
		echo '<div id="header">'."\n";
		//echo '<h1 id="brand" class="content_left"><a href="'.$this->admin_path().'"><img src="'.$this->admin_path().'img/logo_mini.png" alt="ロゴ" /></a></h1>'."\n";
        echo '<h1 id="brand" class="content_left">歯科CMS 管理サイト</h1>'."\n";
		echo '<div class="clear"></div>'."\n";
		echo '</div>'."\n";
        
		echo '<div id="body">'."\n";
		echo '<div id="content">'."\n";
		echo '<div id="content_inner">'."\n";

		echo '<h2>'.$this->title.'</h2>'."\n";
	}

	/**
	 * フッタ部全体出力処理
	 */
	function footer(){
    
		echo '</div>'."\n";
		echo '</div>'."\n";
    
		echo '<div id="sidebar" class="accordion">'."\n";
		echo '<ul>'."\n";
		echo '<li><a href="'.$this->admin_path().'">管理者ポータル</a></li>'."\n";

		for($i = 1;$i <= Config::ADDON_MODULE_NUM;$i++){
			if(Config::get("addon_module".$i."_active") && Config::get("addon_module".$i."_path") && ($this->session->user["admintype"] == SPConst::ADMINTYPE_SUPERVISOR || $this->session->user["ope_auth_ext".$i])){
				echo '<li><a href="'.$this->admin_path().Config::get("addon_module".$i."_path").'">'.Config::get("addon_module".$i."_name").'</a></li>'."\n";
				//フォーム画面時に使用
				/*$code = Config::get("addon_module".$i."_code")."_menu";
				echo $code;
				if(method_exists($this,$code)){
					$this->$code($i);
				}*/
			}
		}

		if($this->session->user["admintype"] == SPConst::ADMINTYPE_SUPERVISOR || $this->session->user["ope_auth_page_view"]){
			echo '<li><a href="'.$this->admin_path().'page_manager.php">ページ情報管理</a></li>'."\n";
		}
		if($this->session->user["admintype"] == SPConst::ADMINTYPE_SUPERVISOR || $this->session->user["ope_auth_template_view"]){
			echo '<li><a href="'.$this->admin_path().'template_manager.php">テンプレート管理</a></li>'."\n";
		}
		if($this->session->user["admintype"] == SPConst::ADMINTYPE_SUPERVISOR || $this->session->user["ope_auth_parts_view"]){
			echo '<li><a href="'.$this->admin_path().'parts_manager.php">部品管理</a></li>'."\n";
		}
		if($this->session->user["admintype"] == SPConst::ADMINTYPE_SUPERVISOR || $this->session->user["ope_auth_stylesheet_view"]){
			echo '<li><a href="'.$this->admin_path().'stylesheet_manager.php">スタイルシート管理</a></li>'."\n";
		}
		if($this->session->user["admintype"] == SPConst::ADMINTYPE_SUPERVISOR || $this->session->user["ope_auth_script_view"]){
			echo '<li><a href="'.$this->admin_path().'script_manager.php">スクリプト管理</a></li>'."\n";
		}
		if($this->session->user["admintype"] == SPConst::ADMINTYPE_SUPERVISOR || $this->session->user["ope_auth_file_admin"]){
			echo '<li><a href="" onClick="open_ckeditor(); return false;">ファイル管理</a></li>'."\n";
		}
		if($this->session->user["admintype"] == SPConst::ADMINTYPE_SUPERVISOR || ($this->session->user["ope_auth_user_self"] && $this->session->user["ope_auth_user_other"])){
			echo '<li class="margin"><a href="'.$this->admin_path().'user_manager.php">ユーザ管理</a></li>'."\n";
		}
		if($this->session->user["admintype"] == SPConst::ADMINTYPE_SUPERVISOR || $this->session->user["ope_auth_website"]){
			echo '<li class="margin"><a href="'.$this->admin_path().'master_manager.php">マスタ設定</a></li>'."\n";
			echo '<li><a href="'.$this->admin_path().'config_manager.php">システム設定</a></li>'."\n";
		}
		//echo '<li><a href="'.$this->admin_path().'login.php?action=logout">ログアウト</a></li>'."\n";
		echo '</ul>'."\n";
		echo '<script src="/'.Config::BASE_DIR_PATH.Config::ADMIN_DIR_PATH.'ckfinder/ckfinder.js"></script>'."\n";
		echo '<script>'."\n";
		echo '	function open_ckeditor(){'."\n";
		echo '		var finder = new CKFinder();'."\n";
		echo '		finder.basePath = "/'.Config::BASE_DIR_PATH.'";'."\n";
		echo '		finder.popup();'."\n";
		echo '	}'."\n";
		echo '</script>'."\n";
		echo '</div>'."\n";

		echo '<div class="clear"></div>'."\n";
		echo '</body>'."\n";
		echo '</html>'."\n";
	}

	/**
	 * フッタ部全体出力処理
	 */
	function footer_login(){
		echo '</div>'."\n";
		echo '</div>'."\n";
		echo '<div id="sidebar" class="accordion">'."\n";
		echo '</div>'."\n";
		echo '<div class="clear"></div>'."\n";
		echo '</div>'."\n";
		echo '</body>'."\n";
		echo '</html>'."\n";
	}

	/**
	 * 管理者画面までのルート相対パスを出力
	 */
	function admin_path(){
		return '/'.Config::BASE_DIR_PATH.Config::ADMIN_DIR_PATH;
	}
}
?>
