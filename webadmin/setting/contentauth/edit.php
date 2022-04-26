<?php
//アプリケーション共通クラス読込
require_once(dirname(__FILE__).'/../../ApplicationCommon/include.php');
require_once(dirname(__FILE__).'/../../DataAccess/ContentAuth.php');			//コンテンツ操作権限クラス

//基本設定
$self = basename(__FILE__);				//自身のファイル名
$error = array();						//エラー情報格納用配列
$message = array();						//メッセージ情報格納用配列
$alert = array();						//アラート情報格納用配列
$redirect = "../../index.php";			//エラー時リダイレクト先
$restrict = array();					//表示・操作制限

//セッション取得
$session = Session::get();

//セッション認証
$session->loginCheckAndRedirect("../../login.php?msg=session_error");

//対象データ設定
//権限
$auth_setting_list[] = array("name" => "con_auth_page_view","display_name" => "ページ閲覧","type" => SPConst::RESTRICT_TYPE_ACTIVE_NONACTIVE);
$auth_setting_list[] = array("name" => "con_auth_page_add","display_name" => "ページ新規追加","type" => SPConst::RESTRICT_TYPE_ACTIVE_NONACTIVE);
$auth_setting_list[] = array("name" => "con_auth_page_edit","display_name" => "ページ編集","type" => SPConst::RESTRICT_TYPE_ACTIVE_NONACTIVE);
$auth_setting_list[] = array("name" => "con_auth_page_delete","display_name" => "ページ削除","type" => SPConst::RESTRICT_TYPE_ACTIVE_NONACTIVE);
$auth_setting_list[] = array("name" => "con_auth_page_workflow","display_name" => "ページワークフロー実行","type" => SPConst::RESTRICT_TYPE_ACTIVE_NONACTIVE);
$auth_setting_list[] = array("name" => "con_auth_page_publish","display_name" => "ページ公開","type" => SPConst::RESTRICT_TYPE_ACTIVE_NONACTIVE);
$auth_setting_list[] = array("name" => "con_auth_page_schedule","display_name" => "ページ公開期限操作","type" => SPConst::RESTRICT_TYPE_OPERATION_LEVEL);
$auth_setting_list[] = array("name" => "con_auth_page_title","display_name" => "ページタイトル操作","type" => SPConst::RESTRICT_TYPE_OPERATION_LEVEL);
$auth_setting_list[] = array("name" => "con_auth_page_url","display_name" => "ページURL操作","type" => SPConst::RESTRICT_TYPE_OPERATION_LEVEL);
$auth_setting_list[] = array("name" => "con_auth_page_content","display_name" => "ページコンテンツ操作","type" => SPConst::RESTRICT_TYPE_OPERATION_LEVEL);
$auth_setting_list[] = array("name" => "con_auth_page_keywords","display_name" => "ページキーワード操作","type" => SPConst::RESTRICT_TYPE_OPERATION_LEVEL);
$auth_setting_list[] = array("name" => "con_auth_page_description","display_name" => "ページディスクリプション操作","type" => SPConst::RESTRICT_TYPE_OPERATION_LEVEL);
$auth_setting_list[] = array("name" => "con_auth_page_author","display_name" => "ページ作成者操作","type" => SPConst::RESTRICT_TYPE_OPERATION_LEVEL);
$auth_setting_list[] = array("name" => "con_auth_page_addinfo","display_name" => "ページ追加情報操作","type" => SPConst::RESTRICT_TYPE_OPERATION_LEVEL);
$auth_setting_list[] = array("name" => "con_auth_page_addinfocode","display_name" => "ページ追加情報コード閲覧","type" => SPConst::RESTRICT_TYPE_ACTIVE_NONACTIVE);
$auth_setting_list[] = array("name" => "con_auth_page_addcolumn","display_name" => "ページ追加情報項目追加","type" => SPConst::RESTRICT_TYPE_ACTIVE_NONACTIVE);
$auth_setting_list[] = array("name" => "con_auth_page_element","display_name" => "ページ要素操作","type" => SPConst::RESTRICT_TYPE_OPERATION_LEVEL);
$auth_setting_list[] = array("name" => "con_auth_page_template","display_name" => "ページテンプレート操作","type" => SPConst::RESTRICT_TYPE_OPERATION_LEVEL);
$auth_setting_list[] = array("name" => "con_auth_page_stylesheet","display_name" => "ページスタイルシート操作","type" => SPConst::RESTRICT_TYPE_OPERATION_LEVEL);
$auth_setting_list[] = array("name" => "con_auth_page_script","display_name" => "ページスクリプト操作","type" => SPConst::RESTRICT_TYPE_OPERATION_LEVEL);
$auth_setting_list[] = array("name" => "con_auth_page_editor","display_name" => "ページエディタ設定操作","type" => SPConst::RESTRICT_TYPE_OPERATION_LEVEL);
$auth_setting_list[] = array("name" => "con_auth_page_doctype","display_name" => "ページDOCTYPE操作","type" => SPConst::RESTRICT_TYPE_OPERATION_LEVEL);
$auth_setting_list[] = array("name" => "con_auth_page_head_attr","display_name" => "ページHEAD属性操作","type" => SPConst::RESTRICT_TYPE_OPERATION_LEVEL);
$auth_setting_list[] = array("name" => "con_auth_page_head_code","display_name" => "ページHEADコード操作","type" => SPConst::RESTRICT_TYPE_OPERATION_LEVEL);
$auth_setting_list[] = array("name" => "con_auth_page_body_attr","display_name" => "ページBODY属性操作","type" => SPConst::RESTRICT_TYPE_OPERATION_LEVEL);
$auth_setting_list[] = array("name" => "con_auth_page_folder","display_name" => "ページ所属フォルダ操作","type" => SPConst::RESTRICT_TYPE_OPERATION_LEVEL);
$auth_setting_list[] = array("name" => "con_auth_page_auth","display_name" => "ページ権限操作","type" => SPConst::RESTRICT_TYPE_OPERATION_LEVEL);
$auth_setting_list[] = array("name" => "con_auth_page_history","display_name" => "ページ公開履歴操作","type" => SPConst::RESTRICT_TYPE_OPERATION_LEVEL);
$auth_setting_list[] = array("name" => "con_auth_page_archive","display_name" => "ページアーカイブ操作","type" => SPConst::RESTRICT_TYPE_OPERATION_LEVEL);
$auth_setting_list[] = array("name" => "con_auth_page_static_mode","display_name" => "ページ動的/静的区分操作","type" => SPConst::RESTRICT_TYPE_OPERATION_LEVEL);
$auth_setting_list[] = array("name" => "con_auth_page_php_mode","display_name" => "ページPHP動作モード操作","type" => SPConst::RESTRICT_TYPE_OPERATION_LEVEL);
$auth_setting_list[] = array("name" => "con_auth_element_view","display_name" => "部品閲覧","type" => SPConst::RESTRICT_TYPE_ACTIVE_NONACTIVE);
$auth_setting_list[] = array("name" => "con_auth_element_add","display_name" => "部品新規追加","type" => SPConst::RESTRICT_TYPE_ACTIVE_NONACTIVE);
$auth_setting_list[] = array("name" => "con_auth_element_edit","display_name" => "部品編集","type" => SPConst::RESTRICT_TYPE_ACTIVE_NONACTIVE);
$auth_setting_list[] = array("name" => "con_auth_element_delete","display_name" => "部品削除","type" => SPConst::RESTRICT_TYPE_ACTIVE_NONACTIVE);
$auth_setting_list[] = array("name" => "con_auth_element_workflow","display_name" => "部品ワークフロー実行","type" => SPConst::RESTRICT_TYPE_ACTIVE_NONACTIVE);
$auth_setting_list[] = array("name" => "con_auth_element_publish","display_name" => "部品公開","type" => SPConst::RESTRICT_TYPE_ACTIVE_NONACTIVE);
$auth_setting_list[] = array("name" => "con_auth_element_schedule","display_name" => "部品公開期限操作","type" => SPConst::RESTRICT_TYPE_OPERATION_LEVEL);
$auth_setting_list[] = array("name" => "con_auth_element_title","display_name" => "部品タイトル操作","type" => SPConst::RESTRICT_TYPE_OPERATION_LEVEL);
$auth_setting_list[] = array("name" => "con_auth_element_content","display_name" => "部品コンテンツ操作","type" => SPConst::RESTRICT_TYPE_OPERATION_LEVEL);
$auth_setting_list[] = array("name" => "con_auth_element_addinfo","display_name" => "部品追加情報操作","type" => SPConst::RESTRICT_TYPE_OPERATION_LEVEL);
$auth_setting_list[] = array("name" => "con_auth_element_addinfocode","display_name" => "部品追加情報コード閲覧","type" => SPConst::RESTRICT_TYPE_ACTIVE_NONACTIVE);
$auth_setting_list[] = array("name" => "con_auth_element_addcolumn","display_name" => "部品追加情報項目追加","type" => SPConst::RESTRICT_TYPE_ACTIVE_NONACTIVE);
$auth_setting_list[] = array("name" => "con_auth_element_element","display_name" => "部品要素操作","type" => SPConst::RESTRICT_TYPE_OPERATION_LEVEL);
$auth_setting_list[] = array("name" => "con_auth_element_template","display_name" => "部品テンプレート操作","type" => SPConst::RESTRICT_TYPE_OPERATION_LEVEL);
$auth_setting_list[] = array("name" => "con_auth_element_stylesheet","display_name" => "部品スタイルシート操作","type" => SPConst::RESTRICT_TYPE_OPERATION_LEVEL);
$auth_setting_list[] = array("name" => "con_auth_element_script","display_name" => "部品スクリプト操作","type" => SPConst::RESTRICT_TYPE_OPERATION_LEVEL);
$auth_setting_list[] = array("name" => "con_auth_element_editor","display_name" => "部品エディタ設定操作","type" => SPConst::RESTRICT_TYPE_OPERATION_LEVEL);
$auth_setting_list[] = array("name" => "con_auth_element_folder","display_name" => "部品所属フォルダ操作","type" => SPConst::RESTRICT_TYPE_OPERATION_LEVEL);
$auth_setting_list[] = array("name" => "con_auth_element_elementtype","display_name" => "部品種別操作","type" => SPConst::RESTRICT_TYPE_OPERATION_LEVEL);
$auth_setting_list[] = array("name" => "con_auth_element_auth","display_name" => "部品権限操作","type" => SPConst::RESTRICT_TYPE_OPERATION_LEVEL);
$auth_setting_list[] = array("name" => "con_auth_element_history","display_name" => "部品公開履歴操作","type" => SPConst::RESTRICT_TYPE_OPERATION_LEVEL);
$auth_setting_list[] = array("name" => "con_auth_element_archive","display_name" => "部品アーカイブ操作","type" => SPConst::RESTRICT_TYPE_OPERATION_LEVEL);
$auth_setting_list[] = array("name" => "con_auth_image_view","display_name" => "イメージ閲覧","type" => SPConst::RESTRICT_TYPE_ACTIVE_NONACTIVE);
$auth_setting_list[] = array("name" => "con_auth_image_add","display_name" => "イメージ新規追加","type" => SPConst::RESTRICT_TYPE_ACTIVE_NONACTIVE);
$auth_setting_list[] = array("name" => "con_auth_image_edit","display_name" => "イメージ編集","type" => SPConst::RESTRICT_TYPE_ACTIVE_NONACTIVE);
$auth_setting_list[] = array("name" => "con_auth_image_delete","display_name" => "イメージ削除","type" => SPConst::RESTRICT_TYPE_ACTIVE_NONACTIVE);
$auth_setting_list[] = array("name" => "con_auth_image_workflow","display_name" => "イメージワークフロー実行","type" => SPConst::RESTRICT_TYPE_ACTIVE_NONACTIVE);
$auth_setting_list[] = array("name" => "con_auth_image_publish","display_name" => "イメージ公開","type" => SPConst::RESTRICT_TYPE_ACTIVE_NONACTIVE);
$auth_setting_list[] = array("name" => "con_auth_image_schedule","display_name" => "イメージ公開期限操作","type" => SPConst::RESTRICT_TYPE_OPERATION_LEVEL);
$auth_setting_list[] = array("name" => "con_auth_image_title","display_name" => "イメージタイトル操作","type" => SPConst::RESTRICT_TYPE_OPERATION_LEVEL);
$auth_setting_list[] = array("name" => "con_auth_image_url","display_name" => "イメージURL操作","type" => SPConst::RESTRICT_TYPE_OPERATION_LEVEL);
$auth_setting_list[] = array("name" => "con_auth_image_content","display_name" => "イメージコンテンツ操作","type" => SPConst::RESTRICT_TYPE_OPERATION_LEVEL);
$auth_setting_list[] = array("name" => "con_auth_image_addinfo","display_name" => "イメージ追加情報操作","type" => SPConst::RESTRICT_TYPE_OPERATION_LEVEL);
$auth_setting_list[] = array("name" => "con_auth_image_addinfocode","display_name" => "イメージ追加情報コード閲覧","type" => SPConst::RESTRICT_TYPE_ACTIVE_NONACTIVE);
$auth_setting_list[] = array("name" => "con_auth_image_addcolumn","display_name" => "イメージ追加情報項目追加","type" => SPConst::RESTRICT_TYPE_ACTIVE_NONACTIVE);
$auth_setting_list[] = array("name" => "con_auth_image_folder","display_name" => "イメージ所属フォルダ操作","type" => SPConst::RESTRICT_TYPE_OPERATION_LEVEL);
$auth_setting_list[] = array("name" => "con_auth_image_auth","display_name" => "イメージ権限操作","type" => SPConst::RESTRICT_TYPE_OPERATION_LEVEL);
$auth_setting_list[] = array("name" => "con_auth_image_history","display_name" => "イメージ公開履歴操作","type" => SPConst::RESTRICT_TYPE_OPERATION_LEVEL);
$auth_setting_list[] = array("name" => "con_auth_image_archive","display_name" => "イメージアーカイブ操作","type" => SPConst::RESTRICT_TYPE_OPERATION_LEVEL);
$auth_setting_list[] = array("name" => "con_auth_file_view","display_name" => "ファイル閲覧","type" => SPConst::RESTRICT_TYPE_ACTIVE_NONACTIVE);
$auth_setting_list[] = array("name" => "con_auth_file_add","display_name" => "ファイル新規追加","type" => SPConst::RESTRICT_TYPE_ACTIVE_NONACTIVE);
$auth_setting_list[] = array("name" => "con_auth_file_edit","display_name" => "ファイル編集","type" => SPConst::RESTRICT_TYPE_ACTIVE_NONACTIVE);
$auth_setting_list[] = array("name" => "con_auth_file_delete","display_name" => "ファイル削除","type" => SPConst::RESTRICT_TYPE_ACTIVE_NONACTIVE);
$auth_setting_list[] = array("name" => "con_auth_file_workflow","display_name" => "ファイルワークフロー実行","type" => SPConst::RESTRICT_TYPE_ACTIVE_NONACTIVE);
$auth_setting_list[] = array("name" => "con_auth_file_publish","display_name" => "ファイル公開","type" => SPConst::RESTRICT_TYPE_ACTIVE_NONACTIVE);
$auth_setting_list[] = array("name" => "con_auth_file_schedule","display_name" => "ファイル公開期限操作","type" => SPConst::RESTRICT_TYPE_OPERATION_LEVEL);
$auth_setting_list[] = array("name" => "con_auth_file_title","display_name" => "ファイルタイトル操作","type" => SPConst::RESTRICT_TYPE_OPERATION_LEVEL);
$auth_setting_list[] = array("name" => "con_auth_file_url","display_name" => "ファイルURL操作","type" => SPConst::RESTRICT_TYPE_OPERATION_LEVEL);
$auth_setting_list[] = array("name" => "con_auth_file_content","display_name" => "ファイルコンテンツ操作","type" => SPConst::RESTRICT_TYPE_OPERATION_LEVEL);
$auth_setting_list[] = array("name" => "con_auth_file_addinfo","display_name" => "ファイル追加情報操作","type" => SPConst::RESTRICT_TYPE_OPERATION_LEVEL);
$auth_setting_list[] = array("name" => "con_auth_file_addinfocode","display_name" => "ファイル追加情報コード閲覧","type" => SPConst::RESTRICT_TYPE_ACTIVE_NONACTIVE);
$auth_setting_list[] = array("name" => "con_auth_file_addcolumn","display_name" => "ファイル追加情報項目追加","type" => SPConst::RESTRICT_TYPE_ACTIVE_NONACTIVE);
$auth_setting_list[] = array("name" => "con_auth_file_folder","display_name" => "ファイル所属フォルダ操作","type" => SPConst::RESTRICT_TYPE_OPERATION_LEVEL);
$auth_setting_list[] = array("name" => "con_auth_file_auth","display_name" => "ファイル権限操作","type" => SPConst::RESTRICT_TYPE_OPERATION_LEVEL);
$auth_setting_list[] = array("name" => "con_auth_file_history","display_name" => "ファイル公開履歴操作","type" => SPConst::RESTRICT_TYPE_OPERATION_LEVEL);
$auth_setting_list[] = array("name" => "con_auth_file_archive","display_name" => "ファイルアーカイブ操作","type" => SPConst::RESTRICT_TYPE_OPERATION_LEVEL);
$auth_setting_list[] = array("name" => "con_auth_template_view","display_name" => "テンプレート閲覧","type" => SPConst::RESTRICT_TYPE_ACTIVE_NONACTIVE);
$auth_setting_list[] = array("name" => "con_auth_template_add","display_name" => "テンプレート新規追加","type" => SPConst::RESTRICT_TYPE_ACTIVE_NONACTIVE);
$auth_setting_list[] = array("name" => "con_auth_template_edit","display_name" => "テンプレート編集","type" => SPConst::RESTRICT_TYPE_ACTIVE_NONACTIVE);
$auth_setting_list[] = array("name" => "con_auth_template_delete","display_name" => "テンプレート削除","type" => SPConst::RESTRICT_TYPE_ACTIVE_NONACTIVE);
$auth_setting_list[] = array("name" => "con_auth_template_workflow","display_name" => "テンプレートワークフロー実行","type" => SPConst::RESTRICT_TYPE_ACTIVE_NONACTIVE);
$auth_setting_list[] = array("name" => "con_auth_template_publish","display_name" => "テンプレート公開","type" => SPConst::RESTRICT_TYPE_ACTIVE_NONACTIVE);
$auth_setting_list[] = array("name" => "con_auth_template_schedule","display_name" => "テンプレート公開期限操作","type" => SPConst::RESTRICT_TYPE_OPERATION_LEVEL);
$auth_setting_list[] = array("name" => "con_auth_template_title","display_name" => "テンプレートタイトル操作","type" => SPConst::RESTRICT_TYPE_OPERATION_LEVEL);
$auth_setting_list[] = array("name" => "con_auth_template_content","display_name" => "テンプレートコンテンツ操作","type" => SPConst::RESTRICT_TYPE_OPERATION_LEVEL);
$auth_setting_list[] = array("name" => "con_auth_template_keywords","display_name" => "テンプレートキーワード操作","type" => SPConst::RESTRICT_TYPE_OPERATION_LEVEL);
$auth_setting_list[] = array("name" => "con_auth_template_description","display_name" => "テンプレートディスクリプション操作","type" => SPConst::RESTRICT_TYPE_OPERATION_LEVEL);
$auth_setting_list[] = array("name" => "con_auth_template_author","display_name" => "テンプレート作成者操作","type" => SPConst::RESTRICT_TYPE_OPERATION_LEVEL);
$auth_setting_list[] = array("name" => "con_auth_template_addinfo","display_name" => "テンプレート追加情報操作","type" => SPConst::RESTRICT_TYPE_OPERATION_LEVEL);
$auth_setting_list[] = array("name" => "con_auth_template_addinfocode","display_name" => "テンプレート追加情報コード閲覧","type" => SPConst::RESTRICT_TYPE_ACTIVE_NONACTIVE);
$auth_setting_list[] = array("name" => "con_auth_template_addcolumn","display_name" => "テンプレート追加情報項目追加","type" => SPConst::RESTRICT_TYPE_ACTIVE_NONACTIVE);
$auth_setting_list[] = array("name" => "con_auth_template_element","display_name" => "テンプレート要素操作","type" => SPConst::RESTRICT_TYPE_OPERATION_LEVEL);
$auth_setting_list[] = array("name" => "con_auth_template_template","display_name" => "テンプレート親テンプレート操作","type" => SPConst::RESTRICT_TYPE_OPERATION_LEVEL);
$auth_setting_list[] = array("name" => "con_auth_template_stylesheet","display_name" => "テンプレートスタイルシート操作","type" => SPConst::RESTRICT_TYPE_OPERATION_LEVEL);
$auth_setting_list[] = array("name" => "con_auth_template_script","display_name" => "テンプレートスクリプト操作","type" => SPConst::RESTRICT_TYPE_OPERATION_LEVEL);
$auth_setting_list[] = array("name" => "con_auth_template_editor","display_name" => "テンプレートエディタ設定操作","type" => SPConst::RESTRICT_TYPE_OPERATION_LEVEL);
$auth_setting_list[] = array("name" => "con_auth_template_doctype","display_name" => "テンプレートDOCTYPE操作","type" => SPConst::RESTRICT_TYPE_OPERATION_LEVEL);
$auth_setting_list[] = array("name" => "con_auth_template_head_attr","display_name" => "テンプレートHEAD属性操作","type" => SPConst::RESTRICT_TYPE_OPERATION_LEVEL);
$auth_setting_list[] = array("name" => "con_auth_template_head_code","display_name" => "テンプレートHEADコード操作","type" => SPConst::RESTRICT_TYPE_OPERATION_LEVEL);
$auth_setting_list[] = array("name" => "con_auth_template_body_attr","display_name" => "テンプレートBODY属性操作","type" => SPConst::RESTRICT_TYPE_OPERATION_LEVEL);
$auth_setting_list[] = array("name" => "con_auth_template_folder","display_name" => "テンプレート所属フォルダ操作","type" => SPConst::RESTRICT_TYPE_OPERATION_LEVEL);
$auth_setting_list[] = array("name" => "con_auth_template_auth","display_name" => "テンプレート権限操作","type" => SPConst::RESTRICT_TYPE_OPERATION_LEVEL);
$auth_setting_list[] = array("name" => "con_auth_template_history","display_name" => "テンプレート公開履歴操作","type" => SPConst::RESTRICT_TYPE_OPERATION_LEVEL);
$auth_setting_list[] = array("name" => "con_auth_template_archive","display_name" => "テンプレートアーカイブ操作","type" => SPConst::RESTRICT_TYPE_OPERATION_LEVEL);
$auth_setting_list[] = array("name" => "con_auth_stylesheet_view","display_name" => "スタイルシート閲覧","type" => SPConst::RESTRICT_TYPE_ACTIVE_NONACTIVE);
$auth_setting_list[] = array("name" => "con_auth_stylesheet_add","display_name" => "スタイルシート新規追加","type" => SPConst::RESTRICT_TYPE_ACTIVE_NONACTIVE);
$auth_setting_list[] = array("name" => "con_auth_stylesheet_edit","display_name" => "スタイルシート編集","type" => SPConst::RESTRICT_TYPE_ACTIVE_NONACTIVE);
$auth_setting_list[] = array("name" => "con_auth_stylesheet_delete","display_name" => "スタイルシート削除","type" => SPConst::RESTRICT_TYPE_ACTIVE_NONACTIVE);
$auth_setting_list[] = array("name" => "con_auth_stylesheet_workflow","display_name" => "スタイルシートワークフロー実行","type" => SPConst::RESTRICT_TYPE_ACTIVE_NONACTIVE);
$auth_setting_list[] = array("name" => "con_auth_stylesheet_publish","display_name" => "スタイルシート公開","type" => SPConst::RESTRICT_TYPE_ACTIVE_NONACTIVE);
$auth_setting_list[] = array("name" => "con_auth_stylesheet_schedule","display_name" => "スタイルシート公開期限操作","type" => SPConst::RESTRICT_TYPE_OPERATION_LEVEL);
$auth_setting_list[] = array("name" => "con_auth_stylesheet_title","display_name" => "スタイルシートタイトル操作","type" => SPConst::RESTRICT_TYPE_OPERATION_LEVEL);
$auth_setting_list[] = array("name" => "con_auth_stylesheet_url","display_name" => "スタイルシートURL操作","type" => SPConst::RESTRICT_TYPE_OPERATION_LEVEL);
$auth_setting_list[] = array("name" => "con_auth_stylesheet_content","display_name" => "スタイルシートコンテンツ操作","type" => SPConst::RESTRICT_TYPE_OPERATION_LEVEL);
$auth_setting_list[] = array("name" => "con_auth_stylesheet_folder","display_name" => "スタイルシート所属フォルダ操作","type" => SPConst::RESTRICT_TYPE_OPERATION_LEVEL);
$auth_setting_list[] = array("name" => "con_auth_stylesheet_auth","display_name" => "スタイルシート権限操作","type" => SPConst::RESTRICT_TYPE_OPERATION_LEVEL);
$auth_setting_list[] = array("name" => "con_auth_stylesheet_history","display_name" => "スタイルシート公開履歴操作","type" => SPConst::RESTRICT_TYPE_OPERATION_LEVEL);
$auth_setting_list[] = array("name" => "con_auth_stylesheet_archive","display_name" => "スタイルシートアーカイブ操作","type" => SPConst::RESTRICT_TYPE_OPERATION_LEVEL);
$auth_setting_list[] = array("name" => "con_auth_script_view","display_name" => "スクリプト閲覧","type" => SPConst::RESTRICT_TYPE_ACTIVE_NONACTIVE);
$auth_setting_list[] = array("name" => "con_auth_script_add","display_name" => "スクリプト新規追加","type" => SPConst::RESTRICT_TYPE_ACTIVE_NONACTIVE);
$auth_setting_list[] = array("name" => "con_auth_script_edit","display_name" => "スクリプト編集","type" => SPConst::RESTRICT_TYPE_ACTIVE_NONACTIVE);
$auth_setting_list[] = array("name" => "con_auth_script_delete","display_name" => "スクリプト削除","type" => SPConst::RESTRICT_TYPE_ACTIVE_NONACTIVE);
$auth_setting_list[] = array("name" => "con_auth_script_workflow","display_name" => "スクリプトワークフロー実行","type" => SPConst::RESTRICT_TYPE_ACTIVE_NONACTIVE);
$auth_setting_list[] = array("name" => "con_auth_script_publish","display_name" => "スクリプト公開","type" => SPConst::RESTRICT_TYPE_ACTIVE_NONACTIVE);
$auth_setting_list[] = array("name" => "con_auth_script_schedule","display_name" => "スクリプト公開期限操作","type" => SPConst::RESTRICT_TYPE_OPERATION_LEVEL);
$auth_setting_list[] = array("name" => "con_auth_script_title","display_name" => "スクリプトタイトル操作","type" => SPConst::RESTRICT_TYPE_OPERATION_LEVEL);
$auth_setting_list[] = array("name" => "con_auth_script_url","display_name" => "スクリプトURL操作","type" => SPConst::RESTRICT_TYPE_OPERATION_LEVEL);
$auth_setting_list[] = array("name" => "con_auth_script_content","display_name" => "スクリプトコンテンツ操作","type" => SPConst::RESTRICT_TYPE_OPERATION_LEVEL);
$auth_setting_list[] = array("name" => "con_auth_script_folder","display_name" => "スクリプト所属フォルダ操作","type" => SPConst::RESTRICT_TYPE_OPERATION_LEVEL);
$auth_setting_list[] = array("name" => "con_auth_script_auth","display_name" => "スクリプト権限操作","type" => SPConst::RESTRICT_TYPE_OPERATION_LEVEL);
$auth_setting_list[] = array("name" => "con_auth_script_history","display_name" => "スクリプト公開履歴操作","type" => SPConst::RESTRICT_TYPE_OPERATION_LEVEL);
$auth_setting_list[] = array("name" => "con_auth_script_archive","display_name" => "スクリプトアーカイブ操作","type" => SPConst::RESTRICT_TYPE_OPERATION_LEVEL);
$auth_setting_list[] = array("name" => "con_auth_dir_view","display_name" => "ディレクトリ閲覧","type" => SPConst::RESTRICT_TYPE_ACTIVE_NONACTIVE);
$auth_setting_list[] = array("name" => "con_auth_dir_add","display_name" => "ディレクトリ新規追加","type" => SPConst::RESTRICT_TYPE_ACTIVE_NONACTIVE);
$auth_setting_list[] = array("name" => "con_auth_dir_edit","display_name" => "ディレクトリ更新","type" => SPConst::RESTRICT_TYPE_ACTIVE_NONACTIVE);
$auth_setting_list[] = array("name" => "con_auth_dir_delete","display_name" => "ディレクトリ削除","type" => SPConst::RESTRICT_TYPE_ACTIVE_NONACTIVE);
$auth_setting_list[] = array("name" => "con_auth_dir_sort","display_name" => "ディレクトリソート","type" => SPConst::RESTRICT_TYPE_ACTIVE_NONACTIVE);
$auth_setting_list[] = array("name" => "con_auth_file_sort","display_name" => "ファイルソート","type" => SPConst::RESTRICT_TYPE_ACTIVE_NONACTIVE);

