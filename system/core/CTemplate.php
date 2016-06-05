<?php

/**
 * 模板类
 *
 * @package template class
 */

class CTemplate
{
	const DIR_SEP = DIRECTORY_SEPARATOR;
	const FILE_PERMS = 0644;
	const DIR_PERMS = 0771;
	const NEST = 6;

	/**
	 * 模板实例
	 *
	 * @staticvar
	 * @var object template
	 */
	protected static $_instance = false;

	/**
	 * 模板参数信息
	 *
	 * @var array
	 */
	protected $_options = array();

	/**
	 * 模板变量
	 * 
	 * @var object
	 */
	protected $vars;

	/**
	 * 模板变量正则
	 * 
	 * @var object
	 */
	protected $var_regexp = "\@?\\\$[a-zA-Z_][\\\$\w]*(?:\[[\w\-\.\"\'\[\]\$]+\])*";

	/**
	 * 模板标签正则
	 * 
	 * @var object
	 */
	protected $vtag_regexp = "\<\?php echo (\@?\\\$[a-zA-Z_][\\\$\w]*(?:\[[\w\-\.\"\'\[\]\$]+\])*)\;\?\>";

	/**
	 * 模板常量正则
	 * 
	 * @var object
	 */
	protected $const_regexp = "([A-Z0-9_]+)";

	/**
	 * 单件模式调用方法
	 *
	 * @static
	 * @return object template
	 */
	public static function getInstance() {
		if (!self :: $_instance instanceof self) {
			self :: $_instance = new self();
		}
		return self :: $_instance;
	}

	/**
	 * 构造方法
	 *
	 * @return void
	 */
	protected function __construct() {
		$this->_options = array (
			'template_dir' => 'templates', //模板文件所在目录
			'cache_dir' => 'templates' . self :: DIR_SEP . 'cache', //缓存文件存放目录
			'cache_lifetime' => 0, //缓存生命周期(分钟)，为 0 表示永久
			'debug' => false,  //每次自动删除缓存文件,true |　false
			'show_exectime' => false, //显示页面的执行时间,true |　false
		);
	}

	/**
	 * 设定模板参数信息
	 *
	 * @param  array $options 参数数组
	 * @return void
	 */
	public function setOptions(array $options) {
		foreach ($options as $name => $value) {
			$this->set($name, $value);
		}
	}

	/**
	 * 设定模板参数
	 *
	 * @param  string $name  参数名称
	 * @param  mixed  $value 参数值
	 * @return void
	 */
	protected function set($name, $value) {
		switch ($name) {
			case 'template_dir' :
				try {
					$value = $this->_trimpath($value);
					if (substr($value,0,7) != 'http://') {
						if (!file_exists($value)) {
							$this->_throwException("Not find the specified template directory\"$value\"");
						}
					}
				} catch(Exception $e) {
					die($e->getMessage());
				}
				$this->_options['template_dir'] = $value;
				break;
			case 'cache_dir' :
				try {
					$value = $this->_trimpath($value);
					if (!file_exists($value)) {
						if(!$this->_makepath($value)) {
							$this->_throwException("Not find the specified cache directory\"$value\"");
						}
					}
				} catch(Exception $e) {
					die($e->getMessage());
				}
				$this->_options['cache_dir'] = $value;
				break;
			case 'cache_lifetime' :
				$this->_options['cache_lifetime'] = (float) $value;
				break;
			case 'debug' :
				$this->_options['debug'] = (boolean) $value;
				break;
			case 'show_exectime' :
				$this->_options['show_exectime'] = (boolean) $value;
				break;
			default :
				die("Unknown template configuration options\"$name\"");
		}
	}

	/**
	 * 获取模板文件
	 *
	 * @param  string $file 模板文件名称
	 * @return string
	 */
	public function getfile($file) {
		$cachefile = $this->_getCacheFile($file);
		if(!file_exists($cachefile)) {
			$this->compile($file);
		}
		$expireTime = filemtime($cachefile);
		if($this->_options['debug']) {
			$this->compile($file);
		}
		elseif($this->_options['cache_lifetime'] > 0 && (time() - $expireTime > $this->_options['cache_lifetime'])) {
			$this->compile($file);
		}
		return $cachefile;
	}

