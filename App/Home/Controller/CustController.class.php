<?php

/**
 *      客户管理控制器
 *      [X-Mis] (C)2007-2099  
 *      This is NOT a freeware, use is subject to license terms
 *      http://www.xinyou88.com
 *      tel:400-000-9981
 *      qq:16129825
 */

namespace Home\Controller;
use Think\Controller;

class CustController extends CommonController{

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
		//发送消息
		D("message","Logic")->sendMsgForCustomerNew(array("customer_id"=>$id));
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
	
	public function fenxi(){
		$this->display();
	}

	public function fenlei(){
		$this->_fenxi('fenlei','进展',4);
	}

	public function type(){
		$this->_fenxi('type','客户来源',1);
	}
	public function uname(){
		$this->_fenxi('uname','添加人',2);
	}

	public function getlistbycust(){
		$jcid = I("get.jcid");
		if(isset($jcid) && is_numeric($jcid) && intval($jcid) > 0){
			//根据$jcid查询
			$list = D("cust","Logic")->getListByCustId($jcid);
			$this->assign('list',$list);
		}
		$this->display("list");
	}

	/**
	 * 图表：统计最近两年的机构增长趋势
	 */
	public function getnumforchartbyyear(){
		$cust_count_num = $this->countcustnumbyyear();
		$chart_data = array(
			"title"=>array("text" => "最近两年机构/代理增长比较","x" => -20),
			"xAxis" => array("categories" => array("一月","二月","三月","四月","五月","六月","七月","八月","九月","十月","十一月","十二月")),
			"yAxis" => array("title" => array("text"=>"数量"),	"plotLines" => array(array("value" => 0,"width" => 1,"color" => "#808080"))),
			"tooltip" => array("valueSuffix" => "人"),
			"legend" => array("align"  => "left","verticalAlign" => "top","borderWidth" => 0,"y"=> 0,"floating"=>true));
		$chart_data["series"] = array(
			array(
				"name"=>date("Y",time())."年",
				"data" =>$cust_count_num["currentYear_cust_sum"]
			),
			array(
				"name"=>date("Y",strtotime("-1 year"))."年",
				"data" =>$cust_count_num["lastYear_cust_sum"]
			)
		);
		echo json_encode($chart_data);
	}

	/**
	 * 图表：按类别统计代理商的增长趋势
	 */
	public function getnumforchartbycate(){
		$cust_count_num = $this->countnumbycate();
		$chart_data = array(
			"chart" => array("type"=>"column"),
			"title"=>array("text" => date("Y",time())."年度机构/代理商增长统计","x" => -20),
			"xAxis" => array("categories" => array("一月","二月","三月","四月","五月","六月","七月","八月","九月","十月","十一月","十二月"),"crosshair"=>true),
			"yAxis" => array("title" => array("text"=>"数量"),	"plotLines" => array(array("value" => 0,"width" => 1,"color" => "#808080"))),
			"tooltip" => array("valueSuffix" => "个"),
			"legend" => array("align"  => "left","verticalAlign" => "top","borderWidth" => 0,"y"=> 0,"floating"=>true));
		$chart_data["series"] = array(
			array(
				"name"=>"总人数",
				"data" =>$cust_count_num["cust_total_num"]
			),
			array(
				"name"=>"美容院",
				"data" =>$cust_count_num["cust_cate0_num"]
			),
			array(
				"name"=>"代理商",
				"data" =>$cust_count_num["cust_cate1_num"]
			)
		);
		echo json_encode($chart_data);
	}

	/**
	 * 计算最近两年的机构增长趋势
	 * @return array
	 */
	private function countcustnumbyyear(){
		$currentYear_cust_sum = array();
		$lastYear_cust_sum = array();
		for($i=1;$i<=12;$i++){
			if($i<10){
				$BeginDate = date("Y-0".$i."-01");//获取指定月份的第一天
			}else{
				$BeginDate = date("Y-".$i."-01");//获取指定月份的第一天
			}
			$firstDay = strtotime($BeginDate);//指定月的第一天
			$endDay = strtotime("$BeginDate +1 month -1 day");
			$map["addtime"] = array(array('egt',$firstDay),array('elt',$endDay));
			$map["status"] = 1;
			$co = M($this->dbname)->where($map)->count('id');
			$currentYear_cust_sum[$i-1] = intval($co);
		}
		for($i=1;$i<=12;$i++){
			if($i<10){
				$BeginDate = date("Y-0".$i."-01",strtotime("-1 year"));//获取指定月份的第一天
			}else{
				$BeginDate = date("Y-".$i."-01",strtotime("-1 year"));//获取指定月份的第一天
			}
			$firstDay = strtotime($BeginDate);//指定月的第一天
			$endDay = strtotime("$BeginDate +1 month -1 day");
			$map["addtime"] = array(array('egt',$firstDay),array('elt',$endDay));
			$map["status"] = 1;
			$co = M($this->dbname)->where($map)->count('id');
			$lastYear_cust_sum[$i-1] = intval($co);
		}
		return array("currentYear_cust_sum"=>$currentYear_cust_sum,"lastYear_cust_sum"=>$lastYear_cust_sum);
	}

	/**
	 * 按列表计算代理商的增长趋势
	 * @return array
	 */
	private function countnumbycate(){
		$cust_total_num = array();
		$cust_cate0_num = array();
		$cust_cate1_num = array();
		for($i=1;$i<=12;$i++){
			if($i<10){
				$BeginDate = date("Y-0".$i."-01");//获取指定月份的第一天
			}else{
				$BeginDate = date("Y-".$i."-01");//获取指定月份的第一天
			}
			$firstDay = strtotime($BeginDate);//指定月的第一天
			$endDay = strtotime("$BeginDate +1 month -1 day");
			$map["addtime"] = array(array('egt',$firstDay),array('elt',$endDay));
			$map["status"] = 1;//启用的用户

			$map = array("addtime"=>array(array('egt',$firstDay),array('elt',$endDay)),"status"=>1);
			$map0 = array("addtime"=>array(array('egt',$firstDay),array('elt',$endDay)),"status"=>1,"cate"=>0);
			$map1 = array("addtime"=>array(array('egt',$firstDay),array('elt',$endDay)),"status"=>1,"cate"=>1);

			$tmp_total_num = M($this->dbname)->where($map)->count('id');
			$tmp_sex0_num = M($this->dbname)->where($map0)->count('id');
			$tmp_sex1_num = M($this->dbname)->where($map1)->count('id');

			$cust_total_num[$i-1] = intval($tmp_total_num);
			$cust_cate0_num[$i-1] = intval($tmp_sex0_num);
			$cust_cate1_num[$i-1] = intval($tmp_sex1_num);
		}
		return array("cust_total_num"=>$cust_total_num,"cust_cate0_num"=>$cust_cate0_num,"cust_cate1_num"=>$cust_cate1_num);
	}

}