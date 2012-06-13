<?php
/**
 * PHP SDK for jse platform(using OAuth2)
 * 
 * @author guoqiang <gqfjob@gmail.com>
 */

/**
 * @ignore
 */
class OAuthException extends Exception {
	// pass
}


/**
 *JSE OAuth 认证类(OAuth2)
 *
 * @author guoqiang
 * @version 1.0
 */
class JSEOAuth {
	/**
	 * @ignore
	 */
	public $client_id;
	/**
	 * @ignore
	 */
	 public $client_secret;
	/**
	 * @ignore
	 */
	public $access_token;
	/**
	 * @ignore
	 */
	public $refresh_token;
	/**
	 * Contains the last HTTP status code returned. 
	 *
	 * @ignore
	 */
	public $http_code;
	/**
	 * Contains the last API call.
	 *
	 * @ignore
	 */
	public $url;
	/**
	 * Set up the API root URL.
	 *
	 * @ignore
	 */
	public $host = "http://api2.jiaoyue365.net/";
	/**
	 * Set timeout default.
	 *
	 * @ignore
	 */
	public $timeout = 30;
	/**
	 * Set connect timeout.
	 *
	 * @ignore
	 */
	public $connecttimeout = 30;
	/**
	 * Verify SSL Cert.
	 *
	 * @ignore
	 */
	public $ssl_verifypeer = FALSE;
	/**
	 * Respons format.
	 *
	 * @ignore
	 */
	public $format = 'json';
	/**
	 * Decode returned json data.
	 *
	 * @ignore
	 */
	public $decode_json = TRUE;
	/**
	 * Contains the last HTTP headers returned.
	 *
	 * @ignore
	 */
	public $http_info;
	/**
	 * Set the useragnet.
	 *
	 * @ignore
	 */
	public $useragent = 'OAuth2 v0.1';
	/* Immediately retry the API call if the response was not successful. */
	//public $retry = TRUE;

	/**
	 * print the debug info
	 *
	 * @ignore
	 */
	public $debug =true;

	/**
	 * Set API URLS
	 */
	/**
	 * @ignore
	 */
	function accessTokenURL()  { return 'http://api2.jiaoyu365.net/oauth2/access_token'; }
	/**
	 * @ignore
	 */
	function authorizeURL()    { return 'http://api2.jiaoyu365.net/oauth2/authorize'; }

	/**
	 * construct WeiboOAuth object
	 */
	function __construct($client_id, $client_secret, $access_token = NULL, $refresh_token = NULL) {
		$this->client_id = $client_id;
		$this->client_secret = $client_secret;
		$this->access_token = $access_token;
		$this->refresh_token = $refresh_token;
	}

	/**
	 * Get the authorize URL
	 *
	 * @return string
	 */
	function getAuthorizeURL( $url, $response_type = 'code' ) {
		$params = array();
		$params['client_id'] = $this->client_id;
		$params['redirect_uri'] = $url;
		$params['response_type'] = $response_type;
		return $this->authorizeURL() . "?" . http_build_query($params);
	}

	/**
	 * Exchange the request token and secret for an access token and
	 * secret, to sign API calls.
	 *
	 * @return array array("oauth_token" => the access token,
	 *                "oauth_token_secret" => the access secret)
	 */
	function getAccessToken( $type = 'code', $keys ) {
		$params = array();
		$params['client_id'] = $this->client_id;
		$params['client_secret'] = $this->client_secret;
		if ( $type === 'token' ) {
			$params['grant_type'] = 'refresh_token';
			$params['refresh_token'] = $keys['refresh_token'];
		} elseif ( $type === 'code' ) {
			$params['grant_type'] = 'authorization_code';
			$params['code'] = $keys['code'];
			$params['redirect_uri'] = $keys['redirect_uri'];
		} elseif ( $type === 'password' ) {
			$params['grant_type'] = 'password';
			$params['username'] = $keys['username'];
			$params['password'] = $keys['password'];
		} else {
			throw new OAuthException("wrong auth type");
		}

		$response = $this->oAuthRequest($this->accessTokenURL(), 'POST', $params);
		$token = json_decode($response,true);
		if ( is_array($token) && !isset($token['error']) ) {
			$this->access_token = $token['access_token'];
			if(isset($token['refresh_token'])){
				$this->refresh_token = $token['refresh_token'];
			}
		} else {
			throw new OAuthException("get access token failed." . $token['error']);
		}
		return $token;
	}

	function setAccessToken($token){
		$this->access_token = $token;
	}

