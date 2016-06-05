<?php
/**
 * 奥点云框架核心类返回HTTP响应
 *
 * @author phenixsoul(329580886@qq.com)
 * @link http://www.aodianyun.com
 * @copyright 2008-2015 aodiansoft
 */

class CHttpHeader
{

    public function __construct($type){
        $action = 'header' . $type;
        if(!method_exists($this, $action)){
            $this->header500();
            throw new CException(Base::init()->getMessage('base','Header Function {action} not find',array('{action}'=>$action)));
        }
        $this->$action();
    }

    public function header404(){
        header("HTTP/1.0 404 Not Found");
        die('404 Not Found');
    }

    public function header500(){
       header("HTTP/1.1 500 Internal Server Error");
    }

}