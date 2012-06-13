<?php include_once 'htmlcache.class.php';?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<title>Insert title here</title>
</head>
<body>
    <div> 这里是已经存在的内容</div>
    <div>
        <?php getdata("getmeetting", "context",array('id'=>1234));?>
        <?php //deldata("getmeetting", "context",array('id'=>1234));?>
    </div>
</body>
</html>