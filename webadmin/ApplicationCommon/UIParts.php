<?php
/*
 説明：ユーザインターフェース部品クラス
作成日：2013/12/8 TS谷
*/

/**
 * ユーザインターフェース部品クラス
*/
class UIParts
{
	static function shortText($name,$value = "",$restrict = SPConst::RESTRICT_ENABLE){
		$str = "";
		if($restrict == SPConst::RESTRICT_READONLY){
			$readonly = "readonly";
		}else{
			$readonly = "";
		}
		$str.= '<input type="text" class="input_short '.$readonly.'" name="'.$name.'" value="'.htmlspecialchars($value).'" '.$readonly.' />';
		return $str;
	}

	static function middleText($name,$value = "",$restrict = SPConst::RESTRICT_ENABLE){
		$str = "";
		if($restrict == SPConst::RESTRICT_READONLY){
			$readonly = "readonly";
		}else{
			$readonly = "";
		}
		$str.= '<input type="text" class="input_middle '.$readonly.'" name="'.$name.'" value="'.htmlspecialchars($value).'" '.$readonly.' />';
		return $str;
	}

	static function longText($name,$value = "",$restrict = SPConst::RESTRICT_ENABLE){
		$str = "";
		if($restrict == SPConst::RESTRICT_READONLY){
			$readonly = "readonly";
		}else{
			$readonly = "";
		}
		$str.= '<input type="text" class="input_long '.$readonly.'" name="'.$name.'" value="'.htmlspecialchars($value).'" '.$readonly.' />';
		return $str;
	}

	static function shortPassword($name,$value = "",$restrict = SPConst::RESTRICT_ENABLE){
		$str = "";
		if($restrict == SPConst::RESTRICT_READONLY){
			$readonly = "readonly";
		}else{
			$readonly = "";
		}
		$str.= '<input type="password" class="input_short '.$readonly.'" name="'.$name.'" value="'.htmlspecialchars($value).'" '.$readonly.' />';
		return $str;
	}

	static function smallTextArea($name,$value = "",$restrict = SPConst::RESTRICT_ENABLE){
		$str = "";
		if($restrict == SPConst::RESTRICT_READONLY){
			$readonly = "readonly";
		}else{
			$readonly = "";
		}
		$str.= '<textarea class="small_textarea '.$readonly.'" name="'.$name.'" '.$readonly.'>'.htmlspecialchars($value).'</textarea>';
		return $str;
	}

	static function middleTextArea($name,$value = "",$restrict = SPConst::RESTRICT_ENABLE){
		$str = "";
		if($restrict == SPConst::RESTRICT_READONLY){
			$readonly = "readonly";
		}else{
			$readonly = "";
		}
		$str.= '<textarea class="middle_textarea '.$readonly.'" name="'.$name.'" '.$readonly.'>'.htmlspecialchars($value).'</textarea>';
		return $str;
	}

	static function largeTextArea($name,$value = "",$restrict = SPConst::RESTRICT_ENABLE){
		$str = "";
		if($restrict == SPConst::RESTRICT_READONLY){
			$readonly = "readonly";
		}else{
			$readonly = "";
		}
		$str.= '<textarea class="large_textarea '.$readonly.'" name="'.$name.'" '.$readonly.'>'.htmlspecialchars($value).'</textarea>';
		return $str;
	}

	/**
	 * 選択肢(SELECT)を出力する
	 * @param string $name NAME属性値
	 * @param array $list
	 * @param string $selected
	 * @param string $restrict
	 * @return string
	 */
	static function select($name,$list,$selected = "",$restrict = SPConst::RESTRICT_ENABLE){
		$str = "";
		if($restrict == SPConst::RESTRICT_READONLY){
			foreach($list as $key => $value){
				if($key == $selected){
					$str.= '<input type="text" class="readonly" name="'.$name.'" value="'.htmlspecialchars($value).'" readonly />';
					$str.= '<input type="hidden" name="'.$name.'" value="'.htmlspecialchars($key).'" />';
				}
			}
		}else{
			$str.= '<select name="'.$name.'" >';
			foreach($list as $key => $value){
				$selected_str = "";
				if($key == $selected){
					$selected_str = "selected=selected";
				}
				$str.= '<option value="'.$key.'" '.$selected_str.'>'.htmlspecialchars($value).'</option>';
			}
			$str.= '</select>';
		}
		return $str;
	}

