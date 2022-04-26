<?php
/*
説明：お知らせコンテンツクラス(アドオン拡張)
作成日：2013/12/1 TS谷
*/
require_once(dirname(__FILE__).'/../../../DataAccess/Content.php');

/**
 * コンテンツクラス
 */
class InformationContent extends Content
{
	//管理画面用
	function getInformationList($parameters){
		$params = array();
		$sql = "SELECT ";
		$sql.= "	cont.*, ";
		$sql.= "	count(sche.content_schedule_id) as sche_cnt, ";
		$sql.= "	add_date.addinfo_content as date, ";
//
		$sql.= "	add_date.addinfo_content as hour, ";
		$sql.= "	add_date.addinfo_content as minute, ";

		$sql.= "	add_sel.optionvalue_name as information_category, ";
		$sql.= "	add_sel1.optionvalue_name as information_type, ";

		$sql.= "	pub.content_id as content_public_id, ";
		$sql.= "	pub.updated as content_public_updated, ";
		$sql.= "	workflow.workflowstate_name ";
		$sql.= "FROM content cont ";
		$sql.= "	LEFT JOIN content_public pub ";
		$sql.= "		ON  cont.content_id = pub.content_id ";
		$sql.= "	LEFT JOIN content_schedule sche ";
		$sql.= "		ON  cont.content_id = sche.content_id ";
		$sql.= "	LEFT JOIN content_addinfo add_date ";
		$sql.= "		ON cont.content_id = add_date.content_id ";
		$sql.= "		AND add_date.name = 'date' ";
//
		$sql.= "	LEFT JOIN content_addinfo add_hour ";
		$sql.= "		ON cont.content_id = add_hour.content_id ";
		$sql.= "		AND add_hour.name = 'hour' ";
		$sql.= "	LEFT JOIN content_addinfo add_minute ";
		$sql.= "		ON cont.content_id = add_minute.content_id ";
		$sql.= "		AND add_minute.name = 'minute' ";

		$sql.= "	LEFT JOIN content_addinfo add_cat ";
		$sql.= "		ON cont.content_id = add_cat.content_id ";
		$sql.= "		AND add_cat.name = 'information_category' ";
		$sql.= "	LEFT JOIN addinfo_select add_sel ";
		$sql.= "		ON add_cat.addinfo_content = add_sel.optionvalue ";
		$sql.= "		AND add_sel.selectname = 'information_category' ";

		$sql.= "	LEFT JOIN content_addinfo add_fac ";
		$sql.= "		ON cont.content_id = add_fac.content_id ";
		$sql.= "		AND add_fac.name = 'information_type' ";
		$sql.= "	LEFT JOIN addinfo_select add_sel1 ";
		$sql.= "		ON add_fac.addinfo_content = add_sel1.optionvalue ";
		$sql.= "		AND add_sel1.selectname = 'information_type' ";

		$sql.= "	LEFT JOIN workflowstate workflow ON cont.workflowstate_id = workflow.workflowstate_id ";
		$sql.= "		AND workflow.active_flg = '1' ";
		$sql.= "WHERE cont.contentclass = 'parts' " ;
		$sql.= "	AND cont.folder_id = '2' " ;

		if(isset($parameters["title"]) && isset($parameters["content"])){
			$sql.= "AND (cont.title LIKE ? OR cont.content LIKE ? ) " ;
			$params[] = $parameters["title"];
			$params[] = $parameters["content"];
		}else{
			if(isset($parameters["title"])){
				$sql.= "AND cont.title LIKE ? " ;
				$params[] = $parameters["title"];
			}
			if(isset($parameters["content"])){
				$sql.= "AND cont.content LIKE ? " ;
				$params[] = $parameters["content"];
			}
		}



		$sql.= "GROUP BY cont.content_id ";
		$sql.= "ORDER BY add_date.addinfo_content DESC , add_hour.addinfo_content DESC  , add_minute.addinfo_content DESC " ;


		$sql.= "LIMIT 0,100 ";


		$result = $this->query($sql,$params);
		return $result;
	}

