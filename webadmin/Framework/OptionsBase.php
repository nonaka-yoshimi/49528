<?php
/*
 説明：選択肢ベースクラス
作成日：2013/05/19 TS谷
*/
abstract class OptionsBase
{
	/**
	 * リスト情報からドロップダウンリスト(SELECT)用のOptionタグを返却する
	 * @param array $list 一覧配列情報
	 * @param string $selected 選択項目（任意）
	 * @return string Optionタグ(HTML)
	 */
	static function toSelectOptions($list,$selected = "")
	{
		$str = "";
		foreach($list as $key => $value)
		{
			if($key == $selected && ($key != 0 || $selected != "")){ $selected_str = 'selected'; }else{ $selected_str = '';}
			$str .= "<option value='".$key."' ".$selected_str.">".$value."</option>";
		}
		return $str;
	}

	/**
	 * 整数数値を選択するドロップダウンリスト(SELECT)用のOptionタグを返却する
	 * @param int $start 開始数値
	 * @param int $end 終了数値
	 * @param int $selected 選択項目（任意）
	 * @param string $before_str 数値の前に付加する文字列（単位など）
	 * @param string $after_str 数値の後に付加する文字列（単位など）
	 * @return string Optionタグ(HTML)
	 */
	static function makeNumOptions($start,$end,$selected = "",$before_str = "",$after_str = "")
	{
		$str = "";
		for($i=$start;$i<=$end;$i++){
			if($selected != "" && $i == $selected){ $selected_str = 'selected'; }else{ $selected_str = '';}
			$str .= "<option value='".$i."' ".$selected_str.">".$before_str.$i.$after_str."</option>";
		}
		return $str;
	}

	/**
	 * リストボックス作成
	 * ※共通区分マスタ
	 * @param string name属性値
	 * @param array 共通区分マスタ一覧
	 * @param string デフォルト値
	 * @param string 空白行追加可否
	 * @param string key値のID
	 * @param string value値のID
	 * @return string Selectタグ(HTML)
	 */
	static function convTableListToSelect($name , $tableList , $defaultValue = '' , $addBlankRow = "0" , $key_id = "id" , $value_id = "name" ){
        //Selectタグ生成用リスト生成
	    $list = array();
	    foreach($tableList as $value){
	        $list[$value[$key_id]] = $value[$value_id];
	    }

	    $selected = ('' != $defaultValue) ? ' selected="selected "' : '';

	    //Selectタグ生成
	    $str = '<select name="'.$name.'" >';

	    if(SPConst::ADD_BLANK_ROW_HEAD == $addBlankRow) $str .= '<option value="" '.$selected.' />　';

	    $str .= OptionsBase::toSelectOptions($list,$defaultValue);

	    if(SPConst::ADD_BLANK_ROW_END == $addBlankRow) $str .= '<option value="" '.$selected.'  />　';

	    $str .= '</select>';

	    return $str;
	}

	/**
	 * リストボックス作成
	 * ※array(key=>value)
	 * @param string name属性値
	 * @param array 一覧(key=>value)
	 * @param デフォルト値
	 * @param 空白行追加可否
	 * @return string Selectタグ(HTML)
	 */
	static function convListToSelect($name , $list ,  $defaultValue = "", $addBlankRow = "0"){

	    $selected = ('' != $defaultValue) ? ' selected="selected "' : '';

	    //Selectタグ生成
	    $str = '<select name="'.$name.'" >';

	    if(SPConst::ADD_BLANK_ROW_HEAD == $addBlankRow) $str .= '<option value="" '.$selected.' />　';

	    $str .= OptionsBase::toSelectOptions($list,$defaultValue);

	    if(SPConst::ADD_BLANK_ROW_END == $addBlankRow) $str .= '<option value="" '.$selected.'  />　';

	    $str .= '</select>';

	    return $str;
	}
}