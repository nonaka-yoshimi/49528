<?php
/*
 説明：データアクセスベースクラス
作成日：2013/05/12 TS谷
更新履歴：
2014/07/04	谷	updateOrInsert処理の追加,getCountResultWithoutLimitの正規表現を修正
2014/07/14	谷	getMinByParameters,getMaxByParameters処理の追加
2014/08/31	谷	make_where null判定処理の修正
*/

/**
 * データベースアクセス用ベースクラス
 *
 */
abstract class DataAccessBase
{

	/**
	 * データ1件を取得する
	 *
	 */
	const FETCH = "1";

	/**
	 * 全データを配列で取得する
	 *
	 */
	const FETCH_ALL = "2";

	/**
	 * クエリタイプ：全て(不明)
	 */
	const QUERY_TYPE_ALL = "0";

	/**
	 * クエリタイプ：SELECT文
	 */
	const QUERY_TYPE_SELECT = "1";

	/**
	 * クエリタイプ：INSERT文
	 */
	const QUERY_TYPE_INSERT = "2";

	/**
	 * クエリタイプ：UPDATE文
	 */
	const QUERY_TYPE_UPDATE = "3";

	/**
	 * クエリタイプ：DELETE文
	 */
	const QUERY_TYPE_DELETE = "4";

	/**
	 * クエリタイプ：last_insert_id
	 */
	const QUERY_TYPE_LAST_INSERT_ID = "5";


	/**
	 * テーブル名称
	 */
	var $tablename;

	/**
	 * データベースコネクションインスタンス
	 */
	var $db;

	/**
	 * 主キー配列
	 */
	var $primaryKeys;

	/**
	 * 実行クエリログ(配列)
	 */
	var $query_log;

	protected function  __construct()
	{
		global $db_connection;
		$this->db = $db_connection;
		$this->sql_log = array();
	}

	/**
	 * テーブル名称設定
	 * @param string テーブル名称
	 */
	protected function setTableName($tablename)
	{
		$this->tablename = $tablename;
	}

	/**
	 * 主キー設定
	 * @param string 主キー名称
	 */
	protected function setPrimaryKey($keyname)
	{
		$this->primaryKeys[] = $keyname;
	}

	/**
	 * 主キーパラメータを使用してデータを1件取得する
	 * @param 主キー（複数設定可）
	 * @return array DB取得結果
	 */
	public function getDataByPrimaryKey()
	{
		global $sql_check;
		global $debug_mode;

		$args = func_get_args();

		if(count($args) != count($this->primaryKeys))
		{
			Logger::error("プライマリキー数が一致しません。");
			if($debug_mode){
				echo "プライマリキー数が一致しません。";
			}
			return false;
		}
		try{
			$sql = "SELECT * FROM ".$this->tablename." WHERE ";
			$param = array();
			$where = "";

			for($i=0;$i<count($args);$i++)
			{
				if($i != 0){ $where .= " AND "; }
				$where .= $this->primaryKeys[$i]." = ?";
				$param[$i] = $args[$i];
			}
			$sql .= $where;
			$stmt = $this->db->prepare($sql);
			$stmt->execute($param);

			$this->setQueryLog(DataAccessBase::QUERY_TYPE_SELECT, $sql, $param);

			if($sql_check){
				echo $this->makeRealSql($sql, $param)."<br>";
			}

			$result = $stmt->fetch(PDO::FETCH_ASSOC);
			return $result;
		}catch(PDOException $e){
			$real_sql = $this->makeRealSql($sql, $param);
			Logger::error("SQLエラー テーブル：".$this->tablename."のSELECTに失敗しました。".$real_sql);
			if($debug_mode){
				throw new Exception($e->getMessage().$real_sql);
			}else{
				throw new Exception($e->getMessage());
			}
			return null;
		}
	}

