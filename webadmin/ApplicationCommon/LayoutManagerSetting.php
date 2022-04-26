<?php
require_once(dirname(__FILE__).'/LayoutManagerAdminMain.php'); //ベースクラス読み込み
/*
説明：設定画面用レイアウト管理クラス
作成日：2013/10/21 TS谷
*/

/**
 * 設定画面用レイアウト管理クラス
 */
class LayoutManagerSetting extends LayoutManagerAdminMain
{
	function sideMenu(){

		$str = '<ul class="dirtree" id="setting">';
		/**
		$str.= '<li class="directory" id="folder-1"><img src="'.$this->admin_path().'/img/folder_empty.png" class="plus" /><a href="'.$this->admin_path().'setting/index.php">セットアップ</a>';
		$str.= '<ul class="dirtree">';
		$str.= '<li class="directory" id="folder-3"><img src="'.$this->admin_path().'/img/folder_empty.png" class="plus" /><a href="'.$this->admin_path().'setting/setup/database.php">データベース初期化</a></li>';
		$str.= '</ul>';
		$str.= '</li>';
		**/
		$str.= '<li class="directory" id="folder-1"><img src="'.$this->admin_path().'/img/folder_empty.png" class="plus" /><a href="'.$this->admin_path().'system/index.php">システム設定</a>';
		$str.= '<ul class="dirtree">';
		$str.= '<li class="directory" id="folder-3"><img src="'.$this->admin_path().'/img/folder_empty.png" class="plus" /><a href="'.$this->admin_path().'setting/setup/database.php">サイト全体設定</a></li>';
		$str.= '</ul>';
		$str.= '</li>';


		$str.= '<li class="directory" id="folder-1"><img src="'.$this->admin_path().'/img/folder_empty.png" class="plus" /><a href="'.$this->admin_path().'setting/domain/list.php">ドメイン設定</a>';
		$str.= '</li>';

		$str.= '<li class="directory" id="folder-1"><img src="'.$this->admin_path().'/img/folder_empty.png" class="plus" /><a href="'.$this->admin_path().'setting/index.php">権限マスタ設定</a>';
		$str.= '<ul class="dirtree">';
		$str.= '<li class="directory" id="folder-3"><img src="'.$this->admin_path().'/img/folder_empty.png" class="plus" /><a href="'.$this->admin_path().'setting/contentauth/list.php">コンテンツ操作権限</a></li>';
		$str.= '<li class="directory" id="folder-3"><img src="'.$this->admin_path().'/img/folder_empty.png" class="plus" /><a href="'.$this->admin_path().'setting/operationauth/list.php">機能操作権限</a></li>';
		$str.= '</ul>';
		$str.= '</li>';

		$str.= '<li class="directory" id="folder-1"><img src="'.$this->admin_path().'/img/folder_empty.png" class="plus" /><a href="'.$this->admin_path().'setting/index.php">種別マスタ設定</a>';
		$str.= '<ul class="dirtree">';
		$str.= '<li class="directory" id="folder-3"><img src="'.$this->admin_path().'/img/folder_empty.png" class="plus" /><a href="'.$this->admin_path().'setting/usertype/list.php">ユーザ種別</a></li>';
		$str.= '<li class="directory" id="folder-3"><img src="'.$this->admin_path().'/img/folder_empty.png" class="plus" /><a href="'.$this->admin_path().'setting/usergrouptype/list.php">ユーザグループ種別</a></li>';
		$str.= '<li class="directory" id="folder-3"><img src="'.$this->admin_path().'/img/folder_empty.png" class="plus" /><a href="'.$this->admin_path().'setting/foldertype/list.php">フォルダ種別</a></li>';
		$str.= '<li class="directory" id="folder-3"><img src="'.$this->admin_path().'/img/folder_empty.png" class="plus" /><a href="'.$this->admin_path().'setting/elementtype/list.php">要素種別</a></li>';
		$str.= '</ul>';
		$str.= '</li>';

		$str.= '<li class="directory" id="folder-1"><img src="'.$this->admin_path().'/img/folder_empty.png" class="plus" /><a href="'.$this->admin_path().'setting/index.php">追加情報設定</a>';
		$str.= '<ul class="dirtree">';
		$str.= '<li class="directory" id="folder-3"><img src="'.$this->admin_path().'/img/folder_empty.png" class="plus" /><a href="'.$this->admin_path().'setting/addinfoselect/list.php">追加情報選択肢</a></li>';
		$str.= '</ul>';
		$str.= '</li>';

		$str.= '<li class="directory" id="folder-1"><img src="'.$this->admin_path().'/img/folder_empty.png" class="plus" /><a href="'.$this->admin_path().'setting/index.php">ワークフロー設定</a>';
		$str.= '<ul class="dirtree">';
		$str.= '<li class="directory" id="folder-3"><img src="'.$this->admin_path().'/img/folder_empty.png" class="plus" /><a href="'.$this->admin_path().'setting/workflowstate/list.php">ワークフロー状態</a></li>';
		$str.= '<li class="directory" id="folder-3"><img src="'.$this->admin_path().'/img/folder_empty.png" class="plus" /><a href="'.$this->admin_path().'setting/workflow/list.php">ワークフローアクション</a></li>';
		$str.= '</ul>';
		$str.= '</li>';

		$str.= '</ul>';

		echo $str;
	}
}
?>