	/**
	 * 显示模板文件
	 *
	 * @param  string $file 模板文件名称
	 * @return string
	 */
	public function display($file) {
		if($this->vars) {
			extract($this->vars, EXTR_SKIP);
		}
		include (self :: getInstance()->getfile($file));
	}

	/**
	 * 模板文件赋值
	 *
	 * @param string $k 模板中变量
	 * @param string $v 变量
	 * @return string
	 */
	function assign($k, $v) {
		$this->vars[$k] = $v;
	}

	/**
	 * 直接加载模板文件
	 *
	 * @param  string $file 模板文件名
	 * @return string
	 */
	function loadfile($file){
		if(!empty($file)){
			return $this->getfile($file);
		}
	}

	/**
	 * 编译模板文件
	 *
	 * @param  string $file 模板文件名称
	 * @return void
	 */
	public function compile($file) {
		try {
			$tplfile = $this->_getTplFile($file);
			if (substr($tplfile,0,7) != 'http://') {
				if (!is_readable($tplfile)) {
					$this->_throwException("Template file '\"$tplfile\"' Not found or unable to open");
				}
			}
		} catch(Exception $e) {
			die($e->getMessage());
		}
		$template = $this->safe_file_get_contents($tplfile);

		//解析模板标签
		$template = $this->parse($template);
		
		$template = "<?php if (!class_exists('CTemplate')) die('Access Denied');?>\r\n$template";
		
		//添加compile时间
		$strpos = stripos($template,'<head>');
		if($strpos > 0) {
			$template = substr($template,0,$strpos)."\r\n<!--template compile at ".date('Y-m-d H:i:s')."-->\r\n\r\n".substr($template,$strpos);
		}
		
		//添加页面exec时间
		if($this->_options['show_exectime']) {
			$strpos = stripos($template,'</body>');
			if($strpos > 0) {
				$template = substr($template,0,$strpos)."<div class=\"runtime\">页面执行时间: ".round((microtime(true) - $GLOBALS['__PAGE_EXEC_TIME__']) * 1000, 1)." 毫秒</div>\r\n".substr($template,$strpos);
			}
			unset($GLOBALS['__PAGE_EXEC_TIME__']);
		}
		
		//写入缓存文件
		try {
			$cachefile = $this->_getCacheFile($file);
			$makepath = $this->_makepath($cachefile);
			if ($makepath !== true) {
				$this->_throwException("Unable to create cache directory '\"$makepath\"'");
			}
		} catch(Exception $e) {
			die($e->getMessage());
		}
		$this->safe_file_put_contents($cachefile, $template);
	}
	
