<?php

/**
 *
 *      [X-Mis] (C)2007-2099  
 *      This is NOT a freeware, use is subject to license terms
 *      http://www.xinyou88.com
 *      tel:400-000-9981
 *      qq:16129825
 */

namespace Home\Logic;
use Think\Model;

class CusprofitLogic extends Model{

	const STATE0 = 0;       // 待审核
	const STATE1 = 1;       // 待确认
	const STATE2 = 2;       // 待付款
	const STATE3 = 3;       // 已完成
	const STATE4 = 4;       // 不通过

	/**
	 * 更新已付款金额并检测该笔订单佣金是否完成
	 * @param $id
	 * @param $fuId
	 * @param $shouId
	 * @param $jine
	 * @return int
	 */
	public function doFuForAgent($id,$fuId,$shouId,$jine){
		//查询提成明细
		$cusprofitData = $this->getNoPayInfoById($id);
		if(empty($cusprofitData)){
			return 0;
		}
		//更新已付款记录
		if(intval($cusprofitData["yifu"]) + intval($jine) - intval($cusprofitData["commission"]) <= 0){
			M("cusprofit")->where('id='.$id)->setInc('yifu',$jine);
		}
		//检测该笔提成是否完成
		if(intval($cusprofitData["yifu"]) + intval($jine) - intval($cusprofitData["commission"]) == 0){
			//设置该笔提成完成
			$this->updateStatusById($id,3);
		}
	}

	/**
	 * 计算利润提成的明细
	 * @param $data
	 * @return array
	 */
	public function doOrderProfit($data){
		if(empty($data) || !isset($data["jcid"]) || !isset($data["jhid"]) || !isset($data["jhcode"]) || !isset($data["jine"]) || !isset($data["profit"]) || !isset($data["level"])){
			return false;
		}
		//查询是否已经增加利润提成
		$tmpCount = $this->GetCountByHidAndJCid($data["jhid"],$data["jcid"]);
		if(intval($tmpCount) > 0){
			return false;
		}
		//查询机构信息
		$cust_info = D("cust","Logic")->getCustInfoById($data["jcid"],"id,rate,jcid,jcname");
		if(empty($cust_info)){
			return false;
		}
		if(intval($cust_info["rate"]) <= 0){
			return false;
		}
		//组装数据
		$tmpData = $data;
		if(!isset($tmpData["pre_rate"])){
			$tmpData["pre_rate"] = 0;
		}
		$tmpData["rate"] = intval($cust_info["rate"]) - intval($tmpData["pre_rate"]);//本机构利润率减去下级机构的利润率
		//计算佣金
		$tmpData["commission"] = floatval(intval($tmpData["profit"]) * intval($tmpData["rate"]) / 100);
		//保存数据
		$cusprofit_model = D("cusprofit");
		$cusprofit_model->startTrans();
		try{
			if($cusprofit_model->create($tmpData)){
				$result = $cusprofit_model->add();
				if($result){
					//转入上级代理商提成
					if(intval($cust_info["jcid"]) > 0){
						//组装新的数据
						$newTmpData = $tmpData;
						$newTmpData["jcid"] = $cust_info["jcid"];
						$newTmpData["jcname"] = $cust_info["jcname"];
						$newTmpData["level"] = intval($newTmpData["level"]) + 1;//推广层级加1
						$newTmpData["pre_rate"] = $cust_info["rate"];
						$this->doOrderProfit($newTmpData);//转入上级代理商处理
					}
				}
			}
			$cusprofit_model->commit();
			return false;
		}
		catch(Exception $e){
			$cusprofit_model->rollback();
			return false;
		}

	}


	/**
	 * 利润提成计算
	 * @param $rate
	 * @param $profit
	 * @return int
	 */
	public function calculateProfit($rate,$profit){
		$profit = $profit * intval($rate) / 100;
		$formula = "rate = ".$rate."%";
		return array("formula"=>$formula,"profit"=>intval($profit));
	}

	/**
	 * 根据推广提成比例计算提成
	 * @param $level
	 * @param $profit
	 * @return array
	 */
	public function calculateProfitBySpread($level,$profit){
		$rate = C("CUS_SPREAD_RATE.".$level);
		if(empty($level)){
			$rate = C("CUS_SPREAD_RATE.other");
		}
		$ret_data = $this->calculateProfit($rate,$profit);
		return array("formula"=>$ret_data["formula"],"profit"=>$ret_data["profit"],"rate"=>$rate);
	}

