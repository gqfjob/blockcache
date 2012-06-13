<?php
class SSOUtil{
	//获取随机字符串
	public function getRandomData($i){
		$c= "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789";
		srand((double)microtime()*1000000);
		for($i=0; $i<$l; $i++) {
		  $rand.= $c[rand()%strlen($c)];
		}
		return $rand;
	}
	//获取一个随机字符串
	public function mkGUID(){
		$microTime = microtime();
		list($a_dec, $a_sec) = explode(" ", $microTime);
		$dec_hex = dechex($a_dec* 1000000);
		$sec_hex = dechex($a_sec);
		substr($dec_hex, 0, 5);
		substr($sec_hex, 0, 6);
		$guid = "";
		$guid .= $dec_hex;
		$guid .= $this->create_guid_section(3);
		$guid .= $this->create_guid_section(4);
		$guid .= $this->create_guid_section(4);
		$guid .= $this->create_guid_section(4);
		$guid .= $sec_hex;
		$guid .= $this->create_guid_section(4);
		return $guid;
	}
	public function getTimeStamp(){
		return mktime();	
	}
	private function create_guid_section($characters){
		$return = "";
		for($i=0; $i<$characters; $i++){
			$return .= dechex(mt_rand(0,15));
		}
		return $return;
	}
}
?>