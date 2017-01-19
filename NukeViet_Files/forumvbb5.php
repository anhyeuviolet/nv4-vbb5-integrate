<?php

/**
 * @Project NUKEVIET 3.0
 * @Author VINADES.,JSC (contact@vinades.vn)
 * @Copyright (C) 2010 VINADES.,JSC. All rights reserved
 * @Createdate 31/05/2010, 00:36
 */
define ( 'NV_ADMIN', true );
define ( 'NV_ROOTDIR', pathinfo ( str_replace ( DIRECTORY_SEPARATOR, '/', __file__ ), PATHINFO_DIRNAME ) );

require NV_ROOTDIR . '/includes/mainfile.php';

require NV_ROOTDIR . '/includes/core/admin_functions.php';

$check_forum_files = false;
if (file_exists ( NV_ROOTDIR . '/' . DIR_FORUM . '/core/includes/config.php' ) and file_exists ( NV_ROOTDIR . '/' . DIR_FORUM . '/includes/vb5/autoloader.php' ) and file_exists ( NV_ROOTDIR . '/' . DIR_FORUM . '/nukeviet' )) {
	$forum_files = @scandir ( NV_ROOTDIR . '/' . DIR_FORUM . '/nukeviet' );
	if (! empty ( $forum_files ) and in_array ( 'is_user.php', $forum_files ) and in_array ( 'changepass.php', $forum_files ) and in_array ( 'editinfo.php', $forum_files ) and in_array ( 'login.php', $forum_files ) and in_array ( 'logout.php', $forum_files ) and in_array ( 'lostpass.php', $forum_files ) and in_array ( 'register.php', $forum_files )) {
		$check_forum_files = true;
	}
}
if (! $check_forum_files) {
	die ( "Error: no dir nukeviet in forum vBB " );
}

$table_prefix = 'vbb_';
require_once (NV_ROOTDIR . '/' . DIR_FORUM . '/config.php');

// check data
$result = $db->query ( "SHOW TABLE STATUS LIKE '" . $table_prefix . "%'" );
$num_table = intval ( $result->rowCount () );
if ($num_table < 50) {
	die ( "Error: No record of vBB" );
}

if ($num_table < 50) {
	die ( "Error: No record of vBB" );
}

list ( $admin_id, $admin_username ) = $db->query ( "SELECT userid, username FROM " . $table_prefix . "user WHERE usergroupid = '6' ORDER BY userid ASC LIMIT 0 , 1" )->fetch ( 3 );
if ($admin_id > 0) {
	
    $db->query( "TRUNCATE TABLE " . NV_AUTHORS_GLOBALTABLE . "" );
    $db->query( "TRUNCATE TABLE " . NV_USERS_GLOBALTABLE . "" );
    $db->query( "INSERT INTO " . NV_AUTHORS_GLOBALTABLE . " (admin_id, editor, lev, files_level, position, addtime, edittime, is_suspend, susp_reason, check_num, last_login, last_ip, last_agent) VALUES(" . $admin_id . ", 'ckeditor', 1, 'images,flash,documents,archives|1|1|1', 'Administrator', 0, 0, 0, '', '', 0, '', '')" );
    
    $db->query( "UPDATE " . NV_CONFIG_GLOBALTABLE . " SET config_value = '1' WHERE lang = 'sys' AND module = 'global' AND config_name = 'is_user_forum'" );
    nv_save_file_config_global();
	
	$contents = "<br><br><center><font class=\"option\"><b>Convert successfully, Account Administrator: " . $admin_username . " you should immediately delete this file.</b></font></center>";
} else {
	$contents = "<br><br><center><font class=\"option\">Error: no admin from table " . $table_prefix . "users </font></center>";
}

die ( $contents );