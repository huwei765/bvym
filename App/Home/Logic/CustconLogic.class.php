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

class CustconLogic extends Model{

	/**
	 * 根据机构ID查询客户列表
	 * @param $jcid
	 * @param string $field
	 * @param string $order
	 * @return mixed
	 */
	public function getListByJCid($jcid,$field="*",$order="id desc"){
		return $this->getList(array("jcid"=>$jcid),$field,$order);
	}

	/**
	 * 根据姓名查询用户
	 * @param $name
	 * @param string $field
	 * @return Model
	 */
	public function getOneInfoByName($name,$field="*"){
		return $this->getCustconInfo(array("xingming"=>$name),$field);
	}

	/**
	 * 根据id查询客户信息的基本逻辑
	 * @param $cuid
	 * @param string $field
	 * @return Model
	 */
	public function getCustconInfoById($cuid,$field="*"){
		return $this->getCustconInfo(array("id"=>$cuid),$field);
	}

	/**
	 * 根据wxid查询客户基本信息
	 * @param $weixinId
	 * @param string $field
	 * @return Model
	 */
	public function getCustconInfoByWeixinId($weixinId,$field="*"){
		return $this->getCustconInfo(array("wxid"=>$weixinId),$field);
	}
	/**
	 * 查询客户信息的基本逻辑
	 * @param $condition
	 * @param string $field
	 * @return Model
	 */
	public function getCustconInfo($condition,$field="*"){
		return M("custcon")->field($field)->where($condition)->find();
	}

	/**
	 * 查询客户列表
	 * @param $condition
	 * @param string $field
	 * @param string $order
	 * @return mixed
	 */
	public function getList($condition,$field="*",$order="id desc"){
		return M("custcon")->field($field)->where($condition)->order($order)->select();
	}

}