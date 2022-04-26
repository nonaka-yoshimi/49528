<?php
require_once(dirname(__FILE__).'/../Framework/SessionBase.php'); //Sessionベースクラス
require_once(dirname(__FILE__).'/../Config/Config.php'); //設定クラス

/*
 説明：セッション管理クラス
作成日：2013/05/19 TS谷
*/

/**
 * セッション管理クラス
 *
 */
class Session extends SessionBase
{
	/**
	 * ユーザ情報配列
	 * @var array ユーザ情報一覧
	 */
	var $user = array("language" => Config::DEFAULT_LANGUAGE);

	/**
	 * 選択中ドメイン
	 */
	var $domain = Config::LOCAL_DOMAIN_NAME;

	/**
	 * 管理画面ログイン状態
	 */
	var $webadmin_login_state = false;

	/**
	 * プレビューモード
	 */
	var $mode = "";

	/**
	 * コンストラクタ(newは禁止する)
	 */
	protected function __construct(){
		//親クラスのコンストラクタを呼び出し
		parent::__construct();
	}

	/**
	 * セッションインスタンスの取得処理
	 * @return Session セッション管理クラスのインスタンス
	 */
	static function get(){
		//変更しないでください
		if(!isset(self::$instance)){
			self::$instance = new Session();
		}else{
		}
		return self::$instance;
	}

	/**
	 * セッションクラスの基本動作設定を行う
	 */
	protected function setConfig(){
		//セッションクッキー有効時間
		$this->session_cookie_lifetime = Config::SESSION_COOKIE_LIFETIME;

		//セッション有効時間
		$this->session_lifetime = Config::SESSION_LIFETIME;

		//アクセス識別子用名称
		$this->session_access_identcode_name = Config::SESSION_ACCESS_IDENTCODE;

		//アクセス識別子生成（暗号化）用文字列(MAGIC CODE)
		$this->session_access_identcode_magic_code = Config::SESSION_ACCESS_IDENTCODE_MAGIC_CODE;

		//ユーザ認証コード格納先セッション名称を設定する
		$this->session_user_auth_name = Config::SESSION_USER_AUTH_NAME;

		//ユーザログインID格納先セッション名称を設定する
		$this->session_user_login_id_name = Config::SESSION_USER_LOGIN_ID_NAME;

		//ログイン認証用マジックコードを設定する
		$this->login_auth_magic_code = Config::SESSION_LOGIN_AUTH_MAGIC_CODE;

		//最終操作時間格納先セッション名称を設定する
		$this->last_operation_time_name = Config::SESSION_LAST_OPERATION_TIME_NAME;
	}

	/**
	 * ログイン処理(実装必須)
	 * @param string ログインID
	 * @param string パスワード
	 * @return boolean true|false
	 */
	protected function loginLogic($login_id,$password)
	{
		//個別ログイン処理を実装する 開始
		require_once(dirname(__FILE__).'/../ApplicationCommon/Util.php'); 	//ユーティリティ
		require_once(dirname(__FILE__).'/../CMSCommon/UserAuth.php'); 		//ユーザ権限関連機能
		require_once(dirname(__FILE__).'/../DataAccess/User.php'); 			//Userテーブル
		require_once(dirname(__FILE__).'/../DataAccess/UserAddInfo.php');	//ユーザ追加情報テーブル

		//ログインID空欄チェック
		if(Util::IsNullOrEmpty($login_id))
		{
			Logger::debug("ログインID空欄エラー");
			return false;
		}

		//パスワード空欄チェック
		if(Util::IsNullOrEmpty($password))
		{
			Logger::debug("パスワード空欄エラー");
			return false;
		}

		//ユーザを取得
		$User = new User();
		$userData = $User->getLoginUserData($login_id);

		//ユーザ不在チェック
		if(Util::IsNullOrEmpty($userData))
		{
			Logger::debug("ユーザ不在エラー");
			return false;
		}

		//パスワード比較チェック
		if(Util::makePasswordHashCode($password) != $userData["password"])
		{
			Logger::debug("パスワード比較エラー",array("input" => Util::makePasswordHashCode($password),"db" => $userData["password"]));
			return false;
		}

		//ユーザ無効チェック
		if($userData["active_flg"] != User::ACTIVE){
			Logger::debug("ユーザ無効エラー");
			return false;
		}

		//部署無効チェック
		if(Util::IsNullOrEmpty($userData["usergroup_id"])){
			Logger::debug("部署無効エラー");
			return false;
		}

		//部署・権限情報一覧を取得
		$userGroupAuthList = UserAuth::getAuthUserGroupListByUserId($userData["user_id"]);

		//ユーザの権限辞書を取得
		$userAuthDic = UserAuth::getOperationAuthDicByAuthList($userGroupAuthList);

		//ユーザの権限辞書をユーザデータに統合
		$userData = array_merge($userData,$userAuthDic);

		//ユーザグループ一覧情報をユーザ情報に追加
		//$userData["usergroups"] = $userGroupAuthList;

		//ユーザグループIDの配列を作成
		$userData["usergroups"] = Util::getArrayByDataListAndName($userGroupAuthList, "usergroup_id");

		//ユーザデータにuser_addinfo内データを格納
		$userAddInfo = new UserAddInfo();
		$addinfoList = $userAddInfo->getListByParameters(array("user_id"=>$userData["user_id"]));
		for($i = 0;$i < count($addinfoList);$i++){
			$userData[$addinfoList[$i]["name"]] = $addinfoList[$i]["addinfo_content"];
		}
		//セッションにユーザデータを格納する
		$this->setSession($userData);

		return true;
	}

