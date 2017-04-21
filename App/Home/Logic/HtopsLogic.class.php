<?php

namespace Home\Logic;
use Think\Model;

class HtopsLogic extends Model{

	/**
	 * 根据合同id获取相关项目列表
	 * @param $hid
	 * @param string $field
	 * @param string $order
	 * @return mixed
	 */
	public function getHtOpsListByHid($hid,$field="*",$order="id desc"){
		return $this->getHtOpsList(array("hid"=>$hid),$field,$order);
	}

	/**
	 * 获取项目列表
	 * @param $condition
	 * @param string $field
	 * @param string $order
	 * @return mixed
	 */
	public function getHtOpsList($condition,$field="*",$order="id desc"){
		return M("htops")->field($field)->where($condition)->order($order)->select();
	}

	/**
	 * 根据id查询相关项目明细
	 * @param $id
	 * @param string $field
	 * @return mixed
	 */
	public function getHtOpsInfoById($id,$field="*"){
		return $this->getHtOpsInfo(array("id"=>$id),$field);
	}

	/**
	 * 查询合同相关项目明细
	 * @param $condition
	 * @param string $field
	 * @return mixed
	 */
	public function getHtOpsInfo($condition,$field="*"){
		return M("htops")->field($field)->where($condition)->find();
	}

	/**
	 * 新增合同相关项目记录
	 * @param $newData
	 * @return int|mixed
	 */
	public function addHtOpsInfo($newData){
		if(empty($newData)){
			return 0;
		}
		if(!isset($newData["hid"]) || !isset($newData["bianhao"]) || !isset($newData["oid"]) || !isset($newData["oname"])){
			return 0;
		}
		$htOpsData["hid"] = $newData["hid"];
		$htOpsData["bianhao"] = $newData["bianhao"];
		$htOpsData["oid"] = $newData["oid"];
		$htOpsData["oname"] = $newData["oname"];
		$htOpsData["ocname"] = $newData["ocname"];
		$htOpsData["price"] = $newData["price"];
		$htOpsData["addtime"] = time();

		$htOpsModel = M("htops");
		if($htOpsModel->create($htOpsData)){
			return $htOpsModel->add();
		}
		else{
			return 0;
		}
	}

	/**
	 * 删除订单相关子项目全逻辑
	 * @param $id
	 * @return bool|mixed
	 */
	public function delHtOpsInfoById($id){
		$id = intval($id);
		if($id > 0){
			$info = $this->getHtOpsInfoById($id);
			if(empty($info)){
				return false;
			}
			$price = $info["price"];
			$hid = $info["hid"];
			$bianhao = $info["bianhao"];
			$ret = $this->delInfoById($id);
			if($ret){
				D("hetong","Logic")->reduceSumJineByIdBH($hid,$bianhao,$price);
			}
			return $ret;
		}
		else{
			return false;
		}
	}
	/**
	 * 删除相关项目
	 * @param $id
	 * @return bool|mixed
	 */
	public function delInfoById($id){
		if(intval($id) > 0){
			return M("htops")->where("id=".$id)->delete();
		}
		else{
			return false;
		}
	}
}