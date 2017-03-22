<?php
namespace Com\Driver;
// 消息发送
class WeiXinMsg {

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
		$temp_params = array(
			"type"=>1,//0:用户自定义消息 1:模板消息
			"content"=>array(
				"touser"=>"18710085136",//如果是微信,就是openid
				"template_id"=>$this->getMsgTemplateIdByTag($params["content"]["template"]),
				"data"=>array()
			)
		);
	}

	/**
	 * 读取模板id
	 * @param $tag
	 * @return mixed
	 */
	private function getMsgTemplateIdByTag($tag){
		return C("WX_PUBLIC.template.".$tag);
	}
}
