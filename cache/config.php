<?php
define('ENABLED_HTML_CACHE', true);
define('CODE_INSIDE', true);//保证actions中的代码不被直接请求
$cachecfg = array(
    'rules'=>array(
        'index'=>array('context','-1','md5',array()),
        'getmeetting'=>array('context','50','md5',array('id'))
    ),
    'cachePath'=>'./html/'
);