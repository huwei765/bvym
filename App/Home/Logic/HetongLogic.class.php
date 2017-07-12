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
	 * 新增订单后开始计算提成信息
	 * @param $jhid
	 * @return bool
	 */
	public function afterOrderAdd($jhid){
		$data = $this->getHetongInfoById($jhid);
		if(empty($data)){
			return false;
		}
		if(intval($data["yishou"])!= 0){
			return false;
		}
		//查询该笔订单是否已经生成提成明细
		$count = D("cusprofit","Logic")->GetCount(array("jhid"=>$data["id"]));
		if(intval($count) > 0){
			return false;
		}
		//根据客户id查询客户信息
		$custcon_info = D("custcon","Logic")->getCustconInfoById($data["cuid"],"jcid,jcname,level");
		if(empty($custcon_info)){
			return false;
		}
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

		return D("cusprofit","Logic")->doOrderProfit($tmpdata);
	}

	/**
	 * 检查即将要收的款是否正常
	 * @param $jhid
	 * @param $shou
	 * @return bool
	 */
	public function checkShouValid($jhid,$shou){
		$ret = true;
		$data = $this->getHetongInfoById($jhid);
		if(!empty($data)){
			if(intval($data["status"]) == 1){
				$ret = false;
			}
			else if(intval($data["yishou"]) - intval($data["jine"]) >= 0 || intval($data["weishou"]) <= 0){
				$ret = false;
			}
			else if(intval($data["weishou"]) - intval($shou) < 0 || intval($data["yishou"]) + intval($shou) > intval($data["jine"])){
				$ret = false;
			}
		}
		else{
			$ret = false;
		}
		return $ret;
	}

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
		return true;
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
	 * 根据返现状态查询待审核的订单
	 * @param $condition
	 * @param string $field
	 * @param string $order
	 * @return mixed
	 */
	public function getListByFuStatusForNoVerify($condition = array(),$field="*",$order="id desc"){
		$condition["fstatus"] = 0;
		return $this->getList($condition,$order,$field);
	}

	/**
	 * 查询待付款订单
	 * @param $condition
	 * @param string $field
	 * @param string $order
	 * @return mixed
	 */
	public function getListByFuStatusForNoPay($condition = array(),$field="*",$order="id desc"){
		$condition["fstatus"] = 1;
		return $this->getList($condition,$order,$field);
	}

	/**
	 * 查询完成订单
	 * @param $condition
	 * @param string $field
	 * @param string $order
	 * @return mixed
	 */
	public function getListByFuStatusForOver($condition = array(),$field="*",$order="id desc"){
		$condition["fstatus"] = 2;
		return $this->getList($condition,$order,$field);
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

	/**
	 * 按月度统计订单的收支财务
	 * @return array
	 */
	public function reportMoneyByMonth(){
		$records = array();
		$cur_month = date("m",time());
		for($i=1;$i<=12;$i++){
			if($i - intval($cur_month) > 0){
				$records[] = array(
					"month" => $i,
					"data" => array(
						"num" => "-",
						"jine" => "-",
						"profit" => "-",
						"yishou" => "-",
						"weishou" => "-",
						"cnum" => "-",
						"commission" => "-",
						"yifu" => "-"
					)
				);
			}
			else{
				$records[] = array("month"=>$i,"data"=>$this->reportMoneyByOneMonth($i));
			}
		}
		return $records;
	}

	/**
	 * 统计指定月份的订单财务收支
	 * @param $index
	 * @return array|mixed
	 */
	public function reportMoneyByOneMonth($index){
		//统计指定月份的金额
		$BeginDate = date("Y-0".$index."-01");//获取指定月份的第一天
		$firstDay = strtotime($BeginDate);//指定月的第一天
		$endDay = strtotime("$BeginDate +1 month -1 day");
		$map["addtime"] = array(array('egt',$firstDay),array('elt',$endDay));
		$result = M("hetong")->field("count(id) as 'num',sum(jine) as 'jine',sum(profit) as 'profit',sum(yishou) as 'yishou',sum(weishou) as 'weishou'")->where($map)->find();
		$cusprofit_data = D("cusprofit","Logic")->reportMoneyByInterval($firstDay,$endDay);
		if(!empty($cusprofit_data)){
			$result = array_merge($result,$cusprofit_data);
		}
		return $result;
	}
}