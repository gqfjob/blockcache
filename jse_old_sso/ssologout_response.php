<?php
session_start();
header('HTTP/1.1 200 OK');
header('Cache-Control:no-cache, no-store');
header('Pragma:no-cache');
header('Content-Type:application/javascript;charset=utf-8');
header('Expires:-1');
header('P3P: CP="CAO DSP COR CUA ADM DEV TAI PSA PSD IVAi IVDi CONi TELo OTPi OUR DELi SAMi OTRi UNRi PUBi IND PHY ONL UNI PUR FIN COM NAV INT DEM CNT STA POL HEA PRE GOV"');
$rid = urldecode($_GET['rid']);
$callback = urldecode($_GET['callback']);
//注销自己系统中的用户session等
$_SESSION['username'] = null;
echo $callback."('".$rid."');";
?>