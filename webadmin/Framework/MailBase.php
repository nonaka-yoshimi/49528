<?php
/*
説明：メール送信ベースクラス
作成日：2013/10/16 TS谷
*/

/**
 * メール送信ベースクラス
 * @author Tani
 *
 */
class MailBase
{
	/**
	 * FROMアドレス
	 */
	var $from;

	/**
	 * TOアドレス配列
	 */
	var $to;

	/**
	 * CCアドレス配列
	 */
	var $cc;

	/**
	 * BCCアドレス配列
	 */
	var $bcc;

	/**
	 * タイトル
	 */
	var $subject;

	/**
	 * 本文
	 */
	var $body;

	/**
	 * 送信者表示名
	 */
	var $from_name;

	/**
	 * return-path
	 */
	var $return_path;

	/**
	 * 使用マルチバイト言語コード
	 */
	var $mb_language;

	/**
	 * 使用内部エンコード
	 */
	var $mb_internal_encoding;

	/**
	 * ヘッダ：Content-Type charset
	 */
	var $content_type_charset;

	/**
	 * ヘッダ：Content-Type boundary
	 */
	var $content_type_boundary;

	/**
	 * ヘッダ：Content-Transfer-Encoding
	 */
	var $content_transfer_encoding;

	/**
	 * 添付ファイル配列
	 */
	var $attach;

	/**
	 * 文字列置換データ配列
	 */
	var $replace;

	/**
	 * コンストラクタ
	 */
	function __construct()
	{
		$this->from = ""; //FROMアドレス初期値
		$this->to = array(); //TOアドレス初期値
		$this->cc = array(); //CCアドレス初期値
		$this->bcc = array(); //BCCアドレス初期値
		$this->subject = ""; //タイトル初期値
		$this->body = ""; //本文初期値
		$this->from_name = ""; //送信者表示名初期値
		$this->return_path = ""; //return_path初期値
		$this->mb_language = "ja"; //使用マルチバイト言語コード初期値
		$this->mb_internal_encoding = "UTF-8"; //使用マルチバイト言語コード初期値
		$this->content_type_charset = "iso-2022-jp";  //ヘッダ：Content-Type charset初期値
		$this->content_type_boundary = "__BOUNDARY__";  //ヘッダ：Content-Type boundary初期値
		$this->content_transfer_encoding = "7bit";  //ヘッダ：Content-Transfer-Encoding初期値
		$this->attach = array(); //添付ファイル初期値
		$this->replace = array(); //文字列置換データ初期値
	}

	/**
	 * FROMアドレス設定処理
	 * @param string メールアドレス
	 * @return boolean true:成功/false:失敗
	 */
	function setFrom($address)
	{
		if($address == null || $address == ""){
			return false;
		}

		//FROMアドレスを設定する
		$this->from = $address;

		return true;
	}

	/**
	 * TOアドレス設定処理
	 * @param string メールアドレス
	 * @return boolean true:成功/false:失敗
	 */

	function setTo($address)
	{
		if($address == null || $address == ""){
			return false;
		}

		//TOアドレスを設定する
		$this->to = array();
		if(is_array($address)){
			foreach($address as $value){
				$this->to[] = $value;
			}
		}else{
			$this->to[] = $address;
		}

		return true;
	}

	/**
	 * TOアドレス追加処理
	 * @param string メールアドレス
	 * @return boolean true:成功/false:失敗
	 */
	function addTo($address)
	{
		if($address == null || $address == ""){
			return false;
		}

		//TOアドレスを追加する
		if(is_array($address)){
			foreach($address as $value){
				$this->to[] = $value;
			}
		}else{
			$this->to[] = $address;
		}

		return true;
	}

	/**
	 * CCアドレス設定処理
	 * @param string メールアドレス
	 * @return boolean true:成功/false:失敗
	 */
	function setCc($address)
	{
		if($address == null || $address == ""){
			return false;
		}

		//CCアドレスを設定する
		$this->cc = array();
		if(is_array($address)){
			foreach($address as $value){
				$this->cc[] = $value;
			}
		}else{
			$this->cc[] = $address;
		}

		return true;
	}

	/**
	 * CCアドレス追加処理
	 * @param string メールアドレス
	 * @return boolean true:成功/false:失敗
	 */
	function addCc($address)
	{
		if($address == null || $address == ""){
			return false;
		}

		//CCアドレスを追加する
		if(is_array($address)){
			foreach($address as $value){
				$this->cc[] = $value;
			}
		}else{
			$this->cc[] = $address;
		}

		return true;
	}

	/**
	 * BCCアドレス設定処理
	 * @param string メールアドレス
	 * @return boolean true:成功/false:失敗
	 */
	function setBcc($address)
	{
		if($address == null || $address == ""){
			return false;
		}

		//BCCアドレスを設定する
		$this->bcc = array();
		if(is_array($address)){
			foreach($address as $value){
				$this->bcc[] = $value;
			}
		}else{
			$this->bcc[] = $address;
		}

		return true;
	}

