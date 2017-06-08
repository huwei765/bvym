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

class PiaoLogic extends Model{

	/**
	 * 根据订单ID查询该订单的所有开票记录
	 * @param $hid
	 * @param string $field
	 * @param string $order
	 * @return mixed
	 */
	public function getListByHid($hid,$field="*",$order="id desc"){
		return $this->getList(array("jhid"=>$hid),$field,$order);
	}

	/**
	 * 查询开票列表
	 * @param $condition
	 * @param string $order
	 * @param string $field
	 * @return mixed
	 */
	public function getList($condition,$field="*",$order="id desc"){
		return M("piao")->field($field)->where($condition)->order($order)->select();
	}

}