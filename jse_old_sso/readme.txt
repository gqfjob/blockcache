php sso登录主要涉及如下几个页面

ssologin.php :发送认证请求页面

response.php :统一认证服务器处理认证请求后，传输认证信息目的页，也就是ssologin.php中的callback

ssologout.php :第三方主动发送注销请求到统一认证服务器

ssologout_response.php :第三方接收认证服务器的用户注销要求页面

AES.class.php :AES加密解密类

RSA.class.php :RSA加密解密类

SSOUtil.class.php :常用工具函数类


如果服务器提供的证书为cer和cfx格式，先用openssl工具转换为pem格式，转换命令如下：

pfx->pem：
openssl pkcs12 -in xxx.pfx -out xxx.pem -nodes
cer->pem
openssl x509 -in xxx.cer -inform DER -out xxx.pem


测试环境：
Apache 2.2.9
PHP 5.2.6
OpenSSL 0.9.8h 
php需要开启mcrypt和open_ssl扩展


jse_

jse_tongbuzhuxue.pfx 同步助学系统的私钥证书（有效期至2012-10-12）的使用密码：
L09KWNvq
32-TDE


http://sso.jse.edu.cn/service/authn.aspx

http://wsxly.jsreading.com:8080/sso/response.php