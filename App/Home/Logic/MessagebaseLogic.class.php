<?php

/**
 * 消息中心
 */

namespace Home\Logic;

class MessagebaseLogic extends BaseLogic{

	private $code = array(
		"ht_new_customer"=>"wx_msg_data_tpl.ht_new_customer",//新增订单发给客户的消息模板
		"ht_new_agent"=>"wx_msg_data_tpl.ht_new_agent",//新增订单发给代理商的消息模板
		"sign_in_customer"=>"wx_msg_data_tpl.sign_in_customer",//签到时发给客户的消息模板
		"sign_in_agent"=>"wx_msg_data_tpl.sign_in_agent",
		"pay_customer"=>"wx_msg_data_tpl.pay_customer",
		"pay_agent"=>"wx_msg_data_tpl.pay_agent",
		"pay_over_customer"=>"wx_msg_data_tpl.pay_over_customer",
		"pay_over_agent"=>"wx_msg_data_tpl.pay_over_agent",
		"opr_new_customer"=>"wx_msg_data_tpl.opr_new_customer",
		"opr_new_agent"=>"wx_msg_data_tpl.opr_new_agent",
		"customer_new_customer"=>"wx_msg_data_tpl.customer_new_customer",//新增客户时发给客户自己的消息
		"customer_new_agent"=>"wx_msg_data_tpl.customer_new_agent",//新增客户时发给代理商的消息
		"cust_new_agent"=>"wx_msg_data_tpl.cust_new_agent"//新增机构时发给机构的消息
	);

	/**
	 * 新增机构时发给机构的消息
	 * @param $param
	 * @return array
	 */
	public function sendMsgForAgentNewByAgent($param){
		//参数检测
		if(empty($param) || !isset($param["agent_openid"]) || !isset($param["agent_name"]) || !isset($param["agent_child"]) || !isset($param["child_level"])){
			return $this->callback(false,"sendMsgForAgentNewByAgent param valid");
		}
		//从配置文件中读取消息模板
		$msg_tpl = $this->getMsgTpl($this->code["customer_new_agent"]);
		if(empty($msg_tpl)){
			return $this->callback(false,"tpl not found");
		}
		//填充数据模板
		$msg_tpl["touser"] = $param["agent_openid"];
		$msg_tpl["data"]["keyword1"]["value"] = sprintf($msg_tpl["data"]["keyword1"]["value"],$param["agent_name"]);
		//发送模板消息
		D("wechat","Logic")->sendTemplateMessage($msg_tpl);
		return $this->callback(true);
	}

	/**
	 * 新增客户时发给客户自己的消息
	 * @param $param
	 * @return array
	 */
	public function sendMsgForCustomerNewByCustomer($param){
		if(empty($param) || !isset($param["customer_id"]) || !is_numeric($param["customer_id"]) || intval($param["customer_id"]) <= 0 || !isset($param["customer_openid"]) || !isset($param["customer_name"])){
			return $this->callback(false,"sendMsgForSignInByCustomer param valid");
		}
		//从配置文件中读取消息模板
		$msg_tpl = $this->getMsgTpl($this->code["customer_new_customer"]);
		if(empty($msg_tpl)){
			return $this->callback(false,"tpl not found");
		}
		//填充数据模板
		$msg_tpl["touser"] = $param["customer_openid"];
		$msg_tpl["data"]["keyword1"]["value"] = sprintf($msg_tpl["data"]["keyword1"]["value"],$param["customer_name"]);
		//发送模板消息
		D("wechat","Logic")->sendTemplateMessage($msg_tpl);
		return $this->callback(true);
	}

	/**
	 * 新增客户时发给代理商的消息
	 * @param $param
	 * @return array
	 */
	public function sendMsgForCustomerNewByAgent($param){
		//参数检测
		if(empty($param) || !isset($param["customer_name"]) || !isset($param["agent_openid"]) || !isset($param["agent_name"]) || !isset($param["customer_from"]) || !isset($param["customer_level"])){
			return $this->callback(false,"sendMsgForSignInByCustomer param valid");
		}
		//从配置文件中读取消息模板
		$msg_tpl = $this->getMsgTpl($this->code["customer_new_agent"]);
		if(empty($msg_tpl)){
			return $this->callback(false,"tpl not found");
		}
		//填充数据模板
		$msg_tpl["touser"] = $param["agent_openid"];
		$msg_tpl["data"]["keyword1"]["value"] = sprintf($msg_tpl["data"]["keyword1"]["value"],$param["agent_name"]);
		//发送模板消息
		D("wechat","Logic")->sendTemplateMessage($msg_tpl);
		return $this->callback(true);
	}

