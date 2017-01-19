<?php

/**
 * @Project NUKEVIET 3.0
 * @Author VINADES.,JSC (contact@vinades.vn)
 * @Copyright (C) 2010 VINADES.,JSC. All rights reserved
 * @Createdate 31/05/2010, 00:36
 */
if ( ! defined( 'NV_MAINFILE' ) ) die( 'Stop!!!' );

$sessionhash = $nv_Request->get_string( 'sessionhash', 'cookie', '', true );
$tableprefix = "";
if ( file_exists( NV_ROOTDIR . '/' . DIR_FORUM . '/core/includes/config.php' ) ){
	
	$vbpath = NV_ROOTDIR . '/' . DIR_FORUM;
	require_once( NV_ROOTDIR . '/' . DIR_FORUM . '/core/includes/config.php' );
	
	$tableprefix = $config['Database']['tableprefix'];
	
	if ( $sessionhash AND preg_match( "/^[a-z0-9]+$/", $sessionhash ) ){
		require_once ( NV_ROOTDIR . '/' . DIR_FORUM . '/nukeviet/function.php' );
		$user_info['userid'] = 0;

		$result = $db->query ( "SELECT userid, idhash FROM " . $tableprefix . "session WHERE userid > 0 AND sessionhash ='" . $sessionhash . "'" );
		$row = $result->fetch ();
		
		if ( $row['idhash'] == md5( NV_USER_AGENT . fetch_substr_ip( $client_info['ip'] ) ) )
		{
			$user_info['userid'] = $row['userid'];
		}
		
		if ( $user_info['userid'] == 0 ) {
			$nv_Request->unset_request( 'bbuserid', 'cookie' );
			$nv_Request->unset_request( 'bbpassword', 'cookie' );
			$nv_Request->unset_request( 'sessionhash', 'cookie' );
			$user_info = array();
		}
	}else{
		$user_info['userid'] = 0;
		define('CSRF_PROTECTION', false);
		require_once ( NV_ROOTDIR . '/' . DIR_FORUM . '/nukeviet/function.php' );
		require_once($vbpath . '/includes/vb5/autoloader.php');
		vB5_Autoloader::register($vbpath);
		vB5_Frontend_Application::init('config.php');	
		$vB_user_info = vB::getCurrentSession()->fetch_userinfo();
		unset($vB_user_info['phrasegroup_global'], $vB_user_info['token'], $vB_user_info['secret']);
		
		$result = $db->query ( "SELECT userid, idhash, useragent FROM " . $tableprefix . "session WHERE userid = " . $vB_user_info['userid'] );
		
		$row = $result->fetch ();
		if ( $row['idhash'] == md5( NV_USER_AGENT . fetch_substr_ip( $client_info['ip'] ) ) AND $row['useragent'] == NV_USER_AGENT ) {
			$user_info['userid'] = $row['userid'];
		}

		if ( $user_info['userid'] == 0 ) {
			$nv_Request->unset_request( 'bbuserid', 'cookie' );
			$nv_Request->unset_request( 'bbpassword', 'cookie' );
			$nv_Request->unset_request( 'sessionhash', 'cookie' );
			$user_info = array();
		}
	}
}
