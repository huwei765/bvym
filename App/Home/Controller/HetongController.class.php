<?php

/**
 *      合同管理控制器
 *      [X-Mis] (C)2007-2099  
 *      This is NOT a freeware, use is subject to license terms
 *      http://www.xinyou88.com
 *      tel:400-000-9981
 *      qq:16129825
 */

namespace Home\Controller;
use Think\Controller;

class HetongController extends CommonController{

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

	/**
	 * 新增订单
	 */
	public function add(){
		if(IS_POST){
			$model = D($this->dbname);
			$bianhao=I('post.bianhao');

			if (false === $data = $model->create()) {
				$this->mtReturn(300,'失败，'.$model->getError(),$_REQUEST['navTabId'],true);
			}
			if($model->add($data)){
				$id = $model->getLastInsID();
				//新增相关项目
				for($i = 0;$i<10;$i++){
					$tmp_data = array("hid"=>$id,"bianhao"=>$bianhao);
					$tmp_data["oid"] = I("post.ops".$i."_oid");
					$tmp_data["oname"] = I("post.ops".$i."_oname");
					$tmp_data["ocname"] = I("post.ops".$i."_ocname");
					$tmp_data["price"] = I("post.ops".$i."_oprice");
					$tmp_data["num"] = I("post.ops".$i."_num");
					$tmp_data["money"] = I("post.ops".$i."_sumprice");
					if(intval($tmp_data["oid"]) > 0 && intval($tmp_data["num"]) > 0){
						D("htops","Logic")->addHtOpsInfo($tmp_data);
					}
				}
				$skip = array(
					"url"=>"/index.php?m=Home&c=shou&a=add&navTabId=shou&hid=".$id,
					"title"=>"新增收款",
					"height"=>"500",
					"width"=>"900",
					"forwardConfirm"=>"去收款吗？",
					"type"=>"dialog"
				);
				$this->mtReturn(200,"新增成功",$_REQUEST['navTabId'],true,$skip);
			}
		}
		else{
			//若带有客户信息则自动查询客户信息
			$cuid = I("get.cuid");
			if(!empty($cuid) && intval($cuid) > 0){
				//查询客户信息
				$custcon_data = D("custcon","Logic")->getCustconInfoById($cuid);
				if(!empty($custcon_data)){
					$this->assign('cuid', $custcon_data["id"]);
					$this->assign('cuname', $custcon_data["xingming"]);
					$this->assign('dianhua', $custcon_data["phone"]);
				}
			}
			//自动生成订单编号
			$orderSn = $this->generateHtSn($cuid);
			$this->assign('bianhao', $orderSn);
		}
		$this->display();
	}

	/**
	 * 修改订单
	 */
	public function edit(){
		if(IS_POST){
			$data=I('post.');
			$model = D($this->dbname);
			if (false === $data = $model->create()) {
				$this->mtReturn(300,'失败，'.$model->getError(),$_REQUEST['navTabId'],true);
			}
			if($model->save($data)){
			}
			$this->mtReturn(200,"编辑成功",$_REQUEST['navTabId'],true);
		}
		$id = I("param.id");
		$hetong_data = D("hetong","Logic")->getHetongInfoById($id);
		if(!empty($hetong_data) && isset($hetong_data["id"])){
			$ops_list = D("htops","Logic")->getHtOpsListByHid($hetong_data["id"]);
			$this->assign('ops_list', $ops_list);
		}
		$this->assign('Rs', $hetong_data);
		$this->assign('id',$id);
		$this->display();
	}

	/**
	 * 订单项目单个保存
	 */
	public function edit_ops(){
		if(IS_POST){
			$hid = I("get.hid");
			$bianhao = I("get.bianhao");
			$ops_no = I("post.ops_no");
			if(is_numeric($hid) && is_numeric($ops_no) && strlen($bianhao) > 6 && strlen($bianhao) < 20){
				$ops_index = intval($ops_no) -1;
				$data["hid"] = $hid;
				$data["bianhao"] = $bianhao;
				$data["oname"] = I("post.ops".$ops_index."_oname");
				$data["oid"] = I("post.ops".$ops_index."_oid");
				$data["ocname"] = I("post.ops".$ops_index."_ocname");
				$data["price"] = I("post.ops".$ops_index."_oprice");
				$data["num"] = I("post.ops".$ops_index."_num");
				$data["money"] = I("post.ops".$ops_index."_sumprice");
				$data["id"] = I("post.ops".$ops_index."_id");
				$ret = D("htops","Logic")->saveInfo($data);
				if(intval($ret) > 0){
					$this->mtReturn(200,"编辑成功",$_REQUEST['navTabId'],true);
				}
				else{
					$this->mtReturn(300,'编辑失败',$_REQUEST['navTabId'],true);
				}
			}
			else{
				$this->mtReturn(300,'编辑失败,订单信息错误',$_REQUEST['navTabId'],true);
			}
		}
		else{
			$this->mtReturn(300,'不好意思，流程错误',$_REQUEST['navTabId'],true);
		}
	}

