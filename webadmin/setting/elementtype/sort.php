<?php
//アプリケーション共通クラス読込
require_once(dirname(__FILE__).'/../../ApplicationCommon/include.php');
require_once(dirname(__FILE__).'/../../DataAccess/ElementType.php');			//要素種別クラス

//セッション取得
$session = Session::get();

//セッション認証
$session->loginCheckAndRedirect("../../login.php?msg=session_error");

//リクエストパラメータ取得
$id_list = isset($_REQUEST["id_list"]) ? $_REQUEST["id_list"] : "";			//ID配列

//並べ替え処理の実行
if(!Util::IsNullOrEmpty($id_list) && is_array($id_list) && count($id_list) > 0){
	DB::beginTransaction();

	$ElementType = new ElementType();		//要素種別クラス
	$counter = 1;
	for($i=0;$i<count($id_list);$i++){
		//並べ替えの実行
		if(!$ElementType->update(array("elementtype_id" => $id_list[$i]), array("sort_no" => $counter))){
			Logger::error("要素種別並べ替え失敗");
			DB::rollBack();
			$result = "error";
			header("Content-Type: application/json; charset=utf-8");
			echo json_encode($result);
			exit;
		}
		$counter++;
	}
	DB::commit();
	$result = "success";
}else{
	$result = "error";
}

header("Content-Type: application/json; charset=utf-8");
echo json_encode($result);
?>
