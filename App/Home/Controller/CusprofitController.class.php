<?php

/**
 *      跟单记录控制器
 *      [X-Mis] (C)2007-2099  
 *      This is NOT a freeware, use is subject to license terms
 *      http://www.xinyou88.com
 *      tel:400-000-9981
 *      qq:16129825
 */

namespace Home\Controller;
use Think\Controller;

class CusprofitController extends CommonController{

   public function _initialize() {
        parent::_initialize();
        $this->dbname = CONTROLLER_NAME;
    }
	
   function _filter(&$map) {
	   if(!in_array(session('uid'),C('ADMINISTRATOR'))){
	    $map[]=array("uid"=>array('EQ', session("uid")));
	   }

	}
	
   public function _befor_index(){
   }

	public function add(){
		if(IS_POST){
			//判断时间是否到了晚上12点
			$BeginTime = strtotime(date("Y-m-d 23:01:00"));//获取指定月份的第一天
			$endTime = $BeginTime + 1 * 60 * 60;
			$curTime = time();
			if($curTime > $endTime || $curTime < $BeginTime){
				$this->mtReturn(200,'xx',$_REQUEST['navTabId'],true);
			}
			//开始自动生成提成明细
		}
		$this->display();
	}
  
  
  public function _befor_add(){
	  if(I("get.cid")!==""){
		$jcid=I("get.cid");
	    $jcname=comname($jcid);
	    $this->assign('jcid',$jcid);
	    $this->assign('jcname',$jcname);  
	  }
	  $attid=time();
	  $this->assign('attid',$attid);
    
  }
	
   public function _after_add($id){
    $upda["id"]=I("jcid");
	$upda["fenlei"]=I("fenlei");
	$upda['xcrq']=I("xcrq");
	$upda['updatetime']=date("Y-m-d H:i:s",time());
	 M("cust")->data($upda)->save();
	
   }

  public function _befor_insert($data){
	 //$data['addtime']=date("Y-m-d H:i:s",time());
	// return $data;
  }
  
  public function _befor_edit(){
     $model = D($this->dbname);
	 $info = $model->find(I('get.id'));
	 $attid=$info['attid'];
	 $this->assign('attid',$attid);
  }
   
  public function _befor_update($data){

  }
	public function _after_edit($id){
     $upda["id"]=I("jcid");
	 $upda["fenlei"]=I("fenlei");
	 $upda['xcrq']=I("xcrq");
	 $upda['updatetime']=date("Y-m-d H:i:s",time());
	 M("cust")->data($upda)->save();
   }

   public function _befor_del($id){
	  
   }

   public function outxls() {
		$model = D($this->dbname);
		$map = $this->_search();
		if (method_exists($this, '_filter')) {
			$this->_filter($map);
		}
		$list = $model->where($map)->field('id,jcname,type,fenlei,xcrq,uname,addtime,updatetime')->select();
	    $headArr=array('ID','分享给','跟单方式','进展阶段','下次联系','跟单人','跟单时间','更新时间');
	    $filename='跟单记录';
		$this->xlsout($filename,$headArr,$list);
	}


	/**
	 * 待审核的提成
	 */
	public function no_verify(){
		$model = D($this->dbname,'Logic');
		$list = $model->GetNoVerifyProfitList();
		$this->assign('list', $list);
		$this->display("index_no_verify");
	}

	/**
	 * 待确认
	 */
	public function no_confirm(){
		$model = D("cusprofit",'Logic');
		$list = $model->GetNoConfirmProfitList();
		$this->assign('list', $list);
		$this->display("index_no_confirm");
	}

	/**
	 * 已审核未支付
	 */
	public function no_pay(){
		$model = D("cusprofit",'Logic');
		$list = $model->GetNoPayProfitList();
		$this->assign('list', $list);
		$this->display("index_no_pay");
	}

	/**
	 * 已完成
	 */
	public function over(){
		$model = D($this->dbname,'Logic');
		$list = $model->GetOverProfitList();
		$this->assign('list', $list);
		$this->display("index_over");
	}

