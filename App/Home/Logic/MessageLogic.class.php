<?php

/**
 * 消息中心
 */

namespace Home\Logic;
use Think\Model;

class MessageLogic extends MessagebaseLogic{

	/**
	 * 新增机构时发送的消息
	 * @param $param
	 * @return array
	 */
	public function sendMsgForAgentNew($param){
		return $this->sendMsgByAgent("sendMsgForAgentNew",$param);
	}

	/**
	 * 新增客户时发送消息
	 * @param $param
	 * @return array
	 */
	public function sendMsgForCustomerNew($param){
		return $this->sendMsgByCustomer("sendMsgForCustomerNew",$param);
	}

	/**
	 * 增加整形记录后发送消息
	 * @param $param
	 * @return array
	 */
	public function sendMsgForOprNew($param){
		return $this->sendMsgByOrder("sendMsgForOprNew",$param);
	}

	/**
	 * 订单收款完成时发送消息
	 * @param $param
	 * @return array
	 */
	public function sendMsgForOverPay($param){
		return $this->sendMsgByOrder("sendMsgForOverPay",$param);
	}

	/**
	 * 客户支付时发送消息
	 * @param $param
	 * @return array
	 */
	public function sendMsgForPay($param){
		return $this->sendMsgByOrder("sendMsgForPay",$param);
	}

	/**
	 * 签到发送消息
	 * @param $param
	 * @return bool
	 */
	public function sendMsgForSignIn($param){
		return $this->sendMsgByCustomer("sendMsgForSignIn",$param);
	}

	/**
	 * 新增订单时发送消息
	 * @param $param
	 * @return bool
	 */
	public function sendMsgForHtNew($param){
		return $this->sendMsgByOrder("sendMsgForHtNew",$param);
	}

	/**
	 * 返现完成时发送消息
	 * @param $param
	 * @return array
	 */
	public function sendMsgForFu($param){
		return $this->sendMsgByOrder("sendMsgForFu",$param);
	}

	/**
	 * 按客户模式发送消息
	 * @param $api_key
	 * @param $param
	 * @return array
	 */
	public function sendMsgByCustomer($api_key,$param){
		if(empty($param) || !isset($param["customer_id"]) || !is_numeric($param["customer_id"]) || intval($param["customer_id"]) <= 0){
			return $this->callback(true,"param valid");
		}
		//查询客户基本信息
		$customer_info = D("custcon","Logic")->getCustconInfoById($param["customer_id"]);
		if(empty($customer_info)){
			return $this->callback(true,"customer not found");
		}
		if(isset($customer_info["wxid"]) && is_numeric($customer_info["wxid"]) && intval($customer_info["wxid"]) > 0){
			//查询微信信息
			$customer_wxinfo = D("wxuser","Logic")->getWxUserInfoById($customer_info["wxid"]);
			if(!empty($customer_wxinfo) && !is_null($customer_wxinfo["openid"]) && $customer_wxinfo["openid"] != ""){
				$param1 = $param;
				$param1["customer_openid"] = $customer_wxinfo["openid"];
				$param1["customer_name"] = $customer_info["xingming"];
				$param1["customer_id"] = $customer_info["id"];
				$param1["customer_phone"] = $customer_info["phone"];
				$param1["customer_addtime"] = $customer_info["addtime"];
				$_api = $this->getFunNameByApiKey($api_key);
				//给客户发送签到消息
				$this->$_api($param1);
			}
		}
		if(isset($customer_info["jcid"]) && is_numeric($customer_info["jcid"]) && intval($customer_info["jcid"]) > 0){
			$param2 = $param;
			$param2["jcid"] = $customer_info["jcid"];
			$param2["customer_name"] = $customer_info["xingming"];
			$param2["customer_id"] = $customer_info["id"];
			$param2["customer_phone"] = $customer_info["phone"];
			$param2["customer_addtime"] = $customer_info["addtime"];
			$param2["agent_child"] = "";
			$param2["child_level"] = 0;
			$this->sendMsgByAgent($api_key,$param2);
		}
		return $this->callback(true);
	}

	/**
	 * 按代理商模板发送消息
	 * @param $api_key
	 * @param $param
	 * @param bool|true $forward
	 * @return array
	 */
	public function sendMsgByAgent($api_key,$param,$forward=true){
		if(empty($param) || !isset($param["jcid"]) || !is_numeric($param["jcid"])){
			return $this->callback(true,"sendMsgByAgent param valid");
		}
		//查询机构/代理商信息
		$agent_info = D("cust","Logic")->getCustInfoById($param["jcid"]);
		if(empty($agent_info)){
			return $this->callback(true,"sendMsgByAgent agent info not found");
		}
		//查询微信信息
		$agent_wxinfo = D("wxuser","Logic")->getWxUserInfoById($agent_info["wxid"]);
		if(!empty($agent_wxinfo) && !is_null($agent_wxinfo["openid"]) && $agent_wxinfo["openid"] != ""){
			$param_tmp = $param;
			$param_tmp["agent_openid"] = $agent_wxinfo["openid"];
			$param_tmp["agent_name"] = $agent_info["title"];
			$_api = $this->getFunNameByApiKey($api_key,1);
			$this->$_api($param_tmp);
		}
		if($forward && is_numeric($agent_info["jcid"]) && intval($agent_info["jcid"]) > 0){
			$param2 = $param;
			$param2["jcid"] = $agent_info["jcid"];
			$param2["agent_child"] = $agent_info["title"];
			$param2["child_level"] = 1;
			$this->sendMsgByAgent($api_key,$param2,false);
		}
		return $this->callback(true);
	}

	/**
	 * 按订单模式发送消息
	 * @param $api_key
	 * @param $param
	 * @return array
	 */
	public function sendMsgByOrder($api_key,$param){
		if(empty($param) || !isset($param["hid"]) || !is_numeric($param["hid"])){
			return $this->callback(false,"sendMsgByOrder param valid");
		}
		//根据订单ID查询订单详情
		$ht_info = D("hetong","Logic")->getHetongInfoById($param["hid"]);
		if(empty($ht_info)){
			return $this->callback(false,"hetong not found,ID:".$param["hid"]);
		}
		if(!isset($ht_info["cuid"]) || !is_numeric($ht_info["cuid"]) || intval($ht_info["cuid"]) <= 0){
			return $this->callback(false,"sendMsgByOrder customer id not found,hetong ID:".$param["hid"]);
		}
		$param["order_bianhao"] = $ht_info["bianhao"];
		$param["order_id"] = $ht_info["id"];
		$param["customer_id"] = $ht_info["cuid"];
		$param["customer_name"] = $ht_info["cuname"];
		$param["order_summoney"] = $ht_info["summoney"];
		$param["order_jine"] = $ht_info["jine"];
		$param["order_yishou"] = $ht_info["yishou"];
		$param["order_weishou"] = $ht_info["weishou"];
		$param["order_addtime"] = $ht_info["addtime"];
		return $this->sendMsgByCustomer($api_key,$param);
	}

}