//表示先
$display_setting_list[] = array("name" => "con_auth_page","display_name" => "ページコンテンツ");
$display_setting_list[] = array("name" => "con_auth_element","display_name" => "部品コンテンツ");
$display_setting_list[] = array("name" => "con_auth_image","display_name" => "イメージコンテンツ");
$display_setting_list[] = array("name" => "con_auth_file","display_name" => "ファイルコンテンツ");
$display_setting_list[] = array("name" => "con_auth_template","display_name" => "テンプレートコンテンツ");
$display_setting_list[] = array("name" => "con_auth_stylesheet","display_name" => "スタイルシートコンテンツ");
$display_setting_list[] = array("name" => "con_auth_script","display_name" => "スクリプトコンテンツ");
$display_setting_list[] = array("name" => "con_auth_folder","display_name" => "フォルダ");
$display_setting_list[] = array("name" => "con_auth_domain","display_name" => "ドメイン");

//リクエストパラメータ取得
$mode = isset($_REQUEST["mode"]) ? $_REQUEST["mode"] : "";																					//起動モード
$action = isset($_REQUEST["action"]) ? $_REQUEST["action"] : "";																			//実行アクション
$close = isset($_REQUEST["close"]) ? $_REQUEST["close"] : "";																				//閉じるフラグ
$message = isset($_REQUEST["message"]) ? $_REQUEST["message"] : $message;																	//メッセージ
$contentauth_id = isset($_REQUEST["contentauth_id"]) ? $_REQUEST["contentauth_id"] : "";													//コンテンツ操作権限ID
$contentauth_name = isset($_REQUEST["contentauth_name"]) ? $_REQUEST["contentauth_name"] : "";												//コンテンツ操作権限名