	/**
	* ログイン成功時最終処理
	* @param string ログインID
	* @param string パスワード
	* @return void
	*/
	protected function loginSuccessLogic($login_id, $password){
		//ログイン成功ログを記録する
		$lib = new Resources();	//ワードライブラリ呼出
		Logger::info($lib->get("LOGIN_SUCCESS").":".$this->user["name"]."(".$this->user["user_id"].")");
	}

	/**
	 * ログイン失敗時最終処理
	 * @param string ログインID
	 * @param string パスワード
	 * @return void
	 */
	protected function loginFailureLogic($login_id, $password){
		//ログイン失敗ログを記録する
		$lib = new Resources();	//ワードライブラリ呼出
		Logger::info($lib->get("LOGIN_FAILURE").":".$login_id);
	}

	/**
	 * セッションにデータを格納する
	 * @param array $userData ユーザ情報
	 */
	protected function setSession($userData){
		//ユーザデータをすべてセッションに格納
		foreach($userData as $key => $value){
			$_SESSION[$key] = $value;
		}
	}

	/**
	 * インスタンス変数にデータを格納する
	 * 格納したデータはアプリケーション内で使用可能となる
	 */
	protected function setInstanceData(){
		//セッション内の情報をすべてインスタンス変数に格納
		foreach($_SESSION as $key => $value){
			$this->user[$key] = $value;
		}

		//操作中ドメインをセッションに保存 TODO修正
		//$_SESSION['domain'] = "sub.tocca-net.jp";
		$this->domain = isset($_SESSION['domain']) ? $_SESSION['domain'] : "";

		//プレビューモード設定
		$this->mode = isset($_SESSION['mode']) ? $_SESSION['mode'] : "";

		//ログイン状態を格納
		if(isset($this->user["user_id"]) && $this->user["user_id"] != ""){
			$this->login_state = true;
			$this->webadmin_login_state = true;
		}
	}

	function setDomain($domain){
		$_SESSION['domain'] = $domain;
		$this->domain = $_SESSION['domain'];
	}

	function setMode($mode){
		$_SESSION['mode'] = $mode;
		$this->mode = $_SESSION['mode'];
	}

	function getMode(){
		return $this->mode;
	}

	/**
	 * ログインチェックの追加オプション処理
	 */
	protected function loginCheckLogic()
	{
		return true;
	}

	/**
	 * ログアウト処理
	 * @return boolean true|false ログアウト結果
	 */
	protected function logoutLogic()
	{
		//ログアウトログを記録する
		$lib = new Resources();	//ワードライブラリ呼出
		$name = isset($this->user["name"]) ? $this->user["name"] : "";
		$user_id = isset($this->user["user_id"]) ? $this->user["user_id"] : "";
		Logger::info($lib->get("LOGOUT").":".$name."(".$user_id.")");

		//セッション破棄
		session_destroy();
		return true;
	}

	/**
	 * アクセス識別子の生成処理（実装必須）
	 * @return string アクセス識別子
	 */
	protected function makeIdentCode()
	{
		//アクセス識別子の生成処理ロジックを記載します。（変更可能）
		return sha1(time().mt_rand().$this->session_access_identcode_magic_code);
	}

	/**
	 * ログイン認証コードの生成処理(実装必須)
	 * @return string ログイン認証コード
	 */
	protected function makeLoginAuthCode($login_id)
	{
		//ログイン認証コードの生成処理ロジックを記載します。（変更可能）
		return hash('sha512',$login_id.$this->login_auth_magic_code);
	}



	/**
	 * セッションから指定インデックスのデータを取得する
	 * 存在しない場合はnullを返す
	 * @param string $param_name
	 * @return 存在する場合：セッション格納値,存在しない場合：null
	 */
	public function getUserSessionData($param_name){
		if(isset($this->user[$param_name])){
			return $this->user[$param_name];
		}
		return null;
	}

	/**
	 * ログイン中のユーザデータを取得する
	 * @return array ユーザデータ:
	 */
	function getUserData(){
		$user = new ReserveUser();
		return $user->getDataByPrimaryKey($_SESSION["user_id"]);
	}
}
