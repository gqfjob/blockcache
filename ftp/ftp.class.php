<?php
/**
 * CodeIgniter的FTP类
 * FTP基本操作：
 * 1) 登陆;           connect
 * 2) 当前目录文件列表;  fileli
 * 3) 目录改变;         changedir
 * 4) 重命名/移动;       rename
 * 5) 创建文件夹;     
 * 6) 删除;               delete_dir/delete_file
 * 7) 上传;               upload
 * 8) 下载                download
 * 9) 目录上传      mirror
 *
 */
class Ftp {

	private $hostname   = '';
	private $username   = '';
	private $password   = '';
	private $port       = 21;
	private $passive    = TRUE;
	private $debug      = TRUE;
	private $conn_id    = FALSE;

	/**
	 * 构造函数
	 *
	 * @param   array   配置数组 : $config = array('hostname'=>'','username'=>'','password'=>'','port'=>''...);
	 */
	public function __construct($config = array()) {
		if(count($config) > 0) {
			$this->_init($config);
		}
	}

	/**
	 * FTP连接
	 *
	 * @access  public
	 * @param   array   配置数组
	 * @return  boolean
	 */
	public function connect($config = array()) {
		if(count($config) > 0) {
			$this->_init($config);
		}

		if(FALSE === ($this->conn_id = @ftp_connect($this->hostname,$this->port))) {
			if($this->debug === TRUE) {
				$this->_error("ftp_unable_to_connect");
			}
			return FALSE;
		}

		if( ! $this->_login()) {
			if($this->debug === TRUE) {
				$this->_error("ftp_unable_to_login");
			}
			return FALSE;
		}

		if($this->passive === TRUE) {
			ftp_pasv($this->conn_id, TRUE);
		}

		return TRUE;
	}


	/**
	 * 目录改变
	 *
	 * @access  public
	 * @param   string  目录标识(ftp)
	 * @param   boolean
	 * @return  boolean
	 */
	public function changedir($path = '', $supress_debug = FALSE) {
		if($path == '' OR ! $this->_is_conn()) {
			return FALSE;
		}

		$result = @ftp_chdir($this->conn_id, $path);

		if($result === FALSE) {
			if($this->debug === TRUE AND $supress_debug == FALSE) {
				$this->_error("ftp_unable_to_chgdir:dir[".$path."]");
			}
			return FALSE;
		}

		return TRUE;
	}

	/**
	 * 目录生成
	 *
	 * @access  public
	 * @param   string  目录标识(ftp)
	 * @param   int     文件权限列表  
	 * @return  boolean
	 */
	public function mkdir($path = '', $permissions = NULL) {
		if($path == '' OR ! $this->_is_conn()) {
			return FALSE;
		}

		$result = @ftp_mkdir($this->conn_id, $path);

		if($result === FALSE) {
			if($this->debug === TRUE) {
				$this->_error("ftp_unable_to_mkdir:dir[".$path."]");
			}
			return FALSE;
		}

		if( ! is_null($permissions)) {
			$this->chmod($path,(int)$permissions);
		}

		return TRUE;
	}

	/**
	 * 上传
	 *
	 * @access  public
	 * @param   string  本地目录标识
	 * @param   string  远程目录标识(ftp)
	 * @param   string  上传模式 auto || ascii
	 * @param   int     上传后的文件权限列表  
	 * @return  boolean
	 */
	public function upload($localpath, $remotepath, $mode = 'auto', $permissions = NULL) {
		if( ! $this->_is_conn()) {
			return FALSE;
		}

		if( ! file_exists($localpath)) {
			if($this->debug === TRUE) {
				$this->_error("ftp_no_source_file:".$localpath);
			}
			return FALSE;
		}

		if($mode == 'auto') {
			$ext = $this->_getext($localpath);
			$mode = $this->_settype($ext);
		}

		$mode = ($mode == 'ascii') ? FTP_ASCII : FTP_BINARY;

		$result = @ftp_put($this->conn_id, $remotepath, $localpath, $mode);

		if($result === FALSE) {
			if($this->debug === TRUE) {
				$this->_error("ftp_unable_to_upload:localpath[".$localpath."]/remotepath[".$remotepath."]");
			}
			return FALSE;
		}

		if( ! is_null($permissions)) {
			$this->chmod($remotepath,(int)$permissions);
		}

		return TRUE;
	}

	/**
	 * 下载
	 *
	 * @access  public
	 * @param   string  远程目录标识(ftp)
	 * @param   string  本地目录标识
	 * @param   string  下载模式 auto || ascii  
	 * @return  boolean
	 */
	public function download($remotepath, $localpath, $mode = 'auto') {
		if( ! $this->_is_conn()) {
			return FALSE;
		}

		if($mode == 'auto') {
			$ext = $this->_getext($remotepath);
			$mode = $this->_settype($ext);
		}

		$mode = ($mode == 'ascii') ? FTP_ASCII : FTP_BINARY;

		$result = @ftp_get($this->conn_id, $localpath, $remotepath, $mode);

		if($result === FALSE) {
			if($this->debug === TRUE) {
				$this->_error("ftp_unable_to_download:localpath[".$localpath."]-remotepath[".$remotepath."]");
			}
			return FALSE;
		}

		return TRUE;
	}

