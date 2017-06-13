<?php

namespace Home\Logic;
use Think\Model;

class JghtLogic extends Model{

	/**
	 * 根据机构ID查询合同详情
	 * @param $cid
	 * @param string $field
	 * @return mixed
	 */
	public function getInfoByCid($cid,$field="*"){
		return $this->getInfo(array("jcid"=>$cid),$field);
	}


	/**
	 * 查询机构合同列表
	 * @param $condition
	 * @param string $field
	 * @param string $order
	 * @return mixed
	 */
	public function getList($condition,$field="*",$order="id desc"){
		return M("jght")->field($field)->where($condition)->order($order)->select();
	}

	/**
	 * 查询机构合同详情
	 * @param $condition
	 * @param string $field
	 * @return mixed
	 */
	public function getInfo($condition,$field="*"){
		return M("jght")->field($field)->where($condition)->find();
	}

}