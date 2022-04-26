/*
 * admin_common.js
 */

function list_check_all(target){
	$("input[type=checkbox]."+target).prop("checked",true);
}
function list_clear_all(target){
	$("input[type=checkbox]."+target).prop("checked",false);
}
function action_publish_multiple(formid){
	if(!$('#'+formid+' input[name=redirect_url]')[0]){
		$('#'+formid).append('<input type="hidden" name="redirect_url" >');
	}
	$('#'+formid+' input[name=redirect_url]').val(location.href);
	$('#'+formid).attr({
	       'action':'/'+base_dir_path+admin_dir_path+'publish_multiple.php',
	       'method':'post'
	     });
	$('#'+formid).submit();
}
function action_unpublish_multiple(formid){
	if(!$('#'+formid+' input[name=redirect_url]')[0]){
		$('#'+formid).append('<input type="hidden" name="redirect_url" >');
	}
	$('#'+formid+' input[name=redirect_url]').val(location.href);
	$('#'+formid).attr({
	       'action':'/'+base_dir_path+admin_dir_path+'unpublish_multiple.php',
	       'method':'post'
	     });
	$('#'+formid).submit();
}
function action_delete_multiple(formid){
	if(!$('#'+formid+' input[name=redirect_url]')[0]){
		$('#'+formid).append('<input type="hidden" name="redirect_url" >');
	}
	$('#'+formid+' input[name=redirect_url]').val(location.href);
	$('#'+formid).attr({
	       'action':'/'+base_dir_path+admin_dir_path+'delete_multiple.php',
	       'method':'post'
	     });
	$('#'+formid).submit();
}
function action_move_multiple(formid){
	if($('#'+formid+' select[name=folder_id]').val() == ""){
		alert("移動先フォルダを選択してください。");
		return false;
	}
	if(!$('#'+formid+' input[name=redirect_url]')[0]){
		$('#'+formid).append('<input type="hidden" name="redirect_url" >');
	}
	$('#'+formid+' input[name=redirect_url]').val(location.href);
	$('#'+formid).attr({
	       'action':'/'+base_dir_path+admin_dir_path+'move_multiple.php',
	       'method':'post'
	     });
	$('#'+formid).submit();
}
function action_restore_multiple(formid){
	if(!$('#'+formid+' input[name=redirect_url]')[0]){
		$('#'+formid).append('<input type="hidden" name="redirect_url" >');
	}
	$('#'+formid+' input[name=redirect_url]').val(location.href);
	$('#'+formid).attr({
	       'action':'/'+base_dir_path+admin_dir_path+'restore_multiple.php',
	       'method':'post'
	     });
	$('#'+formid).submit();
}
function action_archive_delete_multiple(formid){
	if(!$('#'+formid+' input[name=redirect_url]')[0]){
		$('#'+formid).append('<input type="hidden" name="redirect_url" >');
	}
	$('#'+formid+' input[name=redirect_url]').val(location.href);
	$('#'+formid).attr({
	       'action':'/'+base_dir_path+admin_dir_path+'archive_delete_multiple.php',
	       'method':'post'
	     });
	$('#'+formid).submit();
}