	/**
	 * 重命名/移动
	 *
	 * @access  public
	 * @param   string  远程目录标识(ftp)
	 * @param   string  新目录标识
	 * @param   boolean 判断是重命名(FALSE)还是移动(TRUE) 
	 * @return  boolean
	 */
	public function rename($oldname, $newname, $move = FALSE) {
		if( ! $this->_is_conn()) {
			return FALSE;
		}

		$result = @ftp_rename($this->conn_id, $oldname, $newname);

		if($result === FALSE) {
			if($this->debug === TRUE) {
				$msg = ($move == FALSE) ? "ftp_unable_to_rename" : "ftp_unable_to_move";
				$this->_error($msg);
			}
			return FALSE;
		}

		return TRUE;
	}

	/**
	 * 删除文件
	 *
	 * @access  public
	 * @param   string  文件标识(ftp)
	 * @return  boolean
	 */
	public function delete_file($file) {
		if( ! $this->_isconn()) {
			return FALSE;
		}

		$result = @ftp_delete($this->conn_id, $file);

		if($result === FALSE) {
			if($this->debug === TRUE) {
				$this->_error("ftp_unable_to_delete_file:file[".$file."]");
			}
			return FALSE;
		}

		return TRUE;
	}

	/**
	 * 删除文件夹
	 *
	 * @access  public
	 * @param   string  目录标识(ftp)
	 * @return  boolean
	 */
	public function delete_dir($path) {
		if( ! $this->_is_conn()) {
			return FALSE;
		}

		//对目录宏的'/'字符添加反斜杠'\'
		$path = preg_replace("/(.+?)\/*$/", "\\1/", $path);

		//获取目录文件列表
		$filelist = $this->filelist($path);

		if($filelist !== FALSE AND count($filelist) > 0) {
			foreach($filelist as $item) {
				//如果我们无法删除,那么就可能是一个文件夹
				//所以我们递归调用delete_dir()
				if( ! @delete_file($item)) {
					$this->delete_dir($item);
				}
			}
		}

		//删除文件夹(空文件夹)
		$result = @ftp_rmdir($this->conn_id, $path);

		if($result === FALSE) {
			if($this->debug === TRUE) {
				$this->_error("ftp_unable_to_delete_dir:dir[".$path."]");
			}
			return FALSE;
		}

		return TRUE;
	}

	/**
	 * 修改文件权限
	 *
	 * @access  public
	 * @param   string  目录标识(ftp)
	 * @return  boolean
	 */
	public function chmod($path, $perm) {
		if( ! $this->_is_conn()) {
			return FALSE;
		}

		//只有在PHP5中才定义了修改权限的函数(ftp)
		if( ! function_exists('ftp_chmod')) {
			if($this->debug === TRUE) {
				$this->_error("ftp_unable_to_chmod(function)");
			}
			return FALSE;
		}

		$result = @ftp_chmod($this->conn_id, $perm, $path);

		if($result === FALSE) {
			if($this->debug === TRUE) {
				$this->_error("ftp_unable_to_chmod:path[".$path."]-chmod[".$perm."]");
			}
			return FALSE;
		}
		return TRUE;
	}

	/**
	 * 获取目录文件列表
	 *
	 * @access  public
	 * @param   string  目录标识(ftp)
	 * @return  array
	 */
	public function filelist($path = '.',$flag="") {
		if( ! $this->_is_conn()) {
			return FALSE;
		}
        if("" == $flag){
            return ftp_nlist($this->conn_id, $path);
        }else{
        	$res = ftp_rawlist($this->conn_id, $path);
        	$num = count($res);
        	if($num){
        		for($i = 0; $i < $num; $i++){
        			$res[$i] = substr($res[$i],55,strlen($res[$i]));
        		}
        	}
        	return $res;
        }
	}
	/**
	 * 下载整个目录
	 *
	 * @access  public
	 * @param   string  目录标识(ftp)
	 * @return  array
	 */
	public function dldir($path = '.'){
		if( ! $this->_is_conn()) {
			return FALSE;
		}
		//遍历当前目录
		 
	}

