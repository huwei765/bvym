<?php

namespace Home\Controller;
use Think\Controller;

class OpsrecordController extends CommonController{

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
  }
	
   public function _after_add($id){
    
   }

  public function _befor_insert($data){
      return $data;
  }
  
  public function _befor_edit(){
      $id = I("get.id");
      if(isset($id) && is_numeric($id) && intval($id) > 0){
          //查询订单id
          $record_info = M("opsrecord")->where('id='.$id)->field("jhid")->find();
          if(!empty($record_info)){
              $hid = $record_info["jhid"];
              //查询订单信息
              $hetong_info = D("hetong","Logic")->getHetongInfoById($hid);
              if(!empty($hetong_info)){
                  //查询订单商品信息
                  $ops_list = D("htops","Logic")->getHtOpsListByHid($hetong_info["id"]);
                  $this->assign('ht_rs', $hetong_info);
                  $this->assign('ops_list', $ops_list);
              }
          }
      }
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

    public function validatebyname(){
        $title = I("param.title");
        if(isset($title) && $title != ""){
            $info = D("ops","Logic")->getInfoByTit($title);
            if(!empty($info)){
                echo "项目有重复";
            }
            else{
                echo "";
            }
        }
        else{
            echo "";
        }
    }

    public function getlist(){
        $cuid = I("get.cuid");
        if(isset($cuid) && is_numeric($cuid) && intval($cuid) > 0){
            //根据cuid查询
            $list = D("opsrecord","Logic")->getListByCUid($cuid);
            $this->assign('list',$list);
        }
        $this->display("list");
    }
    public function getlistbyhid(){
        $hid = I("get.hid");
        if(isset($hid) && is_numeric($hid) && intval($hid) > 0){
            $list = D("opsrecord","Logic")->getListByHid($hid);
            $this->assign('list',$list);
        }
        $this->display("list2");
    }

}