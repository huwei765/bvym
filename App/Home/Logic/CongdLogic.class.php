<?php

/**
 *		跟单
 *      [X-Mis] (C)2007-2099  
 *      This is NOT a freeware, use is subject to license terms
 *      http://www.xinyou88.com
 *      tel:400-000-9981
 *      qq:16129825
 */

namespace Home\Logic;
use Think\Model;

class CongdLogic extends Model{

	/**
	 * 根据用户id查询跟单信息列表
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
		return M("congd")->field($field)->where($condition)->order($order)->select();
	}

}