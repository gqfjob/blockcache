<?php
/**
 +------------------------------------------------------------------------------
 * 静态缓存类
 * 支持静态缓存规则定义
 +------------------------------------------------------------------------------
 * @category   
 * @package   
 * @author <gqfjob@gmail.com>
 +------------------------------------------------------------------------------
 */
include_once("config.php");

class HtmlCache
{
    private $cacheTime = null; // 缓存有效期（支持函数）
    private $cachePath = ''; //保存缓存的路径
    private $cacheFile = ''; //当前请求对应的缓存文件名
    private $file = ''; //当前缓存文件地址
    
    private $action = '';
    private $block = '';

    private $cfg = array();
    
    public function HtmlCache($cachecfg,$action,$block,$params){

        $this->action = $action;
        $this->block = $block;
        //读取配置文件，获取当前缓存板块对应的缓存时间参数
        $this->cfg = $cachecfg;
        $this->cacheTime = $this->cfg['rules'][$action][1];
         
        //生成缓存文件名
        $this->cachePath = $this->cfg['cachePath'];
        $this->cacheFile = $this->createFileName($params);
        $this->file = $this->cachePath.$this->cacheFile ;
    }

     /**
     +----------------------------------------------------------
     * 由当前url和配置规则生成静态缓存文件名
     +----------------------------------------------------------
     * @access private
     +----------------------------------------------------------
     * @return void
     +----------------------------------------------------------
     */
    private function createFileName($params) {
        //配置缓存规则  'getmetting'=>array('需要缓存的block','缓存时间','md5等文件名生成规则','生成name需要的参数数组');
        //生成 md5(getmetting_context_123).html
        $tmp=$this->action."_".$this->block;
        if(sizeof($params)>0){
            foreach ($params as $k => $v){
                if( in_array($k, $this->cfg['rules'][$this->action][3]) ){
                    $tmp = $tmp."_".$v;
                }
            }
        }
        //使用自定义的文件名生成函数
        $fun = $this->cfg['rules'][$this->action][2];
        if(function_exists($fun)){
            return  $fun($tmp).".html";
        }else{
            return  md5($tmp).".html";
        }
    }
    /**
     +----------------------------------------------------------
     * 读取静态缓存
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @return void
     +----------------------------------------------------------
     */
    public function readHTMLCache() {
        if($this->checkHTMLCache($this->cacheFile,$this->cacheTime)) { //静态页面有效
            if(is_file($this->file)){
                include($this->file);
            }else{
                echo "read file error";
                die();
            }
        }
        return ;
    }

    /**
     +----------------------------------------------------------
     * 写入静态缓存
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @param string $content 页面内容
     +----------------------------------------------------------
     * @return void
     +----------------------------------------------------------
     +----------------------------------------------------------
     */
    public function writeHTMLCache($content) {
        // 静态文件写入
        if(!is_dir(dirname($this->file)))
            mk_dir(dirname($this->file));
        if( false === file_put_contents( $this->file , $content ))
            echo ('write cache error : '.$this->file);
        return ;
    }
    /**
     +----------------------------------------------------------
     * 删除静态缓存
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @return boolean
     +----------------------------------------------------------
     +----------------------------------------------------------
     */
    public function delHTMLCache() {
        if(is_file($this->file)){

            if(unlink($this->file)){//del  success
                return true;
            }else{
                echo "del file file error";
                die();
            }
        }
        return false;
    }
    /**
     +----------------------------------------------------------
     * 检查静态HTML文件是否有效
     * 如果无效删除缓存
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @return boolen
     +----------------------------------------------------------
     */
    public   function checkHTMLCache() {
        if(!file_exists($this->file)){
            return false;
        }elseif ($this->cacheTime != -1 && time() > filemtime($this->file)+$this->cacheTime) {
            // 文件超过有效期
            $this->delHTMLCache();
            return false;
        }
        //静态文件有效
        return true;
    }
     
}
/**
 +----------------------------------------------------------
 * 检查静态HTML文件是否有效
 * 如果无效需要重新更新
 +----------------------------------------------------------
 * @access public
 +----------------------------------------------------------
 * @return boolen
 +----------------------------------------------------------
 */
function getdata($action, $block,$params){
    global $cachecfg;
    if (!ENABLED_HTML_CACHE)      //没有启用缓存
    {
        //包含所有需要从数据库取数的过程,取完后赋值给html变量，注意最终的赋值变量名称为$html
        include("actions/".$action."_".$block.".php");          
        //原始代码
        echo $html;
    }else{
        //启动了缓存 
        $cache = new HtmlCache($cachecfg,$action,$block,$params);
        if ($cache->checkHTMLCache()){          
            //如果文件已经被缓存
            //重定向到缓存文件,getCacheFile 方法根据url生成一个唯一的静态文件名
            $cache->readHTMLCache();
            return;
        }else{          
            //如果文件还没有被缓存，或者缓存已经过期，则执行实际代码并写入内容,注意最终的赋值变量名称为$html
            include("actions/".$action."_".$block.".php");        
            //$html变量包含了文件的内容，writeCache 方法将其写入到文件
            $cache->writeHTMLCache($html);
            //从读缓存
            $cache->readHTMLCache();
        }
    }
}
/**
 +----------------------------------------------------------
 * 检查静态HTML文件是否有效
 +----------------------------------------------------------
 * @access public
 +----------------------------------------------------------
 * @return boolen
 +----------------------------------------------------------
 */
function deldata($action, $block,$params) {
    global $cachecfg;
    $cache = new HtmlCache($cachecfg,$action,$block,$params);
    $cache->delHTMLCache();
}
