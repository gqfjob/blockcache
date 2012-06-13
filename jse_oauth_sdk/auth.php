<?PHP
include_once('config.php');
include_once('jse.oauth2.class.php');

$back = new JSEOAuth(WB_AKEY,WB_SKEY);

$url = $back->getAuthorizeURL(CANVAS_PAGE);
//echo $url."<br/>";
header("location:".$url);

?>

