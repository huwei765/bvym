<?php

namespace Home\Logic;
use Think\Model;

class OpsrecordLogic extends Model{

	/**
	 * 根据用户id查询手术信息列表
	 * @param $cuid
	 * @param string $order
	 * @param string $field
	 * @return mixed
	 */
	public function getListByCUid($cuid,$order="id desc",$field="*"){
		return $this->getList(array("cuid"=>$cuid),$order,$field);
	}

	/**
	 * 查询列表信息
	 * @param $condition
	 * @param string $order
	 * @param string $field
	 * @return mixed
	 */
	public function getList($condition,$order="id desc",$field="*"){
		return M("opsrecord")->field($field)->where($condition)->order($order)->select();
	}
}