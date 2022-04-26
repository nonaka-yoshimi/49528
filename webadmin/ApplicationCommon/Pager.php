<?php
/*
 説明：ページャ管理クラス
作成日：2013/10/29 TS谷
*/

/**
 * ページャ管理クラス
 */
class Pager{

	/**
	 * ページャスタイル１
	 */
	const STYLE1 = "1";

	/**
	 * ページャスタイル２
	 */
	const STYLE2 = "2";

	/**
	 * ファイルパス
	 */
	var $filepath;

	/**
	 * GETパラメータ
	 */
	var $params;

	/**
	 * 開始番号
	 */
	var $start;

	/**
	 * 開始番号のパラメータ文字
	 */
	var $start_index;

	/**
	 * 1ページ当たりの表示データ数
	 */
	var $num;

	/**
	 * 表示データ数のパラメータ文字
	 */
	var $num_index;

	/**
	 * 全データ数
	 */
	var $countAll;

	/**
	 * 戻るボタンの文字列
	 */
	var $prev_str;

    /**
     * 次へボタンの文字列
     */
	var $next_str;

	/**
	 * ページャスタイル
	 */
	var $pager_style;


	/**
	 * ページャ管理クラスコンストラクタ
	 */
	function __construct(){
		//実行ファイル名取得
		$this->filepath = basename($_SERVER["SCRIPT_NAME"]);

		//GETパラメータ取得
		$this->params = $_GET;

		//初期設定
		$this->start_index = "start";
		$this->num_index = "num";
		$this->prev_str = "<< 前のページ";
		$this->next_str = "次のページ >>";

		//開始番号を設定
		if(isset($_GET[$this->start_index]) && is_numeric($_GET[$this->start_index])){
			$this->start = $_GET[$this->start_index];
		}else{
			$this->start = 0;
		}

		//表示データ数を設定
		if(isset($_GET[$this->num_index]) && is_numeric($_GET[$this->num_index])){
			$this->num = $_GET[$this->num_index];
		}else{
			$this->num = Config::DEFAULT_PAGE_LIST_NUM;
		}
	}

	/**
	 * データの総件数を設定する
	 * @param int データ総件数
	 * @return true/false
	 */
	function setCountAll($count){
		if($count == null || $count == "" || !is_numeric($count)){
			return false;
		}

		//データの総件数を設定する
		$this->countAll = $count;

		return true;
	}

	/**
	 * 開始番号を設定する
	 * @param int 開始番号
	 * @return true/false
	 */
	function setStart($start){
		if($start == null || $start == "" || !is_numeric($start)){
			return false;
		}

		//データの総件数を設定する
		$this->start = $start;

		return true;
	}

	/**
	 * 開始番号を取得する
	 * @return int 開始番号
	 */
	function getStart(){
		if(is_numeric($this->start)){
			return $this->start;
		}else{
			return 0;
		}
	}

	/**
	 * データ表示件数を設定する
	 * @param int データ表示件数
	 * @return true/false
	 */
	function setNum($num){
		if($num == null || $num == "" || !is_numeric($num)){
			return false;
		}

		//データの総件数を設定する
		$this->num = $num;

		return true;
	}

	/**
	 * データ表示件数を取得する
	 * @return int データ表示件数
	 */
	function getNum(){
		if(is_numeric($this->num)){
			return $this->num;
		}else{
			return 0;
		}
	}

	/**
	 * ページャのスタイルを設定する<br>
	 * Pager::STYLE1 or Pager::STYLE2(voice用)を設定
	 * @param int $style ページャスタイル
	 * @return true/false;
	 */
	function setStyle($style = "1"){
		if($style == null || $style == "" || !is_numeric($style)){
			return false;
		}

		//データの総件数を設定する
		$this->pager_style = $style;

		return true;
	}

	/**
	 * ページャ表示
	 */
	function display(){
		if($this->pager_style == null || $this->pager_style == "" || $this->pager_style == "1"){
			$this->display_style1();
		}elseif($this->pager_style == "2"){
			$this->display_style2();
		}
	}

	/**
	 * ページャスタイル１
	 */
	function display_style1(){

		if($this->countAll < 1){
			return;
		}

		//パラメータの再構築
		$param_str = "";
		foreach($this->params as $key => $value){
			if($key != $this->start_index && $key != $this->num_index){
				$param_str .= "&".$key."=".htmlspecialchars($value);
			}
		}

		//前へボタンの表示
		if($this->start > 0){
			$prev_start = $this->start - $this->num;
			if($prev_start < 0){ $prev_start = 0; };

			echo ' <a href="'.$this->filepath.'?'.$this->start_index.'='.$prev_start.'&num='.$this->num.$param_str.'">'.$this->prev_str.'</a> ';
		}else{
			echo " ".$this->prev_str." ";
		}

		echo "| ";

		//ページへのジャンプ設定

		//ページ数計算
		$page_num = ceil($this->countAll / $this->num);

		//ページ番号一覧出力
		for($i=1;$i<=$page_num;$i++){
			$start = $this->num * ($i - 1);
			if($start != $this->start){
				echo '<a href="'.$this->filepath.'?'.$this->start_index.'='.$start.'&num='.$this->num.$param_str.'">'.$i."</a> ";
			}else{
				echo $i.' ';
			}
		}
		echo "|";

		//次へボタンの表示
		if(($this->start + $this->num) < $this->countAll){
			$next_start = $this->start + $this->num;
			if($next_start < 0){ $next_start = 0; };
			echo ' <a href="'.$this->filepath.'?'.$this->start_index.'='.$next_start.'&num='.$this->num.$param_str.'">'.$this->next_str.'</a> ';
		}else{
			echo " ".$this->next_str." ";
		}
	}

	/**
	 * ページャスタイル２
	 */
	function display_style2(){

		if($this->countAll < 1){
			return;
		}

		if($this->countAll <= $this->num){
			return;
		}

		//パラメータの再構築
		$param_str = "";
		foreach($this->params as $key => $value){
			if($key != $this->start_index && $key != $this->num_index){
				$param_str .= "&".$key."=".htmlspecialchars($value);
			}
		}

		//前へボタンの表示
		if($this->start > 0){
			$prev_start = $this->start - $this->num;
			if($prev_start < 0){ $prev_start = 0; };

			echo ' <a href="'.$this->filepath.'?'.$this->start_index.'='.$prev_start.'&num='.$this->num.$param_str.'">'.$this->prev_str.'</a> ';
			echo "| ";
		}else{
			//echo " ".$this->prev_str." ";
		}



		//ページへのジャンプ設定

		//ページ数計算
		$page_num = ceil($this->countAll / $this->num);

		//ページ番号一覧出力
		for($i=1;$i<=$page_num;$i++){
			$start = $this->num * ($i - 1);
			if($i > 1){
				echo "|";
			}
			if($start != $this->start){
				echo ' <a href="'.$this->filepath.'?'.$this->start_index.'='.$start.'&num='.$this->num.$param_str.'">'.$i."</a> ";
			}else{
				echo $i.' ';
			}
		}


		//次へボタンの表示
		if(($this->start + $this->num) < $this->countAll){
			$next_start = $this->start + $this->num;
			if($next_start < 0){ $next_start = 0; };
			echo "|";
			echo ' <a href="'.$this->filepath.'?'.$this->start_index.'='.$next_start.'&num='.$this->num.$param_str.'">'.$this->next_str.'</a> ';
		}else{
			//echo " ".$this->next_str." ";
		}
	}
}

?>