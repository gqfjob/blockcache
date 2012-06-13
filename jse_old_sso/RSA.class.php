<?php
class RSA{
	//��������Կ֤��
	private	 $serverPubKeyFile;
	//�������˹�Կ��˽Կ��֤��
	private	 $personalPrivKeyFile;
	//˽Կ����
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
	*��֤���л�ȡ��Կ
	*$typeȡֵ��PUB��������,PUB��ʾҪȡ��Կ��������ʾȡ��Կ
	*$filename ֤���ļ���������·��
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
	*���������γ�ԭʼ�����ַ���
	*$arr ���ݵ�����
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
	*��֤���л�ȡ��������Կ�������ַ�����������base64���ܺ���ַ����γ�sso_secret����
	*$str�������ַ���
	*����ֵ ��sso_secret
	*/
	public function getSecret($src){
		$pubKey = $this->getKey("PUB",$this->serverPubKeyFile);
		openssl_public_encrypt ($src, &$crypted, $pubKey, OPENSSL_PKCS1_PADDING);
		return base64_encode($crypted);
	}

	/*
	*��֤���л�ȡ����˽Կ�����ܷ��������ص�sso_secret������������RSA�����õ�key��Iv
	*$cryptedStr �������ַ���
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
	*�Ӹ���֤���л�ȡ˽Կ�����ַ���ǩ������������Ϻ���ַ���
	*$src ��ǩ����
	*����ֵ��ǩ��������飨����ǩ�����ݣ�
	*/	
	public function getSignedStr($src){
		$privKey = $this->getKey("PRIV",$this->personalPrivKeyFile);
		openssl_sign($src, &$binary_signature, $privKey, OPENSSL_ALGO_SHA1); 
		$signatured = $src."&sso_signature=".urlencode(base64_encode($binary_signature));
		return $signatured;
	}
	
	/*
	*�ӷ�����֤���л�ȡ��Կ����֤�ַ���ǩ������֤���
	*$signedData ��ǩ���ַ�����$signature�� ǩ��
	*����ֵ��ͨ����֤����true�����򷵻�false
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