	/**
	 * 签到时发给客户的消息
	 * @param $param
	 * @return array
	 */
	public function sendMsgForSignInByCustomer($param){
		if(empty($param) || !isset($param["customer_id"]) || !is_numeric($param["customer_id"]) || intval($param["customer_id"]) <= 0 || !isset($param["customer_openid"]) || !isset($param["customer_name"])){
			return $this->callback(false,"sendMsgForSignInByCustomer param valid");
		}
		//从配置文件中读取消息模板
		$msg_tpl = $this->getMsgTpl($this->code["sign_in_customer"]);
		if(empty($msg_tpl)){
			return $this->callback(false,"tpl not found");
		}
		//填充数据模板
		$msg_tpl["touser"] = $param["customer_openid"];
		$msg_tpl["data"]["keyword1"]["value"] = sprintf($msg_tpl["data"]["keyword1"]["value"],$param["customer_name"]);
		//发送模板消息
		D("wechat","Logic")->sendTemplateMessage($msg_tpl);
		return $this->callback(true);

	}

	/**
	 * 签到时发给代理商的消息
	 * @param $param
	 * @return array
	 */
	public function sendMsgForSignInByAgent($param){
		//参数检测
		if(empty($param) || !isset($param["customer_name"]) || !isset($param["agent_openid"]) || !isset($param["agent_name"]) || !isset($param["customer_from"]) || !isset($param["customer_level"])){
			return $this->callback(false,"sendMsgForSignInByCustomer param valid");
		}
		//从配置文件中读取消息模板
		$msg_tpl = $this->getMsgTpl($this->code["sign_in_agent"]);
		if(empty($msg_tpl)){
			return $this->callback(false,"tpl not found");
		}
		//填充数据模板
		$msg_tpl["touser"] = $param["agent_openid"];
		$msg_tpl["data"]["keyword1"]["value"] = sprintf($msg_tpl["data"]["keyword1"]["value"],$param["agent_name"]);
		//发送模板消息
		D("wechat","Logic")->sendTemplateMessage($msg_tpl);
		return $this->callback(true);
	}

	/**
	 * 新增订单时发给客户的消息
	 * @param $param
	 * @return array
	 */
	public function sendMsgForHtNewByCustomer($param){
		if(empty($param) || !isset($param["customer_id"]) || !is_numeric($param["customer_id"]) || intval($param["customer_id"]) <= 0 || !isset($param["customer_openid"]) || !isset($param["customer_name"])){
			return $this->callback(false,"sendMsgForSignInByCustomer customer param valid");
		}
		if(!isset($param["order_bianhao"]) || !isset($param["order_summoney"]) || !isset($param["order_jine"]) || !isset($param["order_yishou"]) || !isset($param["order_weishou"]) || !isset($param["order_addtime"])){
			return $this->callback(false,"sendMsgForSignInByCustomer order param valid");
		}
		//从配置文件中读取消息模板
		$msg_tpl = $this->getMsgTpl($this->code["ht_new_customer"]);
		if(empty($msg_tpl)){
			return $this->callback(false,"tpl not found");
		}
		//填充数据模板
		$msg_tpl["touser"] = $param["customer_openid"];
		$msg_tpl["data"]["keyword1"]["value"] = sprintf($msg_tpl["data"]["keyword1"]["value"],$param["customer_name"]);
		//发送模板消息
		D("wechat","Logic")->sendTemplateMessage($msg_tpl);
		return $this->callback(true);
	}

	/**
	 * 新增订单时发给代理商的消息
	 * @param $param
	 * @return array
	 */
	public function sendMsgForHtNewByAgent($param){
		if(empty($param) || !isset($param["customer_name"]) || !isset($param["agent_openid"]) || !isset($param["agent_name"]) || !isset($param["customer_from"]) || !isset($param["customer_level"])){
			return $this->callback(false,"sendMsgForHtNewByAgent param valid");
		}
		if(!isset($param["order_bianhao"]) || !isset($param["order_summoney"]) || !isset($param["order_jine"]) || !isset($param["order_yishou"]) || !isset($param["order_weishou"]) || !isset($param["order_addtime"])){
			return $this->callback(false,"sendMsgForHtNewByAgent order param valid");
		}
		//从配置文件中读取消息模板
		$msg_tpl = $this->getMsgTpl($this->code["ht_new_agent"]);
		if(empty($msg_tpl)){
			return $this->callback(false,"tpl not found");
		}
		//填充数据模板
		$msg_tpl["touser"] = $param["agent_openid"];
		$msg_tpl["data"]["keyword1"]["value"] = sprintf($msg_tpl["data"]["keyword1"]["value"],$param["agent_name"]);
		//发送模板消息
		D("wechat","Logic")->sendTemplateMessage($msg_tpl);
		return $this->callback(true);
	}

