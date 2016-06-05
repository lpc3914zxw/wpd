<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2015/4/10 0010
 * Time: 13:39
 */
//namespace dms;

class Pusher
{
    /**
     * @var phpMQTT
     */
    private $mqtt;
    /**
     * @var bool
     */
    private $connected;

    public function __construct($pubKey, $subKey)
    {
        $id              = 'php' . time() . rand(1, 50000);
        $this->mqtt      = new phpMQTT('mqtt.dms.aodianyun.com', 1883, $id);
        $this->connected = $this->mqtt->connect(true, null, $pubKey, $subKey);
    }

    /**
     * 返回服务是否连接成功
     * @return bool
     */
    public function has_connected()
    {
        return $this->connected;
    }

    /**
     * 发布数据
     *
     * @param     $topic 数据主题
     * @param     $data  要发送的数据(建议json)
     * @param int $qos mqtt消息level 参考文档:http://www.blogjava.net/yongboy/archive/2014/02/15/409893.html
     * @param int $retain 重复发送标记， 值为1时，每一个新订阅此主题的客户端都会收到这条消息
     */
    public function trigger($topic, $data,$qos=0,$retain=0)
    {
        return $this->mqtt->publish($topic, $data,$qos,$retain);
    }

}