//表示設定リクエストパラメータ取得
$display_setting = array();
for($i=0;$i<count($display_setting_list);$i++){
	$display_setting[$display_setting_list[$i]["name"]] = isset($_REQUEST[$display_setting_list[$i]["name"]]) ? $_REQUEST[$display_setting_list[$i]["name"]] : "";
}

//権限設定リクエストパラメータ取得
$auth_setting = array();
for($i=0;$i<count($auth_setting_list);$i++){
	$auth_setting[$auth_setting_list[$i]["name"]] = isset($_REQUEST[$auth_setting_list[$i]["name"]]) ? $_REQUEST[$auth_setting_list[$i]["name"]] : "";
}

//起動モードが設定されていない場合
if(Util::IsNullOrEmpty($mode)){
	Logger::notice("起動モードが設定されていませんでした。");
	Location::redirect($redirect);
}

//表示・操作制限・デフォルトメッセージ
if($mode == "delete"){
	//表示・操作制限
	$restrict["all"] = SPConst::RESTRICT_READONLY;
	//デフォルトメッセージ
	$alert[] = "一度削除すると元に戻せません。本当に削除しますか？";
}else{
	//表示・操作制限
	$restrict["all"] = SPConst::RESTRICT_ENABLE;
}

//入力値チェック
if(($mode == "new" || $mode == "edit") && $action == "save"){

}

