<?php
namespace Home\Controller;
use Think\Controller;

Class CommonController extends Controller{


	public function _initialize(){
		
        $this->_name = CONTROLLER_NAME;
	    
		if(!session('uid')){
			redirect(U('Public/login'));
		}
       
        $config =   S('DB_CONFIG_DATA');
        if(!$config){
            $config =   api('Config/lists');
            S('DB_CONFIG_DATA',$config);
        }
        C($config); 

		$name=MODULE_NAME.'/'.CONTROLLER_NAME.'/'.ACTION_NAME;
		if(!authcheck($name,session('uid'))){
		   //$this->error(''.session('username').'很抱歉,此项操作您没有权限！');
		   $this->mtReturn(300,''.session('username').'很抱歉,此项操作您没有权限！',$_REQUEST['navTabId']);
		}
		// 左侧菜单
		$main_menu = $this->_getMenuList();
		$this->assign('menu', $main_menu);
	}
	
	
	protected function mtReturn($status,$info,$navTabId="",$closeCurrent=true,$skip=array(),$forward='',$forwardConfirm='') {
       
	    $udata['id']=session('uid');
        $udata['update_time']=time();
        $Rs=M("user")->save($udata);
        $dat['username'] = session('username');
        $dat['content'] = $info;
		$dat['os']=substr($_SERVER['HTTP_USER_AGENT'], 0, 99);
        $dat['url'] = U();
        $dat['addtime'] = date("Y-m-d H:i:s",time());
        $dat['ip'] = get_client_ip();
        M("log")->add($dat);
	   
	    
	    $result = array();
        $result['statusCode'] = $status; 
        $result['message'] = $info;
		$result['tabid'] = strtolower($navTabId).'/index';
		$result['skip'] = $skip;
        $result['forward'] = $forward;
		$result['forwardConfirm']=$forwardConfirm;
        $result['closeCurrent'] =$closeCurrent;
       
        if (empty($type))
            $type = C('DEFAULT_AJAX_RETURN');
        if (strtoupper($type) == 'JSON') {
            // 返回JSON数据格式到客户端 包含状态信息
            header("Content-Type:text/html; charset=utf-8");
            exit(json_encode($result));
        } elseif (strtoupper($type) == 'XML') {
            // 返回xml格式数据
            header("Content-Type:text/xml; charset=utf-8");
            exit(xml_encode($result));
        } elseif (strtoupper($type) == 'EVAL') {
            // 返回可执行的js脚本
            header("Content-Type:text/html; charset=utf-8");
            exit($result);
        } else {
            // TODO 增加其它格式
        }
	}
	
	 /**
     * 列表页面
     */
	protected function _list($model, $map, $asc = false) {
		
		//排序字段 默认为主键名
		if (isset($_REQUEST ['orderField'])) {
			$order = $_REQUEST ['orderField'];
		}
		if($order=='') {
			$order = $model->getPk();

		}
			
		//排序方式默认按照倒序排列
		//接受 sost参数 0 表示倒序 非0都 表示正序
		if (isset($_REQUEST ['orderDirection'])) {
			$sort = $_REQUEST ['orderDirection'];
		}
		if($sort=='') {
			$sort = $asc ? 'asc' : 'desc';

		}

		if (isset($_REQUEST ['pageCurrent'])) {
			$pageCurrent = $_REQUEST ['pageCurrent'];
		}
		if($pageCurrent=='') {
			$pageCurrent =1;

		}
		
		//取得满足条件的记录数
		$count = $model->where($map)->count();
		
       
		if ($count > 0) {

		    $numPerPage=C('PERPAGE');

            $voList = $model->where($map)->order("`" . $order . "` " . $sort)->limit($numPerPage)->page($pageCurrent.','.$numPerPage.'')->select();
		    
			
			//列表排序显示
			$sortImg = $sort; //排序图标
			$sortAlt = $sort == 'desc' ? '升序排列' : '倒序排列'; //排序提示
			$sort = $sort == 'desc' ? 1 : 0; //排序方式
			
		   if( method_exists($this, '_after_list')){
				
				$voList=$this->_after_list($voList);
			}
			
			$this->assign('list', $voList);

		}
		$this->assign('totalCount', $count);//数据总数
		$this->assign('currentPage', !empty($_REQUEST[C('VAR_PAGE')]) ? $_REQUEST[C('VAR_PAGE')] : 1);//当前的页数，默认为1
		$this->assign('numPerPage', $numPerPage); //每页显示多少条
		return;
	}

	
	public function index() {

		$model = D($this->dbname);
		$map = $this->_search();
		if (method_exists($this, '_filter')) {
			$this->_filter($map);
		}
		if (!empty($model)) {
			$this->_list($model, $map);
		}
		if (method_exists($this, '_befor_index')) {
			$this->_befor_index();
		}
		$this->display();
	}

	protected function _search($dbname='') {
		
		$dbname = $dbname ? $dbname : $this->dbname;
		$model = D($dbname);
		$map = array();
		foreach ($model->getDbFields() as $key => $val) {
			if (isset($_REQUEST['keys']) && $_REQUEST['keys'] != '') {
				if(in_array($val, C('SYS_SEARCH_KEY'))){
					$map [$val] = array('like','%'.trim($_REQUEST['keys']).'%');
				}else{
					//$map [$val] = $_REQUEST['keys'];
				}
					
			}
		}
		$map['_logic'] = 'or'; 
        if ((IS_POST)&&isset($_REQUEST['keys']) && $_REQUEST['keys'] != '') {
			$where['_complex'] = $map;
			return $where;
		}else{
			return $map;
			}
		
		
	}
    
	 public function add() {
		if(IS_POST){
		  $model = D($this->dbname);
		  $data=I('post.');
		  if (false === $data = $model->create()) {
			   $this->mtReturn(300,"失败，".$model->getError(),$_REQUEST['navTabId'],true);
            }
          if (method_exists($this, '_befor_insert')) {
                $data = $this->_befor_insert($data);
            }		  
          if($model->add($data)){
			  $id = $model->getLastInsID();
			  if (method_exists($this, '_after_add')) {
				  $this->_after_add($id);
			  }
			  $this->mtReturn(200,"新增成功",$_REQUEST['navTabId'],true);
		  }
	      
		}
		if (method_exists($this, '_befor_add')) {
			$this->_befor_add();
		}
		$this->display();
	}


	public function edit() {
	  $model = D($this->dbname);
	  if(IS_POST){
		   $data=I('post.');
		   if (false === $data = $model->create()) {
			   $this->mtReturn(300,'失败，'.$model->getError(),$_REQUEST['navTabId'],true);
            }
           if (method_exists($this, '_befor_update')) {
                $data = $this->_befor_update($data);
            }
          if($model->save($data)){
			if (method_exists($this, '_after_edit')) {
			  $id = $data['id'];
			  $this->_after_edit($id);
			  }
		  }
		  $this->mtReturn(200,"编辑成功",$_REQUEST['navTabId'],true);
	   }
	     if (method_exists($this, '_befor_edit')) {
			$this->_befor_edit();
		 }
		$id = $_REQUEST [$model->getPk()];
		$vo = $model->getById($id);
		$this->assign('id',$id);
		$this->assign('Rs', $vo);
		$this->display();
	}
	
	public function view() {
	    $model = D($this->dbname);
		$id = $_REQUEST [$model->getPk()];
		$vo = $model->getById($id);
		$this->assign('Rs', $vo);
		$this->display();
	}
	
	public function del(){
		$model = D($this->dbname);
		$id = I('get.id');
		if($id){
			$data=$model->find($id);
			$data['id']=$id;
			if($data['status']==1){
				$data['status']=0;
				$msg='锁定';
				if (method_exists($this, '_befor_del')) {
                $this->_befor_del($id);
                 }	
			}else{
				$data['status']=1;
				$msg='启用';
			}
			$model->save($data);
			$this->mtReturn(200,$msg.$id,$_REQUEST['navTabId'],false);
		}else{
			 $info=$model->where('status=0')->select();
		      foreach($info as $key=>$v){
		       $attid=$v['attid'];
		       $ad['attid']=0;
			   M('files')->where(array("attid"=>$attid))->save($ad);
			}
			 $info=M('files')->where('attid=0 and  uid='.session('uid'))->select();
             foreach($info as $key=>$v){
               $filepath=$v['folder'].$v['filename'];
               unlink($filepath);
	          }
			 M('files')->where('attid=0 and  uid='.session('uid'))->delete();
			  if(!in_array(session('uid'),C('ADMINISTRATOR'))){
			   $Rs=$model->where('status=0 and uid='.session("uid"))->delete();
			  }else{
				$Rs=$model->where('status=0')->delete();  
			  }
			$this->mtReturn(200,'清理'.$Rs.'条无用的记录',$_REQUEST['navTabId'],false);
		}

	}
	
	public function _fenxi($fd,$ft,$type) {
		import("Org.Util.Chart");
        $chart = new \Chart;
		$model = D($this->dbname);
		$this->fd=$fd;
		$map = $this->_search();
		if (method_exists($this, '_filter')) {
			$this->_filter($map);
		}
		$list = $model->where($map)->distinct($this->fd)->field($this->fd)->select();
		echo  $model->getlastsql();
	    foreach ($list as $key =>$vo){	
			$info=$info.",".$vo[$this->fd];
			$co = $model->where(array($this->fd=>$vo[$this->fd]))->where($map)->count('id');
			$count=$count.",".$co;
		}
    $title = $ft; 
    $data = explode(",", substr ($count, 1)); 
    $size = 140; 
    $width = 750; 
    $height = 300; 
    $legend = explode(",", substr ($info, 1));
    ob_end_clean();
	if ($type == 1) {
		$chart->create3dpie($title,$data,$size,$height,$width,$legend);
     }
	 if ($type == 2) {
		$chart->createcolumnar($title,$data,$size,$height,$width,$legend);
     }
	 if ($type == 3) {
		$chart->createmonthline($title,$data,$size,$height,$width,$legend);
     }
	 if ($type == 4) {
		$chart->createring($title,$data,$size,$height,$width,$legend);
     }
	 if ($type == 5) {
		$chart->createhorizoncolumnar($title,$subtitle,$data,$size,$height,$width,$legend);
     }
   
	}
	
    public function xlsout($filename='数据表',$headArr,$list){
			
		//导入PHPExcel类库，因为PHPExcel没有用命名空间，只能inport导入
		import("Org.Util.PHPExcel");
		import("Org.Util.PHPExcel.Writer.Excel5");
		import("Org.Util.PHPExcel.IOFactory.php");
		$this->getExcel($filename,$headArr,$list);
	}
	public function xlsin(){
			
		//导入PHPExcel类库，因为PHPExcel没有用命名空间，只能inport导入
		import("Org.Util.PHPExcel");
		//要导入的xls文件，位于根目录下的Public文件夹
		$filename="./Public/1.xls";
		//创建PHPExcel对象，注意，不能少了\
		$PHPExcel=new \PHPExcel();
		//如果excel文件后缀名为.xls，导入这个类
		import("Org.Util.PHPExcel.Reader.Excel5");
		//如果excel文件后缀名为.xlsx，导入这下类
		//import("Org.Util.PHPExcel.Reader.Excel2007");
		//$PHPReader=new \PHPExcel_Reader_Excel2007();

		$PHPReader=new \PHPExcel_Reader_Excel5();
		//载入文件
		$PHPExcel=$PHPReader->load($filename);
		//获取表中的第一个工作表，如果要获取第二个，把0改为1，依次类推
		$currentSheet=$PHPExcel->getSheet(0);
		//获取总列数
		$allColumn=$currentSheet->getHighestColumn();
		//获取总行数
		$allRow=$currentSheet->getHighestRow();
		//循环获取表中的数据，$currentRow表示当前行，从哪行开始读取数据，索引值从0开始
		for($currentRow=1;$currentRow<=$allRow;$currentRow++){
			//从哪列开始，A表示第一列
			for($currentColumn='A';$currentColumn<=$allColumn;$currentColumn++){
				//数据坐标
				$address=$currentColumn.$currentRow;
				//读取到的数据，保存到数组$arr中
				$arr[$currentRow][$currentColumn]=$currentSheet->getCell($address)->getValue();
			}

		}
			
	}
	public	function getExcel($fileName,$headArr,$data){
		//对数据进行检验
		if(empty($data) || !is_array($data)){
			die("data must be a array");
		}
		//检查文件名
		if(empty($fileName)){
			exit;
		}

		$date = date("Y_m_d",time());
		$fileName .= "_{$date}.xls";


		//创建PHPExcel对象，注意，不能少了\
		$objPHPExcel = new \PHPExcel();
		$objProps = $objPHPExcel->getProperties();
			
		//设置表头
		$key = ord("A");
		foreach($headArr as $v){
			$colum = chr($key);
			$objPHPExcel->setActiveSheetIndex(0)->setCellValue($colum.'1', $v);
			$key += 1;
		}

		$column = 2;
		$objActSheet = $objPHPExcel->getActiveSheet();


		//设置为文本格式
		foreach($data as $key => $rows){ //行写入
			$span = ord("A");
			foreach($rows as $keyName=>$value){// 列写入
				$j = chr($span);

				$objActSheet->setCellValueExplicit($j.$column, $value);
				$span++;
			}
			$column++;
		}

		$fileName = iconv("utf-8", "gb2312", $fileName);
		//重命名表
		// $objPHPExcel->getActiveSheet()->setTitle('test');
		//设置活动单指数到第一个表,所以Excel打开这是第一个表
		$objPHPExcel->setActiveSheetIndex(0);
		ob_end_clean();//清除缓冲区,避免乱码
		header('Content-Type: application/vnd.ms-excel');
		header("Content-Disposition: attachment;filename=\"$fileName\"");
		header('Cache-Control: max-age=0');

		$objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
		$objWriter->save('php://output'); //文件通过浏览器下载
		exit;
	}

	/**
	 * 获取左侧菜单列表
	 * @return array
	 */
	private function _getMenuList() {
		$menu_list = array(
			'customer' => array('name' => '客户管理','sort' => 0,'id' => 1, 'child' => array(
				array('name' => '客户签到','id' => 11, 'act'=>'signin', 'op'=>'index'),
				array('name' => '客户信息','id' => 12, 'act'=>'custcon', 'op'=>'index'),
				array('name' => '跟单记录','id' => 13, 'act'=>'congd', 'op'=>'index'),
				//array('name' => '咨询方案','id' => 14, 'act'=>'design', 'op'=>'index'),
				array('name' => '订单管理','id' => 15, 'act'=>'hetong', 'op'=>'index'),
				array('name' => '收款记录','id' => 16, 'act'=>'shou', 'op'=>'index'),
				array('name' => '开票记录','id' => 17, 'act'=>'piao', 'op'=>'index'),
				array('name' => '手术记录','id' => 18, 'act'=>'huo', 'op'=>'index'),
				array('name' => '统计分析','id' => 19, 'act'=>'fenxi', 'op'=>'index'),
			)),
			'cust' => array('name' => '合作机构','sort' => 1,'id' => 2, 'child' => array(
				array('name' => '机构信息','id' => 21, 'act'=>'cust', 'op'=>'index'),
				array('name' => '跟单记录','id' => 22, 'act'=>'custgd', 'op'=>'index'),
				array('name' => '合同管理','id' => 23, 'act'=>'jght', 'op'=>'index'),
//				array('name' => '推广客户','id' => 24, 'act'=>'cus_invite', 'op'=>'index'),
				array('name' => '推广提成','id' => 25, 'act'=>'cus_profit', 'op'=>'index','child' => array(
					array('name' => '待审核','id' => 251, 'act'=>'cusprofit', 'op'=>'no_verify'),
					array('name' => '未付款','id' => 252, 'act'=>'cusprofit', 'op'=>'no_pay'),
					array('name' => '已完成','id' => 253, 'act'=>'cusprofit', 'op'=>'over'),
					array('name' => '未通过','id' => 254, 'act'=>'cusprofit', 'op'=>'fail')
				)),
				array('name' => '返现记录','id' => 26, 'act'=>'cus_fan', 'op'=>'index'),
//				array('name' => '提成设置','id' => 27, 'act'=>'cus_set', 'op'=>'index'),
				array('name' => '统计分析','id' => 28, 'act'=>'cus_analyse', 'op'=>'index'),
			)),
			'weixin' => array('name' => '微信用户','sort' => 2,'id' => 3, 'child' => array(
				array('name' => '微信用户','id' => 31, 'act'=>'wxuser', 'op'=>'index'),
				array('name' => '推广层级','id' => 32, 'act'=>'wxtg', 'op'=>'index'),
				array('name' => '推广提成','id' => 33, 'act'=>'wxprofit', 'op'=>'index','child' => array(
					array('name' => '待审核','id' => 351, 'act'=>'wxprofit', 'op'=>'no_verify'),
					array('name' => '未付款','id' => 352, 'act'=>'wxprofit', 'op'=>'no_pay'),
					array('name' => '已完成','id' => 353, 'act'=>'wxprofit', 'op'=>'over'),
					array('name' => '未通过','id' => 354, 'act'=>'wxprofit', 'op'=>'fail')
				)),
				array('name' => '返现记录','id' => 34, 'act'=>'wx_fan', 'op'=>'index'),
//				array('name' => '提成设置','id' => 35, 'act'=>'wx_set', 'op'=>'index'),
				array('name' => '统计分析','id' => 36, 'act'=>'wx_analyse', 'op'=>'index'),
			)),
			'hospital' => array('name' => '医院管理','sort' => 3,'id' => 4, 'child' => array(
				array('name' => '医院信息','id' => 41, 'act'=>'hospital', 'op'=>'index'),
				array('name' => '医生信息','id' => 42, 'act'=>'doctor', 'op'=>'index'),
				array('name' => '整形项目','id' => 43, 'act'=>'ops', 'op'=>'index'),
				array('name' => '项目分类','id' => 44, 'act'=>'opscate', 'op'=>'index'),
				array('name' => '整形记录','id' => 45, 'act'=>'opsrecord', 'op'=>'index')
			)),
			'hr' => array('name' => '人事管理','sort' => 4,'id' => 5, 'child' => array(
				array('name' => '员工档案','id' => 51, 'act'=>'hr', 'op'=>'index'),
				array('name' => '人事合同','id' => 52, 'act'=>'hrht', 'op'=>'index'),
				array('name' => '奖罚管理','id' => 53, 'act'=>'hrjf', 'op'=>'index'),
				array('name' => '证照管理','id' => 54, 'act'=>'hrzz', 'op'=>'index'),
				array('name' => '人事调动','id' => 55, 'act'=>'hrdd', 'op'=>'index'),
				array('name' => '员工关怀','id' => 56, 'act'=>'hrgh', 'op'=>'index'),
				array('name' => '统计分析','id' => 57, 'act'=>'hr', 'op'=>'index','child' => array(
					array('name' => '本月下月生日','id' => 571, 'act'=>'hr', 'op'=>'birthday'),
					array('name' => '即将到期的合同','id' => 572, 'act'=>'hrht', 'op'=>'daoqi'),
					array('name' => '员工分析','id' => 573, 'act'=>'hr', 'op'=>'fenxi')
				))
			)),
			'finance' => array('name' => '财务管理','sort' => 5,'id' => 6, 'child' => array(
				array('name' => '收款记录','id' => 61, 'act'=>'shou', 'op'=>'index'),
				array('name' => '付款记录','id' => 62, 'act'=>'fu', 'op'=>'index'),
				array('name' => '开票记录','id' => 63, 'act'=>'piao', 'op'=>'index')
			)),
			'system' => array('name' => '系统管理','sort' => 6,'id' => 7, 'child' => array(
				array('name' => '组织机构','id' => 71, 'act'=>'org', 'op'=>'index','child' => array(
					array('name' => '部门管理','id' => 711, 'act'=>'org', 'op'=>'index'),
					array('name' => '职位管理','id' => 712, 'act'=>'dep', 'op'=>'index'),
					array('name' => '用户管理','id' => 713, 'act'=>'user', 'op'=>'index')
				)),
				array('name' => '系统设置','id' => 72, 'act'=>'system', 'op'=>'index','child' => array(
					array('name' => '数据字典','id' => 721, 'act'=>'config', 'op'=>'index'),
					array('name' => '数据模型','id' => 722, 'act'=>'model', 'op'=>'index'),
					array('name' => '菜单管理','id' => 723, 'act'=>'menu', 'op'=>'index'),
					array('name' => '功能列表','id' => 724, 'act'=>'rule', 'op'=>'index')
				)),
				array('name' => '数据备份','id' => 73, 'act'=>'database', 'op'=>'index'),
				array('name' => '备份文件','id' => 74, 'act'=>'database', 'op'=>'bakup'),
				array('name' => '操作日志','id' => 75, 'act'=>'log', 'op'=>'index')
			))
		);
		return $menu_list;
	}

	/**
	 * 生成单据编号
	 * @param $suffix
	 * @param int $type
	 * @return string
	 */
	public function generateHtSn($suffix,$type=0){
		$yCode = array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J');
		$index = (intval(date('Y')) - 2011) % 10;
		if($type == 1){
			$index = (intval($index) + 1) % 10;
		}
		else if($type == 2){
			$index = (intval($index) + 2) % 10;
		}
		$orderSn = $yCode[$index];
		$orderSn = $orderSn . date("YmdHis");
		if(isset($suffix) && $suffix != ""){
			$orderSn = $orderSn . $suffix;
		}
		$orderSn = $orderSn . sprintf('%02d', rand(0, 99));
		return $orderSn;
	}
	
}