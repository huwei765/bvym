<?php

namespace Home\Controller;
use Think\Controller;

class YuyueController extends Controller{

	public function _initialize() {
		$this->dbname = CONTROLLER_NAME;
	}

	public function add(){
	    if(IS_POST){
        	$model = D($this->dbname);
        	$data=I('post.');
        	if (false === $data = $model->create()) {
        		$this->mtReturn(300,"失败，".$model->getError(),$_REQUEST['navTabId'],true);
            }
            //$data = $this->_befor_insert($data);
            if (method_exists($this, '_befor_insert')) {
                $data = $this->_befor_insert($data);
            }
            if (method_exists($this, '_befor_add')) {
                $this->_befor_add($data);
            }
            if($model->add($data)){
        		$id = $model->getLastInsID();
        		$this->mtReturn(200,"新增成功",$_REQUEST['navTabId'],true);
        	}

        }
	}

	public function _befor_insert($data){
	    $data["oktime"] = strtotime($data["oktime"]);
	    $data["ctype"] = intval($data["ctype"]);
	    return $data;
	}
	public function _befor_add($data){
    	$cuname = $data["cuname"];
        $count = D($this->dbname,"Logic")->getCountByCName($cuname);
        if($count >0){
        	$this->mtReturn(100,"该客户已经预约",$_REQUEST['navTabId'],true);
        }
    }

	public function _befor_edit(){}

	public function _befor_update($data){}

	public function _after_edit($id){}

	public function _befor_del($id){}

	protected function mtReturn($status,$info,$navTabId="",$closeCurrent=true,$skip=array(),$forward='',$forwardConfirm='') {
    	    $result = array();
            $result['statusCode'] = $status;
            $result['message'] = $info;
    		$result['tabid'] = strtolower($navTabId).'/index';
    		$result['skip'] = $skip;
            $result['forward'] = $forward;
    		$result['forwardConfirm']=$forwardConfirm;
            $result['closeCurrent'] =$closeCurrent;

            header("Content-Type:text/html; charset=utf-8");
            exit(json_encode($result));
    	}

}