	/**
	 * 解析模板标签
	 *
	 * @param  string  $template 模板文件名称
	 * @return void
	 */
	public function parse($template) {

		//过滤 <!--{}-->
		$template = preg_replace("/\<\!\-\-\{(.+?)\}\-\-\>/s", "{\\1}", $template);

		//过滤 <{}>
		$template = preg_replace("/\<\{(.+?)\}\>/s", "{\\1}", $template);

		//删除 PHP 代码断间多余的空格及换行
		$template = preg_replace("/ \?\>[\n\r]*\<\? /s", " ", $template);

		//替换 PHP 换行符{LF}
		$template = str_replace("{LF}", "<?php echo \"\\n\";?>", $template);

		//替换带{}的变量
		/*============
		$template = preg_replace("/\{($this->var_regexp)\}/", "<?php echo \\1;?>", $template);
		============*/

		//替换{}标签
		$template = preg_replace("/\{($this->var_regexp)\}/", "\\1", $template);

		//替换带{}的常量
		$template = preg_replace("/\{($this->const_regexp)\}/", "<?php echo \\1;?>", $template);

		//替换重复的<?php echo
		$template = preg_replace("/(?<!\<\?php echo |\\\\)$this->var_regexp/", "<?php echo \\0;?>", $template);

		//替换php标签
		$template = preg_replace("/\{php\s+(.*?)\}/ies", "\$this->stripvTag('<?php \\1;?>')", $template);

		//替换eval标签
		$template = preg_replace("/\{eval\s+(.*?)\}/ies", "\$this->stripvTag('<?php \\1?>')", $template);

		//替换echo标签
		$template = preg_replace("/\{echo\s+(.*?)\}/ies", "\$this->stripvTag('<?php echo \\1;?>')", $template);

		//替换for标签
		$template = preg_replace("/\{for\s+(.*?)\}/ies", "\$this->stripvTag('<?php for(\\1) {?>')", $template);

		//替换/for标签
		$template = preg_replace("/\{\/for\}/is", "<?php } ?>", $template);

		//替换if标签
		$template = preg_replace("/\{if\s+(.+?)\}/ies", "\$this->stripvTag('<?php if(\\1) { ?>')", $template);

		//替换elseif标签
		$template = preg_replace("/\{elseif\s+(.+?)\}/ies", "\$this->stripvTag('<?php } elseif (\\1) { ?>')", $template);

		//替换else标签
		$template = preg_replace("/\{else\}/is", "<?php } else { ?>", $template);

		//替换/if标签
		$template = preg_replace("/\{\/if\}/is", "<?php } ?>", $template);

		for($i = 0; $i < self::NEST; $i++) {
			//替换loop标签
			$template = preg_replace("/\{loop\s+$this->vtag_regexp\s+$this->vtag_regexp\s+$this->vtag_regexp\}(.+?)\{\/loop\}/ies", "\$this->loopSection('\\1', '\\2', '\\3', '\\4')", $template);
			$template = preg_replace("/\{loop\s+$this->vtag_regexp\s+$this->vtag_regexp\}(.+?)\{\/loop\}/ies", "\$this->loopSection('\\1', '', '\\2', '\\3')", $template);
			//替换foreach标签
			$template = preg_replace("/\{foreach\s+$this->vtag_regexp\s+$this->vtag_regexp\s+$this->vtag_regexp\}(.+?)\{\/foreach\}/ies", "\$this->loopSection('\\1', '\\2', '\\3', '\\4')", $template);
			$template = preg_replace("/\{foreach\s+$this->vtag_regexp\s+$this->vtag_regexp\}(.+?)\{\/foreach\}/ies", "\$this->loopSection('\\1', '', '\\2', '\\3')", $template);
			//替换while标签
			$template = preg_replace("/\{while\s+(.*?)\}/ies", "\$this->stripvTag('<?php while(\\1) {?>')", $template);
			//替换/while标签
			$template = preg_replace("/\{\/while\}/is", "<?php } ?>", $template);
			//替换do标签
			$template = preg_replace("/\{do\}/is", "<?php do{\\1 ?>", $template);
			//替换until标签
			$template = preg_replace("/\{until\s+(.*?)\}/ies", "\$this->stripvTag('<?php } while(\\1); ?>')", $template);
		}

		//替换include标签 {include "file_name"}
		$template = preg_replace("/\{include\s+\"(.*?)\"\}/is", "<?php include(CTemplate::getInstance()->getfile('\\1')); ?>", $template);

		//替换include标签 {include file="file_name"}
		$template = preg_replace("/\{include\s+?file=\"(.*?)\"\}/is", "<?php include(CTemplate::getInstance()->getfile('\\1')); ?>", $template);

		//替换include标签 {include_php "file_name"}
		$template = preg_replace("/\{include_php\s+\"(.*?)\"\}/is", "<?php include('\\1'); ?>", $template);

		//替换include标签 {include_php file="file_name"}
		$template = preg_replace("/\{include_php\s+?file=\"(.*?)\"\}/is", "<?php include('\\1'); ?>", $template);

		//替换script标签 {script src="file_name"}{/script}
		$template = preg_replace("/\{script[^\>^\}]*?src=\"(.+?)\".*?\}\s*\{\/script\}/ies", "\$this->stripscriptamp('\\1')", $template);

		//替换block标签 {block var}content{/block}
		$template = preg_replace("/\{block\s+([a-zA-Z0-9_]+)\}(.+?)\{\/block\}/ies", "\$this->stripblock('\\1', '\\2')", $template);

		//URL替换
		$template = preg_replace("/\"(http?[\w\.\/:]+\?[^\"]+?&[^\"]+?)\"/ie", "\$this->transamp('\\1')", $template);

		//将二维数组替换成带单引号的标准模式
		$template = preg_replace("/(\\\$[a-zA-Z_]\w+\[)([a-zA-Z_]\w+)\]/i", "\\1'\\2']", $template);

		return $template;
	}
	
