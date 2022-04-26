<?php
/*
説明：セッションベースクラス
作成日：2013/05/19 TS谷
*/

/**
 * セッション管理用ベースクラス
 *
 */
abstract class SessionBase
{
	/**
	 * セッション管理クラスインスタンス
	 * Singletonデザインパターン
	 * インスタンスの作成は１つのみ
	 * 唯一のインスタンスを格納する変数
	 */
	protected static $instance;

	/**
	 * インスタンス自体の複製禁止
	 */
	public final function __clone() {
		throw new RuntimeException('Clone is not allowd against ' .get_class($this));
	}

	/**
	 * ログイン状態
	 */
	var $login_state = false;

	/**
	 * ユーザ認証コード格納先セッション名称
	 */
	protected $session_user_auth_name;

	/**
	 * ユーザID格納先セッション名称
	 */
	protected $session_user_login_id_name;

	/**
	 * セッションクッキー有効時間
	 */
	protected $session_cookie_lifetime;

	/**
	 * セッション維持時間
	 */
	protected $session_lifetime;

	/**
	 * アクセス識別子名称
	 */
	protected $session_access_identcode_name;

	/**
	 * アクセス識別子生成用マジックコード
	 */
	protected $session_access_identcode_magic_code;

	/**
	 * ログイン認証用マジックコード
	 */
	protected $login_auth_magic_code;

	/**
	 * 最終操作時間
	 */
	protected $last_operation_time_name;

	abstract protected function setConfig();

	abstract protected function setInstanceData();

	abstract protected function makeIdentCode();

	abstract protected function makeLoginAuthCode($login_id);

	abstract protected function loginLogic($login_id,$password);

	abstract protected function loginSuccessLogic($login_id,$password);

	abstract protected function loginFailureLogic($login_id,$password);

	abstract protected function logoutLogic();

	abstract protected function loginCheckLogic();

	/**
	 * コンストラクタ
	 */
	protected function __construct(){
		//セッション基本動作設定を呼び出す
		$this->setConfig();

		//セッション開始処理
		session_set_cookie_params($this->session_cookie_lifetime);
		if( !isset($_SESSION) ) {
			session_start();

			if(!isset($_SESSION[$this->session_access_identcode_name])){
				$identcode = $this->makeIdentCode();
				$_SESSION[$this->session_access_identcode_name] = $identcode;
			}
		}
		//インスタンス変数へのデータ格納処理を呼び出す
		$this->setInstanceData();
	}

	/**
	 * ログイン処理
	 * @param string $login_id ログインID
	 * @param string $password パスワード
	 */
	function login($login_id,$password){
		$result = $this->loginLogic($login_id, $password);
		if($result){
			//ログインセッションを格納する
			$this->setLoginSession($login_id);

			//インスタンス変数へのデータ格納処理を呼び出す
			$this->setInstanceData();

			//最終操作時間を更新
			$_SESSION[$this->last_operation_time_name] = time();

			//ログイン成功時最終処理を呼び出す
			$this->loginSuccessLogic($login_id, $password);

			return true;
		}else{

			//ログアウト処理
			//$this->logout();

			//ログイン失敗時最終処理を呼び出す
			$this->loginFailureLogic($login_id, $password);

			return false;
		}
	}

	/**
	 * ログアウト処理
	 * @return boolean true|false
	 */
	function logout(){
		return $this->logoutLogic();
	}

	/**
	 * セッション情報からログイン状態をチェックする
	 * @param array セッションログインチェック時の条件（任意）
	 * @param array セッションログインチェック時の除外条件（任意）
	 * @return bool ログイン状態（OK/NG)
	 */
	function loginCheck($where = array(),$where_exclude = array())
	{
		global $debug_mode;

		//セッション維持時間を確認
		$last_operation_time = isset($_SESSION[$this->last_operation_time_name]) ? $_SESSION[$this->last_operation_time_name] : "";
		if($last_operation_time == ""){
			Logger::debug("最終操作時間が取得できませんでした。");
			return false;
		}

		if($last_operation_time < time() - $this->session_lifetime){
			Logger::debug("最終操作時間から指定時間を経過しました。");
			return false;
		}

		//セッションからログインIDを取得
		$session_login_id = isset($_SESSION[$this->session_user_login_id_name]) ? $_SESSION[$this->session_user_login_id_name] : "";

		//セッションから認証コードを取得
		$session_auth = isset($_SESSION[$this->session_user_auth_name]) ? $_SESSION[$this->session_user_auth_name] : "";

		//ログイン条件のＳＥＳＳＩＯＮを確認
		$check = TRUE;
		foreach($where as $key => $value){
			if(is_array($value)){
				//配列のOR条件検索
				$check = FALSE;
				$session_value2 = isset($_SESSION[$key]) ? $_SESSION[$key] : "";
				foreach($value as $key2 => $value2){
					if($session_value2 == $value2){
						$check = TRUE;
					}
				}
			}else{
				$session_value = isset($_SESSION[$key]) ? $_SESSION[$key] : "";
				if($session_value != $value){
					$check = FALSE;
				}
			}
			if(!$check){
				return FALSE;
			}
		}

		//除外条件
		foreach($where_exclude as $key => $value){
			if(is_array($value)){
				//配列のOR条件検索
				$check = TRUE;
				$session_value2 = isset($_SESSION[$key]) ? $_SESSION[$key] : "";
				foreach($value as $key2 => $value2){
					if($session_value2 == $value2){
						$check = FALSE;
					}
				}
			}else{
				$session_value = isset($_SESSION[$key]) ? $_SESSION[$key] : "";
				if($session_value == $value){
					$check = FALSE;
				}
			}
			if(!$check){
				return FALSE;
			}
		}

		//認証用コードの生成
		$auth_code = $this->makeLoginAuthCode($session_login_id);

		if($session_auth == $auth_code && $check){
			//個別ログインチェックを呼び出し
			$result = $this->loginCheckLogic();
			if($result){
				//最終操作時間を更新
				$_SESSION[$this->last_operation_time_name] = time();
				return TRUE;
			}else{
				return FALSE;
			}
		}else{
			return FALSE;
		}
	}

	/**
	 * セッション情報からログイン状態をチェックし、未ログインの場合強制リダイレクトする
	 * @param array セッションログインチェック時の特殊条件（任意）
	 * @param array セッションログインチェック時の除外条件（任意）
	 */
	function loginCheckAndRedirect($url,$where = array(),$where_exclude = array()){
		$result = $this->loginCheck($where,$where_exclude);
		if($result){
			//最終操作時間を更新
			$_SESSION[$this->last_operation_time_name] = time();
			return;
		}else{
			header("Location: ".$url);
			exit;
		}
	}

	/**
	 * ログインセッションを設定する
	 * @param string $login_id ログインID
	 * @return void
	 */
	private function setLoginSession($login_id)
	{
		$auth_code = $this->makeLoginAuthCode($login_id);
		$_SESSION[$this->session_user_login_id_name] = $login_id;
		$_SESSION[$this->session_user_auth_name] = $auth_code;
	}
}