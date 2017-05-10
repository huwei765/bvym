<?php

namespace Home\Logic;
use Think\Model;

class OpsLogic extends Model{

	/**
	 * 根据tit获取项目
	 * @param $tit
	 * @param string $field
	 * @return mixed
	 */
	public function getInfoByTit($tit,$field="*"){
		return $this->getInfo(array("title"=>$tit),$field);
	}

	/**
	 * 获取所有的项目列表
	 * @param string $field
	 * @param string $order
	 * @return mixed
	 */
	public function getAllList($field="*",$order="id desc"){
		return M("ops")->field($field)->select();
	}

	/**
	 * 根据id查询项目信息
	 * @param $id
	 * @param string $field
	 * @return mixed
	 */
	public function getInfoById($id,$field="*"){
		return $this->getInfo(array("id"=>$id),$field);
	}

	/**
	 * 查询项目信息
	 * @param $condition
	 * @param string $field
	 * @return mixed
	 */
	public function getInfo($condition,$field="*"){
		return M("ops")->field($field)->where($condition)->find();
	}
}