	/**
	 * BCCアドレス追加処理
	 * @param string メールアドレス
	 * @return boolean true:成功/false:失敗
	 */
	function addBcc($address)
	{
		if($address == null || $address == ""){
			return false;
		}

		//BCCアドレスを追加する
		if(is_array($address)){
			foreach($address as $value){
				$this->bcc[] = $value;
			}
		}else{
			$this->bcc[] = $address;
		}

		return true;
	}

	/**
	 * メールタイトル設定処理
	 * @param string タイトル
	 * @return boolean true:成功/false:失敗
	 */
	function setSubject($subject)
	{
		if($subject == null || $subject == ""){
			return false;
		}

		//メールタイトルを設定する
		$this->subject = $subject;

		return true;
	}

	/**
	 * メール本文設定処理
	 * @param string メール本文
	 * @return boolean true:成功/false:失敗
	 */
	function setBody($body)
	{
		if($body == null || $body == ""){
			return false;
		}

		//メール本文を設定する
		$this->body = $body;

		return true;
	}

	/**
	 * メール本文追加処理
	 * @param string メール本文
	 * @return boolean true:成功/false:失敗
	 */
	function addBody($body)
	{
		if($body == null || $body == ""){
			return false;
		}

		//メール本文を追加する
		$this->body .= $body;

		return true;
	}

	/**
	 * 送信者表示設定処理
	 * @param string 送信者表示名
	 * @return boolean true:成功/false:失敗
	 */
	function setFromName($from_name)
	{
		if($from_name == null || $from_name == ""){
			return false;
		}

		//送信者表示名を設定する
		$this->from_name = $from_name;

		return true;
	}

	/**
	 * return-path設定処理
	 * @param string return-path
	 * @return boolean true:成功/false:失敗
	 */
	function setReturnPath($return_path)
	{
		if($return_path == null || $return_path == ""){
			return false;
		}

		//return-pathを設定する
		$this->return_path = $return_path;

		return true;
	}

	/**
	 * 添付ファイル設定処理
	 * @param string ファイルパス
	 * @param string ファイル名
	 * @return boolean true:成功/false:失敗
	 */
	function setAttach($filepath,$filename)
	{
		if($filepath == null || $filepath == ""){
			return false;
		}
		if($filename == null || $filename == ""){
			return false;
		}

		//添付ファイル情報を設定する
		$this->attach = array();
		$this->attach[0]['filepath'] = $filepath;
		$this->attach[0]['filename'] = $filename;

		return true;
	}

	/**
	 * 添付ファイル追加処理
	 * @param string ファイルパス
	 * @param string ファイル名
	 * @return boolean true:成功/false:失敗
	 */
	function addAttach($filepath,$filename)
	{
		if($filepath == null || $filepath == ""){
			return false;
		}
		if($filename == null || $filename == ""){
			return false;
		}

		//添付ファイル情報を追加する
		$attach = array("filepath" => $filepath,"filename" => $filename);
		$this->attach[] = $attach;

		return true;
	}

	/**
	 * 置換文字列データ追加処理
	 * @param array 置換文字列データ（連想配列で格納します）例：array("%%%replace%%%" => "変換後文字列")
	 * @return boolean true:成功/false:失敗
	 */
	function setReplace($replace)
	{
		if($replace == null || !is_array($replace)){
			return false;
		}

		//置換文字列データを追加する
		$this->replace = $replace;

		return true;
	}