	/**
	 * パラメータ(KeyValue)を使用してデータを1件取得する
	 * @param array 検索キー(連想配列)
	 * @return array DB取得結果
	 */
	public function getCountByParameters($key_value = array())
	{
		global $sql_check;
		global $debug_mode;

		if($key_value != array() && !is_array($key_value))
		{
			die("Parameter is not array");
		}
		try{
			$sql = "SELECT count(*) as cnt FROM ".$this->tablename;
			$param = array();

			$where_set = self::make_where($key_value);
			$sql .= $where_set['sql'];
			$param = $where_set['param'];

			$stmt = $this->db->prepare($sql);
			$stmt->execute($param);

			$this->setQueryLog(DataAccessBase::QUERY_TYPE_SELECT, $sql, $param);

			if($sql_check){
				echo $this->makeRealSql($sql, $param)."<br>";
			}

			$result = $stmt->fetch(PDO::FETCH_ASSOC);
			return $result['cnt'];
		}catch(PDOException $e){
			$real_sql = $this->makeRealSql($sql, $param);
			Logger::error("SQLエラー テーブル：".$this->tablename."のSELECTに失敗しました。".$real_sql);
			if($debug_mode){
				throw new Exception($e->getMessage().$real_sql);
			}else{
				throw new Exception($e->getMessage());
			}
			return null;
		}
	}

	/**
	 * パラメータ(KeyValue)を使用して指定カラムの最大値を1件取得する
	 * @param array 検索対象カラム
	 * @param array 検索キー(連想配列)
	 * @return array DB取得結果
	 */
	public function getMaxByParameters($column,$key_value = array())
	{
		global $sql_check;
		global $debug_mode;

		if($key_value != array() && !is_array($key_value))
		{
			die("Parameter is not array");
		}
		try{
			$sql = "SELECT max(".$column.") as max FROM ".$this->tablename;
			$param = array();

			$where_set = self::make_where($key_value);
			$sql .= $where_set['sql'];
			$param = $where_set['param'];

			$stmt = $this->db->prepare($sql);
			$stmt->execute($param);

			$this->setQueryLog(DataAccessBase::QUERY_TYPE_SELECT, $sql, $param);

			if($sql_check){
				echo $this->makeRealSql($sql, $param)."<br>";
			}

			$result = $stmt->fetch(PDO::FETCH_ASSOC);
			return $result['max'];
		}catch(PDOException $e){
			$real_sql = $this->makeRealSql($sql, $param);
			Logger::error("SQLエラー テーブル：".$this->tablename."のSELECTに失敗しました。".$real_sql);
			if($debug_mode){
				throw new Exception($e->getMessage().$real_sql);
			}else{
				throw new Exception($e->getMessage());
			}
			return null;
		}
	}

	/**
	 * パラメータ(KeyValue)を使用して指定カラムの最小値を1件取得する
	 * @param array 検索対象カラム
	 * @param array 検索キー(連想配列)
	 * @return array DB取得結果
	 */
	public function getMinByParameters($column,$key_value = array())
	{
		global $sql_check;
		global $debug_mode;

		if($key_value != array() && !is_array($key_value))
		{
			die("Parameter is not array");
		}
		try{
			$sql = "SELECT min(".$column.") as min FROM ".$this->tablename;
			$param = array();

			$where_set = self::make_where($key_value);
			$sql .= $where_set['sql'];
			$param = $where_set['param'];

			$stmt = $this->db->prepare($sql);
			$stmt->execute($param);

			$this->setQueryLog(DataAccessBase::QUERY_TYPE_SELECT, $sql, $param);

			if($sql_check){
				echo $this->makeRealSql($sql, $param)."<br>";
			}

			$result = $stmt->fetch(PDO::FETCH_ASSOC);
			return $result['min'];
		}catch(PDOException $e){
			$real_sql = $this->makeRealSql($sql, $param);
			Logger::error("SQLエラー テーブル：".$this->tablename."のSELECTに失敗しました。".$real_sql);
			if($debug_mode){
				throw new Exception($e->getMessage().$real_sql);
			}else{
				throw new Exception($e->getMessage());
			}
			return null;
		}
	}


