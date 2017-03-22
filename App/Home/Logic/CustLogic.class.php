<?php

namespace Home\Logic;
use Think\Model;

class CustLogic extends Model{

	/**
	 * 根据id查询客户信息的基本逻辑
	 * @param $juid
	 * @param string $field
	 * @return Model
	 */
	public function getCustInfoById($juid,$field="*"){
		return $this->getCustInfo(array("id"=>$juid),$field);
	}

	/**
	 * 查询渠道信息的基本逻辑
	 * @param $condition
	 * @param string $field
	 * @return Model
	 */
	public function getCustInfo($condition,$field="*"){
		return M("cust")->field($field)->where($condition)->find();
	}

}