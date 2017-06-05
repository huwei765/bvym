<?php

/**
 *      收款记录控制器
 *      [X-Mis] (C)2007-2099  
 *      This is NOT a freeware, use is subject to license terms
 *      http://www.xinyou88.com
 *      tel:400-000-9981
 *      qq:16129825
 */

namespace Home\Controller;
use Think\Controller;

class ShouController extends CommonController{

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
	  $shouSn = $this->generateHtSn("",1);
	  $this->assign('bianhao', $shouSn);

	  $attid=time();
	  $this->assign('attid',$attid);
    
  }
	
   public function _after_add($id){
	   if(IS_POST){
		   $skip = array(
			   "url"=>"/index.php?m=Home&c=piao&a=add&navTabId=piao",
			   "title"=>"开发票",
			   "height"=>"500",
			   "width"=>"900",
			   "forwardConfirm"=>"要开发票吗？",
			   "type"=>"dialog"
		   );
		   $this->mtReturn(200,"新增成功",$_REQUEST['navTabId'],true,$skip);
	   }
   }

  public function _befor_insert($data){
	  D("shou","Logic")->modifyHtJineByShou(I("jhid"),I("jine"));
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
		if(IS_POST){
			$skip = array(
				"url"=>"/index.php?m=Home&c=piao&a=add&navTabId=piao",
				"title"=>"开发票",
				"height"=>"500",
				"width"=>"900",
				"forwardConfirm"=>"要开发票吗？",
				"type"=>"dialog"
			);
			$this->mtReturn(200,"新增成功",$_REQUEST['navTabId'],true,$skip);
		}
   }

   public function _befor_del($id){
	  
   }

   public function outxls() {
		$model = D($this->dbname);
		$map = $this->_search();
		if (method_exists($this, '_filter')) {
			$this->_filter($map);
		}
		$list = $model->where($map)->field('id,jhname,bianhao,type,jine,juname,beizhu,uname,addtime')->select();
	    $headArr=array('ID','关联合同','单据编号','收款方式','收款金额','经办人','备注','添加人','添加时间');
	    $filename='收款记录';
		$this->xlsout($filename,$headArr,$list);
	}
	
	public function fenxi(){
	 $this->display();
	}
	
	

}