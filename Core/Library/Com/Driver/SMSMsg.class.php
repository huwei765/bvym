<?php
namespace Com\Driver;
// ��Ϣ����
class SMSMsg {

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
	}
}