	/**
	 * パラメータを使用してデータ(1件)を取得する
	 * (複数データが存在する場合は先頭データのみを取得)
	 * @param array 検索条件（$key_value[検索キー] = 検索値） *検索値には配列使用可能
	 * @return array DB取得結果
	 */
	public function getDataByParameters($key_value = array())
	{
		global $sql_check;
		global $debug_mode;

		try{
			$sql = "SELECT * FROM `".$this->tablename."`";

			$where_set = self::make_where($key_value); //WHERE句を生成する
			$sql .= $where_set['sql'];
			$param = $where_set['param'];

			$stmt = $this->db->prepare($sql);
			$stmt->execute($param);

			$this->setQueryLog(DataAccessBase::QUERY_TYPE_SELECT, $sql, $param);

			if($sql_check){
				echo $this->makeRealSql($sql, $param)."<br>";
			}

			$result = $stmt->fetch(PDO::FETCH_ASSOC);
			return $result;
		}catch(PDOException $e){
			$real_sql = $this->makeRealSql($sql, $param);
			Logger::error("SQLエラー テーブル：".$this->tablename."のSELECTに失敗しました。".$real_sql);
			if($debug_mode){
				throw new Exception($e->getMessage().$real_sql);
			}else{
				throw new Exception($e->getMessage());
			}
			return null;
		}
	}

	/**
	 * パラメータを使用してデータリストを取得する
	 * @param array 検索条件（$key_value[検索キー] = 検索値） *検索値には配列使用可能
	 * @param array ソート条件（$order[ソートキー]
	 * @param int 開始番号(LIMIT句の開始番号)
	 * @param int 取得データ数(LIMIT句の取得データ数)
	 * @return array DB取得結果
	 */
	public function getListByParameters($key_value = array(),$order = "",$start = 0,$num = 0)
	{
		global $sql_check;
		global $debug_mode;

		try{
			$sql = "SELECT * FROM `".$this->tablename."`";

			$where_set = self::make_where($key_value); //WHERE句を生成する
			$sql .= $where_set['sql'];
			$param = $where_set['param'];

			$sql .= self::make_order($order); //ORDER句を生成する

			if($num != 0){ $sql .= " LIMIT ".$start.",".$num; } //LIMIT句作成

			$stmt = $this->db->prepare($sql);
			$stmt->execute($param);

			$this->setQueryLog(DataAccessBase::QUERY_TYPE_SELECT, $sql, $param);

			if($sql_check){
				echo $this->makeRealSql($sql, $param)."<br>";
			}

			$result = $stmt->fetchAll(PDO::FETCH_ASSOC);
			return $result;
		}catch(PDOException $e){
			$real_sql = $this->makeRealSql($sql, $param);
			Logger::error("SQLエラー テーブル：".$this->tablename."のSELECTに失敗しました。".$real_sql);
			if($debug_mode){
				throw new Exception($e->getMessage().$real_sql);
			}else{
				throw new Exception($e->getMessage());
			}
			return null;
		}
	}

	/**
	 * パラメータを使用してデータリストを取得する(static:要テーブル名指定)
	 * @param string テーブル名称
	 * @param array 検索条件（$key_value[検索キー] = 検索値） *検索値には配列使用可能
	 * @param array ソート条件（$order[ソートキー]
	 * @param int 開始番号(LIMIT句の開始番号)
	 * @param int 取得データ数(LIMIT句の取得データ数)
	 * @return array DB取得結果
	 */
	public static function getListByTableAndParameters($tablename,$key_value = array(),$order = "",$start = 0,$num = 30)
	{
		global $db_connection;
		global $debug_mode;

		try{

			$sql = "SELECT * FROM `".$tablename."`";

			$where_set = self::make_where($key_value); //WHERE句を生成する
			$sql .= $where_set['sql'];
			$param = $where_set['param'];

			$sql .= self::make_order($order); //ORDER句を生成する

			if($num != 0){ $sql .= " LIMIT ".$start.",".$num; } //LIMIT句作成

			$stmt = $db_connection->prepare($sql);
			$stmt->execute($param);


			$result = $stmt->fetchAll(PDO::FETCH_ASSOC);
			return $result;
		}catch(PDOException $e){
			$real_sql = self::makeRealSql($sql, $param);
			Logger::error("SQLエラー テーブル：".$tablename."のSELECTに失敗しました。".$real_sql);
			if($debug_mode){
				throw new Exception($e->getMessage().$real_sql);
			}else{
				throw new Exception($e->getMessage());
			}
			return null;
		}
	}

