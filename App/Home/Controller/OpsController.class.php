<?php

namespace Home\Controller;
use Think\Controller;

class OpsController extends CommonController{

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
  
  public function _befor_add(){
      $list=D("opscate","Logic")->getAllCateList();
      $this->assign('list',$list);
  }
	
   public function _after_add($id){
    
   }

  public function _befor_insert($data){
      $cid = intval(I('cid'));
      if ($cid==0){
          $data['cid']=0;
          $data['cname']="";
      }else{
          $cate_info=D("opscate","Logic")->getCateInfoById($cid);
          $data['cid']=$cid;
          $data['cname']=$cate_info["title"];
      }
      return $data;
  }
  
  public function _befor_edit(){
      $list=D("opscate","Logic")->getAllCateList();
      $this->assign('list',$list);
  }
   
  public function _befor_update($data){
      $cid = intval(I('cid'));
      if ($cid==0){
          $data['cid']=0;
          $data['cname']="";
      }else{
          $cate_info=D("opscate","Logic")->getCateInfoById($cid);
          $data['cid']=$cid;
          $data['cname']=$cate_info["title"];
      }
      return $data;
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