	/**
	 * 删除相关项目
	 */
	public function ops_del(){
		if(IS_POST) {
			$hid = I("get.hid");
			$bianhao = I("get.bianhao");
			$ht_id = I("get.id");
			if (is_numeric($ht_id) && is_numeric($hid) && strlen($bianhao) > 6 && strlen($bianhao) < 20) {
				if ($ht_id > 0) {
					$ret = D("htops", "Logic")->delHtOpsInfoById($ht_id);
					if ($ret) {
						$this->mtReturn(200, '删除成功', $_REQUEST['navTabId'], false);
					} else {
						$this->mtReturn(300, '删除失败', $_REQUEST['navTabId'], false);
					}
				} else {
					$this->mtReturn(300, '参数错误', $_REQUEST['navTabId'], false);
				}
			}
		}
	}
  
  
  public function _befor_add(){
	  $attid=time();
	  $this->assign('attid',$attid);
    
  }

   public function _after_add($id){
   }

  public function _befor_insert($data){
	$data['addm']=date("Y-m",time());
	$data['weishou']=I('jine');
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
				"url"=>"/index.php?m=Home&c=shou&a=add&navTabId=shou",
				"title"=>"新增收款",
				"height"=>"500",
				"width"=>"900",
				"forwardConfirm"=>"去收款吗？",
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
		$list = $model->where($map)->field('id,title,addtime,jcname,yikai,weishou,jine,yishou,fukuan,name,uname,dqrq,updatetime')->select();
	    $headArr=array('ID','合同名称','签约日期','客户名称','已开票','未收款','合同金额','已收款','已付款','业务员','添加人','到期日期','更新时间');
	    $filename='合同管理';
		$this->xlsout($filename,$headArr,$list);
	}
	
	public function daoqi() {
		$model = D($this->dbname);
		$map = $this->_search();
		if (method_exists($this, '_filter')) {
			$this->_filter($map);
		}
		$map['dqrq']  =  array(array('egt',date("Y-m-d",strtotime("-2 month"))),array('elt',date("Y-m-d",strtotime("+1 month"))));
		$list = $model->where($map)->select();
	    $this->assign('list', $list);
		$this->display("index");
	}
	
	public function fenxi(){
	 $this->display();
	}
	


public function name(){
	$user=I('get.user');
	import("Org.Util.Chart");
    $chart = new \Chart;
	for($i=1;$i<=12;$i++){ 	
			$info=$info.",".$i;
			if($i<10){
			$co =M($this->dbname)->where(array('name'=>$user))->where(array('addm'=>date("Y",time())."-0".$i))->SUM('jine');
			}else{
			$co =M($this->dbname)->where(array('name'=>$user))->where(array('addm'=>date("Y",time())."-".$i))->SUM('jine');
			}
			$count=$count.",".$co;
		}
    $title = $user.date("Y",time()).'年合同总金额'; 
    $data = explode(",", substr ($count, 1)); 
    $size = 140; 
    $width = 750; 
    $height = 300; 
    $legend = explode(",", substr ($info, 1));
    ob_end_clean();
    $chart->createcolumnar($title,$data,$size,$height,$width,$legend);
	}

	public function names(){
	$user=I('get.user');
	import("Org.Util.Chart");
    $chart = new \Chart;
	for($i=1;$i<=12;$i++){ 	
			$info=$info.",".$i;
			if($i<10){
			$co =M($this->dbname)->where(array('name'=>$user))->where(array('addm'=>date("Y",time())."-0".$i))->SUM('yishou');
			}else{
			$co =M($this->dbname)->where(array('name'=>$user))->where(array('addm'=>date("Y",time())."-".$i))->SUM('yishou');
			}
			$count=$count.",".$co;
		}
    $title = $user.date("Y",time()).'年合同已收款'; 
    $data = explode(",", substr ($count, 1)); 
    $size = 140; 
    $width = 750; 
    $height = 300; 
    $legend = explode(",", substr ($info, 1));
    ob_end_clean();
    $chart->createcolumnar($title,$data,$size,$height,$width,$legend);
	}
	