	/**
	 * パラメータ(KeyValue)を使用してWhere句を生成する
	 * @param array 検索キー(連想配列)
	 * 				パターン１: $key_value['key'] = $value
	 * 				パターン２: $key_value['key'] = array($value1,$value2...)
	 * 				パターン３: $key_value[index] = array($key,$value,$operator = "=")
	 * 				パターン４: $key_value[index] = array($key,array($value1,$value2...))
	 * @return array ['sql']:SQL文(Where句) ['param']:パラメータ連想配列
	 */
	protected static function make_where($key_value){
		$counter = 0;
		$param_arr = array();
		$sql = "";
		if($key_value != null || $key_value != ""){
			foreach($key_value as $key => $value){
				if($counter != 0){
					$sql .= " AND ";
				}else{
					$sql .= " WHERE ";
				}
				if(is_numeric($key)){
					//複雑な検索条件のクエリ
					$where_key = $value[0];
					$where_value = $value[1];
					$where_like = isset($value[2]) ? $value[2] : "=";
					if(!is_array($value[1])){
						if($where_value === null){
							if($where_like == "="){
								$sql .= "`".$where_key."` IS NULL";
							}else{
								$sql .= "`".$where_key."` ".$where_like." ?";
								array_push($param_arr,$where_value);
							}
						}else{
							$sql .= "`".$where_key."` ".$where_like." ?";
							array_push($param_arr,$where_value);
						}
						/*
						if($where_value != null){
							$sql .= "`".$where_key."` ".$where_like." ?";
							array_push($param_arr,$where_value);
						}else{
							if($where_like == "="){
								$sql .= "`".$where_key."` IS NULL";
							}else{
								$sql .= "`".$where_key."` ".$where_like." ?";
								array_push($param_arr,$where_value);
							}
						}
						*/
					}else{
						$sql .= "`".$where_key."` IN (";
						for($i=0;$i<count($value[1]);$i++){
							if($i != 0){ $sql .= ","; }
							$sql .= "?";
							array_push($param_arr,$value[1][$i]);
						}
						$sql .= ")";
					}
				}else{
					//単純な検索条件のクエリ
					if(!is_array($value)){
						if($value === null){
							$sql .= "`".$key."` IS NULL";
						}else{
							$sql .= "`".$key."` = ?";
							array_push($param_arr,$value);
						}

						/*
						if($value != null){
							$sql .= "`".$key."` = ?";
							array_push($param_arr,$value);
						}else{
							$sql .= "`".$key."` IS NULL";
						}
						*/
					}else{
						$sql .= "`".$key."` IN (";
						for($i=0;$i<count($value);$i++){
							if($i != 0){ $sql .= ","; }
							$sql .= "?";
							array_push($param_arr,$value[$i]);
						}
						$sql .= ")";
					}
				}
				$counter++;
			}
		}
		$return['sql'] = $sql;
		$return['param'] = $param_arr;
		return $return;
	}

	/**
	 * パラメータ(order)を使用してOrder句を生成する
	 * @param パターン１：string ソートキー　パターン２:string ASC or DESC
	 * @param パターン１：array $order[ソートキー] = ASC or DESC
	 * @return string SQL文(Order句)
	 */
	protected static function make_order($order,$sort = "ASC"){
		$sql = "";
		if($order != "" && $order != null){
			if(is_array($order)){
				$sql .= " ORDER BY ";
				$order_counter = 0;
				foreach($order as $order_key => $order_value){
					if($order_counter != 0){
						$sql .= ",";
					}
					$sql .= "`".$order_key."` ".$order_value." ";
					$order_counter++;
				}
			}else{
				$sql .= " ORDER BY `".$order."` ".$sort;
			}
			return $sql;
		}
		return $sql;
	}


