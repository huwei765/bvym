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
	const STATE1 = 1;       // 审核通过未付款
	const STATE2 = 2;       // 已完成
	const STATE3 = 3;       // 审核不通过

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
		return D('cusprofit')->field($field)->where($condition)->group($group)->order($order)->limit($limit)->page($page, $count)->lock($lock)->select();
	}
	public function GetNoVerifyProfitList($condition, $field = '*', $group = '',$order = '', $limit = 0, $page = 0, $lock = false, $count = 0){
		$condition["status"] = STATE0;
		return $this->GetProfitList($condition, $field, $group,$order, $limit, $page, $lock, $count);
	}
	public function GetNoPayProfitList($condition, $field = '*', $group = '',$order = '', $limit = 0, $page = 0, $lock = false, $count = 0){
		$condition["status"] = STATE1;
		return $this->GetProfitList($condition, $field, $group,$order, $limit, $page, $lock, $count);
	}
	public function GetOverProfitList($condition, $field = '*', $group = '',$order = '', $limit = 0, $page = 0, $lock = false, $count = 0){
		$condition["status"] = STATE2;
		return $this->GetProfitList($condition, $field, $group,$order, $limit, $page, $lock, $count);
	}
	public function GetFailProfitList($condition, $field = '*', $group = '',$order = '', $limit = 0, $page = 0, $lock = false, $count = 0){
		$condition["status"] = STATE3;
		return $this->GetProfitList($condition, $field, $group,$order, $limit, $page, $lock, $count);
	}

}