	/**
	 * 正则表达式匹配替换
	 *
	 * @param string $s ：
	 * @return string
	 */
	function stripvTag($s) {
		return preg_replace("/$this->vtag_regexp/is", "\\1", str_replace("\\\"", '"', $s));
	}
	
	/**
	 * 替换模板中的loop循环
	 *
	 * @param string $arr ：
	 * @param string $k ：
	 * @param string $v ：
	 * @param string $statement ：
	 * @return string
	 */
	function loopSection($arr, $k, $v, $statement) {
		$arr = $this->stripvTag($arr);
		$k = $this->stripvTag($k);
		$v = $this->stripvTag($v);
		$statement = str_replace("\\\"", '"', $statement);
		return $k ? "<?php if(is_array($arr)) {foreach((array)$arr as $k=>$v) {?>$statement<?php }} ?>" : "<?php if(is_array($arr)) {foreach((array)$arr as $v) {?>$statement<?php }} ?>";
	}
	
	/**
	 * 替换模板中的script
	 *
	 * @param string $s ：
	 * @return string
	 */
	function stripscriptamp($s) {
		$s = str_replace('&amp;', '&', $s);
		return "<script src=\"$s\" type=\"text/javascript\"></script>";
	}
	
	/**
	 * 替换模板中的block
	 *
	 * @param string $var ：
	 * @param string $s ：
	 * @return string
	 */
	function stripblock($var, $s) {
		$constadd = '';
		$s = str_replace('\\"', '"', $s);
		$s = preg_replace("/<\?php echo \\\$(.+?)\;?>/", "{\$\\1}", $s);
		preg_match_all("/<\?php echo (.+?)\;?>/e", $s, $constary);
		$constary = array_unique($constary[1]);
		foreach ((array)$constary as $const) {
			$constadd .= '$__' . $const . ' = ' . $const . ';';
		}
		unset($constary);
		$s = preg_replace("/<\?php echo (.+?)\;?>/", "{\$__\\1}", $s);
		$s = str_replace('?>', "\n\$$var .= <<<EOF\n", $s);
		$s = str_replace('<?', "\nEOF;\n", $s);
		return "<?php\n$constadd\$$var = <<<EOF\n" . $s . "\nEOF;\n?>";
	}
	
	/**
	 * 替换模板中的URL
	 *
	 * @param string $s ：
	 * @return string
	 */
	function transamp($s) {
		$s = str_replace('&', '&amp;', $s);
		$s = str_replace('&amp;amp;', '&amp;', $s);
		$s = str_replace('\"', '"', $s);
		return $s;
	}

	/**
	 * 将路径修正为适合操作系统的形式
	 *
	 * @param  string $path 路径名称
	 * @return string
	 */
	protected function _trimpath($path) {
		$_tmp = parse_url($path);
		$_tmp['path'] = str_replace(array ('/','\\','//','\\\\'), '/', $_tmp['path']);
		$_dir = isset($_tmp['scheme']) ? $_tmp['scheme'] : '';
		if(!empty($_tmp['scheme']) && !empty($_tmp['host'])) {
			$_dir .= '://'.$_tmp['host'];
		}
		if(self :: DIR_SEP == '\\' && empty($_tmp['host'])) {
			$_dir .= ':';
		}
		return $_dir.$_tmp['path'];
	}

	/**
	 * 获取模板文件名及路径
	 *
	 * @param  string $file 模板文件名称
	 * @return string
	 */
	protected function _getTplFile($file) {
		$_tmp = parse_url($this->_options['template_dir']);
		$_dir = isset($_tmp['scheme']) ? $_tmp['scheme'] : '';
		$_file = $file;
		if(!empty($_tmp['scheme']) && !empty($_tmp['host'])) {
			$fconut = substr_count($file,'../');
			$_file = str_replace('../','',$file);
			if($fconut > 0) {
				$_tmp['path'] = strrev($_tmp['path']);
				$_tmpDir = explode('/',$_tmp['path']);
				for($fk = 0; $fk <= $fconut; $fk++) {
					if($_tmpDir[$fk]) {
						unset($_tmpDir[$fk]);
					}
				}
				$_tmp['path'] = implode('/',$_tmpDir);
				$_tmp['path'] = strrev($_tmp['path']);
			}
			$_dir .= '://'.$_tmp['host'];
		}
		if(self :: DIR_SEP == '\\' && empty($_tmp['host'])) {
			$_dir .= ':';
		}
		return $this->_trimpath($_dir . $_tmp['path'] . self :: DIR_SEP . $_file);
	}

