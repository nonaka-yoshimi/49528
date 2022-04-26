<?php
require_once(dirname(__FILE__).'/../ApplicationCommon/Logger.php'); //ログ取得
require_once(dirname(__FILE__).'/EngineCommon.php'); //エンジン共通
/*
説明：ファイルエンジンクラス
作成日：2013/11/30 TS谷
*/

/**
 * ファイルエンジンクラス
 * ファイルをレスポンスする
 */
class FileEngine
{

	var $device;

	/**
	 * ファイルを出力する
	 * @param string $url URL
	 * @param array $session セッション
	 * @param array $domain ドメイン
	 * @param array $device デバイス
	 * @param array $extensionInfo 拡張子情報
	 * @param array $config ファイル出力設定
	 */
	function outputFile($url,$session,$domain,$device,$extensionInfo,$config){

		$this->device = $device;

		//最終更新時間条件を取得
		if(isset($_SERVER['HTTP_IF_MODIFIED_SINCE'])){
			$if_modified_timestamp = EngineCommon::parse_http_date($_SERVER['HTTP_IF_MODIFIED_SINCE']);
		}else{
			$if_modified_timestamp = 0;
		}

		//ファイル最終更新時間を取得
		$filemtime = filemtime($url);

		//ファイルサイズを取得
		$filesize = filesize($url);

		//mimeタイプ
		if(!isset($extensionInfo["mime"]) || !$extensionInfo["mime"]){
			$finfo    = finfo_open(FILEINFO_MIME_TYPE);
			$extensionInfo["mime"] = finfo_file($finfo, $url);
			finfo_close($finfo);
		}

		//Accept-Ranges設定を取得
		$accept_ranges = isset($extensionInfo["accept-ranges"]) ? $extensionInfo["accept-ranges"] : false;

		//URL・最終更新時間・ファイルサイズをMD5したものをEtagのIDとする
		$Etag = md5( $_SERVER["REQUEST_URI"] . $filemtime . $filesize );

		//ファイル分割要求取得
		$offset = 0;
		if(isset($_SERVER["HTTP_RANGE"])){
			//ファイルを分割する場合
			$partialContent = true;
			preg_match('/bytes=(\d+)-(\d+)?/', $_SERVER['HTTP_RANGE'], $matches);
			$offset = intval($matches[1]);
			$length = intval($matches[2]) - $offset + 1;
		}else{
			//ファイルを分割しない場合
			$partialContent = false;
			$length = $filesize;
		}

		if($partialContent){
			//分割ファイル出力処理
			//ファイル読込処理
			$file = fopen($url, 'r');
			fseek($file, $offset);
			$data = fread($file, $length);
			fclose($file);

			//ヘッダ出力処理
			header( "HTTP/1.1 206 Partial Content");
			if(Config::STATIC_FILE_BROWSER_CACHE){
				header( "Last-Modified: " . gmdate( "D, d M Y H:i:s", $filemtime ) . " GMT" );	//最終更新時間の出力
			}
			header( "Etag: \"{$Etag}\"");                                 						//キャッシュ用Etagの出力
			header( "Content-Type: ".$extensionInfo["mime"]);									//mime typeの出力
			header( "Accept-Ranges: bytes");													//分割アクセスを許容
			header( "Content-Length: ".$length);												//コンテンツサイズを出力
			header( "Content-Range: bytes " . $offset . "-" . ($offset + $length) . "/" . $filesize);
			//header( "Expires: ". gmdate( "D, d M Y H:i:s", time() + 31536000 ) . " GMT" );	//ファイル有効期限切れ予定日時指定を1年後に設定
			/*
			header_remove("Expires");
			header_remove("Cache-Control");
			header_remove("Pragma");
			header_remove("Transfer-Encoding");
			*/
			header("Expires:");
			header("Cache-Control:");
			header("Pragma:");
			//header("Transfer-Encoding:");

			//ファイル出力処理
			print($data);
			exit;
		}elseif($if_modified_timestamp < $filemtime){

			if($accept_ranges){
				//ファイル分割許可モード
				//ファイル読込処理
				$file = fopen($url, 'r');
				fseek($file, $offset);
				$data = fread($file, $length);
				fclose($file);

				//ヘッダ出力処理
				if(Config::STATIC_FILE_BROWSER_CACHE){
					header( "Last-Modified: " . gmdate( "D, d M Y H:i:s", $filemtime ) . " GMT" );	//最終更新時間の出力
				}
				header( "Etag: \"{$Etag}\"");                                 						//キャッシュ用Etagの出力
				header( "Content-Type: ".$extensionInfo["mime"]);									//mime typeの出力
				header( "Accept-Ranges: bytes");													//分割アクセスを許容
				header( "Content-Length: ".$length);												//コンテンツサイズを出力
				//header( "Expires: ". gmdate( "D, d M Y H:i:s", time() + 31536000 ) . " GMT" );	//ファイル有効期限切れ予定日時指定を1年後に設定
				/*
				header_remove("Expires");
				header_remove("Cache-Control");
				header_remove("Pragma");
				header_remove("Transfer-Encoding");
				*/
				header("Expires:");
				header("Cache-Control:");
				header("Pragma:");
				//header("Transfer-Encoding:");

				//ファイル出力処理
				print($data);
				exit;
			}else{
				//ファイル分割不可モード
				//ヘッダ出力処理
				if(Config::STATIC_FILE_BROWSER_CACHE){
					header( "Last-Modified: " . gmdate( "D, d M Y H:i:s", $filemtime ) . " GMT" );	//最終更新時間の出力
				}

				header( "Etag: \"{$Etag}\"");                                 						//キャッシュ用Etagの出力
				header( "Content-Type: ".$extensionInfo["mime"]);									//mime typeの出力
				header( "Content-Length: ".$length);												//コンテンツサイズを出力
				//header( "Expires: ". gmdate( "D, d M Y H:i:s", time() + 31536000 ) . " GMT" );	//ファイル有効期限切れ予定日時指定を1年後に設定
				/*
				header_remove("Expires");
				header_remove("Cache-Control");
				header_remove("Pragma");
				header_remove("Transfer-Encoding");
				*/
				header("Expires:");
				header("Cache-Control:");
				header("Pragma:");
				//header("Transfer-Encoding:");

				//ファイル出力処理
				if(isset($config["php_mode"]) && $config["php_mode"]){

					include($url);
				}else{

					readfile($url);
				}
				exit;
			}
		}else{
			//変更なしヘッダを出力
			header( "HTTP/1.1 304 Not Modified" );
			header( "Etag: \"{$Etag}\"");
			/*
			header_remove("Expires");
			header_remove("Cache-Control");
			header_remove("Pragma");
			header_remove("Transfer-Encoding");
			*/
			header("Expires:");
			header("Cache-Control:");
			header("Pragma:");
			//header("Transfer-Encoding:");
			exit;
		}
	}
}
?>