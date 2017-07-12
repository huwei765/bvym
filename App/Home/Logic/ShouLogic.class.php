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
	 * 设置收款为已返现状态
	 * @param $id
	 */
	public function setFanOverById($id){
		M("shou")->where('id='.$id)->setField("status",1);
	}

	/**
	 * 订单添加收款逻辑，同时改变订单收款值
	 * @param $newData
	 * @return int|mixed
	 */
	public function addShouInfoForHt($newData){
		$result = $this->addShouInfo($newData);
		if($result){
			if(isset($newData["jhid"]) && is_numeric($newData["jhid"]) && isset($newData["jine"]) && is_numeric($newData["jine"])){
				$jhid = $newData["jhid"];
				$jine = $newData["jine"];
				$this->modifyHtJineByShou($jhid,$jine);
				//发送支付消息
				D("message","Logic")->sendMsgForPay(array("hid"=>$newData["jhid"],"pay_money"=>$newData["jine"]));
			}
		}
		return $result;
	}

	/**
	 * 订单添加收款
	 * @param $newData
	 * @return int|mixed
	 */
	public function addShouInfo($newData){
		if(empty($newData)){
			return 0;
		}
		if(!isset($newData["jhid"]) || !is_numeric($newData["jhid"]) || !isset($newData["bianhao"]) || !isset($newData["jine"]) || !is_numeric($newData["jine"])){
			return 0;
		}
		//查询订单收款是否完成
		if(D("hetong","Logic")->checkShouValid($newData["jhid"],$newData["jine"])){
			return $this->addInfo($newData);
		}
		else{
			return 0;
		}
	}

	/**
	 * 添加新记录
	 * @param $newData
	 * @return int|mixed
	 */
	public function addInfo($newData){
		if(empty($newData)){
			return 0;
		}
		if(!isset($newData["jhid"]) || !is_numeric($newData["jhid"]) || !isset($newData["bianhao"]) || !isset($newData["cuid"]) || !is_numeric($newData["cuid"]) || !isset($newData["cuname"])){
			return 0;
		}
		$newData["addtime"] = time();
		$shouModel = D("shou");
		if($shouModel->create($newData)){
			return $shouModel->add();
		}
		else{
			return 0;
		}
	}

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

	/**
	 * 根据订单ID查询该订单的所有未返现的收款记录
	 * @param $hid
	 * @param string $field
	 * @param string $order
	 * @return mixed
	 */
	public function getListByHidForNoFu($hid,$field="*",$order="id desc"){
		return $this->getList(array("jhid"=>$hid,"status"=>0),$field,$order);
	}

	/**
	 * 根据订单ID查询该订单的所有收款记录
	 * @param $hid
	 * @param string $field
	 * @param string $order
	 * @return mixed
	 */
	public function getListByHid($hid,$field="*",$order="id desc"){
		return $this->getList(array("jhid"=>$hid),$field,$order);
	}

	/**
	 * 查询收款列表
	 * @param $condition
	 * @param string $order
	 * @param string $field
	 * @return mixed
	 */
	public function getList($condition,$field="*",$order="id desc"){
		return M("shou")->field($field)->where($condition)->order($order)->select();
	}

	/**
	 * 查询未返现的收款信息
	 * @param $id
	 * @param string $field
	 * @return mixed
	 */
	public function getInfoByIdForNoFu($id,$field="*"){
		return $this->getInfo(array("id"=>$id,"status"=>0),$field);
	}

	/**
	 * 根据ID查询收款信息
	 * @param $id
	 * @param string $field
	 * @return mixed
	 */
	public function getInfoById($id,$field="*"){
		return $this->getInfo(array("id"=>$id),$field);
	}

	/**
	 * 查询
	 * @param $id
	 * @param $hid
	 * @param $code
	 * @param string $field
	 * @return mixed
	 */
	public function getInfoByIdAndHidAndCode($id,$hid,$code,$field = "*"){
		return $this->getInfo(array("id"=>$id,"hid"=>$hid,"bianhao"=>$code),$field);
	}

	/**
	 * 查询基本信息
	 * @param $condition
	 * @param string $field
	 * @return mixed
	 */
	public function getInfo($condition,$field="*"){
		return M("shou")->field($field)->where($condition)->find();
	}

	public function getSumForYiShouByHid($hid){
		return M("shou")->where("jhid=".$hid)->sum("jine");
	}

}