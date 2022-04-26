<?php
//アプリケーション共通クラス読込
require_once(dirname(__FILE__).'/../../ApplicationCommon/include.php');
require_once(dirname(__FILE__).'/../../DataAccess/AddInfoSelect.php');			//追加情報選択肢クラス

//セッション取得
$session = Session::get();

//セッション認証
$session->loginCheckAndRedirect("../../login.php?msg=session_error");

//リクエストパラメータ取得
$id_list = isset($_REQUEST["id_list"]) ? $_REQUEST["id_list"] : "";			//ID配列

//並べ替え処理の実行
if(!Util::IsNullOrEmpty($id_list) && is_array($id_list) && count($id_list) > 0){
	DB::beginTransaction();

	$AddInfoSelect = new AddInfoSelect();		//追加情報選択肢クラス
	$counter = 1;
	for($i=0;$i<count($id_list);$i++){
		//並べ替えの実行
		if(!$AddInfoSelect->update(array("addinfo_select_id" => $id_list[$i]), array("sort_no" => $counter))){
			Logger::error("追加情報選択肢並べ替え失敗");
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