	/**
	 * 审核失败
	 */
	public function fail(){
		$model = D($this->dbname,'Logic');
		$list = $model->GetFailProfitList();
		$this->assign('list', $list);
		$this->display("index_fail");
	}

	/**
	 * 审核
	 */
	public function verify(){
		if(IS_POST){
			$data=I('post.');
			if(isset($data["id"]) && is_numeric($data["id"]) && intval($data["id"]) > 0){
				//更新
				D("cusprofit","Logic")->updateStatusById($data["id"],$data["status"]);
			}
			$this->mtReturn(200,"保存成功",$_REQUEST['navTabId'],true);
		}
		else{
			$id = $_REQUEST ["id"];
			//查询订单佣金记录
			$profitData = D("cusprofit","Logic")->getInfoById($id);
			if(!empty($profitData)){
				//查询订单明细项目
				$ops_list = D("htops","Logic")->getHtOpsListByHid($profitData["jhid"]);
				$this->assign('ht_rs', $profitData);
				$this->assign('ops_list', $ops_list);
			}
			$this->assign('id',$id);
		}
		$this->display();
	}

	/**
	 * 确认
	 */
	public function confirm(){
		if(IS_POST){
			$data=I('post.');
			if(isset($data["id"]) && is_numeric($data["id"]) && intval($data["id"]) > 0){
				//更新
				D("cusprofit","Logic")->updateStatusById($data["id"],$data["status"]);
			}
			$this->mtReturn(200,"保存成功",$_REQUEST['navTabId'],true);
		}
		else{
			$id = $_REQUEST ["id"];
			//查询订单佣金记录
			$profitData = D("cusprofit","Logic")->getInfoById($id);
			if(!empty($profitData)){
				//查询订单明细项目
				$ops_list = D("htops","Logic")->getHtOpsListByHid($profitData["jhid"]);
				$this->assign('ht_rs', $profitData);
				$this->assign('ops_list', $ops_list);
			}
			$this->assign('id',$id);
		}
		$this->display();
	}

	/**
	 * 付款
	 */
	public function pay(){
		if(IS_POST){
			$data=I('post.');
			//参数检测
			if(!isset($data["id"]) || !is_numeric($data["id"])){
				$this->mtReturn(300,"操作失败,参数错误！",$_REQUEST['navTabId'],true);
			}
			if(!isset($data["jine"]) || !is_numeric($data["jine"])){
				$this->mtReturn(300,"操作失败,金额数据不符合！",$_REQUEST['navTabId'],true);
			}
			if(intval($data["jine"]) < 0 || intval($data["jine"]) > 10000000){
				$this->mtReturn(300,"操作失败,单笔支付额度必须在0到10000000之内！",$_REQUEST['navTabId'],true);
			}
			$tmpData["profit_id"] = $data["id"];
			$tmpData["jine"] = $data["jine"];
			$tmpData["bianhao"] = $data["bianhao"];
			$tmpData["type"] = $data["type"];
			$tmpData["beizhu"] = $data["beizhu"];
			$ret = D("fu","Logic")->doFuForAgentByProfit($tmpData);
			if($ret){
				$this->mtReturn(200,"新增成功",$_REQUEST['navTabId'],true);
			}
			else{
				$this->mtReturn(300,"操作失败！",$_REQUEST['navTabId'],true);
			}
		}
		else{
			$id = $_REQUEST ["id"];
			//查询订单佣金记录
			$profitData = D("cusprofit","Logic")->getInfoById($id);
			if(!empty($profitData)){
				//查询订单明细项目
				$ops_list = D("htops","Logic")->getHtOpsListByHid($profitData["jhid"]);
				$this->assign('ht_rs', $profitData);
				$this->assign('ops_list', $ops_list);
			}
			$this->assign('id',$id);
			//自动生成单据编号
			$fuSn = $this->generateHtSn("",3);
			$this->assign('bianhao', $fuSn);
		}
		$this->display();
	}
}