	function getInformationMemberList($parameters){
		$params = array();
		$sql = "SELECT ";
		$sql.= "	cont.*, ";
		$sql.= "	count(sche.content_schedule_id) as sche_cnt, ";
		$sql.= "	add_date.addinfo_content as date, ";
		//
		$sql.= "	add_date.addinfo_content as hour, ";
		$sql.= "	add_date.addinfo_content as minute, ";

		$sql.= "	add_sel.optionvalue_name as information_category, ";
		$sql.= "	add_sel1.optionvalue_name as information_type, ";

		$sql.= "	add_fp0.addinfo_content as file_path0, ";
		$sql.= "	add_fp1.addinfo_content as file_path1, ";
		$sql.= "	add_fp2.addinfo_content as file_path2, ";
		$sql.= "	add_fp3.addinfo_content as file_path3, ";
		$sql.= "	add_fp4.addinfo_content as file_path4, ";
		$sql.= "	add_fp5.addinfo_content as file_path5, ";

		$sql.= "	add_fn0.addinfo_content as file_name0, ";
		$sql.= "	add_fn1.addinfo_content as file_name1, ";
		$sql.= "	add_fn2.addinfo_content as file_name2, ";
		$sql.= "	add_fn3.addinfo_content as file_name3, ";
		$sql.= "	add_fn4.addinfo_content as file_name4, ";
		$sql.= "	add_fn5.addinfo_content as file_name5, ";

		$sql.= "	add_ft0.addinfo_content as file_type0, ";
		$sql.= "	add_ft1.addinfo_content as file_type1, ";
		$sql.= "	add_ft2.addinfo_content as file_type2, ";
		$sql.= "	add_ft3.addinfo_content as file_type3, ";
		$sql.= "	add_ft4.addinfo_content as file_type4, ";
		$sql.= "	add_ft5.addinfo_content as file_type5, ";

		$sql.= "	pub.content_id as content_public_id, ";
		$sql.= "	pub.updated as content_public_updated, ";
		$sql.= "	workflow.workflowstate_name ";
		$sql.= "FROM content cont ";
		$sql.= "	LEFT JOIN content_public pub ";
		$sql.= "		ON  cont.content_id = pub.content_id ";
		$sql.= "	LEFT JOIN content_schedule sche ";
		$sql.= "		ON  cont.content_id = sche.content_id ";
		$sql.= "	LEFT JOIN content_addinfo add_date ";
		$sql.= "		ON cont.content_id = add_date.content_id ";
		$sql.= "		AND add_date.name = 'date' ";
		//
		$sql.= "	LEFT JOIN content_addinfo add_hour ";
		$sql.= "		ON cont.content_id = add_hour.content_id ";
		$sql.= "		AND add_hour.name = 'hour' ";
		$sql.= "	LEFT JOIN content_addinfo add_minute ";
		$sql.= "		ON cont.content_id = add_minute.content_id ";
		$sql.= "		AND add_minute.name = 'minute' ";

		$sql.= "	LEFT JOIN content_addinfo add_cat ";
		$sql.= "		ON cont.content_id = add_cat.content_id ";
		$sql.= "		AND add_cat.name = 'information_category' ";
		$sql.= "	LEFT JOIN addinfo_select add_sel ";
		$sql.= "		ON add_cat.addinfo_content = add_sel.optionvalue ";
		$sql.= "		AND add_sel.selectname = 'information_category' ";

		$sql.= "	LEFT JOIN content_addinfo add_fac ";
		$sql.= "		ON cont.content_id = add_fac.content_id ";
		$sql.= "		AND add_fac.name = 'information_type' ";
		$sql.= "	LEFT JOIN addinfo_select add_sel1 ";
		$sql.= "		ON add_fac.addinfo_content = add_sel1.optionvalue ";
		$sql.= "		AND add_sel1.selectname = 'information_type' ";

		$sql.= "	LEFT JOIN content_addinfo add_fp0 ";
		$sql.= "		ON cont.content_id = add_fp0.content_id ";
		$sql.= "		AND add_fp0.name = 'file_path0' ";
		$sql.= "	LEFT JOIN content_addinfo add_fp1 ";
		$sql.= "		ON cont.content_id = add_fp1.content_id ";
		$sql.= "		AND add_fp1.name = 'file_path1' ";
		$sql.= "	LEFT JOIN content_addinfo add_fp2 ";
		$sql.= "		ON cont.content_id = add_fp2.content_id ";
		$sql.= "		AND add_fp2.name = 'file_path2' ";
		$sql.= "	LEFT JOIN content_addinfo add_fp3 ";
		$sql.= "		ON cont.content_id = add_fp3.content_id ";
		$sql.= "		AND add_fp3.name = 'file_path3' ";
		$sql.= "	LEFT JOIN content_addinfo add_fp4 ";
		$sql.= "		ON cont.content_id = add_fp4.content_id ";
		$sql.= "		AND add_fp4.name = 'file_path4' ";
		$sql.= "	LEFT JOIN content_addinfo add_fp5 ";
		$sql.= "		ON cont.content_id = add_fp5.content_id ";
		$sql.= "		AND add_fp5.name = 'file_path5' ";

		$sql.= "	LEFT JOIN content_addinfo add_fn0 ";
		$sql.= "		ON cont.content_id = add_fn0.content_id ";
		$sql.= "		AND add_fn0.name = 'file_name0' ";
		$sql.= "	LEFT JOIN content_addinfo add_fn1 ";
		$sql.= "		ON cont.content_id = add_fn1.content_id ";
		$sql.= "		AND add_fn1.name = 'file_name1' ";
		$sql.= "	LEFT JOIN content_addinfo add_fn2 ";
		$sql.= "		ON cont.content_id = add_fn2.content_id ";
		$sql.= "		AND add_fn2.name = 'file_name2' ";
		$sql.= "	LEFT JOIN content_addinfo add_fn3 ";
		$sql.= "		ON cont.content_id = add_fn3.content_id ";
		$sql.= "		AND add_fn3.name = 'file_name3' ";
		$sql.= "	LEFT JOIN content_addinfo add_fn4 ";
		$sql.= "		ON cont.content_id = add_fn4.content_id ";
		$sql.= "		AND add_fn4.name = 'file_name4' ";
		$sql.= "	LEFT JOIN content_addinfo add_fn5 ";
		$sql.= "		ON cont.content_id = add_fn5.content_id ";
		$sql.= "		AND add_fn5.name = 'file_name5' ";

		$sql.= "	LEFT JOIN content_addinfo add_ft0 ";
		$sql.= "		ON cont.content_id = add_ft0.content_id ";
		$sql.= "		AND add_ft0.name = 'file_type0' ";
		$sql.= "	LEFT JOIN content_addinfo add_ft1 ";
		$sql.= "		ON cont.content_id = add_ft1.content_id ";
		$sql.= "		AND add_ft1.name = 'file_type1' ";
		$sql.= "	LEFT JOIN content_addinfo add_ft2 ";
		$sql.= "		ON cont.content_id = add_ft2.content_id ";
		$sql.= "		AND add_ft2.name = 'file_type2' ";
		$sql.= "	LEFT JOIN content_addinfo add_ft3 ";
		$sql.= "		ON cont.content_id = add_ft3.content_id ";
		$sql.= "		AND add_ft3.name = 'file_type3' ";
		$sql.= "	LEFT JOIN content_addinfo add_ft4 ";
		$sql.= "		ON cont.content_id = add_ft4.content_id ";
		$sql.= "		AND add_ft4.name = 'file_type4' ";
		$sql.= "	LEFT JOIN content_addinfo add_ft5 ";
		$sql.= "		ON cont.content_id = add_ft5.content_id ";
		$sql.= "		AND add_ft5.name = 'file_type5' ";

		$sql.= "	LEFT JOIN workflowstate workflow ON cont.workflowstate_id = workflow.workflowstate_id ";
		$sql.= "		AND workflow.active_flg = '1' ";
		$sql.= "WHERE cont.contentclass = 'parts' " ;
		$sql.= "	AND cont.folder_id = '2' " ;

		if(isset($parameters["id"])){
			$sql.= "AND cont.content_id = ? " ;
			$params[] = $parameters["id"];
		}

		if(isset($parameters["title"]) && isset($parameters["content"])){
			$sql.= "AND (cont.title LIKE ? OR cont.content LIKE ? ) " ;
			$params[] = $parameters["title"];
			$params[] = $parameters["content"];
		}else{
			if(isset($parameters["title"])){
				$sql.= "AND cont.title LIKE ? " ;
				$params[] = $parameters["title"];
			}
			if(isset($parameters["content"])){
				$sql.= "AND cont.content LIKE ? " ;
				$params[] = $parameters["content"];
			}
		}

		if(isset($parameters["information_type"])){
			$sql.= "AND add_fac.addinfo_content = '02' " ;
		}

		$sql.= "GROUP BY cont.content_id ";
		$sql.= "ORDER BY add_date.addinfo_content DESC , add_hour.addinfo_content DESC  , add_minute.addinfo_content DESC " ;

		if(isset($parameters["page"])){
			$num = 20/*変数*/;
			$start = $parameters["page"] * $num;
			$sql.= "LIMIT ".$start.",".$num." ";
		}else{
			$sql.= "LIMIT 0,20 ";/*変数*/
		}


		$result = $this->query($sql,$params);
		return $result;
	}