	/**
	 * 解析 signed_request
	 *
	 * @param string $signed_request 应用框架在加载iframe时会通过向Canvas URL post的参数signed_request
	 *
	 * @return array
	 */
	function parseSignedRequest($signed_request) {
		list($encoded_sig, $payload) = explode('.', $signed_request, 2); 
		$sig = self::base64decode($encoded_sig) ;
		$data = json_decode(self::base64decode($payload), true);
		if (strtoupper($data['algorithm']) !== 'HMAC-SHA256') return '-1';
		$expected_sig = hash_hmac('sha256', $payload, $this->client_secret, true);
		return ($sig !== $expected_sig)? '-2':$data;
	}

	/**
	 * @ignore
	 */
	function base64decode($str) {
		return base64_decode(strtr($str.str_repeat('=',(4 - strlen($str) % 4)), '-_', '+/'));
	}

	/**
	 * 读取jssdk授权信息，用于和jssdk的同步登录
	 *
	 * @return array 成功返回array('access_token'=>'value', 'refresh_token'=>'value'); 失败返回false
	 */
	function getTokenFromJSSDK() {
		$key = "weibojs_" . $this->client_id;
		if ( isset($_COOKIE[$key]) && $cookie = $_COOKIE[$key] ) {
			parse_str($cookie, $token);
			if ( isset($token['access_token']) && isset($token['refresh_token']) ) {
				$this->access_token = $token['access_token'];
				$this->refresh_token = $token['refresh_token'];
				return $token;
			} else {
				return false;
			}
		} else {
			return false;
		}
	}

	/**
	 * 从数组中读取access_token和refresh_token
	 * 常用于从Session或Cookie中读取token，或通过Session/Cookie中是否存有token判断登录状态。
	 *
	 * @param array $arr 存有access_token和secret_token的数组
	 * @return array 成功返回array('access_token'=>'value', 'refresh_token'=>'value'); 失败返回false
	 */
	function getTokenFromArray( $arr ) {
		if (isset($arr['access_token']) && $arr['access_token']) {
			$token = array();
			$this->access_token = $token['access_token'] = $arr['access_token'];
			if (isset($arr['refresh_token']) && $arr['refresh_token']) {
				$this->refresh_token = $token['refresh_token'] = $arr['refresh_token'];
			}

			return $token;
		} else {
			return false;
		}
	}

	/**
	 * GET wrappwer for oAuthRequest.
	 *
	 * @return mixed
	 */
	function get($url, $parameters = array()) {
		$response = $this->oAuthRequest($url, 'GET', $parameters);
		if ($this->format === 'json' && $this->decode_json) {
			return json_decode($response, true);
		}
		return $response;
	}

	/**
	 * POST wreapper for oAuthRequest.
	 *
	 * @return mixed
	 */
	function post($url, $parameters = array() , $multi = false) {
		$response = $this->oAuthRequest($url, 'POST', $parameters , $multi );
		if ($this->format === 'json' && $this->decode_json) {
			return json_decode($response, true);
		}
		return $response;
	}

	/**
	 * DELTE wrapper for oAuthReqeust.
	 *
	 * @return mixed
	 */
	function delete($url, $parameters = array()) {
		$response = $this->oAuthRequest($url, 'DELETE', $parameters);
		if ($this->format === 'json' && $this->decode_json) {
			return json_decode($response, true);
		}
		return $response;
	}

	/**
	 * Format and sign an OAuth / API request
	 *
	 * @return string
	 */
	function oAuthRequest($url, $method, $parameters , $multi = false) {
        //注意这里原始方式是检查是否https开头，目前我们的api使用的http开头，暂时修改为检查http
		if (strrpos($url, 'http://') !== 0 && strrpos($url, 'http://') !== 0) {
			$url = "{$this->host}{$url}.{$this->format}";
		}

		switch ($method) {
		case 'GET':
			$url = $url . '?' . http_build_query($parameters);
			return $this->http($url, 'GET');
		default:
			if (!$multi && (is_array($parameters) || is_object($parameters)) ) {
				$parameters = http_build_query($parameters);
			}
			return $this->http($url, $method, $parameters);
		}
	}

