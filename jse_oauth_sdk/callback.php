<?php
session_start();

include_once( 'config.php' );
include_once( 'jse.oauth2.class.php' );

$code = $_GET['code'];
$keys = array('redirect_uri'=>CALLBACK_PAGE,'code'=>$code);
//换取acc_token
$capi = new JSEOAuth(WB_AKEY,WB_SKEY);
$access_token = $capi->getAccessToken('code',$keys);
var_dump($access_token);
//$url = 'http://api2.jiaoyu365.net/oauth2/access_token?client_id='.WB_AKEY.'&client_secret='.WB_SKEY.'&grant_type=authorization&redirect_uri='.CALLBACK_PAGE.'&code=$code';

//post $url then get the back data

?>