//保存処理
if(($mode == "new" || $mode == "edit") && $action == "save" && $error == array()){
	DB::beginTransaction();

	$ContentAuth = new ContentAuth();						//コンテンツ操作権限クラス

	//新規追加の場合データを追加
	if($mode == "new"){
		$insertData = array();
		$insertData["active_flg"] = "1";
		if(!$ContentAuth->insert($insertData)){
			DB::rollBack();
			Logger::error("コンテンツ操作権限新規追加に失敗しました。",$insertData);
			Location::redirect($redirect);
		}
		//ユーザ種別IDを取得
		$contentauth_id = $ContentAuth->last_insert_id();
	}

	//共通更新条件
	$where = array("contentauth_id" => $contentauth_id);						//更新条件

	//更新データ設定
	$saveData = array();
	$saveData["contentauth_name"] = $contentauth_name;							//要素種別名

	//表示設定データ設定
	for($i=0;$i<count($display_setting_list);$i++){
		$saveData[$display_setting_list[$i]["name"]] = $display_setting[$display_setting_list[$i]["name"]];
	}

	//権限設定データ設定
	for($i=0;$i<count($auth_setting_list);$i++){
		$saveData[$auth_setting_list[$i]["name"]] = $auth_setting[$auth_setting_list[$i]["name"]];
	}

	$saveData["active_flg"] = 1;												//有効

	//新規追加の場合は、IDをソート順として初期設定
	if($mode == "new"){
		$saveData["sort_no"] = $contentauth_id;
		$saveData["created"] = time();
		$saveData["created_by"] = $session->user["user_id"];
	}

	$saveData["updated"] = time();
	$saveData["updated_by"] = $session->user["user_id"];

	//データ更新実行
	if(!$ContentAuth->update($where, $saveData)){
		DB::rollBack();
		Logger::error("コンテンツ操作権限更新に失敗しました。",$saveData);
		Location::redirect($redirect);
	}

	DB::commit();

	if($close == "on"){
		//一覧画面に遷移する
		Location::redirect("list.php");
	}else{
		//同画面に遷移する
		$redirectParam["contentauth_id"] = $contentauth_id;
		$redirectParam["mode"] = "edit";
		$redirectParam["message[]"] = "保存しました。";
		Location::redirect($self,$redirectParam);
	}
}elseif($mode == "delete" && $action == "delete" && $error == array()){		//削除処理
	DB::beginTransaction();

	$ContentAuth = new ContentAuth();						//コンテンツ操作権限クラス

	//共通削除条件
	$where = array("contentauth_id" => $contentauth_id);	//削除条件

	//コンテンツ操作権限データ削除
	if(!$ContentAuth->delete($where)){
		DB::rollBack();
		Logger::error("コンテンツ操作権限削除に失敗しました。",$where);
		Location::redirect($redirect);
	}

	DB::commit();

	//一覧画面に遷移する
	Location::redirect("list.php");
}

