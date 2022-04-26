<?php
/*
説明：子画面用レイアウト管理クラス
作成日：2013/10/21 TS谷
*/

/**
 * 子画面用レイアウト管理クラス
 */
class LayoutManagerChild
{

	/**
	 * セッション情報
	 */
	var $session;

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
	 * エラー一覧
	 */
	var $error;

	/**
	 * メッセージ一覧
	 */
	var $message;

	/**
	 * 子画面用レイアウト管理クラスのコンストラクタ
	 */
	function __construct()
	{
		//ユーザ情報を格納(セッションからコピー)
		$this->session = Session::get();

		//デフォルト設定
		$wordlib = new Word();
		$this->title = $wordlib->get("TITLE_NOT_SET");
		$this->title_suffix = " | ".$wordlib->get("PRODUCT_NAME");
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
	 * エラーメッセージ一覧出力
	 */
	function error(){
		if(!is_array($this->error) || $this->error == array()){
			return "";
		}
		echo '<ul class="alert error">'."\n";
		foreach($this->error as $error_one){
			echo '<li class="r b">'.$error_one.'</li>'."\n";
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
		echo '<ul class="alert message">'."\n";
		foreach($this->message as $message_one){
			echo '<li class="o b">'.$message_one.'</li>'."\n";
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
		echo '<link href="'.$this->admin_path().'css/jquery-ui-1.10.3.custom.min.css" rel="stylesheet" type="text/css">'."\n";
		echo '<link href="'.$this->admin_path().'css/common.css" rel="stylesheet" type="text/css">'."\n";
		echo '<link href="'.$this->admin_path().'css/child.css" rel="stylesheet" type="text/css">'."\n";
		echo '<script type="text/javascript" src="'.$this->admin_path().'js/jquery-1.10.2.min.js"></script>'."\n";
		echo '<script type="text/javascript" src="'.$this->admin_path().'js/jquery-ui-1.10.3.custom.min.js"></script>'."\n";
		echo '<script type="text/javascript" src="'.$this->admin_path().'js/jQueryAutoHeight.js"></script>'."\n";
		echo '<script type="text/javascript" src="'.$this->admin_path().'js/jquery.contextmenu.r2.js"></script>'."\n";
		echo '<script type="text/javascript" src="'.$this->admin_path().'js/common.js"></script>'."\n";

		//echo '<link rel="stylesheet" href="//netdna.bootstrapcdn.com/bootstrap/3.0.0/css/bootstrap.min.css">';
		//echo '<link rel="stylesheet" href="'.$this->admin_path().'js/fileupload/css/style.css">';
		//echo '<link rel="stylesheet" href="'.$this->admin_path().'js/fileupload/css/jquery.fileupload.css">';

		echo '</head>'."\n";
		echo '<body>'."\n";
		echo '<div id="container">'."\n";
		echo '<div id="pagebody">'."\n";
	}

	/**
	 * フッタ部全体出力処理
	 */
	function footer(){
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