	/**
	 * パラメータ(group)を使用してGroup句を生成する
	 * @param パターン１：string グループ化キー　パターン２:array グループ化キー配列
	 * @return string SQL文(Group句)
	 */
	protected static function make_group($group){
		$sql = "";
		if($group != "" && $group != null){
			$sql .= " GROUP BY ";
			if(is_array($group)){
				//配列条件のORDER
				$counter = 0;
				foreach($group as $key => $value){
					if($counter != 0){ $sql .= ",";  }
					if(is_numeric($key)){
						//連続単条件のORDER
						$sql .= $value;
					}else{
						//連想配列のORDER
						$sql .= $key." ".$value;
					}
					$counter++;
				}
			}else{
				//単条件のORDER
				$sql .= $group;
			}
		}
		return $sql;
	}

	/**
	 * データベースへの挿入処理を行う
	 * @param array 挿入するデータセット
	 * @return bool 成功/失敗
	 */
	public function insert($dataSet)
	{
		global $debug_mode;
		global $sql_check;

		if($dataSet == "" || $dataSet == array() || $dataSet == null)
		{
			return false;
		}

		try{
			$sql = "INSERT INTO `".$this->tablename."` ";
			$key_value_result = self::make_insert_key_value($dataSet); //Keys Valuesの生成
			$sql .= $key_value_result['sql'];
			$param = $key_value_result['param'];

			if($sql_check){
				echo $this->makeRealSql($sql, $param)."<br>";
			}

			$stmt = $this->db->prepare($sql);
			$stmt->execute($param);

			$this->setQueryLog(DataAccessBase::QUERY_TYPE_INSERT, $sql, $param);

		}catch(PDOException $e){
			$real_sql = $this->makeRealSql($sql, $param);
			Logger::error("SQLエラー テーブル：".$this->tablename."へのINSERTに失敗しました。".$real_sql);
			if($debug_mode){
				throw new Exception($e->getMessage().$real_sql);
			}else{
				throw new Exception($e->getMessage());
			}
			return false;
		}

		return true;
	}

	/**
	 * データベースへの更新or挿入処理を行う(PrimaryKeyにより判定する)
	 * @param array 更新/挿入するデータセット
	 * @return bool 成功/失敗
	 */
	public function updateOrInsert($dataSet)
	{
		global $debug_mode;
		global $sql_check;

		if($dataSet == "" || $dataSet == array() || $dataSet == null)
		{
			return false;
		}

		if(isset($dataSet[0])){
			foreach($dataSet as $data_set_one){
				$where = array();
				foreach($this->primaryKeys as $primaryKey){
					if(isset($data_set_one[$primaryKey])){
						$where[$primaryKey] = $data_set_one[$primaryKey];
					}
				}
				if($where == array()){
					$this->insert($data_set_one);
				}else{
					$cnt = $this->getCountByParameters($where);
					if($cnt > 0){
						$this->update($where, $data_set_one);
					}else{
						$this->insert($data_set_one);
					}
				}
			}
		}else{
			$where = array();
			foreach($this->primaryKeys as $primaryKey){
				if(isset($dataSet[$primaryKey])){
					$where[$primaryKey] = $dataSet[$primaryKey];
				}
			}
			if($where == array()){
				$this->insert($dataSet);
			}else{
				$cnt = $this->getCountByParameters($where);
				if($cnt > 0){
					$this->update($where, $dataSet);
				}else{
					$this->insert($dataSet);
				}
			}
		}
		return true;
	}

	/**
	 * 最終更新IDを取得する(DBによって使用できない場合があります）
	 * @return string 最終更新ID
	 */
	public static function last_insert_id()
	{
		global $db_connection;

		$sql = 'select last_insert_id()';
		$stmt = $db_connection->prepare($sql);
		$stmt->execute();

		$result = $stmt->fetch(PDO::FETCH_ASSOC);
		$last_insert_id = $result['last_insert_id()'];

		//$this->setQueryLog(DataAccessBase::QUERY_TYPE_LAST_INSERT_ID, $sql, array());

		return $last_insert_id;
	}

