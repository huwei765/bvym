<?php

namespace Home\Logic;
use Think\Model;

class OpscateLogic extends Model{

	/**
	 * 获取所有的项目列表
	 * @param string $field
	 * @param string $order
	 * @return mixed
	 */
	public function getAllCateList($field="*",$order="id desc"){
		return M("opscate")->field($field)->select();
	}

	/**
	 * 根据id查询项目类别信息
	 * @param $id
	 * @param string $field
	 * @return mixed
	 */
	public function getCateInfoById($id,$field="*"){
		return $this->getCateInfo(array("id"=>$id),$field);
	}

	/**
	 * 查询项目类别信息
	 * @param $condition
	 * @param string $field
	 * @return mixed
	 */
	public function getCateInfo($condition,$field="*"){
		return M("opscate")->field($field)->where($condition)->find();
	}
}