	/**
	 * 支付时发给客户的消息
	 * @param $param
	 * @return array
	 */
	public function sendMsgForPayByCustomer($param){
		if(empty($param) || !isset($param["customer_id"]) || !is_numeric($param["customer_id"]) || intval($param["customer_id"]) <= 0 || !isset($param["customer_openid"]) || !isset($param["customer_name"])){
			return $this->callback(false,"sendMsgForPayByCustomer customer param valid");
		}
		if(!isset($param["order_bianhao"]) || !isset($param["order_summoney"]) || !isset($param["order_jine"]) || !isset($param["order_yishou"]) || !isset($param["order_weishou"]) || !isset($param["order_addtime"])){
			return $this->callback(false,"sendMsgForPayByCustomer order param valid");
		}
		if(!isset($param["pay_money"]) || !is_numeric($param["pay_money"]) || intval($param["pay_money"]) <= 0){
			return $this->callback(false,"sendMsgForPayByCustomer pay money valid");
		}
		//从配置文件中读取消息模板
		$msg_tpl = $this->getMsgTpl($this->code["pay_customer"]);
		if(empty($msg_tpl)){
			return $this->callback(false,"tpl not found");
		}
		//填充数据模板
		$msg_tpl["touser"] = $param["customer_openid"];
		$msg_tpl["data"]["keyword1"]["value"] = sprintf($msg_tpl["data"]["keyword1"]["value"],$param["customer_name"]);
		//发送模板消息
		D("wechat","Logic")->sendTemplateMessage($msg_tpl);
		return $this->callback(true);
	}

	/**
	 * 支付时发送给代理商的消息
	 * @param $param
	 * @return array
	 */
	public function sendMsgForPayByAgent($param){
		if(empty($param) || !isset($param["customer_name"]) || !isset($param["agent_openid"]) || !isset($param["agent_name"]) || !isset($param["customer_from"]) || !isset($param["customer_level"])){
			return $this->callback(false,"sendMsgForPayByAgent param valid");
		}
		if(!isset($param["order_bianhao"]) || !isset($param["order_summoney"]) || !isset($param["order_jine"]) || !isset($param["order_yishou"]) || !isset($param["order_weishou"]) || !isset($param["order_addtime"])){
			return $this->callback(false,"sendMsgForPayByAgent order param valid");
		}
		if(!isset($param["pay_money"]) || !is_numeric($param["pay_money"]) || intval($param["pay_money"]) <= 0){
			return $this->callback(false,"sendMsgForPayByAgent pay money valid");
		}
		//从配置文件中读取消息模板
		$msg_tpl = $this->getMsgTpl($this->code["pay_agent"]);
		if(empty($msg_tpl)){
			return $this->callback(false,"tpl not found");
		}
		//填充数据模板
		$msg_tpl["touser"] = $param["agent_openid"];
		$msg_tpl["data"]["keyword1"]["value"] = sprintf($msg_tpl["data"]["keyword1"]["value"],$param["agent_name"]);
		//发送模板消息
		D("wechat","Logic")->sendTemplateMessage($msg_tpl);
		return $this->callback(true);
	}

	/**
	 * 订单支付完成时发送给客户的消息
	 * @param $param
	 * @return array
	 */
	public function sendMsgForOverPayByCustomer($param){
		if(empty($param) || !isset($param["customer_id"]) || !is_numeric($param["customer_id"]) || intval($param["customer_id"]) <= 0 || !isset($param["customer_openid"]) || !isset($param["customer_name"])){
			return $this->callback(false,"sendMsgForSignInByCustomer customer param valid");
		}
		if(!isset($param["order_bianhao"]) || !isset($param["order_summoney"]) || !isset($param["order_jine"]) || !isset($param["order_yishou"]) || !isset($param["order_weishou"]) || !isset($param["order_addtime"])){
			return $this->callback(false,"sendMsgForSignInByCustomer order param valid");
		}
		//从配置文件中读取消息模板
		$msg_tpl = $this->getMsgTpl($this->code["pay_over_customer"]);
		if(empty($msg_tpl)){
			return $this->callback(false,"tpl not found");
		}
		//填充数据模板
		$msg_tpl["touser"] = $param["customer_openid"];
		$msg_tpl["data"]["keyword1"]["value"] = sprintf($msg_tpl["data"]["keyword1"]["value"],$param["customer_name"]);
		//发送模板消息
		D("wechat","Logic")->sendTemplateMessage($msg_tpl);
		return $this->callback(true);
	}

