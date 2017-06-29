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
	 * 返现操作
	 * @param $newData
	 * @return int|mixed
	 */
	public function doFuForAgent($newData){
		if(empty($newData)){
			return 0;
		}
		if(!isset($newData["profitid"]) || !is_numeric($newData["profitid"]) || !isset($newData["jine"]) || !is_numeric($newData["jine"]) || !isset($newData["bianhao"]) || !isset($newData["type"]) || !is_numeric($newData["type"])){
			return 0;
		}
		//查询cusprofit状态是否为待返现状态
		$info = D("cusprofit","Logic")->getNoPayInfoById($newData["profitid"],"id");
		if(empty($info)){
			return 0;
		}
		//查询收款是否是未返现状态
		$shouData = D("shou","Logic")->getInfoByIdForNoFu($newData["shouid"],"id");
		if(empty($shouData)){
			return 0;
		}
		//组装新收据
		$tmpData["bianhao"] = $newData["bianhao"];//付款单据编号
		$tmpData["shouid"] = $newData["shouid"];//收款单ID
		$tmpData["sbianhao"] = $newData["sbianhao"];//收款单编号
		$tmpData["shou"] = $newData["shou"];//收款单金额
		$tmpData["profitid"] = $newData["profitid"];//提成明细ID
		$tmpData["jhid"] = $newData["jhid"];//订单ID
		$tmpData["jhname"] = $newData["jhname"];//订单编号
		$tmpData["rate"] = $newData["rate"];//佣金比率
		$tmpData["pay"] = $newData["pay"];//应付佣金
		$tmpData["type"] = $newData["type"];//付款方式
		$tmpData["jine"] = $newData["jine"];//实付佣金
		$tmpData["jcid"] = $newData["jcid"];//机构ID
		$tmpData["jcname"] = $newData["jcname"];//机构名称
		if(isset($newData["juid"])){
			$tmpData["juid"] = $newData["juid"];//经办人ID
			$tmpData["juname"] = $newData["juname"];//经办人名称
		}
		if(isset($newData["beizhu"])){
			$tmpData["beizhu"] =$newData["beizhu"];//备注
		}
		$ret = $this->addFuInfoForAgent($tmpData);
		if($ret){
			//修改收款状态为已返现
			D("shou","Logic")->setFanOverById($tmpData["shouid"]);
			//更新提成明细中的已付款
			D("cusprofit","Logic")->doFuForAgent($tmpData["profitid"],$ret,$tmpData["shouid"],$tmpData["jine"]);
		}
		return $ret;
	}

	/**
	 * 添加返现佣金
	 * @param $newData
	 * @return int|mixed
	 */
	public function addFuInfoForAgent($newData){
		if(empty($newData)){
			return 0;
		}
		if(!isset($newData["profitid"]) || !is_numeric($newData["profitid"]) || !isset($newData["jine"]) || !is_numeric($newData["jine"]) || !isset($newData["bianhao"]) || !isset($newData["type"]) || !is_numeric($newData["type"])){
			return 0;
		}
		//查询订单是否已经付款
		$count = $this->getCountByPidAndSid($newData["profitid"],$newData["shouid"]);
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
	 * 根据提成明细ID和收款ID查询响应的付款记录条数
	 * @param $pid
	 * @param $sid
	 * @param string $field
	 * @return mixed
	 */
	public function getCountByPidAndSid($pid,$sid,$field = "*"){
		return $this->getCount(array("profitid"=>$pid,"shouid"=>$sid),$field);
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