	/**
	 * 获取模板缓存文件名及路径
	 *
	 * @param  string $file 模板文件名称
	 * @return string
	 */
	protected function _getCacheFile($file) {
		$file_arr = pathinfo($file);
		$dirname = md5($file_arr['dirname']);
		$filename = $file_arr['filename'];
		$extension = isset($file_arr['extension']) ? $file_arr['extension'] : '';
		$file = $filename.'_'.$extension.'_'.$dirname.'.cache.php';
		return $this->_trimpath($this->_options['cache_dir'] . self :: DIR_SEP . $file);
	}

	/**
	 * 根据指定的路径创建不存在的文件夹
	 *
	 * @param  string  $path 路径/文件夹名称
	 * @return string
	 */
	protected function _makepath($path) {
		$tmp = dirname($this->_trimpath($path));
		if (!file_exists($tmp) && !@ mkdir($tmp, self :: DIR_PERMS)) {
			return $tmp;
		}
		return true;
	}
	
	/**
	* safe_file_put_contents() 一次性完成打开文件，写入内容，关闭文件三项工作，并且确保写入时不会造成并发冲突
	*
	* @param string $filename
	* @param string $content
	*
	* @return boolean
	*/
	protected function safe_file_put_contents($filename, $content) {
		if(!empty($filename) && !empty($content)) {
			$_dirname = dirname($filename);
			$_tmp_file = tempnam($_dirname, 'wrt');
			if(!($fd = @fopen($_tmp_file, 'wb'))) {
				$_tmp_file = $_dirname . self :: DIR_SEP . uniqid('wrt');
				if(!($fd = @fopen($_tmp_file, 'wb'))) {
					return false;
				}
			}
			fwrite($fd, $content);
			fclose($fd);
			
			if(self :: DIR_SEP == '\\' || !@rename($_tmp_file, $filename)) {
				// On platforms and filesystems that cannot overwrite with rename() 
				// delete the file before renaming it -- because windows always suffers
				// this, it is short-circuited to avoid the initial rename() attempt
				@unlink($filename);
				@rename($_tmp_file, $filename);
			}
			@chmod($filename, self :: FILE_PERMS);
		}
		return true;
	}

	/**
	* safe_file_get_contents() 用共享锁模式打开文件并读取内容，可以避免在并发写入造成的读取不完整问题
	*
	* @param string $filename
	*
	* @return mixed
	*/
	protected function safe_file_get_contents($filename) {
		if(!empty($filename)) {
			if (substr($filename,0,7) != 'http://') {
				if (file_exists($filename) && ($fd = @fopen($filename, 'rb'))) {
					$data = '';
					while (!feof($fd)) {
						$data .= fread($fd, 8192);
					}
					fclose($fd);
					return $data;
				}
			} else {
				return $this->uopen($filename);
			}
		}
		return false;
	}
	
	/**
	* uopen() 远程模板文件载入
	*
	* @param string $url
	*
	* @return mixed
	*/
	protected function uopen($url) {
		$ch = curl_init();
		$timeout = 5;
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
		$contents = curl_exec($ch);
		$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		curl_close($ch);
		if($http_code != 200){
			die("HTTP Code \"$http_code\" Not find the specified URL file\"$url\"");
		}
		return $contents;
	}

	/**
	 * 抛出一个错误信息
	 *
	 * @param string $message
	 * @return void
	 */
	protected function _throwException($message) {
		throw new Exception($message);
	}
}


/**
 * template 直接载入模板文件函数
 * 
 * @package function
 * @return string
 * 
 */
function template($file,$template) {
	if(!empty($file) && is_object($template)) {
		return $template->loadfile($file);
	}
}