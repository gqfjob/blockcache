<?php
session_start();
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>sso注销页面</title>
</head>
<?php
require_once('AES.class.php');
require_once('RSA.class.php');
	echo "欢迎[ ".$_SESSION['username']." ]登录系统<br>";


	//定义数据传输方式POST或GE
	$dataTransType = "POST";
	//服务器的公钥证书
	$serverPubKeyFile = 'jse_sso_server.pem';
	//含有个人私钥的安全证书
	$personalPrivKeyFile = 'jse_tongbuzhuxue.pem';
	//个人私钥解密密码
	$privateKeyPass = 'L09KWNvq';
	//要传递给服务器的基础数据，sso_callback需要根据自己的设定修改
	$postData = array("sso_service_code"=>"32-TDE","sso_callback"=>"http://test.jiaoyu365.net/sso/index.php","sso_timestamp"=>"1288777663","sso_nonce"=>"8bdb6603771c4f129c1eeb28bc902416");

	$rsa = new RSA($serverPubKeyFile,$personalPrivKeyFile,$privateKeyPass);
	//组合请求字符串
	$src = $rsa->compStr($postData);
	//获取使用私钥签名后的字符串
	$signedStr = $rsa->getSignedStr($src);
	$key = base64_decode("evnFVJ+X7tAiGTToNWSqJQ==");
	$iv = base64_decode("QaebkbfIlcV/nlUoJs9n3Q==");
	$aes = new AES();
	$aes->key = $key;
	$aes->iv = $iv;
	$sso_request = $aes->AESEncode($signedStr);
	//组合key和iv
	$cKeyIv = $aes->compKeyIv();
	//加密Key和Iv形成sso_secret
	$sso_secret = $rsa->getSecret($cKeyIv);
	if("POST" == $dataTransType){
		$sso_secret = htmlspecialchars($sso_secret);
		$sso_request = htmlspecialchars($sso_request);
	}else{
		$sso_secret = urlencode($sso_secret);
		$sso_request = urlencode($sso_request);
	}
	//先把本地session注销
	$_SESSION['username'] = null;

	//再告知认证服务器
?>
<body>
<form action="http://sso.jse.edu.cn/service/logout.aspx" method="post">
<div>
<dl><dd>sso_secret :</dd><dd><textarea name="sso_secret" cols="100"  rows="3"><?php echo $sso_secret;?></textarea></dd></dl>
<dl><dd>sso_request:</dd><dd><textarea name="sso_request" cols="100" rows="6"><?php echo $sso_request; ?></textarea></dd></dl>
<dl><dd><input type="submit" value="现在注销"/></dd></dl>
</div>

</form>
</body>
</html>