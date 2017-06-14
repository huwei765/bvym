<?php

namespace Home\Logic;
use Think\Model;

class CustLogic extends Model{

	/**
	 * 根据机构ID查询下属机构
	 * @param $jcid
	 * @param string $field
	 * @param string $order
	 * @return mixed
	 */
	public function getListByCustId($jcid,$field="*",$order="id desc"){
		return $this->getList(array("jcid"=>$jcid),$field,$order);
	}

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

	/**
	 * 查询机构列表
	 * @param $condition
	 * @param string $field
	 * @param string $order
	 * @return mixed
	 */
	public function getList($condition,$field="*",$order="id desc"){
		return M("cust")->field($field)->where($condition)->order($order)->select();
	}

}