	/**
	 * Make an HTTP request
	 *
	 * @return string API results
	 */
	function http($url, $method, $postfields = NULL) {
		$this->http_info = array();
		$ci = curl_init();
		/* Curl settings */
		curl_setopt($ci, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0);
		curl_setopt($ci, CURLOPT_USERAGENT, $this->useragent);
		curl_setopt($ci, CURLOPT_CONNECTTIMEOUT, $this->connecttimeout);
		curl_setopt($ci, CURLOPT_TIMEOUT, $this->timeout);
		curl_setopt($ci, CURLOPT_RETURNTRANSFER, TRUE);
		curl_setopt($ci, CURLOPT_ENCODING, "");
		curl_setopt($ci, CURLOPT_SSL_VERIFYPEER, $this->ssl_verifypeer);
		curl_setopt($ci, CURLOPT_HEADERFUNCTION, array($this, 'getHeader'));
		curl_setopt($ci, CURLOPT_HEADER, FALSE);

		switch ($method) {
		case 'POST':
			curl_setopt($ci, CURLOPT_POST, TRUE);
			if (!empty($postfields)) {
				curl_setopt($ci, CURLOPT_POSTFIELDS, $postfields);
				$this->postdata = $postfields;
			}
			break;
		case 'DELETE':
			curl_setopt($ci, CURLOPT_CUSTOMREQUEST, 'DELETE');
			if (!empty($postfields)) {
				$url = "{$url}?{$postfields}";
			}
		}

		$headers=array();
		if ( isset($this->access_token) && $this->access_token )
			$headers[] = "Authorization: OAuth2 ".$this->access_token;

		$headers[] = "API-RemoteIP: " . $_SERVER['REMOTE_ADDR'];
		curl_setopt($ci, CURLOPT_URL, $url );
		curl_setopt($ci, CURLOPT_HTTPHEADER, $headers );
		curl_setopt($ci, CURLINFO_HEADER_OUT, TRUE );

		$response = curl_exec($ci);
		$this->http_code = curl_getinfo($ci, CURLINFO_HTTP_CODE);
		$this->http_info = array_merge($this->http_info, curl_getinfo($ci));
		$this->url = $url;

		if ($this->debug) {
			echo "=====post data======\r\n";
			var_dump($url."--------".$postfields);

			echo '=====info====='."\r\n";
			print_r( curl_getinfo($ci) );
			
			echo '=====$response====='."\r\n";
			print_r( $response );
		}
		curl_close ($ci);
		return $response; 
	}

	/**
	 * Get the header info to store.
	 *
	 * @return int
	 */
	function getHeader($ch, $header) {
		$i = strpos($header, ':');
		if (!empty($i)) {
			$key = str_replace('-', '_', strtolower(substr($header, 0, $i)));
			$value = trim(substr($header, $i + 2));
			$this->http_header[$key] = $value;
		}
		return strlen($header);
	}
}

class JSEClient{
	/*
	 *认证对象类
	 */
	public $oauth;
	/**
	 * 构造函数
	 * 
	 * @access public
	 * @param mixed $akey 微博开放平台应用APP KEY
	 * @param mixed $skey 微博开放平台应用APP SECRET
	 * @param mixed $access_token OAuth认证返回的token
	 * @param mixed $refresh_token OAuth认证返回的token secret
	 * @return void
	 */	
	function __construct($akey, $akey, $access_token=null, $refresh_token=null){
		$this->oauth = new JSEOAuth($akey,$skey,$access_token,$refresh_token);
		if(null==$access_token){
			$tmp_acc = $this->oauth->getAccessToken();
			$this->oauth->setAccessToken($tmp_acc);
		}
	}
	/**
	 * 获取用户基本资料
	 *
	 * 
	 * <br />对应API：？
	 * 
	 * @access public
	 * @param int $uid 用户的ID
	 * @return array
	 */
	function user_info($uid){
		$params = array();
		$params['uid'] = $uid;
		return $this->oauth->get('http://api2.jiaoyu365.ne/statuses/public_timeline.json', $params);
	}

	/**
	 * 获取最新的公共微博消息
	 *
	 * 返回最新的20条公共微博。返回结果非完全实时，最长会缓存60秒
	 * <br />对应API：statuses/public_timeline
	 * 
	 * @access public
	 * @param int $count 每次返回的记录数。缺省值20，最大值200。可选。
	 * @param int $base_app 是否基于当前应用来获取数据。1为限制本应用微博，0为不做限制。默认为0。可选。
	 * @return array
	 */
	function public_timeline( $count = 20, $base_app = 0 )
	{
		$params = array();
		$params['count'] = intval($count);
		$params['base_app'] = intval($base_app);
		return $this->oauth->get('http://api2.jiaoyu365.net/statuses/public_timeline.json', $params);
	}
	// =========================================

	/**
	 * @ignore
	 */
	protected function request_with_pager( $url , $page = false , $count = false , $params = array() )
	{
		if( $page ) $params['page'] = $page;
		if( $count ) $params['count'] = $count;

		return $this->oauth->get($url , $params );
	}

	/**
	 * @ignore
	 */
	protected function request_with_uid( $url , $uid_or_name , $page = false , $count = false , $cursor = false , $post = false , $params = array())
	{
		if( $page ) $params['page'] = $page;
		if( $count ) $params['count'] = $count;
		if( $cursor )$params['cursor'] =  $cursor;

		if( $post ) $method = 'post';
		else $method = 'get';

		if ( $uid_or_name !== NULL ) {
			$this->id_format($uid_or_name);
			$params['id'] = $uid_or_name;
		}

		return $this->oauth->$method($url , $params );

	}

	protected function id_format(&$id) {
		if ( is_float($id) ) {
			$id = number_format($id, 0, '', '');
		}
	}
}