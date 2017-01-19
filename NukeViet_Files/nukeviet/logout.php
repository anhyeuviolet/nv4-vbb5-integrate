<?php

/**
 * @Project NUKEVIET 3.0
 * @Author VINADES.,JSC (contact@vinades.vn)
 * @Copyright (C) 2010 VINADES.,JSC. All rights reserved
 * @Createdate 14/7/2010, 2:55
 */
if (! defined ( 'NV_IS_MOD_USER' )) {
	die ( 'Stop!!!' );
}

if (isset ( $module_name ) and $module_name == 'users') {
	
	$sessionhash = $nv_Request->get_string ( 'sessionhash', 'cookie', '', true );
	if (preg_match ( "/^[a-z0-9]+$/", $sessionhash )) {
		$vbpath = NV_ROOTDIR . '/' . DIR_FORUM;
		if (file_exists ( $vbpath . '/core/includes/config.php' )) {
			require_once ($vbpath . '/core/includes/config.php');
			define ( 'CSRF_PROTECTION', false );
			require_once ($vbpath . '/includes/vb5/autoloader.php');
			vB5_Autoloader::register ( $vbpath );
			vB5_Frontend_Application::init ( 'config.php' );
			
			$vB_user_info = vB::getCurrentSession ()->fetch_userinfo ();
			unset ( $vB_user_info ['phrasegroup_global'], $vB_user_info ['token'], $vB_user_info ['secret'] );
			$tableprefix = $config ['Database'] ['tableprefix'];
			$db->query ( "DELETE FROM " . $tableprefix . "session WHERE sessionhash = " . $db->quote ( $sessionhash ) . "" );
			header ( "Location:" . NV_MY_DOMAIN . NV_BASE_SITEURL . DIR_FORUM . "/auth/logout?logouthash=" . $vB_user_info ['logouthash'] );
		}
	}
}