	/**
	 * メール送信実行処理
	 * @return boolean true:成功/false:失敗
	 */
	function send()
	{
		global $debug_mode;

		//FROMアドレス設定チェック
		if($this->from == null || $this->from == "")
		{
			if($debug_mode){ echo "FROMアドレス未設定<br />"; }
			return false;
		}

		//TO,CC,BCC設定チェック
		if(count($this->to) == 0 && count($this->cc) == 0 && count($this->bcc) == 0)
		{
			if($debug_mode){ echo "TO,CC,BCCアドレス未設定<br />"; }
			return false;
		}

		//内部処理エンコードを設定
		mb_language($this->mb_language);
		mb_internal_encoding($this->mb_internal_encoding);

		//送信者名設定
		if($this->from_name != null && $this->from_name != ""){
			$from_name = $this->from_name;
		}else{
			$from_name = $this->from;
		}

		//return-path設定
		if($this->return_path != null && $this->return_path != ""){
			$parameter = "-f ".$this->return_path;
		}else{
			$parameter = "-f ".$this->from;
		}

		//文字列置換が設定されている場合は置換処理
		if(count($this->replace) > 0){
			//タイトルの文字列置換処理
			$subject = str_replace(array_keys($this->replace),array_values($this->replace), $this->subject);

			//本文の文字列置換処理
			$bodyMain = str_replace(array_keys($this->replace),array_values($this->replace), $this->body);
		}else{
			$subject = $this->subject;
			$bodyMain = $this->body;
		}

		//ヘッダ情報を設定
		$headers = "From: " . mb_encode_mimeheader($from_name) . "<".$this->from.">\r\n";

		//添付ファイル有無によって切替
		if(count($this->attach) > 0){
			//添付ファイル付きメール
			$headers .= "Content-Type: multipart/mixed; boundary=\"".$this->content_type_boundary."\"\r\n";
		}else{
			//プレーンテキストメール
			$headers .= "Content-Type: text/plain; charset=".$this->content_type_charset."\r\n";
		}
		$headers .= "Content-Transfer-Encoding: ".$this->content_transfer_encoding."\r\n";

		//TOアドレスを設定
		$to = "";
		for($i=0;$i<count($this->to);$i++)
		{
			if($i > 0){ $to .= ","; }
			$to .= $this->to[$i];
		}

		//CCアドレスを設定
		if(count($this->cc) > 0)
		{
			$cc = "Cc: ";
			for($i=0;$i<count($this->cc);$i++)
			{
				if($i > 0){ $cc .= ","; }
				$cc .= $this->cc[$i];
			}
			$headers .= $cc."\r\n"; //ヘッダに追加
		}

		//BCCアドレスを設定
		if(count($this->bcc) > 0)
		{
			$bcc = "Bcc: ";
			for($i=0;$i<count($this->bcc);$i++)
			{
			if($i > 0){ $bcc .= ","; }
			$bcc .= $this->bcc[$i];
		}
			$headers .= $bcc."\r\n"; //ヘッダに追加
		}

		//本文部分を生成
		$body = "";
		if(count($this->attach) > 0){
			//添付ファイル付きメール
			$body .= "--".$this->content_type_boundary."\r\n";
			$body .= "Content-Type: text/plain; charset=\"".$this->content_type_charset."\"\r\n";
			$body .= "\r\n";
			$body .= $bodyMain."\r\n";

			//添付ファイル付加処理
			for($i=0;$i<count($this->attach);$i++){
				$attach = $this->attach[$i]; //添付ファイル情報1件取得

				//ファイルが実在しない場合は処理を終了する
				if(!file_exists($attach["filepath"])){
					if($debug_mode){ echo "添付ファイルが読み込めませんでした：".$attach["filepath"]."<br />"; }
					return false;
				}

				//添付ファイルを読み込み
				$handle = fopen($attach['filepath'], 'r');
				$attachFile = fread($handle, filesize($attach['filepath']));
				fclose($handle);
				$attachEncode = base64_encode($attachFile);

				//ファイル名に正しい拡張子が設定されていない場合は元ファイルの拡張子を付加
				if($this->getFileExtension($attach['filepath']) != $this->getFileNameExtension($attach['filename']))
				{
					$attach_filename = $attach['filename'].".".$this->getFileExtension($attach['filepath']);
				}else{
					$attach_filename = $attach['filename'];
				}

				//添付ファイルを出力
				$body .= "--".$this->content_type_boundary."\r\n";
				$body .= "Content-Type: ".$this->getContentType($attach['filepath'])."; name=\"".$attach_filename."\"\r\n";
				$body .= "Content-Transfer-Encoding: base64\r\n";
				$body .= "Content-Disposition: attachment; filename=\"".mb_encode_mimeheader($attach_filename)."\"\r\n";
				$body .= "\r\n";
				$body .= chunk_split($attachEncode)."\r\n";
			}
		}else{
			//プレーンテキストメール
			$body = $bodyMain;
		}

		//デバッグモードの場合ヘッダ情報を画面出力
		if($debug_mode){
			$headers_output = str_replace("\n","<br />", $headers);
			echo $headers_output."<br />";
			echo $body;
		}

		//メール送信実行(セーフモードがONの場合は第5引数は使用できない)
		if (ini_get('safe_mode')) {
			$result = mb_send_mail($to,$subject,$body,$headers);
		}else{
			$result = mb_send_mail($to,$subject,$body,$headers,$parameter);
		}

		//メール送信に失敗した場合
		if(!$result){
			if($debug_mode){ echo "メール送信失敗<br />"; }
			return false;
		}

		return true;
	}

	/**
	 * Content-Type取得処理(getimagesize()使用,PHPのGD拡張が必要)
	 * @return string Content-Type文字列
	 */
	function getContentType($filepath)
	{
		//ファイルが実在しない場合は終了
		if(!file_exists($filepath)){
			return "";
		}

		//mimeタイプを取得し返却する
		$ret = getimagesize($filepath);
		return $ret["mime"];
	}

	/**
	 * 拡張子取得処理(実在ファイル)
	 * @return string 拡張子文字列
	 */
	static function getFileExtension($filepath)
	{
		//ファイルが実在しない場合は終了
		if(!file_exists($filepath)){
			return "";
		}

		//拡張子を取得し返却する
		$extension = pathinfo($filepath, PATHINFO_EXTENSION);
		return $extension;
	}

	/**
	 * 拡張子取得処理(ファイル名称)
	 * @return string 拡張子文字列
	 */
	static function getFileNameExtension($filepath)
	{
		if($filepath == null || $filepath == ""){
			return "";
		}

		$arr = explode(".",$filepath);
		return $arr[count($arr) - 1];
	}
}

?>