<?php

/**
 *      联系人控制器
 *      [X-Mis] (C)2007-2099  
 *      This is NOT a freeware, use is subject to license terms
 *      http://www.xinyou88.com
 *      tel:400-000-9981
 *      qq:16129825
 */

namespace Home\Controller;
use Think\Controller;

class CustconController extends CommonController{

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
	  if(I("get.cid")!==""){
		$jcid=I("get.cid");
	    $jcname=comname($jcid);
	    $this->assign('jcid',$jcid);
	    $this->assign('jcname',$jcname);  
	  }
	  $attid=time();
	  $this->assign('attid',$attid);
    
  }
	
   public function _after_add($id){
	   $data=I('post.');
	   //新增客户基本信息后，再增加详细信息
	   $data["cuid"] = $id;
	   D("custcon","Logic")->addConDetailInfo($data);
	   //新增客户后发送消息
	   D("message","Logic")->sendMsgForCustomerNew(array("customer_id"=>$id));
   }

  public function _befor_insert($data){
	 //$data['addtime']=date("Y-m-d H:i:s",time());
	// return $data;
	  //根据wxid查询微信信息
	  if(intval($data["wxid"]) > 0){
		  $wxuser_data = D("wxuser","Logic")->getWxUserInfoById(intval($data["wxid"]));
		  if(!empty($wxuser_data)){
			  $data["headimgurl"] = $wxuser_data["headimgurl"];
			  $data["nickname"] = $wxuser_data["nickname"];
		  }
	  }
  }
  
  public function _befor_edit(){
	  //查询详细信息
	  $cuid = I("get.id");
	  if(is_numeric($cuid)){
		  $detailInfo = D("custcon","Logic")->getDetailInfoByCUid($cuid);
		  $this->assign('DetailRs',$detailInfo);
	  }
  }
   
  public function _befor_update($data){
	  return $data;
  }
  
    public function _after_edit($id){
		if(is_numeric($id)){
			$form_data=I('post.');
			D("custcon","Logic")->editDetailInfoByCUid($id,$form_data);
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
		$list = $model->where($map)->field('id,jcname,xingming,sex,bumen,phone,email,qq,addtime,updatetime')->select();
	    $headArr=array('ID','客户名称','姓名','性别','部门职务','手机号码','EMAIL','QQ','添加时间','更新时间');
	    $filename='联系人';
		$this->xlsout($filename,$headArr,$list);
	}

	public function validatecusname(){
		$xingming = I("param.xingming");
		if(isset($xingming) && $xingming != ""){
			$info = D("custcon","Logic")->getOneInfoByName($xingming);
			if(!empty($info)){
				echo "姓名有重复";
			}
			else{
				echo "";
			}
		}
		else{
			echo "";
		}
	}

	/**
	 * 客户统计分析
	 */
	public function fenxi(){
		$this->display();
	}

	public function getlist(){
		$jcid = I("get.jcid");
		if(isset($jcid) && is_numeric($jcid) && intval($jcid) > 0){
			//根据$jcid查询
			$list = D("custcon","Logic")->getListForAgentByJCid($jcid);
			$this->assign('list',$list);
		}
		$this->display("list");
	}

	public function fenlei(){
		$this->_fenxi('fenlei','进展',4);
	}

	/**
	 * 图表：统计最近两年的客户增长数据
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
			"title"=>array("text" => "最近两年客户增长比较","x" => -20),
			"xAxis" => array("categories" => array("1","2","3","4","5","6","7","8","9","10","11","12")),
			"yAxis" => array("title" => array("text"=>"数量"),	"plotLines" => array(array("value" => 0,"width" => 1,"color" => "#808080"))),
			"tooltip" => array("valueSuffix" => "人"),
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
	 * 图表数据：按性别统计月度客户增长数据
	 */
	public function getnumforchartbysex(){
		$customer_count_num = $this->countcustomernumbysex();
		$chart_data = array(
			"chart" => array("type"=>"column"),
			"title"=>array("text" => date("Y",time())."年度客户增长统计","x" => -20),
			"xAxis" => array("categories" => array("一月","二月","三月","四月","五月","六月","七月","八月","九月","十月","十一月","十二月"),"crosshair"=>true),
			"yAxis" => array("title" => array("text"=>"数量"),	"plotLines" => array(array("value" => 0,"width" => 1,"color" => "#808080"))),
			"tooltip" => array("valueSuffix" => "人"),
			"legend" => array("align"  => "left","verticalAlign" => "top","borderWidth" => 0,"y"=> 0,"floating"=>true));
		$chart_data["series"] = array(
			array(
				"name"=>"总人数",
				"data" =>$customer_count_num["customer_total_num"]
			),
			array(
				"name"=>"男性",
				"data" =>$customer_count_num["customer_sex1_num"]
			),
			array(
				"name"=>"女性",
				"data" =>$customer_count_num["customer_sex2_num"]
			),
			array(
				"name"=>"未知",
				"data" =>$customer_count_num["customer_sex0_num"]
			)
		);
		echo json_encode($chart_data);
	}

	/**
	 * 按性别统计当年内月度客户增长数量
	 * @return array
	 */
	public function countcustomernumbysex(){
		$customer_total_num = array();
		$customer_sex0_num = array();
		$customer_sex1_num = array();
		$customer_sex2_num = array();
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
			$map0 = array("addtime"=>array(array('egt',$firstDay),array('elt',$endDay)),"status"=>0);
			$map1 = array("addtime"=>array(array('egt',$firstDay),array('elt',$endDay)),"status"=>1);
			$map2 = array("addtime"=>array(array('egt',$firstDay),array('elt',$endDay)),"status"=>2);

			$tmp_total_num = M($this->dbname)->where($map)->count('id');
			$tmp_sex0_num = M($this->dbname)->where($map0)->count('id');
			$tmp_sex1_num = M($this->dbname)->where($map1)->count('id');
			$tmp_sex2_num = M($this->dbname)->where($map2)->count('id');

			$customer_total_num[$i-1] = intval($tmp_total_num);
			$customer_sex0_num[$i-1] = intval($tmp_sex0_num);
			$customer_sex1_num[$i-1] = intval($tmp_sex1_num);
			$customer_sex2_num[$i-1] = intval($tmp_sex2_num);
		}
		return array("customer_total_num"=>$customer_total_num,"customer_sex0_num"=>$customer_sex0_num,"customer_sex1_num"=>$customer_sex1_num,"customer_sex2_num"=>$customer_sex2_num);
	}
}