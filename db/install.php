<?php

require_once($CFG->dirroot . "/user/lib.php");

function xmldb_forumanonymous_install() {
    global $CFG, $DB;

    $anon_user 			= new stdClass();
    $anon_user->username 	= get_config('forumanonymous_anonuser');
    $anon_user->lastname 	= $anon_user->username;
    $anon_user->auth 		= 'manual';
//     $anon_user->password 	= '';

    // From PHP 5.0, empty() evaluates to false for objects without members
	if (empty((array)$anon_user->username)) {
		//$anon_user->username 	= 'Anonymous';
		// Username must now always be lowercase
		$anon_user->username 	= 'anonymous';
		$anon_user->lastname 	= $anon_user->username;
        set_config('forumanonymous_anonuser', $anon_user->username);
    }
    //$anon_pw = (String)mt_rand();
	// Passwords must now contain lowercase and uppercase letters, digits and special chars
	$anon_pw = "_MyPassword".(String)mt_rand();
    $anon_user->password = $anon_pw;

    if ($DB->count_records('user', array('username'=>$anon_user->username)) == 0){
        set_config('forumanonymous_anonid', user_create_user($anon_user)); 
    }else{
    
		//$anon_pw = ''; // Just to make that clear.
		$anon_pw = "_MyPassword".(String)mt_rand();
		$anon_user = $DB->get_record('user', array('username'=>$anon_user->username));
		set_config('forumanonymous_anonid', $anon_user->id);
		update_internal_user_password($anon_user, $anon_pw);// If someone else had created this user, they're now locked out.
		//$anon_user->username 	= 'Anonymous';
		$anon_user->username 	= 'anonymous';
		$anon_user->lastname 	= $anon_user->username;
		$anon_user->email 	 = ''; // It might have been set.
		$DB->update_record('user', $anon_user);
    }

}

function xmldb_forumanonymous_install_recovery() {
	xmldb_forumanonymous_install();
}
