<?php
namespace Com;
// 消息发送
class Msg {

    private $config = array();

    /**
     * sms 句柄
     * @var
     */
    private $SMSMsgHandler;

    /**
     * 微信消息句柄
     * @var
     */
    private $WeiXinMsgHandler;

    /**
     * 构造函数，设置消息驱动
     * @param array $driver
     * @param array $config
     */
    public function __construct($driver = array(),$config = array()){
        //获取配置
        $this->config   =   array_merge($this->config, $config);
        //设置发送消息驱动
        $this->setDriver($driver);
    }

    /**
     * 设置驱动
     * @param array $driver
     * @param array $config
     */
    private function setDriver($driver = array(), $config = array()){
        if(in_array("sms",$driver)){
            //设置sms消息驱动
            $class =  'Think\\Com\\Driver\\SMSMsg';
            $this->SMSMsgHandler = new $class();
        }
        if(in_array("weixin",$driver)){
            //设置weixin消息驱动
            $class =  'Think\\Com\\Driver\\WeiXinMsg';
            $this->WeiXinMsgHandler = new $class();
        }

        if(!$this->SMSMsgHandler && !$this->WeiXinMsgHandler){
            E("不存在上传驱动");
        }
    }

    /**
     * 发送消息
     * @param $params
     */
    public function sendMsg($params){
        if($this->SMSMsgHandler){
            $this->SMSMsgHandler->send($params);
        }
        if($this->WeiXinMsgHandler){
            $this->WeiXinMsgHandler->send($params);
        }
    }
}
