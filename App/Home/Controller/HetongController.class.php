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
				//发送消息
				D("message","Logic")->sendMsgForHtNew(array("hid"=>$id));
				$skip = array(
					"url"=>"/index.php?m=Home&c=shou&a=add&navTabId=shou&hid=".$id,
					"title"=>"新增收款",
					"height"=>"560",
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

	/**
	 * 根据客户ID获取客户相关订单
	 */
	public function getlist(){
		$cuid = I("get.cuid");
		if(isset($cuid) && is_numeric($cuid) && intval($cuid) > 0){
			//根据cuid查询
			$list = D("hetong","Logic")->getListByCUid($cuid);
			$this->assign('list',$list);
		}
		$this->display("list");
	}

	/**
	 * 查询订单基本信息
	 */
	public function getbase(){
		$hid=I('get.id');
		if(is_numeric($hid)){
			//查询订单详情
			$info = D("hetong","Logic")->getHetongInfoById($hid);
			if(!empty($info)){
				$ops_list = D("htops","Logic")->getHtOpsListByHid($info["id"]);
				$this->assign('Rs',$info);
				$this->assign('ops_list', $ops_list);
			}
		}
		$this->display("base");
	}

	/**
	 * 订单年度增长统计分析
	 */
	public function getnumforchartbyyear(){
		$currentYear_customer_sum = array();
		$lastYear_customer_sum = array();
		//查询今年的12个月份的值
		for($i=1;$i<=12;$i++){
			if($i<10){
				$BeginDate = date("Y-0".$i."-01");//获取指定月份的第一天
				$firstDay = strtotime($BeginDate);//指定月的第一天
				$endDay = strtotime("$BeginDate +1 month -1 day");
				$map["addtime"] = array(array('egt',$firstDay),array('elt',$endDay));
				$co = M($this->dbname)->where($map)->count('id');
			}else{
				$BeginDate = date("Y-".$i."-01");//获取指定月份的第一天
				$firstDay = strtotime($BeginDate);//指定月的第一天
				$endDay = strtotime("$BeginDate +1 month -1 day");
				$map["addtime"] = array(array('egt',$firstDay),array('elt',$endDay));
				$co = M($this->dbname)->where($map)->count('id');
			}
			$currentYear_customer_sum[$i-1] = intval($co);
		}
		//查询去年的12个月份的值
		for($i=1;$i<=12;$i++){
			if($i<10){
				$BeginDate = date("Y-0".$i."-01",strtotime("-1 year"));//获取指定月份的第一天
				$firstDay = strtotime($BeginDate);//指定月的第一天
				$endDay = strtotime("$BeginDate +1 month -1 day");
				$map["addtime"] = array(array('egt',$firstDay),array('elt',$endDay));
				$co = M($this->dbname)->where($map)->count('id');
			}else{
				$BeginDate = date("Y-".$i."-01",strtotime("-1 year"));//获取指定月份的第一天
				$firstDay = strtotime($BeginDate);//指定月的第一天
				$endDay = strtotime("$BeginDate +1 month -1 day");
				$map["addtime"] = array(array('egt',$firstDay),array('elt',$endDay));
				$co = M($this->dbname)->where($map)->count('id');
			}
			$lastYear_customer_sum[$i-1] = intval($co);
		}
		$chart_data = array(
			"title"=>array("text" => "最近两年订单增长比较","x" => -20),
			"xAxis" => array("categories" => array("1","2","3","4","5","6","7","8","9","10","11","12")),
			"yAxis" => array("title" => array("text"=>"数量"),	"plotLines" => array(array("value" => 0,"width" => 1,"color" => "#808080"))),
			"tooltip" => array("valueSuffix" => "单"),
			"legend" => array("align"  => "left","verticalAlign" => "top","borderWidth" => 0,"y"=> 0,"floating"=>true));
		$chart_data["series"] = array(
			array(
				"name"=>date("Y",time())."年",
				"data" =>$currentYear_customer_sum
			),
			array(
				"name"=>date("Y",strtotime("-1 year"))."年",
				"data" =>$lastYear_customer_sum
			)
		);
		echo json_encode($chart_data);
	}

	/**
	 * 按订单状态获取订单总数统计
	 */
	public function getnumforchartbystatus(){
		//统计今年订单总数、已收款完成数、未收款订单总数
		$order_count_num = $this->countOrderNumByStatus();
		$chart_data = array(
			"chart" => array("type"=>"column"),
			"title"=>array("text" => date("Y",time())."年度订单数量统计","x" => -20),
			"xAxis" => array("categories" => array("一月","二月","三月","四月","五月","六月","七月","八月","九月","十月","十一月","十二月"),"crosshair"=>true),
			"yAxis" => array("title" => array("text"=>"数量"),	"plotLines" => array(array("value" => 0,"width" => 1,"color" => "#808080"))),
			"tooltip" => array("valueSuffix" => "单"),
			"legend" => array("align"  => "left","verticalAlign" => "top","borderWidth" => 0,"y"=> 0,"floating"=>true));
		$chart_data["series"] = array(
			array(
				"name"=>"订单总数",
				"data" =>$order_count_num["order_total_num"]
			),
			array(
				"name"=>"欠款订单",
				"data" =>$order_count_num["order_wei_num"]
			),
			array(
				"name"=>"完成订单",
				"data" =>$order_count_num["order_over_num"]
			)
		);
		echo json_encode($chart_data);
	}

	/**
	 * 图表数据：统计订单金额
	 */
	public function getmoneyforchartbystatus(){
		$order_count_money = $this->countOrderMoneyByStatus();
		$chart_data = array(
			"chart" => array("type"=>"column"),
			"title"=>array("text" => date("Y",time())."年度订单对账统计","x" => -20),
			"xAxis" => array("categories" => array("一月","二月","三月","四月","五月","六月","七月","八月","九月","十月","十一月","十二月"),"crosshair"=>true),
			"yAxis" => array("title" => array("text"=>"数量"),	"plotLines" => array(array("value" => 0,"width" => 1,"color" => "#808080"))),
			"tooltip" => array("valueSuffix" => "元"),
			"legend" => array("align"  => "left","verticalAlign" => "top","borderWidth" => 0,"y"=> 0,"floating"=>true));
		$chart_data["series"] = array(
			array(
				"name"=>"订单总额",
				"data" =>$order_count_money["order_total_sum"]
			),
			array(
				"name"=>"未收金额",
				"data" =>$order_count_money["order_wei_sum"]
			),
			array(
				"name"=>"已收金额",
				"data" =>$order_count_money["order_over_sum"]
			)
		);
		echo json_encode($chart_data);
	}

	/**
	 * 按状态统计订单数量
	 * @return array
	 */
	private function countOrderNumByStatus(){
		$order_total_num = array();
		$order_wei_num = array();
		$order_over_num = array();
		for($i=1;$i<=12;$i++){
			if($i<10){
				$BeginDate = date("Y-0".$i."-01");//获取指定月份的第一天
			}else{
				$BeginDate = date("Y-".$i."-01");//获取指定月份的第一天
			}
			$firstDay = strtotime($BeginDate);//指定月的第一天
			$endDay = strtotime("$BeginDate +1 month -1 day");
			$map["addtime"] = array(array('egt',$firstDay),array('elt',$endDay));
			$map_wei["addtime"] = array(array('egt',$firstDay),array('elt',$endDay));
			$map_wei["status"] = 0;
			$co = M($this->dbname)->where($map)->count('id');
			$wei = M($this->dbname)->where($map_wei)->count('id');
			$order_total_num[$i-1] = intval($co);
			$order_wei_num[$i-1] = intval($wei);
			$order_over_num[$i-1] = intval($co) - intval($wei);
		}
		return array("order_total_num"=>$order_total_num,"order_wei_num"=>$order_wei_num,"order_over_num"=>$order_over_num);
	}

	/**
	 * 计算订单金额
	 * @return array
	 */
	private function countOrderMoneyByStatus(){
		$order_total_sum = array();
		$order_wei_sum = array();
		$order_over_sum = array();
		for($i=1;$i<=12;$i++){
			if($i<10){
				$BeginDate = date("Y-0".$i."-01");//获取指定月份的第一天
			}else{
				$BeginDate = date("Y-".$i."-01");//获取指定月份的第一天
			}
			$firstDay = strtotime($BeginDate);//指定月的第一天
			$endDay = strtotime("$BeginDate +1 month -1 day");
			$map["addtime"] = array(array('egt',$firstDay),array('elt',$endDay));
			$tmp_total_sum = M($this->dbname)->where($map)->sum('jine');
			$tmp_wei_sum = M($this->dbname)->where($map)->sum('weishou');
			$tmp_yi_sum = M($this->dbname)->where($map)->sum('yishou');
			$order_total_sum[$i-1] = intval($tmp_total_sum);
			$order_wei_sum[$i-1] = intval($tmp_wei_sum);
			$order_over_sum[$i-1] = intval($tmp_yi_sum);
		}
		return array("order_total_sum"=>$order_total_sum,"order_wei_sum"=>$order_wei_sum,"order_over_sum"=>$order_over_sum);
	}

}