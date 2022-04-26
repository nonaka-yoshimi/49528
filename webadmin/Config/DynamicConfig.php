<?php
require_once(dirname(__FILE__).'/../Cache/ConfigCache.php'); //設定キャッシュクラス読み込み
/*
 説明：動的設定クラス
作成日：2014/07/21 TS谷
*/

/**
 * 動的設定クラス
 * 設定ファイルまたはDBの設定情報を参照する
 */
class DynamicConfig
{
	static function get($configname){
		if(defined("Config::".$configname)){
			//ファイル設定
			return constant("Config::".$configname);
		}elseif(defined("ConfigCache::".$configname)){
			//キャッシュ設定
			return constant("ConfigCache::".$configname);
		}else{
			//DB参照
			$configname = strtolower($configname);
			$db = new DB();
			$sql = "SELECT value FROM config WHERE configname = ?";
			$result = $db->query($sql,array($configname),DB::FETCH);
			return $result["value"];
		}
	}

	static function set($configname,$value){
		$now_timestamp = time();
		$configname = strtolower($configname);
		$db = new DB();
		$db->tablename = "config";
		$db->primaryKeys = array("configname");
		$saveData["configname"] = $configname;
		$saveData["value"] = $value;
		$saveData["updated"] = $now_timestamp;
		$result = $db->updateOrInsert($saveData);

		//コンフィグキャッシュ再生成
		$configList = $db->getListByParameters();
		$str = "<?php "."\n";
		$str.= "class ConfigCache{ "."\n";
		foreach($configList as $configOne){
			$pattern = "/[\"|\r|\n]/";
			if(!preg_match($pattern,$configOne["value"])){
				$str.= "	const  ".$configOne["configname"]." = \"".$configOne["value"]."\";"."\n";
			}
		}
		$str.= "}"."\n";
		$str.= "?>";

		$fp = fopen(dirname(__FILE__)."/../Cache/ConfigCache.php", "w");
		fwrite($fp, $str);
		fclose($fp);

		return $result;
	}

	static function cacheRefresh(){
		//コンフィグキャッシュ再生成
		$db = new DB();
		$db->tablename = "config";
		$db->primaryKeys = array("configname");
		$configList = $db->getListByParameters();
		$str = "<?php "."\n";
		$str.= "class ConfigCache{ "."\n";
		foreach($configList as $configOne){
			$pattern = "/[\"|\r|\n]/";
			if(!preg_match($pattern,$configOne["value"])){
				$str.= "	const  ".$configOne["configname"]." = \"".$configOne["value"]."\";"."\n";
			}
		}
		$str.= "}"."\n";
		$str.= "?>";

		$fp = fopen(dirname(__FILE__)."/../Cache/ConfigCache.php", "w");
		fwrite($fp, $str);
		fclose($fp);
	}
}