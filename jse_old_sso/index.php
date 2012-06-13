<?php
session_start();
if(isset($_SESSION['username']) && "" != $_SESSION['username']){
	$welcome = "欢迎[ ".$_SESSION['username']." ]登录系统<br>";
}else{
	$welcome = "欢迎[ 游客 ]来到系统<br>";
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>首页</title>
</head>

<body>
PHP SSO示例<br>
<?=$welcome?>
<div>
<a href="login.php" >本地登录</a>
<a href="ssologin.php">sso登录</a>
</div>
</body>
</html>