	/**
	 * ラジオボタンを出力する
	 * @param string $name NAME属性値
	 * @param unknown $list
	 * @param unknown $checked
	 * @param unknown $restrict
	 */
	static function radio($name,$list,$checked,$restrict = SPConst::RESTRICT_ENABLE){
		$str = "";
		if($restrict == SPConst::RESTRICT_READONLY){
			$counter = 0;
			foreach($list as $key => $value){
				if($key == $checked){
					$str.= '&nbsp;<input type="radio" name="'.$name.'" value="'.$key.'" checked=checked >'.$value;
				}else{
					$str.= '&nbsp;<input type="radio" name="'.$name.'_dummy_'.$counter.'" value="'.$key.'" onclick="$(this).removeAttr(\'checked\')" >'.$value;
				}
				$counter++;
			}
		}else{
			foreach($list as $key => $value){
				$checked_str = "";
				if($key == $checked){
					$checked_str = "checked=checked";
				}
				$str.= '&nbsp;<input type="radio" name="'.$name.'" value="'.$key.'" '.$checked_str.' >'.$value;
			}
		}
		return $str;
	}

	/**
	 * チェックボックスを出力する
	 * @param string $name NAME属性値
	 * @param string $value VALUE属性値
	 * @param string $checked チェック
	 * @param int $restrict ユーザ制限
	 * @return string
	 */
	static function checkbox($name,$value,$checked,$restrict = SPConst::RESTRICT_ENABLE){
		$str = "";
		if($restrict == SPConst::RESTRICT_READONLY){
			$checked_str = "";
			if($checked != ""){
				$checked_str = "checked=checked";
			}
			$str.= '<input type="checkbox" '.$checked_str.' disabled />';
			$str.= '<input type="hidden" name="'.$name.'" value="'.$value.'" />';
		}else{
			$checked_str = "";
			if($checked != ""){
				$checked_str = "checked=checked";
			}
			$str.= '<input type="checkbox" name="'.$name.'" value='.$value.' '.$checked_str.' />';
		}
		return $str;
	}

