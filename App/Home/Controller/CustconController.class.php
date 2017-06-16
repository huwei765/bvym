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

	public function fenxi(){
		$this->display();
	}

	/**
	 * 今年客户数量比较
	 */
	public function jinnian(){
		$info="";
		import("Org.Util.Chart");
		$chart = new \Chart;
		for($i=1;$i<=12;$i++){
			$info=$info.",".$i;
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
			$count=$count.",".$co;
		}
		$title = date("Y",time()).'年客户增长趋势';
		$data = explode(",", substr ($count, 1));
		$size = 140;
		$width = 750;
		$height = 300;
		$legend = explode(",", substr ($info, 1));
		ob_end_clean();
		$chart->createmonthline($title,$data,$size,$height,$width,$legend);
	}

	/**
	 * 去年客户数量比较
	 */
	public function qunian(){
		import("Org.Util.Chart");
		$chart = new \Chart;
		for($i=1;$i<=12;$i++){
			$info=$info.",".$i;
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
			$count=$count.",".$co;
		}
		$title = date("Y",strtotime("-1 year")).'年客户增长趋势';
		$data = explode(",", substr ($count, 1));
		$size = 140;
		$width = 750;
		$height = 300;
		$legend = explode(",", substr ($info, 1));
		ob_end_clean();
		$chart->createmonthline($title,$data,$size,$height,$width,$legend);
	}

	public function getlist(){
		$jcid = I("get.jcid");
		if(isset($jcid) && is_numeric($jcid) && intval($jcid) > 0){
			//根据$jcid查询
			$list = D("custcon","Logic")->getListByJCid($jcid);
			$this->assign('list',$list);
		}
		$this->display("list");
	}

	public function fenlei(){
		$this->_fenxi('fenlei','进展',4);
	}

	public function getchartdatabyyear(){
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
			"title"=>array("text" => "客户年度增长比较","x" => -20),
			"xAxis" => array("categories" => array("1","2","3","4","5","6","7","8","9","10","11","12")),
			"yAxis" => array("title" => "数量",	"plotLines" => array(array("value" => 0,"width" => 1,"color" => "#808080"))),
			"tooltip" => array("valueSuffix" => "人"),
			"legend" => array("align"  => "left","verticalAlign" => "top","borderWidth" => 0,"y"=> 0,"floating"=>true));
		$chart_data["series"] = array(
			array(
				"name"=>"今年",
				"data" =>$currentYear_customer_sum
			),
			array(
				"name"=>"去年",
				"data" =>$lastYear_customer_sum
			)
		);
		echo json_encode($chart_data);
	}
}