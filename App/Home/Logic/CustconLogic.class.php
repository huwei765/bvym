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
	 * 查询美容院的顾客及下级代理商的顾客
	 * @param $jcid
	 * @param string $field
	 * @param string $order
	 * @return mixed
	 */
	public function getListForAgentByJCid($jcid,$field="*",$order="id desc"){
		$condition["jcid"] = array(array("EQ",$jcid));
		//查询上级
		$custList = D("cust","Logic")->getListByCustId($jcid,"id");
		if(!empty($custList)){
			foreach ($custList as $val) {
				if(isset($val["id"]) && is_numeric($val["id"])){
					array_push($condition["jcid"],array("EQ",$val["id"]));
				}
			}
			array_push($condition["jcid"],"or");
		}
		return $this->getList($condition,$field,$order);
	}

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

	/**
	 * 根据CUID查询客户详细信息
	 * @param $cuid
	 * @param string $field
	 * @return mixed
	 */
	public function getDetailInfoByCUid($cuid,$field="*"){
		return $this->getDetailInfo(array("cuid"=>$cuid),$field);
	}

	/**
	 * 查询详细信息
	 * @param $condition
	 * @param string $field
	 * @return mixed
	 */
	public function getDetailInfo($condition,$field="*"){
		return M("condetail")->field($field)->where($condition)->find();
	}

	/**
	 * 添加客户详细信息
	 * @param $data
	 * @return bool|mixed
	 */
	public function addConDetailInfo($data){
		if(empty($data)){
			return false;
		}
		if(!isset($data["cuid"]) || !is_numeric($data["cuid"])){
			return false;
		}
		$cuid = $data["cuid"];
		//查询客户基本信息
		$cinfo = $this->getCustconInfoById($cuid);
		if(empty($cinfo)){
			return false;
		}
		$data["cuid"] = $cuid;
		$data["cuname"] = $cinfo["xingming"];
		$conDetailModel = D("condetail");
		if($conDetailModel->create($data)){
			return $conDetailModel->add();
		}
		else{
			return false;
		}
	}

	public function editDetailInfoByCUid($cuid,$data){
		unset($data["id"]);
		return M("condetail")->where("cuid=".$cuid)->save($data);
	}
}