	/**
	 * 推广提成新增逻辑
	 * @param $insert
	 * @return mixed
	 */
	public function addProfit($insert){
		return M("cusprofit")->data($insert)->add();
	}

	/**
	 * 根据订单ID和机构ID来统计总数
	 * @param $hid
	 * @param $jcid
	 * @param string $field
	 * @return mixed
	 */
	public function GetCountByHidAndJCid($hid,$jcid,$field = "id"){
		return $this->GetCount(array("jhid"=>$hid,"jcid"=>$jcid),$field);
	}

	/**
	 * 统计总数
	 * @param $condition
	 * @param string $field
	 * @return mixed
	 */
	public function GetCount($condition,$field = "id"){
		return M("cusprofit")->where($condition)->count($field);
	}

	/**
	 * 根据合同ID和机构ID查询该条明细是否存在
	 * @param $hid
	 * @param $jcid
	 * @param string $field
	 * @param string $group
	 * @param string $order
	 * @param int $limit
	 * @param int $page
	 * @param bool|false $lock
	 * @param int $count
	 * @return mixed
	 */
	public function GetProfitListByHidAndJCid($hid,$jcid,$field = '*', $group = '',$order = '', $limit = 0, $page = 0, $lock = false, $count = 0){
		return $this->GetProfitList(array("jhid"=>$hid,"jcid"=>$jcid),$field, $group,$order, $limit, $page, $lock, $count);
	}

	/**
	 * 提成查询基本逻辑
	 * @param $condition
	 * @param string $field
	 * @param string $group
	 * @param string $order
	 * @param int $limit
	 * @param int $page
	 * @param bool|false $lock
	 * @param int $count
	 * @return mixed
	 */
	public function GetProfitList($condition, $field = '*', $group = '',$order = '', $limit = 0, $page = 0, $lock = false, $count = 0){
		return M('cusprofit')->field($field)->where($condition)->order($order)->select();
	}
	public function GetNoVerifyProfitList($field = '*', $group = '',$order = '', $limit = 0, $page = 0, $lock = false, $count = 0){
		$condition["status"] = 0;
		return $this->GetProfitList($condition, $field, $group,$order, $limit, $page, $lock, $count);
	}
	public function GetNoConfirmProfitList($field = '*', $group = '',$order = '', $limit = 0, $page = 0, $lock = false, $count = 0){
		$condition["status"] = 1;
		return $this->GetProfitList($condition, $field, $group,$order, $limit, $page, $lock, $count);
	}

	public function GetNoPayProfitList($field = '*', $group = '',$order = '', $limit = 0, $page = 0, $lock = false, $count = 0){
		$condition["status"] = 2;
		return $this->GetProfitList($condition, $field, $group,$order, $limit, $page, $lock, $count);
	}
	public function GetOverProfitList($field = '*', $group = '',$order = '', $limit = 0, $page = 0, $lock = false, $count = 0){
		$condition["status"] = 3;
		return $this->GetProfitList($condition, $field, $group,$order, $limit, $page, $lock, $count);
	}
	public function GetFailProfitList($field = '*', $group = '',$order = '', $limit = 0, $page = 0, $lock = false, $count = 0){
		$condition["status"] = 4;
		return $this->GetProfitList($condition, $field, $group,$order, $limit, $page, $lock, $count);
	}

	/**
	 * 根据ID更新状态
	 * @param $id
	 * @param int $status
	 * @return bool
	 */
	public function updateStatusById($id,$status = 0){
		return $this->updateInfo(array("id"=>$id),array("status"=>$status));
	}

	/**
	 * 更新用户信息
	 * @param $condition
	 * @param $data
	 * @return bool
	 */
	public function updateInfo($condition,$data){
		return M("cusprofit")->where($condition)->save($data);
	}

	/**
	 * 获取待付款的记录信息
	 * @param $id
	 * @param $field
	 * @return mixed
	 */
	public function getNoPayInfoById($id,$field){
		return $this->getInfo(array("id"=>$id,"status"=>2),$field);
	}

	/**
	 * 通过ID查询基本信息
	 * @param $id
	 * @param $field
	 * @return mixed
	 */
	public function getInfoById($id,$field){
		return $this->getInfo(array("id"=>$id),$field);
	}

	/**
	 * 读取基本信息
	 * @param $condition
	 * @param string $field
	 * @return mixed
	 */
	public function getInfo($condition,$field="*"){
		return M("cusprofit")->field($field)->where($condition)->find();
	}

}