//初期表示情報取得
if(($mode == "edit" || $mode == "delete") && $action == ""){		//編集モードで初期表示の場合

	//初期表示データ取得
	$ContentAuth = new ContentAuth();
	$initData = $ContentAuth->getDataByParameters(array("contentauth_id" => $contentauth_id));

	if(!$initData){
		//初期表示データが取得できない場合
		Logger::error("コンテンツ操作権限初期表示データ取得に失敗しました。",array("contentauth_id" => $contentauth_id));
		Location::redirect("../../index.php");
	}

	$contentauth_name = $initData["contentauth_name"];				//コンテンツ操作権限名

	//表示設定データ取得
	$display_setting = array();
	for($i=0;$i<count($display_setting_list);$i++){
		$display_setting[$display_setting_list[$i]["name"]] = $initData[$display_setting_list[$i]["name"]];
	}

	//権限設定データ取得
	$auth_setting = array();
	for($i=0;$i<count($auth_setting_list);$i++){
		$auth_setting[$auth_setting_list[$i]["name"]] = $initData[$auth_setting_list[$i]["name"]];
	}

}elseif($mode == "new" && $action == ""){		//新規追加モードで初期表示の場合
	//処理なし
}
//Debug::arrayCheck($auth_setting_list);
//Debug::arrayCheck($auth_setting);