	/**
	 * 订单完成时发送给代理商的消息
	 * @param $param
	 * @return array
	 */
	public function sendMsgForOverPayByAgent($param){
		if(empty($param) || !isset($param["customer_name"]) || !isset($param["agent_openid"]) || !isset($param["agent_name"]) || !isset($param["customer_from"]) || !isset($param["customer_level"])){
			return $this->callback(false,"sendMsgForHtNewByAgent param valid");
		}
		if(!isset($param["order_bianhao"]) || !isset($param["order_summoney"]) || !isset($param["order_jine"]) || !isset($param["order_yishou"]) || !isset($param["order_weishou"]) || !isset($param["order_addtime"])){
			return $this->callback(false,"sendMsgForHtNewByAgent order param valid");
		}
		//从配置文件中读取消息模板
		$msg_tpl = $this->getMsgTpl($this->code["pay_over_agent"]);
		if(empty($msg_tpl)){
			return $this->callback(false,"tpl not found");
		}
		//填充数据模板
		$msg_tpl["touser"] = $param["agent_openid"];
		$msg_tpl["data"]["keyword1"]["value"] = sprintf($msg_tpl["data"]["keyword1"]["value"],$param["agent_name"]);
		//发送模板消息
		D("wechat","Logic")->sendTemplateMessage($msg_tpl);
		return $this->callback(true);
	}

	/**
	 * 增加手术记录后发送给客户的消息
	 * @param $param
	 * @return array
	 */
	public function sendMsgForOprNewByCustomer($param){
		if(empty($param) || !isset($param["customer_id"]) || !is_numeric($param["customer_id"]) || intval($param["customer_id"]) <= 0 || !isset($param["customer_openid"]) || !isset($param["customer_name"])){
			return $this->callback(false,"sendMsgForOprNewByCustomer customer param valid");
		}
		if(!isset($param["order_bianhao"]) || !isset($param["order_summoney"]) || !isset($param["order_jine"]) || !isset($param["order_yishou"]) || !isset($param["order_weishou"]) || !isset($param["order_addtime"])){
			return $this->callback(false,"sendMsgForOprNewByCustomer order param valid");
		}
		//从配置文件中读取消息模板
		$msg_tpl = $this->getMsgTpl($this->code["opr_new_customer"]);
		if(empty($msg_tpl)){
			return $this->callback(false,"tpl not found");
		}
		//填充数据模板
		$msg_tpl["touser"] = $param["customer_openid"];
		$msg_tpl["data"]["keyword1"]["value"] = sprintf($msg_tpl["data"]["keyword1"]["value"],$param["customer_name"]);
		//发送模板消息
		D("wechat","Logic")->sendTemplateMessage($msg_tpl);
		return $this->callback(true);
	}

	/**
	 * 增加手术记录后发送给代理商的消息
	 * @param $param
	 * @return array
	 */
	public function sendMsgForOprNewByAgent($param){
		if(empty($param) || !isset($param["customer_name"]) || !isset($param["agent_openid"]) || !isset($param["agent_name"]) || !isset($param["customer_from"]) || !isset($param["customer_level"])){
			return $this->callback(false,"sendMsgForHtNewByAgent param valid");
		}
		if(!isset($param["order_bianhao"]) || !isset($param["order_summoney"]) || !isset($param["order_jine"]) || !isset($param["order_yishou"]) || !isset($param["order_weishou"]) || !isset($param["order_addtime"])){
			return $this->callback(false,"sendMsgForHtNewByAgent order param valid");
		}
		//从配置文件中读取消息模板
		$msg_tpl = $this->getMsgTpl($this->code["opr_new_agent"]);
		if(empty($msg_tpl)){
			return $this->callback(false,"tpl not found");
		}
		//填充数据模板
		$msg_tpl["touser"] = $param["agent_openid"];
		$msg_tpl["data"]["keyword1"]["value"] = sprintf($msg_tpl["data"]["keyword1"]["value"],$param["agent_name"]);
		//发送模板消息
		D("wechat","Logic")->sendTemplateMessage($msg_tpl);
		return $this->callback(true);
	}

	/**
	 * 根据code读取消息模板
	 * @param $code
	 * @return string
	 */
	public function getMsgTpl($code){
		return C($code);
	}

	public function getFunNameByApiKey($api_key,$type = 0){
		$api_tmp = $api_key;
		switch($type){
			case 1 :
				$api_tmp = $api_key."ByAgent";
				break;
			default:
				$api_tmp = $api_key."ByCustomer";
				break;
		}
		return $api_tmp;
	}

}