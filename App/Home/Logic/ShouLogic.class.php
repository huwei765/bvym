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

class ShouLogic extends Model{

	/**
	 * 根据收款操作来更新合同上的收款记录
	 * @param $jhid
	 * @param $money
	 */
	public function modifyHtJineByShou($jhid,$money){
		//修改客户合同收款金额
		D("hetong","Logic")->modifyJineById($jhid,$money);
		//检查客户合同收款是否完成，从而引起合同收款完成动作（修改合同状态以及添加提成记录）
		D("hetong","Logic")->doShouOver($jhid);
	}

}