//表示用配列設定
$restrict_active_nonactive = Options::restrict_active_nonactive();
$restrict_level = Options::restrict_level();

$LayoutManager = new LayoutManagerAdminMain();
$LayoutManager->setTitle("コンテンツ操作権限編集");
$LayoutManager->setErrorList($error);
$LayoutManager->setMessageList($message);
$LayoutManager->setAlertList($alert);
$LayoutManager->header();

?>
<script>
$(function(){
	$("#tabs").tabs({
		selected: 1 //コンテンツタブをデフォルトにする
	});

	//保存するボタン設定
	$("#action_save").click(function(){
		$("*[name=action]").val('save');
		$('#values').attr({
		       'action':'edit.php',
		       'method':'post'
		     });
		$('#values').submit();
	});

	//保存して閉じるボタン設定
	$("#action_save_close").click(function(){
		$("*[name=action]").val('save');
		$("*[name=close]").val('on');
		$('#values').attr({
		       'action':'edit.php',
		       'method':'post'
		     });
		$('#values').submit();
	});

	//削除ボタン設定
	$("#action_delete").click(function(){
		$("*[name=action]").val('delete');
		$('#values').attr({
		       'action':'edit.php',
		       'method':'post'
		     });
		$('#values').submit();
	});

	//一覧に戻るボタン設定
	$("#back_to_list").click(function(){
		$('#values').attr({
		       'action':'list.php',
		       'method':'post'
		     });
		$('#values').submit();
	});
});
</script>