	function download_file($sava_path)
	{
		$fc = $this->conn_id;
		$fn=$this->filelist($path = '.',$flag="raw");//列出该目录的文件名（含子目录），存储在数组中 
		$size=sizeof($fn);

		echo $sava_path."<br/>";
		//进入$sava_path
		if(!file_exists($sava_path))mkdir($sava_path, 0700);
		chdir($sava_path);
		for($i=0;$i<$size;$i++)
		{
			
			if(preg_match('/^[a-zA-Z0-9_]+/',$fn[$i])) //提取是文件和目录，剔除.,..这两个目录 
			{
				if(preg_match('/^[a-zA-Z0-9_]+([a-zA-Z0-9-]*.*)(\.+)/',$fn[$i])) //是文件时直接下载 
				{
					if(ftp_get($fc,$sava_path."/".$fn[$i],$fn[$i],FTP_BINARY))
					{
						echo "<br/>下载".getcwd()."/".$fn[$i]."成功<br/>"; 
					}
					else
					{
						echo "<br/>下载".getcwd()."/".$fn[$i]."失败<br/>"; 
					}
				}//文件下载结束 
				else //"是目录，进入目录，再读取文件"; 
				{
					if(!file_exists($fn[$i]))mkdir($fn[$i], 0700);//本地机器上该目录不存在就创建一个 
					ftp_chdir($fc,$fn[$i]);
					$length;
					$sava_path = $sava_path."/".$fn[$i];
					echo "当前的目录是：".getcwd()."<br/>";// 更好的看清当前目录 
					$this->download_file($sava_path);//递归进入该目录下载文件 
					chdir($tmp);
				}
			}//提取文件，目录结束 
			
		}//for循环结束 
		ftp_cdup($fc);//ftp服务器返回上层目录 
		chdir($sava_path);
	}
	// ------------------------------------------------------------------------

	/**
	 * Read a directory and recreate it remotely
	 *
	 * This function recursively reads a folder and everything it contains (including
	 * sub-folders) and creates a mirror via FTP based on it.  Whatever the directory structure
	 * of the original file path will be recreated on the server.
	 *
	 * @access  public
	 * @param   string  path to source with trailing slash
	 * @param   string  path to destination - include the base folder with trailing slash
	 * @return  bool
	 */
	function mirror($locpath, $rempath)
	{
		if ( ! $this->_is_conn())
		{
			return FALSE;
		}

		// Open the local file path
		if ($fp = @opendir($locpath))
		{
			// Attempt to open the remote file path.
			if ( ! $this->changedir($rempath, TRUE))
			{
				// If it doesn't exist we'll attempt to create the direcotory
				if ( ! $this->mkdir($rempath) OR ! $this->changedir($rempath))
				{
					return FALSE;
				}
			}

			// Recursively read the local directory
			while (FALSE !== ($file = readdir($fp)))
			{
				if (@is_dir($locpath.$file) && substr($file, 0, 1) != '.')
				{
					$this->mirror($locpath.$file."/", $rempath.$file."/");
				}
				elseif (substr($file, 0, 1) != ".")
				{
					// Get the file extension so we can se the upload type
					$ext = $this->_getext($file);
					$mode = $this->_settype($ext);

					$this->upload($locpath.$file, $rempath.$file, $mode);
				}
			}
			return TRUE;
		}

		return FALSE;
	}




	/**
	 * 关闭FTP
	 *
	 * @access  public
	 * @return  boolean
	 */
	public function close() {
		if( ! $this->_is_conn()) {
			return FALSE;
		}

		return @ftp_close($this->conn_id);
	}

	/**
	 * FTP成员变量初始化
	 *
	 * @access  private
	 * @param   array   配置数组    
	 * @return  void
	 */
	private function _init($config = array()) {
		foreach($config as $key => $val) {
			if(isset($this->$key)) {
				$this->$key = $val;
			}
		}

		//特殊字符过滤
		$this->hostname = preg_replace('|.+?://|','',$this->hostname);
	}

	/**
	 * FTP登陆
	 *
	 * @access  private
	 * @return  boolean
	 */
	private function _login() {
		return @ftp_login($this->conn_id, $this->username, $this->password);
	}

	/**
	 * 判断con_id
	 *
	 * @access  private
	 * @return  boolean
	 */
	private function _is_conn() {
		if( ! is_resource($this->conn_id)) {
			if($this->debug === TRUE) {
				$this->_error("ftp_no_connection");
			}
			return FALSE;
		}
		return TRUE;
	}

	/**
	 * 从文件名中获取后缀扩展
	 *
	 * @access  private
	 * @param   string  目录标识
	 * @return  string
	 */
	private function _getext($filename) {
		if(FALSE === strpos($filename, '.')) {
			return 'txt';
		}

		$extarr = explode('.', $filename);
		return end($extarr);
	}

	/**
	 * 从后缀扩展定义FTP传输模式  ascii 或 binary
	 *
	 * @access  private
	 * @param   string  后缀扩展
	 * @return  string
	 */
	private function _settype($ext) {
		$text_type = array (
                            'txt',
                            'text',
                            'php',
                            'phps',
                            'php4',
                            'js',
                            'css',
                            'htm',
                            'html',
                            'phtml',
                            'shtml',
                            'log',
                            'xml'
                            );

                            return (in_array($ext, $text_type)) ? 'ascii' : 'binary';
	}

	/**
	 * 错误日志记录
	 *
	 * @access  prvate
	 * @return  boolean
	 */
	private function _error($msg) {
		return @file_put_contents('ftp_err.log', "date[".date("Y-m-d H:i:s")."]-hostname[".$this->hostname."]-username[".$this->username."]-password[".$this->password."]-msg[".$msg."]\n", FILE_APPEND);
	}
}
?>