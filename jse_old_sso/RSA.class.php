<?php
class RSA{
	//服务器公钥证书
	private	 $serverPubKeyFile;
	//包含个人公钥和私钥的证书
	private	 $personalPrivKeyFile;
	//私钥密码
	private  $privateKeyPass;

	
	public function __construct($sf,$pf,$pkp = ""){
			$this->serverPubKeyFile = $sf;
			$this->personalPrivKeyFile = $pf;
			$this->privateKeyPass = $pkp;
	}
	public function __set($parma, $value){
		$this->$param = $value;
	}
	public function __get($param){
		if(isset($this->$param)){
			return $this->$param;	
		}else{
			return(NULL);
		}
	}
	
	
	private function getFile($filename){
		$file = file_get_contents($filename);
		if(!$file){
			echo "file: ".$filename." does not exist or can not open!";
		}
		return $file;
	}

	/*
	*从证书中获取密钥
	*$type取值“PUB”或其他,PUB表示要取公钥，其他表示取密钥
	*$filename 证书文件名，包含路径
	*/
	private function getKey($type,$filename){
		$file = $this->getFile($filename);
		if($file){
			if("PUB" == trim($type)){
				$Key = openssl_get_publickey($file);

			}else{
				if("" == $this->privateKeyPass){
					$Key = openssl_get_privatekey($file);
				}else{
					$Key = openssl_get_privatekey($file,$this->privateKeyPass);
				}
			}
		}
		return $Key;
	}

	/*
	*从数组中形成原始请求字符串
	*$arr 传递的数组
	*/
	public function compStr($arr){
		$str = "";
		foreach($arr as $key=>$value){
			$key = urlencode($key);
			$value = urlencode($value);
			$str .= $key."=".$value."&";
		}
		
		$str = substr($str,0,-1);
		return $str;
	}
	
	/*
	*从证书中获取服务器公钥，加密字符串，并返回base64加密后的字符串形成sso_secret参数
	*$str被加密字符串
	*返回值 ：sso_secret
	*/
	public function getSecret($src){
		$pubKey = $this->getKey("PUB",$this->serverPubKeyFile);
		openssl_public_encrypt ($src, &$crypted, $pubKey, OPENSSL_PKCS1_PADDING);
		return base64_encode($crypted);
	}

	/*
	*从证书中获取个人私钥，解密服务器返回的sso_secret参数，并返回RSA解码用的key和Iv
	*$cryptedStr 被加密字符串
	*/	
	public function getKeyIvArray($cryptedStr){
		$privKey = $this->getKey("PRIV",$this->personalPrivKeyFile);
		openssl_private_decrypt(base64_decode($cryptedStr),&$decodeStr, $privKey,OPENSSL_PKCS1_PADDING);
		$keyIV =explode("&",$decodeStr);	
		$res["key"] = base64_decode($keyIV[0]);
		$res["iv"] = base64_decode($keyIV[1]);
		return $res;
	}
	/*
	*从个人证书中获取私钥，给字符串签名，并返回组合后的字符串
	*$src 待签名串
	*返回值：签名后的数组（包含签名内容）
	*/	
	public function getSignedStr($src){
		$privKey = $this->getKey("PRIV",$this->personalPrivKeyFile);
		openssl_sign($src, &$binary_signature, $privKey, OPENSSL_ALGO_SHA1); 
		$signatured = $src."&sso_signature=".urlencode(base64_encode($binary_signature));
		return $signatured;
	}
	
	/*
	*从服务器证书中获取公钥，验证字符串签名，验证结果
	*$signedData 被签名字符串，$signature， 签名
	*返回值：通过验证返回true，否则返回false
	*/
	public function checkSignature($signedData,$signature){
		$pubKey = $this->getKey("PUB",$this->serverPubKeyFile);
		if(1 === openssl_verify($signedData, $signature, $pubKey, OPENSSL_ALGO_SHA1)){
			$res = true;
		}else{
			$res = false;	
		}
		return $res;
	}
}
?>