<?php
/**
 * 奥点云框架核心类控制器
 *
 * @author phenixsoul(329580886@qq.com)
 * @link http://www.aodianyun.com
 * @copyright 2008-2015 aodiansoft
 */

class CController{

	protected $base;
	protected $moduleConfig;
	protected $route;
	protected $runtime_begin = 0;
	protected $runtime_end = 0;
	protected $runtime = 0;
	protected $template = array();
	protected $tpl = null;

	public function __construct(array $moduleConfig = array(), array $route){
		$this->base = Base::init();
		$this->moduleConfig = $moduleConfig;
		$this->route = $route;
	}

	/**
     * [begin 页面开始]
     * @param  array  $options [选项]
     */
	protected function begin($options = array('pagecache'=>true,'template'=>array())){
		if(empty($this->moduleConfig['template'])){
			throw new CException(Base::init()->getMessage('base','Module {module} template config is null',array('{module}'=>$this->moduleConfig['moduleName'])));
		}
		//开启页面缓存
		if($options['pagecache']){
			$this->pagecache();
		}
		
		//使用模板引擎
		if($options['template']){
			$this->template = $this->moduleConfig['template'];
			$this->tpl = CTemplate::getInstance();
		}
	}

	/**
	 * [assign 模板变量赋值]
	 * @param  string $name  [模板变量名]
	 * @param  string $value [模板值]
	 */
	protected function assign($name,$value){
		$this->tpl->assign($name,$value);
	}
	
    /**
     * [tpl_path 自定义模板路径]
     * @param  string  $path [路径]
     */
	protected function tpl_path($path){
		$this->template['template_dir'] .= $path.'/';
		$this->template['cache_dir'] .= $path.'/';
	}

	/**
	 * [end 页面结束]
	 * @param  array  $options [选项]
	 */
	protected function end($options = array('runtime'=>true,'tplfile'=>null)){
		/* 设置模板配置 */
		$this->tpl->setOptions($this->template);
		
		/* 计算页面运行时间 */
		$this->runtime_end = microtime(true);
		$this->runtime = round(($this->runtime_end - $this->runtime_begin) * 1000, 1);
		
		/* 页面运行时间模板显示 */
		if($options['runtime']){
			$this->tpl->assign('runtime',$this->runtime);
		}
		
		/* 模板显示 */
		if($options['tplfile']){
			$this->tpl->display($options['tplfile']);
		}
	}
	
	/**
	 * [runtime 页面运行时间]
	 */
	protected function runtime(){
		return $this->runtime;
	}

	/**
	 * [pagecache 页面缓存]
	 */
	private function pagecache(){
		$data = pagecache::init()->start();
		if(!empty($data)){
			exit($data);
		}
		ob_start();
	}

	protected function showMsg($msg = "", $url = "", $target = "") {
		$script = "<html>\r\n";
		$script .= "<meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\" />\r\n";
		$script .= "<script type=\"text/javascript\">\r\n";
		$script .= "if('" . $msg . "' != ''){\r\n";
		$script .= "alert(\"" . $msg . "\");\r\n";
		$script .= "}\r\n";
		$script .= "if('" . $url . "' < 0){\r\n";
		$script .= "window.history.go('". $url ."');\r\n";
		$script .= "}else{\r\n";
		$script .= "if('" . $target . "' == 'parent'){\r\n";
		$script .= "window.top.location.href='" . $url . "';\r\n";
		$script .= "}else{\r\n";
		$script .= "location.href='" . $url . "';\r\n";
		$script .= "}\r\n";
		$script .= "}\r\n";
		$script .= "</script>\r\n";
		$script .= "</html>";
		exit ($script);
	}

}