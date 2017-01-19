<?php

/**
 * @Project NUKEVIET 3.0
 * @Author VINADES.,JSC (contact@vinades.vn)
 * @copyright 2009
 * @createdate 10/03/2010 10:51
 */

if ( ! defined( 'NV_IS_MOD_USER' ) )
{
    die( 'Stop!!!' );
}


if ( file_exists( NV_ROOTDIR . '/' . DIR_FORUM . '/core/includes/config.php' ) ){
	
	require_once ( NV_ROOTDIR . '/' . DIR_FORUM . '/nukeviet/function.php' );
	$error = $lang_global['loginincorrect'];
	
	$vbpath = NV_ROOTDIR . '/' . DIR_FORUM;
	if( get_included_files()[0] == str_replace('/', '\\', NV_ROOTDIR) . '\admin\index.php'){
		define('CSRF_PROTECTION', false);
		require_once($vbpath . '/includes/vb5/autoloader.php');
		vB5_Autoloader::register($vbpath);
		vB5_Frontend_Application::init('config.php');	
	}
	$api = Api_InterfaceAbstract::instance();
	
	if (empty ( $nv_username )) {
		$nv_username = $nv_Request->get_title ( 'nv_login', 'post', '' );
	}
	if (empty ( $nv_password )) {
		$nv_password = $nv_Request->get_title ( 'nv_password', 'post', '' );
	}
	if (empty ( $nv_redirect )) {
		$nv_redirect = $nv_Request->get_title ( 'nv_redirect', 'post,get', '' );
	}
    $password_crypt = $crypt->hash( $nv_password );

	$logIn_Info = $api->callApi('user', 'login', array($nv_username, $nv_password));

    if ( empty($logIn_Info['errors']) )
    {
		$user_info = $vbulletin->userinfo;
		unset($user_info['phrasegroup_global'], $user_info['token'], $user_info['secret']);
		$rememberThisUser = true;
        if ( $rememberThisUser )
        {
			vB5_Auth::setLoginCookies($logIn_Info, '', $rememberThisUser);
            $nv_Request->set_Cookie( 'bbuserid', $logIn_Info['userid'], NV_LIVE_COOKIE_TIME, false );
            $nv_Request->set_Cookie( 'bbpassword', md5( $logIn_Info['password'] ), NV_LIVE_COOKIE_TIME, false );
        }

        include ( NV_ROOTDIR . '/' . DIR_FORUM . '/core/includes/config.php' );
        $tableprefix = $config['Database']['tableprefix'];
		
        $cleaned = build_query_array( $user_info['userid'], $rememberThisUser );
        $db->query( "INSERT IGNORE INTO " . $tableprefix . "session (" . implode( ', ', array_keys( $cleaned ) ) . ") VALUES (" . implode( ', ', $cleaned ) . ")" );
        
        $user_info['active'] = 0;
        $usergroupid = intval( $vbulletin->userinfo['usergroupid'] );
        if ( in_array( $usergroupid, $user_groupid_in_vbb ) )
        {
            $user_info['active'] = 1;
        }

        $birthday = 0;
        if ( $user_info['birthday'] != "" )
        {
            $arr_birthday = array_map( "intval", explode( "-", $user_info['birthday'] ) );
            if ( count( $arr_birthday ) == 3 )
            {
                $birthday = mktime( 0, 0, 0, $arr_birthday[0], $arr_birthday[1], $arr_birthday[2] );
            }
        }
        
        $user_info['userid'] = intval( $user_info['userid'] );
        $user_info['username'] = $user_info['username'];
        $user_info['email'] = $user_info['email'];
        $user_info['full_name'] = $user_info['username'];
        $user_info['birthday'] = $birthday;
        $user_info['regdate'] = intval( $user_info['joindate'] );
        
        $user_info['website'] = $user_info['homepage'];
        $user_info['location'] = "";
        $user_info['view_mail'] = 0;

        // $db->query( "SET NAMES 'utf8'" );
		$sql = "SELECT * FROM " . NV_USERS_GLOBALTABLE . " WHERE userid=" . intval ( $user_info ['userid'] );
		$result = $db->query ( $sql );
		$numrows = $result->rowCount();

        if ( $numrows > 0 )
        {
			$sql = "UPDATE " . NV_USERS_GLOBALTABLE . " SET 
                username = " . $db->quote ( $user_info ['username'] ) . ", 
                md5username = " . $db->quote ( md5 ( $user_info ['username'] ) ) . ", 
                password = " . $db->quote ( $password_crypt ) . ", 
                email = " . $db->quote ( $user_info ['email'] ) . ", 
                first_name = " . $db->quote ( $user_info ['full_name'] ) . ", 
                birthday=" . $user_info ['birthday'] . ", 
				sig=" . $db->quote ( $user_info ['signature'] ) . ", 
                regdate=" . $user_info ['regdate'] . ", 
                view_mail=" . $user_info ['view_mail'] . ",
                active=" . $user_info ['active'] . ",
                last_login=" . NV_CURRENTTIME . ", 
                last_ip=" . $db->quote ( $client_info ['ip'] ) . ", 
                last_agent=" . $db->quote ( NV_USER_AGENT ) . "
                WHERE userid=" . $user_info ['userid'];
        }
        else
        {
			$sql = "INSERT INTO " . NV_USERS_GLOBALTABLE . " 
                (userid, username, md5username, password, email, first_name, gender, photo, birthday, sig, 
                regdate, question, answer, passlostkey, 
                view_mail, remember, in_groups, active, checknum, last_login, last_ip, last_agent, last_openid) VALUES 
                (
                " . intval ( $user_info ['userid'] ) . ", 
                " . $db->quote ( $user_info ['username'] ) . ", 
                " . $db->quote ( md5 ( $user_info ['username'] ) ) . ", 
                " . $db->quote ( $password_crypt ) . ", 
                " . $db->quote ( $user_info ['email'] ) . ", 
                " . $db->quote ( $user_info ['full_name'] ) . ", 
                '', 
                '', 
                " . $user_info ['birthday'] . ", 
				" . $db->quote ( $user_info ['sig'] ) . ", 
                " . $user_info ['regdate'] . ", 
                '', '', '', 
                " . $user_info ['view_mail'] . ", 0, '', 
                " . $user_info ['active'] . ", '', 
                " . NV_CURRENTTIME . ", 
                " . $db->quote ( $client_info ['ip'] ) . ", 
                " . $db->quote ( NV_USER_AGENT ) . ", 
                '' 
			)";
        }
		if ($db->query ( $sql )) {
			$error = "";
		} else {
			$error = $lang_module ['error_update_users_info'];
		}
    }else{
		$error = $lang_global ['loginincorrect'];
	}
	
	if (empty ( $error )) {
		$user_info ['last_ip'] = $client_info ['ip'];
		$user_info ['last_agent'] = NV_USER_AGENT;
		$user_info ['last_openid'] = "";
		$user_info ['last_login'] = NV_CURRENTTIME;
		$remember = 1;
		$checknum = nv_genpass ( 10 );
		$checknum = $crypt->hash ( $checknum );
		$user = array ( //
				'userid' => $user_info ['userid'], //
				'checknum' => $checknum, //
				'current_agent' => NV_USER_AGENT, //
				'last_agent' => $user_info ['last_agent'], //
				'current_ip' => $client_info ['ip'], //
				'last_ip' => $user_info ['last_ip'], //
				'current_login' => NV_CURRENTTIME, //
				'last_login' => intval ( $user_info ['last_login'] ), //
				'last_openid' => $user_info ['last_openid'], //
				'current_openid' => '' 
		);
		
		$user = nv_base64_encode ( serialize ( $user ) );
		$opid = "";
		$db->query ( "UPDATE " . NV_USERS_GLOBALTABLE . " SET 
		checknum = " . $db->quote ( $checknum ) . ", 
		last_login = " . NV_CURRENTTIME . ", 
		last_ip = " . $db->quote ( $client_info ['ip'] ) . ", 
		last_agent = " . $db->quote ( NV_USER_AGENT ) . ", 
		last_openid = " . $db->quote ( $opid ) . ", 
		remember = " . $remember . " 
		WHERE userid=" . $user_info ['userid'] );
		
		$live_cookie_time = ($remember) ? NV_LIVE_COOKIE_TIME : 0;

		$nv_Request->set_Cookie ( 'sessionhash', $logIn_Info['sessionhash'], $live_cookie_time );
		$nv_Request->set_Session ( 'sessionhash', $logIn_Info['sessionhash'] );
	}
}
else
{
    trigger_error( "Error no forum vbb", 256 );
}

