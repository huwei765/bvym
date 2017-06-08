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

class CongdController extends CommonController{

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
      $uid = I("get.uid");
      if(isset($uid) && is_numeric($uid) && intval($uid) > 0){
          //查询客户信息
          $custcon = D("custcon","Logic")->getCustconInfoById($uid,"id,xingming,phone");
          if(!empty($custcon)){
              $this->assign('custom_info', $custcon);
          }
      }
  }
	
   public function _after_add($id){
   }

  public function _befor_insert($data){
  }
  
  public function _befor_edit(){
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
		$list = $model->where($map)->field('id,jcname,type,fenlei,xcrq,uname,addtime,updatetime')->select();
	    $headArr=array('ID','分享给','跟单方式','进展阶段','下次联系','跟单人','跟单时间','更新时间');
	    $filename='跟单记录';
		$this->xlsout($filename,$headArr,$list);
	}

    /**
     * 根据客户ID查询客户的跟单记录
     */
    public function getlist(){
        $cuid = I("get.cuid");
        if(isset($cuid) && is_numeric($cuid) && intval($cuid) > 0){
            //根据cuid查询
            $list = D("congd","Logic")->getListByCUid($cuid);
            $this->assign('list',$list);
        }
        $this->display("list");
    }

}