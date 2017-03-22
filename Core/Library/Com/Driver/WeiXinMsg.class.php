<?php
namespace Com\Driver;
// ��Ϣ����
class WeiXinMsg {

	private $config = array();

	public function __construct($config = array()){
		//��ȡ����
		$this->config   =   array_merge($this->config, $config);
	}

	/**
	 * ������Ϣ
	 * @param $params
	 */
	public function send($params){
		$temp_params = array(
			"type"=>1,//0:�û��Զ�����Ϣ 1:ģ����Ϣ
			"content"=>array(
				"touser"=>"18710085136",//�����΢��,����openid
				"template_id"=>$this->getMsgTemplateIdByTag($params["content"]["template"]),
				"data"=>array()
			)
		);
	}

	/**
	 * ��ȡģ��id
	 * @param $tag
	 * @return mixed
	 */
	private function getMsgTemplateIdByTag($tag){
		return C("WX_PUBLIC.template.".$tag);
	}
}