	/**
 	 * マスタ参照入力欄を出力する
	 * @param string $ref_type 参照先タイプ(page,element,image,file,template,stylesheet,script,folder,user,usergroup)
	 * @param string $id_name ID,NAME属性値
	 * @param string $str_name 文字属性値
	 * @param int $id_value ID,VALUE設定値
	 * @param string $str_value 文字設定値
	 * @param string $restrict 入力制限設定
	 * @return string 入力欄HTML
	 */
	static function shortReference($ref_type,$id_name,$str_name,$id_value = "",$str_value = "",$restrict = SPConst::RESTRICT_ENABLE){

		//子画面へのパスを取得
		$child_path = self::get_ref_path($ref_type);

		$id_name_disp = str_replace(array("[","]"), array("esc","esc"), $id_name);
		$str_name_disp = str_replace(array("[","]"), array("esc","esc"), $str_name);

		//参照スクリプト出力
		$str = "";
		$str.= '<script>';
		$str.= '$(function(){';
		$str.= '	$("#ref_'.$id_name_disp.'").click(function(){';
		$str.= "		window.open('".$child_path."?callback=".$id_name_disp."', 'child', 'toolbar=no, location=no, directories=no, status=no, menubar=no, scrollbars=no, resizable=no, width=700,height=400');";
		$str.= '	});';
		$str.= '	$("#ref_'.$id_name_disp.'_delete").click(function(){';
		$str.= '		$("*[id='.$id_name_disp.']").val("");';
		$str.= '		$("*[id='.$str_name_disp.']").val("");';
		$str.= '		$(this).remove();';
		$str.= '	});';
		$str.= '	$.callbackTo'.$id_name_disp.' = function(id,name){';
		$str.= '		$("*[id='.$id_name_disp.']").val(id);';
		$str.= '		$("*[id='.$str_name_disp.']").val(name);';
		$str.= '		$("#ref_'.$id_name_disp.'_delete").remove();';
		$str.= '		$("#ref_'.$id_name_disp.'").after(\'&nbsp;<input type="button" value="設定解除" id="ref_'.$id_name_disp.'_delete" />\');';
		$str.= '		$("#ref_'.$id_name_disp.'_delete").click(function(){';
		$str.= '			$("*[id='.$id_name_disp.']").val("");';
		$str.= '			$("*[id='.$str_name_disp.']").val("");';
		$str.= '			$(this).remove();';
		$str.= '		});';
		$str.= '	}';
		$str.= '});';
		$str.= '</script>';

		//フォームHTMLコード出力
		if($restrict == SPConst::RESTRICT_READONLY){
			$disabled = "disabled";
		}else{
			$disabled = "";
		}
		$str.= '<input type="text" class="readonly" name="'.$str_name.'" value="'.htmlspecialchars($str_value).'" id="'.$str_name_disp.'" readonly />';
		$str.= '<input type="button" value="参照" id="ref_'.$id_name_disp.'" '.$disabled.' />';

		if(!Util::IsNullOrEmpty($id_value) && $id_value != 0){
			$str.= '&nbsp;<input type="button" value="設定解除" id="ref_'.$id_name_disp.'_delete" '.$disabled.' />';
		}

		$str.= '<input type="hidden" value="'.htmlspecialchars($id_value).'" name="'.$id_name.'" id="'.$id_name_disp.'" />';
		return $str;
	}


	/**
	 * デートピッカーを出力する
	 * @param string $date_column 年月日カラム名
	 * @param unknown $hour_column 時カラム名
	 * @param unknown $minute_column 分カラム名
	 * @param unknown $date 年月日
	 * @param unknown $hour 時
	 * @param unknown $minute 分
	 * @param unknown $restrict 入力制限設定
	 * @return string 入力欄HTML
	 */
	static function dateTimePicker($date_column,$hour_column,$minute_column,$date,$hour,$minute,$restrict = SPConst::RESTRICT_ENABLE){

		//読取専用設定
		if($restrict >= SPConst::RESTRICT_ENABLE){
			$readonly = "";
		}else{
			$readonly = "readonly";
		}

		$str = '';
		if($restrict >= SPConst::RESTRICT_ENABLE){
			$str.= '<script>';
			$str.= '$(function(){';
			$str.= '	$(".datepicker").datepicker();';
			$str.= '	$(".datepicker").datepicker("option", "showOn", \'button\');';
			$str.= '});';
			$str.= '</script>';

			$str.= '<input type="text" name="'.$date_column.'" value="'.htmlspecialchars($date).'" class="input_date datepicker" style="width:100px;" /> ';
			$str.= '<select name="'.$hour_column.'">';
			$str.= '<option value=""></option>';
			$hour_list = Options::hour();
			for($i=0;$i<count($hour_list);$i++){
				$selected = "";
				if($hour_list[$i] == $hour){
					$selected = "selected=selected";
				}
				$str.= '<option value="'.$hour_list[$i].'" '.$selected.'>'.$hour_list[$i].'時</option>';
			}
			$str.= '</select>';
			$str.= '<select name="'.$minute_column.'">';
			$str.= '<option value=""></option>';
			$minute_list = Options::minute();
			for($i=0;$i<count($minute_list);$i++){
				$selected = "";
				if($minute_list[$i] == $minute){
					$selected = "selected=selected";
				}
				$str.= '<option value="'.$minute_list[$i].'" '.$selected.'>'.$minute_list[$i].'分</option>';
			}
			$str.= '</select>';
		}else{
			$str.= '<input type="text" name="'.$date_column.'" value="'.htmlspecialchars($date).'" class="input_date readonly" '.$readonly.' /> ';
			$hour_list = Options::hour();
			$exist_flg = false;
			for($i=0;$i<count($hour_list);$i++){
				if($hour_list[$i] == $hour){
					$str.= '<input type="text" value="'.htmlspecialchars($hour).'時" class="input_hour readonly" '.$readonly.' /> ';
					$str.= '<input type="hidden" name="'.$hour_column.'" value="'.htmlspecialchars($hour).'" /> ';
					$exist_flg = true;
					break;
				}
			}
			if(!$exist_flg){
				$str.= '<input type="text" name="'.$hour_column.'" value="" class="input_hour readonly" '.$readonly.' /> ';
			}
			$minute_list = Options::minute();
			$exist_flg = false;
			for($i=0;$i<count($minute_list);$i++){
				if($minute_list[$i] == $minute){
					$str.= '<input type="text" value="'.htmlspecialchars($minute).'分" class="input_minute readonly" '.$readonly.' /> ';
					$str.= '<input type="hidden" name="'.$minute_column.'" value="'.htmlspecialchars($minute).'" /> ';
					$exist_flg = true;
					break;
				}
			}
			if(!$exist_flg){
				$str.= '<input type="text" name="'.$minute_column.'" value="" class="input_hour readonly" '.$readonly.' /> ';
			}
		}
		return $str;
	}