	/**
	 * Insert用のKeyValue部SQL文を生成する
	 * @param array 挿入するデータセット
	 * @return ['sql']:SQL文 ['param']:パラメータ連想配列
	 */
	protected static function make_insert_key_value($data_arr){
		$sql = "";
		$key_str = "";
		$value_str = "";
		$param_arr = array();

		//KEYの整形
		if(isset($data_arr[0])){
			$counter = 0;
			foreach($data_arr[0] as $key => $value){
				if($counter != 0){
					$key_str .= " , ";
				}else{
					$key_str .= "(";
				}
				$key_str .= "`".$key."`";
				$counter++;
			}
			$key_str .= ")";
		}else{
			$counter = 0;
			foreach($data_arr as $key => $value){
				if($counter != 0){
					$key_str .= " , ";
				}else{
					$key_str .= "(";
				}
				$key_str .= "`".$key."`";
				$counter++;
			}
			$key_str .= ")";
		}

		//VALUESの整形
		if(isset($data_arr[0])){
			$counter_parent = 0;
			foreach($data_arr as $data_arr_one){
				if($counter_parent != 0){
					$value_str .= " , ";
				}
				$counter = 0;
				foreach($data_arr_one as $key => $value){
					if($counter != 0){
						$value_str .= " , ";
					}else{
						$value_str .= "(";
					}
					$value_str .= "?";
					array_push($param_arr,$value);
					$counter++;
				}
				$value_str .= ")";
				$counter_parent++;
			}
		}else{
			$counter = 0;
			foreach($data_arr as $key => $value){
				if($counter != 0){
					$value_str .= " , ";
				}else{
					$value_str .= "(";
				}
				$value_str .= "?";
				array_push($param_arr,$value);
				$counter++;
			}
			$value_str .= ")";
		}

		$sql .= $key_str." VALUES ".$value_str;

		$result['sql'] = $sql;
		$result['param'] = $param_arr;
		return $result;
	}

	/**
	 * データベースへの更新処理を行う
	 * @param array 更新条件
	 * @param array 更新するデータセット
	 * @return bool 成功/失敗
	 */
	public function update($key_value,$dataSet)
	{
		global $debug_mode;
		global $sql_check;

		if($dataSet == "" || $dataSet == array() || $dataSet == null)
		{
			return false;
		}

		try{
			$sql = "UPDATE `".$this->tablename."`";

			$set_set = self::make_update_set($dataSet); //SET句を生成する
			$sql .= $set_set['sql'];
			$param = $set_set['param'];

			$where_set = self::make_where($key_value); //WHERE句を生成する
			$sql .= $where_set['sql'];
			$param = array_merge($param,$where_set['param']);

			if($sql_check){
				echo $this->makeRealSql($sql, $param)."<br>";
			}

			$stmt = $this->db->prepare($sql);
			$stmt->execute($param);

			$this->setQueryLog(DataAccessBase::QUERY_TYPE_UPDATE, $sql, $param);

		}catch(PDOException $e){
			$real_sql = $this->makeRealSql($sql, $param);
			Logger::error("SQLエラー テーブル：".$this->tablename."のUPDATEに失敗しました。".$real_sql);
			if($debug_mode){
				throw new Exception($e->getMessage().$real_sql);
			}else{
				throw new Exception($e->getMessage());
			}
			return false;
		}

		return true;
	}

