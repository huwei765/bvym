<?php

/**
 *      开票记录控制器
 *      [X-Mis] (C)2007-2099  
 *      This is NOT a freeware, use is subject to license terms
 *      http://www.xinyou88.com
 *      tel:400-000-9981
 *      qq:16129825
 */

namespace Home\Controller;
use Think\Controller;

class PiaoController extends CommonController{

   public function _initialize() {
        parent::_initialize();
        $this->dbname = CONTROLLER_NAME;
    }
	
   function _filter(&$map) {
	   if(!in_array(session('uid'),C('ADMINISTRATOR'))){
	    $map[]=array("uid"=>array('EQ', session("uid")),"juid"=>array('like','%'.session("uid").'%'),"_logic"=>"or");
	   }

	}
	
   public function _befor_index(){ 
   
   }
  
  
  public function _befor_add(){
	  //自动填充合同
	  $hid = I("get.hid");
	  if(!empty($hid) && intval($hid) > 0){
		  //查询订单信息
		  $hetong_info = D("hetong","Logic")->getHetongInfoById($hid);
		  if(!empty($hetong_info)){
			  //查询订单商品信息
			  $ops_list = D("htops","Logic")->getHtOpsListByHid($hetong_info["id"]);
			  $this->assign('ht_rs', $hetong_info);
			  $this->assign('ops_list', $ops_list);
		  }
	  }
	  //自动生成单据编号
	  $piaoSn = $this->generateHtSn("",3);
	  $this->assign('bianhao', $piaoSn);
	  $attid=time();
	  $this->assign('attid',$attid);
    
  }
	
   public function _after_add($id){
    
   }

  public function _befor_insert($data){
	M("hetong")->where('id='.I("jhid"))->setInc('yikai',I("jine"));
	$data['addm']=date("Y-m",time());
	return $data;
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
     
   }

   public function _befor_del($id){
	  
   }

   public function outxls() {
		$model = D($this->dbname);
		$map = $this->_search();
		if (method_exists($this, '_filter')) {
			$this->_filter($map);
		}
		$list = $model->where($map)->field('id,jhname,title,jine,bianhao,beizhu,juname,uname,addtime')->select();
	    $headArr=array('ID','关联合同','开票抬头','开票金额','开票编号','备注','经办人','添加人','添加时间');
	    $filename='开票记录';
		$this->xlsout($filename,$headArr,$list);
	}
	
	public function fenxi(){
	 $this->display();
	}

	/**
	 * 查询开票记录
	 */
	public function getlist(){
		$hid = I("get.hid");
		if(isset($hid) && is_numeric($hid) && intval($hid) > 0){
			//根据hid查询订单开票情况
			$list = D("piao","Logic")->getListByHid($hid);
			$this->assign('list',$list);
		}
		$this->display("list");
	}



}