	/**
	 * デートピッカー(日付のみ）を出力する
	 * @param string $date_column 年月日カラム名
	 * @param unknown $date 年月日
	 * @param unknown $restrict 入力制限設定
	 * @return string 入力欄HTML
	 */
	static function datePicker($date_column,$date,$format = "yy/mm/dd",$restrict = SPConst::RESTRICT_ENABLE){

		//読取専用設定
		if($restrict >= SPConst::RESTRICT_ENABLE){
			$readonly = "";
		}else{
			$readonly = "readonly";
		}

		$str = '';
		if($restrict >= SPConst::RESTRICT_ENABLE){
			$str.= '<script>';
			$str.= '$(function(){';
			$str.= '	$(".datepicker2").datepicker();';
			$str.= '	$(".datepicker2").datepicker("option", "showOn", \'button\');';
			$str.= '	$(".datepicker2").datepicker("option", "dateFormat", "'.$format.'");';
			$str.= '});';
			$str.= '</script>';

			$str.= '<input type="text" name="'.$date_column.'" value="'.htmlspecialchars($date).'" class="input_date datepicker2" style="width:100px;" /> ';
		}else{
			$str.= '<input type="text" name="'.$date_column.'" value="'.htmlspecialchars($date).'" class="input_date readonly" '.$readonly.' style="width:100px;" /> ';
		}
		return $str;
	}

