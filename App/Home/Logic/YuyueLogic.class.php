<?php

namespace Home\Logic;
use Think\Model;

class YuyueLogic extends Model{


    public function getCountByCName($cuname,$field="*",$order="id desc"){
	    $BeginDate = date("Y-m-d");
    	$firstDay = strtotime($BeginDate);
    	$endDay = strtotime("$BeginDate +1 month -1 day");
	    $map["cuname"] = $cuname;
	    $map["addtime"] = array(array('egt',$firstDay),array('elt',$endDay));
		return $this->getCount($map,$field,$order);
	}

	public function getCountByPhone($phone,$field="*",$order="id desc"){
	    $BeginDate = date("Y-m-d");
    	$firstDay = strtotime($BeginDate);
    	$endDay = strtotime("$BeginDate +1 month -1 day");
	    $map["phone"] = $phone;
	    $map["addtime"] = array(array('egt',$firstDay),array('elt',$endDay));
		return $this->getCount($map,$field,$order);
	}

	public function getCount($condition,$field="*",$order="id desc"){
    	return M("yuyue")->where($condition)->count("id");
    }

	/**
	 * 根据id查询客户信息的基本逻辑
	 * @param $juid
	 * @param string $field
	 * @return Model
	 */
	public function getCustInfoById($juid,$field="*"){
		return $this->getCustInfo(array("id"=>$juid),$field);
	}

	/**
	 * 查询渠道信息的基本逻辑
	 * @param $condition
	 * @param string $field
	 * @return Model
	 */
	public function getCustInfo($condition,$field="*"){
		return M("cust")->field($field)->where($condition)->find();
	}



	/**
	 * 按代理商统计财务
	 * @return array
	 */
	public function reportMoneyByAgent(){
		$reportArray = array();
		//查询所有的代理商
		$agentList = $this->getList(array(),"id,title,xingming");
		foreach($agentList as $key=>$val){
			$reportArray[$key]["agent"] = $val;
			$tmpData = D("hetong","Logic")->reportMoneyByYearAndAgent($val["id"]);
			$reportArray[$key]["data"] = $tmpData;
		}
		return $reportArray;
	}


}