	public function namew(){
	$user=I('get.user');
	import("Org.Util.Chart");
    $chart = new \Chart;
	for($i=1;$i<=12;$i++){ 	
			$info=$info.",".$i;
			if($i<10){
			$co =M($this->dbname)->where(array('name'=>$user))->where(array('addm'=>date("Y",time())."-0".$i))->SUM('weishou');
			}else{
			$co =M($this->dbname)->where(array('name'=>$user))->where(array('addm'=>date("Y",time())."-".$i))->SUM('weishou');
			}
			$count=$count.",".$co;
		}
    $title = $user.date("Y",time()).'年合同未收款'; 
    $data = explode(",", substr ($count, 1)); 
    $size = 140; 
    $width = 750; 
    $height = 300; 
    $legend = explode(",", substr ($info, 1));
    ob_end_clean();
    $chart->createcolumnar($title,$data,$size,$height,$width,$legend);
	}
	
	public function namef(){
	$user=I('get.user');
	import("Org.Util.Chart");
    $chart = new \Chart;
	for($i=1;$i<=12;$i++){ 	
			$info=$info.",".$i;
			if($i<10){
			$co =M($this->dbname)->where(array('name'=>$user))->where(array('addm'=>date("Y",time())."-0".$i))->SUM('fukuan');
			}else{
			$co =M($this->dbname)->where(array('name'=>$user))->where(array('addm'=>date("Y",time())."-".$i))->SUM('fukuan');
			}
			$count=$count.",".$co;
		}
    $title = $user.date("Y",time()).'年合同已付款'; 
    $data = explode(",", substr ($count, 1)); 
    $size = 140; 
    $width = 750; 
    $height = 300; 
    $legend = explode(",", substr ($info, 1));
    ob_end_clean();
    $chart->createcolumnar($title,$data,$size,$height,$width,$legend);
	}
	
public function jinnian(){
	import("Org.Util.Chart");
    $chart = new \Chart;
	for($i=1;$i<=12;$i++){ 	
			$info=$info.",".$i;
			if($i<10){
			$co =M($this->dbname)->where(array('addm'=>date("Y",time())."-0".$i))->SUM('jine');
			}else{
			$co =M($this->dbname)->where(array('addm'=>date("Y",time())."-".$i))->SUM('jine');
			}
			$count=$count.",".$co;
		}
    $title = date("Y",time()).'年合同金额'; 
    $data = explode(",", substr ($count, 1)); 
    $size = 140; 
    $width = 750; 
    $height = 300; 
    $legend = explode(",", substr ($info, 1));
    ob_end_clean();
    $chart->createcolumnar($title,$data,$size,$height,$width,$legend);
	}
	
	public function jinnians(){
	import("Org.Util.Chart");
    $chart = new \Chart;
	for($i=1;$i<=12;$i++){ 	
			$info=$info.",".$i;
			if($i<10){
			$co =M($this->dbname)->where(array('addm'=>date("Y",time())."-0".$i))->SUM('yishou');
			}else{
			$co =M($this->dbname)->where(array('addm'=>date("Y",time())."-".$i))->SUM('yishou');
			}
			$count=$count.",".$co;
		}
    $title = date("Y",time()).'年合同已收款'; 
    $data = explode(",", substr ($count, 1)); 
    $size = 140; 
    $width = 750; 
    $height = 300; 
    $legend = explode(",", substr ($info, 1));
    ob_end_clean();
    $chart->createcolumnar($title,$data,$size,$height,$width,$legend);
	}
	
	public function jinnianw(){
	import("Org.Util.Chart");
    $chart = new \Chart;
	for($i=1;$i<=12;$i++){ 	
			$info=$info.",".$i;
			if($i<10){
			$co =M($this->dbname)->where(array('addm'=>date("Y",time())."-0".$i))->SUM('weishou');
			}else{
			$co =M($this->dbname)->where(array('addm'=>date("Y",time())."-".$i))->SUM('weishou');
			}
			$count=$count.",".$co;
		}
    $title = date("Y",time()).'年合同未收款'; 
    $data = explode(",", substr ($count, 1)); 
    $size = 140; 
    $width = 750; 
    $height = 300; 
    $legend = explode(",", substr ($info, 1));
    ob_end_clean();
    $chart->createcolumnar($title,$data,$size,$height,$width,$legend);
	}
	
