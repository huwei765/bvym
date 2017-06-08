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

class HetongLogic extends Model{

	/**
	 * 检测合同是否收款完成
	 * @param $jhid
	 * @return bool
	 */
	public function checkShouIsOver($jhid){
		$ret = false;
		$data = $this->getHetongInfoById($jhid);
		if(!empty($data)){
			if(intval($data["status"]) == 1){
				$ret = true;
			}
			else if(intval($data["yishou"]) - intval($data["jine"]) >= 0 || intval($data["weishou"]) <= 0){
				//强制收款完成
				$this->changeHtStatus($jhid);
				$ret = true;
			}
		}
		return $ret;
	}

	/**
	 * 修改已收和未收款逻辑
	 * @param $jhid
	 * @param $jine
	 */
	public function modifyJineById($jhid,$jine){
		M("hetong")->where('id='.$jhid)->setInc('yishou',$jine);
		M("hetong")->where('id='.$jhid)->setDec('weishou',$jine);
		//查询订单是否完成
		$this->changeHtStatus($jhid);
	}

	/**
	 * 检查合同是否完成，若完成就改变状态到完成状态
	 * @param $jhid
	 */
	public function changeHtStatus($jhid){
		$info = $this->getHetongInfoById($jhid);
		if(!empty($info) && isset($info["weishou"])){
			if(intval($info["yishou"]) - intval($info["jine"]) >= 0 || intval($info["weishou"]) <= 0){
				M("hetong")->where('id='.$jhid)->setField(array("status"=>1));
			}
			else{
				M("hetong")->where('id='.$jhid)->setField(array("status"=>0));
			}
		}
	}

	/**
	 * 修改总金额减少和利润基数减少
	 * @param $jhid
	 * @param $jine
	 */
	public function reduceSumJineByIdBH($jhid,$bianhao,$jine){
		M("hetong")->where(array("id"=>$jhid,"bianhao"=>$bianhao))->setDec('jine',$jine);
		M("hetong")->where(array("id"=>$jhid,"bianhao"=>$bianhao))->setDec('profit',$jine);
	}

	/**
	 * 收款完成后的执行逻辑
	 * 完成两个动作：1.修改合同状态为收款完成  2.增加推广提成
	 */
	public function doShouOver($jhid){
		//检测合同是否收款完成
		$data = $this->getHetongInfoById($jhid);
		if(empty($data)){
			return false;
		}
		if(intval($data["yishou"]) - intval($data["jine"]) != 0 || intval($data["weishou"]) != 0){
			return false;
		}
		//根据客户id查询客户信息
		$custcon_info = D("custcon","Logic")->getCustconInfoById($data["cuid"],"jcid,jcname,level");
		if(empty($custcon_info)){
			return false;
		}
		$HetongModel = M("hetong");
		//开启事务处理
		$HetongModel->startTrans();
		//改变收款合同状态
		$ret1 = $HetongModel->where("id=".$jhid)->save(array("status"=>1));
		//增加机构推广提成
		$tmpdata["jhid"] = $data["id"];//合同id
		$tmpdata["jhname"] = $data["title"];//合同名称
		$tmpdata["jhcode"] = $data["bianhao"];//合同编号
		$tmpdata["jine"] = $data["jine"];//合同金额
		$tmpdata["base_jine"] = $data["base_jine"];//合同成本
		$tmpdata["profit"] = $data["profit"];//提成利润基数
		$tmpdata["cuid"] = $data["cuid"];//客户id
		$tmpdata["cuname"] = $data["cuname"];//客户姓名

		$tmpdata["jcid"] = $custcon_info["jcid"];//机构id
		$tmpdata["jcname"] = $custcon_info["jcname"];//机构名称
		$tmpdata["level"] = $custcon_info["level"];//机构推广层级

		//计算利润提成
		$cusProfit_Logic = D("cusprofit","Logic");
		$cusProfit_data = $cusProfit_Logic->calculateProfitBySpread($tmpdata["level"],$tmpdata["profit"]);
		$tmpdata["commission"] = $cusProfit_data["profit"];//提成额度
		$tmpdata["rate"] = $cusProfit_data["rate"];//提成比例
		$tmpdata["beizhu"] = $cusProfit_data["formula"];//提成说明
		//新增推广提成记录
		$ret2 = $cusProfit_Logic->addProfit($tmpdata);

		//增加微信用户推广提成
		//

		if($ret1 && $ret2){
			$HetongModel->commit();
			return true;
		}
		else{
			$HetongModel->rollback();
			return false;
		}
	}

	/**
	 * 通过id来查询单个记录的基本逻辑
	 * @param $jhid
	 * @param string $field
	 * @return mixed
	 */
	public function getHetongInfoById($jhid,$field="*"){
		return $this->getHetongInfo(array("id"=>$jhid),$field);
	}

	/**
	 * 查询单个记录的基本逻辑
	 * @param $condition
	 * @param string $field
	 * @return mixed
	 */
	public function getHetongInfo($condition,$field="*"){
		return M("hetong")->field($field)->where($condition)->find();
	}

	/**
	 * 根据客户ID查询订单列表
	 * @param $cuid
	 * @param string $order
	 * @param string $field
	 * @return mixed
	 */
	public function getListByCUid($cuid,$order="id desc",$field="*"){
		return $this->getList(array("cuid"=>$cuid),$order,$field);
	}

	/**
	 * 获取列表
	 * @param $condition
	 * @param string $order
	 * @param string $field
	 * @return mixed
	 */
	public function getList($condition,$order="id desc",$field="*"){
		return M("hetong")->field($field)->where($condition)->order($order)->select();
	}

}