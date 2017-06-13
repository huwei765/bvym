<?php

namespace Home\Logic;
use Think\Model;

class CustgdLogic extends Model{

	/**
	 * 根据机构获取跟单列表
	 * @param $cid
	 * @param string $field
	 * @param string $order
	 * @return mixed
	 */
	public function getListByCid($cid,$field="*",$order="id desc"){
		return $this->getList(array("jcid"=>$cid),$field,$order);
	}

	/**
	 * 获取跟单列表
	 * @param $condition
	 * @param string $field
	 * @param string $order
	 * @return mixed
	 */
	public function getList($condition,$field="*",$order="id desc"){
		return M("custgd")->field($field)->where($condition)->order($order)->select();
	}

}