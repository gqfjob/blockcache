<?php
class AES{
	private $iv;
	private $cipher_alg;
	private $key;
	public function __construct($key="",$iv=""){
		if("" == trim($key)){
			$this->createKey();
		}else{
			$this->key = $key;
		}
		$this->cipher_alg = MCRYPT_RIJNDAEL_128;
		if("" == trim($iv)){
			$this->createIV();
		}else{
			$this->iv = $iv;
		}
		
	}
	public function __set($property_name, $value){
		$this->$property_name = $value;	
	}
	public function __get($property_name){
		if(isset($this->$property_name)){
			return $this->$property_name;
		}else{
			return (NULL);	
		}
	}
	
	private function generate_rand($l){
		$c= "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789";
		srand((double)microtime()*1000000);
		for($i=0; $i<$l; $i++) {
		  $rand.= $c[rand()%strlen($c)];
		}
		return $rand;
	}

	private function createKey(){
		$this->key = $this->generate_rand(16);
	}
	private function createIV(){
		$this->iv = mcrypt_create_iv (mcrypt_get_iv_size ($this->cipher_alg,MCRYPT_MODE_CBC), MCRYPT_RAND);	
	}
	//把要加密字符串位数补足为16的倍数
	private function  pad2Length($text, $padlen){   
		$len = strlen($text)%$padlen;
		$res = $text;
		if($len > 0){
			$span = $padlen-$len;
			for($i=0; $i<$span; $i++){   
				$res .= chr($span);
			}
		}
		return $res;   
	} 
	//去掉加密时补足的位数
	private function  depad2Length($text, $padlen){
		$strlen = strlen($text);
		$padlen = ord(substr($text,$strlen-1));
		if($padlen < 16){
			$str = substr($text,0,$strlen - $padlen);
		}
		return $str;
	}
	//将xxx=ddd&uuu=223放到数组("xxx"=>"ddd","uuu"=>"223"),并且把value都进行url解码
	private function urlStr2Array($str){
		$requests  = explode("&", $str);
		foreach($requests as $k => $v){
			$req = explode("=", $v);
			$keys[]=  $req[0];
			$values[] = urldecode($req[1]);
		}
		$sArray = array_combine($keys,$values);
		return $sArray;
	}
	/*
	*对key，iv进行base64加密组合
	*$key AES加密中的key, $iv AES加密中iv
	*/
	public function compKeyIv(){
		return base64_encode($this->key)."&".base64_encode($this->iv);	
	}
	//获取全部参数
	public function getRequests($str){
		return $this->urlStr2Array($str);
	}
	//从参数中获得签名
	public function getSignature($str){
		$arr = $this->getRequests($str);
		return base64_decode($arr["sso_signature"]);
	}
	//获取被签名字符串
	public function getSignedStrSrc($requestStr){
		$src = substr($requestStr,0, strpos($requestStr,"sso_signature")-1);
		return $src;
	}
	//返回sso_request
	public function AESEncode($string){	
		$cipher = mcrypt_module_open($this->cipher_alg, '', MCRYPT_MODE_CBC, '');    
		if (mcrypt_generic_init($cipher, $this->key, $this->iv) != -1)   {     
			//如果$data不是128位也就是16字节的倍数，补充字符满足这个条件，补充规则：字符串对16求余，余数为多少就补充多少位余数对应的字符（chr(余数)）	
			$cipherText = mcrypt_generic($cipher,$this->pad2Length($string, 16));   
			mcrypt_generic_deinit($cipher); 
			mcrypt_module_close($cipher);
			$sso_request = base64_encode($cipherText);
			return $sso_request;
		}		
	}
	public function AESDecode($encrypted_string){
		$td = mcrypt_module_open($this->cipher_alg, '', MCRYPT_MODE_CBC, '');	
		if (mcrypt_generic_init($td, $this->key, $this->iv) != -1) {
			mcrypt_generic_init($td, $this->key, $this->iv);
			$src = mdecrypt_generic($td, base64_decode($encrypted_string));
			mcrypt_module_close($td);
			return $this->depad2Length($src,16);
		}
	}
}
?>