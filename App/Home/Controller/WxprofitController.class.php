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

class WxprofitController extends CommonController{

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
	 * 已审核未支付
	 */
	public function no_pay(){
		$model = D($this->dbname,'Logic');
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
}