	function getInformationMemberListNum($parameters){
		$params = array();
		$sql = "SELECT ";
		$sql.= "	count(cont.content_id) as contNum ";
		$sql.= "FROM content cont ";
		$sql.= "	LEFT JOIN content_public pub ";
		$sql.= "		ON  cont.content_id = pub.content_id ";
		$sql.= "	LEFT JOIN content_schedule sche ";
		$sql.= "		ON  cont.content_id = sche.content_id ";
		$sql.= "	LEFT JOIN content_addinfo add_date ";
		$sql.= "		ON cont.content_id = add_date.content_id ";
		$sql.= "		AND add_date.name = 'date' ";
		//
		$sql.= "	LEFT JOIN content_addinfo add_hour ";
		$sql.= "		ON cont.content_id = add_hour.content_id ";
		$sql.= "		AND add_hour.name = 'hour' ";
		$sql.= "	LEFT JOIN content_addinfo add_minute ";
		$sql.= "		ON cont.content_id = add_minute.content_id ";
		$sql.= "		AND add_minute.name = 'minute' ";

		$sql.= "	LEFT JOIN content_addinfo add_cat ";
		$sql.= "		ON cont.content_id = add_cat.content_id ";
		$sql.= "		AND add_cat.name = 'information_category' ";
		$sql.= "	LEFT JOIN addinfo_select add_sel ";
		$sql.= "		ON add_cat.addinfo_content = add_sel.optionvalue ";
		$sql.= "		AND add_sel.selectname = 'information_category' ";

		$sql.= "	LEFT JOIN content_addinfo add_fac ";
		$sql.= "		ON cont.content_id = add_fac.content_id ";
		$sql.= "		AND add_fac.name = 'information_type' ";
		$sql.= "	LEFT JOIN addinfo_select add_sel1 ";
		$sql.= "		ON add_fac.addinfo_content = add_sel1.optionvalue ";
		$sql.= "		AND add_sel1.selectname = 'information_type' ";

		$sql.= "	LEFT JOIN workflowstate workflow ON cont.workflowstate_id = workflow.workflowstate_id ";
		$sql.= "		AND workflow.active_flg = '1' ";
		$sql.= "WHERE cont.contentclass = 'parts' " ;
		$sql.= "	AND cont.folder_id = '2' " ;

		if(isset($parameters["id"])){
			$sql.= "AND cont.content_id = ? " ;
			$params[] = $parameters["id"];
		}

		if(isset($parameters["title"]) && isset($parameters["content"])){
			$sql.= "AND (cont.title LIKE ? OR cont.content LIKE ? ) " ;
			$params[] = $parameters["title"];
			$params[] = $parameters["content"];
		}else{
			if(isset($parameters["title"])){
				$sql.= "AND cont.title LIKE ? " ;
				$params[] = $parameters["title"];
			}
			if(isset($parameters["content"])){
				$sql.= "AND cont.content LIKE ? " ;
				$params[] = $parameters["content"];
			}
		}

		if(isset($parameters["information_type"])){
			$sql.= "AND add_fac.addinfo_content = '02' " ;
		}

		$sql.= " ";

		$result = $this->query($sql,$params);
		return $result;
	}

}
