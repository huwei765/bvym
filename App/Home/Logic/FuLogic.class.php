<?php

/**
 *		收款的业务逻辑
 *      [X-Mis] (C)2007-2099  
 *      This is NOT a freeware, use is subject to license terms
 *      http://www.xinyou88.com
 *      tel:400-000-9981
 *      qq:16129825
 */

namespace Home\Logic;
use Think\Model;

class FuLogic extends Model{

	/**
	 * 付款流程
	 * @param $newData
	 * @return int|mixed
	 */
	public function doFuForAgentByProfit($newData){
		if(empty($newData)){
			return 0;
		}
		if(!isset($newData["profit_id"]) || !is_numeric($newData["profit_id"]) || !isset($newData["jine"]) || !is_numeric($newData["jine"]) || !isset($newData["bianhao"]) || !isset($newData["type"]) || !is_numeric($newData["type"])){
			return 0;
		}
		//查询cusprofit
		$info = D("cusprofit","Logic")->getNoPayInfoById($newData["profit_id"]);
		if(empty($info)){
			return 0;
		}
		//组装新数据
		$tmpData["bianhao"] = $newData["bianhao"];
		$tmpData["jine"] = $newData["jine"];
		$tmpData["type"] = $newData["type"];
		$tmpData["jhid"] = $info["jhid"];
		$tmpData["jhname"] = $info["jhcode"];
		$tmpData["jcid"] = $info["jcid"];
		$tmpData["jcname"] = $info["jcname"];
		$tmpData["juid"] = $info["juid"];
		$tmpData["juname"] = $info["juname"];
		if(isset($newData["beizhu"])){
			$tmpData["beizhu"] =$newData["beizhu"];
		}
		$ret = $this->addFuInfo($tmpData);
		if($ret){
			//修改佣金状态
			D("cusprofit","Logic")->setStatusToOver($newData["profit_id"]);
		}
		return $ret;
	}

	/**
	 * 付款流程
	 * @param $newData
	 * @return int|mixed
	 */
	public function doFuForAgent($newData){
		$ret = $this->addFuInfo($newData);
		return $ret;
	}

	/**
	 * 添加付款记录
	 * @param $newData
	 * @return int|mixed
	 */
	public function addFuInfo($newData){
		if(empty($newData)){
			return 0;
		}
		if(!isset($newData["jhid"]) || !is_numeric($newData["jhid"]) || !isset($newData["jhname"]) || !isset($newData["bianhao"]) || !isset($newData["jine"]) || !is_numeric($newData["jine"]) || !isset($newData["jcid"]) || !is_numeric($newData["jcid"])){
			return 0;
		}
		//查询订单是否已经付款
		$count = $this->getCountByHidAndAgentId($newData["jhid"],$newData["jcid"]);
		if(intval($count) > 0){
			return 0;
		}
		//开始付款
		$fuModel = D("fu");
		if($fuModel->create($newData)){
			return $fuModel->add();
		}
		else{
			return 0;
		}
	}

	/**
	 * 根据订单ID和机构ID统计数量
	 * @param $hid
	 * @param $jcid
	 * @param string $field
	 * @return mixed
	 */
	public function getCountByHidAndAgentId($hid,$jcid,$field = "id"){
		return $this->getCount(array("jhid"=>$hid,"jcid"=>$jcid),$field);
	}

	/**
	 * 统计总数
	 * @param $condtion
	 * @param string $field
	 * @return mixed
	 */
	public function getCount($condtion,$field = "id"){
		return M("fu")->where($condtion)->count($field);
	}

}