<form action="/" method="post" id="values" enctype="multipart/form-data">
<input type="hidden" name="operationauth_id" value="<?php echo htmlspecialchars($operationauth_id); //機能操作権限ID ?>" />
<input type="hidden" name="mode" value="<?php echo htmlspecialchars($mode); //動作モード ?>" />
<input type="hidden" name="action" value="<?php //javascriptから設定 ?>" />
<input type="hidden" name="close" value="<?php //javascriptから設定 ?>" />

<div id="tabs">
<table class="edit_control">
<tr>
<td>
<input type="button" value="一覧に戻る" id="back_to_list" />
</td>
<?php if($mode == "new" || $mode == "edit"): ?>
	<td>
	<input type="button" value="保存する" id="action_save"  />
	</td>
	<td>
	<input type="button" value="保存して閉じる" id="action_save_close"  />
	</td>
<?php elseif($mode == "delete"): ?>
	<td>
	<input type="button" value="削除する" id="action_delete" />
	</td>
<?php endif; ?>
</tr>
</table>
<br>

<!-- メッセージ出力開始 -->
<?php echo $LayoutManager->alert(); 	//アラート出力 ?>
<?php echo $LayoutManager->error(); 	//エラー出力 ?>
<?php echo $LayoutManager->message(); 	//メッセージ出力 ?>
<!-- メッセージ出力終了 -->