	public function jinnianf(){
	import("Org.Util.Chart");
    $chart = new \Chart;
	for($i=1;$i<=12;$i++){ 	
			$info=$info.",".$i;
			if($i<10){
			$co =M($this->dbname)->where(array('addm'=>date("Y",time())."-0".$i))->SUM('fukuan');
			}else{
			$co =M($this->dbname)->where(array('addm'=>date("Y",time())."-".$i))->SUM('fukuan');
			}
			$count=$count.",".$co;
		}
    $title = date("Y",time()).'年合同已付款'; 
    $data = explode(",", substr ($count, 1)); 
    $size = 140; 
    $width = 750; 
    $height = 300; 
    $legend = explode(",", substr ($info, 1));
    ob_end_clean();
    $chart->createcolumnar($title,$data,$size,$height,$width,$legend);
	}

public function qunian(){
	import("Org.Util.Chart");
    $chart = new \Chart;
	for($i=1;$i<=12;$i++){ 	
			$info=$info.",".$i;
			if($i<10){
			$co = M($this->dbname)->where(array('addm'=>date("Y",strtotime("-1 year"))."-0".$i))->sum('jine');
			}else{
			$co = M($this->dbname)->where(array('addm'=>date("Y",strtotime("-1 year"))."-".$i))->sum('jine');
			}
			$count=$count.",".$co;
		}
    $title = date("Y",strtotime("-1 year")).'年合同金额'; 
    $data = explode(",", substr ($count, 1)); 
    $size = 140; 
    $width = 750; 
    $height = 300; 
    $legend = explode(",", substr ($info, 1));
    ob_end_clean();
    $chart->createcolumnar($title,$data,$size,$height,$width,$legend);
	}

	public function qunians(){
	import("Org.Util.Chart");
    $chart = new \Chart;
	for($i=1;$i<=12;$i++){ 	
			$info=$info.",".$i;
			if($i<10){
			$co = M($this->dbname)->where(array('addm'=>date("Y",strtotime("-1 year"))."-0".$i))->sum('yishou');
			}else{
			$co = M($this->dbname)->where(array('addm'=>date("Y",strtotime("-1 year"))."-".$i))->sum('yishou');
			}
			$count=$count.",".$co;
		}
    $title = date("Y",strtotime("-1 year")).'年合同已收款'; 
    $data = explode(",", substr ($count, 1)); 
    $size = 140; 
    $width = 750; 
    $height = 300; 
    $legend = explode(",", substr ($info, 1));
    ob_end_clean();
    $chart->createcolumnar($title,$data,$size,$height,$width,$legend);
	}
	
	public function qunianw(){
	import("Org.Util.Chart");
    $chart = new \Chart;
	for($i=1;$i<=12;$i++){ 	
			$info=$info.",".$i;
			if($i<10){
			$co = M($this->dbname)->where(array('addm'=>date("Y",strtotime("-1 year"))."-0".$i))->sum('weishou');
			}else{
			$co = M($this->dbname)->where(array('addm'=>date("Y",strtotime("-1 year"))."-".$i))->sum('weishou');
			}
			$count=$count.",".$co;
		}
    $title = date("Y",strtotime("-1 year")).'年合同未收款'; 
    $data = explode(",", substr ($count, 1)); 
    $size = 140; 
    $width = 750; 
    $height = 300; 
    $legend = explode(",", substr ($info, 1));
    ob_end_clean();
    $chart->createcolumnar($title,$data,$size,$height,$width,$legend);
	}
	
	public function qunianf(){
	import("Org.Util.Chart");
    $chart = new \Chart;
	for($i=1;$i<=12;$i++){ 	
			$info=$info.",".$i;
			if($i<10){
			$co = M($this->dbname)->where(array('addm'=>date("Y",strtotime("-1 year"))."-0".$i))->sum('fukuan');
			}else{
			$co = M($this->dbname)->where(array('addm'=>date("Y",strtotime("-1 year"))."-".$i))->sum('fukuan');
			}
			$count=$count.",".$co;
		}
    $title = date("Y",strtotime("-1 year")).'年合同已付款'; 
    $data = explode(",", substr ($count, 1)); 
    $size = 140; 
    $width = 750; 
    $height = 300; 
    $legend = explode(",", substr ($info, 1));
    ob_end_clean();
    $chart->createcolumnar($title,$data,$size,$height,$width,$legend);
	}

	/**
	 * 查询所有的合同相关项目列表
	 */
	public function ops(){
		$hid=I('get.id');
		$hid = intval($hid);
		if($hid > 0){
			//查询所有的项目列表
			$ops_list = D("htops","Logic")->getHtOpsListByHid($hid);
			$this->assign('list', $ops_list);
		}
		$this->display();
	}



}