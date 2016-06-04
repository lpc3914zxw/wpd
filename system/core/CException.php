<?php
/**
 * 奥点云框架核心类自定义错误
 *
 * @author phenixsoul(329580886@qq.com)
 * @link http://www.aodianyun.com
 * @copyright 2008-2015 aodiansoft
 */

class CException extends Exception
{
    public function __construct($message, $code=NULL)
    {
        parent::__construct($message, $code);
    }
   
    public static function getStaticException($exception)
    {
        echo $exception->message.'<br>';
        echo $exception->code.'<br>';
        echo $exception->file.'<br>';
        echo $exception->line.'<br>';
        exit;
    }
}