	/**
	 * タイムピッカーを出力する
	 * @param unknown $hour_column 時カラム名
	 * @param unknown $minute_column 分カラム名
	 * @param unknown $hour 時
	 * @param unknown $minute 分
	 * @param unknown $restrict 入力制限設定
	 * @return string 入力欄HTML
	 */
	static function timePicker($hour_column,$minute_column,$hour,$minute,$restrict = SPConst::RESTRICT_ENABLE){

		//読取専用設定
		if($restrict >= SPConst::RESTRICT_ENABLE){
			$readonly = "";
		}else{
			$readonly = "readonly";
		}

		$str = '';
		if($restrict >= SPConst::RESTRICT_ENABLE){
			$str.= '<select name="'.$hour_column.'">';
			$str.= '<option value=""></option>';
			$hour_list = Options::hour();
			for($i=0;$i<count($hour_list);$i++){
				$selected = "";
				if($hour_list[$i] == $hour){
					$selected = "selected=selected";
				}
				$str.= '<option value="'.$hour_list[$i].'" '.$selected.'>'.$hour_list[$i].'時</option>';
			}
			$str.= '</select>';
			$str.= '<select name="'.$minute_column.'">';
			$str.= '<option value=""></option>';
			$minute_list = Options::minute();
			for($i=0;$i<count($minute_list);$i++){
				$selected = "";
				if($minute_list[$i] == $minute){
					$selected = "selected=selected";
				}
				$str.= '<option value="'.$minute_list[$i].'" '.$selected.'>'.$minute_list[$i].'分</option>';
			}
			$str.= '</select>';
		}else{
			$hour_list = Options::hour();
			$exist_flg = false;
			for($i=0;$i<count($hour_list);$i++){
				if($hour_list[$i] == $hour){
					$str.= '<input type="text" value="'.htmlspecialchars($hour).'時" class="input_hour readonly" '.$readonly.' /> ';
					$str.= '<input type="hidden" name="'.$hour_column.'" value="'.htmlspecialchars($hour).'" /> ';
					$exist_flg = true;
					break;
				}
			}
			if(!$exist_flg){
				$str.= '<input type="text" name="'.$hour_column.'" value="" class="input_hour readonly" '.$readonly.' /> ';
			}
			$minute_list = Options::minute();
			$exist_flg = false;
			for($i=0;$i<count($minute_list);$i++){
				if($minute_list[$i] == $minute){
					$str.= '<input type="text" value="'.htmlspecialchars($minute).'分" class="input_minute readonly" '.$readonly.' /> ';
					$str.= '<input type="hidden" name="'.$minute_column.'" value="'.htmlspecialchars($minute).'" /> ';
					$exist_flg = true;
					break;
				}
			}
			if(!$exist_flg){
				$str.= '<input type="text" name="'.$minute_column.'" value="" class="input_hour readonly" '.$readonly.' /> ';
			}
		}
		return $str;
	}

