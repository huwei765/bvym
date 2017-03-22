<?php
namespace Com\Driver;
// 消息发送
class SMSMsg {

	private $config = array();

	public function __construct($config = array()){
		//获取配置
		$this->config   =   array_merge($this->config, $config);
	}

	/**
	 * 发送消息
	 * @param $params
	 */
	public function send($params){
	}
}