<ul>
<li><a href="#tabs-1" class="tabmenu">基本設定</a></li>
<li><a href="#tabs-2" class="tabmenu">権限設定</a></li>
<li><a href="#tabs-3" class="tabmenu">表示先設定</a></li>
</ul>

<!-- 基本設定タブ領域開始 -->
<div id="tabs-1" class="tab_area">
<h1>基本設定</h1>
<table class="content_input_table">
<?php if($restrict["all"] >= SPConst::RESTRICT_READONLY): ?>
	<tr>
	<th>コンテンツ操作権限名</th>
	<td>
	<?php echo UIParts::middleText("contentauth_name",$contentauth_name,$restrict["all"]); ?>
	</td>
	</tr>
<?php endif; ?>
</table>
</div>
<!-- 基本設定タブ領域終了 -->

<!-- 権限設定タブ領域開始 -->
<div id="tabs-2" class="tab_area">
<h1>権限</h1>
<table class="content_input_table">
<?php if($restrict["all"] >= SPConst::RESTRICT_READONLY): ?>
	<?php
	for($i=0;$i<count($auth_setting_list);$i++){
		echo '<tr>';
		echo '<th>'.$auth_setting_list[$i]["display_name"].'</th>';
		echo '<td>';
		if($auth_setting_list[$i]["type"] == SPConst::RESTRICT_TYPE_ACTIVE_NONACTIVE){
			$list = $restrict_active_nonactive;
		}elseif($auth_setting_list[$i]["type"] == SPConst::RESTRICT_TYPE_OPERATION_LEVEL){
			$list = $restrict_level;
		}
		echo UIParts::radio($auth_setting_list[$i]["name"], $list, $auth_setting[$auth_setting_list[$i]["name"]],$restrict["all"]);
		echo '</td>';
		echo '</tr>';
	}
	?>
<?php endif; ?>
</table>
</div>
<!-- 表示先タブ領域終了 -->

<!-- 表示先タブ領域開始 -->
<div id="tabs-3" class="tab_area">
<h1>表示先</h1>
<table class="content_input_table">
<?php if($restrict["all"] >= SPConst::RESTRICT_READONLY): ?>
	<?php
	for($i=0;$i<count($display_setting_list);$i++){
		echo '<tr>';
		echo '<th>'.$display_setting_list[$i]["display_name"].'</th>';
		echo '<td>';
		$list = $restrict_active_nonactive;
		echo UIParts::radio($display_setting_list[$i]["name"], $list, $display_setting[$display_setting_list[$i]["name"]],$restrict["all"]);
		echo '</td>';
		echo '</tr>';
	}
	?>
<?php endif; ?>
</table>
</div>
<!-- 表示先タブ領域終了 -->

</div>
</form>
<?php echo $LayoutManager->footer(); ?>