	/**
	 * データベースに対して任意のクエリを発行する
	 * @param string SQL文
	 * @param array パラメータ配列
	 * @param int データ取得オプション（デフォルトはDB::FETCH_ALL（全件取得）) DB::FETCH_ASSOC（連想配列）、DB::FETCH_ALL（全件取得）
	 * @return クエリ実行結果
	 */
	public function query($sql,$param = array(),$fetch_option = 2){
		global $debug_mode;
		$result = false;
		try{
			//SQLチェック用
			global $sql_check;
			if($sql_check){
				echo $this->makeRealSql($sql, $param)."<br>";
			}

			//クエリの実行
			$stmt = $this->db->prepare($sql);
			if($param == array()){
				$stmt->execute();
			}else{
				$stmt->execute($param);
			}

			$this->setQueryLog(DataAccessBase::QUERY_TYPE_ALL, $sql, $param);

			if($fetch_option == DataAccessBase::FETCH_ALL){
				$result = $stmt->fetchAll(PDO::FETCH_ASSOC);
			}else if($fetch_option == DataAccessBase::FETCH) {
				$result = $stmt->fetch(PDO::FETCH_ASSOC);
			}else{
				$result = false;
			}
		}catch(PDOException $e){
			$real_sql = $this->makeRealSql($sql, $param);
			Logger::error("SQLエラー テーブル：".$this->tablename."へのクエリに失敗しました。".$real_sql);
			if($debug_mode){
				throw new Exception($e->getMessage().$real_sql);
			}else{
				throw new Exception($e->getMessage());
			}
			return false;
		}
		return $result;
	}



	/**
	 * Update用のKeyValue部SQL文を生成する
	 * @param array 更新するデータセット
	 * @return ['sql']:SQL文 ['param']:パラメータ連想配列
	 */
	protected static function make_update_set($data_arr){
		$sql = "";
		$param_arr = array();
		if($data_arr != null || $data_arr != array()){
			$sql = " SET ";
			$counter = 0;
			foreach($data_arr as $key => $value){
				if($counter != 0){
					$sql .= " , ";
				}else{
					$sql .= "";
				}
				$sql .= "`".$key."` = ? ";
				array_push($param_arr,$value);
				$counter++;
			}
		}
		$result['sql'] = $sql;
		$result['param'] = $param_arr;
		return $result;
	}

	/**
	 * データベースへの削除処理を行う
	 * @param array 削除条件
	 * @return bool 成功/失敗
	 */
	public function delete($key_value)
	{
		global $debug_mode;
		global $sql_check;

		if($key_value == "" || $key_value == array() || $key_value == null)
		{
			return false;
		}

		try{
			$sql = "DELETE FROM `".$this->tablename."`";

			$where_set = self::make_where($key_value); //WHERE句を生成する
			$sql .= $where_set['sql'];
			$param = $where_set['param'];

			if($sql_check){
				echo $this->makeRealSql($sql, $param)."<br>";
			}

			$stmt = $this->db->prepare($sql);
			$stmt->execute($param);

			$this->setQueryLog(DataAccessBase::QUERY_TYPE_DELETE, $sql, $param);

		}catch(PDOException $e){
			$real_sql = $this->makeRealSql($sql, $param);
			Logger::error("SQLエラー テーブル：".$this->tablename."のDELETEに失敗しました。".$real_sql);
			if($debug_mode){
				print_r($e);
				throw new Exception("DataDeleteError: ".$this->makeRealSql($sql,$param));
			}else{
				throw new Exception("DataDeleteError");
			}
			return false;
		}

		return true;
	}

	/**
	 * データベーストランザクションを開始する
	 */
	public static function beginTransaction(){
		global $debug_mode;
		if($debug_mode){ echo '[DB.class.php]beginTransaction() <br />'; }
		global $db_connection;
		global $db_transaction;
		$db_transaction = $db_connection;
		$db_transaction->beginTransaction();
	}

	/**
	 * データベーストランザクションをコミットする
	 */
	public static function commit(){
		global $debug_mode;
		if($debug_mode){ echo '[DB.class.php]commit() <br />'; }
		global $db_transaction_mode;
		global $db_transaction;
		if(isset($db_transaction)){
			$db_transaction->commit();
			$db_transaction = null;
		}
	}

	/**
	 * データベーストランザクションをロールバックする
	 */
	public static function rollBack(){
		global $debug_mode;
		if($debug_mode){ echo '[DB.class.php]rollBack() <br />'; }
		global $db_transaction_mode;
		global $db_transaction;
		if(isset($db_transaction)){
			$db_transaction->rollBack();
			$db_transaction = null;
		}
	}

