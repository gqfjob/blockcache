<?php
define('BASE_PATH',str_replace('\\','/',realpath(dirname(__FILE__).'/'))."/");
require_once 'ftp.class.php';

header("Content-type: text/html; charset=utf-8");

$config = array('hostname'=>'10.8.25.59','username'=>'user','password'=>'123');
$client = new Ftp;
if($client->connect($config)){
    /*$out = $client->filelist(".","raw");
    var_dump($out);
    echo "下载多个文件<br/>";
    if(count($out)){
    	foreach($out as $k => $v){
    		$client->download($v,BASE_PATH.'/download/'.$v);
    	}
    }*/
	$client->download_file(BASE_PATH.'download/');
    echo "下载文件成功<br/>";
    
    echo "上传一个文件<br/>";
    echo "上传文件成功<br/>";
    
    echo "删除一个文件<br/>";
    echo "删除文件成功<br/>";
    
    echo "下载一个目录<br/>";
    echo "下载目录成功<br/>";
    
}else{
    echo "cannot connect to ftp server";
}