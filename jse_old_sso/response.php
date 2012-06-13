<?php
session_start();
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>处理服务器返回请求</title>
</head>
<?php
require_once('AES.class.php');
require_once('RSA.class.php');

//获取参数
if(isset($_POST['sso_request'])){
	$sso_request = htmlspecialchars_decode(trim($_REQUEST['sso_request']));
	$sso_secret = htmlspecialchars_decode(trim($_REQUEST['sso_secret']));
}else{
	$sso_request = urldecode($sso_request);
	$sso_secret = urldecode($sso_secret);
}
echo "原始返回字符串sso_request：<br>".$sso_request."<br/>";
echo "原始返回字符串sso_secret:<br>".$sso_sercet."<br/>";
//指定证书文件
//服务器公钥证书
$serverPubKeyFile = 'jse_sso_server.pem';
//$serverPubKeyFile = 'jse_test_client.pem';
//个人安全证书
$personalPrivKeyFile = 'jse_tongbuzhuxue.pem';
$privateKeyPass = 'L09KWNvq';

//解密SSO_secret,获取key 和iv
$rsa = new RSA($serverPubKeyFile,$personalPrivKeyFile,$privateKeyPass);

$keyIvArray = $rsa->getKeyIvArray($sso_secret);
echo "解密获得的base64加密key:".base64_encode($keyIvArray["key"])."  解密获得的base64加密iv:".base64_encode($keyIvArray["iv"])."<br>";

//解密sso_request
$aes = new AES();
$aes->key = $keyIvArray["key"];
$aes->iv = $keyIvArray["iv"];
$requestStr = $aes->AESDecode($sso_request);
//获取sso_request中全部传递参数，保存到数组

$requestArr = $aes->getRequests($requestStr);

//获得解密被签名的原始字符串
$signedData = $aes->getSignedStrSrc($requestStr);
echo "被签名字符串<br/>".$signedData."<br/>";
echo "解码被签名字符<br>".urldecode($signedData)."<br>";
//获取签名
$signature = $aes->getSignature($requestStr);
echo "签名为：<br>".urlencode(base64_encode($signature))."<br>";

//使用服务器公钥验证签名
$verify = $rsa->checkSignature($signedData,$signature);
if($verify){
	echo "签名通过验证";
	$_SESSION['username'] = $requestArr['user_loginname'];
	echo "<br>欢迎[ ".$_SESSION['username']." ]登录系统<br>";
	echo '<a href="index.php">回到首页</a><br>';
	echo '<a href="logout.php">注销</a><br>';
	echo '<a href="ssologout.php">sso注销</a>';
}else{
	echo "签名验证失败";
}



?>
<body>
</body>
</html>