	/**
	 * エラー出力用のSQLを生成する
	 */
	private static function makeRealSql($sql,$param){
		$sql = str_replace("?","'%s'",$sql);
		$sql = vsprintf($sql,$param);
		return $sql;
	}

	/**
	 * インスタンス変数に実行クエリログを保管する
	 * @param int $type 実行クエリタイプ
	 * @param string $sql SQL文
	 * @param string $param パラメータ
	 */
	private function setQueryLog($type,$sql,$param){
		$query_log = array("type" => $type,"sql" => $sql,"param" => $param,"check_sql" => self::makeRealSql($sql, $param));
		$this->query_log[] = $query_log;
	}

	/**
	 * 直前に取得した一覧データの全件数を取得する(LIMIT句を除去した件数を取得する)<br>
	 * ※ 複雑な副問合わせなどでは正確に件数を取得できない場合がありますので、注意してください。
	 * @return int データ件数
	 */
	public function getCountResultWithoutLimit(){
		$query_log = $this->query_log;			//クエリログを取得
		$query_log_count = count($query_log);	//クエリログ数を取得
		$sql = "";
		$param = array();
		//最後に実行したクエリから順に取得
		for($i=($query_log_count - 1);$i>=0;$i--){
			$log = $query_log[$i];
			//実行タイプが全てまたはSELECTのログで処理
			if($log["type"] == DataAccessBase::QUERY_TYPE_ALL || $log["type"] == DataAccessBase::QUERY_TYPE_SELECT){
				$sql = $log["sql"];
				$param = $log["param"];
				$pattern1 = '/SELECT[\s\S]*FROM/i';
				if(preg_match($pattern1,$sql)){

					//LIMIT句を除去
					$pattern2 = '/LIMIT[\s\S]*[0-9 ]+,[0-9 ]+[\s\S]*$/i';
					if(preg_match($pattern2,$sql)){
						$sql = preg_replace($pattern2, "", $sql);
					}

					//GROUP句有無により分岐
					$pattern3 = '/GROUP [^)]+$/i';
					if(preg_match($pattern3,$sql)){
						//SQL文全体を副問合わせ化する
						$sql = "SELECT count(*) cnt FROM (".$sql.") as wk";
					}else{
						//SELECT句をcount(*)に置換
						$replacement1 = "SELECT count(*) cnt FROM";
						$sql = preg_replace($pattern1, $replacement1, $sql,1);
					}

					$result = $this->query($sql,$param,DB::FETCH);
					return $result["cnt"];
				}
			}
		}
		return 0;
	}

	/**
	 * 直前に取得した一覧データの全件データを取得する(LIMIT句を除去したデータを取得する)<br>
	 * ※ 複雑な副問合わせなどでは正確に動作しない場合がありますので、注意してください。
	 * @return array 全件データ
	 */
	public function getResultWithoutLimit(){
		$query_log = $this->query_log;			//クエリログを取得
		$query_log_count = count($query_log);	//クエリログ数を取得
		$sql = "";
		$param = array();
		//最後に実行したクエリから順に取得
		for($i=($query_log_count - 1);$i>=0;$i--){
			$log = $query_log[$i];
			//実行タイプが全てまたはSELECTのログで処理
			if($log["type"] == DataAccessBase::QUERY_TYPE_ALL || $log["type"] == DataAccessBase::QUERY_TYPE_SELECT){
				$sql = $log["sql"];
				$param = $log["param"];
				$pattern1 = '/SELECT .* FROM/i';
				if(preg_match($pattern1,$sql)){
					//LIMIT句を除去
					$pattern2 = '/LIMIT *[0-9 ]+,[0-9 ]+ *$/i';
					if(preg_match($pattern2,$sql)){
						$sql = preg_replace($pattern2, "", $sql);
					}
					$result = $this->query($sql,$param);
					return $result;
				}
			}
		}
		return array();
	}
}

?>