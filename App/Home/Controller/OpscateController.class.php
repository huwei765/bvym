<?php

namespace Home\Controller;
use Think\Controller;

class OpscateController extends CommonController{

   public function _initialize() {
        parent::_initialize();
        $this->dbname = CONTROLLER_NAME;
    }
	
   function _filter(&$map) {
	    if(!in_array(session('uid'),C('ADMINISTRATOR'))){
	    $map[]=array("uid"=>array('EQ', session("uid")),"juid"=>array('like','%'.session("uid").'%'),"_logic"=>"or");
	   }
        if(IS_POST&&isset($_REQUEST['time1']) && $_REQUEST['time1'] != ''&&isset($_REQUEST['time2']) && $_REQUEST['time2'] != ''){
		 $map['addtime'] =array(array('egt',I('time1')),array('elt',I('time2'))) ;
		}

	}
	
   public function _befor_index(){ 
   
   }
  
  
  public function _befor_add(){
	  $attid=time();
	  $this->assign('attid',$attid);
    
  }
	
   public function _after_add($id){
    
   }

  public function _befor_insert($data){
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
		$list = $model->where($map)->field('id,fenlei,title,xcrq,xingming,phone,qq,type,uname,addtime,updatetime')->select();
	    $headArr=array('ID','进展','公司名称','下次联系','联系人','手机号码','QQ','客户来源','添加人','添加时间','更新时间');
	    $filename='客户管理';
		$this->xlsout($filename,$headArr,$list);
	}

}