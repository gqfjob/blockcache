本缓存框架适用于页面局部缓存情况

由配置文件config.php来控制缓存板块和时间，以及缓存存储位置

缓存规则说明
'getmeetting'=>array('context','50','md5',array('id'))
规则 ： '需要缓存的action（或者页面）'=>array('需要缓存的block','缓存时间，以秒为单位，-1表示永久缓存','md5等自定义的文件名生成函数','生成缓存文件名称需要的参数数组');
调用：
    取数据：
        <?php getdata("getmeetting", "context",array('id'=>1234));?>
        说明：getdata($action, $block,$params_需要传递的参数);
        $params用途
            1：在$action_$block.php需要接受的参数如文章id
            2：缓存文件名生成规则中用到的参数
    删除缓存数据：    
        <?php //deldata("getmeetting", "context",array('id'=>1234));?>
        参数应该和生成缓存规则一致
先在action目录中根据板块来写取数据的代码,名称规则 $action_$block.php（如 getmeetting_context.php）,该页面需要的参数全部由
$params传递进来，然后配置缓存规则，在实际代码中调用.