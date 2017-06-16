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

	/**
	 * 新增收款
	 */
	public function add() {
		if(IS_POST){
			$data=I('post.');
			//参数检测
			if(!isset($data["jine"]) || !is_numeric($data["jine"])){
				$this->mtReturn(300,"操作失败,金额数据不符合！",$_REQUEST['navTabId'],true);
			}
			if(intval($data["jine"]) < 100 || intval($data["jine"]) > 1000000){
				$this->mtReturn(300,"操作失败,单笔支付额度必须在100到10000000之内！",$_REQUEST['navTabId'],true);
			}
			//新增收款记录
			$ret = D("shou","Logic")->addShouInfoForHt($data);
			if($ret){
				$this->mtReturn(200,"新增成功",$_REQUEST['navTabId'],true);
			}
			else{
				$this->mtReturn(300,"操作失败,请确认该笔订单是否收款完成！",$_REQUEST['navTabId'],true);
			}
		}
		if (method_exists($this, '_befor_add')) {
			$this->_befor_add();
		}
		$this->display();
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

	/**
	 * 根据订单查询订单收款列表
	 */
	public function getlist(){
		$hid = I("get.hid");
		if(isset($hid) && is_numeric($hid) && intval($hid) > 0){
			//根据hid查询订单收款情况
			$list = D("shou","Logic")->getListByHid($hid);
			$this->assign('list',$list);
		}
		$this->display("list");
	}

	/**
	 * 图表：统计最近两年收款增长趋势
	 */
	public function getmoneyforchartbyyear(){
		$shou_count_sum = $this->countmoneyforchartbyyear();
		$chart_data = array(
			"title"=>array("text" => "最近两年收款增长比较","x" => -20),
			"xAxis" => array("categories" => array("一月","二月","三月","四月","五月","六月","七月","八月","九月","十月","十一月","十二月")),
			"yAxis" => array("title" => array("text"=>"金额（元）"),	"plotLines" => array(array("value" => 0,"width" => 1,"color" => "#808080"))),
			"tooltip" => array("valueSuffix" => "元"),
			"legend" => array("align"  => "left","verticalAlign" => "top","borderWidth" => 0,"y"=> 0,"floating"=>true));
		$chart_data["series"] = array(
			array(
				"name"=>date("Y",time())."年",
				"data" =>$shou_count_sum["currentYear_shou_sum"]
			),
			array(
				"name"=>date("Y",strtotime("-1 year"))."年",
				"data" =>$shou_count_sum["lastYear_shou_sum"]
			)
		);
		echo json_encode($chart_data);
	}

	/**
	 * 图表：按支付方式统计当年各个月度的收款趋势
	 */
	public function getmoneyforchartbytype(){
		$shou_count_sum = $this->countmoneyforchartbytype();
		$chart_data = array(
			"chart" => array("type"=>"column"),
			"title"=>array("text" => date("Y",time())."年度收款对账统计","x" => -20),
			"xAxis" => array("categories" => array("一月","二月","三月","四月","五月","六月","七月","八月","九月","十月","十一月","十二月"),"crosshair"=>true),
			"yAxis" => array("title" => array("text"=>"金额（元）"),	"plotLines" => array(array("value" => 0,"width" => 1,"color" => "#808080"))),
			"tooltip" => array("valueSuffix" => "元"),
			"legend" => array("align"  => "left","verticalAlign" => "top","borderWidth" => 0,"y"=> 0,"floating"=>true));
		$chart_data["series"] = array(
			array(
				"name"=>"现金收款额",
				"data" =>$shou_count_sum["shou_type0_sum"]
			),
			array(
				"name"=>"银行转账收款额",
				"data" =>$shou_count_sum["shou_type1_sum"]
			)
		);
		echo json_encode($chart_data);
	}

	/**
	 * 计算最近两年的收款趋势
	 * @return array
	 */
	private function countmoneyforchartbyyear(){
		$currentYear_shou_sum = array();
		$lastYear_shou_sum = array();
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
			$co = M($this->dbname)->where($map)->sum('jine');
			$currentYear_shou_sum[$i-1] = intval($co);
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
			$co = M($this->dbname)->where($map)->sum('jine');
			$lastYear_shou_sum[$i-1] = intval($co);
		}
		return array("currentYear_shou_sum"=>$currentYear_shou_sum,"lastYear_shou_sum"=>$lastYear_shou_sum);
	}

	/**
	 * 计算当年各个月度的收款趋势
	 * @return array
	 */
	private function countmoneyforchartbytype(){
		$shou_type0_sum = array();
		$shou_type1_sum = array();
		for($i=1;$i<=12;$i++){
			if($i<10){
				$BeginDate = date("Y-0".$i."-01");//获取指定月份的第一天
			}else{
				$BeginDate = date("Y-".$i."-01");//获取指定月份的第一天
			}
			$firstDay = strtotime($BeginDate);//指定月的第一天
			$endDay = strtotime("$BeginDate +1 month -1 day");
			$map["addtime"] = array(array('egt',$firstDay),array('elt',$endDay));

			$map0 = array("addtime"=>array(array('egt',$firstDay),array('elt',$endDay)),"status"=>1,"stype"=>0);
			$map1 = array("addtime"=>array(array('egt',$firstDay),array('elt',$endDay)),"status"=>1,"stype"=>1);

			$tmp_type0_sum = M($this->dbname)->where($map0)->sum('jine');
			$tmp_type1_sum = M($this->dbname)->where($map1)->sum('jine');

			$shou_type0_sum[$i-1] = intval($tmp_type0_sum);
			$shou_type1_sum[$i-1] = intval($tmp_type1_sum);
		}
		return array("shou_type0_sum"=>$shou_type0_sum,"shou_type1_sum"=>$shou_type1_sum);
	}
}