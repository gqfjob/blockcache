php sso��¼��Ҫ�漰���¼���ҳ��

ssologin.php :������֤����ҳ��

response.php :ͳһ��֤������������֤����󣬴�����֤��ϢĿ��ҳ��Ҳ����ssologin.php�е�callback

ssologout.php :��������������ע������ͳһ��֤������

ssologout_response.php :������������֤���������û�ע��Ҫ��ҳ��

AES.class.php :AES���ܽ�����

RSA.class.php :RSA���ܽ�����

SSOUtil.class.php :���ù��ߺ�����


����������ṩ��֤��Ϊcer��cfx��ʽ������openssl����ת��Ϊpem��ʽ��ת���������£�

pfx->pem��
openssl pkcs12 -in xxx.pfx -out xxx.pem -nodes
cer->pem
openssl x509 -in xxx.cer -inform DER -out xxx.pem


���Ի�����
Apache 2.2.9
PHP 5.2.6
OpenSSL 0.9.8h 
php��Ҫ����mcrypt��open_ssl��չ


jse_

jse_tongbuzhuxue.pfx ͬ����ѧϵͳ��˽Կ֤�飨��Ч����2012-10-12����ʹ�����룺
L09KWNvq
32-TDE


http://sso.jse.edu.cn/service/authn.aspx

http://wsxly.jsreading.com:8080/sso/response.php