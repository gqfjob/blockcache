<?php session_start();?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>request</title>
</head>
<?php
require_once('SSOUtil.class.php');
require_once('AES.class.php');
require_once('RSA.class.php');

if(isset($_SESSION['username']) && ("" != trim($_SESSION['username']))){
	echo "欢迎".$_SESSION['username']."登录系统<br>";
	echo '<a href="index.php">回到首页</a><br>';
	echo '<a href="logout.php">注销</a><br>';
	echo '<a href="ssologout.php">sso注销</a>';
}else{
	//定义数据传输方式POST或GE
	$dataTransType = "POST";
	//服务器的公钥证书
	$serverPubKeyFile = 'jse_sso_server.pem';
	//含有个人私钥的安全证书
	$personalPrivKeyFile = 'jse_tongbuzhuxue.pem';
	//个人私钥解密密码
	$privateKeyPass = 'L09KWNvq';

	$ssoUtil = new SSOUtil();
	$currentTime = $ssoUtil->getTimeStamp();
	$sso_nonce = $ssoUtil->mkGUID();
	//要传递给服务器的基础数据
	$postData = array("sso_service_code"=>"32-TDE","sso_callback"=>"http://test.jiaoyu365.net/sso/response.php","sso_timestamp"=>$currentTime,"sso_nonce"=>$sso_nonce);

	$rsa = new RSA($serverPubKeyFile,$personalPrivKeyFile,$privateKeyPass);
	//组合请求字符串
	$src = $rsa->compStr($postData);
	echo "原始请求字符串:<br>".$src."<br>";
	//获取使用私钥签名后的字符串
	$signedStr = $rsa->getSignedStr($src);
	echo "私钥签名后的字符串:<br>".$signedStr."<br>";

	//aes加密形成sso_request，如果没有指定key和iv的生成策略，则随机生成16位的key，iv由算法本身自动生成
	//$key = base64_decode("evnFVJ+X7tAiGTToNWSqJQ==");
	//$iv = base64_decode("QaebkbfIlcV/nlUoJs9n3Q==");
	$aes = new AES();
	//$aes->key = $key;
	//$aes->iv = $iv;
	$sso_request = $aes->AESEncode($signedStr);
	echo "aes加密后的sso_request<br>".$sso_request."<br>";

	//组合key和iv
	$cKeyIv = $aes->compKeyIv();
	echo "key&iv字符串:<br>".$cKeyIv."<br>";

	//加密Key和Iv形成sso_secret
	$sso_secret = $rsa->getSecret($cKeyIv);
	echo "加密后的sso_sercet:<br>".$sso_secret."<br>";


	//对要传输的数据进行重新编码，post方式使用html编码，get方式使用url编码
	if("POST" == $dataTransType){
		$sso_secret = htmlspecialchars($sso_secret);
		$sso_request = htmlspecialchars($sso_request);
	}else{
		$sso_secret = urlencode($sso_secret);
		$sso_request = urlencode($sso_request);
	}
?>
<form action="http://sso.jse.edu.cn/service/authn.aspx" method="post">
<div>
<dl><dd>sso_secret :</dd><dd><textarea name="sso_secret" cols="100"  rows="3"><?php echo $sso_secret;?></textarea></dd></dl>
<dl><dd>sso_request:</dd><dd><textarea name="sso_request" cols="100" rows="6"><?php echo $sso_request; ?></textarea></dd></dl>
<dl><dd><input type="submit" value="提交"/></dd></dl>
</div>

</form>
<?php
}
?>
<body>
</body>
</html>