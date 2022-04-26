<?php
/*
説明：選択リストクラス
作成日：2013/05/12 TS谷
*/

/**
 * 選択リストクラス
 */
class Options extends OptionsBase
{
	static function admin_flg()
	{
		$list = array();
		$list[SPConst::ADMIN_FLG_NO] = "一般ユーザ";
		$list[SPConst::ADMIN_FLG_YES] = "管理者";
		return $list;
	}

	static function admintype()
	{
		$list = array();
		$list[SPConst::ADMINTYPE_NORMAL] = "一般管理者";
		$list[SPConst::ADMINTYPE_SUPERVISOR] = "システム管理者";
		return $list;
	}

	static function language()
	{
		$list = array();
		$list[SPConst::LANGUAGE_JP] = "日本語";
		$list[SPConst::LANGUAGE_EN] = "英語";
		return $list;
	}


	static function contentclass()
	{
		$list = array();
		$list[SPConst::CONTENTCLASS_PAGE] = "ページ";
		$list[SPConst::CONTENTCLASS_PARTS] = "部品";
		$list[SPConst::CONTENTCLASS_IMAGE] = "イメージ";
		$list[SPConst::CONTENTCLASS_FILE] = "ファイル";
		$list[SPConst::CONTENTCLASS_TEMPLATE] = "テンプレート";
		$list[SPConst::CONTENTCLASS_STYLESHEET] = "スタイルシート";
		$list[SPConst::CONTENTCLASS_SCRIPT] = "スクリプト";
		return $list;
	}

	static function inputtype()
	{
		$list = array();
		$list[SPConst::INPUTTYPE_SHORT_TEXT] = "１行テキスト（小）";
		$list[SPConst::INPUTTYPE_MIDDLE_TEXT] = "１行テキスト（中）";
		$list[SPConst::INPUTTYPE_LONG_TEXT] = "１行テキスト（大）";
		$list[SPConst::INPUTTYPE_SMALL_TEXTAREA] = "テキストエリア（小）";
		$list[SPConst::INPUTTYPE_MIDDLE_TEXTAREA] = "テキストエリア（中）";
		$list[SPConst::INPUTTYPE_LARGE_TEXTAREA] = "テキストエリア（大）";
		//$list[SPConst::INPUTTYPE_CKEDITOR] = "エディタ";
		$list[SPConst::INPUTTYPE_DATE] = "日付";
		$list[SPConst::INPUTTYPE_TIME] = "時間";
		$list[SPConst::INPUTTYPE_DATETIME] = "日時";
		$list[SPConst::INPUTTYPE_SELECT] = "選択式";
		$list[SPConst::INPUTTYPE_CHECKBOX] = "チェックボックス";
		$list[SPConst::INPUTTYPE_CONTENT] = "コンテンツ（全ての種類）";
		$list[SPConst::INPUTTYPE_PAGE] = "コンテンツ（ページ）";
		$list[SPConst::INPUTTYPE_ELEMENT] = "コンテンツ（部品）";
		$list[SPConst::INPUTTYPE_IMAGE] = "コンテンツ（イメージ）";
		$list[SPConst::INPUTTYPE_FILE] = "コンテンツ（ファイル）";
		return $list;
	}

	static function operation_history(){
		$list = array();
		$list[SPConst::OPERATION_HISTORY_NEW] = "新規追加";
		$list[SPConst::OPERATION_HISTORY_EDIT] = "編集";
		$list[SPConst::OPERATION_HISTORY_DELETE] = "削除";
		return $list;
	}

	static function schedule_type(){
		$list = array();
		$list[SPConst::SCHEDULE_TYPE_REPLACE] = "公開中のコンテンツと差替える";
		$list[SPConst::SCHEDULE_TYPE_TEMPORARY] = "一時的に公開する";
		return $list;
	}

	static function archive_type(){
		$list = array();
		$list[SPConst::ARCHIVE_TYPE_MANUAL] = "手動";
		$list[SPConst::ARCHIVE_TYPE_PUBLISH] = "公開時に自動";
		$list[SPConst::ARCHIVE_TYPE_SAVE] = "保存時に自動";
		return $list;
	}

	static function edit_mode(){
		$list = array();
		$list[SPConst::EDIT_MODE_DEFAULT] = "デフォルト";
		$list[SPConst::EDIT_MODE_TEXTAREA] = "テキストエディタ";
		$list[SPConst::EDIT_MODE_CKEDITOR] = "WYSIWIGエディタ";
		return $list;
	}

	static function static_mode(){
		$list = array();
		$list[SPConst::STATIC_MODE_DYNAMIC] = "動的ページとして作成";
		$list[SPConst::STATIC_MODE_STATIC] = "静的ページとして作成";
		return $list;
	}

	static function php_mode(){
		$list = array();
		$list[SPConst::PHP_MODE_NO] = "動作させない";
		$list[SPConst::PHP_MODE_YES] = "動作させる";
		return $list;
	}

	static function restrict_active_nonactive(){
		$list = array();
		$list[SPConst::RESTRICT_NONACTIVE] = "無効";
		$list[SPConst::RESTRICT_ACTIVE] = "有効";
		return $list;
	}

	static function restrict_level(){
		$list = array();
		$list[SPConst::RESTRICT_NONE] = "無効";
		$list[SPConst::RESTRICT_READONLY] = "読取専用";
		$list[SPConst::RESTRICT_ENABLE] = "有効";
		return $list;
	}

	static function workflow_user_restriction(){
		$list = array();
		$list[SPConst::WORKFLOW_USER_RESTRICTION_ALL] = "全て";
		$list[SPConst::WORKFLOW_USER_RESTRICTION_DRAFTER_ONLY] = "起案者のみ";
		$list[SPConst::WORKFLOW_USER_RESTRICTION_EXCEPT_DRAFTER] = "起案者以外";
		return $list;
	}

	/**
	 * 曜日
	 */
	static function weekday(){
	    $list = array();
	    $list[0] = "日";
	    $list[1] = "月";
	    $list[2] = "火";
	    $list[3] = "水";
	    $list[4] = "木";
	    $list[5] = "金";
	    $list[6] = "土";
	    return $list;
	}

	/**
	 * 時間（時）<br>
	 * 00～23
	 */
	static function hour(){
		$list = array();
		for($i=0;$i<=23;$i++){
			$list[$i] = STR_PAD($i,2,"0",STR_PAD_LEFT);
		}
		return $list;
	}

	/**
	 * 時間（分）<br>
	 * 00～59
	 */
	static function minute(){
		$list = array();
		for($i=0;$i<=59;$i++){
			$list[$i] = STR_PAD($i,2,"0",STR_PAD_LEFT);
		}
		return  $list;
	}
}

?>