	/**
	 * 複数権限選択欄を取得する
	 * @param string $ref_type 参照先タイプ(page,element,image,file,template,stylesheet,script,folder,user,usergroup)
	 * @param string $name NAME属性値
	 * @param string $title タイトル
	 * @param array $auth_list 設定対象の権限一覧
	 * @param array $auth_data 権限データ 1次元：権限種別ID 2次元:設定データID
	 * @param string $authid_column 権限IDカラム名
	 * @param string $authname_column 権限名カラム名
	 * @param string $dataid_column 設定データIDカラム名
	 * @param string $dataname_column 設定データ名カラム名
	 * @param string $restrict 入力制限設定
	 * @return string 入力欄HTML
	 */
	static function authPicker($ref_type,$name,$title,$auth_list,$auth_data,$authid_column,$authname_column,$dataid_column,$dataname_column,$restrict = SPConst::RESTRICT_ENABLE){
		//子画面へのパスを取得
		$child_path = self::get_ref_path($ref_type);

		//読取専用設定
		if($restrict >= SPConst::RESTRICT_ENABLE){
			$readonly = "";
		}else{
			$readonly = "disabled";
		}

		//参照スクリプト出力
		$str = "";
		if($restrict >= SPConst::RESTRICT_ENABLE){
			$str.= '<script>';
			$str.= '$(function(){';
			$str.= '	$(".ref_'.$name.'").click(function(){';
			$str.= '		var auth_id = $(this).siblings(".auth_id").attr("value");';
			$str.= "		window.open('".$child_path."?callback=".$name."&param1='+auth_id, 'child', 'toolbar=no, location=no, directories=no, status=no, menubar=no, scrollbars=no, resizable=no, width=700,height=400');";
			$str.= '	});';
			$str.= '	$(".delete_'.$dataid_column.'").click(function(){';
			$str.= '		$(this).parent("td").parent("tr").remove();';
			$str.= '	});';
			$str.= '	$.callbackTo'.$name.' = function(id,name,param1){';
			$str.= '		var table_id = "'.$name.'_"+param1;';
			$str.= "		if($('#'+table_id+' tr').hasClass('".$dataid_column."_'+id)){";
			$str.= '			return;';
			$str.= '		}';
			$str.= "		var str = '<tr class=\"".$dataid_column."_'+id+'\"><td style=\"width:250px\">'+escapeHTML(name)+'</td>';";
			$str.= "		str += '<td style=\"width:30px\"><input type=\"button\" value=\"削除\" class=\"delete_".$dataid_column."\">';";
			$str.= "		str += '<input type=\"hidden\" name=\"".$name."['+param1+']['+id+'][".$dataid_column."]\" value=\"'+id+'\">';";
			$str.= "		str += '<input type=\"hidden\" name=\"".$name."['+param1+']['+id+'][".$dataname_column."]\" value=\"'+name+'\"></td></tr>';";
			$str.= '		$(".ref_'.$name.'_"+param1).before(str);';
			$str.= "		$(\".delete_".$dataid_column."\").click(function(){";
			$str.= '			$(this).parent("td").parent("tr").remove();';
			$str.= "		});";
			$str.= "	}";
			$str.= '});';
			$str.= '</script>';
		}

		//フォームHTMLコード出力
		for($i=0;$i<count($auth_list);$i++){
			$str.= "<h2>".htmlspecialchars($auth_list[$i][$authname_column])."</h2>";
			$str.= '<div class="input_panel_border">';
			$str.= '<table id="'.$name.'_'.$auth_list[$i][$authid_column].'"><tbody>';

			if(isset($auth_data[$auth_list[$i][$authid_column]])){
				foreach($auth_data[$auth_list[$i][$authid_column]] as $data){
					$str.= '<tr class="'.$dataid_column.'_'.$data[$dataid_column].'">';
					$str.= '<td style="width:250px">'.htmlspecialchars($data[$dataname_column]).'</td>';
					$str.= '<td style="width:30px">';

					$str.= '<input type="button" value="削除" class="delete_'.$dataid_column.'" '.$readonly.' >';
					$str.= '<input type="hidden" name="'.$name.'['.$auth_list[$i][$authid_column].']['.$data[$dataid_column].']['.$dataid_column.']" value="'.$data[$dataid_column].'">';
					$str.= '<input type="hidden" name="'.$name.'['.$auth_list[$i][$authid_column].']['.$data[$dataid_column].']['.$dataname_column.']" value="'.htmlspecialchars($data[$dataname_column]).'">';
					$str.= '</td></tr>';
				}
			}

			$str.=  '<tr class="ref_'.$name.'_'.$auth_list[$i][$authid_column].'">';
			$str.=  '<td colspan="2">';
			$str.=  '<input type="button" value="'.$title.'を追加する" name="ref_'.$name.'_'.$auth_list[$i][$authid_column].'" class="ref_'.$name.'" '.$readonly.' />';
			$str.=  '<input type="hidden" value="'.$auth_list[$i][$authid_column].'" class="auth_id" />';
			$str.=  '</td>';
			$str.=  '<td>&nbsp</td>';
			$str.=  '</tr>';
			$str.=  '</tbody>';
			$str.=  '</table>';
			$str.=  '</div><!-- input_panel_border -->';
			$str.=  '<br />';
		}

		return $str;
	}

	/**
	 * マスタ参照先のURLパスを取得する
	 * @param string $ref_type 参照先タイプ
	 * @return string URLパス
	 */
	private static function get_ref_path($ref_type){
		$base_path = Config::BASE_DIR_PATH.Config::ADMIN_DIR_PATH;
		if($ref_type == "page"){
			return "/".$base_path."child/template.php";
		}elseif($ref_type == "template"){
			return "/".$base_path."child/template.php";
		}elseif($ref_type == "folder"){
			return "/".$base_path."child/template.php";
		}
	}
}
?>