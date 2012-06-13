<?php
session_start();
ob_start();
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>本地登录</title>
</head>

<body>
<?php

if(isset($_SESSION['username']) && ("" != $_SESSION['username'])){
	echo "欢迎".$_SESSION['username']."登录系统<br>";
	echo '<a href="index.php">回到首页</a><br>';
	echo '<a href="ssologin.php">SSO登录测试页</a><br>';
	echo '<a href="logout.php">本地注销</a><br>';
	echo '<a href="ssologout.php">sso注销</a><br>';
}else{
		if(($_REQUEST['act']) && ("login" == trim($_REQUEST['act']))){
			$username = trim($_REQUEST['username']);	
			$password = trim($_REQUEST['password']);	
			if("sanyuan" == $username){
				if("123456" == $password){
					//写session
					$_SESSION['username'] = $username;
					header("Location: index.php");
					ob_end_flush();
				}else{
					$_SESSION["username"] = "";
					echo "密码输入错误.<br>";
					echo '<a href="index.php">回到首页</a><br>';
					echo '<a href="login.php">重新登录</a><br>';
					echo '<a href="ssologin.php">SSO登录</a><br>';
				}
			}else{
				$_SESSION["username"] = "";
				echo "系统无此用户.<br>";
				echo '<a href="index.php">回到首页</a><br>';
				echo '<a href="index.php">重新登录</a><br>';
				echo '<a href="ssologin.php">SSO登录</a><br>';
			}
		}else{
?>
            游客登录：<br/>
            <form action="login.php?act=login" method="post">
            <div>
                <dl><dd>用户名</dd><dd><input type="text" name="username" value=""/></dd></dl>
                <dl><dd>密码：</dd><dd><input type="password" name="password" value=""/></dd></dl>
                <dl><dd><input type="submit" value="登录"/></dd></dl>
            </div>
            </form>
<?php
		}
}
?>
</body>
</html>