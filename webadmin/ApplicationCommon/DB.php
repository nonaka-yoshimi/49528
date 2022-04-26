<?php
require_once(dirname(__FILE__).'/../Config/DBConfig.php'); //データベース接続設定読込

/*
 説明：汎用DBアクセスクラス
作成日：2013/05/12 TS谷
*/

/**
 * 汎用DBクラス
 * データベースコネクション作成 DB:connect()
 * トランザクション開始 DB::beginTransaction()
 * コミット DB::commit()
 * ロールバック DB::rollBack()
 */
class DB extends DataAccessBase
{
	function __construct(){
		parent::__construct();
	}

	/**
	 * データベースコネクションを作成する
	 */
	public static function connect()
	{
		global $debug_mode; 	//デバッグモード
		global $db_connection;	//DBコネクション格納用グローバル変数

		try{
			if(DBConfig::DATABASE == "1"){
				//MySQLへのコネクション作成
				$db_connection = @new PDO(DBConfig::DSN,DBConfig::USER,DBConfig::PASSWORD);
				$db_connection->query("SET NAMES utf8");
				$db_connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			}else{
				//その他DBへのコネクション作成
				$db_connection = @new PDO(DBConfig::DSN,DBConfig::USER,DBConfig::PASSWORD);
			}
		}catch(PDOException $e){
			include(dirname(__FILE__)."/../unavailable.html");
			Logger::error("データベースへの接続に失敗しました。");
			if($debug_mode){ echo mb_convert_encoding($e->getMessage(),Config::DEFAULT_ENCODE,Config::DEFAULT_ENCODE_FROM)."<br />"; }
			exit;
		}
	}
}

?>